<?php

/**
 * Migration : ajout colonne label à demo_tokens
 * Exécuter via : php app/Migrations/add_label_to_demo_tokens.php
 */

require_once __DIR__ . '/../../config.php';

try {
    $pdo->exec("ALTER TABLE demo_tokens ADD COLUMN label VARCHAR(100) DEFAULT NULL AFTER created_by");
    echo "Colonne label ajoutée avec succès.\n";
} catch (PDOException $e) {
    if (str_contains($e->getMessage(), 'Duplicate column')) {
        echo "Colonne label existe déjà.\n";
    } else {
        echo "Erreur: " . $e->getMessage() . "\n";
    }
}
