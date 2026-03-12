<?php
/**
 * Configuration de l'application MenuMiam
 * 
 * Copiez ce fichier en config.php et adaptez les valeurs à votre environnement.
 * Le fichier config.php est ignoré par Git (.gitignore).
 */

// ==================== BASE DE DONNÉES ====================
$host = 'localhost';
$db   = 'templates_restaurants';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données: " . $e->getMessage());
}

// ==================== ENVIRONNEMENT ====================
// 'dev' ou 'prod' — contrôle l'affichage des erreurs et les outils de debug
define('APP_ENV', 'dev');

// ==================== EMAIL (SMTP) ====================
// En dev : MailHog sur localhost:1025
// En prod : adapter au serveur SMTP réel
define('SMTP_HOST', 'localhost');
define('SMTP_PORT', 1025);

// ==================== STRIPE ====================
// Clés API Stripe (mode test → https://dashboard.stripe.com/test/apikeys)
define('STRIPE_SECRET_KEY', 'sk_test_VOTRE_CLE_SECRETE_ICI');
define('SITE_URL', 'http://localhost/ProjetTemplatesRestaurants');

// Webhook Stripe (Dashboard → Developers → Webhooks → Signing secret)
// URL du webhook : https://votre-domaine.com/?page=stripe-webhook
// Événement à écouter : checkout.session.completed
define('STRIPE_WEBHOOK_SECRET', '');  // whsec_... (laisser vide en dev local)

// ==================== OPTIONS DEV ====================
// Afficher les liens directs (invitations, vérification email) dans l'interface
// ATTENTION : désactiver en production !
define('DEV_SHOW_LINK', APP_ENV === 'dev');
