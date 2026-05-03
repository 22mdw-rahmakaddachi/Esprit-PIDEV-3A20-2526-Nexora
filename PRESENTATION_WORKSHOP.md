# 🎤 Présentation Workshop - Tests Unitaires

## 📋 Introduction (1 minute)

Bonjour, je vais vous présenter mon travail sur les tests unitaires dans Symfony.

**Contexte** : <cite index="1-2,1-3">La phase de test intervient après la phase de développement et a pour objectif de valider et de sécuriser le code réalisé avant la livraison du projet.</cite>

**Objectif** : <cite index="1-7">Les tests unitaires permettent de valider les règles métier, de sécuriser les évolutions et d'améliorer la qualité globale du projet.</cite>

## 🎯 Entité Choisie (1 minute)

J'ai choisi l'entité **Users** qui représente les utilisateurs de notre système.

**Attributs principaux** :
- `nom` : Nom de famille
- `prenom` : Prénom
- `email` : Adresse email
- `mdp` : Mot de passe
- `num` : Numéro de téléphone
- `tentative` : Nombre de tentatives de connexion
- `blockUntil` : Date de fin de blocage

## 📝 Règles Métier Identifiées (2 minutes)

J'ai défini **5 règles métier** à valider :

### 1. Le nom est obligatoire
```php
if (empty($user->getNom())) {
    throw new \InvalidArgumentException('Le nom est obligatoire');
}
```

### 2. Le prénom est obligatoire
```php
if (empty($user->getPrenom())) {
    throw new \InvalidArgumentException('Le prénom est obligatoire');
}
```

### 3. L'email doit être valide
```php
if (!filter_var($user->getEmail(), FILTER_VALIDATE_EMAIL)) {
    throw new \InvalidArgumentException('Email invalide');
}
```

### 4. Le mot de passe doit contenir au moins 8 caractères
```php
if (strlen($user->getMdp()) < 8) {
    throw new \InvalidArgumentException(
        'Le mot de passe doit contenir au moins 8 caractères'
    );
}
```

### 5. Le numéro de téléphone ne peut pas être négatif
```php
if ($user->getNum() < 0) {
    throw new \InvalidArgumentException(
        'Le numéro de téléphone ne peut pas être négatif'
    );
}
```

## 🔧 Service Métier (2 minutes)

J'ai créé le service `UserManager` dans `src/Service/UserManager.php`.

**Méthodes principales** :

### `validate(Users $user): bool`
Valide toutes les règles métier et lance une exception si une règle n'est pas respectée.

### Méthodes utilitaires
- `getFullName()` : Retourne le nom complet
- `isBlocked()` : Vérifie si l'utilisateur est bloqué
- `hasExceededAttempts()` : Vérifie les tentatives de connexion

**Avantage** : Ce service est **indépendant de Doctrine**, ce qui facilite les tests unitaires.

## ✅ Tests Implémentés (3 minutes)

J'ai créé `tests/Service/UserManagerTest.php` avec **11 tests** :

### Tests de validation (6 tests)
1. ✅ `testValidUser()` - Utilisateur avec toutes les données valides
2. ✅ `testUserWithoutNom()` - Exception si nom vide
3. ✅ `testUserWithoutPrenom()` - Exception si prénom vide
4. ✅ `testUserWithInvalidEmail()` - Exception si email invalide
5. ✅ `testUserWithShortPassword()` - Exception si mot de passe < 8 caractères
6. ✅ `testUserWithNegativeNumber()` - Exception si numéro négatif

### Tests fonctionnels (5 tests)
7. ✅ `testGetFullName()` - Formatage du nom complet
8. ✅ `testUserNotBlocked()` - Utilisateur non bloqué
9. ✅ `testUserIsBlocked()` - Utilisateur bloqué
10. ✅ `testUserHasExceededAttempts()` - Trop de tentatives
11. ✅ `testUserHasNotExceededAttempts()` - Tentatives OK

### Exemple de test
```php
public function testUserWithInvalidEmail(): void
{
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('Email invalide');

    $user = new Users();
    $user->setNom('Dupont');
    $user->setPrenom('Jean');
    $user->setEmail('email_invalide');  // ❌ Email invalide
    $user->setMdp('motdepasse123');
    $user->setNum(12345678);

    $this->userManager->validate($user);
}
```

## 🚀 Exécution et Résultats (2 minutes)

### Commande d'exécution
```bash
php bin/phpunit tests/Service/UserManagerTest.php --testdox
```

