<?php
/**
 * Modèle RestaurantElement - Gestion des éléments décoratifs (murs, portes, etc.)
 */
class RestaurantElement
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Créer un nouvel élément
     */
    public function create($floor_id, $element_type, $position_x = 0, $position_y = 0, $width = 100, $height = 20, $rotation = 0, $color = '#666666')
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO restaurant_elements 
             (floor_id, element_type, position_x, position_y, width, height, rotation, color) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([$floor_id, $element_type, $position_x, $position_y, $width, $height, $rotation, $color]);
        return $this->pdo->lastInsertId();
    }

    /**
     * Récupérer tous les éléments d'un étage
     */
    public function getAllByFloor($floor_id)
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM restaurant_elements WHERE floor_id = ? ORDER BY id ASC"
        );
        $stmt->execute([$floor_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupérer un élément par ID
     */
    public function findById($id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM restaurant_elements WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Mettre à jour un élément
     */
    public function update($id, $data)
    {
        $fields = [];
        $values = [];
        
        $allowed = ['element_type', 'position_x', 'position_y', 'width', 'height', 'rotation', 'color'];
        
        foreach ($allowed as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = ?";
                $values[] = $data[$field];
            }
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $values[] = $id;
        $sql = "UPDATE restaurant_elements SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($values);
    }

    /**
     * Mettre à jour la position d'un élément
     */
    public function updatePosition($id, $position_x, $position_y)
    {
        $stmt = $this->pdo->prepare(
            "UPDATE restaurant_elements SET position_x = ?, position_y = ? WHERE id = ?"
        );
        return $stmt->execute([$position_x, $position_y, $id]);
    }

    /**
     * Supprimer un élément
     */
    public function delete($id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM restaurant_elements WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Supprimer tous les éléments d'un étage
     */
    public function deleteAllByFloor($floor_id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM restaurant_elements WHERE floor_id = ?");
        return $stmt->execute([$floor_id]);
    }

    /**
     * Vérifier si un élément appartient à un admin
     */
    public function belongsToAdmin($element_id, $admin_id)
    {
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) FROM restaurant_elements re
             INNER JOIN restaurant_floors rf ON re.floor_id = rf.id
             WHERE re.id = ? AND rf.admin_id = ?"
        );
        $stmt->execute([$element_id, $admin_id]);
        return $stmt->fetchColumn() > 0;
    }
}
