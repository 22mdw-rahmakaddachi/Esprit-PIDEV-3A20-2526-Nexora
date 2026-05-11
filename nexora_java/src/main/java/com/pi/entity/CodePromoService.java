package com.pi.entity;

import com.pi.entities.CodePromo;
import com.pi.utils.mydatabase;
import java.sql.*;
import java.util.ArrayList;
import java.util.List;

public class CodePromoService {
    private Connection con;

    public CodePromoService() {
        con = mydatabase.getInstance().getConnection();
    }

    // Créer un code promo
    public void creer(CodePromo codePromo) throws SQLException {
        String sql = "INSERT INTO code_promo (code, description, type_reduction, valeur_reduction, " +
                    "montant_minimum, date_debut, date_fin, limite_utilisation, actif, partenaire_id, " +
                    "categorie_id, premiere_commande_seulement) " +
                    "VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        PreparedStatement st = con.prepareStatement(sql, Statement.RETURN_GENERATED_KEYS);
        st.setString(1, codePromo.getCode().toUpperCase());
        st.setString(2, codePromo.getDescription());
        st.setString(3, codePromo.getTypeReduction().name());
        st.setDouble(4, codePromo.getValeurReduction());
        st.setDouble(5, codePromo.getMontantMinimum());
        st.setDate(6, new java.sql.Date(codePromo.getDateDebut().getTime()));
        st.setDate(7, new java.sql.Date(codePromo.getDateFin().getTime()));
        
        if (codePromo.getLimiteUtilisation() != null) {
            st.setInt(8, codePromo.getLimiteUtilisation());
        } else {
            st.setNull(8, Types.INTEGER);
        }
        
        st.setBoolean(9, codePromo.isActif());
        
        if (codePromo.getPartenaireId() != null) {
            st.setInt(10, codePromo.getPartenaireId());
        } else {
            st.setNull(10, Types.INTEGER);
        }
        
        if (codePromo.getCategorieId() != null) {
            st.setInt(11, codePromo.getCategorieId());
        } else {
            st.setNull(11, Types.INTEGER);
        }
        
        st.setBoolean(12, codePromo.isPremiereCommandeSeulement());
        
        st.executeUpdate();
        
        ResultSet rs = st.getGeneratedKeys();
        if (rs.next()) {
            codePromo.setId(rs.getInt(1));
        }
        
        System.out.println("✅ Code promo créé: " + codePromo.getCode());
    }

    // Valider un code promo
    public CodePromo validerCode(String code, int clientId, double montantCommande) throws SQLException {
        CodePromo codePromo = getByCode(code);
        
        if (codePromo == null) {
            throw new SQLException("❌ Code promo invalide");
        }
        
        if (!codePromo.estValide()) {
            throw new SQLException("❌ Code promo expiré ou inactif");
        }
        
        if (montantCommande < codePromo.getMontantMinimum()) {
            throw new SQLException("❌ Montant minimum requis: " + 
                String.format("%.3f TND", codePromo.getMontantMinimum()));
        }
        
        // Vérifier si première commande seulement
        if (codePromo.isPremiereCommandeSeulement()) {
            if (aDejaCommande(clientId)) {
                throw new SQLException("❌ Ce code est réservé aux nouveaux clients");
            }
        }
        
        // Vérifier si le client a déjà utilisé ce code
        if (aDejaUtiliseCode(clientId, codePromo.getId())) {
            throw new SQLException("❌ Vous avez déjà utilisé ce code promo");
        }
        
        return codePromo;
    }

    // Appliquer un code promo à une commande
    public void appliquerCode(int codePromoId, int clientId, int commandeId, double montantReduction) 
            throws SQLException {
        // Enregistrer l'utilisation
        String sql1 = "INSERT INTO utilisation_code_promo (code_promo_id, client_id, commande_id, montant_reduction) " +
                     "VALUES (?, ?, ?, ?)";
        PreparedStatement st1 = con.prepareStatement(sql1);
        st1.setInt(1, codePromoId);
        st1.setInt(2, clientId);
        st1.setInt(3, commandeId);
        st1.setDouble(4, montantReduction);
        st1.executeUpdate();
        
        // Incrémenter le compteur d'utilisations
        String sql2 = "UPDATE code_promo SET nombre_utilisations = nombre_utilisations + 1 WHERE id = ?";
        PreparedStatement st2 = con.prepareStatement(sql2);
        st2.setInt(1, codePromoId);
        st2.executeUpdate();
        
        System.out.println("✅ Code promo appliqué: -" + String.format("%.3f TND", montantReduction));
    }

    // Récupérer un code par son code
    public CodePromo getByCode(String code) throws SQLException {
        String sql = "SELECT * FROM code_promo WHERE code = ?";
        PreparedStatement st = con.prepareStatement(sql);
        st.setString(1, code.toUpperCase());
        ResultSet rs = st.executeQuery();
        
        if (rs.next()) {
            return mapResultSetToCodePromo(rs);
        }
        return null;
    }

    // Récupérer tous les codes promo
    public List<CodePromo> getAll() throws SQLException {
        List<CodePromo> codes = new ArrayList<>();
        String sql = "SELECT * FROM code_promo ORDER BY date_creation DESC";
        Statement st = con.createStatement();
        ResultSet rs = st.executeQuery(sql);
        
        while (rs.next()) {
            codes.add(mapResultSetToCodePromo(rs));
        }
        return codes;
    }

