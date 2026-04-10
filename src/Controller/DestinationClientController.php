<?php

namespace App\Controller;

use App\Entity\Destination;
use App\Entity\DestinationMessage;
use App\Entity\DestinationParticipant;
use App\Repository\DestinationRepository;
use App\Repository\DestinationMessageRepository;
use App\Repository\DestinationParticipantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[Route('/destinations')]
class DestinationClientController extends AbstractController
{
    public function __construct(
        private DestinationRepository            $repo,
        private DestinationMessageRepository     $messageRepo,
        private DestinationParticipantRepository $participantRepo,
        private EntityManagerInterface           $em,
        private HttpClientInterface              $httpClient,
    ) {}

    // ========================= LIST CLIENT =========================
    #[Route('', name: 'client_destination_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $search = $request->query->get('search', '');

        $destinations = $search
            ? $this->repo->searchByLocalisation($search)
            : $this->repo->findAllOrdered();

        return $this->render('destination/client/index.html.twig', [
            'destinations' => $destinations,
            'search'       => $search,
        ]);
    }

    // ========================= DETAILS =========================
    #[Route('/{id}', name: 'client_destination_show', methods: ['GET'])]
    public function show(Destination $destination): Response
    {
        /** @var \App\Entity\User|null $user */
        $user = $this->getUser();
        $hasJoined = false;
        if ($user) {
            $hasJoined = $this->participantRepo->hasJoined($destination->getId(), $user->getId());
        }

        return $this->render('destination/client/show.html.twig', [
            'destination' => $destination,
            'hasJoined'   => $hasJoined,
        ]);
    }

    // ========================= REJOINDRE =========================
    #[Route('/{id}/rejoindre', name: 'client_destination_rejoindre', methods: ['POST'])]
    public function rejoindre(Destination $destination): JsonResponse
    {
        /** @var \App\Entity\User|null $user */
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['error' => 'Vous devez être connecté pour rejoindre une destination.'], 401);
        }

        // Vérifie si déjà rejoint
        if ($this->participantRepo->hasJoined($destination->getId(), $user->getId())) {
            return $this->json([
                'success'       => false,
                'message'       => 'Vous avez déjà rejoint cette destination.',
                'nbParticipants'=> $destination->getNbParticipants(),
                'capaciteMax'   => $destination->getCapaciteMax(),
                'statut'        => $destination->getStatut(),
            ]);
        }

        // Enregistre le participant
        $participant = new DestinationParticipant();
        $participant->setDestination($destination);
        $participant->setUserId($user->getId());
        $participant->setUserNom($user->getFullName());
        $this->em->persist($participant);

        // Incrémente le compteur
        $newCount = $destination->getNbParticipants() + 1;
        $destination->setNbParticipants($newCount);

        // Vérifie si le seuil est atteint → statut Disponible
        if ($newCount >= $destination->getCapaciteMax()) {
            $destination->setStatut('Disponible');
        }

        $this->em->flush();

        return $this->json([
            'success'        => true,
            'message'        => 'Vous avez rejoint cette destination !',
            'nbParticipants' => $destination->getNbParticipants(),
            'capaciteMax'    => $destination->getCapaciteMax(),
            'statut'         => $destination->getStatut(),
        ]);
    }

    // ========================= CHAT — LIRE =========================
    #[Route('/{id}/messages', name: 'client_destination_messages', methods: ['GET'])]
    public function messages(Destination $destination, Request $request): JsonResponse
    {
        $since = $request->query->get('since'); // timestamp optionnel
        $messages = $this->messageRepo->findByDestination($destination->getId(), 60);

        // Les messages viennent DESC, on les reverse pour l'affichage chronologique
        $messages = array_reverse($messages);

        $data = array_map(fn(DestinationMessage $m) => [
            'id'       => $m->getId(),
            'userNom'  => $m->getUserNom(),
            'contenu'  => htmlspecialchars($m->getContenu()),
            'time'     => $m->getCreatedAt()->format('H:i'),
            'userId'   => $m->getUserId(),
        ], $messages);

        return $this->json($data);
    }

    // ========================= CHAT — ENVOYER =========================
    #[Route('/{id}/messages/send', name: 'client_destination_messages_send', methods: ['POST'])]
    public function sendMessage(Destination $destination, Request $request): JsonResponse
    {
        /** @var \App\Entity\User|null $user */
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['error' => 'Non authentifié'], 401);
        }

        $data    = json_decode($request->getContent(), true);
        $contenu = trim($data['contenu'] ?? '');
        if ($contenu === '') {
            return $this->json(['error' => 'Message vide'], 400);
        }
        if (mb_strlen($contenu) > 500) {
            return $this->json(['error' => 'Message trop long (max 500 caractères)'], 400);
        }

        $msg = new DestinationMessage();
        $msg->setDestination($destination);
        $msg->setUserId($user->getId());
        $msg->setUserNom($user->getFullName());
        $msg->setContenu($contenu);
        $this->em->persist($msg);
        $this->em->flush();

        return $this->json([
            'id'      => $msg->getId(),
            'userNom' => $msg->getUserNom(),
            'contenu' => htmlspecialchars($msg->getContenu()),
            'time'    => $msg->getCreatedAt()->format('H:i'),
            'userId'  => $msg->getUserId(),
        ], 201);
    }

    // ========================= WEATHER PROXY =========================
    #[Route('/{id}/weather', name: 'client_destination_weather', methods: ['GET'])]
    public function weather(Destination $destination): JsonResponse
    {
        $apiKey = $_ENV['OPENWEATHER_API_KEY'] ?? null;
        if (!$apiKey || $apiKey === 'YOUR_OPENWEATHER_API_KEY_HERE') {
            return $this->json(['error' => 'Veuillez configurer la clé API OpenWeatherMap dans le fichier .env'], 500);
        }

        $location = $destination->getLocalisation();
        if (!$location) {
            return $this->json(['error' => 'Localisation non définie pour cette destination.'], 400);
        }

        try {
            $data = $this->fetchWeatherData($location, $apiKey);
            return $this->json($data);
        } catch (\Exception $e) {
            // Tentative de fallback sur la première partie de la localisation (ex: "Paris, France" -> "Paris")
            $parts = explode(',', $location);
            if (count($parts) > 1) {
                try {
                    $fallbackLocation = trim($parts[0]);
                    $data = $this->fetchWeatherData($fallbackLocation, $apiKey);
                    return $this->json($data);
                } catch (\Exception $e2) {
                    return $this->json(['error' => 'Météo non trouvée pour : ' . $location], 404);
                }
            }
            return $this->json(['error' => 'Météo non trouvée pour : ' . $location], 404);
        }
    }

    /**
     * @throws \Symfony\Contracts\HttpClient\Exception\ExceptionInterface
     */
    private function fetchWeatherData(string $location, string $apiKey): array
    {
        // 1. Current Weather
        $currentUrl = sprintf(
            'https://api.openweathermap.org/data/2.5/weather?q=%s&appid=%s&units=metric&lang=fr',
            urlencode($location),
            $apiKey
        );
        $currentResponse = $this->httpClient->request('GET', $currentUrl);
        $currentData = $currentResponse->toArray();

        // 2. Forecast
        $forecastUrl = sprintf(
            'https://api.openweathermap.org/data/2.5/forecast?q=%s&appid=%s&units=metric&lang=fr',
            urlencode($location),
            $apiKey
        );
        $forecastResponse = $this->httpClient->request('GET', $forecastUrl);
        $forecastData = $forecastResponse->toArray();

        $dailyForecast = [];
        $seenDays = [];
        $today = (new \DateTime())->format('Y-m-d');

        foreach ($forecastData['list'] as $item) {
            $date = (new \DateTime())->setTimestamp($item['dt'])->format('Y-m-d');
            if ($date === $today || in_array($date, $seenDays)) continue;
            
            $dailyForecast[] = [
                'date' => $date,
                'temp' => round($item['main']['temp']),
                'description' => ucfirst($item['weather'][0]['description']),
                'icon' => $item['weather'][0]['icon'],
                'dayName' => $this->getFrenchDayName($date)
            ];
            $seenDays[] = $date;
            if (count($dailyForecast) >= 3) break;
        }

        return [
            'current' => [
                'temp' => round($currentData['main']['temp']),
                'description' => ucfirst($currentData['weather'][0]['description']),
                'icon' => $currentData['weather'][0]['icon'],
                'humidity' => $currentData['main']['humidity'],
                'wind' => round($currentData['wind']['speed'] * 3.6),
            ],
            'forecast' => $dailyForecast
        ];
    }

    private function getFrenchDayName(string $date): string
    {
        $days = [
            'Monday' => 'Lun', 'Tuesday' => 'Mar', 'Wednesday' => 'Mer',
            'Thursday' => 'Jeu', 'Friday' => 'Ven', 'Saturday' => 'Sam', 'Sunday' => 'Dim'
        ];
        $dayName = (new \DateTime($date))->format('l');
        return $days[$dayName] ?? $dayName;
    }
}
