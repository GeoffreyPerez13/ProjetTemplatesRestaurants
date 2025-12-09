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
            SELECT * FROM card_images 
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
            FROM card_images 
            WHERE admin_id = ?
        ");
        $stmt->execute([$adminId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $nextOrder = ($result['max_order'] ?? 0) + 1;

        $stmt = $this->pdo->prepare("
            INSERT INTO card_images (admin_id, filename, original_name, display_order) 
            VALUES (?, ?, ?, ?)
        ");
        return $stmt->execute([$adminId, $filename, $originalName, $nextOrder]);
    }

    public function delete($id, $adminId)
    {
        $stmt = $this->pdo->prepare("
            DELETE FROM card_images 
            WHERE id = ? AND admin_id = ?
        ");
        return $stmt->execute([$id, $adminId]);
    }

    public function getById($id, $adminId)
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM card_images 
            WHERE id = ? AND admin_id = ?
        ");
        $stmt->execute([$id, $adminId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateOrder($id, $adminId, $order)
    {
        $stmt = $this->pdo->prepare("
            UPDATE card_images 
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
        return true;
    }

    public function uploadImage($file)
    {
        $uploadDir = 'uploads/carte-images/';

        // Créer le dossier s'il n'existe pas
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Générer un nom de fichier unique
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $filename = uniqid('carte_') . '.' . $extension;
        $targetPath = $uploadDir . $filename;

        // Vérifier le type de fichier
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf'];
        if (!in_array($extension, $allowedExtensions)) {
            throw new Exception("Type de fichier non autorisé. Formats acceptés: JPG, PNG, GIF, WebP, PDF");
        }

        // Vérifier la taille (max 5MB)
        if ($file['size'] > 5 * 1024 * 1024) {
            throw new Exception("Fichier trop volumineux. Taille maximale: 5MB");
        }

        // Déplacer le fichier
        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            throw new Exception("Erreur lors du téléchargement du fichier");
        }

        return $targetPath;
    }

    public function deleteImageFile($filepath)
    {
        if (file_exists($filepath) && is_file($filepath)) {
            return unlink($filepath);
        }
        return false;
    }
}
