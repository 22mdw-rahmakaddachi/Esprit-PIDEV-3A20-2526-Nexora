-- ============================================
-- CORRECTION CONTRAINTE PARTENAIRE-ACTIVITÉ
-- Résoudre l'erreur de clé étrangère fk_activite_partenaire
-- ============================================

-- 1. Vérifier les partenaires existants
SELECT 'Partenaires existants:' as info;
SELECT id, nom, email FROM partenaire ORDER BY id;

-- 2. Créer un partenaire par défaut si aucun n'existe
INSERT IGNORE INTO partenaire (id, nom, email, telephone, adresse, date_creation) 
VALUES (1, 'Partenaire Par Défaut', 'partenaire@nexora.com', '00000000', 'Adresse par défaut', NOW());

-- 3. Vérifier que le partenaire ID 1 existe maintenant
SELECT 'Vérification partenaire ID 1:' as info;
SELECT * FROM partenaire WHERE id = 1;

-- 4. Alternative: Créer des partenaires basés sur les utilisateurs existants
-- (Décommentez si vous voulez créer des partenaires à partir des utilisateurs)
/*
INSERT IGNORE INTO partenaire (nom, email, telephone, adresse, date_creation)
SELECT 
    CONCAT(prenom, ' ', name) as nom,
    email,
    COALESCE(telephone, '00000000') as telephone,
    COALESCE(adresse, 'Adresse non spécifiée') as adresse,
    NOW() as date_creation
FROM user 
WHERE role = 'PARTENAIRE' OR role = 'partenaire'
ON DUPLICATE KEY UPDATE nom = nom; -- Éviter les doublons
*/

-- 5. Vérifier les activités qui ont des partenaire_id invalides
SELECT 'Activités avec partenaire_id invalide:' as info;
SELECT a.id, a.nom, a.partenaire_id 
FROM activite a 
LEFT JOIN partenaire p ON a.partenaire_id = p.id 
WHERE p.id IS NULL;

-- 6. Corriger les activités avec partenaire_id invalide (les assigner au partenaire par défaut)
UPDATE activite 
SET partenaire_id = 1 
WHERE partenaire_id NOT IN (SELECT id FROM partenaire);

-- 7. Vérification finale
SELECT 'Vérification finale - Toutes les activités ont un partenaire valide:' as info;
SELECT COUNT(*) as activites_sans_partenaire_valide
FROM activite a 
LEFT JOIN partenaire p ON a.partenaire_id = p.id 
WHERE p.id IS NULL;

-- ============================================
-- NOTES
-- ============================================
-- Ce script :
-- 1. Crée un partenaire par défaut avec ID 1 si il n'existe pas
-- 2. Corrige toutes les activités qui référencent des partenaires inexistants
-- 3. Vérifie que toutes les contraintes sont respectées
-- 
-- Après avoir exécuté ce script, vous devriez pouvoir créer des activités
-- sans erreur de contrainte de clé étrangère.
-- ============================================