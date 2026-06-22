<?php
// Configuration sécurisée des cookies de session
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.use_strict_mode', 1);
// En production HTTPS, décommenter la ligne suivante :
// ini_set('session.cookie_secure', 1);

// Démarrer la session
session_start();

require_once __DIR__ . '/../config.php';

// Configuration SMTP (valeurs définies dans config.php, sinon fallback dev)
ini_set('SMTP', defined('SMTP_HOST') ? SMTP_HOST : 'localhost');
ini_set('smtp_port', defined('SMTP_PORT') ? SMTP_PORT : 1025);

if (!defined('DEV_SHOW_LINK')) {
    define('DEV_SHOW_LINK', false);
}

require_once __DIR__ . '/../app/Controllers/AdminController.php';
require_once __DIR__ . '/../app/Controllers/CardController.php';
require_once __DIR__ . '/../app/Controllers/ContactController.php';
require_once __DIR__ . '/../app/Controllers/LogoBannerController.php';
require_once __DIR__ . '/../app/Controllers/LegalController.php';
require_once __DIR__ . '/../app/Controllers/SettingsController.php';
require_once __DIR__ . '/../app/Controllers/DisplayController.php';
require_once __DIR__ . '/../app/Controllers/ServicesController.php';
require_once __DIR__ . '/../app/Controllers/SitemapController.php';
require_once __DIR__ . '/../app/Controllers/StripeController.php';
require_once __DIR__ . '/../app/Controllers/StatsController.php';
require_once __DIR__ . '/../app/Controllers/ReservationController.php';
require_once __DIR__ . '/../app/Controllers/FloorPlanController.php';
require_once __DIR__ . '/../app/Controllers/NotificationStreamController.php';
require_once __DIR__ . '/../app/Models/DemoToken.php';
require_once __DIR__ . '/../app/Helpers/FormHelper.php';
require_once __DIR__ . '/../app/Helpers/Validator.php';
require_once __DIR__ . '/../app/Helpers/old.php';
require_once __DIR__ . '/../app/Helpers/RateLimiter.php';

// Si l'admin est déjà connecté, redirection automatique vers le dashboard
if (isset($_SESSION['admin_logged']) && $_SESSION['admin_logged'] === true && !isset($_GET['page'])) {
    header('Location: ?page=dashboard');
    exit;
}

// Récupération de la page demandée (landing par défaut pour les visiteurs)
$page = $_GET['page'] ?? 'landing';

