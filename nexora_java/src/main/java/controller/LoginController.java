package controller;
import java.io.InputStream;
import com.pi.entities.user;
//import com.pi.entity.FingerprintService;
import com.pi.entity.userservice;
import com.pi.utils.SessionManager;
import javafx.collections.FXCollections;
import javafx.collections.ObservableList;
import javafx.event.ActionEvent;
import javafx.fxml.FXML;
import javafx.fxml.FXMLLoader;
import javafx.fxml.Initializable;
import javafx.scene.Parent;
import javafx.scene.control.*;
import javafx.scene.control.cell.PropertyValueFactory;
import javafx.scene.layout.VBox;
import javafx.scene.layout.HBox;
import com.fazecast.jSerialComm.SerialPort;
import java.util.Scanner;
import java.io.IOException;
import java.net.URL;
import java.sql.SQLException;
import java.util.ResourceBundle;

import javafx.scene.Scene;
import javafx.scene.chart.PieChart;
import javafx.stage.Stage;
import java.io.FileOutputStream;
import com.itextpdf.text.Document;
import com.itextpdf.text.Element;
import com.itextpdf.text.Font;
import com.itextpdf.text.Paragraph;
import com.itextpdf.text.pdf.PdfPTable;
import com.itextpdf.text.pdf.PdfWriter;


public class LoginController implements Initializable {

    @FXML
    private TableView<user> table;

    @FXML
    private TableColumn<user, String> nomT;

    @FXML
    private TableColumn<user, String> prenomT;

    @FXML
    private TableColumn<user, String> emailT;

    @FXML
    private TableColumn<user, Integer> numT;

    @FXML
    private TableColumn<user, String> roleT;

    @FXML
    private TableColumn<user, String>  mdpT;

    @FXML
    private TableColumn<user, Void> actionsT;

    @FXML
    private TextField nomtext;

    @FXML
    private TextField prenomtext;

    @FXML
    private TextField emailtext;

    @FXML
    private TextField numtel;

    @FXML
    private TextField mdp;

    @FXML
    private VBox formContainer;

    @FXML
    private javafx.scene.text.Text formTitle;

    @FXML
    private Button saveButton;

    @FXML
    private Label statusLabel;

    private SerialPort comPort;

    private user userEnCoursDeModification = null;

    @FXML
    private void showStatistique(ActionEvent event) {
        try {
            ObservableList<user> list = FXCollections.observableArrayList(userservice.afficher());

            int adminCount = 0;
            int employeCount = 0;

            for (user u : list) {
                if (u.getRole().equalsIgnoreCase("Admin")) {
                    adminCount++;
                } else if (u.getRole().equalsIgnoreCase("Employe")) {
                    employeCount++;
                }
            }

            PieChart pieChart = new PieChart();
            pieChart.getData().add(new PieChart.Data("Admin", adminCount));
            pieChart.getData().add(new PieChart.Data("Employe", employeCount));

            pieChart.setTitle("Statistique des rôles");

            Stage stage = new Stage();
            stage.setScene(new Scene(pieChart, 500, 400));
            stage.setTitle("Statistique Utilisateurs");
            stage.show();

        } catch (SQLException e) {
            showError(e.getMessage());
        }
    }


    @FXML
    private void exportPDF(ActionEvent event) {

        try {
            Document document = new Document();
            String path = System.getProperty("user.home") + "/Downloads/Users.pdf";
            PdfWriter.getInstance(document, new FileOutputStream(path));
            document.open();

            Font titleFont = new Font(Font.FontFamily.HELVETICA, 18, Font.BOLD);
            Paragraph title = new Paragraph("Liste des Utilisateurs", titleFont);
            title.setAlignment(Element.ALIGN_CENTER);
            document.add(title);
            document.add(new Paragraph(" "));

            PdfPTable pdfTable = new PdfPTable(5);

            pdfTable.addCell("Nom");
            pdfTable.addCell("Prenom");
            pdfTable.addCell("Email");
            pdfTable.addCell("Telephone");
            pdfTable.addCell("Role");

            ObservableList<user> list = FXCollections.observableArrayList(userservice.afficher());

            for (user u : list) {
                pdfTable.addCell(u.getName());
                pdfTable.addCell(u.getPrenom());
                pdfTable.addCell(u.getEmail());
                pdfTable.addCell(String.valueOf(u.getNum()));
                pdfTable.addCell(u.getRole());
            }

            document.add(pdfTable);
            document.close();

            showInfo("PDF exporté avec succès (Users.pdf)");

        } catch (Exception e) {
            showError(e.getMessage());
        }
    }

