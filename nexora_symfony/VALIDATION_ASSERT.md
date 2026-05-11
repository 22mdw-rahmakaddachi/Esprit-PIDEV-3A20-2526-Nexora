# ✅ Contrôle de Saisie avec Assert dans les Entités

## 📋 Vue d'ensemble

Toutes les validations sont définies dans les entités avec les contraintes **Symfony Assert**.

---

## 🎯 Entité: Avis

**Fichier**: `src/Entity/Avis.php`

### Champs avec validation:

#### 1. Titre
```php
#[ORM\Column(length: 100)]
#[Assert\NotBlank(message: 'Le titre est obligatoire.')]
#[Assert\Length(
    min: 3,
    max: 100,
    minMessage: 'Le titre doit contenir au moins {{ limit }} caractères.',
    maxMessage: 'Le titre ne peut pas dépasser {{ limit }} caractères.'
)]
private string $titre = '';
```

**Règles:**
- ✅ Obligatoire (NotBlank)
- ✅ Minimum: 3 caractères
- ✅ Maximum: 100 caractères

**Messages d'erreur:**
- "Le titre est obligatoire."
- "Le titre doit contenir au moins 3 caractères."
- "Le titre ne peut pas dépasser 100 caractères."

---

#### 2. Contenu (Commentaire)
```php
#[ORM\Column(type: 'text')]
#[Assert\NotBlank(message: 'Le commentaire est obligatoire.')]
#[Assert\Length(
    min: 5,
    max: 2000,
    minMessage: 'Le commentaire doit contenir au moins {{ limit }} caractères.',
    maxMessage: 'Le commentaire ne peut pas dépasser {{ limit }} caractères.'
)]
private string $contenu = '';
```

**Règles:**
- ✅ Obligatoire (NotBlank)
- ✅ Minimum: 5 caractères
- ✅ Maximum: 2000 caractères

**Messages d'erreur:**
- "Le commentaire est obligatoire."
- "Le commentaire doit contenir au moins 5 caractères."
- "Le commentaire ne peut pas dépasser 2000 caractères."

---

#### 3. Rating (Note)
```php
#[ORM\Column(type: 'integer')]
#[Assert\Range(min: 1, max: 5, notInRangeMessage: 'La note doit être entre 1 et 5.')]
#[Assert\NotBlank(message: 'La note est obligatoire.')]
private int $rating = 0;
```

**Règles:**
- ✅ Obligatoire (NotBlank)
- ✅ Minimum: 1
- ✅ Maximum: 5

**Messages d'erreur:**
- "La note est obligatoire."
- "La note doit être entre 1 et 5."

---

## 🎯 Entité: Publication

**Fichier**: `src/Entity/Publication.php`

### Champs avec validation:

#### 1. Auteur
```php
#[ORM\Column(type: 'string', length: 255)]
#[Assert\NotBlank(message: 'L\'auteur est obligatoire.')]
#[Assert\Length(
    min: 2,
    max: 255,
    minMessage: 'L\'auteur doit contenir au moins {{ limit }} caractères.',
    maxMessage: 'L\'auteur ne peut pas dépasser {{ limit }} caractères.'
)]
private string $auteur = '';
```

**Règles:**
- ✅ Obligatoire (NotBlank)
- ✅ Minimum: 2 caractères
- ✅ Maximum: 255 caractères

---

#### 2. Contenu
```php
#[ORM\Column(type: 'text')]
#[Assert\NotBlank(message: 'Le contenu est obligatoire.')]
#[Assert\Length(
    min: 2,
    max: 2000,
    minMessage: 'Le contenu doit contenir au moins {{ limit }} caractères.',
    maxMessage: 'Le contenu ne peut pas dépasser {{ limit }} caractères.'
)]
private string $contenu = '';
```

**Règles:**
- ✅ Obligatoire (NotBlank)
- ✅ Minimum: 2 caractères
- ✅ Maximum: 2000 caractères

**Messages d'erreur:**
- "Le contenu est obligatoire."
- "Le contenu doit contenir au moins 2 caractères."
- "Le contenu ne peut pas dépasser 2000 caractères."

---

## 🎯 Entité: Commentaire

**Fichier**: `src/Entity/Commentaire.php`

### Champs avec validation:

#### 1. Contenu
```php
#[ORM\Column(type: 'text')]
#[Assert\NotBlank(message: 'Le commentaire est obligatoire.')]
#[Assert\Length(
    min: 2,
    max: 1000,
    minMessage: 'Le commentaire doit contenir au moins {{ limit }} caractères.',
    maxMessage: 'Le commentaire ne peut pas dépasser {{ limit }} caractères.'
)]
private string $contenu = '';
```

**Règles:**
- ✅ Obligatoire (NotBlank)
- ✅ Minimum: 2 caractères
- ✅ Maximum: 1000 caractères

