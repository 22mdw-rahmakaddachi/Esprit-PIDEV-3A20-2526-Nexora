package com.pi.entities;

import java.util.Date;
import java.util.List;

public class Destination {
    private Integer id;
    private String nom;
    private String description;
    private String localisation;
    private String statut;
    private List<String> images;
    private List<String> images1;

    public List<String> getImages1() {
        return images1;
    }

    public void setImages1(List<String> images1) {
        this.images1 = images1;
    }

    public Integer getId() {
        return id;
    }

    public void setId(Integer id) {
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

    public String getLocalisation() {
        return localisation;
    }

    public void setLocalisation(String localisation) {
        this.localisation = localisation;
    }

    public String getStatut() {
        return statut;
    }

    public void setStatut(String statut) {
        this.statut = statut;
    }

    public List<String> getImages() {
        return images;
    }

    public void setImages(List<String> images) {
        this.images = images;
    }


    public Destination(String nom, String description, String localisation, String statut, List<String> images) {
        this.nom = nom;
        this.description = description;
        this.localisation = localisation;
        this.statut = statut;
        this.images = images;
    }

    public Destination(Integer id, String nom, String description, String localisation, String statut, List<String> images) {
        this.id = id;
        this.nom = nom;
        this.description = description;
        this.localisation = localisation;
        this.statut = statut;
        this.images = images;
    }
}
