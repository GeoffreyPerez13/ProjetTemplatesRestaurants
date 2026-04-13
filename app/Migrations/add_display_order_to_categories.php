<?php
/**
 * Migration pour ajouter une colonne display_order à la table categories
 */

require_once __DIR__ . '/../../config.php';

try {
    $pdo = new PDO("mysql:host=" . $host . ";dbname=" . $db, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Vérifier si la colonne existe déjà
    $stmt = $pdo->query("SHOW COLUMNS FROM categories LIKE 'display_order'");
    if ($stmt->fetch()) {
        echo "La colonne display_order existe déjà.\n";
        exit;
    }

    echo "Ajout de la colonne display_order...\n";
    $pdo->exec("ALTER TABLE categories ADD COLUMN display_order INT NOT NULL DEFAULT 0 AFTER image");

    echo "Initialisation des valeurs display_order par admin...\n";
    $stmt = $pdo->query("SELECT DISTINCT admin_id FROM categories");
    $adminIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

    foreach ($adminIds as $adminId) {
        $stmt = $pdo->prepare("SELECT id FROM categories WHERE admin_id = ? ORDER BY id ASC");
        $stmt->execute([$adminId]);
        $ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $order = 1;
        foreach ($ids as $id) {
            $pdo->prepare("UPDATE categories SET display_order = ? WHERE id = ?")->execute([$order++, $id]);
        }
    }

    echo "Migration terminée avec succès !\n";

} catch (Exception $e) {
    echo "Erreur : " . $e->getMessage() . "\n";
}
