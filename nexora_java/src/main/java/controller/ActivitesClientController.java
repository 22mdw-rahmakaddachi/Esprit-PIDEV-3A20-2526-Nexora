package controller;

import com.pi.entities.Activite;
import com.pi.entities.ParticipationDemande;
import com.pi.entities.Paiement;
import com.pi.entity.ActiviteService;
import com.pi.entity.ParticipationDemandeService;
import com.pi.entity.PaiementService;
import com.pi.entities.user;
import com.pi.utils.SessionManager;
import com.pi.utils.NotificationManager;
import javafx.collections.FXCollections;
import javafx.collections.ObservableList;
import javafx.fxml.FXML;
import javafx.fxml.FXMLLoader;
import javafx.scene.Parent;
import javafx.scene.Scene;
import javafx.scene.control.*;
import javafx.scene.image.Image;
import javafx.scene.image.ImageView;
import javafx.scene.layout.HBox;
import javafx.scene.layout.Priority;
import javafx.scene.layout.Region;
import javafx.scene.layout.VBox;
import javafx.stage.Stage;
import javafx.stage.Modality;

import java.io.File;
import java.io.IOException;
import java.sql.SQLException;
import java.time.LocalDate;
import java.util.List;
import java.util.UUID;

public class ActivitesClientController {
    @FXML private VBox activitesContainer;
    @FXML private ScrollPane listeView;
    @FXML private ScrollPane detailsView;
    @FXML private Label nomLabel;
    @FXML private Label typeLabel;
    @FXML private Label lieuLabel;
    @FXML private Label dateLabel;
    @FXML private Label prixLabel;
    @FXML private Label placesLabel;
    @FXML private Label descriptionLabel;
    @FXML private Label partenaireNomLabel;
    @FXML private Label partenaireEmailLabel;
    @FXML private Label partenaireTelLabel;
    @FXML private Label welcomeClientLabel;
    @FXML private Label notificationBadge;
    @FXML private Label countLabel;
    @FXML private ImageView imageView;
    @FXML private TextField nomClientField;
    @FXML private TextField emailClientField;
    @FXML private TextField telephoneClientField;
    @FXML private ComboBox<String> methodePaiementCombo;
    @FXML private Button participerBtn;
    @FXML private Button annulerBtn;
    @FXML private Button payerBtn;
    @FXML private VBox detailsPane;
    @FXML private VBox statutBox;
    @FXML private Label statutLabel;

    // Champs de recherche
    @FXML private ComboBox<String> searchTypeCombo;
    @FXML private ComboBox<String> searchLieuCombo;
    @FXML private DatePicker searchDatePicker;

    // Labels d'erreur pour validation inline
    @FXML private Label nomErrorLabel;
    @FXML private Label emailErrorLabel;
    @FXML private Label telephoneErrorLabel;
    @FXML private Label paiementErrorLabel;

    private ActiviteService activiteService;
    private ParticipationDemandeService demandeService;
    private PaiementService paiementService;
    private ObservableList<Activite> activitesList;
    private ObservableList<Activite> activitesListeComplete; // Liste complète pour la recherche
    private Activite selectedActivite;
    private int clientId = 1; // À récupérer de la session

    public void setClientId(int clientId) {
        this.clientId = clientId;
    }

    @FXML
    public void initialize() {
        activiteService = new ActiviteService();
        demandeService = new ParticipationDemandeService();
        paiementService = new PaiementService();
        activitesList = FXCollections.observableArrayList();
        activitesListeComplete = FXCollections.observableArrayList();

        setupMethodePaiement();
        setupSearchFields();
        loadActivites();

        // S'assurer que la vue détails est cachée au démarrage
        detailsView.setVisible(false);
        detailsView.setManaged(false);

        // Afficher le message de bienvenue avec le client connecté
        afficherBienvenue();

        // Charger les notifications
        chargerNotifications();

        // Mettre à jour le compteur
        updateCountLabel();
    }

    private void afficherBienvenue() {
        user currentUser = SessionManager.getCurrentUser();
        if (currentUser != null) {
            welcomeClientLabel.setText("Bienvenue, " + currentUser.getPrenom() + " " + currentUser.getName());
        }
    }

    /**
     * Charge et affiche le nombre de notifications non lues
     */
    private void chargerNotifications() {
        user currentUser = SessionManager.getCurrentUser();
        if (currentUser != null) {
            int count = NotificationManager.getNombreNotificationsNonLues(
                    currentUser.getId(),
                    "CLIENT"
            );

            if (count > 0) {
                notificationBadge.setText(String.valueOf(count));
                notificationBadge.setVisible(true);
                notificationBadge.setManaged(true);
            } else {
                notificationBadge.setVisible(false);
                notificationBadge.setManaged(false);
            }
        }
    }

