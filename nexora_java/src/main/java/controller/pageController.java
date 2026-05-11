package controller;

import com.pi.entities.user;
import com.pi.utils.SessionManager;
import javafx.fxml.FXML;
import javafx.fxml.FXMLLoader;
import javafx.scene.Parent;
import javafx.scene.Scene;
import javafx.scene.layout.AnchorPane;
import javafx.stage.Stage;

import java.io.IOException;

public class pageController {

    @FXML
    private AnchorPane contentPane;
    
    private user currentUser;
    
    @FXML
    public void initialize() {
        // Récupérer l'utilisateur de la session
        currentUser = SessionManager.getCurrentUser();
    }

    @FXML
    void showUsers() {
        loadPage("login.fxml");
    }

    @FXML
    void showecomerce() {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/GestionProduitsVariants.fxml"));
            Parent page = loader.load();
            
            // Passer l'ID de l'admin au contrôleur (l'admin peut gérer tous les produits)
            GestionProduitsVariantsController controller = loader.getController();
            if (currentUser != null) {
                // Créer automatiquement un partenaire pour l'admin si nécessaire
                try {
                    com.pi.entity.PartenaireService partenaireService = new com.pi.entity.PartenaireService();
                    com.pi.entities.Partenaire partenaire = partenaireService.creerPartenaireDepuisUser(currentUser);
                    controller.setPartenaireId(partenaire.getId());
                    System.out.println("✅ Admin accède à l'e-commerce avec partenaireId: " + partenaire.getId());
                } catch (Exception e) {
                    System.err.println("❌ Erreur création partenaire admin: " + e.getMessage());
                    controller.setPartenaireId(currentUser.getId());
                }
            }

            contentPane.getChildren().setAll(page);
            AnchorPane.setTopAnchor(page, 0.0);
            AnchorPane.setBottomAnchor(page, 0.0);
            AnchorPane.setLeftAnchor(page, 0.0);
            AnchorPane.setRightAnchor(page, 0.0);
            
            System.out.println("✅ Interface e-commerce chargée pour admin");
        } catch (IOException e) {
            System.out.println("❌ Erreur chargement e-commerce: " + e.getMessage());
            e.printStackTrace();
        }
    }
    
    @FXML
    void showeactivite() {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/GestionActivitesPartenaire.fxml"));
            Parent page = loader.load();

            // Pour l'admin : mode global (voir toutes les activités)
            GestionActivitesPartenaireController controller = loader.getController();
            if (currentUser != null) {
                // Mode admin : passer -1 pour indiquer "toutes les activités"
                controller.setPartenaireId(-1); // -1 = mode admin global
                System.out.println("✅ Admin accède aux activités en mode global (toutes les activités)");
            }

            contentPane.getChildren().setAll(page);
            AnchorPane.setTopAnchor(page, 0.0);
            AnchorPane.setBottomAnchor(page, 0.0);
            AnchorPane.setLeftAnchor(page, 0.0);
            AnchorPane.setRightAnchor(page, 0.0);
            
            System.out.println("✅ Interface activités chargée pour admin (mode global)");
        } catch (IOException e) {
            System.out.println("❌ Erreur chargement activités: " + e.getMessage());
            e.printStackTrace();
        }
    }
    
    @FXML
    void showedestination() {
        loadPage("DestinationView.fxml");
    }
    
    @FXML
    void showCategories() {
        loadPage("GestionCategories.fxml");
    }
    
    @FXML
    void showCodesPromo() {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/GestionCodesPromo.fxml"));
            Parent page = loader.load();
            
            // Passer l'utilisateur au contrôleur
            GestionCodesPromoController controller = loader.getController();
            if (currentUser != null) {
                controller.setUser(currentUser);
            }

            contentPane.getChildren().setAll(page);
            AnchorPane.setTopAnchor(page, 0.0);
            AnchorPane.setBottomAnchor(page, 0.0);
            AnchorPane.setLeftAnchor(page, 0.0);
            AnchorPane.setRightAnchor(page, 0.0);
            
            System.out.println("✅ Gestion codes promo chargée pour admin");
        } catch (IOException e) {
            System.out.println("❌ Erreur chargement codes promo: " + e.getMessage());
            e.printStackTrace();
        }
    }
    
    @FXML
    void showPaiements() {
        loadPage("GestionPaiements.fxml");
    }
    
    @FXML
    void showReclamations() {
        loadPage("GestionReclamations.fxml");
    }

    @FXML
    void showeavis() {
        loadPage("AvisCommentaire.fxml");
    }
    
    @FXML
    void handleLogout() {
        try {
            // Effacer la session
            com.pi.utils.SessionManager.clearSession();
            
            // Charger la page de login
            Parent root = FXMLLoader.load(getClass().getResource("/loginne.fxml"));
            Stage stage = (Stage) contentPane.getScene().getWindow();
            stage.setScene(new Scene(root));
            stage.setTitle("Connexion - NEXORA");
            System.out.println("✅ Déconnexion réussie");
        } catch (IOException e) {
            System.out.println("❌ Erreur lors de la déconnexion: " + e.getMessage());
            e.printStackTrace();
        }
    }
    
    private void loadPage(String fxmlName) {
        try {
            FXMLLoader loader = new FXMLLoader(
                    getClass().getResource("/" + fxmlName)
            );

            Parent page = loader.load();

            contentPane.getChildren().setAll(page);

            AnchorPane.setTopAnchor(page, 0.0);
            AnchorPane.setBottomAnchor(page, 0.0);
            AnchorPane.setLeftAnchor(page, 0.0);
            AnchorPane.setRightAnchor(page, 0.0);

        } catch (IOException e) {
            System.out.println("❌ Impossible de charger : " + fxmlName);
            e.printStackTrace();
        }
    }
}
