# 🎓 Guide d'utilisation - Validations

## Scénarios d'utilisation

### Scénario 1 : Connexion avec email invalide

**Action utilisateur :**
```
Email: "test"
Mot de passe: "password123"
Clic sur "Se connecter"
```

**Résultat :**
```
❌ Erreur affichée :
"Format d'email invalide
Exemple: utilisateur@domaine.com"

✓ Focus automatique sur le champ Email
```

---

### Scénario 2 : Connexion avec mot de passe court

**Action utilisateur :**
```
Email: "user@example.com"
Mot de passe: "12345"
Clic sur "Se connecter"
```

**Résultat :**
```
❌ Erreur affichée :
"Le mot de passe doit contenir au moins 6 caractères"

✓ Focus automatique sur le champ Mot de passe
```

---

### Scénario 3 : Ajout utilisateur avec plusieurs erreurs

**Action utilisateur :**
```
Nom: "J"
Prénom: "Marie123"
Email: "marie@"
Téléphone: "123"
Rôle: (non sélectionné)
Mot de passe: "pass"
Clic sur "ENREGISTRER"
```

**Résultat :**
```
❌ Erreur affichée :
"Erreurs de validation :

• Le nom doit contenir au moins 2 caractères
• Le prénom ne doit contenir que des lettres
• Format d'email invalide (ex: exemple@domaine.com)
• Le numéro doit contenir exactement 8 chiffres
• Veuillez choisir un rôle
• Le mot de passe doit contenir au moins 6 caractères"
```

---

### Scénario 4 : Ajout utilisateur réussi

**Action utilisateur :**
```
Nom: "Dupont"
Prénom: "Marie"
Email: "marie.dupont@example.com"
Téléphone: "12345678"
Rôle: "Client"
Mot de passe: "password123"
Clic sur "ENREGISTRER"
```

**Résultat :**
```
✅ Succès :
"Utilisateur ajouté avec succès"

✓ Formulaire fermé
✓ Tableau mis à jour
✓ Champs réinitialisés
```

---

### Scénario 5 : Modification utilisateur (mot de passe optionnel)

**Action utilisateur :**
```
Clic sur "Modifier" pour un utilisateur existant
Modification du téléphone: "87654321"
Mot de passe: (laissé vide)
Clic sur "METTRE À JOUR"
```

**Résultat :**
```
✅ Succès :
"Utilisateur modifié avec succès"

✓ Le mot de passe n'est pas modifié (car laissé vide)
✓ Seul le téléphone est mis à jour
```

---

## Code d'exemple pour développeurs

### Validation simple d'un champ

```java
// Valider un email
String email = emailField.getText().trim();

if (ValidationUtils.isEmpty(email)) {
    showError("L'email est obligatoire");
    emailField.requestFocus();
    return;
}

if (!ValidationUtils.isValidEmail(email)) {
    showError("Format d'email invalide");
    emailField.requestFocus();
    return;
}

// Email valide, continuer...
```

### Validation avec message personnalisé

```java
// Utiliser les méthodes de messages d'erreur
String email = emailField.getText().trim();
String emailError = ValidationUtils.getEmailErrorMessage(email);

if (!emailError.isEmpty()) {
    showError(emailError);
    emailField.requestFocus();
    return;
}

// Email valide, continuer...
```

### Validation multiple avec accumulation d'erreurs

```java
StringBuilder errors = new StringBuilder();

// Valider le nom
String nom = nomField.getText().trim();
String nomError = ValidationUtils.getNameErrorMessage(nom, "Le nom");
if (!nomError.isEmpty()) {
    errors.append("• ").append(nomError).append("\n");
}

// Valider l'email
String email = emailField.getText().trim();
String emailError = ValidationUtils.getEmailErrorMessage(email);
if (!emailError.isEmpty()) {
    errors.append("• ").append(emailError).append("\n");
}

// Valider le téléphone
String phone = phoneField.getText().trim();
String phoneError = ValidationUtils.getPhoneErrorMessage(phone);
if (!phoneError.isEmpty()) {
    errors.append("• ").append(phoneError).append("\n");
}

// Afficher toutes les erreurs
if (errors.length() > 0) {
    showError("Erreurs de validation :\n\n" + errors.toString());
    return;
}

// Tous les champs sont valides, continuer...
```

### Validation conditionnelle (champ optionnel)

