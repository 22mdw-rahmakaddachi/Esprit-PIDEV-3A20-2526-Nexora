-- Script pour créer un partenaire par défaut si aucun n'existe
-- À exécuter si vous obtenez l'erreur de contrainte de clé étrangère

-- Vérifier s'il existe des partenaires
SELECT COUNT(*) as nb_partenaires FROM partenaire;

-- Si aucun partenaire n'existe, créer un partenaire par défaut
INSERT INTO partenaire (user_id, nom_entreprise, responsable_nom, statut) 
SELECT 1, 'Partenaire Défaut', 'Gestionnaire Système', 'actif'
WHERE NOT EXISTS (SELECT 1 FROM partenaire LIMIT 1);

-- Vérifier que le partenaire a été créé
SELECT * FROM partenaire;

-- Alternative : Si vous voulez associer un partenaire à un utilisateur existant
-- Remplacez USER_ID par l'ID d'un utilisateur existant avec le rôle 'partenaire'
/*
INSERT INTO partenaire (user_id, nom_entreprise, responsable_nom, statut) 
VALUES (34, 'Entreprise Anoire', 'Anoire Douiri', 'actif');
*/