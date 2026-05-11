package com.pi.utils;

import java.io.BufferedReader;
import java.io.InputStreamReader;
import java.io.OutputStream;
import java.net.HttpURLConnection;
import java.net.URL;
import org.json.JSONArray;
import org.json.JSONObject;

/**
 * Service pour l'intégration avec HuggingFace API
 * Modèle: facebook/bart-large-cnn pour la summarization
 */
public class HuggingFaceService {

    private static final String API_URL = "https://api-inference.huggingface.co/models/facebook/bart-large-cnn";
    private static final String API_TOKEN = "YOUR_HUGGINGFACE_API_TOKEN"; // À remplacer

    /**
     * Résume un texte long en utilisant HuggingFace
     * @param text Le texte à résumer
     * @return Le résumé généré
     */
    public static String summarizeText(String text) {
        if (text == null || text.trim().isEmpty()) {
            return "";
        }

        // Si le texte est déjà court, pas besoin de résumer
        if (text.length() < 200) {
            return text;
        }

        try {
            URL url = new URL(API_URL);
            HttpURLConnection conn = (HttpURLConnection) url.openConnection();
            conn.setRequestMethod("POST");
            conn.setRequestProperty("Authorization", "Bearer " + API_TOKEN);
            conn.setRequestProperty("Content-Type", "application/json");
            conn.setDoOutput(true);

            // Créer le payload JSON
            JSONObject payload = new JSONObject();
            payload.put("inputs", text);
            payload.put("parameters", new JSONObject()
                    .put("max_length", 150)
                    .put("min_length", 50)
                    .put("do_sample", false)
            );

            // Envoyer la requête
            try (OutputStream os = conn.getOutputStream()) {
                byte[] input = payload.toString().getBytes("utf-8");
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

                // Parser la réponse JSON
                JSONArray jsonArray = new JSONArray(response.toString());
                if (jsonArray.length() > 0) {
                    JSONObject result = jsonArray.getJSONObject(0);
                    return result.getString("summary_text");
                }
            } else {
                System.err.println("❌ Erreur HuggingFace API: " + responseCode);
                // En cas d'erreur, retourner un résumé simple
                return createSimpleSummary(text);
            }

        } catch (Exception e) {
            System.err.println("❌ Erreur lors de la summarization: " + e.getMessage());
            // En cas d'erreur, retourner un résumé simple
            return createSimpleSummary(text);
        }

        return text;
    }

    /**
     * Crée un résumé simple en prenant les premières phrases
     * Utilisé comme fallback si l'API HuggingFace échoue
     */
    private static String createSimpleSummary(String text) {
        if (text.length() <= 200) {
            return text;
        }

        // Prendre les 200 premiers caractères et couper au dernier point
        String summary = text.substring(0, Math.min(200, text.length()));
        int lastPeriod = summary.lastIndexOf('.');

        if (lastPeriod > 50) {
            summary = summary.substring(0, lastPeriod + 1);
        } else {
            summary = summary + "...";
        }

        return summary;
    }

    /**
     * Génère une description automatique pour une activité
     */
    public static String generateActivityDescription(String nom, String type, String lieu, String date) {
        return String.format(
                "Découvrez %s, une activité de type %s située à %s. " +
                        "Prévue pour le %s, cette expérience unique vous permettra de profiter pleinement de votre temps libre. " +
                        "Réservez dès maintenant votre place pour ne pas manquer cette opportunité exceptionnelle!",
                nom, type, lieu, date
        );
    }
}
