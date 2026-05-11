package controller;

import com.pi.entities.Avis;
import com.pi.entities.Commentaire;
import com.pi.entities.user;
import com.pi.entity.AvisService;
import com.pi.entity.CommentaireService;
import com.pi.entity.userservice;
import com.pi.utils.AlertUtils;
import com.pi.validation.ValidationException;
import javafx.beans.property.SimpleIntegerProperty;
import javafx.beans.property.SimpleStringProperty;
import javafx.fxml.FXML;
import javafx.scene.control.*;

import java.sql.SQLException;
import java.time.format.DateTimeFormatter;
import java.util.List;

public final class AvisController {

    private final AvisService avisService = new AvisService();
    private final CommentaireService commentaireService = new CommentaireService();
    private final userservice userService = new userservice();

    private static final DateTimeFormatter DT = DateTimeFormatter.ofPattern("yyyy-MM-dd HH:mm");
    private List<user> cachedUsers = List.of();

    @FXML private TableView<Avis> avisTable;
    @FXML private TableColumn<Avis, Number> colAvisId;
    @FXML private TableColumn<Avis, String> colAvisUser;
    @FXML private TableColumn<Avis, Number> colAvisRating;
    @FXML private TableColumn<Avis, String> colAvisTitre;
    @FXML private TableColumn<Avis, String> colAvisCreated;

    @FXML private Label selectedAvisLabel;

    @FXML private TableView<Commentaire> comTable;
    @FXML private TableColumn<Commentaire, Number> colComId;
    @FXML private TableColumn<Commentaire, Number> colComAvis;
    @FXML private TableColumn<Commentaire, String> colComUser;
    @FXML private TableColumn<Commentaire, String> colComContenu;
    @FXML private TableColumn<Commentaire, String> colComCreated;

    @FXML
    public void initialize() {

        colAvisId.setCellValueFactory(v -> new SimpleIntegerProperty(v.getValue().getId()));
        colAvisUser.setCellValueFactory(v -> new SimpleStringProperty(userLabel(v.getValue().getUserId())));
        colAvisRating.setCellValueFactory(v -> new SimpleIntegerProperty(v.getValue().getRating()));
        colAvisTitre.setCellValueFactory(v -> new SimpleStringProperty(v.getValue().getTitre()));
        colAvisCreated.setCellValueFactory(v -> new SimpleStringProperty(
                v.getValue().getCreatedAt() == null ? "" : DT.format(v.getValue().getCreatedAt())
        ));

        colComId.setCellValueFactory(v -> new SimpleIntegerProperty(v.getValue().getId()));
        colComAvis.setCellValueFactory(v -> new SimpleIntegerProperty(v.getValue().getAvisId()));
        colComUser.setCellValueFactory(v -> new SimpleStringProperty(userLabel(v.getValue().getUserId())));
        colComContenu.setCellValueFactory(v -> new SimpleStringProperty(v.getValue().getContenu()));
        colComCreated.setCellValueFactory(v -> new SimpleStringProperty(
                v.getValue().getCreatedAt() == null ? "" : DT.format(v.getValue().getCreatedAt())
        ));

        onRefresh();
    }

    private String userLabel(int userId) {
        for (user u : cachedUsers) {
            if (u.getId() == userId) return u.getPrenom() + " " + u.getName();
        }
        return "User#" + userId;
    }

    @FXML
    public void onRefresh() {
        try {
            cachedUsers = userService.afficher();
            avisTable.getItems().setAll(avisService.afficher());
            comTable.getItems().clear();
            selectedAvisLabel.setText("Aucun avis sélectionné.");
        } catch (SQLException e) {
            AlertUtils.error("DB", e.getMessage());
        }
    }

    @FXML
    public void onAddAvis() {
        try {
            cachedUsers = userService.afficher();
            if (cachedUsers.isEmpty()) {
                AlertUtils.error("Users", "Ajoute au moins un user dans la table users avant de créer un avis.");
                return;
            }

            AvisDialog d = new AvisDialog(cachedUsers, null);
            d.showAndWait().ifPresent(a -> {
                try {
                    avisService.ajouter(a);
                    onRefresh();
                } catch (ValidationException ve) {
                    AlertUtils.error("Validation", ve.getMessage());
                } catch (SQLException e) {
                    AlertUtils.error("DB", e.getMessage());
                }
            });
        } catch (SQLException e) {
            AlertUtils.error("DB", e.getMessage());
        }
    }

    @FXML
    public void onEditAvis() {
        Avis selected = avisTable.getSelectionModel().getSelectedItem();
        if (selected == null) {
            AlertUtils.error("Avis", "Sélectionne un avis.");
            return;
        }

        try {
            cachedUsers = userService.afficher();
            AvisDialog d = new AvisDialog(cachedUsers, selected);
            d.showAndWait().ifPresent(updated -> {
                try {
                    avisService.setToUpdate(updated);
                    avisService.modifier(selected.getId());
                    onRefresh();
                } catch (ValidationException ve) {
                    AlertUtils.error("Validation", ve.getMessage());
                } catch (SQLException e) {
                    AlertUtils.error("DB", e.getMessage());
                }
            });
        } catch (SQLException e) {
            AlertUtils.error("DB", e.getMessage());
        }
    }

    @FXML
    public void onDeleteAvis() {
        Avis selected = avisTable.getSelectionModel().getSelectedItem();
        if (selected == null) {
            AlertUtils.error("Avis", "Sélectionne un avis.");
            return;
        }

        if (!AlertUtils.confirm("Suppression", "Supprimer l'avis #" + selected.getId() + " ? (CASCADE commentaires)"))
            return;

        try {
            avisService.supprimer(selected.getId());
            onRefresh();
        } catch (SQLException e) {
            AlertUtils.error("DB", e.getMessage());
        }
    }

    @FXML
    public void onLoadComs() {
        Avis selected = avisTable.getSelectionModel().getSelectedItem();
        if (selected == null) {
            AlertUtils.error("Commentaires", "Sélectionne un avis.");
            return;
        }

        try {
            selectedAvisLabel.setText("Avis #" + selected.getId() + " • " + selected.getTitre());
            comTable.getItems().setAll(commentaireService.afficherParAvis(selected.getId()));
        } catch (SQLException e) {
            AlertUtils.error("DB", e.getMessage());
        }
    }

    @FXML
    public void onDeleteCom() {
        Commentaire selected = comTable.getSelectionModel().getSelectedItem();
        if (selected == null) {
            AlertUtils.error("Commentaires", "Sélectionne un commentaire.");
            return;
        }

        if (!AlertUtils.confirm("Suppression", "Supprimer commentaire #" + selected.getId() + " ?"))
            return;

        try {
            commentaireService.supprimer(selected.getId());
            onLoadComs();
        } catch (SQLException e) {
            AlertUtils.error("DB", e.getMessage());
        }
    }
}
