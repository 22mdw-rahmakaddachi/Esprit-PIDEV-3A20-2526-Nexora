package controller;

import com.pi.entities.CodePromo;
import com.pi.entities.user;
import com.pi.entity.CodePromoService;
import com.pi.utils.AlertUtils;
import javafx.collections.FXCollections;
import javafx.collections.ObservableList;
import javafx.fxml.FXML;
import javafx.scene.control.*;
import javafx.scene.control.cell.PropertyValueFactory;
import javafx.scene.layout.HBox;
import javafx.util.Callback;

import java.sql.SQLException;
import java.time.LocalDate;
import java.time.ZoneId;
import java.util.Date;
import java.util.List;

public class GestionCodesPromoController {

    @FXML private TextField codeField;
    @FXML private TextArea descriptionArea;
    @FXML private ComboBox<String> typeReductionCombo;
    @FXML private TextField valeurReductionField;
    @FXML private TextField montantMinimumField;
    @FXML private DatePicker dateDebutPicker;
    @FXML private DatePicker dateFinPicker;
    @FXML private TextField limiteUtilisationField;
    @FXML private CheckBox premiereCommandeCheck;
    @FXML private CheckBox actifCheck;
    
    @FXML private TableView<CodePromo> codesTable;
    @FXML private TableColumn<CodePromo, String> codeCol;
    @FXML private TableColumn<CodePromo, String> descriptionCol;
    @FXML private TableColumn<CodePromo, String> typeCol;
    @FXML private TableColumn<CodePromo, Double> valeurCol;
    @FXML private TableColumn<CodePromo, Integer> utilisationsCol;
    @FXML private TableColumn<CodePromo, Boolean> actifCol;
    @FXML private TableColumn<CodePromo, Void> actionsCol;

    private CodePromoService codePromoService = new CodePromoService();
    private user currentUser;
    private CodePromo codeEnEdition = null;

    @FXML
    public void initialize() {
        setupTable();
        setupComboBox();
        actifCheck.setSelected(true);
        chargerCodes();
    }

    public void setUser(user user) {
        this.currentUser = user;
        chargerCodes();
    }

    private void setupComboBox() {
        typeReductionCombo.setItems(FXCollections.observableArrayList(
            "POURCENTAGE",
            "MONTANT_FIXE",
            "LIVRAISON_GRATUITE"
        ));
        typeReductionCombo.setValue("POURCENTAGE");
    }

    private void setupTable() {
        codeCol.setCellValueFactory(new PropertyValueFactory<>("code"));
        descriptionCol.setCellValueFactory(new PropertyValueFactory<>("description"));
        typeCol.setCellValueFactory(new PropertyValueFactory<>("typeReduction"));
        valeurCol.setCellValueFactory(new PropertyValueFactory<>("valeurReduction"));
        utilisationsCol.setCellValueFactory(new PropertyValueFactory<>("nombreUtilisations"));
        actifCol.setCellValueFactory(new PropertyValueFactory<>("actif"));

        // Colonne actions
        actionsCol.setCellFactory(new Callback<TableColumn<CodePromo, Void>, TableCell<CodePromo, Void>>() {
            @Override
            public TableCell<CodePromo, Void> call(TableColumn<CodePromo, Void> param) {
                return new TableCell<CodePromo, Void>() {
                    private final Button editBtn = new Button("✏️");
                    private final Button toggleBtn = new Button("🔄");
                    private final Button deleteBtn = new Button("🗑️");
                    private final HBox pane = new HBox(5, editBtn, toggleBtn, deleteBtn);

                    {
                        editBtn.setOnAction(e -> {
                            CodePromo code = getTableView().getItems().get(getIndex());
                            editerCode(code);
                        });

                        toggleBtn.setOnAction(e -> {
                            CodePromo code = getTableView().getItems().get(getIndex());
                            toggleActif(code);
                        });

                        deleteBtn.setOnAction(e -> {
                            CodePromo code = getTableView().getItems().get(getIndex());
                            supprimerCode(code);
                        });

                        editBtn.setStyle("-fx-background-color: #3498db; -fx-text-fill: white;");
                        toggleBtn.setStyle("-fx-background-color: #f39c12; -fx-text-fill: white;");
                        deleteBtn.setStyle("-fx-background-color: #e74c3c; -fx-text-fill: white;");
                    }

                    @Override
                    protected void updateItem(Void item, boolean empty) {
                        super.updateItem(item, empty);
                        setGraphic(empty ? null : pane);
                    }
                };
            }
        });
    }

    private void chargerCodes() {
        try {
            List<CodePromo> codes;
            if (currentUser != null) {
                codes = codePromoService.getByPartenaire(currentUser.getId());
            } else {
                codes = codePromoService.getAll();
            }
            
            ObservableList<CodePromo> observableList = FXCollections.observableArrayList(codes);
            codesTable.setItems(observableList);
            
            System.out.println("✅ " + codes.size() + " codes promo chargés");
        } catch (SQLException e) {
            AlertUtils.showError("Erreur", "Impossible de charger les codes promo: " + e.getMessage());
            e.printStackTrace();
        }
    }

