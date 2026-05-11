package com.pi.dto;

import com.pi.entities.ProduitVariant;
import com.pi.entities.OptionVariation;
import java.util.ArrayList;
import java.util.List;
import java.util.stream.Collectors;

/**
 * DTO pour afficher un variant avec toutes ses options
 */
public class VariantCompletDTO {
    private ProduitVariant produitVariant;
    private List<OptionVariation> options;

    public VariantCompletDTO() {
        this.options = new ArrayList<>();
    }

    public VariantCompletDTO(ProduitVariant produitVariant) {
        this.produitVariant = produitVariant;
        this.options = new ArrayList<>();
    }

    // Getters et Setters
    public ProduitVariant getProduitVariant() { return produitVariant; }
    public void setProduitVariant(ProduitVariant produitVariant) { this.produitVariant = produitVariant; }

    public List<OptionVariation> getOptions() { return options; }
    public void setOptions(List<OptionVariation> options) { this.options = options; }

    public void addOption(OptionVariation option) {
        this.options.add(option);
    }

    // Méthodes utilitaires
    public String getOptionsAffichage() {
        return options.stream()
                .map(OptionVariation::getValeur)
                .collect(Collectors.joining(" / "));
    }

    public boolean isEnStock() {
        return produitVariant != null && produitVariant.getQuantiteStock() > 0;
    }

    public boolean isStockFaible() {
        return produitVariant != null && 
               produitVariant.getQuantiteStock() > 0 && 
               produitVariant.getQuantiteStock() <= produitVariant.getSeuilAlerte();
    }

    public String getImageUrl() {
        if (produitVariant != null && produitVariant.getImageSpecifique() != null) {
            return produitVariant.getImageSpecifique();
        }
        return null;
    }

    @Override
    public String toString() {
        if (produitVariant != null) {
            return produitVariant.getSku() + " - " + getOptionsAffichage();
        }
        return "Variant";
    }
}
