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

// Page d'accueil
$router->get('/', function() {
    echo "MenuMiam V2 - Bienvenue !";
});

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
