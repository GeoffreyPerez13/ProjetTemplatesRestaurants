<?php

/**
 * Seeder de démonstration : crée un restaurant fictif complet
 * Exécuter via : php app/Seeds/DemoSeeder.php
 * Ou via l'admin : ?page=seed-demo (SUPER_ADMIN uniquement)
 */

require_once __DIR__ . '/../../config.php';

class DemoSeeder
{
    private $pdo;
    private $adminId;
    private $restaurantId;

    /** @var string Slug du restaurant de démo */
    const DEMO_SLUG = 'demo-menumiam';

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Vérifie si la démo existe déjà
     */
    public function demoExists()
    {
        $stmt = $this->pdo->prepare("SELECT id FROM restaurants WHERE slug = ?");
        $stmt->execute([self::DEMO_SLUG]);
        return $stmt->fetchColumn() !== false;
    }

    /**
     * Exécute le seeding complet
     */
    public function run()
    {
        if ($this->demoExists()) {
            echo "La démo existe déjà (slug: " . self::DEMO_SLUG . ")\n";
            return false;
        }

        try {
            $this->pdo->beginTransaction();

            $this->createRestaurant();
            $this->createAdmin();
            $this->createContact();
            $this->createCategories();
            $this->createOptions();

            $this->pdo->commit();
            echo "Démo créée avec succès ! Slug: " . self::DEMO_SLUG . "\n";
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            echo "Erreur: " . $e->getMessage() . "\n";
            return false;
        }
    }

    /**
     * Supprime toutes les données de démo
     */
    public function clean()
    {
        try {
            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare("SELECT id FROM restaurants WHERE slug = ?");
            $stmt->execute([self::DEMO_SLUG]);
            $restaurantId = $stmt->fetchColumn();

            if (!$restaurantId) {
                echo "Aucune démo à supprimer.\n";
                return false;
            }

            // Récupérer l'admin
            $stmt = $this->pdo->prepare("SELECT id FROM admins WHERE restaurant_id = ?");
            $stmt->execute([$restaurantId]);
            $adminId = $stmt->fetchColumn();

            if ($adminId) {
                // Supprimer les plats via catégories
                $stmt = $this->pdo->prepare("SELECT id FROM categories WHERE admin_id = ?");
                $stmt->execute([$adminId]);
                $catIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
                if ($catIds) {
                    $placeholders = str_repeat('?,', count($catIds) - 1) . '?';
                    $this->pdo->prepare("DELETE FROM plat_allergenes WHERE plat_id IN (SELECT id FROM plats WHERE category_id IN ($placeholders))")->execute($catIds);
                    $this->pdo->prepare("DELETE FROM plats WHERE category_id IN ($placeholders)")->execute($catIds);
                }

                $this->pdo->prepare("DELETE FROM categories WHERE admin_id = ?")->execute([$adminId]);
                $this->pdo->prepare("DELETE FROM contact WHERE admin_id = ?")->execute([$adminId]);
                $this->pdo->prepare("DELETE FROM admin_options WHERE admin_id = ?")->execute([$adminId]);
                $this->pdo->prepare("DELETE FROM logos WHERE admin_id = ?")->execute([$adminId]);
                $this->pdo->prepare("DELETE FROM banners WHERE admin_id = ?")->execute([$adminId]);
                $this->pdo->prepare("DELETE FROM card_images WHERE admin_id = ?")->execute([$adminId]);
                $this->pdo->prepare("DELETE FROM admins WHERE id = ?")->execute([$adminId]);
            }

            $this->pdo->prepare("DELETE FROM restaurants WHERE id = ?")->execute([$restaurantId]);

            $this->pdo->commit();
            echo "Démo supprimée avec succès.\n";
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            echo "Erreur: " . $e->getMessage() . "\n";
            return false;
        }
    }

    private function createRestaurant()
    {
        $stmt = $this->pdo->prepare("INSERT INTO restaurants (name, slug, created_at, updated_at) VALUES (?, ?, NOW(), NOW())");
        $stmt->execute(['Le Bistrot MenuMiam', self::DEMO_SLUG]);
        $this->restaurantId = $this->pdo->lastInsertId();
    }

