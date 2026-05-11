<?php

namespace App\Controller;

use App\Entity\Users;
use App\Repository\ActiviteRepository;
use App\Repository\ParticipationDemandeRepository;
use App\Service\WeatherService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class ActiviteController extends AbstractController
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private string $geminiApiKey = ''
    ) {}

    private function getClientId(): int
    {
        $user = $this->getUser();
        return $user instanceof Users ? ($user->getId() ?? 0) : 0;
    }

    #[Route('/activites', name: 'app_activites')]
    public function index(ActiviteRepository $repo, Request $request): Response
    {
        $lieu = $request->query->get('lieu');
        $type = $request->query->get('type');

        return $this->render('activite/index.html.twig', [
            'activites' => $repo->findWithFilters($type, $lieu),
            'types'     => $repo->findTypesVisibles(),
            'lieux'     => ['Ariana','Béja','Ben Arous','Bizerte','Gabès','Gafsa','Jendouba','Kairouan','Kasserine','Kébili','Le Kef','Mahdia','La Manouba','Médenine','Monastir','Nabeul','Sfax','Sidi Bouzid','Siliana','Sousse','Tataouine','Tozeur','Tunis','Zaghouan'],
            'typeActif' => $type,
            'lieuActif' => $lieu,
        ]);
    }

    #[Route('/activites/{id}', name: 'app_activite_show')]
    public function show(int $id, ActiviteRepository $repo, ParticipationDemandeRepository $demandeRepo): Response
    {
        $activite = $repo->find($id);
        if (!$activite) throw $this->createNotFoundException();

        return $this->render('activite/show.html.twig', [
            'activite'         => $activite,
            'demandeExistante' => $demandeRepo->findExisting($id, $this->getClientId()),
        ]);
    }

    // ── CONSEIL IA MÉTÉO + ACTIVITÉS ─────────────────────────────────────────

    #[Route('/activites/conseil-meteo', name: 'app_activites_conseil_meteo', methods: ['GET'])]
    public function conseilMeteo(
        Request $request,
        ActiviteRepository $repo,
        WeatherService $weatherService
    ): JsonResponse {
        $lieu     = $request->query->get('lieu', 'Tunis');
        $datetime = $request->query->get('datetime', '');

        if (!$datetime) {
            return $this->json(['error' => 'Date manquante'], 400);
        }

        $targetTs = strtotime($datetime);
        if ($targetTs > strtotime('+5 days')) {
            return $this->json(['error' => 'limit', 'message' => 'Prévisions disponibles sur 5 jours max.']);
        }
        if ($targetTs < time()) {
            return $this->json(['error' => 'past', 'message' => 'La date est dans le passé.']);
        }

        // ── Étape 1 : Récupérer la météo ──
        $meteo = $weatherService->getForecast($lieu, $datetime);
        if (!$meteo) {
            return $this->json(['error' => 'unavailable', 'message' => 'Météo indisponible pour ce lieu.']);
        }

        // ── Étape 2 : Récupérer les activités du lieu ──
        $activites = $repo->findWithFilters(null, $lieu);
        if (empty($activites)) {
            $activites = $repo->findWithFilters(null, null); // toutes si aucune pour ce lieu
        }

        // ── Étape 3 : Gemini analyse météo + recommande les activités ──
        $conseil = $this->getConseilGemini($meteo, $activites, $lieu);

        return $this->json([
            'meteo'   => $meteo,
            'conseil' => $conseil,
        ], 200, [], ['json_encode_options' => JSON_UNESCAPED_UNICODE]);
    }

    /**
     * Gemini analyse la météo et recommande les activités adaptées.
     */
    private function getConseilGemini(array $meteo, array $activites, string $lieu): array
    {
        // Construire la liste des activités
        $listeActivites = implode(', ', array_map(
            fn($a) => '"' . $a->getNom() . ' (' . $a->getType() . ')"',
            array_slice($activites, 0, 15)
        ));

        $prompt = "Tu es un conseiller d'activités touristiques. "
                . "Voici la météo prévue à {$lieu} : "
                . "{$meteo['emoji']} {$meteo['temp']}°C, {$meteo['description']}, "
                . "vent {$meteo['wind']} km/h, humidité {$meteo['humidity']}%. "
                . "Voici les activités disponibles : {$listeActivites}. "
                . "Réponds UNIQUEMENT avec un JSON sur une ligne sans markdown : "
                . "{\"recommandees\":[\"nom1\",\"nom2\",\"nom3\"],\"deconseillees\":[\"nom4\"],\"conseil\":\"phrase courte\",\"emoji\":\"emoji\"} "
                . "Recommande 2-3 activités adaptées à cette météo et déconseille celles qui ne conviennent pas.";

        // Fallback basé sur règles si Gemini indisponible
        $fallback = $this->getConseilFallback($meteo, $activites);

        if (!$this->geminiApiKey) {
            return $fallback;
        }

        try {
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
                            'temperature'    => 0.3,
                            'maxOutputTokens'=> 400,
                            'thinkingConfig' => ['thinkingBudget' => 0],
                        ],
                    ],
                    'timeout' => 10,
                ]
            );

            if ($response->getStatusCode() !== 200) {
                return $fallback;
            }

            $data   = $response->toArray(false);
            $raw    = $data['candidates'][0]['content']['parts'][0]['text'] ?? '{}';
            $clean  = trim(preg_replace('/```json|```/i', '', $raw));
            $result = json_decode($clean, true);

            if (!$result || !isset($result['conseil'])) {
                return $fallback;
            }

            return [
                'source'        => 'gemini',
                'recommandees'  => $result['recommandees']  ?? [],
                'deconseillees' => $result['deconseillees'] ?? [],
                'conseil'       => $result['conseil']       ?? '',
                'emoji'         => $result['emoji']         ?? '🌤️',
            ];

        } catch (\Throwable) {
            return $fallback;
        }
    }

    /**
     * Fallback intelligent basé sur des règles météo sans API.
     */
    private function getConseilFallback(array $meteo, array $activites): array
    {
        $condition = $meteo['condition'];
        $temp      = $meteo['temp'];
        $wind      = $meteo['wind'];

        $typesRecommandes  = [];
        $typesDeconseilles = [];
        $conseil           = '';
        $emoji             = '🌤️';

        if (str_contains($condition, 'Thunderstorm')) {
            $typesDeconseilles = ['Sport', 'Aventure'];
            $typesRecommandes  = ['Culture', 'Gastronomie', 'Bien-être'];
            $conseil = "Orage prévu — privilégiez les activités en intérieur.";
            $emoji   = '⛈️';
        } elseif (str_contains($condition, 'Rain')) {
            $typesDeconseilles = ['Sport', 'Aventure'];
            $typesRecommandes  = ['Culture', 'Gastronomie', 'Bien-être'];
            $conseil = "Pluie prévue — idéal pour la culture et la gastronomie.";
            $emoji   = '🌧️';
        } elseif ($temp > 32) {
            $typesDeconseilles = ['Sport'];
            $typesRecommandes  = ['Gastronomie', 'Culture', 'Bien-être'];
            $conseil = "Forte chaleur — évitez les activités sportives intenses.";
            $emoji   = '🥵';
        } elseif ($temp < 10) {
            $typesDeconseilles = ['Bien-être'];
            $typesRecommandes  = ['Sport', 'Aventure', 'Culture'];
            $conseil = "Temps frais — parfait pour les activités dynamiques.";
            $emoji   = '🧥';
        } elseif (str_contains($condition, 'Clear') && $temp >= 18 && $temp <= 28) {
            $typesRecommandes  = ['Sport', 'Aventure', 'Culture'];
            $conseil = "Conditions idéales — toutes les activités sont recommandées !";
            $emoji   = '☀️';
        } else {
            $typesRecommandes = ['Culture', 'Gastronomie'];
            $conseil = "Conditions correctes — profitez des activités culturelles.";
        }

        $recommandees  = [];
        $deconseillees = [];

        foreach ($activites as $a) {
            if (in_array($a->getType(), $typesRecommandes)) {
                $recommandees[] = $a->getNom();
            } elseif (in_array($a->getType(), $typesDeconseilles)) {
                $deconseillees[] = $a->getNom();
            }
        }

        return [
            'source'        => 'fallback',
            'recommandees'  => array_slice($recommandees, 0, 3),
            'deconseillees' => array_slice($deconseillees, 0, 2),
            'conseil'       => $conseil,
            'emoji'         => $emoji,
        ];
    }

    // ── MÉTÉO POUR UNE DATE/HEURE CHOISIE ────────────────────────────────────

    #[Route('/activites/{id}/weather', name: 'app_activite_weather', methods: ['GET'])]
    public function weather(
        int $id,
        Request $request,
        ActiviteRepository $repo,
        WeatherService $weatherService
    ): JsonResponse {
        $activite = $repo->find($id);
        if (!$activite) {
            return $this->json(['error' => 'Activité introuvable'], 404);
        }

        $datetime = $request->query->get('datetime', '');
        $city     = $activite->getLieu() ?? 'Tunis';

        if (!$datetime) {
            return $this->json(['error' => 'Date manquante'], 400);
        }

        // Vérifier que la date est dans les 5 prochains jours (limite API gratuite)
        $targetTs = strtotime($datetime);
        $maxTs    = strtotime('+5 days');

        if ($targetTs > $maxTs) {
            return $this->json([
                'error'   => 'limit',
                'message' => 'Les prévisions météo sont disponibles uniquement pour les 5 prochains jours.',
            ]);
        }

        if ($targetTs < time()) {
            return $this->json([
                'error'   => 'past',
                'message' => 'La date choisie est dans le passé.',
            ]);
        }

        $forecast = $weatherService->getForecast($city, $datetime);

        if (!$forecast) {
            return $this->json([
                'error'   => 'unavailable',
                'message' => 'Météo indisponible pour cette ville.',
            ]);
        }

        return $this->json($forecast, 200, [], ['json_encode_options' => JSON_UNESCAPED_UNICODE]);
    }
}
