package com.pi.entities;

public class image {

    private int id;
    private String url;
    private int destinationId;

    public image() {}

    public image(String url, int destinationId) {
        this.url = url;
        this.destinationId = destinationId;
    }

    public image(int id, String url, int destinationId) {
        this.id = id;
        this.url = url;
        this.destinationId = destinationId;
    }

    public int getId() { return id; }
    public void setId(int id) { this.id = id; }

    public String getUrl() { return url; }
    public void setUrl(String url) { this.url = url; }

    public int getDestinationId() { return destinationId; }
    public void setDestinationId(int destinationId) {
        this.destinationId = destinationId;
    }
}