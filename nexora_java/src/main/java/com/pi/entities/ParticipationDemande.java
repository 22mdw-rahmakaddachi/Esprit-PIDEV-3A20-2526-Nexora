package com.pi.entities;

import java.time.LocalDateTime;

public class ParticipationDemande {
    private int id;
    private int activiteId;
    private int clientId;
    private String clientNom;
    private String clientEmail;
    private String clientTelephone;
    private String statut; // EN_ATTENTE, ACCEPTEE, REFUSEE
    private LocalDateTime dateDemande;
    private boolean paiementEffectue;

    public ParticipationDemande() {
        this.dateDemande = LocalDateTime.now();
        this.statut = "EN_ATTENTE";
        this.paiementEffectue = false;
    }

    public ParticipationDemande(int activiteId, int clientId, String clientNom,
                                String clientEmail, String clientTelephone) {
        this.activiteId = activiteId;
        this.clientId = clientId;
        this.clientNom = clientNom;
        this.clientEmail = clientEmail;
        this.clientTelephone = clientTelephone;
        this.dateDemande = LocalDateTime.now();
        this.statut = "EN_ATTENTE";
        this.paiementEffectue = false;
    }

    // Getters et Setters
    public int getId() {
        return id;
    }

    public void setId(int id) {
        this.id = id;
    }

    public int getActiviteId() {
        return activiteId;
    }

    public void setActiviteId(int activiteId) {
        this.activiteId = activiteId;
    }

    public int getClientId() {
        return clientId;
    }

    public void setClientId(int clientId) {
        this.clientId = clientId;
    }

    public String getClientNom() {
        return clientNom;
    }

    public void setClientNom(String clientNom) {
        this.clientNom = clientNom;
    }

    public String getClientEmail() {
        return clientEmail;
    }

    public void setClientEmail(String clientEmail) {
        this.clientEmail = clientEmail;
    }

    public String getClientTelephone() {
        return clientTelephone;
    }

    public void setClientTelephone(String clientTelephone) {
        this.clientTelephone = clientTelephone;
    }

    public String getStatut() {
        return statut;
    }

    public void setStatut(String statut) {
        this.statut = statut;
    }

    public LocalDateTime getDateDemande() {
        return dateDemande;
    }

    public void setDateDemande(LocalDateTime dateDemande) {
        this.dateDemande = dateDemande;
    }

    public boolean isPaiementEffectue() {
        return paiementEffectue;
    }

    public void setPaiementEffectue(boolean paiementEffectue) {
        this.paiementEffectue = paiementEffectue;
    }

    @Override
    public String toString() {
        return "ParticipationDemande{" +
                "id=" + id +
                ", activiteId=" + activiteId +
                ", clientNom='" + clientNom + '\'' +
                ", statut='" + statut + '\'' +
                ", dateDemande=" + dateDemande +
                '}';
    }
}
