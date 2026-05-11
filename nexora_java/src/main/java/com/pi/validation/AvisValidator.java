package com.pi.validation;

import com.pi.entities.Avis;


public final class AvisValidator {
    public void validate(Avis a) {
        if (a == null) throw new ValidationException("Avis null.");
        if (a.getUserId() <= 0) throw new ValidationException("User invalide.");
        if (a.getRating() < 1 || a.getRating() > 5) throw new ValidationException("Rating doit être 1..5.");

        String titre = s(a.getTitre());
        String contenu = s(a.getContenu());

        if (titre.length() < 3 || titre.length() > 100) throw new ValidationException("Titre: 3..100.");
        if (contenu.length() < 10 || contenu.length() > 5000) throw new ValidationException("Contenu: 10..5000.");

        a.setTitre(titre);
        a.setContenu(contenu);
    }

    private static String s(String v) { return v == null ? "" : v.trim(); }
}
