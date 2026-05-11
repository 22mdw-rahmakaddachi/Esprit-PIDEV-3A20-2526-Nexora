package com.pi.entity;

import com.pi.entities.ProduitVariant;
import com.pi.utils.mydatabase;
import java.sql.*;
import java.util.ArrayList;
import java.util.List;

public class ProduitVariantService {
    Connection con;

    public ProduitVariantService() {
        con = mydatabase.getInstance().getConnection();
    }

    // Ajouter un variant
    public int ajouter(ProduitVariant variant) throws SQLException {
        String sql = "INSERT INTO produit_variant (produit_parent_id, sku, prix_achat, prix_vente, " +
                "prix_promo, quantite_stock, seuil_alerte, image_specifique, code_barres) " +
                "VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        PreparedStatement st = con.prepareStatement(sql, Statement.RETURN_GENERATED_KEYS);
        st.setInt(1, variant.getProduitParentId());
        st.setString(2, variant.getSku());
        st.setDouble(3, variant.getPrixAchat());
        st.setDouble(4, variant.getPrixVente());
        st.setDouble(5, variant.getPrixPromo());
        st.setInt(6, variant.getQuantiteStock());
        st.setInt(7, variant.getSeuilAlerte());
        st.setString(8, variant.getImageSpecifique());
        st.setString(9, variant.getCodeBarres());
        
        st.executeUpdate();
        
        ResultSet rs = st.getGeneratedKeys();
        if (rs.next()) {
            int id = rs.getInt(1);
            System.out.println("✅ Variant ajouté avec ID: " + id);
            return id;
        }
        return -1;
    }

    // Modifier un variant
    public void modifier(ProduitVariant variant) throws SQLException {
        String sql = "UPDATE produit_variant SET sku = ?, prix_achat = ?, prix_vente = ?, " +
                "prix_promo = ?, quantite_stock = ?, seuil_alerte = ?, image_specifique = ?, " +
                "code_barres = ? WHERE id = ?";
        
        PreparedStatement st = con.prepareStatement(sql);
        st.setString(1, variant.getSku());
        st.setDouble(2, variant.getPrixAchat());
        st.setDouble(3, variant.getPrixVente());
        st.setDouble(4, variant.getPrixPromo());
        st.setInt(5, variant.getQuantiteStock());
        st.setInt(6, variant.getSeuilAlerte());
        st.setString(7, variant.getImageSpecifique());
        st.setString(8, variant.getCodeBarres());
        st.setInt(9, variant.getId());
        
        st.executeUpdate();
        System.out.println("✅ Variant modifié: " + variant.getSku());
    }

    // Supprimer un variant
    public void supprimer(int id) throws SQLException {
        String sql = "DELETE FROM produit_variant WHERE id = ?";
        PreparedStatement st = con.prepareStatement(sql);
        st.setInt(1, id);
        st.executeUpdate();
        System.out.println("✅ Variant supprimé");
    }

    // Récupérer tous les variants d'un produit parent
    public List<ProduitVariant> getByProduitParent(int produitParentId) throws SQLException {
        List<ProduitVariant> variants = new ArrayList<>();
        String sql = "SELECT * FROM produit_variant WHERE produit_parent_id = ? ORDER BY sku";
        PreparedStatement st = con.prepareStatement(sql);
        st.setInt(1, produitParentId);
        ResultSet rs = st.executeQuery();

        while (rs.next()) {
            variants.add(mapResultSet(rs));
        }
        return variants;
    }

    // Récupérer par ID
    public ProduitVariant getById(int id) throws SQLException {
        String sql = "SELECT * FROM produit_variant WHERE id = ?";
        PreparedStatement st = con.prepareStatement(sql);
        st.setInt(1, id);
        ResultSet rs = st.executeQuery();

        if (rs.next()) {
            return mapResultSet(rs);
        }
        return null;
    }

    // Récupérer par SKU
    public ProduitVariant getBySku(String sku) throws SQLException {
        String sql = "SELECT * FROM produit_variant WHERE sku = ?";
        PreparedStatement st = con.prepareStatement(sql);
        st.setString(1, sku);
        ResultSet rs = st.executeQuery();

        if (rs.next()) {
            return mapResultSet(rs);
        }
        return null;
    }

    // Mettre à jour le stock
    public void updateStock(int id, int nouvelleQuantite) throws SQLException {
        String sql = "UPDATE produit_variant SET quantite_stock = ? WHERE id = ?";
        PreparedStatement st = con.prepareStatement(sql);
        st.setInt(1, nouvelleQuantite);
        st.setInt(2, id);
        st.executeUpdate();
        System.out.println("✅ Stock variant mis à jour: " + nouvelleQuantite);
    }

    // Mapper ResultSet vers ProduitVariant
    private ProduitVariant mapResultSet(ResultSet rs) throws SQLException {
        ProduitVariant v = new ProduitVariant();
        v.setId(rs.getInt("id"));
        v.setProduitParentId(rs.getInt("produit_parent_id"));
        v.setSku(rs.getString("sku"));
        v.setPrixAchat(rs.getDouble("prix_achat"));
        v.setPrixVente(rs.getDouble("prix_vente"));
        v.setPrixPromo(rs.getDouble("prix_promo"));
        v.setQuantiteStock(rs.getInt("quantite_stock"));
        v.setSeuilAlerte(rs.getInt("seuil_alerte"));
        v.setImageSpecifique(rs.getString("image_specifique"));
        v.setCodeBarres(rs.getString("code_barres"));
        v.setDateCreation(rs.getTimestamp("date_creation"));
        return v;
    }
}
