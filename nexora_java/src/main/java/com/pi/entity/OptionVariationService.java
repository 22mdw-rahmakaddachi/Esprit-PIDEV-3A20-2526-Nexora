package com.pi.entity;

import com.pi.entities.OptionVariation;
import com.pi.utils.mydatabase;
import java.sql.*;
import java.util.ArrayList;
import java.util.List;

public class OptionVariationService {
    Connection con;

    public OptionVariationService() {
        con = mydatabase.getInstance().getConnection();
    }

    // Ajouter une option
    public void ajouter(OptionVariation option) throws SQLException {
        String sql = "INSERT INTO option_variation (attribut_id, valeur, code_hexadecimal, ordre_affichage) VALUES (?, ?, ?, ?)";
        PreparedStatement st = con.prepareStatement(sql);
        st.setInt(1, option.getAttributId());
        st.setString(2, option.getValeur());
        st.setString(3, option.getCodeHexadecimal());
        st.setInt(4, option.getOrdreAffichage());
        st.executeUpdate();
        System.out.println("✅ Option ajoutée: " + option.getValeur());
    }

    // Modifier une option
    public void modifier(OptionVariation option) throws SQLException {
        String sql = "UPDATE option_variation SET attribut_id = ?, valeur = ?, code_hexadecimal = ?, ordre_affichage = ? WHERE id = ?";
        PreparedStatement st = con.prepareStatement(sql);
        st.setInt(1, option.getAttributId());
        st.setString(2, option.getValeur());
        st.setString(3, option.getCodeHexadecimal());
        st.setInt(4, option.getOrdreAffichage());
        st.setInt(5, option.getId());
        st.executeUpdate();
        System.out.println("✅ Option modifiée: " + option.getValeur());
    }

    // Supprimer une option
    public void supprimer(int id) throws SQLException {
        String sql = "DELETE FROM option_variation WHERE id = ?";
        PreparedStatement st = con.prepareStatement(sql);
        st.setInt(1, id);
        st.executeUpdate();
        System.out.println("✅ Option supprimée");
    }

    // Récupérer toutes les options
    public List<OptionVariation> afficher() throws SQLException {
        List<OptionVariation> options = new ArrayList<>();
        String sql = "SELECT * FROM option_variation ORDER BY ordre_affichage, valeur";
        Statement st = con.createStatement();
        ResultSet rs = st.executeQuery(sql);

        while (rs.next()) {
            OptionVariation o = new OptionVariation();
            o.setId(rs.getInt("id"));
            o.setAttributId(rs.getInt("attribut_id"));
            o.setValeur(rs.getString("valeur"));
            o.setCodeHexadecimal(rs.getString("code_hexadecimal"));
            o.setOrdreAffichage(rs.getInt("ordre_affichage"));
            options.add(o);
        }
        return options;
    }

    // Récupérer par attribut
    public List<OptionVariation> getByAttribut(int attributId) throws SQLException {
        List<OptionVariation> options = new ArrayList<>();
        String sql = "SELECT * FROM option_variation WHERE attribut_id = ? ORDER BY ordre_affichage, valeur";
        PreparedStatement st = con.prepareStatement(sql);
        st.setInt(1, attributId);
        ResultSet rs = st.executeQuery();

        while (rs.next()) {
            OptionVariation o = new OptionVariation();
            o.setId(rs.getInt("id"));
            o.setAttributId(rs.getInt("attribut_id"));
            o.setValeur(rs.getString("valeur"));
            o.setCodeHexadecimal(rs.getString("code_hexadecimal"));
            o.setOrdreAffichage(rs.getInt("ordre_affichage"));
            options.add(o);
        }
        return options;
    }

    // Récupérer par ID
    public OptionVariation getById(int id) throws SQLException {
        String sql = "SELECT * FROM option_variation WHERE id = ?";
        PreparedStatement st = con.prepareStatement(sql);
        st.setInt(1, id);
        ResultSet rs = st.executeQuery();

        if (rs.next()) {
            OptionVariation o = new OptionVariation();
            o.setId(rs.getInt("id"));
            o.setAttributId(rs.getInt("attribut_id"));
            o.setValeur(rs.getString("valeur"));
            o.setCodeHexadecimal(rs.getString("code_hexadecimal"));
            o.setOrdreAffichage(rs.getInt("ordre_affichage"));
            return o;
        }
        return null;
    }
}