    // Récupérer les codes promo d'un partenaire
    public List<CodePromo> getByPartenaire(int partenaireId) throws SQLException {
        List<CodePromo> codes = new ArrayList<>();
        String sql = "SELECT * FROM code_promo WHERE partenaire_id = ? OR partenaire_id IS NULL " +
                    "ORDER BY date_creation DESC";
        PreparedStatement st = con.prepareStatement(sql);
        st.setInt(1, partenaireId);
        ResultSet rs = st.executeQuery();
        
        while (rs.next()) {
            codes.add(mapResultSetToCodePromo(rs));
        }
        return codes;
    }

    // Récupérer les codes promo actifs
    public List<CodePromo> getActifs() throws SQLException {
        List<CodePromo> codes = new ArrayList<>();
        String sql = "SELECT * FROM code_promo WHERE actif = TRUE " +
                    "AND date_debut <= CURDATE() AND date_fin >= CURDATE() " +
                    "ORDER BY date_creation DESC";
        Statement st = con.createStatement();
        ResultSet rs = st.executeQuery(sql);
        
        while (rs.next()) {
            codes.add(mapResultSetToCodePromo(rs));
        }
        return codes;
    }

    // Modifier un code promo
    public void modifier(CodePromo codePromo) throws SQLException {
        String sql = "UPDATE code_promo SET description = ?, type_reduction = ?, valeur_reduction = ?, " +
                    "montant_minimum = ?, date_debut = ?, date_fin = ?, limite_utilisation = ?, " +
                    "actif = ?, categorie_id = ?, premiere_commande_seulement = ? WHERE id = ?";
        
        PreparedStatement st = con.prepareStatement(sql);
        st.setString(1, codePromo.getDescription());
        st.setString(2, codePromo.getTypeReduction().name());
        st.setDouble(3, codePromo.getValeurReduction());
        st.setDouble(4, codePromo.getMontantMinimum());
        st.setDate(5, new java.sql.Date(codePromo.getDateDebut().getTime()));
        st.setDate(6, new java.sql.Date(codePromo.getDateFin().getTime()));
        
        if (codePromo.getLimiteUtilisation() != null) {
            st.setInt(7, codePromo.getLimiteUtilisation());
        } else {
            st.setNull(7, Types.INTEGER);
        }
        
        st.setBoolean(8, codePromo.isActif());
        
        if (codePromo.getCategorieId() != null) {
            st.setInt(9, codePromo.getCategorieId());
        } else {
            st.setNull(9, Types.INTEGER);
        }
        
        st.setBoolean(10, codePromo.isPremiereCommandeSeulement());
        st.setInt(11, codePromo.getId());
        
        st.executeUpdate();
        System.out.println("✅ Code promo modifié: " + codePromo.getCode());
    }

    // Supprimer un code promo
    public void supprimer(int id) throws SQLException {
        String sql = "DELETE FROM code_promo WHERE id = ?";
        PreparedStatement st = con.prepareStatement(sql);
        st.setInt(1, id);
        st.executeUpdate();
        System.out.println("✅ Code promo supprimé");
    }

    // Activer/Désactiver un code promo
    public void toggleActif(int id) throws SQLException {
        String sql = "UPDATE code_promo SET actif = NOT actif WHERE id = ?";
        PreparedStatement st = con.prepareStatement(sql);
        st.setInt(1, id);
        st.executeUpdate();
    }

    // Vérifier si le client a déjà commandé
    private boolean aDejaCommande(int clientId) throws SQLException {
        String sql = "SELECT COUNT(*) as nb FROM commande WHERE user_id = ?";
        PreparedStatement st = con.prepareStatement(sql);
        st.setInt(1, clientId);
        ResultSet rs = st.executeQuery();
        
        if (rs.next()) {
            return rs.getInt("nb") > 0;
        }
        return false;
    }

    // Vérifier si le client a déjà utilisé ce code
    private boolean aDejaUtiliseCode(int clientId, int codePromoId) throws SQLException {
        String sql = "SELECT COUNT(*) as nb FROM utilisation_code_promo " +
                    "WHERE client_id = ? AND code_promo_id = ?";
        PreparedStatement st = con.prepareStatement(sql);
        st.setInt(1, clientId);
        st.setInt(2, codePromoId);
        ResultSet rs = st.executeQuery();
        
        if (rs.next()) {
            return rs.getInt("nb") > 0;
        }
        return false;
    }

    // Mapper ResultSet vers CodePromo
    private CodePromo mapResultSetToCodePromo(ResultSet rs) throws SQLException {
        CodePromo code = new CodePromo();
        code.setId(rs.getInt("id"));
        code.setCode(rs.getString("code"));
        code.setDescription(rs.getString("description"));
        code.setTypeReduction(CodePromo.TypeReduction.valueOf(rs.getString("type_reduction")));
        code.setValeurReduction(rs.getDouble("valeur_reduction"));
        code.setMontantMinimum(rs.getDouble("montant_minimum"));
        code.setDateDebut(rs.getDate("date_debut"));
        code.setDateFin(rs.getDate("date_fin"));
        
        int limiteUtil = rs.getInt("limite_utilisation");
        if (!rs.wasNull()) {
            code.setLimiteUtilisation(limiteUtil);
        }
        
        code.setNombreUtilisations(rs.getInt("nombre_utilisations"));
        code.setActif(rs.getBoolean("actif"));
        
        int partId = rs.getInt("partenaire_id");
        if (!rs.wasNull()) {
            code.setPartenaireId(partId);
        }
        
        int catId = rs.getInt("categorie_id");
        if (!rs.wasNull()) {
            code.setCategorieId(catId);
        }
        
        code.setPremiereCommandeSeulement(rs.getBoolean("premiere_commande_seulement"));
        code.setDateCreation(rs.getTimestamp("date_creation"));
        
        return code;
    }
}
