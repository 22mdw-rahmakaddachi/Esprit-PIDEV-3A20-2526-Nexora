-- ============================================
-- CRÉATION TABLE PARTENAIRE
-- ============================================
-- Cette table est NÉCESSAIRE pour le système
-- Elle stocke les informations spécifiques aux partenaires/vendeurs
-- ============================================

-- Supprimer si existe (pour réinitialisation)
-- DROP TABLE IF EXISTS partenaire;

-- Créer la table partenaire
CREATE TABLE IF NOT EXISTS partenaire (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL UNIQUE COMMENT 'Lien vers users.id',
    nom_entreprise VARCHAR(255) COMMENT 'Nom de l\'entreprise',
    responsable_nom VARCHAR(255) COMMENT 'Nom du responsable',
    telephone VARCHAR(20) COMMENT 'Téléphone de contact',
    adresse TEXT COMMENT 'Adresse de l\'entreprise',
    statut VARCHAR(50) DEFAULT 'ACTIF' COMMENT 'ACTIF, SUSPENDU, INACTIF',
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_modification TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Contraintes
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    
    -- Index pour performance
    INDEX idx_user_id (user_id),
    INDEX idx_statut (statut),
    INDEX idx_nom_entreprise (nom_entreprise)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- DONNÉES DE TEST (Optionnel)
-- ============================================

-- Exemple 1: Partenaire TechStore
-- Assurez-vous que le user_id existe dans la table users
-- INSERT INTO partenaire (user_id, nom_entreprise, responsable_nom, telephone, adresse, statut) 
-- VALUES (2, 'TechStore SARL', 'Ahmed Ben Ali', '+216 20 123 456', 'Avenue Habib Bourguiba, Tunis', 'ACTIF');

-- Exemple 2: Partenaire SportShop
-- INSERT INTO partenaire (user_id, nom_entreprise, responsable_nom, telephone, adresse, statut) 
-- VALUES (3, 'SportShop Tunisia', 'Fatma Trabelsi', '+216 22 987 654', 'Rue de la République, Sfax', 'ACTIF');

-- Exemple 3: Partenaire CampingPro
-- INSERT INTO partenaire (user_id, nom_entreprise, responsable_nom, telephone, adresse, statut) 
-- VALUES (4, 'CampingPro', 'Mohamed Gharbi', '+216 25 555 777', 'Zone Industrielle, Sousse', 'ACTIF');

-- ============================================
-- VÉRIFICATIONS
-- ============================================

-- Vérifier que la table est créée
SELECT 'Table partenaire créée avec succès!' as message;

-- Voir la structure
DESCRIBE partenaire;

-- Compter les partenaires
SELECT COUNT(*) as nombre_partenaires FROM partenaire;

-- ============================================
-- REQUÊTES UTILES
-- ============================================

-- Voir tous les partenaires avec leurs informations users
-- SELECT 
--     p.id as partenaire_id,
--     p.nom_entreprise,
--     p.responsable_nom,
--     p.telephone,
--     p.statut,
--     u.email,
--     u.nom,
--     u.prenom,
--     u.role,
--     p.date_creation
-- FROM partenaire p
-- JOIN users u ON p.user_id = u.id
-- ORDER BY p.date_creation DESC;

-- Compter les produits par partenaire
-- SELECT 
--     p.nom_entreprise,
--     COUNT(pp.id) as nombre_produits
-- FROM partenaire p
-- LEFT JOIN produit_parent pp ON p.id = pp.partenaire_id
-- GROUP BY p.id, p.nom_entreprise
-- ORDER BY nombre_produits DESC;

-- Voir les partenaires actifs
-- SELECT * FROM partenaire WHERE statut = 'ACTIF';

-- ============================================
-- NOTES IMPORTANTES
-- ============================================
-- 
-- 1. Cette table est LIÉE à la table users via user_id
-- 2. Chaque partenaire DOIT avoir un user correspondant
-- 3. Le user doit avoir role = 'Partenaire'
-- 4. La suppression d'un user supprime automatiquement le partenaire (CASCADE)
-- 5. Les produits sont liés au partenaire.id (pas au users.id)
-- 6. Les codes promo sont liés au partenaire.id
-- 
-- ============================================
