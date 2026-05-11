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
        private LoggerInterface $logger,
        private UrlGeneratorInterface $urlGenerator,
        private Environment $twig
    ) {}

    public function generate(ParticipationDemande $demande): ?string
    {
        try {
            $activite = $demande->getActivite();

            // URL du ticket pour le QR code
            $ticketUrl = $this->urlGenerator->generate(
                'app_ticket_view',
                ['id' => $demande->getId()],
                UrlGeneratorInterface::ABSOLUTE_URL
            );

            // QR Code via API externe (facile à intégrer dans le HTML)
            $qrCodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=' . urlencode($ticketUrl);

            // Rendre le template HTML
            $html = $this->twig->render('pdf/ticket.html.twig', [
                'demande'   => $demande,
                'qrCodeUrl' => $qrCodeUrl,
            ]);

            // Configurer Dompdf
            $options = new Options();
            $options->set('defaultFont', 'Arial');
            $options->set('isRemoteEnabled', true); // Important pour le QR code externe

            $dompdf = new Dompdf($options);
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            $this->logger->info('[PdfTicket] PDF généré avec Dompdf.', ['demande_id' => $demande->getId()]);
            
            return $dompdf->output();

        } catch (\Throwable $e) {
            $this->logger->error('[PdfTicket] Erreur lors de la génération PDF: ' . $e->getMessage());
            return null;
        }
    }
}
