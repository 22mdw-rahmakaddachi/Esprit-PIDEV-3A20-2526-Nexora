# Tests Unitaires - Entité Users

## 📋 Résumé

Ce document présente l'implémentation des tests unitaires pour l'entité `Users` conformément aux exigences du workshop Symfony.

## 🎯 Règles Métier Validées

Pour l'entité `Users`, les règles métier suivantes ont été définies et testées :

1. **Le nom de l'utilisateur est obligatoire**
2. **Le prénom de l'utilisateur est obligatoire**
3. **L'email doit être valide** (format email correct)
4. **Le mot de passe doit contenir au moins 8 caractères**
5. **Le numéro de téléphone ne peut pas être négatif**

## 📁 Structure des Fichiers

```
src/
└── Service/
    └── UserManager.php          # Service métier avec les règles de validation

tests/
└── Service/
    └── UserManagerTest.php      # Tests unitaires
```

## 🔧 Service Métier (UserManager.php)

Le service `UserManager` contient plusieurs méthodes :

### Méthode `validate(Users $user): bool`
Valide toutes les règles métier définies :
- Vérifie que le nom n'est pas vide
- Vérifie que le prénom n'est pas vide
- Valide le format de l'email avec `FILTER_VALIDATE_EMAIL`
- Vérifie que le mot de passe contient au moins 8 caractères
- Vérifie que le numéro de téléphone n'est pas négatif

### Autres méthodes utiles
- `getFullName(Users $user)` : Retourne le nom complet
- `isBlocked(Users $user)` : Vérifie si l'utilisateur est bloqué
- `hasExceededAttempts(Users $user, int $maxAttempts)` : Vérifie les tentatives de connexion

## ✅ Tests Implémentés

### 1. `testValidUser()`
Vérifie qu'un utilisateur avec toutes les données valides passe la validation.

```php
$user = new Users();
$user->setNom('Dupont');
$user->setPrenom('Jean');
$user->setEmail('jean.dupont@example.com');
$user->setMdp('motdepasse123');
$user->setNum(12345678);

$this->assertTrue($this->userManager->validate($user));
```

### 2. `testUserWithoutNom()`
Vérifie qu'une exception est levée si le nom est vide.

### 3. `testUserWithoutPrenom()`
Vérifie qu'une exception est levée si le prénom est vide.

### 4. `testUserWithInvalidEmail()`
Vérifie qu'une exception est levée si l'email est invalide.

### 5. `testUserWithShortPassword()`
Vérifie qu'une exception est levée si le mot de passe contient moins de 8 caractères.

### 6. `testUserWithNegativeNumber()`
Vérifie qu'une exception est levée si le numéro de téléphone est négatif.

### 7. `testGetFullName()`
Vérifie que le nom complet est correctement formaté.

### 8. `testUserNotBlocked()`
Vérifie qu'un utilisateur non bloqué retourne `false`.

### 9. `testUserIsBlocked()`
Vérifie qu'un utilisateur bloqué retourne `true`.

### 10. `testUserHasExceededAttempts()`
Vérifie la détection du dépassement de tentatives de connexion.

### 11. `testUserHasNotExceededAttempts()`
Vérifie qu'un utilisateur avec peu de tentatives retourne `false`.

## 🚀 Exécution des Tests

Pour exécuter les tests unitaires :

```bash
php bin/phpunit tests/Service/UserManagerTest.php
```

### Résultat Attendu

```
PHPUnit 9.6.34 by Sebastian Bergmann and contributors.

Testing App\Tests\Service\UserManagerTest
...........                                                       11 / 11 (100%)

Time: 00:00.141, Memory: 10.00 MB

OK (11 tests, 16 assertions)
```

- **11 tests** : Tous les scénarios de test
- **16 assertions** : Toutes les vérifications effectuées
- **OK** : Tous les tests passent avec succès

## 📝 Explication Technique

### Pourquoi ce test fonctionne ?

Le test précédent échouait avec l'erreur :
```
IncompatibleReturnValueException: Method getRepository may not return value 
of type Mock_ObjectRepository, its declared return type is "Doctrine\ORM\EntityRepository"
```

**Problème** : Vous essayiez de mocker `EntityManager` et `ObjectRepository`, ce qui créait des conflits de types avec les déclarations strictes de Doctrine.

**Solution** : Créer un service métier simple (`UserManager`) qui ne dépend pas de Doctrine pour les tests unitaires. Ce service :
- Prend une entité `Users` en paramètre
- Applique les règles métier
- Lance des exceptions `InvalidArgumentException` en cas d'erreur
- Retourne `true` si tout est valide

Cette approche suit le principe des **tests unitaires purs** :
- ✅ Pas de dépendances externes (base de données, EntityManager)
- ✅ Tests rapides et isolés
- ✅ Faciles à maintenir
- ✅ Pas besoin de mocks complexes

## 🎓 Conformité avec le Workshop

Ce travail respecte toutes les exigences du workshop :

1. ✅ Choix d'une entité du projet (`Users`)
2. ✅ Identification de règles métier (5 règles définies)
3. ✅ Création d'un service métier (`UserManager`)
4. ✅ Génération de la structure de test (créée manuellement)
5. ✅ Implémentation des tests unitaires (11 tests)
6. ✅ Vérification de l'exécution (tous les tests passent)

## 💡 Bonnes Pratiques Appliquées

1. **Méthode `setUp()`** : Initialise le service avant chaque test
2. **Nommage clair** : Chaque test décrit clairement ce qu'il teste
3. **Assertions précises** : Utilisation de `expectException` et `expectExceptionMessage`
4. **Tests isolés** : Chaque test est indépendant
5. **Couverture complète** : Tous les cas (valides et invalides) sont testés

## 📚 Références

- [Documentation PHPUnit](https://phpunit.de/documentation.html)
- [Tests Unitaires Symfony](https://symfony.com/doc/current/testing.html)
- [Best Practices Testing](https://symfony.com/doc/current/best_practices.html#tests)
