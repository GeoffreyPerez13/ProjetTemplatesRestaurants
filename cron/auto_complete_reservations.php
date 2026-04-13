<?php
/**
 * CRON Job : Marquage automatique des réservations comme terminées
 * 
 * Ce script doit être exécuté régulièrement (ex: toutes les 15 minutes)
 * via une tâche planifiée (CRON sur Linux, Planificateur de tâches sur Windows)
 * 
 * Configuration recommandée : toutes les 15 minutes
 */

// Charger la configuration (qui crée automatiquement $pdo)
require_once __DIR__ . '/../config.php';

// Vérifier que $pdo est bien disponible
if (!isset($pdo) || !($pdo instanceof PDO)) {
    error_log("CRON auto_complete_reservations - Erreur : \$pdo non disponible depuis config.php");
    exit(1);
}

// Timezone
date_default_timezone_set('Europe/Paris');

// Log de démarrage
$logMessage = "[" . date('Y-m-d H:i:s') . "] CRON auto_complete_reservations - Démarrage\n";
error_log($logMessage);

// Récupérer tous les admins ayant activé le marquage automatique
$stmt = $pdo->prepare("
    SELECT DISTINCT admin_id, option_value as meal_duration
    FROM admin_options
    WHERE option_name = 'booking_auto_complete'
    AND option_value = '1'
");
$stmt->execute();
$adminsWithAutoComplete = $stmt->fetchAll();

if (empty($adminsWithAutoComplete)) {
    error_log("[" . date('Y-m-d H:i:s') . "] CRON auto_complete_reservations - Aucun admin avec auto-complete activé\n");
    exit(0);
}

$totalUpdated = 0;

foreach ($adminsWithAutoComplete as $adminConfig) {
    $adminId = $adminConfig['admin_id'];
    
    // Récupérer la durée du repas pour cet admin
    $stmtDuration = $pdo->prepare("
        SELECT option_value
        FROM admin_options
        WHERE admin_id = :admin_id
        AND option_name = 'booking_meal_duration'
    ");
    $stmtDuration->execute(['admin_id' => $adminId]);
    $durationRow = $stmtDuration->fetch();
    $mealDuration = (int)($durationRow['option_value'] ?? 90); // 90 minutes par défaut
    
    // Calculer le timestamp limite (maintenant - durée du repas)
    $limitDateTime = date('Y-m-d H:i:s', strtotime("-{$mealDuration} minutes"));
    
    // Trouver toutes les réservations confirmées qui devraient être terminées
    $stmtReservations = $pdo->prepare("
        SELECT id, reservation_date, reservation_time
        FROM reservations
        WHERE admin_id = :admin_id
        AND status = 'confirmed'
        AND CONCAT(reservation_date, ' ', reservation_time) <= :limit_datetime
    ");
    $stmtReservations->execute([
        'admin_id' => $adminId,
        'limit_datetime' => $limitDateTime
    ]);
    $reservationsToComplete = $stmtReservations->fetchAll();
    
    if (!empty($reservationsToComplete)) {
        $reservationIds = array_column($reservationsToComplete, 'id');
        
        // Marquer comme terminées
        $placeholders = implode(',', array_fill(0, count($reservationIds), '?'));
        $stmtUpdate = $pdo->prepare("
            UPDATE reservations
            SET status = 'completed'
            WHERE id IN ($placeholders)
        ");
        $stmtUpdate->execute($reservationIds);
        
        $updated = $stmtUpdate->rowCount();
        $totalUpdated += $updated;
        
        error_log("[" . date('Y-m-d H:i:s') . "] CRON auto_complete_reservations - Admin $adminId : $updated réservation(s) marquée(s) comme terminée(s)\n");
    }
}

error_log("[" . date('Y-m-d H:i:s') . "] CRON auto_complete_reservations - Terminé : $totalUpdated réservation(s) au total\n");
exit(0);
