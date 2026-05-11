package controller;

import com.pi.dto.*;
import com.pi.entities.*;
import com.pi.entity.*;
import com.pi.utils.SessionManager;
import javafx.fxml.FXML;
import javafx.geometry.Insets;
import javafx.geometry.Pos;
import javafx.scene.control.*;
import javafx.scene.image.Image;
import javafx.scene.image.ImageView;
import javafx.scene.layout.*;
import java.io.File;
import java.sql.SQLException;
import java.util.*;
import java.util.stream.Collectors;

public class CatalogueClientVariantsController {

    @FXML private TextField searchField;
    @FXML private ComboBox<Categorie> categorieFilter;
    @FXML private ComboBox<SousCategorie> sousCategorieFilter;
    @FXML private Label countLabel;
    @FXML private FlowPane produitsContainer;

    private CatalogueService catalogueService = new CatalogueService();
    private CategorieService categorieService = new CategorieService();
    private SousCategorieService sousCategorieService = new SousCategorieService();
    private PanierService panierService = new PanierService();
    
    private List<ProduitCompletDTO> allProduits = new ArrayList<>();
    private com.pi.entities.user currentUser;

    @FXML
    public void initialize() {
        currentUser = SessionManager.getCurrentUser();
        loadFilters();
        loadProduits();
    }

    private void loadFilters() {
        try {
            categorieFilter.getItems().clear();
            categorieFilter.getItems().addAll(categorieService.afficher());
            
            categorieFilter.setOnAction(e -> {
                Categorie selected = categorieFilter.getValue();
                if (selected != null) {
                    try {
                        sousCategorieFilter.getItems().clear();
                        sousCategorieFilter.getItems().addAll(sousCategorieService.getByCategorie(selected.getId()));
                    } catch (SQLException ex) {}
                }
            });
        } catch (SQLException e) {
            showAlert("Erreur", "Erreur chargement filtres: " + e.getMessage());
        }
    }

    private void loadProduits() {
        try {
            produitsContainer.getChildren().clear();
            
            // Charger tous les produits actifs
            allProduits.clear();
            List<ProduitParent> parents = new ProduitParentService().afficher();
            
            for (ProduitParent parent : parents) {
                if ("ACTIF".equals(parent.getStatut())) {
                    ProduitCompletDTO dto = catalogueService.getProduitComplet(parent.getId());
                    if (dto != null && dto.isEnStock()) {
                        allProduits.add(dto);
                    }
                }
            }
            
            displayProduits(allProduits);
            
        } catch (SQLException e) {
            showAlert("Erreur", "Erreur chargement: " + e.getMessage());
        }
    }

    private void displayProduits(List<ProduitCompletDTO> produits) {
        produitsContainer.getChildren().clear();
        
        for (ProduitCompletDTO dto : produits) {
            produitsContainer.getChildren().add(createProduitCard(dto));
        }
        
        countLabel.setText(produits.size() + " produit(s) trouvé(s)");
    }

