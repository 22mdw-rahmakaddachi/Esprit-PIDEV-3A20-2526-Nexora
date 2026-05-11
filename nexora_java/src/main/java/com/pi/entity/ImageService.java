package com.pi.entity;

import com.pi.entities.image;
import com.pi.utils.mydatabase;

import java.sql.*;
import java.util.ArrayList;
import java.util.List;

public class ImageService {

    private Connection conn = mydatabase.getInstance().getConnection();

    // Ajouter image
    public void ajouter(image image) throws SQLException {

        String sql = "INSERT INTO images (url, destination_id) VALUES (?, ?)";

        PreparedStatement ps = conn.prepareStatement(sql);
        ps.setString(1, image.getUrl());
        ps.setInt(2, image.getDestinationId());
        ps.executeUpdate();
    }

    // Supprimer image
    public void supprimer(int id) throws SQLException {

        String sql = "DELETE FROM images WHERE id = ?";
        PreparedStatement ps = conn.prepareStatement(sql);
        ps.setInt(1, id);
        ps.executeUpdate();
    }

    // Afficher images d’une destination
    public List<image> afficherParDestination(int destinationId) throws SQLException {

        List<image> list = new ArrayList<>();

        String sql = "SELECT * FROM images WHERE destination_id = ?";
        PreparedStatement ps = conn.prepareStatement(sql);
        ps.setInt(1, destinationId);

        ResultSet rs = ps.executeQuery();

        while (rs.next()) {
            list.add(new image(
                    rs.getInt("id"),
                    rs.getString("url"),
                    rs.getInt("destination_id")
            ));
        }

        return list;
    }
}