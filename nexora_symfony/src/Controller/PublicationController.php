<?php

namespace App\Controller;

use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/publications')]
final class PublicationController extends AbstractController
{
    #[Route('/api', name: 'app_publications_api', methods: ['GET'])]
    public function api(Connection $conn): JsonResponse
    {
        $publications = $conn->fetchAllAssociative(
            'SELECT * FROM publication ORDER BY created_at DESC LIMIT 20'
        );
        foreach ($publications as &$pub) {
            $pub['reactions']    = $conn->fetchAllAssociative(
                'SELECT type_reaction, COUNT(*) as total FROM publication_reaction WHERE publication_id = ? GROUP BY type_reaction',
                [$pub['id']]
            );
            $pub['commentaires'] = $conn->fetchAllAssociative(
                'SELECT * FROM publication_commentaire WHERE publication_id = ? ORDER BY created_at ASC',
                [$pub['id']]
            );
            $pub['total_reactions'] = array_sum(array_column($pub['reactions'], 'total'));
        }
        return $this->json($publications);
    }

    #[Route('', name: 'app_publications', methods: ['GET'])]
    public function index(Connection $conn, Request $request): Response
    {
        $publications = $conn->fetchAllAssociative(
            'SELECT * FROM publication ORDER BY created_at DESC'
        );

        foreach ($publications as &$pub) {
            $pub['reactions']    = $conn->fetchAllAssociative(
                'SELECT type_reaction, COUNT(*) as total FROM publication_reaction WHERE publication_id = ? GROUP BY type_reaction',
                [$pub['id']]
            );
            $pub['commentaires'] = $conn->fetchAllAssociative(
                'SELECT * FROM publication_commentaire WHERE publication_id = ? ORDER BY created_at ASC',
                [$pub['id']]
            );
            $pub['total_reactions'] = array_sum(array_column($pub['reactions'], 'total'));
        }

        return $this->render('publication/index.html.twig', [
            'publications' => $publications,
        ]);
    }

