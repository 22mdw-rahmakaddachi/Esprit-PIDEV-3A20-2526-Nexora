package controller;

import com.pi.entities.Activite;
import com.pi.entities.ParticipationDemande;
import com.pi.entity.ActiviteService;
import com.pi.entity.ParticipationDemandeService;
import com.pi.utils.SessionManager;
import com.pi.utils.NotificationManager;
import com.pi.utils.HuggingFaceService;
import javafx.collections.FXCollections;
import javafx.collections.ObservableList;
import javafx.fxml.FXML;
import javafx.fxml.FXMLLoader;
import javafx.geometry.Pos;
import javafx.scene.Parent;
import javafx.scene.Scene;
import javafx.scene.control.*;
import javafx.scene.control.cell.PropertyValueFactory;
import javafx.scene.layout.HBox;
import javafx.scene.layout.VBox;
import javafx.stage.FileChooser;
import javafx.stage.Stage;

import java.io.File;
import java.io.IOException;
import java.sql.SQLException;
import java.time.LocalDate;
import java.time.format.DateTimeFormatter;
import java.util.List;

public class GestionActivitesPartenaireController {
    @FXML private TextField nomField;
    @FXML private ComboBox<String> typeCombo;
    @FXML private TextField autreTypeField;  // Nouveau champ pour type personnalisé
    @FXML private ComboBox<String> lieuCombo;
    @FXML private DatePicker dateActivitePicker;
    @FXML private TextField prixField;
    @FXML private TextField nombrePlacesField;
    @FXML private TextField imagesField;
    @FXML private Button selectImageBtn;
    @FXML private Button ajouterBtn;
    @FXML private Button modifierBtn;
    @FXML private Button supprimerBtn;

    // Nouveaux champs pour l'interface améliorée
    @FXML private ComboBox<String> filtreTypeCombo;
    @FXML private ComboBox<String> filtreLieuCombo;
    @FXML private DatePicker filtreDatePicker;
    @FXML private VBox formulairePane;
    @FXML private TextArea descriptionArea;
    @FXML private Button toggleFormulaireBtn;
    @FXML private Button genererDescriptionBtn;
    @FXML private Button enregistrerBtn;
    @FXML private Label countLabel;

    // Champ de recherche
    @FXML private TextField searchField;

    // Labels d'erreur pour validation inline
    @FXML private Label nomErrorLabel;
    @FXML private Label typeErrorLabel;
    @FXML private Label lieuErrorLabel;
    @FXML private Label dateErrorLabel;
    @FXML private Label prixErrorLabel;
    @FXML private Label placesErrorLabel;
    @FXML private Label notificationBadge;

    @FXML private TableView<Activite> activitesTable;
    @FXML private TableColumn<Activite, String> nomCol;
    @FXML private TableColumn<Activite, String> typeCol;
    @FXML private TableColumn<Activite, String> lieuCol;
    @FXML private TableColumn<Activite, LocalDate> dateCol;
    @FXML private TableColumn<Activite, Double> prixCol;
    @FXML private TableColumn<Activite, Integer> placesCol;
    @FXML private TableColumn<Activite, String> partenaireCol;
    @FXML private TableColumn<Activite, Void> actionsCol;

    private ActiviteService activiteService;
    private ParticipationDemandeService demandeService;
    private ObservableList<Activite> activitesList;
    private ObservableList<Activite> activitesListeComplete; // Liste complète pour la recherche
    private int partenaireId = 1; // Sera défini par setPartenaireId()

    public void setPartenaireId(int partenaireId) {
        this.partenaireId = partenaireId;
        System.out.println("🔑 GestionActivitesPartenaire - ID Partenaire défini: " + partenaireId);
        
        // En mode admin (partenaireId = -1), désactiver les boutons d'ajout/modification
        if (partenaireId == -1) {
            System.out.println("👑 Mode ADMIN activé - Consultation seule");
            if (ajouterBtn != null) ajouterBtn.setDisable(true);
            if (modifierBtn != null) modifierBtn.setDisable(true);
            if (toggleFormulaireBtn != null) toggleFormulaireBtn.setDisable(true);
            if (enregistrerBtn != null) enregistrerBtn.setDisable(true);
        }
        
        loadActivites(); // Charger les activités du partenaire
    }

