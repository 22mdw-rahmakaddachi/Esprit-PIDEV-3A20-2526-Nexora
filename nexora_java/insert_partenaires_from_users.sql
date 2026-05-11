-- ============================================
-- INSERTION AUTOMATIQUE DES PARTENAIRES
-- ============================================
-- Ce script crée automatiquement des partenaires pour tous les utilisateurs
-- ayant le rôle 'partenaire' mais qui n'ont pas encore d'entrée partenaire
-- ============================================

-- 1. Vérifier les utilisateurs partenaires existants
SELECT 
    u.id as user_id,
    u.email,
    u.nom,
    u.prenom,
    u.role,
    p.id as partenaire_id,
    p.nom_entreprise
FROM users u 
LEFT JOIN partenaire p ON u.id = p.user_id 
WHERE u.role IN ('partenaire', 'Partenaire', 'PARTENAIRE')
ORDER BY u.id;

-- 2. Insérer des partenaires pour les utilisateurs qui n'en ont pas
INSERT INTO partenaire (user_id, nom_entreprise, responsable_nom, telephone, statut) 
SELECT 
    u.id,
    CONCAT('Entreprise ', u.prenom, ' ', u.nom),
    CONCAT(u.prenom, ' ', u.nom),
    COALESCE(u.num, '00000000'),
    'ACTIF'
FROM users u 
WHERE u.role IN ('partenaire', 'Partenaire', 'PARTENAIRE')
AND u.id NOT IN (SELECT user_id FROM partenaire);

-- 3. Vérifier le résultat
SELECT 
    p.id as partenaire_id,
    p.user_id,
    p.nom_entreprise,
    p.responsable_nom,
    p.statut,
    u.email,
    u.role
FROM partenaire p
JOIN users u ON p.user_id = u.id
ORDER BY p.id;

-- 4. Compter les partenaires créés
SELECT COUNT(*) as total_partenaires FROM partenaire;

-- ============================================
-- INSERTION MANUELLE POUR UTILISATEURS SPÉCIFIQUES
-- ============================================
-- Si vous voulez créer des partenaires pour des utilisateurs spécifiques :

-- Pour l'utilisateur ID 34 (anoire@gmail.com)
INSERT IGNORE INTO partenaire (user_id, nom_entreprise, responsable_nom, telephone, statut) 
VALUES (34, 'Entreprise Anoire Douiri', 'Anoire Douiri', '78955646', 'ACTIF');

-- Pour l'utilisateur ID 40 (amine@gmail.com)
INSERT IGNORE INTO partenaire (user_id, nom_entreprise, responsable_nom, telephone, statut) 
VALUES (40, 'Entreprise Mohamed Amine', 'Mohamed Amine', '78895462', 'ACTIF');

-- Pour l'utilisateur ID 43 (utilisateur avec mot de passe hashé)
INSERT IGNORE INTO partenaire (user_id, nom_entreprise, responsable_nom, telephone, statut) 
VALUES (43, 'Entreprise ZZZZZ', 'ZZZZZ YYY', '777', 'ACTIF');

-- Pour l'utilisateur ID 45 (israa)
INSERT IGNORE INTO partenaire (user_id, nom_entreprise, responsable_nom, telephone, statut) 
VALUES (45, 'Entreprise Israa', 'III Israa', '1111', 'ACTIF');

-- ============================================
-- VÉRIFICATION FINALE
-- ============================================

-- Voir tous les partenaires avec leurs utilisateurs
SELECT 
    'RÉSULTAT FINAL:' as message,
    p.id as partenaire_id,
    p.nom_entreprise,
    p.responsable_nom,
    u.id as user_id,
    u.email,
    u.role,
    p.statut
FROM partenaire p
JOIN users u ON p.user_id = u.id
ORDER BY p.id;

-- Compter par statut
SELECT 
    statut,
    COUNT(*) as nombre
FROM partenaire 
GROUP BY statut;