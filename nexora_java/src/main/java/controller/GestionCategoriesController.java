package controller;

import com.pi.dto.CategorieAvecSousCategoriesDTO;
import com.pi.entities.Categorie;
import com.pi.entities.SousCategorie;
import com.pi.entity.CatalogueService;
import com.pi.entity.CategorieService;
import com.pi.entity.SousCategorieService;
import javafx.fxml.FXML;
import javafx.geometry.Insets;
import javafx.geometry.Pos;
import javafx.scene.control.*;
import javafx.scene.layout.*;
import javafx.stage.FileChooser;
import java.io.File;
import java.io.IOException;
import java.nio.file.Files;
import java.nio.file.Path;
import java.nio.file.Paths;
import java.nio.file.StandardCopyOption;
import java.sql.SQLException;
import java.util.List;

public class GestionCategoriesController {

    @FXML private VBox categoriesContainer;
    @FXML private VBox formContainer;
    @FXML private Label formTitle;
    @FXML private TextField nomField;
    @FXML private Label nomError;
    @FXML private TextArea descriptionField;
    @FXML private TextField imagePathField;
    @FXML private VBox categorieParentBox;
    @FXML private ComboBox<Categorie> categorieParenteCombo;

    private CategorieService categorieService = new CategorieService();
    private SousCategorieService sousCategorieService = new SousCategorieService();
    private CatalogueService catalogueService = new CatalogueService();
    
    private boolean isEditMode = false;
    private boolean isSousCategorie = false;
    private Categorie selectedCategorie;
    private SousCategorie selectedSousCategorie;
    private File selectedImageFile = null;

    @FXML
    public void initialize() {
        loadCategories();
    }

    private void loadCategories() {
        try {
            categoriesContainer.getChildren().clear();
            List<CategorieAvecSousCategoriesDTO> categories = catalogueService.getCategoriesAvecSousCategories();
            
            for (CategorieAvecSousCategoriesDTO dto : categories) {
                categoriesContainer.getChildren().add(createCategorieCard(dto));
            }
            
            System.out.println("✅ " + categories.size() + " catégories chargées");
        } catch (SQLException e) {
            showAlert("Erreur", "Erreur lors du chargement: " + e.getMessage());
        }
    }

    private VBox createCategorieCard(CategorieAvecSousCategoriesDTO dto) {
        VBox card = new VBox(10);
        card.setStyle("-fx-background-color: #F9FAFB; -fx-background-radius: 6; -fx-padding: 15; -fx-border-color: #E5E7EB; -fx-border-radius: 6;");

        // En-tête catégorie
        HBox header = new HBox(10);
        header.setAlignment(Pos.CENTER_LEFT);
        
        Label nomLabel = new Label("📁 " + dto.getCategorie().getNom());
        nomLabel.setStyle("-fx-font-size: 16px; -fx-font-weight: bold; -fx-text-fill: #1F2937;");
        
        Region spacer = new Region();
        HBox.setHgrow(spacer, Priority.ALWAYS);
        
        Button addSousCatBtn = new Button("➕");
        addSousCatBtn.setStyle("-fx-background-color: #3498db; -fx-text-fill: white; -fx-padding: 4 8; -fx-cursor: hand;");
        addSousCatBtn.setTooltip(new Tooltip("Ajouter une sous-catégorie"));
        addSousCatBtn.setOnAction(e -> handleAjouterSousCategorie(dto.getCategorie()));
        
        Button editBtn = new Button("✏️");
        editBtn.setStyle("-fx-background-color: #f39c12; -fx-text-fill: white; -fx-padding: 4 8; -fx-cursor: hand;");
        editBtn.setOnAction(e -> handleEditCategorie(dto.getCategorie()));
        
        Button deleteBtn = new Button("🗑️");
        deleteBtn.setStyle("-fx-background-color: #e74c3c; -fx-text-fill: white; -fx-padding: 4 8; -fx-cursor: hand;");
        deleteBtn.setOnAction(e -> handleDeleteCategorie(dto.getCategorie()));
        
        header.getChildren().addAll(nomLabel, spacer, addSousCatBtn, editBtn, deleteBtn);

        // Sous-catégories
        VBox sousCategoriesBox = new VBox(5);
        sousCategoriesBox.setPadding(new Insets(10, 0, 0, 20));
        
        for (SousCategorie sc : dto.getSousCategories()) {
            sousCategoriesBox.getChildren().add(createSousCategorieRow(sc));
        }

        card.getChildren().addAll(header, sousCategoriesBox);
        return card;
    }

