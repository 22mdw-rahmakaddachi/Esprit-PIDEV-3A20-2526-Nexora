package com.pi.utils;

import jakarta.mail.*;
import jakarta.mail.internet.*;
import java.io.IOException;
import java.io.InputStream;
import java.util.Properties;

public class EmailService {

    private static Properties emailConfig = null;

    /**
     * Charge la configuration email depuis email.properties
     */
    private static Properties loadEmailConfig() {
        if (emailConfig == null) {
            emailConfig = new Properties();
            try (InputStream input = EmailService.class.getClassLoader().getResourceAsStream("email.properties")) {
                if (input == null) {
                    System.out.println("⚠️ Fichier email.properties non trouvé, utilisation des valeurs par défaut");
                    // Valeurs par défaut
                    emailConfig.setProperty("mail.smtp.host", "smtp.gmail.com");
                    emailConfig.setProperty("mail.smtp.port", "587");
                    emailConfig.setProperty("mail.smtp.auth", "true");
                    emailConfig.setProperty("mail.smtp.starttls.enable", "true");
                    emailConfig.setProperty("mail.from", "votre.email@gmail.com");
                    emailConfig.setProperty("mail.password", "votre_mot_de_passe_app");
                    emailConfig.setProperty("mail.debug", "true");
                } else {
                    emailConfig.load(input);
                    System.out.println("✅ Configuration email chargée depuis email.properties");
                    System.out.println("   Host: " + emailConfig.getProperty("mail.smtp.host"));
                    System.out.println("   Port: " + emailConfig.getProperty("mail.smtp.port"));
                    System.out.println("   From: " + emailConfig.getProperty("mail.from"));
                    System.out.println("   Debug: " + emailConfig.getProperty("mail.debug"));
                }
            } catch (IOException e) {
                System.out.println("❌ Erreur lors du chargement de email.properties: " + e.getMessage());
                e.printStackTrace();
            }
        }
        return emailConfig;
    }

    /**
     * Envoie un email de notification
     * @param to Email du destinataire
     * @param subject Sujet de l'email
     * @param body Corps de l'email
     * @return true si envoyé avec succès, false sinon
     */
    public static boolean sendEmail(String to, String subject, String body) {
        Properties config = loadEmailConfig();

        final String username = config.getProperty("mail.from");
        final String password = config.getProperty("mail.password");

        // Vérifier que les identifiants sont configurés
        if (username == null || username.equals("your.email@gmail.com") ||
                password == null || password.equals("your_app_password_here")) {
            System.out.println("⚠️ Configuration email non définie. Veuillez configurer mail.from et mail.password dans email.properties");
            return false;
        }

        Properties props = new Properties();
        props.put("mail.smtp.auth", config.getProperty("mail.smtp.auth", "true"));
        props.put("mail.smtp.starttls.enable", config.getProperty("mail.smtp.starttls.enable", "true"));
        props.put("mail.smtp.host", config.getProperty("mail.smtp.host", "smtp.gmail.com"));
        props.put("mail.smtp.port", config.getProperty("mail.smtp.port", "587"));
        props.put("mail.smtp.ssl.trust", config.getProperty("mail.smtp.host", "smtp.gmail.com"));
        props.put("mail.smtp.ssl.protocols", config.getProperty("mail.smtp.ssl.protocols", "TLSv1.2"));
        props.put("mail.debug", config.getProperty("mail.debug", "false"));

        System.out.println("📧 Tentative d'envoi d'email...");
        System.out.println("   De: Nexora <" + username + ">");
        System.out.println("   À: " + to);
        System.out.println("   Sujet: " + subject);

        Session session = Session.getInstance(props, new Authenticator() {
            @Override
            protected PasswordAuthentication getPasswordAuthentication() {
                return new PasswordAuthentication(username, password);
            }
        });

        try {
            Message message = new MimeMessage(session);
            try {
                message.setFrom(new InternetAddress(username, "Nexora"));
            } catch (java.io.UnsupportedEncodingException e) {
                // Si l'encodage échoue, utiliser juste l'email
                message.setFrom(new InternetAddress(username));
            }
            message.setRecipients(Message.RecipientType.TO, InternetAddress.parse(to));
            message.setSubject(subject);
            message.setText(body);

            Transport.send(message);
            System.out.println("✅ Email envoyé avec succès à: " + to);
            return true;

        } catch (jakarta.mail.AuthenticationFailedException e) {
            System.out.println("❌ Erreur d'authentification email: Vérifiez vos identifiants Gmail et utilisez un mot de passe d'application");
            System.out.println("   Guide: https://support.google.com/accounts/answer/185833");
            return false;
        } catch (MessagingException e) {
            System.out.println("❌ Erreur lors de l'envoi de l'email: " + e.getMessage());
            return false;
        }
    }

