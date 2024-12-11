/********************************************************************************
** Form generated from reading UI file 'mainwindow.ui'
**
** Created by: Qt User Interface Compiler version 6.3.0
**
** WARNING! All changes made in this file will be lost when recompiling UI file!
********************************************************************************/

#ifndef UI_MAINWINDOW_H
#define UI_MAINWINDOW_H

#include <QtCore/QVariant>
#include <QtGui/QAction>
#include <QtGui/QIcon>
#include <QtWidgets/QApplication>
#include <QtWidgets/QCalendarWidget>
#include <QtWidgets/QCheckBox>
#include <QtWidgets/QComboBox>
#include <QtWidgets/QDateEdit>
#include <QtWidgets/QFrame>
#include <QtWidgets/QGroupBox>
#include <QtWidgets/QHBoxLayout>
#include <QtWidgets/QHeaderView>
#include <QtWidgets/QLabel>
#include <QtWidgets/QLineEdit>
#include <QtWidgets/QMainWindow>
#include <QtWidgets/QMenu>
#include <QtWidgets/QMenuBar>
#include <QtWidgets/QPushButton>
#include <QtWidgets/QRadioButton>
#include <QtWidgets/QStatusBar>
#include <QtWidgets/QTabWidget>
#include <QtWidgets/QTableView>
#include <QtWidgets/QTextEdit>
#include <QtWidgets/QVBoxLayout>
#include <QtWidgets/QWidget>

QT_BEGIN_NAMESPACE

class Ui_MainWindow
{
public:
    QWidget *centralwidget;
    QTabWidget *gs_table;
    QWidget *tab_3;
    QTabWidget *lineEdit_date_2;
    QWidget *tab_5;
    QGroupBox *groupBox_3;
    QVBoxLayout *verticalLayout_3;
    QLabel *label_14;
    QLineEdit *client_lineEditCin;
    QLabel *label_9;
    QLineEdit *client_lineEditNom;
    QLabel *label_10;
    QLineEdit *client_lineEditPrenom;
    QLabel *label_11;
    QLineEdit *client_lineEditTelephone;
    QLabel *label_13;
    QComboBox *client_comboBoxSexe;
    QLabel *label_15;
    QDateEdit *client_res_dateEdit;
    QLineEdit *lineEditSearchClient;
    QTableView *client_tableView;
    QPushButton *client_tri_btn_;
    QLabel *label;
    QComboBox *client_TriBox;
    QFrame *clientChartFrame;
    QHBoxLayout *horizontalLayout;
    QPushButton *client_exportPdf_btn;
    QPushButton *client_update_btn;
    QPushButton *client_delete_btn;
    QPushButton *client_add_btn;
    QComboBox *client_searchBox;
    QWidget *tab_8;
    QPushButton *client_sms_Btn;
    QLineEdit *smsLineEdit;
    QLabel *label_2;
    QWidget *tab_6;
    QCalendarWidget *clients_calendarWidget;
    QLineEdit *lineEdit_date;
    QTableView *clients_calander_tab_view;
    QWidget *equipement;
    QTabWidget *tabWidget_3;
    QWidget *tab1;
    QGroupBox *groupBox_2;
    QVBoxLayout *verticalLayout;
    QLabel *label_5;
    QLineEdit *lineEditReference;
    QLabel *label_4;
    QLineEdit *lineEditNom;
    QLabel *label_6;
    QLineEdit *lineEditQuantite;
    QLabel *label_12;
    QLineEdit *lineEditPrix;
    QLabel *label_16;
    QLineEdit *lineEditMarque;
    QLabel *label_7;
    QComboBox *comboBoxEtat;
    QLabel *label_8;
    QComboBox *comboBoxType;
    QPushButton *equip_add_btn;
    QPushButton *equip_update_btn;
    QPushButton *equip_delete_btn;
    QPushButton *tri_btn;
    QPushButton *search_btn;
    QLineEdit *lineEditSearchReference;
    QTableView *tableViewEquipement;
    QLabel *label_17;
    QComboBox *comboBoxTri1;
    QFrame *equipementChartFrame;
    QHBoxLayout *horizontalLayout_2;
    QLabel *label_19;
    QComboBox *comboBoxSearch;
    QComboBox *comboBoxTri2;
    QPushButton *equip_exportPdf_btn;
    QWidget *tab_21;
    QLabel *qr_code;
    QTextEdit *textEdit;
    QPushButton *generateQRCodeButton;
    QLineEdit *lineEditReference3;
    QLabel *label_25;
    QWidget *tab_4;
    QLabel *label_20;
    QLineEdit *lineEditMail;
    QPushButton *email_Btn;
    QLineEdit *lineEditReference_2;
    QCheckBox *checkBoxGuide;
    QCheckBox *checkBoxFormulaire;
    QLabel *label_21;
    QWidget *tab;
    QTabWidget *tabWidget;
    QWidget *tab_7;
    QGroupBox *groupBox_4;
    QLabel *label_24;
    QLabel *label_26;
    QLabel *label_27;
    QLineEdit *adresseLineEdit_2;
    QLineEdit *idLineEdit_2;
    QLineEdit *typeLineEdit_2;
    QPushButton *ajouterButton_2;
    QTableView *tableView_2;
    QPushButton *pushbutton_2;
    QPushButton *pushButton_8;
    QPushButton *pushButton_9;
    QLineEdit *lineEdit_3;
    QLabel *label_28;
    QPushButton *pushButton_10;
    QPushButton *pushButton_11;
    QPushButton *pushButton_12;
    QRadioButton *radioButton_5;
    QRadioButton *radioButton_6;
    QPushButton *pushButton_13;
    QRadioButton *radioButton_7;
    QRadioButton *radioButton_8;
    QPushButton *pushButton_14;
    QLineEdit *lineEdit_4;
    QTextEdit *textEdit_4;
    QTextEdit *textEdit_5;
    QLabel *flameStatusLabel_2;
    QPushButton *checkButton_2;
    QMenuBar *menubar;
    QMenu *menucleanzy;
    QStatusBar *statusbar;

