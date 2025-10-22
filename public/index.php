<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../app/Controllers/AdminController.php';
require_once __DIR__ . '/../app/Controllers/CarteController.php';
require_once __DIR__ . '/../app/Controllers/ContactController.php';
require_once __DIR__ . '/../app/Controllers/LogoController.php';

// Si l'admin est déjà connecté, redirection automatique depuis login
if (isset($_SESSION['admin_logged']) && $_SESSION['admin_logged'] === true && !isset($_GET['page'])) {
    header('Location: ?page=dashboard');
    exit;
}

$page = $_GET['page'] ?? 'login';

switch ($page) {
    case 'login':
        $controller = new AdminController($pdo);
        $controller->login();
        break;

    case 'dashboard':
        $controller = new AdminController($pdo);
        $controller->dashboard();
        break;

    case 'edit-carte':
        $controller = new CarteController($pdo);
        $controller->edit();
        break;

    case 'edit-contact':
        $controller = new ContactController($pdo);
        $controller->edit();
        break;

    case 'edit-logo':
        $controller = new LogoController($pdo);
        $controller->edit();
        break;

    case 'logout':
        $controller = new AdminController($pdo);
        $controller->logout();
        break;

    default:
        http_response_code(404);
        echo "Page non trouvée";
        break;
}
