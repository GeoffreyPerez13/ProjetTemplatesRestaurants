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
        $db = Database::getInstance()->getConnection();
        
        $sql = "INSERT INTO plats (category_id, name, description, price, image, display_order, created_at) 
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
        $db = Database::getInstance()->getConnection();
        
        $sql = "SELECT p.*, 
                GROUP_CONCAT(a.id) as allergene_ids,
                GROUP_CONCAT(a.nom) as allergene_noms,
                GROUP_CONCAT(a.icone) as allergene_icones
                FROM plats p
                LEFT JOIN plat_allergenes pa ON p.id = pa.plat_id
                LEFT JOIN allergenes a ON pa.allergene_id = a.id
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
        $db = Database::getInstance()->getConnection();
        
        $sql = "SELECT p.*, c.name as category_name,
                GROUP_CONCAT(a.id) as allergene_ids,
                GROUP_CONCAT(a.nom) as allergene_noms,
                GROUP_CONCAT(a.icone) as allergene_icones
                FROM plats p
                INNER JOIN categories c ON p.category_id = c.id
                LEFT JOIN plat_allergenes pa ON p.id = pa.plat_id
                LEFT JOIN allergenes a ON pa.allergene_id = a.id
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
        $db = Database::getInstance()->getConnection();
        
        $sql = "SELECT p.*, 
                GROUP_CONCAT(a.id) as allergene_ids,
                GROUP_CONCAT(a.nom) as allergene_noms,
                GROUP_CONCAT(a.icone) as allergene_icones
                FROM plats p
                LEFT JOIN plat_allergenes pa ON p.id = pa.plat_id
                LEFT JOIN allergenes a ON pa.allergene_id = a.id
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
        $db = Database::getInstance()->getConnection();
        
        $sql = "UPDATE plats 
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
        $db = Database::getInstance()->getConnection();
        
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
        $sql = "DELETE FROM plats WHERE id = :id";
        
        $stmt = $db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Mettre à jour l'ordre des plats dans une catégorie
     */
    public static function updateOrder(array $order, int $categoryId): bool
    {
        $db = Database::getInstance()->getConnection();
        
        try {
            $db->beginTransaction();
            
            $sql = "UPDATE plats SET display_order = :order WHERE id = :id AND category_id = :category_id";
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
        $db = Database::getInstance()->getConnection();
        
        try {
            $db->beginTransaction();
            
            // Supprimer les anciennes associations
            $sql = "DELETE FROM plat_allergenes WHERE plat_id = :plat_id";
            $stmt = $db->prepare($sql);
            $stmt->execute(['plat_id' => $platId]);
            
            // Ajouter les nouvelles associations
            if (!empty($allergeneIds)) {
                $sql = "INSERT INTO plat_allergenes (plat_id, allergene_id) VALUES (:plat_id, :allergene_id)";
                $stmt = $db->prepare($sql);
                
                foreach ($allergeneIds as $allergeneId) {
                    $stmt->execute([
                        'plat_id' => $platId,
                        'allergene_id' => $allergeneId
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
        $db = Database::getInstance()->getConnection();
        
        $sql = "SELECT COUNT(*) FROM plats WHERE category_id = :category_id";
        
        $stmt = $db->prepare($sql);
        $stmt->execute(['category_id' => $categoryId]);
        
        return (int) $stmt->fetchColumn();
    }
}
