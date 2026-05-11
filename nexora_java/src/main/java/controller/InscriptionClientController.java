package controller;

import com.pi.entities.user;
import com.pi.entity.userservice;
import javafx.fxml.FXML;
import javafx.scene.control.Alert;
import javafx.scene.control.PasswordField;
import javafx.scene.control.TextField;

import java.sql.SQLException;

public class InscriptionClientController {

    @FXML
    private TextField nom;

    @FXML
    private TextField prenom;

    @FXML
    private TextField num;

    @FXML
    private TextField email;

    @FXML
    private PasswordField password;

    @FXML
    void inscrireClient() {

        userservice us = new userservice();

        user u = new user();

        u.setName(nom.getText());
        u.setPrenom(prenom.getText());
        u.setNum(Integer.parseInt(num.getText()));
        u.setEmail(email.getText());
        u.setMdp(password.getText());


        // On définit que c'est un CLIENT
        u.setRole("client");

        try {

            // On utilise la fonction ajouter déjà existante
            us.ajouter(u);

            Alert alert = new Alert(Alert.AlertType.INFORMATION);
            alert.setTitle("Succès");
            alert.setContentText("Client inscrit avec succès !");
            alert.show();

        } catch (SQLException e) {
            System.out.println(e.getMessage());
        }
    }
}