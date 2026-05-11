// src/main/java/com/pi/entity/product/ProductService.java
package com.pi.entity;

import com.pi.entity.icruda;
import com.pi.entities.Product;
import com.pi.utils.mydatabase;
import java.sql.*;
import java.util.ArrayList;
import java.util.List;

public class ProductService implements icruda<Product> {
    Connection con;

    public ProductService() {
        con = mydatabase.getInstance().getConnection();
    }

    // 1. AJOUTER
    @Override
    public void ajouter(Product produits) throws SQLException {
        String sql = "INSERT INTO produits (nom, description, prix, quantite, partenaire_id, categorie, statut, image_url) " +
                "VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        PreparedStatement st = con.prepareStatement(sql);
        st.setString(1, produits.getNom());
        st.setString(2, produits.getDescription());
        st.setDouble(3, produits.getPrix());
        st.setInt(4, produits.getQuantite());
        st.setInt(5, produits.getPartenaireId());  // ← Utilise l'ID de l'utilisateur connecté
        st.setString(6, produits.getCategorie());
        st.setString(7, produits.getStatut());
        st.setString(8, produits.getImageUrl());

        st.executeUpdate();
        System.out.println("✅ Produit ajouté pour le partenaire ID: " + produits.getPartenaireId());
    }

    // 2. SUPPRIMER (sans vérification - À ÉVITER)
    @Override
    public void supprimer(int id) throws SQLException {
        String sql = "DELETE FROM produits WHERE id = ?";
        PreparedStatement st = con.prepareStatement(sql);
        st.setInt(1, id);

        int rows = st.executeUpdate();
        if (rows > 0) {
            System.out.println("✅ Produit supprimé (ID: " + id + ")");
        }
    }

    // 3. MODIFIER (sans vérification - À ÉVITER)
    @Override
    public void modifier(int id) throws SQLException {
        String sql = "UPDATE produits SET statut = 'inactif' WHERE id = ?";
        PreparedStatement st = con.prepareStatement(sql);
        st.setInt(1, id);
        st.executeUpdate();
        System.out.println("✅ Statut du produit #" + id + " modifié en 'inactif'");
    }

    // 4. AFFICHER tous les produits (admin)
    @Override
    public List<Product> afficher() throws SQLException {
        List<Product> produits = new ArrayList<>();
        String sql = "SELECT * FROM produits ORDER BY id DESC";

        Statement st = con.createStatement();
        ResultSet rs = st.executeQuery(sql);

        while (rs.next()) {
            Product p = new Product();
            p.setId(rs.getInt("id"));
            p.setNom(rs.getString("nom"));
            p.setDescription(rs.getString("description"));
            p.setPrix(rs.getDouble("prix"));
            p.setQuantite(rs.getInt("quantite"));
            p.setPartenaireId(rs.getInt("partenaire_id"));
            p.setCategorie(rs.getString("categorie"));
            p.setStatut(rs.getString("statut"));
            p.setImageUrl(rs.getString("image_url"));

            produits.add(p);
        }

        return produits;
    }

    // MÉTHODES SUPPLEMENTAIRES

    // Modifier produit complet (AVEC VÉRIFICATION PARTENAIRE)
    public void modifierProduitPartenaire(Product product, int partenaireId) throws SQLException {
        String sql = "UPDATE produits SET nom = ?, description = ?, prix = ?, " +
                "quantite = ?, categorie = ?, statut = ?, image_url = ? WHERE id = ? AND partenaire_id = ?";

        PreparedStatement st = con.prepareStatement(sql);
        st.setString(1, product.getNom());
        st.setString(2, product.getDescription());
        st.setDouble(3, product.getPrix());
        st.setInt(4, product.getQuantite());
        st.setString(5, product.getCategorie());
        st.setString(6, product.getStatut());
        st.setString(7, product.getImageUrl());
        st.setInt(8, product.getId());
        st.setInt(9, partenaireId); // ← Vérifie que le produit appartient au partenaire

        int rows = st.executeUpdate();
        if (rows > 0) {
            System.out.println("✅ Produit modifié: " + product.getNom());
        } else {
            System.out.println("❌ Produit non trouvé ou vous n'êtes pas autorisé");
        }
    }

