<?php

namespace App\Controller\Admin;

use App\Repository\PartenaireRepository;
use App\Repository\ReclamationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/reclamations')]
final class AdminReclamationController extends AbstractController
{
    #[Route('', name: 'admin_reclamations')]
    public function index(ReclamationRepository $repo, PartenaireRepository $partenaireRepo): Response
    {
        $user = $this->getUser();
        $partenaire = $user ? $partenaireRepo->findOneBy(['user' => $user]) : null;

        // Admin → tableau des partenaires
        if ($this->isGranted('ROLE_ADMIN')) {
            return $this->render('admin/reclamations/index.html.twig', [
                'reclamations'    => [],
                'nomPartenaire'   => null,
                'tousPartenaires' => $repo->findTousPartenairesAvecReclamations(),
            ]);
        }

        // Partenaire → ses propres réclamations directement
        $reclamations = $partenaire ? $repo->findByPartenaire($partenaire->getId()) : [];
        $nomPartenaire = $partenaire
            ? ($partenaire->getNomEntreprise() ?: ($user->getPrenom() . ' ' . $user->getNom()))
            : 'Partenaire';

        return $this->render('admin/reclamations/index.html.twig', [
            'reclamations'    => $reclamations,
            'nomPartenaire'   => $nomPartenaire,
            'tousPartenaires' => [],
        ]);
    }

    #[Route('/partenaire/{partenaireId}', name: 'admin_reclamations_partenaire')]
    public function byPartenaire(int $partenaireId, ReclamationRepository $repo, PartenaireRepository $partenaireRepo, \Doctrine\ORM\EntityManagerInterface $em): Response
    {
        $reclamations = $repo->findByPartenaire($partenaireId);

        $row = $em->getConnection()->fetchAssociative(
            'SELECT p.nom_entreprise, u.prenom, u.nom FROM partenaire p JOIN users u ON u.id = p.user_id WHERE p.id = ?',
            [$partenaireId]
        );
        $nomPartenaire = $row ? ($row['nom_entreprise'] ?: $row['prenom'] . ' ' . $row['nom']) : 'Partenaire';

        return $this->render('admin/reclamations/index.html.twig', [
            'reclamations'    => $reclamations,
            'nomPartenaire'   => $nomPartenaire,
            'tousPartenaires' => [],
        ]);
    }

    #[Route('/{id}/supprimer', name: 'admin_reclamation_supprimer', methods: ['POST'])]
    public function supprimer(int $id, ReclamationRepository $repo, \Doctrine\ORM\EntityManagerInterface $em): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('Accès refusé.');
        }
        $reclamation = $repo->find($id);
        if ($reclamation) {
            $em->remove($reclamation);
            $em->flush();
            $this->addFlash('success', '🗑️ Réclamation supprimée.');
        }
        return $this->redirectToRoute('admin_reclamations');
    }
    #[Route('/{id}/traiter', name: 'admin_reclamation_traiter', methods: ['POST'])]
    public function traiter(
        int $id,
        Request $request,
        ReclamationRepository $repo,
        EntityManagerInterface $em,
        MailerInterface $mailer
    ): Response {
        $reclamation = $repo->find($id);
        if (!$reclamation) throw $this->createNotFoundException();

        $reclamation->setStatut('TRAITEE');
        $em->flush();

        // Charger l'email du client directement via SQL
        $conn = $em->getConnection();
        $row  = $conn->fetchAssociative(
            'SELECT u.email, u.prenom FROM reclamation r
             JOIN users u ON u.id = r.client_id
             WHERE r.id = ?',
            [$id]
        );

        $clientEmail   = $row['email']   ?? null;
        $clientPrenom  = $row['prenom']  ?? 'Client';
        $activiteNom   = $reclamation->getActivite()?->getNom() ?? 'votre activité';
        $reponse       = trim($request->request->get('reponse', ''));

        // Nom du partenaire via SQL aussi
        $rowP = $conn->fetchAssociative(
            'SELECT u.prenom, u.nom, p.nom_entreprise FROM reclamation r
             JOIN activite a ON a.id = r.activite_id
             JOIN partenaire p ON p.id = a.partenaire_id
             JOIN users u ON u.id = p.user_id
             WHERE r.id = ?',
            [$id]
        );
        $partenaireNom = $rowP ? ($rowP['nom_entreprise'] ?: $rowP['prenom'] . ' ' . $rowP['nom']) : 'Le partenaire';

        // ── Email au client ──
        if ($clientEmail) {
            $reponseHtml = $reponse
                ? '<div style="background:#d1f2eb;border-left:4px solid #1a7a4a;padding:16px;border-radius:8px;margin:16px 0">'
                  . '<strong>Réponse du partenaire :</strong><br>' . nl2br(htmlspecialchars($reponse))
                  . '</div>'
                : '';

            $email = (new Email())
                ->from('anoir5502@gmail.com')
                ->to($clientEmail)
                ->subject('✅ Votre réclamation a été traitée — ' . $activiteNom)
                ->html(
                    '<div style="font-family:Arial,sans-serif;max-width:600px;margin:auto;padding:24px;border:1px solid #ede5ff;border-radius:12px">'
                    . '<h2 style="color:#1a7a4a">✅ Réclamation traitée</h2>'
                    . '<p>Bonjour <strong>' . htmlspecialchars($clientPrenom) . '</strong>,</p>'
                    . '<p>Votre réclamation concernant l\'activité <strong>' . htmlspecialchars($activiteNom) . '</strong> a été <strong style="color:#1a7a4a">traitée</strong> par <strong>' . htmlspecialchars($partenaireNom) . '</strong>.</p>'
                    . '<div style="background:#f9f7ff;border-left:4px solid #a78bda;padding:16px;border-radius:8px;margin:16px 0">'
                    . '<strong>Votre réclamation :</strong><br>' . nl2br(htmlspecialchars($reclamation->getDescription()))
                    . '</div>'
                    . $reponseHtml
                    . '<p>Merci de votre confiance.</p>'
                    . '<p style="color:#888;font-size:0.85rem">L\'équipe Nexora</p>'
                    . '</div>'
                );

            try {
                $mailer->send($email);
                $this->addFlash('success', '✅ Réclamation traitée — email envoyé à ' . $clientEmail . '.');
            } catch (\Exception $e) {
                $this->addFlash('warning', '⚠️ Réclamation traitée mais email non envoyé : ' . $e->getMessage());
            }
        } else {
            $this->addFlash('success', '✅ Réclamation marquée comme traitée.');
        }

        return $this->redirectToRoute('admin_reclamations');
    }
}
