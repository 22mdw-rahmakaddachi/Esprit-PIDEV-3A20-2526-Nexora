# Fonctionnalité "Remember Me" (Se souvenir de moi)

## Description
La fonctionnalité "Remember Me" permet aux utilisateurs de sauvegarder leurs identifiants de connexion (email et mot de passe) pour une connexion automatique lors des prochaines visites.

## Fichiers modifiés

### 1. RememberMeManager.java
**Emplacement:** `src/main/java/com/pi/utils/RememberMeManager.java`

**Fonctionnalités:**
- `saveCredentials(email, password, rememberMe)` - Sauvegarde l'email et le mot de passe encodé
- `getSavedEmail()` - Récupère l'email sauvegardé
- `getSavedPassword()` - Récupère le mot de passe décodé
- `isRememberMeEnabled()` - Vérifie si la fonctionnalité est activée
- `clearCredentials()` - Efface les informations sauvegardées

**Sécurité:**
- Le mot de passe est encodé en Base64 avant d'être stocké
- Utilise les Preferences Java pour un stockage local sécurisé
- Les données sont stockées dans le registre Windows (ou équivalent selon l'OS)

### 2. loginne.fxml
**Emplacement:** `src/main/resources/loginne.fxml`

**Modifications:**
- Ajout d'une CheckBox "Se souvenir de moi" entre le champ mot de passe et le bouton de connexion
- Import de `javafx.scene.control.CheckBox`

### 3. Loginne.java
**Emplacement:** `src/main/java/controller/Loginne.java`

**Modifications:**
- Ajout du champ `@FXML private CheckBox rememberMeCheckBox`
- Import de `RememberMeManager`
- Dans `initialize()`: Chargement automatique de l'email et du mot de passe au démarrage
- Dans `login()`: Sauvegarde des identifiants après connexion réussie

## Fonctionnement

### Lors de la connexion:
1. L'utilisateur saisit son email et mot de passe
2. Il coche "Se souvenir de moi"
3. Après une connexion réussie, les identifiants sont sauvegardés (mot de passe encodé en Base64)

### Au prochain lancement:
1. L'application charge automatiquement l'email et le mot de passe sauvegardés
2. La CheckBox "Se souvenir de moi" est cochée automatiquement
3. L'utilisateur peut se connecter directement

### Déconnexion de la fonctionnalité:
- Si l'utilisateur décoche "Se souvenir de moi" et se connecte, les informations sauvegardées sont effacées

## Sécurité

⚠️ **Note importante:**
- L'encodage Base64 n'est PAS un chiffrement sécurisé
- Il s'agit d'une obfuscation basique pour éviter que le mot de passe soit visible en clair
- Pour une sécurité renforcée, il est recommandé d'utiliser un chiffrement AES avec une clé dérivée

## Améliorations futures possibles

1. **Chiffrement AES** au lieu de Base64
2. **Expiration automatique** après X jours
3. **Authentification biométrique** (empreinte digitale déjà implémentée)
4. **Token de session** au lieu de stocker le mot de passe

## Test

Pour tester la fonctionnalité:
1. Lancez l'application
2. Connectez-vous avec vos identifiants
3. Cochez "Se souvenir de moi"
4. Fermez l'application
5. Relancez l'application
6. Vérifiez que l'email et le mot de passe sont pré-remplis
