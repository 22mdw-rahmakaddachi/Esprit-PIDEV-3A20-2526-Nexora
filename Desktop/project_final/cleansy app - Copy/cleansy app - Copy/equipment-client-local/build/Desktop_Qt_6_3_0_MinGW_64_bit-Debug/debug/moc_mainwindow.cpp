/****************************************************************************
** Meta object code from reading C++ file 'mainwindow.h'
**
** Created by: The Qt Meta Object Compiler version 68 (Qt 6.3.0)
**
** WARNING! All changes made in this file will be lost!
*****************************************************************************/

#include <memory>
#include "../../../mainwindow.h"
#include <QtGui/qtextcursor.h>
#include <QScreen>
#include <QtCharts/qlineseries.h>
#include <QtCharts/qabstractbarseries.h>
#include <QtCharts/qvbarmodelmapper.h>
#include <QtCharts/qboxplotseries.h>
#include <QtCharts/qcandlestickseries.h>
#include <QtCore/qabstractitemmodel.h>
#include <QtCharts/qpieseries.h>
#include <QtCore/qabstractitemmodel.h>
#include <QtCharts/qboxplotseries.h>
#include <QtCore/qabstractitemmodel.h>
#include <QtCharts/qpieseries.h>
#include <QtCharts/qpieseries.h>
#include <QtCore/qabstractitemmodel.h>
#include <QtCharts/qxyseries.h>
#include <QtCharts/qxyseries.h>
#include <QtCore/qabstractitemmodel.h>
#include <QtCore/qabstractitemmodel.h>
#include <QtCharts/qboxplotseries.h>
#include <QtCore/qabstractitemmodel.h>
#include <QtCharts/qpieseries.h>
#include <QtCore/qabstractitemmodel.h>
#include <QtCharts/qxyseries.h>
#include <QtCore/qabstractitemmodel.h>
#include <QtCore/qbytearray.h>
#include <QtCore/qmetatype.h>
#include <QtCore/QList>
#if !defined(Q_MOC_OUTPUT_REVISION)
#error "The header file 'mainwindow.h' doesn't include <QObject>."
#elif Q_MOC_OUTPUT_REVISION != 68
#error "This file was generated using the moc from 6.3.0. It"
#error "cannot be used with the include files from this version of Qt."
#error "(The moc has changed too much.)"
#endif

QT_BEGIN_MOC_NAMESPACE
QT_WARNING_PUSH
QT_WARNING_DISABLE_DEPRECATED
struct qt_meta_stringdata_MainWindow_t {
    const uint offsetsAndSize[84];
    char stringdata0[989];
};
#define QT_MOC_LITERAL(ofs, len) \
    uint(offsetof(qt_meta_stringdata_MainWindow_t, stringdata0) + ofs), len 
