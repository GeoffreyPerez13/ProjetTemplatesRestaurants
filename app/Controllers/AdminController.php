<?php

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../Models/Admin.php';

/**
 * Contrôleur pour la gestion des administrateurs
 * Gère les fonctionnalités d'authentification, d'invitation et de gestion des comptes admin
 */
class AdminController extends BaseController
{
    /**
     * Constructeur
     * @param PDO $pdo Instance de connexion à la base de données
     */
    public function __construct($pdo)
    {
        parent::__construct($pdo);
        // Vous pouvez personnaliser le délai de scroll si besoin
        // $this->setScrollDelay(4000);
    }

    /**
     * Envoie une invitation pour créer un compte administrateur restaurant
     * Méthode réservée aux SUPER_ADMIN seulement
     */
    public function sendInvitation()
    {
        // Étape 1: Vérifier que l'utilisateur est connecté
        $this->requireLogin();

        // Étape 2: Vérifier les permissions (SUPER_ADMIN uniquement)
        $adminModel = new Admin($this->pdo);
        $admin = $adminModel->findById($_SESSION['admin_id']);

        if ($admin->role !== 'SUPER_ADMIN') {
            header('Location: ?page=dashboard');
            exit;
        }

        // Étape 3: Traitement du formulaire si soumis
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->verifyCsrfToken($_POST['csrf_token'] ?? null)) {
                $this->addErrorMessage("Requête invalide (CSRF).");
            } else {
                $email = trim($_POST['email'] ?? '');
                $restaurantName = trim($_POST['restaurant_name'] ?? '');

                if (empty($email) || empty($restaurantName)) {
                    $this->addErrorMessage("Veuillez remplir tous les champs.");
                } else {
                    $adminModel = new Admin($this->pdo);
                    $token = bin2hex(random_bytes(32));

                    if ($adminModel->createInvitation($email, $restaurantName, $token)) {
                        $inviteLink = "http://" . $_SERVER['HTTP_HOST'] . "?page=register&token=" . $token;

                        if (defined('DEV_SHOW_LINK') && DEV_SHOW_LINK === true) {
                            $this->addSuccessMessage("L'invitation a été envoyée avec succès. Lien: " . $inviteLink);
                        } else {
                            $this->addSuccessMessage("L'invitation a été envoyée avec succès.");
                        }
                    } else {
                        $this->addErrorMessage("Erreur lors de la création de l'invitation.");
                    }
                }
            }

