package com.pi.utils;

import java.io.File;
import java.io.FileWriter;
import java.io.IOException;
import java.time.LocalDateTime;
import java.time.format.DateTimeFormatter;

/**
 * Génère des pages HTML de vérification pour les paiements
 */
public class VerificationPageGenerator {

    private static final String VERIFICATION_DIRECTORY = "verification/";

    /**
     * Génère une page HTML de vérification pour un paiement
     * @param reference Référence du paiement
     * @param clientNom Nom du client
     * @param activiteNom Nom de l'activité
     * @param montant Montant payé
     * @param methodePaiement Méthode de paiement
     * @return Le chemin du fichier HTML généré
     */
    public static String genererPageVerification(String reference, String clientNom,
                                                 String activiteNom, double montant,
                                                 String methodePaiement) {
        try {
            // Créer le dossier verification s'il n'existe pas
            File directory = new File(VERIFICATION_DIRECTORY);
            if (!directory.exists()) {
                directory.mkdirs();
            }

            String fileName = VERIFICATION_DIRECTORY + reference + ".html";
            String dateTime = LocalDateTime.now().format(DateTimeFormatter.ofPattern("dd/MM/yyyy HH:mm"));

            String htmlContent = generateHTML(reference, clientNom, activiteNom, montant, methodePaiement, dateTime);

            // Écrire le fichier HTML
            FileWriter writer = new FileWriter(fileName);
            writer.write(htmlContent);
            writer.close();

            System.out.println("✅ Page de vérification générée: " + fileName);

            // Retourner le chemin absolu pour le QR Code
            return new File(fileName).getAbsolutePath();

        } catch (IOException e) {
            System.err.println("❌ Erreur lors de la génération de la page: " + e.getMessage());
            e.printStackTrace();
            return null;
        }
    }

    private static String generateHTML(String reference, String clientNom, String activiteNom,
                                       double montant, String methodePaiement, String dateTime) {
        return String.format("""
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vérification Paiement - %s</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%%, #764ba2 100%%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        .container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 500px;
            width: 100%%;
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%%, #764ba2 100%%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 { font-size: 32px; margin-bottom: 10px; }
        .header p { font-size: 16px; opacity: 0.9; }
        .status {
            background: #10B981;
            color: white;
            padding: 20px;
            text-align: center;
            font-size: 20px;
            font-weight: bold;
        }
        .status::before { content: "✓ "; font-size: 24px; }
        .content { padding: 30px; }
        .info-group {
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid #E5E7EB;
        }
        .info-group:last-child { border-bottom: none; margin-bottom: 0; }
        .info-label {
            font-size: 12px;
            color: #6B7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
        }
        .info-value {
            font-size: 18px;
            color: #1F2937;
            font-weight: 600;
        }
        .amount {
            font-size: 32px;
            color: #10B981;
            font-weight: bold;
        }
        .footer {
            background: #F9FAFB;
            padding: 20px;
            text-align: center;
            color: #6B7280;
            font-size: 14px;
        }
        .footer a { color: #667eea; text-decoration: none; }
        @media (max-width: 600px) {
            .header h1 { font-size: 24px; }
            .amount { font-size: 24px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>NEXORA</h1>
            <p>Plateforme de Gestion d'Activités</p>
        </div>
        <div class="status">Paiement Vérifié</div>
        <div class="content">
            <div class="info-group">
                <div class="info-label">Référence</div>
                <div class="info-value">%s</div>
            </div>
            <div class="info-group">
                <div class="info-label">Client</div>
                <div class="info-value">%s</div>
            </div>
            <div class="info-group">
                <div class="info-label">Activité</div>
                <div class="info-value">%s</div>
            </div>
            <div class="info-group">
                <div class="info-label">Méthode de Paiement</div>
                <div class="info-value">%s</div>
            </div>
            <div class="info-group">
                <div class="info-label">Montant Payé</div>
                <div class="amount">%.2f TND</div>
            </div>
            <div class="info-group">
                <div class="info-label">Date de Paiement</div>
                <div class="info-value">%s</div>
            </div>
        </div>
        <div class="footer">
            <p>Ce reçu est authentique et vérifié par NEXORA</p>
            <p><a href="https://nexora.tn">www.nexora.tn</a></p>
        </div>
    </div>
</body>
</html>
""", reference, reference, clientNom, activiteNom, methodePaiement, montant, dateTime);
    }
}
