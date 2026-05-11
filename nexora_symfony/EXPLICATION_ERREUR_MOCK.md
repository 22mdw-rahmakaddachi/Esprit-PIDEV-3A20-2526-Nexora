# 🔍 Explication de l'Erreur de Mock et Solution

## ❌ L'Erreur Rencontrée

```
PHPUnit\Framework\MockObject\IncompatibleReturnValueException: 
Method getRepository may not return value of type Mock_ObjectRepository_85ace631, 
its declared return type is "Doctrine\ORM\EntityRepository"
```

## 🤔 Pourquoi Cette Erreur ?

### Le Problème avec les Mocks Doctrine

Lorsque vous essayez de mocker `EntityManager` et `ObjectRepository` dans PHPUnit, vous rencontrez un conflit de types :

```php
// ❌ APPROCHE PROBLÉMATIQUE
$entityManager = $this->createMock(EntityManagerInterface::class);
$repository = $this->createMock(ObjectRepository::class);

$entityManager->method('getRepository')
    ->willReturn($repository);  // ⚠️ Erreur de type !
```

**Pourquoi ça échoue ?**

1. `EntityManager::getRepository()` est déclaré pour retourner `EntityRepository`
2. Votre mock retourne `Mock_ObjectRepository` (type différent)
3. PHP avec typage strict rejette cette incompatibilité

### Doctrine et les Types Stricts

Depuis Symfony 6.x et Doctrine ORM 2.x/3.x, les déclarations de types sont strictes :

```php
// Dans Doctrine\ORM\EntityManagerInterface
public function getRepository(string $className): EntityRepository;
```

Le mock de PHPUnit crée un objet de type `Mock_ObjectRepository` qui n'est **pas** compatible avec `EntityRepository`.

## ✅ La Solution : Tests Unitaires Sans Doctrine

### Principe des Tests Unitaires Purs

<cite index="1-5,1-6">Les tests unitaires constituent la première étape de la phase de test et permettent de vérifier, de manière simple et isolée, la logique interne du code avant d'aborder des tests plus globaux.</cite>

**Un vrai test unitaire doit :**
- ✅ Tester une seule unité de code (une classe, une méthode)
- ✅ Être isolé (pas de base de données, pas de services externes)
- ✅ Être rapide (millisecondes)
- ✅ Être reproductible (même résultat à chaque exécution)

### Notre Solution

Au lieu de mocker Doctrine, nous créons un **service métier simple** :

```php
// ✅ APPROCHE CORRECTE
class UserManager
{
    public function validate(Users $user): bool
    {
        if (empty($user->getNom())) {
            throw new \InvalidArgumentException('Le nom est obligatoire');
        }
        
        if (!filter_var($user->getEmail(), FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Email invalide');
        }
        
        return true;
    }
}
```

**Avantages :**
- ✅ Pas de dépendances Doctrine
- ✅ Pas de mocks complexes
- ✅ Tests rapides et fiables
- ✅ Code facile à maintenir

### Le Test Devient Simple

```php
public function testValidUser(): void
{
    $user = new Users();
    $user->setNom('Dupont');
    $user->setEmail('jean.dupont@example.com');
    
    $manager = new UserManager();
    $this->assertTrue($manager->validate($user));
}
```

**Aucun mock nécessaire !** 🎉

## 📊 Comparaison des Approches

| Critère | Avec Mocks Doctrine ❌ | Sans Doctrine ✅ |
|---------|----------------------|------------------|
| **Complexité** | Élevée (mocks multiples) | Faible (instanciation simple) |
| **Fiabilité** | Erreurs de types fréquentes | Stable et prévisible |
| **Vitesse** | Lente (setup complexe) | Rapide (pas de dépendances) |
| **Maintenance** | Difficile (couplage fort) | Facile (code découplé) |
| **Type de test** | Test d'intégration | Vrai test unitaire |

## 🎯 Quand Utiliser Chaque Approche ?

### Tests Unitaires (Notre Solution)
**Utilisez quand :**
- Vous testez la **logique métier** (validation, calculs, transformations)
- Vous voulez des tests **rapides et isolés**
- Vous n'avez **pas besoin** de la base de données

**Exemples :**
- Validation de données
- Calculs de prix
- Formatage de texte
- Règles métier

### Tests d'Intégration (Avec Doctrine)
**Utilisez quand :**
- Vous testez l'**interaction avec la base de données**
- Vous testez des **requêtes complexes**
- Vous voulez vérifier la **persistance des données**

**Exemples :**
- Requêtes DQL/SQL personnalisées
- Relations entre entités
- Transactions
- Contraintes de base de données

Pour les tests d'intégration, utilisez `KernelTestCase` au lieu de `TestCase` :

```php
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UserRepositoryTest extends KernelTestCase
{
    public function testFindByEmail(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        
        $repository = $container->get(UsersRepository::class);
        $user = $repository->findOneBy(['email' => 'test@example.com']);
        
        $this->assertNotNull($user);
    }
}
```

## 💡 Règles d'Or pour les Tests Unitaires

1. **KISS (Keep It Simple, Stupid)**
   - Si vous avez besoin de 10 lignes de mocks, ce n'est plus un test unitaire

2. **Testez la logique, pas l'infrastructure**
   - La logique métier = tests unitaires
   - L'infrastructure (DB, API) = tests d'intégration

3. **Un test = un concept**
   - Testez une seule règle métier par test
   - Nommage clair : `testUserWithoutNom()`

4. **Indépendance**
   - Chaque test doit pouvoir s'exécuter seul
   - Pas d'ordre d'exécution requis

5. **Rapidité**
   - Un test unitaire doit prendre < 100ms
   - Si c'est plus long, c'est probablement un test d'intégration

## 📚 Ressources Complémentaires

- [PHPUnit Best Practices](https://phpunit.de/documentation.html)
- [Symfony Testing Guide](https://symfony.com/doc/current/testing.html)
- [Test Doubles (Mocks, Stubs, Fakes)](https://martinfowler.com/bliki/TestDouble.html)
- [Unit Testing vs Integration Testing](https://martinfowler.com/bliki/UnitTest.html)

## 🎓 Conclusion

<cite index="1-27">Les tests unitaires permettent de valider la logique métier et de sécuriser le projet avant la livraison finale.</cite>

La clé du succès : **séparer la logique métier de l'infrastructure**. Votre service `UserManager` contient la logique, et peut être testé sans Doctrine. Plus tard, vous pourrez créer des tests d'intégration pour vérifier que tout fonctionne ensemble avec la base de données.

**Résultat :** 11 tests, 16 assertions, 0 erreur ! ✅
