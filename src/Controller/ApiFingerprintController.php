<?php

namespace App\Controller;

use App\Entity\Fingerprint;
use App\Entity\Users;
use App\Repository\FingerprintRepository;
use App\Repository\UsersRepository;
use App\Service\FingerprintBridgeService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[Route('/api/fingerprint')]
class ApiFingerprintController extends AbstractController
{
    // ── Enregistrer une empreinte après succès du lecteur ──
    #[Route('/enroll_success', name: 'api_fingerprint_enroll', methods: ['POST'])]
    public function enrollSuccess(Request $request, EntityManagerInterface $em, UsersRepository $usersRepo): JsonResponse
    {
        $data   = json_decode($request->getContent(), true);
        $userId = $data['user_id'] ?? null;
        $fingId = $data['finger_id'] ?? null;

        if (!$userId || $fingId === null) {
            return $this->json(['error' => 'user_id et finger_id requis'], 400);
        }

        $user = $usersRepo->find($userId);
        if (!$user) {
            return $this->json(['error' => 'Utilisateur introuvable'], 404);
        }

        $fingerprint = new Fingerprint();
        $fingerprint->setFingerId($fingId);
        $fingerprint->setUser($user);
        $fingerprint->setCreatedAt(new \DateTimeImmutable());

        $em->persist($fingerprint);
        $em->flush();

        return $this->json(['success' => true, 'message' => 'Empreinte enregistrée']);
    }

    // ── Vérifier une empreinte et retourner l'utilisateur ──
    #[Route('/verify', name: 'api_fingerprint_verify', methods: ['POST'])]
    public function verify(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data   = json_decode($request->getContent(), true);
        $fingId = $data['finger_id'] ?? null;

        if ($fingId === null) {
            return $this->json(['authenticated' => false, 'message' => 'finger_id requis'], 400);
        }

        $fingerprint = $em->getRepository(Fingerprint::class)->findOneBy(['fingerId' => $fingId]);

        if ($fingerprint) {
            $fingerprint->setLastUsedAt(new \DateTimeImmutable());
            $em->flush();

            $user = $fingerprint->getUser();
            return $this->json([
                'authenticated' => true,
                'user' => [
                    'id'    => $user->getId(),
                    'email' => $user->getEmail(),
                    'name'  => $user->getPrenom() . ' ' . $user->getNom(),
                ]
            ]);
        }

        return $this->json(['authenticated' => false, 'message' => 'Empreinte non reconnue'], 401);
    }

    // ── Login par empreinte (crée la session Symfony) ──
    #[Route('/login', name: 'api_fingerprint_login', methods: ['POST'])]
    public function fingerprintLogin(
        Request $request,
        FingerprintBridgeService $bridge,
        EntityManagerInterface $em,
        EventDispatcherInterface $dispatcher
    ): JsonResponse {
        try {
            // Demander au bridge Python de lire l'empreinte
            $result = $bridge->verifyFingerprint();

            if (!($result['authenticated'] ?? false)) {
                return $this->json([
                    'success' => false,
                    'message' => $result['message'] ?? 'Empreinte non reconnue',
                ], 401);
            }

            $fingId      = $result['finger_id'] ?? null;
            $fingerprint = $em->getRepository(Fingerprint::class)->findOneBy(['fingerId' => $fingId]);

            if (!$fingerprint) {
                return $this->json(['success' => false, 'message' => 'Empreinte non enregistrée'], 401);
            }

            $user = $fingerprint->getUser();
            $fingerprint->setLastUsedAt(new \DateTimeImmutable());
            $em->flush();

            // Créer la session Symfony
            $token = new UsernamePasswordToken($user, 'main', $user->getRoles());
            $this->container->get('security.token_storage')->setToken($token);
            $request->getSession()->set('_security_main', serialize($token));

            $dispatcher->dispatch(new InteractiveLoginEvent($request, $token), 'security.interactive_login');

            return $this->json([
                'success'  => true,
                'message'  => 'Bienvenue ' . $user->getPrenom() . ' !',
                'redirect' => $this->generateUrl('app_redirect_after_login'),
                'user'     => ['name' => $user->getPrenom() . ' ' . $user->getNom()],
            ]);

        } catch (\Exception $e) {
            return $this->json(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()], 500);
        }
    }

    // ── Bridge : enrôler via le lecteur ──
    #[Route('/bridge/enroll', name: 'api_fingerprint_bridge_enroll', methods: ['POST'])]
    public function bridgeEnroll(Request $request, FingerprintBridgeService $bridge): JsonResponse
    {
        set_time_limit(180); // 3 minutes max
        ini_set('default_socket_timeout', '120');
        $data   = json_decode($request->getContent(), true);
        $result = $bridge->enrollFingerprint($data['user_id'] ?? 0);
        return $this->json($result);
    }

    // ── Statut de l'enrôlement en cours (polling) ──
    #[Route('/enroll/status', name: 'api_fingerprint_enroll_status', methods: ['GET'])]
    public function enrollStatus(FingerprintBridgeService $bridge): JsonResponse
    {
        try {
            $r = $bridge->getEnrollStatus();
            return $this->json($r);
        } catch (\Exception $e) {
            return $this->json(['step' => 0]);
        }
    }

    // ── Bridge : statut du lecteur ──
    #[Route('/bridge/status', name: 'api_fingerprint_status', methods: ['GET'])]
    public function bridgeStatus(FingerprintBridgeService $bridge): JsonResponse
    {
        try {
            return $this->json($bridge->getStatus());
        } catch (\Exception $e) {
            return $this->json(['connected' => false, 'message' => 'Lecteur non disponible']);
        }
    }

    // ── Lancer le bridge Python ──
    #[Route('/bridge/start', name: 'api_fingerprint_start_bridge', methods: ['POST'])]
    public function startBridge(): JsonResponse
    {
        $scriptPath = $this->getParameter('kernel.project_dir') . '/fingerprint_bridge.py';
        if (!file_exists($scriptPath)) {
            return $this->json(['success' => false, 'message' => 'Script bridge introuvable']);
        }
        // Lancer en arrière-plan
        if (PHP_OS_FAMILY === 'Windows') {
            pclose(popen('start /B python ' . escapeshellarg($scriptPath), 'r'));
        } else {
            exec('python ' . escapeshellarg($scriptPath) . ' > /dev/null 2>&1 &');
        }
        sleep(2); // attendre démarrage
        return $this->json(['success' => true, 'message' => 'Bridge démarré']);
    }

    // ── Supprimer une empreinte ──
    #[Route('/{id}/delete', name: 'api_fingerprint_delete', methods: ['DELETE', 'POST'])]
    public function deleteFingerprint(int $id, EntityManagerInterface $em): JsonResponse
    {
        $fingerprint = $em->getRepository(Fingerprint::class)->find($id);
        if (!$fingerprint) {
            return $this->json(['error' => 'Introuvable'], 404);
        }
        $em->remove($fingerprint);
        $em->flush();
        return $this->json(['success' => true]);
    }
}
