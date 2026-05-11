package controller;

import com.pi.entities.user;
import com.pi.entities.Product;
import com.pi.entity.ProductService;
import com.pi.entity.PanierService;
import javafx.animation.*;
import javafx.fxml.FXML;
import javafx.fxml.FXMLLoader;
import javafx.scene.Parent;
import javafx.scene.Scene;
import javafx.scene.control.*;
import javafx.scene.layout.StackPane;
import javafx.stage.Stage;
import javafx.util.Duration;
import java.io.IOException;
import java.sql.SQLException;

public class DashboardClientController {

    @FXML private Label welcomeLabel;
    @FXML private StackPane contentPane;
    @FXML private Button chatbotFloatingButton;

    private user currentUser;
    private ProductService productService = new ProductService();
    private PanierService panierService = new PanierService();

    @FXML
    public void initialize() {
        // Animation du bouton chatbot (pulse effect)
        if (chatbotFloatingButton != null) {
            System.out.println("✅ Bouton chatbot initialisé");
            
            // S'assurer que le bouton est cliquable
            chatbotFloatingButton.setPickOnBounds(true);
            chatbotFloatingButton.setMouseTransparent(false);
            
            // Test de clic
            chatbotFloatingButton.setOnMouseClicked(event -> {
                System.out.println("🖱️ CLIC DÉTECTÉ sur le bouton chatbot!");
            });
            
            animateChatbotButton();
        } else {
            System.out.println("⚠️ Bouton chatbot non trouvé");
        }
    }

    private void animateChatbotButton() {
        try {
            // Animation de pulsation simple
            ScaleTransition pulse = new ScaleTransition(Duration.seconds(2), chatbotFloatingButton);
            pulse.setFromX(1.0);
            pulse.setFromY(1.0);
            pulse.setToX(1.08);
            pulse.setToY(1.08);
            pulse.setCycleCount(Animation.INDEFINITE);
            pulse.setAutoReverse(true);
            pulse.play();
            
            System.out.println("✅ Animation chatbot démarrée");
        } catch (Exception e) {
            System.err.println("⚠️ Erreur animation: " + e.getMessage());
        }
    }

    public void setUser(user user) {
        this.currentUser = user;
        welcomeLabel.setText("Bienvenue, " + user.getPrenom() + " " + user.getName());
        showWelcomeMessage();
    }

    private void showWelcomeMessage() {
        try {
            // Créer une vue de bienvenue simple
            javafx.scene.layout.VBox welcomeBox = new javafx.scene.layout.VBox(20);
            welcomeBox.setAlignment(javafx.geometry.Pos.CENTER);
            welcomeBox.setStyle("-fx-background-color: white; -fx-padding: 40;");

            javafx.scene.text.Text title = new javafx.scene.text.Text("Bienvenue dans votre espace client NEXORA");
            title.setStyle("-fx-font-size: 24px; -fx-font-weight: bold; -fx-fill: #1F2937;");

            javafx.scene.text.Text subtitle = new javafx.scene.text.Text("Utilisez le menu à gauche pour naviguer");
            subtitle.setStyle("-fx-font-size: 16px; -fx-fill: #6B7280;");

            javafx.scene.layout.VBox menuInfo = new javafx.scene.layout.VBox(10);
            menuInfo.setAlignment(javafx.geometry.Pos.CENTER_LEFT);
            menuInfo.setMaxWidth(400);
            menuInfo.setStyle("-fx-background-color: #F3F4F6; -fx-padding: 20; -fx-background-radius: 8;");

            javafx.scene.control.Label info1 = new javafx.scene.control.Label("🛍️ Gestion E-commerce - Parcourez et achetez des produits");
            javafx.scene.control.Label info2 = new javafx.scene.control.Label("🎯 Gestion Activités - Découvrez les activités disponibles");
            javafx.scene.control.Label info3 = new javafx.scene.control.Label("🌍 Gestion Destinations - Explorez les destinations");
            javafx.scene.control.Label info4 = new javafx.scene.control.Label("⭐ Gestion Avis - Consultez les avis");
            javafx.scene.control.Label info5 = new javafx.scene.control.Label("📋 Mes Commandes - Suivez vos commandes");
            javafx.scene.control.Label info6 = new javafx.scene.control.Label("🛒 Mon Panier - Gérez votre panier");

            info1.setStyle("-fx-font-size: 14px; -fx-text-fill: #374151;");
            info2.setStyle("-fx-font-size: 14px; -fx-text-fill: #374151;");
            info3.setStyle("-fx-font-size: 14px; -fx-text-fill: #374151;");
            info4.setStyle("-fx-font-size: 14px; -fx-text-fill: #374151;");
            info5.setStyle("-fx-font-size: 14px; -fx-text-fill: #374151;");
            info6.setStyle("-fx-font-size: 14px; -fx-text-fill: #374151;");

            menuInfo.getChildren().addAll(info1, info2, info3, info4, info5, info6);
            welcomeBox.getChildren().addAll(title, subtitle, menuInfo);

            contentPane.getChildren().clear();
            contentPane.getChildren().add(welcomeBox);

        } catch (Exception e) {
            e.printStackTrace();
        }
    }

