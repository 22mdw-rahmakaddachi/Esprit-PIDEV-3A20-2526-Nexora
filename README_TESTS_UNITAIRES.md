# 📚 Guide Complet - Tests Unitaires UserManager

## 🎯 Vue d'Ensemble

Ce projet implémente des tests unitaires pour l'entité `Users` dans le cadre du workshop Symfony sur les tests unitaires.

**Résultat** : ✅ 11 tests, 16 assertions, 100% de réussite

## 📁 Structure des Fichiers

```
Esprit-PIDEV-3A20-2526-Nexora-integrationnne/
│
├── src/
│   └── Service/
│       └── UserManager.php              # Service métier avec règles de validation
│
├── tests/
│   └── Service/
│       └── UserManagerTest.php          # Tests unitaires (11 tests)
│
└── Documentation/
    ├── TESTS_UNITAIRES_USERS.md         # Documentation complète
    ├── EXPLICATION_ERREUR_MOCK.md       # Explication technique
    ├── RESULTAT_FINAL.md                # Résultats et statistiques
    ├── PRESENTATION_WORKSHOP.md         # Support de présentation
    └── README_TESTS_UNITAIRES.md        # Ce fichier
```

## 🚀 Démarrage Rapide

### 1. Exécuter les tests

```bash
# Exécution simple
php bin/phpunit tests/Service/UserManagerTest.php

# Avec format lisible
php bin/phpunit tests/Service/UserManagerTest.php --testdox

# Avec détails
php bin/phpunit tests/Service/UserManagerTest.php --verbose
```

### 2. Résultat attendu

```
PHPUnit 9.6.34 by Sebastian Bergmann and contributors.

Testing App\Tests\Service\UserManagerTest
...........                                                       11 / 11 (100%)

Time: 00:00.141, Memory: 10.00 MB

OK (11 tests, 16 assertions)
```

## 📋 Règles Métier Testées

| # | Règle Métier | Test Associé | Statut |
|---|--------------|--------------|--------|
| 1 | Le nom est obligatoire | `testUserWithoutNom()` | ✅ |
| 2 | Le prénom est obligatoire | `testUserWithoutPrenom()` | ✅ |
| 3 | L'email doit être valide | `testUserWithInvalidEmail()` | ✅ |
| 4 | Mot de passe ≥ 8 caractères | `testUserWithShortPassword()` | ✅ |
| 5 | Numéro de téléphone ≥ 0 | `testUserWithNegativeNumber()` | ✅ |

## 🔧 Utilisation du Service UserManager

### Exemple 1 : Validation d'un utilisateur

```php
use App\Entity\Users;
use App\Service\UserManager;

$user = new Users();
$user->setNom('Dupont');
$user->setPrenom('Jean');
$user->setEmail('jean.dupont@example.com');
$user->setMdp('motdepasse123');
$user->setNum(12345678);

$manager = new UserManager();

try {
    $isValid = $manager->validate($user);
    echo "Utilisateur valide !";
} catch (\InvalidArgumentException $e) {
    echo "Erreur : " . $e->getMessage();
}
```

### Exemple 2 : Vérifier si un utilisateur est bloqué

```php
$user = new Users();
$user->setBlockUntil(time() + 3600); // Bloqué pour 1 heure

$manager = new UserManager();

if ($manager->isBlocked($user)) {
    echo "Utilisateur bloqué jusqu'à " . date('H:i', $user->getBlockUntil());
}
```

### Exemple 3 : Vérifier les tentatives de connexion

```php
$user = new Users();
$user->setTentative(5);

$manager = new UserManager();

if ($manager->hasExceededAttempts($user, 3)) {
    echo "Trop de tentatives de connexion !";
    // Bloquer l'utilisateur
    $user->setBlockUntil(time() + 900); // 15 minutes
}
```

## 📝 Écrire de Nouveaux Tests

### Template de test

```php
/**
 * Test: Description claire de ce qui est testé
 */
public function testNomDescriptif(): void
{
    // 1. Arrange (Préparer les données)
    $user = new Users();
    $user->setNom('Test');
    // ... autres setters

    // 2. Act (Exécuter l'action)
    $result = $this->userManager->validate($user);

    // 3. Assert (Vérifier le résultat)
    $this->assertTrue($result);
}
```

### Test avec exception attendue

```php
public function testNouvelleRegle(): void
{
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('Message d\'erreur attendu');

    $user = new Users();
    // ... configuration qui doit échouer

    $this->userManager->validate($user);
}
```

## 🎓 Concepts Clés

### 1. Tests Unitaires Purs

**Caractéristiques** :
- ✅ Testent une seule unité de code
- ✅ Pas de dépendances externes (DB, API, etc.)
- ✅ Rapides (< 100ms par test)
- ✅ Reproductibles (même résultat à chaque fois)

**Notre approche** :
```php
// ✅ BON : Service pur sans dépendances
class UserManager
{
    public function validate(Users $user): bool
    {
        // Logique métier pure
    }
}

// ❌ MAUVAIS : Service avec dépendances
class UserManager
{
    public function __construct(
        private EntityManagerInterface $em,
        private ValidatorInterface $validator
    ) {}
}
```

### 2. Méthode AAA (Arrange-Act-Assert)

