<?php
/**
 * Configuration de l'Application - MenuMiam V2
 */

return [
    'name' => 'MenuMiam',
    'version' => '2.0',
    'env' => 'development', // development, production
    'debug' => true,
    'timezone' => 'Europe/Paris',
    'locale' => 'fr_FR',
    
    // URL de base
    'base_url' => 'http://localhost/ProjetTemplatesRestaurants',
    
    // Sécurité
    'session_lifetime' => 7200, // 2 heures
    'csrf_token_name' => 'csrf_token',
    
    // Upload
    'upload_max_size' => 5 * 1024 * 1024, // 5 MB
    'allowed_image_types' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
    'allowed_document_types' => ['pdf'],
    
    // Pagination
    'items_per_page' => 20,
    
    // Email
    'mail_from' => 'noreply@menumiam.fr',
    'mail_from_name' => 'MenuMiam',
];
