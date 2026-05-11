package com.pi.entity;

import com.pi.entities.Commentaire;
import com.pi.utils.mydatabase;
import com.pi.validation.CommentaireValidator;

import java.sql.*;
import java.time.LocalDateTime;
import java.util.ArrayList;
import java.util.List;

public class CommentaireService implements icruda<Commentaire> {

    private final Connection con;
    private final CommentaireValidator validator = new CommentaireValidator();

    private Commentaire toUpdate;

    public CommentaireService() {
        con = mydatabase.getInstance().getConnection();
    }

    public void setToUpdate(Commentaire c) {
        this.toUpdate = c;
    }

    @Override
    public void ajouter(Commentaire c) throws SQLException {
        validator.validate(c);

        String sql = "INSERT INTO commentaire(avis_id, user_id, contenu) VALUES (?,?,?)";
        try (PreparedStatement ps = con.prepareStatement(sql, Statement.RETURN_GENERATED_KEYS)) {
            ps.setInt(1, c.getAvisId());
            ps.setInt(2, c.getUserId());
            ps.setString(3, c.getContenu());
            ps.executeUpdate();

            try (ResultSet rs = ps.getGeneratedKeys()) {
                if (rs.next()) c.setId(rs.getInt(1));
            }
        }
    }

    @Override
    public void supprimer(int id) throws SQLException {
        String sql = "DELETE FROM commentaire WHERE id=?";
        try (PreparedStatement ps = con.prepareStatement(sql)) {
            ps.setInt(1, id);
            ps.executeUpdate();
        }
    }

    @Override
    public void modifier(int id) throws SQLException {
        if (toUpdate == null) throw new SQLException("setToUpdate() doit être appelé avant modifier(id).");
        validator.validate(toUpdate);

        String sql = "UPDATE commentaire SET avis_id=?, user_id=?, contenu=? WHERE id=?";
        try (PreparedStatement ps = con.prepareStatement(sql)) {
            ps.setInt(1, toUpdate.getAvisId());
            ps.setInt(2, toUpdate.getUserId());
            ps.setString(3, toUpdate.getContenu());
            ps.setInt(4, id);
            ps.executeUpdate();
        } finally {
            toUpdate = null;
        }
    }

    @Override
    public List<Commentaire> afficher() throws SQLException {
        List<Commentaire> out = new ArrayList<>();
        String sql = "SELECT id, avis_id, user_id, contenu, created_at FROM commentaire ORDER BY created_at DESC";
        try (PreparedStatement ps = con.prepareStatement(sql);
             ResultSet rs = ps.executeQuery()) {
            while (rs.next()) out.add(map(rs));
        }
        return out;
    }

    public List<Commentaire> afficherParAvis(int avisId) throws SQLException {
        List<Commentaire> out = new ArrayList<>();
        String sql = "SELECT id, avis_id, user_id, contenu, created_at FROM commentaire WHERE avis_id=? ORDER BY created_at DESC";
        try (PreparedStatement ps = con.prepareStatement(sql)) {
            ps.setInt(1, avisId);
            try (ResultSet rs = ps.executeQuery()) {
                while (rs.next()) out.add(map(rs));
            }
        }
        return out;
    }

    private static Commentaire map(ResultSet rs) throws SQLException {
        Commentaire c = new Commentaire();
        c.setId(rs.getInt("id"));
        c.setAvisId(rs.getInt("avis_id"));
        c.setUserId(rs.getInt("user_id"));
        c.setContenu(rs.getString("contenu"));
        Timestamp ts = rs.getTimestamp("created_at");
        c.setCreatedAt(ts != null ? ts.toLocalDateTime() : LocalDateTime.now());
        return c;
    }
}