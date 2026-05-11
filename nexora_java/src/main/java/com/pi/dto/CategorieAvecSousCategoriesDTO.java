package com.pi.dto;

import com.pi.entities.Categorie;
import com.pi.entities.SousCategorie;
import java.util.ArrayList;
import java.util.List;

/**
 * DTO pour afficher une catégorie avec toutes ses sous-catégories
 */
public class CategorieAvecSousCategoriesDTO {
    private Categorie categorie;
    private List<SousCategorie> sousCategories;
    private int nombreProduits;

    public CategorieAvecSousCategoriesDTO() {
        this.sousCategories = new ArrayList<>();
    }

    public CategorieAvecSousCategoriesDTO(Categorie categorie) {
        this.categorie = categorie;
        this.sousCategories = new ArrayList<>();
    }

    // Getters et Setters
    public Categorie getCategorie() { return categorie; }
    public void setCategorie(Categorie categorie) { this.categorie = categorie; }

    public List<SousCategorie> getSousCategories() { return sousCategories; }
    public void setSousCategories(List<SousCategorie> sousCategories) { this.sousCategories = sousCategories; }

    public int getNombreProduits() { return nombreProduits; }
    public void setNombreProduits(int nombreProduits) { this.nombreProduits = nombreProduits; }

    public void addSousCategorie(SousCategorie sousCategorie) {
        this.sousCategories.add(sousCategorie);
    }

    @Override
    public String toString() {
        return categorie != null ? categorie.getNom() + " (" + sousCategories.size() + " sous-catégories)" : "Catégorie";
    }
}
