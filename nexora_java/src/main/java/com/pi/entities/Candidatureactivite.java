package com.pi.entities;

public class Candidatureactivite {
    private int id;
    private int activiteId;
    private int userId;
    private String statut; // EN_ATTENTE/ACCEPTEE/REFUSEE
    private String message;

    public Candidatureactivite() {}

    public Candidatureactivite(int activiteId, int userId, String message) {
        this.activiteId = activiteId;
        this.userId = userId;
        this.message = message;
        this.statut = "EN_ATTENTE";
    }


    public int getId() { return id; }
    public void setId(int id) { this.id = id; }

    public int getActiviteId() { return activiteId; }

    public void setActiviteId(int activiteId) { this.activiteId = activiteId; }

    public int getUserId() { return userId; }
    public void setUserId(int userId) { this.userId = userId; }

    public String getStatut() { return statut; }
    public void setStatut(String statut) { this.statut = statut; }

    public String getMessage() { return message; }
    public void setMessage(String message) { this.message = message; }
}

