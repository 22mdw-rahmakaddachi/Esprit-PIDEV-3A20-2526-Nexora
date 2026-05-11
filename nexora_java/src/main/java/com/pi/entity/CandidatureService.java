package com.pi.entity;

import com.pi.entities.Candidatureactivite;
import com.pi.utils.mydatabase;

import java.sql.*;
import java.util.ArrayList;
import java.util.List;

public class CandidatureService implements icruda<Candidatureactivite> {

    private final Connection con;
    private Candidatureactivite toUpdate;

    public CandidatureService() {
        con = mydatabase.getInstance().getConnection();
    }

    public void setToUpdate(Candidatureactivite c) {
        this.toUpdate = c;
    }

    @Override
    public void ajouter(Candidatureactivite c) throws SQLException {
        String sql = "INSERT INTO candidature(activite_id,user_id,message,statut) VALUES (?,?,?,?)";
        try (PreparedStatement ps = con.prepareStatement(sql)) {
            ps.setInt(1, c.getActiviteId());
            ps.setInt(2, c.getUserId());
            ps.setString(3, c.getMessage());
            ps.setString(4, c.getStatut() == null ? "EN_ATTENTE" : c.getStatut());
            ps.executeUpdate();
        }
    }

    @Override
    public void supprimer(int id) throws SQLException {
        String sql = "DELETE FROM candidature WHERE id=?";
        try (PreparedStatement ps = con.prepareStatement(sql)) {
            ps.setInt(1, id);
            ps.executeUpdate();
        }
    }

    @Override
    public void modifier(int id) throws SQLException {
        if (toUpdate == null) {
            throw new SQLException("Erreur: setToUpdate() doit être appelé avant modifier(id)");
        }

        String sql = "UPDATE candidature SET statut=?, message=? WHERE id=?";
        try (PreparedStatement ps = con.prepareStatement(sql)) {
            ps.setString(1, toUpdate.getStatut());
            ps.setString(2, toUpdate.getMessage());
            ps.setInt(3, id);
            ps.executeUpdate();
        } finally {
            toUpdate = null;
        }
    }

    @Override
    public List<Candidatureactivite> afficher() throws SQLException {
        List<Candidatureactivite> list = new ArrayList<>();
        String sql = "SELECT * FROM candidature ORDER BY id DESC";

        try (Statement st = con.createStatement();
             ResultSet rs = st.executeQuery(sql)) {

            while (rs.next()) {
                Candidatureactivite c = new Candidatureactivite();
                c.setId(rs.getInt("id"));
                c.setActiviteId(rs.getInt("activite_id"));
                c.setUserId(rs.getInt("user_id"));
                c.setMessage(rs.getString("message"));
                c.setStatut(rs.getString("statut"));
                list.add(c);
            }
        }
        return list;
    }

    // Bonus pratique (sans icrud)
    public void changerStatut(int candidatureId, String statut) throws SQLException {
        String sql = "UPDATE candidature SET statut=? WHERE id=?";
        try (PreparedStatement ps = con.prepareStatement(sql)) {
            ps.setString(1, statut);
            ps.setInt(2, candidatureId);
            ps.executeUpdate();
        }
    }
}
