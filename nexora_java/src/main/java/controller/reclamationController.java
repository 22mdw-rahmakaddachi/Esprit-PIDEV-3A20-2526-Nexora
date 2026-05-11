package controller;

import com.pi.entities.Activite;
import com.pi.entities.Reclamation;
import com.pi.utils.SessionManager;
import com.pi.utils.mydatabase;
import javafx.collections.FXCollections;
import javafx.collections.ObservableList;
import javafx.fxml.FXML;
import javafx.scene.control.*;
import javafx.scene.control.cell.PropertyValueFactory;
import javafx.scene.layout.HBox;
import javafx.scene.layout.VBox;

import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;

public class reclamationController {

    // TABLE ACTIVITES
    @FXML private TableView<Activite> tableActivites;
    @FXML private TableColumn<Activite,String> colNom;
    @FXML private TableColumn<Activite,String> colLieu;
    @FXML private TableColumn<Activite,Void> colActionActivite;

    // ZONE AJOUT
    @FXML private VBox zoneAjout;
    @FXML private TextArea txtDescription;

    // TABLE RECLAMATIONS
    @FXML private TableView<Reclamation> tableReclamations;
    @FXML private TableColumn<Reclamation,String> colActiviteRec;
    @FXML private TableColumn<Reclamation,String> colDescriptionRec;
    @FXML private TableColumn<Reclamation,String> colStatutRec;
    @FXML private TableColumn<Reclamation,Void> colActionRec;

    private int activiteSelectionnee = 0;
    private int reclamationEnModification = 0;

    @FXML
    public void initialize(){

        colNom.setCellValueFactory(new PropertyValueFactory<>("nom"));
        colLieu.setCellValueFactory(new PropertyValueFactory<>("lieu"));

        colActiviteRec.setCellValueFactory(new PropertyValueFactory<>("activiteNom"));
        colDescriptionRec.setCellValueFactory(new PropertyValueFactory<>("description"));
        colStatutRec.setCellValueFactory(new PropertyValueFactory<>("statut"));

        afficherActivitesClient();
        afficherMesReclamations();
        ajouterBoutonReclamer();
        ajouterBoutonsActions();
    }

    // ================= AFFICHER ACTIVITES =================
    private void afficherActivitesClient(){

        ObservableList<Activite> list = FXCollections.observableArrayList();
        int clientId = SessionManager.getCurrentUser().getId();

        try{
            Connection con = mydatabase.getInstance().getConnection();

            String sql="SELECT a.id,a.nom,a.lieu " +
                    "FROM activite a " +
                    "JOIN participation_demande p ON a.id=p.activite_id " +
                    "WHERE p.client_id=? AND p.statut='ACCEPTEE'";

            PreparedStatement ps=con.prepareStatement(sql);
            ps.setInt(1,clientId);
            ResultSet rs=ps.executeQuery();

            while(rs.next()){
                Activite a=new Activite();
                a.setId(rs.getInt("id"));
                a.setNom(rs.getString("nom"));
                a.setLieu(rs.getString("lieu"));
                list.add(a);
            }

            tableActivites.setItems(list);

        }catch(Exception e){e.printStackTrace();}
    }

    // ================= BOUTON RECLAMER =================
    private void ajouterBoutonReclamer(){

        colActionActivite.setCellFactory(param -> new TableCell<>(){

            private final Button btn=new Button("Réclamer");

            {
                btn.setOnAction(event -> {

                    Activite act=getTableView().getItems().get(getIndex());
                    activiteSelectionnee=act.getId();

                    reclamationEnModification = 0;
                    txtDescription.clear();
                    zoneAjout.setVisible(true);
                });
            }

            @Override
            protected void updateItem(Void item, boolean empty){
                super.updateItem(item,empty);
                setGraphic(empty?null:btn);
            }
        });
    }

