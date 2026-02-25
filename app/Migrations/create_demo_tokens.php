<?php

/**
 * Migration : création de la table demo_tokens
 * Exécuter via : php app/Migrations/create_demo_tokens.php
 */

require_once __DIR__ . '/../../config.php';

try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS demo_tokens (
            id INT AUTO_INCREMENT PRIMARY KEY,
            token VARCHAR(64) NOT NULL UNIQUE,
            admin_id INT NOT NULL COMMENT 'ID du compte admin de démo',
            expires_at DATETIME NOT NULL,
            created_by INT NOT NULL COMMENT 'ID du SUPER_ADMIN qui a généré le lien',
            label VARCHAR(100) DEFAULT NULL COMMENT 'Nom du destinataire du lien',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_token (token),
            INDEX idx_expires (expires_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    echo "Table demo_tokens créée avec succès.\n";
} catch (PDOException $e) {
    echo "Erreur: " . $e->getMessage() . "\n";
}
