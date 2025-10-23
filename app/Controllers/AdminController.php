<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../Models/Admin.php';

class AdminController extends BaseController
{

    public function __construct($pdo)
    {
        parent::__construct($pdo);
    }

    public function sendInvitation()
    {
        $this->requireLogin();

        // Vérifier si l'utilisateur est SUPER_ADMIN
        $adminModel = new Admin($this->pdo);
        $admin = $adminModel->findById($_SESSION['admin_id']);

        if ($admin->role !== 'SUPER_ADMIN') {
            header('Location: ?page=dashboard');
            exit;
        }

        $error = null;
        $success = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->verifyCsrfToken($_POST['csrf_token'] ?? null)) {
                $error = "Requête invalide (CSRF).";
            } else {
                $email = trim($_POST['email'] ?? '');
                $restaurantName = trim($_POST['restaurant_name'] ?? '');

                if (empty($email) || empty($restaurantName)) {
                    $error = "Veuillez remplir tous les champs.";
                } else {
                    $adminModel = new Admin($this->pdo);
                    $token = bin2hex(random_bytes(32));

                    if ($adminModel->createInvitation($email, $restaurantName, $token)) {
                        $inviteLink = "http://" . $_SERVER['HTTP_HOST'] . "?page=register&token=" . $token;
                        if (defined('DEV_SHOW_LINK') && DEV_SHOW_LINK === true) {
                            $success = "L'invitation a été envoyée avec succès.";
                        } else {
                            $success = "L'invitation a été envoyée avec succès.";
                        }
                    } else {
                        $error = "Erreur lors de la création de l'invitation.";
                    }
                }
            }
        }

        $this->render('admin/send-invitation', [
            'error' => $error,
            'success' => $success,
            'csrf_token' => $this->getCsrfToken()
        ]);
    }

    public function register()
    {
        $error = null;
        $token = $_GET['token'] ?? null;

        if (empty($token)) {
            header('Location: ?page=login');
            exit;
        }

        $adminModel = new Admin($this->pdo);
        $invitation = $adminModel->getInvitation($token);

        if (!$invitation || strtotime($invitation->expiry) < time()) {
            $error = "Ce lien d'invitation n'est plus valide.";
            $this->render('admin/register', ['error' => $error]);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->verifyCsrfToken($_POST['csrf_token'] ?? null)) {
                $error = "Requête invalide (CSRF).";
            } else {
                $username = trim($_POST['username'] ?? '');
                $password = trim($_POST['password'] ?? '');
                $confirmPassword = trim($_POST['confirm_password'] ?? '');

                if ($password !== $confirmPassword) {
                    $error = "Les mots de passe ne correspondent pas.";
                } elseif (strlen($password) < 8) {
                    $error = "Le mot de passe doit contenir au moins 8 caractères.";
                } else {
                    if ($adminModel->createAccount($invitation, $username, $password)) {
                        header('Location: ?page=login&success=1');
                        exit;
                    } else {
                        $lastError = error_get_last();
                        $error = "Erreur lors de la création du compte.";
                        if ($lastError) {
                            $error .= " Détails : " . $lastError['message'];
                        }
                    }
                }
            }
        }

        $this->render('admin/register', [
            'error' => $error,
            'invitation' => $invitation,
            'csrf_token' => $this->getCsrfToken()
        ]);
    }

    public function login()
    {
        $error = null;
        $success = null;

        if (isset($_GET['success']) && $_GET['success'] == 1) {
            $success = "Votre compte a été créé avec succès. Vous pouvez maintenant vous connecter.";
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username'] ?? '');
            $password = trim($_POST['password'] ?? '');

            if (empty($username) || empty($password)) {
                $error = "Veuillez remplir tous les champs.";
            } else {
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
        }

        $this->render('admin/login', [
            'error' => $error,
            'success' => $success
        ]);
    }

    public function logout()
    {
        session_destroy();
        header('Location: ?page=login');
        exit;
    }

    public function dashboard()
    {
        $this->requireLogin();
        $admin_name = $_SESSION['admin_name'] ?? '';

        // Récupérer le rôle de l'utilisateur
        $adminModel = new Admin($this->pdo);
        $admin = $adminModel->findById($_SESSION['admin_id']);
        $role = $admin->role ?? 'ADMIN';

        $this->render('admin/dashboard', [
            'admin_name' => $admin_name,
            'role' => $role
        ]);
    }

    public function resetPassword()
    {
        $error = null;
        $success = null;
        $token = $_GET['token'] ?? $_POST['token'] ?? null;
        $adminModel = new Admin($this->pdo);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->verifyCsrfToken($_POST['csrf_token'] ?? null)) {
                $error = "Requête invalide (CSRF).";
            } else {
                if (empty($token)) {
                    $email = trim($_POST['email'] ?? '');
                    if (empty($email)) {
                        $error = "Veuillez renseigner une adresse email.";
                    } else {
                        if ($adminModel->requestPasswordReset($email)) {
                            $success = "Si cette adresse existe dans notre système, vous recevrez un email.";
                        }
                    }
                } else {
                    $newPassword = $_POST['new_password'] ?? '';
                    $confirmPassword = $_POST['confirm_password'] ?? '';

                    if ($newPassword !== $confirmPassword) {
                        $error = "Les mots de passe ne correspondent pas.";
                    } elseif (strlen($newPassword) < 8) {
                        $error = "Le mot de passe doit contenir au moins 8 caractères.";
                    } else {
                        if ($adminModel->resetPassword($token, $newPassword)) {
                            $success = "Mot de passe mis à jour avec succès.";
                            header("refresh:3;url=?page=login");
                        } else {
                            $error = "Lien de réinitialisation invalide ou expiré.";
                        }
                    }
                }
            }
        }

        $this->render('admin/reset-password', [
            'error' => $error,
            'success' => $success,
            'token' => $token,
            'csrf_token' => $this->getCsrfToken()
        ]);
    }
}
