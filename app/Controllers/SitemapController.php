<?php

require_once __DIR__ . '/BaseController.php';

/**
 * Contrôleur de génération du sitemap XML dynamique
 * Liste toutes les pages publiques indexables (vitrines + pages légales)
 */
class SitemapController extends BaseController
{
    /**
     * Génère et affiche le sitemap.xml
     * Inclut les vitrines de tous les restaurants en ligne
     */
    public function generate()
    {
        header('Content-Type: application/xml; charset=UTF-8');

        $baseUrl = SITE_URL;

        // Récupérer tous les restaurants avec site en ligne
        $restaurants = $this->getOnlineRestaurants();

        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        // Pages légales
        $legalPages = ['cgu', 'privacy', 'cookies', 'legal'];
        foreach ($legalPages as $section) {
            echo '  <url>' . "\n";
            echo '    <loc>' . htmlspecialchars($baseUrl . '/index.php?page=legal&section=' . $section) . '</loc>' . "\n";
            echo '    <changefreq>yearly</changefreq>' . "\n";
            echo '    <priority>0.3</priority>' . "\n";
            echo '  </url>' . "\n";
        }

        // Pages vitrine des restaurants
        foreach ($restaurants as $resto) {
            echo '  <url>' . "\n";
            echo '    <loc>' . htmlspecialchars($baseUrl . '/index.php?page=display&slug=' . $resto['slug']) . '</loc>' . "\n";
            if (!empty($resto['updated_at'])) {
                echo '    <lastmod>' . date('Y-m-d', strtotime($resto['updated_at'])) . '</lastmod>' . "\n";
            }
            echo '    <changefreq>weekly</changefreq>' . "\n";
            echo '    <priority>0.8</priority>' . "\n";
            echo '  </url>' . "\n";
        }

        echo '</urlset>';
    }

    /**
     * Récupère les restaurants dont le site est en ligne
     *
     * @return array Restaurants avec slug et updated_at
     */
    private function getOnlineRestaurants()
    {
        $stmt = $this->pdo->prepare("
            SELECT r.slug, r.updated_at
            FROM restaurants r
            JOIN admins a ON a.restaurant_id = r.id
            JOIN admin_options o ON o.admin_id = a.id AND o.option_name = 'site_online' AND o.option_value = '1'
            ORDER BY r.name ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
