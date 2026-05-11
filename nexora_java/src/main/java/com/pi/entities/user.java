package com.pi.entities;

public class user {
    private  int id;
    private  String name;
    private  String email;
    private  String prenom;
    private  int num;
    private  String role;
    private  String mdp;
    private int tentative;
    private boolean validation;
    private long blockUntil;
    private int blockLevel;
    private int fingerId;

    public user(){}




    public user(String name, String email, String prenom, int num, String role, String mdp ,int tentative,boolean validation,long blockUntil ,int blockLevel)
     {
        this.name = name;
        this.email = email;
        this.prenom = prenom;
        this.num = num;
        this.role = role;
        this.mdp = mdp;
        this.tentative = tentative;
        this.validation = validation ;
        this.blockUntil = blockUntil ;
        this.blockLevel = blockLevel ;
    }
    public user(String name, String email, String prenom, int num, String role, String mdp )
    {
        this.name = name;
        this.email = email;
        this.prenom = prenom;
        this.num = num;
        this.role = role;
        this.mdp = mdp;

    }

    public int getTentative() {
        return tentative;
    }

    public void setTentative(int tentative) {
        this.tentative = tentative;
    }

    public boolean isValidation() {
        return validation;
    }

    public void setValidation(boolean validation) {
        this.validation = validation;
    }

    public long getBlockUntil() {
        return blockUntil;
    }

    public void setBlockUntil(long blockUntil) {
        this.blockUntil = blockUntil;
    }

    public int getBlockLevel() {
        return blockLevel;
    }

    public void setBlockLevel(int blockLevel) {
        this.blockLevel = blockLevel;
    }

    public int getId() {
        return id;
    }

    public String getName() {
        return name;
    }

    public void setName(String name) {
        this.name = name;
    }

    public String getEmail() {
        return email;
    }

    public void setEmail(String email) {
        this.email = email;
    }

    public String getPrenom() {
        return prenom;
    }

    public void setPrenom(String prenom) {
        this.prenom = prenom;
    }

    public void setId(int id) {
        this.id = id;
    }

    public int getNum() {
        return num;
    }

    public void setNum(int num) {
        this.num = num;
    }

    public String getRole() {
        return role;
    }

    public void setRole(String role) {
        this.role = role;
    }

    public int getFingerId() { return fingerId; }
    public void setFingerId(int fingerId) { this.fingerId = fingerId; }

    public String getMdp() {
        return mdp;
    }
    public void setMdp(String mdp) {
        this.mdp = mdp;
    }
    @Override
    public String toString() {
        return "user{" +
                "id=" + id +
                ", name='" + name + '\'' +
                ", email='" + email + '\'' +
                ", prenom='" + prenom + '\'' +
                ", num=" + num +
                ", role='" + role + '\'' +
                ", mdp='" + mdp + '\'' +
                ", tentative=" + tentative +
                ", validation=" + validation +
                ", blockUntil=" + blockUntil +
                ", blockLevel=" + blockLevel +
                '}';
    }

}
