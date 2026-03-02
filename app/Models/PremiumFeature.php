<?php
/**
 * Modèle pour gérer les fonctionnalités premium
 */
class PremiumFeature
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Vérifier si une fonctionnalité premium est activée pour un admin
     */
    public function isEnabled($adminId, $featureName)
    {
        $stmt = $this->pdo->prepare("
            SELECT is_active 
            FROM premium_features 
            WHERE admin_id = ? AND feature_name = ?
        ");
        $stmt->execute([$adminId, $featureName]);
        $result = $stmt->fetchColumn();
        return (int)$result === 1;
    }

    /**
     * Activer une fonctionnalité premium pour un admin
     */
    public function enable($adminId, $featureName)
    {
        $this->ensureFeatureExists($adminId, $featureName);
        $stmt = $this->pdo->prepare("
            UPDATE premium_features 
            SET is_active = 1, activated_at = NOW() 
            WHERE admin_id = ? AND feature_name = ?
        ");
        return $stmt->execute([$adminId, $featureName]);
    }

    /**
     * Désactiver une fonctionnalité premium pour un admin
     */
    public function disable($adminId, $featureName)
    {
        $this->ensureFeatureExists($adminId, $featureName);
        $stmt = $this->pdo->prepare("
            UPDATE premium_features 
            SET is_active = 0, activated_at = NULL 
            WHERE admin_id = ? AND feature_name = ?
        ");
        return $stmt->execute([$adminId, $featureName]);
    }

    /**
     * Basculer l'état d'une fonctionnalité premium
     */
    public function toggle($adminId, $featureName)
    {
        if ($this->isEnabled($adminId, $featureName)) {
            return $this->disable($adminId, $featureName);
        } else {
            return $this->enable($adminId, $featureName);
        }
    }

    /**
     * Obtenir toutes les fonctionnalités premium d'un admin
     */
    public function getAllFeatures($adminId)
    {
        // S'assurer que toutes les fonctionnalités existent pour cet admin
        $this->ensureAllFeaturesExist($adminId);
        
        $stmt = $this->pdo->prepare("
            SELECT feature_name, is_active, activated_at 
            FROM premium_features 
            WHERE admin_id = ?
            ORDER BY feature_name
        ");
        $stmt->execute([$adminId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Vérifier si un admin a un abonnement premium actif
     */
    public function hasActiveSubscription($adminId)
    {
        $stmt = $this->pdo->prepare("
            SELECT status, plan_type, features_enabled, expires_at
            FROM client_subscriptions 
            WHERE admin_id = ? AND status = 'active' AND (expires_at IS NULL OR expires_at > NOW())
        ");
        $stmt->execute([$adminId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Vérifier si une feature est incluse dans l'abonnement d'un admin
     */
    public function isFeatureInSubscription($adminId, $featureName)
    {
        $subscription = $this->hasActiveSubscription($adminId);
        if (!$subscription) {
            return false;
        }

        // Plan pro = tout inclus
        if ($subscription['plan_type'] === 'pro') {
            return true;
        }

        // Vérifier dans les features_enabled du plan
        $features = json_decode($subscription['features_enabled'] ?? '[]', true);
        if (!empty($features) && in_array($featureName, $features)) {
            return true;
        }

        // Features par défaut selon le plan
        $defaultFeatures = [
            'free' => [],
            'premium' => ['google_reviews'],
            'pro' => ['google_reviews', 'advanced_analytics', 'online_booking', 'delivery_integration']
        ];

        $planFeatures = $defaultFeatures[$subscription['plan_type']] ?? [];
        return in_array($featureName, $planFeatures);
    }

    /**
     * S'assurer qu'une ligne existe pour un admin/feature donné
     */
    private function ensureFeatureExists($adminId, $featureName)
    {
        $stmt = $this->pdo->prepare("
            INSERT IGNORE INTO premium_features (admin_id, feature_name, is_active) 
            VALUES (?, ?, 0)
        ");
        $stmt->execute([$adminId, $featureName]);
    }

    /**
     * S'assurer que toutes les fonctionnalités existent pour un admin
     */
    private function ensureAllFeaturesExist($adminId)
    {
        $features = array_keys($this->getAvailableFeatures());
        foreach ($features as $feature) {
            $this->ensureFeatureExists($adminId, $feature);
        }
    }

    public function getAvailableFeatures()
    {
        return [
            'google_reviews' => [
                'name'          => 'Avis Google',
                'description'   => 'Afficher les avis Google sur votre site vitrine',
                'icon'          => 'fa-star',
                'price_monthly' => 5,
                'price_annual'  => 4,
            ],
            'advanced_analytics' => [
                'name'          => 'Statistiques avancées',
                'description'   => 'Analyse détaillée du trafic et des performances',
                'icon'          => 'fa-chart-line',
                'price_monthly' => 5,
                'price_annual'  => 4,
            ],
            'online_booking' => [
                'name'          => 'Réservations en ligne',
                'description'   => 'Système de réservation intégré',
                'icon'          => 'fa-calendar-check',
                'price_monthly' => 8,
                'price_annual'  => 6,
            ],
            'delivery_integration' => [
                'name'          => 'Intégration livraison',
                'description'   => 'Connectez Uber Eats, Deliveroo, etc.',
                'icon'          => 'fa-motorcycle',
                'price_monthly' => 7,
                'price_annual'  => 6,
            ],
        ];
    }
}
