#ifndef MAINWINDOW_H
#define MAINWINDOW_H

#include <QMainWindow>
#include <QString>
#include <QSqlQuery>
#include <QDate>
#include <QSqlQueryModel>
#include <QStandardItem>
#include <QDebug>
#include <QFileDialog>
#include <QDir>
#include <QMessageBox>

#include <QSqlRecord>
#include <QSortFilterProxyModel>

#include <QSqlError>
#include <QSqlTableModel>
//pdf
#include <QPdfWriter>
#include <QPainter>
#include <QPrinter>
#include <QFileDialog>

#include <QTextDocument>

#include <QRegularExpression>

#include <QMainWindow>
#include <QRegularExpression>

#include <QItemSelection>

//stats chart
#include <QtCharts>
#include <QChartView>
#include <QBarSet>
#include <QBarSeries>
#include <QLayout>


#include "client.h"
#include <QFrame>
#include "smsmanager.h"
//calendar
#include <QWidget>
#include <QDate>
#include <QSet>
#include <QMap>
#include <QList>
#include <QSqlQuery>
#include <QSqlError>
#include <QStandardItemModel>
#include <QTableView>
#include <QCalendarWidget>
#include <QSqlRecord>
#include <QFont>
#include "arduino.h"
#include "equipement.h"
//sdc
#include <QMainWindow>
#include <QFrame>
#include <QString>
#include <QVariant>
#include <QSqlQuery>
#include <QSqlQueryModel>
#include <QStandardItem>
#include <QDebug>
#include <QFileDialog>
#include <QDir>
#include <QMessageBox>
#include <QPainter>
#include <QStandardItemModel>
#include <QSqlRecord>
#include <QSortFilterProxyModel>

#include <QSqlError>
#include <QSqlTableModel>
//pdf
#include <QPdfWriter>
#include <QPainter>
#include <QPrinter>

#include <QTextDocument>

#include <QRegularExpression>
#include <QtCharts>
#include <QChartView>
#include <QBarSet>
#include <QBarSeries>
#include <QLayout>
#include <QtCharts/QPieSeries>
#include <QtCharts/QPieSlice>
#include <QtCharts/QChart>
#include <QtCharts/QChartView>
#include <QFileDialog>
#include <QTextDocument>
#include <QMainWindow>
#include <QFrame>
#include <QLineEdit>
#include <QTextEdit>
#include <QString>
#include <QMessageBox>
#include <QSqlQuery>
#include <QVariant>
#include "equipement.h"
#include "mailing.h"
#include "qrcode.h"
#include "local.h"
#include "arduino1.h"
////////

QT_BEGIN_NAMESPACE
namespace Ui { class MainWindow; }
QT_END_NAMESPACE

class MainWindow : public QMainWindow
{
    Q_OBJECT



public:
    MainWindow(QWidget *parent = nullptr);
    ~MainWindow();
    void loadData();
    void loadChart(QString chartType);
    void createPieChartReservations(QFrame *frame, const QString &title, QStringList reservationCategories, const QMap<QString, QList<int>> &data);
    //equip
    void equip_loadChart(QString chartType);
    void createPieChartPost(QFrame *frame, const QString &title, QStringList categories, const QMap<QString, QList<int>> &data);
protected:
    void closeEvent(QCloseEvent *event) override;
private slots:
    ///client slots
    void on_client_add_btn_clicked();

    void on_client_update_btn_clicked();

    void on_client_delete_btn_clicked();

    void on_client_tri_btn__clicked();

    void on_client_exportPdf_btn_clicked();

    void on_client_tableView_clicked(const QModelIndex &index);

    void on_client_sms_Btn_clicked();

    void on_lineEditSearchClient_textChanged(const QString &arg1);

    void on_clients_calendarWidget_selectionChanged();

    void addDataToTableViewClientCalander(const QList<QMap<QString, QString>>& dataList);

    void highlightReservationDates();

    ///arduino
    void readFromArduino();


    ///equipement slots
    void on_search_btn_clicked();

    void on_tri_btn_clicked();

    void on_tableViewEquipement_clicked(const QModelIndex &index);

    void on_lineEditSearchReference_textChanged(const QString &text);

    ////////////////////////////////////mailing///////////////////////////////////////////////////

    void on_email_Btn_clicked();

    ///////////qr code//////////
    void on_generateQRCodeButton_clicked();

    void on_equip_add_btn_clicked();

    void on_equip_update_btn_clicked();

    void on_equip_delete_btn_clicked();

    void on_equip_exportPdf_btn_clicked();

    void on_ajouterButton_2_clicked();

    void on_pushButton_8_clicked();

    void on_pushButton_9_clicked();

    //void on_flameStatusLabel_2_linkActivated(const QString &link);

    void on_pushButton_10_clicked();

    void on_pushButton_12_clicked();

    void on_pushButton_11_clicked();

    void on_pushButton_13_clicked();

    void on_pushButton_14_clicked();

    void on_pushbutton_2_clicked();
    void on_checkButton_2_clicked(); // Vérifier la connexion avec l'Arduino
    void readFromArduino1();         // Lire les données envoyées par l'Arduino
    void updateFlameStatus();


private:
    Ui::MainWindow *ui;
    //client
    Client c;
    //sms
    Sms sms;
    //equipement
    Equipement e;
    Equipement temp_eq;
    bool sortAscending = true;
    int currentImageIndex;
    //arduino
    QByteArray arduinoData;
    Arduino arduino;
    bool userExists(QString id_emp);
    QSerialPort *serialPort;        // Port série pour la communication avec l'Arduino
    QTimer *timer;                 // Timer pour lire périodiquement les données de l'Arduino
    bool flameDetected;
};
#endif // MAINWINDOW_H
