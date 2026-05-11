# 🔧 Résolution des Conflits de Merge

## 📊 Conflits Détectés

### Fichiers en Conflit
1. `pom.xml` - Dépendances Maven
2. `Loginne.java` - Contrôleur de connexion
3. `DashboardClientController.java` - Dashboard client
4. `Paiement.java` - Entité paiement
5. `PaiementService.java` - Service paiement
6. `SessionManager.java` - Gestion session
7. `DashboardClient.fxml` - Interface dashboard
8. `loginne.fxml` - Interface login
9. `page.fxml` - Page principale
10. `style.css` - Styles CSS

---

## ✅ STRATÉGIE DE RÉSOLUTION

### Option 1: Garder VOTRE version (ecommerce)
```bash
# Pour chaque fichier en conflit
git checkout --ours <fichier>
git add <fichier>
```

### Option 2: Garder LEUR version (user)
```bash
# Pour chaque fichier en conflit
git checkout --theirs <fichier>
git add <fichier>
```

### Option 3: Fusionner manuellement
Éditer chaque fichier et choisir les parties à garder

---

## 🚀 COMMANDES RAPIDES

### Résoudre TOUS les conflits en gardant votre version
```bash
git checkout --ours pom.xml
git checkout --ours src/main/java/com/pi/entities/Paiement.java
git checkout --ours src/main/java/com/pi/entity/PaiementService.java
git checkout --ours src/main/java/com/pi/utils/SessionManager.java
git checkout --ours src/main/java/controller/DashboardClientController.java
git checkout --ours src/main/java/controller/Loginne.java
git checkout --ours src/main/resources/DashboardClient.fxml
git checkout --ours src/main/resources/loginne.fxml
git checkout --ours src/main/resources/page.fxml
git checkout --ours src/main/resources/style.css

git add .
git commit -m "fix: Résolution conflits merge - version ecommerce"
```

### Résoudre en gardant leur version
```bash
git checkout --theirs pom.xml
git checkout --theirs src/main/java/com/pi/entities/Paiement.java
git checkout --theirs src/main/java/com/pi/entity/PaiementService.java
git checkout --theirs src/main/java/com/pi/utils/SessionManager.java
git checkout --theirs src/main/java/controller/DashboardClientController.java
git checkout --theirs src/main/java/controller/Loginne.java
git checkout --theirs src/main/resources/DashboardClient.fxml
git checkout --theirs src/main/resources/loginne.fxml
git checkout --theirs src/main/resources/page.fxml
git checkout --theirs src/main/resources/style.css

git add .
git commit -m "fix: Résolution conflits merge - version user"
```

---

## 💡 RECOMMANDATION

### Pour un projet e-commerce fonctionnel:

**Garder VOTRE version** (--ours) pour:
- ✅ `pom.xml` (vos dépendances e-commerce)
- ✅ `DashboardClientController.java` (votre dashboard avec chatbot)
- ✅ `DashboardClient.fxml` (votre interface avec bouton chatbot)
- ✅ `style.css` (vos styles e-commerce)

**Garder LEUR version** (--theirs) pour:
- ✅ `Loginne.java` (leur système d'auth avec BCrypt)
- ✅ `loginne.fxml` (leur interface de login)
- ✅ `SessionManager.java` (leur gestion de session)
- ✅ `Paiement.java` / `PaiementService.java` (si différent)

---

## 🔄 FUSION INTELLIGENTE

### Étape 1: Garder les fonctionnalités des deux
```bash
# 1. Garder votre pom.xml mais ajouter leurs dépendances
git checkout --ours pom.xml

# 2. Garder leur système d'auth
git checkout --theirs src/main/java/controller/Loginne.java
git checkout --theirs src/main/resources/loginne.fxml

# 3. Garder votre dashboard e-commerce
git checkout --ours src/main/java/controller/DashboardClientController.java
git checkout --ours src/main/resources/DashboardClient.fxml

# 4. Fusionner les styles
git checkout --ours src/main/resources/style.css

# 5. Vérifier les entités
# Si Paiement.java est différent, choisir la version la plus complète
```

### Étape 2: Ajouter et commiter
```bash
git add .
git commit -m "fix: Fusion branches ecommerce + user

- Système e-commerce complet (variants, codes promo, chatbot)
- Système d'authentification amélioré (BCrypt, forgot password)
- Dashboard client avec bouton chatbot flottant
- Intégration paiement Konnect
- Gestion activités et notifications"
```

---

## ⚠️ APRÈS LA RÉSOLUTION

### Vérifier la compilation
```bash
mvn clean compile
```

### Tester l'application
```bash
mvn javafx:run
```

### Si erreurs de compilation
```bash
# Voir les erreurs
mvn compile

# Corriger les imports manquants
# Corriger les méthodes manquantes
# Vérifier les dépendances dans pom.xml
```

---

## 📝 CHECKLIST

- [ ] Conflits résolus dans pom.xml
- [ ] Conflits résolus dans Loginne.java
- [ ] Conflits résolus dans DashboardClientController.java
- [ ] Conflits résolus dans les fichiers FXML
- [ ] Conflits résolus dans style.css
- [ ] Compilation réussie (`mvn clean compile`)
- [ ] Application lance (`mvn javafx:run`)
- [ ] Login fonctionne
- [ ] Dashboard client s'affiche
- [ ] Chatbot accessible
- [ ] Commit effectué

---

## 🆘 EN CAS DE PROBLÈME

### Annuler le merge
```bash
git merge --abort
```

### Recommencer
```bash
# Retour à l'état avant merge
git reset --hard HEAD~1

# Refaire le merge
git merge origin/user
```

---

**Bonne résolution! 🎉**


## MISE À JOUR - Corrections Supplémentaires

### Problème Panier
- Code vérifié, semble correct
- À tester : cliquer sur "🛒 Mon Panier" et vérifier la console

### Gestion Activités Client
- ✅ Controller corrigé dans `homeactivite.fxml`
- ✅ Changé de `HomeController` → `ActivitesClientController`
- ✅ Accessible via bouton "🎯 Activités" dans le menu

### Table Paiement Unifiée
- ✅ Supporte e-commerce ET activités
- ✅ Fichier SQL : `migration_table_paiement_unifiee.sql`
- À exécuter dans phpMyAdmin avant de tester