    private HBox createSousCategorieRow(SousCategorie sc) {
        HBox row = new HBox(10);
        row.setAlignment(Pos.CENTER_LEFT);
        row.setStyle("-fx-padding: 5; -fx-background-color: white; -fx-background-radius: 4;");
        
        Label nomLabel = new Label("└─ " + sc.getNom());
        nomLabel.setStyle("-fx-font-size: 14px; -fx-text-fill: #4B5563;");
        
        Region spacer = new Region();
        HBox.setHgrow(spacer, Priority.ALWAYS);
        
        Button editBtn = new Button("✏️");
        editBtn.setStyle("-fx-background-color: #f39c12; -fx-text-fill: white; -fx-padding: 2 6; -fx-cursor: hand; -fx-font-size: 11px;");
        editBtn.setOnAction(e -> handleEditSousCategorie(sc));
        
        Button deleteBtn = new Button("🗑️");
        deleteBtn.setStyle("-fx-background-color: #e74c3c; -fx-text-fill: white; -fx-padding: 2 6; -fx-cursor: hand; -fx-font-size: 11px;");
        deleteBtn.setOnAction(e -> handleDeleteSousCategorie(sc));
        
        row.getChildren().addAll(nomLabel, spacer, editBtn, deleteBtn);
        return row;
    }

    @FXML
    public void handleAjouterCategorie() {
        isEditMode = false;
        isSousCategorie = false;
        selectedCategorie = null;
        formTitle.setText("Nouvelle Catégorie");
        categorieParentBox.setVisible(false);
        categorieParentBox.setManaged(false);
        clearForm();
        formContainer.setVisible(true);
    }

    private void handleAjouterSousCategorie(Categorie categorie) {
        isEditMode = false;
        isSousCategorie = true;
        selectedCategorie = categorie;
        formTitle.setText("Nouvelle Sous-Catégorie de " + categorie.getNom());
        categorieParentBox.setVisible(true);
        categorieParentBox.setManaged(true);
        categorieParenteCombo.setValue(categorie);
        categorieParenteCombo.setDisable(true);
        clearForm();
        formContainer.setVisible(true);
    }

    private void handleEditCategorie(Categorie c) {
        isEditMode = true;
        isSousCategorie = false;
        selectedCategorie = c;
        formTitle.setText("Modifier Catégorie");
        categorieParentBox.setVisible(false);
        categorieParentBox.setManaged(false);
        nomField.setText(c.getNom());
        descriptionField.setText(c.getDescription());
        imagePathField.setText(c.getImageUrl() != null ? c.getImageUrl() : "");
        formContainer.setVisible(true);
    }

    private void handleEditSousCategorie(SousCategorie sc) {
        isEditMode = true;
        isSousCategorie = true;
        selectedSousCategorie = sc;
        formTitle.setText("Modifier Sous-Catégorie");
        categorieParentBox.setVisible(true);
        categorieParentBox.setManaged(true);
        loadCategoriesCombo();
        nomField.setText(sc.getNom());
        descriptionField.setText(sc.getDescription());
        imagePathField.setText(sc.getImageUrl() != null ? sc.getImageUrl() : "");
        formContainer.setVisible(true);
    }

    private void loadCategoriesCombo() {
        try {
            categorieParenteCombo.getItems().clear();
            categorieParenteCombo.getItems().addAll(categorieService.afficher());
        } catch (SQLException e) {
            showAlert("Erreur", "Erreur chargement catégories: " + e.getMessage());
        }
    }

    @FXML
    public void handleSelectImage() {
        FileChooser fileChooser = new FileChooser();
        fileChooser.setTitle("Sélectionner une image");
        fileChooser.getExtensionFilters().add(
            new FileChooser.ExtensionFilter("Images", "*.png", "*.jpg", "*.jpeg")
        );
        
        File file = fileChooser.showOpenDialog(imagePathField.getScene().getWindow());
        if (file != null) {
            selectedImageFile = file;
            imagePathField.setText(file.getName());
        }
    }