### Résultat
```
User Manager (App\Tests\Service\UserManager)
 ✔ Valid user
 ✔ User without nom
 ✔ User without prenom
 ✔ User with invalid email
 ✔ User with short password
 ✔ User with negative number
 ✔ Get full name
 ✔ User not blocked
 ✔ User is blocked
 ✔ User has exceeded attempts
 ✔ User has not exceeded attempts

Time: 00:00.141, Memory: 10.00 MB

OK (11 tests, 16 assertions)
```

**Résultat** : ✅ **100% de réussite** - 11 tests, 16 assertions, 0 erreur !

## 💡 Difficultés Rencontrées (2 minutes)

### Problème Initial
Au début, j'ai essayé de mocker `EntityManager` et `ObjectRepository`, ce qui a causé une erreur :

```
IncompatibleReturnValueException: Method getRepository may not return 
value of type Mock_ObjectRepository
```

### Solution Trouvée
Au lieu de mocker Doctrine, j'ai créé un **service métier pur** qui :
- Ne dépend pas de la base de données
- Contient uniquement la logique métier
- Est facile à tester

**Principe** : <cite index="1-5,1-6">Les tests unitaires permettent de vérifier, de manière simple et isolée, la logique interne du code avant d'aborder des tests plus globaux.</cite>

### Avantages de cette approche
1. ✅ **Simplicité** : Pas de mocks complexes
2. ✅ **Rapidité** : Tests ultra-rapides (141ms)
3. ✅ **Fiabilité** : Aucune dépendance externe
4. ✅ **Maintenabilité** : Code clair et facile à comprendre

## 📊 Comparaison : Tests Unitaires vs Tests d'Intégration (1 minute)

| Critère | Tests Unitaires | Tests d'Intégration |
|---------|----------------|---------------------|
| **Cible** | Logique métier | Interaction avec la DB |
| **Dépendances** | Aucune | Base de données |
| **Vitesse** | Très rapide | Plus lent |
| **Classe de base** | `TestCase` | `KernelTestCase` |
| **Exemple** | Validation email | Requête SQL |

**Mon choix** : Tests unitaires car ils testent la logique métier de manière isolée.

## 🎓 Apprentissages (1 minute)

### Techniques apprises
1. ✅ Création de services métier testables
2. ✅ Utilisation de PHPUnit (`TestCase`, `expectException`)
3. ✅ Séparation logique métier / infrastructure
4. ✅ Écriture de tests clairs et descriptifs

### Bonnes pratiques appliquées
- **KISS** : Keep It Simple, Stupid
- **DRY** : Don't Repeat Yourself (méthode `setUp()`)
- **SOLID** : Single Responsibility Principle
- **Tests indépendants** : Chaque test peut s'exécuter seul

## 🔮 Perspectives d'Amélioration (1 minute)

### Court terme
1. Ajouter des règles de complexité du mot de passe
2. Valider le format du numéro de téléphone
3. Tester les cas limites (caractères spéciaux, etc.)

### Moyen terme
1. Mesurer la couverture de code
2. Créer des tests d'intégration pour la persistance
3. Ajouter des tests de performance

### Long terme
1. Intégration dans un pipeline CI/CD
2. Tests de charge et de stress
3. Tests de sécurité

## 📚 Conclusion (1 minute)

### Récapitulatif
- ✅ Entité choisie : **Users**
- ✅ Règles métier : **5 règles identifiées**
- ✅ Service créé : **UserManager**
- ✅ Tests implémentés : **11 tests**
- ✅ Résultat : **100% de réussite**

### Bénéfices
<cite index="1-27">Les tests unitaires permettent de valider la logique métier et de sécuriser le projet avant la livraison finale.</cite>

**Impact** :
- Code plus fiable
- Maintenance facilitée
- Détection précoce des bugs
- Documentation vivante du code

### Message final
Les tests unitaires sont un **investissement** qui :
- Réduit les bugs en production
- Facilite les évolutions futures
- Améliore la confiance dans le code

**Merci pour votre attention !** 🎉

---

## 📎 Annexes

### Fichiers créés
- `src/Service/UserManager.php`
- `tests/Service/UserManagerTest.php`
- `TESTS_UNITAIRES_USERS.md`
- `EXPLICATION_ERREUR_MOCK.md`
- `RESULTAT_FINAL.md`

### Commandes utiles
```bash
# Exécuter les tests
php bin/phpunit tests/Service/UserManagerTest.php

# Format lisible
php bin/phpunit tests/Service/UserManagerTest.php --testdox

# Avec couverture
php bin/phpunit --coverage-html coverage/
```

### Ressources
- [Documentation PHPUnit](https://phpunit.de/)
- [Symfony Testing](https://symfony.com/doc/current/testing.html)
- [Best Practices](https://symfony.com/doc/current/best_practices.html)
