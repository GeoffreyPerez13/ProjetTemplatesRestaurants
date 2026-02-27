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
        return $result === 1;
    }

    /**
     * Activer une fonctionnalité premium pour un admin
     */
    public function enable($adminId, $featureName)
    {
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
     * Obtenir les fonctionnalités premium disponibles
     */
    public function getAvailableFeatures()
    {
        return [
            'google_reviews' => [
                'name' => 'Avis Google',
                'description' => 'Afficher les avis Google sur votre site vitrine',
                'icon' => 'fa-star'
            ],
            'advanced_analytics' => [
                'name' => 'Statistiques avancées',
                'description' => 'Analyse détaillée du trafic et des performances',
                'icon' => 'fa-chart-line'
            ],
            'online_booking' => [
                'name' => 'Réservations en ligne',
                'description' => 'Système de réservation intégré',
                'icon' => 'fa-calendar-check'
            ],
            'delivery_integration' => [
                'name' => 'Intégration livraison',
                'description' => 'Connectez Uber Eats, Deliveroo, etc.',
                'icon' => 'fa-motorcycle'
            ]
        ];
    }
}
