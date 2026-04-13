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
            $this->createLogo();
            $this->createBanner();
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
                $basePath = __DIR__ . '/../../public/';

                // Supprimer les fichiers images des plats
                $stmt = $this->pdo->prepare("SELECT id FROM categories WHERE admin_id = ?");
                $stmt->execute([$adminId]);
                $catIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
                if ($catIds) {
                    $placeholders = str_repeat('?,', count($catIds) - 1) . '?';
                    // Supprimer fichiers images plats
                    $stmtImg = $this->pdo->prepare("SELECT image FROM plats WHERE category_id IN ($placeholders) AND image IS NOT NULL");
                    $stmtImg->execute($catIds);
                    foreach ($stmtImg->fetchAll(PDO::FETCH_COLUMN) as $img) {
                        $file = $basePath . $img;
                        if (file_exists($file)) @unlink($file);
                    }
                    $this->pdo->prepare("DELETE FROM plat_allergenes WHERE plat_id IN (SELECT id FROM plats WHERE category_id IN ($placeholders))")->execute($catIds);
                    $this->pdo->prepare("DELETE FROM plats WHERE category_id IN ($placeholders)")->execute($catIds);
                }

                // Supprimer fichiers images catégories
                $stmtCatImg = $this->pdo->prepare("SELECT image FROM categories WHERE admin_id = ? AND image IS NOT NULL");
                $stmtCatImg->execute([$adminId]);
                foreach ($stmtCatImg->fetchAll(PDO::FETCH_COLUMN) as $img) {
                    $file = $basePath . $img;
                    if (file_exists($file)) @unlink($file);
                }

                // Supprimer fichier logo
                $stmtLogo = $this->pdo->prepare("SELECT filename FROM logos WHERE admin_id = ?");
                $stmtLogo->execute([$adminId]);
                $logoFile = $stmtLogo->fetchColumn();
                if ($logoFile && file_exists($basePath . 'assets/logos/' . $logoFile)) {
                    @unlink($basePath . 'assets/logos/' . $logoFile);
                }

                // Supprimer fichier bannière
                $stmtBanner = $this->pdo->prepare("SELECT filename FROM banners WHERE admin_id = ?");
                $stmtBanner->execute([$adminId]);
                $bannerFile = $stmtBanner->fetchColumn();
                if ($bannerFile && file_exists($basePath . 'assets/banners/' . $bannerFile)) {
                    @unlink($basePath . 'assets/banners/' . $bannerFile);
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

        $stmtCat = $this->pdo->prepare("INSERT INTO categories (admin_id, name, image, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())");
        $stmtPlat = $this->pdo->prepare("INSERT INTO plats (category_id, name, price, description, image, created_at) VALUES (?, ?, ?, ?, ?, NOW())");

        // Couleurs par catégorie pour les placeholders
        $catColors = [
            'Entrées'  => ['bg' => [245, 158, 11], 'icon' => 'Entrees'],
            'Plats'     => ['bg' => [220, 38, 38],  'icon' => 'Plats'],
            'Desserts'  => ['bg' => [168, 85, 247],  'icon' => 'Desserts'],
            'Boissons'  => ['bg' => [14, 165, 233],  'icon' => 'Boissons'],
        ];

        $dishIndex = 0;
        foreach ($menu as $catName => $plats) {
            // Générer image placeholder pour la catégorie
            $color = $catColors[$catName] ?? ['bg' => [107, 114, 128], 'icon' => $catName];
            $catImage = $this->generatePlaceholder(600, 400, $color['bg'], $catName, 'uploads/categories/');
            
            $stmtCat->execute([$this->adminId, $catName, $catImage]);
            $catId = $this->pdo->lastInsertId();

            foreach ($plats as $plat) {
                // Générer image placeholder pour le plat (teinte légèrement variée)
                $shade = $this->shadeColor($color['bg'], $dishIndex * 12);
                $dishImage = $this->generatePlaceholder(400, 300, $shade, $plat['name'], 'uploads/dishes/');
                $stmtPlat->execute([$catId, $plat['name'], $plat['price'], $plat['description'], $dishImage]);
                $dishIndex++;
            }
        }
    }

    /**
     * Crée un logo placeholder pour la démo
     */
    private function createLogo()
    {
        $dir = __DIR__ . '/../../public/assets/logos/';
        if (!is_dir($dir)) mkdir($dir, 0755, true);

        $filename = 'logo_demo_' . $this->adminId . '.png';
        $path = $dir . $filename;

        $img = imagecreatetruecolor(300, 300);
        imagesavealpha($img, true);
        $transparent = imagecolorallocatealpha($img, 0, 0, 0, 127);
        imagefill($img, 0, 0, $transparent);

        // Cercle ambré
        $amber = imagecolorallocate($img, 180, 83, 9);
        imagefilledellipse($img, 150, 150, 280, 280, $amber);

        // Texte "B" au centre
        $white = imagecolorallocate($img, 255, 255, 255);
        $fontSize = 80;
        $fontFile = $this->getFont();
        if ($fontFile) {
            $bbox = imagettfbbox($fontSize, 0, $fontFile, 'B');
            $x = 150 - ($bbox[2] - $bbox[0]) / 2;
            $y = 150 + ($bbox[1] - $bbox[7]) / 2;
            imagettftext($img, $fontSize, 0, (int)$x, (int)$y, $white, $fontFile, 'B');
        } else {
            imagestring($img, 5, 140, 140, 'B', $white);
        }

        imagepng($img, $path);
        imagedestroy($img);

        $stmt = $this->pdo->prepare("INSERT INTO logos (admin_id, filename, uploaded_at) VALUES (?, ?, NOW()) ON DUPLICATE KEY UPDATE filename = VALUES(filename), uploaded_at = NOW()");
        $stmt->execute([$this->adminId, $filename]);
    }

    /**
     * Crée une bannière placeholder pour la démo
     */
    private function createBanner()
    {
        $dir = __DIR__ . '/../../public/assets/banners/';
        if (!is_dir($dir)) mkdir($dir, 0755, true);

        $filename = 'banner_demo_' . $this->adminId . '.png';
        $path = $dir . $filename;

        $img = imagecreatetruecolor(1200, 400);

        // Dégradé ambré → brun
        for ($y = 0; $y < 400; $y++) {
            $ratio = $y / 400;
            $r = (int)(180 - $ratio * 80);
            $g = (int)(83 - $ratio * 40);
            $b = (int)(9 + $ratio * 20);
            $color = imagecolorallocate($img, max(0, $r), max(0, $g), min(255, $b));
            imageline($img, 0, $y, 1199, $y, $color);
        }

        // Texte centré
        $white = imagecolorallocate($img, 255, 255, 255);
        $fontFile = $this->getFont();
        if ($fontFile) {
            $text = 'Le Bistrot MenuMiam';
            $bbox = imagettfbbox(36, 0, $fontFile, $text);
            $x = (1200 - ($bbox[2] - $bbox[0])) / 2;
            imagettftext($img, 36, 0, (int)$x, 180, $white, $fontFile, $text);

            $sub = 'Cuisine francaise traditionnelle';
            $bbox2 = imagettfbbox(18, 0, $fontFile, $sub);
            $x2 = (1200 - ($bbox2[2] - $bbox2[0])) / 2;
            $semi = imagecolorallocatealpha($img, 255, 255, 255, 40);
            imagettftext($img, 18, 0, (int)$x2, 230, $semi, $fontFile, $sub);
        } else {
            imagestring($img, 5, 480, 180, 'Le Bistrot MenuMiam', $white);
        }

        imagepng($img, $path);
        imagedestroy($img);

        $stmt = $this->pdo->prepare("INSERT INTO banners (admin_id, filename, uploaded_at) VALUES (?, ?, NOW()) ON DUPLICATE KEY UPDATE filename = VALUES(filename), uploaded_at = NOW()");
        $stmt->execute([$this->adminId, $filename]);
    }

    /**
     * Génère une image placeholder PNG colorée avec texte
     *
     * @param int    $w     Largeur
     * @param int    $h     Hauteur
     * @param array  $rgb   Couleur [r, g, b]
     * @param string $text  Texte affiché
     * @param string $subdir Sous-dossier relatif (ex: 'uploads/dishes/')
     * @return string Chemin relatif pour la BDD
     */
    private function generatePlaceholder($w, $h, $rgb, $text, $subdir)
    {
        $dir = __DIR__ . '/../../public/' . $subdir;
        if (!is_dir($dir)) mkdir($dir, 0755, true);

        $filename = 'demo_' . uniqid() . '.png';
        $path = $dir . $filename;

        $img = imagecreatetruecolor($w, $h);
        $bg = imagecolorallocate($img, $rgb[0], $rgb[1], $rgb[2]);
        imagefill($img, 0, 0, $bg);

        // Overlay sombre en bas pour lisibilité du texte
        $overlay = imagecolorallocatealpha($img, 0, 0, 0, 60);
        imagefilledrectangle($img, 0, (int)($h * 0.6), $w, $h, $overlay);

        // Icône décorative (cercle clair au centre)
        $light = imagecolorallocatealpha($img, 255, 255, 255, 90);
        imagefilledellipse($img, (int)($w / 2), (int)($h * 0.35), 80, 80, $light);

        // Texte
        $white = imagecolorallocate($img, 255, 255, 255);
        $fontFile = $this->getFont();
        // Tronquer le texte si trop long
        $displayText = mb_strlen($text) > 25 ? mb_substr($text, 0, 22) . '...' : $text;

        if ($fontFile) {
            $fontSize = $w >= 600 ? 20 : 14;
            $bbox = imagettfbbox($fontSize, 0, $fontFile, $displayText);
            $x = ($w - ($bbox[2] - $bbox[0])) / 2;
            $y = $h * 0.8;
            imagettftext($img, $fontSize, 0, (int)$x, (int)$y, $white, $fontFile, $displayText);
        } else {
            $x = ($w - strlen($displayText) * 8) / 2;
            imagestring($img, 4, max(0, (int)$x), (int)($h * 0.75), $displayText, $white);
        }

        imagepng($img, $path);
        imagedestroy($img);

        return $subdir . $filename;
    }

    /**
     * Varie légèrement une couleur RGB
     */
    private function shadeColor($rgb, $offset)
    {
        return [
            min(255, max(0, $rgb[0] + (int)(sin($offset * 0.5) * 30))),
            min(255, max(0, $rgb[1] + (int)(cos($offset * 0.3) * 25))),
            min(255, max(0, $rgb[2] + (int)(sin($offset * 0.7) * 20))),
        ];
    }

    /**
     * Retourne le chemin d'une police TTF disponible, ou null
     */
    private function getFont()
    {
        // Essayer Arial sur Windows (WAMP)
        $candidates = [
            'C:/Windows/Fonts/arial.ttf',
            'C:/Windows/Fonts/segoeui.ttf',
            '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf',
            '/usr/share/fonts/TTF/DejaVuSans.ttf',
        ];
        foreach ($candidates as $f) {
            if (file_exists($f)) return $f;
        }
        return null;
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
