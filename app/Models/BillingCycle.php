<?php
/**
 * Modèle pour gérer les cycles de facturation groupés avec prorata
 */
class BillingCycle
{
    private $pdo;
    private $defaultBillingDay = 15;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Calcule le montant au prorata pour une option premium
     */
    public function calculateProrata($priceMonthly, $activationDate, $nextBillingDate)
    {
        $activation = new DateTime($activationDate);
        $billing = new DateTime($nextBillingDate);
        
        // Si l'activation est après la date de facturation, on calcule pour le mois suivant
        if ($activation > $billing) {
            $billing->modify('+1 month');
        }
        
        $daysInMonth = (int)$billing->format('t');
        $daysRemaining = $billing->diff($activation)->days;
        
        // Calcul prorata : (jours restants / jours total) * prix mensuel
        $prorata = ($daysRemaining / $daysInMonth) * $priceMonthly;
        
        return round($prorata, 2);
    }

    /**
     * Détermine la prochaine date de facturation pour un utilisateur
     */
    public function getNextBillingDate($adminId)
    {
        $stmt = $this->pdo->prepare("
            SELECT billing_cycle_day, next_billing_date 
            FROM client_subscriptions 
            WHERE admin_id = ? AND status = 'active' AND plan_type = 'basique'
            LIMIT 1
        ");
        $stmt->execute([$adminId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result && $result['next_billing_date']) {
            return new DateTime($result['next_billing_date']);
        }
        
        // Par défaut : 15 du mois prochain
        $nextBilling = new DateTime();
        $nextBilling->setDate((int)$nextBilling->format('Y'), (int)$nextBilling->format('m') + 1, $this->defaultBillingDay);
        
        return $nextBilling;
    }

    /**
     * Calcule le temps restant jusqu'à la prochaine facturation
     */
    public function getTimeUntilNextBilling($adminId)
    {
        $nextBilling = $this->getNextBillingDate($adminId);
        $now = new DateTime();
        
        if ($nextBilling <= $now) {
            return null; // Expiré ou devrait être facturé
        }
        
        $diff = $now->diff($nextBilling);
        $timeLeft = [];
        
        if ($diff->days > 0) {
            $timeLeft[] = $diff->days . ' jour' . ($diff->days > 1 ? 's' : '');
        }
        if ($diff->h > 0) {
            $timeLeft[] = $diff->h . ' heure' . ($diff->h > 1 ? 's' : '');
        }
        
        return implode(' et ', $timeLeft) . ' restant' . (count($timeLeft) > 1 ? 's' : '');
    }

    /**
     * Met à jour le cycle de facturation lors de l'activation d'une option premium
     */
    public function updateBillingForPremiumFeature($adminId, $featureKey, $priceMonthly)
    {
        $nextBilling = $this->getNextBillingDate($adminId);
        $now = new DateTime();
        
        // Récupérer la date d'activation de la feature
        $stmt = $this->pdo->prepare("
            SELECT activated_at FROM premium_features 
            WHERE admin_id = ? AND feature_name = ?
        ");
        $stmt->execute([$adminId, $featureKey]);
        $activationDate = $stmt->fetchColumn();
        
        if ($activationDate) {
            $prorataAmount = $this->calculateProrata($priceMonthly, $activationDate, $nextBilling->format('Y-m-d H:i:s'));
            
            // Mettre à jour le montant prorata dans la table premium_features
            $stmt = $this->pdo->prepare("
                UPDATE premium_features 
                SET prorata_amount = ?, next_billing_date = ?
                WHERE admin_id = ? AND feature_name = ?
            ");
            $stmt->execute([$prorataAmount, $nextBilling->format('Y-m-d H:i:s'), $adminId, $featureKey]);
            
            return $prorataAmount;
        }
        
        return 0;
    }

    /**
     * Récupère tous les abonnements avec leurs dates de facturation
     */
    public function getBillingInfo($adminId)
    {
        $billingInfo = [];
        
        // Abonnement basique
        $stmt = $this->pdo->prepare("
            SELECT started_at, expires_at, next_billing_date, billing_cycle_day
            FROM client_subscriptions 
            WHERE admin_id = ? AND status = 'active' AND plan_type = 'basique'
        ");
        $stmt->execute([$adminId]);
        $basic = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($basic) {
            $billingInfo['basic'] = [
                'started_at' => $basic['started_at'],
                'expires_at' => $basic['expires_at'],
                'next_billing_date' => $basic['next_billing_date'],
                'billing_cycle_day' => $basic['billing_cycle_day'],
                'time_left' => $this->getTimeUntilNextBilling($adminId)
            ];
        }
        
        // Options premium
        $stmt = $this->pdo->prepare("
            SELECT feature_name, activated_at, prorata_amount, next_billing_date
            FROM premium_features 
            WHERE admin_id = ? AND is_active = 1
        ");
        $stmt->execute([$adminId]);
        $premium = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if ($premium) {
            $billingInfo['premium'] = [];
            foreach ($premium as $feature) {
                $billingInfo['premium'][$feature['feature_name']] = [
                    'activated_at' => $feature['activated_at'],
                    'prorata_amount' => $feature['prorata_amount'],
                    'next_billing_date' => $feature['next_billing_date']
                ];
            }
        }
        
        return $billingInfo;
    }
}
