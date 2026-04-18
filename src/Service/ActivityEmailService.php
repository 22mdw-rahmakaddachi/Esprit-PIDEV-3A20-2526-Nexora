<?php

namespace App\Service;

use App\Entity\ParticipationDemande;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

class ActivityEmailService
{
    public function __construct(
        private MailerInterface $mailer,
        private Environment $twig,
        private LoggerInterface $logger,
        private PdfTicketService $pdfTicketService,
        private UrlGeneratorInterface $urlGenerator,
        private string $mailerFrom = 'noreply@nexora.com'
    ) {}

    private function send(string $to, string $subject, string $template, array $context): void
    {
        if (empty($to)) {
            $this->logger->error('[ActivityEmail] Adresse email vide, envoi annulé.', ['template' => $template]);
            return;
        }

        try {
            $html = $this->twig->render($template, $context);
            $email = (new Email())
                ->from($this->mailerFrom)
                ->to($to)
                ->subject($subject)
                ->html($html);

            $this->mailer->send($email);
            $this->logger->info('[ActivityEmail] Email envoyé.', ['to' => $to, 'subject' => $subject]);
            error_log('[ActivityEmail] OK - Email envoyé à ' . $to . ' sujet: ' . $subject);
        } catch (\Throwable $e) {
            $this->logger->error('[ActivityEmail] Échec envoi email.', [
                'to' => $to,
                'template' => $template,
                'error' => $e->getMessage(),
            ]);
            error_log('[ActivityEmail] ERREUR - ' . $e->getMessage() . ' | to: ' . $to . ' | template: ' . $template);
        }
    }

    /** Email au client : confirmation de sa demande */
    public function sendConfirmationDemande(ParticipationDemande $demande): void
    {
        $activite = $demande->getActivite();
        $this->send(
            $demande->getClientEmail(),
            '📋 Confirmation de votre demande — ' . $activite->getNom(),
            'emails/activite/confirmation_demande.html.twig',
            ['demande' => $demande, 'activite' => $activite]
        );
    }

    /** Email au partenaire : nouvelle demande reçue */
    public function sendNotificationPartenaire(ParticipationDemande $demande): void
    {
        $activite = $demande->getActivite();
        $partenaire = $activite->getPartenaire();
        $partenaireUser = $partenaire?->getUser();

        if (!$partenaireUser || empty($partenaireUser->getEmail())) {
            $this->logger->error('[ActivityEmail] Partenaire sans email, notification annulée.', [
                'demande_id' => $demande->getId(),
            ]);
            return;
        }

        $this->send(
            $partenaireUser->getEmail(),
            '📩 Nouvelle demande de participation — ' . $activite->getNom(),
            'emails/activite/notification_partenaire.html.twig',
            ['demande' => $demande, 'activite' => $activite]
        );
    }

    /** Email au client : demande acceptée */
    public function sendAcceptation(ParticipationDemande $demande): void
    {
        $activite = $demande->getActivite();

        // QR code encode l'URL du ticket — scanner ouvre le PDF
        $ticketUrl = $this->urlGenerator->generate(
            'app_ticket_view',
            ['id' => $demande->getId()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        $qrCodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=' . urlencode($ticketUrl);

        if (empty($demande->getClientEmail())) {
            $this->logger->error('[ActivityEmail] Email client vide pour acceptation.', ['demande_id' => $demande->getId()]);
            return;
        }

        try {
            $html = $this->twig->render('emails/activite/acceptation.html.twig', [
                'demande'    => $demande,
                'activite'   => $activite,
                'qrCodeUrl'  => $qrCodeUrl,
            ]);

            $email = (new Email())
                ->from($this->mailerFrom)
                ->to($demande->getClientEmail())
                ->subject('✅ Votre demande a été acceptée — ' . $activite->getNom())
                ->html($html);

            // Générer et joindre le ticket PDF
            $pdfContent = $this->pdfTicketService->generate($demande);
            if ($pdfContent !== null) {
                $filename = 'ticket-nexora-' . $demande->getId() . '.pdf';
                $email->addPart(new DataPart($pdfContent, $filename, 'application/pdf'));
                $this->logger->info('[ActivityEmail] Ticket PDF joint à l\'email.', ['demande_id' => $demande->getId()]);
            } else {
                $this->logger->warning('[ActivityEmail] Email acceptation envoyé sans PDF.', ['demande_id' => $demande->getId()]);
            }

            $this->mailer->send($email);
            $this->logger->info('[ActivityEmail] Email envoyé.', ['to' => $demande->getClientEmail(), 'subject' => 'Acceptation']);
            error_log('[ActivityEmail] OK - Email acceptation envoyé à ' . $demande->getClientEmail());

        } catch (\Throwable $e) {
            $this->logger->error('[ActivityEmail] Échec envoi acceptation: ' . $e->getMessage());
            error_log('[ActivityEmail] ERREUR acceptation - ' . $e->getMessage());
        }
    }

    /** Email au client : demande refusée */
    public function sendRefus(ParticipationDemande $demande): void
    {
        $activite = $demande->getActivite();
        $this->send(
            $demande->getClientEmail(),
            '❌ Votre demande a été refusée — ' . $activite->getNom(),
            'emails/activite/refus.html.twig',
            ['demande' => $demande, 'activite' => $activite]
        );
    }

    /** Email au client : confirmation de paiement */
    public function sendConfirmationPaiementClient(ParticipationDemande $demande, string $paymentId): void
    {
        $activite = $demande->getActivite();
        $this->send(
            $demande->getClientEmail(),
            '💳 Paiement confirmé — ' . $activite->getNom(),
            'emails/activite/paiement_client.html.twig',
            ['demande' => $demande, 'activite' => $activite, 'paymentId' => $paymentId]
        );
    }

    /** Email au partenaire : paiement reçu */
    public function sendConfirmationPaiementPartenaire(ParticipationDemande $demande, string $paymentId): void
    {
        $activite   = $demande->getActivite();
        $partenaire = $activite->getPartenaire();
        $partenaireUser = $partenaire?->getUser();

        if (!$partenaireUser || empty($partenaireUser->getEmail())) {
            return;
        }

        $this->send(
            $partenaireUser->getEmail(),
            '💰 Paiement reçu — ' . $activite->getNom(),
            'emails/activite/paiement_partenaire.html.twig',
            ['demande' => $demande, 'activite' => $activite, 'paymentId' => $paymentId]
        );
    }
}
