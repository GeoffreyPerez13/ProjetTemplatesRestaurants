<?php
/**
 * Migration : Création de la table reservations
 * 
 * Usage : php app/Migrations/create_reservations.php
 */

require_once __DIR__ . '/../../config.php';

try {
    // Table des réservations
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS reservations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            admin_id INT NOT NULL,
            customer_name VARCHAR(100) NOT NULL,
            customer_email VARCHAR(255) DEFAULT NULL,
            customer_phone VARCHAR(20) NOT NULL,
            reservation_date DATE NOT NULL,
            reservation_time TIME NOT NULL,
            party_size INT NOT NULL DEFAULT 2,
            special_requests TEXT DEFAULT NULL,
            status ENUM('pending', 'confirmed', 'cancelled', 'completed', 'no_show') NOT NULL DEFAULT 'pending',
            admin_notes TEXT DEFAULT NULL,
            cancelled_reason VARCHAR(255) DEFAULT NULL,
            confirmed_at DATETIME DEFAULT NULL,
            cancelled_at DATETIME DEFAULT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_admin_id (admin_id),
            INDEX idx_reservation_date (reservation_date),
            INDEX idx_status (status),
            INDEX idx_admin_date (admin_id, reservation_date),
            FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    echo "✅ Table 'reservations' créée avec succès.\n";

} catch (PDOException $e) {
    echo "❌ Erreur : " . $e->getMessage() . "\n";
    exit(1);
}
