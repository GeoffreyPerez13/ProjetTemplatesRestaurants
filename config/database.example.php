<?php
/**
 * Configuration de la Base de Données - MenuMiam V2
 * 
 * Copiez ce fichier en database.php et configurez vos identifiants
 * Le fichier database.php est ignoré par Git pour la sécurité
 */

return [
    'host' => 'localhost',
    'database' => 'menumiam_v2',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]
];
