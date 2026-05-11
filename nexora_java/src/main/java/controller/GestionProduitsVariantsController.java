package controller;

import com.pi.dto.*;
import com.pi.entities.*;
import com.pi.entity.*;
import javafx.fxml.FXML;
import javafx.geometry.Insets;
import javafx.geometry.Pos;
import javafx.scene.control.*;
import javafx.scene.image.Image;
import javafx.scene.image.ImageView;
import javafx.scene.layout.*;
import javafx.stage.FileChooser;
import java.io.File;
import java.io.IOException;
import java.nio.file.Files;
import java.nio.file.Path;
import java.nio.file.Paths;
import java.nio.file.StandardCopyOption;
import java.sql.SQLException;
import java.util.*;

public class GestionProduitsVariantsController {

    @FXML private FlowPane produitsContainer;
    @FXML private VBox formContainer;
    @FXML private Label formTitle;
    @FXML private TextField nomField;
    @FXML private Label nomError;
    @FXML private ComboBox<Categorie> categorieCombo;
    @FXML private ComboBox<SousCategorie> sousCategorieCombo;
    @FXML private Label categorieError;
    @FXML private TextField descriptionCourteField;
    @FXML private TextArea descriptionField;
    @FXML private TextField marqueField;
    @FXML private TextField materiauField;
    @FXML private TextField poidsField;
    @FXML private TextField dimensionsField;
    @FXML private TextField imageField;
    @FXML private ImageView imagePreview;
    @FXML private VBox variantsContainer;

    private CatalogueService catalogueService = new CatalogueService();
    private CategorieService categorieService = new CategorieService();
    private SousCategorieService sousCategorieService = new SousCategorieService();
    private AttributVariationService attributService = new AttributVariationService();
    private OptionVariationService optionService = new OptionVariationService();
    private ProduitParentService produitParentService = new ProduitParentService();
    
    private int partenaireId = 1;
    private ProduitParent selectedProduit;
    private File selectedImageFile = null;
    private List<VariantFormData> variantsForms = new ArrayList<>();

    public void setPartenaireId(int id) {
        this.partenaireId = id;
        System.out.println("✅ GestionProduitsVariants - partenaireId: " + id);
        
        // En mode admin (partenaireId = -1), désactiver les boutons d'ajout
        if (id == -1) {
            System.out.println("👑 Mode ADMIN activé - Consultation seule");
            // Les boutons d'ajout seront désactivés dans l'interface
        }
        
        loadProduits();
    }

    @FXML
    public void initialize() {
        loadCategories();
        loadProduits();
    }

    private void loadCategories() {
        try {
            categorieCombo.getItems().clear();
            categorieCombo.getItems().addAll(categorieService.afficher());
        } catch (SQLException e) {
            showAlert("Erreur", "Erreur chargement catégories: " + e.getMessage());
        }
    }

    @FXML
    public void handleCategorieChange() {
        Categorie selected = categorieCombo.getValue();
        if (selected != null) {
            try {
                sousCategorieCombo.getItems().clear();
                sousCategorieCombo.getItems().addAll(sousCategorieService.getByCategorie(selected.getId()));
            } catch (SQLException e) {
                showAlert("Erreur", "Erreur chargement sous-catégories: " + e.getMessage());
            }
        }
    }

    private void loadProduits() {
        try {
            produitsContainer.getChildren().clear();
            
            List<ProduitCompletDTO> produits;
            if (partenaireId == -1) {
                // Mode admin : récupérer TOUS les produits de TOUS les partenaires
                produits = catalogueService.getTousProduitsForAdmin();
                System.out.println("👑 Mode ADMIN : Chargement de tous les produits");
            } else {
                // Mode partenaire normal : récupérer seulement les produits du partenaire
                produits = catalogueService.getProduitsCompletsByPartenaire(partenaireId);
                System.out.println("🏢 Mode PARTENAIRE : Chargement des produits du partenaire #" + partenaireId);
            }
            
            for (ProduitCompletDTO dto : produits) {
                produitsContainer.getChildren().add(createProduitCard(dto));
            }
            
            System.out.println("✅ " + produits.size() + " produits chargés");
        } catch (SQLException e) {
            showAlert("Erreur", "Erreur chargement: " + e.getMessage());
        }
    }

