<?php
// Classe Category : gère les catégories de plats pour un restaurant/admin
class Category
{
    // Connexion PDO à la base de données
    private $pdo;

    // Propriétés publiques représentant une catégorie
    public $id;
    public $admin_id;
    public $name;
    public $image;

    // Constructeur : initialise la connexion PDO
    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    // --- Création d'une catégorie ---
    public function create($admin_id, $name, $image = null)
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO categories (admin_id, name, image) VALUES (?, ?, ?)"
        );
        $stmt->execute([$admin_id, $name, $image]);

        // Met à jour les propriétés de l'objet avec la catégorie créée
        $this->id = $this->pdo->lastInsertId();
        $this->admin_id = $admin_id;
        $this->name = $name;
        $this->image = $image;

        return $this;
    }

    // --- Mise à jour d'une catégorie ---
    public function update($id, $name, $image = null)
    {
        $sql = "UPDATE categories SET name = ?";
        $params = [$name];

        // Si une image est fournie, on l'ajoute à la requête
        if ($image !== null) {
            $sql .= ", image = ?";
            $params[] = $image;
        }

        $sql .= " WHERE id = ?";
        $params[] = $id;

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    // --- Récupérer toutes les catégories d'un admin (back-office) ---
    public function getAllByAdmin($admin_id)
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM categories WHERE admin_id = ? ORDER BY id ASC"
        );
        $stmt->execute([$admin_id]);
        return $stmt->fetchAll(); // Retourne un tableau associatif
    }

    // --- Suppression d'une catégorie ---
    public function delete($id, $admin_id)
    {
        // Supprime d'abord les plats liés à la catégorie
        $stmt = $this->pdo->prepare("DELETE FROM plats WHERE category_id = ?");
        $stmt->execute([$id]);

        // Supprime ensuite la catégorie elle-même
        $stmt = $this->pdo->prepare(
            "DELETE FROM categories WHERE id = ? AND admin_id = ?"
        );
        return $stmt->execute([$id, $admin_id]);
    }

    // --- Récupérer toutes les catégories (global) ---
    public function getAll()
    {
        $stmt = $this->pdo->query("SELECT * FROM categories");
        return $stmt->fetchAll();
    }

    // --- Récupérer les catégories d'un restaurant (front-office) ---
    public function getByRestaurant($restaurantId)
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM categories WHERE restaurant_id = ? ORDER BY sort_order ASC, id ASC"
        );
        $stmt->execute([$restaurantId]);
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
     * Get the value of admin_id
     */
    public function getAdmin_id()
    {
        return $this->admin_id;
    }

    /**
     * Set the value of admin_id
     *
     * @return  self
     */
    public function setAdmin_id($admin_id)
    {
        $this->admin_id = $admin_id;

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
     * Get the value of image
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * Set the value of image
     *
     * @return  self
     */
    public function setImage($image)
    {
        $this->image = $image;

        return $this;
    }
}
