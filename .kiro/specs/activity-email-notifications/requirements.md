# Requirements Document

## Introduction

Ce document décrit les exigences du système de notifications par email et en temps réel pour le module "Activités" de l'application Nexora (Symfony 6.4). Le système expose une API REST pour déclencher les notifications, envoie des emails transactionnels aux clients et aux partenaires à chaque étape du cycle de vie d'une demande de participation, et diffuse des notifications en temps réel via Mercure ou SSE au partenaire lors de la réception d'une nouvelle demande.

Les emails sont déclenchés par les actions du client (soumission d'une demande, annulation) et du partenaire (acceptation, refus, annulation d'une activité). Le système s'appuie sur le composant Symfony Mailer, Twig pour les templates HTML, une API externe (PDFShift ou HTML2PDF) pour la génération de tickets PDF, et l'API publique `api.qrserver.com` pour la génération des QR codes intégrés directement dans les emails et les tickets PDF. La configuration doit être minimale pour permettre un lancement immédiat du projet en environnement de développement.

## Glossaire

- **Activity_Email_Service** : Service Symfony responsable de la composition et de l'envoi de tous les emails liés aux activités.
- **Notification_API** : Contrôleur REST Symfony exposant les endpoints de déclenchement des notifications et emails.
- **Realtime_Notifier** : Composant responsable de l'envoi de notifications en temps réel au partenaire via Mercure ou SSE.
- **PDF_API_Service** : Composant responsable de la génération du ticket PDF via un appel HTTP POST à une API externe (PDFShift ou HTML2PDF API). Reçoit le HTML rendu du template Twig et retourne le contenu binaire du PDF. La clé API est configurée via la variable d'environnement `PDFSHIFT_API_KEY`.
- **QR_Code_API** : Composant responsable de la génération des QR codes via l'API publique gratuite `api.qrserver.com`. Construit l'URL de l'image QR (`https://api.qrserver.com/v1/create-qr-code/?size=150x150&data={RESERVATION_ID}`) sans clé API requise. Le QR code est intégré dans l'email via une balise `<img>` pointant vers cette URL, et dans le ticket PDF via la même URL transmise au template Twig.
- **ParticipationDemande** : Entité représentant une demande de participation d'un client à une activité, avec les statuts `EN_ATTENTE`, `ACCEPTEE`, `REFUSEE`.
- **Activite** : Entité représentant une activité proposée par un partenaire, avec ses attributs (nom, lieu, date, prix, places).
- **Client** : Utilisateur ayant soumis une demande de participation à une activité.
- **Partenaire** : Utilisateur propriétaire d'une activité, pouvant accepter ou refuser des demandes.
- **Reminder_Scheduler** : Composant (commande Symfony) responsable de l'envoi des emails de rappel selon la date de l'activité.
- **MAILER_DSN** : Variable d'environnement Symfony configurant le transport d'envoi des emails (ex. `smtp://localhost:1025` pour Mailpit en dev).

---

## Requirements

### Requirement 1 : API REST pour le déclenchement des notifications

**User Story :** En tant que développeur, je veux une API REST claire pour déclencher les notifications et emails, afin de pouvoir intégrer le système depuis n'importe quel contrôleur ou service du projet.

#### Acceptance Criteria

1. THE Notification_API SHALL exposer un endpoint `POST /api/notifications/demande/{id}` déclenchant l'envoi de l'email au partenaire et la notification temps réel lors d'une nouvelle demande de participation.
2. THE Notification_API SHALL exposer un endpoint `POST /api/notifications/acceptation/{id}` déclenchant l'envoi de l'email d'acceptation avec ticket PDF au client.
3. THE Notification_API SHALL exposer un endpoint `POST /api/notifications/refus/{id}` déclenchant l'envoi de l'email de refus au client.
4. THE Notification_API SHALL exposer un endpoint `POST /api/notifications/rappel/{id}` déclenchant l'envoi de l'email de rappel avec ticket PDF au client.
5. WHEN un endpoint de la Notification_API reçoit un identifiant de `ParticipationDemande` inexistant, THE Notification_API SHALL retourner une réponse JSON avec le code HTTP 404 et un message d'erreur descriptif.
6. WHEN un endpoint de la Notification_API traite une requête avec succès, THE Notification_API SHALL retourner une réponse JSON avec le code HTTP 200 et un champ `status: "sent"`.
7. IF une exception est levée lors du traitement d'un endpoint, THEN THE Notification_API SHALL retourner une réponse JSON avec le code HTTP 500 et journaliser l'erreur sans exposer les détails techniques au client.

---

### Requirement 2 : Notification temps réel et email au partenaire lors d'une nouvelle demande

