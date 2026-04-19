<?php

namespace App\Controller;

use App\Service\AIService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class AIController extends AbstractController
{
    #[Route('/ai/search', name: 'ai_search', methods: ['POST'])]
    public function search(Request $request, AIService $aiService): JsonResponse
    {
        $data     = json_decode($request->getContent(), true);
        $question = trim($data['question'] ?? '');

        if (!$question) {
            return new JsonResponse(['type' => 'text', 'message' => 'Question vide.'], 400);
        }

        $result = $aiService->askAI($question);

        // Sérialiser les entités Activite
        if (isset($result['activites'])) {
            $result['activites'] = array_map(fn($a) => [
                'id'                => $a->getId(),
                'nom'               => $a->getNom(),
                'lieu'              => $a->getLieu(),
                'type'              => $a->getType(),
                'prix'              => $a->getPrix(),
                'placesDisponibles' => $a->getPlacesDisponibles(),
                'imageUrl'          => $a->getImageUrl(),
                'dateActivite'      => $a->getDateActivite()?->format('d/m/Y'),
            ], $result['activites']);
        }

        // Sérialiser les produits
        if (isset($result['produits'])) {
            $result['produits'] = array_map(fn($p) => [
                'id'    => $p->getId(),
                'nom'   => $p->getNom(),
                'prix'  => $p->getPrixMin(),
                'image' => $p->getImagePrincipale(),
            ], $result['produits']);
        }

        return new JsonResponse($result);
    }
}
