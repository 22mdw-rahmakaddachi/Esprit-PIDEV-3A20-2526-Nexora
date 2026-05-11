package controller;
import com.pi.entities.user;
import com.fazecast.jSerialComm.SerialPort;
import java.io.InputStream;
import java.io.OutputStream;
import com.pi.utils.RememberMeManager;
import com.pi.utils.SessionManager;
import com.pi.utils.mydatabase;
import javafx.event.ActionEvent;
import javafx.fxml.FXML;
import javafx.fxml.FXMLLoader;
import javafx.scene.Parent;
import javafx.scene.Scene;
import javafx.scene.control.*;
import javafx.stage.Stage;
import org.mindrot.jbcrypt.BCrypt;

import java.io.IOException;
import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;

public class Loginne {

    @FXML
    private TextField emailL;

    @FXML
    private PasswordField motpHidden;

    @FXML
    private TextField motpVisible;

    @FXML
    private Button showButton;

    @FXML
    private Label statusLabel;

    @FXML
    private CheckBox rememberMeCheckBox;

    private SerialPort serialPort;
    private Thread fingerprintThread;

    private final Connection con = mydatabase.getInstance().getConnection();

    @FXML
    void openForgotPassword(ActionEvent event) throws IOException {

        Parent root = FXMLLoader.load(getClass().getResource("/ForgotPassword.fxml"));
        Stage stage = (Stage) emailL.getScene().getWindow();
        stage.setScene(new Scene(root));
    }

    // ==================== ALLER INSCRIPTION ====================
    @FXML
    void goToInscription(ActionEvent event) {
        try {
            Parent root = FXMLLoader.load(getClass().getResource("/InscriptionClient.fxml"));
            Stage stage = new Stage();
            stage.setScene(new Scene(root));
            stage.show();
        } catch (IOException e) {
            e.printStackTrace();
        }
    }

    //---------------loginemprunte ////////////////
    @FXML
    void loginEmpreinte(ActionEvent event) {

        Alert alert = new Alert(Alert.AlertType.INFORMATION);
        alert.setHeaderText(null);
        alert.setContentText("👆 Placez votre doigt...");
        alert.show();

        new Thread(() -> {

            SerialPort comPort = null;

            try {

                //////////////////////////////////////////////////////
                // CONNEXION PORT
                //////////////////////////////////////////////////////
                comPort = SerialPort.getCommPort("COM3");
                comPort.setBaudRate(9600);

                if (!comPort.openPort())
                    throw new Exception("Impossible d'ouvrir COM3");

                Thread.sleep(2000);

                //////////////////////////////////////////////////////
                // ENVOYER "s" A ARDUINO
                //////////////////////////////////////////////////////
                OutputStream out = comPort.getOutputStream();
                out.write("s\n".getBytes());
                out.flush();

                //////////////////////////////////////////////////////
                // LECTURE REPONSE
                //////////////////////////////////////////////////////
                InputStream in = comPort.getInputStream();
                StringBuilder buffer = new StringBuilder();

                boolean granted = false;
                boolean denied = false;

                long start = System.currentTimeMillis();

                while (System.currentTimeMillis() - start < 40000
                        && !granted && !denied) {

                    while (in.available() > 0) {

                        char c = (char) in.read();
                        buffer.append(c);
                        String data = buffer.toString();

                        System.out.print(c);

                        //////////////////////////////////////////////////////
                        // SI UTILISATEUR TROUVE
                        //////////////////////////////////////////////////////
                        if (data.contains("FINGER_ID:")) {

                            int index = data.indexOf("FINGER_ID:") + 10;
                            StringBuilder number = new StringBuilder();

                            while (index < data.length()
                                    && Character.isDigit(data.charAt(index))) {

                                number.append(data.charAt(index));
                                index++;
                            }

                            if (number.length() > 0) {

                                int fingerId =
                                        Integer.parseInt(number.toString());

                                buffer.setLength(0);

                                javafx.application.Platform.runLater(() -> {
                                    alert.close();
                                    connecterAvecEmpreinte(fingerId);
                                });

                                granted = true;
                                break;
                            }
                        }

                        //////////////////////////////////////////////////////
                        // SI EMPREINTE N'EXISTE PAS
                        //////////////////////////////////////////////////////
                        if (data.contains("ACCESS_DENIED")) {

                            buffer.setLength(0);

                            javafx.application.Platform.runLater(() -> {
                                alert.close();
                                showError("⚠️ Cet utilisateur n'existe pas");
                            });

                            denied = true;
                            break;
                        }
                    }

                    Thread.sleep(50);
                }

                //////////////////////////////////////////////////////
                // TIMEOUT
                //////////////////////////////////////////////////////
                if (!granted && !denied) {

                    javafx.application.Platform.runLater(() -> {
                        alert.close();
                        showError("⏱️ Temps expiré - Empreinte non reconnue");
                    });
                }

            } catch (Exception e) {

                e.printStackTrace();

                javafx.application.Platform.runLater(() -> {
                    alert.close();
                    showError(e.getMessage());
                });

            } finally {

                if (comPort != null && comPort.isOpen())
                    comPort.closePort();
            }

        }).start();
    }






