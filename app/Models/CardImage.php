<?php

class CardImage
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Récupère toutes les images d'un administrateur
     */
    public function getAllByAdmin($adminId)
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM card_images 
            WHERE admin_id = ? 
            ORDER BY display_order ASC, created_at DESC
        ");
        $stmt->execute([$adminId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Ajoute une nouvelle image
     */
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

    /**
     * Supprime une image
     */
    public function delete($id, $adminId)
    {
        $stmt = $this->pdo->prepare("
            DELETE FROM card_images 
            WHERE id = ? AND admin_id = ?
        ");
        return $stmt->execute([$id, $adminId]);
    }

    /**
     * Récupère une image par son ID
     */
    public function getById($id, $adminId)
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM card_images 
            WHERE id = ? AND admin_id = ?
        ");
        $stmt->execute([$id, $adminId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Met à jour l'ordre d'une image spécifique
     */
    public function updateOrder($id, $adminId, $order)
    {
        $stmt = $this->pdo->prepare("
            UPDATE card_images 
            SET display_order = ?, updated_at = NOW()
            WHERE id = ? AND admin_id = ?
        ");
        return $stmt->execute([$order, $id, $adminId]);
    }

    /**
     * Réorganise automatiquement les images (par ordre alphabétique)
     */
    public function reorderImages($adminId)
    {
        try {
            $images = $this->getAllByAdmin($adminId);

            // Trier par nom original
            usort($images, function ($a, $b) {
                return strcmp($a['original_name'], $b['original_name']);
            });

            $this->pdo->beginTransaction();

            $order = 1;
            foreach ($images as $image) {
                $stmt = $this->pdo->prepare("
                    UPDATE card_images 
                    SET display_order = ?, updated_at = NOW() 
                    WHERE id = ? AND admin_id = ?
                ");
                $stmt->execute([$order, $image['id'], $adminId]);
                $order++;
            }

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw new Exception("Erreur lors de la réorganisation des images : " . $e->getMessage());
        }
    }

    /**
     * NOUVELLE MÉTHODE : Met à jour l'ordre des images selon un tableau spécifique
     */
    public function updateImageOrder($adminId, $imageOrder)
    {
        try {
            // Vérifier que le tableau contient des IDs valides
            if (!is_array($imageOrder) || empty($imageOrder)) {
                throw new Exception("Tableau d'ordre invalide");
            }

            // Vérifier que toutes les images appartiennent à cet admin
            $allImages = $this->getAllByAdmin($adminId);
            $validImageIds = array_map(function ($img) {
                return (int)$img['id'];
            }, $allImages);

            $filteredOrder = [];
            foreach ($imageOrder as $imageId) {
                $imageId = (int)$imageId;
                if (in_array($imageId, $validImageIds)) {
                    $filteredOrder[] = $imageId;
                }
            }

            if (empty($filteredOrder)) {
                throw new Exception("Aucun ID d'image valide trouvé");
            }

            // Commencer la transaction
            $this->pdo->beginTransaction();

            // Réinitialiser tous les ordres à 0 d'abord
            $resetStmt = $this->pdo->prepare("
                UPDATE card_images 
                SET display_order = 0 
                WHERE admin_id = ?
            ");
            $resetStmt->execute([$adminId]);

            // Mettre à jour chaque image avec son nouvel ordre
            $order = 1;
            foreach ($filteredOrder as $imageId) {
                $updateStmt = $this->pdo->prepare("
                    UPDATE card_images 
                    SET display_order = ?, updated_at = NOW() 
                    WHERE id = ? AND admin_id = ?
                ");
                $updateStmt->execute([$order, $imageId, $adminId]);
                $order++;
            }

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log("Erreur updateImageOrder: " . $e->getMessage());
            throw new Exception("Erreur lors de la mise à jour de l'ordre des images : " . $e->getMessage());
        }
    }

    /**
     * Télécharge une image sur le serveur
     */
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

        // Retourner le chemin relatif (sans le point de départ)
        return $targetPath;
    }

    /**
     * Supprime un fichier image du serveur
     */
    public function deleteImageFile($filepath)
    {
        if (file_exists($filepath) && is_file($filepath)) {
            return unlink($filepath);
        }
        return false;
    }

    /**
     * Récupère l'ordre maximum actuel
     */
    public function getMaxOrder($adminId)
    {
        $stmt = $this->pdo->prepare("
            SELECT MAX(display_order) as max_order 
            FROM card_images 
            WHERE admin_id = ?
        ");
        $stmt->execute([$adminId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['max_order'] ?? 0;
    }

    /**
     * Met à jour le nom d'une image
     */
    public function updateName($id, $adminId, $newName)
    {
        $stmt = $this->pdo->prepare("
            UPDATE card_images 
            SET original_name = ?, updated_at = NOW() 
            WHERE id = ? AND admin_id = ?
        ");
        return $stmt->execute([$newName, $id, $adminId]);
    }
}
