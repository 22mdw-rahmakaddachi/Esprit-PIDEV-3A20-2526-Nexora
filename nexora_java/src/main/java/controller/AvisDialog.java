package controller;

import com.pi.entities.Avis;
import com.pi.entities.user;
import javafx.collections.FXCollections;
import javafx.scene.control.*;
import javafx.scene.layout.GridPane;

import java.util.List;

public final class AvisDialog extends Dialog<Avis> {

    private final ComboBox<user> userCombo = new ComboBox<>();
    private final ComboBox<Integer> ratingCombo = new ComboBox<>();
    private final TextField titreField = new TextField();
    private final TextArea contenuArea = new TextArea();

    public AvisDialog(List<user> users, Avis initial) {
        setTitle(initial == null ? "Ajouter Avis" : "Modifier Avis");
        setHeaderText(null);
        getDialogPane().getButtonTypes().addAll(ButtonType.OK, ButtonType.CANCEL);

        userCombo.setItems(FXCollections.observableArrayList(users));
        ratingCombo.setItems(FXCollections.observableArrayList(1, 2, 3, 4, 5));
        contenuArea.setWrapText(true);
        contenuArea.setPrefRowCount(6);

        if (!users.isEmpty()) userCombo.getSelectionModel().select(0);
        ratingCombo.getSelectionModel().select(Integer.valueOf(5));

        if (initial != null) {
            users.stream().filter(u -> u.getId() == initial.getUserId()).findFirst()
                    .ifPresent(u -> userCombo.getSelectionModel().select(u));
            ratingCombo.getSelectionModel().select(Integer.valueOf(initial.getRating()));
            titreField.setText(initial.getTitre());
            contenuArea.setText(initial.getContenu());
        }

        GridPane g = new GridPane();
        g.setHgap(10);
        g.setVgap(10);
        g.addRow(0, new Label("User"), userCombo);
        g.addRow(1, new Label("Rating"), ratingCombo);
        g.addRow(2, new Label("Titre"), titreField);
        g.addRow(3, new Label("Contenu"), contenuArea);
        getDialogPane().setContent(g);

        Button ok = (Button) getDialogPane().lookupButton(ButtonType.OK);
        ok.disableProperty().bind(
                userCombo.valueProperty().isNull()
                        .or(ratingCombo.valueProperty().isNull())
                        .or(titreField.textProperty().length().lessThan(3))
                        .or(titreField.textProperty().length().greaterThan(100))
                        .or(contenuArea.textProperty().length().lessThan(10))
                        .or(contenuArea.textProperty().length().greaterThan(5000))
        );

        setResultConverter(bt -> {
            if (bt != ButtonType.OK) return null;
            return new Avis(userCombo.getValue().getId(), ratingCombo.getValue(), titreField.getText(), contenuArea.getText());
        });
    }
}