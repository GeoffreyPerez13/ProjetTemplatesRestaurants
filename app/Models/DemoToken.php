<?php

/**
 * Modèle DemoToken : gestion des tokens de démonstration temporaires
 * Chaque token génère un clone complet du restaurant de démo (isolation totale)
 * Permet à plusieurs clients potentiels d'utiliser la démo simultanément
 */
class DemoToken
{
    /** @var PDO Connexion à la base de données */
    private $pdo;

    /** @var int Durée de validité d'un token en jours */
    const EXPIRY_DAYS = 3;

    /** @var string Slug du restaurant template de démo */
    const DEMO_SLUG = 'demo-menumiam';

    /**
     * @param PDO $pdo Connexion à la base de données
     */
    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Génère un nouveau token de démo avec un clone complet du restaurant
     *
     * @param int $createdBy ID du SUPER_ADMIN qui génère le lien
     * @return array|false Token créé avec ses données, ou false si erreur
     */
    public function generate($createdBy)
    {
        $sourceAdminId = $this->getDemoAdminId();
        if (!$sourceAdminId) {
            return false;
        }

        try {
            $this->pdo->beginTransaction();

            // Générer un suffixe unique pour ce clone
            $suffix = substr(bin2hex(random_bytes(4)), 0, 8);
            $token = bin2hex(random_bytes(32));
            $expiresAt = date('Y-m-d H:i:s', strtotime('+' . self::EXPIRY_DAYS . ' days'));

            // 1. Cloner le restaurant
            $cloneSlug = self::DEMO_SLUG . '-' . $suffix;
            $stmt = $this->pdo->prepare("SELECT * FROM restaurants WHERE slug = ?");
            $stmt->execute([self::DEMO_SLUG]);
            $srcRestaurant = $stmt->fetch(PDO::FETCH_ASSOC);

            $stmt = $this->pdo->prepare("
                INSERT INTO restaurants (name, slug, created_at, updated_at)
                VALUES (?, ?, NOW(), NOW())
            ");
            $stmt->execute([$srcRestaurant['name'], $cloneSlug]);
            $cloneRestaurantId = $this->pdo->lastInsertId();

            // 2. Cloner l'admin
            $stmt = $this->pdo->prepare("SELECT * FROM admins WHERE id = ?");
            $stmt->execute([$sourceAdminId]);
            $srcAdmin = $stmt->fetch(PDO::FETCH_ASSOC);

            $cloneUsername = 'demo_' . $suffix;
            $stmt = $this->pdo->prepare("
                INSERT INTO admins (username, email, password, restaurant_name, restaurant_id, carte_mode, role, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, 'ADMIN', NOW(), NOW())
            ");
            $stmt->execute([
                $cloneUsername,
                'demo_' . $suffix . '@menumiam.com',
                $srcAdmin['password'],
                $srcAdmin['restaurant_name'],
                $cloneRestaurantId,
                $srcAdmin['carte_mode'],
            ]);
            $cloneAdminId = $this->pdo->lastInsertId();

            // 3. Cloner le contact
            $stmt = $this->pdo->prepare("SELECT * FROM contact WHERE admin_id = ?");
            $stmt->execute([$sourceAdminId]);
            $srcContact = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($srcContact) {
                $stmt = $this->pdo->prepare("
                    INSERT INTO contact (admin_id, telephone, email, adresse, horaires, updated_at)
                    VALUES (?, ?, ?, ?, ?, NOW())
                ");
                $stmt->execute([
                    $cloneAdminId,
                    $srcContact['telephone'],
                    $srcContact['email'],
                    $srcContact['adresse'],
                    $srcContact['horaires'],
                ]);
            }

            // 4. Cloner les catégories et plats
            $stmt = $this->pdo->prepare("SELECT * FROM categories WHERE admin_id = ?");
            $stmt->execute([$sourceAdminId]);
            $srcCategories = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $stmtInsertCat = $this->pdo->prepare("
                INSERT INTO categories (admin_id, name, image, created_at, updated_at)
                VALUES (?, ?, ?, NOW(), NOW())
            ");
            $stmtGetPlats = $this->pdo->prepare("SELECT * FROM plats WHERE category_id = ?");
            $stmtInsertPlat = $this->pdo->prepare("
                INSERT INTO plats (category_id, name, description, price, image, created_at)
                VALUES (?, ?, ?, ?, ?, NOW())
            ");

            foreach ($srcCategories as $cat) {
                $stmtInsertCat->execute([$cloneAdminId, $cat['name'], $cat['image'] ?? null]);
                $cloneCatId = $this->pdo->lastInsertId();

                $stmtGetPlats->execute([$cat['id']]);
                $srcPlats = $stmtGetPlats->fetchAll(PDO::FETCH_ASSOC);
                foreach ($srcPlats as $plat) {
                    $stmtInsertPlat->execute([
                        $cloneCatId,
                        $plat['name'],
                        $plat['description'],
                        $plat['price'],
                        $plat['image'] ?? null,
                    ]);
                }
            }

            // 5. Cloner les options (admin_options)
            $stmt = $this->pdo->prepare("SELECT option_name, option_value FROM admin_options WHERE admin_id = ?");
            $stmt->execute([$sourceAdminId]);
            $srcOptions = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $stmtInsertOpt = $this->pdo->prepare("
                INSERT INTO admin_options (admin_id, option_name, option_value, created_at, updated_at)
                VALUES (?, ?, ?, NOW(), NOW())
            ");
            foreach ($srcOptions as $opt) {
                $stmtInsertOpt->execute([$cloneAdminId, $opt['option_name'], $opt['option_value']]);
            }

            // 6. Créer le token
            $stmt = $this->pdo->prepare("
                INSERT INTO demo_tokens (token, admin_id, expires_at, created_by)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$token, $cloneAdminId, $expiresAt, $createdBy]);
            $tokenId = $this->pdo->lastInsertId();

            $this->pdo->commit();

            return [
                'id' => $tokenId,
                'token' => $token,
                'admin_id' => $cloneAdminId,
                'expires_at' => $expiresAt,
                'slug' => $cloneSlug,
            ];
        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log("Erreur génération démo clone: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Valide un token et retourne ses données s'il est encore valide
     *
     * @param string $token Token à valider
     * @return array|false Données du token ou false si invalide/expiré
     */
    public function validate($token)
    {
        $stmt = $this->pdo->prepare("
            SELECT dt.*, r.slug AS demo_slug
            FROM demo_tokens dt
            JOIN admins a ON a.id = dt.admin_id
            JOIN restaurants r ON r.id = a.restaurant_id
            WHERE dt.token = ? AND dt.expires_at > NOW()
        ");
        $stmt->execute([$token]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère tous les tokens actifs (non expirés)
     *
     * @return array Liste des tokens actifs
     */
    public function getActiveTokens()
    {
        $stmt = $this->pdo->query("
            SELECT dt.*, a.username AS created_by_name
            FROM demo_tokens dt
            LEFT JOIN admins a ON a.id = dt.created_by
            WHERE dt.expires_at > NOW()
            ORDER BY dt.created_at DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Met à jour le label (nom du destinataire) d'un token
     *
     * @param int $id ID du token
     * @param string $label Nom du destinataire
     * @return bool Succès
     */
    public function updateLabel($id, $label)
    {
        $stmt = $this->pdo->prepare("UPDATE demo_tokens SET label = ? WHERE id = ?");
        return $stmt->execute([trim($label), $id]);
    }

    /**
     * Supprime un token et toutes les données clonées associées
     *
     * @param int $id ID du token
     * @return bool Succès
     */
    public function delete($id)
    {
        // Récupérer le token pour trouver l'admin cloné
        $stmt = $this->pdo->prepare("SELECT admin_id FROM demo_tokens WHERE id = ?");
        $stmt->execute([$id]);
        $adminId = $stmt->fetchColumn();

        // Supprimer le token
        $stmt = $this->pdo->prepare("DELETE FROM demo_tokens WHERE id = ?");
        $stmt->execute([$id]);

        // Nettoyer les données clonées
        if ($adminId) {
            $this->deleteCloneData($adminId);
        }

        return true;
    }

    /**
     * Supprime tous les tokens expirés et leurs données clonées
     *
     * @return int Nombre de tokens nettoyés
     */
    public function cleanExpired()
    {
        // Récupérer les admin_ids des tokens expirés
        $stmt = $this->pdo->query("SELECT admin_id FROM demo_tokens WHERE expires_at <= NOW()");
        $expiredAdminIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // Supprimer les tokens expirés
        $stmt = $this->pdo->prepare("DELETE FROM demo_tokens WHERE expires_at <= NOW()");
        $stmt->execute();
        $count = $stmt->rowCount();

        // Nettoyer les données clonées
        foreach ($expiredAdminIds as $adminId) {
            $this->deleteCloneData($adminId);
        }

        return $count;
    }

    /**
     * Supprime toutes les données d'un admin cloné (restaurant, plats, catégories, etc.)
     * Ne supprime PAS le restaurant template (DEMO_SLUG)
     *
     * @param int $adminId ID de l'admin cloné
     */
    private function deleteCloneData($adminId)
    {
        try {
            // Vérifier que ce n'est pas l'admin template
            $stmt = $this->pdo->prepare("
                SELECT r.slug FROM admins a
                JOIN restaurants r ON r.id = a.restaurant_id
                WHERE a.id = ?
            ");
            $stmt->execute([$adminId]);
            $slug = $stmt->fetchColumn();

            // Ne jamais supprimer le restaurant template
            if (!$slug || $slug === self::DEMO_SLUG) {
                return;
            }

            // Supprimer les plats via catégories
            $stmt = $this->pdo->prepare("SELECT id FROM categories WHERE admin_id = ?");
            $stmt->execute([$adminId]);
            $catIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
            if ($catIds) {
                $placeholders = str_repeat('?,', count($catIds) - 1) . '?';
                $this->pdo->prepare("DELETE FROM plat_allergenes WHERE plat_id IN (SELECT id FROM plats WHERE category_id IN ($placeholders))")->execute($catIds);
                $this->pdo->prepare("DELETE FROM plats WHERE category_id IN ($placeholders)")->execute($catIds);
            }

            // Supprimer les données liées
            $this->pdo->prepare("DELETE FROM categories WHERE admin_id = ?")->execute([$adminId]);
            $this->pdo->prepare("DELETE FROM contact WHERE admin_id = ?")->execute([$adminId]);
            $this->pdo->prepare("DELETE FROM admin_options WHERE admin_id = ?")->execute([$adminId]);
            $this->pdo->prepare("DELETE FROM logos WHERE admin_id = ?")->execute([$adminId]);
            $this->pdo->prepare("DELETE FROM banners WHERE admin_id = ?")->execute([$adminId]);
            $this->pdo->prepare("DELETE FROM card_images WHERE admin_id = ?")->execute([$adminId]);

            // Récupérer restaurant_id avant de supprimer l'admin
            $stmt = $this->pdo->prepare("SELECT restaurant_id FROM admins WHERE id = ?");
            $stmt->execute([$adminId]);
            $restaurantId = $stmt->fetchColumn();

            // Supprimer l'admin
            $this->pdo->prepare("DELETE FROM admins WHERE id = ?")->execute([$adminId]);

            // Supprimer le restaurant cloné
            if ($restaurantId) {
                $this->pdo->prepare("DELETE FROM restaurants WHERE id = ?")->execute([$restaurantId]);
            }
        } catch (Exception $e) {
            error_log("Erreur nettoyage clone démo (admin_id=$adminId): " . $e->getMessage());
        }
    }

    /**
     * Récupère l'ID admin du restaurant template de démo
     *
     * @return int|false ID de l'admin démo ou false
     */
    public function getDemoAdminId()
    {
        $stmt = $this->pdo->prepare("
            SELECT a.id FROM admins a
            JOIN restaurants r ON r.id = a.restaurant_id
            WHERE r.slug = ?
            LIMIT 1
        ");
        $stmt->execute([self::DEMO_SLUG]);
        return $stmt->fetchColumn();
    }

    /**
     * Vérifie si la session démo courante est encore valide
     *
     * @return bool true si la démo est encore active
     */
    public function isSessionValid()
    {
        if (empty($_SESSION['demo_mode']) || empty($_SESSION['demo_token'])) {
            return false;
        }
        return $this->validate($_SESSION['demo_token']) !== false;
    }

    /**
     * Récupère la date d'expiration de la session démo courante
     *
     * @return string|null Date d'expiration formatée ou null
     */
    public function getSessionExpiry()
    {
        if (empty($_SESSION['demo_token'])) {
            return null;
        }
        $data = $this->validate($_SESSION['demo_token']);
        if ($data) {
            $date = new DateTime($data['expires_at']);
            return $date->format('d/m/Y à H:i');
        }
        return null;
    }
}
