package controller;

import javafx.fxml.FXML;
import javafx.fxml.FXMLLoader;
import javafx.scene.Parent;
import javafx.scene.Scene;
import javafx.scene.layout.AnchorPane;
import javafx.stage.Stage;

import java.io.IOException;

public class homeclient {

    @FXML
    private AnchorPane contentPane;

    @FXML
    void showUsers() {
        loadPage("login.fxml");
    }

    @FXML
    void showReclamation() {
        loadPage("detaille.fxml");
    }
    @FXML
    void showecomerce() {
        loadPage("DashboardPartenaire.fxml");
    }
    @FXML
    void showeactivite() {
        loadPage("ActivitesClient.fxml");
    }
    @FXML
    void showedestination() {
        loadPage("DestinationView.fxml");
    }
    @FXML
    void showereclamatione() {
        loadPage("reclamatione.fxml");
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

            Parent page = loader.load();   // ✅ recharge toujours la nouvelle vue

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
