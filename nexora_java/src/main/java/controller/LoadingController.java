package controller;

import javafx.animation.*;
import javafx.fxml.FXML;
import javafx.fxml.FXMLLoader;
import javafx.fxml.Initializable;
import javafx.scene.Parent;
import javafx.scene.Scene;
import javafx.scene.control.Label;
import javafx.scene.shape.Circle;
import javafx.scene.shape.Path;
import javafx.stage.Stage;
import javafx.util.Duration;

import java.net.URL;
import java.util.ResourceBundle;

public class LoadingController implements Initializable {

    @FXML
    private Label airplane;

    @FXML
    private Label cloud1;

    @FXML
    private Label cloud2;

    @FXML
    private Label cloud3;

    @FXML
    private Circle star1;

    @FXML
    private Circle star2;

    @FXML
    private Circle star3;

    @FXML
    private Circle dot1;

    @FXML
    private Circle dot2;

    @FXML
    private Circle dot3;

    @FXML
    private Circle dot4;

    @FXML
    private Circle dot5;

    @FXML
    private Path flightPath;

    private Stage stage;

    @Override
    public void initialize(URL url, ResourceBundle resourceBundle) {
        startAnimations();
    }

    public void setStage(Stage stage) {
        this.stage = stage;
    }

    private void startAnimations() {
        // Animation de l'avion le long du chemin
        PathTransition airplaneTransition = new PathTransition();
        airplaneTransition.setDuration(Duration.seconds(8));
        airplaneTransition.setPath(flightPath);
        airplaneTransition.setNode(airplane);
        airplaneTransition.setCycleCount(Animation.INDEFINITE);
        airplaneTransition.setAutoReverse(false);
        airplaneTransition.play();

        // Animation des nuages (mouvement horizontal)
        animateCloud(cloud1, -50, 4);
        animateCloud(cloud2, -70, 5);
        animateCloud(cloud3, -60, 4.5);

        // Animation des étoiles (scintillement)
        animateStar(star1, 0);
        animateStar(star2, 0.5);
        animateStar(star3, 1);

        // Animation des points de chargement
        animateDot(dot1, 0);
        animateDot(dot2, 0.2);
        animateDot(dot3, 0.4);
        animateDot(dot4, 0.6);
        animateDot(dot5, 0.8);
    }

    private void animateCloud(Label cloud, double distance, double duration) {
        TranslateTransition transition = new TranslateTransition(Duration.seconds(duration), cloud);
        transition.setFromX(0);
        transition.setToX(distance);
        transition.setCycleCount(Animation.INDEFINITE);
        transition.setAutoReverse(true);
        transition.play();
    }

    private void animateStar(Circle star, double delay) {
        FadeTransition fade = new FadeTransition(Duration.seconds(1.5), star);
        fade.setFromValue(0.3);
        fade.setToValue(1.0);
        fade.setCycleCount(Animation.INDEFINITE);
        fade.setAutoReverse(true);
        fade.setDelay(Duration.seconds(delay));
        fade.play();
    }

    private void animateDot(Circle dot, double delay) {
        ScaleTransition scale = new ScaleTransition(Duration.seconds(1), dot);
        scale.setFromX(0.5);
        scale.setFromY(0.5);
        scale.setToX(1.2);
        scale.setToY(1.2);
        scale.setCycleCount(Animation.INDEFINITE);
        scale.setAutoReverse(true);
        scale.setDelay(Duration.seconds(delay));

        FadeTransition fade = new FadeTransition(Duration.seconds(1), dot);
        fade.setFromValue(0.3);
        fade.setToValue(1.0);
        fade.setCycleCount(Animation.INDEFINITE);
        fade.setAutoReverse(true);
        fade.setDelay(Duration.seconds(delay));

        ParallelTransition parallel = new ParallelTransition(scale, fade);
        parallel.play();
    }

    public void startLoading(String destination) {
        // Attendre 3 secondes puis naviguer vers la page de destination
        PauseTransition pause = new PauseTransition(Duration.seconds(3));
        pause.setOnFinished(event -> {
            try {
                FXMLLoader loader = new FXMLLoader(getClass().getResource(destination));
                Parent root = loader.load();
                
                // Passer l'utilisateur au controller si c'est DashboardClient
                if (destination.equals("/DashboardClient.fxml")) {
                    DashboardClientController controller = loader.getController();
                    com.pi.entities.user currentUser = com.pi.utils.SessionManager.getCurrentUser();
                    if (currentUser != null) {
                        controller.setUser(currentUser);
                    }
                }
                
                stage.setScene(new Scene(root));
            } catch (Exception e) {
                e.printStackTrace();
            }
        });
        pause.play();
    }
}
