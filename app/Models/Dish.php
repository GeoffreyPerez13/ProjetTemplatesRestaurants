<?php
// Classe Dish : gère les plats d'un restaurant
class Dish
{
    // Connexion PDO à la base de données
    private $pdo;

    // Propriétés publiques représentant un plat
    public $id;
    public $category_id;
    public $name;
    public $price;

    // Constructeur : initialise la connexion PDO
    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    // --- Création d'un plat ---
    public function create($category_id, $name, $price)
    {
        $price = floatval($price); // S'assure que le prix est bien un float
        $stmt = $this->pdo->prepare(
            "INSERT INTO plats (category_id, name, price) VALUES (?, ?, ?)"
        );
        $stmt->execute([$category_id, $name, $price]);

        // Remplit les propriétés de l'objet avec les valeurs du nouveau plat
        $this->id = $this->pdo->lastInsertId();
        $this->category_id = $category_id;
        $this->name = $name;
        $this->price = $price;

        return $this;
    }

    // --- Mise à jour d'un plat ---
    public function update($id, $name, $price)
    {
        $price = floatval($price); // S'assure que le prix est un float
        $stmt = $this->pdo->prepare("UPDATE plats SET name = ?, price = ? WHERE id = ?");
        return $stmt->execute([$name, $price, $id]);
    }

    // --- Suppression d'un plat ---
    public function delete($id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM plats WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // --- Récupérer tous les plats d'une catégorie (back-office) ---
    public function getAllByCategory($category_id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM plats WHERE category_id = ?");
        $stmt->execute([$category_id]);
        return $stmt->fetchAll(); // Retourne un tableau associatif
    }

    // --- Formater le prix pour affichage ---
    public function formatPrice($price)
    {
        return number_format($price, 2, ',', '') . '€';
    }

    // --- Récupérer tous les plats d'une catégorie pour la vitrine (front-office) ---
    public function getByCategory($categoryId)
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM dishes WHERE category_id = ? AND restaurant_id = ? ORDER BY id ASC"
        );
        // Note : s'assurer que la table dishes contient la colonne restaurant_id
        $stmt->execute([$categoryId, $_SESSION['current_restaurant_id'] ?? 0]);
        return $stmt->fetchAll(PDO::FETCH_OBJ); // Retourne un tableau d'objets
    }

    /**
     * Get the value of id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the value of id
     *
     * @return  self
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get the value of category_id
     */
    public function getCategory_id()
    {
        return $this->category_id;
    }

    /**
     * Set the value of category_id
     *
     * @return  self
     */
    public function setCategory_id($category_id)
    {
        $this->category_id = $category_id;

        return $this;
    }

    /**
     * Get the value of name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the value of name
     *
     * @return  self
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get the value of price
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set the value of price
     *
     * @return  self
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }
}
