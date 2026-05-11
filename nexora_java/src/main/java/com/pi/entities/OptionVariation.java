package com.pi.entities;

public class OptionVariation {
    private int id;
    private int attributId;
    private String valeur;
    private String codeHexadecimal; // Pour les couleurs
    private int ordreAffichage;

    // Constructeurs
    public OptionVariation() {}

    public OptionVariation(int attributId, String valeur) {
        this.attributId = attributId;
        this.valeur = valeur;
    }

    // Getters et Setters
    public int getId() { return id; }
    public void setId(int id) { this.id = id; }

    public int getAttributId() { return attributId; }
    public void setAttributId(int attributId) { this.attributId = attributId; }

    public String getValeur() { return valeur; }
    public void setValeur(String valeur) { this.valeur = valeur; }

    public String getCodeHexadecimal() { return codeHexadecimal; }
    public void setCodeHexadecimal(String codeHexadecimal) { this.codeHexadecimal = codeHexadecimal; }

    public int getOrdreAffichage() { return ordreAffichage; }
    public void setOrdreAffichage(int ordreAffichage) { this.ordreAffichage = ordreAffichage; }

    @Override
    public String toString() {
        return valeur;
    }
}
