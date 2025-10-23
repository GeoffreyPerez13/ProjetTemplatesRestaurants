<?php

require_once __DIR__ . '/BaseController.php'; // Inclusion du contrôleur de base (qui contient les fonctionnalités communes à tous les contrôleurs)
require_once __DIR__ . '/../Models/Admin.php'; // Inclusion du modèle Admin (qui gère les interactions avec la base pour les administrateurs)

// Définition de la classe AdminController, qui hérite de BaseController
class AdminController extends BaseController
{
    // Constructeur : initialise la connexion à la base de données via le parent
    public function __construct($pdo)
    {
        parent::__construct($pdo);
    }

    // Fonction d’envoi d’invitation pour créer un compte admin restaurant
    public function sendInvitation()
    {
        $this->requireLogin(); // Vérifie que l'utilisateur est connecté

        // Vérifie que l'utilisateur connecté est un SUPER_ADMIN avant d'envoyer une invitation
        $adminModel = new Admin($this->pdo);
        $admin = $adminModel->findById($_SESSION['admin_id']);

        if ($admin->role !== 'SUPER_ADMIN') {
            // Si ce n’est pas un SUPER_ADMIN, redirection vers le tableau de bord
            header('Location: ?page=dashboard');
            exit;
        }

        // Variables d’état pour gérer les messages d’erreur ou de succès
        $error = null;
        $success = null;

        // Si le formulaire est soumis
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            // Vérifie la validité du token CSRF pour éviter les attaques de type Cross-Site Request Forgery
            if (!$this->verifyCsrfToken($_POST['csrf_token'] ?? null)) {
                $error = "Requête invalide (CSRF).";
            } else {
                // Récupération et nettoyage des données saisies
                $email = trim($_POST['email'] ?? '');
                $restaurantName = trim($_POST['restaurant_name'] ?? '');

                // Vérifie que les champs ne sont pas vides
                if (empty($email) || empty($restaurantName)) {
                    $error = "Veuillez remplir tous les champs.";
                } else {
                    // Création d’un modèle Admin pour enregistrer l’invitation
                    $adminModel = new Admin($this->pdo);

                    // Génération d’un token unique (lien d’invitation)
                    $token = bin2hex(random_bytes(32));

                    // Enregistre l’invitation en base de données
                    if ($adminModel->createInvitation($email, $restaurantName, $token)) {
                        // Génère le lien d’invitation à envoyer
                        $inviteLink = "http://" . $_SERVER['HTTP_HOST'] . "?page=register&token=" . $token;

                        // Message de confirmation (le DEV_SHOW_LINK peut être utilisé pour affichage local)
                        if (defined('DEV_SHOW_LINK') && DEV_SHOW_LINK === true) {
                            $success = "L'invitation a été envoyée avec succès.";
                        } else {
                            $success = "L'invitation a été envoyée avec succès.";
                        }
                    } else {
                        // Si l’enregistrement échoue
                        $error = "Erreur lors de la création de l'invitation.";
                    }
                }
            }
        }

