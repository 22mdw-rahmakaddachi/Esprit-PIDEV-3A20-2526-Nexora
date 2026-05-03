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

        // Sérialiser les excursions
        if (isset($result['excursions']) && is_array($result['excursions'])) {
            $result['excursions'] = array_map(fn($d) => [
                'id'            => $d->getId(),
                'nom'           => $d->getNom(),
                'localisation'  => $d->getLocalisation(),
                'statut'        => $d->getStatut(),
                'capaciteMax'   => $d->getCapaciteMax(),
                'nbParticipants'=> $d->getNbParticipants(),
                'image'         => $d->getFirstImage(),
                'dateLancement' => $d->getDateLancement()?->format('d/m/Y'),
            ], $result['excursions']);
        }

        return new JsonResponse($result);
    }

    #[Route('/api/ai/description', name: 'api_ai_description', methods: ['POST'])]
    public function generateDescription(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $nom  = trim($data['nom']  ?? '');
        $type = trim($data['type'] ?? '');
        $lieu = trim($data['lieu'] ?? '');

        if (!$nom || !$type || !$lieu) {
            return new JsonResponse(['error' => 'Données manquantes.'], 400);
        }

        // Essayer HuggingFace si la clé est disponible
        $apiKey = $_ENV['HUGGINGFACE_API_KEY'] ?? $_ENV['HUGGINGFACE_TOKEN'] ?? '';

        if (!empty($apiKey)) {
            try {
                $prompt = "Génère une description courte et attrayante en français (3-4 phrases) pour une activité de type {$type} intitulée \"{$nom}\" qui se déroule à {$lieu} en Tunisie. La description doit être engageante, mentionner le lieu et donner envie de participer. Ne commence pas par \"Voici\" ou \"Description\".";

                $client = \Symfony\Component\HttpClient\HttpClient::create(['timeout' => 15]);
                $response = $client->request('POST',
                    'https://api-inference.huggingface.co/models/mistralai/Mistral-7B-Instruct-v0.2',
                    [
                        'headers' => [
                            'Authorization' => 'Bearer ' . $apiKey,
                            'Content-Type'  => 'application/json',
                        ],
                        'json' => [
                            'inputs'     => "<s>[INST] {$prompt} [/INST]",
                            'parameters' => ['max_new_tokens' => 200, 'temperature' => 0.7, 'return_full_text' => false],
                        ],
                    ]
                );

                $result = $response->toArray(false);
                $text   = $result[0]['generated_text'] ?? null;

                if ($text) {
                    $text = trim(preg_replace('/^[\s\n]*/', '', $text));
                    return new JsonResponse(['description' => $text]);
                }
            } catch (\Throwable $e) {
                // Fallback si HuggingFace échoue
            }
        }

        // ── Fallback : génération locale sans API ──
        $description = $this->generateLocalDescription($nom, $type, $lieu);
        return new JsonResponse(['description' => $description]);
    }

    private function generateLocalDescription(string $nom, string $type, string $lieu): string
    {
        $templates = [
            'Sport' => [
                "Rejoignez-nous pour {nom}, une activité sportive exceptionnelle à {lieu} ! Que vous soyez débutant ou confirmé, cette expérience vous permettra de dépasser vos limites dans un cadre magnifique. Une aventure physique et humaine inoubliable vous attend.",
                "Découvrez {nom}, une activité sportive organisée au cœur de {lieu}. Encadrés par des professionnels passionnés, vous vivrez une expérience unique alliant effort, dépassement de soi et convivialité. Réservez votre place dès maintenant !",
            ],
            'Culture' => [
                "Plongez dans l'univers de {nom}, une activité culturelle enrichissante à {lieu}. Explorez l'histoire, les traditions et le patrimoine de cette région fascinante. Une expérience qui éveillera votre curiosité et élargira vos horizons.",
                "Vivez {nom}, une immersion culturelle unique à {lieu}. Entre découvertes artistiques et rencontres humaines, cette activité vous offrira une perspective nouvelle sur la richesse culturelle tunisienne.",
            ],
            'Gastronomie' => [
                "Savourez {nom}, une expérience gastronomique authentique à {lieu}. Découvrez les saveurs locales, les recettes traditionnelles et les secrets culinaires de la région. Un voyage gustatif qui ravira vos papilles !",
                "Embarquez pour {nom}, une aventure culinaire à {lieu}. Entre dégustations, ateliers et rencontres avec des artisans du goût, cette activité vous fera découvrir la richesse de la gastronomie tunisienne.",
            ],
            'Aventure' => [
                "Préparez-vous pour {nom}, une aventure palpitante à {lieu} ! Sensations fortes, paysages époustouflants et esprit d'équipe seront au rendez-vous. Une expérience qui restera gravée dans votre mémoire.",
                "Osez {nom}, une activité d'aventure au cœur de {lieu}. Dépassez vos limites, explorez des territoires sauvages et créez des souvenirs inoubliables avec des guides expérimentés.",
            ],
            'Bien-être' => [
                "Offrez-vous {nom}, une parenthèse de bien-être à {lieu}. Reconnectez-vous avec vous-même dans un cadre apaisant, guidé par des experts en relaxation et développement personnel.",
                "Ressourcez-vous avec {nom} à {lieu}. Cette activité de bien-être vous apportera sérénité, équilibre et énergie positive. Un moment précieux pour prendre soin de vous.",
            ],
        ];

        $typeKey = $type;
        if (!isset($templates[$typeKey])) {
            $typeKey = 'Aventure'; // fallback
        }

        $list = $templates[$typeKey];
        $tpl  = $list[array_rand($list)];

        return str_replace(['{nom}', '{lieu}'], [$nom, $lieu], $tpl);
    }
}