```php
public function testExample(): void
{
    // Arrange : Préparer les données
    $user = new Users();
    $user->setNom('Test');

    // Act : Exécuter l'action
    $result = $this->userManager->validate($user);

    // Assert : Vérifier le résultat
    $this->assertTrue($result);
}
```

### 3. Méthode setUp()

```php
protected function setUp(): void
{
    // Exécuté AVANT chaque test
    $this->userManager = new UserManager();
}
```

## 🐛 Dépannage

### Problème : Tests ne s'exécutent pas

**Solution** :
```bash
# Vérifier que PHPUnit est installé
composer require --dev phpunit/phpunit

# Vérifier la configuration
cat phpunit.xml.dist
```

### Problème : Erreur de namespace

**Solution** :
```bash
# Régénérer l'autoload
composer dump-autoload
```

### Problème : Erreur "Class not found"

**Vérifier** :
1. Le namespace dans le fichier : `namespace App\Tests\Service;`
2. Le nom de la classe correspond au nom du fichier
3. L'autoload est à jour : `composer dump-autoload`

## 📊 Métriques de Qualité

### Couverture de Code

```bash
# Générer un rapport de couverture HTML
php bin/phpunit --coverage-html coverage/

# Ouvrir le rapport
# Windows
start coverage/index.html

# Linux/Mac
open coverage/index.html
```

### Analyse Statique

```bash
# PHPStan (si installé)
vendor/bin/phpstan analyse src tests

# Psalm (si installé)
vendor/bin/psalm
```

## 🔄 Intégration Continue

### Exemple GitHub Actions

```yaml
name: Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'
      
      - name: Install dependencies
        run: composer install
      
      - name: Run tests
        run: php bin/phpunit tests/Service/UserManagerTest.php
```

## 📚 Ressources Complémentaires

### Documentation Officielle
- [PHPUnit Documentation](https://phpunit.de/documentation.html)
- [Symfony Testing](https://symfony.com/doc/current/testing.html)
- [Best Practices](https://symfony.com/doc/current/best_practices.html)

### Articles Recommandés
- [Test-Driven Development (TDD)](https://martinfowler.com/bliki/TestDrivenDevelopment.html)
- [Unit Testing Best Practices](https://martinfowler.com/bliki/UnitTest.html)
- [Test Doubles (Mocks, Stubs)](https://martinfowler.com/bliki/TestDouble.html)

### Livres
- "Test Driven Development: By Example" - Kent Beck
- "Growing Object-Oriented Software, Guided by Tests" - Steve Freeman
- "xUnit Test Patterns" - Gerard Meszaros

## 🤝 Contribution

### Ajouter une nouvelle règle métier

1. **Ajouter la règle dans UserManager.php**
```php
public function validateNewRule(Users $user): bool
{
    if (/* condition */) {
        throw new \InvalidArgumentException('Message d\'erreur');
    }
    return true;
}
```

2. **Ajouter les tests dans UserManagerTest.php**
```php
public function testNewRuleValid(): void
{
    // Test cas valide
}

public function testNewRuleInvalid(): void
{
    $this->expectException(\InvalidArgumentException::class);
    // Test cas invalide
}
```

3. **Exécuter les tests**
```bash
php bin/phpunit tests/Service/UserManagerTest.php
```

## ❓ FAQ

### Q: Pourquoi ne pas utiliser de mocks Doctrine ?

**R:** Les tests unitaires doivent être simples et isolés. Mocker Doctrine ajoute de la complexité et peut causer des erreurs de types. Notre approche avec un service pur est plus simple et plus fiable.

### Q: Quelle est la différence entre tests unitaires et tests d'intégration ?

**R:**
- **Tests unitaires** : Testent la logique métier isolée (notre cas)
- **Tests d'intégration** : Testent l'interaction avec la base de données

### Q: Combien de tests dois-je écrire ?

**R:** Un test par règle métier, plus des tests pour les cas limites. Visez une couverture de 80-100% de la logique métier.

### Q: Dois-je tester les getters/setters ?

**R:** Non, sauf s'ils contiennent de la logique métier. Les getters/setters simples n'ont pas besoin de tests.

### Q: Comment tester du code asynchrone ?

**R:** Pour du code asynchrone, utilisez des tests d'intégration avec `KernelTestCase` plutôt que des tests unitaires.

## 📞 Support

Pour toute question ou problème :

1. Consultez la documentation dans `TESTS_UNITAIRES_USERS.md`
2. Lisez l'explication technique dans `EXPLICATION_ERREUR_MOCK.md`
3. Vérifiez les résultats dans `RESULTAT_FINAL.md`

## ✅ Checklist de Validation

Avant de soumettre votre travail, vérifiez :

- [ ] Tous les tests passent (11/11)
- [ ] Le code est bien formaté
- [ ] Les commentaires sont clairs
- [ ] La documentation est à jour
- [ ] Les règles métier sont documentées
- [ ] Les exemples fonctionnent
- [ ] Aucune dépendance inutile

## 🎉 Conclusion

Vous avez maintenant un système de tests unitaires complet et fonctionnel pour l'entité Users. Ce travail démontre :

✅ Compréhension des tests unitaires
✅ Maîtrise de PHPUnit
✅ Bonnes pratiques de développement
✅ Séparation des responsabilités
✅ Code maintenable et évolutif

**Félicitations ! 🎊**
