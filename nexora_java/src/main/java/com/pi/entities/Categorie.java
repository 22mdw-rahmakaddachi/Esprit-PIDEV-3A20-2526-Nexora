package com.pi.entities;

public class Categorie {
    private int id;
    private String nom;
    private String description;
    private String imageUrl;
    private int ordre;
    private boolean actif;

    // Constructeurs
    public Categorie() {}

    public Categorie(String nom, String description) {
        this.nom = nom;
        this.description = description;
        this.actif = true;
    }

    // Getters et Setters
    public int getId() { return id; }
    public void setId(int id) { this.id = id; }

    public String getNom() { return nom; }
    public void setNom(String nom) { this.nom = nom; }

    public String getDescription() { return description; }
    public void setDescription(String description) { this.description = description; }

    public String getImageUrl() { return imageUrl; }
    public void setImageUrl(String imageUrl) { this.imageUrl = imageUrl; }

    public int getOrdre() { return ordre; }
    public void setOrdre(int ordre) { this.ordre = ordre; }

    public boolean isActif() { return actif; }
    public void setActif(boolean actif) { this.actif = actif; }

    @Override
    public String toString() {
        return nom;
    }
}
