<?php

namespace App\Controller;

use App\Entity\Avis;
use App\Entity\Users;
use App\Repository\AvisRepository;
use App\Service\ModerationService;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class AvisController extends AbstractController
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private string $geminiApiKey = ''
    ) {}

    // ── RÉPONSE DU PARTENAIRE À UN AVIS ─────────────────────────────────────

    #[Route('/avis/{id}/repondre', name: 'app_avis_repondre', methods: ['POST'])]
    public function repondre(int $id, Request $request, AvisRepository $avisRepo, Connection $conn): JsonResponse
    {
        $avis = $avisRepo->find($id);
        if (!$avis) {
            return $this->json(['error' => 'Avis introuvable'], 404);
        }

        $contenu = trim($request->request->get('contenu', ''));
        if (mb_strlen($contenu) < 5) {
            return $this->json(['error' => 'La réponse est trop courte (min. 5 caractères).'], 400);
        }
        if (mb_strlen($contenu) > 1000) {
            return $this->json(['error' => 'La réponse est trop longue (max. 1000 caractères).'], 400);
        }

        /** @var Users|null $user */
        $user           = $this->getUser();
        $partenaireNom  = $user instanceof Users
            ? $user->getFullName() ?: 'Le Partenaire'
            : 'Le Partenaire';

        // Supprimer l'ancienne réponse si elle existe (1 réponse par avis)
        $conn->delete('avis_reponse', ['avis_id' => $id]);

        // Insérer la nouvelle réponse
        $conn->insert('avis_reponse', [
            'avis_id'        => $id,
            'partenaire_nom' => $partenaireNom,
            'contenu'        => $contenu,
            'created_at'     => (new \DateTime())->format('Y-m-d H:i:s'),
        ]);

        return $this->json([
            'success'        => true,
            'partenaire_nom' => $partenaireNom,
            'contenu'        => $contenu,
            'created_at'     => (new \DateTime())->format('d/m/Y H:i'),
        ], 200, [], ['json_encode_options' => JSON_UNESCAPED_UNICODE]);
    }

    // ── SUPPRESSION RÉPONSE DU PARTENAIRE ────────────────────────────────────

    #[Route('/avis/{id}/reponse/supprimer', name: 'app_avis_reponse_supprimer', methods: ['POST'])]
    public function supprimerReponse(int $id, Connection $conn): JsonResponse
    {
        $conn->delete('avis_reponse', ['avis_id' => $id]);
        return $this->json(['success' => true]);
    }

    // ── SUGGESTION DE RÉPONSE IA POUR LE PARTENAIRE ─────────────────────────

    #[Route('/avis/{id}/suggerer-reponse', name: 'app_avis_suggerer_reponse', methods: ['POST'])]
    public function suggererReponse(int $id, AvisRepository $avisRepo): JsonResponse
    {
        $avis = $avisRepo->find($id);
        if (!$avis) {
            return $this->json(['error' => 'Avis introuvable'], 404);
        }

        $rating  = $avis->getRating();
        $titre   = $avis->getTitre();
        $contenu = $avis->getContenu();

        // ── Essayer Gemini ──
        if ($this->geminiApiKey && $this->geminiApiKey !== 'votre_cle_ici') {
            $tonDescription = match(true) {
                $rating >= 4 => 'positif et enthousiaste',
                $rating <= 2 => 'négatif ou déçu',
                default      => 'mitigé',
            };

            $prompt = "Tu es un responsable d'une agence de tourisme tunisienne. "
                    . "Un client a laissé un avis {$tonDescription} (note : {$rating}/5). "
                    . "Titre de l'avis : \"{$titre}\". "
                    . "Contenu : \"{$contenu}\". "
                    . "Rédige une réponse professionnelle, chaleureuse et adaptée au ton de l'avis, en français. "
                    . "La réponse doit faire 2-3 phrases maximum. "
                    . "Réponds UNIQUEMENT avec un JSON sur une ligne : {\"reponse\":\"...\",\"ton\":\"positif|negatif|mitige\"}";

            try {
                $response = $this->httpClient->request('POST',
                    'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent',
                    [
                        'headers' => ['x-goog-api-key' => $this->geminiApiKey, 'Content-Type' => 'application/json'],
                        'json'    => [
                            'contents'         => [['parts' => [['text' => $prompt]]]],
                            'generationConfig' => ['temperature' => 0.4, 'maxOutputTokens' => 300, 'thinkingConfig' => ['thinkingBudget' => 0]],
                        ],
                        'timeout' => 10,
                    ]
                );

                if ($response->getStatusCode() === 200) {
                    $data   = $response->toArray(false);
                    $raw    = $data['candidates'][0]['content']['parts'][0]['text'] ?? '{}';
                    $clean  = trim(preg_replace('/```json|```/i', '', $raw));
                    $result = json_decode($clean, true);

                    if ($result && isset($result['reponse'])) {
                        return $this->json([
                            'reponse' => $result['reponse'],
                            'ton'     => $result['ton'] ?? 'mitige',
                            'source'  => 'gemini',
                        ], 200, [], ['json_encode_options' => JSON_UNESCAPED_UNICODE]);
                    }
                }
            } catch (\Throwable) {
                // Fallback si Gemini indisponible
            }
        }

        // ── Fallback : réponses prédéfinies selon la note ──
        $reponse = match(true) {
            $rating >= 4 => "Merci beaucoup pour ce retour positif ! Nous sommes ravis que votre expérience ait été à la hauteur de vos attentes. Notre équipe sera heureuse de vous accueillir à nouveau. 🙏",
            $rating <= 2 => "Nous sommes sincèrement désolés pour cette expérience décevante. Votre retour est précieux et nous allons immédiatement travailler à améliorer nos services. N'hésitez pas à nous recontacter directement.",
            default      => "Merci pour votre retour équilibré. Nous prenons note de vos remarques et travaillons continuellement à améliorer votre expérience. À bientôt !",
        };

        return $this->json([
            'reponse' => $reponse,
            'ton'     => $rating >= 4 ? 'positif' : ($rating <= 2 ? 'negatif' : 'mitige'),
            'source'  => 'fallback',
        ], 200, [], ['json_encode_options' => JSON_UNESCAPED_UNICODE]);
    }

    // ── RÉSUMÉ IA DES AVIS ───────────────────────────────────────────────────

    #[Route('/avis/resume', name: 'app_avis_resume', methods: ['GET'])]
    public function resume(AvisRepository $avisRepo): JsonResponse
    {
        $avisList = $avisRepo->findLatest(50);

        if (count($avisList) < 3) {
            return $this->json(['error' => 'Pas assez d\'avis'], 204);
        }

        $total = count($avisList);

        // ── Essayer Gemini ──
        if ($this->geminiApiKey && $this->geminiApiKey !== 'votre_cle_ici') {
            $corpus = '';
            foreach ($avisList as $avis) {
                $corpus .= sprintf("- [%d/5] %s : %s\n",
                    $avis->getRating(),
                    $avis->getTitre(),
                    mb_substr($avis->getContenu(), 0, 200)
                );
            }

            $prompt = "Tu es un assistant d'analyse d'avis clients. Voici $total avis :\n\n"
                    . $corpus . "\n"
                    . "Génère un résumé en JSON sur une seule ligne sans markdown. "
                    . "Clés : positifs (string), ameliorations (string), themes (array de 3 strings ex: \"ambiance (5x)\"), note_moyenne (float). "
                    . "Réponds UNIQUEMENT avec le JSON.";

            try {
                $response = $this->httpClient->request('POST',
                    'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent',
                    [
                        'headers' => ['x-goog-api-key' => $this->geminiApiKey, 'Content-Type' => 'application/json'],
                        'json'    => [
                            'contents'         => [['parts' => [['text' => $prompt]]]],
                            'generationConfig' => ['temperature' => 0.2, 'maxOutputTokens' => 1000, 'thinkingConfig' => ['thinkingBudget' => 0]],
                        ],
                        'timeout' => 15,
                    ]
                );

                if ($response->getStatusCode() === 200) {
                    $data   = $response->toArray(false);
                    $raw    = $data['candidates'][0]['content']['parts'][0]['text'] ?? '{}';
                    $raw    = trim(preg_replace('/```json|```/i', '', $raw));
                    $result = json_decode($raw, true);

                    if ($result && isset($result['positifs'])) {
                        return $this->json([
                            'total'         => $total,
                            'positifs'      => $result['positifs']      ?? '',
                            'ameliorations' => $result['ameliorations'] ?? '',
                            'themes'        => $result['themes']        ?? [],
                            'note_moyenne'  => round((float)($result['note_moyenne'] ?? 0), 1),
                            'source'        => 'gemini',
                        ], 200, [], ['json_encode_options' => JSON_UNESCAPED_UNICODE]);
                    }
                }
            } catch (\Throwable) {
                // Gemini indisponible → fallback local
            }
        }

        // ── Fallback local : calcul sans IA ──
        return $this->json($this->buildLocalResume($avisList, $total));
    }

    /**
     * Génère un résumé des avis sans IA, basé sur les données réelles.
     */
    private function buildLocalResume(array $avisList, int $total): array
    {
        // Calculer la note moyenne réelle
        $somme = array_sum(array_map(fn($a) => $a->getRating(), $avisList));
        $moyenne = round($somme / $total, 1);

        // Compter les avis positifs / négatifs / neutres
        $positifs  = array_filter($avisList, fn($a) => $a->getRating() >= 4);
        $negatifs  = array_filter($avisList, fn($a) => $a->getRating() <= 2);
        $nbPos     = count($positifs);
        $nbNeg     = count($negatifs);

        // Extraire les mots les plus fréquents des contenus
        $allText = implode(' ', array_map(fn($a) => mb_strtolower($a->getContenu() . ' ' . $a->getTitre()), $avisList));
        $stopWords = ['le','la','les','de','du','des','un','une','et','est','en','que','qui','pour','par','sur','avec','dans','ce','se','je','il','elle','nous','vous','ils','très','bien','mais','pas','plus','tout','cette','son','sa','ses','mon','ma','mes','au','aux','ou','si','ne','on','lui','leur','leurs','été','avoir','être','faire','dit','dit','cet','ces','car','donc','or','ni','car'];
        $mots = preg_split('/\s+/', preg_replace('/[^a-zàâäéèêëîïôùûüç\s]/u', ' ', $allText));
        $freq = [];
        foreach ($mots as $mot) {
            $mot = trim($mot);
            if (mb_strlen($mot) > 4 && !in_array($mot, $stopWords)) {
                $freq[$mot] = ($freq[$mot] ?? 0) + 1;
            }
        }
        arsort($freq);
        $topMots = array_slice(array_keys($freq), 0, 3);
        $themes  = array_map(fn($m) => $m . ' (' . $freq[$m] . 'x)', $topMots);

        // Construire les messages
        $positifsMsg = $nbPos > 0
            ? "{$nbPos} client(s) sur {$total} ont donné une note de 4 ou 5 étoiles. Les avis positifs soulignent une expérience satisfaisante."
            : "Peu d'avis très positifs pour le moment.";

        $ameliorationsMsg = $nbNeg > 0
            ? "{$nbNeg} client(s) ont signalé des points à améliorer (note ≤ 2). Consultez les avis détaillés pour plus d'informations."
            : "Aucun point négatif majeur signalé.";

        return [
            'total'         => $total,
            'positifs'      => $positifsMsg,
            'ameliorations' => $ameliorationsMsg,
            'themes'        => $themes ?: ['expérience', 'activité', 'service'],
            'note_moyenne'  => $moyenne,
            'source'        => 'local',
        ];
    }

    // ── ANALYSE DE SENTIMENT EN TEMPS RÉEL ───────────────────────────────────

    #[Route('/avis/sentiment', name: 'app_avis_sentiment', methods: ['POST'])]
    public function sentiment(Request $request): JsonResponse
    {
        $texte = trim($request->request->get('texte', ''));

        if (mb_strlen($texte) < 10) {
            return $this->json(['sentiment' => 'neutre', 'label' => 'Neutre', 'emoji' => '😐', 'score' => 0]);
        }

        if (!$this->geminiApiKey || $this->geminiApiKey === 'votre_cle_ici') {
            return $this->json(['sentiment' => 'neutre', 'label' => 'Neutre', 'emoji' => '😐', 'score' => 0]);
        }

        try {
            $prompt = "Réponds UNIQUEMENT avec ce JSON sur une seule ligne, sans markdown ni explication : "
                    . "{\"sentiment\":\"positif\",\"score\":0.9,\"raison\":\"texte positif\"} "
                    . "Les valeurs possibles pour sentiment sont : positif, negatif, mitige. "
                    . "Score entre -1.0 (très négatif) et 1.0 (très positif). "
                    . "Analyse ce texte en français : \"" . addslashes($texte) . "\"";

            // Essaie gemini-2.5-flash en priorité, fallback sur gemini-flash-latest
            $models = ['gemini-2.5-flash', 'gemini-flash-latest'];
            $data   = null;

            foreach ($models as $model) {
                $requestConfig = [
                    'headers' => [
                        'x-goog-api-key' => $this->geminiApiKey,
                        'Content-Type'   => 'application/json',
                    ],
                    'json' => [
                        'contents' => [['parts' => [['text' => $prompt]]]],
                        'generationConfig' => [
                            'temperature'     => 0.1,
                            'maxOutputTokens' => 1000,
                        ],
                    ],
                    'timeout' => 10,
                ];

                // Désactiver le thinking pour gemini-2.5-flash (évite MAX_TOKENS)
                if ($model === 'gemini-2.5-flash') {
                    $requestConfig['json']['generationConfig']['thinkingConfig'] = ['thinkingBudget' => 0];
                }

                $response   = $this->httpClient->request('POST',
                    "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent",
                    $requestConfig
                );

                $statusCode = $response->getStatusCode();
                if ($statusCode === 429) {
                    continue; // quota épuisé → essaie le modèle suivant
                }

                $data = $response->toArray(false);
                if (isset($data['candidates'])) {
                    break; // succès
                }
            }

            if (!$data || !isset($data['candidates'])) {
                return $this->json(['sentiment' => 'neutre', 'label' => 'Neutre', 'emoji' => '😐', 'score' => 0, 'raison' => '']);
            }

            $raw = $data['candidates'][0]['content']['parts'][0]['text'] ?? '{}';

            // Nettoyer les éventuels backticks markdown
            $raw = preg_replace('/```json|```/i', '', $raw);
            $result = json_decode(trim($raw), true);

            $sentiment = $result['sentiment'] ?? 'mitige';
            $score     = (float) ($result['score'] ?? 0);

            $map = [
                'positif' => ['label' => 'Positif',  'emoji' => '😊', 'color' => '#28a745'],
                'negatif' => ['label' => 'Négatif',  'emoji' => '😞', 'color' => '#dc3545'],
                'mitige'  => ['label' => 'Mitigé',   'emoji' => '😐', 'color' => '#fd7e14'],
            ];

            $info = $map[$sentiment] ?? $map['mitige'];

            return $this->json([
                'sentiment' => $sentiment,
                'label'     => $info['label'],
                'emoji'     => $info['emoji'],
                'color'     => $info['color'],
                'score'     => $score,
                'raison'    => $result['raison'] ?? '',
            ], 200, [], ['json_encode_options' => JSON_UNESCAPED_UNICODE]);

        } catch (\Throwable) {
            return $this->json(['sentiment' => 'neutre', 'label' => 'Neutre', 'emoji' => '😐', 'score' => 0]);
        }
    }

    #[Route('/avis', name: 'app_avis')]
    public function index(AvisRepository $avisRepo, Connection $conn): Response
    {
        $avisList = $avisRepo->findLatest(50);
        $moyenne  = $avisRepo->avgNoteByActivite(0);

        // Charger les réponses partenaire pour chaque avis
        $reponses = [];
        if (!empty($avisList)) {
            $ids  = array_map(fn($a) => $a->getId(), $avisList);
            $rows = $conn->fetchAllAssociative(
                'SELECT * FROM avis_reponse WHERE avis_id IN (' . implode(',', $ids) . ')'
            );
            foreach ($rows as $row) {
                $reponses[$row['avis_id']] = $row;
            }
        }

        // Charger les noms des activités
        $activitesMap = [];
        $rows = $conn->fetchAllAssociative('SELECT id, nom FROM activite ORDER BY nom');
        foreach ($rows as $row) {
            $activitesMap[$row['id']] = $row['nom'];
        }

        return $this->render('avis/index.html.twig', [
            'activites'    => [],
            'avisList'     => $avisList,
            'moyenne'      => $moyenne,
            'reponses'     => $reponses,
            'activitesMap' => $activitesMap,
            'currentUser'  => $this->getUser(),
            'allActivites' => $rows,
        ]);
    }

    #[Route('/avis/new', name: 'app_avis_new_public', methods: ['POST'])]
    public function newPublic(
        Request $request,
        EntityManagerInterface $em,
        Connection $conn,
        ModerationService $moderation,
        MailerInterface $mailer,
        \Symfony\Component\Validator\Validator\ValidatorInterface $validator
    ): Response {
        /** @var Users|null $user */
        $user    = $this->getUser();
        $rating  = (int) $request->request->get('note', $request->request->get('rating', 5));
        $titre   = trim($request->request->get('titre', 'Avis'));
        $contenu = trim($request->request->get('commentaire', $request->request->get('contenu', '')));
        $activiteId = $request->request->get('activite_id') ? (int) $request->request->get('activite_id') : null;

        // ── VALIDATION CÔTÉ SERVEUR (entité) ──
        if (!$activiteId) {
            $request->getSession()->set('avis_old', [
                'titre'       => $titre,
                'commentaire' => $contenu,
                'note'        => $rating,
                'activite_id' => null,
            ]);
            $this->addFlash('error_activite', 'Veuillez sélectionner une activité.');
            return $this->redirectToRoute('app_avis');
        }

        $avisTest = new Avis();
        $avisTest->setRating($rating);
        $avisTest->setTitre($titre);
        $avisTest->setContenu($contenu);
        $avisTest->setCreatedAt(new \DateTime());

        $violations = $validator->validate($avisTest);
        if (count($violations) > 0) {
            // Stocker les valeurs saisies pour les réafficher
            $request->getSession()->set('avis_old', [
                'titre'       => $titre,
                'commentaire' => $contenu,
                'note'        => $rating,
                'activite_id' => $activiteId,
            ]);
            
            foreach ($violations as $v) {
                $field = $v->getPropertyPath();
                // Map entity field names to form field names
                $fieldMap = ['titre' => 'error_titre', 'contenu' => 'error_commentaire', 'rating' => 'error_note'];
                $flashKey = $fieldMap[$field] ?? 'error';
                $this->addFlash($flashKey, $v->getMessage());
            }
            return $this->redirectToRoute('app_avis');
        }
        
        // Nettoyer les anciennes valeurs si validation OK
        $request->getSession()->remove('avis_old');

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

        // Gestion upload image
        $imageFilename = null;
        $file = $request->files->get('image');
        if ($file) {
            $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (in_array($file->getMimeType(), $allowed) && $file->getSize() <= 5 * 1024 * 1024) {
                $imageFilename = uniqid('avis_') . '.' . $file->guessExtension();
                $file->move($this->getParameter('kernel.project_dir') . '/public/uploads/avis', $imageFilename);
            }
        }

        $avis = new Avis();
        $avis->setUserId($userId);
        $avis->setRating($rating);
        $avis->setTitre($titre ?: 'Avis');
        $avis->setContenu($contenu);
        $avis->setCreatedAt(new \DateTime());
        $avis->setActiviteId($activiteId);
        if ($imageFilename) {
            $avis->setImage($imageFilename);
        }

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
