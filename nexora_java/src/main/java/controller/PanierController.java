package controller;

import com.pi.entities.CodePromo;
import com.pi.entities.PanierItem;
import com.pi.entities.user;
import com.pi.entity.CodePromoService;
import com.pi.entity.CommandeService;
import com.pi.entity.PanierService;
import javafx.fxml.FXML;
import javafx.geometry.Pos;
import javafx.scene.control.*;
import javafx.scene.layout.*;
import java.sql.SQLException;
import java.util.List;

public class PanierController {

    @FXML private VBox panierContainer;
    @FXML private VBox emptyMessage;
    @FXML private Label totalLabel;
    @FXML private Label itemCountLabel;
    @FXML private TextField codePromoField;
    @FXML private Button appliquerCodeBtn;
    @FXML private Label reductionLabel;

    private PanierService panierService = new PanierService();
    private CodePromoService codePromoService = new CodePromoService();
    private CommandeService commandeService = new CommandeService();
    private user currentUser;
    private int clientId = -1;
    private List<PanierItem> panierItems;
    private CodePromo codePromoApplique = null;
    private double montantReduction = 0.0;

    public void setUser(user user) {
        this.currentUser = user;
        this.clientId = user.getId();
        System.out.println("✅ Utilisateur connecté au panier - ID: " + clientId);
        loadPanier();
    }

    @FXML
    public void initialize() {
        // Initialisation si nécessaire
    }

    private void loadPanier() {
        try {
            if (clientId == -1) {
                showAlert("Erreur", "Client non identifié");
                return;
            }
            
            panierContainer.getChildren().clear();
            
            // Charger les items du panier (ancien + nouveau système)
            panierItems = panierService.getPanierAvecVariants(clientId);
            
            if (panierItems.isEmpty()) {
                emptyMessage.setVisible(true);
                panierContainer.setVisible(false);
            } else {
                emptyMessage.setVisible(false);
                panierContainer.setVisible(true);
                
                for (PanierItem item : panierItems) {
                    panierContainer.getChildren().add(createPanierCard(item));
                }
            }
            
            updateSummary();
            System.out.println("✅ " + panierItems.size() + " articles dans le panier");
            
        } catch (SQLException e) {
            showAlert("Erreur", "Impossible de charger le panier: " + e.getMessage());
            e.printStackTrace();
        }
    }

    private HBox createPanierCard(PanierItem item) {
        HBox card = new HBox(15);
        card.setStyle("-fx-background-color: white; -fx-background-radius: 8; " +
                     "-fx-effect: dropshadow(gaussian, rgba(0,0,0,0.1), 10, 0, 0, 2); " +
                     "-fx-padding: 15;");
        card.setAlignment(Pos.CENTER_LEFT);

        // Icône produit
        VBox iconBox = new VBox();
        iconBox.setAlignment(Pos.CENTER);
        iconBox.setStyle("-fx-background-color: #F3F4F6; -fx-background-radius: 8; " +
                        "-fx-min-width: 80; -fx-min-height: 80; -fx-max-width: 80; -fx-max-height: 80;");
        Label icon = new Label("📦");
        icon.setStyle("-fx-font-size: 32px;");
        iconBox.getChildren().add(icon);

        // Informations produit
        VBox infoBox = new VBox(5);
        infoBox.setAlignment(Pos.CENTER_LEFT);
        HBox.setHgrow(infoBox, Priority.ALWAYS);
        
        Label nomLabel = new Label(item.getProduitNom());
        nomLabel.setStyle("-fx-font-size: 16px; -fx-font-weight: bold; -fx-text-fill: #1F2937;");
        
        Label prixLabel = new Label(String.format("Prix unitaire: %.3f TND", item.getPrixUnitaire()));
        prixLabel.setStyle("-fx-font-size: 13px; -fx-text-fill: #6B7280;");
        
        infoBox.getChildren().addAll(nomLabel, prixLabel);

        // Contrôles de quantité
        HBox quantityBox = new HBox(8);
        quantityBox.setAlignment(Pos.CENTER);
        
        Button minusBtn = new Button("-");
        minusBtn.setStyle("-fx-background-color: #f39c12; -fx-text-fill: white; " +
                         "-fx-font-size: 16px; -fx-min-width: 35; -fx-min-height: 35; " +
                         "-fx-cursor: hand; -fx-background-radius: 6;");
        minusBtn.setOnAction(e -> handleUpdateQuantity(item, item.getQuantite() - 1));
        
        Label quantityLabel = new Label(String.valueOf(item.getQuantite()));
        quantityLabel.setStyle("-fx-font-size: 18px; -fx-font-weight: bold; " +
                              "-fx-min-width: 40; -fx-alignment: center;");
        
        Button plusBtn = new Button("+");
        plusBtn.setStyle("-fx-background-color: #27ae60; -fx-text-fill: white; " +
                        "-fx-font-size: 16px; -fx-min-width: 35; -fx-min-height: 35; " +
                        "-fx-cursor: hand; -fx-background-radius: 6;");
        plusBtn.setOnAction(e -> handleUpdateQuantity(item, item.getQuantite() + 1));
        
        quantityBox.getChildren().addAll(minusBtn, quantityLabel, plusBtn);

        // Prix total
        VBox priceBox = new VBox(5);
        priceBox.setAlignment(Pos.CENTER_RIGHT);
        priceBox.setStyle("-fx-min-width: 120;");
        
        Label totalItemLabel = new Label(String.format("%.3f TND", item.getTotal()));
        totalItemLabel.setStyle("-fx-font-size: 20px; -fx-font-weight: bold; -fx-text-fill: #2980b9;");
        
        Button deleteBtn = new Button("🗑️ Supprimer");
        deleteBtn.setStyle("-fx-background-color: #e74c3c; -fx-text-fill: white; " +
                          "-fx-font-size: 12px; -fx-padding: 6 12; -fx-cursor: hand; " +
                          "-fx-background-radius: 6;");
        deleteBtn.setOnAction(e -> handleDelete(item));
        
        priceBox.getChildren().addAll(totalItemLabel, deleteBtn);

        card.getChildren().addAll(iconBox, infoBox, quantityBox, priceBox);
        return card;
    }

