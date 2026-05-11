package controller;

import com.pi.entities.user;
import com.pi.entities.Partenaire;
import com.pi.entity.PartenaireService;
import com.pi.utils.SessionManager;
import javafx.fxml.FXML;
import javafx.fxml.FXMLLoader;
import javafx.scene.Parent;
import javafx.scene.Scene;
import javafx.scene.control.Label;
import javafx.scene.layout.StackPane;
import javafx.stage.Stage;
import java.io.IOException;
import java.sql.SQLException;

public class DashboardPartenaireController {

    @FXML private Label welcomeLabel;
    @FXML private StackPane contentPane;

    private user currentUser;
    private int partenaireId;  // ← ID du partenaire (pas user)
    private PartenaireService partenaireService = new PartenaireService();

    public void setUser(user user) {
        System.out.println("✅ DashboardPartenaire.setUser() - User reçu: " + user.getEmail());
        this.currentUser = user;
        SessionManager.setCurrentUser(user);  // Stocker dans la session

        try {
            Partenaire partenaire = partenaireService.getByUserId(user.getId());
            if (partenaire != null) {
                this.partenaireId = partenaire.getId();
                SessionManager.setCurrentPartenaireId(this.partenaireId);  // Stocker l'ID partenaire
                System.out.println("✅ Partenaire ID trouvé: " + partenaireId);
                welcomeLabel.setText("Bienvenue, " + partenaire.getResponsableNom() + " (" + partenaire.getNomEntreprise() + ")");
                showGestionProduits();
            } else {
                System.out.println("❌ Aucun partenaire pour user_id: " + user.getId());
                welcomeLabel.setText("Erreur : vous n'êtes pas partenaire");
            }
        } catch (SQLException e) {
            e.printStackTrace();
        }
    }

    @FXML
    public void showGestionProduits() {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/GestionProduitsVariants.fxml"));
            Parent view = loader.load();

            GestionProduitsVariantsController controller = loader.getController();
            controller.setPartenaireId(this.partenaireId);  // ← Transmission du bon ID

            contentPane.getChildren().clear();
            contentPane.getChildren().add(view);

        } catch (IOException e) {
            e.printStackTrace();
        }
    }

    @FXML
    public void handleLogout() {
        try {
            Parent root = FXMLLoader.load(getClass().getResource("/loginne.fxml"));
            Stage stage = (Stage) welcomeLabel.getScene().getWindow();
            stage.setScene(new Scene(root));
            stage.setTitle("Connexion");
        } catch (IOException e) {
            e.printStackTrace();
        }
    }
}