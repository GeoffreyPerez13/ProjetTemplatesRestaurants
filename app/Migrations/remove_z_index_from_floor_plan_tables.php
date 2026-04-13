<?php
/**
 * Migration : Supprimer z_index des tables restaurant_tables et restaurant_elements
 */

require_once __DIR__ . '/../../config.php';

try {
    // Supprimer z_index de restaurant_tables
    $pdo->exec("ALTER TABLE restaurant_tables DROP COLUMN z_index");
    echo "✓ Colonne z_index supprimée de restaurant_tables\n";

    // Supprimer z_index de restaurant_elements
    $pdo->exec("ALTER TABLE restaurant_elements DROP COLUMN z_index");
    echo "✓ Colonne z_index supprimée de restaurant_elements\n";

    echo "\n✓ Migration terminée avec succès\n";
} catch (PDOException $e) {
    echo "✗ Erreur lors de la migration : " . $e->getMessage() . "\n";
    exit(1);
}