    // ================= AJOUT / MODIFIER =================
    @FXML
    private void ajouterReclamation(){

        int clientId = SessionManager.getCurrentUser().getId();
        String description = txtDescription.getText();

        if(description.isEmpty()){
            showAlert("Veuillez écrire une description !");
            return;
        }

        try{
            Connection con = mydatabase.getInstance().getConnection();

            if(reclamationEnModification == 0){

                String sql = "INSERT INTO reclamation (client_id, activite_id, description, statut) VALUES (?,?,?,?)";
                PreparedStatement ps = con.prepareStatement(sql);
                ps.setInt(1,clientId);
                ps.setInt(2,activiteSelectionnee);
                ps.setString(3,description);
                ps.setString(4,"EN_ATTENTE");
                ps.executeUpdate();

            }else{

                String sql = "UPDATE reclamation SET description=? WHERE id=?";
                PreparedStatement ps = con.prepareStatement(sql);
                ps.setString(1,description);
                ps.setInt(2,reclamationEnModification);
                ps.executeUpdate();

                reclamationEnModification = 0;
            }

        }catch(Exception e){e.printStackTrace();}

        txtDescription.clear();
        zoneAjout.setVisible(false);
        afficherMesReclamations();
    }

    // ================= AFFICHER RECLAMATIONS =================
    @FXML
    private void afficherMesReclamations(){

        ObservableList<Reclamation> list = FXCollections.observableArrayList();

        try{
            Connection con = mydatabase.getInstance().getConnection();

            String sql =
                    "SELECT r.id, r.description, r.statut, r.activite_id, a.nom " +
                            "FROM reclamation r " +
                            "JOIN activite a ON r.activite_id = a.id " +
                            "WHERE r.client_id=?";

            PreparedStatement ps = con.prepareStatement(sql);
            ps.setInt(1, SessionManager.getCurrentUser().getId());

            ResultSet rs = ps.executeQuery();

            while(rs.next()){
                Reclamation r = new Reclamation();
                r.setId(rs.getInt("id"));
                r.setDescription(rs.getString("description"));
                r.setStatut(rs.getString("statut"));
                r.setActiviteId(rs.getInt("activite_id"));
                r.setActiviteNom(rs.getString("nom"));
                list.add(r);
            }

        }catch(Exception e){e.printStackTrace();}

        tableReclamations.setItems(list);
    }

    // ================= MODIFIER + SUPPRIMER =================
    private void ajouterBoutonsActions(){

        colActionRec.setCellFactory(param -> new TableCell<>(){

            private final Button btnMod = new Button("Modifier");
            private final Button btnSup = new Button("Supprimer");
            private final HBox box = new HBox(5, btnMod, btnSup);

            {
                btnMod.setOnAction(event -> {

                    Reclamation r = getTableView().getItems().get(getIndex());

                    reclamationEnModification = r.getId();
                    activiteSelectionnee = r.getActiviteId();

                    txtDescription.setText(r.getDescription());
                    zoneAjout.setVisible(true);
                });

                btnSup.setOnAction(event -> {

                    Reclamation r = getTableView().getItems().get(getIndex());

                    try{
                        Connection con = mydatabase.getInstance().getConnection();
                        String sql = "DELETE FROM reclamation WHERE id=?";
                        PreparedStatement ps = con.prepareStatement(sql);
                        ps.setInt(1,r.getId());
                        ps.executeUpdate();
                    }catch(Exception e){e.printStackTrace();}

                    afficherMesReclamations();
                });
            }

            @Override
            protected void updateItem(Void item, boolean empty){
                super.updateItem(item,empty);
                setGraphic(empty?null:box);
            }
        });
    }

    @FXML
    private void annulerReclamation() {
        txtDescription.clear();
        zoneAjout.setVisible(false);
        reclamationEnModification = 0;
    }

    private void showAlert(String message){
        Alert alert = new Alert(Alert.AlertType.WARNING);
        alert.setContentText(message);
        alert.showAndWait();
    }
}