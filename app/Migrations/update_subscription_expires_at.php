<?php
/**
 * Migration pour ajouter les dates d'expiration aux abonnements existants
 */

require_once __DIR__ . '/../../config.php';

try {
    $pdo = new PDO("mysql:host=" . $host . ";dbname=" . $db, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Mise à jour des dates d'expiration des abonnements...\n";

    // Mettre à jour les abonnements basiques sans date d'expiration
    // On leur donne 1 mois à partir de maintenant
    $sql = "UPDATE client_subscriptions 
            SET expires_at = DATE_ADD(started_at, INTERVAL 1 MONTH)
            WHERE plan_type = 'basique' 
            AND status = 'active' 
            AND expires_at IS NULL";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    
    $updatedCount = $stmt->rowCount();
    
    echo "Mise à jour terminée ! {$updatedCount} abonnement(s) basique(s) mis à jour avec une date d'expiration.\n";

} catch (Exception $e) {
    echo "Erreur : " . $e->getMessage() . "\n";
}
