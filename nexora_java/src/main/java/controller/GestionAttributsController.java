package controller;

import com.pi.dto.AttributAvecOptionsDTO;
import com.pi.entities.AttributVariation;
import com.pi.entities.OptionVariation;
import com.pi.entity.AttributVariationService;
import com.pi.entity.CatalogueService;
import com.pi.entity.OptionVariationService;
import javafx.fxml.FXML;
import javafx.geometry.Insets;
import javafx.geometry.Pos;
import javafx.scene.control.*;
import javafx.scene.layout.*;
import java.sql.SQLException;
import java.util.List;

public class GestionAttributsController {

    @FXML private VBox attributsContainer;
    @FXML private VBox formContainer;
    @FXML private Label formTitle;
    @FXML private TextField nomField;
    @FXML private Label nomError;
    @FXML private ComboBox<String> typeAffichageCombo;
    @FXML private VBox typeAffichageBox;
    @FXML private VBox attributParentBox;
    @FXML private ComboBox<AttributVariation> attributParentCombo;
    @FXML private VBox valeurBox;
    @FXML private TextField valeurField;
    @FXML private VBox couleurBox;
    @FXML private TextField couleurField;

    private AttributVariationService attributService = new AttributVariationService();
    private OptionVariationService optionService = new OptionVariationService();
    private CatalogueService catalogueService = new CatalogueService();
    
    private boolean isEditMode = false;
    private boolean isOption = false;
    private AttributVariation selectedAttribut;
    private OptionVariation selectedOption;

    @FXML
    public void initialize() {
        typeAffichageCombo.getItems().addAll("DROPDOWN", "COLOR_SWATCH", "RADIO", "TEXT");
        typeAffichageCombo.setValue("DROPDOWN");
        loadAttributs();
    }

    private void loadAttributs() {
        try {
            attributsContainer.getChildren().clear();
            List<AttributAvecOptionsDTO> attributs = catalogueService.getAttributsAvecOptions();
            
            for (AttributAvecOptionsDTO dto : attributs) {
                attributsContainer.getChildren().add(createAttributCard(dto));
            }
            
            System.out.println("✅ " + attributs.size() + " attributs chargés");
        } catch (SQLException e) {
            showAlert("Erreur", "Erreur chargement: " + e.getMessage());
        }
    }

    private VBox createAttributCard(AttributAvecOptionsDTO dto) {
        VBox card = new VBox(10);
        card.setStyle("-fx-background-color: #F9FAFB; -fx-background-radius: 6; -fx-padding: 15; -fx-border-color: #E5E7EB; -fx-border-radius: 6;");

        // En-tête attribut
        HBox header = new HBox(10);
        header.setAlignment(Pos.CENTER_LEFT);
        
        Label nomLabel = new Label("🎨 " + dto.getAttribut().getNom());
        nomLabel.setStyle("-fx-font-size: 16px; -fx-font-weight: bold; -fx-text-fill: #1F2937;");
        
        Label typeLabel = new Label(dto.getAttribut().getTypeAffichage());
        typeLabel.setStyle("-fx-background-color: #E0E7FF; -fx-text-fill: #3730A3; -fx-padding: 4 8; -fx-background-radius: 4; -fx-font-size: 11px;");
        
        Region spacer = new Region();
        HBox.setHgrow(spacer, Priority.ALWAYS);
        
        Button addOptionBtn = new Button("➕");
        addOptionBtn.setStyle("-fx-background-color: #3498db; -fx-text-fill: white; -fx-padding: 4 8; -fx-cursor: hand;");
        addOptionBtn.setTooltip(new Tooltip("Ajouter une option"));
        addOptionBtn.setOnAction(e -> handleAjouterOption(dto.getAttribut()));
        
        Button editBtn = new Button("✏️");
        editBtn.setStyle("-fx-background-color: #f39c12; -fx-text-fill: white; -fx-padding: 4 8; -fx-cursor: hand;");
        editBtn.setOnAction(e -> handleEditAttribut(dto.getAttribut()));
        
        Button deleteBtn = new Button("🗑️");
        deleteBtn.setStyle("-fx-background-color: #e74c3c; -fx-text-fill: white; -fx-padding: 4 8; -fx-cursor: hand;");
        deleteBtn.setOnAction(e -> handleDeleteAttribut(dto.getAttribut()));
        
        header.getChildren().addAll(nomLabel, typeLabel, spacer, addOptionBtn, editBtn, deleteBtn);

        // Options
        FlowPane optionsFlow = new FlowPane(8, 8);
        optionsFlow.setPadding(new Insets(10, 0, 0, 0));
        
        for (OptionVariation option : dto.getOptions()) {
            optionsFlow.getChildren().add(createOptionChip(option, dto.getAttribut().getTypeAffichage()));
        }

        card.getChildren().addAll(header, optionsFlow);
        return card;
    }