    private VBox createProduitCard(ProduitCompletDTO dto) {
        VBox card = new VBox(10);
        card.setStyle("-fx-background-color: white; -fx-background-radius: 8; -fx-padding: 15; -fx-effect: dropshadow(gaussian, rgba(0,0,0,0.1), 10, 0, 0, 2);");
        card.setPrefWidth(280);

        // Image
        ImageView imageView = new ImageView();
        imageView.setFitWidth(250);
        imageView.setFitHeight(200);
        imageView.setPreserveRatio(true);
        
        if (dto.getProduitParent().getImagePrincipale() != null) {
            try {
                File imgFile = new File(dto.getProduitParent().getImagePrincipale());
                if (imgFile.exists()) {
                    imageView.setImage(new Image(imgFile.toURI().toString()));
                }
            } catch (Exception e) {}
        }

        // Nom
        Label nomLabel = new Label(dto.getProduitParent().getNom());
        nomLabel.setStyle("-fx-font-size: 16px; -fx-font-weight: bold; -fx-text-fill: #1F2937;");
        nomLabel.setWrapText(true);

        // Catégorie
        Label catLabel = new Label(dto.getCategorieNom() + " > " + dto.getSousCategorieNom());
        catLabel.setStyle("-fx-font-size: 12px; -fx-text-fill: #6B7280;");

        // Partenaire (seulement en mode admin)
        Label partenaireLabel = null;
        if (partenaireId == -1) {
            // Mode admin : afficher le partenaire
            partenaireLabel = new Label("👤 Partenaire #" + dto.getProduitParent().getPartenaireId());
            partenaireLabel.setStyle("-fx-background-color: #FEF3C7; -fx-text-fill: #92400E; -fx-padding: 3 6; -fx-background-radius: 3; -fx-font-size: 10px;");
        }

        // Prix et stock
        HBox infoBox = new HBox(10);
        infoBox.setAlignment(Pos.CENTER_LEFT);
        
        Label prixLabel = new Label(dto.getPrixAffichage());
        prixLabel.setStyle("-fx-font-size: 16px; -fx-font-weight: bold; -fx-text-fill: #2980b9;");
        
        Label stockLabel = new Label("Stock: " + dto.getStockTotal());
        stockLabel.setStyle("-fx-font-size: 12px; -fx-text-fill: #6B7280;");
        
        infoBox.getChildren().addAll(prixLabel, new Region(), stockLabel);
        HBox.setHgrow(infoBox.getChildren().get(1), Priority.ALWAYS);

        // Variants
        Label variantsLabel = new Label(dto.getVariants().size() + " variant(s)");
        variantsLabel.setStyle("-fx-background-color: #E0E7FF; -fx-text-fill: #3730A3; -fx-padding: 4 8; -fx-background-radius: 4; -fx-font-size: 11px;");

        // Boutons
        HBox buttonsBox = new HBox(8);
        buttonsBox.setAlignment(Pos.CENTER);
        
        Button editBtn = new Button("✏️ Modifier");
        editBtn.setStyle("-fx-background-color: #3498db; -fx-text-fill: white; -fx-padding: 6 12; -fx-cursor: hand;");
        editBtn.setOnAction(e -> handleEdit(dto));
        
        Button deleteBtn = new Button("🗑️");
        deleteBtn.setStyle("-fx-background-color: #e74c3c; -fx-text-fill: white; -fx-padding: 6 12; -fx-cursor: hand;");
        deleteBtn.setOnAction(e -> handleDelete(dto));
        
        // En mode admin, désactiver les boutons d'édition
        if (partenaireId == -1) {
            editBtn.setDisable(true);
            editBtn.setStyle("-fx-background-color: #9CA3AF; -fx-text-fill: white; -fx-padding: 6 12; -fx-opacity: 0.6;");
            deleteBtn.setDisable(true);
            deleteBtn.setStyle("-fx-background-color: #9CA3AF; -fx-text-fill: white; -fx-padding: 6 12; -fx-opacity: 0.6;");
        }
        
        buttonsBox.getChildren().addAll(editBtn, deleteBtn);

        // Ajouter les éléments à la carte
        if (partenaireLabel != null) {
            card.getChildren().addAll(imageView, nomLabel, catLabel, partenaireLabel, infoBox, variantsLabel, buttonsBox);
        } else {
            card.getChildren().addAll(imageView, nomLabel, catLabel, infoBox, variantsLabel, buttonsBox);
        }
        
        return card;
    }

