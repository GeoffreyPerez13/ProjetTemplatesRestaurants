<?php
/**
 * Migration ponctuelle : Mettre à jour expires_at sur les premium_features actives
 * en se basant sur la date d'expiration de client_subscriptions
 */

require_once __DIR__ . '/../../config.php';

try {
    $pdo = new PDO("mysql:host=" . $host . ";dbname=" . $db, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->exec("
        UPDATE premium_features pf
        INNER JOIN client_subscriptions cs ON pf.admin_id = cs.admin_id
        SET pf.expires_at = cs.expires_at
        WHERE pf.is_active = 1 AND pf.expires_at IS NULL AND cs.expires_at IS NOT NULL
    ");

    echo "OK : {$stmt} feature(s) mise(s) à jour avec expires_at\n";

} catch (Exception $e) {
    echo "Erreur : " . $e->getMessage() . "\n";
}
