package com.pi.utils;

import com.pi.dto.ProduitCompletDTO;
import com.pi.dto.VariantCompletDTO;
import com.pi.entity.CatalogueService;
import java.sql.SQLException;
import java.util.ArrayList;
import java.util.List;
import java.util.stream.Collectors;

/**
 * Service de chatbot pour aider les clients à trouver des produits selon leur budget
 */
public class ChatbotAPI {
    
    private CatalogueService catalogueService;
    
    public ChatbotAPI() {
        this.catalogueService = new CatalogueService();
    }
    
    /**
     * Traite un message du client et retourne une réponse
     */
    public String processMessage(String userMessage) {
        String message = userMessage.toLowerCase().trim();
        
        try {
            // Détecter si c'est une question sur le budget
            if (message.contains("budget") || message.contains("prix") || message.contains("tnd") || 
                message.contains("combien") || message.contains("coute") || message.contains("coûte")) {
                
                double budget = extractBudget(message);
                if (budget > 0) {
                    return searchProductsByBudget(budget);
                }
            }
            
            // Détecter si c'est une recherche par catégorie
            if (message.contains("camping") || message.contains("tente")) {
                return searchByCategory("camping");
            }
            if (message.contains("jeu") || message.contains("jeux")) {
                return searchByCategory("jeux");
            }
            if (message.contains("sport")) {
                return searchByCategory("sport");
            }
            
            // Détecter si c'est une demande de promo
            if (message.contains("promo") || message.contains("promotion") || message.contains("réduction") || 
                message.contains("reduction") || message.contains("solde")) {
                return searchPromotions();
            }
            
            // Message de bienvenue par défaut
            return "Bonjour! 👋 Je suis votre assistant shopping.\n\n" +
                   "Je peux vous aider à:\n" +
                   "• Trouver des produits selon votre budget (ex: 'produits à moins de 50 TND')\n" +
                   "• Chercher par catégorie (ex: 'montrez-moi des tentes de camping')\n" +
                   "• Voir les promotions en cours (ex: 'quelles sont les promos?')\n\n" +
                   "Comment puis-je vous aider aujourd'hui?";
            
        } catch (Exception e) {
            return "Désolé, j'ai rencontré une erreur. Pouvez-vous reformuler votre question?";
        }
    }
    
    /**
     * Extrait le montant du budget depuis le message
     */
    private double extractBudget(String message) {
        // Chercher un nombre dans le message
        String[] words = message.split("\\s+");
        for (String word : words) {
            try {
                // Nettoyer le mot (enlever les caractères non numériques sauf le point)
                String cleaned = word.replaceAll("[^0-9.]", "");
                if (!cleaned.isEmpty()) {
                    double value = Double.parseDouble(cleaned);
                    if (value > 0 && value < 1000000) { // Validation raisonnable
                        return value;
                    }
                }
            } catch (NumberFormatException e) {
                // Continuer avec le mot suivant
            }
        }
        return 0;
    }
    
    /**
     * Recherche des produits dans un budget donné
     */
    private String searchProductsByBudget(double budget) throws SQLException {
        List<ProduitCompletDTO> allProducts = catalogueService.getTousProduitsActifs();
        
        List<ProduitCompletDTO> inBudget = allProducts.stream()
            .filter(p -> {
                double minPrice = p.getVariants().stream()
                    .mapToDouble(v -> {
                        double promo = v.getProduitVariant().getPrixPromo();
                        double normal = v.getProduitVariant().getPrixVente();
                        return promo > 0 ? promo : normal;
                    })
                    .min().orElse(Double.MAX_VALUE);
                return minPrice <= budget;
            })
            .limit(5)
            .collect(Collectors.toList());
        
        if (inBudget.isEmpty()) {
            return String.format("Désolé, je n'ai pas trouvé de produits à moins de %.3f TND. 😔\n\n" +
                               "Voulez-vous augmenter votre budget?", budget);
        }
        
        StringBuilder response = new StringBuilder();
        response.append(String.format("Super! J'ai trouvé %d produit(s) dans votre budget de %.3f TND: 🎉\n\n",
                                     inBudget.size(), budget));
        
        for (ProduitCompletDTO produit : inBudget) {
            double minPrice = produit.getVariants().stream()
                .mapToDouble(v -> {
                    double promo = v.getProduitVariant().getPrixPromo();
                    double normal = v.getProduitVariant().getPrixVente();
                    return promo > 0 ? promo : normal;
                })
                .min().orElse(0);
            
            boolean hasPromo = produit.getVariants().stream()
                .anyMatch(v -> v.getProduitVariant().getPrixPromo() > 0);
            
            response.append(String.format("📦 %s\n", produit.getProduitParent().getNom()));
            response.append(String.format("   💰 À partir de %.3f TND", minPrice));
            if (hasPromo) {
                response.append(" 🎁 EN PROMO!");
            }
            response.append("\n\n");
        }
        
        response.append("Voulez-vous plus d'informations sur un produit?");
        return response.toString();
    }
    
