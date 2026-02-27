<?php

/**
 * Migration pour la table google_reviews_cache
 * Stocke les avis Google en cache pour éviter de sur-appeler l'API
 */

require_once __DIR__ . '/../../config.php';

try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `google_reviews_cache` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `place_id` varchar(255) NOT NULL,
            `data` longtext NOT NULL,
            `cached_at` datetime NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `place_id` (`place_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    
    echo "Table google_reviews_cache créée avec succès.\n";
} catch (Exception $e) {
    echo "Erreur lors de la création de la table : " . $e->getMessage() . "\n";
}
