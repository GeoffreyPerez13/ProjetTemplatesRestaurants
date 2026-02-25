<?php
/**
 * Modèle Category : CRUD des catégories de plats
 * Chaque catégorie appartient à un admin et peut contenir des plats
 */
class Category
{
    /** @var PDO Connexion à la base de données */
    private $pdo;

    /** @var int|null ID de la catégorie */
    public $id;
    /** @var int|null ID de l'admin propriétaire */
    public $admin_id;
    /** @var string|null Nom de la catégorie */
    public $name;
    /** @var string|null Chemin relatif de l'image (ex: 'uploads/categories/cat_xxx.jpg') */
    public $image;

    /**
     * @param PDO $pdo Connexion à la base de données
     */
    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Crée une nouvelle catégorie
     *
     * @param int         $admin_id ID de l'admin
     * @param string      $name     Nom de la catégorie
     * @param string|null $image    Chemin de l'image (optionnel)
     * @return self
     */
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

    /**
     * Upload et valide une image de catégorie (max 2MB, JPEG/PNG/GIF/WebP)
     *
     * @param array $file Fichier $_FILES
     * @return string Chemin relatif de l'image uploadée
     * @throws Exception Si validation ou déplacement échoue
     */
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

    /**
     * Supprime le fichier image physique du serveur
     *
     * @param string|null $imagePath Chemin relatif de l'image
     */
    public function deleteImage($imagePath)
    {
        if ($imagePath && file_exists(__DIR__ . '/../../public/' . $imagePath)) {
            unlink(__DIR__ . '/../../public/' . $imagePath);
        }
    }

    /**
     * Met à jour une catégorie (nom et/ou image)
     * Si $image est '' : supprime l'image ; si null : conserve l'existante
     *
     * @param int         $id    ID de la catégorie
     * @param string      $name  Nouveau nom
     * @param string|null $image Nouveau chemin image, '' pour supprimer, null pour garder
     * @return bool Succès
     */
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

    /**
     * Récupère toutes les catégories d'un admin (back-office)
     *
     * @param int $admin_id ID de l'admin
     * @return array Liste des catégories
     */
    public function getAllByAdmin($admin_id)
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM categories WHERE admin_id = ? ORDER BY id ASC"
        );
        $stmt->execute([$admin_id]);
        return $stmt->fetchAll(); // Retourne un tableau associatif
    }

    /**
     * Récupère une catégorie par son ID, avec vérification optionnelle du propriétaire
     *
     * @param int      $id       ID de la catégorie
     * @param int|null $admin_id ID de l'admin (si fourni, vérifie l'appartenance)
     * @return array|null Données de la catégorie ou null
     */
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

    /**
     * Supprime une catégorie et ses plats associés
     *
     * @param int $id       ID de la catégorie
     * @param int $admin_id ID de l'admin propriétaire
     * @return bool Succès
     */
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

    /**
     * Récupère toutes les catégories (toutes les admins confondus)
     *
     * @return array
     */
    public function getAll()
    {
        $stmt = $this->pdo->query("SELECT * FROM categories");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère les catégories d'un restaurant via restaurant_id (front-office)
     *
     * @param int $restaurantId ID du restaurant
     * @return array
     */
    public function getByRestaurant($restaurantId)
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM categories WHERE restaurant_id = ? ORDER BY id ASC"
        );
        $stmt->execute([$restaurantId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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
