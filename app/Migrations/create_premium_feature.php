<?php
/**
 * Migration pour ajouter une table premium_features
 * Permet d'activer/désactiver des fonctionnalités premium par admin
 */

require_once __DIR__ . '/../../config.php';

try {
    $pdo = new PDO("mysql:host=" . $host . ";dbname=" . $db, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Création de la table premium_features...\n";

    $sql = "CREATE TABLE IF NOT EXISTS premium_features (
        id INT AUTO_INCREMENT PRIMARY KEY,
        admin_id INT NOT NULL,
        feature_name VARCHAR(50) NOT NULL,
        is_active BOOLEAN DEFAULT FALSE,
        activated_at DATETIME NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY unique_admin_feature (admin_id, feature_name),
        INDEX idx_admin_feature (admin_id, feature_name)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    $pdo->exec($sql);

    echo "Table premium_features créée avec succès !\n";

    // Insérer les fonctionnalités premium possibles
    $features = ['google_reviews', 'advanced_analytics', 'online_booking', 'delivery_integration'];
    
    foreach ($features as $feature) {
        $sql = "INSERT IGNORE INTO premium_features (admin_id, feature_name) 
                SELECT id, ? FROM admins";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$feature]);
    }

    echo "Fonctionnalités premium initialisées pour tous les admins !\n";

} catch (Exception $e) {
    echo "Erreur : " . $e->getMessage() . "\n";
}