    @FXML
    public void handleNouveauProduit() {
        // Vérifier si on est en mode admin
        if (partenaireId == -1) {
            showAlert("Mode Admin", "En mode administrateur, vous pouvez seulement consulter les produits.\nVous ne pouvez pas en créer de nouveaux.");
            return;
        }
        
        selectedProduit = null;
        formTitle.setText("Nouveau Produit");
        clearForm();
        formContainer.setVisible(true);
    }

    @FXML
    public void handleAjouterVariant() {
        try {
            List<AttributAvecOptionsDTO> attributs = catalogueService.getAttributsAvecOptions();
            if (attributs.isEmpty()) {
                showAlert("Info", "Veuillez d'abord créer des attributs (Taille, Couleur, etc.)");
                return;
            }

            VBox variantBox = createVariantForm(attributs);
            variantsContainer.getChildren().add(variantBox);
            
        } catch (SQLException e) {
            showAlert("Erreur", "Erreur: " + e.getMessage());
        }
    }

    private VBox createVariantForm(List<AttributAvecOptionsDTO> attributs) {
        VBox variantBox = new VBox(10);
        variantBox.setStyle("-fx-background-color: #F9FAFB; -fx-padding: 15; -fx-background-radius: 6; -fx-border-color: #E5E7EB; -fx-border-radius: 6;");

        VariantFormData formData = new VariantFormData();
        variantsForms.add(formData);

        // En-tête
        HBox header = new HBox(10);
        header.setAlignment(Pos.CENTER_LEFT);
        Label title = new Label("Variant #" + (variantsContainer.getChildren().size() + 1));
        title.setStyle("-fx-font-weight: bold; -fx-font-size: 14px;");
        Region spacer = new Region();
        HBox.setHgrow(spacer, Priority.ALWAYS);
        Button removeBtn = new Button("🗑️");
        removeBtn.setStyle("-fx-background-color: #e74c3c; -fx-text-fill: white; -fx-padding: 4 8; -fx-cursor: hand;");
        removeBtn.setOnAction(e -> {
            variantsContainer.getChildren().remove(variantBox);
            variantsForms.remove(formData);
        });
        header.getChildren().addAll(title, spacer, removeBtn);

        // SKU
        HBox skuBox = new HBox(10);
        Label skuLabel = new Label("SKU * (Code unique)");
        skuLabel.setPrefWidth(120);
        TextField skuField = new TextField();
        skuField.setPromptText("Ex: TENTE-CAMP-001");
        HBox.setHgrow(skuField, Priority.ALWAYS);
        formData.skuField = skuField;
        skuBox.getChildren().addAll(skuLabel, skuField);

        // Prix
        HBox prixBox = new HBox(10);
        VBox prixAchatBox = new VBox(3);
        Label prixAchatLabel = new Label("Prix Achat (optionnel)");
        TextField prixAchatField = new TextField();
        prixAchatField.setPromptText("Ex: 80,00 ou 80.00");
        formData.prixAchatField = prixAchatField;
        prixAchatBox.getChildren().addAll(prixAchatLabel, prixAchatField);
        
        VBox prixVenteBox = new VBox(3);
        Label prixVenteLabel = new Label("Prix Vente * (obligatoire)");
        TextField prixVenteField = new TextField();
        prixVenteField.setPromptText("Ex: 150,00 ou 150.00");
        formData.prixVenteField = prixVenteField;
        prixVenteBox.getChildren().addAll(prixVenteLabel, prixVenteField);
        
        VBox prixPromoBox = new VBox(3);
        Label prixPromoLabel = new Label("Prix Promo (optionnel)");
        TextField prixPromoField = new TextField();
        prixPromoField.setPromptText("Ex: 120,00 ou 120.00");
        formData.prixPromoField = prixPromoField;
        prixPromoBox.getChildren().addAll(prixPromoLabel, prixPromoField);
        
        prixBox.getChildren().addAll(prixAchatBox, prixVenteBox, prixPromoBox);
        HBox.setHgrow(prixAchatBox, Priority.ALWAYS);
        HBox.setHgrow(prixVenteBox, Priority.ALWAYS);
        HBox.setHgrow(prixPromoBox, Priority.ALWAYS);

        // Stock
        HBox stockBox = new HBox(10);
        VBox qteBox = new VBox(3);
        Label qteLabel = new Label("Stock * (quantité)");
        TextField qteField = new TextField();
        qteField.setPromptText("Ex: 10");
        formData.stockField = qteField;
        qteBox.getChildren().addAll(qteLabel, qteField);
        
        VBox seuilBox = new VBox(3);
        Label seuilLabel = new Label("Seuil Alerte (min stock)");
        TextField seuilField = new TextField();
        seuilField.setText("2");
        seuilField.setPromptText("Ex: 2");
        formData.seuilField = seuilField;
        seuilBox.getChildren().addAll(seuilLabel, seuilField);
        
        stockBox.getChildren().addAll(qteBox, seuilBox);
        HBox.setHgrow(qteBox, Priority.ALWAYS);
        HBox.setHgrow(seuilBox, Priority.ALWAYS);

        // Attributs
        VBox attributsBox = new VBox(8);
        Label attributsLabel = new Label("Options du variant");
        attributsLabel.setStyle("-fx-font-weight: bold; -fx-font-size: 12px;");
        attributsBox.getChildren().add(attributsLabel);

        for (AttributAvecOptionsDTO attr : attributs) {
            HBox attrRow = new HBox(10);
            attrRow.setAlignment(Pos.CENTER_LEFT);
            Label attrLabel = new Label(attr.getAttribut().getNom() + ":");
            attrLabel.setPrefWidth(80);
            ComboBox<OptionVariation> optionCombo = new ComboBox<>();
            optionCombo.getItems().addAll(attr.getOptions());
            optionCombo.setPrefWidth(200);
            formData.optionsCombo.put(attr.getAttribut().getId(), optionCombo);
            attrRow.getChildren().addAll(attrLabel, optionCombo);
            attributsBox.getChildren().add(attrRow);
        }

        variantBox.getChildren().addAll(header, skuBox, prixBox, stockBox, attributsBox);
        return variantBox;
    }

