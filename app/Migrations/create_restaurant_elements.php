<?php
/**
 * Migration : Création de la table restaurant_elements
 * Gère les éléments décoratifs (murs, portes, etc.)
 */

require_once __DIR__ . '/../../config.php';

try {
    $pdo = new PDO("mysql:host=" . $host . ";dbname=" . $db, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $sql = "CREATE TABLE IF NOT EXISTS restaurant_elements (
        id INT AUTO_INCREMENT PRIMARY KEY,
        floor_id INT NOT NULL,
        element_type ENUM('wall', 'door', 'window', 'decoration') NOT NULL DEFAULT 'wall',
        position_x INT NOT NULL DEFAULT 0,
        position_y INT NOT NULL DEFAULT 0,
        width INT NOT NULL DEFAULT 100,
        height INT NOT NULL DEFAULT 20,
        rotation INT NOT NULL DEFAULT 0,
        color VARCHAR(20) DEFAULT '#666666',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (floor_id) REFERENCES restaurant_floors(id) ON DELETE CASCADE,
        INDEX idx_floor (floor_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $pdo->exec($sql);
    echo "✅ Table 'restaurant_elements' créée avec succès.\n";
    
} catch (PDOException $e) {
    echo "❌ Erreur lors de la création de la table 'restaurant_elements' : " . $e->getMessage() . "\n";
    exit(1);
}