    /**
     * Envoie un email HTML
     */
    public static boolean sendHtmlEmail(String to, String subject, String htmlBody) {
        Properties config = loadEmailConfig();

        final String username = config.getProperty("mail.from");
        final String password = config.getProperty("mail.password");

        // Vérifier que les identifiants sont configurés
        if (username == null || username.equals("your.email@gmail.com") ||
                password == null || password.equals("your_app_password_here")) {
            System.out.println("⚠️ Configuration email non définie. Veuillez configurer mail.from et mail.password dans email.properties");
            return false;
        }

        Properties props = new Properties();
        props.put("mail.smtp.auth", config.getProperty("mail.smtp.auth", "true"));
        props.put("mail.smtp.starttls.enable", config.getProperty("mail.smtp.starttls.enable", "true"));
        props.put("mail.smtp.host", config.getProperty("mail.smtp.host", "smtp.gmail.com"));
        props.put("mail.smtp.port", config.getProperty("mail.smtp.port", "587"));
        props.put("mail.smtp.ssl.trust", config.getProperty("mail.smtp.host", "smtp.gmail.com"));
        props.put("mail.smtp.ssl.protocols", config.getProperty("mail.smtp.ssl.protocols", "TLSv1.2"));
        props.put("mail.debug", config.getProperty("mail.debug", "false"));

        System.out.println("📧 Tentative d'envoi d'email HTML...");
        System.out.println("   De: Nexora <" + username + ">");
        System.out.println("   À: " + to);

        Session session = Session.getInstance(props, new Authenticator() {
            @Override
            protected PasswordAuthentication getPasswordAuthentication() {
                return new PasswordAuthentication(username, password);
            }
        });

        try {
            Message message = new MimeMessage(session);
            try {
                message.setFrom(new InternetAddress(username, "Nexora"));
            } catch (java.io.UnsupportedEncodingException e) {
                // Si l'encodage échoue, utiliser juste l'email
                message.setFrom(new InternetAddress(username));
            }
            message.setRecipients(Message.RecipientType.TO, InternetAddress.parse(to));
            message.setSubject(subject);
            message.setContent(htmlBody, "text/html; charset=utf-8");

            Transport.send(message);
            System.out.println("✅ Email HTML envoyé avec succès à: " + to);
            return true;

        } catch (jakarta.mail.AuthenticationFailedException e) {
            System.out.println("❌ Erreur d'authentification email: Vérifiez vos identifiants Gmail et utilisez un mot de passe d'application");
            System.out.println("   Guide: https://support.google.com/accounts/answer/185833");
            return false;
        } catch (MessagingException e) {
            System.out.println("❌ Erreur lors de l'envoi de l'email HTML: " + e.getMessage());
            return false;
        }
    }

