# 🔍 Debug - Tableau des réclamations vide

## Problème
Le tableau des réclamations n'affiche rien alors qu'il y a des réclamations dans la base de données.

## Étapes de diagnostic

### Étape 1 : Vérifier la console

Lancez l'application et regardez la console. Vous devriez voir :

```
🚀 Initialisation reclamationController
✅ Colonnes réclamations configurées
  - tableReclamations: OK
  - colReclamationId: OK
  - colActiviteNom: OK
✅ Colonnes activités configurées
📥 Chargement des réclamations...
  Client ID: X
✅ Réclamations chargées: Y
  - Réclamation #1 | Activité: XXX | Statut: XXX
✅ Tableau mis à jour avec Y items
✅ Initialisation terminée
```

### Étape 2 : Analyser les logs

**Si vous voyez "tableReclamations: NULL"**
- Le fx:id dans le FXML ne correspond pas
- Vérifiez que `fx:id="tableReclamations"` est bien dans le FXML

**Si vous voyez "Réclamations chargées: 0"**
- Aucune réclamation pour ce client
- Exécutez le script SQL de test

**Si vous voyez "Activité: null"**
- Les activités n'existent pas dans la base
- Problème de JOIN dans la requête SQL

**Si vous voyez "Tableau mis à jour avec 0 items"**
- Les items ne sont pas ajoutés au tableau
- Problème avec setItems()

### Étape 3 : Vérifier la base de données

Exécutez ce script SQL :

```sql
-- Voir toutes les réclamations avec activités
SELECT 
    r.id,
    r.client_id,
    r.activite_id,
    r.description,
    r.statut,
    a.nom as activite_nom
FROM reclamation r
LEFT JOIN activite a ON r.activite_id = a.id;
```

**Vérifications :**
- [ ] Des réclamations existent
- [ ] `activite_nom` n'est pas NULL
- [ ] Le `client_id` correspond à l'utilisateur connecté
- [ ] Les statuts sont corrects (EN_ATTENTE, EN_COURS, etc.)

### Étape 4 : Vérifier les colonnes FXML

Ouvrez `reclamatione.fxml` et vérifiez :

```xml
<TableView fx:id="tableReclamations" prefHeight="200">
    <columns>
        <TableColumn fx:id="colReclamationId" text="ID" prefWidth="50"/>
        <TableColumn fx:id="colActiviteNom" text="Activité" prefWidth="200"/>
        <TableColumn fx:id="colDescription" text="Description" prefWidth="300"/>
        <TableColumn fx:id="colStatut" text="Statut" prefWidth="120"/>
        <TableColumn fx:id="colDate" text="Date" prefWidth="150"/>
        <TableColumn fx:id="colActions" text="Actions" prefWidth="180"/>
    </columns>
</TableView>
```

**Vérifications :**
- [ ] `fx:id="tableReclamations"` existe
- [ ] Tous les `fx:id` des colonnes correspondent au contrôleur
- [ ] Le contrôleur est bien `fx:controller="controller.reclamationController"`

### Étape 5 : Vérifier le contrôleur

Dans `reclamationController.java`, vérifiez :

```java
@FXML private TableView<Reclamation> tableReclamations;
@FXML private TableColumn<Reclamation, Integer> colReclamationId;
@FXML private TableColumn<Reclamation, String> colActiviteNom;
// etc.
```

**Vérifications :**
- [ ] Tous les champs ont `@FXML`
- [ ] Les noms correspondent exactement au FXML
- [ ] Les types sont corrects (Integer, String, Void)

## Solutions courantes

### Solution 1 : Réclamations orphelines

Si les activités n'existent pas :

```sql
-- Supprimer les réclamations orphelines
DELETE FROM reclamation 
WHERE activite_id NOT IN (SELECT id FROM activite);
```

### Solution 2 : Forcer le rafraîchissement

Ajoutez dans `afficherReclamations()` :

```java
javafx.application.Platform.runLater(() -> {
    tableReclamations.setItems(list);
    tableReclamations.refresh();
});
```

### Solution 3 : Vérifier l'utilisateur connecté

```java
user currentUser = SessionManager.getCurrentUser();
if (currentUser == null) {
    System.err.println("❌ Aucun utilisateur connecté!");
    return;
}
int clientId = currentUser.getId();
System.out.println("Client ID: " + clientId);
```

### Solution 4 : Test avec données fictives

Ajoutez temporairement dans `initialize()` :

```java
// TEST : Ajouter des données fictives
ObservableList<Reclamation> testList = FXCollections.observableArrayList();
Reclamation test = new Reclamation();
test.setId(999);
test.setActiviteNom("Test Activité");
test.setDescription("Test Description");
test.setStatut("EN_ATTENTE");
test.setDateCreation(new java.sql.Timestamp(System.currentTimeMillis()));
testList.add(test);
tableReclamations.setItems(testList);
System.out.println("✅ Données de test ajoutées");
```

Si les données de test s'affichent, le problème vient de la requête SQL ou des données.

## Checklist de vérification

- [ ] Console affiche les logs d'initialisation
- [ ] "tableReclamations: OK" dans les logs
- [ ] "Réclamations chargées: X" avec X > 0
- [ ] "Tableau mis à jour avec X items" avec X > 0
- [ ] Requête SQL retourne des résultats
- [ ] Les activités existent dans la base
- [ ] L'utilisateur est bien connecté
- [ ] Les fx:id correspondent entre FXML et contrôleur
- [ ] Les colonnes sont bien configurées

## Test manuel

1. Ouvrez la page de réclamations
2. Regardez la console
3. Notez les messages affichés
4. Exécutez le script SQL de test
5. Comparez les résultats

## Si rien ne fonctionne

Essayez cette version simplifiée dans `afficherReclamations()` :

```java
private void afficherReclamations(){
    try {
        int clientId = SessionManager.getCurrentUser().getId();
        List<Reclamation> list = rs.afficher(clientId);
        
        System.out.println("=== DEBUG ===");
        System.out.println("Client ID: " + clientId);
        System.out.println("Réclamations trouvées: " + list.size());
        System.out.println("TableView null? " + (tableReclamations == null));
        
        if (tableReclamations != null && list != null) {
            ObservableList<Reclamation> observableList = FXCollections.observableArrayList(list);
            tableReclamations.setItems(observableList);
            System.out.println("Items dans le tableau: " + tableReclamations.getItems().size());
        }
    } catch (Exception e) {
        System.err.println("ERREUR: " + e.getMessage());
        e.printStackTrace();
    }
}
```

## Contact

Si le problème persiste, fournissez :
1. Les logs complets de la console
2. Le résultat de `SELECT * FROM reclamation LIMIT 3;`
3. Le résultat de `SELECT COUNT(*) FROM reclamation WHERE client_id = X;`
4. Une capture d'écran de l'interface
