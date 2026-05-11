package controller;

import com.pi.entities.Paiement;
import com.pi.entity.PaiementService;
import javafx.collections.FXCollections;
import javafx.collections.ObservableList;
import javafx.fxml.FXML;
import javafx.geometry.Pos;
import javafx.scene.control.*;
import javafx.scene.control.cell.PropertyValueFactory;
import javafx.scene.layout.*;
import java.sql.SQLException;
import java.util.List;

public class GestionPaiementsController {

    @FXML private TableView<Paiement> paiementsTable;
    @FXML private TableColumn<Paiement, Integer> colId;
    @FXML private TableColumn<Paiement, Integer> colCommandeId;
    @FXML private TableColumn<Paiement, String> colMethode;
    @FXML private TableColumn<Paiement, Double> colMontant;
    @FXML private TableColumn<Paiement, String> colStatut;
    @FXML private TableColumn<Paiement, Void> colActions;
    
    @FXML private ComboBox<String> filtreStatut;
    @FXML private Label totalPaiementsLabel;
    @FXML private Label montantTotalLabel;
    @FXML private Label enAttenteLabel;
    @FXML private Label validesLabel;

    private PaiementService paiementService = new PaiementService();
    private ObservableList<Paiement> paiementsList = FXCollections.observableArrayList();

    @FXML
    public void initialize() {
        setupTable();
        setupFilters();
        loadPaiements();
        updateStatistiques();
    }

    private void setupTable() {
        colId.setCellValueFactory(new PropertyValueFactory<>("id"));
        colCommandeId.setCellValueFactory(new PropertyValueFactory<>("demandeId"));
        colMethode.setCellValueFactory(new PropertyValueFactory<>("methodePaiement"));
        colMontant.setCellValueFactory(new PropertyValueFactory<>("montant"));
        colStatut.setCellValueFactory(new PropertyValueFactory<>("statut"));
        
        // Formater la colonne montant
        colMontant.setCellFactory(col -> new TableCell<Paiement, Double>() {
            @Override
            protected void updateItem(Double montant, boolean empty) {
                super.updateItem(montant, empty);
                if (empty || montant == null) {
                    setText(null);
                } else {
                    setText(String.format("%.3f TND", montant));
                }
            }
        });
        
        // Colorer la colonne statut
        colStatut.setCellFactory(col -> new TableCell<Paiement, String>() {
            @Override
            protected void updateItem(String statut, boolean empty) {
                super.updateItem(statut, empty);
                if (empty || statut == null) {
                    setText(null);
                    setStyle("");
                } else {
                    setText(statut);
                    switch (statut) {
                        case "COMPLETE":
                            setStyle("-fx-text-fill: #27ae60; -fx-font-weight: bold;");
                            break;
                        case "EN_COURS":
                            setStyle("-fx-text-fill: #f39c12; -fx-font-weight: bold;");
                            break;
                        case "ECHOUE":
                            setStyle("-fx-text-fill: #e74c3c; -fx-font-weight: bold;");
                            break;
                        default:
                            setStyle("");
                    }
                }
            }
        });
        
        // Colonne actions
        colActions.setCellFactory(col -> new TableCell<Paiement, Void>() {
            private final Button btnValider = new Button("✓");
            private final Button btnRefuser = new Button("✗");
            private final Button btnDetails = new Button("👁");
            private final HBox buttons = new HBox(5, btnValider, btnRefuser, btnDetails);
            
            {
                btnValider.setStyle("-fx-background-color: #27ae60; -fx-text-fill: white; -fx-cursor: hand;");
                btnRefuser.setStyle("-fx-background-color: #e74c3c; -fx-text-fill: white; -fx-cursor: hand;");
                btnDetails.setStyle("-fx-background-color: #3498db; -fx-text-fill: white; -fx-cursor: hand;");
                
                btnValider.setOnAction(e -> {
                    Paiement paiement = getTableView().getItems().get(getIndex());
                    validerPaiement(paiement);
                });
                
                btnRefuser.setOnAction(e -> {
                    Paiement paiement = getTableView().getItems().get(getIndex());
                    refuserPaiement(paiement);
                });
                
                btnDetails.setOnAction(e -> {
                    Paiement paiement = getTableView().getItems().get(getIndex());
                    afficherDetails(paiement);
                });
                
                buttons.setAlignment(Pos.CENTER);
            }
            
            @Override
            protected void updateItem(Void item, boolean empty) {
                super.updateItem(item, empty);
                if (empty) {
                    setGraphic(null);
                } else {
                    Paiement paiement = getTableView().getItems().get(getIndex());
                    if ("EN_COURS".equals(paiement.getStatut())) {
                        btnValider.setVisible(true);
                        btnRefuser.setVisible(true);
                    } else {
                        btnValider.setVisible(false);
                        btnRefuser.setVisible(false);
                    }
                    setGraphic(buttons);
                }
            }
        });
        
        paiementsTable.setItems(paiementsList);
    }

