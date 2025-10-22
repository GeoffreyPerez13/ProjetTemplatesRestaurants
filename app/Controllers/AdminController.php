<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../Models/Admin.php';

class AdminController extends BaseController {

    public function __construct($pdo) {
        parent::__construct($pdo);
    }

    public function login() {
        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username']);
            $password = trim($_POST['password']);

            $adminModel = new Admin($this->pdo);
            $user = $adminModel->login($username, $password);

            if ($user) {
                $_SESSION['admin_logged'] = true;
                $_SESSION['admin_id'] = $user->id;
                $_SESSION['admin_name'] = $user->restaurant_name;
                header('Location: ?page=dashboard');
                exit;
            } else {
                $error = "Identifiant ou mot de passe incorrect.";
            }
        }

        $this->render('admin/login', ['error' => $error]);
    }

    public function dashboard() {
        $this->requireLogin();
        $admin_name = $_SESSION['admin_name'] ?? '';
        $this->render('admin/dashboard', ['admin_name' => $admin_name]);
    }

    public function logout() {
        session_destroy();
        header('Location: ?page=login');
        exit;
    }
}
?>
