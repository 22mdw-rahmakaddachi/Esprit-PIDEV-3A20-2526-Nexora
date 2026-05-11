package com.pi.entity;

import java.sql.SQLException;
import java.util.List;

public interface icrud <T>{
    public  void ajouter(T t) throws SQLException;
    public  void supprimer(int id) throws SQLException;
    public  void modifier(T t) throws SQLException;
    List<T> afficher() throws SQLException;
}
