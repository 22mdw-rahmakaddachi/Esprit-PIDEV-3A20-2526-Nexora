package controller;

import javafx.fxml.FXML;
import javafx.scene.control.Label;

public class detailleController {

    @FXML
    private Label textemail;

    @FXML
    private Label textnom;

    @FXML
    private Label textprenom;

    public Label getTextemail() {
        return textemail;
    }

    public void setTextemail(String textemail) {
        this.textemail.setText(textemail);
    }

    public Label getTextnom() {
        return textnom;
    }

    public void setTextnom(String textnom) {
        this.textnom.setText(textnom);
    }

    public Label getTextprenom() {
        return textprenom;
    }

    public void setTextprenom(String textprenom) {
        this.textprenom.setText(textprenom);
    }
}
