package com.pi.entities;

import java.time.LocalDateTime;

public class Notification {
    private int id;
    private int userId;
    private String userType; // CLIENT ou PARTENAIRE
    private String type; // NOUVELLE_DEMANDE, ACCEPTATION, REFUS, PAIEMENT, etc.
    private String titre;
    private String message;
    private boolean lue;
    private LocalDateTime dateCreation;
    private int activiteId;
    private int demandeId;

    public Notification() {
        this.dateCreation = LocalDateTime.now();
        this.lue = false;
    }

    public Notification(int userId, String userType, String type, String titre, String message) {
        this.userId = userId;
        this.userType = userType;
        this.type = type;
        this.titre = titre;
        this.message = message;
        this.dateCreation = LocalDateTime.now();
        this.lue = false;
    }

    // Getters et Setters
    public int getId() {
        return id;
    }

    public void setId(int id) {
        this.id = id;
    }

    public int getUserId() {
        return userId;
    }

    public void setUserId(int userId) {
        this.userId = userId;
    }

    public String getUserType() {
        return userType;
    }

    public void setUserType(String userType) {
        this.userType = userType;
    }

    public String getType() {
        return type;
    }

    public void setType(String type) {
        this.type = type;
    }

    public String getTitre() {
        return titre;
    }

    public void setTitre(String titre) {
        this.titre = titre;
    }

    public String getMessage() {
        return message;
    }

    public void setMessage(String message) {
        this.message = message;
    }

    public boolean isLue() {
        return lue;
    }

    public void setLue(boolean lue) {
        this.lue = lue;
    }

    public LocalDateTime getDateCreation() {
        return dateCreation;
    }

    public void setDateCreation(LocalDateTime dateCreation) {
        this.dateCreation = dateCreation;
    }

    public int getActiviteId() {
        return activiteId;
    }

    public void setActiviteId(int activiteId) {
        this.activiteId = activiteId;
    }

    public int getDemandeId() {
        return demandeId;
    }

    public void setDemandeId(int demandeId) {
        this.demandeId = demandeId;
    }

    @Override
    public String toString() {
        return "Notification{" +
                "id=" + id +
                ", userId=" + userId +
                ", type='" + type + '\'' +
                ", titre='" + titre + '\'' +
                ", lue=" + lue +
                ", dateCreation=" + dateCreation +
                '}';
    }
}