    private VBox createProduitCard(ProduitCompletDTO dto) {
        VBox card = new VBox(12);
        card.setStyle("-fx-background-color: white; -fx-background-radius: 10; -fx-padding: 15; -fx-effect: dropshadow(gaussian, rgba(0,0,0,0.1), 10, 0, 0, 2);");
        card.setPrefWidth(300);

        // Container pour l'image avec badge PROMO
        StackPane imageContainer = new StackPane();
        
        // Image
        ImageView imageView = new ImageView();
        imageView.setFitWidth(270);
        imageView.setFitHeight(220);
        imageView.setPreserveRatio(true);
        imageView.setStyle("-fx-background-color: #F3F4F6; -fx-background-radius: 8;");
        
        if (dto.getProduitParent().getImagePrincipale() != null) {
            try {
                File imgFile = new File(dto.getProduitParent().getImagePrincipale());
                if (imgFile.exists()) {
                    imageView.setImage(new Image(imgFile.toURI().toString()));
                }
            } catch (Exception e) {}
        }
        
        imageContainer.getChildren().add(imageView);
        
        // Vérifier si au moins un variant a un prix promo
        boolean hasPromo = dto.getVariants().stream()
            .anyMatch(v -> v.getProduitVariant().getPrixPromo() > 0);
        
        if (hasPromo) {
            Label promoBadge = new Label("🎁 PROMO");
            promoBadge.setStyle("-fx-background-color: #e74c3c; -fx-text-fill: white; -fx-font-weight: bold; " +
                              "-fx-font-size: 12px; -fx-padding: 5 10; -fx-background-radius: 5;");
            StackPane.setAlignment(promoBadge, javafx.geometry.Pos.TOP_RIGHT);
            StackPane.setMargin(promoBadge, new Insets(5, 5, 0, 0));
            imageContainer.getChildren().add(promoBadge);
        }

        // Nom
        Label nomLabel = new Label(dto.getProduitParent().getNom());
        nomLabel.setStyle("-fx-font-size: 16px; -fx-font-weight: bold; -fx-text-fill: #1F2937;");
        nomLabel.setWrapText(true);
        nomLabel.setMaxWidth(270);

        // Description courte
        String desc = dto.getProduitParent().getDescriptionCourte();
        if (desc != null && !desc.isEmpty()) {
            Label descLabel = new Label(desc.length() > 80 ? desc.substring(0, 80) + "..." : desc);
            descLabel.setStyle("-fx-font-size: 13px; -fx-text-fill: #6B7280;");
            descLabel.setWrapText(true);
            descLabel.setMaxWidth(270);
            card.getChildren().add(descLabel);
        }

        // Prix (avec affichage promo si applicable)
        VBox prixBox = new VBox(3);
        
        // Calculer le prix min et vérifier si promo
        double prixMin = dto.getVariants().stream()
            .mapToDouble(v -> v.getProduitVariant().getPrixVente())
            .min().orElse(0);
        double prixPromoMin = dto.getVariants().stream()
            .filter(v -> v.getProduitVariant().getPrixPromo() > 0)
            .mapToDouble(v -> v.getProduitVariant().getPrixPromo())
            .min().orElse(0);
        
        if (prixPromoMin > 0) {
            // Afficher prix barré + prix promo
            Label prixNormalLabel = new Label(String.format("%.3f TND", prixMin));
            prixNormalLabel.setStyle("-fx-font-size: 14px; -fx-text-fill: #95a5a6; -fx-strikethrough: true;");
            
            Label prixPromoLabel = new Label(String.format("%.3f TND", prixPromoMin));
            prixPromoLabel.setStyle("-fx-font-size: 20px; -fx-font-weight: bold; -fx-text-fill: #e74c3c;");
            
            // Calculer le pourcentage de réduction
            double reduction = ((prixMin - prixPromoMin) / prixMin) * 100;
            Label reductionLabel = new Label(String.format("-%.0f%%", reduction));
            reductionLabel.setStyle("-fx-font-size: 12px; -fx-font-weight: bold; -fx-text-fill: #27ae60; " +
                                  "-fx-background-color: #d5f4e6; -fx-padding: 2 6; -fx-background-radius: 3;");
            
            HBox prixPromoBox = new HBox(8);
            prixPromoBox.setAlignment(Pos.CENTER_LEFT);
            prixPromoBox.getChildren().addAll(prixPromoLabel, reductionLabel);
            
            prixBox.getChildren().addAll(prixNormalLabel, prixPromoBox);
        } else {
            // Afficher prix normal
            Label prixLabel = new Label(dto.getPrixAffichage());
            prixLabel.setStyle("-fx-font-size: 20px; -fx-font-weight: bold; -fx-text-fill: #2980b9;");
            prixBox.getChildren().add(prixLabel);
        }

        // Stock
        Label stockLabel = new Label("Stock disponible: " + dto.getStockTotal());
        stockLabel.setStyle("-fx-font-size: 12px; -fx-text-fill: " + (dto.getStockTotal() > 10 ? "#27ae60" : "#e67e22") + ";");

        // Sélection de variant
        VBox variantSelection = new VBox(8);
        variantSelection.setStyle("-fx-background-color: #F9FAFB; -fx-padding: 10; -fx-background-radius: 6;");
        
        Label variantLabel = new Label("Choisir les options:");
        variantLabel.setStyle("-fx-font-size: 12px; -fx-font-weight: bold;");
        variantSelection.getChildren().add(variantLabel);

        // Grouper les options par attribut
        Map<String, Set<String>> attributsOptions = new HashMap<>();
        Map<String, Integer> attributIds = new HashMap<>();
        
        for (VariantCompletDTO variant : dto.getVariants()) {
            for (OptionVariation option : variant.getOptions()) {
                try {
                    AttributVariation attr = new AttributVariationService().getById(option.getAttributId());
                    if (attr != null) {
                        attributsOptions.computeIfAbsent(attr.getNom(), k -> new LinkedHashSet<>()).add(option.getValeur());
                        attributIds.put(attr.getNom(), attr.getId());
                    }
                } catch (SQLException e) {}
            }
        }

        Map<String, ComboBox<String>> optionCombos = new HashMap<>();
        
        for (Map.Entry<String, Set<String>> entry : attributsOptions.entrySet()) {
            HBox attrBox = new HBox(8);
            attrBox.setAlignment(Pos.CENTER_LEFT);
            
            Label attrLabel = new Label(entry.getKey() + ":");
            attrLabel.setStyle("-fx-font-size: 12px; -fx-min-width: 70;");
            
            ComboBox<String> optionCombo = new ComboBox<>();
            optionCombo.getItems().addAll(entry.getValue());
            optionCombo.setPromptText("Choisir");
            optionCombo.setPrefWidth(150);
            optionCombos.put(entry.getKey(), optionCombo);
            
            attrBox.getChildren().addAll(attrLabel, optionCombo);
            variantSelection.getChildren().add(attrBox);
        }

        // Quantité et bouton
        HBox actionBox = new HBox(10);
        actionBox.setAlignment(Pos.CENTER);
        actionBox.setStyle("-fx-padding: 10 0 0 0;");
        
        Label qteLabel = new Label("Qté:");
        qteLabel.setStyle("-fx-font-weight: bold;");
        
        Spinner<Integer> qteSpinner = new Spinner<>(1, 99, 1);
        qteSpinner.setPrefWidth(80);
        qteSpinner.setEditable(true);
        
        Button addBtn = new Button("🛒 Ajouter au panier");
        addBtn.setStyle("-fx-background-color: #27ae60; -fx-text-fill: white; -fx-padding: 10 15; -fx-cursor: hand; -fx-font-weight: bold;");
        addBtn.setOnAction(e -> handleAddToCart(dto, optionCombos, qteSpinner.getValue()));
        
        actionBox.getChildren().addAll(qteLabel, qteSpinner, addBtn);

        card.getChildren().addAll(imageContainer, nomLabel, prixBox, stockLabel, variantSelection, actionBox);
        return card;
    }

