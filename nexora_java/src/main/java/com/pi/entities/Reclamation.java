package com.pi.entities;

import java.sql.Timestamp;

public class Reclamation {

    private int id;
    private int clientId;
    private int activiteId;
    private String description;
    private String statut;
    private Timestamp dateCreation;

    public Reclamation(){}

    public Reclamation(int clientId,int activiteId,String description,String statut){
        this.clientId=clientId;
        this.activiteId=activiteId;
        this.description=description;
        this.statut=statut;
    }

    public int getId(){return id;}
    public void setId(int id){this.id=id;}

    public int getClientId(){return clientId;}
    public void setClientId(int clientId){this.clientId=clientId;}

    public int getActiviteId(){return activiteId;}
    public void setActiviteId(int activiteId){this.activiteId=activiteId;}

    public String getDescription(){return description;}
    public void setDescription(String description){this.description=description;}

    public String getStatut(){return statut;}
    public void setStatut(String statut){this.statut=statut;}

    public Timestamp getDateCreation(){return dateCreation;}
    public void setDateCreation(Timestamp dateCreation){this.dateCreation=dateCreation;}

    // Propriété calculée pour l'affichage dans les tables
    private String activiteNom;

    public String getActiviteNom() { return activiteNom; }
    public void setActiviteNom(String activiteNom) { this.activiteNom = activiteNom; }
}