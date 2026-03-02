<?php
/**
 * Migration : ajout des colonnes email_verified et verification_token à la table admins
 */

require_once __DIR__ . '/../../config.php';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Ajout des colonnes email_verified et verification_token...\n";

    // Vérifier si email_verified existe déjà
    $cols = $pdo->query("SHOW COLUMNS FROM admins LIKE 'email_verified'")->fetchAll();
    if (empty($cols)) {
        $pdo->exec("ALTER TABLE admins ADD COLUMN email_verified TINYINT(1) NOT NULL DEFAULT 0 AFTER email");
        echo "Colonne email_verified ajoutée.\n";
    } else {
        echo "Colonne email_verified déjà présente.\n";
    }

    // Vérifier si verification_token existe déjà
    $cols2 = $pdo->query("SHOW COLUMNS FROM admins LIKE 'verification_token'")->fetchAll();
    if (empty($cols2)) {
        $pdo->exec("ALTER TABLE admins ADD COLUMN verification_token VARCHAR(64) NULL DEFAULT NULL AFTER email_verified");
        echo "Colonne verification_token ajoutée.\n";
    } else {
        echo "Colonne verification_token déjà présente.\n";
    }

    echo "Colonnes ajoutées avec succès.\n";

    // Les admins existants sont déjà vérifiés (comptes créés manuellement ou par invitation)
    $pdo->exec("UPDATE admins SET email_verified = 1 WHERE email_verified = 0");

    echo "Admins existants marqués comme vérifiés.\n";

} catch (Exception $e) {
    echo "Erreur : " . $e->getMessage() . "\n";
}
