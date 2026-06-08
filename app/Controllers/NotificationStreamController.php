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

        // IMPORTANT: Libérer la session pour ne pas bloquer les autres requêtes AJAX
        session_write_close();

        // Configuration pour SSE
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');
        header('X-Accel-Buffering: no'); // Désactiver le buffering nginx

        // Désactiver le buffering PHP
        while (ob_get_level()) ob_end_clean();
        
        // Désactiver la limite de temps d'exécution
        set_time_limit(0);
        
        // Envoyer immédiatement l'état actuel (pour que le client ait les données dès la connexion)
        $lastCount = $this->getPendingReservationsCount($adminId);
        $initialData = [
            'count' => $lastCount,
            'hasNew' => false,
            'timestamp' => time()
        ];
        echo "data: " . json_encode($initialData) . "\n\n";
        flush();

        $heartbeatCounter = 0;

        // Boucle infinie pour envoyer des événements
        while (true) {
            // Vérifier si la connexion est toujours active
            if (connection_aborted()) {
                break;
            }

            // Attendre 3 secondes avant la prochaine vérification (plus réactif)
            sleep(3);

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
                $heartbeatCounter = 0;
            }

            // Envoyer un heartbeat toutes les 15 secondes (~5 itérations de 3s) pour maintenir la connexion
            $heartbeatCounter++;
            if ($heartbeatCounter >= 5) {
                echo ": heartbeat " . time() . "\n\n";
                flush();
                $heartbeatCounter = 0;
            }
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
