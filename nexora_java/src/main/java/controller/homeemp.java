package controller;

import com.pi.utils.SessionManager;
import javafx.fxml.FXML;
import javafx.fxml.FXMLLoader;
import javafx.scene.Parent;
import javafx.scene.Scene;
import javafx.scene.control.Alert;
import javafx.stage.Stage;

import java.io.IOException;

public class homeemp {

    @FXML
    private void showeactivite() {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/GestionActivitesPartenaire.fxml"));
            Parent root = loader.load();

            // ❌ On ne récupère plus partenaireId ici
            // ❌ On ne fait plus controller.setPartenaireId()

            Stage stage = new Stage();
            stage.setTitle("Gestion des Activités");
            stage.setScene(new Scene(root));
            stage.setMaximized(true);
            stage.show();

        } catch (IOException e) {
            e.printStackTrace();
            showAlert(Alert.AlertType.ERROR, "Erreur",
                    "Impossible d'ouvrir la fenêtre: " + e.getMessage());
        }
    }

    @FXML
    private void showecomerce() {
        // Méthode existante pour E-commerce
    }

    @FXML
    private void showedestination() {
        // Méthode existante pour Destinations
    }

    private void showAlert(Alert.AlertType type, String title, String content) {
        Alert alert = new Alert(type);
        alert.setTitle(title);
        alert.setHeaderText(null);
        alert.setContentText(content);
        alert.showAndWait();
    }
}
