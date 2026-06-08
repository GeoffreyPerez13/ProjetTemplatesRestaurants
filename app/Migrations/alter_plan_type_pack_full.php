<?php
/**
 * Migration : Ajouter 'pack_full' à l'ENUM plan_type de client_subscriptions
 */

require_once __DIR__ . '/../../config.php';

try {
    $pdo = new PDO("mysql:host=" . $host . ";dbname=" . $db, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $pdo->exec("ALTER TABLE client_subscriptions MODIFY COLUMN plan_type ENUM('basique', 'pack_full') NOT NULL DEFAULT 'basique'");

    echo "OK : colonne plan_type mise à jour (basique, pack_full)\n";

} catch (Exception $e) {
    echo "Erreur : " . $e->getMessage() . "\n";
}
