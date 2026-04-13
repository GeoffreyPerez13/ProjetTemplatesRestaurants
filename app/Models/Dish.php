<?php
/**
 * Modèle Dish : CRUD des plats du restaurant
 * Chaque plat appartient à une catégorie et peut avoir une image et des allergènes
 */
class Dish
{
    /** @var PDO Connexion à la base de données */
    private $pdo;

    /** @var int|null ID du plat */
    public $id;
    /** @var int|null FK vers categories.id */
    public $category_id;
    /** @var string|null Nom du plat */
    public $name;
    /** @var float|null Prix en euros */
    public $price;
    /** @var string|null Description du plat */
    public $description;
    /** @var string|null Chemin relatif de l'image */
    public $image;

    /**
     * @param PDO $pdo Connexion à la base de données
     */
    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Crée un nouveau plat
     *
     * @param int         $category_id ID de la catégorie parente
     * @param string      $name        Nom du plat
     * @param float       $price       Prix en euros
     * @param string      $description Description (optionnel)
     * @param string|null $image       Chemin de l'image (optionnel)
     * @return self
     */
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

    /**
     * Met à jour un plat existant
     * Si $image est fourni : nouvelle image ; si null : supprime l'image
     *
     * @param int         $id          ID du plat
     * @param string      $name        Nouveau nom
     * @param float       $price       Nouveau prix
     * @param string      $description Nouvelle description
     * @param string|null $image       Nouveau chemin image ou null pour supprimer
     * @return bool Succès
     */
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

    /**
     * Upload et valide une image de plat (max 2MB, JPEG/PNG/GIF/WebP)
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

    /**
     * Supprime le fichier image physique du serveur (tente plusieurs chemins)
     *
     * @param string|null $imagePath Chemin relatif de l'image
     * @return bool true si supprimé, false sinon
     */
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

    /**
     * Supprime un plat par son ID
     *
     * @param int $id ID du plat
     * @return bool Succès
     */
    public function delete($id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM plats WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Récupère tous les plats d'une catégorie (back-office)
     *
     * @param int $category_id ID de la catégorie
     * @return array
     */
    public function getAllByCategory($category_id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM plats WHERE category_id = ?");
        $stmt->execute([$category_id]);
        return $stmt->fetchAll(); // Retourne un tableau associatif
    }

    /**
     * Formate un prix pour l'affichage (ex: '12,50€')
     *
     * @param float $price Prix brut
     * @return string Prix formaté
     */
    public function formatPrice($price)
    {
        return number_format($price, 2, ',', '') . '€';
    }

    /**
     * Récupère les plats d'une catégorie triés par ID (front-office)
     *
     * @param int $categoryId ID de la catégorie
     * @return array
     */
    public function getByCategory($categoryId)
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM plats WHERE category_id = ? ORDER BY id ASC"
        );
        $stmt->execute([$categoryId]);
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