    void setupUi(QMainWindow *MainWindow)
    {
        if (MainWindow->objectName().isEmpty())
            MainWindow->setObjectName(QString::fromUtf8("MainWindow"));
        MainWindow->resize(1560, 815);
        MainWindow->setStyleSheet(QString::fromUtf8("/* Dark Theme for the entire application */\n"
"QMainWindow {\n"
"    background-color: #02355F; /* Navy blue background */\n"
"}\n"
"\n"
"QWidget {\n"
"    background-color: #02355F;\n"
"}\n"
"\n"
"/* Styling for Labels */\n"
"QLabel {\n"
"    font-size: 14px;\n"
"    color: #ffffff; /* White text color */\n"
"}\n"
"\n"
"/* Styling for Line Edits (not readOnly) */\n"
"QLineEdit[readOnly=\"false\"] {\n"
"    background-color: #D3D3D3; /* Slightly darker background */\n"
"    border: 2px solid #1DB954; /* Spotify green border */\n"
"    border-radius: 5px;\n"
"    padding: 5px;\n"
"    font-size: 14px;\n"
"    color: #ffffff;\n"
"}\n"
"\n"
"/* Hover effect for Line Edits (not readOnly) */\n"
"QLineEdit[readOnly=\"false\"]:hover {\n"
"    background-color: #1E1E1E; /* Slightly lighter background on hover */\n"
"    border-color: #33A2FF; /* Light blue border on hover */\n"
"}\n"
"\n"
"/* Focus effect for Line Edits (not readOnly) */\n"
"QLineEdit[readOnly=\"false\"]:focus {\n"
"    border-color: #33A2FF; /* Ligh"
                        "t blue border on focus */\n"
"}\n"
"\n"
"/* Styling for Line Edits (readOnly) */\n"
"QLineEdit[readOnly=\"true\"] {\n"
"    background-color: #1f1e1e; /* Slightly darker background */\n"
"    border: 2px solid #1DB954; /* Spotify green border */\n"
"    border-radius: 5px;\n"
"    padding: 5px;\n"
"    font-size: 14px;\n"
"    color: #ffffff;\n"
"}\n"
"\n"
"/* Style for Line Edits that are disabled */\n"
"QLineEdit:disabled {\n"
"    background-color: #666666; /* Gray background for disabled state */\n"
"    border: 2px solid #999999; /* Gray border for disabled state */\n"
"    border-radius: 5px;\n"
"    padding: 5px;\n"
"    font-size: 14px;\n"
"    color: #999999; /* Light gray text color for disabled state */\n"
"}\n"
"\n"
"/* Styling for Push Buttons */\n"
"QPushButton {\n"
"    background-color: #0073e6;\n"
"    color: #ffffff;\n"
"    border: 2px solid #00509e;\n"
"    border-radius: 5px;\n"
"    padding: 10px 20px;\n"
"    font-size: 16px;\n"
"}\n"
"\n"
"/* Hover effect for \"add\" QPushButton */\n"
""
                        "QPushButton[accessibleName=\"add\"]:hover {\n"
"    background-color: #107d38;\n"
"}\n"
"\n"
"/* Hover effect for \"delete\" QPushButton */\n"
"QPushButton[accessibleName=\"delete\"]:hover {\n"
"    background-color: #107d38;\n"
"}\n"
"\n"
"/* Styling for QTextEdit */\n"
"QTextEdit {\n"
"    background-color: #121212;\n"
"    border: 2px solid #1DB954;\n"
"    border-radius: 5px;\n"
"    padding: 5px;\n"
"    font-size: 14px;\n"
"    color: #ffffff;\n"
"}\n"
"\n"
"QTextEdit:hover {\n"
"    background-color: #1E1E1E;\n"
"    border-color: #33A2FF;\n"
"}\n"
"\n"
"QTextEdit:focus {\n"
"    border-color: #33A2FF;\n"
"}\n"
"\n"
"/* Styling for QComboBox */\n"
"QComboBox {\n"
"    background-color: #121212;\n"
"    border: 2px solid #1DB954;\n"
"    border-radius: 5px;\n"
"    padding: 5px;\n"
"    font-size: 14px;\n"
"    color: #ffffff;\n"
"}\n"
"\n"
"QComboBox:hover {\n"
"    background-color: #1E1E1E;\n"
"    border-color: #33A2FF;\n"
"}\n"
"\n"
"QComboBox:focus {\n"
"    border-color: #33A2FF;\n"
"}\n"
"\n"
"/*"
                        " Styling for QGroupBox */\n"
"QGroupBox {\n"
"    border: none;\n"
"}\n"
"\n"
"QGroupBox::title {\n"
"    color: #ffffff;\n"
"}\n"
"\n"
"QGroupBox:hover {\n"
"    background-color: #1E1E1E;\n"
"}\n"
"\n"
"/* Styling for QScrollBar */\n"
"QScrollBar:vertical {\n"
"    background: #121212;\n"
"    width: 10px;\n"
"}\n"
"\n"
"QScrollBar::handle:vertical {\n"
"    background: #1DB954;\n"
"    min-height: 20px;\n"
"    border-radius: 5px;\n"
"}\n"
"\n"
"/* Styling for QListView */\n"
"QListView {\n"
"    background-color: #121212;\n"
"    border: 2px solid #1DB954;\n"
"    border-radius: 5px;\n"
"    color: #ffffff;\n"
"    selection-background-color: #1DB954;\n"
"}\n"
"\n"
"/* Styling for QTabWidget */\n"
"QTabWidget {\n"
"    background-color: #212121;\n"
"}\n"
"\n"
"QTabBar::tab {\n"
"    padding: 10px 25px;\n"
"}\n"
"\n"
"QTabBar::tab:selected {\n"
"    background-color: #1DB954;\n"
"    color: #ffffff;\n"
"}\n"
"\n"
"QTabBar::tab:!selected {\n"
"    background-color: #212121;\n"
"    color: #b3b3b3;\n"
"}\n"
""
                        "\n"
"/* Styling for QTableView and QTableWidget */\n"
"QTableView, QTableWidget {\n"
"    background-color: #02355F; /* Navy blue table background */\n"
"    border: 1px solid #666666; /* Fine gray border */\n"
"    border-radius: 5px;\n"
"    color: #ffffff;\n"
"    selection-background-color: #1DB954;\n"
"    font-size: 18px;\n"
"}\n"
"\n"
"/* Header style for QTableView and QTableWidget */\n"
"QHeaderView::section {\n"
"    background-color: #1DB954;\n"
"    color: #ffffff;\n"
"    padding: 5px;\n"
"    border: 1px solid #666666; /* Fine gray borders for headers */\n"
"}\n"
"\n"
"/* Row style for QTableView and QTableWidget */\n"
"QTableView::item, QTableWidget::item {\n"
"    border: 1px solid #666666; /* Fine gray row and column borders */\n"
"    padding: 5px;\n"
"}\n"
"\n"
"/* Row hover effect */\n"
"QTableView::item:hover, QTableWidget::item:hover {\n"
"    background-color: #333333;\n"
"}\n"
"\n"
"/* Selected row style */\n"
"QTableView::item:selected, QTableWidget::item:selected {\n"
"    background-"
                        "color: #1DB954;\n"
"    color: #ffffff;\n"
"}\n"
"\n"
"/* Styling for QSpinBox and QDoubleSpinBox */\n"
"QSpinBox[readOnly=\"false\"], QDoubleSpinBox[readOnly=\"false\"] {\n"
"    background-color: #121212;\n"
"    border: 2px solid #1DB954;\n"
"    border-radius: 5px;\n"
"    padding: 5px;\n"
"    font-size: 14px;\n"
"    color: #ffffff;\n"
"}\n"
"\n"
"QSpinBox[readOnly=\"true\"], QDoubleSpinBox[readOnly=\"true\"] {\n"
"    background-color: #1f1e1e;\n"
"    border: 2px solid #1DB954;\n"
"    border-radius: 5px;\n"
"    padding: 5px;\n"
"    font-size: 14px;\n"
"    color: #ffffff;\n"
"}\n"
""));
        centralwidget = new QWidget(MainWindow);
        centralwidget->setObjectName(QString::fromUtf8("centralwidget"));
        gs_table = new QTabWidget(centralwidget);
        gs_table->setObjectName(QString::fromUtf8("gs_table"));
        gs_table->setGeometry(QRect(0, 0, 1441, 731));
        gs_table->setStyleSheet(QString::fromUtf8(""));
        gs_table->setTabShape(QTabWidget::Triangular);
        gs_table->setIconSize(QSize(32, 32));
        tab_3 = new QWidget();
        tab_3->setObjectName(QString::fromUtf8("tab_3"));
        lineEdit_date_2 = new QTabWidget(tab_3);
        lineEdit_date_2->setObjectName(QString::fromUtf8("lineEdit_date_2"));
        lineEdit_date_2->setGeometry(QRect(0, 0, 1471, 791));
        lineEdit_date_2->setTabPosition(QTabWidget::West);
        lineEdit_date_2->setTabShape(QTabWidget::Rounded);
        lineEdit_date_2->setIconSize(QSize(32, 32));
        tab_5 = new QWidget();
        tab_5->setObjectName(QString::fromUtf8("tab_5"));
        groupBox_3 = new QGroupBox(tab_5);
        groupBox_3->setObjectName(QString::fromUtf8("groupBox_3"));
        groupBox_3->setGeometry(QRect(0, 10, 551, 521));
        groupBox_3->setStyleSheet(QString::fromUtf8("font: 13pt \"Calibri\";"));
        groupBox_3->setFlat(false);
        groupBox_3->setCheckable(false);
        verticalLayout_3 = new QVBoxLayout(groupBox_3);
        verticalLayout_3->setSpacing(7);
        verticalLayout_3->setObjectName(QString::fromUtf8("verticalLayout_3"));
        verticalLayout_3->setContentsMargins(15, 50, 15, 50);
        label_14 = new QLabel(groupBox_3);
        label_14->setObjectName(QString::fromUtf8("label_14"));

        verticalLayout_3->addWidget(label_14);

        client_lineEditCin = new QLineEdit(groupBox_3);
        client_lineEditCin->setObjectName(QString::fromUtf8("client_lineEditCin"));

        verticalLayout_3->addWidget(client_lineEditCin);

        label_9 = new QLabel(groupBox_3);
        label_9->setObjectName(QString::fromUtf8("label_9"));
        QFont font;
        font.setFamilies({QString::fromUtf8("Calibri")});
        font.setPointSize(13);
        font.setBold(false);
        font.setItalic(false);
        label_9->setFont(font);

        verticalLayout_3->addWidget(label_9);

        client_lineEditNom = new QLineEdit(groupBox_3);
        client_lineEditNom->setObjectName(QString::fromUtf8("client_lineEditNom"));

        verticalLayout_3->addWidget(client_lineEditNom);

        label_10 = new QLabel(groupBox_3);
        label_10->setObjectName(QString::fromUtf8("label_10"));

        verticalLayout_3->addWidget(label_10);

        client_lineEditPrenom = new QLineEdit(groupBox_3);
        client_lineEditPrenom->setObjectName(QString::fromUtf8("client_lineEditPrenom"));

        verticalLayout_3->addWidget(client_lineEditPrenom);

        label_11 = new QLabel(groupBox_3);
        label_11->setObjectName(QString::fromUtf8("label_11"));

        verticalLayout_3->addWidget(label_11);

        client_lineEditTelephone = new QLineEdit(groupBox_3);
        client_lineEditTelephone->setObjectName(QString::fromUtf8("client_lineEditTelephone"));

        verticalLayout_3->addWidget(client_lineEditTelephone);

        label_13 = new QLabel(groupBox_3);
        label_13->setObjectName(QString::fromUtf8("label_13"));

        verticalLayout_3->addWidget(label_13);

        client_comboBoxSexe = new QComboBox(groupBox_3);
        client_comboBoxSexe->addItem(QString());
        client_comboBoxSexe->addItem(QString());
        client_comboBoxSexe->addItem(QString());
        client_comboBoxSexe->setObjectName(QString::fromUtf8("client_comboBoxSexe"));

        verticalLayout_3->addWidget(client_comboBoxSexe);

        label_15 = new QLabel(groupBox_3);
        label_15->setObjectName(QString::fromUtf8("label_15"));

        verticalLayout_3->addWidget(label_15);

        client_res_dateEdit = new QDateEdit(groupBox_3);
        client_res_dateEdit->setObjectName(QString::fromUtf8("client_res_dateEdit"));

        verticalLayout_3->addWidget(client_res_dateEdit);

        verticalLayout_3->setStretch(6, 1);
        verticalLayout_3->setStretch(7, 1);
        lineEditSearchClient = new QLineEdit(tab_5);
        lineEditSearchClient->setObjectName(QString::fromUtf8("lineEditSearchClient"));
        lineEditSearchClient->setGeometry(QRect(650, 50, 295, 42));
        client_tableView = new QTableView(tab_5);
        client_tableView->setObjectName(QString::fromUtf8("client_tableView"));
        client_tableView->setGeometry(QRect(550, 140, 671, 231));
        client_tri_btn_ = new QPushButton(tab_5);
        client_tri_btn_->setObjectName(QString::fromUtf8("client_tri_btn_"));
        client_tri_btn_->setGeometry(QRect(970, 80, 131, 41));
        QIcon icon;
        icon.addFile(QString::fromUtf8(":/res/refresh.ico"), QSize(), QIcon::Normal, QIcon::Off);
        client_tri_btn_->setIcon(icon);
        client_tri_btn_->setIconSize(QSize(32, 32));
        label = new QLabel(tab_5);
        label->setObjectName(QString::fromUtf8("label"));
        label->setGeometry(QRect(1070, 470, 171, 141));
        label->setStyleSheet(QString::fromUtf8("image: url(:/ressources/logo/logo.png);\n"
""));
        client_TriBox = new QComboBox(tab_5);
        client_TriBox->addItem(QString());
        client_TriBox->addItem(QString());
        client_TriBox->addItem(QString());
        client_TriBox->addItem(QString());
        client_TriBox->addItem(QString());
        client_TriBox->addItem(QString());
        client_TriBox->setObjectName(QString::fromUtf8("client_TriBox"));
        client_TriBox->setGeometry(QRect(960, 40, 151, 28));
        clientChartFrame = new QFrame(tab_5);
        clientChartFrame->setObjectName(QString::fromUtf8("clientChartFrame"));
        clientChartFrame->setGeometry(QRect(609, 399, 481, 201));
        horizontalLayout = new QHBoxLayout(clientChartFrame);
        horizontalLayout->setObjectName(QString::fromUtf8("horizontalLayout"));
        client_exportPdf_btn = new QPushButton(tab_5);
        client_exportPdf_btn->setObjectName(QString::fromUtf8("client_exportPdf_btn"));
        client_exportPdf_btn->setGeometry(QRect(190, 580, 171, 41));
        QIcon icon1;
        icon1.addFile(QString::fromUtf8(":/res/file.ico"), QSize(), QIcon::Normal, QIcon::Off);
        client_exportPdf_btn->setIcon(icon1);
        client_exportPdf_btn->setIconSize(QSize(32, 32));
        client_update_btn = new QPushButton(tab_5);
        client_update_btn->setObjectName(QString::fromUtf8("client_update_btn"));
        client_update_btn->setGeometry(QRect(190, 520, 171, 41));
        client_update_btn->setIcon(icon);
        client_update_btn->setIconSize(QSize(32, 32));
        client_delete_btn = new QPushButton(tab_5);
        client_delete_btn->setObjectName(QString::fromUtf8("client_delete_btn"));
        client_delete_btn->setGeometry(QRect(370, 520, 171, 41));
        QIcon icon2;
        icon2.addFile(QString::fromUtf8(":/res/delete.ico"), QSize(), QIcon::Normal, QIcon::Off);
        client_delete_btn->setIcon(icon2);
        client_delete_btn->setIconSize(QSize(32, 32));
        client_add_btn = new QPushButton(tab_5);
        client_add_btn->setObjectName(QString::fromUtf8("client_add_btn"));
        client_add_btn->setGeometry(QRect(10, 520, 171, 41));
        QIcon icon3;
        icon3.addFile(QString::fromUtf8(":/res/add.ico"), QSize(), QIcon::Normal, QIcon::Off);
        client_add_btn->setIcon(icon3);
        client_add_btn->setIconSize(QSize(32, 32));
        client_searchBox = new QComboBox(tab_5);
        client_searchBox->addItem(QString());
        client_searchBox->addItem(QString());
        client_searchBox->addItem(QString());
        client_searchBox->setObjectName(QString::fromUtf8("client_searchBox"));
        client_searchBox->setGeometry(QRect(560, 60, 82, 28));
        QIcon icon4;
        icon4.addFile(QString::fromUtf8(":/res/application.ico"), QSize(), QIcon::Normal, QIcon::Off);
        lineEdit_date_2->addTab(tab_5, icon4, QString());
        tab_8 = new QWidget();
        tab_8->setObjectName(QString::fromUtf8("tab_8"));
        client_sms_Btn = new QPushButton(tab_8);
        client_sms_Btn->setObjectName(QString::fromUtf8("client_sms_Btn"));
        client_sms_Btn->setGeometry(QRect(790, 170, 151, 51));
        QIcon icon5;
        icon5.addFile(QString::fromUtf8(":/res/send_1.ico"), QSize(), QIcon::Normal, QIcon::Off);
        client_sms_Btn->setIcon(icon5);
        smsLineEdit = new QLineEdit(tab_8);
        smsLineEdit->setObjectName(QString::fromUtf8("smsLineEdit"));
        smsLineEdit->setGeometry(QRect(490, 170, 261, 41));
        label_2 = new QLabel(tab_8);
        label_2->setObjectName(QString::fromUtf8("label_2"));
        label_2->setGeometry(QRect(320, 160, 141, 61));
        label_2->setStyleSheet(QString::fromUtf8("font: 18pt \"Segoe UI\";"));
        QIcon icon6;
        icon6.addFile(QString::fromUtf8(":/res/sms_1.ico"), QSize(), QIcon::Normal, QIcon::Off);
        lineEdit_date_2->addTab(tab_8, icon6, QString());
        tab_6 = new QWidget();
        tab_6->setObjectName(QString::fromUtf8("tab_6"));
        clients_calendarWidget = new QCalendarWidget(tab_6);
        clients_calendarWidget->setObjectName(QString::fromUtf8("clients_calendarWidget"));
        clients_calendarWidget->setGeometry(QRect(60, 14, 1141, 291));
        lineEdit_date = new QLineEdit(tab_6);
        lineEdit_date->setObjectName(QString::fromUtf8("lineEdit_date"));
        lineEdit_date->setGeometry(QRect(60, 330, 1141, 31));
        clients_calander_tab_view = new QTableView(tab_6);
        clients_calander_tab_view->setObjectName(QString::fromUtf8("clients_calander_tab_view"));
        clients_calander_tab_view->setGeometry(QRect(60, 370, 1141, 241));
        QIcon icon7;
        icon7.addFile(QString::fromUtf8(":/res/calendar.png"), QSize(), QIcon::Normal, QIcon::Off);
        lineEdit_date_2->addTab(tab_6, icon7, QString());
        QIcon icon8;
        icon8.addFile(QString::fromUtf8(":/res/client.ico"), QSize(), QIcon::Normal, QIcon::Off);
        gs_table->addTab(tab_3, icon8, QString());
        equipement = new QWidget();
        equipement->setObjectName(QString::fromUtf8("equipement"));
        tabWidget_3 = new QTabWidget(equipement);
        tabWidget_3->setObjectName(QString::fromUtf8("tabWidget_3"));
        tabWidget_3->setGeometry(QRect(0, 0, 1521, 741));
        tabWidget_3->setTabPosition(QTabWidget::West);
        tabWidget_3->setTabShape(QTabWidget::Rounded);
        tabWidget_3->setIconSize(QSize(32, 32));
        tab1 = new QWidget();
        tab1->setObjectName(QString::fromUtf8("tab1"));
        groupBox_2 = new QGroupBox(tab1);
        groupBox_2->setObjectName(QString::fromUtf8("groupBox_2"));
        groupBox_2->setGeometry(QRect(20, 20, 461, 541));
        groupBox_2->setStyleSheet(QString::fromUtf8("font: 13pt \"Calibri\";"));
        groupBox_2->setFlat(false);
        groupBox_2->setCheckable(false);
        verticalLayout = new QVBoxLayout(groupBox_2);
        verticalLayout->setSpacing(7);
        verticalLayout->setObjectName(QString::fromUtf8("verticalLayout"));
        verticalLayout->setContentsMargins(15, 30, 15, 30);
        label_5 = new QLabel(groupBox_2);
        label_5->setObjectName(QString::fromUtf8("label_5"));

        verticalLayout->addWidget(label_5);

        lineEditReference = new QLineEdit(groupBox_2);
        lineEditReference->setObjectName(QString::fromUtf8("lineEditReference"));

        verticalLayout->addWidget(lineEditReference);

        label_4 = new QLabel(groupBox_2);
        label_4->setObjectName(QString::fromUtf8("label_4"));
        QFont font1;
        font1.setFamilies({QString::fromUtf8("Calibri")});
        font1.setPointSize(11);
        font1.setBold(false);
        font1.setItalic(false);
        label_4->setFont(font1);
        label_4->setStyleSheet(QString::fromUtf8("font: 11pt \"Calibri\";"));

        verticalLayout->addWidget(label_4);

        lineEditNom = new QLineEdit(groupBox_2);
        lineEditNom->setObjectName(QString::fromUtf8("lineEditNom"));

        verticalLayout->addWidget(lineEditNom);

        label_6 = new QLabel(groupBox_2);
        label_6->setObjectName(QString::fromUtf8("label_6"));

        verticalLayout->addWidget(label_6);

        lineEditQuantite = new QLineEdit(groupBox_2);
        lineEditQuantite->setObjectName(QString::fromUtf8("lineEditQuantite"));

        verticalLayout->addWidget(lineEditQuantite);

        label_12 = new QLabel(groupBox_2);
        label_12->setObjectName(QString::fromUtf8("label_12"));

        verticalLayout->addWidget(label_12);

        lineEditPrix = new QLineEdit(groupBox_2);
        lineEditPrix->setObjectName(QString::fromUtf8("lineEditPrix"));

        verticalLayout->addWidget(lineEditPrix);

        label_16 = new QLabel(groupBox_2);
        label_16->setObjectName(QString::fromUtf8("label_16"));

        verticalLayout->addWidget(label_16);

        lineEditMarque = new QLineEdit(groupBox_2);
        lineEditMarque->setObjectName(QString::fromUtf8("lineEditMarque"));

        verticalLayout->addWidget(lineEditMarque);

        label_7 = new QLabel(groupBox_2);
        label_7->setObjectName(QString::fromUtf8("label_7"));

        verticalLayout->addWidget(label_7);

        comboBoxEtat = new QComboBox(groupBox_2);
        comboBoxEtat->addItem(QString());
        comboBoxEtat->addItem(QString());
        comboBoxEtat->addItem(QString());
        comboBoxEtat->setObjectName(QString::fromUtf8("comboBoxEtat"));

        verticalLayout->addWidget(comboBoxEtat);

        label_8 = new QLabel(groupBox_2);
        label_8->setObjectName(QString::fromUtf8("label_8"));

        verticalLayout->addWidget(label_8);

        comboBoxType = new QComboBox(groupBox_2);
        comboBoxType->addItem(QString());
        comboBoxType->addItem(QString());
        comboBoxType->setObjectName(QString::fromUtf8("comboBoxType"));

        verticalLayout->addWidget(comboBoxType);

        verticalLayout->setStretch(0, 1);
        verticalLayout->setStretch(1, 1);
        equip_add_btn = new QPushButton(tab1);
        equip_add_btn->setObjectName(QString::fromUtf8("equip_add_btn"));
        equip_add_btn->setGeometry(QRect(40, 560, 131, 41));
        equip_add_btn->setIcon(icon3);
        equip_add_btn->setIconSize(QSize(32, 32));
        equip_update_btn = new QPushButton(tab1);
        equip_update_btn->setObjectName(QString::fromUtf8("equip_update_btn"));
        equip_update_btn->setGeometry(QRect(180, 560, 131, 41));
        equip_update_btn->setIcon(icon);
        equip_update_btn->setIconSize(QSize(32, 32));
        equip_delete_btn = new QPushButton(tab1);
        equip_delete_btn->setObjectName(QString::fromUtf8("equip_delete_btn"));
        equip_delete_btn->setGeometry(QRect(320, 560, 131, 41));
        equip_delete_btn->setStyleSheet(QString::fromUtf8(""));
        equip_delete_btn->setIcon(icon2);
        equip_delete_btn->setIconSize(QSize(32, 32));
        tri_btn = new QPushButton(tab1);
        tri_btn->setObjectName(QString::fromUtf8("tri_btn"));
        tri_btn->setGeometry(QRect(990, 10, 171, 41));
        QIcon icon9;
        icon9.addFile(QString::fromUtf8(":/res/filter.ico"), QSize(), QIcon::Normal, QIcon::Off);
        tri_btn->setIcon(icon9);
        tri_btn->setIconSize(QSize(32, 32));
        search_btn = new QPushButton(tab1);
        search_btn->setObjectName(QString::fromUtf8("search_btn"));
        search_btn->setGeometry(QRect(500, 20, 131, 41));
        search_btn->setStyleSheet(QString::fromUtf8(""));
        QIcon icon10;
        icon10.addFile(QString::fromUtf8(":/res/search.ico"), QSize(), QIcon::Normal, QIcon::Off);
        search_btn->setIcon(icon10);
        search_btn->setIconSize(QSize(32, 32));
        lineEditSearchReference = new QLineEdit(tab1);
        lineEditSearchReference->setObjectName(QString::fromUtf8("lineEditSearchReference"));
        lineEditSearchReference->setGeometry(QRect(640, 40, 295, 42));
        tableViewEquipement = new QTableView(tab1);
        tableViewEquipement->setObjectName(QString::fromUtf8("tableViewEquipement"));
        tableViewEquipement->setGeometry(QRect(490, 130, 711, 281));
        label_17 = new QLabel(tab1);
        label_17->setObjectName(QString::fromUtf8("label_17"));
        label_17->setGeometry(QRect(820, 550, 63, 20));
        comboBoxTri1 = new QComboBox(tab1);
        comboBoxTri1->addItem(QString());
        comboBoxTri1->addItem(QString());
        comboBoxTri1->addItem(QString());
        comboBoxTri1->setObjectName(QString::fromUtf8("comboBoxTri1"));
        comboBoxTri1->setGeometry(QRect(950, 60, 111, 41));
        equipementChartFrame = new QFrame(tab1);
        equipementChartFrame->setObjectName(QString::fromUtf8("equipementChartFrame"));
        equipementChartFrame->setGeometry(QRect(550, 430, 601, 201));
        horizontalLayout_2 = new QHBoxLayout(equipementChartFrame);
        horizontalLayout_2->setObjectName(QString::fromUtf8("horizontalLayout_2"));
        label_19 = new QLabel(tab1);
        label_19->setObjectName(QString::fromUtf8("label_19"));
        label_19->setGeometry(QRect(1130, 480, 111, 111));
        label_19->setPixmap(QPixmap(QString::fromUtf8("../GS__equipement - Copy succes 2/Design a (((log 02555e2c-2316-439f-ae6c-687596cb4096.ico")));
        label_19->setScaledContents(true);
        comboBoxSearch = new QComboBox(tab1);
        comboBoxSearch->addItem(QString());
        comboBoxSearch->addItem(QString());
        comboBoxSearch->addItem(QString());
        comboBoxSearch->setObjectName(QString::fromUtf8("comboBoxSearch"));
        comboBoxSearch->setGeometry(QRect(500, 70, 131, 41));
        comboBoxTri2 = new QComboBox(tab1);
        comboBoxTri2->addItem(QString());
        comboBoxTri2->addItem(QString());
        comboBoxTri2->setObjectName(QString::fromUtf8("comboBoxTri2"));
        comboBoxTri2->setGeometry(QRect(1070, 60, 111, 41));
        equip_exportPdf_btn = new QPushButton(tab1);
        equip_exportPdf_btn->setObjectName(QString::fromUtf8("equip_exportPdf_btn"));
        equip_exportPdf_btn->setGeometry(QRect(180, 620, 131, 41));
        equip_exportPdf_btn->setIcon(icon1);
        equip_exportPdf_btn->setIconSize(QSize(32, 32));
        tabWidget_3->addTab(tab1, icon4, QString());
        tab_21 = new QWidget();
        tab_21->setObjectName(QString::fromUtf8("tab_21"));
        qr_code = new QLabel(tab_21);
        qr_code->setObjectName(QString::fromUtf8("qr_code"));
        qr_code->setGeometry(QRect(730, 160, 150, 150));
        qr_code->setStyleSheet(QString::fromUtf8("background-color: rgb(255, 255, 255);"));
        textEdit = new QTextEdit(tab_21);
        textEdit->setObjectName(QString::fromUtf8("textEdit"));
        textEdit->setGeometry(QRect(420, 20, 341, 51));
        textEdit->setStyleSheet(QString::fromUtf8("background-color: #02355F;"));
        generateQRCodeButton = new QPushButton(tab_21);
        generateQRCodeButton->setObjectName(QString::fromUtf8("generateQRCodeButton"));
        generateQRCodeButton->setGeometry(QRect(740, 360, 131, 61));
        generateQRCodeButton->setStyleSheet(QString::fromUtf8("font: 15pt \"Calibri\";"));
        QIcon icon11;
        icon11.addFile(QString::fromUtf8(":/res/qr-code_1.ico"), QSize(), QIcon::Normal, QIcon::Off);
        generateQRCodeButton->setIcon(icon11);
        generateQRCodeButton->setIconSize(QSize(25, 25));
        lineEditReference3 = new QLineEdit(tab_21);
        lineEditReference3->setObjectName(QString::fromUtf8("lineEditReference3"));
        lineEditReference3->setGeometry(QRect(210, 230, 281, 51));
        label_25 = new QLabel(tab_21);
        label_25->setObjectName(QString::fromUtf8("label_25"));
        label_25->setGeometry(QRect(210, 170, 211, 41));
        label_25->setStyleSheet(QString::fromUtf8("font: 15pt \"Calibri\";"));
        QIcon icon12;
        icon12.addFile(QString::fromUtf8(":/res/barcode-scan.ico"), QSize(), QIcon::Normal, QIcon::Off);
        tabWidget_3->addTab(tab_21, icon12, QString());
        tab_4 = new QWidget();
        tab_4->setObjectName(QString::fromUtf8("tab_4"));
        label_20 = new QLabel(tab_4);
        label_20->setObjectName(QString::fromUtf8("label_20"));
        label_20->setGeometry(QRect(150, 50, 221, 61));
        label_20->setStyleSheet(QString::fromUtf8("font: 12pt \"Segoe UI\";"));
        lineEditMail = new QLineEdit(tab_4);
        lineEditMail->setObjectName(QString::fromUtf8("lineEditMail"));
        lineEditMail->setGeometry(QRect(260, 230, 291, 51));
        email_Btn = new QPushButton(tab_4);
        email_Btn->setObjectName(QString::fromUtf8("email_Btn"));
        email_Btn->setGeometry(QRect(230, 500, 181, 51));
        email_Btn->setIcon(icon5);
        lineEditReference_2 = new QLineEdit(tab_4);
        lineEditReference_2->setObjectName(QString::fromUtf8("lineEditReference_2"));
        lineEditReference_2->setGeometry(QRect(220, 110, 291, 51));
        checkBoxGuide = new QCheckBox(tab_4);
        checkBoxGuide->setObjectName(QString::fromUtf8("checkBoxGuide"));
        checkBoxGuide->setGeometry(QRect(230, 320, 151, 26));
        checkBoxFormulaire = new QCheckBox(tab_4);
        checkBoxFormulaire->setObjectName(QString::fromUtf8("checkBoxFormulaire"));
        checkBoxFormulaire->setGeometry(QRect(250, 370, 181, 26));
        label_21 = new QLabel(tab_4);
        label_21->setObjectName(QString::fromUtf8("label_21"));
        label_21->setGeometry(QRect(170, 190, 151, 20));
        label_21->setStyleSheet(QString::fromUtf8("font: 12pt \"Segoe UI\";"));
        QIcon icon13;
        icon13.addFile(QString::fromUtf8(":/res/email.ico"), QSize(), QIcon::Normal, QIcon::Off);
        tabWidget_3->addTab(tab_4, icon13, QString());
        QIcon icon14;
        icon14.addFile(QString::fromUtf8(":/res/cleaning.ico"), QSize(), QIcon::Normal, QIcon::Off);
        gs_table->addTab(equipement, icon14, QString());
        tab = new QWidget();
        tab->setObjectName(QString::fromUtf8("tab"));
        tabWidget = new QTabWidget(tab);
        tabWidget->setObjectName(QString::fromUtf8("tabWidget"));
        tabWidget->setGeometry(QRect(0, -40, 1311, 721));
        tabWidget->setStyleSheet(QString::fromUtf8("/* Dark Theme for the entire application */\n"
"QMainWindow {\n"
"    background-color: #02355F; /* Navy blue background */\n"
"}\n"
"\n"
"QWidget {\n"
"    background-color: #02355F;\n"
"}\n"
"\n"
"/* Styling for Labels */\n"
"QLabel {\n"
"    font-size: 14px;\n"
"    color: #ffffff; /* White text color */\n"
"}\n"
"\n"
"/* Styling for Line Edits (not readOnly) */\n"
"QLineEdit[readOnly=\"false\"] {\n"
"    background-color: #D3D3D3; /* Slightly darker background */\n"
"    border: 2px solid #1DB954; /* Spotify green border */\n"
"    border-radius: 5px;\n"
"    padding: 5px;\n"
"    font-size: 14px;\n"
"    color: #ffffff;\n"
"}\n"
"\n"
"/* Hover effect for Line Edits (not readOnly) */\n"
"QLineEdit[readOnly=\"false\"]:hover {\n"
"    background-color: #1E1E1E; /* Slightly lighter background on hover */\n"
"    border-color: #33A2FF; /* Light blue border on hover */\n"
"}\n"
"\n"
"/* Focus effect for Line Edits (not readOnly) */\n"
"QLineEdit[readOnly=\"false\"]:focus {\n"
"    border-color: #33A2FF; /* Ligh"
                        "t blue border on focus */\n"
"}\n"
"\n"
"/* Styling for Line Edits (readOnly) */\n"
"QLineEdit[readOnly=\"true\"] {\n"
"    background-color: #1f1e1e; /* Slightly darker background */\n"
"    border: 2px solid #1DB954; /* Spotify green border */\n"
"    border-radius: 5px;\n"
"    padding: 5px;\n"
"    font-size: 14px;\n"
"    color: #ffffff;\n"
"}\n"
"\n"
"/* Style for Line Edits that are disabled */\n"
"QLineEdit:disabled {\n"
"    background-color: #666666; /* Gray background for disabled state */\n"
"    border: 2px solid #999999; /* Gray border for disabled state */\n"
"    border-radius: 5px;\n"
"    padding: 5px;\n"
"    font-size: 14px;\n"
"    color: #999999; /* Light gray text color for disabled state */\n"
"}\n"
"\n"
"/* Styling for Push Buttons */\n"
"QPushButton {\n"
"    background-color: #0073e6;\n"
"    color: #ffffff;\n"
"    border: 2px solid #00509e;\n"
"    border-radius: 5px;\n"
"    padding: 10px 20px;\n"
"    font-size: 16px;\n"
"}\n"
"\n"
"/* Hover effect for \"add\" QPushButton */\n"
""
                        "QPushButton[accessibleName=\"add\"]:hover {\n"
"    background-color: #107d38;\n"
"}\n"
"\n"
"/* Hover effect for \"delete\" QPushButton */\n"
"QPushButton[accessibleName=\"delete\"]:hover {\n"
"    background-color: #107d38;\n"
"}\n"
"\n"
"/* Styling for QTextEdit */\n"
"QTextEdit {\n"
"    background-color: #121212;\n"
"    border: 2px solid #1DB954;\n"
"    border-radius: 5px;\n"
"    padding: 5px;\n"
"    font-size: 14px;\n"
"    color: #ffffff;\n"
"}\n"
"\n"
"QTextEdit:hover {\n"
"    background-color: #1E1E1E;\n"
"    border-color: #33A2FF;\n"
"}\n"
"\n"
"QTextEdit:focus {\n"
"    border-color: #33A2FF;\n"
"}\n"
"\n"
"/* Styling for QComboBox */\n"
"QComboBox {\n"
"    background-color: #121212;\n"
"    border: 2px solid #1DB954;\n"
"    border-radius: 5px;\n"
"    padding: 5px;\n"
"    font-size: 14px;\n"
"    color: #ffffff;\n"
"}\n"
"\n"
"QComboBox:hover {\n"
"    background-color: #1E1E1E;\n"
"    border-color: #33A2FF;\n"
"}\n"
"\n"
"QComboBox:focus {\n"
"    border-color: #33A2FF;\n"
"}\n"
"\n"
"/*"
                        " Styling for QGroupBox */\n"
"QGroupBox {\n"
"    border: none;\n"
"}\n"
"\n"
"QGroupBox::title {\n"
"    color: #ffffff;\n"
"}\n"
"\n"
"QGroupBox:hover {\n"
"    background-color: #1E1E1E;\n"
"}\n"
"\n"
"/* Styling for QScrollBar */\n"
"QScrollBar:vertical {\n"
"    background: #121212;\n"
"    width: 10px;\n"
"}\n"
"\n"
"QScrollBar::handle:vertical {\n"
"    background: #1DB954;\n"
"    min-height: 20px;\n"
"    border-radius: 5px;\n"
"}\n"
"\n"
"/* Styling for QListView */\n"
"QListView {\n"
"    background-color: #121212;\n"
"    border: 2px solid #1DB954;\n"
"    border-radius: 5px;\n"
"    color: #ffffff;\n"
"    selection-background-color: #1DB954;\n"
"}\n"
"\n"
"/* Styling for QTabWidget */\n"
"QTabWidget {\n"
"    background-color: #212121;\n"
"}\n"
"\n"
"QTabBar::tab {\n"
"    padding: 10px 25px;\n"
"}\n"
"\n"
"QTabBar::tab:selected {\n"
"    background-color: #1DB954;\n"
"    color: #ffffff;\n"
"}\n"
"\n"
"QTabBar::tab:!selected {\n"
"    background-color: #212121;\n"
"    color: #b3b3b3;\n"
"}\n"
""
                        "\n"
"/* Styling for QTableView and QTableWidget */\n"
"QTableView, QTableWidget {\n"
"    background-color: #02355F; /* Navy blue table background */\n"
"    border: 1px solid #666666; /* Fine gray border */\n"
"    border-radius: 5px;\n"
"    color: #ffffff;\n"
"    selection-background-color: #1DB954;\n"
"    font-size: 18px;\n"
"}\n"
"\n"
"/* Header style for QTableView and QTableWidget */\n"
"QHeaderView::section {\n"
"    background-color: #1DB954;\n"
"    color: #ffffff;\n"
"    padding: 5px;\n"
"    border: 1px solid #666666; /* Fine gray borders for headers */\n"
"}\n"
"\n"
"/* Row style for QTableView and QTableWidget */\n"
"QTableView::item, QTableWidget::item {\n"
"    border: 1px solid #666666; /* Fine gray row and column borders */\n"
"    padding: 5px;\n"
"}\n"
"\n"
"/* Row hover effect */\n"
"QTableView::item:hover, QTableWidget::item:hover {\n"
"    background-color: #333333;\n"
"}\n"
"\n"
"/* Selected row style */\n"
"QTableView::item:selected, QTableWidget::item:selected {\n"
"    background-"
                        "color: #1DB954;\n"
"    color: #ffffff;\n"
"}\n"
"\n"
"/* Styling for QSpinBox and QDoubleSpinBox */\n"
"QSpinBox[readOnly=\"false\"], QDoubleSpinBox[readOnly=\"false\"] {\n"
"    background-color: #121212;\n"
"    border: 2px solid #1DB954;\n"
"    border-radius: 5px;\n"
"    padding: 5px;\n"
"    font-size: 14px;\n"
"    color: #ffffff;\n"
"}\n"
"\n"
"QSpinBox[readOnly=\"true\"], QDoubleSpinBox[readOnly=\"true\"] {\n"
"    background-color: #1f1e1e;\n"
"    border: 2px solid #1DB954;\n"
"    border-radius: 5px;\n"
"    padding: 5px;\n"
"    font-size: 14px;\n"
"    color: #ffffff;\n"
"}\n"
""));
        tab_7 = new QWidget();
        tab_7->setObjectName(QString::fromUtf8("tab_7"));
        groupBox_4 = new QGroupBox(tab_7);
        groupBox_4->setObjectName(QString::fromUtf8("groupBox_4"));
        groupBox_4->setGeometry(QRect(0, 20, 311, 201));
        label_24 = new QLabel(groupBox_4);
        label_24->setObjectName(QString::fromUtf8("label_24"));
        label_24->setGeometry(QRect(10, 30, 91, 31));
        label_26 = new QLabel(groupBox_4);
        label_26->setObjectName(QString::fromUtf8("label_26"));
        label_26->setGeometry(QRect(10, 80, 91, 16));
        label_27 = new QLabel(groupBox_4);
        label_27->setObjectName(QString::fromUtf8("label_27"));
        label_27->setGeometry(QRect(10, 120, 81, 31));
        adresseLineEdit_2 = new QLineEdit(groupBox_4);
        adresseLineEdit_2->setObjectName(QString::fromUtf8("adresseLineEdit_2"));
        adresseLineEdit_2->setGeometry(QRect(130, 20, 131, 41));
        idLineEdit_2 = new QLineEdit(groupBox_4);
        idLineEdit_2->setObjectName(QString::fromUtf8("idLineEdit_2"));
        idLineEdit_2->setGeometry(QRect(130, 130, 131, 31));
        typeLineEdit_2 = new QLineEdit(groupBox_4);
        typeLineEdit_2->setObjectName(QString::fromUtf8("typeLineEdit_2"));
        typeLineEdit_2->setGeometry(QRect(130, 80, 131, 31));
        ajouterButton_2 = new QPushButton(tab_7);
        ajouterButton_2->setObjectName(QString::fromUtf8("ajouterButton_2"));
        ajouterButton_2->setGeometry(QRect(10, 240, 121, 41));
        tableView_2 = new QTableView(tab_7);
        tableView_2->setObjectName(QString::fromUtf8("tableView_2"));
        tableView_2->setGeometry(QRect(290, 30, 621, 291));
        pushbutton_2 = new QPushButton(tab_7);
        pushbutton_2->setObjectName(QString::fromUtf8("pushbutton_2"));
        pushbutton_2->setGeometry(QRect(950, 90, 131, 41));
        pushButton_8 = new QPushButton(tab_7);
        pushButton_8->setObjectName(QString::fromUtf8("pushButton_8"));
        pushButton_8->setGeometry(QRect(150, 240, 101, 41));
        pushButton_9 = new QPushButton(tab_7);
        pushButton_9->setObjectName(QString::fromUtf8("pushButton_9"));
        pushButton_9->setGeometry(QRect(20, 360, 131, 41));
        lineEdit_3 = new QLineEdit(tab_7);
        lineEdit_3->setObjectName(QString::fromUtf8("lineEdit_3"));
        lineEdit_3->setGeometry(QRect(110, 310, 113, 28));
        label_28 = new QLabel(tab_7);
        label_28->setObjectName(QString::fromUtf8("label_28"));
        label_28->setGeometry(QRect(30, 320, 63, 20));
        pushButton_10 = new QPushButton(tab_7);
        pushButton_10->setObjectName(QString::fromUtf8("pushButton_10"));
        pushButton_10->setGeometry(QRect(180, 360, 131, 41));
        pushButton_11 = new QPushButton(tab_7);
        pushButton_11->setObjectName(QString::fromUtf8("pushButton_11"));
        pushButton_11->setGeometry(QRect(950, 150, 131, 41));
        pushButton_12 = new QPushButton(tab_7);
        pushButton_12->setObjectName(QString::fromUtf8("pushButton_12"));
        pushButton_12->setGeometry(QRect(950, 40, 131, 41));
        radioButton_5 = new QRadioButton(tab_7);
        radioButton_5->setObjectName(QString::fromUtf8("radioButton_5"));
        radioButton_5->setGeometry(QRect(10, 490, 151, 26));
        radioButton_6 = new QRadioButton(tab_7);
        radioButton_6->setObjectName(QString::fromUtf8("radioButton_6"));
        radioButton_6->setGeometry(QRect(10, 540, 151, 26));
        pushButton_13 = new QPushButton(tab_7);
        pushButton_13->setObjectName(QString::fromUtf8("pushButton_13"));
        pushButton_13->setGeometry(QRect(170, 510, 101, 51));
        radioButton_7 = new QRadioButton(tab_7);
        radioButton_7->setObjectName(QString::fromUtf8("radioButton_7"));
        radioButton_7->setGeometry(QRect(300, 490, 181, 26));
        radioButton_8 = new QRadioButton(tab_7);
        radioButton_8->setObjectName(QString::fromUtf8("radioButton_8"));
        radioButton_8->setGeometry(QRect(300, 540, 191, 26));
        pushButton_14 = new QPushButton(tab_7);
        pushButton_14->setObjectName(QString::fromUtf8("pushButton_14"));
        pushButton_14->setGeometry(QRect(760, 370, 151, 41));
        lineEdit_4 = new QLineEdit(tab_7);
        lineEdit_4->setObjectName(QString::fromUtf8("lineEdit_4"));
        lineEdit_4->setGeometry(QRect(940, 370, 161, 41));
        textEdit_4 = new QTextEdit(tab_7);
        textEdit_4->setObjectName(QString::fromUtf8("textEdit_4"));
        textEdit_4->setGeometry(QRect(680, 440, 231, 141));
        textEdit_5 = new QTextEdit(tab_7);
        textEdit_5->setObjectName(QString::fromUtf8("textEdit_5"));
        textEdit_5->setGeometry(QRect(920, 440, 191, 101));
        flameStatusLabel_2 = new QLabel(tab_7);
        flameStatusLabel_2->setObjectName(QString::fromUtf8("flameStatusLabel_2"));
        flameStatusLabel_2->setGeometry(QRect(320, 10, 541, 331));
        flameStatusLabel_2->setStyleSheet(QString::fromUtf8("background-color:none;"));
        checkButton_2 = new QPushButton(tab_7);
        checkButton_2->setObjectName(QString::fromUtf8("checkButton_2"));
        checkButton_2->setGeometry(QRect(980, 220, 141, 51));
        tabWidget->addTab(tab_7, QString());
        gs_table->addTab(tab, QString());
        MainWindow->setCentralWidget(centralwidget);
        menubar = new QMenuBar(MainWindow);
        menubar->setObjectName(QString::fromUtf8("menubar"));
        menubar->setGeometry(QRect(0, 0, 1560, 25));
        menucleanzy = new QMenu(menubar);
        menucleanzy->setObjectName(QString::fromUtf8("menucleanzy"));
        MainWindow->setMenuBar(menubar);
        statusbar = new QStatusBar(MainWindow);
        statusbar->setObjectName(QString::fromUtf8("statusbar"));
        MainWindow->setStatusBar(statusbar);

        menubar->addAction(menucleanzy->menuAction());

        retranslateUi(MainWindow);

        gs_table->setCurrentIndex(2);
        lineEdit_date_2->setCurrentIndex(0);
        tabWidget_3->setCurrentIndex(2);
        tabWidget->setCurrentIndex(0);


        QMetaObject::connectSlotsByName(MainWindow);
    } // setupUi

