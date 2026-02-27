<?php
/**
 * Migration pour gérer les abonnements premium des clients
 */

require_once __DIR__ . '/../../config.php';

try {
    $pdo = new PDO("mysql:host=" . $host . ";dbname=" . $db, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Création de la table client_subscriptions...\n";

    $sql = "CREATE TABLE IF NOT EXISTS client_subscriptions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        admin_id INT NOT NULL,
        plan_type ENUM('free', 'premium', 'pro') NOT NULL DEFAULT 'free',
        status ENUM('active', 'inactive', 'cancelled', 'expired') NOT NULL DEFAULT 'inactive',
        price_per_month DECIMAL(10,2) DEFAULT 0.00,
        features_enabled JSON DEFAULT NULL,
        started_at DATETIME NULL,
        expires_at DATETIME NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        created_by INT NULL, -- Super admin qui active l'abonnement
        notes TEXT NULL,
        INDEX idx_admin_id (admin_id),
        INDEX idx_status (status),
        INDEX idx_expires_at (expires_at),
        FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE CASCADE,
        FOREIGN KEY (created_by) REFERENCES admins(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    $pdo->exec($sql);

    echo "Table client_subscriptions créée avec succès !\n";

    // Créer les fonctionnalités premium par défaut
    $premiumFeatures = [
        'google_reviews' => [
            'name' => 'Avis Google',
            'description' => 'Affichage des avis Google sur la vitrine',
            'price_per_month' => 19.00
        ],
        'advanced_analytics' => [
            'name' => 'Statistiques avancées',
            'description' => 'Analytics détaillés du trafic et performances',
            'price_per_month' => 15.00
        ],
        'online_booking' => [
            'name' => 'Réservations en ligne',
            'description' => 'Système de réservation intégré',
            'price_per_month' => 25.00
        ],
        'delivery_integration' => [
            'name' => 'Intégration livraison',
            'description' => 'Connexion Uber Eats, Deliveroo, etc.',
            'price_per_month' => 20.00
        ]
    ];

    // Insérer les abonnements existants comme 'free'
    $sql = "INSERT IGNORE INTO client_subscriptions (admin_id, plan_type, status, features_enabled) 
            SELECT id, 'free', 'active', NULL FROM admins";
    $pdo->exec($sql);

    echo "Abonnements initialisés pour tous les admins !\n";

} catch (Exception $e) {
    echo "Erreur : " . $e->getMessage() . "\n";
}
