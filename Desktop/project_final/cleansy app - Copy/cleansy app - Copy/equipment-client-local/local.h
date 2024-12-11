#ifndef LOCAL_H
#define LOCAL_H

#include <QString>
#include <QSqlQueryModel>

class Local {
public:
    Local();
    Local(int ID, const QString& ADRESSE, const QString& TYPE);

    bool ajouterLocal();
    QSqlQueryModel* afficherLocal();
    bool modifierLocal();
    bool supprimerLocal(int ID);
    bool recherche(int ID);
    QSqlQueryModel* tri_par_id_croissant();
    QSqlQueryModel* tri_par_id_decroissant();
    QSqlQueryModel* tri_par_adresse_croissant();
    QSqlQueryModel* tri_par_adresse_decroissant();
private:
    int ID;
    QString ADRESSE;
    QString TYPE;
};

#endif // LOCAL_H
