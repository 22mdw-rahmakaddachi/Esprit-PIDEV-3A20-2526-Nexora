package com.pi.entities;

import java.time.LocalDateTime;

public class Avis {
    private int id;
    private int userId;
    private int rating;
    private String titre;
    private String contenu;
    private LocalDateTime createdAt;

    public Avis() {}

    public Avis(int userId, int rating, String titre, String contenu) {
        this.userId = userId;
        this.rating = rating;
        this.titre = titre;
        this.contenu = contenu;
    }

    public int getId() { return id; }
    public void setId(int id) { this.id = id; }

    public int getUserId() { return userId; }
    public void setUserId(int userId) { this.userId = userId; }

    public int getRating() { return rating; }
    public void setRating(int rating) { this.rating = rating; }

    public String getTitre() { return titre; }
    public void setTitre(String titre) { this.titre = titre; }

    public String getContenu() { return contenu; }
    public void setContenu(String contenu) { this.contenu = contenu; }

    public LocalDateTime getCreatedAt() { return createdAt; }
    public void setCreatedAt(LocalDateTime createdAt) { this.createdAt = createdAt; }

    @Override
    public String toString() {
        return "Avis{id=" + id + ", userId=" + userId + ", rating=" + rating + ", titre='" + titre + "'}";
    }
}