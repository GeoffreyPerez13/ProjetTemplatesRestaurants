<?php
// Classe Contact : gère les informations de contact d'un restaurant/admin
class Contact
{
    // Connexion PDO à la base de données
    private $pdo;

    // Constructeur : initialise la connexion PDO
    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    // --- Récupérer le contact pour un admin spécifique ---
    public function getByAdmin($admin_id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM contact WHERE admin_id = ?");
        $stmt->execute([$admin_id]);
        return $stmt->fetch(); // Retourne un tableau associatif avec les informations de contact
    }

    // --- Mettre à jour les informations de contact pour un admin ---
    public function update($admin_id, $telephone, $email, $adresse, $horaires)
    {
        $stmt = $this->pdo->prepare(
            "UPDATE contact SET telephone = ?, email = ?, adresse = ?, horaires = ? WHERE admin_id = ?"
        );
        return $stmt->execute([$telephone, $email, $adresse, $horaires, $admin_id]);
    }

    // --- Créer une ligne de contact si elle n'existe pas pour l'admin ---
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

    // --- Récupérer le contact pour un restaurant public ---
    public function getByRestaurant($restaurantId)
    {
        // Limité à 1 ligne, renvoie un objet avec les informations
        $stmt = $this->pdo->prepare("SELECT * FROM contacts WHERE restaurant_id = ? LIMIT 1");
        $stmt->execute([$restaurantId]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }
}