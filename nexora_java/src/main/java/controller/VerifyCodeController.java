package controller;

import javafx.event.ActionEvent;
import javafx.fxml.FXML;
import javafx.fxml.FXMLLoader;
import javafx.scene.Parent;
import javafx.scene.Scene;
import javafx.scene.control.TextField;
import javafx.scene.control.Alert;
import javafx.stage.Stage;

import java.sql.*;
import java.util.Properties;

import jakarta.mail.*;
import jakarta.mail.internet.InternetAddress;
import jakarta.mail.internet.MimeMessage;

import com.pi.utils.mydatabase;

public class VerifyCodeController {

    @FXML
    private TextField codeField;

    private Connection con = mydatabase.getInstance().getConnection();

    private int userId;
    private String userEmail;

    private String EXPEDITEUR;
    private String MOT_DE_PASSE_APP;

    public VerifyCodeController() {
        try {
            Properties config = new Properties();
            config.load(getClass().getResourceAsStream("/config.properties"));

            EXPEDITEUR = config.getProperty("mail.username");
            MOT_DE_PASSE_APP = config.getProperty("mail.password");

        } catch (Exception e) {
            e.printStackTrace();
        }
    }

    public void setUserId(int id){
        this.userId = id;

        try {
            PreparedStatement ps = con.prepareStatement("SELECT email FROM users WHERE id=?");
            ps.setInt(1, id);
            ResultSet rs = ps.executeQuery();
            if(rs.next()){
                userEmail = rs.getString("email");
            }
        } catch (Exception e){
            e.printStackTrace();
        }
    }

    // ✅ Vérifier le code
    @FXML
    void verifyCode(ActionEvent event){

        try {

            String sql = "SELECT reset_code, reset_expiration FROM users WHERE id=?";
            PreparedStatement ps = con.prepareStatement(sql);
            ps.setInt(1, userId);

            ResultSet rs = ps.executeQuery();

            if(rs.next()){

                String dbCode = rs.getString("reset_code");
                long expiration = rs.getLong("reset_expiration");

                if(codeField.getText().equals(dbCode)
                        && System.currentTimeMillis() < expiration){

                    FXMLLoader loader = new FXMLLoader(getClass().getResource("/ResetPassword.fxml"));
                    Parent root = loader.load();

                    ResetPasswordController controller = loader.getController();
                    controller.setUserId(userId);

                    Stage stage = (Stage) codeField.getScene().getWindow();
                    stage.setScene(new Scene(root));
                    stage.show();

                } else {
                    showAlert("Code invalide ou expiré !");
                }
            }

        } catch (Exception e){
            e.printStackTrace();
        }
    }

    // ✅ Bouton Retour
    @FXML
    void goBack(ActionEvent event){
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/ForgotPassword.fxml"));
            Parent root = loader.load();

            Stage stage = (Stage) codeField.getScene().getWindow();
            stage.setScene(new Scene(root));
            stage.show();

        } catch (Exception e){
            e.printStackTrace();
        }
    }

    // ✅ Renvoyer le code
    @FXML
    void resendCode(ActionEvent event){
        try {

            String newCode = String.valueOf((int)(Math.random() * 900000) + 100000);
            long expiration = System.currentTimeMillis() + (5 * 60 * 1000);

            PreparedStatement ps = con.prepareStatement(
                    "UPDATE users SET reset_code=?, reset_expiration=? WHERE id=?"
            );

            ps.setString(1, newCode);
            ps.setLong(2, expiration);
            ps.setInt(3, userId);
            ps.executeUpdate();

            envoyerEmail(userEmail, newCode);

            showAlert("Nouveau code envoyé !");

        } catch (Exception e){
            e.printStackTrace();
        }
    }

    private void envoyerEmail(String destinataire, String code) {

        Properties props = new Properties();
        props.put("mail.smtp.host", "smtp.gmail.com");
        props.put("mail.smtp.port", "587");
        props.put("mail.smtp.auth", "true");
        props.put("mail.smtp.starttls.enable", "true");

        Session session = Session.getInstance(props, new Authenticator() {
            protected PasswordAuthentication getPasswordAuthentication() {
                return new PasswordAuthentication(EXPEDITEUR, MOT_DE_PASSE_APP);
            }
        });

        try {

            Message message = new MimeMessage(session);
            message.setFrom(new InternetAddress(EXPEDITEUR));
            message.setRecipients(Message.RecipientType.TO,
                    InternetAddress.parse(destinataire));
            message.setSubject("Nouveau code de réinitialisation");

            message.setText("Votre nouveau code est : " + code + "\nValide 5 minutes.");

            Transport.send(message);

        } catch (MessagingException e) {
            e.printStackTrace();
        }
    }

    private void showAlert(String msg){
        Alert alert = new Alert(Alert.AlertType.INFORMATION);
        alert.setContentText(msg);
        alert.show();
    }
}