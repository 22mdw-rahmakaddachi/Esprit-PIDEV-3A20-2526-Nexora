package com.pi.utils;

import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.SQLException;

public class mydatabase {
    final String USERNAME = "root";
    final String PASSWORD = "";
    final String URL = "jdbc:mysql://localhost:3306/java";

    Connection connection;
    static mydatabase instance;

    private mydatabase() {
        try {
            connection = DriverManager.getConnection(URL, USERNAME, PASSWORD);
            System.out.println("Connected to database successfully");
        } catch (SQLException e){
            System.out.println(e.getMessage());
        }
    }

    public static mydatabase getInstance() {
        if (instance == null) {
            instance = new mydatabase();
        }
        return instance;

    }

    public Connection getConnection() {
        return connection;
    }
}
