<?php
/**
 * Constantes de l'Application - MenuMiam V2
 */

// Chemins
define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('CONFIG_PATH', ROOT_PATH . '/config');
define('UPLOADS_PATH', PUBLIC_PATH . '/assets/uploads');

// URLs
define('BASE_URL', 'http://localhost/ProjetTemplatesRestaurants');
define('ASSETS_URL', BASE_URL . '/public/assets');
define('UPLOADS_URL', ASSETS_URL . '/uploads');

// Sécurité
define('BCRYPT_COST', 12);
define('SESSION_NAME', 'menumiam_session');

// Features Premium
define('FEATURE_GOOGLE_REVIEWS', 'google_reviews');
define('FEATURE_ADVANCED_ANALYTICS', 'advanced_analytics');
define('FEATURE_ONLINE_BOOKING', 'online_booking');
define('FEATURE_DELIVERY_INTEGRATION', 'delivery_integration');

// Statuts
define('STATUS_PENDING', 'pending');
define('STATUS_CONFIRMED', 'confirmed');
define('STATUS_CANCELLED', 'cancelled');
define('STATUS_COMPLETED', 'completed');
define('STATUS_NO_SHOW', 'no_show');

// Rôles
define('ROLE_ADMIN', 'ADMIN');
define('ROLE_SUPER_ADMIN', 'SUPER_ADMIN');
