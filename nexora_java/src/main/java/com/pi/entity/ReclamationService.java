package com.pi.entity;

import com.pi.entities.Reclamation;
import com.pi.utils.mydatabase;

import java.sql.*;
import java.util.ArrayList;
import java.util.List;

public class ReclamationService {

    Connection con = mydatabase.getInstance().getConnection();

    // AJOUTER
    public void ajouter(Reclamation r){

        try{

            String sql="INSERT INTO reclamation(client_id,activite_id,description,statut) VALUES(?,?,?,?)";
            PreparedStatement ps=con.prepareStatement(sql);
            ps.setInt(1,r.getClientId());
            ps.setInt(2,r.getActiviteId());
            ps.setString(3,r.getDescription());
            ps.setString(4,r.getStatut());

            ps.executeUpdate();

        }catch(Exception e){e.printStackTrace();}
    }

    // MODIFIER
    public void modifier(Reclamation r){

        try{

            String sql="UPDATE reclamation SET description=? WHERE client_id=? AND activite_id=?";
            PreparedStatement ps=con.prepareStatement(sql);
            ps.setString(1,r.getDescription());
            ps.setInt(2,r.getClientId());
            ps.setInt(3,r.getActiviteId());

            ps.executeUpdate();

        }catch(Exception e){e.printStackTrace();}
    }

    // SUPPRIMER
    public void supprimer(int clientId,int activiteId){

        try{

            String sql="DELETE FROM reclamation WHERE client_id=? AND activite_id=?";
            PreparedStatement ps=con.prepareStatement(sql);
            ps.setInt(1,clientId);
            ps.setInt(2,activiteId);

            ps.executeUpdate();

        }catch(Exception e){e.printStackTrace();}
    }

    // AFFICHER
    public List<Reclamation> afficher(int clientId){

        List<Reclamation> list=new ArrayList<>();

        try{

            String sql="SELECT * FROM reclamation WHERE client_id=?";
            PreparedStatement ps=con.prepareStatement(sql);
            ps.setInt(1,clientId);

            ResultSet rs=ps.executeQuery();

            while(rs.next()){

                Reclamation r=new Reclamation();
                r.setId(rs.getInt("id"));
                r.setClientId(rs.getInt("client_id"));
                r.setActiviteId(rs.getInt("activite_id"));
                r.setDescription(rs.getString("description"));
                r.setStatut(rs.getString("statut"));
                r.setDateCreation(rs.getTimestamp("date_creation"));

                list.add(r);
            }

        }catch(Exception e){e.printStackTrace();}

        return list;
    }
}