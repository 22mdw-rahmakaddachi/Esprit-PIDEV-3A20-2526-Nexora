<?php

namespace App\Controller;

use App\Entity\Avis;
use App\Entity\Users;
use App\Repository\AvisRepository;
use App\Service\ModerationService;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;

final class AvisController extends AbstractController
{
    #[Route('/avis', name: 'app_avis')]
    public function index(AvisRepository $avisRepo, Connection $conn): Response
    {
        $avisList = $avisRepo->findLatest(50);
        $moyenne  = $avisRepo->avgNoteByActivite(0);

        return $this->render('avis/index.html.twig', [
            'activites'    => [],
            'avisList'     => $avisList,
            'moyenne'      => $moyenne,
            'currentUser'  => $this->getUser(),
            'allActivites' => $conn->fetchAllAssociative('SELECT id, nom FROM activite ORDER BY nom'),
        ]);
    }

    #[Route('/avis/new', name: 'app_avis_new_public', methods: ['POST'])]
    public function newPublic(
        Request $request,
        EntityManagerInterface $em,
        Connection $conn,
        ModerationService $moderation,
        MailerInterface $mailer
    ): Response {
        /** @var Users|null $user */
        $user    = $this->getUser();
        $rating  = (int) $request->request->get('note', $request->request->get('rating', 5));
        $titre   = trim($request->request->get('titre', 'Avis'));
        $contenu = trim($request->request->get('commentaire', $request->request->get('contenu', '')));

        if (strlen($contenu) < 5 || $rating < 1 || $rating > 5) {
            $this->addFlash('error', 'Données invalides. Vérifiez tous les champs.');
            return $this->redirectToRoute('app_avis');
        }

        // ── MODÉRATION ──
        if ($user instanceof Users) {
            $userId    = $user->getId();
            $userEmail = $user->getEmail();
            $userNom   = $user->getFullName();

            // Vérifier si bloqué
            $blocked = $conn->fetchOne(
                'SELECT is_blocked FROM publication_warning WHERE user_id = ? ORDER BY id DESC LIMIT 1',
                [$userId]
            );
            if ($blocked) {
                $this->addFlash('danger', '🚫 Votre compte est bloqué. Vous ne pouvez plus publier d\'avis. Contactez l\'administrateur.');
                return $this->redirectToRoute('app_avis');
            }

            // Analyser le contenu (titre + commentaire)
            $textToCheck = $titre . ' ' . $contenu;
            $result = $moderation->analyze($textToCheck);

            if ($result['toxic']) {
                $warningCount = (int) $conn->fetchOne(
                    'SELECT COALESCE(MAX(warning_count), 0) FROM publication_warning WHERE user_id = ?',
                    [$userId]
                );
                $newCount = $warningCount + 1;

                if ($newCount >= 3) {
                    // Bloquer
                    $conn->insert('publication_warning', [
                        'user_id'        => $userId,
                        'user_email'     => $userEmail,
                        'user_nom'       => $userNom,
                        'contenu_bloque' => '[AVIS] ' . $contenu,
                        'warning_count'  => $newCount,
                        'is_blocked'     => 1,
                        'created_at'     => (new \DateTime())->format('Y-m-d H:i:s'),
                    ]);
                    $this->sendWarningEmail($mailer, $userEmail, $userNom, $contenu, $newCount, true);
                    $this->notifyAdmin($mailer, $conn, $userNom, $userEmail, $contenu, $newCount);
                    $this->addFlash('danger', '🚫 Votre compte a été bloqué suite à des avis répétés avec du contenu inapproprié. Un email vous a été envoyé.');
                    return $this->redirectToRoute('app_avis');
                } else {
                    // Avertissement
                    $conn->insert('publication_warning', [
                        'user_id'        => $userId,
                        'user_email'     => $userEmail,
                        'user_nom'       => $userNom,
                        'contenu_bloque' => '[AVIS] ' . $contenu,
                        'warning_count'  => $newCount,
                        'is_blocked'     => 0,
                        'created_at'     => (new \DateTime())->format('Y-m-d H:i:s'),
                    ]);
                    $this->sendWarningEmail($mailer, $userEmail, $userNom, $contenu, $newCount, false);
                    $remaining = 3 - $newCount;
                    $this->addFlash('warning',
                        "⚠️ Votre avis contient du contenu inapproprié et n'a pas été publié. " .
                        "Avertissement {$newCount}/2. " .
                        ($remaining > 0 ? "Il vous reste {$remaining} chance(s) avant blocage." : "")
                    );
                    return $this->redirectToRoute('app_avis');
                }
            }
        }

        // ── ENREGISTREMENT ──
        $userId = $user instanceof Users ? $user->getId() : 0;

        $avis = new Avis();
        $avis->setUserId($userId);
        $avis->setRating($rating);
        $avis->setTitre($titre ?: 'Avis');
        $avis->setContenu($contenu);
        $avis->setCreatedAt(new \DateTime());

        $em->persist($avis);
        $em->flush();

        $this->addFlash('success', '✅ Votre avis a été publié avec succès.');
        return $this->redirectToRoute('app_avis');
    }

