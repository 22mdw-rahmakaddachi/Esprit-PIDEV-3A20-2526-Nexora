package controller;

import com.pi.entities.Activite;
import com.pi.entities.ParticipationDemande;
import com.pi.entity.ActiviteService;
import com.pi.entity.ParticipationDemandeService;
import com.pi.utils.NotificationManager;
import javafx.collections.FXCollections;
import javafx.collections.ObservableList;
import javafx.fxml.FXML;
import javafx.scene.control.*;
import javafx.scene.control.cell.PropertyValueFactory;

import java.sql.SQLException;
import java.time.LocalDateTime;

public class ParticipantsController {
    @FXML private Label activiteLabel;
    @FXML private TableView<ParticipationDemande> participantsTable;
    @FXML private TableColumn<ParticipationDemande, String> nomCol;
    @FXML private TableColumn<ParticipationDemande, String> emailCol;
    @FXML private TableColumn<ParticipationDemande, String> telephoneCol;
    @FXML private TableColumn<ParticipationDemande, String> statutCol;
    @FXML private TableColumn<ParticipationDemande, LocalDateTime> dateCol;
    @FXML private TableColumn<ParticipationDemande, Boolean> paiementCol;

    private ParticipationDemandeService demandeService;
    private ObservableList<ParticipationDemande> participantsList;
    private Activite activite;

    @FXML
    public void initialize() {
        demandeService = new ParticipationDemandeService();
        participantsList = FXCollections.observableArrayList();
        setupTable();
    }

    private void setupTable() {
        nomCol.setCellValueFactory(new PropertyValueFactory<>("clientNom"));
        emailCol.setCellValueFactory(new PropertyValueFactory<>("clientEmail"));
        telephoneCol.setCellValueFactory(new PropertyValueFactory<>("clientTelephone"));
        statutCol.setCellValueFactory(new PropertyValueFactory<>("statut"));
        dateCol.setCellValueFactory(new PropertyValueFactory<>("dateDemande"));
        paiementCol.setCellValueFactory(new PropertyValueFactory<>("paiementEffectue"));

        participantsTable.setItems(participantsList);
    }

    public void setActivite(Activite activite) {
        this.activite = activite;
        activiteLabel.setText("Participants pour: " + activite.getNom());
        loadParticipants();
    }

    private void loadParticipants() {
        try {
            participantsList.clear();
            participantsList.addAll(demandeService.getByActivite(activite.getId()));
        } catch (SQLException e) {
            showAlert(Alert.AlertType.ERROR, "Erreur", "Erreur lors du chargement: " + e.getMessage());
        }
    }

    @FXML
    private void accepterDemande() {
        ParticipationDemande selected = participantsTable.getSelectionModel().getSelectedItem();
        if (selected == null) {
            showAlert(Alert.AlertType.WARNING, "Attention", "Veuillez sélectionner une demande");
            return;
        }

        if (selected.getStatut().equals("ACCEPTEE")) {
            showAlert(Alert.AlertType.INFORMATION, "Information", "Cette demande est déjà acceptée");
            return;
        }

        // Vérifier qu'il reste des places disponibles
        if (activite.getPlacesDisponibles() <= 0) {
            showAlert(Alert.AlertType.ERROR, "Erreur",
                    "Plus de places disponibles pour cette activité!\n\n" +
                            "Places disponibles: 0\n" +
                            "Impossible d'accepter cette demande.");
            return;
        }

        try {
            // Accepter la demande
            selected.setStatut("ACCEPTEE");
            demandeService.modifier(selected);

            // ✅ Créer une notification pour le client
            NotificationManager.creerNotificationAcceptation(
                    selected.getClientId(),
                    selected,
                    activite
            );

            // Décrémenter les places disponibles
            int nouvellesPlaces = activite.getPlacesDisponibles() - 1;
            ActiviteService activiteService = new ActiviteService();
            activiteService.updatePlacesDisponibles(activite.getId(), nouvellesPlaces);
            activite.setPlacesDisponibles(nouvellesPlaces);

            // Mettre à jour le label
            activiteLabel.setText(String.format("Participants pour: %s (Places restantes: %d)",
                    activite.getNom(), nouvellesPlaces));

            // Envoyer l'email de notification
            envoyerEmailAcceptation(selected);

            String message = "Demande acceptée !\n\n" +
                    "Client: " + selected.getClientNom() + "\n" +
                    "Email: " + selected.getClientEmail() + "\n" +
                    "Téléphone: " + selected.getClientTelephone() + "\n\n" +
                    "Places restantes: " + nouvellesPlaces;

            if (nouvellesPlaces == 0) {
                message += "\n\n⚠️ ATTENTION: Plus de places disponibles!\n" +
                        "Cette activité est maintenant COMPLÈTE.";
            }

            showAlert(Alert.AlertType.INFORMATION, "Succès", message);
            loadParticipants();
        } catch (SQLException e) {
            showAlert(Alert.AlertType.ERROR, "Erreur", "Erreur: " + e.getMessage());
            e.printStackTrace();
        }
    }

