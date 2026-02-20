<?php
class Restaurant
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    // Méthodes existantes
    public function findBySlug($slug)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM restaurants WHERE slug = ? LIMIT 1");
        $stmt->execute([$slug]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    public function updateTimestamp($restaurantId)
    {
        $stmt = $this->pdo->prepare("UPDATE restaurants SET updated_at = NOW() WHERE id = ?");
        return $stmt->execute([$restaurantId]);
    }

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
     */
    public function getAdminByRestaurantId($restaurantId)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM admins WHERE restaurant_id = ? LIMIT 1");
        $stmt->execute([$restaurantId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère le logo du restaurant (via admin_id)
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
     * Récupère la bannière du restaurant (via admin_id)
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
     * Récupère les catégories avec leurs plats pour un admin donné
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
     * Récupère les images de la carte (mode images)
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
     * Récupère les informations de contact
     */
    public function getContact($adminId)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM contact WHERE admin_id = ?");
        $stmt->execute([$adminId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Vérifie si le site est en ligne (option site_online)
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