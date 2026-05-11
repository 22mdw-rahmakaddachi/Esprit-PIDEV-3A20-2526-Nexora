package com.pi.entities;

import java.sql.Timestamp;

public class ProduitParent {
    private int id;
    private int partenaireId;
    private int sousCategorieId;
    private String nom;
    private String description;
    private String descriptionCourte;
    private String marque;
    private String materiau;
    private double poidsKg;
    private String dimensionsCm;
    private String imagePrincipale;
    private Timestamp dateAjout;
    private String statut; // BROUILLON, ACTIF, INACTIF

    // Constructeurs
    public ProduitParent() {}

    public ProduitParent(int partenaireId, int sousCategorieId, String nom) {
        this.partenaireId = partenaireId;
        this.sousCategorieId = sousCategorieId;
        this.nom = nom;
        this.statut = "ACTIF";
    }

    // Getters et Setters
    public int getId() { return id; }
    public void setId(int id) { this.id = id; }

    public int getPartenaireId() { return partenaireId; }
    public void setPartenaireId(int partenaireId) { this.partenaireId = partenaireId; }

    public int getSousCategorieId() { return sousCategorieId; }
    public void setSousCategorieId(int sousCategorieId) { this.sousCategorieId = sousCategorieId; }

    public String getNom() { return nom; }
    public void setNom(String nom) { this.nom = nom; }

    public String getDescription() { return description; }
    public void setDescription(String description) { this.description = description; }

    public String getDescriptionCourte() { return descriptionCourte; }
    public void setDescriptionCourte(String descriptionCourte) { this.descriptionCourte = descriptionCourte; }

    public String getMarque() { return marque; }
    public void setMarque(String marque) { this.marque = marque; }

    public String getMateriau() { return materiau; }
    public void setMateriau(String materiau) { this.materiau = materiau; }

    public double getPoidsKg() { return poidsKg; }
    public void setPoidsKg(double poidsKg) { this.poidsKg = poidsKg; }

    public String getDimensionsCm() { return dimensionsCm; }
    public void setDimensionsCm(String dimensionsCm) { this.dimensionsCm = dimensionsCm; }

    public String getImagePrincipale() { return imagePrincipale; }
    public void setImagePrincipale(String imagePrincipale) { this.imagePrincipale = imagePrincipale; }

    public Timestamp getDateAjout() { return dateAjout; }
    public void setDateAjout(Timestamp dateAjout) { this.dateAjout = dateAjout; }

    public String getStatut() { return statut; }
    public void setStatut(String statut) { this.statut = statut; }

    @Override
    public String toString() {
        return nom;
    }
}