    @FXML
    private void refuserDemande() {
        ParticipationDemande selected = participantsTable.getSelectionModel().getSelectedItem();
        if (selected == null) {
            showAlert(Alert.AlertType.WARNING, "Attention", "Veuillez sélectionner une demande");
            return;
        }

        if (selected.getStatut().equals("REFUSEE")) {
            showAlert(Alert.AlertType.INFORMATION, "Information", "Cette demande est déjà refusée");
            return;
        }

        Alert confirm = new Alert(Alert.AlertType.CONFIRMATION);
        confirm.setTitle("Confirmation");
        confirm.setHeaderText("Refuser la demande ?");
        confirm.setContentText("Êtes-vous sûr de vouloir refuser la demande de " + selected.getClientNom() + " ?");

        if (confirm.showAndWait().get() == ButtonType.OK) {
            try {
                selected.setStatut("REFUSEE");
                demandeService.modifier(selected);

                // ✅ Créer une notification pour le client
                NotificationManager.creerNotificationRefus(
                        selected.getClientId(),
                        selected,
                        activite
                );

                // Envoyer l'email de notification
                envoyerEmailRefus(selected);

                showAlert(Alert.AlertType.INFORMATION, "Succès",
                        "Demande refusée.\n\n" +
                                "Le client " + selected.getClientNom() + " a été notifié par email.\n\n" +
                                "Email: " + selected.getClientEmail());
                loadParticipants();
            } catch (SQLException e) {
                showAlert(Alert.AlertType.ERROR, "Erreur", "Erreur: " + e.getMessage());
            }
        }
    }

    private void envoyerEmailAcceptation(ParticipationDemande demande) {
        new Thread(() -> {
            try {
                String clientEmail = demande.getClientEmail();
                System.out.println("📧 Envoi de l'email d'acceptation à: " + clientEmail);

                // Vérifier que l'email du client n'est pas null ou vide
                if (clientEmail == null || clientEmail.trim().isEmpty() || clientEmail.equals("null")) {
                    System.err.println("❌ Email du client non disponible, impossible d'envoyer la notification");
                    return;
                }

                String subject = " Votre demande a été acceptée - " + activite.getNom();

                // Essayer d'envoyer en HTML d'abord
                String htmlBody = com.pi.utils.EmailService.createAcceptanceEmailHtml(
                        demande.getClientNom(),
                        activite.getNom(),
                        activite.getLieu(),
                        activite.getDateActivite().toString(),
                        activite.getPrix(),
                        activite.getPartenaireNom() != null ? activite.getPartenaireNom() : "Partenaire",
                        activite.getPartenaireEmail() != null ? activite.getPartenaireEmail() : "Non disponible",
                        activite.getPartenaireTelephone() != null ? activite.getPartenaireTelephone() : "Non disponible"
                );

                boolean sent = com.pi.utils.EmailService.sendHtmlEmail(
                        clientEmail,
                        subject,
                        htmlBody
                );

                if (!sent) {
                    // Si l'envoi HTML échoue, essayer en texte simple
                    String textBody = com.pi.utils.EmailService.createAcceptanceEmailBody(
                            demande.getClientNom(),
                            activite.getNom(),
                            activite.getLieu(),
                            activite.getDateActivite().toString(),
                            activite.getPrix(),
                            activite.getPartenaireNom() != null ? activite.getPartenaireNom() : "Partenaire",
                            activite.getPartenaireEmail() != null ? activite.getPartenaireEmail() : "Non disponible",
                            activite.getPartenaireTelephone() != null ? activite.getPartenaireTelephone() : "Non disponible"
                    );

                    com.pi.utils.EmailService.sendEmail(
                            clientEmail,
                            subject,
                            textBody
                    );
                }
            } catch (Exception e) {
                System.out.println("❌ Erreur lors de l'envoi de l'email: " + e.getMessage());
                e.printStackTrace();
            }
        }).start();
    }

    private void envoyerEmailRefus(ParticipationDemande demande) {
        new Thread(() -> {
            try {
                String clientEmail = demande.getClientEmail();
                System.out.println("📧 Envoi de l'email de refus à: " + clientEmail);

                // Vérifier que l'email du client n'est pas null ou vide
                if (clientEmail == null || clientEmail.trim().isEmpty() || clientEmail.equals("null")) {
                    System.err.println("❌ Email du client non disponible, impossible d'envoyer la notification");
                    return;
                }

                String subject = " Votre demande a été refusée - " + activite.getNom();

                // Essayer d'envoyer en HTML d'abord
                String htmlBody = com.pi.utils.EmailService.createRejectionEmailHtml(
                        demande.getClientNom(),
                        activite.getNom(),
                        activite.getLieu(),
                        activite.getDateActivite().toString(),
                        activite.getPartenaireNom() != null ? activite.getPartenaireNom() : "Partenaire",
                        activite.getPartenaireEmail() != null ? activite.getPartenaireEmail() : "Non disponible",
                        activite.getPartenaireTelephone() != null ? activite.getPartenaireTelephone() : "Non disponible"
                );

                boolean sent = com.pi.utils.EmailService.sendHtmlEmail(
                        clientEmail,
                        subject,
                        htmlBody
                );

                if (!sent) {
                    // Si l'envoi HTML échoue, essayer en texte simple
                    String textBody = com.pi.utils.EmailService.createRejectionEmailBody(
                            demande.getClientNom(),
                            activite.getNom(),
                            activite.getLieu(),
                            activite.getDateActivite().toString(),
                            activite.getPartenaireNom() != null ? activite.getPartenaireNom() : "Partenaire",
                            activite.getPartenaireEmail() != null ? activite.getPartenaireEmail() : "Non disponible",
                            activite.getPartenaireTelephone() != null ? activite.getPartenaireTelephone() : "Non disponible"
                    );

                    com.pi.utils.EmailService.sendEmail(
                            clientEmail,
                            subject,
                            textBody
                    );
                }
            } catch (Exception e) {
                System.out.println("❌ Erreur lors de l'envoi de l'email: " + e.getMessage());
                e.printStackTrace();
            }
        }).start();
    }

    private void showAlert(Alert.AlertType type, String title, String content) {
        Alert alert = new Alert(type);
        alert.setTitle(title);
        alert.setHeaderText(null);
        alert.setContentText(content);
        alert.showAndWait();
    }
}
