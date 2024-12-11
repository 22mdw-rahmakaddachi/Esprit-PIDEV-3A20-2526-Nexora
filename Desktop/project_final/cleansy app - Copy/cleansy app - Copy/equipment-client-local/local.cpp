#include "local.h"
#include <QSqlQuery>
#include <QSqlQueryModel>
#include <QSqlError>
#include <QDebug>

Local::Local() {}

Local::Local(int ID, const QString& ADRESSE, const QString& TYPE)
    : ID(ID), ADRESSE(ADRESSE), TYPE(TYPE) {}

bool Local::ajouterLocal() {
    // Vérifier si la base de données est ouverte
    if (!QSqlDatabase::database().isOpen()) {
        qDebug() << "Erreur : La base de données n'est pas ouverte.";
        return false;
    }

    // Préparer la requête SQL
    QSqlQuery query;
    query.prepare("INSERT INTO LOCAL (ID, ADRESSE, TYPE) VALUES (:id, :adresse, :type)");
    query.bindValue(":id", ID);
    query.bindValue(":adresse", ADRESSE.trimmed());
    query.bindValue(":type", TYPE.trimmed());

    // Afficher les valeurs pour diagnostic
    qDebug() << "Valeurs à insérer : ID =" << ID << ", ADRESSE =" << ADRESSE << ", TYPE =" << TYPE;

    // Exécuter la requête
    if (!query.exec()) {
        qDebug() << "Erreur lors de l'ajout :" << query.lastError().text();
        return false;
    }

    return true;
}

QSqlQueryModel* Local::afficherLocal() {
    QSqlQueryModel *model = new QSqlQueryModel();

    // Sélectionner toutes les lignes de la table "local"
    model->setQuery("SELECT ID, ADRESSE, TYPE FROM local");  // Assurez-vous que le nom de la table est correct

    // Ajouter des en-têtes de colonnes pour le tableau
    model->setHeaderData(0, Qt::Horizontal, QObject::tr("ID"));
    model->setHeaderData(1, Qt::Horizontal, QObject::tr("Adresse"));
    model->setHeaderData(2, Qt::Horizontal, QObject::tr("Type"));

    return model;  // Retourner le modèle mis à jour
}
bool Local::modifierLocal() {
    QSqlQuery query;
    query.prepare("UPDATE local SET ADRESSE = :adresse, TYPE = :type WHERE ID = :id");
    query.bindValue(":id", ID);
    query.bindValue(":adresse", ADRESSE);
    query.bindValue(":type", TYPE);

    if (!query.exec()) {
        qDebug() << "Erreur de mise à jour dans la base de données :" << query.lastError();
        return false;
    }
    return true;
}

bool Local::supprimerLocal(int id) {
    QSqlQuery query;
    query.prepare("DELETE FROM local WHERE ID = :id");
    query.bindValue(":id", id);
    return query.exec();
}

bool Local::recherche(int ID)
{
    QSqlQuery query;
    query.prepare("SELECT * FROM local WHERE ID = :id");
    query.bindValue(":id", ID);

    if (query.exec() && query.next()) {
        return true;
    } else {
        qDebug() << "Error while searching for patient by ID:" << query.lastError().text();
        return false;
    }
}
QSqlQueryModel* Local::tri_par_id_croissant()
{
    QSqlQueryModel *model = new QSqlQueryModel();
    model->setQuery("SELECT * FROM local ORDER BY ID ASC");

    if (model->lastError().isValid()) {
        qDebug() << "Erreur dans tri_par_id_croissant:" << model->lastError().text();
    }

    return model;
}

// Trier les patients par ID décroissant
QSqlQueryModel* Local::tri_par_id_decroissant()
{
    QSqlQueryModel *model = new QSqlQueryModel();
    model->setQuery("SELECT * FROM local ORDER BY ID DESC");

    if (model->lastError().isValid()) {
        qDebug() << "Erreur dans tri_par_id_decroissant:" << model->lastError().text();
    }

    return model;
}

// Trier les patients par NOM croissant
QSqlQueryModel* Local::tri_par_adresse_croissant()
{
    QSqlQueryModel *model = new QSqlQueryModel();
    model->setQuery("SELECT * FROM local ORDER BY ADRESSE ASC, ID ASC");

    if (model->lastError().isValid()) {
        qDebug() << "Erreur dans tri_par_nom_croissant:" << model->lastError().text();
    }

    return model;
}

// Trier les patients par NOM décroissant
QSqlQueryModel* Local::tri_par_adresse_decroissant()
{
    QSqlQueryModel *model = new QSqlQueryModel();
    model->setQuery("SELECT * FROM local ORDER BY ADRESSE DESC, ID ASC");

    if (model->lastError().isValid()) {
        qDebug() << "Erreur dans tri_par_nom_decroissant:" << model->lastError().text();
    }

    return model;
}

