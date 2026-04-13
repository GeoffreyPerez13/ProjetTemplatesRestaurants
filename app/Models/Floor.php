<?php
/**
 * Modèle Floor - Gestion des étages/salles du restaurant
 */
class Floor
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Créer un nouvel étage
     */
    public function create($admin_id, $name, $display_order = 0)
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO restaurant_floors (admin_id, name, display_order) 
             VALUES (?, ?, ?)"
        );
        $stmt->execute([$admin_id, $name, $display_order]);
        return $this->pdo->lastInsertId();
    }

    /**
     * Récupérer tous les étages d'un admin
     */
    public function getAllByAdmin($admin_id)
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM restaurant_floors 
             WHERE admin_id = ? 
             ORDER BY display_order ASC, id ASC"
        );
        $stmt->execute([$admin_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupérer un étage par ID
     */
    public function findById($id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM restaurant_floors WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Mettre à jour un étage
     */
    public function update($id, $name, $display_order)
    {
        $stmt = $this->pdo->prepare(
            "UPDATE restaurant_floors 
             SET name = ?, display_order = ? 
             WHERE id = ?"
        );
        return $stmt->execute([$name, $display_order, $id]);
    }

    /**
     * Supprimer un étage
     */
    public function delete($id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM restaurant_floors WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Vérifier si un étage appartient à un admin
     */
    public function belongsToAdmin($floor_id, $admin_id)
    {
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) FROM restaurant_floors WHERE id = ? AND admin_id = ?"
        );
        $stmt->execute([$floor_id, $admin_id]);
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Créer un étage par défaut pour un nouvel admin
     */
    public function createDefault($admin_id)
    {
        return $this->create($admin_id, 'Rez-de-chaussée', 0);
    }
}
