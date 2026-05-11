package com.pi.utils;

import java.util.regex.Pattern;

public class ValidationUtils {
    
    // Regex pour email valide
    private static final Pattern EMAIL_PATTERN = Pattern.compile(
        "^[A-Za-z0-9+_.-]+@[A-Za-z0-9.-]+\\.[A-Za-z]{2,}$"
    );
    
    // Regex pour numéro de téléphone tunisien (8 chiffres)
    private static final Pattern PHONE_PATTERN = Pattern.compile(
        "^[0-9]{8}$"
    );
    
    // Regex pour mot de passe fort (min 8 caractères, 1 majuscule, 1 minuscule, 1 chiffre)
    private static final Pattern PASSWORD_STRONG_PATTERN = Pattern.compile(
        "^(?=.*[a-z])(?=.*[A-Z])(?=.*\\d).{8,}$"
    );
    
    /**
     * Vérifie si une chaîne est vide ou null
     */
    public static boolean isEmpty(String str) {
        return str == null || str.trim().isEmpty();
    }
    
    /**
     * Vérifie si un email est valide
     */
    public static boolean isValidEmail(String email) {
        if (isEmpty(email)) return false;
        return EMAIL_PATTERN.matcher(email.trim()).matches();
    }
    
    /**
     * Vérifie si un numéro de téléphone est valide (8 chiffres)
     */
    public static boolean isValidPhone(String phone) {
        if (isEmpty(phone)) return false;
        return PHONE_PATTERN.matcher(phone.trim()).matches();
    }
    
    /**
     * Vérifie si un mot de passe est fort
     */
    public static boolean isStrongPassword(String password) {
        if (isEmpty(password)) return false;
        return PASSWORD_STRONG_PATTERN.matcher(password).matches();
    }
    
    /**
     * Vérifie si un mot de passe a une longueur minimale
     */
    public static boolean isValidPasswordLength(String password, int minLength) {
        if (isEmpty(password)) return false;
        return password.length() >= minLength;
    }
    
    /**
     * Vérifie si un nom est valide (lettres uniquement, min 2 caractères)
     */
    public static boolean isValidName(String name) {
        if (isEmpty(name)) return false;
        String trimmed = name.trim();
        return trimmed.length() >= 2 && trimmed.matches("^[a-zA-ZÀ-ÿ\\s'-]+$");
    }
    
    /**
     * Vérifie si un nombre est valide
     */
    public static boolean isValidNumber(String number) {
        if (isEmpty(number)) return false;
        try {
            Integer.parseInt(number.trim());
            return true;
        } catch (NumberFormatException e) {
            return false;
        }
    }
    
    /**
     * Retourne un message d'erreur pour l'email
     */
    public static String getEmailErrorMessage(String email) {
        if (isEmpty(email)) {
            return "L'email est obligatoire";
        }
        if (!isValidEmail(email)) {
            return "Format d'email invalide (ex: exemple@domaine.com)";
        }
        return "";
    }
    
    /**
     * Retourne un message d'erreur pour le téléphone
     */
    public static String getPhoneErrorMessage(String phone) {
        if (isEmpty(phone)) {
            return "Le numéro de téléphone est obligatoire";
        }
        if (!isValidNumber(phone)) {
            return "Le numéro doit contenir uniquement des chiffres";
        }
        if (!isValidPhone(phone)) {
            return "Le numéro doit contenir exactement 8 chiffres";
        }
        return "";
    }
    
    /**
     * Retourne un message d'erreur pour le mot de passe
     */
    public static String getPasswordErrorMessage(String password) {
        if (isEmpty(password)) {
            return "Le mot de passe est obligatoire";
        }
        if (password.length() < 6) {
            return "Le mot de passe doit contenir au moins 6 caractères";
        }
        if (!isStrongPassword(password)) {
            return "Le mot de passe doit contenir au moins 8 caractères, une majuscule, une minuscule et un chiffre";
        }
        return "";
    }
    
    /**
     * Retourne un message d'erreur pour le nom/prénom
     */
    public static String getNameErrorMessage(String name, String fieldName) {
        if (isEmpty(name)) {
            return fieldName + " est obligatoire";
        }
        if (name.trim().length() < 2) {
            return fieldName + " doit contenir au moins 2 caractères";
        }
        if (!isValidName(name)) {
            return fieldName + " ne doit contenir que des lettres";
        }
        return "";
    }
}
