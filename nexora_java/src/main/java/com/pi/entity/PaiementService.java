package com.pi.entity;

import com.pi.entities.Paiement;
import com.pi.utils.mydatabase;

import java.sql.*;
import java.util.ArrayList;
import java.util.List;

public class PaiementService implements icrud<Paiement> {
    private Connection connection;

    public PaiementService() {
        connection = mydatabase.getInstance().getConnection();
    }

    @Override
    public void ajouter(Paiement paiement) throws SQLException {
        String query = "INSERT INTO paiement (demande_id, client_id, activite_id, montant, methode_paiement, statut, date_paiement, reference_transaction) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        try (PreparedStatement pst = connection.prepareStatement(query)) {
            pst.setInt(1, paiement.getDemandeId());
            pst.setInt(2, paiement.getClientId());
            pst.setInt(3, paiement.getActiviteId());
            pst.setDouble(4, paiement.getMontant());
            pst.setString(5, paiement.getMethodePaiement());
            pst.setString(6, paiement.getStatut());
            pst.setTimestamp(7, Timestamp.valueOf(paiement.getDatePaiement()));
            pst.setString(8, paiement.getReferenceTransaction());
            pst.executeUpdate();
        }
    }

    @Override
    public void modifier(Paiement paiement) throws SQLException {
        String query = "UPDATE paiement SET statut=?, reference_transaction=? WHERE id=?";
        try (PreparedStatement pst = connection.prepareStatement(query)) {
            pst.setString(1, paiement.getStatut());
            pst.setString(2, paiement.getReferenceTransaction());
            pst.setInt(3, paiement.getId());
            pst.executeUpdate();
        }
    }

    @Override
    public void supprimer(int id) throws SQLException {
        String query = "DELETE FROM paiement WHERE id=?";
        try (PreparedStatement pst = connection.prepareStatement(query)) {
            pst.setInt(1, id);
            pst.executeUpdate();
        }
    }

    @Override
    public List<Paiement> afficher() throws SQLException {
        List<Paiement> paiements = new ArrayList<>();
        String query = "SELECT * FROM paiement ORDER BY date_paiement DESC";
        try (Statement st = connection.createStatement();
             ResultSet rs = st.executeQuery(query)) {
            while (rs.next()) {
                paiements.add(mapResultSetToPaiement(rs));
            }
        }
        return paiements;
    }

    public Paiement getByDemande(int demandeId) throws SQLException {
        String query = "SELECT * FROM paiement WHERE demande_id=?";
        try (PreparedStatement pst = connection.prepareStatement(query)) {
            pst.setInt(1, demandeId);
            try (ResultSet rs = pst.executeQuery()) {
                if (rs.next()) {
                    return mapResultSetToPaiement(rs);
                }
            }
        }
        return null;
    }

    private Paiement mapResultSetToPaiement(ResultSet rs) throws SQLException {
        Paiement paiement = new Paiement();
        paiement.setId(rs.getInt("id"));
        paiement.setDemandeId(rs.getInt("demande_id"));
        paiement.setClientId(rs.getInt("client_id"));
        paiement.setActiviteId(rs.getInt("activite_id"));
        paiement.setMontant(rs.getDouble("montant"));
        paiement.setMethodePaiement(rs.getString("methode_paiement"));
        paiement.setStatut(rs.getString("statut"));
        paiement.setDatePaiement(rs.getTimestamp("date_paiement").toLocalDateTime());
        paiement.setReferenceTransaction(rs.getString("reference_transaction"));
        return paiement;
    }
}
