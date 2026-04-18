<?php

namespace App\Service;

use App\Entity\ParticipationDemande;
use Dompdf\Dompdf;
use Dompdf\Options;
use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

class PdfTicketService
{
    public function __construct(
        private Environment $twig,
        private LoggerInterface $logger,
        private UrlGeneratorInterface $urlGenerator
    ) {}

    /**
     * Génère le ticket PDF localement via Dompdf.
     * Le QR code encode l'URL du ticket — scanner ouvre le PDF dans le navigateur.
     */
    public function generate(ParticipationDemande $demande): ?string
    {
        $activite = $demande->getActivite();

        // QR code encode l'URL publique du ticket
        $ticketUrl = $this->urlGenerator->generate(
            'app_ticket_view',
            ['id' => $demande->getId()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        $qrCodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=130x130&data=' . urlencode($ticketUrl);

        try {
            $html = $this->twig->render('emails/activite/ticket_pdf.html.twig', [
                'demande'   => $demande,
                'activite'  => $activite,
                'qrCodeUrl' => $qrCodeUrl,
            ]);
        } catch (\Throwable $e) {
            $this->logger->error('[PdfTicket] Erreur rendu template: ' . $e->getMessage());
            return null;
        }

        try {
            $options = new Options();
            $options->set('isRemoteEnabled', true);
            $options->set('isHtml5ParserEnabled', true);
            $options->set('defaultFont', 'DejaVu Sans');

            $dompdf = new Dompdf($options);
            $dompdf->loadHtml($html, 'UTF-8');
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            $this->logger->info('[PdfTicket] PDF généré avec succès.', ['demande_id' => $demande->getId()]);
            return $dompdf->output();

        } catch (\Throwable $e) {
            $this->logger->warning('[PdfTicket] Échec génération PDF: ' . $e->getMessage());
            return null;
        }
    }
}