    @FXML
    public void handleSelectImage() {
        FileChooser fileChooser = new FileChooser();
        fileChooser.setTitle("Sélectionner une image");
        fileChooser.getExtensionFilters().add(
            new FileChooser.ExtensionFilter("Images", "*.png", "*.jpg", "*.jpeg")
        );
        
        File file = fileChooser.showOpenDialog(imageField.getScene().getWindow());
        if (file != null) {
            selectedImageFile = file;
            imageField.setText(file.getName());
            try {
                imagePreview.setImage(new Image(file.toURI().toString()));
                imagePreview.setVisible(true);
            } catch (Exception e) {}
        }
    }

    @FXML
    public void handleSave() {
        clearErrors();
        
        // Validation
        if (nomField.getText().trim().isEmpty()) {
            nomError.setText("⚠ Le nom est obligatoire");
            nomError.setVisible(true);
            return;
        }
        
        if (sousCategorieCombo.getValue() == null) {
            categorieError.setText("⚠ La catégorie est obligatoire");
            categorieError.setVisible(true);
            return;
        }

        if (variantsForms.isEmpty()) {
            showAlert("Erreur", "Ajoutez au moins un variant");
            return;
        }

        try {
            ProduitParent parent;
            
            // Mode édition ou création
            if (produitEnEdition != null) {
                // Modification d'un produit existant
                parent = produitEnEdition;
                parent.setNom(nomField.getText().trim());
                parent.setSousCategorieId(sousCategorieCombo.getValue().getId());
            } else {
                // Création d'un nouveau produit
                parent = new ProduitParent(partenaireId, sousCategorieCombo.getValue().getId(), nomField.getText().trim());
            }
            
            // Mettre à jour les champs communs
            parent.setDescription(descriptionField.getText().trim());
            parent.setDescriptionCourte(descriptionCourteField.getText().trim());
            parent.setMarque(marqueField.getText().trim());
            parent.setMateriau(materiauField.getText().trim());
            
            if (!poidsField.getText().trim().isEmpty()) {
                parent.setPoidsKg(parseDouble(poidsField.getText().trim()));
            }
            parent.setDimensionsCm(dimensionsField.getText().trim());
            
            if (selectedImageFile != null) {
                parent.setImagePrincipale(saveImage(selectedImageFile));
            }

            // Créer les variants
            List<ProduitVariant> variants = new ArrayList<>();
            List<List<Integer>> variantOptions = new ArrayList<>();

            for (VariantFormData formData : variantsForms) {
                ProduitVariant variant = new ProduitVariant();
                variant.setSku(formData.skuField.getText().trim());
                variant.setPrixVente(parseDouble(formData.prixVenteField.getText().trim()));
                
                if (!formData.prixAchatField.getText().trim().isEmpty()) {
                    variant.setPrixAchat(parseDouble(formData.prixAchatField.getText().trim()));
                }
                if (!formData.prixPromoField.getText().trim().isEmpty()) {
                    variant.setPrixPromo(parseDouble(formData.prixPromoField.getText().trim()));
                }
                
                variant.setQuantiteStock(parseInt(formData.stockField.getText().trim()));
                variant.setSeuilAlerte(parseInt(formData.seuilField.getText().trim()));

                variants.add(variant);

                // Options du variant
                List<Integer> options = new ArrayList<>();
                for (ComboBox<OptionVariation> combo : formData.optionsCombo.values()) {
                    if (combo.getValue() != null) {
                        options.add(combo.getValue().getId());
                    }
                }
                variantOptions.add(options);
            }

            // Enregistrer tout
            if (produitEnEdition != null) {
                // Mise à jour du produit existant
                produitParentService.modifier(parent);
                showAlert("Succès", "Produit modifié avec succès");
                produitEnEdition = null; // Réinitialiser
            } else {
                // Création d'un nouveau produit
                catalogueService.creerProduitAvecVariants(parent, variants, variantOptions);
                showAlert("Succès", "Produit créé avec " + variants.size() + " variant(s)");
            }
            
            handleFermerForm();
            loadProduits();

        } catch (NumberFormatException e) {
            showAlert("Erreur", "Vérifiez les valeurs numériques");
        } catch (SQLException e) {
            showAlert("Erreur", "Erreur: " + e.getMessage());
        }
    }

