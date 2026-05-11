-- Script de test pour vérifier l'affichage des réclamations

-- 1. Vérifier les réclamations avec leurs activités
SELECT 
    r.id,
    r.client_id,
    r.activite_id,
    r.description,
    r.statut,
    r.date_creation,
    a.nom as activite_nom
FROM reclamation r
LEFT JOIN activite a ON r.activite_id = a.id
ORDER BY r.date_creation DESC;

-- 2. Vérifier s'il y a des réclamations orphelines (sans activité)
SELECT 
    r.id,
    r.client_id,
    r.activite_id,
    r.description,
    'ACTIVITE MANQUANTE' as probleme
FROM reclamation r
LEFT JOIN activite a ON r.activite_id = a.id
WHERE a.id IS NULL;

-- 3. Compter les réclamations par client
SELECT 
    client_id,
    COUNT(*) as nombre_reclamations
FROM reclamation
GROUP BY client_id;

-- 4. Vérifier les statuts
SELECT DISTINCT statut FROM reclamation;

-- 5. Voir les réclamations d'un client spécifique (remplacer X par l'ID)
-- SELECT * FROM reclamation WHERE client_id = X;
