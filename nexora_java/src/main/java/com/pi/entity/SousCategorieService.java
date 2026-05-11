package com.pi.entity;

import com.pi.entities.SousCategorie;
import com.pi.utils.mydatabase;
import java.sql.*;
import java.util.ArrayList;
import java.util.List;

public class SousCategorieService {
    Connection con;

    public SousCategorieService() {
        con = mydatabase.getInstance().getConnection();
    }

    // Ajouter une sous-catégorie
    public void ajouter(SousCategorie sousCategorie) throws SQLException {
        String sql = "INSERT INTO sous_categorie (categorie_id, nom, description, image) VALUES (?, ?, ?, ?)";
        PreparedStatement st = con.prepareStatement(sql);
        st.setInt(1, sousCategorie.getCategorieId());
        st.setString(2, sousCategorie.getNom());
        st.setString(3, sousCategorie.getDescription());
        st.setString(4, sousCategorie.getImageUrl());
        st.executeUpdate();
        System.out.println("✅ Sous-catégorie ajoutée: " + sousCategorie.getNom());
    }

    // Modifier une sous-catégorie
    public void modifier(SousCategorie sousCategorie) throws SQLException {
        String sql = "UPDATE sous_categorie SET categorie_id = ?, nom = ?, description = ?, image = ? WHERE id = ?";
        PreparedStatement st = con.prepareStatement(sql);
        st.setInt(1, sousCategorie.getCategorieId());
        st.setString(2, sousCategorie.getNom());
        st.setString(3, sousCategorie.getDescription());
        st.setString(4, sousCategorie.getImageUrl());
        st.setInt(5, sousCategorie.getId());
        st.executeUpdate();
        System.out.println("✅ Sous-catégorie modifiée: " + sousCategorie.getNom());
    }

    // Supprimer une sous-catégorie
    public void supprimer(int id) throws SQLException {
        String sql = "DELETE FROM sous_categorie WHERE id = ?";
        PreparedStatement st = con.prepareStatement(sql);
        st.setInt(1, id);
        st.executeUpdate();
        System.out.println("✅ Sous-catégorie supprimée");
    }

    // Récupérer toutes les sous-catégories
    public List<SousCategorie> afficher() throws SQLException {
        List<SousCategorie> sousCategories = new ArrayList<>();
        String sql = "SELECT * FROM sous_categorie ORDER BY nom";
        Statement st = con.createStatement();
        ResultSet rs = st.executeQuery(sql);

        while (rs.next()) {
            SousCategorie sc = new SousCategorie();
            sc.setId(rs.getInt("id"));
            sc.setCategorieId(rs.getInt("categorie_id"));
            sc.setNom(rs.getString("nom"));
            sc.setDescription(rs.getString("description"));
            sc.setImageUrl(rs.getString("image"));
            sousCategories.add(sc);
        }
        return sousCategories;
    }

    // Récupérer par catégorie
    public List<SousCategorie> getByCategorie(int categorieId) throws SQLException {
        List<SousCategorie> sousCategories = new ArrayList<>();
        String sql = "SELECT * FROM sous_categorie WHERE categorie_id = ? ORDER BY nom";
        PreparedStatement st = con.prepareStatement(sql);
        st.setInt(1, categorieId);
        ResultSet rs = st.executeQuery();

        while (rs.next()) {
            SousCategorie sc = new SousCategorie();
            sc.setId(rs.getInt("id"));
            sc.setCategorieId(rs.getInt("categorie_id"));
            sc.setNom(rs.getString("nom"));
            sc.setDescription(rs.getString("description"));
            sc.setImageUrl(rs.getString("image"));
            sousCategories.add(sc);
        }
        return sousCategories;
    }

    // Récupérer par ID
    public SousCategorie getById(int id) throws SQLException {
        String sql = "SELECT * FROM sous_categorie WHERE id = ?";
        PreparedStatement st = con.prepareStatement(sql);
        st.setInt(1, id);
        ResultSet rs = st.executeQuery();

        if (rs.next()) {
            SousCategorie sc = new SousCategorie();
            sc.setId(rs.getInt("id"));
            sc.setCategorieId(rs.getInt("categorie_id"));
            sc.setNom(rs.getString("nom"));
            sc.setDescription(rs.getString("description"));
            sc.setImageUrl(rs.getString("image"));
            return sc;
        }
        return null;
    }
}