**User Story :** En tant que partenaire, je veux être notifié immédiatement en temps réel et par email lorsqu'un client soumet une demande de participation à mon activité, afin de pouvoir traiter la demande rapidement.

#### Acceptance Criteria

1. WHEN un client soumet une demande de participation à une activité, THE Realtime_Notifier SHALL publier un événement sur le topic Mercure (ou SSE) dédié au partenaire propriétaire de l'activité dans un délai de 3 secondes.
2. THE Realtime_Notifier SHALL inclure dans l'événement temps réel : le nom du client, le nom de l'activité, l'identifiant de la `ParticipationDemande`, et la date de soumission.
3. WHEN un client soumet une demande de participation à une activité, THE Activity_Email_Service SHALL envoyer un email HTML au partenaire propriétaire de l'activité dans un délai de 10 secondes.
4. THE Activity_Email_Service SHALL inclure dans l'email au partenaire : le nom du client, son adresse email, le nom de l'activité, la date de l'activité, et un lien direct vers la page de gestion de la demande.
5. THE Activity_Email_Service SHALL utiliser le template Twig `emails/activite/notification_partenaire.html.twig` pour l'email au partenaire.
6. IF l'adresse email du partenaire est absente ou invalide, THEN THE Activity_Email_Service SHALL journaliser l'erreur avec le niveau `error` et continuer l'exécution sans interrompre le flux de réservation.
7. IF la publication Mercure échoue, THEN THE Realtime_Notifier SHALL journaliser l'erreur avec le niveau `warning` et continuer l'exécution sans interrompre le flux de réservation.

---

### Requirement 3 : Email de confirmation de demande au client

**User Story :** En tant que client, je veux recevoir un email de confirmation immédiatement après avoir soumis ma demande de participation, afin d'être informé que ma demande a bien été enregistrée.

#### Acceptance Criteria

1. WHEN un client soumet une demande de participation à une activité, THE Activity_Email_Service SHALL envoyer un email HTML de confirmation à l'adresse email du client dans un délai de 10 secondes.
2. THE Activity_Email_Service SHALL inclure dans l'email de confirmation : le nom de l'activité, le lieu, la date (si définie), le prix, et le nom du client.
3. THE Activity_Email_Service SHALL utiliser le template Twig `emails/activite/confirmation_demande.html.twig` pour composer le corps HTML de l'email.
4. IF l'adresse email du client est invalide ou absente, THEN THE Activity_Email_Service SHALL journaliser l'erreur et continuer l'exécution sans interrompre le flux de réservation.
5. THE Activity_Email_Service SHALL définir l'expéditeur à partir de la variable d'environnement `MAILER_FROM` avec le nom d'affichage "Nexora Activités".

---

### Requirement 4 : Email d'acceptation avec ticket PDF et QR code

**User Story :** En tant que client, je veux recevoir un email m'informant que ma demande a été acceptée, avec un QR code directement visible dans l'email et un ticket PDF en pièce jointe contenant également le QR code, afin d'avoir un justificatif officiel de ma participation.

#### Acceptance Criteria

1. WHEN le statut d'une `ParticipationDemande` passe à `ACCEPTEE`, THE Activity_Email_Service SHALL envoyer un email HTML d'acceptation au client avec le ticket PDF en pièce jointe.
2. THE Activity_Email_Service SHALL inclure dans le corps de l'email d'acceptation : le nom de l'activité, le lieu, la date, le prix, un message de confirmation, et une balise `<img>` pointant vers l'URL `https://api.qrserver.com/v1/create-qr-code/?size=150x150&data={RESERVATION_ID}` pour afficher le QR code directement dans l'email.
3. THE Activity_Email_Service SHALL utiliser le template Twig `emails/activite/acceptation.html.twig` pour le corps de l'email.
4. WHEN le statut d'une `ParticipationDemande` passe à `ACCEPTEE`, THE PDF_API_Service SHALL générer un fichier PDF en envoyant le HTML rendu du template Twig `emails/activite/ticket_pdf.html.twig` via un appel HTTP POST à l'API externe PDFShift ou HTML2PDF API.
5. THE PDF_API_Service SHALL inclure dans le HTML transmis à l'API externe : le nom du client, le nom de l'activité, le lieu, la date, le prix, le numéro de demande, et l'URL du QR code `https://api.qrserver.com/v1/create-qr-code/?size=150x150&data={RESERVATION_ID}` intégrée via une balise `<img>`.
6. THE QR_Code_API SHALL construire l'URL du QR code en encodant l'identifiant unique de la `ParticipationDemande` dans le paramètre `data` de l'URL `https://api.qrserver.com/v1/create-qr-code/`.
7. THE PDF_API_Service SHALL lire la clé d'accès à l'API PDF depuis la variable d'environnement `PDFSHIFT_API_KEY` définie dans le fichier `.env` du projet.
8. THE PDF_API_Service SHALL recevoir le contenu binaire du PDF retourné par l'API externe et le transmettre directement comme pièce jointe à l'email, sans écriture sur disque.
9. IF l'appel HTTP à l'API PDF externe retourne une erreur HTTP (code 401, 402, 429, 5xx) ou expire après 10 secondes, THEN THE PDF_API_Service SHALL journaliser l'erreur avec le niveau `warning` et retourner une valeur nulle sans bloquer l'envoi de l'email.
10. IF la variable d'environnement `PDFSHIFT_API_KEY` est absente ou vide, THEN THE PDF_API_Service SHALL journaliser un message de niveau `warning` indiquant que la génération PDF est désactivée et retourner une valeur nulle.
11. IF la génération du PDF échoue (valeur nulle retournée par le PDF_API_Service), THEN THE Activity_Email_Service SHALL envoyer l'email d'acceptation sans pièce jointe et journaliser l'erreur avec le niveau `warning`.
12. IF l'envoi de l'email d'acceptation échoue, THEN THE Activity_Email_Service SHALL journaliser l'erreur avec le niveau `error` sans annuler le changement de statut de la demande.

