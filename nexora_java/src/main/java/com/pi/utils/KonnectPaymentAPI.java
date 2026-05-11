package com.pi.utils;

import java.io.*;
import java.net.HttpURLConnection;
import java.net.URL;
import java.nio.charset.StandardCharsets;
import org.json.JSONObject;

/**
 * Intégration API Konnect (Flouci) pour les paiements en Tunisie
 * Documentation: https://api.konnect.network/api/v2/payments/init-payment
 */
public class KonnectPaymentAPI {
    
    // Configuration - À REMPLACER par vos vraies clés
    private static final String API_URL = "https://api.konnect.network/api/v2/payments/init-payment";
    private static final String API_KEY = "VOTRE_CLE_API_KONNECT"; // À configurer
    private static final String WALLET_ID = "VOTRE_WALLET_ID"; // À configurer
    private static final boolean MODE_TEST = true; // true = simulation, false = production
    
    /**
     * Initialiser un paiement Konnect
     * @param montant Montant en TND (millimes)
     * @param commandeId ID de la commande
     * @param clientNom Nom du client
     * @return URL de paiement ou null si erreur
     */
    public static String initierPaiement(double montant, int commandeId, String clientNom) {
        if (MODE_TEST) {
            return simulerPaiement(montant, commandeId);
        }
        
        try {
            // Convertir TND en millimes (1 TND = 1000 millimes)
            int montantMillimes = (int) (montant * 1000);
            
            // Créer la requête JSON
            JSONObject payload = new JSONObject();
            payload.put("receiverWalletId", WALLET_ID);
            payload.put("amount", montantMillimes);
            payload.put("token", API_KEY);
            payload.put("type", "immediate");
            payload.put("description", "Commande #" + commandeId);
            payload.put("acceptedPaymentMethods", new String[]{"wallet", "bank_card", "e-DINAR"});
            payload.put("lifespan", 10); // 10 minutes
            payload.put("checkoutForm", true);
            payload.put("addPaymentFeesToAmount", true);
            
            // Webhook pour notification
            JSONObject webhook = new JSONObject();
            webhook.put("successUrl", "http://votre-site.com/payment/success?commande=" + commandeId);
            webhook.put("failUrl", "http://votre-site.com/payment/fail?commande=" + commandeId);
            payload.put("webhook", webhook);
            
            // Envoyer la requête
            URL url = new URL(API_URL);
            HttpURLConnection conn = (HttpURLConnection) url.openConnection();
            conn.setRequestMethod("POST");
            conn.setRequestProperty("Content-Type", "application/json");
            conn.setDoOutput(true);
            
            try (OutputStream os = conn.getOutputStream()) {
                byte[] input = payload.toString().getBytes(StandardCharsets.UTF_8);
                os.write(input, 0, input.length);
            }
            
            // Lire la réponse
            int responseCode = conn.getResponseCode();
            if (responseCode == HttpURLConnection.HTTP_OK) {
                BufferedReader in = new BufferedReader(new InputStreamReader(conn.getInputStream()));
                String inputLine;
                StringBuilder response = new StringBuilder();
                
                while ((inputLine = in.readLine()) != null) {
                    response.append(inputLine);
                }
                in.close();
                
                JSONObject jsonResponse = new JSONObject(response.toString());
                String paymentUrl = jsonResponse.getString("payUrl");
                String paymentRef = jsonResponse.getString("paymentRef");
                
                System.out.println("✅ Paiement Konnect initialisé: " + paymentRef);
                return paymentUrl;
            } else {
                System.err.println("❌ Erreur API Konnect: " + responseCode);
                return null;
            }
            
        } catch (Exception e) {
            System.err.println("❌ Erreur initialisation paiement: " + e.getMessage());
            e.printStackTrace();
            return null;
        }
    }
    
    /**
     * Vérifier le statut d'un paiement
     * @param paymentRef Référence du paiement
     * @return Statut (VALIDE, REFUSE, EN_ATTENTE)
     */
    public static String verifierStatutPaiement(String paymentRef) {
        if (MODE_TEST) {
            return "VALIDE"; // Simulation
        }
        
        try {
            String checkUrl = "https://api.konnect.network/api/v2/payments/" + paymentRef;
            URL url = new URL(checkUrl);
            HttpURLConnection conn = (HttpURLConnection) url.openConnection();
            conn.setRequestMethod("GET");
            conn.setRequestProperty("x-api-key", API_KEY);
            
            int responseCode = conn.getResponseCode();
            if (responseCode == HttpURLConnection.HTTP_OK) {
                BufferedReader in = new BufferedReader(new InputStreamReader(conn.getInputStream()));
                String inputLine;
                StringBuilder response = new StringBuilder();
                
                while ((inputLine = in.readLine()) != null) {
                    response.append(inputLine);
                }
                in.close();
                
                JSONObject jsonResponse = new JSONObject(response.toString());
                String status = jsonResponse.getJSONObject("payment").getString("status");
                
                // Mapper les statuts Konnect vers nos statuts
                switch (status) {
                    case "completed": return "VALIDE";
                    case "failed": return "REFUSE";
                    case "pending": return "EN_ATTENTE";
                    default: return "EN_ATTENTE";
                }
            }
            
        } catch (Exception e) {
            System.err.println("❌ Erreur vérification paiement: " + e.getMessage());
        }
        
        return "EN_ATTENTE";
    }
    
    /**
     * Mode simulation pour les tests (sans vraie API)
     */
    private static String simulerPaiement(double montant, int commandeId) {
        System.out.println("🧪 MODE SIMULATION - Paiement de " + montant + " TND pour commande #" + commandeId);
        // Retourner une URL de simulation
        return "SIMULATION_" + commandeId + "_" + System.currentTimeMillis();
    }
}