    #[Route('/new', name: 'app_publication_new', methods: ['POST'])]
    public function new(
        Request $request,
        Connection $conn,
        \Symfony\Component\Validator\Validator\ValidatorInterface $validator,
        \App\Service\ModerationService $moderation,
        \Symfony\Component\Mailer\MailerInterface $mailer
    ): Response {
        $user    = $this->getUser();
        $auteur  = $user ? $user->getPrenom() . ' ' . $user->getNom() : trim($request->request->get('auteur', ''));
        $userId  = $user ? $user->getId() : null;
        $contenu = trim($request->request->get('contenu', ''));
        $image   = null;

        // ── VALIDATION CÔTÉ SERVEUR ──
        $publication = new \App\Entity\Publication();
        $publication->setAuteur($auteur);
        $publication->setContenu($contenu);
        $publication->setCreatedAt(new \DateTime());

        $violations = $validator->validate($publication);
        if (count($violations) > 0) {
            $request->getSession()->set('pub_old', ['contenu' => $contenu]);
            foreach ($violations as $v) {
                $field = $v->getPropertyPath();
                $flashKey = $field === 'contenu' ? 'error_contenu' : 'error_' . $field;
                $this->addFlash($flashKey, $v->getMessage());
            }
            return $this->redirectToRoute('app_publications');
        }
        $request->getSession()->remove('pub_old');

        // ── MODÉRATION (mots graves) ──
        if ($user instanceof \App\Entity\Users) {
            $userEmail = $user->getEmail();
            $userNom   = $user->getFullName();

            // Vérifier si déjà bloqué
            $blocked = $conn->fetchOne(
                'SELECT is_blocked FROM publication_warning WHERE user_id = ? AND is_blocked = 1 LIMIT 1',
                [$userId]
            );
            if ($blocked) {
                $this->addFlash('danger', '🚫 Votre compte est bloqué. Vous ne pouvez plus publier. Contactez l\'administrateur.');
                return $this->redirectToRoute('app_publications');
            }

            // Analyser le contenu
            $result = $moderation->analyze($contenu);
            if ($result['toxic']) {
                $warningCount = (int) $conn->fetchOne(
                    'SELECT COALESCE(MAX(warning_count), 0) FROM publication_warning WHERE user_id = ?',
                    [$userId]
                );
                $newCount = $warningCount + 1;

                if ($newCount >= 3) {
                    // BLOQUER
                    $conn->insert('publication_warning', [
                        'user_id'        => $userId,
                        'user_email'     => $userEmail,
                        'user_nom'       => $userNom,
                        'contenu_bloque' => '[PUBLICATION] ' . $contenu,
                        'warning_count'  => $newCount,
                        'is_blocked'     => 1,
                        'created_at'     => (new \DateTime())->format('Y-m-d H:i:s'),
                    ]);
                    $this->sendWarningEmail($mailer, $userEmail, $userNom, $contenu, $newCount, true);
                    $this->addFlash('danger', '🚫 Votre compte a été bloqué suite à des publications répétées avec du contenu inapproprié.');
                } else {
                    // AVERTISSEMENT
                    $conn->insert('publication_warning', [
                        'user_id'        => $userId,
                        'user_email'     => $userEmail,
                        'user_nom'       => $userNom,
                        'contenu_bloque' => '[PUBLICATION] ' . $contenu,
                        'warning_count'  => $newCount,
                        'is_blocked'     => 0,
                        'created_at'     => (new \DateTime())->format('Y-m-d H:i:s'),
                    ]);
                    $this->sendWarningEmail($mailer, $userEmail, $userNom, $contenu, $newCount, false);
                    $remaining = 3 - $newCount;
                    $this->addFlash('warning',
                        "⚠️ Votre publication contient du contenu inapproprié et n'a pas été publiée. " .
                        "Avertissement {$newCount}/2. " .
                        "Il vous reste {$remaining} chance(s) avant blocage."
                    );
                }
                return $this->redirectToRoute('app_publications');
            }
        }

        // Gestion upload image
        $file = $request->files->get('image');
        if ($file) {
            $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (!in_array($file->getMimeType(), $allowed)) {
                $this->addFlash('error', 'Format image non supporté (jpg, png, gif, webp).');
                return $this->redirectToRoute('app_home', ['_fragment' => 'publications']);
            }
            if ($file->getSize() > 5 * 1024 * 1024) {
                $this->addFlash('error', 'Image trop lourde (max 5 Mo).');
                return $this->redirectToRoute('app_home', ['_fragment' => 'publications']);
            }
            $filename = uniqid('pub_') . '.' . $file->guessExtension();
            $file->move($this->getParameter('kernel.project_dir') . '/public/uploads/publications', $filename);
            $image = $filename;
        }

        $conn->insert('publication', [
            'auteur'     => htmlspecialchars($auteur),
            'contenu'    => htmlspecialchars($contenu),
            'image'      => $image,
            'user_id'    => $userId,
            'created_at' => (new \DateTime())->format('Y-m-d H:i:s'),
        ]);

        return $this->redirectToRoute('app_home', ['_fragment' => 'publications']);
    }

    #[Route('/{id}/commenter', name: 'app_publication_commenter', methods: ['POST'])]
    public function commenter(
        int $id,
        Request $request,
        Connection $conn,
        \App\Service\NotificationService $notificationService
    ): JsonResponse {
        $auteur  = trim($request->request->get('auteur', ''));
        $contenu = trim($request->request->get('contenu', ''));

        if (strlen($auteur) < 2 || strlen($contenu) < 2) {
            return $this->json(['error' => 'Données invalides.'], 400);
        }

        // Récupérer l'auteur de la publication
        $publication = $conn->fetchAssociative('SELECT * FROM publication WHERE id = ?', [$id]);
        if (!$publication) {
            return $this->json(['error' => 'Publication introuvable.'], 404);
        }

        $conn->insert('publication_commentaire', [
            'publication_id' => $id,
            'auteur'         => htmlspecialchars($auteur),
            'contenu'        => htmlspecialchars($contenu),
            'created_at'     => (new \DateTime())->format('Y-m-d H:i:s'),
        ]);

        $commentId = $conn->lastInsertId();

        // ── CRÉER NOTIFICATION ──
        if ($publication['user_id'] && $publication['user_id'] > 0) {
            $notificationService->create(
                $publication['user_id'],
                'comment',
                "{$auteur} a commenté votre publication",
                $id,
                'publication'
            );
        }

        return $this->json([
            'id'         => $commentId,
            'auteur'     => htmlspecialchars($auteur),
            'contenu'    => htmlspecialchars($contenu),
            'created_at' => (new \DateTime())->format('d/m/Y H:i'),
        ]);
    }

