package com.pi.entity;

import com.pi.entities.ProduitParent;
import com.pi.utils.mydatabase;
import java.sql.*;
import java.util.ArrayList;
import java.util.List;

public class ProduitParentService {
    Connection con;

    public ProduitParentService() {
        con = mydatabase.getInstance().getConnection();
    }

    // Ajouter un produit parent
    public int ajouter(ProduitParent produit) throws SQLException {
        String sql = "INSERT INTO produit_parent (partenaire_id, sous_categorie_id, nom, description, " +
                "description_courte, marque, materiau, poids_kg, dimensions_cm, image_principale, statut) " +
                "VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        PreparedStatement st = con.prepareStatement(sql, Statement.RETURN_GENERATED_KEYS);
        st.setInt(1, produit.getPartenaireId());
        st.setInt(2, produit.getSousCategorieId());
        st.setString(3, produit.getNom());
        st.setString(4, produit.getDescription());
        st.setString(5, produit.getDescriptionCourte());
        st.setString(6, produit.getMarque());
        st.setString(7, produit.getMateriau());
        st.setDouble(8, produit.getPoidsKg());
        st.setString(9, produit.getDimensionsCm());
        st.setString(10, produit.getImagePrincipale());
        st.setString(11, produit.getStatut());
        
        st.executeUpdate();
        
        ResultSet rs = st.getGeneratedKeys();
        if (rs.next()) {
            int id = rs.getInt(1);
            System.out.println("✅ Produit parent ajouté avec ID: " + id);
            return id;
        }
        return -1;
    }

    // Modifier un produit parent
    public void modifier(ProduitParent produit) throws SQLException {
        String sql = "UPDATE produit_parent SET sous_categorie_id = ?, nom = ?, description = ?, " +
                "description_courte = ?, marque = ?, materiau = ?, poids_kg = ?, dimensions_cm = ?, " +
                "image_principale = ?, statut = ? WHERE id = ? AND partenaire_id = ?";
        
        PreparedStatement st = con.prepareStatement(sql);
        st.setInt(1, produit.getSousCategorieId());
        st.setString(2, produit.getNom());
        st.setString(3, produit.getDescription());
        st.setString(4, produit.getDescriptionCourte());
        st.setString(5, produit.getMarque());
        st.setString(6, produit.getMateriau());
        st.setDouble(7, produit.getPoidsKg());
        st.setString(8, produit.getDimensionsCm());
        st.setString(9, produit.getImagePrincipale());
        st.setString(10, produit.getStatut());
        st.setInt(11, produit.getId());
        st.setInt(12, produit.getPartenaireId());
        
        int rows = st.executeUpdate();
        if (rows > 0) {
            System.out.println("✅ Produit parent modifié: " + produit.getNom());
        }
    }

    // Supprimer un produit parent
    public void supprimer(int id, int partenaireId) throws SQLException {
        String sql = "DELETE FROM produit_parent WHERE id = ? AND partenaire_id = ?";
        PreparedStatement st = con.prepareStatement(sql);
        st.setInt(1, id);
        st.setInt(2, partenaireId);
        st.executeUpdate();
        System.out.println("✅ Produit parent supprimé");
    }

    // Récupérer tous les produits d'un partenaire
    public List<ProduitParent> getByPartenaire(int partenaireId) throws SQLException {
        List<ProduitParent> produits = new ArrayList<>();
        String sql = "SELECT * FROM produit_parent WHERE partenaire_id = ? ORDER BY date_ajout DESC";
        PreparedStatement st = con.prepareStatement(sql);
        st.setInt(1, partenaireId);
        ResultSet rs = st.executeQuery();

        while (rs.next()) {
            produits.add(mapResultSet(rs));
        }
        return produits;
    }

    // Récupérer tous les produits (pour le catalogue client)
    public List<ProduitParent> afficher() throws SQLException {
        List<ProduitParent> produits = new ArrayList<>();
        String sql = "SELECT * FROM produit_parent ORDER BY date_ajout DESC";
        Statement st = con.createStatement();
        ResultSet rs = st.executeQuery(sql);

        while (rs.next()) {
            produits.add(mapResultSet(rs));
        }
        return produits;
    }

    // Récupérer par ID
    public ProduitParent getById(int id) throws SQLException {
        String sql = "SELECT * FROM produit_parent WHERE id = ?";
        PreparedStatement st = con.prepareStatement(sql);
        st.setInt(1, id);
        ResultSet rs = st.executeQuery();

        if (rs.next()) {
            return mapResultSet(rs);
        }
        return null;
    }

    // Récupérer par sous-catégorie
    public List<ProduitParent> getBySousCategorie(int sousCategorieId) throws SQLException {
        List<ProduitParent> produits = new ArrayList<>();
        String sql = "SELECT * FROM produit_parent WHERE sous_categorie_id = ? AND statut = 'ACTIF' ORDER BY nom";
        PreparedStatement st = con.prepareStatement(sql);
        st.setInt(1, sousCategorieId);
        ResultSet rs = st.executeQuery();

        while (rs.next()) {
            produits.add(mapResultSet(rs));
        }
        return produits;
    }

    // Rechercher
    public List<ProduitParent> rechercher(String mot, int partenaireId) throws SQLException {
        List<ProduitParent> produits = new ArrayList<>();
        String sql = "SELECT * FROM produit_parent WHERE partenaire_id = ? AND " +
                "(nom LIKE ? OR description LIKE ? OR marque LIKE ?)";
        
        PreparedStatement st = con.prepareStatement(sql);
        st.setInt(1, partenaireId);
        st.setString(2, "%" + mot + "%");
        st.setString(3, "%" + mot + "%");
        st.setString(4, "%" + mot + "%");
        ResultSet rs = st.executeQuery();

        while (rs.next()) {
            produits.add(mapResultSet(rs));
        }
        return produits;
    }

    // Mapper ResultSet vers ProduitParent
    private ProduitParent mapResultSet(ResultSet rs) throws SQLException {
        ProduitParent p = new ProduitParent();
        p.setId(rs.getInt("id"));
        p.setPartenaireId(rs.getInt("partenaire_id"));
        p.setSousCategorieId(rs.getInt("sous_categorie_id"));
        p.setNom(rs.getString("nom"));
        p.setDescription(rs.getString("description"));
        p.setDescriptionCourte(rs.getString("description_courte"));
        p.setMarque(rs.getString("marque"));
        p.setMateriau(rs.getString("materiau"));
        p.setPoidsKg(rs.getDouble("poids_kg"));
        p.setDimensionsCm(rs.getString("dimensions_cm"));
        p.setImagePrincipale(rs.getString("image_principale"));
        p.setDateAjout(rs.getTimestamp("date_ajout"));
        p.setStatut(rs.getString("statut"));
        return p;
    }
}
