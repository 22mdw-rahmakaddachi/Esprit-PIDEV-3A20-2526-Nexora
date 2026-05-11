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

public class pagePartenaireController {

    @FXML
    private AnchorPane contentPane;
    
    private user currentUser;
    
    @FXML
    public void initialize() {
        // Récupérer l'utilisateur de la session
        currentUser = SessionManager.getCurrentUser();
    }

    @FXML
    void showecomerce() {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/GestionProduitsVariants.fxml"));
            Parent page = loader.load();
            
            // Passer l'ID du partenaire au contrôleur
            GestionProduitsVariantsController controller = loader.getController();
            if (currentUser != null) {
                controller.setPartenaireId(currentUser.getId());
            }

            contentPane.getChildren().setAll(page);
            AnchorPane.setTopAnchor(page, 0.0);
            AnchorPane.setBottomAnchor(page, 0.0);
            AnchorPane.setLeftAnchor(page, 0.0);
            AnchorPane.setRightAnchor(page, 0.0);
            
        } catch (IOException e) {
            System.out.println("❌ Impossible de charger GestionProduitsVariants");
            e.printStackTrace();
        }
    }
    
    @FXML
    void showeactivite() {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/GestionActivitesPartenaire.fxml"));
            Parent page = loader.load();

            // Passer l'ID du partenaire au contrôleur
            GestionActivitesPartenaireController controller = loader.getController();
            if (currentUser != null) {
                // S'assurer que l'utilisateur a une entrée partenaire
                try {
                    com.pi.entity.PartenaireService partenaireService = new com.pi.entity.PartenaireService();
                    com.pi.entities.Partenaire partenaire = partenaireService.creerPartenaireDepuisUser(currentUser);
                    
                    controller.setPartenaireId(partenaire.getId());
                    System.out.println("✅ PartenaireId défini: " + partenaire.getId() + " pour user: " + currentUser.getName());
                } catch (Exception e) {
                    System.err.println("❌ Erreur création partenaire: " + e.getMessage());
                    // Fallback: utiliser l'ID utilisateur directement
                    controller.setPartenaireId(currentUser.getId());
                }
            } else {
                System.err.println("❌ CurrentUser est null - impossible de définir partenaireId");
            }

            contentPane.getChildren().setAll(page);
            AnchorPane.setTopAnchor(page, 0.0);
            AnchorPane.setBottomAnchor(page, 0.0);
            AnchorPane.setLeftAnchor(page, 0.0);
            AnchorPane.setRightAnchor(page, 0.0);
            
            System.out.println("✅ Gestion activités partenaire chargée");
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
    void showeavis() {
        loadPage("AvisCommentaire.fxml");
    }
    
    @FXML
    void showCategories() {
        loadPage("GestionCategories.fxml");
    }
    
    @FXML
    void showAttributs() {
        loadPage("GestionAttributs.fxml");
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
            
            System.out.println("✅ Gestion codes promo chargée");
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
