package com.pi.entities;

public class CommandeItem {
    private int id;
    private int commandeId;
    private int produitId;
    private String produitNom;
    private int quantite;
    private double prixUnitaire;
    private double sousTotal;

    // Constructeurs
    public CommandeItem() {}

    // Getters et Setters
    public int getId() { return id; }
    public void setId(int id) { this.id = id; }

    public int getCommandeId() { return commandeId; }
    public void setCommandeId(int commandeId) { this.commandeId = commandeId; }

    public int getProduitId() { return produitId; }
    public void setProduitId(int produitId) { this.produitId = produitId; }

    public String getProduitNom() { return produitNom; }
    public void setProduitNom(String produitNom) { this.produitNom = produitNom; }

    public int getQuantite() { return quantite; }
    public void setQuantite(int quantite) {
        this.quantite = quantite;
        this.sousTotal = this.quantite * this.prixUnitaire;
    }

    public double getPrixUnitaire() { return prixUnitaire; }
    public void setPrixUnitaire(double prixUnitaire) {
        this.prixUnitaire = prixUnitaire;
        this.sousTotal = this.quantite * this.prixUnitaire;
    }

    public double getSousTotal() { return sousTotal; }
    public void setSousTotal(double sousTotal) { this.sousTotal = sousTotal; }

    @Override
    public String toString() {
        return produitNom + " x" + quantite + " = " + sousTotal + " TND";
    }
}