<?php
/**
 * Migration : Ajout de la colonne table_id à la table reservations
 * Permet d'assigner une réservation à une table spécifique
 */

require_once __DIR__ . '/../../config.php';

try {
    $pdo = new PDO("mysql:host=" . $host . ";dbname=" . $db, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Vérifier si la colonne existe déjà
    $stmt = $pdo->query("SHOW COLUMNS FROM reservations LIKE 'table_id'");
    if ($stmt->rowCount() > 0) {
        echo "ℹ️  La colonne 'table_id' existe déjà dans la table 'reservations'.\n";
        exit(0);
    }
    
    $sql = "ALTER TABLE reservations 
            ADD COLUMN table_id INT DEFAULT NULL AFTER admin_id,
            ADD FOREIGN KEY (table_id) REFERENCES restaurant_tables(id) ON DELETE SET NULL,
            ADD INDEX idx_table (table_id)";
    
    $pdo->exec($sql);
    echo "✅ Colonne 'table_id' ajoutée à la table 'reservations' avec succès.\n";
    
} catch (PDOException $e) {
    echo "❌ Erreur lors de l'ajout de la colonne 'table_id' : " . $e->getMessage() . "\n";
    exit(1);
}