    /**
     * Crée un email d'acceptation de demande
     */
    public static String createAcceptanceEmailBody(String clientNom, String activiteNom,
                                                   String lieu, String date, double prix,
                                                   String partenaireNom, String partenaireEmail,
                                                   String partenaireTel) {
        return String.format(
                "Bonjour %s,\n\n" +
                        "Bonne nouvelle ! Votre demande de participation a été ACCEPTÉE.\n\n" +
                        "📋 DÉTAILS DE L'ACTIVITÉ:\n" +
                        "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n" +
                        "• Activité: %s\n" +
                        "• Lieu: %s\n" +
                        "• Date: %s\n" +
                        "• Prix: %.2f TND\n\n" +
                        "👤 INFORMATIONS DU PARTENAIRE:\n" +
                        "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n" +
                        "• Nom: %s\n" +
                        "• Email: %s\n" +
                        "• Téléphone: %s\n\n" +
                        "💳 PROCHAINES ÉTAPES:\n" +
                        "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n" +
                        "1. Connectez-vous à votre compte\n" +
                        "2. Allez dans la section 'Activités'\n" +
                        "3. Sélectionnez l'activité '%s'\n" +
                        "4. Cliquez sur 'Voir Statut' pour confirmer l'acceptation\n" +
                        "5. Cliquez sur 'Payer' pour finaliser votre réservation\n\n" +
                        "⚠️ IMPORTANT:\n" +
                        "Veuillez effectuer le paiement dans les 48 heures pour confirmer votre participation.\n" +
                        "Après ce délai, votre place pourra être attribuée à un autre participant.\n\n" +
                        "Si vous avez des questions, n'hésitez pas à contacter le partenaire directement.\n\n" +
                        "Cordialement,\n" +
                        "L'équipe NEXORA\n\n" +
                        "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n" +
                        "Cet email est envoyé automatiquement, merci de ne pas y répondre.",
                clientNom, activiteNom, lieu, date, prix,
                partenaireNom, partenaireEmail, partenaireTel, activiteNom
        );
    }

    /**
     * Crée un email de refus de demande
     */
    public static String createRejectionEmailBody(String clientNom, String activiteNom,
                                                  String partenaireNom, String partenaireEmail,
                                                  String partenaireTel) {
        return String.format(
                "Bonjour %s,\n\n" +
                        "Nous sommes désolés de vous informer que votre demande de participation a été REFUSÉE.\n\n" +
                        "📋 DÉTAILS:\n" +
                        "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n" +
                        "• Activité: %s\n" +
                        "• Partenaire: %s\n\n" +
                        "❓ RAISONS POSSIBLES:\n" +
                        "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n" +
                        "• Places complètes\n" +
                        "• Critères de participation non remplis\n" +
                        "• Activité annulée ou reportée\n" +
                        "• Autres raisons spécifiques au partenaire\n\n" +
                        "💡 QUE FAIRE MAINTENANT?\n" +
                        "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n" +
                        "• Contactez le partenaire pour plus d'informations:\n" +
                        "  📧 Email: %s\n" +
                        "  📞 Téléphone: %s\n\n" +
                        "• Consultez d'autres activités disponibles sur notre plateforme\n" +
                        "• Vous pouvez soumettre une nouvelle demande ultérieurement\n\n" +
                        "Nous vous encourageons à explorer nos autres activités qui pourraient vous intéresser.\n\n" +
                        "Cordialement,\n" +
                        "L'équipe NEXORA\n\n" +
                        "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n" +
                        "Cet email est envoyé automatiquement, merci de ne pas y répondre.",
                clientNom, activiteNom, partenaireNom, partenaireEmail, partenaireTel
        );
    }

