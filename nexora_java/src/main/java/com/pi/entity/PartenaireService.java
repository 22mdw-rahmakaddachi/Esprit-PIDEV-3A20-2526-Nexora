package com.pi.entity;

import com.pi.entities.Partenaire;
import com.pi.utils.mydatabase;
import java.sql.*;
import java.util.ArrayList;
import java.util.List;

public class PartenaireService {

    private Connection connection;

    public PartenaireService() {
        connection = mydatabase.getInstance().getConnection();
    }

    public Partenaire getByUserId(int userId) throws SQLException {
        String sql = "SELECT * FROM partenaire WHERE user_id = ?";
        PreparedStatement ps = connection.prepareStatement(sql);
        ps.setInt(1, userId);
        ResultSet rs = ps.executeQuery();

        if (rs.next()) {
            Partenaire p = new Partenaire();
            p.setId(rs.getInt("id"));
            p.setUserId(rs.getInt("user_id"));
            p.setNomEntreprise(rs.getString("nom_entreprise"));
            p.setResponsableNom(rs.getString("responsable_nom"));
            p.setStatut(rs.getString("statut"));
            return p;
        }
        return null;
    }
    
    /**
     * Récupère le premier partenaire disponible dans la base
     */
    public Partenaire getPremierPartenaire() throws SQLException {
        String sql = "SELECT * FROM partenaire LIMIT 1";
        PreparedStatement ps = connection.prepareStatement(sql);
        ResultSet rs = ps.executeQuery();

        if (rs.next()) {
            Partenaire p = new Partenaire();
            p.setId(rs.getInt("id"));
            p.setUserId(rs.getInt("user_id"));
            p.setNomEntreprise(rs.getString("nom_entreprise"));
            p.setResponsableNom(rs.getString("responsable_nom"));
            p.setStatut(rs.getString("statut"));
            return p;
        }
        return null;
    }
    
    /**
     * Récupère tous les partenaires
     */
    public List<Partenaire> afficher() throws SQLException {
        List<Partenaire> partenaires = new ArrayList<>();
        String sql = "SELECT * FROM partenaire";
        PreparedStatement ps = connection.prepareStatement(sql);
        ResultSet rs = ps.executeQuery();

        while (rs.next()) {
            Partenaire p = new Partenaire();
            p.setId(rs.getInt("id"));
            p.setUserId(rs.getInt("user_id"));
            p.setNomEntreprise(rs.getString("nom_entreprise"));
            p.setResponsableNom(rs.getString("responsable_nom"));
            p.setStatut(rs.getString("statut"));
            partenaires.add(p);
        }
        return partenaires;
    }
    
    /**
     * Crée automatiquement un partenaire à partir d'un utilisateur
     */
    public Partenaire creerPartenaireDepuisUser(com.pi.entities.user user) throws SQLException {
        // Vérifier si le partenaire existe déjà
        Partenaire existant = getByUserId(user.getId());
        if (existant != null) {
            return existant;
        }
        
        // Créer un nouveau partenaire
        String nomEntreprise = (user.getPrenom() != null ? user.getPrenom() + " " : "") + 
                              (user.getName() != null ? user.getName() : "Entreprise");
        String responsableNom = (user.getPrenom() != null ? user.getPrenom() + " " : "") + 
                               (user.getName() != null ? user.getName() : "Responsable");
        
        String sql = "INSERT INTO partenaire (user_id, nom_entreprise, responsable_nom, responsable_telephone, adresse_entreprise, statut, date_inscription, commission) VALUES (?, ?, ?, ?, ?, ?, NOW(), ?)";
        PreparedStatement ps = connection.prepareStatement(sql, Statement.RETURN_GENERATED_KEYS);
        ps.setInt(1, user.getId());
        ps.setString(2, nomEntreprise);
        ps.setString(3, responsableNom);
        ps.setString(4, user.getNum() != 0 ? String.valueOf(user.getNum()) : "00000000");
        ps.setString(5, "Adresse non spécifiée");
        ps.setString(6, "ACTIF");
        ps.setDouble(7, 10.0); // Commission par défaut
        
        int rowsAffected = ps.executeUpdate();
        if (rowsAffected > 0) {
            ResultSet generatedKeys = ps.getGeneratedKeys();
            if (generatedKeys.next()) {
                int newId = generatedKeys.getInt(1);
                
                // Retourner le partenaire créé
                Partenaire nouveau = new Partenaire();
                nouveau.setId(newId);
                nouveau.setUserId(user.getId());
                nouveau.setNomEntreprise(nomEntreprise);
                nouveau.setResponsableNom(responsableNom);
                nouveau.setResponsableTelephone(user.getNum() != 0 ? String.valueOf(user.getNum()) : "00000000");
                nouveau.setAdresseEntreprise("Adresse non spécifiée");
                nouveau.setStatut("ACTIF");
                nouveau.setCommission(10.0);
                nouveau.setDateInscription(new java.util.Date());
                
                System.out.println("✅ Nouveau partenaire créé automatiquement: ID=" + newId + ", User=" + user.getName());
                return nouveau;
            }
        }
        
        throw new SQLException("Impossible de créer le partenaire pour l'utilisateur " + user.getId());
    }
}