    private HBox createOptionChip(OptionVariation option, String typeAffichage) {
        HBox chip = new HBox(5);
        chip.setAlignment(Pos.CENTER_LEFT);
        chip.setStyle("-fx-background-color: white; -fx-background-radius: 12; -fx-padding: 6 12; -fx-border-color: #D1D5DB; -fx-border-radius: 12;");
        
        // Affichage selon le type
        if ("COLOR_SWATCH".equals(typeAffichage) && option.getCodeHexadecimal() != null) {
            Region colorBox = new Region();
            colorBox.setStyle("-fx-background-color: " + option.getCodeHexadecimal() + "; -fx-min-width: 16; -fx-min-height: 16; -fx-background-radius: 3; -fx-border-color: #D1D5DB; -fx-border-radius: 3;");
            chip.getChildren().add(colorBox);
        }
        
        Label label = new Label(option.getValeur());
        label.setStyle("-fx-font-size: 13px; -fx-text-fill: #374151;");
        
        Button editBtn = new Button("✏️");
        editBtn.setStyle("-fx-background-color: transparent; -fx-text-fill: #6B7280; -fx-padding: 0; -fx-cursor: hand; -fx-font-size: 11px;");
        editBtn.setOnAction(e -> handleEditOption(option));
        
        Button deleteBtn = new Button("✖");
        deleteBtn.setStyle("-fx-background-color: transparent; -fx-text-fill: #EF4444; -fx-padding: 0; -fx-cursor: hand; -fx-font-size: 11px;");
        deleteBtn.setOnAction(e -> handleDeleteOption(option));
        
        chip.getChildren().addAll(label, editBtn, deleteBtn);
        return chip;
    }

    @FXML
    public void handleAjouterAttribut() {
        isEditMode = false;
        isOption = false;
        selectedAttribut = null;
        formTitle.setText("Nouvel Attribut");
        typeAffichageBox.setVisible(true);
        typeAffichageBox.setManaged(true);
        attributParentBox.setVisible(false);
        attributParentBox.setManaged(false);
        valeurBox.setVisible(false);
        valeurBox.setManaged(false);
        couleurBox.setVisible(false);
        couleurBox.setManaged(false);
        clearForm();
        formContainer.setVisible(true);
    }

    private void handleAjouterOption(AttributVariation attribut) {
        isEditMode = false;
        isOption = true;
        selectedAttribut = attribut;
        formTitle.setText("Nouvelle Option pour " + attribut.getNom());
        typeAffichageBox.setVisible(false);
        typeAffichageBox.setManaged(false);
        attributParentBox.setVisible(true);
        attributParentBox.setManaged(true);
        attributParentCombo.setValue(attribut);
        attributParentCombo.setDisable(true);
        valeurBox.setVisible(true);
        valeurBox.setManaged(true);
        
        if ("COLOR_SWATCH".equals(attribut.getTypeAffichage())) {
            couleurBox.setVisible(true);
            couleurBox.setManaged(true);
        } else {
            couleurBox.setVisible(false);
            couleurBox.setManaged(false);
        }
        
        clearForm();
        formContainer.setVisible(true);
    }

    private void handleEditAttribut(AttributVariation a) {
        isEditMode = true;
        isOption = false;
        selectedAttribut = a;
        formTitle.setText("Modifier Attribut");
        typeAffichageBox.setVisible(true);
        typeAffichageBox.setManaged(true);
        attributParentBox.setVisible(false);
        attributParentBox.setManaged(false);
        valeurBox.setVisible(false);
        valeurBox.setManaged(false);
        couleurBox.setVisible(false);
        couleurBox.setManaged(false);
        nomField.setText(a.getNom());
        typeAffichageCombo.setValue(a.getTypeAffichage());
        formContainer.setVisible(true);
    }

