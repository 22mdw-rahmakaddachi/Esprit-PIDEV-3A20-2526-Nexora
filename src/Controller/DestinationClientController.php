<?php

namespace App\Controller;

use App\Entity\Destination;
use App\Entity\DestinationMessage;
use App\Entity\DestinationParticipant;
use App\Entity\Users;
use App\Form\DestinationAvisType;
use App\Repository\DestinationMessageRepository;
use App\Repository\DestinationParticipantRepository;
use App\Repository\DestinationRepository;
use App\Service\TravelInfoService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[Route('/destinations')]
class DestinationClientController extends AbstractController
{
    private $em;
    private $destRepo;
    private $participantRepo;
    private $mailer;

    public function __construct(
        EntityManagerInterface $em,
        DestinationRepository $destRepo,
        DestinationParticipantRepository $participantRepo,
        MailerInterface $mailer
    ) {
        $this->em = $em;
        $this->destRepo = $destRepo;
        $this->participantRepo = $participantRepo;
        $this->mailer = $mailer;
    }

    #[Route('/', name: 'client_destination_index')]
    public function index(Request $request): Response
    {
        $search = $request->query->get('q', '');
        if ($search) {
            $destinations = $this->destRepo->createQueryBuilder('d')
                ->where('d.nom LIKE :q OR d.localisation LIKE :q')
                ->setParameter('q', '%'.$search.'%')
                ->getQuery()
                ->getResult();
        } else {
            $destinations = $this->destRepo->findAll();
        }

        return $this->render('destination/client/index.html.twig', [
            'destinations' => $destinations,
            'search' => $search
        ]);
    }

    #[Route('/{id}', name: 'client_destination_show', methods: ['GET', 'POST'])]
    public function show(Destination $destination, Request $request, DestinationParticipantRepository $partRepo): Response
    {
        /** @var Users|null $user */
        $user = $this->getUser();
        $hasJoined = $user ? $partRepo->hasJoined($destination->getId(), $user->getId()) : false;

        $reviewForm = $this->createForm(DestinationAvisType::class);
        $reviewForm->handleRequest($request);

        if ($reviewForm->isSubmitted() && $reviewForm->isValid()) {
            if (!$user) {
                return $this->redirectToRoute('app_login');
            }
            $review = $reviewForm->getData();
            $review->setUser($user);
            $review->setDestination($destination);
            $review->setCreatedAt(new \DateTimeImmutable());

            $this->em->persist($review);
            $this->em->flush();

            $this->addFlash('success', 'Merci pour votre avis !');
            return $this->redirectToRoute('client_destination_show', ['id' => $destination->getId()]);
        }

        return $this->render('destination/client/show.html.twig', [
            'destination' => $destination,
            'hasJoined'   => $hasJoined,
            'reviewForm'  => $reviewForm->createView()
        ]);
    }

