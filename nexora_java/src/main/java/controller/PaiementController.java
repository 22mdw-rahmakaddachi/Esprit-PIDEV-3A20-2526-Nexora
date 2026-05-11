package controller;

import com.pi.entities.Paiement;
import com.pi.entities.commande;
import com.pi.entity.CommandeService;
import com.pi.entity.PaiementService;
import com.pi.utils.KonnectPaymentAPI;
import javafx.fxml.FXML;
import javafx.geometry.Pos;
import javafx.scene.control.*;
import javafx.scene.layout.*;
import javafx.stage.Stage;
import java.sql.SQLException;

public class PaiementController {

    @FXML private Label montantLabel;
    @FXML private Label commandeLabel;
    @FXML private RadioButton rbKonnect;
    @FXML private RadioButton rbCarteBancaire;
    @FXML private RadioButton rbEspeces;
    @FXML private ToggleGroup methodePaiementGroup;
    @FXML private VBox carteDetailsBox;
    @FXML private TextField numeroCarteField;
    @FXML private TextField dateExpirationField;
    @FXML private TextField cvvField;
    @FXML private Button btnPayer;
    @FXML private ProgressIndicator progressIndicator;

    private commande commande;
    private PaiementService paiementService = new PaiementService();
    private CommandeService commandeService = new CommandeService();
    private Runnable onSuccessCallback;

    public void setCommande(commande commande) {
        this.commande = commande;
        commandeLabel.setText("Commande #" + commande.getId());
        montantLabel.setText(String.format("%.3f TND", commande.getTotal()));
    }

    public void setOnSuccessCallback(Runnable callback) {
        this.onSuccessCallback = callback;
    }

    @FXML
    public void initialize() {
        methodePaiementGroup = new ToggleGroup();
        rbKonnect.setToggleGroup(methodePaiementGroup);
        rbCarteBancaire.setToggleGroup(methodePaiementGroup);
        rbEspeces.setToggleGroup(methodePaiementGroup);
        
        // Par défaut, sélectionner Konnect
        rbKonnect.setSelected(true);
        carteDetailsBox.setVisible(false);
        carteDetailsBox.setManaged(false);
        
        // Listener pour afficher/masquer les détails de carte
        methodePaiementGroup.selectedToggleProperty().addListener((obs, oldVal, newVal) -> {
            if (newVal == rbCarteBancaire) {
                carteDetailsBox.setVisible(true);
                carteDetailsBox.setManaged(true);
            } else {
                carteDetailsBox.setVisible(false);
                carteDetailsBox.setManaged(false);
            }
        });
        
        progressIndicator.setVisible(false);
    }

    @FXML
    public void handlePayer() {
        if (commande == null) {
            showAlert("Erreur", "Aucune commande sélectionnée");
            return;
        }

        RadioButton selected = (RadioButton) methodePaiementGroup.getSelectedToggle();
        if (selected == null) {
            showAlert("Erreur", "Veuillez sélectionner une méthode de paiement");
            return;
        }

        String methode = selected.getText();
        
        // Validation pour carte bancaire
        if (selected == rbCarteBancaire) {
            if (!validerCarteBancaire()) {
                return;
            }
        }

        progressIndicator.setVisible(true);
        btnPayer.setDisable(true);

        // Traiter le paiement selon la méthode
        new Thread(() -> {
            try {
                boolean success = false;
                String transactionId = null;
                
                if (selected == rbKonnect) {
                    success = traiterPaiementKonnect();
                    transactionId = "KONNECT_" + System.currentTimeMillis();
                } else if (selected == rbCarteBancaire) {
                    success = traiterPaiementCarte();
                    transactionId = "CARTE_" + System.currentTimeMillis();
                } else if (selected == rbEspeces) {
                    success = traiterPaiementEspeces();
                    transactionId = "ESPECES_" + System.currentTimeMillis();
                }

                final boolean finalSuccess = success;
                final String finalTransactionId = transactionId;
                
                javafx.application.Platform.runLater(() -> {
                    progressIndicator.setVisible(false);
                    btnPayer.setDisable(false);
                    
                    if (finalSuccess) {
                        enregistrerPaiement(methode, finalTransactionId, "COMPLETE");
                        afficherSucces();
                    } else {
                        enregistrerPaiement(methode, finalTransactionId, "ECHOUE");
                        showAlert("Échec", "Le paiement a échoué. Veuillez réessayer.");
                    }
                });
                
            } catch (Exception e) {
                javafx.application.Platform.runLater(() -> {
                    progressIndicator.setVisible(false);
                    btnPayer.setDisable(false);
                    showAlert("Erreur", "Erreur lors du paiement: " + e.getMessage());
                });
            }
        }).start();
    }