    private void handleEditOption(OptionVariation o) {
        isEditMode = true;
        isOption = true;
        selectedOption = o;
        formTitle.setText("Modifier Option");
        typeAffichageBox.setVisible(false);
        typeAffichageBox.setManaged(false);
        attributParentBox.setVisible(true);
        attributParentBox.setManaged(true);
        loadAttributsCombo();
        valeurBox.setVisible(true);
        valeurBox.setManaged(true);
        valeurField.setText(o.getValeur());
        
        if (o.getCodeHexadecimal() != null) {
            couleurBox.setVisible(true);
            couleurBox.setManaged(true);
            couleurField.setText(o.getCodeHexadecimal());
        }
        
        formContainer.setVisible(true);
    }

    private void loadAttributsCombo() {
        try {
            attributParentCombo.getItems().clear();
            attributParentCombo.getItems().addAll(attributService.afficher());
        } catch (SQLException e) {
            showAlert("Erreur", "Erreur chargement attributs: " + e.getMessage());
        }
    }

    @FXML
    public void handleSave() {
        nomError.setVisible(false);
        
        String nom = nomField.getText().trim();
        if (nom.isEmpty() && !isOption) {
            nomError.setText("⚠ Le nom est obligatoire");
            nomError.setVisible(true);
            return;
        }

        try {
            if (isOption) {
                String valeur = valeurField.getText().trim();
                if (valeur.isEmpty()) {
                    nomError.setText("⚠ La valeur est obligatoire");
                    nomError.setVisible(true);
                    return;
                }

                if (isEditMode && selectedOption != null) {
                    selectedOption.setValeur(valeur);
                    selectedOption.setCodeHexadecimal(couleurField.getText().trim());
                    optionService.modifier(selectedOption);
                    showAlert("Succès", "Option modifiée");
                } else {
                    OptionVariation o = new OptionVariation(selectedAttribut.getId(), valeur);
                    o.setCodeHexadecimal(couleurField.getText().trim());
                    optionService.ajouter(o);
                    showAlert("Succès", "Option ajoutée");
                }
            } else {
                String typeAffichage = typeAffichageCombo.getValue();
                
                if (isEditMode && selectedAttribut != null) {
                    selectedAttribut.setNom(nom);
                    selectedAttribut.setTypeAffichage(typeAffichage);
                    attributService.modifier(selectedAttribut);
                    showAlert("Succès", "Attribut modifié");
                } else {
                    AttributVariation a = new AttributVariation(nom, typeAffichage);
                    attributService.ajouter(a);
                    showAlert("Succès", "Attribut ajouté");
                }
            }

            handleCancel();
            loadAttributs();

        } catch (SQLException e) {
            showAlert("Erreur", "Erreur: " + e.getMessage());
        }
    }

    private void handleDeleteAttribut(AttributVariation a) {
        Alert alert = new Alert(Alert.AlertType.CONFIRMATION);
        alert.setTitle("Confirmation");
        alert.setContentText("Supprimer l'attribut " + a.getNom() + " et toutes ses options ?");
        
        alert.showAndWait().ifPresent(r -> {
            if (r == ButtonType.OK) {
                try {
                    attributService.supprimer(a.getId());
                    loadAttributs();
                    showAlert("Succès", "Attribut supprimé");
                } catch (SQLException e) {
                    showAlert("Erreur", e.getMessage());
                }
            }
        });
    }

    private void handleDeleteOption(OptionVariation o) {
        Alert alert = new Alert(Alert.AlertType.CONFIRMATION);
        alert.setTitle("Confirmation");
        alert.setContentText("Supprimer l'option " + o.getValeur() + " ?");
        
        alert.showAndWait().ifPresent(r -> {
            if (r == ButtonType.OK) {
                try {
                    optionService.supprimer(o.getId());
                    loadAttributs();
                    showAlert("Succès", "Option supprimée");
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
        valeurField.clear();
        couleurField.clear();
        nomError.setVisible(false);
    }

    private void showAlert(String title, String message) {
        Alert a = new Alert(Alert.AlertType.INFORMATION);
        a.setTitle(title);
        a.setContentText(message);
        a.show();
    }
}
