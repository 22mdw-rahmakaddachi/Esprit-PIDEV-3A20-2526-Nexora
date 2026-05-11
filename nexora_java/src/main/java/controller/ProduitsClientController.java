package controller;

import com.pi.entities.Product;
import com.pi.entities.user;
import com.pi.entity.PanierService;
import com.pi.entity.ProductService;
import javafx.collections.FXCollections;
import javafx.collections.ObservableList;
import javafx.fxml.FXML;
import javafx.scene.control.*;
import javafx.scene.control.cell.PropertyValueFactory;
import javafx.scene.layout.HBox;
import java.sql.SQLException;

public class ProduitsClientController {

    @FXML private TableView<Product> produitsTable;
    @FXML private TableColumn<Product, String> nomColumn;
    @FXML private TableColumn<Product, String> descriptionColumn;
    @FXML private TableColumn<Product, Double> prixColumn;
    @FXML private TableColumn<Product, Integer> stockColumn;
    @FXML private TableColumn<Product, Void> actionColumn;
    @FXML private TextField searchField;  // ← AJOUTÉ

    private ObservableList<Product> produitsList = FXCollections.observableArrayList();
    private ProductService productService = new ProductService();
    private PanierService panierService = new PanierService();

    private user currentUser;

    public void setUser(user user) {
        this.currentUser = user;
        loadProduits();
    }

    @FXML
    public void initialize() {
        nomColumn.setCellValueFactory(new PropertyValueFactory<>("nom"));
        descriptionColumn.setCellValueFactory(new PropertyValueFactory<>("description"));
        prixColumn.setCellValueFactory(new PropertyValueFactory<>("prix"));
        stockColumn.setCellValueFactory(new PropertyValueFactory<>("quantite"));

        setupActionColumn();
    }

    private void setupActionColumn() {
        actionColumn.setCellFactory(param -> new TableCell<>() {
            private final Button addButton = new Button("Ajouter au panier");
            private final Spinner<Integer> quantitySpinner = new Spinner<>();
            private final HBox box = new HBox(5, quantitySpinner, addButton);

            {
                addButton.setStyle("-fx-background-color: #27ae60; -fx-text-fill: white;");
                quantitySpinner.setPrefWidth(60);

                addButton.setOnAction(event -> {
                    Product product = getTableView().getItems().get(getIndex());
                    int quantity = quantitySpinner.getValue();
                    handleAddToCart(product, quantity);
                });
            }

            @Override
            protected void updateItem(Void item, boolean empty) {
                super.updateItem(item, empty);
                if (empty) {
                    setGraphic(null);
                } else {
                    Product product = getTableView().getItems().get(getIndex());
                    if (product.getQuantite() <= 0) {
                        setGraphic(new Label("Rupture de stock"));
                    } else {
                        quantitySpinner.setValueFactory(new SpinnerValueFactory.IntegerSpinnerValueFactory(1, product.getQuantite(), 1));
                        setGraphic(box);
                    }
                }
            }
        });
    }

    private void loadProduits() {
        try {
            produitsList.clear();
            produitsList.addAll(productService.afficher().stream()
                    .filter(p -> p.getQuantite() > 0 && "actif".equals(p.getStatut()))
                    .toList());
            produitsTable.setItems(produitsList);
        } catch (SQLException e) {
            showAlert("Erreur", e.getMessage());
        }
    }

    private void handleAddToCart(Product product, int quantity) {
        try {
            panierService.ajouterAuPanier(currentUser.getId(), product.getId(), quantity);
            showAlert("Succès", quantity + " x " + product.getNom() + " ajouté(s) au panier");
        } catch (SQLException e) {
            showAlert("Erreur", e.getMessage());
        }
    }

    // ========== MÉTHODES AJOUTÉES ==========

    @FXML
    public void handleSearch() {
        String searchText = searchField.getText().trim().toLowerCase();

        if (searchText.isEmpty()) {
            loadProduits();
            return;
        }

        try {
            ObservableList<Product> filteredList = FXCollections.observableArrayList();

            for (Product p : productService.afficher()) {
                if (p.getQuantite() > 0 && "actif".equals(p.getStatut())) {
                    if (p.getNom().toLowerCase().contains(searchText) ||
                            p.getDescription().toLowerCase().contains(searchText)) {
                        filteredList.add(p);
                    }
                }
            }

            produitsList.clear();
            produitsList.addAll(filteredList);
            produitsTable.setItems(produitsList);

            if (filteredList.isEmpty()) {
                showAlert("Recherche", "Aucun produit trouvé pour \"" + searchText + "\"");
            }

        } catch (SQLException e) {
            showAlert("Erreur", e.getMessage());
        }
    }

    @FXML
    public void handleRefresh() {
        loadProduits();
        if (searchField != null) {
            searchField.clear();
        }
        showAlert("Succès", "Liste des produits actualisée");
    }

    @FXML
    public void handleClearSearch() {
        if (searchField != null) {
            searchField.clear();
        }
        loadProduits();
    }

    private void showAlert(String title, String message) {
        Alert alert = new Alert(Alert.AlertType.INFORMATION);
        alert.setTitle(title);
        alert.setHeaderText(null);
        alert.setContentText(message);
        alert.show();
    }
}