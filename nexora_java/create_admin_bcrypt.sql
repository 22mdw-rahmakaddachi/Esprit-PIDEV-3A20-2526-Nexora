-- ============================================
-- CRÉER ADMIN AVEC MOT DE PASSE BCRYPT
-- Compatible avec votre code existant
-- ============================================

-- 1. Vérifier la table users existante
SELECT 'Table users actuelle:' as info;
SELECT id, nom, prenom, email, role FROM users WHERE role = 'Admin' LIMIT 5;

-- 2. Créer un utilisateur admin avec mot de passe BCrypt
-- Mot de passe: admin123 (haché avec BCrypt)
INSERT INTO users (prenom, nom, email, num, role, mdp, tentative, validation, block_until, block_level) 
VALUES (
    'Super',                    -- prenom
    'Administrateur',           -- nom
    'admin@nexora.com',        -- email
    12345678,                  -- num
    'Admin',                   -- role
    '$2a$10$N9qo8uLOickgx2ZMRZoMye.IjZJLjqaddqeafQwFS2ChSM6L6RDAW', -- mdp (BCrypt hash pour "admin123")
    0,                         -- tentative
    1,                         -- validation (compte activé)
    0,                         -- block_until
    0                          -- block_level
) ON DUPLICATE KEY UPDATE
    mdp = VALUES(mdp),
    validation = 1,
    tentative = 0,
    block_until = 0,
    block_level = 0;

-- 3. Alternative: Réinitialiser un admin existant
-- Décommentez si vous voulez utiliser un admin existant
/*
UPDATE users 
SET mdp = '$2a$10$N9qo8uLOickgx2ZMRZoMye.IjZJLjqaddqeafQwFS2ChSM6L6RDAW',
    validation = 1,
    tentative = 0,
    block_until = 0,
    block_level = 0
WHERE role = 'Admin' 
LIMIT 1;
*/

-- 4. Vérification finale
SELECT 'Admin créé avec succès:' as info;
SELECT id, nom, prenom, email, role, validation FROM users WHERE email = 'admin@nexora.com';

-- 5. Informations de connexion
SELECT '=== INFORMATIONS DE CONNEXION ===' as info;
SELECT 'Email: admin@nexora.com' as email_info;
SELECT 'Mot de passe: admin123' as password_info;
SELECT 'Interface: /page.fxml (Admin Dashboard)' as interface_info;

-- ============================================
-- INSTRUCTIONS
-- ============================================
-- 1. Exécutez ce script SQL
-- 2. Connectez-vous avec :
--    Email: admin@nexora.com
--    Mot de passe: admin123
-- 
-- 3. Le système vous dirigera vers /page.fxml (interface admin)
--    car votre rôle sera "Admin"
-- 
-- Le mot de passe est haché avec BCrypt comme votre code l'exige
-- ============================================