    @FXML
    public void initialize() {
        activiteService = new ActiviteService();
        demandeService = new ParticipationDemandeService();
        activitesList = FXCollections.observableArrayList();
        activitesListeComplete = FXCollections.observableArrayList();

        setupComboBoxes();
        setupTable();
        loadActivites();
        setupEventHandlers();
        setupFiltres();

        // Charger les notifications
        chargerNotifications();

        // Mettre à jour le compteur
        updateCountLabel();
    }

    /**
     * Configure les ComboBox de filtres
     */
    private void setupFiltres() {
        if (filtreTypeCombo != null) {
            filtreTypeCombo.setItems(FXCollections.observableArrayList(
                    "Sport", "Culture", "Aventure", "Gastronomie", "Détente", "Éducatif", "Autre"
            ));
        }

        if (filtreLieuCombo != null) {
            filtreLieuCombo.setItems(FXCollections.observableArrayList(
                    "Tunis", "Ariana", "Ben Arous", "Manouba", "Nabeul", "Zaghouan",
                    "Bizerte", "Béja", "Jendouba", "Kef", "Siliana", "Sousse",
                    "Monastir", "Mahdia", "Sfax", "Kairouan", "Kasserine", "Sidi Bouzid",
                    "Gabès", "Médenine", "Tataouine", "Gafsa", "Tozeur", "Kébili"
            ));
        }
    }

    /**
     * Met à jour le label du compteur d'activités
     */
    private void updateCountLabel() {
        if (countLabel != null) {
            int count = activitesList.size();
            countLabel.setText("(" + count + " activité" + (count > 1 ? "s" : "") + ")");
        }
    }

    /**
     * Charge et affiche le nombre de notifications non lues
     */
    private void chargerNotifications() {
        com.pi.entities.user currentUser = SessionManager.getCurrentUser();
        if (currentUser != null) {
            int count = NotificationManager.getNombreNotificationsNonLues(
                    currentUser.getId(),
                    "PARTENAIRE"
            );

            if (count > 0) {
                notificationBadge.setText(String.valueOf(count));
                notificationBadge.setVisible(true);
                notificationBadge.setManaged(true);
            } else {
                notificationBadge.setVisible(false);
                notificationBadge.setManaged(false);
            }
        }
    }

