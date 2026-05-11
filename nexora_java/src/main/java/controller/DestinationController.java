package controller;

import com.pi.entities.Destination;
import com.pi.entity.DestinationService;
import com.pi.entity.GoogleDriveService;
import javafx.collections.FXCollections;
import javafx.collections.ObservableList;
import javafx.fxml.FXML;
import javafx.fxml.FXMLLoader;
import javafx.scene.Node;
import javafx.scene.Parent;
import javafx.scene.Scene;
import javafx.scene.control.*;
import javafx.scene.control.cell.PropertyValueFactory;
import javafx.scene.layout.HBox;
import javafx.scene.layout.VBox;
import javafx.stage.FileChooser;
import javafx.stage.Stage;
import javafx.util.Callback;





import java.awt.event.ActionEvent;
import java.net.HttpURLConnection;
import java.net.URL;
import java.io.BufferedReader;
import java.io.InputStreamReader;
import org.json.JSONArray;
import org.json.JSONObject;
import javafx.geometry.Side;


import javafx.scene.image.Image;
import javafx.scene.image.ImageView;
import javafx.scene.control.TableCell;


import java.io.File;
import java.sql.SQLException;
import java.util.ArrayList;
import java.util.Arrays;
import java.util.List;

public class DestinationController {

    @FXML private TextField nomField;
    @FXML private TextField descriptionField;
    @FXML private TextField localisationField;
    // @FXML private TextField statutField;
    @FXML private TextField imagesField; // images séparées par virgule
    @FXML
    private ComboBox<String> statutField;

    @FXML private TableView<Destination> tableView;
    @FXML private TableColumn<Destination, Integer> colId;
    @FXML private TableColumn<Destination, String> colNom;
    @FXML private TableColumn<Destination, String> colDescription;
    @FXML private TableColumn<Destination, String> colLocalisation;
    @FXML private TableColumn<Destination, String> colStatut;
    @FXML
    private TableColumn<Destination, String> colImages;

    @FXML private TableColumn<Destination, Void> colActions;


    @FXML
    private TextField searchField;


    @FXML private Button btnAjouter;
    @FXML private Button btnModifierDest;

    private DestinationService service = new DestinationService();
    private ObservableList<Destination> destinationsList = FXCollections.observableArrayList();

    private Integer selectedId = null;


    @FXML
    private Pagination pagination;

    private static final int ROWS_PER_PAGE = 10;
    private ObservableList<Destination> masterData;




