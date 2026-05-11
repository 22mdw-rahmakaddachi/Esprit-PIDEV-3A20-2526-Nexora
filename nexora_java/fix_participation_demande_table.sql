-- ============================================
-- CORRECTION TABLE PARTICIPATION_DEMANDE
-- Ajouter les colonnes manquantes
-- ============================================

-- Vérifier si la table existe et la créer si nécessaire
CREATE TABLE IF NOT EXISTS `participation_demande` (
  `id` int NOT NULL AUTO_INCREMENT,
  `activite_id` int NOT NULL,
  `client_id` int NOT NULL,
  `client_nom` varchar(255) NOT NULL,
  `client_email` varchar(255) NOT NULL,
  `client_telephone` varchar(20) DEFAULT NULL,
  `statut` varchar(50) DEFAULT 'EN_ATTENTE',
  `date_demande` datetime DEFAULT CURRENT_TIMESTAMP,
  `paiement_effectue` boolean DEFAULT FALSE,
  PRIMARY KEY (`id`),
  KEY `idx_participation_activite` (`activite_id`),
  KEY `idx_participation_client` (`client_id`),
  KEY `idx_participation_statut` (`statut`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Ajouter les colonnes manquantes si elles n'existent pas
ALTER TABLE `participation_demande` 
ADD COLUMN IF NOT EXISTS `client_telephone` varchar(20) DEFAULT NULL,
ADD COLUMN IF NOT EXISTS `date_demande` datetime DEFAULT CURRENT_TIMESTAMP,
ADD COLUMN IF NOT EXISTS `paiement_effectue` boolean DEFAULT FALSE;

-- Mettre à jour les colonnes existantes si nécessaire
ALTER TABLE `participation_demande` 
MODIFY COLUMN `client_nom` varchar(255) NOT NULL,
MODIFY COLUMN `client_email` varchar(255) NOT NULL,
MODIFY COLUMN `statut` varchar(50) DEFAULT 'EN_ATTENTE';

-- Ajouter les index si ils n'existent pas
ALTER TABLE `participation_demande` 
ADD INDEX IF NOT EXISTS `idx_participation_activite` (`activite_id`),
ADD INDEX IF NOT EXISTS `idx_participation_client` (`client_id`),
ADD INDEX IF NOT EXISTS `idx_participation_statut` (`statut`);

-- ============================================
-- NOTES
-- ============================================
-- Ce script corrige la table participation_demande pour inclure
-- toutes les colonnes utilisées par ParticipationDemandeService :
-- - client_telephone : numéro de téléphone du client
-- - date_demande : date de création de la demande
-- - paiement_effectue : statut du paiement (boolean)
-- ============================================