<?php
/**
 * Seed des données de base après un TRUNCATE complet
 * - 14 allergènes officiels
 * - 1 compte SUPER_ADMIN (superadmin / SuperAdmin1!)
 * - 1 restaurant associé
 * 
 * Usage : php app/Migrations/seed_base_data.php
 */

require_once __DIR__ . '/../../config.php';

echo "\n🌱 Seed des données de base\n";
echo str_repeat('=', 50) . "\n\n";

try {
    // ========== 1. ALLERGÈNES ==========
    echo "📋 Insertion des 14 allergènes...\n";

    $allergenes = [
        ['Gluten', 'fas fa-bread-slice'],
        ['Crustacés', 'fas fa-shrimp'],
        ['Œufs', 'fas fa-egg'],
        ['Poissons', 'fas fa-fish'],
        ['Arachides', 'fas fa-seedling'],
        ['Soja', 'fas fa-leaf'],
        ['Lait', 'fas fa-glass-whiskey'],
        ['Fruits à coque', 'fas fa-tree'],
        ['Céleri', 'fas fa-carrot'],
        ['Moutarde', 'fas fa-mortar-pestle'],
        ['Sésame', 'fas fa-circle'],
        ['Sulfites', 'fas fa-wine-bottle'],
        ['Lupin', 'fas fa-spa'],
        ['Mollusques', 'fas fa-water'],
    ];

    $stmt = $pdo->prepare("INSERT INTO allergenes (nom, icone, created_at) VALUES (?, ?, NOW())");
    foreach ($allergenes as $a) {
        $stmt->execute([$a[0], $a[1]]);
    }
    echo "  ✅ 14 allergènes insérés\n\n";

    // ========== 2. RESTAURANT ==========
    echo "🏪 Création du restaurant...\n";

    $pdo->exec("INSERT INTO restaurants (name, slug, created_at, updated_at) VALUES ('Mon Restaurant', 'mon-restaurant', NOW(), NOW())");
    $restaurantId = $pdo->lastInsertId();
    echo "  ✅ Restaurant créé (id: $restaurantId)\n\n";

    // ========== 3. COMPTE SUPER_ADMIN ==========
    echo "👤 Création du compte SUPER_ADMIN...\n";

    $password = password_hash('SuperAdmin1!', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("
        INSERT INTO admins (username, email, password, restaurant_name, restaurant_id, carte_mode, role, email_verified, created_at, updated_at)
        VALUES (?, ?, ?, ?, ?, 'editable', 'SUPER_ADMIN', 1, NOW(), NOW())
    ");
    $stmt->execute(['superadmin', 'admin@menucraft.fr', $password, 'Mon Restaurant', $restaurantId]);
    $adminId = $pdo->lastInsertId();
    echo "  ✅ SUPER_ADMIN créé (id: $adminId)\n";
    echo "     → Username: superadmin\n";
    echo "     → Password: SuperAdmin1!\n\n";

    // ========== 4. ABONNEMENT ACTIF ==========
    echo "💳 Activation de l'abonnement...\n";

    // Vérifier si la table existe
    $tableCheck = $pdo->query("SHOW TABLES LIKE 'client_subscriptions'");
    if ($tableCheck->rowCount() > 0) {
        $stmt = $pdo->prepare("
            INSERT INTO client_subscriptions (admin_id, plan_type, status, price_per_month, started_at, created_at)
            VALUES (?, 'basique', 'active', 9.00, NOW(), NOW())
        ");
        $stmt->execute([$adminId]);
        echo "  ✅ Abonnement basique activé\n\n";
    } else {
        echo "  ⏭️  Table client_subscriptions inexistante, ignorée\n\n";
    }

    // ========== 5. PREMIUM FEATURES ==========
    echo "⭐ Activation des features premium...\n";

    $tableCheck = $pdo->query("SHOW TABLES LIKE 'premium_features'");
    if ($tableCheck->rowCount() > 0) {
        $features = ['google_reviews', 'advanced_analytics', 'online_booking', 'delivery_integration'];
        $stmt = $pdo->prepare("
            INSERT INTO premium_features (admin_id, feature_name, is_active, activated_at, created_at)
            VALUES (?, ?, 1, NOW(), NOW())
        ");
        foreach ($features as $f) {
            $stmt->execute([$adminId, $f]);
        }
        echo "  ✅ 4 features premium activées\n\n";
    } else {
        echo "  ⏭️  Table premium_features inexistante, ignorée\n\n";
    }

    // ========== 6. OPTIONS DE BASE ==========
    echo "⚙️  Options par défaut...\n";

    $options = [
        'site_online' => '1',
        'site_template' => 'classic',
    ];
    $stmt = $pdo->prepare("INSERT INTO admin_options (admin_id, option_name, option_value, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())");
    foreach ($options as $name => $value) {
        $stmt->execute([$adminId, $name, $value]);
    }
    echo "  ✅ Options insérées\n\n";

    echo str_repeat('=', 50) . "\n";
    echo "🎉 Base de données prête !\n";
    echo "   Connectez-vous avec : superadmin / SuperAdmin1!\n\n";

} catch (PDOException $e) {
    echo "\n❌ Erreur : " . $e->getMessage() . "\n";
    exit(1);
}
