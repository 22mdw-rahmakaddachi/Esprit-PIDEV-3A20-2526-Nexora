-- ============================================
-- CORRECTION COMPLÈTE POUR CONNEXION ADMIN
-- Résout les problèmes de table et crée un admin fonctionnel
-- ============================================

-- 1. Vérifier la structure actuelle
SELECT 'Structure table user actuelle:' as info;
DESCRIBE user;

-- 2. Créer/vérifier la table users (utilisée par le code de connexion)
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `prenom` varchar(255) DEFAULT NULL,
  `num` int DEFAULT NULL,
  `role` varchar(50) DEFAULT NULL,
  `mdp` varchar(255) DEFAULT NULL,
  `tentative` int DEFAULT 0,
  `validation` boolean DEFAULT TRUE,
  `block_until` bigint DEFAULT 0,
  `block_level` int DEFAULT 0,
  `finger_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- 3. Synchroniser les données de user vers users
INSERT INTO users (id, nom, email, prenom, num, role, mdp, tentative, validation, block_until, block_level, finger_id)
SELECT id, name, email, prenom, num, role, mdp, tentative, validation, blockUntil, blockLevel, fingerId
FROM user
ON DUPLICATE KEY UPDATE
    nom = VALUES(nom),
    email = VALUES(email),
    prenom = VALUES(prenom),
    num = VALUES(num),
    role = VALUES(role),
    mdp = VALUES(mdp),
    tentative = VALUES(tentative),
    validation = VALUES(validation),
    block_until = VALUES(block_until),
    block_level = VALUES(block_level),
    finger_id = VALUES(finger_id);

-- 4. Créer un admin avec mot de passe BCrypt
-- Mot de passe: admin123 (haché avec BCrypt)
INSERT INTO users (nom, email, prenom, num, role, mdp, tentative, validation, block_until, block_level) 
VALUES (
    'Administrateur',
    'admin@nexora.com',
    'Super',
    12345678,
    'Admin',
    '$2a$10$N9qo8uLOickgx2ZMRZoMye.IjZJLjqaddqeafQwFS2ChSM6L6RDAW', -- BCrypt hash pour "admin123"
    0,
    1,
    0,
    0
) ON DUPLICATE KEY UPDATE
    mdp = VALUES(mdp),
    validation = 1,
    tentative = 0;

-- 5. Alternative: Réinitialiser un admin existant avec mot de passe simple
-- Décommentez si vous préférez utiliser un admin existant
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

-- 6. Vérification finale
SELECT 'Utilisateurs Admin dans table users:' as info;
SELECT id, nom, prenom, email, role, validation FROM users WHERE role = 'Admin';

-- 7. Test de connexion (informations à utiliser)
SELECT '=== INFORMATIONS DE CONNEXION ADMIN ===' as info;
SELECT 'Email: admin@nexora.com' as email_info;
SELECT 'Mot de passe: admin123' as password_info;
SELECT 'Page de destination: /page.fxml' as destination_info;

-- ============================================
-- INSTRUCTIONS DE CONNEXION
-- ============================================
-- Après avoir exécuté ce script, connectez-vous avec :
-- 
-- Email: admin@nexora.com
-- Mot de passe: admin123
-- 
-- Le système vous dirigera automatiquement vers l'interface admin
-- (/page.fxml) car le rôle est "Admin".
-- 
-- Si vous avez des problèmes, vérifiez que :
-- 1. La table "users" existe (pas "user")
-- 2. Les colonnes correspondent au code (nom, pas name)
-- 3. Le mot de passe est haché avec BCrypt
-- ============================================