    /**
     * Crée un email HTML d'acceptation (version stylée)
     */
    public static String createAcceptanceEmailHtml(String clientNom, String activiteNom,
                                                   String lieu, String date, double prix,
                                                   String partenaireNom, String partenaireEmail,
                                                   String partenaireTel) {
        return String.format(
                "<!DOCTYPE html>" +
                        "<html>" +
                        "<head><meta charset='UTF-8'></head>" +
                        "<body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>" +
                        "<div style='max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f9f9f9;'>" +
                        "<div style='background-color: #4CAF50; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0;'>" +
                        "<h1 style='margin: 0;'>✅ Demande Acceptée !</h1>" +
                        "</div>" +
                        "<div style='background-color: white; padding: 30px; border-radius: 0 0 8px 8px;'>" +
                        "<p>Bonjour <strong>%s</strong>,</p>" +
                        "<p style='font-size: 16px;'>Bonne nouvelle ! Votre demande de participation a été <strong style='color: #4CAF50;'>ACCEPTÉE</strong>.</p>" +
                        "<div style='background-color: #f0f8ff; padding: 20px; border-left: 4px solid #2196F3; margin: 20px 0;'>" +
                        "<h3 style='margin-top: 0; color: #2196F3;'>📋 Détails de l'activité</h3>" +
                        "<p><strong>Activité:</strong> %s</p>" +
                        "<p><strong>Lieu:</strong> %s</p>" +
                        "<p><strong>Date:</strong> %s</p>" +
                        "<p><strong>Prix:</strong> <span style='font-size: 18px; color: #4CAF50;'>%.2f TND</span></p>" +
                        "</div>" +
                        "<div style='background-color: #fff3e0; padding: 20px; border-left: 4px solid #FF9800; margin: 20px 0;'>" +
                        "<h3 style='margin-top: 0; color: #FF9800;'>👤 Informations du partenaire</h3>" +
                        "<p><strong>Nom:</strong> %s</p>" +
                        "<p><strong>Email:</strong> <a href='mailto:%s'>%s</a></p>" +
                        "<p><strong>Téléphone:</strong> %s</p>" +
                        "</div>" +
                        "<div style='background-color: #e8f5e9; padding: 20px; border-left: 4px solid #4CAF50; margin: 20px 0;'>" +
                        "<h3 style='margin-top: 0; color: #4CAF50;'>💳 Prochaines étapes</h3>" +
                        "<ol>" +
                        "<li>Connectez-vous à votre compte</li>" +
                        "<li>Allez dans la section 'Activités'</li>" +
                        "<li>Sélectionnez l'activité '%s'</li>" +
                        "<li>Cliquez sur 'Voir Statut' pour confirmer</li>" +
                        "<li>Cliquez sur 'Payer' pour finaliser</li>" +
                        "</ol>" +
                        "</div>" +
                        "<div style='background-color: #ffebee; padding: 15px; border-left: 4px solid #f44336; margin: 20px 0;'>" +
                        "<p style='margin: 0;'><strong>⚠️ IMPORTANT:</strong> Veuillez effectuer le paiement dans les 48 heures.</p>" +
                        "</div>" +
                        "<p style='text-align: center; margin-top: 30px;'>" +
                        "<a href='#' style='background-color: #4CAF50; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;'>Accéder à mon compte</a>" +
                        "</p>" +
                        "</div>" +
                        "<div style='text-align: center; padding: 20px; color: #666; font-size: 12px;'>" +
                        "<p>Cordialement,<br>L'équipe NEXORA</p>" +
                        "<p>Cet email est envoyé automatiquement, merci de ne pas y répondre.</p>" +
                        "</div>" +
                        "</div>" +
                        "</body>" +
                        "</html>",
                clientNom, activiteNom, lieu, date, prix,
                partenaireNom, partenaireEmail, partenaireEmail, partenaireTel,
                activiteNom
        );
    }

    /**
     * Crée un email pour notifier le partenaire d'une nouvelle demande
     */
    public static String createNewDemandeEmailForPartenaire(String partenaireNom, String clientNom,
                                                            String clientEmail, String clientTel,
                                                            String activiteNom, String lieu, String date) {
        return String.format(
                "Bonjour %s,\n\n" +
                        "Vous avez reçu une NOUVELLE DEMANDE de participation !\n\n" +
                        "📋 DÉTAILS DE LA DEMANDE\n" +
                        "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n" +
                        "Client: %s\n" +
                        "Email: %s\n" +
                        "Téléphone: %s\n\n" +
                        "Activité: %s\n" +
                        "Lieu: %s\n" +
                        "Date: %s\n\n" +
                        "⚡ ACTION REQUISE\n" +
                        "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n" +
                        "1. Connectez-vous à votre espace partenaire\n" +
                        "2. Allez dans 'Gestion des Activités'\n" +
                        "3. Cliquez sur 'Voir les participants'\n" +
                        "4. Acceptez ou refusez la demande\n\n" +
                        "⏰ Le client attend votre réponse !\n\n" +
                        "Cordialement,\n" +
                        "L'équipe NEXORA\n\n" +
                        "---\n" +
                        "Cet email est envoyé automatiquement, merci de ne pas y répondre.",
                partenaireNom, clientNom, clientEmail, clientTel,
                activiteNom, lieu, date
        );
    }