    @FXML
    public void showCatalogue() {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/CatalogueClientVariants.fxml"));
            Parent view = loader.load();

            contentPane.getChildren().clear();
            contentPane.getChildren().add(view);

        } catch (IOException e) {
            e.printStackTrace();
            showAlert("Erreur", "Impossible de charger le catalogue: " + e.getMessage());
        }
    }

    @FXML
    public void showActivites() {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/ActivitesClient.fxml"));
            Parent view = loader.load();

            ActivitesClientController controller = loader.getController();
            if (currentUser != null) {
                controller.setClientId(currentUser.getId());
            }

            contentPane.getChildren().clear();
            contentPane.getChildren().add(view);

            System.out.println("✅ Vue Activités chargée avec clientId: " + (currentUser != null ? currentUser.getId() : "null"));
        } catch (IOException e) {
            e.printStackTrace();
            showAlert("Erreur", "Impossible de charger les activités: " + e.getMessage());
        }
    }

    @FXML
    public void showDestinations() {
        loadView("/DestinationsClient.fxml", "Destinations");
    }

    @FXML
    public void showAvis() {
        loadView("/reclamatione.fxml", "Avis et Commentaires");
    }


    @FXML
    public void showPanier() {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/Panier.fxml"));
            Parent view = loader.load();

            PanierController controller = loader.getController();
            controller.setUser(currentUser);

            contentPane.getChildren().clear();
            contentPane.getChildren().add(view);

        } catch (IOException e) {
            e.printStackTrace();
            showAlert("Erreur", "Impossible de charger le panier: " + e.getMessage());
        }
    }

    @FXML
    public void showMesCommandes() {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/MesCommandes.fxml"));
            Parent view = loader.load();

            MesCommandesController controller = loader.getController();
            controller.setUser(currentUser);

            contentPane.getChildren().clear();
            contentPane.getChildren().add(view);

        } catch (IOException e) {
            e.printStackTrace();
            showAlert("Erreur", "Impossible de charger les commandes: " + e.getMessage());
        }
    }
    
    @FXML
    public void showChatbot() {
        System.out.println("🤖 Ouverture du chatbot...");
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/Chatbot.fxml"));
            Parent view = loader.load();

            contentPane.getChildren().clear();
            contentPane.getChildren().add(view);
            
            System.out.println("✅ Chatbot chargé avec succès");

        } catch (IOException e) {
            System.err.println("❌ Erreur chargement chatbot: " + e.getMessage());
            e.printStackTrace();
            showAlert("Erreur", "Impossible de charger le chatbot: " + e.getMessage());
        }
    }
    
    @FXML
    public void handleLogout() {
        try {
            // Effacer la session
            com.pi.utils.SessionManager.clearSession();
            
            // Charger la page de login
            Parent root = FXMLLoader.load(getClass().getResource("/loginne.fxml"));
            Stage stage = (Stage) welcomeLabel.getScene().getWindow();
            stage.setScene(new Scene(root));
            stage.setTitle("Connexion - NEXORA");
            System.out.println("✅ Déconnexion réussie");
        } catch (IOException e) {
            e.printStackTrace();
            showAlert("Erreur", "Impossible de charger la page de connexion: " + e.getMessage());
        }
    }

    private void loadView(String fxmlPath, String viewName) {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource(fxmlPath));
            Parent view = loader.load();

            contentPane.getChildren().clear();
            contentPane.getChildren().add(view);

            System.out.println("✅ Vue chargée: " + viewName);
        } catch (IOException e) {
            e.printStackTrace();
            showAlert("Erreur", "Impossible de charger " + viewName + ": " + e.getMessage());
        }
    }

    private void showAlert(String title, String message) {
        Alert alert = new Alert(Alert.AlertType.INFORMATION);
        alert.setTitle(title);
        alert.setHeaderText(null);
        alert.setContentText(message);
        alert.show();
    }
}