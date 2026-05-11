package controller;

import javafx.fxml.FXML;
import javafx.fxml.FXMLLoader;
import javafx.scene.Parent;
import javafx.scene.Scene;
import javafx.scene.control.TextField;
import javafx.scene.control.Alert;
import java.sql.*;
import com.pi.utils.mydatabase;
import javafx.stage.Stage;

import java.util.Properties;
import jakarta.mail.*;
import jakarta.mail.internet.InternetAddress;
import jakarta.mail.internet.MimeMessage;

public class ForgotPasswordController {

    @FXML
    private TextField emailField;

    private Connection con = mydatabase.getInstance().getConnection();

    private String EXPEDITEUR;
    private String MOT_DE_PASSE_APP;

    public ForgotPasswordController() {
        try {
            Properties config = new Properties();
            config.load(getClass().getResourceAsStream("/config.properties"));

            EXPEDITEUR = config.getProperty("mail.username");
            MOT_DE_PASSE_APP = config.getProperty("mail.password");

        } catch (Exception e) {
            e.printStackTrace();
        }
    }

    @FXML
    void sendCode() {
        try {
            String email = emailField.getText();

            String sql = "SELECT id FROM users WHERE email=?";
            PreparedStatement ps = con.prepareStatement(sql);
            ps.setString(1, email);
            ResultSet rs = ps.executeQuery();

            if (rs.next()) {

                int userId = rs.getInt("id");

                String code = String.valueOf((int)(Math.random() * 900000) + 100000);
                long expiration = System.currentTimeMillis() + (5 * 60 * 1000);

                String update = "UPDATE users SET reset_code=?, reset_expiration=? WHERE id=?";
                PreparedStatement ups = con.prepareStatement(update);
                ups.setString(1, code);
                ups.setLong(2, expiration);
                ups.setInt(3, userId);
                ups.executeUpdate();

                envoyerEmail(email, code);

                FXMLLoader loader = new FXMLLoader(getClass().getResource("/VerifyCode.fxml"));
                Parent root = loader.load();

                VerifyCodeController controller = loader.getController();
                controller.setUserId(userId);

                Stage stage = (Stage) emailField.getScene().getWindow();
                stage.setScene(new Scene(root));

            } else {
                showAlert("Email introuvable !");
            }

        } catch (Exception e) {
            e.printStackTrace();
            showAlert("Erreur : " + e.getMessage());
        }
    }

    private void envoyerEmail(String destinataire, String code) {

        Properties props = new Properties();

        props.put("mail.smtp.host", "smtp.gmail.com");
        props.put("mail.smtp.port", "587");
        props.put("mail.smtp.auth", "true");
        props.put("mail.smtp.starttls.enable", "true");
        props.put("mail.smtp.ssl.trust", "smtp.gmail.com");
        props.put("mail.smtp.ssl.protocols", "TLSv1.2");

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
            message.setSubject("Code de réinitialisation");

            message.setText("Votre code est : " + code + "\nValide 5 minutes.");

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