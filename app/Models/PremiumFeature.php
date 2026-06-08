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
     * Prend en compte expires_at : si expiré, la feature n'est plus active
     */
    public function isEnabled($adminId, $featureName)
    {
        $stmt = $this->pdo->prepare("
            SELECT is_active, expires_at 
            FROM premium_features 
            WHERE admin_id = ? AND feature_name = ?
        ");
        $stmt->execute([$adminId, $featureName]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$row || (int)$row['is_active'] !== 1) {
            return false;
        }
        // Si expires_at est défini et dépassé, la feature est expirée
        if (!empty($row['expires_at']) && strtotime($row['expires_at']) < time()) {
            return false;
        }
        return true;
    }

    /**
     * Activer une fonctionnalité premium pour un admin
     * @param int|null $durationMonths Durée en mois (null = pas d'expiration)
     */
    public function enable($adminId, $featureName, $durationMonths = null)
    {
        $this->ensureFeatureExists($adminId, $featureName);
        if ($durationMonths) {
            $stmt = $this->pdo->prepare("
                UPDATE premium_features 
                SET is_active = 1, activated_at = NOW(), cancelled_at = NULL,
                    expires_at = DATE_ADD(NOW(), INTERVAL ? MONTH)
                WHERE admin_id = ? AND feature_name = ?
            ");
            return $stmt->execute([$durationMonths, $adminId, $featureName]);
        } else {
            $stmt = $this->pdo->prepare("
                UPDATE premium_features 
                SET is_active = 1, activated_at = NOW(), cancelled_at = NULL, expires_at = NULL
                WHERE admin_id = ? AND feature_name = ?
            ");
            return $stmt->execute([$adminId, $featureName]);
        }
    }

    /**
     * Désactiver une fonctionnalité premium pour un admin (immédiat)
     */
    public function disable($adminId, $featureName)
    {
        $this->ensureFeatureExists($adminId, $featureName);
        $stmt = $this->pdo->prepare("
            UPDATE premium_features 
            SET is_active = 0, activated_at = NULL, expires_at = NULL, cancelled_at = NULL
            WHERE admin_id = ? AND feature_name = ?
        ");
        return $stmt->execute([$adminId, $featureName]);
    }

    /**
     * Marquer une fonctionnalité comme résiliée (garde l'accès jusqu'à expires_at)
     */
    public function markCancelled($adminId, $featureName)
    {
        $this->ensureFeatureExists($adminId, $featureName);
        $stmt = $this->pdo->prepare("
            UPDATE premium_features 
            SET cancelled_at = NOW()
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
            WHERE admin_id = ? AND status IN ('active', 'cancelled') AND (expires_at IS NULL OR expires_at > NOW())
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
                'price_monthly' => 3.99,
                'price_annual'  => 2.99,
            ],
            'advanced_analytics' => [
                'name'          => 'Statistiques avancées',
                'description'   => 'Analyse détaillée du trafic et des performances',
                'icon'          => 'fa-chart-line',
                'price_monthly' => 3.99,
                'price_annual'  => 2.99,
            ],
            'online_booking' => [
                'name'          => 'Réservations en ligne',
                'description'   => 'Système de réservation intégré',
                'icon'          => 'fa-calendar-check',
                'price_monthly' => 10.99,
                'price_annual'  => 8.99,
            ],
            'delivery_integration' => [
                'name'          => 'Intégration livraison',
                'description'   => 'Connectez Uber Eats, Deliveroo, etc.',
                'icon'          => 'fa-motorcycle',
                'price_monthly' => 3.99,
                'price_annual'  => 2.99,
            ],
        ];
    }

    /**
     * Retourne les packs disponibles (tout inclus : basique + toutes les options premium)
     */
    public function getPackFull()
    {
        $features = $this->getAvailableFeatures();
        $individualTotal = 11.99; // basique
        foreach ($features as $f) {
            $individualTotal += $f['price_monthly'];
        }
        $individualTotal = round($individualTotal, 2);

        return [
            'name'             => 'Pack Full',
            'description'      => 'Abonnement Basique + toutes les options premium',
            'icon'             => 'fa-gem',
            'includes'         => array_keys($features),
            'individual_total' => $individualTotal,
            'prices' => [
                '1_month'  => ['price' => 29.99, 'duration_months' => 1,  'label' => '1 mois'],
                '3_months' => ['price' => 26.99, 'duration_months' => 3,  'label' => '3 mois',  'total' => 80.97],
                '1_year'   => ['price' => 22.99, 'duration_months' => 12, 'label' => '1 an',    'total' => 275.88],
            ],
        ];
    }
}
