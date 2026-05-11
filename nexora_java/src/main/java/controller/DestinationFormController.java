package controller;

import com.pi.entities.Destination;
import com.pi.entity.DestinationService;
import com.pi.entity.GoogleDriveService;
import javafx.fxml.FXML;
import javafx.fxml.FXMLLoader;
import javafx.scene.Parent;
import javafx.scene.Scene;
import javafx.scene.control.*;
import javafx.stage.FileChooser;
import javafx.stage.Stage;
import javafx.geometry.Side;
import org.json.JSONArray;
import org.json.JSONObject;

import java.io.*;
import java.net.HttpURLConnection;
import java.net.URL;
import java.sql.SQLException;
import java.util.ArrayList;
import java.util.Arrays;
import java.util.List;

public class DestinationFormController {

    @FXML private TextField nomField;
    @FXML private TextField descriptionField;
    @FXML private TextField localisationField;
    @FXML private ComboBox<String> statutField;
    @FXML private TextField imagesField;
    @FXML private Button btnSave;

    private DestinationService service = new DestinationService();
    private Destination destinationToEdit = null;

    @FXML
    public void initialize() {

        statutField.getItems().addAll("Disponible", "Complet");

        // ===== VALIDATION STYLE ROUGE =====
        TextField[] champs = {nomField, descriptionField, localisationField, imagesField};

        for (TextField champ : champs) {
            champ.textProperty().addListener((obs, oldText, newText) -> {
                if (!newText.isEmpty()) champ.setStyle(null);
            });
        }

        statutField.valueProperty().addListener((obs, oldVal, newVal) -> {
            if (newVal != null) statutField.setStyle(null);
        });

        // ===== AUTOCOMPLETE LOCALISATION =====
        ContextMenu suggestionsMenu = new ContextMenu();

        localisationField.textProperty().addListener((obs, oldText, newText) -> {

            if (newText.length() < 3) {
                suggestionsMenu.hide();
                return;
            }

            List<String> suggestions = searchLocation(newText);

            suggestionsMenu.getItems().clear();

            for (String result : suggestions) {
                MenuItem item = new MenuItem(result);
                item.setOnAction(e -> {
                    localisationField.setText(result);
                    suggestionsMenu.hide();
                });
                suggestionsMenu.getItems().add(item);
            }

            if (!suggestions.isEmpty()) {
                suggestionsMenu.show(localisationField, Side.BOTTOM, 0, 0);
            }
        });




    }

    // ================= VALIDATION =================

    private boolean validerChamps() {

        boolean valide = true;

        TextField[] champs = {nomField, descriptionField, localisationField, imagesField};

        for (TextField champ : champs) {
            if (champ.getText().isEmpty()) {
                champ.setStyle("-fx-border-color: red; -fx-border-width: 2px;");
                valide = false;
            }
        }

        if (statutField.getValue() == null) {
            statutField.setStyle("-fx-border-color: red; -fx-border-width: 2px;");
            valide = false;
        }

        if (!valide) {
            showAlert("Champs manquants", "Veuillez remplir tous les champs en rouge !");
        }

        return valide;
    }

    // ================= SET EDIT MODE =================

    public void setDestinationToEdit(Destination d) {

        this.destinationToEdit = d;

        nomField.setText(d.getNom());
        descriptionField.setText(d.getDescription());
        localisationField.setText(d.getLocalisation());
        statutField.setValue(d.getStatut());

        if (d.getImages() != null) {
            imagesField.setText(String.join(",", d.getImages()));
        }

        btnSave.setText("Modifier Destination");
    }

    // ================= SAVE =================

    @FXML
    private void saveDestination() {

        if (!validerChamps()) return;

        try {

            List<String> imagesList = new ArrayList<>();
            if (!imagesField.getText().isEmpty()) {
                imagesList = Arrays.asList(imagesField.getText().split(","));
            }

            if (destinationToEdit == null) {
                Destination d = new Destination(
                        nomField.getText(),
                        descriptionField.getText(),
                        localisationField.getText(),
                        statutField.getValue(),
                        imagesList
                );
                service.ajouter(d);
            } else {
                destinationToEdit.setNom(nomField.getText());
                destinationToEdit.setDescription(descriptionField.getText());
                destinationToEdit.setLocalisation(localisationField.getText());
                destinationToEdit.setStatut(statutField.getValue());
                destinationToEdit.setImages(imagesList);

                service.modifier(destinationToEdit);
            }

            showAlert("Succès", "Opération réussie !");
            goBack();

        } catch (SQLException e) {
            showAlert("Erreur DB", e.getMessage());
        }
    }

    // ================= IMAGE =================

    @FXML
    private void choisirImage() {

        FileChooser fileChooser = new FileChooser();
        fileChooser.setTitle("Choisir une image");

        fileChooser.getExtensionFilters().addAll(
                new FileChooser.ExtensionFilter("Images", "*.png", "*.jpg", "*.jpeg")
        );

        File file = fileChooser.showOpenDialog(null);

        if (file != null) {
            try {
                String imageUrl = GoogleDriveService.uploadFile(file);
                imagesField.setText(imageUrl);
            } catch (Exception e) {
                showAlert("Erreur Upload", e.getMessage());
            }
        }
    }

    // ================= AUTOCOMPLETE API =================

    private List<String> searchLocation(String query) {

        List<String> results = new ArrayList<>();

        try {
            String urlString = "https://nominatim.openstreetmap.org/search?q="
                    + query.replace(" ", "%20")
                    + "&format=json&limit=5";

            HttpURLConnection conn = (HttpURLConnection) new URL(urlString).openConnection();
            conn.setRequestProperty("User-Agent", "JavaFX App");

            BufferedReader reader = new BufferedReader(
                    new InputStreamReader(conn.getInputStream())
            );

            StringBuilder response = new StringBuilder();
            String line;

            while ((line = reader.readLine()) != null) {
                response.append(line);
            }

            JSONArray jsonArray = new JSONArray(response.toString());

            for (int i = 0; i < jsonArray.length(); i++) {
                JSONObject obj = jsonArray.getJSONObject(i);
                results.add(obj.getString("display_name"));
            }

        } catch (Exception e) {
            e.printStackTrace();
        }

        return results;
    }

    // ================= RETOUR =================

    @FXML
    private void goBack() {
        try {
            Parent root = FXMLLoader.load(getClass().getResource("/DestinationView.fxml"));
            Stage stage = (Stage) nomField.getScene().getWindow();
            stage.setScene(new Scene(root));
        } catch (Exception e) {
            e.printStackTrace();
        }
    }

    private void showAlert(String title, String message) {
        Alert alert = new Alert(Alert.AlertType.INFORMATION);
        alert.setTitle(title);
        alert.setContentText(message);
        alert.showAndWait();
    }
}