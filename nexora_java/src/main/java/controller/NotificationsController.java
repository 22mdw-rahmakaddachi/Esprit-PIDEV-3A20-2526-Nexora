package controller;

import com.pi.entities.Notification;
import com.pi.entity.NotificationService;
import com.pi.entities.user;
import com.pi.utils.SessionManager;
import com.pi.utils.NotificationManager;
import javafx.fxml.FXML;
import javafx.geometry.Insets;
import javafx.geometry.Pos;
import javafx.scene.control.*;
import javafx.scene.layout.*;
import javafx.stage.Stage;

import java.sql.SQLException;
import java.time.format.DateTimeFormatter;
import java.util.List;

public class NotificationsController {

    @FXML private VBox notificationsContainer;
    @FXML private Label emptyLabel;
    @FXML private Button toutBtn;
    @FXML private Button nonLuBtn;

    private NotificationService notificationService;
    private String userType; // "CLIENT" ou "PARTENAIRE"
    private int userId;
    private boolean afficherSeulementNonLues = false;

    @FXML
    public void initialize() {
        notificationService = new NotificationService();

        user currentUser = SessionManager.getCurrentUser();
        if (currentUser != null) {
            userId = currentUser.getId();
            // Déterminer le type d'utilisateur (à adapter selon votre logique)
            userType = determinerTypeUtilisateur(currentUser);
            chargerNotifications();
        }
    }

    private String determinerTypeUtilisateur(user currentUser) {
        // À adapter selon votre logique
        // Par exemple, vérifier si l'utilisateur a un rôle partenaire
        // Pour l'instant, on suppose que c'est passé en paramètre ou déterminé autrement
        return "CLIENT"; // Par défaut
    }

    public void setUserType(String userType) {
        this.userType = userType;
        chargerNotifications();
    }

    @FXML
    private void afficherTout() {
        afficherSeulementNonLues = false;
        toutBtn.getStyleClass().clear();
        toutBtn.getStyleClass().add("tab-button-active");
        nonLuBtn.getStyleClass().clear();
        nonLuBtn.getStyleClass().add("tab-button");
        chargerNotifications();
    }

    @FXML
    private void afficherNonLu() {
        afficherSeulementNonLues = true;
        nonLuBtn.getStyleClass().clear();
        nonLuBtn.getStyleClass().add("tab-button-active");
        toutBtn.getStyleClass().clear();
        toutBtn.getStyleClass().add("tab-button");
        chargerNotifications();
    }

    @FXML
    private void marquerToutesLues() {
        NotificationManager.marquerToutesCommeLues(userId, userType);
        chargerNotifications();

        Alert alert = new Alert(Alert.AlertType.INFORMATION);
        alert.setTitle("Succès");
        alert.setHeaderText(null);
        alert.setContentText("Toutes les notifications ont été marquées comme lues.");
        alert.showAndWait();
    }

    @FXML
    private void fermer() {
        Stage stage = (Stage) notificationsContainer.getScene().getWindow();
        stage.close();
    }

    private void chargerNotifications() {
        notificationsContainer.getChildren().clear();

        try {
            List<Notification> notifications;
            if (afficherSeulementNonLues) {
                notifications = notificationService.getUnreadByUser(userId, userType);
            } else {
                notifications = notificationService.getByUser(userId, userType);
            }

            if (notifications.isEmpty()) {
                emptyLabel.setVisible(true);
                emptyLabel.setText(afficherSeulementNonLues ?
                        "Aucune notification non lue" : "Aucune notification");
            } else {
                emptyLabel.setVisible(false);

                for (Notification notif : notifications) {
                    VBox notifCard = creerCarteNotification(notif);
                    notificationsContainer.getChildren().add(notifCard);
                }
            }

        } catch (SQLException e) {
            System.err.println("Erreur chargement notifications: " + e.getMessage());
            e.printStackTrace();
        }
    }

    private VBox creerCarteNotification(Notification notif) {
        VBox card = new VBox(8);
        card.setPadding(new Insets(15));
        card.setStyle(
                "-fx-background-color: " + (notif.isLue() ? "#F9FAFB" : "#EFF6FF") + ";" +
                        "-fx-background-radius: 8;" +
                        "-fx-border-color: " + (notif.isLue() ? "#E5E7EB" : "#BFDBFE") + ";" +
                        "-fx-border-width: 1;" +
                        "-fx-border-radius: 8;" +
                        "-fx-cursor: hand;"
        );

        // Header avec titre et date
        HBox header = new HBox(10);
        header.setAlignment(Pos.CENTER_LEFT);

        // Indicateur non lu
        if (!notif.isLue()) {
            Label indicator = new Label("●");
            indicator.setStyle("-fx-text-fill: #3B82F6; -fx-font-size: 16px;");
            header.getChildren().add(indicator);
        }

        // Titre
        Label titre = new Label(notif.getTitre());
        titre.setStyle("-fx-font-weight: bold; -fx-font-size: 14px; -fx-text-fill: #111827;");
        HBox.setHgrow(titre, Priority.ALWAYS);
        header.getChildren().add(titre);

        // Date
        DateTimeFormatter formatter = DateTimeFormatter.ofPattern("dd/MM/yyyy HH:mm");
        Label date = new Label(notif.getDateCreation().format(formatter));
        date.setStyle("-fx-text-fill: #6B7280; -fx-font-size: 12px;");
        header.getChildren().add(date);

        // Message
        Label message = new Label(notif.getMessage());
        message.setWrapText(true);
        message.setStyle("-fx-text-fill: #374151; -fx-font-size: 13px;");

        card.getChildren().addAll(header, message);

        // Clic pour marquer comme lue
        if (!notif.isLue()) {
            card.setOnMouseClicked(e -> {
                try {
                    notificationService.marquerCommeLue(notif.getId());
                    chargerNotifications();
                } catch (SQLException ex) {
                    System.err.println("Erreur marquage notification: " + ex.getMessage());
                }
            });
        }

        return card;
    }
}
