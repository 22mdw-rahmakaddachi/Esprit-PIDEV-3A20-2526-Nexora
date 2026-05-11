package com.pi.entity;

import com.pi.dto.*;
import com.pi.entities.*;
import java.sql.SQLException;
import java.util.ArrayList;
import java.util.List;

/**
 * Service pour gérer le catalogue complet avec variants
 */
public class CatalogueService {
    
    private ProduitParentService produitParentService;
    private ProduitVariantService produitVariantService;
    private VariantOptionService variantOptionService;
    private OptionVariationService optionVariationService;
    private AttributVariationService attributVariationService;
    private CategorieService categorieService;
    private SousCategorieService sousCategorieService;

    public CatalogueService() {
        this.produitParentService = new ProduitParentService();
        this.produitVariantService = new ProduitVariantService();
        this.variantOptionService = new VariantOptionService();
        this.optionVariationService = new OptionVariationService();
        this.attributVariationService = new AttributVariationService();
        this.categorieService = new CategorieService();
        this.sousCategorieService = new SousCategorieService();
    }

    /**
     * Récupérer un produit complet avec tous ses variants et options
     */
    public ProduitCompletDTO getProduitComplet(int produitParentId) throws SQLException {
        // Récupérer le produit parent
        ProduitParent parent = produitParentService.getById(produitParentId);
        if (parent == null) {
            return null;
        }

        ProduitCompletDTO dto = new ProduitCompletDTO(parent);

        // Récupérer la sous-catégorie et catégorie
        SousCategorie sousCategorie = sousCategorieService.getById(parent.getSousCategorieId());
        if (sousCategorie != null) {
            dto.setSousCategorieNom(sousCategorie.getNom());
            Categorie categorie = categorieService.getById(sousCategorie.getCategorieId());
            if (categorie != null) {
                dto.setCategorieNom(categorie.getNom());
            }
        }

        // Récupérer tous les variants
        List<ProduitVariant> variants = produitVariantService.getByProduitParent(produitParentId);
        
        for (ProduitVariant variant : variants) {
            VariantCompletDTO variantDTO = new VariantCompletDTO(variant);
            
            // Récupérer les options du variant
            List<Integer> optionIds = variantOptionService.getOptionsByVariant(variant.getId());
            for (Integer optionId : optionIds) {
                OptionVariation option = optionVariationService.getById(optionId);
                if (option != null) {
                    variantDTO.addOption(option);
                }
            }
            
            dto.addVariant(variantDTO);
        }

        return dto;
    }

    /**
     * Récupérer tous les produits d'un partenaire avec leurs variants
     */
    public List<ProduitCompletDTO> getProduitsCompletsByPartenaire(int partenaireId) throws SQLException {
        List<ProduitCompletDTO> produits = new ArrayList<>();
        List<ProduitParent> parents = produitParentService.getByPartenaire(partenaireId);

        for (ProduitParent parent : parents) {
            ProduitCompletDTO dto = getProduitComplet(parent.getId());
            if (dto != null) {
                produits.add(dto);
            }
        }

        return produits;
    }

    /**
     * Récupérer tous les produits actifs d'une sous-catégorie
     */
    public List<ProduitCompletDTO> getProduitsCompletsBySousCategorie(int sousCategorieId) throws SQLException {
        List<ProduitCompletDTO> produits = new ArrayList<>();
        List<ProduitParent> parents = produitParentService.getBySousCategorie(sousCategorieId);

        for (ProduitParent parent : parents) {
            ProduitCompletDTO dto = getProduitComplet(parent.getId());
            if (dto != null && dto.isEnStock()) {
                produits.add(dto);
            }
        }

        return produits;
    }

    /**
     * Récupérer toutes les catégories avec leurs sous-catégories
     */
    public List<CategorieAvecSousCategoriesDTO> getCategoriesAvecSousCategories() throws SQLException {
        List<CategorieAvecSousCategoriesDTO> result = new ArrayList<>();
        List<Categorie> categories = categorieService.afficher();

        for (Categorie categorie : categories) {
            CategorieAvecSousCategoriesDTO dto = new CategorieAvecSousCategoriesDTO(categorie);
            List<SousCategorie> sousCategories = sousCategorieService.getByCategorie(categorie.getId());
            dto.setSousCategories(sousCategories);
            result.add(dto);
        }

        return result;
    }

    /**
     * Récupérer tous les attributs avec leurs options
     */
    public List<AttributAvecOptionsDTO> getAttributsAvecOptions() throws SQLException {
        List<AttributAvecOptionsDTO> result = new ArrayList<>();
        List<AttributVariation> attributs = attributVariationService.afficher();

        for (AttributVariation attribut : attributs) {
            AttributAvecOptionsDTO dto = new AttributAvecOptionsDTO(attribut);
            List<OptionVariation> options = optionVariationService.getByAttribut(attribut.getId());
            dto.setOptions(options);
            result.add(dto);
        }

        return result;
    }

    /**
     * Créer un produit complet avec variants
     */
    public int creerProduitAvecVariants(ProduitParent parent, List<ProduitVariant> variants, 
                                        List<List<Integer>> variantOptions) throws SQLException {
        // Ajouter le produit parent
        int parentId = produitParentService.ajouter(parent);
        
        if (parentId > 0 && variants != null) {
            for (int i = 0; i < variants.size(); i++) {
                ProduitVariant variant = variants.get(i);
                variant.setProduitParentId(parentId);
                
                // Ajouter le variant
                int variantId = produitVariantService.ajouter(variant);
                
                // Ajouter les options du variant
                if (variantId > 0 && variantOptions != null && i < variantOptions.size()) {
                    List<Integer> options = variantOptions.get(i);
                    for (Integer optionId : options) {
                        VariantOption vo = new VariantOption(variantId, optionId);
                        variantOptionService.ajouter(vo);
                    }
                }
            }
        }

        return parentId;
    }

    /**
     * Rechercher des produits
     */
    public List<ProduitCompletDTO> rechercherProduits(String mot, int partenaireId) throws SQLException {
        List<ProduitCompletDTO> produits = new ArrayList<>();
        List<ProduitParent> parents = produitParentService.rechercher(mot, partenaireId);

        for (ProduitParent parent : parents) {
            ProduitCompletDTO dto = getProduitComplet(parent.getId());
            if (dto != null) {
                produits.add(dto);
            }
        }

        return produits;
    }
    
    /**
     * Récupérer tous les produits actifs (pour le catalogue client et chatbot)
     */
    public List<ProduitCompletDTO> getTousProduitsActifs() throws SQLException {
        List<ProduitCompletDTO> produits = new ArrayList<>();
        List<ProduitParent> parents = produitParentService.afficher();

        for (ProduitParent parent : parents) {
            if ("ACTIF".equals(parent.getStatut())) {
                ProduitCompletDTO dto = getProduitComplet(parent.getId());
                if (dto != null && dto.isEnStock()) {
                    produits.add(dto);
                }
            }
        }

        return produits;
    }

    /**
     * Récupérer TOUS les produits de TOUS les partenaires (mode admin)
     */
    public List<ProduitCompletDTO> getTousProduitsForAdmin() throws SQLException {
        List<ProduitCompletDTO> produits = new ArrayList<>();
        List<ProduitParent> parents = produitParentService.afficher();

        for (ProduitParent parent : parents) {
            ProduitCompletDTO dto = getProduitComplet(parent.getId());
            if (dto != null) {
                produits.add(dto);
            }
        }

        return produits;
    }
}
