<?php
// ---------- Connexion à la base ----------
// Paramètres de connexion MySQL
$host = 'localhost';
$db   = 'templates_restaurants';
$user = 'root';      // utilisateur MySQL
$pass = '';          // mot de passe MySQL
$charset = 'utf8mb4'; // encodage UTF-8 complet

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,          // Lancer une exception en cas d'erreur
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,    // Récupérer les résultats sous forme de tableau associatif
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options); // Création de l'objet PDO pour la base
} catch (PDOException $e) {
    die("Connexion échouée : " . $e->getMessage()); // Stop le script si erreur de connexion
}

// ---------- Session ----------
// Démarrage de la session si elle n'est pas déjà active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ---------- Définition des constantes utiles ----------
define('BASE_URL', '/'); // URL de base du site, utile pour générer des liens
?>