        // Affiche la vue du formulaire d’invitation avec les éventuels messages
        $this->render('admin/send-invitation', [
            'error' => $error,
            'success' => $success,
            'csrf_token' => $this->getCsrfToken()
        ]);
    }

    // Fonction d’inscription via lien d’invitation
    public function register()
    {
        $error = null;
        $token = $_GET['token'] ?? null; // Récupère le token du lien d’invitation

        // Si pas de token, redirige vers la page de connexion
        if (empty($token)) {
            header('Location: ?page=login');
            exit;
        }

        // Vérifie si l’invitation existe et est encore valide
        $adminModel = new Admin($this->pdo);
        $invitation = $adminModel->getInvitation($token);

        if (!$invitation || strtotime($invitation->expiry) < time()) {
            $error = "Ce lien d'invitation n'est plus valide.";
            $this->render('admin/register', ['error' => $error]);
            return;
        }

        // Si le formulaire d’inscription est soumis
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->verifyCsrfToken($_POST['csrf_token'] ?? null)) {
                $error = "Requête invalide (CSRF).";
            } else {
                // Récupération des données saisies
                $username = trim($_POST['username'] ?? '');
                $password = trim($_POST['password'] ?? '');
                $confirmPassword = trim($_POST['confirm_password'] ?? '');

                // Vérifie la cohérence des mots de passe
                if ($password !== $confirmPassword) {
                    $error = "Les mots de passe ne correspondent pas.";
                } elseif (strlen($password) < 8) {
                    $error = "Le mot de passe doit contenir au moins 8 caractères.";
                } else {
                    // Création du compte admin à partir de l’invitation
                    if ($adminModel->createAccount($invitation, $username, $password)) {
                        // Redirection vers la connexion après succès
                        header('Location: ?page=login&success=1');
                        exit;
                    } else {
                        // En cas d’erreur technique
                        $lastError = error_get_last();
                        $error = "Erreur lors de la création du compte.";
                        if ($lastError) {
                            $error .= " Détails : " . $lastError['message'];
                        }
                    }
                }
            }
        }

        // Affiche la page d’inscription
        $this->render('admin/register', [
            'error' => $error,
            'invitation' => $invitation,
            'csrf_token' => $this->getCsrfToken()
        ]);
    }

    // Fonction de connexion d’un administrateur
    public function login()
    {
        $error = null;
        $success = null;

        // Si un compte vient d’être créé, afficher un message de succès
        if (isset($_GET['success']) && $_GET['success'] == 1) {
            $success = "Votre compte a été créé avec succès. Vous pouvez maintenant vous connecter.";
        }

        // Si le formulaire de connexion est soumis
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username'] ?? '');
            $password = trim($_POST['password'] ?? '');

            if (empty($username) || empty($password)) {
                $error = "Veuillez remplir tous les champs.";
            } else {
                $adminModel = new Admin($this->pdo);
                $user = $adminModel->login($username, $password);

                // Si les identifiants sont corrects, enregistre la session
                if ($user) {
                    $_SESSION['admin_logged'] = true;
                    $_SESSION['admin_id'] = $user->id;
                    $_SESSION['admin_name'] = $user->restaurant_name;

                    // Redirection vers le tableau de bord
                    header('Location: ?page=dashboard');
                    exit;
                } else {
                    $error = "Identifiant ou mot de passe incorrect.";
                }
            }
        }

        // Affiche la vue de connexion
        $this->render('admin/login', [
            'error' => $error,
            'success' => $success
        ]);
    }

    // Déconnexion de l’administrateur
    public function logout()
    {
        session_destroy(); // Supprime toutes les données de session
        header('Location: ?page=login'); // Redirige vers la connexion
        exit;
    }

    // Tableau de bord de l’administrateur
    public function dashboard()
    {
        $this->requireLogin(); // Vérifie la session active
        $admin_name = $_SESSION['admin_name'] ?? '';

        // Récupère les informations de rôle depuis la base
        $adminModel = new Admin($this->pdo);
        $admin = $adminModel->findById($_SESSION['admin_id']);
        $role = $admin->role ?? 'ADMIN';

        // Affiche la vue du tableau de bord avec les données
        $this->render('admin/dashboard', [
            'admin_name' => $admin_name,
            'role' => $role
        ]);
    }

    // Gestion de la réinitialisation de mot de passe
    public function resetPassword()
    {
        $error = null;
        $success = null;
        $token = $_GET['token'] ?? $_POST['token'] ?? null; // Peut venir de l’URL ou du formulaire
        $adminModel = new Admin($this->pdo);

        // Si le formulaire est soumis
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Vérifie le token CSRF
            if (!$this->verifyCsrfToken($_POST['csrf_token'] ?? null)) {
                $error = "Requête invalide (CSRF).";
            } else {
                // Si aucun token, l’utilisateur demande un email de réinitialisation
                if (empty($token)) {
                    $email = trim($_POST['email'] ?? '');
                    if (empty($email)) {
                        $error = "Veuillez renseigner une adresse email.";
                    } else {
                        // Envoie du mail de réinitialisation
                        if ($adminModel->requestPasswordReset($email)) {
                            $success = "Si cette adresse existe dans notre système, vous recevrez un email.";
                        }
                    }
                } else {
                    // Si token présent, alors l’utilisateur soumet un nouveau mot de passe
                    $newPassword = $_POST['new_password'] ?? '';
                    $confirmPassword = $_POST['confirm_password'] ?? '';

                    // Vérifications basiques
                    if ($newPassword !== $confirmPassword) {
                        $error = "Les mots de passe ne correspondent pas.";
                    } elseif (strlen($newPassword) < 8) {
                        $error = "Le mot de passe doit contenir au moins 8 caractères.";
                    } else {
                        // Tentative de mise à jour du mot de passe
                        if ($adminModel->resetPassword($token, $newPassword)) {
                            $success = "Mot de passe mis à jour avec succès.";
                            // Redirection automatique après 3 secondes vers la connexion
                            header("refresh:3;url=?page=login");
                        } else {
                            $error = "Lien de réinitialisation invalide ou expiré.";
                        }
                    }
                }
            }
        }

        // Affiche la page de réinitialisation avec les messages éventuels
        $this->render('admin/reset-password', [
            'error' => $error,
            'success' => $success,
            'token' => $token,
            'csrf_token' => $this->getCsrfToken()
        ]);
    }
}