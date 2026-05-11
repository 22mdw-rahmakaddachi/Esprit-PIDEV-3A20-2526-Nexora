# 🖥️ NEXORA — Desktop Application (Pro)

**Nexora Desktop** est le module d'administration et de gestion avancée de l'écosystème Nexora. Développée en JavaFX, cette application permet une gestion en temps réel des stocks, des destinations et des utilisateurs, synchronisée avec la plateforme Web Symfony.

---

## 🌟 Points Forts de l'Intégration
- **Base de Données Unifiée :** Partage total des données avec la version Web (MySQL).
- **Charte Graphique Harmonisée :** Design moderne en violet Nexora (`#6c3fc5`).
- **Synchronisation Instantanée :** Toute modification sur le Desktop est visible sur le Web (et inversement).

## 📋 Modules Principaux

### 🌍 Gestion des Destinations & Excursions
- Création et modification des destinations de voyage.
- Suivi des participants et des capacités en temps réel.
- Statut de disponibilité dynamique (Disponible / Complet).

### 🛒 E-commerce & Stocks
- Gestion avancée des produits avec variants (tailles, couleurs).
- Système de codes promotionnels intelligents.
- Dashboard des ventes et commandes.

### 👥 Administration
- Gestion des comptes utilisateurs et partenaires.
- Sécurisation des accès.

## 🚀 Technologies & Stack
- **Langage :** Java 17 (JDK)
- **UI Framework :** JavaFX 21
- **Gestionnaire :** Maven
- **Base de données :** MySQL 8.0
- **Build Tool :** Maven Shade (pour le Fat JAR)

---

## 📦 Installation & Exécution

### 1. Prérequis
- Java JDK 17 installé.
- MySQL Server en marche.
- Maven (pour la compilation).

### 2. Base de Données (Partagée avec Symfony)
L'application utilise la base de données nommée `java`.
```sql
-- Assurez-vous que la base existe
CREATE DATABASE IF NOT EXISTS java;
```

### 3. Exécution Rapide (Via le JAR)
Si vous avez téléchargé le fichier `nexora-desktop.jar` depuis le site web :
```bash
java -jar nexora-desktop.jar
```

### 4. Compilation depuis les sources
```bash
mvn clean package -DskipTests
java -jar target/pi_deve-1.0-SNAPSHOT.jar
```

---

## 🛠 Configuration Technique
Le fichier de connexion se trouve dans :
`src/main/java/com/pi/utils/mydatabase.java`

```java
private static final String URL = "jdbc:mysql://localhost:3306/java";
private static final String USER = "root";
private static final String PASSWORD = "";
```

---

## 👨‍💻 Équipe Nexora
Projet réalisé dans le cadre de la validation PIDEV - Esprit 2026.

---
**Note :** Pour une expérience complète, lancez simultanément le serveur Symfony et l'application Desktop pour tester la synchronisation des données.
