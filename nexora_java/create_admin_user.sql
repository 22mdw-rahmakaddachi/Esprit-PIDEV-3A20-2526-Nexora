-- ============================================
-- CRÉER UN UTILISATEUR ADMIN
-- Script pour créer un compte administrateur
-- ============================================

-- 1. Voir les utilisateurs admin existants
SELECT 'Utilisateurs Admin existants:' as info;
SELECT id, name, prenom, email, role FROM user WHERE role = 'Admin' OR role = 'admin';

-- 2. Créer un nouvel utilisateur admin
-- MODIFIEZ LES INFORMATIONS CI-DESSOUS SELON VOS BESOINS
INSERT INTO user (name, email, prenom, num, role, mdp, tentative, validation, blockUntil, blockLevel) 
VALUES (
    'Administrateur',           -- name
    'admin@nexora.com',        -- email (CHANGEZ CECI)
    'Super',                   -- prenom
    12345678,                  -- num (numéro de téléphone)
    'Admin',                   -- role
    'admin123',                -- mdp (CHANGEZ CE MOT DE PASSE)
    0,                         -- tentative
    1,                         -- validation (1 = validé)
    0,                         -- blockUntil
    0                          -- blockLevel
);

-- 3. Vérifier que l'utilisateur a été créé
SELECT 'Nouvel utilisateur admin créé:' as info;
SELECT id, name, prenom, email, role FROM user WHERE email = 'admin@nexora.com';

-- 4. Alternative: Réinitialiser le mot de passe d'un admin existant
-- Décommentez et modifiez selon vos besoins
/*
UPDATE user 
SET mdp = 'nouveaumotdepasse123' 
WHERE id = 111 AND role = 'Admin';

SELECT 'Mot de passe réinitialisé pour:' as info;
SELECT id, name, prenom, email FROM user WHERE id = 111;
*/

-- ============================================
-- INSTRUCTIONS D'UTILISATION
-- ============================================
-- 1. Modifiez l'email et le mot de passe dans la requête INSERT
-- 2. Exécutez ce script
-- 3. Connectez-vous avec les nouvelles informations :
--    Email: admin@nexora.com
--    Mot de passe: admin123
-- 
-- OU
-- 
-- 1. Décommentez la section "Alternative" 
-- 2. Modifiez l'ID et le nouveau mot de passe
-- 3. Utilisez un compte admin existant avec le nouveau mot de passe
-- ============================================