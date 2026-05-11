package com.pi.entity;

import com.pi.entities.ParticipationDemande;
import com.pi.utils.mydatabase;

import java.sql.*;
import java.util.ArrayList;
import java.util.List;

public class ParticipationDemandeService implements icrud<ParticipationDemande> {
    private Connection connection;

    public ParticipationDemandeService() {
        connection = mydatabase.getInstance().getConnection();
    }

    @Override
    public void ajouter(ParticipationDemande demande) throws SQLException {
        String query = "INSERT INTO participation_demande (activite_id, client_id, client_nom, client_email, client_telephone, statut, date_demande, paiement_effectue) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        try (PreparedStatement pst = connection.prepareStatement(query)) {
            pst.setInt(1, demande.getActiviteId());
            pst.setInt(2, demande.getClientId());
            pst.setString(3, demande.getClientNom());
            pst.setString(4, demande.getClientEmail());
            pst.setString(5, demande.getClientTelephone());
            pst.setString(6, demande.getStatut());
            pst.setTimestamp(7, Timestamp.valueOf(demande.getDateDemande()));
            pst.setBoolean(8, demande.isPaiementEffectue());
            pst.executeUpdate();
        }
    }

    @Override
    public void modifier(ParticipationDemande demande) throws SQLException {
        String query = "UPDATE participation_demande SET statut=?, paiement_effectue=? WHERE id=?";
        try (PreparedStatement pst = connection.prepareStatement(query)) {
            pst.setString(1, demande.getStatut());
            pst.setBoolean(2, demande.isPaiementEffectue());
            pst.setInt(3, demande.getId());
            pst.executeUpdate();
        }
    }

    @Override
    public void supprimer(int id) throws SQLException {
        String query = "DELETE FROM participation_demande WHERE id=?";
        try (PreparedStatement pst = connection.prepareStatement(query)) {
            pst.setInt(1, id);
            pst.executeUpdate();
        }
    }

    @Override
    public List<ParticipationDemande> afficher() throws SQLException {
        List<ParticipationDemande> demandes = new ArrayList<>();
        String query = "SELECT * FROM participation_demande ORDER BY date_demande DESC";
        try (Statement st = connection.createStatement();
             ResultSet rs = st.executeQuery(query)) {
            while (rs.next()) {
                demandes.add(mapResultSetToDemande(rs));
            }
        }
        return demandes;
    }

    public List<ParticipationDemande> getByActivite(int activiteId) throws SQLException {
        List<ParticipationDemande> demandes = new ArrayList<>();
        String query = "SELECT * FROM participation_demande WHERE activite_id=? ORDER BY date_demande DESC";
        try (PreparedStatement pst = connection.prepareStatement(query)) {
            pst.setInt(1, activiteId);
            try (ResultSet rs = pst.executeQuery()) {
                while (rs.next()) {
                    demandes.add(mapResultSetToDemande(rs));
                }
            }
        }
        return demandes;
    }

    public List<ParticipationDemande> getByClient(int clientId) throws SQLException {
        List<ParticipationDemande> demandes = new ArrayList<>();
        String query = "SELECT * FROM participation_demande WHERE client_id=? ORDER BY date_demande DESC";
        try (PreparedStatement pst = connection.prepareStatement(query)) {
            pst.setInt(1, clientId);
            try (ResultSet rs = pst.executeQuery()) {
                while (rs.next()) {
                    demandes.add(mapResultSetToDemande(rs));
                }
            }
        }
        return demandes;
    }

    public ParticipationDemande getById(int id) throws SQLException {
        String query = "SELECT * FROM participation_demande WHERE id=?";
        try (PreparedStatement pst = connection.prepareStatement(query)) {
            pst.setInt(1, id);
            try (ResultSet rs = pst.executeQuery()) {
                if (rs.next()) {
                    return mapResultSetToDemande(rs);
                }
            }
        }
        return null;
    }

    private ParticipationDemande mapResultSetToDemande(ResultSet rs) throws SQLException {
        ParticipationDemande demande = new ParticipationDemande();
        demande.setId(rs.getInt("id"));
        demande.setActiviteId(rs.getInt("activite_id"));
        demande.setClientId(rs.getInt("client_id"));
        demande.setClientNom(rs.getString("client_nom"));
        demande.setClientEmail(rs.getString("client_email"));
        demande.setClientTelephone(rs.getString("client_telephone"));
        demande.setStatut(rs.getString("statut"));
        demande.setDateDemande(rs.getTimestamp("date_demande").toLocalDateTime());
        demande.setPaiementEffectue(rs.getBoolean("paiement_effectue"));
        return demande;
    }
}