static const qt_meta_stringdata_MainWindow_t qt_meta_stringdata_MainWindow = {
    {
QT_MOC_LITERAL(0, 10), // "MainWindow"
QT_MOC_LITERAL(11, 25), // "on_client_add_btn_clicked"
QT_MOC_LITERAL(37, 0), // ""
QT_MOC_LITERAL(38, 28), // "on_client_update_btn_clicked"
QT_MOC_LITERAL(67, 28), // "on_client_delete_btn_clicked"
QT_MOC_LITERAL(96, 26), // "on_client_tri_btn__clicked"
QT_MOC_LITERAL(123, 31), // "on_client_exportPdf_btn_clicked"
QT_MOC_LITERAL(155, 27), // "on_client_tableView_clicked"
QT_MOC_LITERAL(183, 11), // "QModelIndex"
QT_MOC_LITERAL(195, 5), // "index"
QT_MOC_LITERAL(201, 25), // "on_client_sms_Btn_clicked"
QT_MOC_LITERAL(227, 35), // "on_lineEditSearchClient_textC..."
QT_MOC_LITERAL(263, 4), // "arg1"
QT_MOC_LITERAL(268, 42), // "on_clients_calendarWidget_sel..."
QT_MOC_LITERAL(311, 32), // "addDataToTableViewClientCalander"
QT_MOC_LITERAL(344, 28), // "QList<QMap<QString,QString>>"
QT_MOC_LITERAL(373, 8), // "dataList"
QT_MOC_LITERAL(382, 25), // "highlightReservationDates"
QT_MOC_LITERAL(408, 15), // "readFromArduino"
QT_MOC_LITERAL(424, 21), // "on_search_btn_clicked"
QT_MOC_LITERAL(446, 18), // "on_tri_btn_clicked"
QT_MOC_LITERAL(465, 30), // "on_tableViewEquipement_clicked"
QT_MOC_LITERAL(496, 38), // "on_lineEditSearchReference_te..."
QT_MOC_LITERAL(535, 4), // "text"
QT_MOC_LITERAL(540, 20), // "on_email_Btn_clicked"
QT_MOC_LITERAL(561, 31), // "on_generateQRCodeButton_clicked"
QT_MOC_LITERAL(593, 24), // "on_equip_add_btn_clicked"
QT_MOC_LITERAL(618, 27), // "on_equip_update_btn_clicked"
QT_MOC_LITERAL(646, 27), // "on_equip_delete_btn_clicked"
QT_MOC_LITERAL(674, 30), // "on_equip_exportPdf_btn_clicked"
QT_MOC_LITERAL(705, 26), // "on_ajouterButton_2_clicked"
QT_MOC_LITERAL(732, 23), // "on_pushButton_8_clicked"
QT_MOC_LITERAL(756, 23), // "on_pushButton_9_clicked"
QT_MOC_LITERAL(780, 24), // "on_pushButton_10_clicked"
QT_MOC_LITERAL(805, 24), // "on_pushButton_12_clicked"
QT_MOC_LITERAL(830, 24), // "on_pushButton_11_clicked"
QT_MOC_LITERAL(855, 24), // "on_pushButton_13_clicked"
QT_MOC_LITERAL(880, 24), // "on_pushButton_14_clicked"
QT_MOC_LITERAL(905, 23), // "on_pushbutton_2_clicked"
QT_MOC_LITERAL(929, 24), // "on_checkButton_2_clicked"
QT_MOC_LITERAL(954, 16), // "readFromArduino1"
QT_MOC_LITERAL(971, 17) // "updateFlameStatus"

    },
    "MainWindow\0on_client_add_btn_clicked\0"
    "\0on_client_update_btn_clicked\0"
    "on_client_delete_btn_clicked\0"
    "on_client_tri_btn__clicked\0"
    "on_client_exportPdf_btn_clicked\0"
    "on_client_tableView_clicked\0QModelIndex\0"
    "index\0on_client_sms_Btn_clicked\0"
    "on_lineEditSearchClient_textChanged\0"
    "arg1\0on_clients_calendarWidget_selectionChanged\0"
    "addDataToTableViewClientCalander\0"
    "QList<QMap<QString,QString>>\0dataList\0"
    "highlightReservationDates\0readFromArduino\0"
    "on_search_btn_clicked\0on_tri_btn_clicked\0"
    "on_tableViewEquipement_clicked\0"
    "on_lineEditSearchReference_textChanged\0"
    "text\0on_email_Btn_clicked\0"
    "on_generateQRCodeButton_clicked\0"
    "on_equip_add_btn_clicked\0"
    "on_equip_update_btn_clicked\0"
    "on_equip_delete_btn_clicked\0"
    "on_equip_exportPdf_btn_clicked\0"
    "on_ajouterButton_2_clicked\0"
    "on_pushButton_8_clicked\0on_pushButton_9_clicked\0"
    "on_pushButton_10_clicked\0"
    "on_pushButton_12_clicked\0"
    "on_pushButton_11_clicked\0"
    "on_pushButton_13_clicked\0"
    "on_pushButton_14_clicked\0"
    "on_pushbutton_2_clicked\0"
    "on_checkButton_2_clicked\0readFromArduino1\0"
    "updateFlameStatus"
};
#undef QT_MOC_LITERAL

