# 🚀 Guide de Déploiement : Nexora (Symfony)

Pour gagner les **0,5 points bonus**, l'application doit être accessible en ligne. Je vous recommande **AlwaysData** car ils offrent un pack gratuit idéal pour les projets étudiants Symfony.

## 1. Création du compte
1. Allez sur [alwaysdata.com](https://www.alwaysdata.com/) et créez un compte gratuit.
2. Choisissez un nom d'utilisateur (ex: `nexora`). Votre URL sera `nexora.alwaysdata.net`.

## 2. Configuration de la Base de Données
1. Dans le panel AlwaysData, allez dans **Bases de données > MySQL**.
2. Créez une nouvelle base de données nommée `nexora_db`.
3. Créez un utilisateur et notez le mot de passe.
4. **Important :** Importez votre schéma SQL local vers cette base via PhpMyAdmin (fourni par AlwaysData).

## 3. Préparation des fichiers
1. Dans votre dossier `Esprit-PIDEV-3A20-2526-Nexora`, ouvrez le fichier `.env`.
2. Modifiez la ligne `DATABASE_URL` avec les accès AlwaysData :
   `DATABASE_URL="mysql://user:password@mysql-nexora.alwaysdata.net/nexora_db"`
3. Mettez `APP_ENV=prod`.

## 4. Transfert via FTP
1. Utilisez **FileZilla** pour vous connecter à votre compte AlwaysData.
2. Transférez tout le contenu de votre dossier `Esprit-PIDEV-3A20-2526-Nexora` dans le dossier `/www/` de AlwaysData.
3. **Note :** Ne transférez PAS le dossier `vendor/` ni `var/cache/`. Ils seront régénérés.

## 5. Finalisation via SSH (Console)
Si vous avez accès à SSH sur AlwaysData :
```bash
cd www
composer install --no-dev --optimize-autoloader
php bin/console cache:clear --env=prod
php bin/console assets:install public
```

## 6. Configuration du site
Dans le panel AlwaysData, allez dans **Web > Sites** :
- **Type :** PHP
- **Chemin racine :** `/www/public/` (Très important : le site doit pointer sur le dossier public).

---
### ✅ Pourquoi AlwaysData ?
- Supporte PHP 8.2+.
- Base de données MySQL incluse.
- Certificat SSL (HTTPS) automatique.
- **Parfait pour la démo PIDEV.**
