<?php
/**
 * Migration : Créer la table site_visits pour le tracking des visites
 * Usage : php app/Migrations/create_site_visits.php
 */

require_once __DIR__ . '/../../config.php';

try {
    // Créer la table site_visits
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS site_visits (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            admin_id INT NOT NULL,
            visitor_hash VARCHAR(64) NOT NULL COMMENT 'Hash SHA-256 de IP+UA pour unicité sans stocker IP',
            user_agent VARCHAR(512) DEFAULT NULL,
            referrer VARCHAR(1024) DEFAULT NULL,
            device_type ENUM('desktop', 'mobile', 'tablet') DEFAULT 'desktop',
            browser VARCHAR(64) DEFAULT NULL,
            page_path VARCHAR(255) DEFAULT '/' COMMENT 'Page visitée (slug)',
            visited_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_admin_visited (admin_id, visited_at),
            INDEX idx_admin_device (admin_id, device_type),
            INDEX idx_visitor_hash (admin_id, visitor_hash, visited_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    echo "✅ Table 'site_visits' créée avec succès.\n";

} catch (PDOException $e) {
    echo "❌ Erreur : " . $e->getMessage() . "\n";
    exit(1);
}
