package com.pi.entities;

public class PanierItem {
    private int id;
    private int produitId;
    private String variantSku;  // Pour le nouveau système de variants
    private String produitNom;
    private double prixUnitaire;
    private int quantite;
    private double total;

    // Constructeurs
    public PanierItem() {}

    // Getters et Setters
    public int getId() { return id; }
    public void setId(int id) { this.id = id; }

    public int getProduitId() { return produitId; }
    public void setProduitId(int produitId) { this.produitId = produitId; }

    public String getVariantSku() { return variantSku; }
    public void setVariantSku(String variantSku) { this.variantSku = variantSku; }

    public String getProduitNom() { return produitNom; }
    public void setProduitNom(String produitNom) { this.produitNom = produitNom; }

    public double getPrixUnitaire() { return prixUnitaire; }
    public void setPrixUnitaire(double prixUnitaire) { this.prixUnitaire = prixUnitaire; }

    public int getQuantite() { return quantite; }
    public void setQuantite(int quantite) {
        this.quantite = quantite;
        this.total = this.prixUnitaire * this.quantite;
    }

    public double getTotal() { return total; }
    public void setTotal(double total) { this.total = total; }

    @Override
    public String toString() {
        return produitNom + " x" + quantite + " = " + total + " TND";
    }
}