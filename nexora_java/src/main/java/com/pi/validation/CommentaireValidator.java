package com.pi.validation;

import com.pi.entities.Commentaire;

public final class CommentaireValidator {
    public void validate(Commentaire c) {
        if (c == null) throw new ValidationException("Commentaire null.");
        if (c.getAvisId() <= 0) throw new ValidationException("Avis invalide.");
        if (c.getUserId() <= 0) throw new ValidationException("User invalide.");

        String contenu = s(c.getContenu());
        if (contenu.length() < 2 || contenu.length() > 2000) throw new ValidationException("Contenu: 2..2000.");
        c.setContenu(contenu);
    }

    private static String s(String v) { return v == null ? "" : v.trim(); }
}
