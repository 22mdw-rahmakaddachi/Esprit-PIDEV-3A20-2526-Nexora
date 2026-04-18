<?php

namespace App\Controller;

use App\Repository\ParticipationDemandeRepository;
use App\Service\PdfTicketService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class TicketController extends AbstractController
{
    #[Route('/ticket/{id}', name: 'app_ticket_view', methods: ['GET'])]
    public function view(int $id, ParticipationDemandeRepository $repo, PdfTicketService $pdfService): Response
    {
        $demande = $repo->find($id);
        
        if (!$demande) {
            throw $this->createNotFoundException('Ticket introuvable.');
        }

        // Générer le PDF
        $pdfContent = $pdfService->generate($demande);
        
        if ($pdfContent === null) {
            throw $this->createNotFoundException('Impossible de générer le ticket PDF.');
        }

        // Retourner le PDF directement dans le navigateur
        return new Response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="ticket-nexora-' . $id . '.pdf"',
        ]);
    }
}
