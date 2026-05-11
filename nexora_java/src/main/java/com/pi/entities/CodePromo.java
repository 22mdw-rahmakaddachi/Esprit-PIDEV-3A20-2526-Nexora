package com.pi.entities;

import java.util.Date;

public class CodePromo {
    private int id;
    private String code;
    private String description;
    private TypeReduction typeReduction;
    private double valeurReduction;
    private double montantMinimum;
    private Date dateDebut;
    private Date dateFin;
    private Integer limiteUtilisation;
    private int nombreUtilisations;
    private boolean actif;
    private Integer partenaireId;
    private Integer categorieId;
    private boolean premiereCommandeSeulement;
    private Date dateCreation;

    public enum TypeReduction {
        POURCENTAGE,
        MONTANT_FIXE,
        LIVRAISON_GRATUITE
    }

    // Constructeurs
    public CodePromo() {}

    public CodePromo(String code, TypeReduction typeReduction, double valeurReduction) {
        this.code = code;
        this.typeReduction = typeReduction;
        this.valeurReduction = valeurReduction;
        this.actif = true;
        this.nombreUtilisations = 0;
    }

    // Getters et Setters
    public int getId() { return id; }
    public void setId(int id) { this.id = id; }

    public String getCode() { return code; }
    public void setCode(String code) { this.code = code.toUpperCase(); }

    public String getDescription() { return description; }
    public void setDescription(String description) { this.description = description; }

    public TypeReduction getTypeReduction() { return typeReduction; }
    public void setTypeReduction(TypeReduction typeReduction) { this.typeReduction = typeReduction; }

    public double getValeurReduction() { return valeurReduction; }
    public void setValeurReduction(double valeurReduction) { this.valeurReduction = valeurReduction; }

    public double getMontantMinimum() { return montantMinimum; }
    public void setMontantMinimum(double montantMinimum) { this.montantMinimum = montantMinimum; }

    public Date getDateDebut() { return dateDebut; }
    public void setDateDebut(Date dateDebut) { this.dateDebut = dateDebut; }

    public Date getDateFin() { return dateFin; }
    public void setDateFin(Date dateFin) { this.dateFin = dateFin; }

    public Integer getLimiteUtilisation() { return limiteUtilisation; }
    public void setLimiteUtilisation(Integer limiteUtilisation) { this.limiteUtilisation = limiteUtilisation; }

    public int getNombreUtilisations() { return nombreUtilisations; }
    public void setNombreUtilisations(int nombreUtilisations) { this.nombreUtilisations = nombreUtilisations; }

    public boolean isActif() { return actif; }
    public void setActif(boolean actif) { this.actif = actif; }

    public Integer getPartenaireId() { return partenaireId; }
    public void setPartenaireId(Integer partenaireId) { this.partenaireId = partenaireId; }

    public Integer getCategorieId() { return categorieId; }
    public void setCategorieId(Integer categorieId) { this.categorieId = categorieId; }

    public boolean isPremiereCommandeSeulement() { return premiereCommandeSeulement; }
    public void setPremiereCommandeSeulement(boolean premiereCommandeSeulement) { 
        this.premiereCommandeSeulement = premiereCommandeSeulement; 
    }

    public Date getDateCreation() { return dateCreation; }
    public void setDateCreation(Date dateCreation) { this.dateCreation = dateCreation; }

    // Méthodes utiles
    public boolean estValide() {
        if (!actif) return false;
        
        Date maintenant = new Date();
        if (dateDebut != null && maintenant.before(dateDebut)) return false;
        if (dateFin != null && maintenant.after(dateFin)) return false;
        
        if (limiteUtilisation != null && nombreUtilisations >= limiteUtilisation) return false;
        
        return true;
    }

    public double calculerReduction(double montantCommande) {
        if (!estValide()) return 0.0;
        if (montantCommande < montantMinimum) return 0.0;

        switch (typeReduction) {
            case POURCENTAGE:
                return montantCommande * (valeurReduction / 100.0);
            case MONTANT_FIXE:
                return Math.min(valeurReduction, montantCommande);
            case LIVRAISON_GRATUITE:
                return 0.0; // Géré séparément
            default:
                return 0.0;
        }
    }

    @Override
    public String toString() {
        return code + " - " + description;
    }
}
