package com.pi.entities;

import java.util.Date;

public class commande {
    private int id;
    private int userId;
    private String clientNom;
    private Date dateCommande;
    private double total;
    private String statut;

    // Constructeurs
    public commande() {}

    public commande(int userId, String clientNom, Date dateCommande, double total, String statut) {
        this.userId = userId;
        this.clientNom = clientNom;
        this.dateCommande = dateCommande;
        this.total = total;
        this.statut = statut;
    }

    // Getters et Setters
    public int getId() { return id; }
    public void setId(int id) { this.id = id; }

    public int getUserId() { return userId; }
    public void setUserId(int userId) { this.userId = userId; }

    public String getClientNom() { return clientNom; }
    public void setClientNom(String clientNom) { this.clientNom = clientNom; }

    public Date getDateCommande() { return dateCommande; }
    public void setDateCommande(Date dateCommande) { this.dateCommande = dateCommande; }

    public double getTotal() { return total; }
    public void setTotal(double total) { this.total = total; }

    public String getStatut() { return statut; }
    public void setStatut(String statut) { this.statut = statut; }

    @Override
    public String toString() {
        return "Commande #" + id + " - " + clientNom + " (" + total + " TND) - " + statut;
    }
}