package com.pi.dto;

import com.pi.entities.AttributVariation;
import com.pi.entities.OptionVariation;
import java.util.ArrayList;
import java.util.List;

/**
 * DTO pour afficher un attribut avec toutes ses options
 */
public class AttributAvecOptionsDTO {
    private AttributVariation attribut;
    private List<OptionVariation> options;

    public AttributAvecOptionsDTO() {
        this.options = new ArrayList<>();
    }

    public AttributAvecOptionsDTO(AttributVariation attribut) {
        this.attribut = attribut;
        this.options = new ArrayList<>();
    }

    // Getters et Setters
    public AttributVariation getAttribut() { return attribut; }
    public void setAttribut(AttributVariation attribut) { this.attribut = attribut; }

    public List<OptionVariation> getOptions() { return options; }
    public void setOptions(List<OptionVariation> options) { this.options = options; }

    public void addOption(OptionVariation option) {
        this.options.add(option);
    }

    @Override
    public String toString() {
        return attribut != null ? attribut.getNom() + " (" + options.size() + " options)" : "Attribut";
    }
}
