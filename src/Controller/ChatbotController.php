<?php

namespace App\Controller;

use App\Repository\CodePromoRepository;
use App\Repository\ProduitParentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/chatbot')]
final class ChatbotController extends AbstractController
{
    #[Route('', name: 'app_chatbot')]
    public function index(): Response
    {
        return $this->render('chatbot/index.html.twig');
    }

    #[Route('/message', name: 'app_chatbot_message', methods: ['POST'])]
    public function message(
        Request $request,
        ProduitParentRepository $produitRepo,
        CodePromoRepository $promoRepo
    ): JsonResponse {
        $data    = json_decode($request->getContent(), true);
        $message = trim($data['message'] ?? '');

        if (empty($message)) {
            return $this->json(['error' => 'Message vide'], 400);
        }

        // Construire le contexte boutique depuis la base de données
        $context = $this->buildShopContext($produitRepo, $promoRepo);

        // Appel API OpenAI
        $apiKey = $_ENV['OPENAI_API_KEY'] ?? '';
        if (empty($apiKey) || $apiKey === 'sk-your-openai-api-key-here') {
            // Mode démo sans clé API
            $reply = $this->demoResponse($message, $produitRepo, $promoRepo);
            return $this->json(['reply' => $reply]);
        }

        try {
            $client = HttpClient::create();
            $response = $client->request('POST', 'https://api.openai.com/v1/chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type'  => 'application/json',
                ],
                'json' => [
                    'model'       => 'gpt-3.5-turbo',
                    'max_tokens'  => 500,
                    'temperature' => 0.7,
                    'messages'    => [
                        [
                            'role'    => 'system',
                            'content' => $context,
                        ],
                        [
                            'role'    => 'user',
                            'content' => $message,
                        ],
                    ],
                ],
            ]);

            $result = $response->toArray();
            $reply  = $result['choices'][0]['message']['content'] ?? 'Je n\'ai pas pu répondre.';
        } catch (\Throwable $e) {
            $reply = 'Désolé, une erreur est survenue. Veuillez réessayer.';
        }

        return $this->json(['reply' => $reply]);
    }

    /**
     * Construit le contexte système avec les données réelles de la boutique.
     */
    private function buildShopContext(ProduitParentRepository $produitRepo, CodePromoRepository $promoRepo): string
    {
        $produits = $produitRepo->findActifs();
        $promos   = $promoRepo->findAll();

        $produitsText = '';
        foreach ($produits as $p) {
            $prix = $p->getPrixMin() ? $p->getPrixMin() . ' TND' : 'N/A';
            $cat  = $p->getSousCategorie() ? $p->getSousCategorie()->getNom() : 'Sans catégorie';
            $produitsText .= "- {$p->getNom()} | Catégorie: {$cat} | Prix: {$prix} | Marque: {$p->getMarque()}\n";
        }

        $promosText = '';
        foreach ($promos as $promo) {
            if (!$promo->getActif()) continue;
            $type = $promo->getTypeReduction() === 'pourcentage'
                ? $promo->getValeurReduction() . '%'
                : $promo->getValeurReduction() . ' TND';
            $fin = $promo->getDateFin() ? $promo->getDateFin()->format('d/m/Y') : 'N/A';
            $promosText .= "- Code: {$promo->getCode()} | Réduction: {$type} | Valide jusqu\'au: {$fin}\n";
        }

        return "Tu es un assistant commercial pour la boutique NEXORA, une plateforme de produits outdoor et d'activités en Tunisie.
Tu dois aider les clients à trouver des produits selon leur budget, leurs besoins, et les informer sur les promotions disponibles.
Réponds toujours en français, de manière concise et amicale.
Ne réponds qu'aux questions liées à la boutique, aux produits, aux prix et aux promotions.

PRODUITS DISPONIBLES:
{$produitsText}

CODES PROMO ACTIFS:
" . ($promosText ?: "Aucun code promo actif en ce moment.\n") . "

Règles:
- Si le client donne un budget, propose les produits dont le prix est inférieur ou égal à ce budget.
- Si le client demande les promotions, liste les codes promo actifs.
- Si le client cherche un produit par catégorie ou marque, filtre la liste.
- Si tu ne trouves pas de produit correspondant, dis-le poliment.
- Ne parle pas de sujets hors boutique.";
    }

    /**
     * Réponse de démonstration sans clé API (basée sur des règles simples).
     */
    private function demoResponse(string $message, ProduitParentRepository $produitRepo, CodePromoRepository $promoRepo): string
    {
        $msg = mb_strtolower($message);

        // Cherche un budget dans le message
        preg_match('/(\d+)\s*(tnd|dt|dinars?)?/i', $message, $matches);
        $budget = isset($matches[1]) ? (float) $matches[1] : null;

        if ($budget && ($budget > 0)) {
            $produits = $produitRepo->findActifs();
            $found = [];
            foreach ($produits as $p) {
                if ($p->getPrixMin() && $p->getPrixMin() <= $budget) {
                    $found[] = "• {$p->getNom()} — {$p->getPrixMin()} TND";
                }
            }
            if (empty($found)) {
                return "Je n'ai pas trouvé de produits dans votre budget de {$budget} TND. Voulez-vous voir tous nos produits ?";
            }
            return "Voici les produits disponibles pour un budget de {$budget} TND :\n\n" . implode("\n", $found);
        }

        if (str_contains($msg, 'promo') || str_contains($msg, 'réduction') || str_contains($msg, 'code') || str_contains($msg, 'remise')) {
            $promos = $promoRepo->findAll();
            $actifs = array_filter($promos, fn($p) => $p->getActif());
            if (empty($actifs)) {
                return "Il n'y a pas de codes promo actifs en ce moment. Revenez bientôt !";
            }
            $lines = ["Voici nos codes promo actifs :\n"];
            foreach ($actifs as $promo) {
                $type = $promo->getTypeReduction() === 'pourcentage'
                    ? $promo->getValeurReduction() . '%'
                    : $promo->getValeurReduction() . ' TND';
                $fin = $promo->getDateFin() ? $promo->getDateFin()->format('d/m/Y') : 'N/A';
                $lines[] = "• **{$promo->getCode()}** — Réduction de {$type} (valide jusqu'au {$fin})";
            }
            return implode("\n", $lines);
        }

        if (str_contains($msg, 'produit') || str_contains($msg, 'article') || str_contains($msg, 'catalogue')) {
            $produits = $produitRepo->findActifs();
            if (empty($produits)) {
                return "Notre catalogue est en cours de mise à jour. Revenez bientôt !";
            }
            $lines = ["Voici nos produits disponibles :\n"];
            foreach (array_slice($produits, 0, 8) as $p) {
                $prix = $p->getPrixMin() ? $p->getPrixMin() . ' TND' : 'Prix sur demande';
                $lines[] = "• {$p->getNom()} — {$prix}";
            }
            if (count($produits) > 8) {
                $lines[] = "\n...et " . (count($produits) - 8) . " autres produits. Visitez notre boutique !";
            }
            return implode("\n", $lines);
        }

        if (str_contains($msg, 'bonjour') || str_contains($msg, 'salut') || str_contains($msg, 'hello')) {
            return "Bonjour ! 👋 Je suis l'assistant NEXORA. Je peux vous aider à :\n• Trouver des produits selon votre budget\n• Voir nos promotions en cours\n• Découvrir notre catalogue\n\nQue puis-je faire pour vous ?";
        }

        return "Bonjour ! Je suis l'assistant NEXORA. Je peux vous aider à trouver des produits selon votre budget ou vous informer sur nos promotions. Que recherchez-vous ?";
    }
}
