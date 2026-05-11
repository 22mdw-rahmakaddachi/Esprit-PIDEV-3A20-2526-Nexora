package com.pi.entity;

import com.pi.utils.mydatabase;

import java.sql.*;
import java.util.ArrayList;
import java.util.List;

public class ChatService {
    private final Connection con;

    public ChatService() {
        con = mydatabase.getInstance().getConnection();
    }

    // crée (ou récupère) une conversation entre user1 et user2
    public int getOrCreateConversation(int user1, int user2) throws SQLException {
        int a = Math.min(user1, user2);
        int b = Math.max(user1, user2);

        String findSql = "SELECT id FROM conversation WHERE user1_id=? AND user2_id=?";
        try (PreparedStatement ps = con.prepareStatement(findSql)) {
            ps.setInt(1, a);
            ps.setInt(2, b);
            ResultSet rs = ps.executeQuery();
            if (rs.next()) return rs.getInt("id");
        }

        String insertSql = "INSERT INTO conversation(user1_id,user2_id) VALUES (?,?)";
        try (PreparedStatement ps = con.prepareStatement(insertSql, Statement.RETURN_GENERATED_KEYS)) {
            ps.setInt(1, a);
            ps.setInt(2, b);
            ps.executeUpdate();
            ResultSet keys = ps.getGeneratedKeys();
            if (keys.next()) return keys.getInt(1);
        }
        throw new SQLException("Impossible de créer la conversation");
    }

    public void envoyerMessage(int conversationId, int senderId, String contenu) throws SQLException {
        String sql = "INSERT INTO message_prive(conversation_id,sender_id,contenu) VALUES (?,?,?)";
        try (PreparedStatement ps = con.prepareStatement(sql)) {
            ps.setInt(1, conversationId);
            ps.setInt(2, senderId);
            ps.setString(3, contenu);
            ps.executeUpdate();
        }
    }

    public List<String> lireMessages(int conversationId) throws SQLException {
        String sql =
                "SELECT m.id, m.sent_at, u.prenom, u.nom, m.contenu " +
                        "FROM message_prive m " +
                        "JOIN users u ON u.id = m.sender_id " +
                        "WHERE m.conversation_id=? " +
                        "ORDER BY m.id ASC";

        List<String> res = new ArrayList<>();
        try (PreparedStatement ps = con.prepareStatement(sql)) {
            ps.setInt(1, conversationId);
            ResultSet rs = ps.executeQuery();
            while (rs.next()) {
                res.add("[" + rs.getTimestamp("sent_at") + "] " +
                        rs.getString("prenom") + " " + rs.getString("nom") +
                        " : " + rs.getString("contenu"));
            }
        }
        return res;
    }
}
