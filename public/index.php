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
            // Retirez save-option car non utilisé
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

    case 'vitrine':
        // $displayController = new DisplayController($pdo);
        // $displayController->show($slug);
        http_response_code(404);
        echo "Page en construction";
        break;

    case 'legal':
        $controller = new LegalController($pdo);
        $controller->show();
        break;

    default:
        http_response_code(404);
        echo "Page non trouvée";  // Page inexistante
        break;
}