    private String saveImage(File imageFile) {
        try {
            Path imagesDir = Paths.get("images/products");
            if (!Files.exists(imagesDir)) {
                Files.createDirectories(imagesDir);
            }
            
            String fileName = System.currentTimeMillis() + "_" + imageFile.getName();
            Path targetPath = imagesDir.resolve(fileName);
            Files.copy(imageFile.toPath(), targetPath, StandardCopyOption.REPLACE_EXISTING);
            
            return "images/products/" + fileName;
        } catch (IOException e) {
            System.out.println("❌ Erreur sauvegarde image: " + e.getMessage());
            return null;
        }
    }

    private void handleEdit(ProduitCompletDTO dto) {
        // Vérifier si on est en mode admin
        if (partenaireId == -1) {
            showAlert("Mode Admin", "En mode administrateur, vous pouvez seulement consulter les produits.\nVous ne pouvez pas les modifier.");
            return;
        }
        
        try {
            // Charger le produit parent complet
            ProduitParent produit = produitParentService.getById(dto.getProduitParent().getId());
            if (produit == null) {
                showAlert("Erreur", "Produit introuvable");
                return;
            }

            // Afficher le formulaire
            formContainer.setVisible(true);
            formTitle.setText("✏️ Modifier le Produit");
            
            // Charger les catégories si nécessaire
            if (categorieCombo.getItems().isEmpty()) {
                loadCategories();
            }

            // Pré-remplir le formulaire
            nomField.setText(produit.getNom());
            descriptionField.setText(produit.getDescription());
            descriptionCourteField.setText(produit.getDescriptionCourte());
            marqueField.setText(produit.getMarque());
            materiauField.setText(produit.getMateriau());
            
            if (produit.getPoidsKg() > 0) {
                poidsField.setText(String.valueOf(produit.getPoidsKg()));
            }
            if (produit.getDimensionsCm() != null) {
                dimensionsField.setText(produit.getDimensionsCm());
            }

            // Charger la sous-catégorie et sélectionner la catégorie parente
            SousCategorie sousCategorie = sousCategorieService.getById(produit.getSousCategorieId());
            if (sousCategorie != null) {
                // Sélectionner la catégorie
                for (Categorie cat : categorieCombo.getItems()) {
                    if (cat.getId() == sousCategorie.getCategorieId()) {
                        categorieCombo.setValue(cat);
                        // Charger les sous-catégories de cette catégorie
                        sousCategorieCombo.getItems().clear();
                        sousCategorieCombo.getItems().addAll(sousCategorieService.getByCategorie(cat.getId()));
                        // Sélectionner la sous-catégorie
                        for (SousCategorie sc : sousCategorieCombo.getItems()) {
                            if (sc.getId() == produit.getSousCategorieId()) {
                                sousCategorieCombo.setValue(sc);
                                break;
                            }
                        }
                        break;
                    }
                }
            }

            // Stocker l'ID pour la modification
            produitEnEdition = produit;

        } catch (SQLException e) {
            showAlert("Erreur", "Impossible de charger le produit: " + e.getMessage());
            e.printStackTrace();
        }
    }

