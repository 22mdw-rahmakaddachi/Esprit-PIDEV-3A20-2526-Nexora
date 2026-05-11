package com.pi.utils;

import com.itextpdf.kernel.pdf.PdfWriter;
import com.itextpdf.kernel.pdf.PdfDocument;
import com.itextpdf.layout.Document;
import com.itextpdf.layout.element.Paragraph;
import com.itextpdf.layout.element.Table;
import com.itextpdf.layout.element.Image;
import com.itextpdf.layout.properties.TextAlignment;
import com.itextpdf.layout.properties.UnitValue;
import com.itextpdf.layout.properties.HorizontalAlignment;
import com.itextpdf.kernel.colors.ColorConstants;
import com.itextpdf.layout.borders.SolidBorder;
import com.itextpdf.io.image.ImageDataFactory;

import java.io.File;
import java.time.LocalDateTime;
import java.time.format.DateTimeFormatter;
import java.awt.image.BufferedImage;
import javax.imageio.ImageIO;

/**
 * Service de génération de reçus PDF pour les paiements
 */
public class PdfReceiptGenerator {

    private static final String PDF_DIRECTORY = "receipts/";

    /**
     * Génère un reçu PDF pour un paiement
     * @param reference Référence du paiement
     * @param clientNom Nom du client
     * @param clientEmail Email du client
     * @param activiteNom Nom de l'activité
     * @param montant Montant payé
     * @param methodePaiement Méthode de paiement utilisée
     * @return Le chemin du fichier PDF généré
     */
    public static String genererRecuPaiement(
            String reference,
            String clientNom,
            String clientEmail,
            String activiteNom,
            double montant,
            String methodePaiement) {

        try {
            // Créer le dossier receipts s'il n'existe pas
            File directory = new File(PDF_DIRECTORY);
            if (!directory.exists()) {
                directory.mkdirs();
            }

            // Nom du fichier PDF
            String fileName = PDF_DIRECTORY + "recu_" + reference + ".pdf";

            // Créer le document PDF
            PdfWriter writer = new PdfWriter(fileName);
            PdfDocument pdfDoc = new PdfDocument(writer);
            Document document = new Document(pdfDoc);

            // En-tête
            Paragraph header = new Paragraph("NEXORA")
                    .setFontSize(24)
                    .setBold()
                    .setTextAlignment(TextAlignment.CENTER)
                    .setFontColor(ColorConstants.BLUE);
            document.add(header);

            Paragraph subHeader = new Paragraph("Reçu de Paiement")
                    .setFontSize(18)
                    .setTextAlignment(TextAlignment.CENTER)
                    .setMarginBottom(20);
            document.add(subHeader);

            // Date et heure
            String dateTime = LocalDateTime.now().format(DateTimeFormatter.ofPattern("dd/MM/yyyy HH:mm:ss"));
            Paragraph date = new Paragraph("Date: " + dateTime)
                    .setTextAlignment(TextAlignment.RIGHT)
                    .setMarginBottom(20);
            document.add(date);

            // Informations du client
            document.add(new Paragraph("Informations du Client")
                    .setFontSize(14)
                    .setBold()
                    .setMarginTop(10));

            Table clientTable = new Table(UnitValue.createPercentArray(new float[]{1, 2}))
                    .setWidth(UnitValue.createPercentValue(100))
                    .setMarginBottom(20);

            clientTable.addCell("Nom:");
            clientTable.addCell(clientNom);
            clientTable.addCell("Email:");
            clientTable.addCell(clientEmail);

            document.add(clientTable);

            // Détails du paiement
            document.add(new Paragraph("Détails du Paiement")
                    .setFontSize(14)
                    .setBold()
                    .setMarginTop(10));

            Table paymentTable = new Table(UnitValue.createPercentArray(new float[]{1, 2}))
                    .setWidth(UnitValue.createPercentValue(100))
                    .setMarginBottom(20);

            paymentTable.addCell("Référence:");
            paymentTable.addCell(reference);
            paymentTable.addCell("Activité:");
            paymentTable.addCell(activiteNom);
            paymentTable.addCell("Méthode:");
            paymentTable.addCell(methodePaiement);
            paymentTable.addCell("Montant:");
            paymentTable.addCell(String.format("%.2f TND", montant));

            document.add(paymentTable);

            // Total
            Table totalTable = new Table(UnitValue.createPercentArray(new float[]{1, 1}))
                    .setWidth(UnitValue.createPercentValue(100))
                    .setMarginTop(20)
                    .setBorder(new SolidBorder(ColorConstants.BLACK, 2));

            totalTable.addCell(new Paragraph("TOTAL PAYÉ")
                    .setBold()
                    .setFontSize(14)
                    .setTextAlignment(TextAlignment.RIGHT));
            totalTable.addCell(new Paragraph(String.format("%.2f TND", montant))
                    .setBold()
                    .setFontSize(14)
                    .setFontColor(ColorConstants.GREEN)
                    .setTextAlignment(TextAlignment.RIGHT));

            document.add(totalTable);

            // Générer et ajouter le QR Code
            try {
                String qrContent = String.format(
                        "NEXORA - Reçu de Paiement\nRéférence: %s\nClient: %s\nActivité: %s\nMontant: %.2f TND\nVérification: https://nexora.tn/verify/%s",
                        reference, clientNom, activiteNom, montant, reference
                );

                BufferedImage qrImage = QRCodeService.genererQRCodeImage(qrContent);

                if (qrImage != null) {
                    // Sauvegarder temporairement le QR Code
                    File tempQR = new File("temp_qr.png");
                    ImageIO.write(qrImage, "PNG", tempQR);

                    // Ajouter au PDF
                    Image qrPdfImage = new Image(ImageDataFactory.create(tempQR.getAbsolutePath()));
                    qrPdfImage.setWidth(150);
                    qrPdfImage.setHeight(150);
                    qrPdfImage.setHorizontalAlignment(HorizontalAlignment.CENTER);
                    qrPdfImage.setMarginTop(20);

                    document.add(new Paragraph("\nScannez pour vérifier")
                            .setTextAlignment(TextAlignment.CENTER)
                            .setFontSize(12)
                            .setMarginTop(10));
                    document.add(qrPdfImage);

                    // Supprimer le fichier temporaire
                    tempQR.delete();
                }
            } catch (Exception e) {
                System.err.println("⚠️ Impossible d'ajouter le QR Code au PDF: " + e.getMessage());
            }

            // Pied de page
            Paragraph footer = new Paragraph("\nMerci pour votre confiance!\n" +
                    "Pour toute question, contactez-nous à: support@nexora.com")
                    .setTextAlignment(TextAlignment.CENTER)
                    .setFontSize(10)
                    .setMarginTop(30)
                    .setFontColor(ColorConstants.GRAY);
            document.add(footer);

            // Fermer le document
            document.close();

            System.out.println("✅ Reçu PDF généré: " + fileName);
            return fileName;

        } catch (Exception e) {
            System.err.println("❌ Erreur lors de la génération du PDF: " + e.getMessage());
            e.printStackTrace();
            return null;
        }
    }
}