    @FXML
    public void initialize() {

        // ================= TABLE COLUMNS =================
        colId.setCellValueFactory(new PropertyValueFactory<>("id"));
        colNom.setCellValueFactory(new PropertyValueFactory<>("nom"));
        colDescription.setCellValueFactory(new PropertyValueFactory<>("description"));
        colLocalisation.setCellValueFactory(new PropertyValueFactory<>("localisation"));
        colStatut.setCellValueFactory(new PropertyValueFactory<>("statut"));

        // ================= IMAGES COLUMN =================
        colImages.setCellValueFactory(cellData -> {
            List<String> images = cellData.getValue().getImages();
            return new javafx.beans.property.SimpleStringProperty(
                    images != null ? String.join(",", images) : ""
            );
        });

        colImages.setCellFactory(column -> new TableCell<Destination, String>() {

            private final HBox hbox = new HBox(5);
            private final int imageWidth = 100;
            private final int imageHeight = 70;

            @Override
            protected void updateItem(String item, boolean empty) {
                super.updateItem(item, empty);

                if (empty || getTableRow() == null || getTableRow().getItem() == null) {
                    setGraphic(null);
                    return;
                }

                hbox.getChildren().clear();
                Destination destination = getTableRow().getItem();
                List<String> images = destination.getImages();

                if (images != null && !images.isEmpty()) {
                    for (String imageUrl : images) {
                        try {
                            ImageView imageView = new ImageView(
                                    new Image(imageUrl, imageWidth, imageHeight, true, true, true)
                            );
                            imageView.setPreserveRatio(true);
                            hbox.getChildren().add(imageView);
                        } catch (Exception e) {
                            ImageView placeholder = new ImageView(
                                    new Image("file:src/main/resources/icons/image_placeholder.png",
                                            imageWidth, imageHeight, true, true, true)
                            );
                            hbox.getChildren().add(placeholder);
                        }
                    }
                }

                setGraphic(hbox);
            }
        });

        addButtonToTable();

        // ================= LOAD DATA =================
        masterData = FXCollections.observableArrayList(service.readAll());
        loadDestinations();

        // ================= PAGINATION =================
        int pageCount = (int) Math.ceil((double) masterData.size() / ROWS_PER_PAGE);
        pagination.setPageCount(pageCount);
        pagination.setCurrentPageIndex(0);
        pagination.setPageFactory(this::createPage);

        // ================= SEARCH =================
        searchField.textProperty().addListener((obs, oldText, newText) -> {
            filterDestinations(newText);
        });

        // ================= FORM VALIDATION (OPTIONAL) =================
        if (statutField != null) {
            statutField.getItems().addAll("Disponible", "Complet");
        }

        if (nomField != null && descriptionField != null &&
                localisationField != null && imagesField != null) {

            TextField[] textFields = {nomField, descriptionField, localisationField, imagesField};

            for (TextField champ : textFields) {
                champ.textProperty().addListener((obs, oldText, newText) -> {
                    if (!newText.isEmpty()) {
                        champ.setStyle(null);
                    }
                });
            }

            if (statutField != null) {
                statutField.valueProperty().addListener((obs, oldValue, newValue) -> {
                    if (newValue != null && !newValue.isEmpty()) {
                        statutField.setStyle(null);
                    }
                });
            }

            // ================= AUTOCOMPLETE =================
            ContextMenu suggestionsMenu = new ContextMenu();

            localisationField.textProperty().addListener((obs, oldText, newText) -> {

                if (newText.length() < 3) {
                    suggestionsMenu.hide();
                    return;
                }

                List<String> suggestions = searchLocation(newText);

                if (suggestions.isEmpty()) {
                    suggestionsMenu.hide();
                    return;
                }

                suggestionsMenu.getItems().clear();

                for (String result : suggestions) {
                    MenuItem item = new MenuItem(result);
                    item.setOnAction(e -> {
                        localisationField.setText(result);
                        suggestionsMenu.hide();
                    });
                    suggestionsMenu.getItems().add(item);
                }

                suggestionsMenu.show(localisationField, Side.BOTTOM, 0, 0);
            });
        }



        colLocalisation.setCellFactory(column -> new TableCell<Destination, String>() {

            private final Hyperlink link = new Hyperlink();

            {
                link.setOnAction(event -> {
                    Destination d = getTableView().getItems().get(getIndex());
                    if (d != null) {
                        openInMaps(d.getLocalisation());
                    }
                });
            }

            @Override
            protected void updateItem(String item, boolean empty) {
                super.updateItem(item, empty);
                if (empty || item == null) {
                    setGraphic(null);
                } else {
                    link.setText(item); // le texte du lien = localisation
                    setGraphic(link);
                }
            }
        });
    }


    private Node createPage(int pageIndex) {

        int fromIndex = pageIndex * ROWS_PER_PAGE;
        int toIndex = Math.min(fromIndex + ROWS_PER_PAGE, masterData.size());

        tableView.setItems(FXCollections.observableArrayList(
                masterData.subList(fromIndex, toIndex)));

        return new VBox(tableView);
    }

    // Méthode pour filtrer la liste par localisation
    private void filterDestinations(String query) {
        if (query == null || query.isEmpty()) {
            tableView.setItems(destinationsList); // pas de filtre
            return;
        }

        String lowerCaseQuery = query.toLowerCase();

        ObservableList<Destination> filteredList = FXCollections.observableArrayList();

        for (Destination d : destinationsList) {
            if (d.getLocalisation() != null && d.getLocalisation().toLowerCase().startsWith(lowerCaseQuery)) {
                filteredList.add(d);
            }
        }

        tableView.setItems(filteredList);


    }