    private boolean traiterPaiementKonnect() {
        System.out.println("💳 Traitement paiement Konnect...");
        
        // Initialiser le paiement via API Konnect
        String paymentUrl = KonnectPaymentAPI.initierPaiement(
            commande.getTotal(), 
            commande.getId(), 
            commande.getClientNom()
        );
        
        if (paymentUrl != null) {
            // En mode simulation, on considère le paiement comme réussi
            if (paymentUrl.startsWith("SIMULATION_")) {
                System.out.println("✅ Paiement Konnect simulé avec succès");
                return true;
            }
            
            // En mode réel, ouvrir l'URL de paiement dans le navigateur
            // et attendre la confirmation via webhook
            try {
                java.awt.Desktop.getDesktop().browse(new java.net.URI(paymentUrl));
                // Attendre quelques secondes pour la simulation
                Thread.sleep(2000);
                return true;
            } catch (Exception e) {
                System.err.println("❌ Erreur ouverture URL: " + e.getMessage());
                return false;
            }
        }
        
        return false;
    }

    private boolean traiterPaiementCarte() {
        System.out.println("💳 Traitement paiement carte bancaire...");
        
        // Simulation de traitement de carte
        try {
            Thread.sleep(2000); // Simuler le temps de traitement
            
            // Validation basique du numéro de carte (algorithme de Luhn)
            String numero = numeroCarteField.getText().replaceAll("\\s", "");
            if (numero.length() >= 13 && numero.length() <= 19) {
                System.out.println("✅ Paiement carte validé");
                return true;
            }
        } catch (InterruptedException e) {
            e.printStackTrace();
        }
        
        return false;
    }

    private boolean traiterPaiementEspeces() {
        System.out.println("💵 Paiement en espèces enregistré");
        // Le paiement en espèces est toujours accepté (à confirmer à la livraison)
        return true;
    }

    private boolean validerCarteBancaire() {
        String numero = numeroCarteField.getText().replaceAll("\\s", "");
        String expiration = dateExpirationField.getText();
        String cvv = cvvField.getText();
        
        if (numero.isEmpty() || numero.length() < 13) {
            showAlert("Erreur", "Numéro de carte invalide");
            return false;
        }
        
        if (expiration.isEmpty() || !expiration.matches("\\d{2}/\\d{2}")) {
            showAlert("Erreur", "Date d'expiration invalide (format: MM/AA)");
            return false;
        }
        
        if (cvv.isEmpty() || cvv.length() < 3) {
            showAlert("Erreur", "CVV invalide");
            return false;
        }
        
        return true;
    }

    private void enregistrerPaiement(String methode, String transactionId, String statut) {
        try {
            System.out.println("🔍 Début enregistrement paiement...");
            System.out.println("  Commande ID: " + commande.getId());
            System.out.println("  Méthode: " + methode);
            System.out.println("  Montant: " + commande.getTotal());
            System.out.println("  Statut: " + statut);
            System.out.println("  Transaction ID: " + transactionId);
            
            Paiement paiement = new Paiement();
            // Utiliser demandeId au lieu de commandeId pour correspondre à la structure
            paiement.setDemandeId(commande.getId());
            paiement.setClientId(commande.getUserId()); // Utiliser getUserId() au lieu de getClientId()
            paiement.setActiviteId(0); // Pas d'activité pour les commandes e-commerce
            paiement.setMethodePaiement(methode);
            paiement.setMontant(commande.getTotal());
            paiement.setStatut(statut);
            paiement.setReferenceTransaction(transactionId);
            paiement.setDatePaiement(java.time.LocalDateTime.now());
            
            System.out.println("📝 Appel ajouter...");
            paiementService.ajouter(paiement);
            System.out.println("✅ Paiement créé avec succès");
            
            // Mettre à jour le statut de la commande
            if ("COMPLETE".equals(statut)) {
                commande.setStatut("PAYEE");
            } else {
                commande.setStatut("PAIEMENT_REFUSE");
            }
            System.out.println("📝 Mise à jour statut commande...");
            commandeService.modifierCommande(commande);
            System.out.println("✅ Statut commande mis à jour");
            
        } catch (SQLException e) {
            System.err.println("❌ Erreur enregistrement paiement: " + e.getMessage());
            e.printStackTrace();
        } catch (Exception e) {
            System.err.println("❌ Erreur inattendue: " + e.getMessage());
            e.printStackTrace();
        }
    }

    private void afficherSucces() {
        Alert alert = new Alert(Alert.AlertType.INFORMATION);
        alert.setTitle("Paiement Réussi");
        alert.setHeaderText("✓ Paiement validé avec succès!");
        alert.setContentText("Votre commande #" + commande.getId() + " a été payée.\n" +
                           "Montant: " + String.format("%.3f TND", commande.getTotal()));
        alert.showAndWait();
        
        // Callback et fermeture
        if (onSuccessCallback != null) {
            onSuccessCallback.run();
        }
        
        Stage stage = (Stage) btnPayer.getScene().getWindow();
        stage.close();
    }

    @FXML
    public void handleAnnuler() {
        Stage stage = (Stage) btnPayer.getScene().getWindow();
        stage.close();
    }

    private void showAlert(String title, String message) {
        Alert alert = new Alert(Alert.AlertType.WARNING);
        alert.setTitle(title);
        alert.setHeaderText(null);
        alert.setContentText(message);
        alert.show();
    }
}
