<?php
ini_set('SMTP', 'localhost');   // Pour l'envoi de mails en dev
ini_set('smtp_port', 1025);     // Port du serveur SMTP local (ex : MailHog)
define('DEV_SHOW_LINK', true);   // Constante dev pour afficher les liens directs

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../app/Controllers/AdminController.php';
require_once __DIR__ . '/../app/Controllers/CardController.php';
require_once __DIR__ . '/../app/Controllers/ContactController.php';
require_once __DIR__ . '/../app/Controllers/LogoBannerController.php';
require_once __DIR__ . '/../app/Controllers/LegalController.php';
require_once __DIR__ . '/../app/Controllers/SettingsController.php';
require_once __DIR__ . '/../app/Controllers/DisplayController.php';
require_once __DIR__ . '/../app/Controllers/ServicesController.php';
require_once __DIR__ . '/../app/Controllers/SitemapController.php';
require_once __DIR__ . '/../app/Models/DemoToken.php';
require_once __DIR__ . '/../app/Helpers/FormHelper.php';
require_once __DIR__ . '/../app/Helpers/Validator.php';
require_once __DIR__ . '/../app/Helpers/old.php';

// Si l'admin est déjà connecté, redirection automatique vers le dashboard
if (isset($_SESSION['admin_logged']) && $_SESSION['admin_logged'] === true && !isset($_GET['page'])) {
    header('Location: ?page=dashboard');
    exit;
}

// Récupération de la page demandée
$page = $_GET['page'] ?? 'login';

// Router simple en fonction de la page
switch ($page) {
    case 'send-invitation':
        $adminController = new AdminController($pdo);
        $adminController->sendInvitation();  // Affiche le formulaire et gère l'envoi
        break;

    case 'register':
        $adminController = new AdminController($pdo);
        $adminController->register();  // Formulaire de création de compte via invitation
        break;

    case 'login':
        $controller = new AdminController($pdo);
        $controller->login();  // Page de connexion
        break;

    case 'dashboard':
        $controller = new AdminController($pdo);
        $controller->dashboard();  // Tableau de bord admin
        break;

    case 'edit-card':
        $controller = new CardController($pdo);
        $controller->edit();  // Gestion de la carte (catégories + plats)
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
        if (method_exists($controller, $action)) {
            $controller->$action();
        } else {
            $controller->show();
        }
        break;

    case 'edit-services':
        $controller = new ServicesController($pdo);
        $action = $_GET['action'] ?? 'show';
        if (method_exists($controller, $action)) {
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

    case 'demo':
        // Redirige vers la vitrine du restaurant de démo
        header('Location: ?page=display&slug=demo-menumiam');
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
            $_SESSION['error_message'] = "Accès refusé.";
            header('Location: ?page=dashboard');
            exit;
        }
        $demoTokenModel = new DemoToken($pdo);
        // Vérifier que la démo existe
        if (!$demoTokenModel->getDemoAdminId()) {
            $_SESSION['error_message'] = "Le restaurant de démo n'existe pas. <a href='?page=seed-demo'>Créer la démo d'abord</a>.";
            header('Location: ?page=dashboard');
            exit;
        }
        $result = $demoTokenModel->generate($_SESSION['admin_id']);
        if ($result) {
            $demoLink = SITE_URL . '/index.php?page=demo-access&token=' . $result['token'];
            $_SESSION['success_message'] = "Lien de démo généré (valide 3 jours) :<br><code style='user-select:all;padding:4px 8px;border-radius:4px;font-size:0.85em;word-break:break-all'>" . htmlspecialchars($demoLink) . "</code>";
        } else {
            $_SESSION['error_message'] = "Erreur lors de la génération du lien.";
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
        // Suppression d'un token de démo (SUPER_ADMIN uniquement)
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
        $tokenId = $_GET['id'] ?? null;
        if ($tokenId) {
            $demoTokenModel = new DemoToken($pdo);
            $demoTokenModel->delete($tokenId);
            $_SESSION['success_message'] = "Lien de démo révoqué.";
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
        session_start();
        $_SESSION['success_message'] = "Votre session de démonstration est terminée. Merci de votre intérêt !";
        header('Location: ?page=login');
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
            $_SESSION['error_message'] = "Accès refusé.";
            header('Location: ?page=dashboard');
            exit;
        }
        require_once __DIR__ . '/../app/Seeds/DemoSeeder.php';
        $seeder = new DemoSeeder($pdo);
        $action = $_GET['action'] ?? 'run';
        if ($action === 'clean') {
            $seeder->clean();
            $_SESSION['success_message'] = "Démo supprimée avec succès.";
        } else {
            if ($seeder->demoExists()) {
                $_SESSION['error_message'] = "La démo existe déjà.";
            } elseif ($seeder->run()) {
                $_SESSION['success_message'] = "Démo créée ! Voir : <a href='?page=demo'>?page=demo</a>";
            } else {
                $_SESSION['error_message'] = "Erreur lors de la création de la démo.";
            }
        }
        header('Location: ?page=dashboard');
        exit;

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
