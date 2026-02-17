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

        $this->id = $this->pdo->lastInsertId();
        $this->admin_id = $admin_id;
        $this->name = $name;
        $this->image = $image;

        return $this;
    }

    // --- Upload d'image pour catégorie ---
    public function uploadImage($file)
    {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('Erreur lors du téléchargement de l\'image');
        }

        // Validation du type de fichier
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, $allowedTypes)) {
            throw new Exception('Type de fichier non autorisé. Formats acceptés: JPEG, PNG, GIF, WebP');
        }

        // Validation de la taille (max 2MB)
        if ($file['size'] > 2 * 1024 * 1024) {
            throw new Exception('L\'image ne doit pas dépasser 2MB');
        }

        // Génération d'un nom unique
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid('cat_') . '.' . $extension;
        $uploadPath = __DIR__ . '/../../public/uploads/categories/' . $filename;

        // Création du dossier si nécessaire
        $uploadDir = dirname($uploadPath);
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
            throw new Exception('Erreur lors de l\'enregistrement de l\'image');
        }

        return 'uploads/categories/' . $filename;
    }

    // --- Suppression d'image ---
    public function deleteImage($imagePath)
    {
        if ($imagePath && file_exists(__DIR__ . '/../../public/' . $imagePath)) {
            unlink(__DIR__ . '/../../public/' . $imagePath);
        }
    }

    // --- Mise à jour d'une catégorie ---
    public function update($id, $name, $image = null)
    {
        if ($image === '') {
            // Supprimer l'image (mettre à NULL en base)
            $stmt = $this->pdo->prepare(
                "UPDATE categories SET name = ?, image = NULL WHERE id = ?"
            );
            return $stmt->execute([$name, $id]);
        } elseif ($image !== null) {
            // Mettre à jour avec nouvelle image
            $stmt = $this->pdo->prepare(
                "UPDATE categories SET name = ?, image = ? WHERE id = ?"
            );
            return $stmt->execute([$name, $image, $id]);
        } else {
            // Garder l'image existante, juste changer le nom
            $stmt = $this->pdo->prepare(
                "UPDATE categories SET name = ? WHERE id = ?"
            );
            return $stmt->execute([$name, $id]);
        }
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

    // --- Récupérer une catégorie par son ID ---
    public function getById($id, $admin_id = null)
    {
        if ($admin_id) {
            // Vérifie que la catégorie appartient à l'admin spécifié
            $stmt = $this->pdo->prepare(
                "SELECT * FROM categories WHERE id = ? AND admin_id = ?"
            );
            $stmt->execute([$id, $admin_id]);
        } else {
            // Récupère la catégorie sans vérification d'admin
            $stmt = $this->pdo->prepare("SELECT * FROM categories WHERE id = ?");
            $stmt->execute([$id]);
        }

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            $this->id = $result['id'];
            $this->admin_id = $result['admin_id'];
            $this->name = $result['name'];
            $this->image = $result['image'];

            return $result; // Retourne le tableau associatif
        }

        return null;
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
            "SELECT * FROM categories WHERE restaurant_id = ? ORDER BY id ASC"
        );
        $stmt->execute([$restaurantId]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
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
