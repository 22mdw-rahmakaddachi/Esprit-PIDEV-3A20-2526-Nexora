package controller;

import com.pi.entities.commande;
import com.pi.entities.CommandeItem;
import com.pi.entities.user;
import com.pi.entities.commande;
import com.pi.entity.CommandeService;
import com.pi.entity.CommandeItemService;
import javafx.collections.FXCollections;
import javafx.collections.ObservableList;
import javafx.fxml.FXML;
import javafx.scene.control.*;
import javafx.scene.control.cell.PropertyValueFactory;
import javafx.scene.layout.VBox;

import java.sql.SQLException;
import java.util.List;

public class MesCommandesController {

    @FXML
    private TableView<commande> commandesTable;
    @FXML
    private TableColumn<commande, Integer> idColumn;
    @FXML
    private TableColumn<commande, java.util.Date> dateColumn;
    @FXML
    private TableColumn<commande, Double> totalColumn;
    @FXML
    private TableColumn<commande, String> statutColumn;
    @FXML
    private TableColumn<commande, Void> detailsColumn;

    @FXML
    private VBox detailsContainer;
    @FXML
    private TableView<CommandeItem> detailsTable;
    @FXML
    private TableColumn<CommandeItem, String> produitColumn;
    @FXML
    private TableColumn<CommandeItem, Integer> quantiteColumn;
    @FXML
    private TableColumn<CommandeItem, Double> prixColumn;
    @FXML
    private TableColumn<CommandeItem, Double> sousTotalColumn;
    @FXML
    private Label totalCommandeLabel;
    @FXML
    private Button closeDetailsButton;

    private ObservableList<commande> commandesList = FXCollections.observableArrayList();
    private CommandeService commandeService = new CommandeService();
    private CommandeItemService commandeItemService = new CommandeItemService();

    private user currentUser;
    private commande selectedCommande;

    public void setUser(user user) {
        this.currentUser = user;
        System.out.println("✅ Utilisateur défini dans MesCommandes: " + user.getPrenom() + " " + user.getName());
        loadCommandes();
    }

    @FXML
    public void initialize() {
        System.out.println("🔧 Initialisation de MesCommandesController");
        // Configuration des colonnes de la table des commandes
        idColumn.setCellValueFactory(new PropertyValueFactory<>("id"));
        dateColumn.setCellValueFactory(new PropertyValueFactory<>("dateCommande"));
        totalColumn.setCellValueFactory(new PropertyValueFactory<>("total"));
        statutColumn.setCellValueFactory(new PropertyValueFactory<>("statut"));

        // Formatage de la date
        dateColumn.setCellFactory(column -> new TableCell<commande, java.util.Date>() {
            @Override
            protected void updateItem(java.util.Date item, boolean empty) {
                super.updateItem(item, empty);
                if (empty || item == null) {
                    setText(null);
                } else {
                    // Format simple de la date
                    java.text.SimpleDateFormat sdf = new java.text.SimpleDateFormat("dd/MM/yyyy HH:mm");
                    setText(sdf.format(item));
                }
            }
        });

        // Formatage du total
        totalColumn.setCellFactory(column -> new TableCell<commande, Double>() {
            @Override
            protected void updateItem(Double item, boolean empty) {
                super.updateItem(item, empty);
                if (empty || item == null) {
                    setText(null);
                } else {
                    setText(String.format("%.3f TND", item));
                }
            }
        });

        // Configuration de la colonne des détails
        setupDetailsColumn();

        // Configuration de la table des détails
        produitColumn.setCellValueFactory(new PropertyValueFactory<>("produitNom"));
        quantiteColumn.setCellValueFactory(new PropertyValueFactory<>("quantite"));
        prixColumn.setCellValueFactory(new PropertyValueFactory<>("prixUnitaire"));
        sousTotalColumn.setCellValueFactory(new PropertyValueFactory<>("sousTotal"));

        // Formatage des prix dans les détails
        prixColumn.setCellFactory(column -> new TableCell<CommandeItem, Double>() {
            @Override
            protected void updateItem(Double item, boolean empty) {
                super.updateItem(item, empty);
                if (empty || item == null) {
                    setText(null);
                } else {
                    setText(String.format("%.3f TND", item));
                }
            }
        });

        sousTotalColumn.setCellFactory(column -> new TableCell<CommandeItem, Double>() {
            @Override
            protected void updateItem(Double item, boolean empty) {
                super.updateItem(item, empty);
                if (empty || item == null) {
                    setText(null);
                } else {
                    setText(String.format("%.3f TND", item));
                }
            }
        });

        // Cacher le conteneur des détails au démarrage
        detailsContainer.setVisible(false);
    }

