<?php
require_once __DIR__ . '/../../config.php';

try {
    $pdo = new PDO("mysql:host=" . $host . ";dbname=" . $db, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $count = $pdo->exec("UPDATE purchase_history SET label = REPLACE(label, 'Pack Full — ', 'Pack Full ') WHERE label LIKE '%Pack Full —%'");
    echo "OK : {$count} entrée(s) corrigée(s)\n";

} catch (Exception $e) {
    echo "Erreur : " . $e->getMessage() . "\n";
}
