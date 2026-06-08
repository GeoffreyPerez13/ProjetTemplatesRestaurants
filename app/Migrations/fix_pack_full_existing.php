<?php
/**
 * Migration ponctuelle : mettre à jour l'abonnement existant en pack_full
 * À exécuter une seule fois pour corriger les paiements Pack Full passés avant le fix
 */

require_once __DIR__ . '/../../config.php';

try {
    $pdo = new PDO("mysql:host=" . $host . ";dbname=" . $db, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Mettre à jour tous les abonnements actifs qui ont toutes les features premium actives
    // (= ce sont des pack_full non détectés)
    $stmt = $pdo->query("
        SELECT cs.admin_id, cs.plan_type, cs.price_per_month,
               COUNT(pf.id) as active_features
        FROM client_subscriptions cs
        LEFT JOIN premium_features pf ON pf.admin_id = cs.admin_id AND pf.is_active = 1
        WHERE cs.status = 'active'
        GROUP BY cs.admin_id
    ");

    $updated = 0;
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // Si toutes les 4 features sont actives et plan_type est encore 'basique'
        if ((int)$row['active_features'] >= 4 && $row['plan_type'] === 'basique') {
            // Déterminer le prix selon la durée (on met 26.99 pour 3 mois par défaut)
            $update = $pdo->prepare("UPDATE client_subscriptions SET plan_type = 'pack_full', price_per_month = 26.99 WHERE admin_id = ?");
            $update->execute([$row['admin_id']]);
            $updated++;
            echo "Admin #{$row['admin_id']} : mis à jour en pack_full (26.99€/mois)\n";
        }
    }

    if ($updated === 0) {
        echo "Aucun abonnement à corriger.\n";
    } else {
        echo "\n$updated abonnement(s) corrigé(s).\n";
    }

} catch (Exception $e) {
    echo "Erreur : " . $e->getMessage() . "\n";
}
