<?php
class BaseController {
    protected $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Vérifie si l'admin est connecté
    protected function isLogged() {
        return isset($_SESSION['admin_logged']) && $_SESSION['admin_logged'] === true;
    }


    protected function requireLogin() {
        if (!$this->isLogged()) {
            header('Location: login.php');
            exit;
        }
    }

    // Pour charger une vue
    protected function render($view, $data = []) {
        extract($data);
        include __DIR__ . "/../Views/$view.php";
    }
}
