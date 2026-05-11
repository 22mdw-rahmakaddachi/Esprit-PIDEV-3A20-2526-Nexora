package com.pi.entity;

import com.pi.entities.*;
import com.pi.utils.mydatabase;
import java.sql.*;
import java.util.ArrayList;
import java.util.List;
import java.util.Date;

public class PanierService {

    private Connection con;

    public PanierService() {
        con = mydatabase.getInstance().getConnection();
    }

    // 1. AJOUTER au panier
    public void ajouterAuPanier(int clientId, int produitId, int quantite) throws SQLException {
        // Vérifier stock disponible
        ProductService productService = new ProductService();
        Product produit = productService.getById(produitId);
        if (produit.getQuantite() < quantite) {
            throw new SQLException("❌ Stock insuffisant. Disponible: " + produit.getQuantite());
        }

        // Vérifier si déjà dans panier
        String checkSql = "SELECT id, quantite FROM panier WHERE client_id = ? AND produit_id = ?";
        PreparedStatement checkSt = con.prepareStatement(checkSql);
        checkSt.setInt(1, clientId);
        checkSt.setInt(2, produitId);
        ResultSet rs = checkSt.executeQuery();

        if (rs.next()) {
            // Déjà dans panier → augmenter quantité
            int panierId = rs.getInt("id");
            int nouvelleQuantite = rs.getInt("quantite") + quantite;

            String updateSql = "UPDATE panier SET quantite = ? WHERE id = ?";
            PreparedStatement updateSt = con.prepareStatement(updateSql);
            updateSt.setInt(1, nouvelleQuantite);
            updateSt.setInt(2, panierId);
            updateSt.executeUpdate();
            System.out.println("✅ Quantité augmentée: +" + quantite);
        } else {
            // Nouveau dans panier
            String insertSql = "INSERT INTO panier (client_id, produit_id, quantite) VALUES (?, ?, ?)";
            PreparedStatement insertSt = con.prepareStatement(insertSql);
            insertSt.setInt(1, clientId);
            insertSt.setInt(2, produitId);
            insertSt.setInt(3, quantite);
            insertSt.executeUpdate();
            System.out.println("✅ Produit ajouté au panier");
        }
    }

    // 2. VOIR le panier
    public List<PanierItem> getPanier(int clientId) throws SQLException {
        List<PanierItem> panierItems = new ArrayList<>();
        String sql = "SELECT p.id, p.produit_id, p.quantite, pr.nom, pr.prix " +
                "FROM panier p " +
                "JOIN produits pr ON p.produit_id = pr.id " +
                "WHERE p.client_id = ? " +
                "ORDER BY p.date_ajout DESC";

        PreparedStatement st = con.prepareStatement(sql);
        st.setInt(1, clientId);
        ResultSet rs = st.executeQuery();

        while (rs.next()) {
            PanierItem item = new PanierItem();
            item.setId(rs.getInt("id"));
            item.setProduitId(rs.getInt("produit_id"));
            item.setProduitNom(rs.getString("nom"));
            item.setPrixUnitaire(rs.getDouble("prix"));
            item.setQuantite(rs.getInt("quantite"));
            panierItems.add(item);
        }

        return panierItems;
    }

