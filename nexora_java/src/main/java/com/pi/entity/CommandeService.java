package com.pi.entity;

import com.pi.entity.icrud;
import com.pi.entities.commande;
import com.pi.utils.mydatabase;
import java.sql.*;
import java.util.ArrayList;
import java.util.List;

public class CommandeService implements icruda<commande> {
    Connection con;

    public CommandeService() {
        con = mydatabase.getInstance().getConnection();
    }

    // 1. AJOUTER
    @Override
    public void ajouter(commande commande) throws SQLException {
        String sql = "INSERT INTO commande (client_nom, date_commande, total, statut) " +
                "VALUES (?, ?, ?, ?)";

        PreparedStatement st = con.prepareStatement(sql, Statement.RETURN_GENERATED_KEYS);
        st.setString(1, commande.getClientNom());
        st.setDate(2, new java.sql.Date(commande.getDateCommande().getTime()));
        st.setDouble(3, commande.getTotal());
        st.setString(4, commande.getStatut());

        st.executeUpdate();

        ResultSet rs = st.getGeneratedKeys();
        if (rs.next()) {
            commande.setId(rs.getInt(1));
        }

        System.out.println("✅ Commande ajoutée: #" + commande.getId());
    }

    // 2. SUPPRIMER
    @Override
    public void supprimer(int id) throws SQLException {
        String sql = "DELETE FROM commande WHERE id = ?";
        PreparedStatement st = con.prepareStatement(sql);
        st.setInt(1, id);
        st.executeUpdate();
        System.out.println("✅ Commande supprimée");
    }

    // 3. MODIFIER
    @Override
    public void modifier(int id) throws SQLException {
        String sql = "UPDATE commande SET statut = 'ANNULEE' WHERE id = ?";
        PreparedStatement st = con.prepareStatement(sql);
        st.setInt(1, id);
        st.executeUpdate();
    }

    // 3b. MODIFIER avec objet commande
    public void modifierCommande(commande commande) throws SQLException {
        String sql = "UPDATE commande SET client_nom = ?, date_commande = ?, total = ?, statut = ? WHERE id = ?";
        PreparedStatement st = con.prepareStatement(sql);
        st.setString(1, commande.getClientNom());
        st.setDate(2, new java.sql.Date(commande.getDateCommande().getTime()));
        st.setDouble(3, commande.getTotal());
        st.setString(4, commande.getStatut());
        st.setInt(5, commande.getId());
        st.executeUpdate();
        System.out.println("✅ Commande #" + commande.getId() + " modifiée - Statut: " + commande.getStatut());
    }

    // 4. AFFICHER toutes
    @Override
    public List<commande> afficher() throws SQLException {
        List<commande> commandes = new ArrayList<>();
        String sql = "SELECT * FROM commande ORDER BY date_commande DESC";

        Statement st = con.createStatement();
        ResultSet rs = st.executeQuery(sql);

        while (rs.next()) {
            commande c = new commande();
            c.setId(rs.getInt("id"));
            c.setClientNom(rs.getString("client_nom"));
            c.setDateCommande(rs.getDate("date_commande"));
            c.setTotal(rs.getDouble("total"));
            c.setStatut(rs.getString("statut"));
            commandes.add(c);
        }

        return commandes;
    }

    // Commandes par client (recherche par nom)
    public List<commande> getByClientNom(String clientNom) throws SQLException {
        List<commande> commandes = new ArrayList<>();
        String sql = "SELECT * FROM commande WHERE client_nom LIKE ? ORDER BY date_commande DESC";

        System.out.println("🔍 Recherche commandes pour client_nom LIKE: %" + clientNom + "%");
        
        PreparedStatement st = con.prepareStatement(sql);
        st.setString(1, "%" + clientNom + "%");
        ResultSet rs = st.executeQuery();

        while (rs.next()) {
            commande c = new commande();
            c.setId(rs.getInt("id"));
            c.setClientNom(rs.getString("client_nom"));
            c.setDateCommande(rs.getDate("date_commande"));
            c.setTotal(rs.getDouble("total"));
            c.setStatut(rs.getString("statut"));
            commandes.add(c);
            System.out.println("  ✓ Commande #" + c.getId() + " - " + c.getClientNom());
        }

        System.out.println("📊 Total: " + commandes.size() + " commande(s) trouvée(s)");
        return commandes;
    }

    // Récupérer par ID
    public commande getById(int id) throws SQLException {
        String sql = "SELECT * FROM commande WHERE id = ?";
        PreparedStatement st = con.prepareStatement(sql);
        st.setInt(1, id);
        ResultSet rs = st.executeQuery();

        if (rs.next()) {
            commande c = new commande();
            c.setId(rs.getInt("id"));
            c.setClientNom(rs.getString("client_nom"));
            c.setDateCommande(rs.getDate("date_commande"));
            c.setTotal(rs.getDouble("total"));
            c.setStatut(rs.getString("statut"));
            return c;
        }
        return null;
    }
}