<?php
/**
 * Controller pour la gestion des clients Premium
 */
class ClientManagementController extends BaseController
{
    private $subscriptionModel;

    public function __construct($pdo)
    {
        parent::__construct($pdo);
        require_once __DIR__ . '/../Models/ClientSubscription.php';
        $this->subscriptionModel = new ClientSubscription($pdo);
    }

    /**
     * Afficher la page de gestion des clients
     */
    public function show()
    {
        $this->requireLogin();
        
        // Seuls les super-admins peuvent accéder
        if ($_SESSION['admin_role'] !== 'SUPER_ADMIN') {
            $this->addErrorMessage('Accès réservé aux super-administrateurs.');
            header('Location: ?page=dashboard');
            exit;
        }

        // Charger la vue
        require_once __DIR__ . '/../Views/admin/manage-clients.php';
    }

    /**
     * Activer l'abonnement premium d'un client
     */
    public function activateSubscription()
    {
        $this->requireLogin();
        
        if ($_SESSION['admin_role'] !== 'SUPER_ADMIN') {
            $this->jsonResponse(['success' => false, 'message' => 'Accès non autorisé']);
            return;
        }

        // Vérifier le CSRF
        if (!$this->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            $this->jsonResponse(['success' => false, 'message' => 'Token de sécurité invalide']);
            return;
        }

        $clientId = $_POST['client_id'] ?? '';
        $planType = $_POST['plan_type'] ?? 'premium';
        $duration = intval($_POST['duration'] ?? 1);

        if (empty($clientId)) {
            $this->jsonResponse(['success' => false, 'message' => 'ID client non spécifié']);
            return;
        }

        try {
            // Activer l'abonnement
            $this->subscriptionModel->activatePremium(
                $clientId,
                $planType,
                null, // fonctionnalités par défaut selon le plan
                $_SESSION['admin_id']
            );

            // Prolonger selon la durée choisie
            if ($duration > 1) {
                $stmt = $this->pdo->prepare("
                    UPDATE client_subscriptions 
                    SET expires_at = DATE_ADD(expires_at, INTERVAL ? MONTH)
                    WHERE admin_id = ?
                ");
                $stmt->execute([$duration - 1, $clientId]);
            }

            $this->jsonResponse([
                'success' => true,
                'message' => "Abonnement {$planType} activé avec succès pour {$duration} mois"
            ]);

        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Annuler l'abonnement d'un client
     */
    public function cancelSubscription()
    {
        $this->requireLogin();
        
        if ($_SESSION['admin_role'] !== 'SUPER_ADMIN') {
            $this->jsonResponse(['success' => false, 'message' => 'Accès non autorisé']);
            return;
        }

        // Vérifier le CSRF
        if (!$this->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            $this->jsonResponse(['success' => false, 'message' => 'Token de sécurité invalide']);
            return;
        }

        $clientId = $_POST['client_id'] ?? '';

        if (empty($clientId)) {
            $this->jsonResponse(['success' => false, 'message' => 'ID client non spécifié']);
            return;
        }

        try {
            $this->subscriptionModel->deactivateSubscription(
                $clientId,
                $_SESSION['admin_id']
            );

            $this->jsonResponse([
                'success' => true,
                'message' => 'Abonnement annulé avec succès'
            ]);

        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Prolonger l'abonnement d'un client
     */
    public function extendSubscription()
    {
        $this->requireLogin();
        
        if ($_SESSION['admin_role'] !== 'SUPER_ADMIN') {
            $this->jsonResponse(['success' => false, 'message' => 'Accès non autorisé']);
            return;
        }

        // Vérifier le CSRF
        if (!$this->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            $this->jsonResponse(['success' => false, 'message' => 'Token de sécurité invalide']);
            return;
        }

        $clientId = $_POST['client_id'] ?? '';
        $months = intval($_POST['months'] ?? 1);

        if (empty($clientId) || $months <= 0) {
            $this->jsonResponse(['success' => false, 'message' => 'Paramètres invalides']);
            return;
        }

        try {
            $stmt = $this->pdo->prepare("
                UPDATE client_subscriptions 
                SET expires_at = DATE_ADD(COALESCE(expires_at, NOW()), INTERVAL ? MONTH),
                    status = 'active',
                    updated_at = CURRENT_TIMESTAMP
                WHERE admin_id = ?
            ");
            $stmt->execute([$months, $clientId]);

            $this->jsonResponse([
                'success' => true,
                'message' => "Abonnement prolongé de {$months} mois"
            ]);

        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Obtenir les détails d'un client
     */
    public function getClientDetails()
    {
        $this->requireLogin();
        
        if ($_SESSION['admin_role'] !== 'SUPER_ADMIN') {
            $this->jsonResponse(['success' => false, 'message' => 'Accès non autorisé']);
            return;
        }

        $clientId = $_GET['client_id'] ?? '';

        if (empty($clientId)) {
            $this->jsonResponse(['success' => false, 'message' => 'ID client non spécifié']);
            return;
        }

        try {
            $subscription = $this->subscriptionModel->getClientSubscription($clientId);
            
            if (!$subscription) {
                $this->jsonResponse(['success' => false, 'message' => 'Client non trouvé']);
                return;
            }

            $this->jsonResponse([
                'success' => true,
                'data' => $subscription
            ]);

        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Réponse JSON
     */
    private function jsonResponse($data)
    {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
