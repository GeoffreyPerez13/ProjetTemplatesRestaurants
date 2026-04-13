<?php

namespace App\Models;

use App\Core\Database;
use PDO;

/**
 * Classe Dish - Gestion des plats
 */
class Dish
{
    /**
     * Créer un plat
     */
    public static function create(array $data): int
    {
        $db = Database::getInstance();
        
        $sql = "INSERT INTO dishes (category_id, name, description, price, image, display_order, created_at) 
                VALUES (:category_id, :name, :description, :price, :image, :display_order, NOW())";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([
            'category_id' => $data['category_id'],
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'price' => $data['price'] ?? null,
            'image' => $data['image'] ?? null,
            'display_order' => $data['display_order'] ?? 0
        ]);
        
        return (int) $db->lastInsertId();
    }

    /**
     * Récupérer tous les plats d'une catégorie
     */
    public static function getAllByCategory(int $categoryId): array
    {
        $db = Database::getInstance();
        
        $sql = "SELECT p.*, 
                GROUP_CONCAT(a.id) as allergene_ids,
                GROUP_CONCAT(a.nom) as allergene_noms,
                GROUP_CONCAT(a.icone) as allergene_icones
                FROM dishes p
                LEFT JOIN dish_allergens pa ON p.id = pa.dish_id
                LEFT JOIN allergens a ON pa.allergen_id = a.id
                WHERE p.category_id = :category_id
                GROUP BY p.id
                ORDER BY p.display_order ASC, p.created_at ASC";
        
        $stmt = $db->prepare($sql);
        $stmt->execute(['category_id' => $categoryId]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupérer tous les plats d'un admin
     */
    public static function getAllByAdmin(int $adminId): array
    {
        $db = Database::getInstance();
        
        $sql = "SELECT p.*, c.name as category_name,
                GROUP_CONCAT(a.id) as allergene_ids,
                GROUP_CONCAT(a.nom) as allergene_noms,
                GROUP_CONCAT(a.icone) as allergene_icones
                FROM dishes p
                INNER JOIN categories c ON p.category_id = c.id
                LEFT JOIN dish_allergens pa ON p.id = pa.dish_id
                LEFT JOIN allergens a ON pa.allergen_id = a.id
                WHERE c.admin_id = :admin_id
                GROUP BY p.id
                ORDER BY c.display_order ASC, p.display_order ASC";
        
        $stmt = $db->prepare($sql);
        $stmt->execute(['admin_id' => $adminId]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupérer un plat par ID
     */
    public static function findById(int $id): ?array
    {
        $db = Database::getInstance();
        
        $sql = "SELECT p.*, 
                GROUP_CONCAT(a.id) as allergene_ids,
                GROUP_CONCAT(a.nom) as allergene_noms,
                GROUP_CONCAT(a.icone) as allergene_icones
                FROM dishes p
                LEFT JOIN dish_allergens pa ON p.id = pa.dish_id
                LEFT JOIN allergens a ON pa.allergen_id = a.id
                WHERE p.id = :id
                GROUP BY p.id";
        
        $stmt = $db->prepare($sql);
        $stmt->execute(['id' => $id]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Mettre à jour un plat
     */
    public static function update(int $id, array $data): bool
    {
        $db = Database::getInstance();
        
        $sql = "UPDATE dishes 
                SET name = :name, 
                    description = :description,
                    price = :price,
                    image = :image
                WHERE id = :id";
        
        $stmt = $db->prepare($sql);
        return $stmt->execute([
            'id' => $id,
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'price' => $data['price'] ?? null,
            'image' => $data['image'] ?? null
        ]);
    }

    /**
     * Supprimer un plat
     */
    public static function delete(int $id): bool
    {
        $db = Database::getInstance();
        
        // Récupérer le plat pour supprimer l'image
        $plat = self::findById($id);
        if (!$plat) {
            return false;
        }
        
        // Supprimer l'image si elle existe
        if ($plat['image']) {
            $imagePath = __DIR__ . '/../../public/uploads/plats/' . $plat['image'];
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }
        
        // Supprimer le plat (les relations allergènes seront supprimées en cascade)
        $sql = "DELETE FROM dishes WHERE id = :id";
        
        $stmt = $db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Mettre à jour l'ordre des plats dans une catégorie
     */
    public static function updateOrder(array $order, int $categoryId): bool
    {
        $db = Database::getInstance();
        
        try {
            $db->beginTransaction();
            
            $sql = "UPDATE dishes SET display_order = :order WHERE id = :id AND category_id = :category_id";
            $stmt = $db->prepare($sql);
            
            foreach ($order as $index => $platId) {
                $stmt->execute([
                    'order' => $index,
                    'id' => $platId,
                    'category_id' => $categoryId
                ]);
            }
            
            $db->commit();
            return true;
        } catch (\Exception $e) {
            $db->rollBack();
            return false;
        }
    }

    /**
     * Associer des allergènes à un plat
     */
    public static function syncAllergenes(int $platId, array $allergeneIds): bool
    {
        $db = Database::getInstance();
        
        try {
            $db->beginTransaction();
            
            // Supprimer les anciennes associations
            $sql = "DELETE FROM dish_allergens WHERE dish_id = :dish_id";
            $stmt = $db->prepare($sql);
            $stmt->execute(['dish_id' => $platId]);
            
            // Ajouter les nouvelles associations
            if (!empty($allergeneIds)) {
                $sql = "INSERT INTO dish_allergens (dish_id, allergen_id) VALUES (:dish_id, :allergen_id)";
                $stmt = $db->prepare($sql);
                
                foreach ($allergeneIds as $allergeneId) {
                    $stmt->execute([
                        'dish_id' => $platId,
                        'allergen_id' => $allergeneId
                    ]);
                }
            }
            
            $db->commit();
            return true;
        } catch (\Exception $e) {
            $db->rollBack();
            return false;
        }
    }

    /**
     * Compter les plats d'une catégorie
     */
    public static function countByCategory(int $categoryId): int
    {
        $db = Database::getInstance();
        
        $sql = "SELECT COUNT(*) FROM dishes WHERE category_id = :category_id";
        
        $stmt = $db->prepare($sql);
        $stmt->execute(['category_id' => $categoryId]);
        
        return (int) $stmt->fetchColumn();
    }
}
