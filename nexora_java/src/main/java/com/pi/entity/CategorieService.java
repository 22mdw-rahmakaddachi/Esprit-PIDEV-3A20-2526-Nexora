package com.pi.entity;

import com.pi.entities.Categorie;
import com.pi.utils.mydatabase;
import java.sql.*;
import java.util.ArrayList;
import java.util.List;

public class CategorieService {
    Connection con;

    public CategorieService() {
        con = mydatabase.getInstance().getConnection();
    }

    // Ajouter une catégorie
    public void ajouter(Categorie categorie) throws SQLException {
        String sql = "INSERT INTO categorie (nom, description, image, ordre_affichage) VALUES (?, ?, ?, ?)";
        PreparedStatement st = con.prepareStatement(sql);
        st.setString(1, categorie.getNom());
        st.setString(2, categorie.getDescription());
        st.setString(3, categorie.getImageUrl());
        st.setInt(4, categorie.getOrdre());
        st.executeUpdate();
        System.out.println("✅ Catégorie ajoutée: " + categorie.getNom());
    }

    // Modifier une catégorie
    public void modifier(Categorie categorie) throws SQLException {
        String sql = "UPDATE categorie SET nom = ?, description = ?, image = ?, ordre_affichage = ? WHERE id = ?";
        PreparedStatement st = con.prepareStatement(sql);
        st.setString(1, categorie.getNom());
        st.setString(2, categorie.getDescription());
        st.setString(3, categorie.getImageUrl());
        st.setInt(4, categorie.getOrdre());
        st.setInt(5, categorie.getId());
        st.executeUpdate();
        System.out.println("✅ Catégorie modifiée: " + categorie.getNom());
    }

    // Supprimer une catégorie
    public void supprimer(int id) throws SQLException {
        String sql = "DELETE FROM categorie WHERE id = ?";
        PreparedStatement st = con.prepareStatement(sql);
        st.setInt(1, id);
        st.executeUpdate();
        System.out.println("✅ Catégorie supprimée");
    }

    // Récupérer toutes les catégories
    public List<Categorie> afficher() throws SQLException {
        List<Categorie> categories = new ArrayList<>();
        String sql = "SELECT * FROM categorie ORDER BY ordre_affichage, nom";
        Statement st = con.createStatement();
        ResultSet rs = st.executeQuery(sql);

        while (rs.next()) {
            Categorie c = new Categorie();
            c.setId(rs.getInt("id"));
            c.setNom(rs.getString("nom"));
            c.setDescription(rs.getString("description"));
            c.setImageUrl(rs.getString("image"));
            c.setOrdre(rs.getInt("ordre_affichage"));
            categories.add(c);
        }
        return categories;
    }

    // Récupérer par ID
    public Categorie getById(int id) throws SQLException {
        String sql = "SELECT * FROM categorie WHERE id = ?";
        PreparedStatement st = con.prepareStatement(sql);
        st.setInt(1, id);
        ResultSet rs = st.executeQuery();

        if (rs.next()) {
            Categorie c = new Categorie();
            c.setId(rs.getInt("id"));
            c.setNom(rs.getString("nom"));
            c.setDescription(rs.getString("description"));
            c.setImageUrl(rs.getString("image"));
            c.setOrdre(rs.getInt("ordre_affichage"));
            return c;
        }
        return null;
    }
}
