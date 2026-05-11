package com.pi.utils;

import com.google.zxing.BarcodeFormat;
import com.google.zxing.EncodeHintType;
import com.google.zxing.WriterException;
import com.google.zxing.client.j2se.MatrixToImageWriter;
import com.google.zxing.common.BitMatrix;
import com.google.zxing.qrcode.QRCodeWriter;
import com.google.zxing.qrcode.decoder.ErrorCorrectionLevel;

import java.awt.image.BufferedImage;
import java.io.File;
import java.io.IOException;
import java.nio.file.FileSystems;
import java.nio.file.Path;
import java.util.HashMap;
import java.util.Map;
import javax.imageio.ImageIO;

/**
 * Service de génération de QR Codes
 * Utilisé pour les reçus de paiement et les tickets d'activité
 */
public class QRCodeService {

    private static final String QR_CODE_DIRECTORY = "qrcodes/";
    private static final int QR_CODE_SIZE = 300; // Taille en pixels

    /**
     * Génère un QR Code pour un reçu de paiement
     * @param reference Référence du paiement
     * @param clientNom Nom du client
     * @param activiteNom Nom de l'activité
     * @param montant Montant payé
     * @param methodePaiement Méthode de paiement
     * @return Le chemin du fichier QR Code généré
     */
    public static String genererQRCodePaiement(String reference, String clientNom,
                                               String activiteNom, double montant,
                                               String methodePaiement) {
        // Créer le contenu du QR Code - TEXTE SIMPLE
        // Fonctionne sur tous les téléphones sans besoin d'internet
        String qrContent = String.format(
                "╔══════════════════════════════╗\n" +
                        "║   NEXORA - Reçu Paiement    ║\n" +
                        "╚══════════════════════════════╝\n\n" +
                        "📋 Référence: %s\n\n" +
                        "👤 Client: %s\n\n" +
                        "🎯 Activité: %s\n\n" +
                        "💳 Méthode: %s\n\n" +
                        "💰 Montant: %.2f TND\n\n" +
                        "✅ Paiement vérifié et authentique\n" +
                        "🌐 www.nexora.tn",
                reference, clientNom, activiteNom, methodePaiement, montant
        );

        return genererQRCode(qrContent, "paiement_" + reference);
    }

    /**
     * Génère un QR Code pour un ticket d'activité
     * @param demandeId ID de la demande
     * @param clientNom Nom du client
     * @param activiteNom Nom de l'activité
     * @param dateActivite Date de l'activité
     * @return Le chemin du fichier QR Code généré
     */
    public static String genererQRCodeTicket(int demandeId, String clientNom,
                                             String activiteNom, String dateActivite) {
        String qrContent = String.format(
                "NEXORA - Ticket d'Entrée\n" +
                        "Ticket #%d\n" +
                        "Client: %s\n" +
                        "Activité: %s\n" +
                        "Date: %s\n" +
                        "Scan à l'entrée",
                demandeId, clientNom, activiteNom, dateActivite
        );

        return genererQRCode(qrContent, "ticket_" + demandeId);
    }

    /**
     * Génère un QR Code pour partager une activité
     * @param activiteId ID de l'activité
     * @param activiteNom Nom de l'activité
     * @return Le chemin du fichier QR Code généré
     */
    public static String genererQRCodeActivite(int activiteId, String activiteNom) {
        String qrContent = String.format(
                "https://nexora.tn/activite/%d\n" +
                        "Activité: %s\n" +
                        "Scannez pour voir les détails",
                activiteId, activiteNom
        );

        return genererQRCode(qrContent, "activite_" + activiteId);
    }

    /**
     * Génère un QR Code générique
     * @param content Contenu du QR Code
     * @param fileName Nom du fichier (sans extension)
     * @return Le chemin du fichier QR Code généré
     */
    public static String genererQRCode(String content, String fileName) {
        try {
            // Créer le dossier qrcodes s'il n'existe pas
            File directory = new File(QR_CODE_DIRECTORY);
            if (!directory.exists()) {
                directory.mkdirs();
            }

            // Configuration du QR Code
            Map<EncodeHintType, Object> hints = new HashMap<>();
            hints.put(EncodeHintType.ERROR_CORRECTION, ErrorCorrectionLevel.H);
            hints.put(EncodeHintType.CHARACTER_SET, "UTF-8");
            hints.put(EncodeHintType.MARGIN, 1);

            // Générer le QR Code
            QRCodeWriter qrCodeWriter = new QRCodeWriter();
            BitMatrix bitMatrix = qrCodeWriter.encode(
                    content,
                    BarcodeFormat.QR_CODE,
                    QR_CODE_SIZE,
                    QR_CODE_SIZE,
                    hints
            );

            // Sauvegarder en PNG
            String filePath = QR_CODE_DIRECTORY + fileName + ".png";
            Path path = FileSystems.getDefault().getPath(filePath);
            MatrixToImageWriter.writeToPath(bitMatrix, "PNG", path);

            System.out.println("✅ QR Code généré: " + filePath);
            return filePath;

        } catch (WriterException | IOException e) {
            System.err.println("❌ Erreur lors de la génération du QR Code: " + e.getMessage());
            e.printStackTrace();
            return null;
        }
    }

    /**
     * Génère un QR Code et retourne l'image BufferedImage
     * Utile pour l'intégration dans un PDF
     */
    public static BufferedImage genererQRCodeImage(String content) {
        try {
            Map<EncodeHintType, Object> hints = new HashMap<>();
            hints.put(EncodeHintType.ERROR_CORRECTION, ErrorCorrectionLevel.H);
            hints.put(EncodeHintType.CHARACTER_SET, "UTF-8");
            hints.put(EncodeHintType.MARGIN, 1);

            QRCodeWriter qrCodeWriter = new QRCodeWriter();
            BitMatrix bitMatrix = qrCodeWriter.encode(
                    content,
                    BarcodeFormat.QR_CODE,
                    QR_CODE_SIZE,
                    QR_CODE_SIZE,
                    hints
            );

            return MatrixToImageWriter.toBufferedImage(bitMatrix);

        } catch (WriterException e) {
            System.err.println("❌ Erreur lors de la génération du QR Code: " + e.getMessage());
            e.printStackTrace();
            return null;
        }
    }
}
