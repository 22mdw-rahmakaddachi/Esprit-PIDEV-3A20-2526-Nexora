# 📋 Exemple Visuel - Contrôle de Saisie avec Cadre Rouge

## Comment ça fonctionne

### 1️⃣ **Formulaire Avis** (`/avis`)

#### Champ Titre (vide ou < 3 caractères)
```
┌─────────────────────────────────────┐
│ Titre *                             │
├─────────────────────────────────────┤
│ [Résumé de votre avis]              │ ← Bordure rouge si erreur
└─────────────────────────────────────┘
┌─────────────────────────────────────┐
│ ⚠️ Le titre doit contenir au moins  │ ← Cadre rouge sous le champ
│    3 caractères.                    │
└─────────────────────────────────────┘
```

#### Champ Commentaire (vide ou < 5 caractères)
```
┌─────────────────────────────────────┐
│ Commentaire *                       │
├─────────────────────────────────────┤
│                                     │
│ [Partagez votre expérience...]     │ ← Bordure rouge si erreur
│                                     │
└─────────────────────────────────────┘
┌─────────────────────────────────────┐
│ ⚠️ Le commentaire doit contenir au  │ ← Cadre rouge sous le champ
│    moins 5 caractères.              │
└─────────────────────────────────────┘
```

#### Champ Note (invalide)
```
┌─────────────────────────────────────┐
│ Note *                              │
│ ★ ★ ★ ★ ★                           │
└─────────────────────────────────────┘
┌─────────────────────────────────────┐
│ ⚠️ La note doit être entre 1 et 5.  │ ← Cadre rouge sous le champ
└─────────────────────────────────────┘
```

---

### 2️⃣ **Formulaire Publication** (`/publications`)

#### Champ Contenu (vide ou < 2 caractères)
```
┌─────────────────────────────────────┐
│                                     │
│ [Quoi de neuf ? Partagez...]       │ ← Bordure rouge si erreur
│                                     │
└─────────────────────────────────────┘
┌─────────────────────────────────────┐
│ ⚠️ Le contenu est obligatoire       │ ← Cadre rouge sous le champ
│    (min. 2 caractères).             │
└─────────────────────────────────────┘
```

---

## 🎨 Style des Cadres d'Erreur

### Apparence Visuelle
```css
┌────────────────────────────────────────┐
│ ⚠️ Message d'erreur ici                │
└────────────────────────────────────────┘
  ↑
  Bordure gauche rouge (3px)
  Fond: #fde8e8 (rouge clair)
  Texte: #c0392b (rouge foncé)
  Icône: bi-exclamation-circle
```

### Code CSS Appliqué
```css
margin-top: 6px;
padding: 8px 12px;
background: #fde8e8;
border-left: 3px solid #e74c3c;
border-radius: 6px;
font-size: 0.8rem;
color: #c0392b;
```

---

## 🔄 Flux de Validation

### Étape 1: Utilisateur soumet le formulaire
```
[Utilisateur] → Clique sur "Publier"
```

### Étape 2: Validation côté serveur (PHP)
```php
// Dans AvisController.php
$violations = $validator->validate($avisTest);
if (count($violations) > 0) {
    foreach ($violations as $v) {
        $field = $v->getPropertyPath();
        $fieldMap = [
            'titre' => 'error_titre',
            'contenu' => 'error_commentaire',
            'rating' => 'error_note'
        ];
        $flashKey = $fieldMap[$field] ?? 'error';
        $this->addFlash($flashKey, $v->getMessage());
    }
    return $this->redirectToRoute('app_avis');
}
```

### Étape 3: Redirection avec messages flash
```
[Controller] → addFlash('error_titre', 'Message')
            → redirectToRoute('app_avis')
```

### Étape 4: Affichage dans le template
```twig
{# Bordure rouge sur le champ #}
<input type="text" name="titre" 
       style="{% if app.flashes('error_titre') is not empty %}border-color:#e74c3c;{% endif %}">

{# Cadre rouge sous le champ #}
{% for error in app.flashes('error_titre') %}
<div style="margin-top:6px;padding:8px 12px;background:#fde8e8;border-left:3px solid #e74c3c;border-radius:6px;font-size:0.8rem;color:#c0392b">
    <i class="bi bi-exclamation-circle me-1"></i>{{ error }}
</div>
{% endfor %}
```