    private void handleUpdateQuantity(PanierItem item, int newQuantity) {
        if (newQuantity < 1) {
            handleDelete(item);
            return;
        }
        
        try {
            panierService.modifierQuantite(item.getId(), newQuantity);
            loadPanier();
        } catch (SQLException e) {
            showAlert("Erreur", "Impossible de modifier la quantité: " + e.getMessage());
        }
    }

    private void handleDelete(PanierItem item) {
        Alert alert = new Alert(Alert.AlertType.CONFIRMATION);
        alert.setTitle("Confirmation");
        alert.setHeaderText("Supprimer du panier");
        alert.setContentText("Voulez-vous supprimer " + item.getProduitNom() + " du panier ?");

        alert.showAndWait().ifPresent(response -> {
            if (response == ButtonType.OK) {
                try {
                    panierService.supprimerDuPanier(item.getId());
                    loadPanier();
                    showAlert("Succès", "Article supprimé du panier");
                } catch (SQLException e) {
                    showAlert("Erreur", "Impossible de supprimer l'article: " + e.getMessage());
                }
            }
        });
    }

    @FXML
    public void handleViderPanier() {
        if (panierItems == null || panierItems.isEmpty()) {
            showAlert("Information", "Votre panier est déjà vide");
            return;
        }

        Alert alert = new Alert(Alert.AlertType.CONFIRMATION);
        alert.setTitle("Confirmation");
        alert.setHeaderText("Vider le panier");
        alert.setContentText("Êtes-vous sûr de vouloir vider votre panier ?");

        alert.showAndWait().ifPresent(response -> {
            if (response == ButtonType.OK) {
                try {
                    panierService.viderPanier(clientId);
                    loadPanier();
                    showAlert("Succès", "Panier vidé avec succès");
                } catch (SQLException e) {
                    showAlert("Erreur", "Impossible de vider le panier: " + e.getMessage());
                }
            }
        });
    }

