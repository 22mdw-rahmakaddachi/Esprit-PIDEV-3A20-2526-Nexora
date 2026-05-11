package com.pi.entity;

import com.pi.entities.AttributVariation;
import com.pi.utils.mydatabase;
import java.sql.*;
import java.util.ArrayList;
import java.util.List;

public class AttributVariationService {
    Connection con;

    public AttributVariationService() {
        con = mydatabase.getInstance().getConnection();
    }

    // Ajouter un attribut
    public void ajouter(AttributVariation attribut) throws SQLException {
        String sql = "INSERT INTO attribut_variation (nom, type_affichage) VALUES (?, ?)";
        PreparedStatement st = con.prepareStatement(sql);
        st.setString(1, attribut.getNom());
        st.setString(2, attribut.getTypeAffichage());
        st.executeUpdate();
        System.out.println("✅ Attribut ajouté: " + attribut.getNom());
    }

    // Modifier un attribut
    public void modifier(AttributVariation attribut) throws SQLException {
        String sql = "UPDATE attribut_variation SET nom = ?, type_affichage = ? WHERE id = ?";
        PreparedStatement st = con.prepareStatement(sql);
        st.setString(1, attribut.getNom());
        st.setString(2, attribut.getTypeAffichage());
        st.setInt(3, attribut.getId());
        st.executeUpdate();
        System.out.println("✅ Attribut modifié: " + attribut.getNom());
    }

    // Supprimer un attribut
    public void supprimer(int id) throws SQLException {
        String sql = "DELETE FROM attribut_variation WHERE id = ?";
        PreparedStatement st = con.prepareStatement(sql);
        st.setInt(1, id);
        st.executeUpdate();
        System.out.println("✅ Attribut supprimé");
    }

    // Récupérer tous les attributs
    public List<AttributVariation> afficher() throws SQLException {
        List<AttributVariation> attributs = new ArrayList<>();
        String sql = "SELECT * FROM attribut_variation ORDER BY nom";
        Statement st = con.createStatement();
        ResultSet rs = st.executeQuery(sql);

        while (rs.next()) {
            AttributVariation a = new AttributVariation();
            a.setId(rs.getInt("id"));
            a.setNom(rs.getString("nom"));
            a.setTypeAffichage(rs.getString("type_affichage"));
            attributs.add(a);
        }
        return attributs;
    }

    // Récupérer par ID
    public AttributVariation getById(int id) throws SQLException {
        String sql = "SELECT * FROM attribut_variation WHERE id = ?";
        PreparedStatement st = con.prepareStatement(sql);
        st.setInt(1, id);
        ResultSet rs = st.executeQuery();

        if (rs.next()) {
            AttributVariation a = new AttributVariation();
            a.setId(rs.getInt("id"));
            a.setNom(rs.getString("nom"));
            a.setTypeAffichage(rs.getString("type_affichage"));
            return a;
        }
        return null;
    }
}
