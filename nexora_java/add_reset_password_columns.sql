-- Ajout des colonnes pour la fonctionnalité "mot de passe oublié"
-- Ces colonnes permettent de stocker le code de réinitialisation et sa date d'expiration

ALTER TABLE user 
ADD COLUMN reset_code VARCHAR(10) DEFAULT NULL,
ADD COLUMN reset_expiration BIGINT DEFAULT NULL;

-- Créer un index pour améliorer les performances
CREATE INDEX idx_reset_code ON user(reset_code);

-- Vérification
-- SELECT id, nom, email, reset_code, reset_expiration FROM user;