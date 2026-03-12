<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../Models/Admin.php';
require_once __DIR__ . '/../Models/PremiumFeature.php';
require_once __DIR__ . '/../Models/SiteVisit.php';

/**
 * Contrôleur pour les statistiques avancées (option premium)
 */
class StatsController extends BaseController
{
    public function __construct($pdo)
    {
        parent::__construct($pdo);
    }

    /**
     * Affiche la page des statistiques
     */
    public function show()
    {
        $this->requireLogin();

        $adminModel = new Admin($this->pdo);
        $admin = $adminModel->findById($_SESSION['admin_id']);

        if (!$admin) {
            $this->addErrorMessage('Administrateur non trouvé.');
            header('Location: ?page=login');
            exit;
        }

        $isSuperAdmin = ($admin->role === 'SUPER_ADMIN');

        // Vérifier l'accès premium (SUPER_ADMIN ou feature activée)
        $premiumFeature = new PremiumFeature($this->pdo);
        $hasAccess = $isSuperAdmin || $premiumFeature->isEnabled($_SESSION['admin_id'], 'advanced_analytics');

        if (!$hasAccess) {
            $this->addErrorMessage('Cette fonctionnalité nécessite l\'option Statistiques avancées. Activez-la dans les fonctionnalités premium.');
            header('Location: ?page=settings&section=premium');
            exit;
        }

        // Récupérer le slug du restaurant
        $slug = null;
        if ($admin->restaurant_id) {
            $stmt = $this->pdo->prepare("SELECT slug FROM restaurants WHERE id = ?");
            $stmt->execute([$admin->restaurant_id]);
            $slug = $stmt->fetchColumn() ?: null;
        }

        $messages = $this->getFlashMessages();
        $csrf_token = $this->getCsrfToken();

        $this->render('admin/stats', [
            'success_message' => $messages['success_message'],
            'error_message' => $messages['error_message'],
            'csrf_token' => $csrf_token,
            'restaurant_name' => $admin->restaurant_name ?? '',
            'slug' => $slug,
            'is_super_admin' => $isSuperAdmin,
        ]);
    }

    /**
     * API JSON : retourne les données de statistiques pour une période
     */
    public function getData()
    {
        $this->requireLogin();
        header('Content-Type: application/json');

        $adminModel = new Admin($this->pdo);
        $admin = $adminModel->findById($_SESSION['admin_id']);

        $isSuperAdmin = $admin && ($admin->role === 'SUPER_ADMIN');
        $premiumFeature = new PremiumFeature($this->pdo);
        $hasAccess = $isSuperAdmin || $premiumFeature->isEnabled($_SESSION['admin_id'], 'advanced_analytics');

        if (!$hasAccess) {
            echo json_encode(['success' => false, 'message' => 'Accès refusé.']);
            exit;
        }

        $days = max(1, min(365, (int)($_GET['days'] ?? 30)));
        $adminId = $_SESSION['admin_id'];
        $siteVisit = new SiteVisit($this->pdo);

        try {
            $data = [
                'success' => true,
                'period' => $days,
                'total_visits' => $siteVisit->getTotalVisits($adminId, $days),
                'unique_visitors' => $siteVisit->getUniqueVisitors($adminId, $days),
                'trend' => $siteVisit->getTrend($adminId, $days),
                'visits_per_day' => $siteVisit->getVisitsPerDay($adminId, $days),
                'devices' => $siteVisit->getDeviceBreakdown($adminId, $days),
                'browsers' => $siteVisit->getBrowserBreakdown($adminId, $days),
                'referrers' => $siteVisit->getTopReferrers($adminId, $days),
                'hours' => $siteVisit->getVisitsByHour($adminId, $days),
                'weekdays' => $siteVisit->getVisitsByDayOfWeek($adminId, $days),
            ];

            echo json_encode($data);
        } catch (Exception $e) {
            error_log('[Stats] getData error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erreur lors de la récupération des données.']);
        }
        exit;
    }
}
