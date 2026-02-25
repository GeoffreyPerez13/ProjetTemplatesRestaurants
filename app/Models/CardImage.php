<?php

/**
 * Modèle CardImage : gestion des images de carte en mode "images"
 * Permet l'upload, la suppression, le réordonnancement et le renommage des images
 */
class CardImage
{
    /** @var PDO Connexion à la base de données */
    private $pdo;

    /**
     * @param PDO $pdo Connexion à la base de données
     */
    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Récupère toutes les images d'un admin, triées par ordre d'affichage
     *
     * @param int $adminId ID de l'admin
     * @return array Liste des images
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
     * Ajoute une nouvelle image avec un ordre d'affichage auto-incrémenté
     *
     * @param int    $adminId      ID de l'admin
     * @param string $filename     Nom du fichier sur le serveur
     * @param string $originalName Nom original du fichier uploadé
     * @return bool Succès
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
     * Supprime une image de la BDD (vérifie l'appartenance à l'admin)
     *
     * @param int $id      ID de l'image
     * @param int $adminId ID de l'admin
     * @return bool true si supprimée
     */
    public function delete($id, $adminId)
    {
        try {
            $stmt = $this->pdo->prepare("
            DELETE FROM card_images 
            WHERE id = ? AND admin_id = ?
        ");

            $stmt->execute([$id, $adminId]);

            // Vérifier si une ligne a été affectée
            $rowCount = $stmt->rowCount();

            error_log("DEBUG CardImage::delete - Rows affected: $rowCount");

            return $rowCount > 0;
        } catch (Exception $e) {
            error_log("Erreur suppression image BD: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Récupère une image par son ID (vérifie l'appartenance)
     *
     * @param int $id      ID de l'image
     * @param int $adminId ID de l'admin
     * @return array|false Données image ou false
     */
    public function getById($id, $adminId)
    {
        try {
            $stmt = $this->pdo->prepare("
            SELECT * FROM card_images 
            WHERE id = ? AND admin_id = ?
        ");
            $stmt->execute([$id, $adminId]);

            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$result) {
                error_log("Image non trouvée: id=$id, admin=$adminId");
                return false;
            }

            return $result;
        } catch (Exception $e) {
            error_log("Erreur getById: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Met à jour l'ordre d'affichage d'une image
     *
     * @param int $id      ID de l'image
     * @param int $adminId ID de l'admin
     * @param int $order   Nouvel ordre
     * @return bool Succès
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
     * Réorganise automatiquement les images par nom original (alphabétique)
     *
     * @param int $adminId ID de l'admin
     * @return bool Succès
     * @throws Exception Si erreur lors de la transaction
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
     * Met à jour l'ordre des images depuis un tableau d'IDs (drag & drop)
     *
     * @param int   $adminId    ID de l'admin
     * @param array $imageOrder Tableau ordonné d'IDs d'images
     * @return bool Succès
     * @throws Exception Si tableau invalide ou erreur transaction
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
     * Upload une image sur le serveur (max 5MB, JPG/PNG/GIF/WebP/PDF)
     *
     * @param array $file Fichier $_FILES
     * @return string Chemin relatif du fichier uploadé
     * @throws Exception Si validation ou déplacement échoue
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
     * Supprime un fichier image physique du serveur
     *
     * @param string $filepath Chemin du fichier
     * @return bool true si supprimé
     */
    public function deleteImageFile($filepath)
    {
        if (file_exists($filepath) && is_file($filepath)) {
            return unlink($filepath);
        }
        return false;
    }

    /**
     * Récupère l'ordre d'affichage maximum actuel
     *
     * @param int $adminId ID de l'admin
     * @return int Ordre maximum (0 si aucune image)
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
     * Met à jour le nom original d'une image
     *
     * @param int    $id      ID de l'image
     * @param int    $adminId ID de l'admin
     * @param string $newName Nouveau nom
     * @return bool Succès
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
