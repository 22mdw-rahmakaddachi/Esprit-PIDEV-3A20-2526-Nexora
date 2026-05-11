# NEXORA E-commerce System - Complete Technical Documentation

## Table of Contents
1. [System Overview](#system-overview)
2. [Architecture & Design](#architecture--design)
3. [Core Entities & Data Model](#core-entities--data-model)
4. [Advanced Functionalities](#advanced-functionalities)
5. [API Integrations](#api-integrations)
6. [Service Layer](#service-layer)
7. [User Interface Controllers](#user-interface-controllers)
8. [Business Logic & Workflows](#business-logic--workflows)
9. [Technical Choices & Rationale](#technical-choices--rationale)
10. [Database Schema](#database-schema)
11. [Security Implementation](#security-implementation)
12. [Performance & Scalability](#performance--scalability)

---

## System Overview

NEXORA is a sophisticated JavaFX-based e-commerce platform specifically designed for the Tunisian market. The system implements advanced product variant management, intelligent promotional systems, integrated payment processing, and AI-powered shopping assistance.

### Key Features
- **Product Variant System**: Complex product hierarchy with ProduitParent/ProduitVariant architecture
- **Advanced Shopping Cart**: Supports both legacy products and new variant-based products
- **Multi-Type Promotional Codes**: Percentage, fixed amount, free shipping, category-specific, first-order restrictions
- **Konnect Payment Integration**: Native Tunisian payment gateway supporting Flouci, E-Dinar, bank cards
- **Intelligent Chatbot**: Budget-based product recommendations with natural language processing
- **Unified Payment System**: Supports both e-commerce and activity payments
- **Email Service**: HTML templates with attachment support
- **Multi-User Roles**: Client, Partner, Administrator with role-based access control

---

## Architecture & Design

### Overall Architecture Pattern
The system follows a **layered MVC (Model-View-Controller)** architecture with additional service and utility layers:

```
┌─────────────────────────────────────────┐
│           Presentation Layer            │
│        (JavaFX Controllers)             │
├─────────────────────────────────────────┤
│            Service Layer                │
│     (Business Logic Services)          │
├─────────────────────────────────────────┤
│             Entity Layer                │
│        (Data Access Objects)           │
├─────────────────────────────────────────┤
│            Utility Layer                │
│    (APIs, Email, Database Utils)       │
├─────────────────────────────────────────┤
│            Data Layer                   │
│          (MySQL Database)               │
└─────────────────────────────────────────┘
```

### Package Structure
```
com.pi/
├── entities/          # Data models and POJOs
├── entity/           # Service classes (DAO pattern)
├── dto/              # Data Transfer Objects
├── utils/            # Utility classes (APIs, Email, DB)
├── validation/       # Input validation classes
controller/           # JavaFX UI controllers
resources/            # FXML files, CSS, properties
```

---

## Core Entities & Data Model

### Product Variant System

#### ProduitParent Entity
```java
public class ProduitParent {
    private int id;
    private int partenaireId;           // Owner partner
    private int sousCategorieId;        // Category classification
    private String nom;                 // Product name
    private String description;         // Detailed description
    private String descriptionCourte;   // Short description
    private String marque;              // Brand
    private String materiau;            // Material
    private double poidsKg;             // Weight in kg
    private String dimensionsCm;        // Dimensions
    private String imagePrincipale;     // Main product image
    private Timestamp dateAjout;        // Creation date
    private String statut;              // BROUILLON, ACTIF, INACTIF
}
```

**Business Logic**: ProduitParent serves as a template containing common attributes shared by all variants. It represents the "generic" product before specific variations are applied.

#### ProduitVariant Entity
```java
public class ProduitVariant {
    private int id;
    private int produitParentId;        // Reference to parent product
    private String sku;                 // Unique Stock Keeping Unit
    private double prixAchat;           // Purchase price
    private double prixVente;           // Selling price
    private double prixPromo;           // Promotional price (0 = no promo)
    private int quantiteStock;          // Current stock quantity
    private int seuilAlerte;            // Low stock alert threshold
    private String imageSpecifique;     // Variant-specific image
    private String codeBarres;          // Barcode
    private Timestamp dateCreation;     // Creation timestamp
}
```

**Business Logic**: Each variant represents a specific, purchasable product with unique pricing, stock, and attributes. The SKU system enables precise inventory tracking.

#### Attribute System
```java
public class AttributVariation {
    private int id;
    private String nom;                 // Attribute name (e.g., "Couleur", "Taille")
    private String type;                // Attribute type
}

public class OptionVariation {
    private int id;
    private int attributId;             // Reference to attribute
    private String valeur;              // Option value (e.g., "Rouge", "Large")
    private String codeHex;             // Color code for color attributes
}
```

**Business Logic**: This flexible system allows any number of attributes (size, color, material, etc.) with unlimited options per attribute.

### Promotional System

#### CodePromo Entity
```java
public class CodePromo {
    private int id;
    private String code;                        // Unique promo code
    private String description;                 // Human-readable description
    private TypeReduction typeReduction;       // POURCENTAGE, MONTANT_FIXE, LIVRAISON_GRATUITE
    private double valeurReduction;             // Reduction value
    private double montantMinimum;              // Minimum order amount
    private Date dateDebut;                     // Start date (null = immediate)
    private Date dateFin;                       // End date (null = unlimited)
    private Integer limiteUtilisation;          // Usage limit (null = unlimited)
    private int nombreUtilisations;            // Current usage count
    private boolean actif;                      // Active status
    private Integer partenaireId;               // Partner-specific (null = global)
    private Integer categorieId;                // Category-specific (null = all)
    private boolean premiereCommandeSeulement;  // First-order only restriction
    
    // Business logic methods
    public boolean estValide() { /* validation logic */ }
    public double calculerReduction(double montantCommande) { /* calculation logic */ }
}
```

**Advanced Features**:
- **Multi-type reductions**: Percentage, fixed amount, free shipping
- **Conditional logic**: Minimum amounts, date ranges, usage limits
- **Targeting**: Partner-specific, category-specific, first-order only
- **Real-time validation**: Checks all conditions before application

### Shopping Cart System

#### PanierItem Entity
```java
public class PanierItem {
    private int id;
    private int clientId;               // Cart owner
    private int produitId;              // Legacy product ID (backward compatibility)
    private String variantSku;          // New variant-based system
    private String produitNom;          // Product name (cached for performance)
    private double prixUnitaire;        // Unit price (cached)
    private int quantite;               // Quantity
    private Timestamp dateAjout;        // Added to cart timestamp
    
    // Calculated fields
    public double getTotal() { return prixUnitaire * quantite; }
}
```

**Hybrid Design**: Supports both legacy products (produitId) and new variant system (variantSku) for backward compatibility during migration.

---

## Advanced Functionalities

### 1. Product Variant Management

#### Variant Creation Workflow
```java
// CatalogueService.creerProduitAvecVariants()
public int creerProduitAvecVariants(ProduitParent parent, 
                                   List<ProduitVariant> variants, 
                                   List<List<Integer>> variantOptions) {
    // 1. Create parent product
    int parentId = produitParentService.ajouter(parent);
    
    // 2. Create each variant
    for (int i = 0; i < variants.size(); i++) {
        ProduitVariant variant = variants.get(i);
        variant.setProduitParentId(parentId);
        int variantId = produitVariantService.ajouter(variant);
        
        // 3. Associate variant with attribute options
        List<Integer> options = variantOptions.get(i);
        for (Integer optionId : options) {
            VariantOption vo = new VariantOption(variantId, optionId);
            variantOptionService.ajouter(vo);
        }
    }
    return parentId;
}
```

#### Complex Product Retrieval
```java
// CatalogueService.getProduitComplet()
public ProduitCompletDTO getProduitComplet(int produitParentId) {
    // 1. Get parent product
    ProduitParent parent = produitParentService.getById(produitParentId);
    ProduitCompletDTO dto = new ProduitCompletDTO(parent);
    
    // 2. Get category information
    SousCategorie sousCategorie = sousCategorieService.getById(parent.getSousCategorieId());
    dto.setSousCategorieNom(sousCategorie.getNom());
    
    // 3. Get all variants with their options
    List<ProduitVariant> variants = produitVariantService.getByProduitParent(produitParentId);
    for (ProduitVariant variant : variants) {
        VariantCompletDTO variantDTO = new VariantCompletDTO(variant);
        
        // 4. Get variant options (size, color, etc.)
        List<Integer> optionIds = variantOptionService.getOptionsByVariant(variant.getId());
        for (Integer optionId : optionIds) {
            OptionVariation option = optionVariationService.getById(optionId);
            variantDTO.addOption(option);
        }
        dto.addVariant(variantDTO);
    }
    return dto;
}
```

### 2. Advanced Shopping Cart Logic

#### Hybrid Cart System
```java
// PanierService.getPanierAvecVariants()
public List<PanierItem> getPanierAvecVariants(int clientId) {
    String sql = "SELECT p.id, p.produit_id, p.variant_sku, p.produit_nom, p.prix_unitaire, p.quantite, " +
                "pr.nom as ancien_nom, pr.prix as ancien_prix " +
                "FROM panier p " +
                "LEFT JOIN produits pr ON p.produit_id = pr.id " +
                "WHERE p.client_id = ?";
    
    // Handle both legacy products and new variants
    while (rs.next()) {
        PanierItem item = new PanierItem();
        String variantSku = rs.getString("variant_sku");
        
        if (variantSku != null && !variantSku.isEmpty()) {
            // New variant system
            item.setVariantSku(variantSku);
            item.setProduitNom(rs.getString("produit_nom"));
            item.setPrixUnitaire(rs.getDouble("prix_unitaire"));
        } else {
            // Legacy product system
            item.setProduitId(rs.getInt("produit_id"));
            item.setProduitNom(rs.getString("ancien_nom"));
            item.setPrixUnitaire(rs.getDouble("ancien_prix"));
        }
    }
}
```

#### Smart Stock Management
```java
// PanierService.passerCommande()
public commande passerCommande(int clientId, String clientNom) {
    // 1. Validate cart and calculate total
    List<PanierItem> panier = getPanierAvecVariants(clientId);
    double total = panier.stream().mapToDouble(PanierItem::getTotal).sum();
    
    // 2. Create order
    commande commande = new commande();
    commande.setUserId(clientId);
    commande.setTotal(total);
    commande.setStatut("EN_ATTENTE");
    commandeService.ajouter(commande);
    
    // 3. Process each item and update stock
    for (PanierItem item : panier) {
        // Create order item
        CommandeItem cmdItem = new CommandeItem();
        cmdItem.setCommandeId(commande.getId());
        cmdItem.setProduitNom(item.getProduitNom());
        cmdItem.setQuantite(item.getQuantite());
        itemService.ajouter(cmdItem);
        
        // Update stock based on item type
        if (item.getVariantSku() != null) {
            // New variant system - update variant stock
            ProduitVariant variant = variantService.getBySku(item.getVariantSku());
            int nouveauStock = variant.getQuantiteStock() - item.getQuantite();
            variantService.updateStock(variant.getId(), nouveauStock);
        } else {
            // Legacy system - update product stock
            Product produit = productService.getById(item.getProduitId());
            int nouveauStock = produit.getQuantite() - item.getQuantite();
            productService.updateQuantite(item.getProduitId(), nouveauStock);
        }
    }
    
    // 4. Clear cart
    viderPanier(clientId);
    return commande;
}
```

### 3. Sophisticated Promotional Code System

#### Multi-Condition Validation
```java
// CodePromoService.validerCode()
public CodePromo validerCode(String code, int clientId, double montantCommande) {
    CodePromo codePromo = getByCode(code);
    
    // 1. Basic validation
    if (codePromo == null) throw new SQLException("Code promo invalide");
    if (!codePromo.estValide()) throw new SQLException("Code promo expiré");
    
    // 2. Minimum amount validation
    if (montantCommande < codePromo.getMontantMinimum()) {
        throw new SQLException("Montant minimum requis: " + codePromo.getMontantMinimum() + " TND");
    }
    
    // 3. First-order restriction
    if (codePromo.isPremiereCommandeSeulement() && aDejaCommande(clientId)) {
        throw new SQLException("Ce code est réservé aux nouveaux clients");
    }
    
    // 4. Usage limit per client
    if (aDejaUtiliseCode(clientId, codePromo.getId())) {
        throw new SQLException("Vous avez déjà utilisé ce code promo");
    }
    
    return codePromo;
}
```

#### Dynamic Reduction Calculation
```java
// CodePromo.calculerReduction()
public double calculerReduction(double montantCommande) {
    if (!estValide() || montantCommande < montantMinimum) return 0.0;
    
    switch (typeReduction) {
        case POURCENTAGE:
            return montantCommande * (valeurReduction / 100.0);
        case MONTANT_FIXE:
            return Math.min(valeurReduction, montantCommande); // Never exceed order total
        case LIVRAISON_GRATUITE:
            return 0.0; // Handled separately in shipping calculation
        default:
            return 0.0;
    }
}
```

---

## API Integrations

### 1. Konnect Payment API Integration

#### Payment Initialization
```java
// KonnectPaymentAPI.initierPaiement()
public static String initierPaiement(double montant, int commandeId, String clientNom) {
    if (MODE_TEST) return simulerPaiement(montant, commandeId);
    
    // Convert TND to millimes (Tunisian currency subdivision)
    int montantMillimes = (int) (montant * 1000);
    
    // Build JSON payload
    JSONObject payload = new JSONObject();
    payload.put("receiverWalletId", WALLET_ID);
    payload.put("amount", montantMillimes);
    payload.put("token", API_KEY);
    payload.put("type", "immediate");
    payload.put("description", "Commande #" + commandeId);
    payload.put("acceptedPaymentMethods", new String[]{"wallet", "bank_card", "e-DINAR"});
    payload.put("lifespan", 10); // 10 minutes timeout
    payload.put("checkoutForm", true);
    payload.put("addPaymentFeesToAmount", true);
    
    // Webhook configuration
    JSONObject webhook = new JSONObject();
    webhook.put("successUrl", "http://votre-site.com/payment/success?commande=" + commandeId);
    webhook.put("failUrl", "http://votre-site.com/payment/fail?commande=" + commandeId);
    payload.put("webhook", webhook);
    
    // Send HTTP POST request
    HttpURLConnection conn = (HttpURLConnection) new URL(API_URL).openConnection();
    conn.setRequestMethod("POST");
    conn.setRequestProperty("Content-Type", "application/json");
    conn.setDoOutput(true);
    
    // Write payload
    try (OutputStream os = conn.getOutputStream()) {
        byte[] input = payload.toString().getBytes(StandardCharsets.UTF_8);
        os.write(input, 0, input.length);
    }
    
    // Parse response
    if (conn.getResponseCode() == HttpURLConnection.HTTP_OK) {
        JSONObject jsonResponse = new JSONObject(readResponse(conn));
        return jsonResponse.getString("payUrl"); // Return payment URL
    }
    
    return null;
}
```

#### Payment Status Verification
```java
// KonnectPaymentAPI.verifierStatutPaiement()
public static String verifierStatutPaiement(String paymentRef) {
    if (MODE_TEST) return "VALIDE";
    
    String checkUrl = "https://api.konnect.network/api/v2/payments/" + paymentRef;
    HttpURLConnection conn = (HttpURLConnection) new URL(checkUrl).openConnection();
    conn.setRequestMethod("GET");
    conn.setRequestProperty("x-api-key", API_KEY);
    
    if (conn.getResponseCode() == HttpURLConnection.HTTP_OK) {
        JSONObject jsonResponse = new JSONObject(readResponse(conn));
        String status = jsonResponse.getJSONObject("payment").getString("status");
        
        // Map Konnect statuses to internal statuses
        switch (status) {
            case "completed": return "VALIDE";
            case "failed": return "REFUSE";
            case "pending": return "EN_ATTENTE";
            default: return "EN_ATTENTE";
        }
    }
    return "EN_ATTENTE";
}
```

### 2. Intelligent Chatbot API

#### Natural Language Processing
```java
// ChatbotAPI.processMessage()
public String processMessage(String userMessage) {
    String message = userMessage.toLowerCase().trim();
    
    // Budget detection
    if (message.contains("budget") || message.contains("prix") || message.contains("tnd")) {
        double budget = extractBudget(message);
        if (budget > 0) return searchProductsByBudget(budget);
    }
    
    // Category detection
    if (message.contains("camping")) return searchByCategory("camping");
    if (message.contains("sport")) return searchByCategory("sport");
    
    // Promotion detection
    if (message.contains("promo") || message.contains("réduction")) {
        return searchPromotions();
    }
    
    // Default welcome message
    return generateWelcomeMessage();
}
```

#### Budget-Based Product Search
```java
// ChatbotAPI.searchProductsByBudget()
private String searchProductsByBudget(double budget) throws SQLException {
    List<ProduitCompletDTO> allProducts = catalogueService.getTousProduitsActifs();
    
    // Filter products within budget
    List<ProduitCompletDTO> inBudget = allProducts.stream()
        .filter(p -> {
            double minPrice = p.getVariants().stream()
                .mapToDouble(v -> {
                    double promo = v.getProduitVariant().getPrixPromo();
                    double normal = v.getProduitVariant().getPrixVente();
                    return promo > 0 ? promo : normal; // Use promo price if available
                })
                .min().orElse(Double.MAX_VALUE);
            return minPrice <= budget;
        })
        .limit(5)
        .collect(Collectors.toList());
    
    // Generate response
    if (inBudget.isEmpty()) {
        return String.format("Désolé, je n'ai pas trouvé de produits à moins de %.3f TND. 😔", budget);
    }
    
    StringBuilder response = new StringBuilder();
    response.append(String.format("Super! J'ai trouvé %d produit(s) dans votre budget: 🎉\n\n", inBudget.size()));
    
    for (ProduitCompletDTO produit : inBudget) {
        double minPrice = getMinPrice(produit);
        boolean hasPromo = hasPromotion(produit);
        
        response.append(String.format("📦 %s\n", produit.getProduitParent().getNom()));
        response.append(String.format("   💰 À partir de %.3f TND", minPrice));
        if (hasPromo) response.append(" 🎁 EN PROMO!");
        response.append("\n\n");
    }
    
    return response.toString();
}
```

---

## Service Layer

### 1. CatalogueService - Product Management Hub

```java
public class CatalogueService {
    // Comprehensive product retrieval with all relationships
    public ProduitCompletDTO getProduitComplet(int produitParentId) throws SQLException {
        // 1. Get parent product
        ProduitParent parent = produitParentService.getById(produitParentId);
        ProduitCompletDTO dto = new ProduitCompletDTO(parent);
        
        // 2. Enrich with category information
        SousCategorie sousCategorie = sousCategorieService.getById(parent.getSousCategorieId());
        if (sousCategorie != null) {
            dto.setSousCategorieNom(sousCategorie.getNom());
            Categorie categorie = categorieService.getById(sousCategorie.getCategorieId());
            if (categorie != null) {
                dto.setCategorieNom(categorie.getNom());
            }
        }
        
        // 3. Get all variants with their attribute options
        List<ProduitVariant> variants = produitVariantService.getByProduitParent(produitParentId);
        for (ProduitVariant variant : variants) {
            VariantCompletDTO variantDTO = new VariantCompletDTO(variant);
            
            // Get variant's attribute options (color, size, etc.)
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
    
    // Advanced search with multiple filters
    public List<ProduitCompletDTO> rechercherProduits(String mot, int partenaireId, 
                                                      String categorie, double prixMin, double prixMax) {
        // Implementation with complex SQL queries and filtering
    }
}
```

### 2. PaiementService - Unified Payment Management

```java
public class PaiementService implements icrud<Paiement> {
    // Unified payment creation supporting both e-commerce and activities
    @Override
    public void ajouter(Paiement paiement) throws SQLException {
        String query = "INSERT INTO paiement (commande_id, demande_id, client_id, activite_id, " +
                      "montant, methode_paiement, statut, date_paiement, reference_transaction, " +
                      "reference_externe, details_json) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        try (PreparedStatement pst = connection.prepareStatement(query)) {
            // E-commerce payments use commande_id
            if (paiement.getCommandeId() > 0) {
                pst.setInt(1, paiement.getCommandeId());
                pst.setNull(2, Types.INTEGER); // demande_id
                pst.setNull(3, Types.INTEGER); // client_id
                pst.setNull(4, Types.INTEGER); // activite_id
            } else {
                // Activity payments use demande_id, client_id, activite_id
                pst.setNull(1, Types.INTEGER); // commande_id
                pst.setInt(2, paiement.getDemandeId());
                pst.setInt(3, paiement.getClientId());
                pst.setInt(4, paiement.getActiviteId());
            }
            
            pst.setDouble(5, paiement.getMontant());
            pst.setString(6, paiement.getMethodePaiement());
            pst.setString(7, paiement.getStatut());
            pst.setTimestamp(8, Timestamp.valueOf(paiement.getDatePaiement()));
            pst.setString(9, paiement.getReferenceTransaction());
            pst.setString(10, paiement.getReferenceExterne());
            pst.setString(11, paiement.getDetailsJson());
            
            pst.executeUpdate();
        }
    }
}
```

### 3. EmailService - Advanced Email Management

```java
public class EmailService {
    // HTML email with professional styling
    public static String createAcceptanceEmailHtml(String clientNom, String activiteNom,
                                                   String lieu, String date, double prix) {
        return String.format(
            "<!DOCTYPE html>" +
            "<html>" +
            "<head><meta charset='UTF-8'></head>" +
            "<body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>" +
            "<div style='max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f9f9f9;'>" +
            "<div style='background-color: #4CAF50; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0;'>" +
            "<h1 style='margin: 0;'>✅ Demande Acceptée !</h1>" +
            "</div>" +
            "<div style='background-color: white; padding: 30px; border-radius: 0 0 8px 8px;'>" +
            "<p>Bonjour <strong>%s</strong>,</p>" +
            "<div style='background-color: #f0f8ff; padding: 20px; border-left: 4px solid #2196F3;'>" +
            "<h3>📋 Détails de l'activité</h3>" +
            "<p><strong>Activité:</strong> %s</p>" +
            "<p><strong>Lieu:</strong> %s</p>" +
            "<p><strong>Date:</strong> %s</p>" +
            "<p><strong>Prix:</strong> %.2f TND</p>" +
            "</div>" +
            "</div>" +
            "</div>" +
            "</body>" +
            "</html>",
            clientNom, activiteNom, lieu, date, prix
        );
    }
    
    // Email with attachment support
    public static void envoyerEmailAvecPieceJointe(String to, String subject, 
                                                   String body, String attachmentPath) {
        // SMTP configuration and multipart message creation
        MimeBodyPart messageBodyPart = new MimeBodyPart();
        messageBodyPart.setText(body, "UTF-8");
        
        MimeBodyPart attachmentBodyPart = new MimeBodyPart();
        attachmentBodyPart.attachFile(attachmentPath);
        
        Multipart multipart = new MimeMultipart();
        multipart.addBodyPart(messageBodyPart);
        multipart.addBodyPart(attachmentBodyPart);
        
        message.setContent(multipart);
        Transport.send(message);
    }
}
```

---

## User Interface Controllers

### 1. PaiementController - Payment Processing

```java
public class PaiementController {
    @FXML private ComboBox<String> methodePaiementCombo;
    @FXML private VBox carteDetailsBox;
    @FXML private ProgressIndicator progressIndicator;
    
    private commande commande;
    private PaiementService paiementService = new PaiementService();
    
    @FXML
    public void handlePayer() {
        String methode = methodePaiementCombo.getValue();
        
        progressIndicator.setVisible(true);
        btnPayer.setDisable(true);
        
        // Asynchronous payment processing
        new Thread(() -> {
            try {
                boolean success = false;
                String transactionId = null;
                
                switch (methode) {
                    case "Konnect (Flouci)":
                        success = traiterPaiementKonnect();
                        transactionId = "KONNECT_" + System.currentTimeMillis();
                        break;
                    case "Carte Bancaire":
                        success = traiterPaiementCarte();
                        transactionId = "CARTE_" + System.currentTimeMillis();
                        break;
                }
                
                final boolean finalSuccess = success;
                final String finalTransactionId = transactionId;
                
                // Update UI on JavaFX thread
                javafx.application.Platform.runLater(() -> {
                    progressIndicator.setVisible(false);
                    btnPayer.setDisable(false);
                    
                    if (finalSuccess) {
                        enregistrerPaiement(methode, finalTransactionId, "COMPLETE");
                        afficherSucces();
                    } else {
                        enregistrerPaiement(methode, finalTransactionId, "ECHOUE");
                        showAlert("Échec", "Le paiement a échoué.");
                    }
                });
                
            } catch (Exception e) {
                // Error handling on UI thread
                javafx.application.Platform.runLater(() -> {
                    progressIndicator.setVisible(false);
                    btnPayer.setDisable(false);
                    showAlert("Erreur", "Erreur lors du paiement: " + e.getMessage());
                });
            }
        }).start();
    }
    
    private boolean traiterPaiementKonnect() {
        // Initialize payment via Konnect API
        String paymentUrl = KonnectPaymentAPI.initierPaiement(
            commande.getTotal(), 
            commande.getId(), 
            commande.getClientNom()
        );
        
        if (paymentUrl != null) {
            if (paymentUrl.startsWith("SIMULATION_")) {
                // Test mode - simulate success
                return true;
            } else {
                // Production mode - open browser
                try {
                    java.awt.Desktop.getDesktop().browse(new java.net.URI(paymentUrl));
                    Thread.sleep(2000); // Wait for user interaction
                    return true;
                } catch (Exception e) {
                    return false;
                }
            }
        }
        return false;
    }
}
```

### 2. PanierController - Advanced Cart Management

```java
public class PanierController {
    @FXML private VBox panierContainer;
    @FXML private TextField codePromoField;
    @FXML private Label reductionLabel;
    
    private CodePromo codePromoApplique = null;
    private double montantReduction = 0.0;
    
    private void loadPanier() {
        try {
            panierContainer.getChildren().clear();
            
            // Load cart items (supports both legacy and variant systems)
            panierItems = panierService.getPanierAvecVariants(clientId);
            
            if (panierItems.isEmpty()) {
                emptyMessage.setVisible(true);
            } else {
                emptyMessage.setVisible(false);
                for (PanierItem item : panierItems) {
                    panierContainer.getChildren().add(createPanierCard(item));
                }
            }
            
            updateSummary();
        } catch (SQLException e) {
            showAlert("Erreur", "Impossible de charger le panier: " + e.getMessage());
        }
    }
    
    private HBox createPanierCard(PanierItem item) {
        HBox card = new HBox(15);
        card.setStyle("-fx-background-color: white; -fx-background-radius: 8; " +
                     "-fx-effect: dropshadow(gaussian, rgba(0,0,0,0.1), 10, 0, 0, 2); " +
                     "-fx-padding: 15;");
        
        // Product icon
        VBox iconBox = new VBox();
        iconBox.setStyle("-fx-background-color: #F3F4F6; -fx-background-radius: 8; " +
                        "-fx-min-width: 80; -fx-min-height: 80;");
        Label icon = new Label("📦");
        icon.setStyle("-fx-font-size: 32px;");
        iconBox.getChildren().add(icon);
        
        // Product information
        VBox infoBox = new VBox(5);
        Label nomLabel = new Label(item.getProduitNom());
        nomLabel.setStyle("-fx-font-size: 16px; -fx-font-weight: bold;");
        Label prixLabel = new Label(String.format("%.3f TND", item.getPrixUnitaire()));
        prixLabel.setStyle("-fx-font-size: 13px; -fx-text-fill: #6B7280;");
        infoBox.getChildren().addAll(nomLabel, prixLabel);
        
        // Quantity controls
        HBox quantityBox = new HBox(8);
        Button minusBtn = new Button("-");
        minusBtn.setOnAction(e -> handleUpdateQuantity(item, item.getQuantite() - 1));
        Label quantityLabel = new Label(String.valueOf(item.getQuantite()));
        Button plusBtn = new Button("+");
        plusBtn.setOnAction(e -> handleUpdateQuantity(item, item.getQuantite() + 1));
        quantityBox.getChildren().addAll(minusBtn, quantityLabel, plusBtn);
        
        // Total price and delete button
        VBox priceBox = new VBox(5);
        Label totalItemLabel = new Label(String.format("%.3f TND", item.getTotal()));
        totalItemLabel.setStyle("-fx-font-size: 20px; -fx-font-weight: bold; -fx-text-fill: #2980b9;");
        Button deleteBtn = new Button("🗑️ Supprimer");
        deleteBtn.setOnAction(e -> handleDelete(item));
        priceBox.getChildren().addAll(totalItemLabel, deleteBtn);
        
        card.getChildren().addAll(iconBox, infoBox, quantityBox, priceBox);
        return card;
    }
    
    @FXML
    public void handleAppliquerCode() {
        try {
            String code = codePromoField.getText().trim().toUpperCase();
            double total = calculerTotal();
            
            // Validate promo code
            codePromoApplique = codePromoService.validerCode(code, clientId, total);
            montantReduction = codePromoApplique.calculerReduction(total);
            
            // Update UI
            reductionLabel.setText(String.format("-%.3f TND", montantReduction));
            reductionLabel.setVisible(true);
            codePromoField.setDisable(true);
            
            updateSummary();
            showAlert("Succès", "✅ Code promo appliqué: " + codePromoApplique.getDescription());
            
        } catch (SQLException e) {
            showAlert("Erreur", e.getMessage());
            codePromoApplique = null;
            montantReduction = 0.0;
            reductionLabel.setVisible(false);
        }
    }
}
```

### 3. ChatbotController - Interactive AI Assistant

```java
public class ChatbotController {
    @FXML private VBox chatContainer;
    @FXML private ScrollPane scrollPane;
    @FXML private TextField messageField;
    
    private ChatbotAPI chatbotAPI;
    
    @FXML
    public void initialize() {
        chatbotAPI = new ChatbotAPI();
        
        // Auto-scroll to bottom
        chatContainer.heightProperty().addListener((obs, oldVal, newVal) -> {
            scrollPane.setVvalue(1.0);
        });
        
        // Send message on Enter key
        messageField.setOnAction(e -> handleSendMessage());
        
        // Welcome message
        addBotMessage(chatbotAPI.processMessage("bonjour"));
    }
    
    @FXML
    public void handleSendMessage() {
        String userMessage = messageField.getText().trim();
        if (userMessage.isEmpty()) return;
        
        // Display user message
        addUserMessage(userMessage);
        messageField.clear();
        
        // Process message and get bot response
        String botResponse = chatbotAPI.processMessage(userMessage);
        
        // Display bot response with delay for natural feel
        new Thread(() -> {
            try {
                Thread.sleep(500);
                javafx.application.Platform.runLater(() -> addBotMessage(botResponse));
            } catch (InterruptedException e) {
                e.printStackTrace();
            }
        }).start();
    }
    
    private void addUserMessage(String message) {
        HBox messageBox = new HBox(10);
        messageBox.setAlignment(Pos.CENTER_RIGHT);
        messageBox.setPadding(new Insets(5, 10, 5, 50));
        
        VBox bubble = createMessageBubble(message, "#2980b9", "#ffffff", true);
        messageBox.getChildren().add(bubble);
        chatContainer.getChildren().add(messageBox);
    }
    
    private void addBotMessage(String message) {
        HBox messageBox = new HBox(10);
        messageBox.setAlignment(Pos.CENTER_LEFT);
        messageBox.setPadding(new Insets(5, 50, 5, 10));
        
        Label icon = new Label("🤖");
        icon.setStyle("-fx-font-size: 24px;");
        
        VBox bubble = createMessageBubble(message, "#ecf0f1", "#2c3e50", false);
        messageBox.getChildren().addAll(icon, bubble);
        chatContainer.getChildren().add(messageBox);
    }
    
    private VBox createMessageBubble(String message, String bgColor, String textColor, boolean isUser) {
        VBox bubble = new VBox(5);
        bubble.setStyle(String.format(
            "-fx-background-color: %s; -fx-background-radius: 15; -fx-padding: 12; -fx-max-width: 400;",
            bgColor
        ));
        
        Label messageLabel = new Label(message);
        messageLabel.setWrapText(true);
        messageLabel.setStyle(String.format("-fx-text-fill: %s; -fx-font-size: 13px;", textColor));
        
        Label timeLabel = new Label(LocalTime.now().format(DateTimeFormatter.ofPattern("HH:mm")));
        timeLabel.setStyle(String.format("-fx-text-fill: %s; -fx-font-size: 10px; -fx-opacity: 0.7;", textColor));
        
        bubble.getChildren().addAll(messageLabel, timeLabel);
        return bubble;
    }
}
```

---

## Business Logic & Workflows

### 1. Order Processing Workflow

```
Client Cart → Validation → Promo Code Application → Order Creation → Payment → Stock Update → Email Notification
```

**Detailed Flow:**
1. **Cart Validation**: Check stock availability, calculate totals
2. **Promo Code Processing**: Validate conditions, calculate reductions
3. **Order Creation**: Generate order ID, create order items
4. **Payment Processing**: Konnect API integration, status tracking
5. **Stock Management**: Atomic stock updates, prevent overselling
6. **Notifications**: Email confirmations, PDF receipts

### 2. Product Variant Creation Workflow

```
Parent Product → Attribute Definition → Variant Creation → Option Assignment → Stock Initialization
```

**Implementation:**
```java
// Complete product creation workflow
public void creerProduitComplet() {
    // 1. Create parent product
    ProduitParent parent = new ProduitParent();
    parent.setNom("T-Shirt Premium");
    parent.setDescription("T-shirt en coton bio");
    int parentId = produitParentService.ajouter(parent);
    
    // 2. Create variants with different combinations
    List<ProduitVariant> variants = Arrays.asList(
        new ProduitVariant(parentId, "TSHIRT-ROUGE-S", 25.000),
        new ProduitVariant(parentId, "TSHIRT-ROUGE-M", 25.000),
        new ProduitVariant(parentId, "TSHIRT-BLEU-S", 25.000),
        new ProduitVariant(parentId, "TSHIRT-BLEU-M", 25.000)
    );
    
    // 3. Define attribute options
    List<List<Integer>> variantOptions = Arrays.asList(
        Arrays.asList(1, 3), // Rouge + Small
        Arrays.asList(1, 4), // Rouge + Medium
        Arrays.asList(2, 3), // Bleu + Small
        Arrays.asList(2, 4)  // Bleu + Medium
    );
    
    // 4. Create complete product structure
    catalogueService.creerProduitAvecVariants(parent, variants, variantOptions);
}
```

### 3. Payment Processing Workflow

```
Payment Initiation → API Call → User Interaction → Status Verification → Order Update → Notification
```

**Konnect Integration Flow:**
1. **Initiation**: Convert TND to millimes, create payment request
2. **API Call**: Send JSON payload to Konnect API
3. **User Interaction**: Redirect to Konnect payment page
4. **Verification**: Check payment status via API
5. **Order Update**: Update order status based on payment result
6. **Notification**: Send confirmation emails, generate receipts

---

## Technical Choices & Rationale

### 1. Architecture Decisions

#### JavaFX for Desktop Application
**Choice**: JavaFX over web-based solution
**Rationale**: 
- Rich desktop UI capabilities
- Better performance for complex interfaces
- Offline functionality
- Native OS integration
- Suitable for business applications

#### Layered Architecture
**Choice**: MVC with Service Layer
**Rationale**:
- Clear separation of concerns
- Testable business logic
- Maintainable codebase
- Scalable structure

### 2. Database Design

#### Hybrid Product System
**Choice**: Support both legacy products and new variant system
**Rationale**:
- Backward compatibility during migration
- Gradual system evolution
- Data preservation
- Minimal disruption to existing workflows

#### Unified Payment Table
**Choice**: Single table for e-commerce and activity payments
**Rationale**:
- Simplified payment management
- Consistent reporting
- Reduced code duplication
- Unified business logic

### 3. API Integration

#### Konnect Payment Gateway
**Choice**: Konnect over international gateways
**Rationale**:
- Tunisian market specialization
- Local payment methods (Flouci, E-Dinar)
- TND currency support
- Regulatory compliance
- Better user experience for local customers

#### JSON for API Communication
**Choice**: JSON over XML
**Rationale**:
- Lightweight data format
- Easy parsing and generation
- Wide API support
- Better performance

### 4. Email System

#### HTML Email Templates
**Choice**: HTML emails with fallback to plain text
**Rationale**:
- Professional appearance
- Better user engagement
- Brand consistency
- Rich content support

#### Jakarta Mail
**Choice**: Jakarta Mail over alternatives
**Rationale**:
- Standard Java email API
- Comprehensive feature set
- Good documentation
- Active maintenance

---

## Database Schema

### Core Tables

#### Product Variant System
```sql
-- Parent product template
CREATE TABLE produit_parent (
    id INT PRIMARY KEY AUTO_INCREMENT,
    partenaire_id INT NOT NULL,
    sous_categorie_id INT NOT NULL,
    nom VARCHAR(255) NOT NULL,
    description TEXT,
    description_courte VARCHAR(500),
    marque VARCHAR(100),
    materiau VARCHAR(100),
    poids_kg DECIMAL(8,3),
    dimensions_cm VARCHAR(100),
    image_principale VARCHAR(255),
    date_ajout TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    statut ENUM('BROUILLON', 'ACTIF', 'INACTIF') DEFAULT 'ACTIF',
    INDEX idx_partenaire (partenaire_id),
    INDEX idx_sous_categorie (sous_categorie_id),
    INDEX idx_statut (statut)
);

-- Specific product variants
CREATE TABLE produit_variant (
    id INT PRIMARY KEY AUTO_INCREMENT,
    produit_parent_id INT NOT NULL,
    sku VARCHAR(100) UNIQUE NOT NULL,
    prix_achat DECIMAL(10,3) DEFAULT 0,
    prix_vente DECIMAL(10,3) NOT NULL,
    prix_promo DECIMAL(10,3) DEFAULT 0,
    quantite_stock INT DEFAULT 0,
    seuil_alerte INT DEFAULT 5,
    image_specifique VARCHAR(255),
    code_barres VARCHAR(50),
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (produit_parent_id) REFERENCES produit_parent(id) ON DELETE CASCADE,
    INDEX idx_parent (produit_parent_id),
    INDEX idx_sku (sku),
    INDEX idx_stock (quantite_stock)
);

-- Attribute definitions (Color, Size, etc.)
CREATE TABLE attribut_variation (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(100) NOT NULL UNIQUE,
    type VARCHAR(50) DEFAULT 'TEXT'
);

-- Attribute options (Red, Blue, Small, Large, etc.)
CREATE TABLE option_variation (
    id INT PRIMARY KEY AUTO_INCREMENT,
    attribut_id INT NOT NULL,
    valeur VARCHAR(100) NOT NULL,
    code_hex VARCHAR(7), -- For color attributes
    FOREIGN KEY (attribut_id) REFERENCES attribut_variation(id) ON DELETE CASCADE,
    INDEX idx_attribut (attribut_id)
);

-- Variant-Option relationships
CREATE TABLE variant_option (
    id INT PRIMARY KEY AUTO_INCREMENT,
    variant_id INT NOT NULL,
    option_id INT NOT NULL,
    FOREIGN KEY (variant_id) REFERENCES produit_variant(id) ON DELETE CASCADE,
    FOREIGN KEY (option_id) REFERENCES option_variation(id) ON DELETE CASCADE,
    UNIQUE KEY unique_variant_option (variant_id, option_id)
);
```

#### Promotional System
```sql
CREATE TABLE code_promo (
    id INT PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(50) UNIQUE NOT NULL,
    description TEXT,
    type_reduction ENUM('POURCENTAGE', 'MONTANT_FIXE', 'LIVRAISON_GRATUITE') NOT NULL,
    valeur_reduction DECIMAL(10,3) NOT NULL,
    montant_minimum DECIMAL(10,3) DEFAULT 0,
    date_debut DATE,
    date_fin DATE,
    limite_utilisation INT,
    nombre_utilisations INT DEFAULT 0,
    actif BOOLEAN DEFAULT TRUE,
    partenaire_id INT, -- NULL = global code
    categorie_id INT,  -- NULL = all categories
    premiere_commande_seulement BOOLEAN DEFAULT FALSE,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_code (code),
    INDEX idx_dates (date_debut, date_fin),
    INDEX idx_partenaire (partenaire_id)
);

-- Track code usage
CREATE TABLE utilisation_code_promo (
    id INT PRIMARY KEY AUTO_INCREMENT,
    code_promo_id INT NOT NULL,
    client_id INT NOT NULL,
    commande_id INT NOT NULL,
    montant_reduction DECIMAL(10,3) NOT NULL,
    date_utilisation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (code_promo_id) REFERENCES code_promo(id),
    UNIQUE KEY unique_client_code (client_id, code_promo_id)
);
```

#### Unified Payment System
```sql
CREATE TABLE paiement (
    id INT PRIMARY KEY AUTO_INCREMENT,
    -- E-commerce payments
    commande_id INT,
    -- Activity payments
    demande_id INT,
    client_id INT,
    activite_id INT,
    -- Common fields
    montant DECIMAL(10,2) NOT NULL,
    methode_paiement VARCHAR(100) NOT NULL,
    statut VARCHAR(50) DEFAULT 'EN_ATTENTE',
    date_paiement DATETIME DEFAULT CURRENT_TIMESTAMP,
    transaction_id VARCHAR(255),
    reference_externe VARCHAR(255),
    reference_transaction VARCHAR(255),
    details_json TEXT,
    INDEX idx_commande (commande_id),
    INDEX idx_demande (demande_id),
    INDEX idx_client (client_id),
    INDEX idx_statut (statut),
    INDEX idx_date (date_paiement)
);
```

#### Shopping Cart System
```sql
CREATE TABLE panier (
    id INT PRIMARY KEY AUTO_INCREMENT,
    client_id INT NOT NULL,
    -- Legacy product support
    produit_id INT,
    -- New variant system
    variant_sku VARCHAR(100),
    produit_nom VARCHAR(255),
    prix_unitaire DECIMAL(10,3),
    -- Common fields
    quantite INT NOT NULL DEFAULT 1,
    date_ajout TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_client (client_id),
    INDEX idx_variant (variant_sku),
    INDEX idx_produit (produit_id)
);
```

---

## Security Implementation

### 1. Password Security
```java
// BCrypt password hashing
public class PasswordUtils {
    private static final int ROUNDS = 12;
    
    public static String hashPassword(String plainPassword) {
        return BCrypt.hashpw(plainPassword, BCrypt.gensalt(ROUNDS));
    }
    
    public static boolean verifyPassword(String plainPassword, String hashedPassword) {
        return BCrypt.checkpw(plainPassword, hashedPassword);
    }
}
```

### 2. SQL Injection Prevention
```java
// Always use PreparedStatement
public ProduitVariant getBySku(String sku) throws SQLException {
    String sql = "SELECT * FROM produit_variant WHERE sku = ?";
    PreparedStatement st = con.prepareStatement(sql);
    st.setString(1, sku); // Parameterized query prevents SQL injection
    ResultSet rs = st.executeQuery();
    // ...
}
```

### 3. Input Validation
```java
// Comprehensive input validation
public class ValidationUtils {
    public static boolean isValidEmail(String email) {
        return email != null && email.matches("^[A-Za-z0-9+_.-]+@(.+)$");
    }
    
    public static boolean isValidPrice(double price) {
        return price >= 0 && price <= 999999.999;
    }
    
    public static String sanitizeInput(String input) {
        if (input == null) return "";
        return input.trim().replaceAll("[<>\"'&]", "");
    }
}
```

### 4. Session Management
```java
// Secure session handling
public class SessionManager {
    private static user currentUser = null;
    private static long sessionStartTime = 0;
    private static final long SESSION_TIMEOUT = 30 * 60 * 1000; // 30 minutes
    
    public static boolean isSessionValid() {
        return currentUser != null && 
               (System.currentTimeMillis() - sessionStartTime) < SESSION_TIMEOUT;
    }
    
    public static void refreshSession() {
        sessionStartTime = System.currentTimeMillis();
    }
}
```

---

## Performance & Scalability

### 1. Database Optimization

#### Indexing Strategy
```sql
-- Performance-critical indexes
CREATE INDEX idx_produit_parent_partenaire ON produit_parent(partenaire_id);
CREATE INDEX idx_produit_variant_parent ON produit_variant(produit_parent_id);
CREATE INDEX idx_produit_variant_sku ON produit_variant(sku);
CREATE INDEX idx_panier_client ON panier(client_id);
CREATE INDEX idx_commande_user ON commande(user_id);
CREATE INDEX idx_paiement_commande ON paiement(commande_id);
CREATE INDEX idx_code_promo_code ON code_promo(code);
```

#### Query Optimization
```java
// Efficient product loading with single query
public List<ProduitCompletDTO> getProduitsOptimized(int partenaireId) {
    String sql = """
        SELECT pp.*, pv.*, sc.nom as sous_categorie_nom, c.nom as categorie_nom
        FROM produit_parent pp
        LEFT JOIN produit_variant pv ON pp.id = pv.produit_parent_id
        LEFT JOIN sous_categorie sc ON pp.sous_categorie_id = sc.id
        LEFT JOIN categorie c ON sc.categorie_id = c.id
        WHERE pp.partenaire_id = ? AND pp.statut = 'ACTIF'
        ORDER BY pp.id, pv.id
    """;
    
    // Process results efficiently to avoid N+1 queries
    Map<Integer, ProduitCompletDTO> produitsMap = new HashMap<>();
    // ... result processing
}
```

### 2. Memory Management

#### Connection Pooling
```java
// Database connection pooling
public class DatabasePool {
    private static final int MAX_CONNECTIONS = 20;
    private static final Queue<Connection> connectionPool = new LinkedList<>();
    
    public static Connection getConnection() {
        synchronized (connectionPool) {
            if (!connectionPool.isEmpty()) {
                return connectionPool.poll();
            }
        }
        return createNewConnection();
    }
    
    public static void releaseConnection(Connection conn) {
        synchronized (connectionPool) {
            if (connectionPool.size() < MAX_CONNECTIONS) {
                connectionPool.offer(conn);
            } else {
                try { conn.close(); } catch (SQLException e) { /* log error */ }
            }
        }
    }
}
```

#### Caching Strategy
```java
// Simple in-memory cache for frequently accessed data
public class CacheManager {
    private static final Map<String, Object> cache = new ConcurrentHashMap<>();
    private static final long CACHE_TIMEOUT = 5 * 60 * 1000; // 5 minutes
    
    public static void put(String key, Object value) {
        cache.put(key, new CacheEntry(value, System.currentTimeMillis()));
    }
    
    public static Object get(String key) {
        CacheEntry entry = (CacheEntry) cache.get(key);
        if (entry != null && !entry.isExpired()) {
            return entry.getValue();
        }
        cache.remove(key);
        return null;
    }
}
```

### 3. Asynchronous Processing

#### Background Tasks
```java
// Asynchronous email sending
public class AsyncEmailService {
    private static final ExecutorService emailExecutor = 
        Executors.newFixedThreadPool(3);
    
    public static void sendEmailAsync(String to, String subject, String body) {
        emailExecutor.submit(() -> {
            try {
                EmailService.sendEmail(to, subject, body);
            } catch (Exception e) {
                System.err.println("Failed to send email: " + e.getMessage());
            }
        });
    }
}
```

---

## Conclusion

NEXORA represents a comprehensive e-commerce solution that successfully combines modern software architecture principles with practical business requirements. The system's sophisticated product variant management, intelligent promotional systems, and seamless payment integration create a robust platform suitable for the Tunisian e-commerce market.

### Key Achievements

1. **Flexible Product Architecture**: The ProduitParent/ProduitVariant system provides unlimited scalability for product variations while maintaining data integrity.

2. **Advanced Business Logic**: Multi-type promotional codes, intelligent stock management, and unified payment processing demonstrate sophisticated business rule implementation.

3. **User Experience Excellence**: The chatbot integration, intuitive cart management, and responsive UI design create an engaging customer experience.

4. **Technical Excellence**: Clean architecture, comprehensive error handling, security best practices, and performance optimization ensure system reliability.

5. **Market Adaptation**: Konnect API integration, TND currency support, and Tunisian payment methods show thoughtful localization.

The system successfully balances complexity with usability, providing partners with powerful management tools while offering customers an intuitive shopping experience. The modular architecture ensures future extensibility, while the comprehensive documentation and clean codebase facilitate maintenance and enhancement.

This documentation serves as both a technical reference and a testament to the sophisticated engineering that powers the NEXORA e-commerce platform.