            // Redirection pour éviter le rechargement du formulaire
            header('Location: ?page=send-invitation');
            exit;
        }

        // Étape 4: Récupération des messages flash
        $messages = $this->getFlashMessages();

        // Étape 5: Affichage de la vue avec les données
        $this->render('admin/send-invitation', array_merge($messages, [
            'csrf_token' => $this->getCsrfToken()
        ]));
    }

    /**
     * Inscription via un lien d'invitation
     * Permet à un restaurateur de créer son compte après avoir reçu une invitation
     */
    public function register()
    {
        // Étape 1: Initialisation et récupération du token
        $token = $_GET['token'] ?? null;

        if (empty($token)) {
            header('Location: ?page=login');
            exit;
        }

        // Étape 2: Vérification de la validité de l'invitation
        $adminModel = new Admin($this->pdo);
        $invitation = $adminModel->getInvitation($token);

        if (!$invitation || strtotime($invitation->expiry) < time()) {
            $this->addErrorMessage("Ce lien d'invitation n'est plus valide.");

            // Redirection avec message d'erreur
            header('Location: ?page=login');
            exit;
        }

        // Étape 3: Traitement du formulaire d'inscription si soumis
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->verifyCsrfToken($_POST['csrf_token'] ?? null)) {
                $this->addErrorMessage("Requête invalide (CSRF).");
            } else {
                $username = trim($_POST['username'] ?? '');
                $password = trim($_POST['password'] ?? '');
                $confirmPassword = trim($_POST['confirm_password'] ?? '');

                if ($password !== $confirmPassword) {
                    $this->addErrorMessage("Les mots de passe ne correspondent pas.");
                } elseif (strlen($password) < 8) {
                    $this->addErrorMessage("Le mot de passe doit contenir au moins 8 caractères.");
                } else {
                    if ($adminModel->createAccount($invitation, $username, $password)) {
                        // Succès: redirection vers la page de login avec message de succès
                        $this->addSuccessMessage("Votre compte a été créé avec succès. Vous pouvez maintenant vous connecter.");
                        header('Location: ?page=login');
                        exit;
                    } else {
                        $lastError = error_get_last();
                        $errorMsg = "Erreur lors de la création du compte.";
                        if ($lastError) {
                            $errorMsg .= " Détails : " . $lastError['message'];
                        }
                        $this->addErrorMessage($errorMsg);
                    }
                }
            }

            // Redirection pour éviter le rechargement du formulaire
            header('Location: ?page=register&token=' . urlencode($token));
            exit;
        }

        // Étape 4: Récupération des messages flash
        $messages = $this->getFlashMessages();

        // Étape 5: Affichage du formulaire d'inscription
        $this->render('admin/register', array_merge($messages, [
            'invitation' => $invitation,
            'csrf_token' => $this->getCsrfToken(),
            'token' => $token
        ]));
    }

    /**
     * Connexion d'un administrateur
     * Authentifie un administrateur et démarre une session
     */
    public function login()
    {
        // Étape 1: Variables locales
        $error = null;
        $success = null;

        // Étape 2: Vérifier si un message de succès vient d'être ajouté (après inscription)
        // Vous pouvez garder cette partie si vous voulez récupérer les messages flash
        $flashMessages = $this->getFlashMessages();
        if ($flashMessages['success_message']) {
            $success = $flashMessages['success_message'];
        }

        // Étape 3: Traitement du formulaire de connexion
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username'] ?? '');
            $password = trim($_POST['password'] ?? '');

            if (empty($username) || empty($password)) {
                $error = "Veuillez remplir tous les champs.";
            } else {
                $adminModel = new Admin($this->pdo);
                $user = $adminModel->login($username, $password);

                if ($user) {
                    // Authentification réussie
                    $_SESSION['admin_logged'] = true;
                    $_SESSION['admin_id'] = $user->id;
                    $_SESSION['admin_name'] = $user->restaurant_name;

                    // Redirection vers le dashboard
                    header('Location: ?page=dashboard');
                    exit;
                } else {
                    $error = "Identifiant ou mot de passe incorrect.";
                }
            }
        }

        // Étape 4: Affichage du formulaire de connexion
        $this->render('admin/login', [
            'error_message' => $error,
            'success_message' => $success
        ]);
    }

    /**
     * Déconnexion de l'administrateur
     * Termine la session en cours et redirige vers la page de login
     */
    public function logout()
    {
        // Message de déconnexion
        $this->addSuccessMessage("Vous avez été déconnecté avec succès.");

        // Destruction de la session
        session_destroy();

        // Redirection vers la page de connexion
        header('Location: ?page=login');
        exit;
    }

    /**
     * Tableau de bord de l'administrateur
     * Page principale après connexion, affiche les informations de l'admin
     */
    public function dashboard()
    {
        // Étape 1: Vérification de la connexion
        $this->requireLogin();

        // Étape 2: Récupération des informations de session
        $admin_name = $_SESSION['admin_name'] ?? '';

        // Étape 3: Récupération des informations détaillées depuis la BD
        $adminModel = new Admin($this->pdo);
        $admin = $adminModel->findById($_SESSION['admin_id']);
        $role = $admin->role ?? 'ADMIN';

        // Étape 4: Récupération des messages flash
        $messages = $this->getFlashMessages();

        // Étape 5: Affichage du tableau de bord
        $this->render('admin/dashboard', array_merge($messages, [
            'admin_name' => $admin_name,
            'role' => $role
        ]));
    }

    /**
     * Gestion de la réinitialisation de mot de passe
     */
    public function resetPassword()
    {
        // Étape 1: Initialisation
        $token = $_GET['token'] ?? $_POST['token'] ?? null;
        $adminModel = new Admin($this->pdo);

        // Étape 2: Traitement du formulaire
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->verifyCsrfToken($_POST['csrf_token'] ?? null)) {
                $this->addErrorMessage("Requête invalide (CSRF).");
            } else {
                if (empty($token)) {
                    // CAS 1: Demande de réinitialisation
                    $email = trim($_POST['email'] ?? '');

                    if (empty($email)) {
                        $this->addErrorMessage("Veuillez renseigner une adresse email.");
                    } else {
                        if ($adminModel->requestPasswordReset($email)) {
                            $this->addSuccessMessage("Si cette adresse existe dans notre système, vous recevrez un email.");
                        }
                    }
                } else {
                    // CAS 2: Validation du nouveau mot de passe
                    $newPassword = $_POST['new_password'] ?? '';
                    $confirmPassword = $_POST['confirm_password'] ?? '';

                    if ($newPassword !== $confirmPassword) {
                        $this->addErrorMessage("Les mots de passe ne correspondent pas.");
                    } elseif (strlen($newPassword) < 8) {
                        $this->addErrorMessage("Le mot de passe doit contenir au moins 8 caractères.");
                    } else {
                        if ($adminModel->resetPassword($token, $newPassword)) {
                            $this->addSuccessMessage("Mot de passe mis à jour avec succès. Redirection vers la page de connexion...");
                            header("refresh:3;url=?page=login");
                            // Pas de exit ici car on veut afficher le message
                        } else {
                            $this->addErrorMessage("Lien de réinitialisation invalide ou expiré.");
                        }
                    }
                }
            }

            // Redirection pour éviter le rechargement du formulaire (sauf pour le cas avec refresh)
            if (!($token && $_POST['new_password'] && $adminModel->resetPassword($token, $_POST['new_password']))) {
                if ($token) {
                    header('Location: ?page=reset-password&token=' . urlencode($token));
                } else {
                    header('Location: ?page=reset-password');
                }
                exit;
            }
        }

        // Étape 3: Récupération des messages flash
        $messages = $this->getFlashMessages();

        // Étape 4: Affichage du formulaire approprié
        $this->render('admin/reset-password', array_merge($messages, [
            'token' => $token,
            'csrf_token' => $this->getCsrfToken()
        ]));
    }
}
