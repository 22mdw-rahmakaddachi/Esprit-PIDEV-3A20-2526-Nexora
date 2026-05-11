package com.pi.utils;

import com.pi.entities.Notification;
import com.pi.entities.ParticipationDemande;
import com.pi.entities.Activite;
import com.pi.entity.NotificationService;

import java.sql.SQLException;

/**
 * Gestionnaire centralisé des notifications
 */
public class NotificationManager {

    private static NotificationService notificationService = new NotificationService();

    /**
     * Crée une notification pour une nouvelle demande de participation (pour le partenaire)
     */
    public static void creerNotificationNouvelleDemande(int partenaireId, ParticipationDemande demande, Activite activite) {
        try {
            // Récupérer le user_id du partenaire depuis la table partenaire
            int userId = getUserIdFromPartenaireId(partenaireId);

            if (userId <= 0) {
                System.err.println("❌ ERREUR: Impossible de trouver le user_id pour le partenaire #" + partenaireId);
                return;
            }

            Notification notification = new Notification(
                    userId,  // Utiliser le user_id, pas le partenaire_id
                    "PARTENAIRE",
                    "NOUVELLE_DEMANDE",
                    "🔔 Nouvelle demande de participation",
                    demande.getClientNom() + " souhaite participer à votre activité \"" + activite.getNom() + "\""
            );
            notification.setActiviteId(activite.getId());
            notification.setDemandeId(demande.getId());

            notificationService.ajouter(notification);
            System.out.println("✅ Notification créée pour le partenaire #" + partenaireId + " (user_id: " + userId + ")");
        } catch (SQLException e) {
            System.err.println("❌ Erreur création notification: " + e.getMessage());
        }
    }

    /**
     * Récupère le user_id depuis le partenaire_id
     */
    private static int getUserIdFromPartenaireId(int partenaireId) {
        try {
            java.sql.Connection conn = com.pi.utils.mydatabase.getInstance().getConnection();
            String query = "SELECT user_id FROM partenaire WHERE id = ?";
            java.sql.PreparedStatement pst = conn.prepareStatement(query);
            pst.setInt(1, partenaireId);
            java.sql.ResultSet rs = pst.executeQuery();

            if (rs.next()) {
                int userId = rs.getInt("user_id");
                System.out.println("🔍 DEBUG: partenaire_id " + partenaireId + " → user_id " + userId);
                return userId;
            }
        } catch (SQLException e) {
            System.err.println("❌ Erreur récupération user_id: " + e.getMessage());
        }
        return 0;
    }

    /**
     * Crée une notification d'acceptation (pour le client)
     */
    public static void creerNotificationAcceptation(int clientId, ParticipationDemande demande, Activite activite) {
        try {
            Notification notification = new Notification(
                    clientId,
                    "CLIENT",
                    "ACCEPTATION",
                    "✅ Demande acceptée !",
                    "Votre demande pour \"" + activite.getNom() + "\" a été acceptée. Vous pouvez maintenant procéder au paiement."
            );
            notification.setActiviteId(activite.getId());
            notification.setDemandeId(demande.getId());

            notificationService.ajouter(notification);
            System.out.println("✅ Notification d'acceptation créée pour le client #" + clientId);
        } catch (SQLException e) {
            System.err.println("❌ Erreur création notification: " + e.getMessage());
        }
    }

    /**
     * Crée une notification de refus (pour le client)
     */
    public static void creerNotificationRefus(int clientId, ParticipationDemande demande, Activite activite) {
        try {
            Notification notification = new Notification(
                    clientId,
                    "CLIENT",
                    "REFUS",
                    "❌ Demande refusée",
                    "Votre demande pour \"" + activite.getNom() + "\" a été refusée. Consultez d'autres activités disponibles."
            );
            notification.setActiviteId(activite.getId());
            notification.setDemandeId(demande.getId());

            notificationService.ajouter(notification);
            System.out.println("✅ Notification de refus créée pour le client #" + clientId);
        } catch (SQLException e) {
            System.err.println("❌ Erreur création notification: " + e.getMessage());
        }
    }

