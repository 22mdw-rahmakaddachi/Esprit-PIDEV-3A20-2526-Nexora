package com.pi.entities;

import java.util.Date;

public class Partenaire {
    private int id;
    private int userId;
    private String nomEntreprise;
    private String ice;
    private String responsableNom;
    private String responsableTelephone;
    private String adresseEntreprise;
    private String siteWeb;
    private String description;
    private String statut;
    private Date dateValidation;
    private double commission;
    private Date dateInscription;

    // Constructeur vide
    public Partenaire() {}

    // Constructeur avec paramètres principaux
    public Partenaire(int userId, String nomEntreprise, String responsableNom) {
        this.userId = userId;
        this.nomEntreprise = nomEntreprise;
        this.responsableNom = responsableNom;
        this.statut = "actif";
        this.dateInscription = new Date();
        this.commission = 10.0;
    }

    // Getters et Setters
    public int getId() { return id; }
    public void setId(int id) { this.id = id; }

    public int getUserId() { return userId; }
    public void setUserId(int userId) { this.userId = userId; }

    public String getNomEntreprise() { return nomEntreprise; }
    public void setNomEntreprise(String nomEntreprise) { this.nomEntreprise = nomEntreprise; }

    public String getIce() { return ice; }
    public void setIce(String ice) { this.ice = ice; }

    public String getResponsableNom() { return responsableNom; }
    public void setResponsableNom(String responsableNom) { this.responsableNom = responsableNom; }

    public String getResponsableTelephone() { return responsableTelephone; }
    public void setResponsableTelephone(String responsableTelephone) { this.responsableTelephone = responsableTelephone; }

    public String getAdresseEntreprise() { return adresseEntreprise; }
    public void setAdresseEntreprise(String adresseEntreprise) { this.adresseEntreprise = adresseEntreprise; }

    public String getSiteWeb() { return siteWeb; }
    public void setSiteWeb(String siteWeb) { this.siteWeb = siteWeb; }

    public String getDescription() { return description; }
    public void setDescription(String description) { this.description = description; }

    public String getStatut() { return statut; }
    public void setStatut(String statut) { this.statut = statut; }

    public Date getDateValidation() { return dateValidation; }
    public void setDateValidation(Date dateValidation) { this.dateValidation = dateValidation; }

    public double getCommission() { return commission; }
    public void setCommission(double commission) { this.commission = commission; }

    public Date getDateInscription() { return dateInscription; }
    public void setDateInscription(Date dateInscription) { this.dateInscription = dateInscription; }

    @Override
    public String toString() {
        return "Partenaire #" + id + " - " + nomEntreprise + " (" + responsableNom + ")";
    }
}