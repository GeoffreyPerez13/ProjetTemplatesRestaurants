<?php
/**
 * Migration : Création de la table daily_menus
 * 
 * Table pour les menus du jour / formules / plats du jour
 * Chaque menu appartient à un admin et contient des lignes (items) en JSON
 * 
 * Usage : php app/Migrations/create_daily_menus.php
 */

require_once __DIR__ . '/../../config.php';

try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS daily_menus (
            id INT AUTO_INCREMENT PRIMARY KEY,
            admin_id INT NOT NULL,
            title VARCHAR(150) NOT NULL,
            description TEXT NULL,
            price DECIMAL(6,2) NULL,
            items JSON NOT NULL COMMENT 'Array of menu lines: [{\"label\":\"Entrée\",\"value\":\"Salade César\"}]',
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            display_order INT NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE CASCADE,
            INDEX idx_admin_active (admin_id, is_active),
            INDEX idx_display_order (admin_id, display_order)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    echo "✅ Table 'daily_menus' créée avec succès.\n";
} catch (PDOException $e) {
    echo "❌ Erreur : " . $e->getMessage() . "\n";
    exit(1);
}
