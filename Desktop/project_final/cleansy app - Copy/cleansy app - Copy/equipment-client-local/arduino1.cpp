#include "arduino1.h"
Arduino1::Arduino1() {
    data1 = "";
    arduino_port_name1 = "";
    serial1 = new QSerialPort;
}

Arduino1::~Arduino1() {
    if (serial1->isOpen()) {
        serial1->close();
    }
    delete serial1;
}

QString Arduino1::getarduino_port_name1() {
    return arduino_port_name1;
}

QSerialPort* Arduino1::getserial1() {
    return serial1;
}

int Arduino1::connect_arduino1(QString portName) {
    arduino_port_name1 = portName;
    serial1->setPortName(arduino_port_name1);

    if (serial1->open(QSerialPort::ReadWrite)) {
        serial1->setBaudRate(QSerialPort::Baud9600);
        serial1->setDataBits(QSerialPort::Data8);
        serial1->setParity(QSerialPort::NoParity);
        serial1->setStopBits(QSerialPort::OneStop);
        serial1->setFlowControl(QSerialPort::NoFlowControl);

        qDebug() << "Successfully connected to port:" << arduino_port_name1;
        return 0; // Success
    } else {
        qDebug() << "Failed to open port:" << arduino_port_name1 << serial1->errorString();
        return 1; // Failed to open port
    }
}

int Arduino1::close_arduino1() {
    if (serial1->isOpen()) {
        serial1->close();
        return 0; // Success
    }
    return 1; // Port was not open
}

int Arduino1::write_to_arduino1(QByteArray d) {
    if (serial1->isWritable()) {
        serial1->write(d);
        return 0; // Success
    } else {
        qDebug() << "Couldn't write to serial!";
        return 1; // Write failed
    }
}

QByteArray Arduino1::read_from_arduino1() {
    if (serial1->isReadable()) {
        data1 = serial1->readAll();
        return data1;
    }
    return QByteArray();
}






int Arduino1::updateDatabase1() {
    QSqlDatabase db = QSqlDatabase::addDatabase("local");  // Changez selon votre SGBD (SQLite ici).
    db.setDatabaseName("path/to/your/database.db");          // Chemin de votre base de données.

    if (!db.open()) {
        qDebug() << "Erreur d'ouverture de la base de données:" << db.lastError().text();
        return 1; // Échec d'ouverture
    }

    QSqlQuery query;
    query.prepare("UPDATE local SET flam = 1 WHERE id = :id");  // Changez selon vos besoins.
    query.bindValue(":id", 1);  // Remplacez avec l'ID ou condition voulue.

    if (!query.exec()) {
        qDebug() << "Erreur lors de la mise à jour:" << query.lastError().text();
        db.close();
        return 1; // Échec de la mise à jour
    }

    db.close();
    qDebug() << "Mise à jour réussie.";
    return 0; // Succès
}