    // ==================== LOGIN ====================
    @FXML
    void login(ActionEvent event) {

        String mdp = motpHidden.isVisible() ? motpHidden.getText().trim() : motpVisible.getText().trim();
        String email = emailL.getText().trim();

        if (email.isEmpty() || mdp.isEmpty()) {
            showError("Veuillez remplir tous les champs");
            return;
        }

        try {

            String sql = "SELECT * FROM users WHERE email=?";
            PreparedStatement ps = con.prepareStatement(sql);
            ps.setString(1, email);

            ResultSet rs = ps.executeQuery();

            if (rs.next()) {

                int userId = rs.getInt("id");
                String role = rs.getString("role");
                String hashedPassword = rs.getString("mdp");
                int tentative = rs.getInt("tentative");
                boolean validation = rs.getBoolean("validation");
                long blockUntil = rs.getLong("block_until");
                int blockLevel = rs.getInt("block_level");

                long now = System.currentTimeMillis();

                // 🔒 Vérifier si compte bloqué
                if (!validation) {

                    if (now < blockUntil) {
                        long seconds = (blockUntil - now) / 1000;
                        showError("Compte bloqué encore " + seconds + " secondes");
                        return;
                    } else {
                        // Débloquer automatiquement
                        validation = true;
                        tentative = 0;

                        String unlockSql = "UPDATE users SET validation=1, tentative=0 WHERE id=?";
                        PreparedStatement unlockPs = con.prepareStatement(unlockSql);
                        unlockPs.setInt(1, userId);
                        unlockPs.executeUpdate();
                    }
                }

                // 🔐 Vérification BCrypt
                if (hashedPassword == null || hashedPassword.isEmpty()) {
                    showError("Mot de passe invalide en base de données. Contactez l'administrateur.");
                    return;
                }
                boolean passwordMatch;
                try {
                    // jBCrypt ne supporte pas $2y$ (PHP), on le convertit en $2a$
                    String hashToCheck = hashedPassword;
                    if (hashToCheck.startsWith("$2y$")) {
                        hashToCheck = "$2a$" + hashToCheck.substring(4);
                    }
                    passwordMatch = BCrypt.checkpw(mdp, hashToCheck);
                } catch (IllegalArgumentException e) {
                    System.err.println("Hash BCrypt invalide pour: " + email + " | hash: " + hashedPassword);
                    showError("Erreur de connexion: mot de passe mal configuré. Contactez l'administrateur.");
                    return;
                }
                if (passwordMatch) {

                    // Reset sécurité
                    String resetSql = "UPDATE users SET tentative=0, block_level=0 WHERE id=?";
                    PreparedStatement resetPs = con.prepareStatement(resetSql);
                    resetPs.setInt(1, userId);
                    resetPs.executeUpdate();

                    // 💾 Sauvegarder les informations "Remember Me" (email + mot de passe)
                    RememberMeManager.saveCredentials(email, mdp, rememberMeCheckBox.isSelected());

                    // 🔥 CREATION OBJET USER
                    user loggedUser = new user();
                    loggedUser.setId(userId);
                    loggedUser.setEmail(email);
                    loggedUser.setRole(role);
                    loggedUser.setPrenom(rs.getString("prenom"));
                    loggedUser.setName(rs.getString("nom"));
                    loggedUser.setNum(rs.getInt("num"));

                    // 🔥 SAUVEGARDE SESSION
                    SessionManager.setCurrentUser(loggedUser);

                    System.out.println("✅ Utilisateur connecté ID: " + userId);

                    String pageToOpen;
                    String roleTrimmed = role != null ? role.trim() : "";
                    System.out.println("🔑 Rôle utilisateur: [" + roleTrimmed + "]");

                    if (roleTrimmed.equalsIgnoreCase("Admin") || roleTrimmed.equalsIgnoreCase("ROLE_ADMIN")) {
                        pageToOpen = "/page.fxml";
                    } else if (roleTrimmed.equalsIgnoreCase("partenaire") || roleTrimmed.equalsIgnoreCase("ROLE_PARTENAIRE")) {
                        pageToOpen = "/pagePartenaire.fxml";
                    } else if (roleTrimmed.equalsIgnoreCase("Client") || roleTrimmed.equalsIgnoreCase("ROLE_CLIENT") || roleTrimmed.equalsIgnoreCase("ROLE_USER")) {
                        pageToOpen = "/DashboardClient.fxml";
                    } else {
                        showError("Rôle inconnu: [" + roleTrimmed + "]");
                        return;
                    }

                    FXMLLoader loader = new FXMLLoader(getClass().getResource("/loading.fxml"));
                    Parent root = loader.load();

                    Stage stage = (Stage) emailL.getScene().getWindow();
                    stage.setScene(new Scene(root));
                    stage.show();

                    LoadingController controller = loader.getController();
                    controller.setStage(stage);
                    controller.startLoading(pageToOpen);
                } else {

                    // ❌ Mot de passe incorrect
                    tentative++;

                    if (tentative >= 3) {

                        blockLevel++;
                        long blockTime;

                        if (blockLevel == 1) {
                            blockTime = 30;
                        } else if (blockLevel == 2) {
                            blockTime = 60;
                        } else {
                            blockTime = 30 * blockLevel;
                        }

                        long newBlockUntil = System.currentTimeMillis() + (blockTime * 1000);

                        String blockSql = "UPDATE users SET validation=0, tentative=0, block_until=?, block_level=? WHERE id=?";
                        PreparedStatement blockPs = con.prepareStatement(blockSql);
                        blockPs.setLong(1, newBlockUntil);
                        blockPs.setInt(2, blockLevel);
                        blockPs.setInt(3, userId);
                        blockPs.executeUpdate();

                        showError("Compte bloqué pendant " + blockTime + " secondes");

                    } else {

                        String updateSql = "UPDATE users SET tentative=? WHERE id=?";
                        PreparedStatement updatePs = con.prepareStatement(updateSql);
                        updatePs.setInt(1, tentative);
                        updatePs.setInt(2, userId);
                        updatePs.executeUpdate();

                        showError("Email ou mot de passe incorrect (Tentative " + tentative + "/3)");
                    }
                }

            } else {
                showError("Email ou mot de passe incorrect");
            }

        } catch (Exception e) {
            e.printStackTrace();
            showError("Erreur de connexion");
        }
    }



