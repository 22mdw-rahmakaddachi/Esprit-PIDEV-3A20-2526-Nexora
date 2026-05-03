# 🛡️ API de Modération - Détection de Mots Graves

## 📋 Vue d'ensemble

Le système de modération détecte automatiquement les **mots graves/toxiques** dans les avis et publications pour **bloquer leur publication**.

---

## 🔧 Service: ModerationService

**Fichier**: `src/Service/ModerationService.php`

### Fonctionnement en 2 couches:

#### Couche 1 — Liste locale (instantané, sans internet)
```php
private const BAD_WORDS = [
    'merde','putain','connard','connasse','salope','enculé','enculer','fdp','fils de pute',
    'bâtard','batard','nique','niquer','ta gueule','va te faire','idiot','imbécile',
    'fuck','shit','bitch','asshole','bastard','cunt','dick','pussy','whore','nigger',
    'pute','con','conne','abruti','crétin','débile','mongol','attardé',
];
```

**Avantage**: Rapide, fonctionne hors ligne

#### Couche 2 — API HuggingFace (toxic-bert)
```
POST https://api-inference.huggingface.co/models/unitary/toxic-bert
Body: { "inputs": "le texte à analyser" }
Réponse: [{"label":"toxic","score":0.95}]
```

**Avantage**: Détection IA avancée (contexte, nuances)

---

## 🔑 Configuration

### Fichier `.env`:
```env
HUGGINGFACE_API_KEY=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
```

### Fichier `config/services.yaml`:
```yaml
services:
    App\Service\ModerationService:
        arguments:
            $huggingfaceApiKey: '%env(HUGGINGFACE_API_KEY)%'
```

---

## 📊 Méthode `analyze()`

### Signature:
```php
public function analyze(string $text): array
```

### Retour:
```php
[
    'toxic'  => bool,   // true si toxique
    'score'  => float,  // 0.0 à 1.0
    'method' => string, // 'local' ou 'huggingface'
    'word'   => string  // (optionnel) mot détecté en local
]
```

### Exemples:

#### Texte propre:
```php
$result = $moderation->analyze("Excellent service, très satisfait!");
// ['toxic' => false, 'score' => 0.0, 'method' => 'local']
```

#### Mot interdit (liste locale):
```php
$result = $moderation->analyze("C'est de la merde");
// ['toxic' => true, 'score' => 1.0, 'method' => 'local', 'word' => 'merde']
```

#### Toxicité détectée par IA:
```php
$result = $moderation->analyze("Je vais te détruire");
// ['toxic' => true, 'score' => 0.89, 'method' => 'huggingface']
```

---

## 🚫 Utilisation dans AvisController

**Fichier**: `src/Controller/AvisController.php`

### Code actuel:
```php
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
        // BLOQUER l'utilisateur
        $conn->insert('publication_warning', [
            'user_id'        => $userId,
            'user_email'     => $userEmail,
            'user_nom'       => $userNom,
            'contenu_bloque' => '[AVIS] ' . $contenu,
            'warning_count'  => $newCount,
            'is_blocked'     => 1,
            'created_at'     => (new \DateTime())->format('Y-m-d H:i:s'),
        ]);
        
        $this->addFlash('danger', '🚫 Votre compte a été bloqué suite à des avis répétés avec du contenu inapproprié.');
        return $this->redirectToRoute('app_avis');
    } else {
        // AVERTISSEMENT
        $conn->insert('publication_warning', [
            'user_id'        => $userId,
            'user_email'     => $userEmail,
            'user_nom'       => $userNom,
            'contenu_bloque' => '[AVIS] ' . $contenu,
            'warning_count'  => $newCount,
            'is_blocked'     => 0,
            'created_at'     => (new \DateTime())->format('Y-m-d H:i:s'),
        ]);
        
        $remaining = 3 - $newCount;
        $this->addFlash('warning',
            "⚠️ Votre avis contient du contenu inapproprié et n'a pas été publié. " .
            "Avertissement {$newCount}/2. " .
            "Il vous reste {$remaining} chance(s) avant blocage."
        );
        return $this->redirectToRoute('app_avis');
    }
}
```

---

## 📧 Système d'Avertissement

### Table: `publication_warning`
```sql
CREATE TABLE publication_warning (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    user_email VARCHAR(255),
    user_nom VARCHAR(255),
    contenu_bloque TEXT,
    warning_count INT DEFAULT 1,
    is_blocked TINYINT(1) DEFAULT 0,
    created_at DATETIME
);
```

### Règles:
1. **1er avertissement**: Message d'alerte, avis refusé
2. **2ème avertissement**: Message d'alerte, avis refusé
3. **3ème avertissement**: **BLOCAGE DÉFINITIF**