static const uint qt_meta_data_MainWindow[] = {

 // content:
      10,       // revision
       0,       // classname
       0,    0, // classinfo
      34,   14, // methods
       0,    0, // properties
       0,    0, // enums/sets
       0,    0, // constructors
       0,       // flags
       0,       // signalCount

 // slots: name, argc, parameters, tag, flags, initial metatype offsets
       1,    0,  218,    2, 0x08,    1 /* Private */,
       3,    0,  219,    2, 0x08,    2 /* Private */,
       4,    0,  220,    2, 0x08,    3 /* Private */,
       5,    0,  221,    2, 0x08,    4 /* Private */,
       6,    0,  222,    2, 0x08,    5 /* Private */,
       7,    1,  223,    2, 0x08,    6 /* Private */,
      10,    0,  226,    2, 0x08,    8 /* Private */,
      11,    1,  227,    2, 0x08,    9 /* Private */,
      13,    0,  230,    2, 0x08,   11 /* Private */,
      14,    1,  231,    2, 0x08,   12 /* Private */,
      17,    0,  234,    2, 0x08,   14 /* Private */,
      18,    0,  235,    2, 0x08,   15 /* Private */,
      19,    0,  236,    2, 0x08,   16 /* Private */,
      20,    0,  237,    2, 0x08,   17 /* Private */,
      21,    1,  238,    2, 0x08,   18 /* Private */,
      22,    1,  241,    2, 0x08,   20 /* Private */,
      24,    0,  244,    2, 0x08,   22 /* Private */,
      25,    0,  245,    2, 0x08,   23 /* Private */,
      26,    0,  246,    2, 0x08,   24 /* Private */,
      27,    0,  247,    2, 0x08,   25 /* Private */,
      28,    0,  248,    2, 0x08,   26 /* Private */,
      29,    0,  249,    2, 0x08,   27 /* Private */,
      30,    0,  250,    2, 0x08,   28 /* Private */,
      31,    0,  251,    2, 0x08,   29 /* Private */,
      32,    0,  252,    2, 0x08,   30 /* Private */,
      33,    0,  253,    2, 0x08,   31 /* Private */,
      34,    0,  254,    2, 0x08,   32 /* Private */,
      35,    0,  255,    2, 0x08,   33 /* Private */,
      36,    0,  256,    2, 0x08,   34 /* Private */,
      37,    0,  257,    2, 0x08,   35 /* Private */,
      38,    0,  258,    2, 0x08,   36 /* Private */,
      39,    0,  259,    2, 0x08,   37 /* Private */,
      40,    0,  260,    2, 0x08,   38 /* Private */,
      41,    0,  261,    2, 0x08,   39 /* Private */,

 // slots: parameters
    QMetaType::Void,
    QMetaType::Void,
    QMetaType::Void,
    QMetaType::Void,
    QMetaType::Void,
    QMetaType::Void, 0x80000000 | 8,    9,
    QMetaType::Void,
    QMetaType::Void, QMetaType::QString,   12,
    QMetaType::Void,
    QMetaType::Void, 0x80000000 | 15,   16,
    QMetaType::Void,
    QMetaType::Void,
    QMetaType::Void,
    QMetaType::Void,
    QMetaType::Void, 0x80000000 | 8,    9,
    QMetaType::Void, QMetaType::QString,   23,
    QMetaType::Void,
    QMetaType::Void,
    QMetaType::Void,
    QMetaType::Void,
    QMetaType::Void,
    QMetaType::Void,
    QMetaType::Void,
    QMetaType::Void,
    QMetaType::Void,
    QMetaType::Void,
    QMetaType::Void,
    QMetaType::Void,
    QMetaType::Void,
    QMetaType::Void,
    QMetaType::Void,
    QMetaType::Void,
    QMetaType::Void,
    QMetaType::Void,

       0        // eod
};

void MainWindow::qt_static_metacall(QObject *_o, QMetaObject::Call _c, int _id, void **_a)
{
    if (_c == QMetaObject::InvokeMetaMethod) {
        auto *_t = static_cast<MainWindow *>(_o);
        (void)_t;
        switch (_id) {
        case 0: _t->on_client_add_btn_clicked(); break;
        case 1: _t->on_client_update_btn_clicked(); break;
        case 2: _t->on_client_delete_btn_clicked(); break;
        case 3: _t->on_client_tri_btn__clicked(); break;
        case 4: _t->on_client_exportPdf_btn_clicked(); break;
        case 5: _t->on_client_tableView_clicked((*reinterpret_cast< std::add_pointer_t<QModelIndex>>(_a[1]))); break;
        case 6: _t->on_client_sms_Btn_clicked(); break;
        case 7: _t->on_lineEditSearchClient_textChanged((*reinterpret_cast< std::add_pointer_t<QString>>(_a[1]))); break;
        case 8: _t->on_clients_calendarWidget_selectionChanged(); break;
        case 9: _t->addDataToTableViewClientCalander((*reinterpret_cast< std::add_pointer_t<QList<QMap<QString,QString>>>>(_a[1]))); break;
        case 10: _t->highlightReservationDates(); break;
        case 11: _t->readFromArduino(); break;
        case 12: _t->on_search_btn_clicked(); break;
        case 13: _t->on_tri_btn_clicked(); break;
        case 14: _t->on_tableViewEquipement_clicked((*reinterpret_cast< std::add_pointer_t<QModelIndex>>(_a[1]))); break;
        case 15: _t->on_lineEditSearchReference_textChanged((*reinterpret_cast< std::add_pointer_t<QString>>(_a[1]))); break;
        case 16: _t->on_email_Btn_clicked(); break;
        case 17: _t->on_generateQRCodeButton_clicked(); break;
        case 18: _t->on_equip_add_btn_clicked(); break;
        case 19: _t->on_equip_update_btn_clicked(); break;
        case 20: _t->on_equip_delete_btn_clicked(); break;
        case 21: _t->on_equip_exportPdf_btn_clicked(); break;
        case 22: _t->on_ajouterButton_2_clicked(); break;
        case 23: _t->on_pushButton_8_clicked(); break;
        case 24: _t->on_pushButton_9_clicked(); break;
        case 25: _t->on_pushButton_10_clicked(); break;
        case 26: _t->on_pushButton_12_clicked(); break;
        case 27: _t->on_pushButton_11_clicked(); break;
        case 28: _t->on_pushButton_13_clicked(); break;
        case 29: _t->on_pushButton_14_clicked(); break;
        case 30: _t->on_pushbutton_2_clicked(); break;
        case 31: _t->on_checkButton_2_clicked(); break;
        case 32: _t->readFromArduino1(); break;
        case 33: _t->updateFlameStatus(); break;
        default: ;
        }
    }
}