---

### Requirement 5 : Email de refus de réservation

**User Story :** En tant que client, je veux recevoir un email m'informant que ma demande a été refusée par le partenaire, afin d'être informé et de pouvoir chercher d'autres activités.

#### Acceptance Criteria

1. WHEN le statut d'une `ParticipationDemande` passe à `REFUSEE`, THE Activity_Email_Service SHALL envoyer un email HTML de refus au client.
2. THE Activity_Email_Service SHALL inclure dans l'email de refus : le nom de l'activité, le nom du client, et un lien vers la liste des activités disponibles.
3. THE Activity_Email_Service SHALL utiliser le template Twig `emails/activite/refus.html.twig`.
4. IF l'envoi de l'email de refus échoue, THEN THE Activity_Email_Service SHALL journaliser l'erreur avec le niveau `error` sans annuler le changement de statut de la demande.

---

### Requirement 6 : Email de rappel 24h avant l'activité avec ticket PDF et QR code

**User Story :** En tant que client, je veux recevoir un email de rappel 24 heures avant la date de mon activité avec mon ticket PDF, afin de ne pas oublier ma participation et d'avoir mon justificatif à portée de main.

#### Acceptance Criteria

1. WHEN la date d'une `Activite` est dans exactement 24 heures (±30 minutes), THE Reminder_Scheduler SHALL déclencher l'envoi d'un email de rappel à tous les clients dont la `ParticipationDemande` associée a le statut `ACCEPTEE`.
2. THE Activity_Email_Service SHALL inclure dans l'email de rappel : le nom de l'activité, le lieu, la date et l'heure exactes, le nom du client, et une balise `<img>` pointant vers l'URL `https://api.qrserver.com/v1/create-qr-code/?size=150x150&data={RESERVATION_ID}` pour afficher le QR code directement dans l'email.
3. THE Activity_Email_Service SHALL joindre à l'email de rappel le ticket PDF généré par le PDF_API_Service selon les mêmes règles que le Requirement 4.
4. THE Activity_Email_Service SHALL utiliser le template Twig `emails/activite/rappel.html.twig`.
5. THE Reminder_Scheduler SHALL être exécutable via la commande Symfony `app:send-activity-reminders` planifiable par cron.
6. IF une `Activite` n'a pas de date définie (`avecDate = false`), THEN THE Reminder_Scheduler SHALL ignorer cette activité pour l'envoi des rappels.
7. IF un email de rappel a déjà été envoyé pour une `ParticipationDemande` donnée, THEN THE Reminder_Scheduler SHALL ne pas envoyer de second rappel pour cette même demande.

---

### Requirement 7 : Email d'annulation d'activité par le partenaire

**User Story :** En tant que client, je veux recevoir un email m'informant que l'activité à laquelle je suis inscrit a été annulée par le partenaire, afin d'être prévenu rapidement.

#### Acceptance Criteria

1. WHEN un partenaire supprime ou annule une `Activite`, THE Activity_Email_Service SHALL envoyer un email HTML d'annulation à tous les clients dont la `ParticipationDemande` associée a le statut `ACCEPTEE` ou `EN_ATTENTE`.
2. THE Activity_Email_Service SHALL inclure dans l'email d'annulation : le nom de l'activité, la date initialement prévue, et un message d'excuse.
3. THE Activity_Email_Service SHALL utiliser le template Twig `emails/activite/annulation_activite.html.twig`.
4. IF l'envoi d'un email d'annulation échoue pour un client donné, THEN THE Activity_Email_Service SHALL journaliser l'erreur et continuer l'envoi aux autres clients concernés.