    /**
     * Ouvre la fenêtre des notifications
     */
    @FXML
    private void ouvrirNotifications() {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/Notifications.fxml"));
            Parent root = loader.load();

            NotificationsController controller = loader.getController();
            controller.setUserType("CLIENT");

            Stage stage = new Stage();
            stage.setTitle("Notifications");
            stage.setScene(new javafx.scene.Scene(root));
            stage.setOnHidden(e -> chargerNotifications()); // Recharger le badge à la fermeture
            stage.show();

        } catch (IOException e) {
            System.err.println("Erreur ouverture notifications: " + e.getMessage());
            e.printStackTrace();
        }
    }

    /**
     * Met à jour le label du compteur d'activités
     */
    private void updateCountLabel() {
        if (countLabel != null) {
            int count = activitesList.size();
            countLabel.setText("(" + count + " activité" + (count > 1 ? "s" : "") + ")");
        }
    }

    /**
     * Crée une carte visuelle pour une activité (version liste)
     */
    private VBox createActiviteCard(Activite activite) {
        // Vérifier si l'activité est expirée
        boolean isExpired = activite.getDateActivite() != null &&
                activite.getDateActivite().isBefore(java.time.LocalDate.now());

        VBox card = new VBox(15);
        card.setMaxWidth(850);

        // Style différent pour les activités expirées
        if (isExpired) {
            card.setStyle(
                    "-fx-background-color: #FEE2E2; " +
                            "-fx-padding: 20; " +
                            "-fx-background-radius: 10; " +
                            "-fx-border-color: #EF4444; " +
                            "-fx-border-width: 2; " +
                            "-fx-border-radius: 10; " +
                            "-fx-cursor: hand; " +
                            "-fx-effect: dropshadow(gaussian, rgba(239,68,68,0.3), 8, 0, 0, 2);"
            );
        } else {
            card.setStyle(
                    "-fx-background-color: white; " +
                            "-fx-padding: 20; " +
                            "-fx-background-radius: 10; " +
                            "-fx-border-color: #E5E7EB; " +
                            "-fx-border-width: 2; " +
                            "-fx-border-radius: 10; " +
                            "-fx-cursor: hand; " +
                            "-fx-effect: dropshadow(gaussian, rgba(0,0,0,0.08), 8, 0, 0, 2);"
            );
        }

        HBox mainContent = new HBox(20);
        mainContent.setAlignment(javafx.geometry.Pos.CENTER_LEFT);

        // Image miniature
        ImageView miniImage = new ImageView();
        miniImage.setFitWidth(150);
        miniImage.setFitHeight(100);
        miniImage.setPreserveRatio(true);
        miniImage.setStyle("-fx-effect: dropshadow(gaussian, rgba(0,0,0,0.15), 5, 0, 0, 1); -fx-background-radius: 8;");

        if (activite.getImages() != null && !activite.getImages().isEmpty()) {
            try {
                File file = new File(activite.getImages());
                if (file.exists()) {
                    Image image = new Image(file.toURI().toString());
                    miniImage.setImage(image);
                }
            } catch (Exception e) {
                System.out.println("Erreur chargement image: " + e.getMessage());
            }
        }

        // Informations de l'activité
        VBox infoBox = new VBox(8);
        HBox.setHgrow(infoBox, Priority.ALWAYS);

        // Nom de l'activité avec badge EXPIRÉ si nécessaire
        HBox nomBox = new HBox(10);
        nomBox.setAlignment(javafx.geometry.Pos.CENTER_LEFT);

        Label nomLabel = new Label(activite.getNom());
        nomLabel.setStyle("-fx-font-size: 20px; -fx-font-weight: bold; -fx-text-fill: #1F2937;");
        nomLabel.setWrapText(true);

        nomBox.getChildren().add(nomLabel);

        // Ajouter badge EXPIRÉ si l'activité est expirée
        if (isExpired) {
            Label expiredBadge = new Label("⏰ EXPIRÉ");
            expiredBadge.setStyle(
                    "-fx-font-size: 12px; " +
                            "-fx-font-weight: bold; " +
                            "-fx-text-fill: white; " +
                            "-fx-background-color: #EF4444; " +
                            "-fx-padding: 3 8; " +
                            "-fx-background-radius: 5;"
            );
            nomBox.getChildren().add(expiredBadge);
        }

        // Type et lieu
        HBox detailsBox = new HBox(20);
        Label typeLabel = new Label("📌 " + activite.getType());
        typeLabel.setStyle("-fx-font-size: 13px; -fx-text-fill: #6B7280;");

        Label lieuLabel = new Label("📍 " + activite.getLieu());
        lieuLabel.setStyle("-fx-font-size: 13px; -fx-text-fill: #6B7280;");

        // Date avec style rouge si expirée
        Label dateLabel = new Label("📅 " + activite.getDateActivite());
        if (isExpired) {
            dateLabel.setStyle("-fx-font-size: 13px; -fx-font-weight: bold; -fx-text-fill: #EF4444;");
        } else {
            dateLabel.setStyle("-fx-font-size: 13px; -fx-text-fill: #6B7280;");
        }

        detailsBox.getChildren().addAll(typeLabel, lieuLabel, dateLabel);

        // Prix et places
        HBox bottomBox = new HBox(15);
        bottomBox.setAlignment(javafx.geometry.Pos.CENTER_LEFT);

        Label prixLabel = new Label(activite.getPrix() + " TND");
        prixLabel.setStyle("-fx-font-size: 18px; -fx-font-weight: bold; -fx-text-fill: #10B981;");

        Region spacer = new Region();
        HBox.setHgrow(spacer, Priority.ALWAYS);

        Label placesLabel = new Label();
        if (activite.getPlacesDisponibles() <= 0) {
            placesLabel.setText("🔴 COMPLET");
            placesLabel.setStyle("-fx-font-size: 13px; -fx-font-weight: bold; -fx-text-fill: #EF4444; -fx-background-color: #FEE2E2; -fx-padding: 5 10; -fx-background-radius: 5;");
        } else if (activite.getPlacesDisponibles() <= 3) {
            placesLabel.setText("⚠️ " + activite.getPlacesDisponibles() + " places");
            placesLabel.setStyle("-fx-font-size: 13px; -fx-font-weight: bold; -fx-text-fill: #F59E0B; -fx-background-color: #FEF3C7; -fx-padding: 5 10; -fx-background-radius: 5;");
        } else {
            placesLabel.setText("✅ " + activite.getPlacesDisponibles() + " places");
            placesLabel.setStyle("-fx-font-size: 13px; -fx-text-fill: #10B981; -fx-background-color: #D1FAE5; -fx-padding: 5 10; -fx-background-radius: 5;");
        }

        bottomBox.getChildren().addAll(prixLabel, spacer, placesLabel);

        infoBox.getChildren().addAll(nomBox, detailsBox, bottomBox);

        mainContent.getChildren().addAll(miniImage, infoBox);
        card.getChildren().add(mainContent);

        // Effet hover - différent pour les activités expirées
        final String normalStyle = isExpired ?
                "-fx-background-color: #FEE2E2; " +
                        "-fx-padding: 20; " +
                        "-fx-background-radius: 10; " +
                        "-fx-border-color: #EF4444; " +
                        "-fx-border-width: 2; " +
                        "-fx-border-radius: 10; " +
                        "-fx-cursor: hand; " +
                        "-fx-effect: dropshadow(gaussian, rgba(239,68,68,0.3), 8, 0, 0, 2);" :
                "-fx-background-color: white; " +
                        "-fx-padding: 20; " +
                        "-fx-background-radius: 10; " +
                        "-fx-border-color: #E5E7EB; " +
                        "-fx-border-width: 2; " +
                        "-fx-border-radius: 10; " +
                        "-fx-cursor: hand; " +
                        "-fx-effect: dropshadow(gaussian, rgba(0,0,0,0.08), 8, 0, 0, 2);";

        card.setOnMouseEntered(e -> {
            if (isExpired) {
                card.setStyle(normalStyle + "-fx-border-color: #DC2626; -fx-border-width: 3; -fx-effect: dropshadow(gaussian, rgba(220,38,38,0.5), 12, 0, 0, 3);");
            } else {
                card.setStyle(normalStyle + "-fx-border-color: #3B82F6; -fx-border-width: 2; -fx-effect: dropshadow(gaussian, rgba(59,130,246,0.3), 12, 0, 0, 3);");
            }
        });

        card.setOnMouseExited(e -> {
            card.setStyle(normalStyle);
        });

        // Clic pour afficher les détails
        card.setOnMouseClicked(e -> showDetails(activite));

        return card;
    }

    /**
     * Affiche toutes les activités sous forme de cartes en liste verticale
     */
    private void displayActivitesCards() {
        activitesContainer.getChildren().clear();

        for (Activite activite : activitesList) {
            VBox card = createActiviteCard(activite);
            activitesContainer.getChildren().add(card);
        }

        updateCountLabel();
    }

    /**
     * Affiche la vue des détails et cache la liste
     */
    private void showDetails(Activite activite) {
        System.out.println("🔍 showDetails appelé pour: " + activite.getNom());
        selectedActivite = activite;

        // Basculer les vues
        listeView.setVisible(false);
        listeView.setManaged(false);
        detailsView.setVisible(true);
        detailsView.setManaged(true);

        System.out.println("✅ Vues basculées - detailsView visible: " + detailsView.isVisible());

        nomLabel.setText(activite.getNom());
        typeLabel.setText(activite.getType());
        lieuLabel.setText(activite.getLieu());
        dateLabel.setText(activite.getDateActivite().toString());
        prixLabel.setText(activite.getPrix() + " TND");

        // Afficher la description
        if (descriptionLabel != null) {
            if (activite.getDescription() != null && !activite.getDescription().trim().isEmpty()) {
                descriptionLabel.setText(activite.getDescription());
            } else {
                descriptionLabel.setText("Aucune description disponible.");
            }
        }

        // Afficher les places disponibles avec couleur selon disponibilité
        if (activite.getPlacesDisponibles() <= 0) {
            placesLabel.setText("🔴 COMPLET - 0 places");
            placesLabel.setStyle("-fx-text-fill: #EF4444; -fx-font-weight: bold; -fx-font-size: 14px;");
        } else if (activite.getPlacesDisponibles() <= 3) {
            placesLabel.setText("⚠️ " + activite.getPlacesDisponibles() + " places - Dépêchez-vous!");
            placesLabel.setStyle("-fx-text-fill: #F59E0B; -fx-font-weight: bold;");
        } else {
            placesLabel.setText("✅ " + activite.getPlacesDisponibles() + " places disponibles");
            placesLabel.setStyle("-fx-text-fill: #10B981; -fx-font-weight: bold;");
        }

        // Afficher les informations du partenaire (récupérées depuis la base)
        if (activite.getPartenaireNom() != null && !activite.getPartenaireNom().equals("Non disponible")) {
            partenaireNomLabel.setText("📍 Partenaire: " + activite.getPartenaireNom());
        } else {
            partenaireNomLabel.setText("📍 Partenaire: Non disponible");
        }

        // Ne plus afficher l'email
        partenaireEmailLabel.setVisible(false);
        partenaireEmailLabel.setManaged(false);

        if (activite.getPartenaireTelephone() != null && !activite.getPartenaireTelephone().equals("Non disponible")) {
            partenaireTelLabel.setText("📞 Téléphone: " + activite.getPartenaireTelephone());
        } else {
            partenaireTelLabel.setText("📞 Téléphone: Non disponible");
        }

        // Charger l'image
        if (activite.getImages() != null && !activite.getImages().isEmpty()) {
            try {
                File file = new File(activite.getImages());
                if (file.exists()) {
                    Image image = new Image(file.toURI().toString());
                    imageView.setImage(image);
                }
            } catch (Exception e) {
                System.out.println("Erreur chargement image: " + e.getMessage());
            }
        }

        // Pré-remplir automatiquement les informations du client depuis la session
        remplirInfosClient();

        // Vérifier automatiquement le statut de la demande
        checkDemandeStatus();
    }

    /**
     * Retourne à la vue liste et cache les détails
     */
    @FXML
    private void retourListe() {
        detailsView.setVisible(false);
        detailsView.setManaged(false);
        listeView.setVisible(true);
        listeView.setManaged(true);
    }

    private void setupMethodePaiement() {
        methodePaiementCombo.setItems(FXCollections.observableArrayList(
                "Carte Bancaire"
        ));
        methodePaiementCombo.setValue("Carte Bancaire");
    }

    private void setupSearchFields() {
        // Charger les types et lieux dynamiquement depuis la base de données
        loadTypesFromDatabase();
        loadLieuxFromDatabase();
    }

    /**
     * Charge tous les types uniques depuis la base de données
     */
    private void loadTypesFromDatabase() {
        try {
            List<Activite> activites = activiteService.afficher();

            // Extraire tous les types uniques
            java.util.Set<String> typesUniques = new java.util.TreeSet<>(); // TreeSet pour tri alphabétique
            for (Activite activite : activites) {
                if (activite.getType() != null && !activite.getType().trim().isEmpty()) {
                    typesUniques.add(activite.getType());
                }
            }

            // Créer la liste avec "Tous les types" en premier
            ObservableList<String> typesList = FXCollections.observableArrayList();
            typesList.add("Tous les types");
            typesList.addAll(typesUniques);

            searchTypeCombo.setItems(typesList);
            searchTypeCombo.setValue("Tous les types");

            System.out.println("✅ " + typesUniques.size() + " types d'activités chargés depuis la base de données");

        } catch (SQLException e) {
            System.err.println("❌ Erreur lors du chargement des types: " + e.getMessage());
            // En cas d'erreur, utiliser une liste par défaut
            searchTypeCombo.setItems(FXCollections.observableArrayList(
                    "Tous les types", "Sport", "Culture", "Gastronomie", "Nature", "Aventure", "Détente"
            ));
            searchTypeCombo.setValue("Tous les types");
        }
    }

    /**
     * Charge tous les lieux uniques depuis la base de données
     */
    private void loadLieuxFromDatabase() {
        // Liste complète des 24 gouvernorats de Tunisie
        ObservableList<String> lieuxList = FXCollections.observableArrayList(
                "Tous les lieux",
                "Ariana",
                "Béja",
                "Ben Arous",
                "Bizerte",
                "Gabès",
                "Gafsa",
                "Jendouba",
                "Kairouan",
                "Kasserine",
                "Kébili",
                "Le Kef",
                "Mahdia",
                "La Manouba",
                "Médenine",
                "Monastir",
                "Nabeul",
                "Sfax",
                "Sidi Bouzid",
                "Siliana",
                "Sousse",
                "Tataouine",
                "Tozeur",
                "Tunis",
                "Zaghouan"
        );

        searchLieuCombo.setItems(lieuxList);
        searchLieuCombo.setValue("Tous les lieux");

        System.out.println("✅ 24 gouvernorats de Tunisie chargés dans le filtre");
    }

    private void loadActivites() {
        try {
            activitesList.clear();
            activitesListeComplete.clear();
            // Utiliser la méthode avec infos partenaire pour les clients
            List<Activite> activites = activiteService.getAllWithPartenaireInfo();
            activitesListeComplete.addAll(activites);
            activitesList.addAll(activites);
            displayActivitesCards();
        } catch (SQLException e) {
            showAlert(Alert.AlertType.ERROR, "Erreur", "Erreur lors du chargement: " + e.getMessage());
            e.printStackTrace();
        }
    }


    // Nouvelle méthode pour pré-remplir les informations du client
    private void remplirInfosClient() {
        user currentUser = SessionManager.getCurrentUser();
        if (currentUser != null) {
            // Remplir avec les informations de l'utilisateur connecté
            String nomComplet = currentUser.getPrenom() + " " + currentUser.getName();
            nomClientField.setText(nomComplet);
            emailClientField.setText(currentUser.getEmail());

            // Convertir le numéro en String
            if (currentUser.getNum() > 0) {
                telephoneClientField.setText(String.valueOf(currentUser.getNum()));
            }

            // Désactiver les champs pour éviter la modification
            nomClientField.setEditable(false);
            emailClientField.setEditable(false);
            telephoneClientField.setEditable(false);

            // Ajouter un style pour indiquer que c'est pré-rempli
            nomClientField.setStyle("-fx-background-color: #F3F4F6;");
            emailClientField.setStyle("-fx-background-color: #F3F4F6;");
            telephoneClientField.setStyle("-fx-background-color: #F3F4F6;");
        }
    }
    @FXML
    private void checkDemandeStatus() {
        try {
            var demandes = demandeService.getByClient(clientId);
            ParticipationDemande demande = null;

            for (ParticipationDemande d : demandes) {
                if (d.getActiviteId() == selectedActivite.getId()) {
                    demande = d;
                    break;
                }
            }

            // Vérifier si l'activité est expirée
            boolean isExpired = selectedActivite.getDateActivite() != null &&
                    selectedActivite.getDateActivite().isBefore(java.time.LocalDate.now());

            // Vérifier si l'activité est complète
            boolean activiteComplete = selectedActivite.getPlacesDisponibles() <= 0;

            if (demande == null) {
                // Pas de demande
                if (isExpired) {
                    // Activité expirée
                    participerBtn.setDisable(true);
                    annulerBtn.setVisible(false);
                    annulerBtn.setManaged(false);
                    payerBtn.setDisable(true);
                    participerBtn.setText("⏰ ACTIVITÉ EXPIRÉE");
                    participerBtn.setStyle("-fx-background-color: #EF4444; -fx-text-fill: white;");
                    statutLabel.setText("❌ Cette activité est expirée");
                    statutLabel.setStyle("-fx-text-fill: #EF4444; -fx-font-weight: bold; -fx-font-size: 14px;");
                } else if (activiteComplete) {
                    participerBtn.setDisable(true);
                    annulerBtn.setVisible(false);
                    annulerBtn.setManaged(false);
                    payerBtn.setDisable(true);
                    participerBtn.setText("🔴 COMPLET");
                    participerBtn.setStyle("-fx-background-color: #C62828; -fx-text-fill: white;");
                } else {
                    participerBtn.setDisable(false);
                    annulerBtn.setVisible(false);
                    annulerBtn.setManaged(false);
                    payerBtn.setDisable(true);
                    participerBtn.setText("Envoyer Demande");
                    participerBtn.setStyle("");
                }
            } else {
                switch (demande.getStatut()) {
                    case "EN_ATTENTE":
                        participerBtn.setDisable(true);
                        annulerBtn.setVisible(true);
                        annulerBtn.setManaged(true);
                        annulerBtn.setDisable(false);
                        payerBtn.setDisable(true);
                        participerBtn.setText("⏳ En attente");
                        participerBtn.setStyle("");
                        break;

                    case "ACCEPTEE":
                        annulerBtn.setVisible(false);
                        annulerBtn.setManaged(false);
                        if (demande.isPaiementEffectue()) {
                            participerBtn.setDisable(true);
                            payerBtn.setDisable(true);
                            participerBtn.setText("✅ Payé");
                            participerBtn.setStyle("");
                        } else {
                            participerBtn.setDisable(true);
                            payerBtn.setDisable(false);
                            participerBtn.setText("✅ Acceptée");
                            participerBtn.setStyle("");
                        }
                        break;

                    case "REFUSEE":
                        annulerBtn.setVisible(false);
                        annulerBtn.setManaged(false);
                        if (activiteComplete) {
                            participerBtn.setDisable(true);
                            participerBtn.setText("🔴 COMPLET");
                            participerBtn.setStyle("-fx-background-color: #C62828; -fx-text-fill: white;");
                        } else {
                            participerBtn.setDisable(false);
                            participerBtn.setText("❌ Refusée - Réessayer");
                            participerBtn.setStyle("");
                        }
                        payerBtn.setDisable(true);
                        break;
                }
            }
        } catch (SQLException e) {
            System.out.println("Erreur vérification statut: " + e.getMessage());
        }
    }

    @FXML
    private void envoyerDemande() {
        if (selectedActivite == null) {
            showAlert(Alert.AlertType.WARNING, "Attention", "Veuillez sélectionner une activité");
            return;
        }

        // Validation inline sans alerte
        if (!validateClientFields()) {
            // Les erreurs sont déjà affichées sous les champs
            return;
        }

        if (selectedActivite.getPlacesDisponibles() <= 0) {
            showAlert(Alert.AlertType.WARNING, "Attention", "Plus de places disponibles");
            return;
        }

        try {
            // Vérifications de sécurité
            if (selectedActivite.getId() <= 0) {
                showAlert(Alert.AlertType.ERROR, "Erreur", "ID d'activité invalide");
                return;
            }
            
            if (clientId <= 0) {
                showAlert(Alert.AlertType.ERROR, "Erreur", "Utilisateur non connecté. Veuillez vous reconnecter.");
                return;
            }
            
            System.out.println("🔍 DEBUG: Création demande avec activiteId=" + selectedActivite.getId() + ", clientId=" + clientId);
            
            ParticipationDemande demande = new ParticipationDemande(
                    selectedActivite.getId(),
                    clientId,
                    nomClientField.getText(),
                    emailClientField.getText(),
                    telephoneClientField.getText()
            );

            demandeService.ajouter(demande);

            // Cacher les erreurs après succès
            hideAllErrors();

            // ✅ Créer une notification pour le partenaire
            int partenaireId = selectedActivite.getPartenaireId();
            System.out.println("🔍 DEBUG: partenaireId = " + partenaireId + " pour l'activité: " + selectedActivite.getNom());

            if (partenaireId > 0) {
                NotificationManager.creerNotificationNouvelleDemande(
                        partenaireId,
                        demande,
                        selectedActivite
                );
            } else {
                System.err.println("❌ ERREUR: partenaireId est 0 ou invalide! Impossible de créer la notification.");
            }

            // Envoyer un email au partenaire pour le notifier de la nouvelle demande
            envoyerEmailNouvelleDemandeAuPartenaire(demande, selectedActivite);

            showAlert(Alert.AlertType.INFORMATION, "Succès",
                    "Demande envoyée avec succès!\n\n" +
                            "Votre demande est en attente d'acceptation par le partenaire.\n" +
                            "Vous recevrez une notification une fois acceptée.\n" +
                            "Vous pourrez alors procéder au paiement.");

            // Désactiver le bouton payer jusqu'à acceptation
            payerBtn.setDisable(true);
            participerBtn.setDisable(true);

        } catch (SQLException e) {
            showAlert(Alert.AlertType.ERROR, "Erreur", "Erreur lors de l'envoi: " + e.getMessage());
        }
    }

    @FXML
    private void annulerDemande() {
        if (selectedActivite == null) {
            showAlert(Alert.AlertType.WARNING, "Attention", "Veuillez sélectionner une activité");
            return;
        }

        try {
            // Récupérer la demande en attente du client pour cette activité
            var demandes = demandeService.getByClient(clientId);
            ParticipationDemande demande = null;

            for (ParticipationDemande d : demandes) {
                if (d.getActiviteId() == selectedActivite.getId()) {
                    demande = d;
                    break;
                }
            }

            if (demande == null) {
                showAlert(Alert.AlertType.WARNING, "Attention", "Aucune demande trouvée pour cette activité");
                return;
            }

            // Vérifier que la demande est en attente
            if (!demande.getStatut().equals("EN_ATTENTE")) {
                String message = "";
                if (demande.getStatut().equals("ACCEPTEE")) {
                    message = "Votre demande a déjà été acceptée par le partenaire.\n" +
                            "Vous ne pouvez plus l'annuler.\n\n" +
                            "Si vous ne souhaitez plus participer, veuillez contacter le partenaire directement.";
                } else if (demande.getStatut().equals("REFUSEE")) {
                    message = "Votre demande a déjà été refusée.\n" +
                            "Vous pouvez envoyer une nouvelle demande si vous le souhaitez.";
                }
                showAlert(Alert.AlertType.WARNING, "Impossible d'annuler", message);
                return;
            }

            // Créer des variables finales pour les utiliser dans la lambda
            final ParticipationDemande demandeFinal = demande;
            final Activite activiteFinal = selectedActivite;

            // Demander confirmation
            Alert confirmAlert = new Alert(Alert.AlertType.CONFIRMATION);
            confirmAlert.setTitle("Confirmer l'annulation");
            confirmAlert.setHeaderText("Annuler votre demande de participation");
            confirmAlert.setContentText(
                    "Êtes-vous sûr de vouloir annuler votre demande pour l'activité:\n\n" +
                            "• " + activiteFinal.getNom() + "\n" +
                            "• " + activiteFinal.getLieu() + "\n" +
                            "• " + activiteFinal.getDateActivite() + "\n\n" +
                            "Cette action est irréversible."
            );

            confirmAlert.showAndWait().ifPresent(response -> {
                if (response == ButtonType.OK) {
                    try {
                        // ✅ Créer une notification pour le partenaire AVANT suppression
                        NotificationManager.creerNotificationAnnulation(
                                activiteFinal.getPartenaireId(),
                                demandeFinal,
                                activiteFinal
                        );

                        // Supprimer la demande de la base de données
                        demandeService.supprimer(demandeFinal.getId());

                        showAlert(Alert.AlertType.INFORMATION, "Succès",
                                "Votre demande a été annulée avec succès.\n\n" +
                                        "Vous pouvez envoyer une nouvelle demande à tout moment.");

                        // Réinitialiser l'interface
                        annulerBtn.setVisible(false);
                        annulerBtn.setManaged(false);
                        participerBtn.setDisable(false);
                        participerBtn.setText("Envoyer Demande");
                        participerBtn.setStyle("");
                        payerBtn.setDisable(true);

                        // Recharger les activités pour mettre à jour l'affichage
                        loadActivites();

                    } catch (SQLException e) {
                        showAlert(Alert.AlertType.ERROR, "Erreur",
                                "Erreur lors de l'annulation: " + e.getMessage());
                        e.printStackTrace();
                    }
                }
            });

        } catch (SQLException e) {
            showAlert(Alert.AlertType.ERROR, "Erreur",
                    "Erreur lors de la vérification de la demande: " + e.getMessage());
            e.printStackTrace();
        }
    }

    /**
     * Envoie un email au partenaire pour le notifier d'une nouvelle demande
     */
    private void envoyerEmailNouvelleDemandeAuPartenaire(ParticipationDemande demande, Activite activite) {
        new Thread(() -> {
            try {
                String partenaireEmail = activite.getPartenaireEmail();
                System.out.println("📧 Envoi de l'email au partenaire: " + partenaireEmail);

                // Vérifier que l'email du partenaire n'est pas null ou vide
                if (partenaireEmail == null || partenaireEmail.trim().isEmpty() || partenaireEmail.equals("null")) {
                    System.err.println("❌ Email du partenaire non disponible, impossible d'envoyer la notification");
                    return;
                }

                String subject = "🔔 Nouvelle demande de participation - " + activite.getNom();

                // Essayer d'envoyer en HTML d'abord
                String htmlBody = com.pi.utils.EmailService.createNewDemandeEmailHtmlForPartenaire(
                        activite.getPartenaireNom(),
                        demande.getClientNom(),
                        demande.getClientEmail(),
                        demande.getClientTelephone(),
                        activite.getNom(),
                        activite.getLieu(),
                        activite.getDateActivite().toString()
                );

                boolean sent = com.pi.utils.EmailService.sendHtmlEmail(
                        partenaireEmail,
                        subject,
                        htmlBody
                );

                if (!sent) {
                    // Si l'envoi HTML échoue, essayer en texte simple
                    String textBody = com.pi.utils.EmailService.createNewDemandeEmailForPartenaire(
                            activite.getPartenaireNom(),
                            demande.getClientNom(),
                            demande.getClientEmail(),
                            demande.getClientTelephone(),
                            activite.getNom(),
                            activite.getLieu(),
                            activite.getDateActivite().toString()
                    );

                    com.pi.utils.EmailService.sendEmail(
                            partenaireEmail,
                            subject,
                            textBody
                    );
                }

                System.out.println("✅ Email envoyé au partenaire avec succès");
            } catch (Exception e) {
                System.err.println("❌ Erreur lors de l'envoi de l'email au partenaire: " + e.getMessage());
                e.printStackTrace();
            }
        }).start();
    }

    @FXML
    private void effectuerPaiement() {
        if (selectedActivite == null) {
            showAlert(Alert.AlertType.WARNING, "Attention", "Veuillez sélectionner une activité");
            return;
        }

        try {
            // Récupérer la dernière demande du client pour cette activité
            var demandes = demandeService.getByClient(clientId);
            ParticipationDemande demande = null;
            for (ParticipationDemande d : demandes) {
                if (d.getActiviteId() == selectedActivite.getId() && !d.isPaiementEffectue()) {
                    demande = d;
                    break;
                }
            }

            if (demande == null) {
                showAlert(Alert.AlertType.WARNING, "Attention", "Veuillez d'abord envoyer une demande de participation");
                return;
            }

            // Vérifier que la demande est acceptée
            if (!demande.getStatut().equals("ACCEPTEE")) {
                showAlert(Alert.AlertType.WARNING, "Attention",
                        "Votre demande n'a pas encore été acceptée par le partenaire.\n\n" +
                                "Statut actuel: " + demande.getStatut() + "\n\n" +
                                "Veuillez attendre que le partenaire accepte votre demande avant de procéder au paiement.");
                return;
            }

            // Ouvrir l'interface de paiement
            ouvrirInterfacePaiement(demande);

        } catch (SQLException e) {
            showAlert(Alert.AlertType.ERROR, "Erreur", "Erreur lors du paiement: " + e.getMessage());
            e.printStackTrace();
        }
    }
    
    // ================= OUVRIR RÉCLAMATION =================
    @FXML
    private void ouvrirReclamation() {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/reclamatione.fxml"));
            Parent root = loader.load();

            Stage stage = new Stage();
            stage.setTitle("Soumettre une Réclamation");
            stage.setScene(new Scene(root));
            stage.setResizable(false);
            stage.initModality(Modality.APPLICATION_MODAL);
            stage.show();

        } catch (IOException e) {
            showAlert(Alert.AlertType.ERROR, "Erreur", "Impossible d'ouvrir l'interface de réclamation: " + e.getMessage());
            e.printStackTrace();
        }
    }

    private void ouvrirInterfacePaiement(ParticipationDemande demande) {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/InterfacePaiement.fxml"));
            Parent root = loader.load();

            InterfacePaiementController controller = loader.getController();
            controller.setDemande(demande, selectedActivite);

            Stage stage = new Stage();
            stage.setTitle("Paiement - " + selectedActivite.getNom());
            stage.setScene(new Scene(root));
            stage.setOnHidden(e -> {
                // Recharger les activités après le paiement
                loadActivites();
            });
            stage.show();

        } catch (IOException e) {
            e.printStackTrace();
            // Si l'interface de paiement n'existe pas encore, utiliser l'ancien système
            effectuerPaiementDirect(demande);
        }
    }

    private void effectuerPaiementDirect(ParticipationDemande demande) {
        try {
            // Créer le paiement
            Paiement paiement = new Paiement(
                    demande.getId(),
                    clientId,
                    selectedActivite.getId(),
                    selectedActivite.getPrix(),
                    methodePaiementCombo.getValue()
            );
            paiement.setStatut("COMPLETE");
            paiement.setReferenceTransaction("REF-" + UUID.randomUUID().toString().substring(0, 8));

            paiementService.ajouter(paiement);

            // Mettre à jour la demande
            demande.setPaiementEffectue(true);
            demandeService.modifier(demande);

            // Mettre à jour les places disponibles
            activiteService.updatePlacesDisponibles(
                    selectedActivite.getId(),
                    selectedActivite.getPlacesDisponibles() - 1
            );

            showAlert(Alert.AlertType.INFORMATION, "Succès",
                    "Paiement effectué avec succès!\n\n" +
                            "Référence: " + paiement.getReferenceTransaction() + "\n" +
                            "Montant: " + selectedActivite.getPrix() + " TND\n" +
                            "Méthode: " + methodePaiementCombo.getValue() + "\n\n" +
                            "Vous recevrez une confirmation par email.");

            loadActivites();
            clearClientFields();
            payerBtn.setDisable(true);
            participerBtn.setDisable(false);

        } catch (SQLException e) {
            showAlert(Alert.AlertType.ERROR, "Erreur", "Erreur lors du paiement: " + e.getMessage());
            e.printStackTrace();
        }
    }

    @FXML
    private void voirStatutDemande() {
        if (selectedActivite == null) {
            showAlert(Alert.AlertType.WARNING, "Attention", "Veuillez sélectionner une activité");
            return;
        }

        try {
            // Récupérer toutes les demandes du client pour cette activité
            var demandes = demandeService.getByClient(clientId);
            ParticipationDemande demande = null;

            for (ParticipationDemande d : demandes) {
                if (d.getActiviteId() == selectedActivite.getId()) {
                    demande = d;
                    break;
                }
            }

            if (demande == null) {
                showAlert(Alert.AlertType.INFORMATION, "Information",
                        "Vous n'avez pas encore envoyé de demande pour cette activité.\n\n" +
                                "Cliquez sur 'Participer' pour envoyer une demande.");
                return;
            }

            // Afficher le statut détaillé
            String statutMessage = "";
            Alert.AlertType alertType = Alert.AlertType.INFORMATION;

            switch (demande.getStatut()) {
                case "EN_ATTENTE":
                    alertType = Alert.AlertType.INFORMATION;
                    statutMessage = "⏳ DEMANDE EN ATTENTE\n\n" +
                            "Votre demande de participation a été envoyée avec succès.\n\n" +
                            "📋 Détails:\n" +
                            "• Activité: " + selectedActivite.getNom() + "\n" +
                            "• Date demande: " + demande.getDateDemande() + "\n" +
                            "• Statut: En attente de validation\n\n" +
                            "Le partenaire " + selectedActivite.getPartenaireNom() + " va examiner votre demande.\n" +
                            "Vous serez notifié dès qu'une décision sera prise.";

                    // Désactiver le bouton payer
                    payerBtn.setDisable(true);
                    participerBtn.setDisable(true);
                    break;

                case "ACCEPTEE":
                    alertType = Alert.AlertType.CONFIRMATION;
                    if (demande.isPaiementEffectue()) {
                        statutMessage = "✅ DEMANDE ACCEPTÉE - PAIEMENT EFFECTUÉ\n\n" +
                                "Félicitations! Votre participation est confirmée.\n\n" +
                                "📋 Détails:\n" +
                                "• Activité: " + selectedActivite.getNom() + "\n" +
                                "• Date demande: " + demande.getDateDemande() + "\n" +
                                "• Statut: Acceptée et payée\n" +
                                "• Paiement: Effectué ✓\n\n" +
                                "Vous recevrez un email de confirmation avec tous les détails.";
                    } else {
                        statutMessage = "✅ DEMANDE ACCEPTÉE\n\n" +
                                "Bonne nouvelle! Le partenaire a accepté votre demande.\n\n" +
                                "📋 Détails:\n" +
                                "• Activité: " + selectedActivite.getNom() + "\n" +
                                "• Date demande: " + demande.getDateDemande() + "\n" +
                                "• Statut: Acceptée\n" +
                                "• Paiement: En attente\n\n" +
                                "💳 Vous pouvez maintenant procéder au paiement en cliquant sur le bouton 'Payer'.";

                        // Activer le bouton payer
                        payerBtn.setDisable(false);
                        participerBtn.setDisable(true);
                    }
                    break;

                case "REFUSEE":
                    alertType = Alert.AlertType.ERROR;
                    statutMessage = "❌ DEMANDE REFUSÉE\n\n" +
                            "Malheureusement, votre demande a été refusée.\n\n" +
                            "📋 Détails:\n" +
                            "• Activité: " + selectedActivite.getNom() + "\n" +
                            "• Date demande: " + demande.getDateDemande() + "\n" +
                            "• Statut: Refusée\n\n" +
                            "Raisons possibles:\n" +
                            "• Places complètes\n" +
                            "• Critères non remplis\n" +
                            "• Activité annulée\n\n" +
                            "Vous pouvez contacter le partenaire pour plus d'informations:\n" +
                            "📧 " + selectedActivite.getPartenaireEmail() + "\n" +
                            "📞 " + selectedActivite.getPartenaireTelephone();

                    // Réactiver le bouton participer pour une nouvelle demande
                    payerBtn.setDisable(true);
                    participerBtn.setDisable(false);
                    break;

                default:
                    statutMessage = "Statut inconnu: " + demande.getStatut();
            }

            showAlert(alertType, "Statut de votre demande", statutMessage);

        } catch (SQLException e) {
            showAlert(Alert.AlertType.ERROR, "Erreur", "Erreur lors de la récupération du statut: " + e.getMessage());
            e.printStackTrace();
        }
    }

    private boolean validateClientFields() {
        boolean isValid = true;

        // Cacher tous les messages d'erreur d'abord
        hideAllErrors();

        // Réinitialiser les styles des champs
        nomClientField.setStyle("-fx-background-color: #F3F4F6;");
        emailClientField.setStyle("-fx-background-color: #F3F4F6;");
        telephoneClientField.setStyle("-fx-background-color: #F3F4F6;");
        methodePaiementCombo.setStyle("");

        // Validation du nom
        if (nomClientField.getText().isEmpty()) {
            showError(nomClientField, nomErrorLabel, "Le nom est obligatoire");
            isValid = false;
        }

        // Validation de l'email
        if (emailClientField.getText().isEmpty()) {
            showError(emailClientField, emailErrorLabel, "L'email est obligatoire");
            isValid = false;
        } else if (!emailClientField.getText().matches("^[A-Za-z0-9+_.-]+@(.+)$")) {
            showError(emailClientField, emailErrorLabel, "Cette adresse email est invalide");
            isValid = false;
        }

        // Validation du téléphone
        if (telephoneClientField.getText().isEmpty()) {
            showError(telephoneClientField, telephoneErrorLabel, "Le téléphone est obligatoire");
            isValid = false;
        } else if (!telephoneClientField.getText().matches("\\d{8,}")) {
            showError(telephoneClientField, telephoneErrorLabel, "Le numéro de téléphone doit contenir au moins 8 chiffres");
            isValid = false;
        }

        // Validation de la méthode de paiement
        if (methodePaiementCombo.getValue() == null || methodePaiementCombo.getValue().isEmpty()) {
            showError(methodePaiementCombo, paiementErrorLabel, "La méthode de paiement est obligatoire");
            isValid = false;
        }

        return isValid;
    }

    private void showError(javafx.scene.Node field, Label errorLabel, String message) {
        // Changer le style du champ
        if (field instanceof TextField) {
            field.setStyle("-fx-border-color: #C62828; -fx-border-width: 2px; -fx-background-color: #FFEBEE;");
        } else if (field instanceof ComboBox) {
            field.setStyle("-fx-border-color: #C62828; -fx-border-width: 2px;");
        }

        // Afficher le message d'erreur
        errorLabel.setText(message);
        errorLabel.setVisible(true);
        errorLabel.setManaged(true);
    }

    private void hideAllErrors() {
        nomErrorLabel.setVisible(false);
        nomErrorLabel.setManaged(false);
        emailErrorLabel.setVisible(false);
        emailErrorLabel.setManaged(false);
        telephoneErrorLabel.setVisible(false);
        telephoneErrorLabel.setManaged(false);
        paiementErrorLabel.setVisible(false);
        paiementErrorLabel.setManaged(false);
    }

    private void clearClientFields() {
        nomClientField.clear();
        emailClientField.clear();
        telephoneClientField.clear();
    }

    @FXML
    private void rechercherActivites() {
        String typeRecherche = searchTypeCombo.getValue();
        String lieuRecherche = searchLieuCombo.getValue();
        LocalDate dateRecherche = searchDatePicker.getValue();

        // Filtrer la liste complète
        ObservableList<Activite> activitesFiltrees = FXCollections.observableArrayList();

        for (Activite activite : activitesListeComplete) {
            boolean correspond = true;

            // Filtrer par type
            if (typeRecherche != null && !typeRecherche.equals("Tous les types")) {
                if (!activite.getType().equalsIgnoreCase(typeRecherche)) {
                    correspond = false;
                }
            }

            // Filtrer par lieu
            if (lieuRecherche != null && !lieuRecherche.equals("Tous les lieux")) {
                if (!activite.getLieu().equalsIgnoreCase(lieuRecherche)) {
                    correspond = false;
                }
            }

            // Filtrer par date
            if (dateRecherche != null) {
                if (activite.getDateActivite() == null || !activite.getDateActivite().equals(dateRecherche)) {
                    correspond = false;
                }
            }

            if (correspond) {
                activitesFiltrees.add(activite);
            }
        }

        // Mettre à jour la liste affichée
        activitesList.clear();
        activitesList.addAll(activitesFiltrees);
        displayActivitesCards();

        // Afficher un message si aucun résultat
        if (activitesFiltrees.isEmpty()) {
            showAlert(Alert.AlertType.INFORMATION, "Recherche",
                    "Aucune activité trouvée avec les critères sélectionnés.");
        }
    }

    @FXML
    private void reinitialiserRecherche() {
        // Réinitialiser les champs de recherche
        searchTypeCombo.setValue("Tous les types");
        searchLieuCombo.setValue("Tous les lieux");
        searchDatePicker.setValue(null);

        // Réafficher toutes les activités
        activitesList.clear();
        activitesList.addAll(activitesListeComplete);
        displayActivitesCards();
        activitesList.clear();
        activitesList.addAll(activitesListeComplete);
    }

    private void showAlert(Alert.AlertType type, String title, String content) {
        Alert alert = new Alert(type);
        alert.setTitle(title);
        alert.setHeaderText(null);
        alert.setContentText(content);
        alert.showAndWait();
    }
}
