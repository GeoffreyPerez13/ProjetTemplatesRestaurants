<?php
/**
 * Modèle pour la gestion des réservations en ligne
 */
class Reservation
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Créer une nouvelle réservation (côté client vitrine)
     */
    public function create($adminId, $data)
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO reservations (admin_id, customer_name, customer_email, customer_phone, reservation_date, reservation_time, party_size, special_requests, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $adminId,
            $data['customer_name'],
            $data['customer_email'] ?? null,
            $data['customer_phone'],
            $data['reservation_date'],
            $data['reservation_time'],
            $data['party_size'],
            $data['special_requests'] ?? null,
            $data['status'] ?? 'pending'
        ]);
        return $this->pdo->lastInsertId();
    }

    /**
     * Trouver une réservation par ID
     */
    public function findById($id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM reservations WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Récupérer toutes les réservations d'un admin avec filtres
     */
    public function getAll($adminId, $filters = [])
    {
        $sql = "SELECT * FROM reservations WHERE admin_id = ?";
        $params = [$adminId];

        if (!empty($filters['status'])) {
            $sql .= " AND status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['date'])) {
            $sql .= " AND reservation_date = ?";
            $params[] = $filters['date'];
        }

        if (!empty($filters['date_from'])) {
            $sql .= " AND reservation_date >= ?";
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $sql .= " AND reservation_date <= ?";
            $params[] = $filters['date_to'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (customer_name LIKE ? OR customer_email LIKE ? OR customer_phone LIKE ?)";
            $search = '%' . $filters['search'] . '%';
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
        }

        $sql .= " ORDER BY reservation_date ASC, reservation_time ASC";

        if (!empty($filters['limit'])) {
            $sql .= " LIMIT " . intval($filters['limit']);
            if (!empty($filters['offset'])) {
                $sql .= " OFFSET " . intval($filters['offset']);
            }
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupérer les réservations pour une date spécifique
     */
    public function getByDate($adminId, $date)
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM reservations 
            WHERE admin_id = ? AND reservation_date = ? 
            ORDER BY reservation_time ASC
        ");
        $stmt->execute([$adminId, $date]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Compter les réservations avec filtres
     */
    public function count($adminId, $filters = [])
    {
        $sql = "SELECT COUNT(*) FROM reservations WHERE admin_id = ?";
        $params = [$adminId];

        if (!empty($filters['status'])) {
            $sql .= " AND status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['date'])) {
            $sql .= " AND reservation_date = ?";
            $params[] = $filters['date'];
        }

        if (!empty($filters['date_from'])) {
            $sql .= " AND reservation_date >= ?";
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $sql .= " AND reservation_date <= ?";
            $params[] = $filters['date_to'];
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Mettre à jour le statut d'une réservation
     */
    public function updateStatus($id, $adminId, $status, $extra = [])
    {
        $setClauses = ["status = ?"];
        $params = [$status];

        if ($status === 'confirmed') {
            $setClauses[] = "confirmed_at = NOW()";
        } elseif ($status === 'cancelled') {
            $setClauses[] = "cancelled_at = NOW()";
            if (!empty($extra['cancelled_reason'])) {
                $setClauses[] = "cancelled_reason = ?";
                $params[] = $extra['cancelled_reason'];
            }
        }

        if (isset($extra['admin_notes'])) {
            $setClauses[] = "admin_notes = ?";
            $params[] = $extra['admin_notes'];
        }

        $params[] = $id;
        $params[] = $adminId;

        $stmt = $this->pdo->prepare("
            UPDATE reservations SET " . implode(', ', $setClauses) . "
            WHERE id = ? AND admin_id = ?
        ");
        return $stmt->execute($params);
    }

    /**
     * Statistiques des réservations pour le dashboard admin
     */
    public function getStats($adminId)
    {
        $today = date('Y-m-d');

        // Réservations du jour
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) FROM reservations 
            WHERE admin_id = ? AND reservation_date = ? AND status IN ('pending', 'confirmed')
        ");
        $stmt->execute([$adminId, $today]);
        $todayCount = (int)$stmt->fetchColumn();

        // Réservations en attente
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) FROM reservations 
            WHERE admin_id = ? AND status = 'pending' AND reservation_date >= ?
        ");
        $stmt->execute([$adminId, $today]);
        $pendingCount = (int)$stmt->fetchColumn();

        // Réservations confirmées à venir
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) FROM reservations 
            WHERE admin_id = ? AND status = 'confirmed' AND reservation_date >= ?
        ");
        $stmt->execute([$adminId, $today]);
        $confirmedCount = (int)$stmt->fetchColumn();

        // Total couverts du jour
        $stmt = $this->pdo->prepare("
            SELECT COALESCE(SUM(party_size), 0) FROM reservations 
            WHERE admin_id = ? AND reservation_date = ? AND status IN ('pending', 'confirmed')
        ");
        $stmt->execute([$adminId, $today]);
        $todayCovers = (int)$stmt->fetchColumn();

        // Total réservations cette semaine
        $weekStart = date('Y-m-d', strtotime('monday this week'));
        $weekEnd = date('Y-m-d', strtotime('sunday this week'));
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) FROM reservations 
            WHERE admin_id = ? AND reservation_date BETWEEN ? AND ? AND status IN ('pending', 'confirmed')
        ");
        $stmt->execute([$adminId, $weekStart, $weekEnd]);
        $weekCount = (int)$stmt->fetchColumn();

        return [
            'today'     => $todayCount,
            'pending'   => $pendingCount,
            'confirmed' => $confirmedCount,
            'today_covers' => $todayCovers,
            'this_week' => $weekCount,
        ];
    }

    /**
     * Récupérer les réservations du jour pour un admin
     */
    public function getToday($adminId)
    {
        $today = date('Y-m-d');
        $stmt = $this->pdo->prepare("
            SELECT * FROM reservations 
            WHERE admin_id = ? AND reservation_date = ? AND status IN ('pending', 'confirmed')
            ORDER BY reservation_time ASC
        ");
        $stmt->execute([$adminId, $today]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Vérifier les disponibilités pour un créneau
     */
    public function countForSlot($adminId, $date, $time)
    {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) FROM reservations 
            WHERE admin_id = ? AND reservation_date = ? AND reservation_time = ? AND status IN ('pending', 'confirmed')
        ");
        $stmt->execute([$adminId, $date, $time]);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Récupérer les créneaux occupés pour une date donnée
     */
    public function getBookedSlots($adminId, $date, $maxPerSlot)
    {
        $stmt = $this->pdo->prepare("
            SELECT reservation_time, COUNT(*) as count
            FROM reservations 
            WHERE admin_id = ? AND reservation_date = ? AND status IN ('pending', 'confirmed')
            GROUP BY reservation_time
            HAVING count >= ?
        ");
        $stmt->execute([$adminId, $date, $maxPerSlot]);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $bookedSlots = [];
        foreach ($result as $row) {
            $bookedSlots[] = substr($row['reservation_time'], 0, 5);
        }
        return $bookedSlots;
    }

    /**
     * Supprimer une réservation
     */
    public function delete($id, $adminId)
    {
        $stmt = $this->pdo->prepare("DELETE FROM reservations WHERE id = ? AND admin_id = ?");
        return $stmt->execute([$id, $adminId]);
    }

    /**
     * Supprimer toutes les réservations d'un admin
     */
    public function deleteAll($adminId)
    {
        $stmt = $this->pdo->prepare("DELETE FROM reservations WHERE admin_id = ?");
        $stmt->execute([$adminId]);
        return $stmt->rowCount();
    }

    /**
     * Supprimer toutes les réservations d'un admin par statut
     */
    public function deleteByStatus($adminId, $status)
    {
        $stmt = $this->pdo->prepare("DELETE FROM reservations WHERE admin_id = ? AND status = ?");
        $stmt->execute([$adminId, $status]);
        return $stmt->rowCount();
    }

    /**
     * Marquer toutes les réservations comme terminées
     */
    public function completeAll($adminId)
    {
        $stmt = $this->pdo->prepare("
            UPDATE reservations 
            SET status = 'completed' 
            WHERE admin_id = ? AND status != 'completed'
        ");
        $stmt->execute([$adminId]);
        return $stmt->rowCount();
    }

    /**
     * Marquer automatiquement les réservations passées comme "completed"
     */
    public function autoComplete($adminId)
    {
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        $stmt = $this->pdo->prepare("
            UPDATE reservations 
            SET status = 'completed' 
            WHERE admin_id = ? AND reservation_date < ? AND status = 'confirmed'
        ");
        return $stmt->execute([$adminId, $yesterday]);
    }
}
