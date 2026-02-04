<?php
class Restaurant
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function findBySlug($slug)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM restaurants WHERE slug = ? LIMIT 1");
        $stmt->execute([$slug]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    /**
     * Met à jour la date de modification du restaurant
     * @param int $restaurantId ID du restaurant
     * @return bool Succès de la mise à jour
     */
    public function updateTimestamp($restaurantId)
    {
        $stmt = $this->pdo->prepare("UPDATE restaurants SET updated_at = NOW() WHERE id = ?");
        return $stmt->execute([$restaurantId]);
    }

    /**
     * Récupère la dernière date de mise à jour du restaurant
     * @param int $restaurantId ID du restaurant
     * @return string|null Date de mise à jour
     */
    public function getLastUpdate($restaurantId)
    {
        $stmt = $this->pdo->prepare("SELECT updated_at FROM restaurants WHERE id = ? LIMIT 1");
        $stmt->execute([$restaurantId]);
        $result = $stmt->fetch(PDO::FETCH_OBJ);
        return $result ? $result->updated_at : null;
    }
}