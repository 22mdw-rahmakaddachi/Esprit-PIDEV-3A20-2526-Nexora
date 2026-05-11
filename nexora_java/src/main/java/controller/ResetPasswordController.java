package controller;

import javafx.fxml.FXML;
import javafx.fxml.FXMLLoader;
import javafx.scene.Parent;
import javafx.scene.Scene;
import javafx.scene.control.PasswordField;
import javafx.scene.control.Alert;
import javafx.stage.Stage;
import org.mindrot.jbcrypt.BCrypt;

import java.sql.Connection;
import java.sql.PreparedStatement;

import com.pi.utils.mydatabase;

public class ResetPasswordController {

    @FXML
    private PasswordField newPasswordField;

    private Connection con = mydatabase.getInstance().getConnection();
    private int userId;

    public void setUserId(int id){
        this.userId = id;
    }

    @FXML
    void resetPassword(){

        try {

            // 🔐 HASH AVEC BCRYPT
            String hashedPassword = BCrypt.hashpw(
                    newPasswordField.getText(),
                    BCrypt.gensalt()
            );

            String sql = "UPDATE users SET mdp=? WHERE id=?";
            PreparedStatement ps = con.prepareStatement(sql);

            ps.setString(1, hashedPassword);
            ps.setInt(2, userId);

            ps.executeUpdate();

            showAlert("Mot de passe modifié avec succès !");
            goToLogin();

        } catch (Exception e){
            e.printStackTrace();
        }
    }

    private void showAlert(String msg){
        Alert alert = new Alert(Alert.AlertType.INFORMATION);
        alert.setContentText(msg);
        alert.showAndWait();
    }

    private void goToLogin() {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/loginne.fxml"));
            Parent root = loader.load();

            Stage stage = (Stage) newPasswordField.getScene().getWindow();
            stage.setScene(new Scene(root));
            stage.show();

        } catch (Exception e) {
            e.printStackTrace();
        }
    }
}