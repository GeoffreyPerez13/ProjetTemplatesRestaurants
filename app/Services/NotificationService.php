<?php

require_once __DIR__ . '/../Helpers/Mailer.php';

class NotificationService
{
    private $pdo;
    private $mailer;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
        $this->mailer = new Mailer();
    }

    /**
     * Envoie une notification à tous les admins ayant email_notifications = 1
     * @param string $subject Sujet
     * @param string $body    Corps HTML (peut contenir {username})
     * @return int Nombre d'emails envoyés
     */
    public function notifyAdmins($subject, $body)
    {
        $stmt = $this->pdo->prepare("
            SELECT a.email, a.username
            FROM admins a
            INNER JOIN admin_options o ON a.id = o.admin_id
            WHERE o.option_name = 'email_notifications' AND o.option_value = '1'
        ");
        $stmt->execute();
        $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $count = 0;
        foreach ($admins as $admin) {
            $personalizedBody = str_replace('{username}', htmlspecialchars($admin['username']), $body);
            if ($this->mailer->send($admin['email'], $subject, $personalizedBody)) {
                $count++;
            }
        }
        return $count;
    }
}