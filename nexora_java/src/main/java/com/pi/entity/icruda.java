package com.pi.entity;

import java.sql.SQLException;
import java.util.List;

public interface icruda <T>{
    public  void ajouter(T t) throws SQLException;
    public  void supprimer(int id) throws SQLException;
    public  void modifier(int id ) throws SQLException;
    List<T> afficher() throws SQLException;
}
