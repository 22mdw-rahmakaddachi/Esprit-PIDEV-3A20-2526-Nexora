# Guide : Table Paiement Unifiée

## Problème Résolu

Vous aviez 2 tables `paiement` incompatibles après le merge :
- **Votre table e-commerce** : pour les paiements de commandes produits
- **Table de votre collègue** : pour les paiements d'activités

## Solution : Table Unifiée

Une seule table `paiement` qui supporte les DEUX cas d'usage.

### Structure de la Table

```sql
CREATE TABLE `paiement` (
  `id` int NOT NULL AUTO_INCREMENT,
  
  -- Pour E-commerce
  `commande_id` int DEFAULT NULL,
  
  -- Pour Activités
  `demande_id` int DEFAULT NULL,
  `client_id` int DEFAULT NULL,
  `activite_id` int DEFAULT NULL,
  
  -- Champs communs
  `montant` decimal(10,2) NOT NULL,
  `methode_paiement` varchar(100) NOT NULL,
  `statut` varchar(50) DEFAULT 'EN_ATTENTE',
  `date_paiement` datetime DEFAULT NULL,
  `transaction_id` varchar(255) DEFAULT NULL,
  `reference_externe` varchar(255) DEFAULT NULL,
  `reference_transaction` varchar(255) DEFAULT NULL,
  `details_json` text DEFAULT NULL,
  
  PRIMARY KEY (`id`)
)
```

## Utilisation

### Pour E-commerce (Commandes Produits)

```java
Paiement paiement = new Paiement();
paiement.setCommandeId(commandeId);  // ✅ Remplir commande_id
// demande_id, client_id, activite_id restent NULL
paiement.setMontant(montant);
paiement.setMethodePaiement("KONNECT");
paiement.setStatut("VALIDE");
paiement.setReferenceExterne("REF-12345");
paiementService.creerPaiement(paiement);
```

### Pour Activités

```java
Paiement paiement = new Paiement();
// commande_id reste à 0 ou NULL
paiement.setDemandeId(demandeId);      // ✅ Remplir demande_id
paiement.setClientId(clientId);        // ✅ Remplir client_id
paiement.setActiviteId(activiteId);    // ✅ Remplir activite_id
paiement.setMontant(montant);
paiement.setMethodePaiement("CARTE_BANCAIRE");
paiement.setStatut("COMPLETE");
paiement.setReferenceTransaction("REF-67890");
paiementService.creerPaiement(paiement);
```

## Migration

1. **Exécuter le script SQL** : `migration_table_paiement_unifiee.sql`
2. **Classe Paiement.java** : Déjà mise à jour ✅
3. **PaiementService.java** : Déjà mis à jour ✅
4. **Controllers** : Déjà corrigés ✅

## Compatibilité

- `getReferenceExterne()` et `getReferenceTransaction()` sont synchronisés
- Vous pouvez utiliser l'un ou l'autre, ils retournent la même valeur
- Le code existant continue de fonctionner

## Fichiers Modifiés

- ✅ `src/main/java/com/pi/entities/Paiement.java`
- ✅ `src/main/java/com/pi/entity/PaiementService.java`
- ✅ `src/main/java/controller/ActivitesClientController.java`
- ✅ `src/main/java/controller/InterfacePaiementController.java`
- ✅ `migration_table_paiement_unifiee.sql` (nouveau)
