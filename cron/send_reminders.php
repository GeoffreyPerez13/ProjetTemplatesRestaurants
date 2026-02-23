#!/usr/bin/env php
<?php
// cron/send_reminders.php

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../app/Helpers/Mailer.php';

function logMessage($message) {
    $logFile = __DIR__ . '/logs/reminders.log';
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
}

logMessage("Début de l'envoi des rappels mensuels.");

try {
    logMessage("Recherche des admins avec mail_reminder=1...");

    $stmt = $pdo->prepare("
        SELECT a.id, a.email, a.username, a.restaurant_name
        FROM admins a
        INNER JOIN admin_options o ON a.id = o.admin_id
        WHERE o.option_name = 'mail_reminder' AND o.option_value = '1'
    ");
    $stmt->execute();
    $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);

    logMessage("Nombre d'admins trouvés : " . count($admins));

    if (empty($admins)) {
        logMessage("Aucun admin avec rappel activé.");
        exit;
    }

    $mailer = new Mailer();

    foreach ($admins as $admin) {
        $editLink = SITE_URL . '/?page=edit-card';

        $subject = "Rappel : Mettez à jour votre carte !";
        $body = "
        <html>
        <body>
            <p>Bonjour " . htmlspecialchars($admin['username']) . ",</p>
            <p>Nous vous rappelons de mettre à jour la carte de votre restaurant <strong>" . htmlspecialchars($admin['restaurant_name']) . "</strong>.</p>
            <p>Pour garantir une expérience optimale à vos clients, pensez à vérifier régulièrement vos plats, prix et descriptions.</p>
            <p>Accédez à la modification : <a href='$editLink'>$editLink</a></p>
            <p>Merci de votre confiance,<br>L'équipe Menumiam</p>
        </body>
        </html>
        ";

        if ($mailer->send($admin['email'], $subject, $body)) {
            logMessage("Email envoyé à " . $admin['email']);
        } else {
            logMessage("ÉCHEC d'envoi à " . $admin['email']);
        }

        usleep(200000);
    }

    logMessage("Fin de l'envoi des rappels.");
} catch (Exception $e) {
    logMessage("ERREUR : " . $e->getMessage());
}