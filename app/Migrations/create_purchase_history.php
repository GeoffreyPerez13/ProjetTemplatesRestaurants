<?php
/**
 * Migration : Créer la table purchase_history pour la traçabilité des achats
 */

require_once __DIR__ . '/../../config.php';

try {
    $pdo = new PDO("mysql:host=" . $host . ";dbname=" . $db, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Création de la table purchase_history...\n";

    $sql = "CREATE TABLE IF NOT EXISTS purchase_history (
        id INT AUTO_INCREMENT PRIMARY KEY,
        admin_id INT NOT NULL,
        type ENUM('basique', 'premium', 'pack_full') NOT NULL,
        label VARCHAR(255) NOT NULL COMMENT 'Libellé affiché (ex: Pack Full 3 mois)',
        features JSON DEFAULT NULL COMMENT 'Liste des features incluses',
        amount DECIMAL(10,2) NOT NULL COMMENT 'Montant total payé',
        price_per_month DECIMAL(10,2) DEFAULT NULL,
        duration_months INT DEFAULT 1,
        stripe_session_id VARCHAR(255) DEFAULT NULL,
        status ENUM('completed', 'refunded', 'failed') NOT NULL DEFAULT 'completed',
        purchased_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        expires_at DATETIME DEFAULT NULL,
        INDEX idx_admin_id (admin_id),
        INDEX idx_purchased_at (purchased_at),
        FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    $pdo->exec($sql);

    echo "Table purchase_history créée avec succès !\n";

} catch (Exception $e) {
    echo "Erreur : " . $e->getMessage() . "\n";
}
