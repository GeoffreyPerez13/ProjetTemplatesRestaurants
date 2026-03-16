<?php
/**
 * Migration pour ajouter le cycle de facturation groupé
 * Permet de gérer les échéances groupées avec prorata
 */

require_once __DIR__ . '/../../config.php';

try {
    $pdo = new PDO("mysql:host=" . $host . ";dbname=" . $db, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Ajout des colonnes pour le cycle de facturation...\n";

    // Ajouter les colonnes pour la gestion groupée
    $sql = "ALTER TABLE client_subscriptions 
            ADD COLUMN billing_cycle_day INT DEFAULT 15 COMMENT 'Jour du mois pour la facturation groupée',
            ADD COLUMN next_billing_date DATETIME NULL COMMENT 'Prochaine date de facturation',
            ADD COLUMN is_grouped BOOLEAN DEFAULT TRUE COMMENT 'Fait partie du cycle groupé',
            ADD COLUMN prorata_amount DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Montant au prorata pour le mois en cours'";
    
    $pdo->exec($sql);

    // Mettre à jour les abonnements existants
    $sql = "UPDATE client_subscriptions 
            SET billing_cycle_day = 15,
                next_billing_date = CASE 
                    WHEN expires_at > NOW() THEN expires_at
                    ELSE DATE_ADD(NOW(), INTERVAL 1 MONTH)
                END,
                is_grouped = TRUE
            WHERE plan_type = 'basique' AND status = 'active'";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    
    $updatedCount = $stmt->rowCount();
    
    echo "Migration terminée ! {$updatedCount} abonnement(s) configuré(s) pour le cycle groupé.\n";

} catch (Exception $e) {
    echo "Erreur : " . $e->getMessage() . "\n";
}
