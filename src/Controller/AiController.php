<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class AiController extends AbstractController
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private string $huggingFaceToken = ''
    ) {}

    #[Route('/api/ai/description', name: 'api_ai_description', methods: ['POST'])]
    public function generateDescription(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $nom  = trim($data['nom']  ?? '');
        $type = trim($data['type'] ?? '');
        $lieu = trim($data['lieu'] ?? '');

        if (!$nom || !$type || !$lieu) {
            return $this->json(['error' => 'Nom, type et lieu requis.'], 400);
        }

        if (empty($this->huggingFaceToken)) {
            return $this->json(['error' => 'Token Hugging Face non configuré.'], 500);
        }

        $prompt = "Écris une description courte et attrayante en français (3-4 phrases) pour une activité de type {$type} appelée \"{$nom}\" située à {$lieu}, Tunisie. Décris ce que les participants vont vivre et apprécier. Sois enthousiaste et invitant.";

        try {
            $response = $this->httpClient->request('POST',
                'https://router.huggingface.co/novita/v3/openai/chat/completions',
                [
                    'timeout' => 30,
                    'headers' => [
                        'Authorization' => 'Bearer ' . $this->huggingFaceToken,
                        'Content-Type'  => 'application/json',
                    ],
                    'json' => [
                        'model'    => 'meta-llama/llama-3.1-8b-instruct',
                        'messages' => [
                            ['role' => 'user', 'content' => $prompt]
                        ],
                        'max_tokens' => 200,
                    ],
                ]
            );

            $result = $response->toArray();
            $generated = $result['choices'][0]['message']['content'] ?? null;

            if (!$generated) {
                return $this->json(['error' => 'Réponse vide de l\'API.'], 500);
            }

            return $this->json(['description' => trim($generated)]);

        } catch (\Throwable $e) {
            return $this->json(['error' => 'Erreur API: ' . $e->getMessage()], 500);
        }
    }
}
