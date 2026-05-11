# 📋 Gestion des Réclamations - Version Complète

## Vue d'ensemble

La page de réclamations affiche maintenant :
1. **Un tableau des réclamations existantes** avec actions (modifier/supprimer)
2. **Un formulaire pour créer** de nouvelles réclamations

## Fonctionnalités implémentées

### 1. Tableau des réclamations existantes

**Colonnes affichées :**
- **ID** : Numéro de la réclamation
- **Activité** : Nom de l'activité concernée
- **Description** : Texte de la réclamation
- **Statut** : Badge coloré (En Attente, En Cours, Résolue, Rejetée)
- **Date** : Date et heure de création (format dd/MM/yyyy HH:mm)
- **Actions** : Boutons Modifier et Supprimer

**Statuts avec badges colorés :**
- ⏳ **En Attente** (jaune) - Actions disponibles
- 🔄 **En Cours** (bleu) - Actions disponibles
- ✅ **Résolue** (vert) - Actions désactivées
- ❌ **Rejetée** (rouge) - Actions désactivées

### 2. Actions disponibles

#### ✏️ Modifier
- Ouvre une boîte de dialogue
- Permet de modifier la description
- Validation : description non vide
- Disponible uniquement si statut = EN_ATTENTE ou EN_COURS

#### 🗑️ Supprimer
- Demande confirmation
- Affiche les détails de la réclamation
- Suppression définitive
- Disponible uniquement si statut = EN_ATTENTE ou EN_COURS

#### 🔄 Actualiser
- Bouton en haut à droite du tableau
- Recharge la liste des réclamations
- Affiche un message de confirmation

### 3. Création de nouvelles réclamations

**Processus :**
1. Sélectionner une activité dans le tableau "Créer une Nouvelle Réclamation"
2. Cliquer sur "📝 Réclamer"
3. Rédiger la description
4. Soumettre ou annuler

**Après soumission :**
- Message de succès
- Formulaire masqué
- Liste des réclamations actualisée automatiquement

## Structure de l'interface

```
┌─────────────────────────────────────────────────────────┐
│ 📝 Gestion des Réclamations                             │
│ Consultez vos réclamations existantes ou créez-en...   │
├─────────────────────────────────────────────────────────┤
│ 📋 Mes Réclamations                    [🔄 Actualiser] │
│ ┌────┬──────────┬─────────────┬────────┬──────┬───────┐│
│ │ ID │ Activité │ Description │ Statut │ Date │Actions││
│ ├────┼──────────┼─────────────┼────────┼──────┼───────┤│
│ │ 1  │ Camping  │ Problème... │⏳ En A.│12/01 │✏️ 🗑️ ││
│ │ 2  │ Randonnée│ Mauvais...  │✅ Rés. │10/01 │ - -  ││
│ └────┴──────────┴─────────────┴────────┴──────┴───────┘│
├─────────────────────────────────────────────────────────┤
│ ➕ Créer une Nouvelle Réclamation                       │
│ Sélectionnez une activité à laquelle vous avez...      │
│ ┌──────────────────┬──────────┬────────┐               │
│ │ Nom de l'Activité│   Lieu   │ Action │               │
│ ├──────────────────┼──────────┼────────┤               │
│ │ Camping          │ Jendouba │📝 Réc. │               │
│ │ Randonnée        │ Béja     │📝 Réc. │               │
│ └──────────────────┴──────────┴────────┘               │
└─────────────────────────────────────────────────────────┘
```

## Règles de gestion

### Modification
- ✅ Possible si statut = EN_ATTENTE ou EN_COURS
- ❌ Impossible si statut = RESOLUE ou REJETEE
- Bouton grisé et désactivé pour les réclamations closes

### Suppression
- ✅ Possible si statut = EN_ATTENTE ou EN_COURS
- ❌ Impossible si statut = RESOLUE ou REJETEE
- Confirmation obligatoire avant suppression
- Action irréversible

### Création
- ✅ Toujours possible
- Sélection d'une activité rejointe obligatoire
- Description obligatoire (non vide)
- Statut initial : EN_ATTENTE

