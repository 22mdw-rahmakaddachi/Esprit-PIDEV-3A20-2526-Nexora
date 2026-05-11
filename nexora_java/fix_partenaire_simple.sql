-- ============================================
-- SOLUTION SIMPLE - CRÉER PARTENAIRE MANUELLEMENT
-- ============================================

-- 1. Vérifier la structure de la table partenaire
DESCRIBE partenaire;

-- 2. Voir les utilisateurs partenaires
SELECT id, email, prenom, nom, role FROM users WHERE role LIKE '%partenaire%';

-- 3. Créer un partenaire par défaut (ID = 1)
INSERT IGNORE INTO partenaire (id, user_id, nom_entreprise, responsable_nom, statut) 
VALUES (1, 1, 'Partenaire Défaut', 'Système', 'ACTIF');

-- 4. Créer un partenaire pour l'utilisateur ID 34 (anoire@gmail.com)
INSERT IGNORE INTO partenaire (user_id, nom_entreprise, responsable_nom, responsable_telephone, statut) 
VALUES (34, 'Entreprise Anoire Douiri', 'Anoire Douiri', '78955646', 'ACTIF');

-- 5. Créer des partenaires pour tous les utilisateurs partenaires
INSERT IGNORE INTO partenaire (user_id, nom_entreprise, responsable_nom, responsable_telephone, statut) 
SELECT 
    id,
    CONCAT('Entreprise ', prenom, ' ', nom),
    CONCAT(prenom, ' ', nom),
    COALESCE(num, '00000000'),
    'ACTIF'
FROM users 
WHERE role IN ('partenaire', 'Partenaire', 'PARTENAIRE');

-- 6. Vérifier le résultat
SELECT 
    p.id as partenaire_id,
    p.user_id,
    p.nom_entreprise,
    p.responsable_nom,
    u.email,
    u.role
FROM partenaire p
LEFT JOIN users u ON p.user_id = u.id
ORDER BY p.id;

-- 7. Compter les partenaires
SELECT COUNT(*) as total_partenaires FROM partenaire;