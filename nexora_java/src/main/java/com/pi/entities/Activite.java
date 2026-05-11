package com.pi.entities;

import java.time.LocalDate;

public class Activite {
    private int id;
    private String nom;
    private String description;
    private String type;
    private String lieu;
    private LocalDate dateActivite;
    private String images;
    private double prix;
    private int nombrePlaces;
    private int placesDisponibles;
    private int partenaireId;
    private LocalDate dateCreation;
    private String partenaireNom;
    private String partenaireEmail;
    private String partenaireTelephone;

    public Activite() {
        this.dateCreation = LocalDate.now();
    }

    public Activite(String nom, String type, String lieu, LocalDate dateActivite,
                    String images, double prix, int nombrePlaces, int partenaireId) {
        this.nom = nom;
        this.type = type;
        this.lieu = lieu;
        this.dateActivite = dateActivite;
        this.images = images;
        this.prix = prix;
        this.nombrePlaces = nombrePlaces;
        this.placesDisponibles = nombrePlaces;
        this.partenaireId = partenaireId;
        this.dateCreation = LocalDate.now();
    }

    public Activite(String nom, String description, String type, String lieu, LocalDate dateActivite,
                    String images, double prix, int nombrePlaces, int partenaireId) {
        this.nom = nom;
        this.description = description;
        this.type = type;
        this.lieu = lieu;
        this.dateActivite = dateActivite;
        this.images = images;
        this.prix = prix;
        this.nombrePlaces = nombrePlaces;
        this.placesDisponibles = nombrePlaces;
        this.partenaireId = partenaireId;
        this.dateCreation = LocalDate.now();
    }

    // Getters et Setters
    public int getId() {
        return id;
    }

    public void setId(int id) {
        this.id = id;
    }

    public String getNom() {
        return nom;
    }

    public void setNom(String nom) {
        this.nom = nom;
    }

    public String getDescription() {
        return description;
    }

    public void setDescription(String description) {
        this.description = description;
    }

    public String getType() {
        return type;
    }

    public void setType(String type) {
        this.type = type;
    }

    public String getLieu() {
        return lieu;
    }

    public void setLieu(String lieu) {
        this.lieu = lieu;
    }

    public LocalDate getDateActivite() {
        return dateActivite;
    }

    public void setDateActivite(LocalDate dateActivite) {
        this.dateActivite = dateActivite;
    }

    public String getImages() {
        return images;
    }

    public void setImages(String images) {
        this.images = images;
    }

    public double getPrix() {
        return prix;
    }

    public void setPrix(double prix) {
        this.prix = prix;
    }

    public int getNombrePlaces() {
        return nombrePlaces;
    }

    public void setNombrePlaces(int nombrePlaces) {
        this.nombrePlaces = nombrePlaces;
    }

    public int getPlacesDisponibles() {
        return placesDisponibles;
    }

    public void setPlacesDisponibles(int placesDisponibles) {
        this.placesDisponibles = placesDisponibles;
    }

    public int getPartenaireId() {
        return partenaireId;
    }

    public void setPartenaireId(int partenaireId) {
        this.partenaireId = partenaireId;
    }

    public LocalDate getDateCreation() {
        return dateCreation;
    }

    public void setDateCreation(LocalDate dateCreation) {
        this.dateCreation = dateCreation;
    }

    public String getPartenaireNom() {
        return partenaireNom;
    }

    public void setPartenaireNom(String partenaireNom) {
        this.partenaireNom = partenaireNom;
    }

    public String getPartenaireEmail() {
        return partenaireEmail;
    }

    public void setPartenaireEmail(String partenaireEmail) {
        this.partenaireEmail = partenaireEmail;
    }

    public String getPartenaireTelephone() {
        return partenaireTelephone;
    }

    public void setPartenaireTelephone(String partenaireTelephone) {
        this.partenaireTelephone = partenaireTelephone;
    }

    @Override
    public String toString() {
        return "Activite{" +
                "id=" + id +
                ", nom='" + nom + '\'' +
                ", description='" + description + '\'' +
                ", type='" + type + '\'' +
                ", lieu='" + lieu + '\'' +
                ", dateActivite=" + dateActivite +
                ", prix=" + prix +
                ", placesDisponibles=" + placesDisponibles +
                '}';
    }
}