    // ==================== BUTTON SHOW PASSWORD ====================
    @FXML
    public void initialize() {

        // Charger les informations sauvegardées si "Remember Me" était activé
        if (RememberMeManager.isRememberMeEnabled()) {
            String savedEmail = RememberMeManager.getSavedEmail();
            String savedPassword = RememberMeManager.getSavedPassword();
            
            if (!savedEmail.isEmpty()) {
                emailL.setText(savedEmail);
                rememberMeCheckBox.setSelected(true);
            }
            
            if (!savedPassword.isEmpty()) {
                motpHidden.setText(savedPassword);
                motpVisible.setText(savedPassword);
            }
        }

        // Affiche le mot de passe quand on appuie sur le bouton
        showButton.setOnMousePressed(event -> {
            motpVisible.setText(motpHidden.getText());
            motpVisible.setVisible(true);
            motpVisible.setManaged(true);

            motpHidden.setVisible(false);
            motpHidden.setManaged(false);
        });

        showButton.setOnMouseReleased(event -> {
            motpHidden.setText(motpVisible.getText());
            motpHidden.setVisible(true);
            motpHidden.setManaged(true);

            motpVisible.setVisible(false);
            motpVisible.setManaged(false);
        });


    }

    // ==================== MESSAGE ERREUR ====================
    private void showError(String msg) {
        Alert alert = new Alert(Alert.AlertType.ERROR);
        alert.setTitle("Erreur");
        alert.setHeaderText(null);
        alert.setContentText(msg);
        alert.showAndWait();
    }
    private void connecterAvecEmpreinte(int fingerId) {

        try {

            String sql =
                    "SELECT * FROM users WHERE finger_id=?";

            PreparedStatement ps =
                    con.prepareStatement(sql);

            ps.setInt(1, fingerId);

            ResultSet rs =
                    ps.executeQuery();

            if (!rs.next()) {
                showError("Utilisateur introuvable");
                return;
            }

            //////////////////////////////////////////////////////
            // CREATION SESSION
            //////////////////////////////////////////////////////
            user loggedUser = new user();

            loggedUser.setId(rs.getInt("id"));
            loggedUser.setEmail(rs.getString("email"));
            loggedUser.setRole(rs.getString("role"));
            loggedUser.setPrenom(rs.getString("prenom"));
            loggedUser.setName(rs.getString("nom"));
            loggedUser.setNum(rs.getInt("num"));

            SessionManager.setCurrentUser(loggedUser);

            //////////////////////////////////////////////////////
            // REDIRECTION DASHBOARD
            //////////////////////////////////////////////////////
            String role = rs.getString("role");
            String page;

            if (role.equalsIgnoreCase("Admin"))
                page = "/page.fxml";
            else if (role.equalsIgnoreCase("partenaire"))
                page = "/Homeemploye.fxml";
            else
                page = "/homeclient.fxml";

            FXMLLoader loader =
                    new FXMLLoader(getClass().getResource("/loading.fxml"));

            Parent root =
                    loader.load();

            Stage stage =
                    (Stage) emailL.getScene().getWindow();

            stage.setScene(new Scene(root));

            LoadingController controller =
                    loader.getController();

            controller.setStage(stage);
            controller.startLoading(page);

        }
        catch (Exception e) {
            e.printStackTrace();
            showError("Erreur connexion empreinte");
        }
    }




}