    /**
     * Crée un email HTML pour notifier le partenaire d'une nouvelle demande
     */
    public static String createNewDemandeEmailHtmlForPartenaire(String partenaireNom, String clientNom,
                                                                String clientEmail, String clientTel,
                                                                String activiteNom, String lieu, String date) {
        return String.format(
                "<!DOCTYPE html>" +
                        "<html>" +
                        "<head><meta charset='UTF-8'></head>" +
                        "<body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>" +
                        "<div style='max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f9f9f9;'>" +
                        "<div style='background-color: #2196F3; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0;'>" +
                        "<h1 style='margin: 0;'> Nouvelle Demande !</h1>" +
                        "</div>" +
                        "<div style='background-color: white; padding: 30px; border-radius: 0 0 8px 8px;'>" +
                        "<p>Bonjour <strong>%s</strong>,</p>" +
                        "<p style='font-size: 16px;'>Vous avez reçu une <strong style='color: #2196F3;'>NOUVELLE DEMANDE</strong> de participation !</p>" +
                        "<div style='background-color: #e3f2fd; padding: 20px; border-left: 4px solid #2196F3; margin: 20px 0;'>" +
                        "<h3 style='margin-top: 0; color: #2196F3;'>👤 Informations du client</h3>" +
                        "<p><strong>Nom:</strong> %s</p>" +
                        "<p><strong>Email:</strong> <a href='mailto:%s'>%s</a></p>" +
                        "<p><strong>Téléphone:</strong> %s</p>" +
                        "</div>" +
                        "<div style='background-color: #f0f8ff; padding: 20px; border-left: 4px solid #4CAF50; margin: 20px 0;'>" +
                        "<h3 style='margin-top: 0; color: #4CAF50;'>📋 Détails de l'activité</h3>" +
                        "<p><strong>Activité:</strong> %s</p>" +
                        "<p><strong>Lieu:</strong> %s</p>" +
                        "<p><strong>Date:</strong> %s</p>" +
                        "</div>" +
                        "<div style='background-color: #fff3e0; padding: 20px; border-left: 4px solid #FF9800; margin: 20px 0;'>" +
                        "<h3 style='margin-top: 0; color: #FF9800;'>⚡ Action requise</h3>" +
                        "<ol>" +
                        "<li>Connectez-vous à votre espace partenaire</li>" +
                        "<li>Allez dans 'Gestion des Activités'</li>" +
                        "<li>Cliquez sur 'Voir les participants'</li>" +
                        "<li>Acceptez ou refusez la demande</li>" +
                        "</ol>" +
                        "</div>" +
                        "<div style='background-color: #ffebee; padding: 15px; border-left: 4px solid #f44336; margin: 20px 0;'>" +
                        "<p style='margin: 0;'><strong>⏰ IMPORTANT:</strong> Le client attend votre réponse !</p>" +
                        "</div>" +
                        "<p style='text-align: center; margin-top: 30px;'>" +
                        "<a href='#' style='background-color: #2196F3; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;'>Accéder à mon espace</a>" +
                        "</p>" +
                        "</div>" +
                        "<div style='text-align: center; padding: 20px; color: #666; font-size: 12px;'>" +
                        "<p>Cordialement,<br>L'équipe NEXORA</p>" +
                        "<p>Cet email est envoyé automatiquement, merci de ne pas y répondre.</p>" +
                        "</div>" +
                        "</div>" +
                        "</body>" +
                        "</html>",
                partenaireNom, clientNom, clientEmail, clientEmail, clientTel,
                activiteNom, lieu, date
        );
    }

