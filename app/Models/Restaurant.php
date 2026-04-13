<?php

/**
 * Modèle Restaurant : accès aux données du restaurant et de ses ressources associées
 * Utilisé principalement par DisplayController pour la page vitrine publique
 */
class Restaurant
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
     * Recherche un restaurant par son slug unique
     *
     * @param string $slug Slug URL du restaurant
     * @return object|false Restaurant ou false
     */
    public function findBySlug($slug)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM restaurants WHERE slug = ? LIMIT 1");
        $stmt->execute([$slug]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    /**
     * Met à jour le champ updated_at du restaurant
     *
     * @param int $restaurantId ID du restaurant
     * @return bool Succès
     */
    public function updateTimestamp($restaurantId)
    {
        $stmt = $this->pdo->prepare("UPDATE restaurants SET updated_at = NOW() WHERE id = ?");
        return $stmt->execute([$restaurantId]);
    }

    /**
     * Récupère la date de dernière mise à jour
     *
     * @param int $restaurantId ID du restaurant
     * @return string|null Date au format MySQL ou null
     */
    public function getLastUpdate($restaurantId)
    {
        $stmt = $this->pdo->prepare("SELECT updated_at FROM restaurants WHERE id = ? LIMIT 1");
        $stmt->execute([$restaurantId]);
        $result = $stmt->fetch(PDO::FETCH_OBJ);
        return $result ? $result->updated_at : null;
    }

    // ==================== NOUVELLES MÉTHODES POUR LA VITRINE ====================

    /**
     * Récupère l'admin associé à un restaurant
     *
     * @param int $restaurantId ID du restaurant
     * @return array|false Données admin ou false
     */
    public function getAdminByRestaurantId($restaurantId)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM admins WHERE restaurant_id = ? LIMIT 1");
        $stmt->execute([$restaurantId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère le logo du restaurant avec son URL publique
     *
     * @param int $adminId ID de l'admin
     * @return array|null Données logo avec 'url' ou null
     */
    public function getLogo($adminId)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM logos WHERE admin_id = ?");
        $stmt->execute([$adminId]);
        $logo = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($logo) {
            $logo['url'] = '/assets/logos/' . $logo['filename'];
        }
        return $logo;
    }

    /**
     * Récupère la bannière du restaurant avec son URL publique
     *
     * @param int $adminId ID de l'admin
     * @return array|null Données bannière avec 'url' ou null
     */
    public function getBanner($adminId)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM banners WHERE admin_id = ?");
        $stmt->execute([$adminId]);
        $banner = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($banner) {
            $banner['url'] = '/assets/banners/' . $banner['filename'];
        }
        return $banner;
    }

    /**
     * Récupère les catégories avec leurs plats et images pour la vitrine
     *
     * @param int $adminId ID de l'admin
     * @return array Catégories avec sous-clé 'plats' et 'image_url'
     */
    public function getCategoriesWithPlats($adminId)
    {
        $stmt = $this->pdo->prepare("
            SELECT c.*, 
                   (SELECT COUNT(*) FROM plats WHERE category_id = c.id) as plats_count
            FROM categories c
            WHERE c.admin_id = ?
            ORDER BY c.id ASC
        ");
        $stmt->execute([$adminId]);
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($categories as &$category) {
            $stmtPlats = $this->pdo->prepare("
                SELECT * FROM plats 
                WHERE category_id = ? 
                ORDER BY id ASC
            ");
            $stmtPlats->execute([$category['id']]);
            $plats = $stmtPlats->fetchAll(PDO::FETCH_ASSOC);
            foreach ($plats as &$plat) {
                if (!empty($plat['image'])) {
                    $plat['image_url'] = '/' . $plat['image'];
                }
            }
            $category['plats'] = $plats;

            if (!empty($category['image'])) {
                $category['image_url'] = '/' . $category['image'];
            }
        }

        return $categories;
    }

    /**
     * Récupère les images de la carte (mode images) triées par ordre d'affichage
     *
     * @param int $adminId ID de l'admin
     * @return array Images avec 'url'
     */
    public function getCardImages($adminId)
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM card_images 
            WHERE admin_id = ? 
            ORDER BY display_order ASC, id ASC
        ");
        $stmt->execute([$adminId]);
        $images = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($images as &$image) {
            $image['url'] = '/' . $image['filename'];
        }
        return $images;
    }

    /**
     * Récupère les informations de contact du restaurant
     *
     * @param int $adminId ID de l'admin
     * @return array|false Données contact ou false
     */
    public function getContact($adminId)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM contact WHERE admin_id = ?");
        $stmt->execute([$adminId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Vérifie si le site vitrine est en ligne
     *
     * @param int $adminId ID de l'admin
     * @return bool true si l'option site_online vaut '1'
     */
    public function isSiteOnline($adminId)
    {
        $stmt = $this->pdo->prepare("
            SELECT option_value FROM admin_options 
            WHERE admin_id = ? AND option_name = 'site_online'
        ");
        $stmt->execute([$adminId]);
        $value = $stmt->fetchColumn();
        return $value === '1';
    }
}