    @FXML
    private ComboBox<String> textrole;

    private userservice userservice = new userservice();

    @Override
    public void initialize(URL url, ResourceBundle resourceBundle) {

        nomT.setCellValueFactory(new PropertyValueFactory<>("name"));
        prenomT.setCellValueFactory(new PropertyValueFactory<>("prenom"));
        emailT.setCellValueFactory(new PropertyValueFactory<>("email"));
        numT.setCellValueFactory(new PropertyValueFactory<>("num"));
        roleT.setCellValueFactory((new PropertyValueFactory<>("role")));
        textrole.setItems(FXCollections.observableArrayList(
                "Admin",
                "Client",
                "partenaire"
        ));
        mdpT.setCellValueFactory(new PropertyValueFactory<>("mdp"));

        // Configuration de la colonne Actions avec boutons Modifier/Supprimer
        actionsT.setCellFactory(param -> new TableCell<>() {

            private final Button btnModifier = new Button("Modifier");
            private final Button btnSupprimer = new Button("Supprimer");
            private final Button btnEmpreinte = new Button("Empreinte");

            private final HBox hbox = new HBox(5, btnModifier, btnSupprimer, btnEmpreinte);

            {

                btnModifier.getStyleClass().add("btn-action-modifier");
                btnSupprimer.getStyleClass().add("btn-action-supprimer");
                btnEmpreinte.getStyleClass().add("btn-secondary");

                hbox.setAlignment(javafx.geometry.Pos.CENTER);

                btnModifier.setOnAction(event -> {
                    user u = getTableView().getItems().get(getIndex());
                    modifierUserInline(u);
                });

                btnSupprimer.setOnAction(event -> {
                    user u = getTableView().getItems().get(getIndex());
                    supprimerUserInline(u);
                });

                // 🔥 NOUVEAU BOUTON EMPREINTE
                btnEmpreinte.setOnAction(event -> {
                    user u = getTableView().getItems().get(getIndex());
                    ajouterEmpreintePourUser(u);
                });
            }

            @Override
            protected void updateItem(Void item, boolean empty) {
                super.updateItem(item, empty);
                setGraphic(empty ? null : hbox);
            }
        });

        afficherUsers();
    }