    #[Route('/{id}/reagir', name: 'app_publication_reagir', methods: ['POST'])]
    public function reagir(
        int $id,
        Request $request,
        Connection $conn,
        \App\Service\NotificationService $notificationService
    ): JsonResponse {
        $auteur  = trim($request->request->get('auteur', 'Anonyme'));
        $type    = $request->request->get('type', 'jaime');
        $types   = ['jaime', 'jadore', 'haha', 'wow', 'triste', 'grrr'];

        if (!in_array($type, $types)) {
            return $this->json(['error' => 'Réaction invalide.'], 400);
        }

        // Récupérer l'auteur de la publication
        $publication = $conn->fetchAssociative('SELECT * FROM publication WHERE id = ?', [$id]);
        if (!$publication) {
            return $this->json(['error' => 'Publication introuvable.'], 404);
        }

        // Vérifie si une réaction existe déjà pour cet auteur
        $existing = $conn->fetchOne(
            'SELECT type_reaction FROM publication_reaction WHERE publication_id = ? AND auteur = ?',
            [$id, $auteur]
        );

        if ($existing === $type) {
            // Même réaction → on la retire (toggle)
            $conn->delete('publication_reaction', ['publication_id' => $id, 'auteur' => $auteur]);
        } elseif ($existing) {
            // Réaction différente → on la change
            $conn->update('publication_reaction', ['type_reaction' => $type], ['publication_id' => $id, 'auteur' => $auteur]);
        } else {
            // Nouvelle réaction
            $conn->insert('publication_reaction', [
                'publication_id' => $id,
                'auteur'         => $auteur,
                'type_reaction'  => $type,
                'created_at'     => (new \DateTime())->format('Y-m-d H:i:s'),
            ]);
            
            // ── CRÉER NOTIFICATION ──
            if ($publication['user_id'] && $publication['user_id'] > 0) {
                $emojis = ['jaime'=>'👍','jadore'=>'❤️','haha'=>'😂','wow'=>'😮','triste'=>'😢','grrr'=>'😡'];
                $emoji = $emojis[$type] ?? '👍';
                $notificationService->create(
                    $publication['user_id'],
                    'reaction',
                    "{$auteur} a réagi {$emoji} à votre publication",
                    $id,
                    'publication'
                );
            }
        }

        // Retourne les totaux mis à jour
        $reactions = $conn->fetchAllAssociative(
            'SELECT type_reaction, COUNT(*) as total FROM publication_reaction WHERE publication_id = ? GROUP BY type_reaction',
            [$id]
        );

        return $this->json(['reactions' => $reactions]);
    }

    private function sendWarningEmail(
        \Symfony\Component\Mailer\MailerInterface $mailer,
        string $to,
        string $nom,
        string $contenu,
        int $count,
        bool $blocked
    ): void {
        $subject = $blocked
            ? '🚫 Votre compte Nexora a été bloqué'
            : "⚠️ Avertissement {$count}/2 — Publication refusée";

        $color   = $blocked ? '#c0392b' : '#e67e22';
        $icon    = $blocked ? '🚫' : '⚠️';
        $message = $blocked
            ? 'Votre compte a été <strong>bloqué définitivement</strong> suite à des publications répétées avec du contenu inapproprié.'
            : "Votre publication a été <strong>refusée</strong> car elle contient du contenu inapproprié.<br>Avertissement <strong>{$count}/2</strong>. " .
              ($count < 2 ? 'Il vous reste <strong>' . (2 - $count) . ' chance(s)</strong> avant blocage.' : '<strong>Prochain manquement = blocage définitif.</strong>');

        $html = "
        <div style='font-family:Arial,sans-serif;max-width:560px;margin:auto;padding:24px;border:1px solid #ede5ff;border-radius:12px'>
            <h2 style='color:{$color}'>{$icon} " . ($blocked ? 'Compte bloqué' : "Avertissement {$count}/2") . "</h2>
            <p>Bonjour <strong>" . htmlspecialchars($nom) . "</strong>,</p>
            <p>{$message}</p>
            <div style='background:#fff3cd;border-left:4px solid {$color};padding:14px;border-radius:8px;margin:16px 0'>
                <strong>Contenu refusé :</strong><br>
                <em style='color:#555'>" . htmlspecialchars(mb_substr($contenu, 0, 200)) . "</em>
            </div>
            <p style='color:#888;font-size:0.85rem'>Nexora — Modération automatique</p>
        </div>";

        try {
            $mailer->send(
                (new \Symfony\Component\Mime\Email())
                    ->from($_ENV['MAILER_FROM'] ?? 'noreply@nexora.tn')
                    ->to($to)
                    ->subject($subject)
                    ->html($html)
            );
        } catch (\Throwable) {}
    }
}
