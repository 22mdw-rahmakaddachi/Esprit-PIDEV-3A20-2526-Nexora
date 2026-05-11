-- Création de la table reclamation
CREATE TABLE IF NOT EXISTS reclamation (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    activite_id INT NOT NULL,
    description TEXT NOT NULL,
    statut ENUM('EN_ATTENTE', 'EN_COURS', 'RESOLUE', 'REJETEE') DEFAULT 'EN_ATTENTE',
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_modification TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (client_id) REFERENCES user(id) ON DELETE CASCADE,
    FOREIGN KEY (activite_id) REFERENCES activite(id) ON DELETE CASCADE,
    
    INDEX idx_client_id (client_id),
    INDEX idx_activite_id (activite_id),
    INDEX idx_statut (statut)
);

-- Insertion de données de test (optionnel)
-- INSERT INTO reclamation (client_id, activite_id, description, statut) VALUES
-- (1, 1, 'Problème avec l\'organisation de l\'activité', 'EN_ATTENTE'),
-- (2, 1, 'Activité annulée sans préavis', 'EN_COURS');