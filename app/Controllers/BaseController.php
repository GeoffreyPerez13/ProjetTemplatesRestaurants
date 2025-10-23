<?php
// Classe de base dont héritent tous les autres contrôleurs
// Elle contient les fonctionnalités communes à toutes les pages du back-office
class BaseController
{
    // Propriété pour stocker la connexion PDO à la base de données
    protected $pdo;

    // Constructeur : initialise la connexion à la base de données
    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    // Vérifie si un administrateur est connecté
    // Retourne true si la session contient une variable "admin_logged" à true
    protected function isLogged()
    {
        return isset($_SESSION['admin_logged']) && $_SESSION['admin_logged'] === true;
    }

    // Force la connexion : si l'utilisateur n'est pas connecté, il est redirigé vers la page de login
    protected function requireLogin()
    {
        if (!$this->isLogged()) {
            header('Location: login.php'); // Redirection vers la page de connexion
            exit; // Stoppe immédiatement l'exécution du script
        }
    }

    // Fonction utilitaire pour charger une vue
    // $view correspond au chemin du fichier PHP à afficher (par exemple "admin/login")
    // $data contient les variables à passer à la vue (tableau associatif)
    protected function render($view, $data = [])
    {
        extract($data); // Transforme chaque clé du tableau en variable (ex: $data['error'] devient $error)
        include __DIR__ . "/../Views/$view.php"; // Inclut la vue correspondante
    }

    // Génère ou récupère un token CSRF (sécurité contre les attaques par falsification de requête)
    protected function getCsrfToken(): string
    {
        // Démarre la session si elle n’est pas encore active
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        // Si aucun token CSRF n’existe encore, en créer un nouveau
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // Génère une chaîne aléatoire sécurisée
        }

        // Retourne le token stocké en session
        return $_SESSION['csrf_token'];
    }

    // Vérifie que le token CSRF soumis correspond à celui stocké en session
    protected function verifyCsrfToken(?string $token): bool
    {
        // Démarre la session si nécessaire
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        // Vérifie que le token reçu et celui en session existent et sont identiques
        return !empty($token)
            && !empty($_SESSION['csrf_token'])
            && hash_equals($_SESSION['csrf_token'], $token); // Comparaison sécurisée pour éviter les attaques temporelles
    }
}