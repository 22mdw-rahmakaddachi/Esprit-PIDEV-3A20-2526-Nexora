package com.pi.entity;

import com.pi.entities.Activite;
import com.pi.utils.mydatabase;

import java.sql.*;
import java.time.LocalDate;
import java.util.ArrayList;
import java.util.List;

public class ActiviteService implements icrud<Activite> {
    private Connection connection;

    public ActiviteService() {
        connection = mydatabase.getInstance().getConnection();
    }

    @Override
    public void ajouter(Activite activite) throws SQLException {
        // Générer une description automatique si elle est vide
        String description = activite.getDescription();
        if (description == null || description.trim().isEmpty()) {
            description = com.pi.utils.HuggingFaceService.generateActivityDescription(
                    activite.getNom(),
                    activite.getType(),
                    activite.getLieu(),
                    activite.getDateActivite().toString()
            );
        }

        String query = "INSERT INTO activite (nom, description, type, lieu, date_activite, images, prix, nombre_places, places_disponibles, partenaire_id, date_creation) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        try (PreparedStatement pst = connection.prepareStatement(query)) {
            pst.setString(1, activite.getNom());
            pst.setString(2, description);
            pst.setString(3, activite.getType());
            pst.setString(4, activite.getLieu());
            pst.setDate(5, Date.valueOf(activite.getDateActivite()));
            pst.setString(6, activite.getImages());
            pst.setDouble(7, activite.getPrix());
            pst.setInt(8, activite.getNombrePlaces());
            pst.setInt(9, activite.getNombrePlaces());
            pst.setInt(10, activite.getPartenaireId());
            pst.setDate(11, Date.valueOf(LocalDate.now()));
            pst.executeUpdate();
        }
    }

    @Override
    public void modifier(Activite activite) throws SQLException {
        String query = "UPDATE activite SET nom=?, description=?, type=?, lieu=?, date_activite=?, images=?, prix=?, nombre_places=? WHERE id=?";
        try (PreparedStatement pst = connection.prepareStatement(query)) {
            pst.setString(1, activite.getNom());
            pst.setString(2, activite.getDescription());
            pst.setString(3, activite.getType());
            pst.setString(4, activite.getLieu());
            pst.setDate(5, Date.valueOf(activite.getDateActivite()));
            pst.setString(6, activite.getImages());
            pst.setDouble(7, activite.getPrix());
            pst.setInt(8, activite.getNombrePlaces());
            pst.setInt(9, activite.getId());
            pst.executeUpdate();
        }
    }

    @Override
    public void supprimer(int id) throws SQLException {
        String query = "DELETE FROM activite WHERE id=?";
        try (PreparedStatement pst = connection.prepareStatement(query)) {
            pst.setInt(1, id);
            pst.executeUpdate();
        }
    }

    @Override
    public List<Activite> afficher() throws SQLException {
        List<Activite> activites = new ArrayList<>();
        // Requête avec JOIN pour récupérer les informations du partenaire incluant l'email
        String query = "SELECT a.*, " +
                "COALESCE(p.nom_entreprise, 'Partenaire') as partenaire_nom, " +
                "COALESCE(u.email, '') as partenaire_email, " +
                "COALESCE(p.responsable_telephone, '') as partenaire_telephone " +
                "FROM activite a " +
                "LEFT JOIN partenaire p ON a.partenaire_id = p.id " +
                "LEFT JOIN users u ON p.user_id = u.id " +
                "WHERE a.date_activite >= CURDATE() " +
                "ORDER BY a.date_activite";

        try (Statement st = connection.createStatement();
             ResultSet rs = st.executeQuery(query)) {
            while (rs.next()) {
                activites.add(mapResultSetToActivite(rs));
            }
        } catch (SQLException e) {
            // Si le JOIN échoue, utiliser la requête simple
            System.out.println("⚠️ JOIN échoué, utilisation requête simple");
            e.printStackTrace();
            String simpleQuery = "SELECT * FROM activite WHERE date_activite >= CURDATE() ORDER BY date_activite";
            try (Statement st = connection.createStatement();
                 ResultSet rs = st.executeQuery(simpleQuery)) {
                while (rs.next()) {
                    activites.add(mapResultSetToActivite(rs));
                }
            }
        }
        return activites;
    }

    public List<Activite> getByPartenaire(int partenaireId) throws SQLException {
        List<Activite> activites = new ArrayList<>();
        // Requête avec JOIN pour récupérer le nom du partenaire
        String query = "SELECT a.*, " +
                "COALESCE(p.nom_entreprise, 'Partenaire #' + CAST(a.partenaire_id AS CHAR)) as partenaire_nom, " +
                "COALESCE(u.email, '') as partenaire_email, " +
                "COALESCE(p.responsable_telephone, '') as partenaire_telephone " +
                "FROM activite a " +
                "LEFT JOIN partenaire p ON a.partenaire_id = p.id " +
                "LEFT JOIN users u ON p.user_id = u.id " +
                "WHERE a.partenaire_id=? " +
                "ORDER BY a.date_activite DESC";

        try (PreparedStatement pst = connection.prepareStatement(query)) {
            pst.setInt(1, partenaireId);
            try (ResultSet rs = pst.executeQuery()) {
                while (rs.next()) {
                    activites.add(mapResultSetToActivite(rs));
                }
            }
        } catch (SQLException e) {
            // Si le JOIN échoue, utiliser la requête simple
            System.out.println("⚠️ JOIN échoué, utilisation requête simple pour partenaire " + partenaireId);
            e.printStackTrace();
            String simpleQuery = "SELECT * FROM activite WHERE partenaire_id=? ORDER BY date_activite DESC";
            try (PreparedStatement pst = connection.prepareStatement(simpleQuery)) {
                pst.setInt(1, partenaireId);
                try (ResultSet rs = pst.executeQuery()) {
                    while (rs.next()) {
                        Activite act = mapResultSetToActivite(rs);
                        act.setPartenaireNom("Partenaire #" + partenaireId);
                        activites.add(act);
                    }
                }
            }
        }
        return activites;
    }

    public Activite getById(int id) throws SQLException {
        String query = "SELECT * FROM activite WHERE id=?";
        try (PreparedStatement pst = connection.prepareStatement(query)) {
            pst.setInt(1, id);
            try (ResultSet rs = pst.executeQuery()) {
                if (rs.next()) {
                    return mapResultSetToActivite(rs);
                }
            }
        }
        return null;
    }

    public void updatePlacesDisponibles(int activiteId, int nouvellePlaces) throws SQLException {
        String query = "UPDATE activite SET places_disponibles=? WHERE id=?";
        try (PreparedStatement pst = connection.prepareStatement(query)) {
            pst.setInt(1, nouvellePlaces);
            pst.setInt(2, activiteId);
            pst.executeUpdate();
        }
    }

    // Supprimer toutes les activités d'un partenaire (pour suppression en cascade)
    public void supprimerParPartenaire(int partenaireId) throws SQLException {
        String query = "DELETE FROM activite WHERE partenaire_id=?";
        try (PreparedStatement pst = connection.prepareStatement(query)) {
            pst.setInt(1, partenaireId);
            int count = pst.executeUpdate();
            System.out.println("🗑️ " + count + " activité(s) supprimée(s) pour le partenaire #" + partenaireId);
        }
    }

    // Méthode pour récupérer les activités avec les informations du partenaire (pour les clients)
    public List<Activite> getAllWithPartenaireInfo() throws SQLException {
        List<Activite> activites = new ArrayList<>();
        String query = "SELECT a.*, " +
                "COALESCE(p.nom_entreprise, 'Non disponible') as partenaire_nom, " +
                "COALESCE(u.email, '') as partenaire_email, " +
                "COALESCE(p.responsable_telephone, 'Non disponible') as partenaire_telephone " +
                "FROM activite a " +
                "LEFT JOIN partenaire p ON a.partenaire_id = p.id " +
                "LEFT JOIN users u ON p.user_id = u.id " +
                "WHERE a.date_activite >= CURDATE() " +
                "ORDER BY a.date_activite";

        try (Statement st = connection.createStatement();
             ResultSet rs = st.executeQuery(query)) {
            while (rs.next()) {
                activites.add(mapResultSetToActivite(rs));
            }
        } catch (SQLException e) {
            // Si le JOIN échoue (table partenaire n'existe pas), utiliser la requête simple
            System.out.println("⚠️ Impossible de récupérer les infos partenaire, utilisation de la requête simple");
            e.printStackTrace();
            return afficher();
        }
        return activites;
    }

    // Méthode pour l'admin : récupérer TOUTES les activités de TOUS les partenaires
    public List<Activite> getAllForAdmin() throws SQLException {
        List<Activite> activites = new ArrayList<>();
        String query = "SELECT a.*, " +
                "COALESCE(p.nom_entreprise, CONCAT('Partenaire #', a.partenaire_id)) as partenaire_nom, " +
                "COALESCE(u.email, '') as partenaire_email, " +
                "COALESCE(p.responsable_telephone, '') as partenaire_telephone " +
                "FROM activite a " +
                "LEFT JOIN partenaire p ON a.partenaire_id = p.id " +
                "LEFT JOIN users u ON p.user_id = u.id " +
                "ORDER BY a.date_activite DESC";

        try (Statement st = connection.createStatement();
             ResultSet rs = st.executeQuery(query)) {
            while (rs.next()) {
                activites.add(mapResultSetToActivite(rs));
            }
        } catch (SQLException e) {
            // Si le JOIN échoue, utiliser la requête simple
            System.out.println("⚠️ JOIN échoué pour admin, utilisation requête simple");
            e.printStackTrace();
            String simpleQuery = "SELECT * FROM activite ORDER BY date_activite DESC";
            try (Statement st = connection.createStatement();
                 ResultSet rs = st.executeQuery(simpleQuery)) {
                while (rs.next()) {
                    Activite act = mapResultSetToActivite(rs);
                    act.setPartenaireNom("Partenaire #" + act.getPartenaireId());
                    activites.add(act);
                }
            }
        }
        return activites;
    }

    private Activite mapResultSetToActivite(ResultSet rs) throws SQLException {
        Activite activite = new Activite();
        activite.setId(rs.getInt("id"));
        activite.setNom(rs.getString("nom"));

        // Récupérer la description si elle existe
        try {
            activite.setDescription(rs.getString("description"));
        } catch (SQLException e) {
            // La colonne description n'existe pas encore
            activite.setDescription("");
        }

        activite.setType(rs.getString("type"));
        activite.setLieu(rs.getString("lieu"));
        activite.setDateActivite(rs.getDate("date_activite").toLocalDate());
        activite.setImages(rs.getString("images"));
        activite.setPrix(rs.getDouble("prix"));
        activite.setNombrePlaces(rs.getInt("nombre_places"));
        activite.setPlacesDisponibles(rs.getInt("places_disponibles"));
        activite.setPartenaireId(rs.getInt("partenaire_id"));
        activite.setDateCreation(rs.getDate("date_creation").toLocalDate());

        // Récupérer les informations du partenaire si disponibles
        try {
            activite.setPartenaireNom(rs.getString("partenaire_nom"));
            activite.setPartenaireEmail(rs.getString("partenaire_email"));
            activite.setPartenaireTelephone(rs.getString("partenaire_telephone"));
        } catch (SQLException e) {
            // Les colonnes du partenaire ne sont pas disponibles (requête sans JOIN)
        }

        return activite;
    }

}
