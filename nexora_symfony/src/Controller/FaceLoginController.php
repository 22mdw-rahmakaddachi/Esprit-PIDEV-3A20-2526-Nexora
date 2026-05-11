<?php

namespace App\Controller;

use App\Entity\Users;
use App\Repository\UsersRepository;
use App\Service\FaceRecognitionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class FaceLoginController extends AbstractController
{
    #[Route('/login/face', name: 'login_face_page')]
    public function faceLoginPage(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_redirect_after_login');
        }

        return $this->render('security/face_login.html.twig', [
            'last_username' => $authenticationUtils->getLastUsername(),
            'error'         => $authenticationUtils->getLastAuthenticationError(),
        ]);
    }

    #[Route('/login/face/verify', name: 'login_face_verify', methods: ['POST'])]
    public function verifyFace(
        Request $request,
        FaceRecognitionService $faceService,
        UsersRepository $usersRepo,
        EventDispatcherInterface $dispatcher
    ): Response {
        $email     = $request->get('email');
        $faceImage = $request->files->get('face_image');

        if (!$email || !$faceImage) {
            return $this->json(['success' => false, 'message' => 'Email et image requis'], 400);
        }

        $user = $usersRepo->findOneBy(['email' => $email]);

        if (!$user) {
            return $this->json(['success' => false, 'message' => 'Utilisateur non trouvé'], 404);
        }

        $result = $faceService->verifyFace($user->getId(), $faceImage);

        if ($result['verified']) {
            // ── Créer la session Symfony ──
            $token = new UsernamePasswordToken($user, 'main', $user->getRoles());
            $this->container->get('security.token_storage')->setToken($token);
            $request->getSession()->set('_security_main', serialize($token));

            // Déclencher l'événement de login
            $event = new InteractiveLoginEvent($request, $token);
            $dispatcher->dispatch($event, 'security.interactive_login');

            return $this->json([
                'success'    => true,
                'similarity' => $result['similarity'],
                'redirect'   => $this->generateUrl('app_redirect_after_login'),
                'message'    => 'Connexion réussie ! Bienvenue ' . $user->getPrenom(),
            ]);
        }

        return $this->json([
            'success'    => false,
            'similarity' => $result['similarity'] ?? 0,
            'message'    => $result['message'],
        ], 401);
    }

    #[Route('/face/identify', name: 'face_identify', methods: ['POST'])]
    public function identifyFace(
        Request $request,
        FaceRecognitionService $faceService,
        UsersRepository $usersRepo
    ): Response {
        $faceImage = $request->files->get('face_image');
        if (!$faceImage) {
            return $this->json(['success' => false, 'message' => 'Image requise'], 400);
        }
        $result = $faceService->identifyFace($faceImage);
        if ($result['identified']) {
            $user = $usersRepo->find($result['user_id']);
            if ($user) {
                return $this->json([
                    'success'    => true,
                    'user'       => ['id' => $user->getId(), 'email' => $user->getEmail(), 'name' => $user->getPrenom() . ' ' . $user->getNom()],
                    'similarity' => $result['similarity'],
                ]);
            }
        }
        return $this->json(['success' => false, 'message' => $result['message']], 404);
    }

    #[Route('/login/face/identify', name: 'face_identify_login', methods: ['POST'])]
    public function identifyAndLogin(
        Request $request,
        FaceRecognitionService $faceService,
        UsersRepository $usersRepo,
        EventDispatcherInterface $dispatcher
    ): Response {
        $faceImage = $request->files->get('face_image');
        if (!$faceImage) {
            return $this->json(['success' => false, 'message' => 'Image requise'], 400);
        }

        $result = $faceService->identifyFace($faceImage);

        if (!($result['identified'] ?? false)) {
            return $this->json([
                'success' => false,
                'message' => $result['message'] ?? 'Visage non reconnu. Assurez-vous d\'avoir enregistré votre visage.',
            ], 401);
        }

        $user = $usersRepo->find($result['user_id']);
        if (!$user) {
            return $this->json(['success' => false, 'message' => 'Utilisateur introuvable'], 404);
        }

        // Créer la session Symfony
        $token = new UsernamePasswordToken($user, 'main', $user->getRoles());
        $this->container->get('security.token_storage')->setToken($token);
        $request->getSession()->set('_security_main', serialize($token));

        $event = new InteractiveLoginEvent($request, $token);
        $dispatcher->dispatch($event, 'security.interactive_login');

        return $this->json([
            'success'    => true,
            'user_name'  => $user->getPrenom() . ' ' . $user->getNom(),
            'similarity' => $result['similarity'],
            'redirect'   => $this->generateUrl('app_redirect_after_login'),
            'message'    => 'Bienvenue ' . $user->getPrenom() . ' !',
        ]);
    }
}
