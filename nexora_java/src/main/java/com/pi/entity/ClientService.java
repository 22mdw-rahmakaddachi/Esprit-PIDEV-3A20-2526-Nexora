package com.pi.entity;

import com.pi.entities.Client;
import com.pi.utils.mydatabase;
import java.sql.*;

public class ClientService {
    private Connection connection;

    public ClientService() {
        connection = mydatabase.getInstance().getConnection();
    }

    // Récupérer un client par user_id
    public Client getByUserId(int userId) throws SQLException {
        String sql = "SELECT * FROM client WHERE user_id = ?";
        PreparedStatement ps = connection.prepareStatement(sql);
        ps.setInt(1, userId);
        ResultSet rs = ps.executeQuery();

        if (rs.next()) {
            Client c = new Client();
            c.setId(rs.getInt("id"));
            c.setUserId(rs.getInt("user_id"));
            c.setNom(rs.getString("nom"));
            c.setPrenom(rs.getString("prenom"));
            c.setEmail(rs.getString("email"));
            c.setTelephone(rs.getString("telephone"));
            c.setAdresse(rs.getString("adresse"));
            c.setDateInscription(rs.getTimestamp("date_inscription"));
            return c;
        }
        return null;
    }

    // Récupérer un client par son ID
    public Client getById(int id) throws SQLException {
        String sql = "SELECT * FROM client WHERE id = ?";
        PreparedStatement ps = connection.prepareStatement(sql);
        ps.setInt(1, id);
        ResultSet rs = ps.executeQuery();

        if (rs.next()) {
            Client c = new Client();
            c.setId(rs.getInt("id"));
            c.setUserId(rs.getInt("user_id"));
            c.setNom(rs.getString("nom"));
            c.setPrenom(rs.getString("prenom"));
            c.setEmail(rs.getString("email"));
            return c;
        }
        return null;
    }

    // Créer un nouveau client (après inscription)
    public void create(Client client) throws SQLException {
        String sql = "INSERT INTO client (user_id, nom, prenom, email, telephone, adresse) VALUES (?, ?, ?, ?, ?, ?)";
        PreparedStatement ps = connection.prepareStatement(sql);
        ps.setInt(1, client.getUserId());
        ps.setString(2, client.getNom());
        ps.setString(3, client.getPrenom());
        ps.setString(4, client.getEmail());
        ps.setString(5, client.getTelephone());
        ps.setString(6, client.getAdresse());
        ps.executeUpdate();
        ps.close();
    }
}
