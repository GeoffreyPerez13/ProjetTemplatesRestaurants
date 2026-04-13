<?php

namespace App\Models;

use App\Core\Database;
use PDO;

/**
 * Classe Category - Gestion des catégories de plats
 */
class Category
{
    /**
     * Créer une catégorie
     */
    public static function create(array $data): int
    {
        $db = Database::getInstance()->getConnection();
        
        $sql = "INSERT INTO categories (admin_id, name, image, display_order, created_at, updated_at) 
                VALUES (:admin_id, :name, :image, :display_order, NOW(), NOW())";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([
            'admin_id' => $data['admin_id'],
            'name' => $data['name'],
            'image' => $data['image'] ?? null,
            'display_order' => $data['display_order'] ?? 0
        ]);
        
        return (int) $db->lastInsertId();
    }

    /**
     * Récupérer toutes les catégories d'un admin
     */
    public static function getAllByAdmin(int $adminId): array
    {
        $db = Database::getInstance()->getConnection();
        
        $sql = "SELECT c.*, COUNT(p.id) as plats_count 
                FROM categories c
                LEFT JOIN plats p ON c.id = p.category_id
                WHERE c.admin_id = :admin_id
                GROUP BY c.id
                ORDER BY c.display_order ASC, c.created_at ASC";
        
        $stmt = $db->prepare($sql);
        $stmt->execute(['admin_id' => $adminId]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupérer une catégorie par ID
     */
    public static function findById(int $id, int $adminId): ?array
    {
        $db = Database::getInstance()->getConnection();
        
        $sql = "SELECT * FROM categories WHERE id = :id AND admin_id = :admin_id";
        
        $stmt = $db->prepare($sql);
        $stmt->execute(['id' => $id, 'admin_id' => $adminId]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Mettre à jour une catégorie
     */
    public static function update(int $id, int $adminId, array $data): bool
    {
        $db = Database::getInstance()->getConnection();
        
        $sql = "UPDATE categories 
                SET name = :name, 
                    image = :image,
                    updated_at = NOW()
                WHERE id = :id AND admin_id = :admin_id";
        
        $stmt = $db->prepare($sql);
        return $stmt->execute([
            'id' => $id,
            'admin_id' => $adminId,
            'name' => $data['name'],
            'image' => $data['image'] ?? null
        ]);
    }

    /**
     * Supprimer une catégorie
     */
    public static function delete(int $id, int $adminId): bool
    {
        $db = Database::getInstance()->getConnection();
        
        // Vérifier que la catégorie appartient à l'admin
        $category = self::findById($id, $adminId);
        if (!$category) {
            return false;
        }
        
        // Supprimer l'image si elle existe
        if ($category['image']) {
            $imagePath = __DIR__ . '/../../public/uploads/categories/' . $category['image'];
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }
        
        // Supprimer la catégorie (les plats seront supprimés en cascade)
        $sql = "DELETE FROM categories WHERE id = :id AND admin_id = :admin_id";
        
        $stmt = $db->prepare($sql);
        return $stmt->execute(['id' => $id, 'admin_id' => $adminId]);
    }

    /**
     * Mettre à jour l'ordre des catégories
     */
    public static function updateOrder(array $order, int $adminId): bool
    {
        $db = Database::getInstance()->getConnection();
        
        try {
            $db->beginTransaction();
            
            $sql = "UPDATE categories SET display_order = :order WHERE id = :id AND admin_id = :admin_id";
            $stmt = $db->prepare($sql);
            
            foreach ($order as $index => $categoryId) {
                $stmt->execute([
                    'order' => $index,
                    'id' => $categoryId,
                    'admin_id' => $adminId
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
     * Compter les catégories d'un admin
     */
    public static function countByAdmin(int $adminId): int
    {
        $db = Database::getInstance()->getConnection();
        
        $sql = "SELECT COUNT(*) FROM categories WHERE admin_id = :admin_id";
        
        $stmt = $db->prepare($sql);
        $stmt->execute(['admin_id' => $adminId]);
        
        return (int) $stmt->fetchColumn();
    }
}
