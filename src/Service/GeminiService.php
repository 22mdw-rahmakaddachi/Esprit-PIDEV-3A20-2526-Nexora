<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class GeminiService
{
    private const API_URL = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent';

    public function __construct(
        private HttpClientInterface $httpClient,
        private string $geminiApiKey
    ) {}

    /**
     * Génère un programme d'excursion sur 3 jours basé sur la localisation.
     */
    public function generateProgramme(string $location): string
    {
        if (!$this->geminiApiKey || $this->geminiApiKey === 'votre_cle_ici') {
            return "⚠️ Clé API Gemini manquante ou invalide dans le fichier .env";
        }

        try {
            $prompt = "Tu es un guide de voyage expert pour Nexora. Génère un itinéraire de 3 jours structuré et invitant pour une excursion à : $location. " .
                      "L'itinéraire doit être formaté de manière lisible avec 'Jour 1', 'Jour 2', 'Jour 3'. " .
                      "Donne des idées d'activités concrètes (matin, après-midi, soir).";

            $response = $this->httpClient->request('POST', self::API_URL, [
                'headers' => [
                    'x-goog-api-key' => $this->geminiApiKey,
                    'Content-Type'   => 'application/json',
                ],
                'json' => [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt]
                            ]
                        ]
                    ]
                ]
            ]);

            $data = $response->toArray();
            
            // Extraction du texte de la réponse Gemini
            return $data['candidates'][0]['content']['parts'][0]['text'] ?? "Désolé, je n'ai pas pu générer de programme pour cette destination.";

        } catch (\Throwable $e) {
            return "Erreur lors de la génération du programme : " . $e->getMessage();
        }
    }
}
