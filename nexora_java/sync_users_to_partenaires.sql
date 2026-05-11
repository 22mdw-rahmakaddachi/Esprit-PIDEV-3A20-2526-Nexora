-- ============================================
-- SYNCHRONISATION USERS -> PARTENAIRES
-- Créer des entrées partenaire pour tous les utilisateurs
-- ============================================

-- 1. Vérifier la structure des tables
SELECT 'Structure table user:' as info;
DESCRIBE user;

SELECT 'Structure table partenaire:' as info;
DESCRIBE partenaire;

-- 2. Voir les utilisateurs qui n'ont pas d'entrée partenaire correspondante
SELECT 'Utilisateurs sans entrée partenaire:' as info;
SELECT u.id, u.name, u.prenom, u.email, u.role
FROM user u
LEFT JOIN partenaire p ON u.id = p.id
WHERE p.id IS NULL;

-- 3. Créer des entrées partenaire pour tous les utilisateurs qui n'en ont pas
INSERT INTO partenaire (id, nom, email, telephone, adresse, date_creation)
SELECT 
    u.id,
    CONCAT(COALESCE(u.prenom, ''), ' ', COALESCE(u.name, '')) as nom,
    u.email,
    COALESCE(u.telephone, '00000000') as telephone,
    COALESCE(u.adresse, 'Adresse non spécifiée') as adresse,
    NOW() as date_creation
FROM user u
LEFT JOIN partenaire p ON u.id = p.id
WHERE p.id IS NULL
ON DUPLICATE KEY UPDATE 
    nom = VALUES(nom),
    email = VALUES(email);

-- 4. Vérification finale
SELECT 'Vérification - Utilisateurs avec entrée partenaire:' as info;
SELECT COUNT(*) as total_users_avec_partenaire
FROM user u
INNER JOIN partenaire p ON u.id = p.id;

SELECT 'Vérification - Utilisateurs sans entrée partenaire:' as info;
SELECT COUNT(*) as total_users_sans_partenaire
FROM user u
LEFT JOIN partenaire p ON u.id = p.id
WHERE p.id IS NULL;

-- 5. Afficher quelques exemples de correspondances
SELECT 'Exemples de correspondances user-partenaire:' as info;
SELECT u.id, u.name, u.prenom, u.email as user_email, p.nom as partenaire_nom, p.email as partenaire_email
FROM user u
INNER JOIN partenaire p ON u.id = p.id
LIMIT 5;

-- ============================================
-- NOTES
-- ============================================
-- Ce script :
-- 1. Identifie tous les utilisateurs qui n'ont pas d'entrée correspondante dans la table partenaire
-- 2. Crée automatiquement des entrées partenaire pour ces utilisateurs
-- 3. Utilise l'ID de l'utilisateur comme ID du partenaire pour maintenir la cohérence
-- 4. Combine prénom + nom pour créer le nom du partenaire
-- 5. Utilise des valeurs par défaut pour les champs manquants
-- 
-- Après avoir exécuté ce script, tous les utilisateurs auront une entrée partenaire
-- correspondante, ce qui résoudra les erreurs de clé étrangère.
-- ============================================