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
    public $description;
    public $image;

    // Constructeur : initialise la connexion PDO
    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    // --- Création d'un plat ---
    public function create($category_id, $name, $price, $description = '', $image = null)
    {
        $price = floatval($price);
        $stmt = $this->pdo->prepare(
            "INSERT INTO plats (category_id, name, price, description, image) VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->execute([$category_id, $name, $price, $description, $image]);

        $this->id = $this->pdo->lastInsertId();
        $this->category_id = $category_id;
        $this->name = $name;
        $this->price = $price;
        $this->description = $description;
        $this->image = $image;

        return $this;
    }

    // --- Mise à jour d'un plat ---
    public function update($id, $name, $price, $description = '', $image = null)
    {
        $price = floatval($price);

        // Construction dynamique de la requête
        $sql = "UPDATE plats SET name = ?, price = ?, description = ?";
        $params = [$name, $price, $description];

        if ($image !== null) {
            // Si on a une nouvelle image
            $sql .= ", image = ?";
            $params[] = $image;
        } else {
            // Si on veut supprimer l'image (image = NULL)
            $sql .= ", image = NULL";
        }

        $sql .= " WHERE id = ?";
        $params[] = $id;

        $stmt = $this->pdo->prepare($sql);
        $result = $stmt->execute($params);

        return $result;
    }

    // --- Upload d'image pour plat ---
    public function uploadImage($file)
    {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('Erreur lors du téléchargement de l\'image');
        }

        // Validation du type de fichier
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

        // Méthode sans finfo_close() dépréciée
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        try {
            $mimeType = finfo_file($finfo, $file['tmp_name']);
        } finally {
            // Assure la fermeture même en cas d'exception
            if (is_resource($finfo)) {
                finfo_close($finfo);
            }
        }

        if (!in_array($mimeType, $allowedTypes)) {
            throw new Exception('Type de fichier non autorisé. Formats acceptés: JPEG, PNG, GIF, WebP');
        }

        // Validation de la taille (max 2MB)
        if ($file['size'] > 2 * 1024 * 1024) {
            throw new Exception('L\'image ne doit pas dépasser 2MB');
        }

        // Génération d'un nom unique
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid('dish_') . '.' . $extension;
        $uploadPath = __DIR__ . '/../../public/uploads/dishes/' . $filename;

        // Création du dossier si nécessaire
        $uploadDir = dirname($uploadPath);
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
            throw new Exception('Erreur lors de l\'enregistrement de l\'image');
        }

        return 'uploads/dishes/' . $filename;
    }

    // --- Suppression d'image ---
    public function deleteImage($imagePath)
    {
        if ($imagePath) {
            // Plusieurs chemins possibles à tester
            $pathsToTry = [
                __DIR__ . '/../../public/' . $imagePath,
                __DIR__ . '/../../public/' . ltrim($imagePath, '/'),
                $_SERVER['DOCUMENT_ROOT'] . '/' . ltrim($imagePath, '/'),
                $_SERVER['DOCUMENT_ROOT'] . $imagePath
            ];

            foreach ($pathsToTry as $path) {
                if (file_exists($path)) {
                    if (unlink($path)) {
                        return true;
                    } else {
                        return false;
                    }
                }
            }
        }

        return false;
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

    /**
     * Get the value of description
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set the value of description
     *
     * @return  self
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }
}