    private void ajouterEmpreintePourUser(user u) {

        new Thread(() -> {

            SerialPort comPort = null;

            try {

                SerialPort[] ports = SerialPort.getCommPorts();
                boolean portExists = false;
                for (SerialPort p : ports) {
                    if (p.getSystemPortName().equalsIgnoreCase("COM3")) {
                        portExists = true;
                        break;
                    }
                }
                if (!portExists) {
                    javafx.application.Platform.runLater(() ->
                            statusLabel.setText("❌ Capteur d'empreinte non détecté. Branchez le dispositif sur COM3.")
                    );
                    return;
                }

                comPort = SerialPort.getCommPort("COM3");
                comPort.setBaudRate(9600);

                if (!comPort.openPort()) {
                    final SerialPort portToClose = comPort;
                    javafx.application.Platform.runLater(() -> {
                        statusLabel.setText("❌ Capteur d'empreinte non connecté (COM3 indisponible)");
                    });
                    return;
                }

                Thread.sleep(2000);

                javafx.application.Platform.runLater(() ->
                        statusLabel.setText("Placez votre doigt sur le capteur...")
                );

                comPort.getOutputStream().write('e');
                comPort.getOutputStream().flush();

                InputStream in = comPort.getInputStream();
                StringBuilder buffer = new StringBuilder();

                int fingerId = -1;
                boolean enrollSuccess = false;
                boolean alreadyExists = false;

                long startTime = System.currentTimeMillis();

                while (System.currentTimeMillis() - startTime < 40000
                        && !enrollSuccess && !alreadyExists) {

                    while (in.available() > 0) {

                        char c = (char) in.read();
                        buffer.append(c);

                        String data = buffer.toString();
                        System.out.print(c);

                        // --------- STATUS ---------

                        if (data.contains("PLACE_FINGER")) {
                            javafx.application.Platform.runLater(() ->
                                    statusLabel.setText("Placez votre doigt...")
                            );
                            buffer.setLength(0);
                        }

                        if (data.contains("IMAGE_OK")) {
                            javafx.application.Platform.runLater(() ->
                                    statusLabel.setText("Empreinte capturée, replacez le doigt...")
                            );
                            buffer.setLength(0);
                        }

                        if (data.contains("PLACE_SAME_FINGER")) {
                            javafx.application.Platform.runLater(() ->
                                    statusLabel.setText("Placez le même doigt...")
                            );
                            buffer.setLength(0);
                        }

                        // --------- SI EMPREINTE EXISTE DEJA ---------

                        if (data.contains("FINGER_ALREADY_EXISTS")) {

                            javafx.application.Platform.runLater(() ->
                                    statusLabel.setText("⚠️ Cet utilisateur existe déjà !")
                            );

                            buffer.setLength(0);
                            alreadyExists = true;
                            break;
                        }

                        // --------- EXTRACTION ID ---------

                        if (data.contains("FINGER_ID:")) {

                            int index = data.indexOf("FINGER_ID:") + 10;
                            StringBuilder number = new StringBuilder();

                            while (index < data.length()
                                    && Character.isDigit(data.charAt(index))) {

                                number.append(data.charAt(index));
                                index++;
                            }

                            if (number.length() > 0) {
                                fingerId = Integer.parseInt(number.toString());
                                System.out.println("\nFingerID extrait = " + fingerId);
                                buffer.setLength(0);
                            }
                        }

                        // --------- SUCCESS ---------

                        if (data.contains("ENROLL_SUCCESS")) {
                            enrollSuccess = true;
                            buffer.setLength(0);
                            break;
                        }
                    }

                    Thread.sleep(50);
                }

                if (comPort != null && comPort.isOpen())
                    comPort.closePort();

                // --------- RESULTAT ---------

                if (alreadyExists) {

                    javafx.application.Platform.runLater(() ->
                            statusLabel.setText("⚠️ Cet utilisateur existe déjà !")
                    );

                } else if (enrollSuccess && fingerId != -1) {

                    if (userservice.fingerIdExiste(fingerId)) {

                        javafx.application.Platform.runLater(() ->
                                statusLabel.setText("Cette empreinte est déjà utilisée.")
                        );

                    } else {

                        userservice.updateFingerId(u.getId(), fingerId);

                        javafx.application.Platform.runLater(() -> {
                            statusLabel.setText("Empreinte enregistrée avec succès !");
                            afficherUsers();
                        });
                    }

                } else {

                    javafx.application.Platform.runLater(() ->
                            statusLabel.setText("⏱️ Échec enregistrement empreinte.")
                    );
                }

            } catch (Exception e) {

                e.printStackTrace();

                javafx.application.Platform.runLater(() ->
                        statusLabel.setText("Erreur : " + e.getMessage())
                );

            } finally {

                if (comPort != null && comPort.isOpen()) {
                    comPort.closePort();
                }
            }

        }).start();
    }


    // ================== AJOUT ==================

    @FXML
    void ajouterdonner(ActionEvent event) {
        // Afficher le formulaire en mode ajout
        userEnCoursDeModification = null;
        formTitle.setText("AJOUTER UTILISATEUR");
        saveButton.setText("ENREGISTRER");
        clearFields();
        formContainer.setVisible(true);
    }

    @FXML
    void annulerFormulaire(ActionEvent event) {
        // Cacher le formulaire
        formContainer.setVisible(false);
        clearFields();
        userEnCoursDeModification = null;
    }

    @FXML
    void enregistrerUser(ActionEvent event) {
        if (userEnCoursDeModification == null) {
            // Mode ajout
            ajouteruser();
        } else {
            // Mode modification
            modifierUserSave();
        }
    }

