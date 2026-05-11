<?php

namespace App\Service;

use App\Repository\DestinationParticipantRepository;
use App\Repository\DestinationRepository;
use App\Repository\UsersRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;

class ReminderService
{
    public function __construct(
        private DestinationRepository $destinationRepo,
        private DestinationParticipantRepository $participantRepo,
        private UsersRepository $userRepo,
        private WeatherService $weatherService,
        private MailerInterface $mailer,
        private EntityManagerInterface $em
    ) {}

    /**
     * Recherche les départs pour demain et envoie les rappels.
     * @return int Le nombre d'emails envoyés.
     */
    public function sendDailyReminders(): int
    {
        $tomorrow = (new \DateTime())->modify('+1 day');
        $start = (clone $tomorrow)->setTime(0, 0, 0);
        $end = (clone $tomorrow)->setTime(23, 59, 59);

        // Trouver les destinations partant demain sans rappel envoyé
        $destinations = $this->destinationRepo->createQueryBuilder('d')
            ->where('d.dateLancement >= :start')
            ->andWhere('d.dateLancement <= :end')
            ->andWhere('d.reminderSent = :false')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->setParameter('false', false)
            ->getQuery()
            ->getResult();

        if (empty($destinations)) {
            return 0;
        }

        $countEmails = 0;

        foreach ($destinations as $destination) {
            $weather = $this->weatherService->getWeather($destination->getLocalisation() ?? '');
            $tips = $this->weatherService->getPackingTips($weather);

            $participants = $this->participantRepo->findBy(['destination' => $destination]);
            
            foreach ($participants as $participant) {
                $user = $this->userRepo->find($participant->getUserId());
                if (!$user || !$user->getEmail()) continue;

                $email = (new TemplatedEmail())
                    ->from('gti.enterprise.team@gmail.com')
                    ->to($user->getEmail())
                    ->subject('🔔 Votre départ approche : Préparez votre sac !')
                    ->htmlTemplate('emails/departure_reminder.html.twig')
                    ->context([
                        'userNom' => $user->getFullName(),
                        'destination' => $destination,
                        'weather' => $weather,
                        'tips' => $tips
                    ]);

                try {
                    $this->mailer->send($email);
                    $countEmails++;
                } catch (\Exception $e) {
                    // Log error if logger is available, otherwise skip
                }
            }

            $destination->setReminderSent(true);
        }

        $this->em->flush();

        return $countEmails;
    }
}
