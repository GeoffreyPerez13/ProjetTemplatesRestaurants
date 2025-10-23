<?php
class BaseController
{
    protected $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    // Vérifie si l'admin est connecté
    protected function isLogged()
    {
        return isset($_SESSION['admin_logged']) && $_SESSION['admin_logged'] === true;
    }


    protected function requireLogin()
    {
        if (!$this->isLogged()) {
            header('Location: login.php');
            exit;
        }
    }

    // Pour charger une vue
    protected function render($view, $data = [])
    {
        extract($data);
        include __DIR__ . "/../Views/$view.php";
    }

    protected function getCsrfToken(): string
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    protected function verifyCsrfToken(?string $token): bool
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        return !empty($token) && !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
}