    // Modifier produit complet (version originale - sans vérification)
    public void modifierProduit(Product product) throws SQLException {
        String sql = "UPDATE produits SET nom = ?, description = ?, prix = ?, " +
                "quantite = ?, categorie = ?, statut = ? WHERE id = ?";

        PreparedStatement st = con.prepareStatement(sql);
        st.setString(1, product.getNom());
        st.setString(2, product.getDescription());
        st.setDouble(3, product.getPrix());
        st.setInt(4, product.getQuantite());
        st.setString(5, product.getCategorie());
        st.setString(6, product.getStatut());
        st.setInt(7, product.getId());

        int rows = st.executeUpdate();
        if (rows > 0) {
            System.out.println("✅ Produit modifié: " + product.getNom());
        }
    }

    // Récupérer par ID (sans vérification partenaire)
    public Product getById(int id) throws SQLException {
        String sql = "SELECT * FROM produits WHERE id = ?";
        PreparedStatement st = con.prepareStatement(sql);
        st.setInt(1, id);
        ResultSet rs = st.executeQuery();

        if (rs.next()) {
            Product p = new Product();
            p.setId(rs.getInt("id"));
            p.setNom(rs.getString("nom"));
            p.setDescription(rs.getString("description"));
            p.setPrix(rs.getDouble("prix"));
            p.setQuantite(rs.getInt("quantite"));
            p.setPartenaireId(rs.getInt("partenaire_id"));
            p.setCategorie(rs.getString("categorie"));
            p.setStatut(rs.getString("statut"));
            return p;
        }
        return null;
    }

    // Récupérer par ID avec vérification partenaire
    public Product getByIdPartenaire(int id, int partenaireId) throws SQLException {
        String sql = "SELECT * FROM produits WHERE id = ? AND partenaire_id = ?";
        PreparedStatement st = con.prepareStatement(sql);
        st.setInt(1, id);
        st.setInt(2, partenaireId);
        ResultSet rs = st.executeQuery();

        if (rs.next()) {
            Product p = new Product();
            p.setId(rs.getInt("id"));
            p.setNom(rs.getString("nom"));
            p.setDescription(rs.getString("description"));
            p.setPrix(rs.getDouble("prix"));
            p.setQuantite(rs.getInt("quantite"));
            p.setPartenaireId(rs.getInt("partenaire_id"));
            p.setCategorie(rs.getString("categorie"));
            p.setStatut(rs.getString("statut"));
            return p;
        }
        return null;
    }

    // Rechercher (tous les produits)
    public List<Product> rechercher(String mot) throws SQLException {
        List<Product> produits = new ArrayList<>();
        String sql = "SELECT * FROM produits WHERE nom LIKE ? OR description LIKE ? OR categorie LIKE ?";

        PreparedStatement st = con.prepareStatement(sql);
        st.setString(1, "%" + mot + "%");
        st.setString(2, "%" + mot + "%");
        st.setString(3, "%" + mot + "%");

        ResultSet rs = st.executeQuery();

        while (rs.next()) {
            Product p = new Product();
            p.setId(rs.getInt("id"));
            p.setNom(rs.getString("nom"));
            p.setDescription(rs.getString("description"));
            p.setPrix(rs.getDouble("prix"));
            p.setQuantite(rs.getInt("quantite"));
            p.setPartenaireId(rs.getInt("partenaire_id"));
            p.setCategorie(rs.getString("categorie"));
            p.setStatut(rs.getString("statut"));

            produits.add(p);
        }

        return produits;
    }

    // Rechercher les produits d'un partenaire spécifique
    public List<Product> rechercherParPartenaire(String mot, int partenaireId) throws SQLException {
        List<Product> produits = new ArrayList<>();
        String sql = "SELECT * FROM produits WHERE partenaire_id = ? AND (nom LIKE ? OR description LIKE ? OR categorie LIKE ?)";

        PreparedStatement st = con.prepareStatement(sql);
        st.setInt(1, partenaireId);
        st.setString(2, "%" + mot + "%");
        st.setString(3, "%" + mot + "%");
        st.setString(4, "%" + mot + "%");

        ResultSet rs = st.executeQuery();

        while (rs.next()) {
            Product p = new Product();
            p.setId(rs.getInt("id"));
            p.setNom(rs.getString("nom"));
            p.setDescription(rs.getString("description"));
            p.setPrix(rs.getDouble("prix"));
            p.setQuantite(rs.getInt("quantite"));
            p.setPartenaireId(rs.getInt("partenaire_id"));
            p.setCategorie(rs.getString("categorie"));
            p.setStatut(rs.getString("statut"));

            produits.add(p);
        }

        return produits;
    }