    @FXML
    public void handleValiderCommande() {
        if (panierItems == null || panierItems.isEmpty()) {
            showAlert("Panier vide", "Votre panier est vide. Ajoutez des produits avant de valider.");
            return;
        }

        Alert alert = new Alert(Alert.AlertType.CONFIRMATION);
        alert.setTitle("Confirmation de commande");
        alert.setHeaderText("Valider la commande");
        alert.setContentText("Confirmez-vous votre commande pour un total de " + totalLabel.getText() + " ?");

        alert.showAndWait().ifPresent(response -> {
            if (response == ButtonType.OK) {
                try {
                    // Passer la commande
                    var commande = panierService.passerCommande(clientId, currentUser.getPrenom() + " " + currentUser.getName());

                    // Appliquer le code promo si présent
                    if (codePromoApplique != null && montantReduction > 0) {
                        codePromoService.appliquerCode(
                            codePromoApplique.getId(),
                            clientId,
                            commande.getId(),
                            montantReduction
                        );
                        
                        // Mettre à jour le total de la commande
                        commande.setTotal(commande.getTotal() - montantReduction);
                        commandeService.modifierCommande(commande);
                        
                        System.out.println("🎁 Code promo enregistré pour commande #" + commande.getId());
                    }

                    // Réinitialiser le code promo
                    codePromoApplique = null;
                    montantReduction = 0.0;
                    codePromoField.clear();
                    codePromoField.setDisable(false);
                    appliquerCodeBtn.setDisable(false);
                    reductionLabel.setVisible(false);

                    // Ouvrir la fenêtre de paiement
                    ouvrirFenetrePaiement(commande);

                } catch (SQLException e) {
                    showAlert("Erreur", "Erreur lors de la validation de la commande: " + e.getMessage());
                    e.printStackTrace();
                }
            }
        });
    }

    private void ouvrirFenetrePaiement(com.pi.entities.commande commande) {
        try {
            javafx.fxml.FXMLLoader loader = new javafx.fxml.FXMLLoader(
                getClass().getResource("/Paiement.fxml")
            );
            javafx.scene.Parent root = loader.load();
            
            PaiementController paiementController = loader.getController();
            paiementController.setCommande(commande);
            paiementController.setOnSuccessCallback(() -> {
                // Callback après paiement réussi
                loadPanier(); // Recharger le panier (qui sera vide)
                showAlert("Succès", "Commande #" + commande.getId() + " payée avec succès!");
            });
            
            javafx.stage.Stage stage = new javafx.stage.Stage();
            stage.setTitle("Paiement - Commande #" + commande.getId());
            stage.setScene(new javafx.scene.Scene(root, 600, 700));
            stage.initModality(javafx.stage.Modality.APPLICATION_MODAL);
            stage.show();
            
        } catch (Exception e) {
            showAlert("Erreur", "Impossible d'ouvrir la fenêtre de paiement: " + e.getMessage());
            e.printStackTrace();
        }
    }

    @FXML
    public void handleRefresh() {
        loadPanier();
    }

    @FXML
    public void handleAppliquerCode() {
        try {
            String code = codePromoField.getText().trim().toUpperCase();
            if (code.isEmpty()) {
                showAlert("Attention", "Veuillez saisir un code promo");
                return;
            }

            if (panierItems == null || panierItems.isEmpty()) {
                showAlert("Attention", "Votre panier est vide");
                return;
            }

            double total = calculerTotal();
            codePromoApplique = codePromoService.validerCode(code, clientId, total);
            montantReduction = codePromoApplique.calculerReduction(total);
            
            reductionLabel.setText(String.format("-%.3f TND", montantReduction));
            reductionLabel.setVisible(true);
            appliquerCodeBtn.setDisable(true);
            codePromoField.setDisable(true);
            
            updateSummary();
            
            showAlert("Succès", "✅ Code promo appliqué: " + codePromoApplique.getDescription());
            System.out.println("🎁 Code promo appliqué: " + code + " (-" + String.format("%.3f TND", montantReduction) + ")");
            
        } catch (SQLException e) {
            showAlert("Erreur", e.getMessage());
            codePromoApplique = null;
            montantReduction = 0.0;
            reductionLabel.setVisible(false);
        }
    }

    private double calculerTotal() {
        double total = 0.0;
        if (panierItems != null) {
            for (PanierItem item : panierItems) {
                total += item.getTotal();
            }
        }
        return total;
    }

    private void updateSummary() {
        if (panierItems == null || panierItems.isEmpty()) {
            itemCountLabel.setText("0");
            totalLabel.setText("0,000 TND");
            return;
        }

        // Calculer le total
        double total = calculerTotal();
        double totalFinal = total - montantReduction;

        // Mettre à jour les labels
        itemCountLabel.setText(String.valueOf(panierItems.size()));
        totalLabel.setText(String.format("%.3f TND", totalFinal));
        
        System.out.println("📊 Résumé panier: " + panierItems.size() + " articles, Total: " + String.format("%.3f TND", totalFinal));
    }

    private void showAlert(String title, String message) {
        Alert alert = new Alert(Alert.AlertType.INFORMATION);
        alert.setTitle(title);
        alert.setHeaderText(null);
        alert.setContentText(message);
        alert.show();
    }
}