    private ProduitParent produitEnEdition = null;

    private void handleDelete(ProduitCompletDTO dto) {
        // Vérifier si on est en mode admin
        if (partenaireId == -1) {
            showAlert("Mode Admin", "En mode administrateur, vous pouvez seulement consulter les produits.\nVous ne pouvez pas les supprimer.");
            return;
        }
        
        Alert alert = new Alert(Alert.AlertType.CONFIRMATION);
        alert.setTitle("Confirmation");
        alert.setContentText("Supprimer " + dto.getProduitParent().getNom() + " et tous ses variants ?");
        
        alert.showAndWait().ifPresent(r -> {
            if (r == ButtonType.OK) {
                try {
                    new ProduitParentService().supprimer(dto.getProduitParent().getId(), partenaireId);
                    loadProduits();
                    showAlert("Succès", "Produit supprimé");
                } catch (SQLException e) {
                    showAlert("Erreur", e.getMessage());
                }
            }
        });
    }

    @FXML
    public void handleFermerForm() {
        formContainer.setVisible(false);
        clearForm();
    }

    private void clearForm() {
        nomField.clear();
        descriptionField.clear();
        descriptionCourteField.clear();
        marqueField.clear();
        materiauField.clear();
        poidsField.clear();
        dimensionsField.clear();
        imageField.clear();
        imagePreview.setVisible(false);
        categorieCombo.setValue(null);
        sousCategorieCombo.getItems().clear();
        variantsContainer.getChildren().clear();
        variantsForms.clear();
        selectedImageFile = null;
        produitEnEdition = null; // Réinitialiser le mode édition
        clearErrors();
        selectedImageFile = null;
        clearErrors();
    }

    private void clearErrors() {
        nomError.setVisible(false);
        categorieError.setVisible(false);
    }

    private void showAlert(String title, String message) {
        Alert a = new Alert(Alert.AlertType.INFORMATION);
        a.setTitle(title);
        a.setContentText(message);
        a.show();
    }

    // Classe interne pour stocker les données du formulaire variant
    private static class VariantFormData {
        TextField skuField;
        TextField prixAchatField;
        TextField prixVenteField;
        TextField prixPromoField;
        TextField stockField;
        TextField seuilField;
        Map<Integer, ComboBox<OptionVariation>> optionsCombo = new HashMap<>();
    }

    // Méthode utilitaire pour parser les nombres avec virgules ou points
    private double parseDouble(String value) {
        if (value == null || value.trim().isEmpty()) {
            return 0.0;
        }
        // Remplacer les virgules par des points pour la conversion
        return Double.parseDouble(value.trim().replace(",", "."));
    }

    private int parseInt(String value) {
        if (value == null || value.trim().isEmpty()) {
            return 0;
        }
        // Enlever les décimales si présentes
        String cleanValue = value.trim().replace(",", ".").split("\\.")[0];
        return Integer.parseInt(cleanValue);
    }
}
