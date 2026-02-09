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
        $this->setScrollDelay(1500);
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
            $this->addErrorMessage("Accès refusé : réservé aux SUPER_ADMIN uniquement.", '');
            header('Location: ?page=dashboard');
            exit;
        }

        // Étape 3: Traitement du formulaire si soumis
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->verifyCsrfToken($_POST['csrf_token'] ?? null)) {
                $this->addErrorMessage("Requête invalide (CSRF).", '');
            } else {
                $email = trim($_POST['email'] ?? '');
                $restaurantName = trim($_POST['restaurant_name'] ?? '');

                if (empty($email) || empty($restaurantName)) {
                    $this->addErrorMessage("Veuillez remplir tous les champs.", '');
                } else {
                    $adminModel = new Admin($this->pdo);
                    $token = bin2hex(random_bytes(32));

                    if ($adminModel->createInvitation($email, $restaurantName, $token)) {
                        $this->addSuccessMessage("L'invitation a été envoyée avec succès à $email.", '');
                    } else {
                        $this->addErrorMessage("Erreur lors de l'envoi de l'invitation. Vérifiez les logs.", '');
                    }
                }
            }

            // Redirection pour éviter le rechargement du formulaire
            header('Location: ?page=send-invitation');
            exit;
        }

        // Étape 4: Récupération des messages flash
        $messages = $this->getFlashMessages();
        $success_message = $messages['success_message'];
        $error_message = $messages['error_message'];

        // Étape 5: Affichage de la vue avec les données
        $this->render('admin/send-invitation', [
            'success_message' => $success_message,
            'error_message' => $error_message,
            'csrf_token' => $this->getCsrfToken()
        ]);
    }

    /**
     * Inscription via un lien d'invitation
     */
    public function register()
    {
        // Étape 1: Initialisation et récupération du token
        $token = $_GET['token'] ?? null;

        if (empty($token)) {
            $this->addErrorMessage("Token d'invitation manquant.", '');
            header('Location: ?page=login');
            exit;
        }

        // Étape 2: Vérification de la validité de l'invitation
        $adminModel = new Admin($this->pdo);
        $invitation = $adminModel->getInvitation($token);

        if (!$invitation) {
            $this->addErrorMessage("Lien d'invitation invalide ou introuvable.", '');
            header('Location: ?page=login');
            exit;
        }

        if (strtotime($invitation->expiry) < time()) {
            $this->addErrorMessage("Ce lien d'invitation a expiré.", '');
            header('Location: ?page=login');
            exit;
        }

        if ($invitation->used == 1) {
            $this->addErrorMessage("Ce lien d'invitation a déjà été utilisé.", '');
            header('Location: ?page=login');
            exit;
        }

        // Étape 3: Traitement du formulaire d'inscription si soumis
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username'] ?? '');
            $password = trim($_POST['password'] ?? '');
            $confirmPassword = trim($_POST['confirm_password'] ?? '');
            $error = null;

            // Validation
            if (empty($username) || empty($password) || empty($confirmPassword)) {
                $error = "Tous les champs sont obligatoires.";
            } elseif ($password !== $confirmPassword) {
                $error = "Les mots de passe ne correspondent pas.";
            } elseif (strlen($password) < 8) {
                $error = "Le mot de passe doit contenir au moins 8 caractères.";
            } elseif (!preg_match('/[a-zA-Z]/', $password) || !preg_match('/\d/', $password)) {
                $error = "Le mot de passe doit contenir au moins une lettre et un chiffre.";
            }

            if ($error) {
                $this->addErrorMessage($error, '');
                header('Location: ?page=register&token=' . urlencode($token));
                exit;
            }

            // Essayer de créer le compte
            if ($adminModel->createAccount($invitation, $username, $password)) {
                $this->addSuccessMessage("Votre compte a été créé avec succès. Vous pouvez maintenant vous connecter.", '');
                header('Location: ?page=login');
                exit;
            } else {
                $this->addErrorMessage("Erreur lors de la création du compte. Le nom d'utilisateur existe peut-être déjà.", '');
                header('Location: ?page=register&token=' . urlencode($token));
                exit;
            }
        }

        // Étape 4: Récupération des messages flash
        $messages = $this->getFlashMessages();
        $success_message = $messages['success_message'];
        $error_message = $messages['error_message'];

        // Étape 5: Affichage du formulaire d'inscription
        $this->render('admin/register', [
            'invitation' => $invitation,
            'token' => $token,
            'success_message' => $success_message,
            'error_message' => $error_message,
            'csrf_token' => $this->getCsrfToken()
        ]);
    }

    /**
     * Connexion d'un administrateur
     */
    public function login()
    {
        // Récupération des messages flash
        $messages = $this->getFlashMessages();
        $success_message = $messages['success_message'];
        $error_message = $messages['error_message'];

        $error = null;

        // Traitement du formulaire
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
                    $_SESSION['username'] = $user->username;

                    // Redirection vers le dashboard
                    header('Location: ?page=dashboard');
                    exit;
                } else {
                    $error = "Identifiant ou mot de passe incorrect.";
                }
            }
        }

        // Affichage
        $this->render('admin/login', [
            'error_message' => $error ?? $error_message,
            'success_message' => $success_message
        ]);
    }

    /**
     * Déconnexion de l'administrateur
     * Termine la session en cours et redirige vers la page de login
     */
    public function logout()
    {
        // Message de déconnexion
        $_SESSION['success_message'] = "Vous avez été déconnecté avec succès.";

        // Destruction de la session
        session_destroy();

        // Redirection vers la page de connexion
        header('Location: ?page=login');
        exit;
    }

    /**
     * Tableau de bord de l'administrateur
     */
    public function dashboard()
    {
        // Étape 1: Vérification de la connexion
        $this->requireLogin();

        // Étape 2: Récupération des informations détaillées depuis la BD
        $adminModel = new Admin($this->pdo);
        $admin = $adminModel->findById($_SESSION['admin_id']);

        if (!$admin) {
            $this->addErrorMessage("Administrateur non trouvé.", '');
            header('Location: ?page=login');
            exit;
        }

        $role = $admin->role ?? 'ADMIN';
        $restaurant_name = $admin->restaurant_name ?? '';
        $username = $admin->username ?? '';
        $restaurant_id = $admin->restaurant_id ?? null;

        // Étape 3: Récupération de la date de dernière modification du restaurant
        $last_updated = null;
        if ($restaurant_id) {
            try {
                $stmt = $this->pdo->prepare("SELECT updated_at FROM restaurants WHERE id = ?");
                $stmt->execute([$restaurant_id]);
                $restaurant = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($restaurant && $restaurant['updated_at']) {
                    $last_updated = $restaurant['updated_at'];
                }
            } catch (Exception $e) {
                error_log("Erreur récupération date mise à jour: " . $e->getMessage());
            }
        }

        // Étape 4: Récupération des messages flash
        $messages = $this->getFlashMessages();
        $success_message = $messages['success_message'];
        $error_message = $messages['error_message'];

        // Étape 5: Affichage du tableau de bord
        $this->render('admin/dashboard', [
            'success_message' => $success_message,
            'error_message' => $error_message,
            'restaurant_name' => $restaurant_name,
            'username' => $username,
            'role' => $role,
            'last_updated' => $last_updated,
            'restaurant_id' => $restaurant_id
        ]);
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
                $_SESSION['error_message'] = "Requête invalide (CSRF).";
            } else {
                if (empty($token)) {
                    // CAS 1: Demande de réinitialisation
                    $email = trim($_POST['email'] ?? '');

                    if (empty($email)) {
                        $_SESSION['error_message'] = "Veuillez renseigner une adresse email.";
                    } else {
                        if ($adminModel->requestPasswordReset($email)) {
                            $_SESSION['success_message'] = "Si cette adresse existe dans notre système, vous recevrez un email.";
                        } else {
                            $_SESSION['error_message'] = "Erreur lors de l'envoi de l'email de réinitialisation.";
                        }
                    }
                } else {
                    // CAS 2: Validation du nouveau mot de passe
                    $newPassword = $_POST['new_password'] ?? '';
                    $confirmPassword = $_POST['confirm_password'] ?? '';

                    if ($newPassword !== $confirmPassword) {
                        $_SESSION['error_message'] = "Les mots de passe ne correspondent pas.";
                    } elseif (strlen($newPassword) < 8) {
                        $_SESSION['error_message'] = "Le mot de passe doit contenir au moins 8 caractères.";
                    } else {
                        if ($adminModel->resetPassword($token, $newPassword)) {
                            $_SESSION['success_message'] = "Mot de passe mis à jour avec succès. Redirection vers la page de connexion...";
                            header("refresh:3;url=?page=login");
                            // Pas de exit ici car on veut afficher le message
                        } else {
                            $_SESSION['error_message'] = "Lien de réinitialisation invalide ou expiré.";
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
        $success_message = $_SESSION['success_message'] ?? null;
        $error_message = $_SESSION['error_message'] ?? null;

        // Nettoyer après récupération
        unset($_SESSION['success_message'], $_SESSION['error_message']);

        // Étape 4: Affichage du formulaire approprié
        $this->render('admin/reset-password', [
            'success_message' => $success_message,
            'error_message' => $error_message,
            'token' => $token,
            'csrf_token' => $this->getCsrfToken()
        ]);
    }
}
