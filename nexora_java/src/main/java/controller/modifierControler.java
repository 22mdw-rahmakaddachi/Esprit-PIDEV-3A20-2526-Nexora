package controller;

import com.pi.entities.user;
import com.pi.entity.userservice;
import javafx.event.ActionEvent;
import javafx.fxml.FXML;
import javafx.fxml.FXMLLoader;
import javafx.scene.Parent;
import javafx.scene.control.TextField;

public class modifierControler {

    @FXML
    private TextField emailmodif;

    @FXML
    private TextField nommodif;

    @FXML
    private TextField nummodif;

    @FXML
    private TextField prenommodif;

    @FXML
    private TextField rolemodif;

    private user userActuel;
    private userservice userservice = new userservice();

    // recevoir user depuis table
    public void setUser(user u) {

        this.userActuel = u;

        nommodif.setText(u.getName());
        prenommodif.setText(u.getPrenom());
        emailmodif.setText(u.getEmail());
        nummodif.setText(String.valueOf(u.getNum()));
        rolemodif.setText(u.getRole());
    }

    @FXML
    void modifierEtRetour(ActionEvent event) {

        try {

            // Vérification champ numéro
            if (!nummodif.getText().matches("\\d+")) {
                System.out.println("Le numéro doit être un nombre !");
                return;
            }

            userActuel.setName(nommodif.getText());
            userActuel.setPrenom(prenommodif.getText());
            userActuel.setEmail(emailmodif.getText());
            userActuel.setNum(Integer.parseInt(nummodif.getText()));
            userActuel.setRole(rolemodif.getText());

            userservice.modifier(userActuel);

            FXMLLoader loader = new FXMLLoader(getClass().getResource("/login.fxml"));
            Parent root = loader.load();

            LoginController lc = loader.getController();
            lc.afficherUsers();

            nommodif.getScene().setRoot(root);

        } catch (NumberFormatException e) {
            System.out.println("Erreur : numéro invalide !");
        } catch (Exception e) {
            e.printStackTrace();
        }
    }

}
