<?php
/**
 * Migration : Ajouter expires_at et cancelled_at à premium_features,
 * et cancelled_at à client_subscriptions pour le système de période de grâce
 */

require_once __DIR__ . '/../../config.php';

try {
    $pdo = new PDO("mysql:host=" . $host . ";dbname=" . $db, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Ajouter expires_at et cancelled_at à premium_features
    $columns = $pdo->query("SHOW COLUMNS FROM premium_features LIKE 'expires_at'")->rowCount();
    if ($columns === 0) {
        $pdo->exec("ALTER TABLE premium_features ADD COLUMN expires_at DATETIME NULL AFTER activated_at");
        echo "OK : colonne expires_at ajoutée à premium_features\n";
    } else {
        echo "SKIP : expires_at existe déjà dans premium_features\n";
    }

    $columns = $pdo->query("SHOW COLUMNS FROM premium_features LIKE 'cancelled_at'")->rowCount();
    if ($columns === 0) {
        $pdo->exec("ALTER TABLE premium_features ADD COLUMN cancelled_at DATETIME NULL AFTER expires_at");
        echo "OK : colonne cancelled_at ajoutée à premium_features\n";
    } else {
        echo "SKIP : cancelled_at existe déjà dans premium_features\n";
    }

    // Ajouter cancelled_at à client_subscriptions
    $columns = $pdo->query("SHOW COLUMNS FROM client_subscriptions LIKE 'cancelled_at'")->rowCount();
    if ($columns === 0) {
        $pdo->exec("ALTER TABLE client_subscriptions ADD COLUMN cancelled_at DATETIME NULL AFTER expires_at");
        echo "OK : colonne cancelled_at ajoutée à client_subscriptions\n";
    } else {
        echo "SKIP : cancelled_at existe déjà dans client_subscriptions\n";
    }

    echo "\nMigration terminée.\n";

} catch (Exception $e) {
    echo "Erreur : " . $e->getMessage() . "\n";
}