    private void setupFilters() {
        filtreStatut.getItems().addAll("TOUS", "EN_COURS", "COMPLETE", "ECHOUE");
        filtreStatut.setValue("TOUS");
        
        filtreStatut.setOnAction(e -> loadPaiements());
    }

    private void loadPaiements() {
        try {
            paiementsList.clear();
            
            // Récupérer tous les paiements via le service
            List<Paiement> allPaiements = paiementService.afficher();
            
            // Filtrer selon le statut sélectionné
            String filtre = filtreStatut.getValue();
            if ("TOUS".equals(filtre)) {
                paiementsList.addAll(allPaiements);
            } else {
                for (Paiement p : allPaiements) {
                    if (filtre.equals(p.getStatut())) {
                        paiementsList.add(p);
                    }
                }
            }
            
            updateStatistiques();
            
        } catch (Exception e) {
            showAlert("Erreur", "Impossible de charger les paiements: " + e.getMessage());
            e.printStackTrace();
        }
    }

    private void validerPaiement(Paiement paiement) {
        Alert confirm = new Alert(Alert.AlertType.CONFIRMATION);
        confirm.setTitle("Confirmer le paiement");
        confirm.setHeaderText("Valider le paiement #" + paiement.getId());
        confirm.setContentText("Montant: " + String.format("%.3f TND", paiement.getMontant()) + 
                              "\nDemande: #" + paiement.getDemandeId());
        
        confirm.showAndWait().ifPresent(response -> {
            if (response == ButtonType.OK) {
                try {
                    // Mettre à jour le statut du paiement
                    paiement.setStatut("COMPLETE");
                    paiementService.modifier(paiement);
                    
                    showAlert("Succès", "Paiement validé avec succès!");
                    loadPaiements();
                    
                } catch (SQLException e) {
                    showAlert("Erreur", "Erreur lors de la validation: " + e.getMessage());
                }
            }
        });
    }

    private void refuserPaiement(Paiement paiement) {
        Alert confirm = new Alert(Alert.AlertType.CONFIRMATION);
        confirm.setTitle("Refuser le paiement");
        confirm.setHeaderText("Refuser le paiement #" + paiement.getId());
        confirm.setContentText("Êtes-vous sûr de vouloir refuser ce paiement?");
        
        confirm.showAndWait().ifPresent(response -> {
            if (response == ButtonType.OK) {
                try {
                    // Mettre à jour le statut du paiement
                    paiement.setStatut("ECHOUE");
                    paiementService.modifier(paiement);
                    
                    showAlert("Succès", "Paiement refusé");
                    loadPaiements();
                    
                } catch (SQLException e) {
                    showAlert("Erreur", "Erreur lors du refus: " + e.getMessage());
                }
            }
        });
    }

    private void afficherDetails(Paiement paiement) {
        Alert details = new Alert(Alert.AlertType.INFORMATION);
        details.setTitle("Détails du Paiement");
        details.setHeaderText("Paiement #" + paiement.getId());
        
        java.time.format.DateTimeFormatter formatter = java.time.format.DateTimeFormatter.ofPattern("dd/MM/yyyy HH:mm");
        String dateStr = paiement.getDatePaiement() != null ? 
            paiement.getDatePaiement().format(formatter) : "N/A";
        
        String content = "Demande: #" + paiement.getDemandeId() + "\n" +
                       "Client ID: " + paiement.getClientId() + "\n" +
                       "Activité ID: " + paiement.getActiviteId() + "\n" +
                       "Méthode: " + paiement.getMethodePaiement() + "\n" +
                       "Montant: " + String.format("%.3f TND", paiement.getMontant()) + "\n" +
                       "Statut: " + paiement.getStatut() + "\n" +
                       "Transaction ID: " + paiement.getReferenceTransaction() + "\n" +
                       "Date: " + dateStr;
        
        details.setContentText(content);
        details.show();
    }

    private void updateStatistiques() {
        try {
            List<Paiement> all = paiementService.afficher();
            
            int total = all.size();
            int enCours = 0;
            int completes = 0;
            double montantTotal = 0.0;
            
            for (Paiement p : all) {
                if ("EN_COURS".equals(p.getStatut())) {
                    enCours++;
                } else if ("COMPLETE".equals(p.getStatut())) {
                    completes++;
                    montantTotal += p.getMontant();
                }
            }
            
            totalPaiementsLabel.setText(String.valueOf(total));
            enAttenteLabel.setText(String.valueOf(enCours));
            validesLabel.setText(String.valueOf(completes));
            montantTotalLabel.setText(String.format("%.3f TND", montantTotal));
            
        } catch (SQLException e) {
            System.err.println("Erreur calcul statistiques: " + e.getMessage());
        }
    }

    @FXML
    public void handleRefresh() {
        loadPaiements();
    }

    private void showAlert(String title, String message) {
        Alert alert = new Alert(Alert.AlertType.INFORMATION);
        alert.setTitle(title);
        alert.setHeaderText(null);
        alert.setContentText(message);
        alert.show();
    }
}
