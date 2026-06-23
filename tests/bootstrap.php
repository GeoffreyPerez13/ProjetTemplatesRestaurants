<?php
/**
 * Bootstrap PHPUnit : configure l'environnement de test
 * - Crée une base de données de test isolée
 * - Charge les classes nécessaires
 */

// Autoloader Composer
require_once __DIR__ . '/../vendor/autoload.php';

// Simuler les superglobales serveur pour les tests
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['SCRIPT_NAME'] = '/public/index.php';
$_SERVER['REQUEST_METHOD'] = 'GET';

// Démarrer la session si pas active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Définir les constantes utilisées par l'application
if (!defined('SITE_URL')) {
    define('SITE_URL', 'http://localhost/ProjetTemplatesRestaurants/public');
}

// Charger les helpers
require_once __DIR__ . '/../app/Helpers/Validator.php';
require_once __DIR__ . '/../app/Helpers/FormHelper.php';
require_once __DIR__ . '/../app/Helpers/old.php';

// Charger les models
require_once __DIR__ . '/../app/Models/Admin.php';
require_once __DIR__ . '/../app/Models/Restaurant.php';
require_once __DIR__ . '/../app/Models/DemoToken.php';
require_once __DIR__ . '/../app/Models/Category.php';
require_once __DIR__ . '/../app/Models/Dish.php';
require_once __DIR__ . '/../app/Models/OptionModel.php';

// Charger les controllers
require_once __DIR__ . '/../app/Controllers/BaseController.php';

/**
 * Crée et retourne une connexion PDO vers la base de test
 */
function getTestPdo(): PDO
{
    $host = getenv('DB_HOST') ?: 'localhost';
    $dbName = getenv('DB_NAME') ?: 'menucraft_test';
    $user = getenv('DB_USER') ?: 'root';
    $pass = getenv('DB_PASS') ?: '';

    // Connexion sans DB pour créer la base si nécessaire
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_BOTH,
    ]);
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName`");
    $pdo->exec("USE `$dbName`");

    return $pdo;
}

/**
 * Initialise le schéma de la base de test (tables minimales)
 */
function initTestSchema(PDO $pdo): void
{
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");

    $pdo->exec("DROP TABLE IF EXISTS demo_tokens, plat_allergenes, plats, categories, 
                admin_options, contact, card_images, logos, banners, invitations, 
                reservations, admins, restaurants, subscriptions, billing_cycles");

    $pdo->exec("
        CREATE TABLE restaurants (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            slug VARCHAR(255) UNIQUE NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB
    ");

    $pdo->exec("
        CREATE TABLE admins (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(100) UNIQUE NOT NULL,
            email VARCHAR(255) NOT NULL,
            password VARCHAR(255) NOT NULL,
            role ENUM('ADMIN', 'SUPER_ADMIN') DEFAULT 'ADMIN',
            restaurant_name VARCHAR(255),
            restaurant_id INT,
            carte_mode ENUM('editable', 'images') DEFAULT 'editable',
            reset_token VARCHAR(255),
            reset_token_expiry DATETIME,
            email_verified TINYINT(1) DEFAULT 1,
            verification_token VARCHAR(255),
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE SET NULL
        ) ENGINE=InnoDB
    ");

    $pdo->exec("
        CREATE TABLE categories (
            id INT AUTO_INCREMENT PRIMARY KEY,
            admin_id INT NOT NULL,
            name VARCHAR(255) NOT NULL,
            image VARCHAR(500),
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE CASCADE
        ) ENGINE=InnoDB
    ");

    $pdo->exec("
        CREATE TABLE plats (
            id INT AUTO_INCREMENT PRIMARY KEY,
            category_id INT NOT NULL,
            name VARCHAR(255) NOT NULL,
            description TEXT,
            price DECIMAL(8,2) NOT NULL,
            image VARCHAR(500),
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
        ) ENGINE=InnoDB
    ");

    $pdo->exec("
        CREATE TABLE plat_allergenes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            plat_id INT NOT NULL,
            allergene_id INT NOT NULL,
            FOREIGN KEY (plat_id) REFERENCES plats(id) ON DELETE CASCADE
        ) ENGINE=InnoDB
    ");

    $pdo->exec("
        CREATE TABLE admin_options (
            id INT AUTO_INCREMENT PRIMARY KEY,
            admin_id INT NOT NULL,
            option_name VARCHAR(100) NOT NULL,
            option_value VARCHAR(255) DEFAULT '0',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY (admin_id, option_name),
            FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE CASCADE
        ) ENGINE=InnoDB
    ");

    $pdo->exec("
        CREATE TABLE contact (
            id INT AUTO_INCREMENT PRIMARY KEY,
            admin_id INT UNIQUE NOT NULL,
            telephone VARCHAR(50),
            email VARCHAR(255),
            adresse TEXT,
            horaires TEXT,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE CASCADE
        ) ENGINE=InnoDB
    ");

    $pdo->exec("
        CREATE TABLE demo_tokens (
            id INT AUTO_INCREMENT PRIMARY KEY,
            token VARCHAR(255) UNIQUE NOT NULL,
            admin_id INT NOT NULL,
            label VARCHAR(255),
            expires_at DATETIME NOT NULL,
            created_by INT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE CASCADE,
            FOREIGN KEY (created_by) REFERENCES admins(id) ON DELETE SET NULL
        ) ENGINE=InnoDB
    ");

    $pdo->exec("
        CREATE TABLE invitations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(255) NOT NULL,
            restaurant_name VARCHAR(255) NOT NULL,
            token VARCHAR(255) UNIQUE NOT NULL,
            expiry DATETIME NOT NULL,
            used TINYINT(1) DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB
    ");

    $pdo->exec("
        CREATE TABLE logos (
            id INT AUTO_INCREMENT PRIMARY KEY,
            admin_id INT UNIQUE NOT NULL,
            filename VARCHAR(500) NOT NULL,
            uploaded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE CASCADE
        ) ENGINE=InnoDB
    ");

    $pdo->exec("
        CREATE TABLE banners (
            id INT AUTO_INCREMENT PRIMARY KEY,
            admin_id INT UNIQUE NOT NULL,
            filename VARCHAR(500) NOT NULL,
            text TEXT,
            uploaded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE CASCADE
        ) ENGINE=InnoDB
    ");

    $pdo->exec("
        CREATE TABLE card_images (
            id INT AUTO_INCREMENT PRIMARY KEY,
            admin_id INT NOT NULL,
            filename VARCHAR(500) NOT NULL,
            uploaded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE CASCADE
        ) ENGINE=InnoDB
    ");

    $pdo->exec("
        CREATE TABLE subscriptions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            admin_id INT NOT NULL,
            plan_name VARCHAR(100) NOT NULL DEFAULT 'basique',
            status ENUM('active', 'cancelled', 'expired') DEFAULT 'active',
            start_date DATETIME DEFAULT CURRENT_TIMESTAMP,
            end_date DATETIME,
            stripe_subscription_id VARCHAR(255),
            FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE CASCADE
        ) ENGINE=InnoDB
    ");

    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
}
