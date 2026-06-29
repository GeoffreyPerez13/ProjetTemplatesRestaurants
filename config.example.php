<?php
/**
 * MenuCraft — Fichier de configuration
 * 
 * Copiez ce fichier en `config.php` et renseignez vos valeurs.
 * Le fichier config.php est ignoré par Git (voir .gitignore).
 */

// ============================================================
// Base de données
// ============================================================
$host   = 'localhost';
$dbname = 'menucraft';
$user   = 'root';
$pass   = '';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    die('Erreur de connexion à la base de données : ' . $e->getMessage());
}

// ============================================================
// URLs de l'application
// ============================================================
define('SITE_URL', 'http://localhost/ProjetTemplatesRestaurants/public');
define('BASE_PATH', __DIR__);

// ============================================================
// Stripe (paiement)
// ============================================================
// Clés de test : https://dashboard.stripe.com/test/apikeys
// Carte de test : 4242 4242 4242 4242 / exp 12/26 / CVV 123
define('STRIPE_SECRET_KEY', 'sk_test_VOTRE_CLE_SECRETE_ICI');
define('STRIPE_PUBLISHABLE_KEY', 'pk_test_VOTRE_CLE_PUBLIQUE_ICI');
define('STRIPE_WEBHOOK_SECRET', 'whsec_VOTRE_WEBHOOK_SECRET_ICI');

// ============================================================
// Mode Beta
// ============================================================
// true = toutes les fonctionnalités premium sont gratuites
// false = fonctionnement normal avec paiement Stripe
define('BETA_MODE', true);
define('BETA_EXPIRES', '2026-09-30');
