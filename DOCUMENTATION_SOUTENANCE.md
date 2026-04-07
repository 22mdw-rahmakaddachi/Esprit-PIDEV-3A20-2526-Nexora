# Documentation de Soutenance — Module E-Commerce (Symfony)

---

## 1. CONTRÔLE DE SAISIE — Comment ça fonctionne ?

### Q : Comment avez-vous fait le contrôle de saisie ?

**Réponse en 3 étapes (exactement comme le workshop) :**

**Étape 1 — Annotations Assert dans l'entité**
```php
// src/Entity/ProduitParent.php
use Symfony\Component\Validator\Constraints as Assert;

#[Assert\NotBlank(message: 'Le nom du produit est obligatoire.')]
#[Assert\Length(min: 3, max: 200, minMessage: 'Le nom doit contenir au moins {{ limit }} caractères.')]
private string $nom = '';
```

**Étape 2 — FormType avec `isValid()`**
```php
// src/Controller/Admin/AdminProduitController.php
$form = $this->createForm(ProduitParentType::class, $produit);
$form->handleRequest($request);
if ($form->isSubmitted() && $form->isValid()) {
    // sauvegarder seulement si valide
}
```

**Étape 3 — Template avec `novalidate` + `form_errors`**
```twig
{# Désactiver la validation HTML5 pour afficher les erreurs Symfony #}
{{ form_start(form, {'attr': {'novalidate': 'novalidate'}}) }}

{{ form_widget(form.nom) }}
{% if form.vars.submitted %}
    <div class="field-error">{{ form_errors(form.nom) }}</div>
{% endif %}
```

---

### Q : Pourquoi `novalidate` dans le formulaire ?

Sans `novalidate`, le navigateur bloque la soumission avec sa propre validation HTML5 **avant** que Symfony puisse valider. En ajoutant `novalidate`, on désactive la validation du navigateur pour laisser Symfony afficher ses propres messages d'erreur personnalisés.

---

### Q : Pourquoi les erreurs n'apparaissent qu'après avoir cliqué sur "Enregistrer" ?

Grâce à `form.vars.submitted` dans le template :
```twig
{% if form.vars.submitted %}
    <div class="field-error">{{ form_errors(form.nom) }}</div>
{% endif %}
```
- `form.vars.submitted = false` → page chargée pour la première fois → aucune erreur affichée
- `form.vars.submitted = true` → formulaire soumis → erreurs affichées

---

### Q : Pourquoi la couleur des messages d'erreur est rouge ?

**Fichier :** `templates/admin/produits/form.html.twig`

```css
/* Dans le bloc {% block stylesheets %} du même fichier */
.field-error {
    color: #c0392b;   /* rouge */
    font-size: 0.82rem;
    margin-top: 4px;
}
.has-error .form-control {
    border-color: #e74c3c;  /* bordure rouge */
}
```

`#c0392b` et `#e74c3c` sont des codes hexadécimaux de couleur rouge.

---

### Q : Quelles contraintes (Assert) avez-vous utilisées ?

| Contrainte | Utilité | Exemple dans le projet |
|---|---|---|
| `NotBlank` | Champ obligatoire (non vide) | Nom du produit, Code promo |
| `Length` | Longueur min/max | Nom : 3-200 chars |
| `Positive` | Nombre strictement positif | Prix de vente |
| `PositiveOrZero` | Nombre positif ou zéro | Stock, Poids |
| `NotNull` | Valeur non nulle | Poids, Dates |
| `Choice` | Valeur parmi une liste | Statut : actif/inactif |
| `Regex` | Format spécifique | SKU, Code promo |
| `GreaterThan` | Comparaison entre champs | Date fin > Date début |
| `File` | Validation fichier uploadé | Image : max 5Mo, JPG/PNG/WebP |

---

## 2. SYMFONY FORMS — Questions techniques

### Q : C'est quoi un FormType ?

Un FormType est une classe PHP qui définit la structure d'un formulaire. Il est créé avec :
```bash
php bin/console make:form ProduitParentType ProduitParent
```
Il se trouve dans `src/Form/ProduitParentType.php`.

---

### Q : C'est quoi `form_start`, `form_widget`, `form_errors`, `form_end` ?

