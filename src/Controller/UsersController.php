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
        $q    = $request->query->get('q');
        $role = $request->query->get('role');
        return $this->render('users/index.html.twig', [
            'users'     => $usersRepository->search($q, $role),
            'stats'     => $usersRepository->countByRole(),
            'q'         => $q,
            'roleActif' => $role,
            'roles'     => ['ROLE_ADMIN', 'ROLE_PARTENAIRE', 'ROLE_USER'],
        ]);
    }

    #[Route('/new', name: 'app_users_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $hasher): Response
    {
        $user = new Users();
        $form = $this->createForm(UsersType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user->setMdp($hasher->hashPassword($user, $form->get('mdp')->getData()));
            $em->persist($user);
            $em->flush();
            return $this->redirectToRoute('app_users_index', [], Response::HTTP_SEE_OTHER);
        }
        return $this->render('users/new.html.twig', ['user' => $user, 'form' => $form]);
    }

    #[Route('/{id}', name: 'app_users_show', methods: ['GET'])]
    public function show(Users $user): Response
    {
        return $this->render('users/show.html.twig', ['user' => $user]);
    }

    #[Route('/{id}/edit', name: 'app_users_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Users $user, EntityManagerInterface $em, UserPasswordHasherInterface $hasher): Response
    {
        $form = $this->createForm(UsersType::class, $user, ['is_edit' => true]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $plain = $form->get('mdp')->getData();
            if ($plain) {
                $user->setMdp($hasher->hashPassword($user, $plain));
            }
            $em->flush();
            return $this->redirectToRoute('app_users_index', [], Response::HTTP_SEE_OTHER);
        }
        return $this->render('users/edit.html.twig', ['user' => $user, 'form' => $form]);
    }

    #[Route('/{id}/toggle-block', name: 'app_users_toggle_block', methods: ['POST'])]
    public function toggleBlock(Request $request, Users $user, EntityManagerInterface $em): Response
    {
        if (!$this->isCsrfTokenValid('block' . $user->getId(), $request->request->get('_token'))) {
            throw $this->createAccessDeniedException();
        }
        $isBlocked = $user->getBlockUntil() > time();
        $user->setBlockUntil($isBlocked ? 0 : 9999999999);
        $em->flush();
        $nom = $user->getPrenom() . ' ' . $user->getNom();
        $this->addFlash($isBlocked ? 'success' : 'warning', $isBlocked ? "$nom est debloque." : "$nom est bloque.");
        return $this->redirectToRoute('app_users_index');
    }

    #[Route('/{id}', name: 'app_users_delete', methods: ['POST'])]
    public function delete(Request $request, Users $user, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete' . $user->getId(), $request->getPayload()->getString('_token'))) {
            $em->remove($user);
            $em->flush();
        }
        return $this->redirectToRoute('app_users_index', [], Response::HTTP_SEE_OTHER);
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