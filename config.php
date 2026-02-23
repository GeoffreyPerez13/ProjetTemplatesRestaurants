<?php
// ---------- Connexion à la base ----------
$host = 'localhost';
$db   = 'templates_restaurants';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    die("Connexion échouée : " . $e->getMessage());
}

// ---------- Définition de SITE_URL compatible CLI ----------
if (php_sapi_name() === 'cli') {
    // En ligne de commande, on définit l'URL du site (à adapter selon votre environnement)
    define('SITE_URL', 'http://templatesrestaurants.local');
    define('BASE_PATH', '');
} else {
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'];
    $basePath = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
    define('SITE_URL', $protocol . $host . $basePath);
    define('BASE_PATH', $basePath);
}

// ---------- Session ----------
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