    private void handleAddToCart(ProduitCompletDTO dto, Map<String, ComboBox<String>> optionCombos, int quantite) {
        if (currentUser == null) {
            showAlert("Erreur", "Veuillez vous connecter");
            return;
        }

        // Vérifier que toutes les options sont sélectionnées
        for (Map.Entry<String, ComboBox<String>> entry : optionCombos.entrySet()) {
            if (entry.getValue().getValue() == null) {
                showAlert("Erreur", "Veuillez sélectionner " + entry.getKey());
                return;
            }
        }

        // Trouver le variant correspondant
        VariantCompletDTO selectedVariant = null;
        for (VariantCompletDTO variant : dto.getVariants()) {
            boolean match = true;
            for (Map.Entry<String, ComboBox<String>> entry : optionCombos.entrySet()) {
                String selectedValue = entry.getValue().getValue();
                boolean hasOption = variant.getOptions().stream()
                    .anyMatch(opt -> opt.getValeur().equals(selectedValue));
                if (!hasOption) {
                    match = false;
                    break;
                }
            }
            if (match) {
                selectedVariant = variant;
                break;
            }
        }

        if (selectedVariant == null) {
            showAlert("Erreur", "Combinaison d'options non disponible");
            return;
        }

        if (selectedVariant.getProduitVariant().getQuantiteStock() < quantite) {
            showAlert("Erreur", "Stock insuffisant");
            return;
        }

        try {
            String produitNom = dto.getProduitParent().getNom() + " - " + selectedVariant.getOptionsAffichage();
            String sku = selectedVariant.getProduitVariant().getSku();
            double prix = selectedVariant.getProduitVariant().getPrixVente();
            
            System.out.println("🛒 Ajout au panier:");
            System.out.println("  Client ID: " + currentUser.getId());
            System.out.println("  SKU: " + sku);
            System.out.println("  Produit: " + produitNom);
            System.out.println("  Prix: " + prix + " TND");
            System.out.println("  Quantité: " + quantite);
            
            panierService.ajouterVariantAuPanier(
                currentUser.getId(),
                sku,
                produitNom,
                prix,
                quantite
            );
            
            showAlert("Succès", "✅ Produit ajouté au panier!\n\n" + produitNom + "\nQuantité: " + quantite);
            
        } catch (SQLException e) {
            System.err.println("❌ Erreur ajout panier: " + e.getMessage());
            e.printStackTrace();
            showAlert("Erreur", "Erreur lors de l'ajout au panier:\n" + e.getMessage());
        }
    }

