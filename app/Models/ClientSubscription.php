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
     * Obtenir l'abonnement d'un client par admin_id
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
     * Obtenir un abonnement par son ID (clé primaire)
     */
    public function getSubscriptionById($subscriptionId)
    {
        $stmt = $this->pdo->prepare("
            SELECT cs.*, a.username, a.restaurant_name, a.email,
                   COALESCE(cs.features_enabled,
                       (SELECT CONCAT('[', GROUP_CONCAT(CONCAT('\"', pf.feature_name, '\"')), ']')
                        FROM premium_features pf
                        WHERE pf.admin_id = cs.admin_id AND pf.is_active = 1)
                   ) as features_enabled
            FROM client_subscriptions cs
            JOIN admins a ON cs.admin_id = a.id
            WHERE cs.id = ?
        ");
        $stmt->execute([$subscriptionId]);
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
    public function deactivateSubscription($subscriptionId, $cancelledBy = null)
    {
        $pdo = $this->pdo;
        
        try {
            $pdo->beginTransaction();

            // Récupérer l'admin_id avant de modifier
            $stmtInfo = $pdo->prepare("SELECT admin_id FROM client_subscriptions WHERE id = ?");
            $stmtInfo->execute([$subscriptionId]);
            $adminId = $stmtInfo->fetchColumn();

            // Mettre à jour le statut de l'abonnement
            $stmt = $pdo->prepare("
                UPDATE client_subscriptions 
                SET status = 'cancelled', 
                    updated_at = CURRENT_TIMESTAMP,
                    notes = CONCAT(IFNULL(notes, ''), '\nAnnulé le ', NOW(), ' par admin ID ', ?)
                WHERE id = ?
            ");
            $stmt->execute([$cancelledBy, $subscriptionId]);

            // Désactiver toutes les fonctionnalités premium si plus aucun abo actif
            $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM client_subscriptions WHERE admin_id = ? AND status = 'active'");
            $stmtCheck->execute([$adminId]);
            if ($stmtCheck->fetchColumn() == 0) {
                $this->syncPremiumFeatures($adminId, []);
            }

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
            SELECT cs.*, a.username, a.restaurant_name, a.email, a.created_at as client_since,
                   a.role,
                   COALESCE(cs.features_enabled, 
                       (SELECT CONCAT('[', GROUP_CONCAT(CONCAT('\"', pf.feature_name, '\"')), ']')
                        FROM premium_features pf 
                        WHERE pf.admin_id = cs.admin_id AND pf.is_active = 1)
                   ) as features_enabled
            FROM client_subscriptions cs
            JOIN admins a ON cs.admin_id = a.id
            WHERE a.role != 'SUPER_ADMIN'
        ";

        if ($status) {
            $sql .= " AND cs.status = ?";
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
            'basique' => ['google_reviews', 'advanced_analytics', 'online_booking', 'delivery_integration'],
            'pack_full' => ['google_reviews', 'advanced_analytics', 'online_booking', 'delivery_integration'],
            'premium' => ['google_reviews', 'advanced_analytics', 'online_booking', 'delivery_integration'],
            'pro' => ['google_reviews', 'advanced_analytics', 'online_booking', 'delivery_integration']
        ];

        return $plans[$planType] ?? ['google_reviews', 'advanced_analytics', 'online_booking', 'delivery_integration'];
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
    /**
     * Suspendre l'abonnement d'un client
     */
    public function suspendSubscription($subscriptionId, $suspendedBy = null)
    {
        $stmt = $this->pdo->prepare("
            UPDATE client_subscriptions 
            SET status = 'inactive', 
                updated_at = CURRENT_TIMESTAMP,
                notes = CONCAT(IFNULL(notes, ''), '\nSuspendu le ', NOW(), ' par admin ID ', ?)
            WHERE id = ?
        ");
        $stmt->execute([$suspendedBy, $subscriptionId]);
        return true;
    }

    /**
     * Reactiver l'abonnement d'un client
     */
    public function reactivateSubscription($subscriptionId, $reactivatedBy = null)
    {
        $pdo = $this->pdo;
        try {
            $pdo->beginTransaction();

            // Récupérer l'admin_id
            $stmtInfo = $pdo->prepare("SELECT admin_id FROM client_subscriptions WHERE id = ?");
            $stmtInfo->execute([$subscriptionId]);
            $adminId = $stmtInfo->fetchColumn();

            $stmt = $pdo->prepare("
                UPDATE client_subscriptions 
                SET status = 'active',
                    expires_at = CASE 
                        WHEN expires_at IS NULL OR expires_at < NOW() THEN DATE_ADD(NOW(), INTERVAL 1 MONTH)
                        ELSE expires_at
                    END,
                    updated_at = CURRENT_TIMESTAMP,
                    notes = CONCAT(IFNULL(notes, ''), '\nReactive le ', NOW(), ' par admin ID ', ?)
                WHERE id = ?
            ");
            $stmt->execute([$reactivatedBy, $subscriptionId]);

            // Reactiver toutes les features existantes pour cet admin
            $stmtFeatures = $pdo->prepare("SELECT feature_name FROM premium_features WHERE admin_id = ?");
            $stmtFeatures->execute([$adminId]);
            $existingFeatures = $stmtFeatures->fetchAll(PDO::FETCH_COLUMN);

            if (!empty($existingFeatures)) {
                // Réactiver les features existantes
                $this->syncPremiumFeatures($adminId, $existingFeatures);
            } else {
                // Aucune feature existante, utiliser les defaults du plan
                $sub = $this->getClientSubscription($adminId);
                if ($sub) {
                    $features = $this->getDefaultFeatures($sub['plan_type']);
                    $this->syncPremiumFeatures($adminId, $features);
                }
            }

            $pdo->commit();
            return true;
        } catch (Exception $e) {
            $pdo->rollback();
            throw $e;
        }
    }

    /**
     * Supprimer un client et toutes ses donnees associees
     */
    public function deleteClient($adminId)
    {
        $pdo = $this->pdo;
        try {
            $pdo->beginTransaction();

            // Supprimer les features premium
            $stmt = $pdo->prepare("DELETE FROM premium_features WHERE admin_id = ?");
            $stmt->execute([$adminId]);

            // Supprimer l'abonnement
            $stmt = $pdo->prepare("DELETE FROM client_subscriptions WHERE admin_id = ?");
            $stmt->execute([$adminId]);

            // Supprimer le compte admin
            $stmt = $pdo->prepare("DELETE FROM admins WHERE id = ? AND role != 'SUPER_ADMIN'");
            $stmt->execute([$adminId]);

            $pdo->commit();
            return true;
        } catch (Exception $e) {
            $pdo->rollback();
            throw $e;
        }
    }

}
