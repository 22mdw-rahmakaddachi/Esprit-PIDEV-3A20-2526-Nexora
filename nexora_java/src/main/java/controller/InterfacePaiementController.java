package controller;

import com.pi.entities.Activite;
import com.pi.entities.ParticipationDemande;
import com.pi.entities.Paiement;
import com.pi.entity.ActiviteService;
import com.pi.entity.ParticipationDemandeService;
import com.pi.entity.PaiementService;
import com.pi.utils.NotificationManager;
import javafx.collections.FXCollections;
import javafx.fxml.FXML;
import javafx.scene.control.*;
import javafx.stage.Stage;

import java.sql.SQLException;
import java.util.UUID;

public class InterfacePaiementController {

    @FXML private Label activiteLabel;
    @FXML private Label prixLabel;
    @FXML private Label clientLabel;
    @FXML private ComboBox<String> methodePaiementCombo;
    @FXML private TextField numeroCarteField;
    @FXML private TextField cvvField;
    @FXML private TextField dateExpirationField;
    @FXML private Button payerBtn;
    @FXML private Button annulerBtn;

    private ParticipationDemande demande;
    private Activite activite;
    private PaiementService paiementService;
    private ParticipationDemandeService demandeService;
    private ActiviteService activiteService;

    @FXML
    public void initialize() {
        paiementService = new PaiementService();
        demandeService = new ParticipationDemandeService();
        activiteService = new ActiviteService();

        methodePaiementCombo.setItems(FXCollections.observableArrayList(
                "Konnect (Flouci)", "Carte Bancaire", "E-Dinar", "Wallet Mobile"
        ));
        methodePaiementCombo.setValue("Konnect (Flouci)");

        // Masquer les champs de carte bancaire par défaut (Konnect gère tout)
        numeroCarteField.setVisible(false);
        cvvField.setVisible(false);
        dateExpirationField.setVisible(false);

        // Afficher/masquer les champs selon la méthode sélectionnée
        methodePaiementCombo.setOnAction(e -> {
            String methode = methodePaiementCombo.getValue();
            boolean afficherChamps = "Carte Bancaire".equals(methode);
            
            numeroCarteField.setVisible(afficherChamps);
            cvvField.setVisible(afficherChamps);
            dateExpirationField.setVisible(afficherChamps);
        });
    }

    public void setDemande(ParticipationDemande demande, Activite activite) {
        this.demande = demande;
        this.activite = activite;

        activiteLabel.setText("Activité: " + activite.getNom());
        prixLabel.setText("Montant à payer: " + activite.getPrix() + " TND");
        clientLabel.setText("Client: " + demande.getClientNom());
    }

    @FXML
    private void effectuerPaiement() {
        if (!validerPaiement()) return;

        try {
            String methode = methodePaiementCombo.getValue();
            String reference = "ACT-" + UUID.randomUUID().toString().substring(0, 8);
            
            // Utiliser l'API Konnect pour les paiements électroniques
            if ("Konnect (Flouci)".equals(methode) || "E-Dinar".equals(methode) || "Wallet Mobile".equals(methode)) {
                // Initialiser le paiement avec Konnect
                String paymentUrl = com.pi.utils.KonnectPaymentAPI.initierPaiement(
                        activite.getPrix(),
                        demande.getId(),
                        demande.getClientNom()
                );
                
                if (paymentUrl != null) {
                    // Créer le paiement avec statut EN_ATTENTE
                    Paiement paiement = new Paiement(
                            demande.getId(),
                            demande.getClientId(),
                            activite.getId(),
                            activite.getPrix(),
                            methode
                    );
                    paiement.setStatut("EN_ATTENTE");
                    paiement.setReferenceTransaction(reference);
                    // Stocker l'URL/référence Konnect dans le champ référence transaction

                    paiementService.ajouter(paiement);
                    
                    // Ouvrir l'URL de paiement dans le navigateur
                    if (paymentUrl.startsWith("SIMULATION_")) {
                        // Mode simulation - traiter comme paiement réussi
                        traiterPaiementReussi(paiement, reference);
                    } else {
                        // Mode production - ouvrir le navigateur
                        java.awt.Desktop.getDesktop().browse(java.net.URI.create(paymentUrl));
                        
                        showAlert(Alert.AlertType.INFORMATION, "Paiement Konnect", 
                                "Vous allez être redirigé vers la page de paiement Konnect.\n\n" +
                                "Référence: " + reference + "\n\n" +
                                "Une fois le paiement effectué, votre statut sera mis à jour automatiquement.");
                    }
                } else {
                    showAlert(Alert.AlertType.ERROR, "Erreur", "Impossible d'initialiser le paiement Konnect");
                    return;
                }
            } else {
                // Paiement manuel (Carte Bancaire)
                Paiement paiement = new Paiement(
                        demande.getId(),
                        demande.getClientId(),
                        activite.getId(),
                        activite.getPrix(),
                        methode
                );
                paiement.setStatut("COMPLETE");
                paiement.setReferenceTransaction(reference);

                paiementService.ajouter(paiement);
                traiterPaiementReussi(paiement, reference);
            }

        } catch (Exception e) {
            showAlert(Alert.AlertType.ERROR, "Erreur", "Erreur lors du paiement: " + e.getMessage());
            e.printStackTrace();
        }
    }
    
