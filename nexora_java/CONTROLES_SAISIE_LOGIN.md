# Contrôles de Saisie - Authentification et Gestion Utilisateurs

## Vue d'ensemble

Des contrôles de saisie complets ont été ajoutés aux pages de connexion et de gestion des utilisateurs pour garantir l'intégrité des données et améliorer l'expérience utilisateur.

## Fichiers modifiés

### 1. ValidationUtils.java (NOUVEAU)
**Emplacement:** `src/main/java/com/pi/utils/ValidationUtils.java`

Classe utilitaire centralisée pour toutes les validations.

#### Méthodes de validation:

**Validations de base:**
- `isEmpty(String)` - Vérifie si une chaîne est vide ou null
- `isValidNumber(String)` - Vérifie si une chaîne est un nombre valide

**Validations spécifiques:**
- `isValidEmail(String)` - Valide le format email (ex: user@domain.com)
- `isValidPhone(String)` - Valide le numéro de téléphone (8 chiffres)
- `isValidName(String)` - Valide nom/prénom (lettres uniquement, min 2 caractères)
- `isStrongPassword(String)` - Vérifie mot de passe fort (8+ caractères, majuscule, minuscule, chiffre)
- `isValidPasswordLength(String, int)` - Vérifie longueur minimale du mot de passe

**Méthodes de messages d'erreur:**
- `getEmailErrorMessage(String)` - Retourne message d'erreur pour email
- `getPhoneErrorMessage(String)` - Retourne message d'erreur pour téléphone
- `getPasswordErrorMessage(String)` - Retourne message d'erreur pour mot de passe
- `getNameErrorMessage(String, String)` - Retourne message d'erreur pour nom/prénom

### 2. Loginne.java
**Emplacement:** `src/main/java/controller/Loginne.java`

#### Validations ajoutées dans la méthode `login()`:

1. **Email vide**
   - Message: "Veuillez saisir votre email"
   - Focus automatique sur le champ email

2. **Format email invalide**
   - Message: "Format d'email invalide\nExemple: utilisateur@domaine.com"
   - Validation avec regex
   - Focus automatique sur le champ email

3. **Mot de passe vide**
   - Message: "Veuillez saisir votre mot de passe"
   - Focus automatique sur le champ mot de passe

4. **Mot de passe trop court**
   - Message: "Le mot de passe doit contenir au moins 6 caractères"
   - Focus automatique sur le champ mot de passe

### 3. LoginController.java
**Emplacement:** `src/main/java/controller/LoginController.java`

#### Validations ajoutées dans `ajouteruser()`:

Validation complète de tous les champs avec affichage groupé des erreurs:

1. **Nom**
   - Obligatoire
   - Minimum 2 caractères
   - Lettres uniquement (accepte accents, espaces, apostrophes, tirets)

2. **Prénom**
   - Obligatoire
   - Minimum 2 caractères
   - Lettres uniquement

3. **Email**
   - Obligatoire
   - Format valide (ex: user@domain.com)

4. **Téléphone**
   - Obligatoire
   - Chiffres uniquement
   - Exactement 8 chiffres

5. **Rôle**
   - Obligatoire
   - Doit être sélectionné dans la liste

6. **Mot de passe**
   - Obligatoire
   - Minimum 6 caractères

#### Validations ajoutées dans `modifierUserSave()`:

Mêmes validations que l'ajout, avec une exception:
- Le mot de passe est optionnel en modification
- S'il est fourni, il doit respecter la longueur minimale (6 caractères)

## Règles de validation

### Format Email
```
Regex: ^[A-Za-z0-9+_.-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$
Exemples valides:
  ✓ user@example.com
  ✓ john.doe@company.co.uk
  ✓ contact+info@domain.org

Exemples invalides:
  ✗ user@
  ✗ @domain.com
  ✗ user@domain
  ✗ user domain@test.com
```

### Format Téléphone
```
Regex: ^[0-9]{8}$
Exemples valides:
  ✓ 12345678
  ✓ 98765432

Exemples invalides:
  ✗ 123456 (trop court)
  ✗ 123456789 (trop long)
  ✗ 1234-5678 (caractères spéciaux)
  ✗ +216 12345678 (préfixe)
```

### Format Nom/Prénom
```
Regex: ^[a-zA-ZÀ-ÿ\s'-]+$
Longueur minimale: 2 caractères

Exemples valides:
  ✓ Jean
  ✓ Marie-Claire
  ✓ O'Connor
  ✓ José
  ✓ Ben Ali

Exemples invalides:
  ✗ J (trop court)
  ✗ Jean123 (chiffres)
  ✗ Jean@ (caractères spéciaux)
```

### Format Mot de passe

**Minimum (actuel):**
- Au moins 6 caractères

**Recommandé (disponible dans ValidationUtils):**
- Au moins 8 caractères
- Une lettre majuscule
- Une lettre minuscule
- Un chiffre

## Expérience utilisateur

### Affichage des erreurs

**Page de connexion (Loginne):**
- Une erreur à la fois
- Focus automatique sur le champ en erreur
- Message clair et précis

**Gestion utilisateurs (LoginController):**
- Toutes les erreurs affichées ensemble
- Format liste à puces
- Titre: "Erreurs de validation :"

### Exemple d'affichage groupé:
```
Erreurs de validation :

• Le nom doit contenir au moins 2 caractères
• Format d'email invalide (ex: exemple@domaine.com)
• Le numéro doit contenir exactement 8 chiffres
• Veuillez choisir un rôle
```

## Avantages

1. **Sécurité renforcée**
   - Prévention des injections SQL via validation
   - Garantie de formats de données cohérents

2. **Intégrité des données**
   - Données propres et standardisées
   - Réduction des erreurs de saisie

3. **Expérience utilisateur améliorée**
   - Messages d'erreur clairs et explicites
   - Focus automatique sur les champs en erreur
   - Exemples de formats attendus

4. **Maintenabilité**
   - Code centralisé dans ValidationUtils
   - Réutilisable dans tout le projet
   - Facile à modifier et étendre

## Extensions futures possibles

1. **Validation en temps réel**
   - Afficher les erreurs pendant la saisie
   - Indicateurs visuels (bordures rouges/vertes)

2. **Validation côté serveur**
   - Double validation (client + serveur)
   - Protection contre les contournements

3. **Messages personnalisés**
   - Internationalisation (i18n)
   - Messages adaptés au contexte

4. **Validation avancée**
   - Vérification d'unicité email en temps réel
   - Suggestions de correction automatique
   - Force du mot de passe avec indicateur visuel

## Test

Pour tester les validations:

1. **Page de connexion:**
   - Essayer de se connecter sans email
   - Essayer avec un email invalide (ex: "test")
   - Essayer sans mot de passe
   - Essayer avec un mot de passe court (< 6 caractères)

2. **Gestion utilisateurs:**
   - Essayer d'ajouter un utilisateur avec des champs vides
   - Essayer avec un nom d'1 caractère
   - Essayer avec un email invalide
   - Essayer avec un téléphone de 7 chiffres
   - Essayer sans sélectionner de rôle
   - Vérifier que toutes les erreurs s'affichent ensemble
