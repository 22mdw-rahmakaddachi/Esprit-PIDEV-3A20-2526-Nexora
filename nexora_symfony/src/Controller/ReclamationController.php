<?php

namespace App\Controller;

use App\Entity\Reclamation;
use App\Entity\Users;
use App\Repository\ActiviteRepository;
use App\Repository\ReclamationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/reclamations')]
final class ReclamationController extends AbstractController
{
    private function getCurrentUser(): ?Users
    {
        $user = $this->getUser();
        return $user instanceof Users ? $user : null;
    }

    #[Route('', name: 'app_mes_reclamations')]
    public function index(ReclamationRepository $repo): Response
    {
        $user = $this->getCurrentUser();
        return $this->render('reclamation/index.html.twig', [
            'reclamations' => $user ? $repo->findByClient($user->getId()) : [],
        ]);
    }

    #[Route('/new', name: 'app_reclamation_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $em,
        ActiviteRepository $activiteRepo,
        MailerInterface $mailer,
        ReclamationRepository $reclamationRepo
    ): Response {
        $user = $this->getCurrentUser();
        // Pré-sélection depuis GET ou POST
        $preselectedActiviteId = $request->query->get('activite_id') ?? $request->request->get('activite_id');

        if ($request->isMethod('POST')) {
            $description = trim($request->request->get('description', ''));
            $activiteId  = $request->request->get('activite_id');

            if (empty($description)) {
                $this->addFlash('error', 'La description est obligatoire.');
                return $this->redirectToRoute('app_reclamation_new');
            }

            $reclamation = new Reclamation();
            $reclamation->setDescription($description);
            $reclamation->setStatut('EN_ATTENTE');
            $reclamation->setDateCreation(new \DateTime());

            if ($user) {
                $reclamation->setClient($user);
            }

            $activite = null;
            if ($activiteId) {
                $activite = $activiteRepo->find((int)$activiteId);
                if ($activite) {
                    $reclamation->setActivite($activite);
                }
            }

            $em->persist($reclamation);
            $em->flush();

            // ── Envoyer email au partenaire responsable de l'activité ──
            // Charger l'email du partenaire directement via SQL (évite les problèmes de lazy loading)
            $partenaireEmail   = null;
            $partenairePrenom  = 'Partenaire';
            $activiteNom       = $activite?->getNom() ?? 'une activité';

            if ($activite) {
                $conn = $em->getConnection();
                $row  = $conn->fetchAssociative(
                    'SELECT u.email, u.prenom, p.id as partenaire_id, p.nom_entreprise FROM activite a
                     JOIN partenaire p ON p.id = a.partenaire_id
                     JOIN users u ON u.id = p.user_id
                     WHERE a.id = ?',
                    [$activite->getId()]
                );
                if ($row) {
                    $partenaireEmail  = $row['email'];
                    $partenairePrenom = $row['prenom'];
                    $partenaireId     = (int)$row['partenaire_id'];
                }
            }

            $clientNom   = $user ? ($user->getPrenom() . ' ' . $user->getNom()) : 'Un client';
            $clientEmail = $user?->getEmail() ?? '';

            if ($partenaireEmail) {
                $email = (new Email())
                    ->from('anoir5502@gmail.com')
                    ->to($partenaireEmail)
                    ->subject('⚠️ Nouvelle réclamation reçue — ' . $activiteNom)
                    ->html(
                        '<div style="font-family:Arial,sans-serif;max-width:600px;margin:auto;padding:24px;border:1px solid #ede5ff;border-radius:12px">'
                        . '<h2 style="color:#e67e22">⚠️ Nouvelle réclamation</h2>'
                        . '<p>Bonjour <strong>' . htmlspecialchars($partenairePrenom) . '</strong>,</p>'
                        . '<p>Vous avez reçu une nouvelle réclamation concernant votre activité <strong>' . htmlspecialchars($activiteNom) . '</strong>.</p>'
                        . '<table style="width:100%;border-collapse:collapse;margin:16px 0">'
                        . '<tr><td style="padding:8px;background:#f9f7ff;font-weight:bold;width:140px">Client</td>'
                        . '<td style="padding:8px;border-bottom:1px solid #ede5ff">' . htmlspecialchars($clientNom) . ' (' . htmlspecialchars($clientEmail) . ')</td></tr>'
                        . '<tr><td style="padding:8px;background:#f9f7ff;font-weight:bold">Activité</td>'
                        . '<td style="padding:8px;border-bottom:1px solid #ede5ff">' . htmlspecialchars($activiteNom) . '</td></tr>'
                        . '<tr><td style="padding:8px;background:#f9f7ff;font-weight:bold">Date</td>'
                        . '<td style="padding:8px;border-bottom:1px solid #ede5ff">' . (new \DateTime())->format('d/m/Y H:i') . '</td></tr>'
                        . '</table>'
                        . '<div style="background:#fff3cd;border-left:4px solid #e67e22;padding:16px;border-radius:8px;margin:16px 0">'
                        . '<strong>Description :</strong><br>' . nl2br(htmlspecialchars($description))
                        . '</div>'
                        . '<p>Connectez-vous à votre espace partenaire pour traiter cette réclamation.</p>'
                        . '<p style="color:#888;font-size:0.85rem">L\'équipe Nexora</p>'
                        . '</div>'
                    );

                try {
                    $mailer->send($email);
                    $this->addFlash('info', '📧 Email envoyé à ' . $partenaireEmail);
                } catch (\Exception $e) {
                    $this->addFlash('warning', '⚠️ Email partenaire non envoyé : ' . $e->getMessage());
                }

                // ── Vérifier si le partenaire dépasse le seuil de 3 réclamations ──
                if (isset($partenaireId)) {
                    $nbReclamations = $reclamationRepo->countByPartenaire($partenaireId);
                    if ($nbReclamations > 3) {
                        // Trouver l'email de l'admin
                        $adminRow = $em->getConnection()->fetchAssociative(
                            "SELECT email FROM users WHERE role = 'ROLE_ADMIN' LIMIT 1"
                        );
                        if ($adminRow) {
                            $nomPartenaire = $row['nom_entreprise'] ?: ($row['prenom'] . ' ' . $row['nom'] ?? 'Partenaire');
                            $emailAdmin = (new Email())
                                ->from('anoir5502@gmail.com')
                                ->to($adminRow['email'])
                                ->subject('🚨 Alerte — Partenaire en zone rouge : ' . $nomPartenaire)
                                ->html(
                                    '<div style="font-family:Arial,sans-serif;max-width:600px;margin:auto;padding:24px;border:2px solid #e74c3c;border-radius:12px">'
                                    . '<h2 style="color:#c0392b">🚨 Partenaire en zone rouge</h2>'
                                    . '<p>Bonjour Administrateur,</p>'
                                    . '<p>Le partenaire <strong>' . htmlspecialchars($nomPartenaire) . '</strong> a dépassé le seuil de réclamations.</p>'
                                    . '<div style="background:#fde8e8;border-left:4px solid #e74c3c;padding:16px;border-radius:8px;margin:16px 0">'
                                    . '<strong>Nombre de réclamations :</strong> ' . $nbReclamations . '<br>'
                                    . '<strong>Email partenaire :</strong> ' . htmlspecialchars($partenaireEmail) . '<br>'
                                    . '<strong>Activité concernée :</strong> ' . htmlspecialchars($activiteNom)
                                    . '</div>'
                                    . '<p>Veuillez vérifier ce partenaire et prendre les mesures nécessaires.</p>'
                                    . '<p style="color:#888;font-size:0.85rem">Système Nexora — Alerte automatique</p>'
                                    . '</div>'
                                );
                            try {
                                $mailer->send($emailAdmin);
                            } catch (\Exception $e) {
                                // silencieux
                            }
                        }
                    }
                }
            }

            $this->addFlash('success', '✅ Votre réclamation a été soumise. Le partenaire a été notifié.');
            return $this->redirectToRoute('app_mes_reclamations');
        }

        return $this->render('reclamation/new.html.twig', [
            'activites'            => $user ? $activiteRepo->findAll() : [],
            'preselectedActiviteId' => $preselectedActiviteId,
        ]);
    }
}
