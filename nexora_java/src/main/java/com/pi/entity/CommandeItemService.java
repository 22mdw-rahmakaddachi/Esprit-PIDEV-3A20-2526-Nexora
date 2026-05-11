package com.pi.entity;

import com.pi.entity.icrud;
import com.pi.entities.CommandeItem;
import com.pi.utils.mydatabase;
import java.sql.*;
import java.util.ArrayList;
import java.util.List;

public class CommandeItemService implements icruda<CommandeItem> {
    Connection con;

    public CommandeItemService() {
        con = mydatabase.getInstance().getConnection();
    }

    // 1. AJOUTER
    @Override
    public void ajouter(CommandeItem item) throws SQLException {
        String sql = "INSERT INTO commande_item (commande_id, produit_nom, quantite, prix_unitaire, sous_total) " +
                "VALUES (?, ?, ?, ?, ?)";

        PreparedStatement st = con.prepareStatement(sql);
        st.setInt(1, item.getCommandeId());
        st.setString(2, item.getProduitNom());
        st.setInt(3, item.getQuantite());
        st.setDouble(4, item.getPrixUnitaire());
        st.setDouble(5, item.getSousTotal());

        st.executeUpdate();
        System.out.println("✅ Item ajouté: " + item.getProduitNom());
    }

    // 2. SUPPRIMER
    @Override
    public void supprimer(int id) throws SQLException {
        String sql = "DELETE FROM commande_item WHERE id = ?";
        PreparedStatement st = con.prepareStatement(sql);
        st.setInt(1, id);
        st.executeUpdate();
    }

    // 3. MODIFIER
    @Override
    public void modifier(int id) throws SQLException {
        // Pas utilisé directement
    }

    // 4. AFFICHER tous
    @Override
    public List<CommandeItem> afficher() throws SQLException {
        List<CommandeItem> items = new ArrayList<>();
        String sql = "SELECT * FROM commande_item";

        Statement st = con.createStatement();
        ResultSet rs = st.executeQuery(sql);

        while (rs.next()) {
            CommandeItem item = new CommandeItem();
            item.setId(rs.getInt("id"));
            item.setCommandeId(rs.getInt("commande_id"));
            item.setProduitNom(rs.getString("produit_nom"));
            item.setQuantite(rs.getInt("quantite"));
            item.setPrixUnitaire(rs.getDouble("prix_unitaire"));
            item.setSousTotal(rs.getDouble("sous_total"));
            items.add(item);
        }

        return items;
    }

    // Items par commande
    public List<CommandeItem> getByCommandeId(int commandeId) throws SQLException {
        List<CommandeItem> items = new ArrayList<>();
        String sql = "SELECT * FROM commande_item WHERE commande_id = ?";

        PreparedStatement st = con.prepareStatement(sql);
        st.setInt(1, commandeId);
        ResultSet rs = st.executeQuery();

        while (rs.next()) {
            CommandeItem item = new CommandeItem();
            item.setId(rs.getInt("id"));
            item.setCommandeId(rs.getInt("commande_id"));
            item.setProduitNom(rs.getString("produit_nom"));
            item.setQuantite(rs.getInt("quantite"));
            item.setPrixUnitaire(rs.getDouble("prix_unitaire"));
            item.setSousTotal(rs.getDouble("sous_total"));
            items.add(item);
        }

        return items;
    }
}