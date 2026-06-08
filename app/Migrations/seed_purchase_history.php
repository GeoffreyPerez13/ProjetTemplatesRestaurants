<?php
/**
 * Migration ponctuelle : Insérer un historique d'achat rétroactif 
 * pour les abonnements existants qui n'ont pas été enregistrés
 */

require_once __DIR__ . '/../../config.php';

try {
    $pdo = new PDO("mysql:host=" . $host . ";dbname=" . $db, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Récupérer tous les abonnements actifs sans entrée dans purchase_history
    $stmt = $pdo->query("
        SELECT cs.admin_id, cs.plan_type, cs.price_per_month, cs.started_at, cs.expires_at, cs.notes
        FROM client_subscriptions cs
        WHERE cs.status IN ('active', 'cancelled')
        AND cs.admin_id NOT IN (SELECT DISTINCT admin_id FROM purchase_history)
    ");

    $inserted = 0;
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $adminId = $row['admin_id'];
        $planType = $row['plan_type'];
        $pricePerMonth = (float)$row['price_per_month'];
        $startedAt = $row['started_at'];
        $expiresAt = $row['expires_at'];

        // Calculer la durée
        if ($startedAt && $expiresAt) {
            $start = new DateTime($startedAt);
            $end = new DateTime($expiresAt);
            $durationMonths = (int)$start->diff($end)->m + ((int)$start->diff($end)->y * 12);
        } else {
            $durationMonths = 1;
        }

        $totalAmount = $pricePerMonth * $durationMonths;

        if ($planType === 'pack_full') {
            $durationLabel = match(true) {
                $durationMonths >= 12 => '1 an',
                $durationMonths >= 3 => '3 mois',
                default => '1 mois',
            };
            $label = 'Pack Full ' . $durationLabel;
            $features = json_encode(['google_reviews', 'advanced_analytics', 'online_booking', 'delivery_integration']);
        } else {
            $label = 'Abonnement Basique';
            $features = null;
        }

        // Extraire session_id du notes si disponible
        $stripeSession = null;
        if (!empty($row['notes']) && str_contains($row['notes'], 'Stripe session:')) {
            $stripeSession = trim(str_replace('Stripe session:', '', $row['notes']));
        }

        $insert = $pdo->prepare("
            INSERT INTO purchase_history 
                (admin_id, type, label, features, amount, price_per_month, duration_months, stripe_session_id, status, purchased_at, expires_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'completed', ?, ?)
        ");
        $insert->execute([
            $adminId,
            $planType,
            $label,
            $features,
            $totalAmount,
            $pricePerMonth,
            $durationMonths,
            $stripeSession,
            $startedAt,
            $expiresAt
        ]);
        $inserted++;
        echo "Admin #{$adminId} : historique ajouté ({$label}, {$totalAmount}€)\n";
    }

    if ($inserted === 0) {
        echo "Aucun historique à ajouter.\n";
    } else {
        echo "\n{$inserted} entrée(s) ajoutée(s) dans purchase_history.\n";
    }

} catch (Exception $e) {
    echo "Erreur : " . $e->getMessage() . "\n";
}
