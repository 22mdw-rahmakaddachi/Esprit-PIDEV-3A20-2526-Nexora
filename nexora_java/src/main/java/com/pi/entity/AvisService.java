package com.pi.entity;

import com.pi.entities.Avis;
import com.pi.entity.icruda;
import com.pi.utils.mydatabase;
import com.pi.validation.AvisValidator;

import java.sql.*;
import java.time.LocalDateTime;
import java.util.ArrayList;
import java.util.List;

public class AvisService implements icruda<Avis> {

    private final Connection con;
    private final AvisValidator validator = new AvisValidator();

    private Avis toUpdate;

    public AvisService() {
        con = mydatabase.getInstance().getConnection();
    }

    public void setToUpdate(Avis a) {
        this.toUpdate = a;
    }

    @Override
    public void ajouter(Avis a) throws SQLException {
        validator.validate(a);

        String sql = "INSERT INTO avis(user_id, rating, titre, contenu) VALUES (?,?,?,?)";
        try (PreparedStatement ps = con.prepareStatement(sql, Statement.RETURN_GENERATED_KEYS)) {
            ps.setInt(1, a.getUserId());
            ps.setInt(2, a.getRating());
            ps.setString(3, a.getTitre());
            ps.setString(4, a.getContenu());
            ps.executeUpdate();

            try (ResultSet rs = ps.getGeneratedKeys()) {
                if (rs.next()) a.setId(rs.getInt(1));
            }
        }
    }

    @Override
    public void supprimer(int id) throws SQLException {
        String sql = "DELETE FROM avis WHERE id=?";
        try (PreparedStatement ps = con.prepareStatement(sql)) {
            ps.setInt(1, id);
            ps.executeUpdate();
        }
    }

    @Override
    public void modifier(int id) throws SQLException {
        if (toUpdate == null) throw new SQLException("setToUpdate() doit être appelé avant modifier(id).");
        validator.validate(toUpdate);

        String sql = "UPDATE avis SET user_id=?, rating=?, titre=?, contenu=? WHERE id=?";
        try (PreparedStatement ps = con.prepareStatement(sql)) {
            ps.setInt(1, toUpdate.getUserId());
            ps.setInt(2, toUpdate.getRating());
            ps.setString(3, toUpdate.getTitre());
            ps.setString(4, toUpdate.getContenu());
            ps.setInt(5, id);
            ps.executeUpdate();
        } finally {
            toUpdate = null;
        }
    }

    @Override
    public List<Avis> afficher() throws SQLException {
        List<Avis> out = new ArrayList<>();
        String sql = "SELECT id, user_id, rating, titre, contenu, created_at FROM avis ORDER BY created_at DESC";
        try (PreparedStatement ps = con.prepareStatement(sql);
             ResultSet rs = ps.executeQuery()) {
            while (rs.next()) out.add(map(rs));
        }
        return out;
    }

    public List<Avis> afficherParUser(int userId) throws SQLException {
        List<Avis> out = new ArrayList<>();
        String sql = "SELECT id, user_id, rating, titre, contenu, created_at FROM avis WHERE user_id=? ORDER BY created_at DESC";
        try (PreparedStatement ps = con.prepareStatement(sql)) {
            ps.setInt(1, userId);
            try (ResultSet rs = ps.executeQuery()) {
                while (rs.next()) out.add(map(rs));
            }
        }
        return out;
    }

    private static Avis map(ResultSet rs) throws SQLException {
        Avis a = new Avis();
        a.setId(rs.getInt("id"));
        a.setUserId(rs.getInt("user_id"));
        a.setRating(rs.getInt("rating"));
        a.setTitre(rs.getString("titre"));
        a.setContenu(rs.getString("contenu"));
        Timestamp ts = rs.getTimestamp("created_at");
        a.setCreatedAt(ts != null ? ts.toLocalDateTime() : LocalDateTime.now());
        return a;
    }
}