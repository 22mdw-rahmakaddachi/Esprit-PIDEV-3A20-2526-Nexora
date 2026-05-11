package com.pi.entities;

public class VariantOption {
    private int id;
    private int variantId;
    private int optionId;

    // Constructeurs
    public VariantOption() {}

    public VariantOption(int variantId, int optionId) {
        this.variantId = variantId;
        this.optionId = optionId;
    }

    // Getters et Setters
    public int getId() { return id; }
    public void setId(int id) { this.id = id; }

    public int getVariantId() { return variantId; }
    public void setVariantId(int variantId) { this.variantId = variantId; }

    public int getOptionId() { return optionId; }
    public void setOptionId(int optionId) { this.optionId = optionId; }
}
