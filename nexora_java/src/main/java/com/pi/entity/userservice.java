package com.pi.entity;

import com.pi.entities.user;
import com.pi.utils.mydatabase;

import java.sql.*;
import java.util.ArrayList;
import java.util.List;
import org.mindrot.jbcrypt.BCrypt;

public class userservice implements icrud<user> {

    private Connection con;

    public userservice() {
        con = mydatabase.getInstance().getConnection();
    }

    // ======================= AJOUT =======================

    @Override
    public void ajouter(user user) throws SQLException {

        String sql = "INSERT INTO users(prenom, nom, email, num, role, mdp) VALUES (?, ?, ?, ?, ?, ?)";

        PreparedStatement ps = con.prepareStatement(sql, Statement.RETURN_GENERATED_KEYS);
        String hashedPassword = BCrypt.hashpw(user.getMdp(), BCrypt.gensalt());
        ps.setString(1, user.getPrenom());
        ps.setString(2, user.getName());
        ps.setString(3, user.getEmail());
        ps.setInt(4, user.getNum());
        ps.setString(5, user.getRole());
        ps.setString(6, hashedPassword);

        ps.executeUpdate();

        // 🔴 récupérer ID généré automatiquement
        ResultSet rs = ps.getGeneratedKeys();

        if (rs.next()) {

            int generatedId = rs.getInt(1);

            // 🟢 si partenaire → ajouter dans table partenaire
            if (user.getRole().equalsIgnoreCase("partenaire")) {

                ajouterPartenaire(generatedId);
            }
        }

        System.out.println("Utilisateur ajouté avec succès");
    }

    // ======================= AJOUT PARTENAIRE =======================

    public void ajouterPartenaire(int userId) throws SQLException {

        String sql = "INSERT INTO partenaire " +
                "(user_id, nom_entreprise, responsable_nom, responsable_telephone) " +
                "VALUES (?, 'Entreprise', 'Responsable', '00000000')";

        PreparedStatement ps = con.prepareStatement(sql);
        ps.setInt(1, userId);
        ps.executeUpdate();

        System.out.println("Partenaire ajouté avec succès");
    }

    // ======================= SUPPRESSION =======================

    @Override
    public void supprimer(int id) throws SQLException {

        String sql = "DELETE FROM users WHERE id = ?";
        PreparedStatement ps = con.prepareStatement(sql);
        ps.setInt(1, id);
        ps.executeUpdate();

        System.out.println("Utilisateur supprimé avec succès");
    }

    public void supprimer(user u) throws SQLException {
        supprimer(u.getId());
    }



    // ======================= GESTION DES EMPREINTES =======================

    /**
     * Met à jour l'ID d'empreinte d'un utilisateur
     */
    public boolean assignFingerprintToUser(int userId, int fingerId) throws SQLException {
        String sql = "UPDATE users SET finger_id = ? WHERE id = ?";
        PreparedStatement ps = con.prepareStatement(sql);
        ps.setInt(1, fingerId);
        ps.setInt(2, userId);
        int rows = ps.executeUpdate();
        return rows > 0;
    }

    /**
     * Met à jour l'ID d'empreinte (méthode alternative)
     */
    public void updateFingerId(int userId, int fingerId) throws SQLException {
        String sql = "UPDATE users SET finger_id = ? WHERE id = ?";
        PreparedStatement ps = con.prepareStatement(sql);
        ps.setInt(1, fingerId);
        ps.setInt(2, userId);
        ps.executeUpdate();
    }

    /**
     * Recherche un utilisateur par son ID d'empreinte
     */


    /**
     * Vérifie si une empreinte est déjà utilisée
     */
    public boolean fingerIdExiste(int fingerId) {

        String sql = "SELECT COUNT(*) FROM users WHERE finger_id = ?";

        try (PreparedStatement ps = con.prepareStatement(sql)) {

            ps.setInt(1, fingerId);
            ResultSet rs = ps.executeQuery();

            if (rs.next()) {
                return rs.getInt(1) > 0;
            }

        } catch (SQLException e) {
            e.printStackTrace();
        }

        return false;
    }

    /**
     * Supprime l'empreinte d'un utilisateur
     */


    /**
     * Compte le nombre d'utilisateurs avec empreinte
     */


    // ======================= MODIFICATION =======================

    @Override
    public void modifier(user u) throws SQLException {

        String sql = "UPDATE users SET prenom=?, nom=?, email=?, num=?, role=?, mdp=? WHERE id=?";
        String hashedPassword = BCrypt.hashpw(u.getMdp(), BCrypt.gensalt());
        PreparedStatement ps = con.prepareStatement(sql);
        ps.setString(1, u.getPrenom());
        ps.setString(2, u.getName());
        ps.setString(3, u.getEmail());
        ps.setInt(4, u.getNum());
        ps.setString(5, u.getRole());
        ps.setString(6, hashedPassword);
        ps.setInt(7, u.getId());

        ps.executeUpdate();

        System.out.println("Utilisateur modifié avec succès");
    }

    // ======================= AFFICHAGE =======================

    @Override
    public List<user> afficher() throws SQLException {

        List<user> users = new ArrayList<>();

        String sql = "SELECT * FROM users";
        Statement st = con.createStatement();
        ResultSet rs = st.executeQuery(sql);

        while (rs.next()) {

            user user = new user();

            user.setId(rs.getInt("id"));
            user.setPrenom(rs.getString("prenom"));
            user.setName(rs.getString("nom"));
            user.setEmail(rs.getString("email"));
            user.setNum(rs.getInt("num"));
            user.setRole(rs.getString("role"));

            user.setMdp("********");

            users.add(user);
        }

        return users;
    }
    public void updateSecurity(user user) throws SQLException {

        String sql = "UPDATE users SET tentative=?, validation=?, block_until=?, block_level=? WHERE id=?";

        PreparedStatement ps = con.prepareStatement(sql);
        ps.setInt(1, user.getTentative());
        ps.setBoolean(2, user.isValidation());
        ps.setLong(3, user.getBlockUntil());
        ps.setInt(4, user.getBlockLevel());
        ps.setInt(5, user.getId());

        ps.executeUpdate();
    }

    public user getUserByFingerId(int fingerId) throws SQLException {

        user u = null;

        String req = "SELECT * FROM users WHERE finger_id = ?";

        try {

            PreparedStatement ps = con.prepareStatement(req);
            ps.setInt(1, fingerId);

            ResultSet rs = ps.executeQuery();

            if(rs.next()){

                u = new user();
                u.setId(rs.getInt("id"));
                u.setName(rs.getString("nom"));
                u.setEmail(rs.getString("email"));
                u.setMdp(rs.getString("mdp"));
                u.setFingerId(rs.getInt("finger_id"));
            }

        } catch (SQLException e) {
            e.printStackTrace();
        }

        return u;
    }
}