package com.pi.utils;

import java.util.Base64;
import java.util.prefs.Preferences;

public class RememberMeManager {
    
    private static final String PREF_EMAIL = "remembered_email";
    private static final String PREF_PASSWORD = "remembered_password";
    private static final String PREF_REMEMBER = "remember_me";
    
    private static final Preferences prefs = Preferences.userNodeForPackage(RememberMeManager.class);
    
    /**
     * Sauvegarde l'email et le mot de passe si "Remember Me" est activé
     */
    public static void saveCredentials(String email, String password, boolean rememberMe) {
        if (rememberMe) {
            prefs.put(PREF_EMAIL, email);
            // Encoder le mot de passe en Base64 pour une sécurité basique
            String encodedPassword = Base64.getEncoder().encodeToString(password.getBytes());
            prefs.put(PREF_PASSWORD, encodedPassword);
            prefs.putBoolean(PREF_REMEMBER, true);
        } else {
            clearCredentials();
        }
    }
    
    /**
     * Récupère l'email sauvegardé
     */
    public static String getSavedEmail() {
        return prefs.get(PREF_EMAIL, "");
    }
    
    /**
     * Récupère le mot de passe sauvegardé (décodé)
     */
    public static String getSavedPassword() {
        String encodedPassword = prefs.get(PREF_PASSWORD, "");
        if (encodedPassword.isEmpty()) {
            return "";
        }
        try {
            byte[] decodedBytes = Base64.getDecoder().decode(encodedPassword);
            return new String(decodedBytes);
        } catch (Exception e) {
            return "";
        }
    }
    
    /**
     * Vérifie si "Remember Me" était activé
     */
    public static boolean isRememberMeEnabled() {
        return prefs.getBoolean(PREF_REMEMBER, false);
    }
    
    /**
     * Efface les informations sauvegardées
     */
    public static void clearCredentials() {
        prefs.remove(PREF_EMAIL);
        prefs.remove(PREF_PASSWORD);
        prefs.putBoolean(PREF_REMEMBER, false);
    }
}