    /**
     * Crée un email HTML de refus (version stylée en rouge)
     */
    public static String createRejectionEmailHtml(String clientNom, String activiteNom,
                                                  String lieu, String date,
                                                  String partenaireNom, String partenaireEmail,
                                                  String partenaireTel) {
        return String.format(
                "<!DOCTYPE html>" +
                        "<html>" +
                        "<head><meta charset='UTF-8'></head>" +
                        "<body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>" +
                        "<div style='max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f9f9f9;'>" +
                        "<div style='background-color: #EF4444; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0;'>" +
                        "<h1 style='margin: 0;'>❌ Demande Refusée</h1>" +
                        "</div>" +
                        "<div style='background-color: white; padding: 30px; border-radius: 0 0 8px 8px;'>" +
                        "<p>Bonjour <strong>%s</strong>,</p>" +
                        "<p style='font-size: 16px;'>Nous sommes désolés de vous informer que votre demande de participation a été <strong style='color: #EF4444;'>REFUSÉE</strong>.</p>" +
                        "<div style='background-color: #fef2f2; padding: 20px; border-left: 4px solid #EF4444; margin: 20px 0;'>" +
                        "<h3 style='margin-top: 0; color: #EF4444;'>📋 Détails de l'activité</h3>" +
                        "<p><strong>Activité:</strong> %s</p>" +
                        "<p><strong>Lieu:</strong> %s</p>" +
                        "<p><strong>Date:</strong> %s</p>" +
                        "</div>" +
                        "<div style='background-color: #fff3e0; padding: 20px; border-left: 4px solid #FF9800; margin: 20px 0;'>" +
                        "<h3 style='margin-top: 0; color: #FF9800;'>👤 Informations du partenaire</h3>" +
                        "<p><strong>Nom:</strong> %s</p>" +
                        "<p><strong>Email:</strong> <a href='mailto:%s'>%s</a></p>" +
                        "<p><strong>Téléphone:</strong> %s</p>" +
                        "</div>" +
                        "<div style='background-color: #fef2f2; padding: 20px; border-left: 4px solid #EF4444; margin: 20px 0;'>" +
                        "<h3 style='margin-top: 0; color: #EF4444;'>❓ Raisons possibles</h3>" +
                        "<ul>" +
                        "<li>Places complètes</li>" +
                        "<li>Critères de participation non remplis</li>" +
                        "<li>Activité annulée ou reportée</li>" +
                        "<li>Autres contraintes organisationnelles</li>" +
                        "</ul>" +
                        "</div>" +
                        "<div style='background-color: #e0f2fe; padding: 20px; border-left: 4px solid #3B82F6; margin: 20px 0;'>" +
                        "<h3 style='margin-top: 0; color: #3B82F6;'>💡 Que faire maintenant ?</h3>" +
                        "<ul>" +
                        "<li>Consultez d'autres activités disponibles</li>" +
                        "<li>Contactez le partenaire pour plus d'informations</li>" +
                        "<li>Restez à l'affût de nouvelles opportunités</li>" +
                        "</ul>" +
                        "</div>" +
                        "<p style='text-align: center; margin-top: 30px;'>" +
                        "<a href='#' style='background-color: #3B82F6; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;'>Voir d'autres activités</a>" +
                        "</p>" +
                        "</div>" +
                        "<div style='text-align: center; padding: 20px; color: #666; font-size: 12px;'>" +
                        "<p>Cordialement,<br>L'équipe NEXORA</p>" +
                        "<p>Cet email est envoyé automatiquement, merci de ne pas y répondre.</p>" +
                        "</div>" +
                        "</div>" +
                        "</body>" +
                        "</html>",
                clientNom, activiteNom, lieu, date,
                partenaireNom, partenaireEmail, partenaireEmail, partenaireTel
        );
    }