    private void loadDestinations() {
        try {
            destinationsList.clear();
            destinationsList.addAll(service.afficher());
            tableView.setItems(destinationsList);
        } catch (SQLException e) {
            showAlert("Erreur DB", e.getMessage());
        }
    }



    @FXML
    private void goToAddPage() {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/DestinationAjout.fxml"));
            Parent root = loader.load();

            Stage stage = (Stage) tableView.getScene().getWindow();
            stage.setScene(new Scene(root));
            stage.show();

        } catch (Exception e) {
            e.printStackTrace();
        }
    }


    //ce ci pour controle de saisie
    private boolean validerChamps() {
        if (nomField.getText().isEmpty() ||
                descriptionField.getText().isEmpty() ||
                localisationField.getText().isEmpty() ||
                statutField.getValue() == null || statutField.getValue().isEmpty() ||
                imagesField.getText().isEmpty()) {

            showAlert("Champs manquants", "Veuillez remplir tous les champs avant de continuer !");
            return false;
        }
        return true;
    }
    //ce ci pour le champs vide rouge
    private boolean validerChampsAvecStyle() {
        boolean valide = true;

        // Liste de tous les champs à valider
        TextField[] champs = {nomField, descriptionField, localisationField, imagesField};

        if (statutField.getValue() == null || statutField.getValue().isEmpty()) {
            statutField.setStyle("-fx-border-color: red; -fx-border-width: 2px;");
            valide = false;
        } else {
            statutField.setStyle(null);
        }

        for (TextField champ : champs) {
            if (champ.getText().isEmpty()) {
                // Champ vide → bordure rouge
                champ.setStyle("-fx-border-color: red; -fx-border-width: 2px;");
                valide = false;
            } else {
                // Champ rempli → bordure normale
                champ.setStyle(null);
            }
        }

        if (!valide) {
            showAlert("Champs manquants", "Veuillez remplir tous les champs en rouge avant de continuer !");
        }

        return valide;
    }



    // ======================= AJOUTER =======================
    @FXML
    public void ajouterDestination() {
        if (!validerChampsAvecStyle()) {
            return; // arrête si validation échoue
        }

        try {
            List<String> imagesList = new ArrayList<>();
            if (!imagesField.getText().isEmpty()) {
                imagesList = Arrays.asList(imagesField.getText().split(","));
            }

            Destination d = new Destination(
                    nomField.getText(),
                    descriptionField.getText(),
                    localisationField.getText(),
                    statutField.getValue(),
                    imagesList
            );

            service.ajouter(d);
            showAlert("Succès", "Destination ajoutée avec succès");

            loadDestinations();
            clearFields();

        } catch (SQLException e) {
            showAlert("Erreur DB", e.getMessage());
        }
    }


    // ======================= MODIFIER =======================
    @FXML
    public void modifierDestination() {
        if (!validerChampsAvecStyle()) {
            return; // arrête si validation échoue
        }

        try {
            List<String> imagesList = new ArrayList<>();
            if (!imagesField.getText().isEmpty()) {
                imagesList = Arrays.asList(imagesField.getText().split(","));
            }

            Destination d = new Destination(
                    selectedId,
                    nomField.getText(),
                    descriptionField.getText(),
                    localisationField.getText(),
                    statutField.getValue(),
                    imagesList
            );

            service.modifier(d);

            showAlert("Succès", "Destination modifiée avec succès");

            loadDestinations();
            clearFields();

            btnAjouter.setVisible(true);
            btnModifierDest.setVisible(false);
            selectedId = null;

        } catch (SQLException e) {
            showAlert("Erreur DB", e.getMessage());
        }
    }


    // ======================= BOUTONS TABLE =======================
    private void addButtonToTable() {

        Callback<TableColumn<Destination, Void>, TableCell<Destination, Void>> cellFactory = param -> new TableCell<>() {

            // icônes
            private final ImageView iconEdit = new ImageView(new Image("file:src/main/resources/icons/edit.png"));
            private final ImageView iconDelete = new ImageView(new Image("file:src/main/resources/icons/delete.png"));

            // boutons avec icônes
            private final Button btnModifier = new Button("", iconEdit);
            private final Button btnSupprimer = new Button("", iconDelete);

            {
                // ajustement taille icône
                iconEdit.setFitWidth(20);
                iconEdit.setFitHeight(20);
                iconEdit.setPreserveRatio(true);

                iconDelete.setFitWidth(30);
                iconDelete.setFitHeight(30);
                iconDelete.setPreserveRatio(true);

                // bouton transparent
                btnModifier.setStyle("-fx-background-color: transparent;");
                btnSupprimer.setStyle("-fx-background-color: transparent;");

                // tooltip
                btnModifier.setTooltip(new Tooltip("Modifier cette destination"));
                btnSupprimer.setTooltip(new Tooltip("Supprimer cette destination"));

                // effet hover : change légèrement l'opacité
                btnModifier.setOnMouseEntered(e -> btnModifier.setOpacity(0.7));
                btnModifier.setOnMouseExited(e -> btnModifier.setOpacity(1.0));

                btnSupprimer.setOnMouseEntered(e -> btnSupprimer.setOpacity(0.7));
                btnSupprimer.setOnMouseExited(e -> btnSupprimer.setOpacity(1.0));

                // action modifier
                btnModifier.setOnAction(event -> {
                    Destination d = getTableView().getItems().get(getIndex());

                    try {
                        FXMLLoader loader = new FXMLLoader(getClass().getResource("/DestinationAjout.fxml"));
                        Parent root = loader.load();

                        DestinationFormController controller = loader.getController();
                        controller.setDestinationToEdit(d); // envoyer objet

                        Stage stage = (Stage) tableView.getScene().getWindow();
                        stage.setScene(new Scene(root));
                        stage.show();

                    } catch (Exception e) {
                        e.printStackTrace();
                    }
                });

                // action supprimer avec confirmation
                btnSupprimer.setOnAction(event -> {
                    Destination d = getTableView().getItems().get(getIndex());

                    Alert confirmationAlert = new Alert(Alert.AlertType.CONFIRMATION);
                    confirmationAlert.setTitle("Confirmation de suppression");
                    confirmationAlert.setHeaderText("Voulez-vous vraiment supprimer cette destination ?");
                    confirmationAlert.setContentText(d.getNom());

                    confirmationAlert.showAndWait().ifPresent(response -> {
                        if (response == ButtonType.OK) {
                            try {
                                service.supprimer(d.getId());
                                showAlert("Succès", "Destination supprimée");
                                loadDestinations();
                                clearFields();
                            } catch (SQLException e) {
                                showAlert("Erreur DB", e.getMessage());
                            }
                        }
                    });
                });
            }

            @Override
            protected void updateItem(Void item, boolean empty) {
                super.updateItem(item, empty);
                if (empty) {
                    setGraphic(null);
                } else {
                    HBox pane = new HBox(10, btnModifier, btnSupprimer);
                    setGraphic(pane);
                }
            }
        };

        colActions.setCellFactory(cellFactory);
    }

    // ======================= CLEAR =======================
    private void clearFields() {
        nomField.clear();
        descriptionField.clear();
        localisationField.clear();
        statutField.setValue(null); // ← pour ComboBox
        imagesField.clear();
    }

    // ======================= ALERT =======================
    private void showAlert(String title, String message) {
        Alert alert = new Alert(Alert.AlertType.INFORMATION);
        alert.setTitle(title);
        alert.setContentText(message);
        alert.showAndWait();
    }



    @FXML
    private void choisirImage() {

        FileChooser fileChooser = new FileChooser();
        fileChooser.setTitle("Choisir une image");

        fileChooser.getExtensionFilters().addAll(
                new FileChooser.ExtensionFilter("Images", "*.png", "*.jpg", "*.jpeg")
        );

        File file = fileChooser.showOpenDialog(null);

        if (file != null) {
            imagesField.setText(file.getAbsolutePath());
        }
    }



    private List<String> searchLocation(String query) {

        List<String> results = new ArrayList<>();

        try {
            String urlString = "https://nominatim.openstreetmap.org/search?q="
                    + query.replace(" ", "%20")
                    + "&format=json&limit=5";

            URL url = new URL(urlString);
            HttpURLConnection conn = (HttpURLConnection) url.openConnection();

            conn.setRequestMethod("GET");
            conn.setRequestProperty("User-Agent", "JavaFX App");

            BufferedReader reader = new BufferedReader(
                    new InputStreamReader(conn.getInputStream())
            );

            StringBuilder response = new StringBuilder();
            String line;

            while ((line = reader.readLine()) != null) {
                response.append(line);
            }

            reader.close();

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






    private String generateDescription(String city, String country, String type) {
        switch (type) {
            case "capital":
                return city + " est la capitale de " + country +
                        ", connue pour son importance politique, culturelle et touristique.";
            case "city":
                return city + " est une grande ville située en " + country +
                        ", appréciée pour son patrimoine, son dynamisme et ses attractions.";
            case "town":
                return city + " est une charmante ville de " + country +
                        ", réputée pour son authenticité et son cadre agréable.";
            default:
                return city + " est une destination située en " + country +
                        ", offrant un mélange unique de culture et de paysages.";
        }
    }



    @FXML
    private void choisirImage1(javafx.event.ActionEvent event) {
        FileChooser fileChooser = new FileChooser();
        fileChooser.setTitle("Choisir une image");

        fileChooser.getExtensionFilters().addAll(
                new FileChooser.ExtensionFilter("Images", "*.png", "*.jpg", "*.jpeg")
        );

        File file = fileChooser.showOpenDialog(null);

        if (file != null) {
            try {
                // Upload sur Google Drive
                String imageUrl = GoogleDriveService.uploadFile(file);
                // Stocker l'URL dans le champ
                imagesField.setText(imageUrl);
                System.out.println("Upload OK : " + imageUrl);
            } catch (Exception e) {
                e.printStackTrace();
                showAlert("Erreur Upload", e.getMessage());
            }
        }
    }



    private void addMapsButtonToTable() {

        TableColumn<Destination, Void> colMaps = new TableColumn<>("Maps");

        Callback<TableColumn<Destination, Void>, TableCell<Destination, Void>> cellFactory = param -> new TableCell<>() {

            private final Button btnMaps = new Button("🗺️");

            {
                btnMaps.setOnAction(event -> {
                    Destination d = getTableView().getItems().get(getIndex());
                    openInMaps(d.getLocalisation());
                });
            }

            @Override
            protected void updateItem(Void item, boolean empty) {
                super.updateItem(item, empty);
                if (empty) {
                    setGraphic(null);
                } else {
                    setGraphic(btnMaps);
                }
            }
        };

        colMaps.setCellFactory(cellFactory);
        tableView.getColumns().add(colMaps);
    }




    private void openInMaps(String localisation) {
        try {
            if (localisation == null || localisation.isEmpty()) return;

            // Générer lien Google Maps
            String mapsUrl = "https://www.google.com/maps/search/?api=1&query="
                    + localisation.replace(" ", "+");

            // Copier dans le presse-papiers
            javafx.scene.input.Clipboard clipboard = javafx.scene.input.Clipboard.getSystemClipboard();
            javafx.scene.input.ClipboardContent content = new javafx.scene.input.ClipboardContent();
            content.putString(mapsUrl);
            clipboard.setContent(content);

            System.out.println("Lien Google Maps copié : " + mapsUrl);

            // Ouvrir dans le navigateur par défaut
            java.awt.Desktop.getDesktop().browse(new java.net.URI(mapsUrl));

        } catch (Exception e) {
            e.printStackTrace();
        }
    }


    @FXML
    private void reloadTable() {
        try {
            ObservableList<Destination> list =
                    FXCollections.observableArrayList(service.afficher());

            tableView.setItems(list);
            tableView.refresh();

            showAlert("Actualisation", "Liste mise à jour avec succès !");

        } catch (SQLException e) {
            showAlert("Erreur", e.getMessage());
        }
    }

}