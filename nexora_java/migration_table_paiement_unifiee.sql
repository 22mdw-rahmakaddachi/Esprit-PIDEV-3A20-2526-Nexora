-- ============================================
-- MIGRATION TABLE PAIEMENT UNIFIÉE
-- Supporte E-commerce + Gestion Activités
-- ============================================

-- Supprimer l'ancienne table si elle existe
DROP TABLE IF EXISTS `paiement`;

-- Créer la nouvelle table unifiée
CREATE TABLE `paiement` (
  `id` int NOT NULL AUTO_INCREMENT,
  
  -- Pour E-commerce (commandes produits)
  `commande_id` int DEFAULT NULL,
  
  -- Pour Gestion Activités
  `demande_id` int DEFAULT NULL,
  `client_id` int DEFAULT NULL,
  `activite_id` int DEFAULT NULL,
  
  -- Champs communs
  `montant` decimal(10,2) NOT NULL,
  `methode_paiement` varchar(100) NOT NULL,
  `statut` varchar(50) DEFAULT 'EN_ATTENTE',
  `date_paiement` datetime DEFAULT NULL,
  
  -- Références transactions
  `transaction_id` varchar(255) DEFAULT NULL,
  `reference_externe` varchar(255) DEFAULT NULL,
  `reference_transaction` varchar(255) DEFAULT NULL,
  
  -- Détails supplémentaires (JSON pour Konnect API, etc.)
  `details_json` text DEFAULT NULL,
  
  -- Timestamps
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  
  PRIMARY KEY (`id`),
  KEY `idx_paiement_commande` (`commande_id`),
  KEY `idx_paiement_demande` (`demande_id`),
  KEY `idx_paiement_client` (`client_id`),
  KEY `idx_paiement_activite` (`activite_id`),
  KEY `idx_paiement_statut` (`statut`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- ============================================
-- NOTES D'UTILISATION
-- ============================================
-- 
-- Pour E-commerce :
--   - Utiliser commande_id (référence à la table commande)
--   - Laisser demande_id, client_id, activite_id à NULL
--
-- Pour Activités :
--   - Utiliser demande_id, client_id, activite_id
--   - Laisser commande_id à NULL
--
-- Champs de référence :
--   - transaction_id : ID Konnect ou autre gateway
--   - reference_externe : Référence externe (REF-xxxxx)
--   - reference_transaction : Alias pour compatibilité
--
-- ============================================
