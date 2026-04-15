<?php
/**
 * MenuMiam V2 - Point d'entrée principal
 * 
 * @author Geoffrey Perez
 * @version 2.0
 */

// Démarrer la session
session_start();

// Charger les constantes
require_once __DIR__ . '/../config/constants.php';

// Charger l'autoloader
require_once APP_PATH . '/Core/Autoloader.php';

use App\Core\Autoloader;
use App\Core\Database;
use App\Core\Router;
use App\Core\Request;

// Enregistrer l'autoloader PSR-4
Autoloader::register();
Autoloader::addNamespace('App', APP_PATH);

// Charger la configuration de la base de données
$dbConfig = require CONFIG_PATH . '/database.php';
Database::init($dbConfig);

// Charger la configuration de l'application
$appConfig = require CONFIG_PATH . '/app.php';

// Définir le timezone
date_default_timezone_set($appConfig['timezone']);

// Créer le router
$router = new Router();
$request = new Request();

// ============================================================
// ROUTES
// ============================================================

use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\SettingsController;
use App\Controllers\CardController;

// Page d'accueil - Redirection vers login
$router->get('/', function() {
    header('Location: /login');
    exit;
});

// Authentification
$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login']);
$router->get('/register', [AuthController::class, 'showRegister']);
$router->post('/register', [AuthController::class, 'register']);
$router->get('/logout', [AuthController::class, 'logout']);

// Dashboard
$router->get('/dashboard', [DashboardController::class, 'index']);

// Paramètres
$router->get('/settings', [SettingsController::class, 'index']);
$router->post('/settings/update-profile', [SettingsController::class, 'updateProfile']);

// Gestion de la carte
$router->get('/card', [CardController::class, 'index']);
$router->get('/card/dishes/{id}', [CardController::class, 'getDishes']);
$router->get('/card/dish/{id}', [CardController::class, 'getDish']);
$router->post('/card/category/create', [CardController::class, 'createCategory']);
$router->post('/card/category/update', [CardController::class, 'updateCategory']);
$router->post('/card/category/delete', [CardController::class, 'deleteCategory']);
$router->post('/card/category/reorder', [CardController::class, 'reorderCategories']);
$router->post('/card/category/update-order', [CardController::class, 'updateCategoryOrder']);
$router->post('/card/dish/create', [CardController::class, 'createDish']);
$router->post('/card/dish/update', [CardController::class, 'updateDish']);
$router->post('/card/dish/delete', [CardController::class, 'deleteDish']);
$router->post('/card/dish/reorder', [CardController::class, 'reorderDishes']);
$router->post('/card/dish/update-order', [CardController::class, 'updateDishOrder']);

// Route de test
$router->get('/test', function() {
    echo "Test OK - Architecture MVC fonctionnelle !";
});

// Route 404
$router->setNotFoundHandler(function() {
    http_response_code(404);
    echo "404 - Page non trouvée";
});

// Dispatcher la requête
$router->dispatch($request);
