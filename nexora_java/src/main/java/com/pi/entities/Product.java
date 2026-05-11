// src/main/java/com/pi/entities/product/Product.java
package com.pi.entities;

public class Product {
    private int id;
    private String nom;
    private String description;
    private double prix;
    private int quantite;
    private int partenaireId;
    private String categorie;
    private String statut;
    private String imageUrl;

    // Constructeurs
    public Product() {}

    public Product(String nom, double prix, int partenaireId) {
        this.nom = nom;
        this.prix = prix;
        this.partenaireId = partenaireId;
        this.quantite = 0;
        this.statut = "actif";
    }

    // Getters et Setters
    public int getId() { return id; }
    public void setId(int id) { this.id = id; }

    public String getNom() { return nom; }
    public void setNom(String nom) { this.nom = nom; }

    public String getDescription() { return description; }
    public void setDescription(String description) { this.description = description; }

    public double getPrix() { return prix; }
    public void setPrix(double prix) { this.prix = prix; }

    public int getQuantite() { return quantite; }
    public void setQuantite(int quantite) { this.quantite = quantite; }

    public int getPartenaireId() { return partenaireId; }
    public void setPartenaireId(int partenaireId) { this.partenaireId = partenaireId; }

    public String getCategorie() { return categorie; }
    public void setCategorie(String categorie) { this.categorie = categorie; }

    public String getStatut() { return statut; }
    public void setStatut(String statut) { this.statut = statut; }

    public String getImageUrl() { return imageUrl; }
    public void setImageUrl(String imageUrl) { this.imageUrl = imageUrl; }

    @Override
    public String toString() {
        return id + " - " + nom + " (" + prix + " TND) - Stock: " + quantite;
    }
}