---

## ✅ Règles de Validation

### Avis
| Champ       | Règle                          | Message d'erreur                                    |
|-------------|--------------------------------|-----------------------------------------------------|
| Titre       | NotBlank, Length(min:3, max:100) | "Le titre doit contenir au moins 3 caractères."    |
| Commentaire | NotBlank, Length(min:5, max:2000)| "Le commentaire doit contenir au moins 5 caractères."|
| Note        | Range(min:1, max:5)            | "La note doit être entre 1 et 5."                   |

### Publication
| Champ   | Règle                    | Message d'erreur                                |
|---------|--------------------------|------------------------------------------------|
| Contenu | min:2, max:2000          | "Le contenu est obligatoire (min. 2 caractères)."|

---

## 🚫 Pas de Validation HTML5

### Attributs Supprimés
```html
<!-- ❌ AVANT (avec HTML5) -->
<input type="text" name="titre" required minlength="3">
<textarea name="commentaire" required minlength="5"></textarea>

<!-- ✅ APRÈS (sans HTML5) -->
<form novalidate>
  <input type="text" name="titre">
  <textarea name="commentaire"></textarea>
</form>
```

### Pourquoi `novalidate` ?
- Désactive les bulles de validation HTML5 natives
- Permet d'afficher uniquement les cadres rouges personnalisés
- Validation 100% côté serveur (PHP/Symfony)

---

## 📸 Exemple Visuel Complet

### Formulaire avec Erreurs Multiples
```
┌──────────────────────────────────────────────────┐
│ Laisser un avis                                  │
├──────────────────────────────────────────────────┤
│                                                  │
│ Titre *                                          │
│ ┌──────────────────────────────────────────┐    │
│ │ [Résumé de votre avis]                   │    │ ← Bordure rouge
│ └──────────────────────────────────────────┘    │
│ ┌──────────────────────────────────────────┐    │
│ │ ⚠️ Le titre doit contenir au moins       │    │ ← Cadre rouge
│ │    3 caractères.                         │    │
│ └──────────────────────────────────────────┘    │
│                                                  │
│ Note *                                           │
│ ★ ★ ★ ★ ★                                        │
│                                                  │
│ Commentaire *                                    │
│ ┌──────────────────────────────────────────┐    │
│ │                                          │    │
│ │ [Partagez votre expérience...]          │    │ ← Bordure rouge
│ │                                          │    │
│ └──────────────────────────────────────────┘    │
│ ┌──────────────────────────────────────────┐    │
│ │ ⚠️ Le commentaire doit contenir au moins │    │ ← Cadre rouge
│ │    5 caractères.                         │    │
│ └──────────────────────────────────────────┘    │
│                                                  │
│                    [📤 Publier]                  │
└──────────────────────────────────────────────────┘
```

---

## 🧪 Test de Validation

### Pour tester les erreurs:

1. **Avis - Titre trop court**
   - Aller sur `/avis`
   - Entrer "ab" dans le champ Titre
   - Cliquer sur Publier
   - ✅ Cadre rouge apparaît: "Le titre doit contenir au moins 3 caractères."

2. **Avis - Commentaire vide**
   - Laisser le champ Commentaire vide
   - Cliquer sur Publier
   - ✅ Cadre rouge apparaît: "Le commentaire est obligatoire."

3. **Publication - Contenu trop court**
   - Aller sur `/publications`
   - Entrer "a" dans le textarea
   - Cliquer sur Publier
   - ✅ Cadre rouge apparaît: "Le contenu est obligatoire (min. 2 caractères)."

---

## 📝 Notes Importantes

- ✅ Validation **100% côté serveur** (PHP)
- ✅ Pas de validation JavaScript/HTML5
- ✅ Cadres rouges sous chaque champ avec erreur
- ✅ Bordure rouge sur le champ avec erreur
- ✅ Messages d'erreur clairs et en français
- ✅ Icônes Bootstrap pour meilleure UX