| Fonction Twig | Rôle |
|---|---|
| `form_start(form)` | Génère la balise `<form>` |
| `form_label(form.champ)` | Génère le label HTML |
| `form_widget(form.champ)` | Génère l'input HTML |
| `form_errors(form.champ)` | Affiche les erreurs du champ |
| `form_row(form.champ)` | = label + widget + errors |
| `form_end(form)` | Génère `</form>` |

---

### Q : C'est quoi `empty_data` dans le FormType ?

Quand un champ texte est laissé vide, Symfony envoie `null` par défaut. Mais si la propriété PHP est typée `string` (non nullable), ça provoque une erreur. `empty_data: ''` force la valeur à être une chaîne vide au lieu de `null`.

```php
->add('nom', TextType::class, [
    'empty_data' => '',  // évite l'erreur "null given, string expected"
])
```

---

## 3. PHP — Questions sur la syntaxe

### Q : À quoi sert le `?` devant un type PHP ? (ex: `?string`, `?int`)

Le `?` signifie que la valeur peut être **null** ou du type indiqué.

```php
private ?string $marque = null;   // peut être null OU une string
private string $nom = '';          // ne peut PAS être null
```

---

### Q : À quoi sert le `??` (double point d'interrogation) ?

C'est l'opérateur **null coalescing**. Il retourne la valeur de gauche si elle n'est pas null, sinon la valeur de droite.

```php
// Dans ProduitVariant.php
return $this->prixPromo ?? $this->prixVente;
// Si prixPromo est null → retourne prixVente
// Si prixPromo a une valeur → retourne prixPromo
```

---

### Q : À quoi sert le `!` devant une condition ?

Le `!` est l'opérateur de **négation** (NOT). Il inverse la valeur booléenne.

```php
if (!$produit) {  // si produit est null/false
    return $this->redirectToRoute('admin_produits');
}

if (!is_array($skus)) return;  // si skus n'est PAS un tableau
```

---

### Q : À quoi sert `fn()` ?

C'est une **arrow function** (fonction fléchée), syntaxe courte pour les fonctions anonymes.

```php
// Calcul du total du panier
$total = array_sum(array_map(fn($item) => $item['prix'] * $item['quantite'], $panier));

// Équivalent long :
$total = array_sum(array_map(function($item) {
    return $item['prix'] * $item['quantite'];
}, $panier));
```

---

### Q : À quoi sert `static` dans les setters ?

`static` permet le **chaînage de méthodes** (method chaining). Il retourne l'objet lui-même.

```php
public function setNom(string $nom): static
{
    $this->nom = $nom;
    return $this;  // retourne l'objet
}

// Permet d'écrire :
$produit->setNom('Sac')->setMarque('Quechua')->setStatut('actif');
```

---

## 4. DOCTRINE ORM — Questions sur les entités

### Q : C'est quoi `#[ORM\Entity]`, `#[ORM\Column]` ?

Ce sont des **attributs PHP** (annotations modernes) qui indiquent à Doctrine comment mapper la classe vers la base de données.

```php
#[ORM\Entity(repositoryClass: ProduitParentRepository::class)]
#[ORM\Table(name: 'produit_parent')]  // nom de la table en base
class ProduitParent
{
    #[ORM\Id]
    #[ORM\GeneratedValue]  // auto-increment
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 200)]  // VARCHAR(200)
    private string $nom = '';
}
```

---

### Q : C'est quoi `ManyToOne`, `OneToMany` ?

Ce sont les **relations entre entités** :

```php
// ProduitParent a PLUSIEURS variantes (OneToMany)
#[ORM\OneToMany(targetEntity: ProduitVariant::class, mappedBy: 'produitParent')]
private Collection $variants;

// ProduitVariant appartient à UN seul produit parent (ManyToOne)
#[ORM\ManyToOne(targetEntity: ProduitParent::class, inversedBy: 'variants')]
private ?ProduitParent $produitParent = null;
```

---

### Q : C'est quoi `cascade: ['persist', 'remove']` ?

- `persist` : quand on sauvegarde le parent, les enfants sont aussi sauvegardés automatiquement
- `remove` : quand on supprime le parent, les enfants sont aussi supprimés automatiquement

```php
#[ORM\OneToMany(targetEntity: ProduitVariant::class, cascade: ['persist', 'remove'])]
```

