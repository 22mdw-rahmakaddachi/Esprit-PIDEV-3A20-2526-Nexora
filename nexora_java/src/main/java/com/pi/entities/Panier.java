package com.pi.entities;

import java.util.Date;

public class Panier {
    private int id;
    private int clientId;
    private int produitId;
    private int quantite;
    private Date dateAjout;

    // Constructeurs
    public Panier() {}

    public Panier(int clientId, int produitId, int quantite) {
        this.clientId = clientId;
        this.produitId = produitId;
        this.quantite = quantite;
        this.dateAjout = new Date();
    }

    // Getters et Setters
    public int getId() { return id; }
    public void setId(int id) { this.id = id; }

    public int getClientId() { return clientId; }
    public void setClientId(int clientId) { this.clientId = clientId; }

    public int getProduitId() { return produitId; }
    public void setProduitId(int produitId) { this.produitId = produitId; }

    public int getQuantite() { return quantite; }
    public void setQuantite(int quantite) { this.quantite = quantite; }

    public Date getDateAjout() { return dateAjout; }
    public void setDateAjout(Date dateAjout) { this.dateAjout = dateAjout; }

    @Override
    public String toString() {
        return "Panier #" + id + " - Client: " + clientId + " - Produit: " + produitId + " x" + quantite;
    }
}