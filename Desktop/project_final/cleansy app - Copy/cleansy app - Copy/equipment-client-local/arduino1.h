#ifndef ARDUINO1_H
#define ARDUINO1_H

#include <QtSql/QSqlDatabase>
#include <QtSql/QSqlQuery>
#include <QtSql/QSqlError>
#include <QtSerialPort/QSerialPort>
#include <QtSerialPort/QSerialPortInfo>
#include <QDebug>
#include <QString>
#include <QMessageBox>
#include <QSqlQuery>
#include <QVariant>

class Arduino1 {
public:
    Arduino1();
    ~Arduino1();
    int connect_arduino1(QString portName1); // Connexion manuelle avec nom de port spécifié
    int close_arduino1();
    int write_to_arduino1(QByteArray);
    QByteArray read_from_arduino1();
    QSerialPort* getserial1();
    QString getarduino_port_name1();
    int updateDatabase1();
private:
    QSerialPort* serial1;
    QString arduino_port_name1;
    QByteArray data1;
};
#endif // ARDUINO1_H