    /**
     * Traiter un paiement réussi (commun à tous les types de paiement)
     */
    private void traiterPaiementReussi(Paiement paiement, String reference) {
        try {

            // Mettre à jour la demande
            demande.setPaiementEffectue(true);
            demandeService.modifier(demande);

            // ✅ Générer le reçu PDF
            String pdfPath = com.pi.utils.PdfReceiptGenerator.genererRecuPaiement(
                    reference,
                    demande.getClientNom(),
                    demande.getClientEmail(),
                    activite.getNom(),
                    paiement.getMontant(),
                    methodePaiementCombo.getValue()
            );

            // ✅ Créer une notification pour le CLIENT
            NotificationManager.creerNotification(
                    demande.getClientId(),
                    "CLIENT",
                    "PAIEMENT",
                    "Paiement confirmé",
                    "Votre paiement de " + paiement.getMontant() + " TND pour l'activité '" +
                            activite.getNom() + "' a été effectué avec succès. Référence: " + reference,
                    demande.getId()
            );

            // ✅ Créer une notification pour le PARTENAIRE
            NotificationManager.creerNotificationPaiement(
                    activite.getPartenaireId(),
                    demande,
                    activite,
                    paiement.getMontant()
            );

            // ✅ Envoyer email avec PDF en pièce jointe
            if (pdfPath != null) {
                com.pi.utils.EmailService.envoyerEmailAvecPieceJointe(
                        demande.getClientEmail(),
                        "Confirmation de paiement - " + activite.getNom(),
                        "Bonjour " + demande.getClientNom() + ",\n\n" +
                                "Votre paiement a été effectué avec succès!\n\n" +
                                "Détails du paiement:\n" +
                                "- Référence: " + reference + "\n" +
                                "- Activité: " + activite.getNom() + "\n" +
                                "- Montant: " + paiement.getMontant() + " TND\n" +
                                "- Méthode: " + methodePaiementCombo.getValue() + "\n\n" +
                                "Vous trouverez votre reçu en pièce jointe.\n\n" +
                                "Merci pour votre confiance!\n\n" +
                                "L'équipe Nexora",
                        pdfPath
                );
            }

            // Mettre à jour les places disponibles
            activiteService.updatePlacesDisponibles(
                    activite.getId(),
                    activite.getPlacesDisponibles() - 1
            );

            // Afficher confirmation
            Alert alert = new Alert(Alert.AlertType.INFORMATION);
            alert.setTitle("Paiement réussi");
            alert.setHeaderText("Votre paiement a été effectué avec succès !");
            alert.setContentText(
                    "Référence: " + reference + "\n" +
                            "Montant: " + activite.getPrix() + " TND\n" +
                            "Méthode: " + methodePaiementCombo.getValue() + "\n\n" +
                            "Vous recevrez une confirmation par email à:\n" +
                            demande.getClientEmail()
            );
            alert.showAndWait();

            // Fermer la fenêtre
            ((Stage) payerBtn.getScene().getWindow()).close();

        } catch (SQLException e) {
            Alert alert = new Alert(Alert.AlertType.ERROR);
            alert.setTitle("Erreur");
            alert.setHeaderText("Erreur lors du paiement");
            alert.setContentText(e.getMessage());
            alert.showAndWait();
            e.printStackTrace();
        }
    }

    @FXML
    private void annuler() {
        ((Stage) annulerBtn.getScene().getWindow()).close();
    }

    private boolean validerPaiement() {
        if (methodePaiementCombo.getValue() == null) {
            showAlert("Veuillez sélectionner une méthode de paiement");
            return false;
        }

        if (methodePaiementCombo.getValue().equals("Carte Bancaire")) {
            if (numeroCarteField.getText().isEmpty()) {
                showAlert("Veuillez entrer le numéro de carte");
                return false;
            }
            if (cvvField.getText().isEmpty()) {
                showAlert("Veuillez entrer le CVV");
                return false;
            }
            if (dateExpirationField.getText().isEmpty()) {
                showAlert("Veuillez entrer la date d'expiration");
                return false;
            }
        }

        return true;
    }

    private void showAlert(String message) {
        Alert alert = new Alert(Alert.AlertType.WARNING);
        alert.setTitle("Attention");
        alert.setHeaderText(null);
        alert.setContentText(message);
        alert.showAndWait();
    }
    
    private void showAlert(Alert.AlertType type, String title, String message) {
        Alert alert = new Alert(type);
        alert.setTitle(title);
        alert.setHeaderText(null);
        alert.setContentText(message);
        alert.showAndWait();
    }
}
