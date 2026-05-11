package com.pi.entities;

import java.sql.Timestamp;

public class MessagePrive {

    private int id;
    private int conversationId;
    private int senderId;
    private String contenu;
    private Timestamp sentAt;

    public MessagePrive() {}

    public MessagePrive(int conversationId, int senderId, String contenu) {
        this.conversationId = conversationId;
        this.senderId = senderId;
        this.contenu = contenu;
    }

    // getters setters

    public int getId() {
        return id;
    }

    public void setId(int id) {
        this.id = id;
    }


    public int getConversationId() {
        return conversationId;
    }

    public void setConversationId(int conversationId) {
        this.conversationId = conversationId;
    }

    public int getSenderId() {
        return senderId;
    }

    public void setSenderId(int senderId) {
        this.senderId = senderId;
    }

    public String getContenu() {
        return contenu;
    }

    public void setContenu(String contenu) {
        this.contenu = contenu;
    }

    public Timestamp getSentAt() {
        return sentAt;
    }

    public void setSentAt(Timestamp sentAt) {
        this.sentAt = sentAt;
    }
}
