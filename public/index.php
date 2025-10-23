<?php
ini_set('SMTP', 'localhost');   // Pour l'envoi de mails en dev
ini_set('smtp_port', 1025);     // Port du serveur SMTP local (ex : MailHog)
define('DEV_SHOW_LINK', true);   // Constante dev pour afficher les liens directs

require_once __DIR__ . '/../config.php';  // Chargement config PDO, constantes...
require_once __DIR__ . '/../app/Controllers/AdminController.php';
require_once __DIR__ . '/../app/Controllers/CarteController.php';
require_once __DIR__ . '/../app/Controllers/ContactController.php';
require_once __DIR__ . '/../app/Controllers/LogoController.php';

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

    case 'edit-carte':
        $controller = new CarteController($pdo);
        $controller->edit();  // Gestion de la carte (catégories + plats)
        break;

    case 'edit-contact':
        $controller = new ContactController($pdo);
        $controller->edit();  // Gestion des informations de contact
        break;

    case 'edit-logo':
        $controller = new LogoController($pdo);
        $controller->edit();  // Gestion du logo
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
        $slug = $_GET['slug'] ?? null;
        $vitrineController = new VitrineController($pdo);
        $vitrineController->show($slug);  // Affichage de la vitrine publique du restaurant
        break;

    default:
        http_response_code(404);
        echo "Page non trouvée";  // Page inexistante
        break;
}