    /**
     * Crée une notification de paiement (pour le partenaire)
     */
    public static void creerNotificationPaiement(int partenaireId, ParticipationDemande demande, Activite activite, double montant) {
        try {
            // Récupérer le user_id du partenaire
            int userId = getUserIdFromPartenaireId(partenaireId);

            if (userId <= 0) {
                System.err.println("❌ ERREUR: Impossible de trouver le user_id pour le partenaire #" + partenaireId);
                return;
            }

            Notification notification = new Notification(
                    userId,
                    "PARTENAIRE",
                    "PAIEMENT",
                    "💰 Paiement reçu",
                    demande.getClientNom() + " a payé " + montant + " TND pour \"" + activite.getNom() + "\""
            );
            notification.setActiviteId(activite.getId());
            notification.setDemandeId(demande.getId());

            notificationService.ajouter(notification);
            System.out.println("✅ Notification de paiement créée pour le partenaire #" + partenaireId + " (user_id: " + userId + ")");
        } catch (SQLException e) {
            System.err.println("❌ Erreur création notification: " + e.getMessage());
        }
    }

    /**
     * Crée une notification d'annulation (pour le partenaire)
     * NOTE: demande_id est NULL car la demande sera supprimée
     */
    public static void creerNotificationAnnulation(int partenaireId, ParticipationDemande demande, Activite activite) {
        try {
            // Récupérer le user_id du partenaire
            int userId = getUserIdFromPartenaireId(partenaireId);

            if (userId <= 0) {
                System.err.println("❌ ERREUR: Impossible de trouver le user_id pour le partenaire #" + partenaireId);
                return;
            }

            Notification notification = new Notification(
                    userId,
                    "PARTENAIRE",
                    "ANNULATION",
                    "🚫 Demande annulée",
                    demande.getClientNom() + " a annulé sa demande pour \"" + activite.getNom() + "\""
            );
            notification.setActiviteId(activite.getId());
            // NE PAS définir demande_id car la demande sera supprimée
            // notification.setDemandeId(demande.getId());

            notificationService.ajouter(notification);
            System.out.println("✅ Notification d'annulation créée pour le partenaire #" + partenaireId + " (user_id: " + userId + ")");
        } catch (SQLException e) {
            System.err.println("❌ Erreur création notification: " + e.getMessage());
        }
    }

    /**
     * Récupère le nombre de notifications non lues
     */
    public static int getNombreNotificationsNonLues(int userId, String userType) {
        try {
            return notificationService.countUnread(userId, userType);
        } catch (SQLException e) {
            System.err.println("❌ Erreur comptage notifications: " + e.getMessage());
            return 0;
        }
    }

    /**
     * Marque toutes les notifications comme lues
     */
    public static void marquerToutesCommeLues(int userId, String userType) {
        try {
            notificationService.marquerToutesCommeLues(userId, userType);
            System.out.println("✅ Toutes les notifications marquées comme lues");
        } catch (SQLException e) {
            System.err.println("❌ Erreur marquage notifications: " + e.getMessage());
        }
    }

    /**
     * Crée une notification générique
     * @param userId ID de l'utilisateur
     * @param userType Type d'utilisateur (CLIENT ou PARTENAIRE)
     * @param type Type de notification (PAIEMENT, CONFIRMATION, etc.)
     * @param titre Titre de la notification
     * @param message Message de la notification
     * @param demandeId ID de la demande (peut être null)
     */
    public static void creerNotification(int userId, String userType, String type, String titre, String message, Integer demandeId) {
        try {
            Notification notification = new Notification(
                    userId,
                    userType,
                    type,
                    titre,
                    message
            );
            if (demandeId != null) {
                notification.setDemandeId(demandeId);
            }
            notificationService.ajouter(notification);
            System.out.println("✅ Notification créée pour " + userType + " ID: " + userId);
        } catch (SQLException e) {
            System.err.println("❌ Erreur création notification: " + e.getMessage());
            e.printStackTrace();
        }
    }
}
