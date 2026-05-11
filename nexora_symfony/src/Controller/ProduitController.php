<?php

namespace App\Controller;

use App\Repository\ProduitParentRepository;
use App\Repository\SousCategorieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class ProduitController extends AbstractController
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private string $geminiApiKey = ''
    ) {}

    #[Route('/boutique', name: 'app_produits')]
    public function index(
        Request $request,
        ProduitParentRepository $produitRepo,
        SousCategorieRepository $sousCatRepo
    ): Response {
        $sousCategorieId = $request->query->get('categorie') ? (int) $request->query->get('categorie') : null;

        return $this->render('produit/index.html.twig', [
            'produits'    => $produitRepo->findActifs($sousCategorieId),
            'categories'  => $sousCatRepo->findAll(),
            'categorieId' => $sousCategorieId,
        ]);
    }

    // ── RECHERCHE VOCALE IA ───────────────────────────────────────────────────

    #[Route('/boutique/vocal', name: 'app_produits_vocal', methods: ['POST'])]
    public function vocal(
        Request $request,
        ProduitParentRepository $produitRepo,
        SousCategorieRepository $sousCatRepo
    ): JsonResponse {
        $texte = trim($request->request->get('texte', ''));

        if (mb_strlen($texte) < 2) {
            return $this->json(['error' => 'Texte trop court'], 400);
        }

        // ── Étape 1 : Gemini extrait les mots-clés ──
        $keywords = $this->extractKeywordsWithGemini($texte);

        // ── Étape 2 : Recherche en BDD ──
        $produits = $produitRepo->searchByKeywords($keywords);

        // ── Étape 3 : Formater les résultats ──
        $results = array_map(function ($p) {
            return [
                'id'          => $p->getId(),
                'nom'         => $p->getNom(),
                'description' => $p->getDescriptionCourte() ?? '',
                'marque'      => $p->getMarque() ?? '',
                'image'       => $p->getImagePrincipale()
                    ? '/uploads/produits/' . $p->getImagePrincipale()
                    : null,
                'prix_min'    => $p->getPrixMin(),
                'url'         => '/boutique/' . $p->getId(),
                'categorie'   => $p->getSousCategorie()?->getNom() ?? '',
            ];
        }, $produits);

        return $this->json([
            'texte'    => $texte,
            'keywords' => $keywords,
            'total'    => count($results),
            'produits' => $results,
        ]);
    }

    /**
     * Utilise Gemini pour extraire les mots-clés pertinents d'une phrase vocale.
     * Fallback intelligent si Gemini est indisponible (quota dépassé).
     * @return string[]
     */
    private function extractKeywordsWithGemini(string $texte): array
    {
        // ── Essayer Gemini d'abord ──
        if ($this->geminiApiKey) {
            try {
                $prompt = "Extrait les mots-clés de recherche de produits e-commerce depuis cette phrase en français. "
                        . "Réponds UNIQUEMENT avec un JSON sur une ligne : {\"keywords\":[\"mot1\",\"mot2\",...]} "
                        . "Maximum 5 mots-clés, uniquement les mots utiles pour chercher un produit (nom, type, matière, usage, saison). "
                        . "Phrase : \"" . addslashes($texte) . "\"";

                $response = $this->httpClient->request('POST',
                    'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent',
                    [
                        'headers' => [
                            'x-goog-api-key' => $this->geminiApiKey,
                            'Content-Type'   => 'application/json',
                        ],
                        'json' => [
                            'contents' => [['parts' => [['text' => $prompt]]]],
                            'generationConfig' => [
                                'temperature'    => 0.1,
                                'maxOutputTokens'=> 200,
                                'thinkingConfig' => ['thinkingBudget' => 0],
                            ],
                        ],
                        'timeout' => 8,
                    ]
                );

                if ($response->getStatusCode() === 200) {
                    $data   = $response->toArray(false);
                    $raw    = $data['candidates'][0]['content']['parts'][0]['text'] ?? '{}';
                    $clean  = trim(preg_replace('/```json|```/i', '', $raw));
                    $result = json_decode($clean, true);
                    $kws    = $result['keywords'] ?? [];

                    if (!empty($kws)) {
                        return array_slice($kws, 0, 5);
                    }
                }
            } catch (\Throwable) {
                // Gemini indisponible → fallback intelligent
            }
        }

        // ── Fallback intelligent : dictionnaire de synonymes e-commerce ──
        return $this->extractKeywordsFallback($texte);
    }

    /**
     * Fallback intelligent sans API — dictionnaire de synonymes e-commerce.
     * @return string[]
     */
    private function extractKeywordsFallback(string $texte): array
    {
        $texte = mb_strtolower($texte);

        // Mots à ignorer (stop words français)
        $stopWords = [
            'je', 'tu', 'il', 'elle', 'nous', 'vous', 'ils', 'elles',
            'veux', 'veux', 'cherche', 'chercher', 'trouver', 'avoir',
            'un', 'une', 'des', 'le', 'la', 'les', 'de', 'du', 'au',
            'pour', 'avec', 'sans', 'sur', 'sous', 'dans', 'par',
            'que', 'qui', 'quoi', 'comment', 'quelque', 'chose',
            'mon', 'ma', 'mes', 'ton', 'ta', 'ses', 'son',
            'est', 'sont', 'être', 'avoir', 'faire',
            'très', 'trop', 'peu', 'bien', 'bon', 'bonne',
        ];

        // Dictionnaire de synonymes / expansion e-commerce
        $synonymes = [
            'tenue'      => ['tenue', 'vêtement', 'habit', 'outfit'],
            'vetement'   => ['vêtement', 'tenue', 'habit'],
            'sport'      => ['sport', 'sportif', 'fitness', 'gym'],
            'chaud'      => ['chaud', 'hiver', 'laine', 'manteau', 'veste'],
            'froid'      => ['froid', 'hiver', 'imperméable'],
            'ete'        => ['été', 'léger', 'frais'],
            'hiver'      => ['hiver', 'chaud', 'laine'],
            'sac'        => ['sac', 'sacoche', 'backpack'],
            'chaussure'  => ['chaussure', 'basket', 'sneaker', 'botte'],
            'cadeau'     => ['cadeau', 'offrir'],
            'pas cher'   => ['économique', 'abordable'],
            'leger'      => ['léger', 'light'],
            'randonnee'  => ['randonnée', 'trek', 'outdoor', 'montagne'],
            'running'    => ['running', 'course', 'jogging'],
            'yoga'       => ['yoga', 'pilates', 'stretching'],
            'natation'   => ['natation', 'piscine', 'maillot'],
            'cyclisme'   => ['cyclisme', 'vélo', 'bike'],
        ];

        // Extraire les mots significatifs
        $mots = preg_split('/\s+/', $texte);
        $keywords = [];

        foreach ($mots as $mot) {
            $mot = trim($mot, '.,!?;:');
            if (mb_strlen($mot) < 3) continue;
            if (in_array($mot, $stopWords)) continue;

            // Chercher dans le dictionnaire de synonymes
            foreach ($synonymes as $cle => $valeurs) {
                if ($mot === $cle || in_array($mot, $valeurs)) {
                    $keywords[] = $cle;
                    break;
                }
            }

            // Ajouter le mot tel quel s'il n'est pas déjà présent
            if (!in_array($mot, $keywords)) {
                $keywords[] = $mot;
            }
        }

        return array_unique(array_slice($keywords, 0, 5));
    }

    #[Route('/boutique/{id}', name: 'app_produit_show')]
    public function show(int $id, ProduitParentRepository $produitRepo): Response
    {
        $produit = $produitRepo->find($id);

        return $this->render('produit/show.html.twig', [
            'produit' => $produit,
        ]);
    }
}