```java
// Le mot de passe est optionnel en modification
String password = passwordField.getText();

if (!ValidationUtils.isEmpty(password)) {
    // Si un mot de passe est fourni, il doit être valide
    if (password.length() < 6) {
        showError("Le mot de passe doit contenir au moins 6 caractères");
        passwordField.requestFocus();
        return;
    }
    
    // Mettre à jour le mot de passe
    user.setMdp(password);
}
// Sinon, ne pas modifier le mot de passe existant
```

---

## Tests manuels recommandés

### Test 1 : Champs vides
- [ ] Essayer de se connecter sans rien saisir
- [ ] Vérifier que le message "Veuillez saisir votre email" s'affiche

### Test 2 : Email invalide
- [ ] Saisir "test" comme email
- [ ] Vérifier le message d'erreur avec exemple

### Test 3 : Téléphone invalide
- [ ] Essayer avec 7 chiffres
- [ ] Essayer avec 9 chiffres
- [ ] Essayer avec des lettres
- [ ] Essayer avec des espaces ou tirets

### Test 4 : Nom avec chiffres
- [ ] Saisir "Jean123" comme nom
- [ ] Vérifier le message "ne doit contenir que des lettres"

### Test 5 : Validation multiple
- [ ] Laisser tous les champs vides dans le formulaire d'ajout
- [ ] Vérifier que toutes les erreurs s'affichent ensemble

### Test 6 : Modification sans mot de passe
- [ ] Modifier un utilisateur existant
- [ ] Laisser le champ mot de passe vide
- [ ] Vérifier que la modification réussit sans changer le mot de passe

### Test 7 : Remember Me avec validation
- [ ] Se connecter avec des identifiants valides
- [ ] Cocher "Se souvenir de moi"
- [ ] Fermer et rouvrir l'application
- [ ] Vérifier que les champs sont pré-remplis avec des valeurs valides

---

## Messages d'erreur par langue

### Français (actuel)
```
✗ "Veuillez saisir votre email"
✗ "Format d'email invalide"
✗ "Le mot de passe doit contenir au moins 6 caractères"
✗ "Le nom doit contenir au moins 2 caractères"
✗ "Le numéro doit contenir exactement 8 chiffres"
```

### Anglais (exemple pour future internationalisation)
```
✗ "Please enter your email"
✗ "Invalid email format"
✗ "Password must contain at least 6 characters"
✗ "Name must contain at least 2 characters"
✗ "Phone number must contain exactly 8 digits"
```

---

## Bonnes pratiques

### ✅ À faire

1. **Valider avant traitement**
   ```java
   // Valider d'abord
   if (!ValidationUtils.isValidEmail(email)) {
       showError("Email invalide");
       return;
   }
   
   // Puis traiter
   processLogin(email, password);
   ```

2. **Donner du feedback immédiat**
   ```java
   // Focus sur le champ en erreur
   emailField.requestFocus();
   ```

3. **Messages clairs et constructifs**
   ```java
   // Bon message
   "Format d'email invalide\nExemple: utilisateur@domaine.com"
   
   // Mauvais message
   "Erreur"
   ```

4. **Trim les espaces**
   ```java
   String email = emailField.getText().trim();
   ```

### ❌ À éviter

1. **Ne pas valider**
   ```java
   // Mauvais
   String email = emailField.getText();
   processLogin(email, password); // Pas de validation !
   ```

2. **Messages vagues**
   ```java
   // Mauvais
   showError("Erreur de saisie");
   ```

3. **Validation incomplète**
   ```java
   // Mauvais - vérifie seulement si vide
   if (email.isEmpty()) {
       showError("Email requis");
   }
   // Oublie de vérifier le format !
   ```

4. **Oublier le trim**
   ```java
   // Mauvais - accepte les espaces
   if (email.isEmpty()) { ... }
   
   // Bon
   if (email.trim().isEmpty()) { ... }
   ```

---

## Dépannage

### Problème : Les validations ne fonctionnent pas

**Solution :**
1. Vérifier que `ValidationUtils.java` est bien compilé
2. Vérifier les imports dans les contrôleurs
3. Vérifier que les méthodes sont appelées avant le traitement

### Problème : Messages en anglais au lieu du français

**Solution :**
1. Vérifier que vous utilisez les bonnes méthodes de `ValidationUtils`
2. Les messages sont en français par défaut dans le code

### Problème : Focus ne fonctionne pas

**Solution :**
```java
// Utiliser Platform.runLater si nécessaire
javafx.application.Platform.runLater(() -> {
    emailField.requestFocus();
});
```

### Problème : Validation trop stricte

**Solution :**
1. Ajuster les regex dans `ValidationUtils.java`
2. Modifier les longueurs minimales selon vos besoins
3. Rendre certains champs optionnels
