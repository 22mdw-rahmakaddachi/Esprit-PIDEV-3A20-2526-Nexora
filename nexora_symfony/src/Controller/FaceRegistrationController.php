<?php

namespace App\Controller;

use App\Entity\Users;
use App\Service\FaceRecognitionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class FaceRegistrationController extends AbstractController
{
    #[Route('/face/register', name: 'face_register', methods: ['POST'])]
    public function register(Request $request, FaceRecognitionService $faceService): Response
    {
        /** @var Users $user */
        $user = $this->getUser();

        if (!$user instanceof Users) {
            return $this->json(['error' => 'Vous devez être connecté pour enregistrer votre visage'], 401);
        }

        $faceImage = $request->files->get('face_image');

        if (!$faceImage) {
            return $this->json(['error' => 'Aucune image fournie'], 400);
        }

        $result = $faceService->registerFace(
            $user->getId(),
            $faceImage,
            $user->getPrenom() . ' ' . $user->getNom()
        );

        return $this->json($result);
    }

    #[Route('/face/status', name: 'face_status', methods: ['GET'])]
    public function status(): Response
    {
        $user = $this->getUser();

        if (!$user instanceof Users) {
            return $this->json(['has_face_data' => false]);
        }

        $encodingFile = $this->getParameter('kernel.project_dir')
            . '/var/face_data/user_' . $user->getId() . '_encoding.json';

        return $this->json(['has_face_data' => file_exists($encodingFile)]);
    }

    #[Route('/face/delete', name: 'face_delete', methods: ['DELETE'])]
    public function delete(FaceRecognitionService $faceService): Response
    {
        $user = $this->getUser();

        if (!$user instanceof Users) {
            return $this->json(['error' => 'Non authentifié'], 401);
        }

        return $this->json(['success' => $faceService->deleteFaceData($user->getId())]);
    }
}