    public void ajouteruser() {
        try {
            int tel = Integer.parseInt(numtel.getText());
            String role = textrole.getValue();

            if(role == null){
                showError("Veuillez choisir un rôle");
                return;
            }
            user u = new user(
                    nomtext.getText(),
                    emailtext.getText(),
                    prenomtext.getText(),
                    tel,
                    role,
                    mdp.getText()
            );

            userservice.ajouter(u);
            userEnCoursDeModification = u;
            showInfo("Utilisateur ajouté avec succès");

            afficherUsers();
            clearFields();
            formContainer.setVisible(false);

        } catch (NumberFormatException e) {
            showError("Numéro invalide");
        } catch (SQLException e) {
            showError(e.getMessage());
        }
    }





    // ================== MODIFICATION ==================

    private void modifierUserInline(user selectedUser) {
        if (selectedUser == null) {
            showError("Utilisateur non trouvé");
            return;
        }

        // Remplir le formulaire avec les données de l'utilisateur
        userEnCoursDeModification = selectedUser;
        formTitle.setText("MODIFIER UTILISATEUR");
        saveButton.setText("METTRE À JOUR");

        nomtext.setText(selectedUser.getName());
        prenomtext.setText(selectedUser.getPrenom());
        emailtext.setText(selectedUser.getEmail());
        numtel.setText(String.valueOf(selectedUser.getNum()));
        textrole.setValue(selectedUser.getRole());
        mdp.setText(selectedUser.getMdp());

        formContainer.setVisible(true);
    }

    private void modifierUserSave() {
        if (userEnCoursDeModification == null) {
            showError("Aucun utilisateur sélectionné");
            return;
        }

        try {
            int tel = Integer.parseInt(numtel.getText());
            String role = textrole.getValue();

            if(role == null){
                showError("Veuillez choisir un rôle");
                return;
            }

            userEnCoursDeModification.setName(nomtext.getText());
            userEnCoursDeModification.setPrenom(prenomtext.getText());
            userEnCoursDeModification.setEmail(emailtext.getText());
            userEnCoursDeModification.setNum(tel);
            userEnCoursDeModification.setRole(role);
            userEnCoursDeModification.setMdp(mdp.getText());

            userservice.modifier(userEnCoursDeModification);

            showInfo("Utilisateur modifié avec succès");

            afficherUsers();
            clearFields();
            formContainer.setVisible(false);
            userEnCoursDeModification = null;

        } catch (NumberFormatException e) {
            showError("Numéro invalide");
        } catch (SQLException e) {
            showError(e.getMessage());
        }
    }










    // ================== SUPPRESSION ==================

    private void supprimerUserInline(user selectedUser) {
        if (selectedUser == null) {
            showError("Utilisateur non trouvé");
            return;
        }

        Alert confirmation = new Alert(Alert.AlertType.CONFIRMATION);
        confirmation.setTitle("Confirmation");
        confirmation.setHeaderText("Voulez-vous vraiment supprimer cet utilisateur ?");
        confirmation.setContentText(selectedUser.getName() + " " + selectedUser.getPrenom());

        confirmation.showAndWait().ifPresent(response -> {
            if (response == ButtonType.OK) {
                try {
                    userservice.supprimer(selectedUser);
                    showInfo("Utilisateur supprimé avec succès");
                    afficherUsers();
                } catch (SQLException e) {
                    showError(e.getMessage());
                }
            }
        });
    }


    // ================== AFFICHAGE ==================

    public void afficherUsers() {
        ObservableList<user> list = FXCollections.observableArrayList();
        try {
            list.addAll(userservice.afficher());
            table.setItems(list);
        } catch (SQLException e) {
            showError(e.getMessage());
        }
    }

    private void clearFields() {
        nomtext.clear();
        prenomtext.clear();
        emailtext.clear();
        numtel.clear();
        textrole.setValue(null);
        mdp.clear();

    }

    private void showError(String msg) {
        Alert alert = new Alert(Alert.AlertType.ERROR);
        alert.setTitle("Erreur");
        alert.setHeaderText(msg);
        alert.show();
    }

    private void showInfo(String msg) {
        Alert alert = new Alert(Alert.AlertType.INFORMATION);
        alert.setTitle("Succès");
        alert.setHeaderText(msg);
        alert.show();
    }
}
