<?php
/**
 * Modèle pour gérer les abonnements premium des clients
 */
class ClientSubscription
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Obtenir l'abonnement d'un client
     */
    public function getClientSubscription($adminId)
    {
        $stmt = $this->pdo->prepare("
            SELECT cs.*, a.username, a.restaurant_name, a.email
            FROM client_subscriptions cs
            JOIN admins a ON cs.admin_id = a.id
            WHERE cs.admin_id = ?
        ");
        $stmt->execute([$adminId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Activer un abonnement premium pour un client
     */
    public function activatePremium($adminId, $planType = 'premium', $features = null, $createdBy = null)
    {
        $pdo = $this->pdo;
        
        try {
            $pdo->beginTransaction();

            // Définir les fonctionnalités par défaut selon le plan
            if ($features === null) {
                $features = $this->getDefaultFeatures($planType);
            }

            // Mettre à jour ou insérer l'abonnement
            $sql = "INSERT INTO client_subscriptions 
                    (admin_id, plan_type, status, features_enabled, started_at, expires_at, created_by)
                    VALUES (?, ?, 'active', ?, NOW(), DATE_ADD(NOW(), INTERVAL 1 MONTH), ?)
                    ON DUPLICATE KEY UPDATE
                    plan_type = VALUES(plan_type),
                    status = VALUES(status),
                    features_enabled = VALUES(features_enabled),
                    started_at = VALUES(started_at),
                    expires_at = VALUES(expires_at),
                    created_by = VALUES(created_by),
                    updated_at = CURRENT_TIMESTAMP";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $adminId,
                $planType,
                json_encode($features),
                $createdBy
            ]);

            // Activer les fonctionnalités dans premium_features
            $this->syncPremiumFeatures($adminId, $features);

            $pdo->commit();
            return true;
        } catch (Exception $e) {
            $pdo->rollback();
            throw $e;
        }
    }

    /**
     * Désactiver l'abonnement d'un client
     */
    public function deactivateSubscription($adminId, $cancelledBy = null)
    {
        $pdo = $this->pdo;
        
        try {
            $pdo->beginTransaction();

            // Mettre à jour le statut de l'abonnement
            $stmt = $pdo->prepare("
                UPDATE client_subscriptions 
                SET status = 'cancelled', 
                    updated_at = CURRENT_TIMESTAMP,
                    notes = CONCAT(IFNULL(notes, ''), '\nAnnulé le ', NOW(), ' par admin ID ', ?)
                WHERE admin_id = ?
            ");
            $stmt->execute([$cancelledBy, $adminId]);

            // Désactiver toutes les fonctionnalités premium
            $this->syncPremiumFeatures($adminId, []);

            $pdo->commit();
            return true;
        } catch (Exception $e) {
            $pdo->rollback();
            throw $e;
        }
    }

    /**
     * Obtenir tous les clients avec leurs abonnements
     */
    public function getAllClients($status = null)
    {
        $sql = "
            SELECT cs.*, a.username, a.restaurant_name, a.email, a.created_at as client_since
            FROM client_subscriptions cs
            JOIN admins a ON cs.admin_id = a.id
        ";

        if ($status) {
            $sql .= " WHERE cs.status = ?";
        }

        $sql .= " ORDER BY cs.created_at DESC";

        $stmt = $this->pdo->prepare($sql);
        if ($status) {
            $stmt->execute([$status]);
        } else {
            $stmt->execute();
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Vérifier si un client a accès à une fonctionnalité premium
     */
    public function hasFeature($adminId, $feature)
    {
        $subscription = $this->getClientSubscription($adminId);
        
        if (!$subscription || $subscription['status'] !== 'active') {
            return false;
        }

        $features = json_decode($subscription['features_enabled'] ?? '[]', true);
        return in_array($feature, $features);
    }

    /**
     * Obtenir les fonctionnalités par défaut selon le plan
     */
    private function getDefaultFeatures($planType)
    {
        $plans = [
            'free' => [],
            'premium' => ['google_reviews'],
            'pro' => ['google_reviews', 'advanced_analytics', 'online_booking']
        ];

        return $plans[$planType] ?? [];
    }

    /**
     * Synchroniser les fonctionnalités dans la table premium_features
     */
    private function syncPremiumFeatures($adminId, $features)
    {
        // Désactiver toutes les fonctionnalités
        $stmt = $this->pdo->prepare("
            UPDATE premium_features 
            SET is_active = 0, activated_at = NULL 
            WHERE admin_id = ?
        ");
        $stmt->execute([$adminId]);

        // Activer les fonctionnalités spécifiées
        if (!empty($features)) {
            foreach ($features as $feature) {
                $stmt = $this->pdo->prepare("
                    UPDATE premium_features 
                    SET is_active = 1, activated_at = NOW() 
                    WHERE admin_id = ? AND feature_name = ?
                ");
                $stmt->execute([$adminId, $feature]);
            }
        }
    }

    /**
     * Obtenir les statistiques des abonnements
     */
    public function getSubscriptionStats()
    {
        $stmt = $this->pdo->prepare("
            SELECT 
                plan_type,
                status,
                COUNT(*) as count,
                SUM(CASE WHEN status = 'active' AND expires_at > NOW() THEN 1 ELSE 0 END) as active_count
            FROM client_subscriptions 
            GROUP BY plan_type, status
            ORDER BY plan_type, status
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