    void retranslateUi(QMainWindow *MainWindow)
    {
        MainWindow->setWindowTitle(QCoreApplication::translate("MainWindow", "MainWindow", nullptr));
        groupBox_3->setTitle(QCoreApplication::translate("MainWindow", "New client", nullptr));
        label_14->setText(QCoreApplication::translate("MainWindow", "CIN:", nullptr));
        label_9->setText(QCoreApplication::translate("MainWindow", "nom:", nullptr));
        label_10->setText(QCoreApplication::translate("MainWindow", "prenom:", nullptr));
        label_11->setText(QCoreApplication::translate("MainWindow", "telephone:", nullptr));
        label_13->setText(QCoreApplication::translate("MainWindow", "sexe:", nullptr));
        client_comboBoxSexe->setItemText(0, QCoreApplication::translate("MainWindow", "male", nullptr));
        client_comboBoxSexe->setItemText(1, QCoreApplication::translate("MainWindow", "female", nullptr));
        client_comboBoxSexe->setItemText(2, QString());

        label_15->setText(QCoreApplication::translate("MainWindow", "reservation date:", nullptr));
        client_tri_btn_->setText(QCoreApplication::translate("MainWindow", "tri", nullptr));
        label->setText(QString());
        client_TriBox->setItemText(0, QCoreApplication::translate("MainWindow", "nom_ascendant", nullptr));
        client_TriBox->setItemText(1, QCoreApplication::translate("MainWindow", "nom_descendant", nullptr));
        client_TriBox->setItemText(2, QCoreApplication::translate("MainWindow", "id_ascendant", nullptr));
        client_TriBox->setItemText(3, QCoreApplication::translate("MainWindow", "id_descendant", nullptr));
        client_TriBox->setItemText(4, QCoreApplication::translate("MainWindow", "sexe_ascendant", nullptr));
        client_TriBox->setItemText(5, QCoreApplication::translate("MainWindow", "sexe_descendant", nullptr));

        client_exportPdf_btn->setText(QCoreApplication::translate("MainWindow", "export ", nullptr));
        client_update_btn->setText(QCoreApplication::translate("MainWindow", "update ", nullptr));
        client_delete_btn->setText(QCoreApplication::translate("MainWindow", "delete", nullptr));
        client_add_btn->setText(QCoreApplication::translate("MainWindow", "Add ", nullptr));
        client_searchBox->setItemText(0, QCoreApplication::translate("MainWindow", "Nom", nullptr));
        client_searchBox->setItemText(1, QCoreApplication::translate("MainWindow", "Pr\303\251nom", nullptr));
        client_searchBox->setItemText(2, QCoreApplication::translate("MainWindow", "ID", nullptr));

        lineEdit_date_2->setTabText(lineEdit_date_2->indexOf(tab_5), QCoreApplication::translate("MainWindow", "work", nullptr));
        client_sms_Btn->setText(QCoreApplication::translate("MainWindow", "send", nullptr));
        label_2->setText(QCoreApplication::translate("MainWindow", "enter ID :", nullptr));
        lineEdit_date_2->setTabText(lineEdit_date_2->indexOf(tab_8), QCoreApplication::translate("MainWindow", "sms", nullptr));
        lineEdit_date_2->setTabText(lineEdit_date_2->indexOf(tab_6), QCoreApplication::translate("MainWindow", "calendar", nullptr));
        gs_table->setTabText(gs_table->indexOf(tab_3), QCoreApplication::translate("MainWindow", "clients", nullptr));
        groupBox_2->setTitle(QCoreApplication::translate("MainWindow", "New equipement", nullptr));
        label_5->setText(QCoreApplication::translate("MainWindow", "Reference:", nullptr));
        label_4->setText(QCoreApplication::translate("MainWindow", "Nom:", nullptr));
        label_6->setText(QCoreApplication::translate("MainWindow", "Quantite:", nullptr));
        label_12->setText(QCoreApplication::translate("MainWindow", "Prix:", nullptr));
        label_16->setText(QCoreApplication::translate("MainWindow", "Marque:", nullptr));
        label_7->setText(QCoreApplication::translate("MainWindow", "Etat:", nullptr));
        comboBoxEtat->setItemText(0, QCoreApplication::translate("MainWindow", "En service", nullptr));
        comboBoxEtat->setItemText(1, QCoreApplication::translate("MainWindow", "Disponible", nullptr));
        comboBoxEtat->setItemText(2, QCoreApplication::translate("MainWindow", "En panne", nullptr));

        label_8->setText(QCoreApplication::translate("MainWindow", "Type:", nullptr));
        comboBoxType->setItemText(0, QCoreApplication::translate("MainWindow", "Machine", nullptr));
        comboBoxType->setItemText(1, QCoreApplication::translate("MainWindow", "Materiel", nullptr));

        equip_add_btn->setText(QCoreApplication::translate("MainWindow", "Add ", nullptr));
        equip_update_btn->setText(QCoreApplication::translate("MainWindow", "update ", nullptr));
        equip_delete_btn->setText(QCoreApplication::translate("MainWindow", "delete", nullptr));
        tri_btn->setText(QCoreApplication::translate("MainWindow", "Filtre", nullptr));
        search_btn->setText(QCoreApplication::translate("MainWindow", "search", nullptr));
        label_17->setText(QString());
        comboBoxTri1->setItemText(0, QCoreApplication::translate("MainWindow", "Prix", nullptr));
        comboBoxTri1->setItemText(1, QCoreApplication::translate("MainWindow", "Marque", nullptr));
        comboBoxTri1->setItemText(2, QCoreApplication::translate("MainWindow", "Nom", nullptr));

        label_19->setText(QString());
        comboBoxSearch->setItemText(0, QCoreApplication::translate("MainWindow", "Reference", nullptr));
        comboBoxSearch->setItemText(1, QCoreApplication::translate("MainWindow", "Marque", nullptr));
        comboBoxSearch->setItemText(2, QCoreApplication::translate("MainWindow", "Nom", nullptr));

        comboBoxTri2->setItemText(0, QCoreApplication::translate("MainWindow", "Ascendant", nullptr));
        comboBoxTri2->setItemText(1, QCoreApplication::translate("MainWindow", "Desccendant", nullptr));

        equip_exportPdf_btn->setText(QCoreApplication::translate("MainWindow", "export ", nullptr));
        tabWidget_3->setTabText(tabWidget_3->indexOf(tab1), QCoreApplication::translate("MainWindow", "work", nullptr));
        qr_code->setText(QString());
        textEdit->setHtml(QCoreApplication::translate("MainWindow", "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.0//EN\" \"http://www.w3.org/TR/REC-html40/strict.dtd\">\n"
"<html><head><meta name=\"qrichtext\" content=\"1\" /><meta charset=\"utf-8\" /><style type=\"text/css\">\n"
"p, li { white-space: pre-wrap; }\n"
"hr { height: 1px; border-width: 0; }\n"
"li.unchecked::marker { content: \"\\2610\"; }\n"
"li.checked::marker { content: \"\\2612\"; }\n"
"</style></head><body style=\" font-family:'Segoe UI'; font-size:14px; font-weight:400; font-style:normal;\">\n"
"<p align=\"center\" style=\" margin-top:0px; margin-bottom:0px; margin-left:0px; margin-right:0px; -qt-block-indent:0; text-indent:0px;\"><span style=\" font-family:'MS Shell Dlg 2'; font-size:11pt; font-weight:600; color:#03ace3;\">Scanner pour plus d'information</span></p></body></html>", nullptr));
        generateQRCodeButton->setText(QCoreApplication::translate("MainWindow", "code", nullptr));
        label_25->setText(QCoreApplication::translate("MainWindow", "Enter the reference : ", nullptr));
        tabWidget_3->setTabText(tabWidget_3->indexOf(tab_21), QCoreApplication::translate("MainWindow", "scan", nullptr));
        label_20->setText(QCoreApplication::translate("MainWindow", "Equipement's reference:", nullptr));
        email_Btn->setText(QCoreApplication::translate("MainWindow", "Send", nullptr));
        checkBoxGuide->setText(QCoreApplication::translate("MainWindow", "Guide d'utilisation", nullptr));
        checkBoxFormulaire->setText(QCoreApplication::translate("MainWindow", "Formulaire de feedback", nullptr));
        label_21->setText(QCoreApplication::translate("MainWindow", "Enter your email:", nullptr));
        tabWidget_3->setTabText(tabWidget_3->indexOf(tab_4), QCoreApplication::translate("MainWindow", "mail", nullptr));
        gs_table->setTabText(gs_table->indexOf(equipement), QCoreApplication::translate("MainWindow", "equipement", nullptr));
        groupBox_4->setTitle(QCoreApplication::translate("MainWindow", "Ajouter Client", nullptr));
        label_24->setText(QCoreApplication::translate("MainWindow", "Adresse :", nullptr));
        label_26->setText(QCoreApplication::translate("MainWindow", "type :", nullptr));
        label_27->setText(QCoreApplication::translate("MainWindow", "id :", nullptr));
        ajouterButton_2->setText(QCoreApplication::translate("MainWindow", "Ajouter", nullptr));
        pushbutton_2->setText(QCoreApplication::translate("MainWindow", "afficher", nullptr));
        pushButton_8->setText(QCoreApplication::translate("MainWindow", "modifier", nullptr));
        pushButton_9->setText(QCoreApplication::translate("MainWindow", "supprimer", nullptr));
        label_28->setText(QCoreApplication::translate("MainWindow", "ID :", nullptr));
        pushButton_10->setText(QCoreApplication::translate("MainWindow", "recherche", nullptr));
        pushButton_11->setText(QCoreApplication::translate("MainWindow", "PDF", nullptr));
        pushButton_12->setText(QCoreApplication::translate("MainWindow", "statistique", nullptr));
        radioButton_5->setText(QCoreApplication::translate("MainWindow", "trier id decroissant", nullptr));
        radioButton_6->setText(QCoreApplication::translate("MainWindow", "trier id croissant ", nullptr));
        pushButton_13->setText(QCoreApplication::translate("MainWindow", "trie", nullptr));
        radioButton_7->setText(QCoreApplication::translate("MainWindow", "trier adresse croissante", nullptr));
        radioButton_8->setText(QCoreApplication::translate("MainWindow", "trier adresse droissante", nullptr));
        pushButton_14->setText(QCoreApplication::translate("MainWindow", "chatbot", nullptr));
        flameStatusLabel_2->setText(QString());
        checkButton_2->setText(QCoreApplication::translate("MainWindow", "alarme", nullptr));
        tabWidget->setTabText(tabWidget->indexOf(tab_7), QCoreApplication::translate("MainWindow", "Ajouter", nullptr));
        gs_table->setTabText(gs_table->indexOf(tab), QCoreApplication::translate("MainWindow", "local", nullptr));
        menucleanzy->setTitle(QCoreApplication::translate("MainWindow", "cleanzy", nullptr));
    } // retranslateUi

};

namespace Ui {
    class MainWindow: public Ui_MainWindow {};
} // namespace Ui

QT_END_NAMESPACE

#endif // UI_MAINWINDOW_H
