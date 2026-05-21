<?php
/**
 * Script pour vider TOUTES les tables de la base de données
 * Garde la structure intacte mais supprime toutes les données
 * 
 * Usage : php app/Migrations/truncate_all_tables.php
 */

require_once __DIR__ . '/../../config.php';

// Liste de toutes les tables dans l'ordre correct (enfants avant parents à cause des FK)
$tables = [
    'plat_allergenes',
    'plats',
    'categories',
    'card_images',
    'logos',
    'banners',
    'contact',
    'admin_options',
    'site_visits',
    'daily_menus',
    'reservations',
    'restaurant_elements',
    'restaurant_tables',
    'restaurant_floors',
    'demo_tokens',
    'premium_features',
    'client_subscriptions',
    'google_reviews_cache',
    'invitations',
    'admins',
    'restaurants',
    'allergenes',
];

echo "\n🗑️  TRUNCATE de toutes les tables de la base de données\n";
echo str_repeat('=', 50) . "\n\n";

try {
    // Désactiver les contraintes de clés étrangères
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    echo "🔓 Contraintes FK désactivées\n\n";

    $success = 0;
    $skipped = 0;

    foreach ($tables as $table) {
        try {
            // Vérifier si la table existe
            $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
            if ($stmt->rowCount() === 0) {
                echo "  ⏭️  $table (n'existe pas, ignorée)\n";
                $skipped++;
                continue;
            }

            $pdo->exec("TRUNCATE TABLE `$table`");
            echo "  ✅ $table vidée\n";
            $success++;
        } catch (PDOException $e) {
            echo "  ❌ $table : " . $e->getMessage() . "\n";
        }
    }

    // Réactiver les contraintes de clés étrangères
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    echo "\n🔒 Contraintes FK réactivées\n";

    echo "\n" . str_repeat('=', 50) . "\n";
    echo "📊 Résultat : $success tables vidées, $skipped ignorées\n";
    echo "✅ Base de données prête pour de nouveaux tests !\n\n";

} catch (PDOException $e) {
    // En cas d'erreur, réactiver les FK
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    echo "\n❌ Erreur fatale : " . $e->getMessage() . "\n";
    exit(1);
}
