-- ============================================
-- CRÉER PARTENAIRE POUR UTILISATEUR ACTUEL
-- Script rapide pour créer un partenaire
-- ============================================

-- 1. Voir les utilisateurs existants
SELECT 'Utilisateurs existants:' as info;
SELECT id, name, prenom, email FROM user ORDER BY id;

-- 2. Voir les partenaires existants
SELECT 'Partenaires existants:' as info;
SELECT id, user_id, nom_entreprise, responsable_nom, statut FROM partenaire ORDER BY id;

-- 3. Créer un partenaire pour l'utilisateur ID 1 (modifiez l'ID selon vos besoins)
-- CHANGEZ LE @user_id SELON L'UTILISATEUR QUI VEUT CRÉER DES ACTIVITÉS
SET @user_id = 1; -- ← MODIFIEZ CETTE VALEUR

INSERT IGNORE INTO partenaire (
    user_id, 
    nom_entreprise, 
    ice,
    responsable_nom, 
    responsable_telephone, 
    adresse_entreprise, 
    site_web,
    description,
    statut, 
    date_inscription, 
    commission
) 
SELECT 
    u.id,
    CONCAT(COALESCE(u.prenom, ''), ' ', COALESCE(u.name, ''), ' Entreprise') as nom_entreprise,
    '1111' as ice, -- ICE par défaut
    CONCAT(COALESCE(u.prenom, ''), ' ', COALESCE(u.name, '')) as responsable_nom,
    COALESCE(u.telephone, '00000000') as responsable_telephone,
    COALESCE(u.adresse, 'Adresse non spécifiée') as adresse_entreprise,
    NULL as site_web,
    NULL as description,
    'ACTIF' as statut,
    NOW() as date_inscription,
    10.0 as commission
FROM user u 
WHERE u.id = @user_id;

-- 4. Vérification
SELECT 'Partenaire créé pour utilisateur:' as info;
SELECT 
    p.id as partenaire_id,
    p.user_id,
    u.name as user_name,
    u.prenom as user_prenom,
    p.nom_entreprise,
    p.responsable_nom,
    p.statut
FROM partenaire p
JOIN user u ON p.user_id = u.id
WHERE p.user_id = @user_id;

-- ============================================
-- INSTRUCTIONS
-- ============================================
-- 1. Modifiez la valeur @user_id (ligne 13) avec l'ID de l'utilisateur 
--    qui veut créer des activités
-- 2. Exécutez ce script
-- 3. L'utilisateur pourra maintenant créer des activités sans erreur
-- 
-- Pour trouver l'ID de votre utilisateur, regardez la première requête
-- qui affiche tous les utilisateurs avec leurs IDs.
-- ============================================