---

### Q : C'est quoi `$em->persist()` et `$em->flush()` ?

```php
$em->persist($produit);  // prépare l'objet pour être sauvegardé (en mémoire)
$em->flush();            // exécute le SQL INSERT/UPDATE en base de données
```
Sans `flush()`, rien n'est écrit en base.

---

## 5. COMMANDES UTILISÉES

### Q : Quelles commandes Symfony avez-vous utilisées ?

```bash
# Créer un FormType lié à une entité
php bin/console make:form ProduitParentType ProduitParent

# Générer une migration (diff entre entités et base)
php bin/console doctrine:migrations:diff

# Appliquer les migrations en base
php bin/console doctrine:migrations:migrate

# Vider le cache
php bin/console cache:clear

# Voir toutes les routes
php bin/console debug:router

# Exécuter une requête SQL directe
php bin/console doctrine:query:sql "SELECT * FROM partenaire"

# Lancer les tests unitaires
php bin/phpunit tests/Entity/ --testdox
```

---

## 6. TESTS UNITAIRES — Questions

### Q : Comment avez-vous fait les tests unitaires ?

```php
// tests/Entity/ProduitParentValidationTest.php
class ProduitParentValidationTest extends TestCase
{
    private $validator;

    protected function setUp(): void
    {
        // Créer le validateur Symfony
        $this->validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()  // lire les annotations Assert
            ->getValidator();
    }

    public function testNomObligatoire(): void
    {
        $p = new ProduitParent();
        $p->setNom('');  // champ vide
        $errors = $this->validator->validate($p);
        $this->assertGreaterThan(0, count($errors));  // doit avoir des erreurs
        $this->assertStringContainsString('obligatoire', $errors[0]->getMessage());
    }
}
```

---

### Q : Combien de tests avez-vous ? Résultat ?

**66 tests, 83 assertions — tous passent (OK)**

Répartis en 4 fichiers :
- `ProduitParentValidationTest` : 25 tests (nom, description, marque, matériau, poids, dimensions, statut)
- `ProduitVariantValidationTest` : 17 tests (SKU, prix vente, prix promo, stock, seuil alerte, prix achat)
- `CodePromoValidationTest` : 16 tests (code, type, valeur, dates, montant minimum, limite)
- `CategorieValidationTest` : 8 tests (catégorie et sous-catégorie)

---

### Q : C'est quoi `assertGreaterThan`, `assertCount`, `assertStringContainsString` ?

Ce sont des **méthodes d'assertion PHPUnit** :

```php
$this->assertGreaterThan(0, count($errors));
// Vérifie que count($errors) > 0 (il y a des erreurs)

$this->assertCount(0, $errors);
// Vérifie qu'il y a exactement 0 erreurs (valide)

$this->assertStringContainsString('obligatoire', $errors[0]->getMessage());
// Vérifie que le message contient le mot "obligatoire"
```

---

## 7. ARCHITECTURE DU MODULE

### Q : Expliquez l'architecture de votre module e-commerce

```
Module E-Commerce
│
├── CÔTÉ PARTENAIRE (admin)
│   ├── /admin/categories     → CRUD catégories + sous-catégories
│   ├── /admin/attributs      → CRUD attributs (Taille, Couleur...) + options
│   ├── /admin/produits       → CRUD produits avec variantes + upload image
│   └── /admin/promos         → CRUD codes promo
│
├── CÔTÉ CLIENT (boutique)
│   ├── /boutique             → Liste produits avec filtre catégorie
│   ├── /boutique/{id}        → Fiche produit + sélection variante
│   ├── /panier               → Panier (session) + code promo
│   ├── /panier/commander     → Checkout + confirmation commande
│   └── /panier/confirmation  → Page de confirmation
│
└── TESTS
    └── tests/Entity/         → 66 tests unitaires de validation
```

---

### Q : Où est stocké le panier ?

Le panier est stocké en **session PHP** (pas en base de données). Chaque item a la structure :

```php
$panier[$key] = [
    'produitId'    => 9,
    'variantId'    => 3,
    'nom'          => 'Chaussures de ski',
    'variantLabel' => 'Noir / 40',
    'sku'          => 'SKI-NOIR-40',
    'prix'         => 120.0,
    'image'        => 'chaussures-abc123.jpg',
    'quantite'     => 1,
];
```