    /**
     * Recherche par catégorie
     */
    private String searchByCategory(String category) throws SQLException {
        List<ProduitCompletDTO> allProducts = catalogueService.getTousProduitsActifs();
        
        List<ProduitCompletDTO> filtered = allProducts.stream()
            .filter(p -> {
                String nom = p.getProduitParent().getNom().toLowerCase();
                String desc = p.getProduitParent().getDescription() != null ? 
                             p.getProduitParent().getDescription().toLowerCase() : "";
                return nom.contains(category) || desc.contains(category);
            })
            .limit(5)
            .collect(Collectors.toList());
        
        if (filtered.isEmpty()) {
            return String.format("Je n'ai pas trouvé de produits dans la catégorie '%s'. 😔\n\n" +
                               "Essayez une autre catégorie ou demandez-moi de voir tous les produits!", category);
        }
        
        StringBuilder response = new StringBuilder();
        response.append(String.format("Voici %d produit(s) dans la catégorie '%s': 🛍️\n\n",
                                     filtered.size(), category));
        
        for (ProduitCompletDTO produit : filtered) {
            double minPrice = produit.getVariants().stream()
                .mapToDouble(v -> {
                    double promo = v.getProduitVariant().getPrixPromo();
                    double normal = v.getProduitVariant().getPrixVente();
                    return promo > 0 ? promo : normal;
                })
                .min().orElse(0);
            
            response.append(String.format("📦 %s - %.3f TND\n", 
                                        produit.getProduitParent().getNom(), minPrice));
        }
        
        return response.toString();
    }
    
    /**
     * Recherche les produits en promotion
     */
    private String searchPromotions() throws SQLException {
        List<ProduitCompletDTO> allProducts = catalogueService.getTousProduitsActifs();
        
        List<ProduitCompletDTO> promos = allProducts.stream()
            .filter(p -> p.getVariants().stream()
                .anyMatch(v -> v.getProduitVariant().getPrixPromo() > 0))
            .limit(5)
            .collect(Collectors.toList());
        
        if (promos.isEmpty()) {
            return "Désolé, il n'y a pas de promotions en cours pour le moment. 😔\n\n" +
                   "Revenez bientôt pour découvrir nos offres!";
        }
        
        StringBuilder response = new StringBuilder();
        response.append(String.format("🎁 Super! J'ai trouvé %d produit(s) en promotion:\n\n", promos.size()));
        
        for (ProduitCompletDTO produit : promos) {
            double normalPrice = produit.getVariants().stream()
                .mapToDouble(v -> v.getProduitVariant().getPrixVente())
                .min().orElse(0);
            
            double promoPrice = produit.getVariants().stream()
                .filter(v -> v.getProduitVariant().getPrixPromo() > 0)
                .mapToDouble(v -> v.getProduitVariant().getPrixPromo())
                .min().orElse(0);
            
            double reduction = ((normalPrice - promoPrice) / normalPrice) * 100;
            
            response.append(String.format("📦 %s\n", produit.getProduitParent().getNom()));
            response.append(String.format("   💰 %.3f TND → %.3f TND (-%d%%)\n\n",
                                        normalPrice, promoPrice, (int)reduction));
        }
        
        response.append("Profitez-en vite! 🏃‍♂️");
        return response.toString();
    }
}