    /**
     * Ouvre la fenêtre des notifications
     */
    @FXML
    private void ouvrirNotifications() {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/Notifications.fxml"));
            Parent root = loader.load();

            NotificationsController controller = loader.getController();
            controller.setUserType("PARTENAIRE");

            Stage stage = new Stage();
            stage.setTitle("Notifications");
            stage.setScene(new javafx.scene.Scene(root));
            stage.setOnHidden(e -> chargerNotifications()); // Recharger le badge à la fermeture
            stage.show();

        } catch (IOException e) {
            System.err.println("Erreur ouverture notifications: " + e.getMessage());
            e.printStackTrace();
        }
    }

    private void setupComboBoxes() {
        typeCombo.setItems(FXCollections.observableArrayList(
                "Sport", "Culture", "Aventure", "Gastronomie", "Détente", "Éducatif", "Autre"
        ));

        // Listener pour afficher/cacher le champ "Autre"
        typeCombo.valueProperty().addListener((obs, oldValue, newValue) -> {
            if ("Autre".equals(newValue)) {
                autreTypeField.setVisible(true);
                autreTypeField.setManaged(true);
                autreTypeField.requestFocus();
            } else {
                autreTypeField.setVisible(false);
                autreTypeField.setManaged(false);
                autreTypeField.clear();
            }
        });

        lieuCombo.setItems(FXCollections.observableArrayList(
                "Tunis", "Ariana", "Ben Arous", "Manouba", "Nabeul", "Zaghouan",
                "Bizerte", "Béja", "Jendouba", "Kef", "Siliana", "Sousse",
                "Monastir", "Mahdia", "Sfax", "Kairouan", "Kasserine", "Sidi Bouzid",
                "Gabès", "Médenine", "Tataouine", "Gafsa", "Tozeur", "Kébili"
        ));
    }

    private void setupTable() {
        nomCol.setCellValueFactory(new PropertyValueFactory<>("nom"));
        typeCol.setCellValueFactory(new PropertyValueFactory<>("type"));
        lieuCol.setCellValueFactory(new PropertyValueFactory<>("lieu"));
        dateCol.setCellValueFactory(new PropertyValueFactory<>("dateActivite"));
        prixCol.setCellValueFactory(new PropertyValueFactory<>("prix"));
        placesCol.setCellValueFactory(new PropertyValueFactory<>("placesDisponibles"));
        partenaireCol.setCellValueFactory(new PropertyValueFactory<>("partenaireNom"));

        // Appliquer le style rouge aux lignes expirées
        activitesTable.setRowFactory(tv -> new TableRow<Activite>() {
            @Override
            protected void updateItem(Activite activite, boolean empty) {
                super.updateItem(activite, empty);

                if (empty || activite == null) {
                    setStyle("");
                } else {
                    // Vérifier si l'activité est expirée
                    boolean isExpired = activite.getDateActivite() != null &&
                            activite.getDateActivite().isBefore(LocalDate.now());

                    if (isExpired) {
                        // Style rouge pour les activités expirées
                        setStyle("-fx-background-color: #FEE2E2; -fx-text-fill: #991B1B;");
                    } else {
                        setStyle("");
                    }
                }
            }
        });

        // Colonne Actions avec boutons
        actionsCol.setCellFactory(param -> new TableCell<>() {
            private final Button modifierBtn = new Button("✏️ Modifier");
            private final Button supprimerBtn = new Button("🗑️ Supprimer");
            private final HBox actionBox = new HBox(10, modifierBtn, supprimerBtn);

            {
                // Style des boutons
                modifierBtn.setStyle("-fx-background-color: #3B82F6; -fx-text-fill: white; -fx-background-radius: 6; -fx-padding: 5 15; -fx-cursor: hand; -fx-font-size: 12px;");
                supprimerBtn.setStyle("-fx-background-color: #EF4444; -fx-text-fill: white; -fx-background-radius: 6; -fx-padding: 5 15; -fx-cursor: hand; -fx-font-size: 12px;");

                actionBox.setAlignment(javafx.geometry.Pos.CENTER);

                // Actions des boutons
                modifierBtn.setOnAction(event -> {
                    Activite activite = getTableView().getItems().get(getIndex());

                    // Vérifier si l'activité est expirée
                    boolean isExpired = activite.getDateActivite() != null &&
                            activite.getDateActivite().isBefore(LocalDate.now());

                    if (isExpired) {
                        Alert alert = new Alert(Alert.AlertType.WARNING);
                        alert.setTitle("Modification impossible");
                        alert.setHeaderText("Activité expirée");
                        alert.setContentText("Vous ne pouvez pas modifier une activité dont la date est passée.\n\n" +
                                "Vous pouvez uniquement la supprimer.");
                        alert.showAndWait();
                    } else {
                        fillFields(activite);
                    }
                });

                supprimerBtn.setOnAction(event -> {
                    Activite activite = getTableView().getItems().get(getIndex());
                    getTableView().getSelectionModel().select(activite);
                    supprimerActivite();
                });
            }

            @Override
            protected void updateItem(Void item, boolean empty) {
                super.updateItem(item, empty);
                if (empty) {
                    setGraphic(null);
                } else {
                    // Vérifier si l'activité est expirée pour désactiver le bouton Modifier
                    Activite activite = getTableView().getItems().get(getIndex());
                    boolean isExpired = activite.getDateActivite() != null &&
                            activite.getDateActivite().isBefore(LocalDate.now());

                    if (isExpired) {
                        modifierBtn.setDisable(true);
                        modifierBtn.setStyle("-fx-background-color: #9CA3AF; -fx-text-fill: white; -fx-background-radius: 6; -fx-padding: 5 15; -fx-opacity: 0.6;");
                    } else {
                        modifierBtn.setDisable(false);
                        modifierBtn.setStyle("-fx-background-color: #3B82F6; -fx-text-fill: white; -fx-background-radius: 6; -fx-padding: 5 15; -fx-cursor: hand; -fx-font-size: 12px;");
                    }

                    setGraphic(actionBox);
                }
            }
        });

        activitesTable.setItems(activitesList);
    }

    private void setupEventHandlers() {
        if (selectImageBtn != null) {
            selectImageBtn.setOnAction(e -> selectImage());
        }

        activitesTable.getSelectionModel().selectedItemProperty().addListener(
                (obs, oldSelection, newSelection) -> {
                    if (newSelection != null) {
                        fillFields(newSelection);
                    }
                }
        );
    }

    @FXML
    private void selectImage() {
        FileChooser fileChooser = new FileChooser();
        fileChooser.setTitle("Sélectionner une image");
        fileChooser.getExtensionFilters().addAll(
                new FileChooser.ExtensionFilter("Images", "*.png", "*.jpg", "*.jpeg", "*.gif")
        );
        File file = fileChooser.showOpenDialog(selectImageBtn.getScene().getWindow());
        if (file != null) {
            imagesField.setText(file.getAbsolutePath());
        }
    }

    @FXML
    private void ajouterActivite() {
        if (!validateFields()) return;

        try {
            // Déterminer le type à enregistrer
            String typeAEnregistrer;
            if ("Autre".equals(typeCombo.getValue())) {
                typeAEnregistrer = autreTypeField.getText().trim();
            } else {
                typeAEnregistrer = typeCombo.getValue();
            }

            Activite activite = new Activite(
                    nomField.getText(),
                    typeAEnregistrer,
                    lieuCombo.getValue(),
                    dateActivitePicker.getValue(),
                    imagesField.getText(),
                    Double.parseDouble(prixField.getText()),
                    Integer.parseInt(nombrePlacesField.getText()),
                    partenaireId
            );

            activiteService.ajouter(activite);
            loadActivites();
            clearFields();
        } catch (SQLException e) {
            showError(nomField, nomErrorLabel, "❌ Erreur lors de l'ajout: " + e.getMessage());
        }
    }

    @FXML
    private void modifierActivite() {
        Activite selected = activitesTable.getSelectionModel().getSelectedItem();
        if (selected == null) {
            showError(nomField, nomErrorLabel, "❌ Veuillez sélectionner une activité à modifier");
            return;
        }

        if (!validateFields()) return;

        try {
            // Déterminer le type à enregistrer
            String typeAEnregistrer;
            if ("Autre".equals(typeCombo.getValue())) {
                typeAEnregistrer = autreTypeField.getText().trim();
            } else {
                typeAEnregistrer = typeCombo.getValue();
            }

            selected.setNom(nomField.getText());
            selected.setType(typeAEnregistrer);
            selected.setLieu(lieuCombo.getValue());
            selected.setDateActivite(dateActivitePicker.getValue());
            selected.setImages(imagesField.getText());
            selected.setPrix(Double.parseDouble(prixField.getText()));
            selected.setNombrePlaces(Integer.parseInt(nombrePlacesField.getText()));

            activiteService.modifier(selected);
            loadActivites();
            clearFields();
        } catch (SQLException e) {
            showError(nomField, nomErrorLabel, "❌ Erreur lors de la modification: " + e.getMessage());
        }
    }

    @FXML
    private void supprimerActivite() {
        Activite selected = activitesTable.getSelectionModel().getSelectedItem();
        if (selected == null) {
            showError(nomField, nomErrorLabel, "❌ Veuillez sélectionner une activité à supprimer");
            return;
        }

        Alert confirm = new Alert(Alert.AlertType.CONFIRMATION);
        confirm.setTitle("Confirmation");
        confirm.setHeaderText("Supprimer l'activité?");
        confirm.setContentText("Êtes-vous sûr de vouloir supprimer cette activité?");

        if (confirm.showAndWait().get() == ButtonType.OK) {
            try {
                activiteService.supprimer(selected.getId());
                loadActivites();
                clearFields();
            } catch (SQLException e) {
                showError(nomField, nomErrorLabel, "❌ Erreur lors de la suppression: " + e.getMessage());
            }
        }
    }

    @FXML
    private void voirParticipants() {
        Activite selected = activitesTable.getSelectionModel().getSelectedItem();
        if (selected == null) {
            showError(nomField, nomErrorLabel, "❌ Veuillez sélectionner une activité");
            return;
        }

        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/Participants.fxml"));
            Parent root = loader.load();

            ParticipantsController controller = loader.getController();
            controller.setActivite(selected);

            Stage stage = new Stage();
            stage.setTitle("Participants - " + selected.getNom());
            stage.setScene(new Scene(root));
            stage.show();
        } catch (IOException e) {
            e.printStackTrace();
            showError(nomField, nomErrorLabel, "❌ Impossible d'ouvrir la fenêtre: " + e.getMessage());
        }
    }

    private void loadActivites() {
        try {
            System.out.println("📋 Chargement des activités pour partenaire ID: " + partenaireId);
            activitesList.clear();
            activitesListeComplete.clear();
            
            List<Activite> activites;
            if (partenaireId == -1) {
                // Mode admin : récupérer TOUTES les activités de TOUS les partenaires
                activites = activiteService.getAllForAdmin();
                System.out.println("👑 Mode ADMIN : Chargement de toutes les activités");
            } else {
                // Mode partenaire normal : récupérer seulement les activités du partenaire
                activites = activiteService.getByPartenaire(partenaireId);
                System.out.println("🏢 Mode PARTENAIRE : Chargement des activités du partenaire #" + partenaireId);
            }
            
            activitesListeComplete.addAll(activites);
            activitesList.addAll(activites);
            System.out.println("✅ " + activitesList.size() + " activité(s) chargée(s)");
            updateCountLabel();
        } catch (SQLException e) {
            showError(nomField, nomErrorLabel, "❌ Erreur lors du chargement: " + e.getMessage());
            e.printStackTrace();
        }
    }

    private void fillFields(Activite activite) {
        // Sélectionner l'activité dans le tableau pour que enregistrerActivite() sache qu'on est en mode modification
        activitesTable.getSelectionModel().select(activite);

        nomField.setText(activite.getNom());

        // Vérifier si le type existe dans la liste prédéfinie
        String type = activite.getType();
        ObservableList<String> typesList = typeCombo.getItems();

        if (typesList.contains(type)) {
            // Type prédéfini
            typeCombo.setValue(type);
        } else {
            // Type personnalisé
            typeCombo.setValue("Autre");
            autreTypeField.setText(type);
            autreTypeField.setVisible(true);
            autreTypeField.setManaged(true);
        }

        lieuCombo.setValue(activite.getLieu());
        dateActivitePicker.setValue(activite.getDateActivite());
        prixField.setText(String.valueOf(activite.getPrix()));
        nombrePlacesField.setText(String.valueOf(activite.getNombrePlaces()));
        imagesField.setText(activite.getImages());

        // Remplir la description si elle existe
        if (descriptionArea != null && activite.getDescription() != null) {
            descriptionArea.setText(activite.getDescription());
        }

        // Afficher le formulaire en mode modification
        if (formulairePane != null && !formulairePane.isVisible()) {
            formulairePane.setVisible(true);
            formulairePane.setManaged(true);
            toggleFormulaireBtn.setText("❌ Fermer");
        }
    }

    private void clearFields() {
        nomField.clear();
        typeCombo.setValue(null);
        autreTypeField.clear();
        autreTypeField.setVisible(false);
        autreTypeField.setManaged(false);
        lieuCombo.setValue(null);
        dateActivitePicker.setValue(null);
        prixField.clear();
        nombrePlacesField.clear();
        imagesField.clear();
        if (descriptionArea != null) {
            descriptionArea.clear();
        }
        activitesTable.getSelectionModel().clearSelection();
    }

    private boolean validateFields() {
        boolean isValid = true;

        // Cacher tous les messages d'erreur d'abord
        hideAllErrors();

        // Réinitialiser les styles des champs
        nomField.setStyle("");
        typeCombo.setStyle("");
        lieuCombo.setStyle("");
        dateActivitePicker.setStyle("");
        prixField.setStyle("");
        nombrePlacesField.setStyle("");

        // Validation du nom
        if (nomField.getText().isEmpty()) {
            showError(nomField, nomErrorLabel, "❌ Le nom de l'activité est obligatoire");
            isValid = false;
        }

        // Validation du type
        if (typeCombo.getValue() == null) {
            showError(typeCombo, typeErrorLabel, "❌ Le type d'activité est obligatoire");
            isValid = false;
        } else if ("Autre".equals(typeCombo.getValue())) {
            if (autreTypeField.getText() == null || autreTypeField.getText().trim().isEmpty()) {
                showError(autreTypeField, typeErrorLabel, "❌ Veuillez entrer le type personnalisé");
                isValid = false;
            }
        }

        // Validation du lieu
        if (lieuCombo.getValue() == null) {
            showError(lieuCombo, lieuErrorLabel, "❌ Le lieu est obligatoire");
            isValid = false;
        }

        // Validation de la date
        if (dateActivitePicker.getValue() == null) {
            showError(dateActivitePicker, dateErrorLabel, "❌ La date de l'activité est obligatoire");
            isValid = false;
        } else if (dateActivitePicker.getValue().isBefore(LocalDate.now())) {
            showError(dateActivitePicker, dateErrorLabel, "❌ La date doit être dans le futur");
            isValid = false;
        }

        // Validation du prix
        if (prixField.getText().isEmpty()) {
            showError(prixField, prixErrorLabel, "❌ Le prix est obligatoire");
            isValid = false;
        } else {
            try {
                double prix = Double.parseDouble(prixField.getText());
                if (prix <= 0) {
                    showError(prixField, prixErrorLabel, "❌ Le prix doit être supérieur à 0");
                    isValid = false;
                }
            } catch (NumberFormatException e) {
                showError(prixField, prixErrorLabel, "❌ Le prix doit être un nombre valide");
                isValid = false;
            }
        }

        // Validation du nombre de places
        if (nombrePlacesField.getText().isEmpty()) {
            showError(nombrePlacesField, placesErrorLabel, "❌ Le nombre de places est obligatoire");
            isValid = false;
        } else {
            try {
                int places = Integer.parseInt(nombrePlacesField.getText());
                if (places <= 0) {
                    showError(nombrePlacesField, placesErrorLabel, "❌ Le nombre de places doit être supérieur à 0");
                    isValid = false;
                }
            } catch (NumberFormatException e) {
                showError(nombrePlacesField, placesErrorLabel, "❌ Le nombre de places doit être un nombre entier");
                isValid = false;
            }
        }

        return isValid;
    }

    private void showError(javafx.scene.Node field, Label errorLabel, String message) {
        // Changer le style du champ
        if (field instanceof TextField) {
            field.setStyle("-fx-border-color: #DC2626; -fx-border-width: 2px;");
        } else if (field instanceof ComboBox) {
            field.setStyle("-fx-border-color: #DC2626; -fx-border-width: 2px;");
        } else if (field instanceof DatePicker) {
            field.setStyle("-fx-border-color: #DC2626; -fx-border-width: 2px;");
        }

        // Afficher le message d'erreur
        errorLabel.setText(message);
        errorLabel.setVisible(true);
        errorLabel.setManaged(true);
    }

    private void hideAllErrors() {
        nomErrorLabel.setVisible(false);
        nomErrorLabel.setManaged(false);
        typeErrorLabel.setVisible(false);
        typeErrorLabel.setManaged(false);
        lieuErrorLabel.setVisible(false);
        lieuErrorLabel.setManaged(false);
        dateErrorLabel.setVisible(false);
        dateErrorLabel.setManaged(false);
        prixErrorLabel.setVisible(false);
        prixErrorLabel.setManaged(false);
        placesErrorLabel.setVisible(false);
        placesErrorLabel.setManaged(false);
    }

    @FXML
    private void deconnexion() {
        try {
            // Charger l'interface de connexion
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/loginne.fxml"));
            Parent root = loader.load();

            // Obtenir la fenêtre actuelle et la remplacer
            Stage stage = (Stage) activitesTable.getScene().getWindow();
            stage.setScene(new Scene(root));
            stage.setTitle("Connexion");
            stage.show();

        } catch (IOException e) {
            showError(nomField, nomErrorLabel, "❌ Erreur lors de la déconnexion: " + e.getMessage());
            e.printStackTrace();
        }
    }

    @FXML
    private void rechercherActivite() {
        String recherche = searchField.getText().toLowerCase().trim();

        if (recherche.isEmpty()) {
            // Si le champ est vide, afficher toutes les activités
            activitesList.clear();
            activitesList.addAll(activitesListeComplete);
            return;
        }

        // Filtrer les activités
        ObservableList<Activite> activitesFiltrees = FXCollections.observableArrayList();

        for (Activite activite : activitesListeComplete) {
            // Rechercher dans le nom, type et lieu
            if (activite.getNom().toLowerCase().contains(recherche) ||
                    activite.getType().toLowerCase().contains(recherche) ||
                    activite.getLieu().toLowerCase().contains(recherche)) {
                activitesFiltrees.add(activite);
            }
        }

        // Mettre à jour la liste affichée
        activitesList.clear();
        activitesList.addAll(activitesFiltrees);
    }

    @FXML
    private void actualiserActivites() {
        searchField.clear();
        loadActivites();
        clearFields();
    }

    private void showAlert(Alert.AlertType type, String title, String content) {
        Alert alert = new Alert(type);
        alert.setTitle(title);
        alert.setHeaderText(null);
        alert.setContentText(content);
        alert.showAndWait();
    }

    /**
     * Affiche/cache le formulaire d'ajout
     */
    @FXML
    private void toggleFormulaire() {
        if (formulairePane != null) {
            boolean isVisible = formulairePane.isVisible();
            formulairePane.setVisible(!isVisible);
            formulairePane.setManaged(!isVisible);

            if (!isVisible) {
                clearFields();
                toggleFormulaireBtn.setText("❌ Fermer");
            } else {
                toggleFormulaireBtn.setText("➕ Ajouter");
            }
        }
    }

    /**
     * Applique les filtres sur les activités
     */
    @FXML
    private void appliquerFiltres() {
        ObservableList<Activite> activitesFiltrees = FXCollections.observableArrayList();

        String typeFiltre = filtreTypeCombo != null ? filtreTypeCombo.getValue() : null;
        String lieuFiltre = filtreLieuCombo != null ? filtreLieuCombo.getValue() : null;
        LocalDate dateFiltre = filtreDatePicker != null ? filtreDatePicker.getValue() : null;

        for (Activite activite : activitesListeComplete) {
            boolean match = true;

            if (typeFiltre != null && !typeFiltre.isEmpty()) {
                match = match && activite.getType().equalsIgnoreCase(typeFiltre);
            }

            if (lieuFiltre != null && !lieuFiltre.isEmpty()) {
                match = match && activite.getLieu().equalsIgnoreCase(lieuFiltre);
            }

            if (dateFiltre != null) {
                match = match && activite.getDateActivite().equals(dateFiltre);
            }

            if (match) {
                activitesFiltrees.add(activite);
            }
        }

        activitesList.clear();
        activitesList.addAll(activitesFiltrees);
        updateCountLabel();
    }

    /**
     * Réinitialise tous les filtres
     */
    @FXML
    private void reinitialiserFiltres() {
        if (filtreTypeCombo != null) filtreTypeCombo.setValue(null);
        if (filtreLieuCombo != null) filtreLieuCombo.setValue(null);
        if (filtreDatePicker != null) filtreDatePicker.setValue(null);

        activitesList.clear();
        activitesList.addAll(activitesListeComplete);
        updateCountLabel();
    }

    /**
     * Génère une description avec HuggingFace
     */
    @FXML
    private void genererDescription() {
        if (descriptionArea == null) return;

        // Vérifier que les champs nécessaires sont remplis
        if (nomField.getText().isEmpty() || typeCombo.getValue() == null || lieuCombo.getValue() == null) {
            showAlert(Alert.AlertType.WARNING, "Champs manquants",
                    "Veuillez remplir au moins le nom, le type et le lieu pour générer une description.");
            return;
        }

        try {
            genererDescriptionBtn.setDisable(true);
            genererDescriptionBtn.setText("⏳ Génération...");

            String nom = nomField.getText();
            String type = "Autre".equals(typeCombo.getValue()) ? autreTypeField.getText() : typeCombo.getValue();
            String lieu = lieuCombo.getValue();
            String date = dateActivitePicker.getValue() != null ?
                    dateActivitePicker.getValue().format(DateTimeFormatter.ofPattern("dd/MM/yyyy")) :
                    "date à définir";

            String description = HuggingFaceService.generateActivityDescription(nom, type, lieu, date);

            descriptionArea.setText(description);

        } catch (Exception e) {
            showAlert(Alert.AlertType.ERROR, "Erreur",
                    "Impossible de générer la description: " + e.getMessage());
            e.printStackTrace();
        } finally {
            genererDescriptionBtn.setDisable(false);
            genererDescriptionBtn.setText("✨ Générer avec IA");
        }
    }

    /**
     * Enregistre l'activité (ajouter ou modifier)
     */
    @FXML
    private void enregistrerActivite() {
        Activite selected = activitesTable.getSelectionModel().getSelectedItem();

        // Vérifier si on est en mode modification en vérifiant si l'ID existe
        boolean isModification = selected != null && selected.getId() > 0;

        if (isModification) {
            // Mode modification
            System.out.println("🔄 Mode MODIFICATION - ID: " + selected.getId());

            // Demander confirmation pour la modification
            Alert confirm = new Alert(Alert.AlertType.CONFIRMATION);
            confirm.setTitle("Confirmation");
            confirm.setHeaderText("Modifier l'activité?");
            confirm.setContentText("Voulez-vous vraiment modifier l'activité \"" + selected.getNom() + "\"?\n\n" +
                    "Si vous voulez AJOUTER une nouvelle activité, cliquez sur 'Annuler' puis sur le bouton '➕ Ajouter'.");

            if (confirm.showAndWait().get() == ButtonType.OK) {
                modifierActiviteAvecDescription();
            }
        } else {
            // Mode ajout
            System.out.println("➕ Mode AJOUT - Nouvelle activité");
            ajouterActiviteAvecDescription();
        }
    }

    /**
     * Ajoute une activité avec description
     */
    private void ajouterActiviteAvecDescription() {
        if (!validateFields()) return;

        // Vérification de sécurité pour le partenaire
        if (partenaireId <= 0) {
            showAlert(Alert.AlertType.ERROR, "Erreur", 
                "Partenaire non défini. Veuillez vous reconnecter ou contacter l'administrateur.");
            return;
        }

        try {
            String typeAEnregistrer;
            if ("Autre".equals(typeCombo.getValue())) {
                typeAEnregistrer = autreTypeField.getText().trim();
            } else {
                typeAEnregistrer = typeCombo.getValue();
            }

            System.out.println("🔍 DEBUG: Création activité avec partenaireId=" + partenaireId);

            Activite activite = new Activite(
                    nomField.getText(),
                    typeAEnregistrer,
                    lieuCombo.getValue(),
                    dateActivitePicker.getValue(),
                    imagesField.getText(),
                    Double.parseDouble(prixField.getText()),
                    Integer.parseInt(nombrePlacesField.getText()),
                    partenaireId
            );

            // Ajouter la description si elle existe
            if (descriptionArea != null && !descriptionArea.getText().trim().isEmpty()) {
                activite.setDescription(descriptionArea.getText().trim());
            }

            activiteService.ajouter(activite);
            loadActivites();
            clearFields();
            toggleFormulaire();

            showAlert(Alert.AlertType.INFORMATION, "Succès", "Activité ajoutée avec succès!");

        } catch (SQLException e) {
            String errorMsg = e.getMessage();
            if (errorMsg.contains("foreign key constraint fails") && errorMsg.contains("partenaire")) {
                showAlert(Alert.AlertType.ERROR, "Erreur de Partenaire", 
                    "Le partenaire associé n'existe pas dans la base de données.\n\n" +
                    "Solutions :\n" +
                    "1. Exécutez le script 'fix_partenaire_activite_constraint.sql'\n" +
                    "2. Ou contactez l'administrateur pour créer votre profil partenaire\n\n" +
                    "Erreur technique : " + errorMsg);
            } else {
                showError(nomField, nomErrorLabel, "❌ Erreur lors de l'ajout: " + errorMsg);
            }
        }
    }

    /**
     * Modifie une activité avec description
     */
    private void modifierActiviteAvecDescription() {
        Activite selected = activitesTable.getSelectionModel().getSelectedItem();
        if (selected == null) return;

        if (!validateFields()) return;

        try {
            String typeAEnregistrer;
            if ("Autre".equals(typeCombo.getValue())) {
                typeAEnregistrer = autreTypeField.getText().trim();
            } else {
                typeAEnregistrer = typeCombo.getValue();
            }

            selected.setNom(nomField.getText());
            selected.setType(typeAEnregistrer);
            selected.setLieu(lieuCombo.getValue());
            selected.setDateActivite(dateActivitePicker.getValue());
            selected.setImages(imagesField.getText());
            selected.setPrix(Double.parseDouble(prixField.getText()));
            selected.setNombrePlaces(Integer.parseInt(nombrePlacesField.getText()));

            // Mettre à jour la description
            if (descriptionArea != null) {
                selected.setDescription(descriptionArea.getText().trim());
            }

            activiteService.modifier(selected);
            loadActivites();
            clearFields();
            toggleFormulaire();

            showAlert(Alert.AlertType.INFORMATION, "Succès", "Activité modifiée avec succès!");

        } catch (SQLException e) {
            showError(nomField, nomErrorLabel, "❌ Erreur lors de la modification: " + e.getMessage());
        }
    }
}
