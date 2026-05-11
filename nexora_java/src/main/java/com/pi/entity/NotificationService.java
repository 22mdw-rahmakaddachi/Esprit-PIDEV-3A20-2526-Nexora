package com.pi.entity;

import com.pi.entities.Notification;
import com.pi.utils.mydatabase;

import java.sql.*;
import java.util.ArrayList;
import java.util.List;

public class NotificationService implements icrud<Notification> {
    private Connection connection;

    public NotificationService() {
        connection = mydatabase.getInstance().getConnection();
    }

    @Override
    public void ajouter(Notification notification) throws SQLException {
        String query = "INSERT INTO notification (user_id, user_type, type, titre, message, lue, date_creation, activite_id, demande_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        try (PreparedStatement pst = connection.prepareStatement(query)) {
            pst.setInt(1, notification.getUserId());
            pst.setString(2, notification.getUserType());
            pst.setString(3, notification.getType());
            pst.setString(4, notification.getTitre());
            pst.setString(5, notification.getMessage());
            pst.setBoolean(6, notification.isLue());
            pst.setTimestamp(7, Timestamp.valueOf(notification.getDateCreation()));
            pst.setInt(8, notification.getActiviteId());
            pst.setInt(9, notification.getDemandeId());
            pst.executeUpdate();
            System.out.println("✅ Notification créée: " + notification.getTitre());
        }
    }

    @Override
    public void modifier(Notification notification) throws SQLException {
        String query = "UPDATE notification SET lue=? WHERE id=?";
        try (PreparedStatement pst = connection.prepareStatement(query)) {
            pst.setBoolean(1, notification.isLue());
            pst.setInt(2, notification.getId());
            pst.executeUpdate();
        }
    }

    @Override
    public void supprimer(int id) throws SQLException {
        String query = "DELETE FROM notification WHERE id=?";
        try (PreparedStatement pst = connection.prepareStatement(query)) {
            pst.setInt(1, id);
            pst.executeUpdate();
        }
    }

    @Override
    public List<Notification> afficher() throws SQLException {
        List<Notification> notifications = new ArrayList<>();
        String query = "SELECT * FROM notification ORDER BY date_creation DESC";
        try (Statement st = connection.createStatement();
             ResultSet rs = st.executeQuery(query)) {
            while (rs.next()) {
                notifications.add(mapResultSetToNotification(rs));
            }
        }
        return notifications;
    }

    /**
     * Récupère les notifications d'un utilisateur
     */
    public List<Notification> getByUser(int userId, String userType) throws SQLException {
        List<Notification> notifications = new ArrayList<>();
        String query = "SELECT * FROM notification WHERE user_id=? AND user_type=? ORDER BY date_creation DESC";
        try (PreparedStatement pst = connection.prepareStatement(query)) {
            pst.setInt(1, userId);
            pst.setString(2, userType);
            try (ResultSet rs = pst.executeQuery()) {
                while (rs.next()) {
                    notifications.add(mapResultSetToNotification(rs));
                }
            }
        }
        return notifications;
    }

    /**
     * Récupère les notifications non lues d'un utilisateur
     */
    public List<Notification> getUnreadByUser(int userId, String userType) throws SQLException {
        List<Notification> notifications = new ArrayList<>();
        String query = "SELECT * FROM notification WHERE user_id=? AND user_type=? AND lue=false ORDER BY date_creation DESC";
        try (PreparedStatement pst = connection.prepareStatement(query)) {
            pst.setInt(1, userId);
            pst.setString(2, userType);
            try (ResultSet rs = pst.executeQuery()) {
                while (rs.next()) {
                    notifications.add(mapResultSetToNotification(rs));
                }
            }
        }
        return notifications;
    }

    /**
     * Compte les notifications non lues
     */
    public int countUnread(int userId, String userType) throws SQLException {
        String query = "SELECT COUNT(*) FROM notification WHERE user_id=? AND user_type=? AND lue=false";
        try (PreparedStatement pst = connection.prepareStatement(query)) {
            pst.setInt(1, userId);
            pst.setString(2, userType);
            try (ResultSet rs = pst.executeQuery()) {
                if (rs.next()) {
                    return rs.getInt(1);
                }
            }
        }
        return 0;
    }

    /**
     * Marque une notification comme lue
     */
    public void marquerCommeLue(int notificationId) throws SQLException {
        String query = "UPDATE notification SET lue=true WHERE id=?";
        try (PreparedStatement pst = connection.prepareStatement(query)) {
            pst.setInt(1, notificationId);
            pst.executeUpdate();
        }
    }

    /**
     * Marque toutes les notifications d'un utilisateur comme lues
     */
    public void marquerToutesCommeLues(int userId, String userType) throws SQLException {
        String query = "UPDATE notification SET lue=true WHERE user_id=? AND user_type=?";
        try (PreparedStatement pst = connection.prepareStatement(query)) {
            pst.setInt(1, userId);
            pst.setString(2, userType);
            pst.executeUpdate();
        }
    }

    /**
     * Supprime les anciennes notifications (plus de 30 jours)
     */
    public void supprimerAnciennesNotifications() throws SQLException {
        String query = "DELETE FROM notification WHERE date_creation < DATE_SUB(NOW(), INTERVAL 30 DAY)";
        try (Statement st = connection.createStatement()) {
            int deleted = st.executeUpdate(query);
            System.out.println("🗑️ " + deleted + " anciennes notifications supprimées");
        }
    }

    private Notification mapResultSetToNotification(ResultSet rs) throws SQLException {
        Notification notification = new Notification();
        notification.setId(rs.getInt("id"));
        notification.setUserId(rs.getInt("user_id"));
        notification.setUserType(rs.getString("user_type"));
        notification.setType(rs.getString("type"));
        notification.setTitre(rs.getString("titre"));
        notification.setMessage(rs.getString("message"));
        notification.setLue(rs.getBoolean("lue"));
        notification.setDateCreation(rs.getTimestamp("date_creation").toLocalDateTime());
        notification.setActiviteId(rs.getInt("activite_id"));
        notification.setDemandeId(rs.getInt("demande_id"));
        return notification;
    }
}