### Emails automatiques:
- ✅ Email à l'utilisateur (avertissement ou blocage)
- ✅ Email à l'admin (notification de blocage)

---

## 🔄 Ajouter la Modération aux Publications

Pour ajouter la même modération aux publications, modifiez `PublicationController`:

```php
#[Route('/new', name: 'app_publication_new', methods: ['POST'])]
public function new(
    Request $request,
    Connection $conn,
    \Symfony\Component\Validator\Validator\ValidatorInterface $validator,
    ModerationService $moderation,
    MailerInterface $mailer
): Response {
    $user    = $this->getUser();
    $auteur  = $user ? $user->getPrenom() . ' ' . $user->getNom() : trim($request->request->get('auteur', ''));
    $userId  = $user ? $user->getId() : null;
    $contenu = trim($request->request->get('contenu', ''));

    // ── VALIDATION ──
    $publication = new \App\Entity\Publication();
    $publication->setAuteur($auteur);
    $publication->setContenu($contenu);
    $publication->setCreatedAt(new \DateTime());

    $violations = $validator->validate($publication);
    if (count($violations) > 0) {
        // ... gestion erreurs validation ...
    }

    // ── MODÉRATION ──
    if ($user instanceof Users) {
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
                    'user_email'     => $user->getEmail(),
                    'user_nom'       => $user->getFullName(),
                    'contenu_bloque' => '[PUBLICATION] ' . $contenu,
                    'warning_count'  => $newCount,
                    'is_blocked'     => 1,
                    'created_at'     => (new \DateTime())->format('Y-m-d H:i:s'),
                ]);
                
                $this->addFlash('danger', '🚫 Votre compte a été bloqué suite à des publications répétées avec du contenu inapproprié.');
                return $this->redirectToRoute('app_publications');
            } else {
                // AVERTISSEMENT
                $conn->insert('publication_warning', [
                    'user_id'        => $userId,
                    'user_email'     => $user->getEmail(),
                    'user_nom'       => $user->getFullName(),
                    'contenu_bloque' => '[PUBLICATION] ' . $contenu,
                    'warning_count'  => $newCount,
                    'is_blocked'     => 0,
                    'created_at'     => (new \DateTime())->format('Y-m-d H:i:s'),
                ]);
                
                $remaining = 3 - $newCount;
                $this->addFlash('warning',
                    "⚠️ Votre publication contient du contenu inapproprié et n'a pas été publiée. " .
                    "Avertissement {$newCount}/2. " .
                    "Il vous reste {$remaining} chance(s) avant blocage."
                );
                return $this->redirectToRoute('app_publications');
            }
        }
    }

    // ── ENREGISTREMENT si OK ──
    // ...
}
```

---

## 🧪 Test de la Modération

### Test 1: Mot interdit (liste locale)
```
Titre: "Service"
Commentaire: "C'est de la merde"
→ ⚠️ Avertissement 1/2
→ Avis refusé
```

### Test 2: Deuxième tentative
```
Titre: "Nul"
Commentaire: "Vous êtes des connards"
→ ⚠️ Avertissement 2/2
→ Avis refusé
→ "Il vous reste 1 chance avant blocage"
```

### Test 3: Troisième tentative
```
Titre: "Horrible"
Commentaire: "Allez vous faire foutre"
→ 🚫 COMPTE BLOQUÉ
→ Email envoyé à l'utilisateur
→ Email envoyé à l'admin
```

### Test 4: Utilisateur bloqué essaie de publier
```
→ 🚫 "Votre compte est bloqué. Contactez l'administrateur."
→ Aucun avis ne peut être publié
```

---

## 📈 Avantages du Système

1. **Double protection**: Liste locale + IA
2. **Fallback**: Fonctionne même si API indisponible
3. **Progressif**: 3 chances avant blocage
4. **Traçabilité**: Historique dans `publication_warning`
5. **Notifications**: Emails automatiques
6. **Multilingue**: Détecte français et anglais

---

## 🔒 Sécurité

- ✅ Vérification côté serveur (PHP)
- ✅ Impossible de contourner avec JavaScript
- ✅ Analyse avant enregistrement en base
- ✅ Blocage définitif après 3 avertissements
- ✅ Admin notifié des blocages

---

## 📝 Notes

- La clé API HuggingFace est **gratuite** (limite: 30 000 requêtes/mois)
- Le modèle `toxic-bert` est pré-entraîné (pas besoin de prompt)
- Score > 0.75 = considéré comme toxique
- La liste locale est vérifiée en premier (plus rapide)
