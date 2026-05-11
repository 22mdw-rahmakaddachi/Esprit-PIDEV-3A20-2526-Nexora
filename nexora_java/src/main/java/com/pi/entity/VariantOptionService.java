package com.pi.entity;

import com.pi.entities.VariantOption;
import com.pi.utils.mydatabase;
import java.sql.*;
import java.util.ArrayList;
import java.util.List;

public class VariantOptionService {
    Connection con;

    public VariantOptionService() {
        con = mydatabase.getInstance().getConnection();
    }

    // Ajouter une association variant-option
    public void ajouter(VariantOption variantOption) throws SQLException {
        String sql = "INSERT INTO variant_option (variant_id, option_id) VALUES (?, ?)";
        PreparedStatement st = con.prepareStatement(sql);
        st.setInt(1, variantOption.getVariantId());
        st.setInt(2, variantOption.getOptionId());
        st.executeUpdate();
        System.out.println("✅ Association variant-option ajoutée");
    }

    // Supprimer une association
    public void supprimer(int id) throws SQLException {
        String sql = "DELETE FROM variant_option WHERE id = ?";
        PreparedStatement st = con.prepareStatement(sql);
        st.setInt(1, id);
        st.executeUpdate();
    }

    // Supprimer toutes les options d'un variant
    public void supprimerByVariant(int variantId) throws SQLException {
        String sql = "DELETE FROM variant_option WHERE variant_id = ?";
        PreparedStatement st = con.prepareStatement(sql);
        st.setInt(1, variantId);
        st.executeUpdate();
        System.out.println("✅ Options du variant supprimées");
    }

    // Récupérer les options d'un variant
    public List<Integer> getOptionsByVariant(int variantId) throws SQLException {
        List<Integer> optionIds = new ArrayList<>();
        String sql = "SELECT option_id FROM variant_option WHERE variant_id = ?";
        PreparedStatement st = con.prepareStatement(sql);
        st.setInt(1, variantId);
        ResultSet rs = st.executeQuery();

        while (rs.next()) {
            optionIds.add(rs.getInt("option_id"));
        }
        return optionIds;
    }

    // Récupérer les variants ayant une option spécifique
    public List<Integer> getVariantsByOption(int optionId) throws SQLException {
        List<Integer> variantIds = new ArrayList<>();
        String sql = "SELECT variant_id FROM variant_option WHERE option_id = ?";
        PreparedStatement st = con.prepareStatement(sql);
        st.setInt(1, optionId);
        ResultSet rs = st.executeQuery();

        while (rs.next()) {
            variantIds.add(rs.getInt("variant_id"));
        }
        return variantIds;
    }
}