    // 2b. VOIR le panier (avec variants - nouveau système)
    public List<PanierItem> getPanierAvecVariants(int clientId) throws SQLException {
        List<PanierItem> panierItems = new ArrayList<>();
        
        // Requête pour récupérer tous les items (ancien + nouveau système)
        String sql = "SELECT p.id, p.produit_id, p.variant_sku, p.produit_nom, p.prix_unitaire, p.quantite, " +
                    "pr.nom as ancien_nom, pr.prix as ancien_prix " +
                    "FROM panier p " +
                    "LEFT JOIN produits pr ON p.produit_id = pr.id " +
                    "WHERE p.client_id = ? " +
                    "ORDER BY p.date_ajout DESC";

        PreparedStatement st = con.prepareStatement(sql);
        st.setInt(1, clientId);
        ResultSet rs = st.executeQuery();

        while (rs.next()) {
            PanierItem item = new PanierItem();
            item.setId(rs.getInt("id"));
            
            // Vérifier si c'est un variant (nouveau système) ou produit simple (ancien)
            String variantSku = rs.getString("variant_sku");
            if (variantSku != null && !variantSku.isEmpty()) {
                // Nouveau système (variant)
                item.setVariantSku(variantSku);
                item.setProduitNom(rs.getString("produit_nom"));
                item.setPrixUnitaire(rs.getDouble("prix_unitaire"));
            } else {
                // Ancien système (produit simple)
                item.setProduitId(rs.getInt("produit_id"));
                item.setProduitNom(rs.getString("ancien_nom"));
                item.setPrixUnitaire(rs.getDouble("ancien_prix"));
            }
            
            item.setQuantite(rs.getInt("quantite"));
            panierItems.add(item);
        }

        return panierItems;
    }

    // 3. MODIFIER quantité
    public void modifierQuantite(int panierId, int nouvelleQuantite) throws SQLException {
        if (nouvelleQuantite <= 0) {
            supprimerDuPanier(panierId);
            return;
        }

        String sql = "UPDATE panier SET quantite = ? WHERE id = ?";
        PreparedStatement st = con.prepareStatement(sql);
        st.setInt(1, nouvelleQuantite);
        st.setInt(2, panierId);
        st.executeUpdate();
        System.out.println("✅ Quantité modifiée");
    }

    // 4. SUPPRIMER du panier
    public void supprimerDuPanier(int panierId) throws SQLException {
        String sql = "DELETE FROM panier WHERE id = ?";
        PreparedStatement st = con.prepareStatement(sql);
        st.setInt(1, panierId);
        st.executeUpdate();
        System.out.println("✅ Article supprimé du panier");
    }

    // 5. VIDER le panier
    public void viderPanier(int clientId) throws SQLException {
        String sql = "DELETE FROM panier WHERE client_id = ?";
        PreparedStatement st = con.prepareStatement(sql);
        st.setInt(1, clientId);
        int rows = st.executeUpdate();
        System.out.println("✅ Panier vidé (" + rows + " articles)");
    }

    // 6. CALCULER total
    public double calculerTotalPanier(int clientId) throws SQLException {
        String sql = "SELECT SUM(pr.prix * p.quantite) as total " +
                "FROM panier p " +
                "JOIN produits pr ON p.produit_id = pr.id " +
                "WHERE p.client_id = ?";

        PreparedStatement st = con.prepareStatement(sql);
        st.setInt(1, clientId);
        ResultSet rs = st.executeQuery();

        if (rs.next()) {
            return rs.getDouble("total");
        }
        return 0.0;
    }

    // 6b. AJOUTER VARIANT AU PANIER (nouveau système)
    public void ajouterVariantAuPanier(int clientId, String variantSku, String produitNom, 
                                        double prixUnitaire, int quantite) throws SQLException {
        System.out.println("🛒 Ajout variant au panier: " + variantSku);
        
        // Vérifier si déjà dans panier
        String checkSql = "SELECT id, quantite FROM panier WHERE client_id = ? AND variant_sku = ?";
        PreparedStatement checkSt = con.prepareStatement(checkSql);
        checkSt.setInt(1, clientId);
        checkSt.setString(2, variantSku);
        ResultSet rs = checkSt.executeQuery();

        if (rs.next()) {
            // Déjà dans panier → augmenter quantité
            int panierId = rs.getInt("id");
            int nouvelleQuantite = rs.getInt("quantite") + quantite;
            
            String updateSql = "UPDATE panier SET quantite = ? WHERE id = ?";
            PreparedStatement updateSt = con.prepareStatement(updateSql);
            updateSt.setInt(1, nouvelleQuantite);
            updateSt.setInt(2, panierId);
            updateSt.executeUpdate();
            System.out.println("✅ Quantité augmentée: " + nouvelleQuantite);
        } else {
            // Nouveau dans panier
            String insertSql = "INSERT INTO panier (client_id, variant_sku, produit_nom, prix_unitaire, quantite) " +
                              "VALUES (?, ?, ?, ?, ?)";
            PreparedStatement insertSt = con.prepareStatement(insertSql);
            insertSt.setInt(1, clientId);
            insertSt.setString(2, variantSku);
            insertSt.setString(3, produitNom);
            insertSt.setDouble(4, prixUnitaire);
            insertSt.setInt(5, quantite);
            insertSt.executeUpdate();
            System.out.println("✅ Variant ajouté au panier: " + produitNom);
        }
    }

