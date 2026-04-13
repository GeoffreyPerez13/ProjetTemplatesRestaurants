<?php
/**
 * Migration : Ajouter rotation à restaurant_tables
 */

require_once __DIR__ . '/../../config.php';

try {
    // Ajouter rotation à restaurant_tables
    $pdo->exec("
        ALTER TABLE restaurant_tables 
        ADD COLUMN rotation INT DEFAULT 0 AFTER height
    ");
    echo "✓ Colonne rotation ajoutée à restaurant_tables\n";

    echo "\n✓ Migration terminée avec succès\n";
} catch (PDOException $e) {
    echo "✗ Erreur lors de la migration : " . $e->getMessage() . "\n";
    exit(1);
}