    @FXML
    public void handleSave() {
        nomError.setVisible(false);
        
        String nom = nomField.getText().trim();
        if (nom.isEmpty()) {
            nomError.setText("⚠ Le nom est obligatoire");
            nomError.setVisible(true);
            return;
        }

        try {
            String desc = descriptionField.getText().trim();
            String imageUrl = selectedImageFile != null ? saveImage(selectedImageFile) : 
                             (isEditMode ? imagePathField.getText() : null);

            if (isSousCategorie) {
                if (isEditMode && selectedSousCategorie != null) {
                    selectedSousCategorie.setNom(nom);
                    selectedSousCategorie.setDescription(desc);
                    selectedSousCategorie.setImageUrl(imageUrl);
                    sousCategorieService.modifier(selectedSousCategorie);
                    showAlert("Succès", "Sous-catégorie modifiée");
                } else {
                    SousCategorie sc = new SousCategorie(selectedCategorie.getId(), nom);
                    sc.setDescription(desc);
                    sc.setImageUrl(imageUrl);
                    sousCategorieService.ajouter(sc);
                    showAlert("Succès", "Sous-catégorie ajoutée");
                }
            } else {
                if (isEditMode && selectedCategorie != null) {
                    selectedCategorie.setNom(nom);
                    selectedCategorie.setDescription(desc);
                    selectedCategorie.setImageUrl(imageUrl);
                    categorieService.modifier(selectedCategorie);
                    showAlert("Succès", "Catégorie modifiée");
                } else {
                    Categorie c = new Categorie(nom, desc);
                    c.setImageUrl(imageUrl);
                    categorieService.ajouter(c);
                    showAlert("Succès", "Catégorie ajoutée");
                }
            }

            handleCancel();
            loadCategories();

        } catch (SQLException e) {
            showAlert("Erreur", "Erreur: " + e.getMessage());
        }
    }

    private String saveImage(File imageFile) {
        try {
            Path imagesDir = Paths.get("images/categories");
            if (!Files.exists(imagesDir)) {
                Files.createDirectories(imagesDir);
            }
            
            String fileName = System.currentTimeMillis() + "_" + imageFile.getName();
            Path targetPath = imagesDir.resolve(fileName);
            Files.copy(imageFile.toPath(), targetPath, StandardCopyOption.REPLACE_EXISTING);
            
            return "images/categories/" + fileName;
        } catch (IOException e) {
            System.out.println("❌ Erreur sauvegarde image: " + e.getMessage());
            return null;
        }
    }

    private void handleDeleteCategorie(Categorie c) {
        Alert alert = new Alert(Alert.AlertType.CONFIRMATION);
        alert.setTitle("Confirmation");
        alert.setContentText("Supprimer la catégorie " + c.getNom() + " ?");
        
        alert.showAndWait().ifPresent(r -> {
            if (r == ButtonType.OK) {
                try {
                    categorieService.supprimer(c.getId());
                    loadCategories();
                    showAlert("Succès", "Catégorie supprimée");
                } catch (SQLException e) {
                    showAlert("Erreur", e.getMessage());
                }
            }
        });
    }

    private void handleDeleteSousCategorie(SousCategorie sc) {
        Alert alert = new Alert(Alert.AlertType.CONFIRMATION);
        alert.setTitle("Confirmation");
        alert.setContentText("Supprimer la sous-catégorie " + sc.getNom() + " ?");
        
        alert.showAndWait().ifPresent(r -> {
            if (r == ButtonType.OK) {
                try {
                    sousCategorieService.supprimer(sc.getId());
                    loadCategories();
                    showAlert("Succès", "Sous-catégorie supprimée");
                } catch (SQLException e) {
                    showAlert("Erreur", e.getMessage());
                }
            }
        });
    }

    @FXML
    public void handleCancel() {
        clearForm();
        formContainer.setVisible(false);
    }

    private void clearForm() {
        nomField.clear();
        descriptionField.clear();
        imagePathField.clear();
        selectedImageFile = null;
        nomError.setVisible(false);
    }

    private void showAlert(String title, String message) {
        Alert a = new Alert(Alert.AlertType.INFORMATION);
        a.setTitle(title);
        a.setContentText(message);
        a.show();
    }
}
