package com.pi.utils;

import java.io.IOException;
import java.io.InputStream;
import java.util.Properties;

/**
 * Gestionnaire de configuration pour les APIs
 */
public class ApiConfigManager {
    
    private static Properties apiConfig = null;
    
    /**
     * Charge la configuration API depuis api.properties
     */
    private static Properties loadApiConfig() {
        if (apiConfig == null) {
            apiConfig = new Properties();
            try (InputStream input = ApiConfigManager.class.getClassLoader().getResourceAsStream("api.properties")) {
                if (input == null) {
                    System.out.println("⚠️ Fichier api.properties non trouvé, utilisation des valeurs par défaut");
                    setDefaultValues();
                } else {
                    apiConfig.load(input);
                    System.out.println("✅ Configuration API chargée depuis api.properties");
                }
            } catch (IOException e) {
                System.out.println("❌ Erreur lors du chargement de api.properties: " + e.getMessage());
                setDefaultValues();
            }
        }
        return apiConfig;
    }
    
    private static void setDefaultValues() {
        // Valeurs par défaut Konnect
        apiConfig.setProperty("konnect.api.url", "https://api.konnect.network/api/v2/payments/init-payment");
        apiConfig.setProperty("konnect.api.key", "VOTRE_CLE_API_KONNECT");
        apiConfig.setProperty("konnect.wallet.id", "VOTRE_WALLET_ID");
        apiConfig.setProperty("konnect.mode.test", "true");
        apiConfig.setProperty("konnect.timeout.seconds", "30");
        
        // Valeurs par défaut Chatbot
        apiConfig.setProperty("chatbot.max.results", "5");
        apiConfig.setProperty("chatbot.response.delay.ms", "500");
        apiConfig.setProperty("chatbot.budget.extraction.enabled", "true");
        
        // Valeurs par défaut HTTP
        apiConfig.setProperty("http.connection.timeout", "10000");
        apiConfig.setProperty("http.socket.timeout", "30000");
        apiConfig.setProperty("http.max.connections", "20");
        apiConfig.setProperty("http.user.agent", "NEXORA-Ecommerce/1.0");
    }
    
    // Méthodes d'accès pour Konnect API
    public static String getKonnectApiUrl() {
        return loadApiConfig().getProperty("konnect.api.url");
    }
    
    public static String getKonnectApiKey() {
        return loadApiConfig().getProperty("konnect.api.key");
    }
    
    public static String getKonnectWalletId() {
        return loadApiConfig().getProperty("konnect.wallet.id");
    }
    
    public static boolean isKonnectTestMode() {
        return Boolean.parseBoolean(loadApiConfig().getProperty("konnect.mode.test", "true"));
    }
    
    public static int getKonnectTimeout() {
        return Integer.parseInt(loadApiConfig().getProperty("konnect.timeout.seconds", "30"));
    }
    
    // Méthodes d'accès pour Chatbot
    public static int getChatbotMaxResults() {
        return Integer.parseInt(loadApiConfig().getProperty("chatbot.max.results", "5"));
    }
    
    public static int getChatbotResponseDelay() {
        return Integer.parseInt(loadApiConfig().getProperty("chatbot.response.delay.ms", "500"));
    }
    
    public static boolean isBudgetExtractionEnabled() {
        return Boolean.parseBoolean(loadApiConfig().getProperty("chatbot.budget.extraction.enabled", "true"));
    }
    
    // Méthodes d'accès pour HTTP
    public static int getHttpConnectionTimeout() {
        return Integer.parseInt(loadApiConfig().getProperty("http.connection.timeout", "10000"));
    }
    
    public static int getHttpSocketTimeout() {
        return Integer.parseInt(loadApiConfig().getProperty("http.socket.timeout", "30000"));
    }
    
    public static String getHttpUserAgent() {
        return loadApiConfig().getProperty("http.user.agent", "NEXORA-Ecommerce/1.0");
    }
    
    /**
     * Recharge la configuration (utile pour les tests)
     */
    public static void reloadConfig() {
        apiConfig = null;
        loadApiConfig();
    }
}