    // 7. VALIDER commande
    public commande passerCommande(int clientId, String clientNom) throws SQLException {
        System.out.println("🛒 Passage de commande pour: " + clientNom + " (ID: " + clientId + ")");
        
        // Vérifier panier non vide (utiliser la nouvelle méthode avec variants)
        List<PanierItem> panier = getPanierAvecVariants(clientId);
        if (panier.isEmpty()) {
            throw new SQLException("❌ Panier vide");
        }
        System.out.println("  ✓ Panier contient " + panier.size() + " article(s)");

        // Calculer total à partir des items
        double total = 0.0;
        for (PanierItem item : panier) {
            total += item.getTotal();
        }
        System.out.println("  ✓ Total: " + total + " TND");

        // Créer commande
        CommandeService commandeService = new CommandeService();
        commande commande = new commande();
        commande.setUserId(clientId);
        commande.setClientNom(clientNom);
        commande.setDateCommande(new Date());
        commande.setTotal(total);
        commande.setStatut("EN_ATTENTE");

        commandeService.ajouter(commande);
        System.out.println("  ✓ Commande #" + commande.getId() + " créée");

        // Ajouter items
        CommandeItemService itemService = new CommandeItemService();
        ProductService productService = new ProductService();
        ProduitVariantService variantService = new ProduitVariantService();

        for (PanierItem item : panier) {
            CommandeItem cmdItem = new CommandeItem();
            cmdItem.setCommandeId(commande.getId());
            cmdItem.setProduitNom(item.getProduitNom());
            cmdItem.setQuantite(item.getQuantite());
            cmdItem.setPrixUnitaire(item.getPrixUnitaire());
            cmdItem.setSousTotal(item.getTotal());

            itemService.ajouter(cmdItem);

            // Mettre à jour stock selon le type de produit
            if (item.getVariantSku() != null && !item.getVariantSku().isEmpty()) {
                // Nouveau système (variant) - mettre à jour le stock du variant
                try {
                    ProduitVariant variant = variantService.getBySku(item.getVariantSku());
                    if (variant != null) {
                        int nouveauStock = variant.getQuantiteStock() - item.getQuantite();
                        variantService.updateStock(variant.getId(), nouveauStock);
                        System.out.println("  ✓ Stock variant mis à jour: " + item.getVariantSku() + " → " + nouveauStock);
                    }
                } catch (SQLException e) {
                    System.err.println("⚠️ Erreur mise à jour stock variant: " + e.getMessage());
                }
            } else if (item.getProduitId() > 0) {
                // Ancien système (produit simple) - mettre à jour le stock du produit
                try {
                    Product produit = productService.getById(item.getProduitId());
                    if (produit != null) {
                        int nouveauStock = produit.getQuantite() - item.getQuantite();
                        productService.updateQuantite(item.getProduitId(), nouveauStock);
                        System.out.println("  ✓ Stock produit mis à jour: " + item.getProduitId() + " → " + nouveauStock);
                    }
                } catch (SQLException e) {
                    System.err.println("⚠️ Erreur mise à jour stock produit: " + e.getMessage());
                }
            }
        }

        // Vider panier
        viderPanier(clientId);

        System.out.println("✅ Commande #" + commande.getId() + " créée avec succès pour " + clientNom);
        return commande;
    }
}