    // Mettre à jour le stock (sans vérification)
    public void updateQuantite(int id, int nouvelleQuantite) throws SQLException {
        String sql = "UPDATE produits SET quantite = ? WHERE id = ?";
        PreparedStatement st = con.prepareStatement(sql);
        st.setInt(1, nouvelleQuantite);
        st.setInt(2, id);
        st.executeUpdate();
        System.out.println("✅ Stock du produit #" + id + " mis à jour: " + nouvelleQuantite + " unités");
    }

    // Mettre à jour le stock avec vérification partenaire
    public void updateQuantitePartenaire(int id, int partenaireId, int nouvelleQuantite) throws SQLException {
        String sql = "UPDATE produits SET quantite = ? WHERE id = ? AND partenaire_id = ?";
        PreparedStatement st = con.prepareStatement(sql);
        st.setInt(1, nouvelleQuantite);
        st.setInt(2, id);
        st.setInt(3, partenaireId);

        int rows = st.executeUpdate();
        if (rows > 0) {
            System.out.println("✅ Stock du produit #" + id + " mis à jour: " + nouvelleQuantite + " unités");
        } else {
            System.out.println("❌ Produit non trouvé ou vous n'êtes pas autorisé");
        }
    }

    // Produits par partenaire (utilise l'ID utilisateur comme partenaire_id)
    public List<Product> getByPartenaire(int partenaireId) throws SQLException {
        List<Product> produits = new ArrayList<>();
        String sql = "SELECT * FROM produits WHERE partenaire_id = ? ORDER BY id DESC";

        PreparedStatement st = con.prepareStatement(sql);
        st.setInt(1, partenaireId);
        ResultSet rs = st.executeQuery();

        while (rs.next()) {
            Product p = new Product();
            p.setId(rs.getInt("id"));
            p.setNom(rs.getString("nom"));
            p.setDescription(rs.getString("description"));
            p.setPrix(rs.getDouble("prix"));
            p.setQuantite(rs.getInt("quantite"));
            p.setPartenaireId(rs.getInt("partenaire_id"));
            p.setCategorie(rs.getString("categorie"));
            p.setStatut(rs.getString("statut"));
            p.setImageUrl(rs.getString("image_url"));

            produits.add(p);
        }

        return produits;
    }

    // Supprimer un produit avec vérification partenaire (SÉCURISÉ)
    public void supprimerProduitPartenaire(int productId, int partenaireId) throws SQLException {
        String sql = "DELETE FROM produits WHERE id = ? AND partenaire_id = ?";
        PreparedStatement st = con.prepareStatement(sql);
        st.setInt(1, productId);
        st.setInt(2, partenaireId);

        int rows = st.executeUpdate();
        if (rows > 0) {
            System.out.println("✅ Produit #" + productId + " supprimé");
        } else {
            System.out.println("❌ Produit non trouvé ou vous n'êtes pas autorisé");
        }
    }

    // Changer le statut avec vérification partenaire
    public void changerStatutPartenaire(int productId, int partenaireId, String nouveauStatut) throws SQLException {
        String sql = "UPDATE produits SET statut = ? WHERE id = ? AND partenaire_id = ?";
        PreparedStatement st = con.prepareStatement(sql);
        st.setString(1, nouveauStatut);
        st.setInt(2, productId);
        st.setInt(3, partenaireId);

        int rows = st.executeUpdate();
        if (rows > 0) {
            System.out.println("✅ Statut du produit #" + productId + " changé en: " + nouveauStatut);
        } else {
            System.out.println("❌ Produit non trouvé ou vous n'êtes pas autorisé");
        }
    }

    // Compter le nombre de produits d'un partenaire
    public int countByPartenaire(int partenaireId) throws SQLException {
        String sql = "SELECT COUNT(*) as total FROM produits WHERE partenaire_id = ?";
        PreparedStatement st = con.prepareStatement(sql);
        st.setInt(1, partenaireId);
        ResultSet rs = st.executeQuery();

        if (rs.next()) {
            return rs.getInt("total");
        }
        return 0;
    }
}