package controller;

import com.pi.entities.Destination;
import com.pi.entities.user;
import com.pi.entity.DestinationService;
import com.pi.utils.SessionManager;
import javafx.collections.FXCollections;
import javafx.collections.ObservableList;
import javafx.fxml.FXML;
import javafx.scene.control.*;
import javafx.scene.image.Image;
import javafx.scene.image.ImageView;
import javafx.scene.layout.*;

import java.io.File;
import java.sql.SQLException;
import java.util.List;

public class DestinationsClientController {

    @FXML private VBox destinationsContainer;
    @FXML private ScrollPane listeView;
    @FXML private ScrollPane detailsView;

    @FXML private Label nomLabel;
    @FXML private Label lieuLabel;
    @FXML private Label prixLabel;
    @FXML private Label descriptionLabel;
    @FXML private Label partenaireNomLabel;
    @FXML private Label partenaireTelLabel;
    @FXML private ImageView imageView;

    @FXML private Label welcomeClientLabel;

    private DestinationService destinationService;
    private ObservableList<Destination> destinationsList;
    private Destination selectedDestination;

    @FXML
    public void initialize() {

        destinationService = new DestinationService();
        destinationsList = FXCollections.observableArrayList();

        detailsView.setVisible(false);
        detailsView.setManaged(false);

        afficherBienvenue();
        loadDestinations();
    }

    // ==============================
    // BIENVENUE
    // ==============================
    private void afficherBienvenue() {
        user currentUser = SessionManager.getCurrentUser();
        if (currentUser != null) {
            welcomeClientLabel.setText(
                    "Bienvenue, " + currentUser.getPrenom() + " " + currentUser.getName()
            );
        }
    }

    // ==============================
    // LOAD DESTINATIONS
    // ==============================
    private void loadDestinations() {
        try {
            destinationsList.clear();
            List<Destination> list = destinationService.afficher();
            destinationsList.addAll(list);
            displayDestinationCards();
        } catch (SQLException e) {
            showAlert(Alert.AlertType.ERROR, "Erreur", e.getMessage());
        }
    }

    // ==============================
    // CARD DESIGN (comme Activités)
    // ==============================
    private VBox createDestinationCard(Destination destination) {

        VBox card = new VBox(15);
        card.setMaxWidth(850);
        card.setStyle(
                "-fx-background-color: white;" +
                        "-fx-padding: 20;" +
                        "-fx-background-radius: 10;" +
                        "-fx-border-color: #E5E7EB;" +
                        "-fx-border-width: 2;" +
                        "-fx-border-radius: 10;" +
                        "-fx-cursor: hand;"
        );

        HBox mainBox = new HBox(20);

        ImageView miniImage = new ImageView();
        miniImage.setFitWidth(150);
        miniImage.setFitHeight(100);
        miniImage.setPreserveRatio(true);

        if (destination.getImages() != null && !destination.getImages().isEmpty()) {
            try {
                File file = new File(destination.getImages().get(0));
                if (file.exists()) {
                    miniImage.setImage(new Image(file.toURI().toString()));
                }
            } catch (Exception ignored) {}
        }

        VBox infoBox = new VBox(8);
        Label nom = new Label(destination.getNom());
        nom.setStyle("-fx-font-size: 20px; -fx-font-weight: bold;");

        Label lieu = new Label("📍 " + destination.getLocalisation());

        mainBox.getChildren().addAll(miniImage, infoBox);
        card.getChildren().add(mainBox);

        card.setOnMouseClicked(e -> showDetails(destination));

        return card;
    }

    private void displayDestinationCards() {
        destinationsContainer.getChildren().clear();
        for (Destination d : destinationsList) {
            destinationsContainer.getChildren().add(createDestinationCard(d));
        }
    }

    // ==============================
    // SHOW DETAILS
    // ==============================
    private void showDetails(Destination destination) {

        selectedDestination = destination;

        listeView.setVisible(false);
        listeView.setManaged(false);

        detailsView.setVisible(true);
        detailsView.setManaged(true);

        nomLabel.setText(destination.getNom());
        lieuLabel.setText(destination.getLocalisation());


        descriptionLabel.setText(
                destination.getDescription() != null ?
                        destination.getDescription() :
                        "Aucune description disponible."
        );



        if (destination.getImages() != null && !destination.getImages().isEmpty()) {
            try {
                File file = new File(destination.getImages().get(0));
                if (file.exists()) {
                    imageView.setImage(new Image(file.toURI().toString()));
                }
            } catch (Exception ignored) {}
        }
    }

    @FXML
    private void retourListe() {
        detailsView.setVisible(false);
        detailsView.setManaged(false);
        listeView.setVisible(true);
        listeView.setManaged(true);
    }

    // ==============================
    // RECHERCHE SIMPLE
    // ==============================
    @FXML
    private void rechercherDestinations(String keyword) {

        ObservableList<Destination> filtered = FXCollections.observableArrayList();

        for (Destination d : destinationsList) {
            if (d.getNom().toLowerCase().contains(keyword.toLowerCase()) ||
                    d.getLocalisation().toLowerCase().contains(keyword.toLowerCase())) {
                filtered.add(d);
            }
        }

        destinationsList.clear();
        destinationsList.addAll(filtered);
        displayDestinationCards();
    }

    private void showAlert(Alert.AlertType type, String title, String content) {
        Alert alert = new Alert(type);
        alert.setTitle(title);
        alert.setHeaderText(null);
        alert.setContentText(content);
        alert.showAndWait();
    }
}