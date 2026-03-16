<?php
/**
 * Migration pour ajouter la gestion du prorata aux options premium
 */

require_once __DIR__ . '/../../config.php';

try {
    $pdo = new PDO("mysql:host=" . $host . ";dbname=" . $db, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Ajout des colonnes prorata à premium_features...\n";

    // Ajouter les colonnes pour le prorata
    $sql = "ALTER TABLE premium_features 
            ADD COLUMN prorata_amount DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Montant au prorata pour le mois en cours',
            ADD COLUMN next_billing_date DATETIME NULL COMMENT 'Prochaine date de facturation groupée'";
    
    $pdo->exec($sql);
    
    echo "Migration premium_features terminée !\n";

} catch (Exception $e) {
    echo "Erreur : " . $e->getMessage() . "\n";
}