    // ========================= REJOINDRE =========================
    #[Route('/{id}/rejoindre', name: 'client_destination_rejoindre', methods: ['POST'])]
    public function rejoindre(Destination $destination): JsonResponse
    {
        /** @var Users|null $user */
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['error' => 'Vous devez être connecté pour rejoindre une excursion.'], 401);
        }

        // 1. Vérifie si déjà rejoint
        if ($this->participantRepo->hasJoined($destination->getId(), $user->getId())) {
            return $this->json([
                'success'       => false,
                'message'       => 'Vous avez déjà rejoint cette excursion.',
                'nbParticipants'=> $destination->getNbParticipants(),
                'capaciteMax'   => $destination->getCapaciteMax(),
                'statut'        => $destination->getStatut(),
            ]);
        }

        // 2. Enregistre le participant
        $participant = new DestinationParticipant();
        $participant->setDestination($destination);
        $participant->setUserId($user->getId());
        $participant->setUserNom($user->getFullName());
        $this->em->persist($participant);

        // 3. Incrémente le compteur
        $destination->setNbParticipants($destination->getNbParticipants() + 1);
        
        $this->em->flush();
        // 4. 📧 ENVOI D'EMAIL PROFESSIONNEL
        try {
            $subject = '🚢 Confirmation : ' . $destination->getNom() . ' - Départ le ' . ($destination->getDateLancement() ? $destination->getDateLancement()->format('d/m/Y') : 'à venir');

            $email = (new TemplatedEmail())
                ->from('gti.enterprise.team@gmail.com')
                ->to($user->getEmail())
                ->subject($subject)
                ->htmlTemplate('emails/participation_confirmation.html.twig')
                ->context([
                    'user_prenom' => $user->getPrenom(),
                    'excursion_nom' => $destination->getNom(),
                    'excursion_localisation' => $destination->getLocalisation(),
                    'excursion_date' => $destination->getDateLancement(),
                    'excursion_id' => $destination->getId(),
                    'qrCodeUrl' => 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=' . urlencode('PARTICIPANT-' . $user->getId() . '-' . $destination->getId())
                ]);

            $this->mailer->send($email);
        } catch (\Throwable $e) {
            // Silence en production ou log via logger
        }

        return $this->json([
            'success'        => true,
            'message'        => 'Félicitations ! Vous avez rejoint cette excursion.',
            'nbParticipants' => $destination->getNbParticipants(),
            'capaciteMax'    => $destination->getCapaciteMax(),
            'statut'         => $destination->getStatut(),
        ]);
    }

    // ========================= WEATHER =========================
    #[Route('/{id}/weather', name: 'client_destination_weather')]
    public function weather(Destination $destination, HttpClientInterface $client): JsonResponse
    {
        $loc = $destination->getLocalisation() ?: $destination->getNom();
        $apiKey = $this->getParameter('app.weather.api_key');
        
        try {
            $url = "https://api.openweathermap.org/data/2.5/forecast?q=" . urlencode($loc) . "&appid=$apiKey&units=metric&lang=fr";
            $response = $client->request('GET', $url);
            $data = $response->toArray();

            // Formatage simplifié
            $current = [
                'temp' => round($data['list'][0]['main']['temp']),
                'description' => ucfirst($data['list'][0]['weather'][0]['description']),
                'icon' => $data['list'][0]['weather'][0]['icon'],
                'humidity' => $data['list'][0]['main']['humidity'],
                'wind' => round($data['list'][0]['wind']['speed'] * 3.6),
            ];

            $forecast = [];
            $days = [];
            foreach ($data['list'] as $item) {
                $date = new \DateTime($item['dt_txt']);
                $dayName = $date->format('D');
                if (!isset($days[$dayName]) && $date->format('H') == '12') {
                    $days[$dayName] = true;
                    $forecast[] = [
                        'dayName' => $dayName,
                        'temp' => round($item['main']['temp']),
                        'icon' => $item['weather'][0]['icon'],
                        'description' => $item['weather'][0]['description']
                    ];
                }
                if (count($forecast) >= 3) break;
            }

            return $this->json(['current' => $current, 'forecast' => $forecast]);

        } catch (\Exception $e) {
            return $this->json(['error' => 'Météo indisponible'], 500);
        }
    }

    // ========================= CHAT =========================
    #[Route('/{id}/messages', name: 'client_destination_messages', methods: ['GET'])]
    public function messages(Destination $destination, DestinationMessageRepository $msgRepo): JsonResponse
    {
        $messages = $msgRepo->findBy(['destination' => $destination], ['createdAt' => 'DESC'], 60);
        $data = [];
        foreach ($messages as $m) {
            $data[] = [
                'user_id'   => $m->getUserId(),
                'user_nom'  => $m->getUserNom(),
                'contenu'   => $m->getContenu(),
                'time'      => $m->getCreatedAt()->format('H:i')
            ];
        }
        return $this->json(array_reverse($data));
    }

    #[Route('/{id}/messages/send', name: 'client_destination_messages_send', methods: ['POST'])]
    public function sendMessage(Destination $destination, Request $request): JsonResponse
    {
        /** @var Users|null $user */
        $user = $this->getUser();
        if (!$user) return $this->json(['error' => 'Non connecté'], 401);

        $data = json_decode($request->getContent(), true);
        $content = $data['content'] ?? '';

        if (empty($content)) return $this->json(['error' => 'Message vide'], 400);

        $msg = new DestinationMessage();
        $msg->setDestination($destination);
        $msg->setUserId($user->getId());
        $msg->setUserNom($user->getPrenom() . ' ' . $user->getNom());
        $msg->setContenu($content);
        $msg->setCreatedAt(new \DateTimeImmutable());

        $this->em->persist($msg);
        $this->em->flush();

        return $this->json(['success' => true]);
    }
}
