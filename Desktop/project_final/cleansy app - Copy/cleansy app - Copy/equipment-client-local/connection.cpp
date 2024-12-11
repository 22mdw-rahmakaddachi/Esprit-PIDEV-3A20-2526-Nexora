#include "connection.h"

Connection::Connection() {}

bool Connection::createconnect()
{
    bool test = false;
    db = QSqlDatabase::addDatabase("QODBC");
    db.setDatabaseName("CPP_Project"); // Insert your data source name
    db.setUserName("system"); // Insert your username
    db.setPassword("123"); // Insert your password

    if (db.open()) {
        test = true;
    }

    return test;
}

void Connection::closeConnection() {
    db.close();
}



