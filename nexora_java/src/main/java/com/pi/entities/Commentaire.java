package com.pi.entities;

import java.time.LocalDateTime;

public class Commentaire {
    private int id;
    private int avisId;
    private int userId;
    private String contenu;
    private LocalDateTime createdAt;

    public Commentaire() {}

    public Commentaire(int avisId, int userId, String contenu) {
        this.avisId = avisId;
        this.userId = userId;
        this.contenu = contenu;
    }

    public int getId() { return id; }
    public void setId(int id) { this.id = id; }

    public int getAvisId() { return avisId; }
    public void setAvisId(int avisId) { this.avisId = avisId; }

    public int getUserId() { return userId; }
    public void setUserId(int userId) { this.userId = userId; }

    public String getContenu() { return contenu; }
    public void setContenu(String contenu) { this.contenu = contenu; }

    public LocalDateTime getCreatedAt() { return createdAt; }
    public void setCreatedAt(LocalDateTime createdAt) { this.createdAt = createdAt; }

    @Override
    public String toString() {
        return "Commentaire{id=" + id + ", avisId=" + avisId + ", userId=" + userId + "}";
    }
}