    /**
     * Crée un email texte simple de refus (fallback)
     */
    public static String createRejectionEmailBody(String clientNom, String activiteNom,
                                                  String lieu, String date,
                                                  String partenaireNom, String partenaireEmail,
                                                  String partenaireTel) {
        return String.format(
                "Bonjour %s,\n\n" +
                        "Nous sommes désolés de vous informer que votre demande de participation a été REFUSÉE.\n\n" +
                        "DÉTAILS:\n" +
                        "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n" +
                        "• Activité: %s\n" +
                        "• Lieu: %s\n" +
                        "• Date: %s\n" +
                        "• Partenaire: %s\n\n" +
                        "RAISONS POSSIBLES:\n" +
                        "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n" +
                        "• Places complètes\n" +
                        "• Critères de participation non remplis\n" +
                        "• Activité annulée ou reportée\n\n" +
                        "Pour plus d'informations, vous pouvez contacter le partenaire:\n" +
                        "Email: %s\n" +
                        "Téléphone: %s\n\n" +
                        "N'hésitez pas à consulter d'autres activités disponibles sur notre plateforme.\n\n" +
                        "Cordialement,\n" +
                        "L'équipe NEXORA",
                clientNom, activiteNom, lieu, date, partenaireNom,
                partenaireEmail, partenaireTel
        );
    }

    /**
     * Envoie un email avec une pièce jointe (PDF)
     * @param to Email du destinataire
     * @param subject Sujet de l'email
     * @param body Corps de l'email
     * @param attachmentPath Chemin du fichier à joindre
     */
    public static void envoyerEmailAvecPieceJointe(String to, String subject, String body, String attachmentPath) {
        Properties config = loadEmailConfig();

        // Configuration de la session
        Properties props = new Properties();
        props.put("mail.smtp.host", config.getProperty("mail.smtp.host"));
        props.put("mail.smtp.port", config.getProperty("mail.smtp.port"));
        props.put("mail.smtp.auth", config.getProperty("mail.smtp.auth"));
        props.put("mail.smtp.starttls.enable", config.getProperty("mail.smtp.starttls.enable"));

        // Debug mode
        if ("true".equals(config.getProperty("mail.debug"))) {
            props.put("mail.debug", "true");
        }

        final String username = config.getProperty("mail.from");
        final String password = config.getProperty("mail.password");
        final String displayName = config.getProperty("mail.display.name", "Nexora");

        Session session = Session.getInstance(props, new Authenticator() {
            @Override
            protected PasswordAuthentication getPasswordAuthentication() {
                return new PasswordAuthentication(username, password);
            }
        });

        try {
            Message message = new MimeMessage(session);
            message.setFrom(new InternetAddress(username, displayName));
            message.setRecipients(Message.RecipientType.TO, InternetAddress.parse(to));
            message.setSubject(subject);

            // Créer le corps du message
            MimeBodyPart messageBodyPart = new MimeBodyPart();
            messageBodyPart.setText(body, "UTF-8");

            // Créer la partie pour la pièce jointe
            MimeBodyPart attachmentBodyPart = new MimeBodyPart();
            attachmentBodyPart.attachFile(attachmentPath);

            // Combiner les parties
            Multipart multipart = new MimeMultipart();
            multipart.addBodyPart(messageBodyPart);
            multipart.addBodyPart(attachmentBodyPart);

            // Définir le contenu du message
            message.setContent(multipart);

            // Envoyer l'email
            Transport.send(message);

            System.out.println("✅ Email avec pièce jointe envoyé à: " + to);
            System.out.println("   Sujet: " + subject);
            System.out.println("   Pièce jointe: " + attachmentPath);

        } catch (Exception e) {
            System.err.println("❌ Erreur lors de l'envoi de l'email avec pièce jointe: " + e.getMessage());
            e.printStackTrace();
        }
    }
}
