-- Migration pour normaliser les display_order (min = 1 au lieu de 0)
-- Date: 2026-04-15

-- Normaliser les ordres des catégories
-- Incrémenter tous les ordres de 1 pour chaque admin
UPDATE categories c1
SET display_order = display_order + 1
WHERE display_order >= 0;

-- Normaliser les ordres des plats
-- Incrémenter tous les ordres de 1 pour chaque catégorie
UPDATE dishes d1
SET display_order = display_order + 1
WHERE display_order >= 0;

-- Vérification : Afficher les catégories avec ordre 0 (ne devrait rien retourner)
SELECT id, name, display_order, admin_id 
FROM categories 
WHERE display_order = 0;

-- Vérification : Afficher les plats avec ordre 0 (ne devrait rien retourner)
SELECT id, name, display_order, category_id 
FROM dishes 
WHERE display_order = 0;

-- Afficher les ordres des catégories par admin
SELECT admin_id, id, name, display_order 
FROM categories 
ORDER BY admin_id, display_order;

-- Afficher les ordres des plats par catégorie
SELECT category_id, id, name, display_order 
FROM dishes 
ORDER BY category_id, display_order;