    // ── HELPERS ──────────────────────────────────────────────────────────────

    private function sendWarningEmail(
        MailerInterface $mailer,
        string $to,
        string $nom,
        string $contenu,
        int $count,
        bool $blocked
    ): void {
        $subject = $blocked
            ? '🚫 Votre compte Nexora a été bloqué'
            : "⚠️ Avertissement {$count}/2 — Avis refusé";

        $color   = $blocked ? '#c0392b' : '#e67e22';
        $icon    = $blocked ? '🚫' : '⚠️';
        $message = $blocked
            ? 'Votre compte a été <strong>bloqué définitivement</strong> suite à des avis répétés avec du contenu inapproprié. Contactez l\'administrateur pour contester.'
            : "Votre avis a été <strong>refusé</strong> car il contient du contenu inapproprié.<br>Avertissement <strong>{$count}/2</strong>. " .
              ($count < 2 ? 'Il vous reste <strong>' . (2 - $count) . ' chance(s)</strong> avant blocage.' : '<strong>Prochain manquement = blocage définitif.</strong>');

        $html = "
        <div style='font-family:Arial,sans-serif;max-width:560px;margin:auto;padding:24px;border:1px solid #ede5ff;border-radius:12px'>
            <h2 style='color:{$color}'>{$icon} " . ($blocked ? 'Compte bloqué' : "Avertissement {$count}/2") . "</h2>
            <p>Bonjour <strong>" . htmlspecialchars($nom) . "</strong>,</p>
            <p>{$message}</p>
            <div style='background:#fff3cd;border-left:4px solid {$color};padding:14px;border-radius:8px;margin:16px 0'>
                <strong>Contenu refusé :</strong><br>
                <em style='color:#555'>" . htmlspecialchars(mb_substr($contenu, 0, 200)) . (mb_strlen($contenu) > 200 ? '…' : '') . "</em>
            </div>
            <p style='color:#888;font-size:0.85rem'>Nexora — Modération automatique</p>
        </div>";

        try {
            $mailer->send(
                (new Email())
                    ->from($_ENV['MAILER_FROM'] ?? 'noreply@nexora.tn')
                    ->to($to)
                    ->subject($subject)
                    ->html($html)
            );
        } catch (\Throwable) {}
    }

    private function notifyAdmin(
        MailerInterface $mailer,
        Connection $conn,
        string $userNom,
        string $userEmail,
        string $contenu,
        int $count
    ): void {
        $adminRow = $conn->fetchAssociative("SELECT email FROM users WHERE role = 'ROLE_ADMIN' LIMIT 1");
        if (!$adminRow) return;

        $html = "
        <div style='font-family:Arial,sans-serif;max-width:560px;margin:auto;padding:24px;border:2px solid #e74c3c;border-radius:12px'>
            <h2 style='color:#c0392b'>🚨 Utilisateur bloqué — Avis inapproprié</h2>
            <p>L'utilisateur <strong>" . htmlspecialchars($userNom) . "</strong> (" . htmlspecialchars($userEmail) . ") a été bloqué après <strong>{$count} avis inappropriés</strong>.</p>
            <div style='background:#fde8e8;border-left:4px solid #e74c3c;padding:14px;border-radius:8px;margin:16px 0'>
                <strong>Dernier contenu bloqué :</strong><br>
                <em>" . htmlspecialchars(mb_substr($contenu, 0, 300)) . "</em>
            </div>
            <p>Connectez-vous au dashboard admin → Modération pour gérer cet utilisateur.</p>
            <p style='color:#888;font-size:0.85rem'>Nexora — Alerte automatique</p>
        </div>";

        try {
            $mailer->send(
                (new Email())
                    ->from($_ENV['MAILER_FROM'] ?? 'noreply@nexora.tn')
                    ->to($adminRow['email'])
                    ->subject('🚨 Utilisateur bloqué (avis) : ' . $userNom)
                    ->html($html)
            );
        } catch (\Throwable) {}
    }
}
