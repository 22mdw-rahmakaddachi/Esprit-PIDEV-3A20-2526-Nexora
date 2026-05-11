# 📝 Guide de Création de Produits avec Variants

## ✅ Corrections Apportées

1. **Support des virgules ET points** : Vous pouvez maintenant utiliser `150,00` ou `150.00` pour les prix
2. **Placeholders explicites** : Chaque champ indique clairement ce qu'il faut remplir
3. **Labels détaillés** : Les labels indiquent si le champ est obligatoire ou optionnel

## 🏕️ Exemple Complet : Tente Camping

### Informations Générales
- **Nom du produit** : `Tente Camping`
- **Catégorie** : Sélectionner dans la liste (ex: Sport)
- **Sous-Catégorie** : Sélectionner dans la liste (ex: Camping)
- **Description courte** : `Tente 2 places imperméable`
- **Description complète** : `Tente de camping 2 places, imperméable, facile à monter. Idéale pour les randonnées et le camping sauvage.`
- **Marque** : `OutdoorPro`
- **Matériau** : `Polyester imperméable`
- **Poids** : `2,5` (ou `2.5`)
- **Dimensions** : `200x150x110`

### Variant 1 : Tente Verte
- **SKU** : `TENTE-CAMP-VERT`
- **Prix d'achat** : `80,00` (optionnel)
- **Prix de vente** : `150,00` (obligatoire)
- **Prix promo** : `120,00` (optionnel)
- **Stock** : `10`
- **Seuil d'alerte** : `2`
- **Options** : Couleur = Vert, Taille = 2 places

### Variant 2 : Tente Bleue
- **SKU** : `TENTE-CAMP-BLEU`
- **Prix d'achat** : `80,00`
- **Prix de vente** : `150,00`
- **Prix promo** : `120,00`
- **Stock** : `15`
- **Seuil d'alerte** : `2`
- **Options** : Couleur = Bleu, Taille = 2 places

## 💡 Conseils

### Formats Acceptés
✅ **Prix** : `150,00` ou `150.00` ou `150`
✅ **Poids** : `2,5` ou `2.5`
✅ **Stock** : `10` (nombre entier)

### Champs Obligatoires (*)
- Nom du produit
- Catégorie/Sous-catégorie
- SKU (pour chaque variant)
- Prix de vente (pour chaque variant)
- Stock (pour chaque variant)

### Champs Optionnels
- Description courte/complète
- Marque, Matériau, Poids, Dimensions
- Prix d'achat, Prix promo
- Seuil d'alerte (par défaut = 2)

## 🔧 Fonctionnalités

1. **Ajout de variants** : Cliquez sur "➕ Ajouter Variant"
2. **Suppression** : Cliquez sur 🗑️ pour supprimer un variant
3. **Options** : Sélectionnez couleur, taille, etc. selon vos attributs configurés
4. **Image** : Cliquez sur 📁 pour ajouter une image

## ⚠️ Important

- Créez d'abord vos **attributs** (Couleur, Taille) dans la section "Gestion Attributs"
- Créez vos **catégories** dans la section "Gestion Catégories"
- Chaque variant doit avoir un **SKU unique**