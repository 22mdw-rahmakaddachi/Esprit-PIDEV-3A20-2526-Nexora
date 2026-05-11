package com.pi.entity;

import com.pi.entities.Destination;
import com.pi.utils.mydatabase;

import java.sql.*;
import java.util.ArrayList;
import java.util.Arrays;
import java.util.Collections;
import java.util.List;

public class DestinationService {

    Connection con;

    public DestinationService() {
        con = mydatabase.getInstance().getConnection();
    }

    // ========================= AJOUTER =========================
    public void ajouter(Destination destination) throws SQLException {

        String sql = "INSERT INTO destination "
                + "(nom, description, localisation, statut, images) "
                + "VALUES (?, ?, ?, ?, ?)";

        PreparedStatement st = con.prepareStatement(sql);

        st.setString(1, destination.getNom());
        st.setString(2, destination.getDescription());
        st.setString(3, destination.getLocalisation());
        st.setString(4, destination.getStatut());

        // Convert List<String> -> String
        if (destination.getImages() != null && !destination.getImages().isEmpty()) {
            st.setString(5, String.join(",", destination.getImages()));
        } else {
            st.setString(5, null);
        }

        st.executeUpdate();
        st.close();
    }

    // ========================= MODIFIER =========================
    public void modifier(Destination d) throws SQLException {

        String sql = "UPDATE destination SET "
                + "nom=?, description=?, localisation=?, statut=?, images=? "
                + "WHERE id=?";

        PreparedStatement ps = con.prepareStatement(sql);

        ps.setString(1, d.getNom());
        ps.setString(2, d.getDescription());
        ps.setString(3, d.getLocalisation());
        ps.setString(4, d.getStatut());

        // Convert List<String> -> String
        if (d.getImages() != null && !d.getImages().isEmpty()) {
            ps.setString(5, String.join(",", d.getImages()));
        } else {
            ps.setString(5, null);
        }

        if (d.getId() == null) {
            throw new SQLException("Destination ID is null !");
        }

        ps.setInt(6, d.getId());

        ps.executeUpdate();
        ps.close();
    }

    // ========================= SUPPRIMER =========================
    public void supprimer(int id) throws SQLException {

        String sql = "DELETE FROM destination WHERE id=?";

        PreparedStatement ps = con.prepareStatement(sql);
        ps.setInt(1, id);
        ps.executeUpdate();
        ps.close();
    }

    // ========================= AFFICHER =========================
    public List<Destination> afficher() throws SQLException {

        List<Destination> destinations = new ArrayList<>();
        String sql = "SELECT * FROM destination";

        Statement st = con.createStatement();
        ResultSet rs = st.executeQuery(sql);

        while (rs.next()) {

            // Convert String -> List<String>
            String imagesString = rs.getString("images");
            List<String> images = new ArrayList<>();

            if (imagesString != null && !imagesString.isEmpty()) {
                images = Arrays.asList(imagesString.split(","));
            }

            Destination d = new Destination(
                    rs.getInt("id"),
                    rs.getString("nom"),
                    rs.getString("description"),
                    rs.getString("localisation"),
                    rs.getString("statut"),
                    images
            );

            destinations.add(d);
        }

        rs.close();
        st.close();

        return destinations;
    }



    public List<Destination> readAll() {
        List<Destination> list = new ArrayList<>();

        String query = "SELECT * FROM destination";

        try {
            Statement st = con.createStatement();
            ResultSet rs = st.executeQuery(query);

            while (rs.next()) {
                Destination d = new Destination(
                        rs.getInt("id"),
                        rs.getString("nom"),
                        rs.getString("description"),
                        rs.getString("localisation"),
                        rs.getString("statut"),
                        Collections.singletonList(rs.getString("images"))
                );
                list.add(d);
            }

        } catch (SQLException e) {
            System.out.println(e.getMessage());
        }

        return list;
    }
}
