<?php
// ---------- Connexion à la base ----------
$host = 'localhost';
$db   = 'templates_restaurants';
$user = 'root';      // utilisateur MySQL
$pass = '';          // mot de passe MySQL
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

// ---------- Session ----------
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ---------- Définition des constantes utiles ----------
define('BASE_URL', '/'); // Ajuste si besoin pour ton serveur local
?>