---

#### 2. AvisId
```php
#[ORM\Column(type: 'integer')]
#[Assert\NotBlank(message: 'L\'identifiant de l\'avis est obligatoire.')]
#[Assert\Positive(message: 'L\'identifiant de l\'avis doit être positif.')]
private int $avisId = 0;
```

**Règles:**
- ✅ Obligatoire (NotBlank)
- ✅ Doit être positif (> 0)

---

## 🔧 Utilisation dans les Contrôleurs

### AvisController
```php
use Symfony\Component\Validator\Validator\ValidatorInterface;

public function newPublic(
    Request $request,
    EntityManagerInterface $em,
    ValidatorInterface $validator
): Response {
    $avis = new Avis();
    $avis->setTitre($titre);
    $avis->setContenu($contenu);
    $avis->setRating($rating);
    
    // Validation
    $violations = $validator->validate($avis);
    if (count($violations) > 0) {
        foreach ($violations as $v) {
            $field = $v->getPropertyPath();
            $fieldMap = [
                'titre' => 'error_titre',
                'contenu' => 'error_commentaire',
                'rating' => 'error_note'
            ];
            $flashKey = $fieldMap[$field] ?? 'error';
            $this->addFlash($flashKey, $v->getMessage());
        }
        return $this->redirectToRoute('app_avis');
    }
    
    // Enregistrement si validation OK
    $em->persist($avis);
    $em->flush();
}
```

### PublicationController
```php
use Symfony\Component\Validator\Validator\ValidatorInterface;

public function new(
    Request $request,
    Connection $conn,
    ValidatorInterface $validator
): Response {
    $publication = new Publication();
    $publication->setAuteur($auteur);
    $publication->setContenu($contenu);
    
    // Validation
    $violations = $validator->validate($publication);
    if (count($violations) > 0) {
        foreach ($violations as $v) {
            $field = $v->getPropertyPath();
            $flashKey = $field === 'contenu' ? 'error_contenu' : 'error_' . $field;
            $this->addFlash($flashKey, $v->getMessage());
        }
        return $this->redirectToRoute('app_publications');
    }
    
    // Enregistrement si validation OK
    // ...
}
```

---

## 📊 Tableau Récapitulatif

| Entité       | Champ       | NotBlank | Min | Max  | Range | Positive |
|--------------|-------------|----------|-----|------|-------|----------|
| Avis         | titre       | ✅       | 3   | 100  | -     | -        |
| Avis         | contenu     | ✅       | 5   | 2000 | -     | -        |
| Avis         | rating      | ✅       | -   | -    | 1-5   | -        |
| Publication  | auteur      | ✅       | 2   | 255  | -     | -        |
| Publication  | contenu     | ✅       | 2   | 2000 | -     | -        |
| Commentaire  | contenu     | ✅       | 2   | 1000 | -     | -        |
| Commentaire  | avisId      | ✅       | -   | -    | -     | ✅       |

---

## 🎨 Affichage des Erreurs

### Dans les Templates (Twig)

#### Bordure rouge sur le champ:
```twig
<input type="text" name="titre" 
       style="{% if app.session.flashBag.peek('error_titre') is not empty %}border:2px solid #e74c3c !important;{% endif %}">
```

#### Message rouge sous le champ:
```twig
{% for error in app.flashes('error_titre') %}
<div style="margin-top:5px;font-size:0.85rem;color:#e74c3c;font-weight:500;">
    {{ error }}
</div>
{% endfor %}
```

---

## ✅ Avantages de cette Approche

1. **Centralisé**: Toutes les règles de validation sont dans les entités
2. **Réutilisable**: Les mêmes règles s'appliquent partout où l'entité est utilisée
3. **Maintenable**: Facile à modifier les règles en un seul endroit
4. **Messages personnalisés**: Messages d'erreur en français et clairs
5. **Type-safe**: Validation au niveau de l'objet PHP
6. **Symfony standard**: Utilise le système de validation Symfony natif

---

## 🧪 Test

Pour tester la validation:

1. Aller sur http://localhost:8000/avis
2. Laisser le champ **Titre** vide
3. Cliquer sur **Publier**
4. ✅ Voir: Bordure rouge + message "Le titre est obligatoire."

5. Entrer "ab" dans **Titre**
6. Cliquer sur **Publier**
7. ✅ Voir: Bordure rouge + message "Le titre doit contenir au moins 3 caractères."

---

## 📝 Notes

- Validation **100% côté serveur** (PHP)
- Pas de validation HTML5 (`novalidate` sur les formulaires)
- Messages flash avec clés spécifiques (`error_titre`, `error_commentaire`, etc.)
- Les messages généraux (success, danger, warning) ne s'affichent pas en popup pour les erreurs de validation
