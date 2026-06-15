<?php
require_once __DIR__ . '/../Models/ClientSubscription.php';
require_once __DIR__ . '/../Models/Admin.php';

/**
 * Controller pour la gestion des clients Premium
 */
class ClientManagementController extends BaseController
{
    private $subscriptionModel;

    public function __construct($pdo)
    {
        parent::__construct($pdo);
        $this->subscriptionModel = new ClientSubscription($pdo);
    }

    /**
     * Vérifie si l'admin connecté est SUPER_ADMIN (via BDD)
     */
    private function isSuperAdmin(): bool
    {
        $adminModel = new Admin($this->pdo);
        $currentAdmin = $adminModel->findById($_SESSION['admin_id']);
        return $currentAdmin && $currentAdmin->role === 'SUPER_ADMIN';
    }

    /**
     * Afficher la page de gestion des clients
     */
    public function show()
    {
        $this->requireLogin();
        
        // Seuls les super-admins peuvent accéder
        if (!$this->isSuperAdmin()) {
            $this->addErrorMessage('Accès réservé aux super-administrateurs.');
            header('Location: ?page=dashboard');
            exit;
        }

        // Rendre $pdo et csrf_token accessibles dans la vue
        $pdo = $this->pdo;
        $csrf_token = $this->getCsrfToken();

        // Charger la vue
        require_once __DIR__ . '/../Views/admin/manage-clients.php';
    }

    /**
     * Activer l'abonnement premium d'un client
     */
    public function activateSubscription()
    {
        $this->requireLogin();
        
        if (!$this->isSuperAdmin()) {
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
                    WHERE id = ?
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
        
        if (!$this->isSuperAdmin()) {
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
        
        if (!$this->isSuperAdmin()) {
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
                WHERE id = ?
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
        
        if (!$this->isSuperAdmin()) {
            $this->jsonResponse(['success' => false, 'message' => 'Accès non autorisé']);
            return;
        }

        $clientId = $_GET['client_id'] ?? '';

        if (empty($clientId)) {
            $this->jsonResponse(['success' => false, 'message' => 'ID client non spécifié']);
            return;
        }

        try {
            $subscription = $this->subscriptionModel->getSubscriptionById($clientId);
            
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
     * Suspendre l'abonnement d'un client
     */
    public function suspendSubscription()
    {
        $this->requireLogin();
        if (!$this->isSuperAdmin()) {
            $this->jsonResponse(['success' => false, 'message' => 'Accès non autorisé']);
            return;
        }
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
            $this->subscriptionModel->suspendSubscription($clientId, $_SESSION['admin_id']);
            $this->jsonResponse(['success' => true, 'message' => 'Abonnement suspendu avec succès']);
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Reactiver l'abonnement d'un client
     */
    public function reactivateSubscription()
    {
        $this->requireLogin();
        if (!$this->isSuperAdmin()) {
            $this->jsonResponse(['success' => false, 'message' => 'Accès non autorisé']);
            return;
        }
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
            $this->subscriptionModel->reactivateSubscription($clientId, $_SESSION['admin_id']);
            $this->jsonResponse(['success' => true, 'message' => 'Abonnement réactivé avec succès']);
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Supprimer un client
     */
    public function deleteClient()
    {
        $this->requireLogin();
        if (!$this->isSuperAdmin()) {
            $this->jsonResponse(['success' => false, 'message' => 'Accès non autorisé']);
            return;
        }
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
            $this->subscriptionModel->deleteClient($clientId);
            $this->jsonResponse(['success' => true, 'message' => 'Client supprimé avec succès']);
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