    private function createAdmin()
    {
        $hash = password_hash('demo_password_not_for_login', PASSWORD_DEFAULT);
        $stmt = $this->pdo->prepare("
            INSERT INTO admins (username, email, password, restaurant_name, restaurant_id, carte_mode, role, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, 'editable', 'ADMIN', NOW(), NOW())
        ");
        $stmt->execute(['demo_menumiam', 'demo@menumiam.com', $hash, 'Le Bistrot MenuMiam', $this->restaurantId]);
        $this->adminId = $this->pdo->lastInsertId();
    }

    private function createContact()
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO contact (admin_id, telephone, email, adresse, horaires, updated_at)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $this->adminId,
            '01 23 45 67 89',
            'contact@bistrot-menumiam.fr',
            '42 Rue de la Gastronomie, 75001 Paris',
            "Lundi - Vendredi : 12h00 - 14h30 / 19h00 - 22h30\nSamedi : 12h00 - 15h00 / 19h00 - 23h00\nDimanche : Fermé"
        ]);
    }

    private function createCategories()
    {
        $menu = [
            'Entrées' => [
                ['name' => 'Soupe à l\'oignon gratinée', 'price' => 9.50, 'description' => 'Oignons caramélisés, bouillon maison, croûtons dorés, gruyère fondu'],
                ['name' => 'Salade de chèvre chaud', 'price' => 11.00, 'description' => 'Mesclun, crottin de chèvre sur toast, noix, miel, vinaigrette balsamique'],
                ['name' => 'Terrine de campagne', 'price' => 8.50, 'description' => 'Terrine maison aux herbes, cornichons, pain de campagne toasté'],
                ['name' => 'Velouté de butternut', 'price' => 8.00, 'description' => 'Butternut rôti, crème fraîche, noisettes torréfiées, huile de noisette'],
            ],
            'Plats' => [
                ['name' => 'Entrecôte grillée', 'price' => 22.50, 'description' => 'Entrecôte de bœuf 300g, sauce au poivre, frites maison, salade verte'],
                ['name' => 'Filet de bar rôti', 'price' => 19.00, 'description' => 'Bar de ligne, risotto aux légumes de saison, beurre blanc citronné'],
                ['name' => 'Confit de canard', 'price' => 18.50, 'description' => 'Cuisse de canard confite, pommes sarladaises, salade aux gésiers'],
                ['name' => 'Risotto aux champignons', 'price' => 16.00, 'description' => 'Arborio crémeux, mélange de champignons forestiers, parmesan, truffe'],
                ['name' => 'Burger du Bistrot', 'price' => 15.50, 'description' => 'Bœuf charolais, cheddar affiné, oignons confits, sauce maison, frites'],
            ],
            'Desserts' => [
                ['name' => 'Crème brûlée à la vanille', 'price' => 8.00, 'description' => 'Vanille de Madagascar, caramel croustillant'],
                ['name' => 'Moelleux au chocolat', 'price' => 9.50, 'description' => 'Cœur coulant chocolat noir 70%, glace vanille artisanale'],
                ['name' => 'Tarte Tatin', 'price' => 9.00, 'description' => 'Pommes caramélisées, pâte feuilletée, crème fraîche d\'Isigny'],
                ['name' => 'Assiette de fromages', 'price' => 10.00, 'description' => 'Sélection de 4 fromages affinés, confiture de figues, pain aux noix'],
            ],
            'Boissons' => [
                ['name' => 'Verre de vin rouge (Bordeaux)', 'price' => 6.50, 'description' => 'Château La Tour, AOC Bordeaux 2020'],
                ['name' => 'Verre de vin blanc (Chablis)', 'price' => 7.00, 'description' => 'Domaine Laroche, AOC Chablis 2021'],
                ['name' => 'Bière artisanale pression', 'price' => 5.50, 'description' => 'Bière blonde brassée localement, 33cl'],
                ['name' => 'Eau minérale', 'price' => 3.50, 'description' => 'Evian ou Badoit, 50cl'],
                ['name' => 'Café gourmand', 'price' => 7.50, 'description' => 'Expresso, mini crème brûlée, financier, mousse au chocolat'],
            ],
        ];

        $stmtCat = $this->pdo->prepare("INSERT INTO categories (admin_id, name, created_at, updated_at) VALUES (?, ?, NOW(), NOW())");
        $stmtPlat = $this->pdo->prepare("INSERT INTO plats (category_id, name, price, description, created_at) VALUES (?, ?, ?, ?, NOW())");

        foreach ($menu as $catName => $plats) {
            $stmtCat->execute([$this->adminId, $catName]);
            $catId = $this->pdo->lastInsertId();

            foreach ($plats as $plat) {
                $stmtPlat->execute([$catId, $plat['name'], $plat['price'], $plat['description']]);
            }
        }
    }

    private function createOptions()
    {
        $options = [
            'site_online'                      => '1',
            'site_template'                    => 'classic',
            'mail_reminder'                    => '0',
            'email_notifications'              => '0',
            'service_sur_place'                => '1',
            'service_a_emporter'               => '1',
            'service_livraison_etablissement'  => '0',
            'service_livraison_ubereats'       => '1',
            'service_wifi'                     => '1',
            'service_climatisation'            => '1',
            'service_pmr'                      => '1',
            'payment_visa'                     => '1',
            'payment_mastercard'               => '1',
            'payment_cb'                       => '1',
            'payment_especes'                  => '1',
            'payment_cheques'                  => '0',
            'social_instagram'                 => 'https://instagram.com/menumiam',
            'social_facebook'                  => 'https://facebook.com/menumiam',
        ];

        $stmt = $this->pdo->prepare("
            INSERT INTO admin_options (admin_id, option_name, option_value, created_at, updated_at)
            VALUES (?, ?, ?, NOW(), NOW())
        ");

        foreach ($options as $name => $value) {
            $stmt->execute([$this->adminId, $name, $value]);
        }
    }
}

// Exécution directe en CLI
if (php_sapi_name() === 'cli') {
    $seeder = new DemoSeeder($pdo);
    $action = $argv[1] ?? 'run';

    if ($action === 'clean') {
        $seeder->clean();
    } else {
        $seeder->run();
    }
}
