package com.pi.entities;

import java.time.LocalDateTime;

public class Paiement {
    private int id;
    private int demandeId;
    private int clientId;
    private int activiteId;
    private double montant;
    private String methodePaiement;
    private String statut; // EN_COURS, COMPLETE, ECHOUE
    private LocalDateTime datePaiement;
    private String referenceTransaction;

    public Paiement() {
        this.datePaiement = LocalDateTime.now();
        this.statut = "EN_COURS";
    }

    public Paiement(int demandeId, int clientId, int activiteId, double montant, String methodePaiement) {
        this.demandeId = demandeId;
        this.clientId = clientId;
        this.activiteId = activiteId;
        this.montant = montant;
        this.methodePaiement = methodePaiement;
        this.datePaiement = LocalDateTime.now();
        this.statut = "EN_COURS";
    }

    // Getters et Setters
    public int getId() {
        return id;
    }

    public void setId(int id) {
        this.id = id;
    }

    public int getDemandeId() {
        return demandeId;
    }

    public void setDemandeId(int demandeId) {
        this.demandeId = demandeId;
    }

    public int getClientId() {
        return clientId;
    }

    public void setClientId(int clientId) {
        this.clientId = clientId;
    }

    public int getActiviteId() {
        return activiteId;
    }

    public void setActiviteId(int activiteId) {
        this.activiteId = activiteId;
    }

    public double getMontant() {
        return montant;
    }

    public void setMontant(double montant) {
        this.montant = montant;
    }

    public String getMethodePaiement() {
        return methodePaiement;
    }

    public void setMethodePaiement(String methodePaiement) {
        this.methodePaiement = methodePaiement;
    }

    public String getStatut() {
        return statut;
    }

    public void setStatut(String statut) {
        this.statut = statut;
    }

    public LocalDateTime getDatePaiement() {
        return datePaiement;
    }

    public void setDatePaiement(LocalDateTime datePaiement) {
        this.datePaiement = datePaiement;
    }

    public String getReferenceTransaction() {
        return referenceTransaction;
    }

    public void setReferenceTransaction(String referenceTransaction) {
        this.referenceTransaction = referenceTransaction;
    }

    @Override
    public String toString() {
        return "Paiement{" +
                "id=" + id +
                ", montant=" + montant +
                ", statut='" + statut + '\'' +
                ", datePaiement=" + datePaiement +
                '}';
    }
}
