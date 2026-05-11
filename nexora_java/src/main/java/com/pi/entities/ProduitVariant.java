package com.pi.entities;

import java.sql.Timestamp;

public class ProduitVariant {
    private int id;
    private int produitParentId;
    private String sku;
    private double prixAchat;
    private double prixVente;
    private double prixPromo;
    private int quantiteStock;
    private int seuilAlerte;
    private String imageSpecifique;
    private String codeBarres;
    private Timestamp dateCreation;

    // Constructeurs
    public ProduitVariant() {}

    public ProduitVariant(int produitParentId, String sku, double prixVente) {
        this.produitParentId = produitParentId;
        this.sku = sku;
        this.prixVente = prixVente;
        this.quantiteStock = 0;
        this.seuilAlerte = 5;
    }

    // Getters et Setters
    public int getId() { return id; }
    public void setId(int id) { this.id = id; }

    public int getProduitParentId() { return produitParentId; }
    public void setProduitParentId(int produitParentId) { this.produitParentId = produitParentId; }

    public String getSku() { return sku; }
    public void setSku(String sku) { this.sku = sku; }

    public double getPrixAchat() { return prixAchat; }
    public void setPrixAchat(double prixAchat) { this.prixAchat = prixAchat; }

    public double getPrixVente() { return prixVente; }
    public void setPrixVente(double prixVente) { this.prixVente = prixVente; }

    public double getPrixPromo() { return prixPromo; }
    public void setPrixPromo(double prixPromo) { this.prixPromo = prixPromo; }

    public int getQuantiteStock() { return quantiteStock; }
    public void setQuantiteStock(int quantiteStock) { this.quantiteStock = quantiteStock; }

    public int getSeuilAlerte() { return seuilAlerte; }
    public void setSeuilAlerte(int seuilAlerte) { this.seuilAlerte = seuilAlerte; }

    public String getImageSpecifique() { return imageSpecifique; }
    public void setImageSpecifique(String imageSpecifique) { this.imageSpecifique = imageSpecifique; }

    public String getCodeBarres() { return codeBarres; }
    public void setCodeBarres(String codeBarres) { this.codeBarres = codeBarres; }

    public Timestamp getDateCreation() { return dateCreation; }
    public void setDateCreation(Timestamp dateCreation) { this.dateCreation = dateCreation; }

    @Override
    public String toString() {
        return sku + " - " + prixVente + " TND";
    }
}
