<?php
/**
 * Migration : Création de la table restaurant_tables
 * Gère les tables du restaurant avec leur position et capacité
 */

require_once __DIR__ . '/../../config.php';

try {
    $pdo = new PDO("mysql:host=" . $host . ";dbname=" . $db, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $sql = "CREATE TABLE IF NOT EXISTS restaurant_tables (
        id INT AUTO_INCREMENT PRIMARY KEY,
        floor_id INT NOT NULL,
        table_number VARCHAR(20) NOT NULL,
        shape ENUM('round', 'square', 'rectangle') NOT NULL DEFAULT 'round',
        capacity_min INT NOT NULL DEFAULT 2,
        capacity_max INT NOT NULL DEFAULT 4,
        position_x INT NOT NULL DEFAULT 0,
        position_y INT NOT NULL DEFAULT 0,
        width INT NOT NULL DEFAULT 60,
        height INT NOT NULL DEFAULT 60,
        zone VARCHAR(50) DEFAULT NULL,
        is_active TINYINT(1) NOT NULL DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (floor_id) REFERENCES restaurant_floors(id) ON DELETE CASCADE,
        INDEX idx_floor (floor_id),
        INDEX idx_active (is_active)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $pdo->exec($sql);
    echo "✅ Table 'restaurant_tables' créée avec succès.\n";
    
} catch (PDOException $e) {
    echo "❌ Erreur lors de la création de la table 'restaurant_tables' : " . $e->getMessage() . "\n";
    exit(1);
}
