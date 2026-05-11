package com.pi.dto;

import com.pi.entities.ProduitParent;
import java.util.ArrayList;
import java.util.List;

/**
 * DTO pour afficher un produit complet avec tous ses variants
 */
public class ProduitCompletDTO {
    private ProduitParent produitParent;
    private List<VariantCompletDTO> variants;
    private String categorieNom;
    private String sousCategorieNom;
    private double prixMin;
    private double prixMax;
    private int stockTotal;

    public ProduitCompletDTO() {
        this.variants = new ArrayList<>();
    }

    public ProduitCompletDTO(ProduitParent produitParent) {
        this.produitParent = produitParent;
        this.variants = new ArrayList<>();
    }

    // Getters et Setters
    public ProduitParent getProduitParent() { return produitParent; }
    public void setProduitParent(ProduitParent produitParent) { this.produitParent = produitParent; }

    public List<VariantCompletDTO> getVariants() { return variants; }
    public void setVariants(List<VariantCompletDTO> variants) { this.variants = variants; }

    public String getCategorieNom() { return categorieNom; }
    public void setCategorieNom(String categorieNom) { this.categorieNom = categorieNom; }

    public String getSousCategorieNom() { return sousCategorieNom; }
    public void setSousCategorieNom(String sousCategorieNom) { this.sousCategorieNom = sousCategorieNom; }

    public double getPrixMin() { return prixMin; }
    public void setPrixMin(double prixMin) { this.prixMin = prixMin; }

    public double getPrixMax() { return prixMax; }
    public void setPrixMax(double prixMax) { this.prixMax = prixMax; }

    public int getStockTotal() { return stockTotal; }
    public void setStockTotal(int stockTotal) { this.stockTotal = stockTotal; }

    // Méthodes utilitaires
    public void addVariant(VariantCompletDTO variant) {
        this.variants.add(variant);
        calculerPrixMinMax();
        calculerStockTotal();
    }

    private void calculerPrixMinMax() {
        if (variants.isEmpty()) {
            prixMin = 0;
            prixMax = 0;
            return;
        }

        prixMin = variants.stream()
                .mapToDouble(v -> v.getProduitVariant().getPrixVente())
                .min()
                .orElse(0);

        prixMax = variants.stream()
                .mapToDouble(v -> v.getProduitVariant().getPrixVente())
                .max()
                .orElse(0);
    }

    private void calculerStockTotal() {
        stockTotal = variants.stream()
                .mapToInt(v -> v.getProduitVariant().getQuantiteStock())
                .sum();
    }

    public String getPrixAffichage() {
        if (prixMin == prixMax) {
            return String.format("%.3f TND", prixMin);
        } else {
            return String.format("%.3f - %.3f TND", prixMin, prixMax);
        }
    }

    public boolean isEnStock() {
        return stockTotal > 0;
    }

    @Override
    public String toString() {
        return produitParent != null ? produitParent.getNom() + " (" + variants.size() + " variants)" : "Produit";
    }
}