const QMetaObject MainWindow::staticMetaObject = { {
    QMetaObject::SuperData::link<QMainWindow::staticMetaObject>(),
    qt_meta_stringdata_MainWindow.offsetsAndSize,
    qt_meta_data_MainWindow,
    qt_static_metacall,
    nullptr,
qt_incomplete_metaTypeArray<qt_meta_stringdata_MainWindow_t
, QtPrivate::TypeAndForceComplete<MainWindow, std::true_type>
, QtPrivate::TypeAndForceComplete<void, std::false_type>, QtPrivate::TypeAndForceComplete<void, std::false_type>, QtPrivate::TypeAndForceComplete<void, std::false_type>, QtPrivate::TypeAndForceComplete<void, std::false_type>, QtPrivate::TypeAndForceComplete<void, std::false_type>, QtPrivate::TypeAndForceComplete<void, std::false_type>, QtPrivate::TypeAndForceComplete<const QModelIndex &, std::false_type>, QtPrivate::TypeAndForceComplete<void, std::false_type>, QtPrivate::TypeAndForceComplete<void, std::false_type>, QtPrivate::TypeAndForceComplete<const QString &, std::false_type>, QtPrivate::TypeAndForceComplete<void, std::false_type>, QtPrivate::TypeAndForceComplete<void, std::false_type>, QtPrivate::TypeAndForceComplete<const QList<QMap<QString,QString>> &, std::false_type>, QtPrivate::TypeAndForceComplete<void, std::false_type>, QtPrivate::TypeAndForceComplete<void, std::false_type>, QtPrivate::TypeAndForceComplete<void, std::false_type>, QtPrivate::TypeAndForceComplete<void, std::false_type>, QtPrivate::TypeAndForceComplete<void, std::false_type>, QtPrivate::TypeAndForceComplete<const QModelIndex &, std::false_type>, QtPrivate::TypeAndForceComplete<void, std::false_type>, QtPrivate::TypeAndForceComplete<const QString &, std::false_type>, QtPrivate::TypeAndForceComplete<void, std::false_type>, QtPrivate::TypeAndForceComplete<void, std::false_type>, QtPrivate::TypeAndForceComplete<void, std::false_type>, QtPrivate::TypeAndForceComplete<void, std::false_type>, QtPrivate::TypeAndForceComplete<void, std::false_type>, QtPrivate::TypeAndForceComplete<void, std::false_type>, QtPrivate::TypeAndForceComplete<void, std::false_type>, QtPrivate::TypeAndForceComplete<void, std::false_type>, QtPrivate::TypeAndForceComplete<void, std::false_type>, QtPrivate::TypeAndForceComplete<void, std::false_type>, QtPrivate::TypeAndForceComplete<void, std::false_type>, QtPrivate::TypeAndForceComplete<void, std::false_type>, QtPrivate::TypeAndForceComplete<void, std::false_type>, QtPrivate::TypeAndForceComplete<void, std::false_type>, QtPrivate::TypeAndForceComplete<void, std::false_type>, QtPrivate::TypeAndForceComplete<void, std::false_type>, QtPrivate::TypeAndForceComplete<void, std::false_type>, QtPrivate::TypeAndForceComplete<void, std::false_type>


>,
    nullptr
} };


const QMetaObject *MainWindow::metaObject() const
{
    return QObject::d_ptr->metaObject ? QObject::d_ptr->dynamicMetaObject() : &staticMetaObject;
}

void *MainWindow::qt_metacast(const char *_clname)
{
    if (!_clname) return nullptr;
    if (!strcmp(_clname, qt_meta_stringdata_MainWindow.stringdata0))
        return static_cast<void*>(this);
    return QMainWindow::qt_metacast(_clname);
}

int MainWindow::qt_metacall(QMetaObject::Call _c, int _id, void **_a)
{
    _id = QMainWindow::qt_metacall(_c, _id, _a);
    if (_id < 0)
        return _id;
    if (_c == QMetaObject::InvokeMetaMethod) {
        if (_id < 34)
            qt_static_metacall(this, _c, _id, _a);
        _id -= 34;
    } else if (_c == QMetaObject::RegisterMethodArgumentMetaType) {
        if (_id < 34)
            *reinterpret_cast<QMetaType *>(_a[0]) = QMetaType();
        _id -= 34;
    }
    return _id;
}
QT_WARNING_POP
QT_END_MOC_NAMESPACE