---

### Requirement 8 : Fiabilité, traçabilité et testabilité

**User Story :** En tant que développeur, je veux que le système soit opérationnel dès le lancement du projet avec une configuration minimale, et que tous les envois soient tracés, afin de pouvoir développer et tester sans infrastructure complexe.

#### Acceptance Criteria

1. WHERE l'environnement Symfony est `dev`, THE Activity_Email_Service SHALL utiliser Mailpit comme transport SMTP local (`smtp://localhost:1025`) sans nécessiter de configuration supplémentaire.
2. THE Activity_Email_Service SHALL fonctionner avec la seule variable d'environnement `MAILER_DSN` définie dans le fichier `.env` du projet, sans dépendance à des services externes en environnement de développement.
3. THE PDF_API_Service SHALL lire la clé d'accès à l'API PDF depuis la variable d'environnement `PDFSHIFT_API_KEY` définie dans le fichier `.env` du projet ; si cette variable est absente, la génération PDF est désactivée sans erreur bloquante.
4. THE Activity_Email_Service SHALL journaliser chaque tentative d'envoi d'email avec : le type d'email, l'adresse destinataire, et l'identifiant de la `ParticipationDemande`.
5. IF une exception est levée lors de l'envoi d'un email, THEN THE Activity_Email_Service SHALL capturer l'exception, journaliser le message d'erreur avec le niveau `error`, et ne pas propager l'exception vers le contrôleur appelant.
6. THE Activity_Email_Service SHALL être injectable via l'injection de dépendances Symfony dans tout contrôleur ou service du module Activités.
7. THE Notification_API SHALL être testable via des requêtes HTTP directes (curl, Postman) sans authentification requise en environnement de développement.
8. WHERE l'environnement Symfony est `test`, THE Activity_Email_Service SHALL utiliser le transport `null://null` pour éviter tout envoi réel lors des tests automatisés.

---

### Requirement 9 : Intégration de la météo dans les emails et tickets PDF

**User Story :** En tant que client, je veux voir la météo prévue pour le jour de mon activité dans l'email de rappel et dans mon ticket PDF, afin de me préparer en conséquence.

#### Acceptance Criteria

1. THE Weather_Service SHALL appeler l'API OpenWeatherMap (ou WeatherAPI) en utilisant la ville/lieu de l'`Activite` et la date de l'activité pour récupérer les prévisions météorologiques.
2. THE Weather_Service SHALL lire la clé d'accès à l'API météo depuis la variable d'environnement `WEATHER_API_KEY` définie dans le fichier `.env` du projet.
3. WHEN le Weather_Service récupère avec succès les données météo, THE Weather_Service SHALL retourner un objet contenant : la température en degrés Celsius, la description textuelle des conditions météo, et l'URL de l'icône météo correspondante.
4. WHEN l'Activity_Email_Service compose l'email de rappel (Requirement 6), THE Activity_Email_Service SHALL appeler le Weather_Service et inclure les données météo dans le template Twig `emails/activite/rappel.html.twig` si elles sont disponibles.
5. WHEN le PDF_API_Service génère le ticket PDF (Requirement 4 et Requirement 6), THE PDF_API_Service SHALL inclure les données météo dans le template Twig `emails/activite/ticket_pdf.html.twig` si elles sont disponibles.
6. WHILE la date de l'`Activite` est supérieure à 7 jours à partir de la date courante, THE Weather_Service SHALL retourner une valeur nulle indiquant que les prévisions météo ne sont pas disponibles pour cette échéance, sans lever d'exception.
7. IF la variable d'environnement `WEATHER_API_KEY` est absente ou vide, THEN THE Weather_Service SHALL retourner une valeur nulle et journaliser un message de niveau `info` indiquant que la météo est désactivée, sans bloquer l'envoi de l'email ni la génération du ticket PDF.
8. IF l'appel à l'API météo retourne une erreur HTTP (code 401, 429, 5xx) ou expire après 3 secondes, THEN THE Weather_Service SHALL journaliser l'erreur avec le niveau `warning` et retourner une valeur nulle sans bloquer l'envoi de l'email ni la génération du ticket PDF.
9. IF le Weather_Service retourne une valeur nulle, THEN THE Activity_Email_Service SHALL envoyer l'email de rappel et THE PDF_API_Service SHALL générer le ticket PDF sans section météo, sans message d'erreur visible pour le client.
10. WHERE l'environnement Symfony est `dev` ou `test`, THE Weather_Service SHALL fonctionner sans clé API réelle : si `WEATHER_API_KEY` est vide, la météo est simplement omise des emails et tickets PDF sans erreur.