// Router simple en fonction de la page
switch ($page) {
    case 'landing':
        require __DIR__ . '/../app/Views/landing.php';
        break;

    case 'seed-reviews':
        // Protection : SUPER_ADMIN uniquement
        if (!isset($_SESSION['admin_logged']) || $_SESSION['admin_logged'] !== true) {
            header('Location: ?page=login');
            exit;
        }
        $adminModel = new Admin($pdo);
        $currentAdmin = $adminModel->findById($_SESSION['admin_id']);
        if (!$currentAdmin || $currentAdmin->role !== 'SUPER_ADMIN') {
            $_SESSION['error_message'] = "Accès refusé.";
            header('Location: ?page=dashboard');
            exit;
        }

        $action = $_GET['action'] ?? 'replace';
        $settingsController = new SettingsController($pdo);
        
        switch ($action) {
            case 'clear':
                $settingsController->clearReviews();
                break;
            case 'add-5':
                $settingsController->addReviews(5);
                break;
            case 'replace':
            default:
                $settingsController->seedReviews();
                break;
        }
        break;

    case 'auto-register':
        // En mode beta, l'inscription publique est désactivée (sur invitation uniquement)
        if (defined('BETA_MODE') && BETA_MODE === true) {
            $_SESSION['error_message'] = "L'inscription est actuellement sur invitation uniquement. Contactez-nous pour obtenir un accès.";
            header('Location: ?page=landing');
            exit;
        }
        $adminController = new AdminController($pdo);
        $adminController->autoRegister();  // Inscription libre depuis la page vitrine
        break;

    case 'verify-email':
        $adminController = new AdminController($pdo);
        $adminController->verifyEmail();  // Confirmation de l'adresse email après inscription
        break;

    case 'stripe-checkout':
        $stripeController = new StripeController($pdo);
        $stripeController->createCheckout();  // Créer une session Stripe Checkout
        break;

    case 'stripe-success':
        $stripeController = new StripeController($pdo);
        $stripeController->handleSuccess();  // Traiter le retour Stripe après paiement
        break;

    case 'stripe-webhook':
        $stripeController = new StripeController($pdo);
        $stripeController->handleWebhook();  // Webhook Stripe (serveur-à-serveur, activation fiable)
        break;

    case 'cancel-subscription':
        $stripeController = new StripeController($pdo);
        $stripeController->cancelSubscription();  // Résilier un abonnement ou une option premium
        break;

    case 'reactivate-subscription':
        $stripeController = new StripeController($pdo);
        $stripeController->reactivateSubscription();  // Réactiver un abonnement résilié (période de grâce)
        break;

    case 'send-invitation':
        $adminController = new AdminController($pdo);
        $adminController->sendInvitation();  // Affiche le formulaire et gère l'envoi
        break;

    case 'register':
        $adminController = new AdminController($pdo);
        $adminController->register();  // Formulaire de création de compte via invitation
        break;

    case 'feedback':
        require_once __DIR__ . '/../app/Controllers/FeedbackController.php';
        $feedbackController = new FeedbackController($pdo);
        $action = $_GET['action'] ?? '';
        if ($action === 'submit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $feedbackController->submit();
        } elseif ($action === 'dashboard') {
            $feedbackController->dashboard();
        } else {
            $feedbackController->show();
        }
        break;

    case 'login':
        $controller = new AdminController($pdo);
        $controller->login();  // Page de connexion
        break;

    case 'dashboard':
        $controller = new AdminController($pdo);
        $controller->dashboard();  // Tableau de bord admin
        break;

    case 'google-reviews-roadmap':
        // Vérifier que l'utilisateur est super admin
        if (!isset($_SESSION['admin_logged']) || !$_SESSION['admin_logged']) {
            header('Location: ?page=login');
            exit;
        }
        $adminModel = new Admin($pdo);
        $admin = $adminModel->findById($_SESSION['admin_id']);
        if (!$admin || $admin->role !== 'SUPER_ADMIN') {
            header('Location: ?page=dashboard');
            exit;
        }
        require_once __DIR__ . '/../app/Views/admin/google-reviews-roadmap.php';
        break;

    case 'edit-card':
        $controller = new CardController($pdo);
        $action = $_GET['action'] ?? '';
        if ($action === 'update-category-order') {
            $controller->updateCategoryOrder();
        } else {
            $controller->edit();  // Gestion de la carte (catégories + plats)
        }
        break;

    case 'view-card':
        $controller = new CardController($pdo);
        $controller->view();  // Affichage de la carte (catégories + plats) 
        break;

    case 'edit-contact':
        $controller = new ContactController($pdo);
        $controller->edit();  // Gestion des informations de contact
        break;

    case 'edit-logo-banner':
        $controller = new LogoBannerController($pdo);
        $action = $_GET['action'] ?? 'show';
        $allowed = ['show', 'uploadLogo', 'uploadBanner', 'deleteLogo', 'deleteBanner', 'updateBannerText', 'deleteBannerText'];
        if (in_array($action, $allowed)) {
            $controller->$action();
        } else {
            $controller->show();
        }
        break;

    case 'edit-services':
        $controller = new ServicesController($pdo);
        $action = $_GET['action'] ?? 'show';
        $allowed = ['show', 'save'];
        if (in_array($action, $allowed)) {
            $controller->$action();
        } else {
            $controller->show();
        }
        break;

    case 'edit-template':
        $controller = new SettingsController($pdo);
        $action = $_GET['action'] ?? 'showTemplates';
        if ($action === 'save-palette') {
            $controller->savePalette();
        } elseif ($action === 'save-layout') {
            $controller->saveLayout();
        } elseif ($action === 'save-template') {
            $controller->savePalette(); // Rétrocompatibilité
        } else {
            $controller->showTemplates();
        }
        break;

    case 'settings':
        $controller = new SettingsController($pdo);
        $action = $_GET['action'] ?? 'show';

        switch ($action) {
            case 'update-profile':
                $controller->updateProfile();
                break;
            case 'change-password':
                $controller->changePassword();
                break;
            case 'get-options':
                $controller->getOptions();
                break;
            case 'save-options-batch':
                $controller->saveOptionsBatch();
                break;
            case 'update-google-reviews':
                $controller->updateGoogleReviews();
                break;
            case 'seed-reviews':
                // Redirige vers la route top-level seed-reviews
                header('Location: ?page=seed-reviews');
                exit;
            case 'toggle-premium':
                $controller->togglePremium();
                break;
            case 'get-closure-dates':
                $controller->getClosureDates();
                break;
            case 'save-closure-dates':
                $controller->saveClosureDates();
                break;
            case 'test-delivery-connection':
                $controller->testDeliveryConnection();
                break;
            case 'save-delivery-config':
                $controller->saveDeliveryConfig();
                break;
            case 'get-delivery-configs':
                $controller->getDeliveryConfigs();
                break;
            default:
                $controller->show();
                break;
        }
        break;

    case 'logout':
        $controller = new AdminController($pdo);
        $controller->logout();  // Déconnexion
        break;

    case 'reset-password':
        $adminController = new AdminController($pdo);
        $adminController->resetPassword();  // Réinitialisation du mot de passe
        break;

    case 'reset-password-admin':
        $adminController = new AdminController($pdo);
        $adminController->resetPasswordAdmin();  // Réinitialisation du mot de passe (contexte admin)
        break;

    case 'display':
        $slug = $_GET['slug'] ?? '';
        if (empty($slug)) {
            http_response_code(404);
            require __DIR__ . '/../app/Views/errors/404.php';
            break;
        }
        $controller = new DisplayController($pdo);
        $controller->show($slug);
        break;

    case 'manage-clients':
        require_once __DIR__ . '/../app/Controllers/ClientManagementController.php';
        $controller = new ClientManagementController($pdo);
        $action = $_GET['action'] ?? 'show';

        switch ($action) {
            case 'activate-subscription':
                $controller->activateSubscription();
                break;
            case 'cancel-subscription':
                $controller->cancelSubscription();
                break;
            case 'extend-subscription':
                $controller->extendSubscription();
                break;
            case 'get-client-details':
                $controller->getClientDetails();
                break;
            case 'suspend-subscription':
                $controller->suspendSubscription();
                break;
            case 'reactivate-subscription':
                $controller->reactivateSubscription();
                break;
            case 'delete-client':
                $controller->deleteClient();
                break;
            default:
                $controller->show();
                break;
        }
        break;

    case 'demo':
        // Redirige vers la vitrine du restaurant de démo
        header('Location: ?page=display&slug=demo-menucraft');
        exit;

    case 'demo-access':
        // Accès démo via token temporaire (pour clients potentiels)
        $token = $_GET['token'] ?? '';
        if (empty($token)) {
            require __DIR__ . '/../app/Views/errors/demo-expired.php';
            break;
        }
        $demoTokenModel = new DemoToken($pdo);
        $demoTokenModel->cleanExpired(); // Nettoyage des clones expirés au passage
        $tokenData = $demoTokenModel->validate($token);
        if (!$tokenData) {
            require __DIR__ . '/../app/Views/errors/demo-expired.php';
            break;
        }
        // Auto-login dans le compte admin de démo (clone isolé)
        $_SESSION['admin_logged'] = true;
        $_SESSION['admin_id'] = $tokenData['admin_id'];
        $_SESSION['demo_mode'] = true;
        $_SESSION['demo_token'] = $token;
        $_SESSION['demo_expires_at'] = $tokenData['expires_at'];
        $_SESSION['demo_slug'] = $tokenData['demo_slug'] ?? '';
        header('Location: ?page=dashboard');
        exit;

    case 'generate-demo':
        // Génération d'un lien de démo (SUPER_ADMIN uniquement)
        if (!isset($_SESSION['admin_logged']) || $_SESSION['admin_logged'] !== true) {
            header('Location: ?page=login');
            exit;
        }
        $adminModel = new Admin($pdo);
        $currentAdmin = $adminModel->findById($_SESSION['admin_id']);
        if (!$currentAdmin || $currentAdmin->role !== 'SUPER_ADMIN') {
            $_SESSION['pendingToast'] = json_encode(['message' => 'Accès refusé.', 'type' => 'error']);
            header('Location: ?page=dashboard');
            exit;
        }
        $demoTokenModel = new DemoToken($pdo);
        // Vérifier que la démo existe
        if (!$demoTokenModel->getDemoAdminId()) {
            $_SESSION['pendingToast'] = json_encode(['message' => "Le restaurant de démo n'existe pas.", 'type' => 'error']);
            header('Location: ?page=dashboard');
            exit;
        }
        $result = $demoTokenModel->generate($_SESSION['admin_id']);
        if ($result) {
            $demoLink = SITE_URL . '/index.php?page=demo-access&token=' . $result['token'];
            $_SESSION['pendingToast'] = json_encode(['message' => 'Lien de démo généré avec succès (valide 3 jours).', 'type' => 'success']);
        } else {
            $_SESSION['pendingToast'] = json_encode(['message' => 'Erreur lors de la génération du lien.', 'type' => 'error']);
        }
        // Nettoyer les tokens expirés au passage
        $demoTokenModel->cleanExpired();
        header('Location: ?page=dashboard');
        exit;

    case 'update-demo-label':
        // Mise à jour du label d'un token (AJAX, SUPER_ADMIN uniquement)
        header('Content-Type: application/json');
        if (!isset($_SESSION['admin_logged']) || $_SESSION['admin_logged'] !== true) {
            echo json_encode(['success' => false, 'message' => 'Non connecté']);
            exit;
        }
        $adminModel = new Admin($pdo);
        $currentAdmin = $adminModel->findById($_SESSION['admin_id']);
        if (!$currentAdmin || $currentAdmin->role !== 'SUPER_ADMIN') {
            echo json_encode(['success' => false, 'message' => 'Accès refusé']);
            exit;
        }
        // Vérification CSRF
        $baseController = new BaseController($pdo);
        if (!$baseController->verifyCsrfTokenPublic($_POST['csrf_token'] ?? null)) {
            echo json_encode(['success' => false, 'message' => 'Token CSRF invalide']);
            exit;
        }
        $tokenId = $_POST['id'] ?? null;
        $label = $_POST['label'] ?? '';
        if ($tokenId) {
            $demoTokenModel = new DemoToken($pdo);
            $demoTokenModel->updateLabel($tokenId, $label);
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'ID manquant']);
        }
        exit;

    case 'delete-demo-token':
        // Suppression d'un token de démo (SUPER_ADMIN uniquement, POST + CSRF)
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ?page=dashboard');
            exit;
        }
        if (!isset($_SESSION['admin_logged']) || $_SESSION['admin_logged'] !== true) {
            header('Location: ?page=login');
            exit;
        }
        $adminModel = new Admin($pdo);
        $currentAdmin = $adminModel->findById($_SESSION['admin_id']);
        if (!$currentAdmin || $currentAdmin->role !== 'SUPER_ADMIN') {
            $_SESSION['pendingToast'] = json_encode(['message' => 'Accès refusé.', 'type' => 'error']);
            header('Location: ?page=dashboard');
            exit;
        }
        // Vérification CSRF
        $baseController = new BaseController($pdo);
        if (!$baseController->verifyCsrfTokenPublic($_POST['csrf_token'] ?? null)) {
            $_SESSION['pendingToast'] = json_encode(['message' => 'Token de sécurité invalide.', 'type' => 'error']);
            header('Location: ?page=dashboard');
            exit;
        }
        $tokenId = $_POST['id'] ?? null;
        if ($tokenId) {
            $demoTokenModel = new DemoToken($pdo);
            $demoTokenModel->delete(intval($tokenId));
            $_SESSION['pendingToast'] = json_encode(['message' => 'Lien de démo révoqué.', 'type' => 'success']);
        }
        header('Location: ?page=dashboard');
        exit;

    case 'delete-demo-tokens-bulk':
        // Suppression en masse de tokens de démo (SUPER_ADMIN uniquement, POST + CSRF)
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ?page=dashboard');
            exit;
        }
        if (!isset($_SESSION['admin_logged']) || $_SESSION['admin_logged'] !== true) {
            header('Location: ?page=login');
            exit;
        }
        $adminModel = new Admin($pdo);
        $currentAdmin = $adminModel->findById($_SESSION['admin_id']);
        if (!$currentAdmin || $currentAdmin->role !== 'SUPER_ADMIN') {
            $_SESSION['pendingToast'] = json_encode(['message' => 'Accès refusé.', 'type' => 'error']);
            header('Location: ?page=dashboard');
            exit;
        }
        // Vérification CSRF
        $baseController = new BaseController($pdo);
        if (!$baseController->verifyCsrfTokenPublic($_POST['csrf_token'] ?? null)) {
            $_SESSION['pendingToast'] = json_encode(['message' => 'Token de sécurité invalide.', 'type' => 'error']);
            header('Location: ?page=dashboard');
            exit;
        }
        $idsParam = $_POST['ids'] ?? '';
        $ids = array_filter(array_map('intval', explode(',', $idsParam)));
        if (!empty($ids)) {
            $demoTokenModel = new DemoToken($pdo);
            $count = 0;
            foreach ($ids as $id) {
                $demoTokenModel->delete($id);
                $count++;
            }
            $_SESSION['pendingToast'] = json_encode(['message' => "$count lien(s) de démo révoqué(s).", 'type' => 'success']);
        }
        header('Location: ?page=dashboard');
        exit;

    case 'demo-logout':
        // Déconnexion de la session démo — nettoyer les données clonées
        if (!empty($_SESSION['demo_token'])) {
            $demoTokenModel = new DemoToken($pdo);
            $demoTokenModel->cleanExpired();
        }
        session_destroy();
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <script>
                sessionStorage.setItem('pendingToast', JSON.stringify({
                    message: 'Votre session de démonstration est terminée. Merci de votre intérêt !',
                    type: 'success'
                }));
                window.location.href = '?page=login';
            </script>
        </head>
        <body></body>
        </html>
        <?php
        exit;

    case 'seed-demo':
        // Création/suppression de la démo (SUPER_ADMIN uniquement)
        if (!isset($_SESSION['admin_logged']) || $_SESSION['admin_logged'] !== true) {
            header('Location: ?page=login');
            exit;
        }
        $adminModel = new Admin($pdo);
        $currentAdmin = $adminModel->findById($_SESSION['admin_id']);
        if (!$currentAdmin || $currentAdmin->role !== 'SUPER_ADMIN') {
            $_SESSION['pendingToast'] = json_encode(['message' => 'Accès refusé.', 'type' => 'error']);
            header('Location: ?page=dashboard');
            exit;
        }
        require_once __DIR__ . '/../app/Seeds/DemoSeeder.php';
        $seeder = new DemoSeeder($pdo);
        $action = $_GET['action'] ?? 'run';
        if ($action === 'clean') {
            $seeder->clean();
            $_SESSION['pendingToast'] = json_encode(['message' => 'Démo supprimée avec succès.', 'type' => 'success']);
        } else {
            if ($seeder->demoExists()) {
                $_SESSION['pendingToast'] = json_encode(['message' => 'La démo existe déjà.', 'type' => 'error']);
            } elseif ($seeder->run()) {
                $_SESSION['pendingToast'] = json_encode(['message' => 'Démo créée avec succès !', 'type' => 'success']);
            } else {
                $_SESSION['pendingToast'] = json_encode(['message' => 'Erreur lors de la création de la démo.', 'type' => 'error']);
            }
        }
        header('Location: ?page=dashboard');
        exit;

    case 'stats':
        $controller = new StatsController($pdo);
        $controller->show();
        break;

    case 'stats-data':
        $controller = new StatsController($pdo);
        $controller->getData();
        break;

    // Réservations en ligne (premium)
    case 'reservations':
        $controller = new ReservationController($pdo);
        $controller->show();
        break;

    case 'reservation-update-status':
        $controller = new ReservationController($pdo);
        $controller->updateStatus();
        break;

    case 'reservation-update-datetime':
        $controller = new ReservationController($pdo);
        $controller->updateDateTime();
        break;

    case 'reservation-delete':
        $controller = new ReservationController($pdo);
        $controller->deleteReservation();
        break;

    case 'reservation-delete-all':
        $controller = new ReservationController($pdo);
        $controller->deleteAllReservations();
        break;

    case 'reservation-delete-completed':
        $controller = new ReservationController($pdo);
        $controller->deleteCompletedReservations();
        break;

    case 'reservation-complete-all':
        $controller = new ReservationController($pdo);
        $controller->completeAllReservations();
        break;

    case 'reservation-save-settings':
        $controller = new ReservationController($pdo);
        $controller->saveSettings();
        break;

    case 'reservation-get-tables':
        $controller = new ReservationController($pdo);
        $controller->getAvailableTables();
        break;

    case 'reservation-get-settings':
        $controller = new ReservationController($pdo);
        $controller->getBookingSettings();
        break;

    // API publique réservations (vitrine)
    case 'booking-submit':
        $controller = new ReservationController($pdo);
        $controller->publicBook();
        break;

    case 'booking-slots':
        $controller = new ReservationController($pdo);
        $controller->publicGetSlots();
        break;

    case 'get-pending-reservations':
        $controller = new ReservationController($pdo);
        $controller->getPendingReservations();
        break;

    case 'get-day-reservations':
        $controller = new ReservationController($pdo);
        $controller->getDayReservations();
        break;

    case 'notification-stream':
        $controller = new NotificationStreamController($pdo);
        $controller->stream();
        break;

    // Plan de salle
    case 'floor-plan':
        $controller = new FloorPlanController($pdo);
        $controller->show();
        break;

    case 'floor-plan-create-floor':
        $controller = new FloorPlanController($pdo);
        $controller->createFloor();
        break;

    case 'floor-plan-update-floor':
        $controller = new FloorPlanController($pdo);
        $controller->updateFloor();
        break;

    case 'floor-plan-delete-floor':
        $controller = new FloorPlanController($pdo);
        $controller->deleteFloor();
        break;

    case 'floor-plan-delete-all-floors':
        $controller = new FloorPlanController($pdo);
        $controller->deleteAllFloors();
        break;

    case 'floor-plan-clear-floor':
        $controller = new FloorPlanController($pdo);
        $controller->clearFloor();
        break;

    case 'floor-plan-create-table':
        $controller = new FloorPlanController($pdo);
        $controller->createTable();
        break;

    case 'floor-plan-update-table':
        $controller = new FloorPlanController($pdo);
        $controller->updateTable();
        break;

    case 'floor-plan-delete-table':
        $controller = new FloorPlanController($pdo);
        $controller->deleteTable();
        break;

    case 'floor-plan-create-element':
        $controller = new FloorPlanController($pdo);
        $controller->createElement();
        break;

    case 'floor-plan-update-element':
        $controller = new FloorPlanController($pdo);
        $controller->updateElement();
        break;

    case 'floor-plan-delete-element':
        $controller = new FloorPlanController($pdo);
        $controller->deleteElement();
        break;

    case 'floor-plan-get-data':
        $controller = new FloorPlanController($pdo);
        $controller->getFloorData();
        break;

    case 'sitemap':
        $controller = new SitemapController($pdo);
        $controller->generate();
        break;

    case 'legal':
        $controller = new LegalController($pdo);
        $controller->show();
        break;

    default:
        http_response_code(404);
        require __DIR__ . '/../app/Views/errors/404.php';
        break;
}