    @FXML
    public void handleSearch() {
        String searchText = searchField.getText().trim().toLowerCase();
        if (searchText.isEmpty()) {
            displayProduits(allProduits);
            return;
        }

        List<ProduitCompletDTO> filtered = allProduits.stream()
            .filter(p -> p.getProduitParent().getNom().toLowerCase().contains(searchText) ||
                        (p.getProduitParent().getDescription() != null && 
                         p.getProduitParent().getDescription().toLowerCase().contains(searchText)))
            .collect(Collectors.toList());
        
        displayProduits(filtered);
    }

    @FXML
    public void handleFilterChange() {
        List<ProduitCompletDTO> filtered = new ArrayList<>(allProduits);

        SousCategorie selectedSousCat = sousCategorieFilter.getValue();
        if (selectedSousCat != null) {
            filtered = filtered.stream()
                .filter(p -> p.getProduitParent().getSousCategorieId() == selectedSousCat.getId())
                .collect(Collectors.toList());
        } else {
            Categorie selectedCat = categorieFilter.getValue();
            if (selectedCat != null) {
                try {
                    List<Integer> sousCatIds = sousCategorieService.getByCategorie(selectedCat.getId())
                        .stream().map(SousCategorie::getId).collect(Collectors.toList());
                    filtered = filtered.stream()
                        .filter(p -> sousCatIds.contains(p.getProduitParent().getSousCategorieId()))
                        .collect(Collectors.toList());
                } catch (SQLException e) {}
            }
        }

        displayProduits(filtered);
    }

    @FXML
    public void handleResetFilters() {
        searchField.clear();
        categorieFilter.setValue(null);
        sousCategorieFilter.getItems().clear();
        sousCategorieFilter.setValue(null);
        displayProduits(allProduits);
    }

    private void showAlert(String title, String message) {
        Alert a = new Alert(Alert.AlertType.INFORMATION);
        a.setTitle(title);
        a.setContentText(message);
        a.show();
    }
}
