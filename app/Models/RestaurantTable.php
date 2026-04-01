<?php
/**
 * Modèle RestaurantTable - Gestion des tables du restaurant
 */
class RestaurantTable
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Créer une nouvelle table
     */
    public function create($floor_id, $table_number, $shape, $capacity_min, $capacity_max, $position_x = 0, $position_y = 0, $width = 60, $height = 60, $zone = null)
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO restaurant_tables 
             (floor_id, table_number, shape, capacity_min, capacity_max, position_x, position_y, width, height, zone) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([$floor_id, $table_number, $shape, $capacity_min, $capacity_max, $position_x, $position_y, $width, $height, $zone]);
        return $this->pdo->lastInsertId();
    }

    /**
     * Récupérer toutes les tables d'un étage
     */
    public function getAllByFloor($floor_id)
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM restaurant_tables 
             WHERE floor_id = ? AND is_active = 1 
             ORDER BY table_number ASC"
        );
        $stmt->execute([$floor_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupérer toutes les tables d'un admin
     */
    public function getAllByAdmin($admin_id)
    {
        $stmt = $this->pdo->prepare(
            "SELECT rt.* FROM restaurant_tables rt
             INNER JOIN restaurant_floors rf ON rt.floor_id = rf.id
             WHERE rf.admin_id = ? AND rt.is_active = 1
             ORDER BY rf.display_order ASC, rt.table_number ASC"
        );
        $stmt->execute([$admin_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupérer une table par ID
     */
    public function findById($id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM restaurant_tables WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Mettre à jour une table
     */
    public function update($id, $data)
    {
        $fields = [];
        $values = [];
        
        $allowed = ['table_number', 'shape', 'capacity_min', 'capacity_max', 'position_x', 'position_y', 'width', 'height', 'zone', 'is_active'];
        
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
        $sql = "UPDATE restaurant_tables SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($values);
    }

    /**
     * Mettre à jour la position d'une table
     */
    public function updatePosition($id, $position_x, $position_y)
    {
        $stmt = $this->pdo->prepare(
            "UPDATE restaurant_tables SET position_x = ?, position_y = ? WHERE id = ?"
        );
        return $stmt->execute([$position_x, $position_y, $id]);
    }

    /**
     * Supprimer une table
     */
    public function delete($id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM restaurant_tables WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Supprimer toutes les tables d'un étage
     */
    public function deleteAllByFloor($floor_id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM restaurant_tables WHERE floor_id = ?");
        return $stmt->execute([$floor_id]);
    }

    /**
     * Vérifier si une table appartient à un admin
     */
    public function belongsToAdmin($table_id, $admin_id)
    {
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) FROM restaurant_tables rt
             INNER JOIN restaurant_floors rf ON rt.floor_id = rf.id
             WHERE rt.id = ? AND rf.admin_id = ?"
        );
        $stmt->execute([$table_id, $admin_id]);
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Récupérer les tables disponibles pour une capacité donnée
     */
    public function getAvailableForCapacity($admin_id, $party_size)
    {
        $stmt = $this->pdo->prepare(
            "SELECT rt.* FROM restaurant_tables rt
             INNER JOIN restaurant_floors rf ON rt.floor_id = rf.id
             WHERE rf.admin_id = ? 
             AND rt.is_active = 1
             AND rt.capacity_min <= ? 
             AND rt.capacity_max >= ?
             ORDER BY rt.capacity_max ASC"
        );
        $stmt->execute([$admin_id, $party_size, $party_size]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