## Fichiers modifiés

### 1. reclamatione.fxml
**Ajouts :**
- Tableau des réclamations avec 6 colonnes
- Bouton "Actualiser"
- Séparateurs pour organiser l'interface
- Titre modifié : "Gestion des Réclamations"

### 2. reclamationController.java
**Ajouts :**
- Champs FXML pour le tableau des réclamations
- Méthode `afficherReclamations()` - Charge les réclamations du client
- Méthode `actualiserReclamations()` - Recharge la liste
- Méthode `ajouterBoutonsActions()` - Ajoute les boutons Modifier/Supprimer
- Méthode `modifierReclamation()` - Gère la modification
- Méthode `supprimerReclamation()` - Gère la suppression
- Formatage de la date dans le tableau
- Stylisation des badges de statut
- Désactivation contextuelle des boutons

**Modifications :**
- `initialize()` - Initialise aussi le tableau des réclamations
- `ajouterReclamation()` - Actualise le tableau après ajout

## Utilisation

### Pour consulter ses réclamations
1. Ouvrir la page "Nouvelle Réclamation"
2. Le tableau en haut affiche toutes vos réclamations
3. Voir le statut avec le badge coloré

### Pour modifier une réclamation
1. Trouver la réclamation dans le tableau
2. Cliquer sur "✏️ Modifier"
3. Modifier la description dans la boîte de dialogue
4. Valider

### Pour supprimer une réclamation
1. Trouver la réclamation dans le tableau
2. Cliquer sur "🗑️ Supprimer"
3. Confirmer la suppression

### Pour créer une réclamation
1. Descendre jusqu'au tableau des activités
2. Cliquer sur "📝 Réclamer" pour une activité
3. Rédiger la description
4. Cliquer sur "📤 Soumettre la Réclamation"

### Pour actualiser la liste
1. Cliquer sur "🔄 Actualiser" en haut à droite du tableau

## Avantages

1. **Tout en un** : Consultation et création sur la même page
2. **Visibilité** : Voir immédiatement toutes ses réclamations
3. **Actions rapides** : Modifier ou supprimer en un clic
4. **Feedback visuel** : Badges colorés pour les statuts
5. **Protection** : Impossible de modifier/supprimer les réclamations closes
6. **Actualisation** : Liste mise à jour après chaque action

## Tests recommandés

- [ ] Affichage des réclamations existantes
- [ ] Badges de statut colorés correctement
- [ ] Formatage de la date (dd/MM/yyyy HH:mm)
- [ ] Bouton Modifier fonctionne (EN_ATTENTE)
- [ ] Bouton Supprimer fonctionne (EN_ATTENTE)
- [ ] Boutons désactivés pour RESOLUE
- [ ] Boutons désactivés pour REJETEE
- [ ] Création d'une nouvelle réclamation
- [ ] Actualisation automatique après création
- [ ] Bouton Actualiser fonctionne
- [ ] Confirmation avant suppression

## Notes techniques

**ReclamationService.java :**
- Méthode `afficher(clientId)` utilisée pour charger les réclamations
- Méthode `modifier(reclamation)` pour la modification
- Méthode `supprimer(clientId, activiteId)` pour la suppression

**Formatage des dates :**
```java
SimpleDateFormat format = new SimpleDateFormat("dd/MM/yyyy HH:mm");
```

**Désactivation des boutons :**
```java
boolean disabled = reclamation.getStatut().equals("RESOLUE") || 
                   reclamation.getStatut().equals("REJETEE");
btnModifier.setDisable(disabled);
btnSupprimer.setDisable(disabled);
```

## Améliorations futures possibles

1. **Filtre par statut** : ComboBox pour filtrer
2. **Recherche** : Champ de recherche dans les descriptions
3. **Tri** : Cliquer sur les en-têtes pour trier
4. **Export** : Exporter la liste en PDF
5. **Notifications** : Badge pour nouvelles réponses
6. **Historique** : Voir les modifications apportées
7. **Pièces jointes** : Ajouter des images/documents
