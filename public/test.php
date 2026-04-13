<?php
// Test simple pour vérifier PHP
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Test PHP - MenuMiam V2</h1>";
echo "<p>PHP Version: " . phpversion() . "</p>";

// Test autoloader
require_once __DIR__ . '/../config/constants.php';
echo "<p>✅ Constants loaded</p>";

require_once APP_PATH . '/Core/Autoloader.php';
echo "<p>✅ Autoloader loaded</p>";

App\Core\Autoloader::register();
App\Core\Autoloader::addNamespace('App', APP_PATH);
echo "<p>✅ Autoloader registered</p>";

// Test Database config
try {
    $dbConfig = require CONFIG_PATH . '/database.php';
    echo "<p>✅ Database config loaded</p>";
    echo "<pre>" . print_r($dbConfig, true) . "</pre>";
} catch (Exception $e) {
    echo "<p>❌ Error loading database config: " . $e->getMessage() . "</p>";
}

// Test Database connection
try {
    App\Core\Database::init($dbConfig);
    $pdo = App\Core\Database::getInstance();
    echo "<p>✅ Database connection OK</p>";
} catch (Exception $e) {
    echo "<p>❌ Database connection error: " . $e->getMessage() . "</p>";
}

echo "<p><strong>All tests passed! Application should work.</strong></p>";
