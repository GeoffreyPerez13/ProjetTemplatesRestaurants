<?php

class NotificationStreamController
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Stream des notifications en temps réel via Server-Sent Events (SSE)
     */
    public function stream()
    {
        // Vérifier que l'utilisateur est connecté
        if (!isset($_SESSION['admin_id'])) {
            http_response_code(403);
            exit;
        }

        $adminId = $_SESSION['admin_id'];

        // Configuration pour SSE
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');
        header('X-Accel-Buffering: no'); // Désactiver le buffering nginx

        // Désactiver le buffering PHP
        if (ob_get_level()) ob_end_clean();
        
        // Garder une trace du dernier compteur de réservations
        $lastCount = $this->getPendingReservationsCount($adminId);

        // Boucle infinie pour envoyer des événements
        while (true) {
            // Vérifier si la connexion est toujours active
            if (connection_aborted()) {
                break;
            }

            // Récupérer le nombre actuel de réservations en attente
            $currentCount = $this->getPendingReservationsCount($adminId);

            // Si le nombre a changé, envoyer un événement
            if ($currentCount !== $lastCount) {
                $data = [
                    'count' => $currentCount,
                    'hasNew' => $currentCount > $lastCount,
                    'timestamp' => time()
                ];

                // Si nouvelles réservations, récupérer les détails
                if ($currentCount > $lastCount) {
                    $newReservations = $this->getLatestReservations($adminId, $currentCount - $lastCount);
                    $data['newReservations'] = $newReservations;
                }

                // Envoyer l'événement
                echo "data: " . json_encode($data) . "\n\n";
                flush();

                $lastCount = $currentCount;
            }

            // Envoyer un heartbeat toutes les 15 secondes pour maintenir la connexion
            echo ": heartbeat\n\n";
            flush();

            // Attendre 5 secondes avant la prochaine vérification
            sleep(5);
        }
    }

    /**
     * Récupérer le nombre de réservations en attente
     */
    private function getPendingReservationsCount($adminId)
    {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) 
            FROM reservations 
            WHERE admin_id = ? AND status = 'pending'
        ");
        $stmt->execute([$adminId]);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Récupérer les dernières réservations
     */
    private function getLatestReservations($adminId, $limit = 1)
    {
        $stmt = $this->pdo->prepare("
            SELECT 
                id,
                customer_name,
                customer_phone,
                customer_email,
                reservation_date,
                reservation_time,
                party_size,
                special_requests
            FROM reservations 
            WHERE admin_id = ? AND status = 'pending'
            ORDER BY created_at DESC
            LIMIT ?
        ");
        $stmt->execute([$adminId, $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
