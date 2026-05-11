package com.pi.entities;

public class AttributVariation {
    private int id;
    private String nom;
    private String typeAffichage; // dropdown, color, button, text
    private int ordre;
    private boolean actif;

    // Constructeurs
    public AttributVariation() {}

    public AttributVariation(String nom, String typeAffichage) {
        this.nom = nom;
        this.typeAffichage = typeAffichage;
        this.actif = true;
    }

    // Getters et Setters
    public int getId() { return id; }
    public void setId(int id) { this.id = id; }

    public String getNom() { return nom; }
    public void setNom(String nom) { this.nom = nom; }

    public String getTypeAffichage() { return typeAffichage; }
    public void setTypeAffichage(String typeAffichage) { this.typeAffichage = typeAffichage; }

    public int getOrdre() { return ordre; }
    public void setOrdre(int ordre) { this.ordre = ordre; }

    public boolean isActif() { return actif; }
    public void setActif(boolean actif) { this.actif = actif; }

    @Override
    public String toString() {
        return nom;
    }
}
