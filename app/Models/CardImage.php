<?php

class CardImage
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function getAllByAdmin($adminId)
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM carte_images 
            WHERE admin_id = ? 
            ORDER BY display_order, created_at DESC
        ");
        $stmt->execute([$adminId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function add($adminId, $filename, $originalName)
    {
        // Trouver le prochain ordre d'affichage
        $stmt = $this->pdo->prepare("
            SELECT MAX(display_order) as max_order 
            FROM carte_images 
            WHERE admin_id = ?
        ");
        $stmt->execute([$adminId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $nextOrder = ($result['max_order'] ?? 0) + 1;

        $stmt = $this->pdo->prepare("
            INSERT INTO carte_images (admin_id, filename, original_name, display_order) 
            VALUES (?, ?, ?, ?)
        ");
        return $stmt->execute([$adminId, $filename, $originalName, $nextOrder]);
    }

    public function delete($id, $adminId)
    {
        $stmt = $this->pdo->prepare("
            DELETE FROM carte_images 
            WHERE id = ? AND admin_id = ?
        ");
        return $stmt->execute([$id, $adminId]);
    }

    public function getById($id, $adminId)
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM carte_images 
            WHERE id = ? AND admin_id = ?
        ");
        $stmt->execute([$id, $adminId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateOrder($id, $adminId, $order)
    {
        $stmt = $this->pdo->prepare("
            UPDATE carte_images 
            SET display_order = ? 
            WHERE id = ? AND admin_id = ?
        ");
        return $stmt->execute([$order, $id, $adminId]);
    }

    public function reorderImages($adminId)
    {
        $images = $this->getAllByAdmin($adminId);
        foreach ($images as $index => $image) {
            $this->updateOrder($image['id'], $adminId, $index + 1);
        }
    }
}