---

### Q : Comment fonctionne le code promo ?

1. Client saisit le code dans le panier
2. `PanierController::appliquerPromo()` appelle `CodePromoRepository::findValidCode()`
3. Vérifications : code actif, dates valides, montant minimum, limite d'utilisation
4. Calcul de la réduction : `pourcentage` ou `montant_fixe`
5. Stockage en session : `code_promo` et `reduction`
6. À la confirmation de commande : `nombreUtilisations` s'incrémente en base

---

## 8. QUESTIONS PIÈGES FRÉQUENTES

### Q : Quelle est la différence entre `persist` et `flush` ?

- `persist` : dit à Doctrine "surveille cet objet" (en mémoire uniquement)
- `flush` : envoie **toutes** les opérations en attente vers la base de données en une seule transaction

### Q : Pourquoi utiliser un Repository ?

Pour centraliser les requêtes SQL. Au lieu d'écrire du SQL partout, on crée des méthodes réutilisables :
```php
// ProduitParentRepository.php
public function findActifs(?int $sousCategorieId = null): array
{
    return $this->createQueryBuilder('p')
        ->where('p.statut = :statut')
        ->setParameter('statut', 'actif')
        ->getQuery()->getResult();
}
```

### Q : Pourquoi `nullable: true` dans les colonnes ORM ?

Permet à la colonne d'accepter `NULL` en base de données. Sans ça, la colonne est `NOT NULL`.

### Q : C'est quoi `ArrayCollection` ?

C'est la classe Doctrine pour gérer les collections d'entités liées (équivalent d'un tableau PHP mais avec des fonctionnalités supplémentaires comme `contains()`, `filter()`, etc.).

```php
public function __construct()
{
    $this->variants = new ArrayCollection();
}
```

### Q : Pourquoi `#[ORM\JoinColumn(nullable: true)]` ?

Permet à la clé étrangère d'être NULL en base. Utile quand la relation est optionnelle.

### Q : C'est quoi `SluggerInterface` ?

Un service Symfony qui transforme un texte en slug (URL-friendly) :
```php
$slugger->slug('Sac à dos 40L') // → 'Sac-a-dos-40L'
```
Utilisé pour générer des noms de fichiers propres lors de l'upload d'images.

---

## 9. UPLOAD IMAGE — Questions

### Q : Comment fonctionne l'upload d'image ?

```php
// 1. Dans le FormType : champ FileType non mappé
->add('imagePrincipale', FileType::class, [
    'mapped'   => false,  // ne pas lier directement à l'entité
    'required' => false,
])

// 2. Dans le Controller : récupérer et déplacer le fichier
$imageFile = $form->get('imagePrincipale')->getData();
if ($imageFile) {
    $filename = $slugger->slug(...) . '-' . uniqid() . '.' . $imageFile->guessExtension();
    $imageFile->move('/public/uploads/produits', $filename);
    $produit->setImagePrincipale($filename);
}
```

### Q : Pourquoi `mapped: false` pour le champ image ?

Parce que l'entité stocke le **nom du fichier** (string), pas le fichier lui-même. Si `mapped: true`, Symfony essaierait de setter un objet `UploadedFile` dans une propriété `string`, ce qui provoquerait une erreur.

### Q : Où sont stockées les images ?

Dans `public/uploads/produits/` — accessible directement via l'URL `/uploads/produits/nom-fichier.jpg`.

---

## 10. RÉSUMÉ RAPIDE À RETENIR

| Quoi | Où |
|---|---|
| Contraintes de validation | `src/Entity/ProduitParent.php` (annotations Assert) |
| Définition du formulaire | `src/Form/ProduitParentType.php` |
| Traitement du formulaire | `src/Controller/Admin/AdminProduitController.php` |
| Affichage + erreurs | `templates/admin/produits/form.html.twig` |
| Couleur rouge des erreurs | `templates/admin/produits/form.html.twig` (CSS `.field-error { color: #c0392b }`) |
| Tests unitaires | `tests/Entity/ProduitParentValidationTest.php` |
| Panier (session) | `src/Controller/PanierController.php` |
| Code promo | `src/Repository/CodePromoRepository.php` + `PanierController` |