    @FXML
    private void handleCreer() {
        try {
            // Validation
            if (codeField.getText().trim().isEmpty()) {
                AlertUtils.showWarning("Attention", "Le code est obligatoire");
                return;
            }

            if (valeurReductionField.getText().trim().isEmpty()) {
                AlertUtils.showWarning("Attention", "La valeur de réduction est obligatoire");
                return;
            }

            // Les dates sont optionnelles pour un code illimité
            // if (dateDebutPicker.getValue() == null || dateFinPicker.getValue() == null) {
            //     AlertUtils.showWarning("Attention", "Les dates sont obligatoires");
            //     return;
            // }

            CodePromo code = new CodePromo();
            code.setCode(codeField.getText().trim().toUpperCase());
            code.setDescription(descriptionArea.getText().trim());
            code.setTypeReduction(CodePromo.TypeReduction.valueOf(typeReductionCombo.getValue()));
            code.setValeurReduction(Double.parseDouble(valeurReductionField.getText()));
            code.setMontantMinimum(montantMinimumField.getText().isEmpty() ? 0 : 
                Double.parseDouble(montantMinimumField.getText()));
            
            // Dates optionnelles (NULL = illimité)
            if (dateDebutPicker.getValue() != null) {
                code.setDateDebut(Date.from(dateDebutPicker.getValue().atStartOfDay(ZoneId.systemDefault()).toInstant()));
            }
            if (dateFinPicker.getValue() != null) {
                code.setDateFin(Date.from(dateFinPicker.getValue().atStartOfDay(ZoneId.systemDefault()).toInstant()));
            }
            
            if (!limiteUtilisationField.getText().isEmpty()) {
                code.setLimiteUtilisation(Integer.parseInt(limiteUtilisationField.getText()));
            }
            
            code.setPremiereCommandeSeulement(premiereCommandeCheck.isSelected());
            code.setActif(actifCheck.isSelected());
            
            if (currentUser != null) {
                code.setPartenaireId(currentUser.getId());
            }

            if (codeEnEdition != null) {
                code.setId(codeEnEdition.getId());
                codePromoService.modifier(code);
                AlertUtils.showSuccess("Succès", "Code promo modifié avec succès");
            } else {
                codePromoService.creer(code);
                AlertUtils.showSuccess("Succès", "Code promo créé avec succès");
            }

            clearForm();
            chargerCodes();

        } catch (NumberFormatException e) {
            AlertUtils.showError("Erreur", "Valeurs numériques invalides");
        } catch (SQLException e) {
            AlertUtils.showError("Erreur", "Erreur lors de la création: " + e.getMessage());
            e.printStackTrace();
        }
    }

    @FXML
    private void handleAnnuler() {
        clearForm();
    }

    private void editerCode(CodePromo code) {
        codeEnEdition = code;
        
        codeField.setText(code.getCode());
        codeField.setDisable(true); // Ne pas modifier le code
        descriptionArea.setText(code.getDescription());
        typeReductionCombo.setValue(code.getTypeReduction().name());
        valeurReductionField.setText(String.valueOf(code.getValeurReduction()));
        montantMinimumField.setText(String.valueOf(code.getMontantMinimum()));
        
        // Gérer les dates NULL (illimité)
        if (code.getDateDebut() != null) {
            dateDebutPicker.setValue(code.getDateDebut().toInstant().atZone(ZoneId.systemDefault()).toLocalDate());
        } else {
            dateDebutPicker.setValue(null);
        }
        
        if (code.getDateFin() != null) {
            dateFinPicker.setValue(code.getDateFin().toInstant().atZone(ZoneId.systemDefault()).toLocalDate());
        } else {
            dateFinPicker.setValue(null);
        }
        
        if (code.getLimiteUtilisation() != null) {
            limiteUtilisationField.setText(String.valueOf(code.getLimiteUtilisation()));
        } else {
            limiteUtilisationField.clear();
        }
        
        premiereCommandeCheck.setSelected(code.isPremiereCommandeSeulement());
        actifCheck.setSelected(code.isActif());
    }

    private void toggleActif(CodePromo code) {
        try {
            codePromoService.toggleActif(code.getId());
            AlertUtils.showSuccess("Succès", "Statut modifié");
            chargerCodes();
        } catch (SQLException e) {
            AlertUtils.showError("Erreur", "Impossible de modifier le statut: " + e.getMessage());
        }
    }

    private void supprimerCode(CodePromo code) {
        Alert confirm = new Alert(Alert.AlertType.CONFIRMATION);
        confirm.setTitle("Confirmation");
        confirm.setHeaderText("Supprimer le code promo?");
        confirm.setContentText("Code: " + code.getCode());
        
        confirm.showAndWait().ifPresent(response -> {
            if (response == ButtonType.OK) {
                try {
                    codePromoService.supprimer(code.getId());
                    AlertUtils.showSuccess("Succès", "Code promo supprimé");
                    chargerCodes();
                } catch (SQLException e) {
                    AlertUtils.showError("Erreur", "Impossible de supprimer: " + e.getMessage());
                }
            }
        });
    }

    private void clearForm() {
        codeEnEdition = null;
        codeField.clear();
        codeField.setDisable(false);
        descriptionArea.clear();
        typeReductionCombo.setValue("POURCENTAGE");
        valeurReductionField.clear();
        montantMinimumField.clear();
        dateDebutPicker.setValue(LocalDate.now());
        dateFinPicker.setValue(LocalDate.now().plusMonths(1));
        limiteUtilisationField.clear();
        premiereCommandeCheck.setSelected(false);
        actifCheck.setSelected(true);
    }
}
