<?php
/**
 * Modèle Contact : gestion des informations de contact du restaurant
 * Stocke téléphone, email, adresse et horaires dans la table `contact`
 */
class Contact
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
     * Récupère les informations de contact d'un admin
     *
     * @param int $admin_id ID de l'admin
     * @return array|false Données contact ou false
     */
    public function getByAdmin($admin_id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM contact WHERE admin_id = ?");
        $stmt->execute([$admin_id]);
        return $stmt->fetch(); // Retourne un tableau associatif avec les informations de contact
    }

    /**
     * Met à jour les informations de contact
     *
     * @param int    $admin_id  ID de l'admin
     * @param string $telephone Numéro de téléphone
     * @param string $email     Adresse email
     * @param string $adresse   Adresse postale
     * @param string $horaires  Horaires d'ouverture
     * @return bool Succès
     */
    public function update($admin_id, $telephone, $email, $adresse, $horaires)
    {
        $stmt = $this->pdo->prepare(
            "UPDATE contact SET telephone = ?, email = ?, adresse = ?, horaires = ? WHERE admin_id = ?"
        );
        return $stmt->execute([$telephone, $email, $adresse, $horaires, $admin_id]);
    }

    /**
     * Crée une ligne de contact vide si aucune n'existe pour cet admin
     *
     * @param int $admin_id ID de l'admin
     */
    public function createIfNotExist($admin_id)
    {
        // Vérifie si une ligne existe déjà
        $stmt = $this->pdo->prepare("SELECT id FROM contact WHERE admin_id = ?");
        $stmt->execute([$admin_id]);
        if (!$stmt->fetch()) {
            // Crée une ligne vide pour l'admin
            $stmt = $this->pdo->prepare(
                "INSERT INTO contact (admin_id, telephone, email, adresse, horaires) VALUES (?, '', '', '', '')"
            );
            $stmt->execute([$admin_id]);
        }
    }

    /**
     * Récupère le contact d'un restaurant via son ID (front-office)
     *
     * @param int $restaurantId ID du restaurant
     * @return array|false Données contact ou false
     */
    public function getByRestaurant($restaurantId)
    {
        $stmt = $this->pdo->prepare("
        SELECT c.* FROM contact c
        JOIN admins a ON a.id = c.admin_id
        WHERE a.restaurant_id = ?
        LIMIT 1
    ");
        $stmt->execute([$restaurantId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
