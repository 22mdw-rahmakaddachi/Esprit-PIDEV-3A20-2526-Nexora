# 🌌 NEXORA - Plateforme de Voyage & E-commerce Intégrée

Nexora est une solution complète de gestion de voyages, d'activités et de produits e-commerce, conçue pour offrir une expérience utilisateur fluide et moderne. Ce dépôt contient la partie **Web (Symfony)** du projet PIDEV 3A.

## 🚀 Modules Principaux

### 🌍 Gestion des Destinations & Excursions
- Catalogue interactif des destinations.
- Intégration météo en temps réel (OpenWeatherMap API).
- Génération d'itinéraires intelligents.

### 🎭 Activités & Planning
- Gestion des activités locales et événements.
- Inscription et suivi des participants.

### 🛍️ E-commerce Avancé
- Catalogue de produits avec variantes (tailles, couleurs).
- Panier intelligent et gestion des stocks.
- Système de codes promotionnels et remises.
- Paiement sécurisé via l'API Konnect (Flouci).

### 💬 Communauté & Support
- **Forum / Publications :** Espace d'échange entre voyageurs.
- **Avis & Réclamations :** Système de feedback avec modération intelligente.
- **Chatbot IA :** Assistant shopping et guide de voyage basé sur l'IA (Gemini API).

### 🔒 Sécurité & Innovation
- Authentification Google OAuth2.
- Inscription biométrique (Reconnaissance faciale & Empreinte digitale via bridge Python).
- Notifications en temps réel via Server-Sent Events (SSE).

## 🛠 Technologies utilisées

- **Framework :** Symfony 7.x
- **Moteur de Template :** Twig avec design personnalisé (Violet Theme).
- **Base de Données :** MySQL (Base commune avec l'application Java Desktop).
- **APIs Externes :** Konnect API, OpenWeatherMap, Gemini AI, Google Auth.
- **Tests :** PHPUnit (Tests unitaires) & PHPStan (Analyse statique).

## 📦 Installation

1. **Cloner le projet**
   ```bash
   git clone https://github.com/votre-username/nexora-web.git
   cd nexora-web
   ```

2. **Installer les dépendances**
   ```bash
   composer install
   npm install && npm run build
   ```

3. **Configurer l'environnement**
   Copiez le fichier `.env` et ajustez votre connexion DATABASE_URL :
   ```bash
   DATABASE_URL="mysql://root:@127.0.0.1:3306/java"
   ```

4. **Lancer le serveur**
   ```bash
   symfony serve
   ```

---

## 🏷️ Mots-clés (Topics)
`#symfony` `#php` `#pidev` `#java-integration` `#ecommerce` `#travel-tech` `#ai` `#biometrics`

---
**Note :** Ce projet fait partie de la validation d'intégration finale Web/Java de l'ESPRIT.
