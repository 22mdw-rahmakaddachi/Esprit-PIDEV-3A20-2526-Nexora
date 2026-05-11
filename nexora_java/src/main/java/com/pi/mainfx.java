package com.pi;

import javafx.application.Application;
import javafx.fxml.FXMLLoader;
import javafx.scene.Parent;
import javafx.scene.Scene;
import javafx.stage.Stage;

import java.io.IOException;

public class mainfx extends Application {
    public static void main(String[] args){
        launch(args);
    }
    @Override
    public void start(Stage stage) throws IOException {
        Parent root = FXMLLoader.load(getClass().getResource("/loginne.fxml"));
        Scene scene = new Scene(root);
        stage.setScene(scene);
        stage.show();
    }
}

/*package com.pi;

import com.pi.entities.user;
import controller.DashboardPartenaireController;  // ← Import pour partenaire
import javafx.application.Application;
import javafx.fxml.FXMLLoader;
import javafx.scene.Parent;
import javafx.scene.Scene;
import javafx.stage.Stage;

public class mainfx extends Application {
    public static void main(String[] args){
        launch(args);
    }

    @Override
    public void start(Stage stage) throws Exception {
        // ✅ Charger DashboardPartenaire.fxml
        FXMLLoader loader = new FXMLLoader(getClass().getResource("/DashboardPartenaire.fxml"));
        Parent root = loader.load();

        // Créer un utilisateur partenaire de test
        user testUser = new user();
        testUser.setId(1);
        testUser.setName("Partenaire");
        testUser.setPrenom("Test");
        testUser.setEmail("partenaire@test.com");
        testUser.setRole("partenaire");

        // ✅ Cast correct vers DashboardPartenaireController
        DashboardPartenaireController controller = loader.getController();
        controller.setUser(testUser);

        stage.setTitle("NEX-ORA - Dashboard Partenaire");
        stage.setScene(new Scene(root));
        stage.show();
    }
}*/