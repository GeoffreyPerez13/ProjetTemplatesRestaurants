<?php
// Démarrage de session pour pouvoir la détruire
session_start();

// Détruit toutes les variables de session et la session elle-même
session_destroy();

// Redirige vers la page de connexion
header('Location: login.php');
exit;