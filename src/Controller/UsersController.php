<?php

namespace App\Controller;

use App\Entity\Users;
use App\Form\UsersType;
use App\Repository\UsersRepository;
use App\Service\FaceRecognitionService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/users')]
final class UsersController extends AbstractController
{
    #[Route(name: 'app_users_index', methods: ['GET'])]
    public function index(Request $request, UsersRepository $usersRepository): Response
    {
        $search = $request->query->get('search', '');
        $role   = $request->query->get('role', '');

        $users = $usersRepository->findBySearchAndRole($search, $role);

        $stats = [
            'ROLE_ADMIN'      => $usersRepository->countByRole('ROLE_ADMIN'),
            'ROLE_PARTENAIRE' => $usersRepository->countByRole('ROLE_PARTENAIRE'),
            'ROLE_USER'       => $usersRepository->countByRole('ROLE_USER'),
        ];

        return $this->render('users/index.html.twig', [
            'users'  => $users,
            'stats'  => $stats,
            'search' => $search,
            'role'   => $role,
        ]);
    }

    #[Route('/new', name: 'app_users_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = new Users();
        $form = $this->createForm(UsersType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $hashedPassword = $passwordHasher->hashPassword($user, $form->get('mdp')->getData());
            $user->setMdp($hashedPassword);
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('app_users_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('users/new.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_users_show', methods: ['GET'])]
    public function show(Users $user): Response
    {
        return $this->render('users/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_users_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Users $user, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $form = $this->createForm(UsersType::class, $user, ['is_edit' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plain = $form->get('mdp')->getData();
            if ($plain) {
                $hashedPassword = $passwordHasher->hashPassword($user, $user->getMdp());
                $user->setMdp($hashedPassword);
            }
            // S'assurer que le rôle n'est jamais null
            if (!$user->getRole()) {
                $user->setRole('ROLE_USER');
            }
            $entityManager->flush();

            return $this->redirectToRoute('app_users_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('users/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_users_delete', methods: ['POST'])]
    public function delete(Request $request, Users $user, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->getPayload()->getString('_token'))) {
            // Supprimer via DBAL pour éviter les FK violations
            $conn = $entityManager->getConnection();
            $userId = $user->getId();

            // 1. Nullifier les notifications liées aux demandes de cet user (FK demande_id)
            $conn->executeStatement(
                'UPDATE notification SET demande_id = NULL WHERE demande_id IN (SELECT id FROM participation_demande WHERE client_id = ?)',
                [$userId]
            );
            // 2. Supprimer les demandes de participation
            $conn->executeStatement('DELETE FROM participation_demande WHERE client_id = ?', [$userId]);
            // 3. Supprimer les avis destination (CASCADE mais on force)
            $conn->executeStatement('DELETE FROM destination_avis WHERE user_id = ?', [$userId]);
            // 4. Supprimer les empreintes
            $conn->executeStatement('DELETE FROM fingerprint WHERE user_id = ?', [$userId]);
            // 5. Nullifier les FK nullable
            $conn->executeStatement('UPDATE partenaire SET user_id = NULL WHERE user_id = ?', [$userId]);
            $conn->executeStatement('UPDATE notification SET user_id = NULL WHERE user_id = ?', [$userId]);

            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_users_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/toggle-block', name: 'app_users_toggle_block', methods: ['POST'])]
    public function toggleBlock(Users $user, EntityManagerInterface $entityManager): Response
    {
        $isBlocked = $user->getBlockUntil() > time();
        if ($isBlocked) {
            // Débloquer
            $user->setBlockUntil(0);
            $user->setBlockLevel(0);
            $user->setTentative(0);
            $this->addFlash('success', '✅ Utilisateur ' . $user->getPrenom() . ' débloqué.');
        } else {
            // Bloquer indéfiniment (100 ans)
            $user->setBlockUntil(time() + 60 * 60 * 24 * 365 * 100);
            $user->setBlockLevel(99);
            $this->addFlash('warning', '🔒 Utilisateur ' . $user->getPrenom() . ' bloqué.');
        }
        $entityManager->flush();
        return $this->redirectToRoute('app_users_index');
    }

    #[Route('/{id}/register-face', name: 'app_users_register_face', methods: ['POST'])]
    public function registerFace(int $id, Request $request, UsersRepository $usersRepo, FaceRecognitionService $faceService): Response
    {
        try {
            $user = $usersRepo->find($id);
            if (!$user) {
                return $this->json(['success' => false, 'message' => 'Utilisateur introuvable'], 404);
            }

            $faceImage = $request->files->get('face_image');
            if (!$faceImage) {
                return $this->json(['success' => false, 'message' => 'Aucune image fournie'], 400);
            }

            $result = $faceService->registerFace(
                $user->getId(),
                $faceImage,
                $user->getPrenom() . ' ' . $user->getNom()
            );

            return $this->json($result);

        } catch (\Throwable $e) {
            return $this->json([
                'success' => false,
                'message' => get_class($e) . ': ' . $e->getMessage() . ' (line ' . $e->getLine() . ' in ' . basename($e->getFile()) . ')'
            ], 500);
        }
    }
}