    private void setupDetailsColumn() {
        detailsColumn.setCellFactory(param -> new TableCell<>() {
            private final Button detailsButton = new Button("Voir détails");

            {
                detailsButton.setStyle("-fx-background-color: #3498db; -fx-text-fill: white;");

                detailsButton.setOnAction(event -> {
                    commande commande = getTableView().getItems().get(getIndex());
                    showDetails(commande);
                });
            }

            @Override
            protected void updateItem(Void item, boolean empty) {
                super.updateItem(item, empty);
                setGraphic(empty ? null : detailsButton);
            }
        });
    }

    private void loadCommandes() {
        try {
            if (currentUser == null) {
                System.out.println("⚠️ Utilisateur non défini, impossible de charger les commandes");
                return;
            }
            
            commandesList.clear();
            // Récupérer les commandes du client connecté par son nom
            String clientNom = currentUser.getPrenom() + " " + currentUser.getName();
            System.out.println("🔍 Recherche des commandes pour: " + clientNom);
            
            // DEBUG: Afficher toutes les commandes de la base
            try {
                List<commande> toutesCommandes = commandeService.afficher();
                System.out.println("📊 DEBUG - Toutes les commandes dans la base:");
                for (commande c : toutesCommandes) {
                    System.out.println("  - Commande #" + c.getId() + " | Client: '" + c.getClientNom() + "' | Total: " + c.getTotal() + " TND");
                }
            } catch (Exception e) {
                System.err.println("Erreur lors de l'affichage debug: " + e.getMessage());
            }
            
            List<commande> commandes = commandeService.getByClientNom(clientNom);
            System.out.println("✅ " + commandes.size() + " commande(s) trouvée(s)");
            
            commandesList.addAll(commandes);
            commandesTable.setItems(commandesList);
            
            if (commandes.isEmpty()) {
                System.out.println("ℹ️ Aucune commande trouvée pour ce client");
            }
        } catch (SQLException e) {
            System.err.println("❌ Erreur lors du chargement des commandes: " + e.getMessage());
            e.printStackTrace();
            showAlert("Erreur", "Impossible de charger les commandes: " + e.getMessage());
        }
    }

    private void showDetails(commande commande) {
        try {
            this.selectedCommande = commande;

            // Charger les détails de la commande
            List<CommandeItem> details = commandeItemService.getByCommandeId(commande.getId());
            ObservableList<CommandeItem> detailsList = FXCollections.observableArrayList(details);
            detailsTable.setItems(detailsList);

            // Afficher le total
            totalCommandeLabel.setText("Total: " + String.format("%.3f", commande.getTotal()) + " TND");

            // Afficher le conteneur des détails
            detailsContainer.setVisible(true);

        } catch (SQLException e) {
            showAlert("Erreur", "Impossible de charger les détails: " + e.getMessage());
        }
    }

    @FXML
    public void handleCloseDetails() {
        detailsContainer.setVisible(false);
        selectedCommande = null;
    }

    @FXML
    public void handleRefresh() {
        loadCommandes();
        handleCloseDetails();
        showAlert("Succès", "Liste des commandes actualisée");
    }

    private void showAlert(String title, String message) {
        Alert alert = new Alert(Alert.AlertType.INFORMATION);
        alert.setTitle(title);
        alert.setHeaderText(null);
        alert.setContentText(message);
        alert.show();
    }
}