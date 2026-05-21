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
        $this->blockIfDemo("L'envoi d'invitations n'est pas disponible en mode démonstration.");

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
     * Inscription libre depuis la page vitrine (sans invitation)
     */
    public function autoRegister()
    {
        // Si déjà connecté, rediriger vers le dashboard
        if (isset($_SESSION['admin_logged']) && $_SESSION['admin_logged'] === true) {
            header('Location: ?page=dashboard');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Vérifier le CSRF
            if (!$this->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                $this->addErrorMessage('Token de sécurité invalide.', '');
                header('Location: ?page=auto-register');
                exit;
            }

            $username = trim($_POST['username'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $restaurantName = trim($_POST['restaurant_name'] ?? '');
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            $error = null;

            // Validations
            if (empty($username) || empty($email) || empty($restaurantName) || empty($password) || empty($confirmPassword)) {
                $error = "Tous les champs sont obligatoires.";
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = "L'adresse email n'est pas valide.";
            } elseif (strlen($username) < 3) {
                $error = "Le nom d'utilisateur doit contenir au moins 3 caractères.";
            } elseif (strlen($restaurantName) < 2) {
                $error = "Le nom du restaurant doit contenir au moins 2 caractères.";
            } else {
                $passwordErrors = Validator::validatePassword($password, $confirmPassword);
                if (!empty($passwordErrors)) {
                    $error = implode('<br>', $passwordErrors);
                }
            }

            if ($error) {
                $_SESSION['form_data'] = [
                    'username' => $username,
                    'email' => $email,
                    'restaurant_name' => $restaurantName
                ];
                $this->addErrorMessage($error, '');
                header('Location: ?page=auto-register');
                exit;
            }

            // Créer le compte
            $adminModel = new Admin($this->pdo);
            $adminId = $adminModel->createAccountDirect($username, $email, $restaurantName, $password);

            if ($adminId) {
                // Créer l'abonnement basique (inactif par défaut, activé après paiement)
                try {
                    $stmt = $this->pdo->prepare("
                        INSERT INTO client_subscriptions (admin_id, plan_type, status, price_per_month, started_at)
                        VALUES (?, 'basique', 'inactive', 9.00, NULL)
                    ");
                    $stmt->execute([$adminId]);
                } catch (Exception $e) {
                    error_log("Erreur création abonnement: " . $e->getMessage());
                }

                $this->addSuccessMessage("Compte créé ! Un email de confirmation vous a été envoyé. Cliquez sur le lien dans le mail pour activer votre compte.", '');
                header('Location: ?page=login');
                exit;
            } else {
                $_SESSION['form_data'] = [
                    'username' => $username,
                    'email' => $email,
                    'restaurant_name' => $restaurantName
                ];
                $this->addErrorMessage("Erreur lors de la création du compte. Le nom d'utilisateur ou l'email existe peut-être déjà.", '');
                header('Location: ?page=auto-register');
                exit;
            }
        }

        // Affichage du formulaire
        $messages = $this->getFlashMessages();
        $form_data = $_SESSION['form_data'] ?? [];
        unset($_SESSION['form_data']);

        $this->render('admin/auto-register', [
            'success_message' => $messages['success_message'],
            'error_message' => $messages['error_message'],
            'csrf_token' => $this->getCsrfToken(),
            'form_data' => $form_data
        ]);
    }

    /**
     * Vérifie le token de confirmation d'email et active le compte
     */
    public function verifyEmail()
    {
        $token = trim($_GET['token'] ?? '');

        if (empty($token)) {
            $this->addErrorMessage('Lien de confirmation invalide.');
            header('Location: ?page=login');
            exit;
        }

        try {
            $stmt = $this->pdo->prepare("
                SELECT id, username FROM admins
                WHERE verification_token = ? AND email_verified = 0
            ");
            $stmt->execute([$token]);
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log('[verifyEmail] Erreur SQL: ' . $e->getMessage());
            $admin = null;
        }

        if (!$admin) {
            $this->addErrorMessage('Ce lien est invalide ou a déjà été utilisé.');
            header('Location: ?page=login');
            exit;
        }

        // Marquer l'email comme vérifié
        $this->pdo->prepare("
            UPDATE admins SET email_verified = 1, verification_token = NULL WHERE id = ?
        ")->execute([$admin['id']]);

        $this->addSuccessMessage('Votre email a été confirmé ! Vous pouvez maintenant vous connecter.');
        header('Location: ?page=login');
        exit;
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
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            $error = null;

            // Validation améliorée avec messages spécifiques
            if (empty($username) || empty($password) || empty($confirmPassword)) {
                $error = "Tous les champs sont obligatoires.";
            } else {
                $passwordErrors = Validator::validatePassword($password, $confirmPassword);
                if (!empty($passwordErrors)) {
                    $error = implode('<br>', $passwordErrors);
                }
            }

            if ($error) {
                // Conserver les données saisies (sauf mots de passe pour la sécurité)
                $_SESSION['form_data'] = [
                    'username' => $username
                ];
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

        // Récupérer les données du formulaire en cas d'erreur
        $form_data = $_SESSION['form_data'] ?? [];
        unset($_SESSION['form_data']);

        // Étape 5: Affichage du formulaire d'inscription
        $this->render('admin/register', [
            'invitation' => $invitation,
            'token' => $token,
            'success_message' => $success_message,
            'error_message' => $error_message,
            'csrf_token' => $this->getCsrfToken(),
            'form_data' => $form_data
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
            // Rate limiting basé sur IP : max 5 tentatives par 15 minutes
            $rateLimiter = new RateLimiter();
            if (!$rateLimiter->attempt('login', 5, 900)) {
                $remaining = ceil($rateLimiter->retryAfter('login', 900) / 60);
                $error = "Trop de tentatives de connexion. Réessayez dans {$remaining} minute(s).";
            } else {
                $username = trim($_POST['username'] ?? '');
                $password = $_POST['password'] ?? '';

                if (empty($username) || empty($password)) {
                    $error = "Veuillez remplir tous les champs.";
                } else {
                    $adminModel = new Admin($this->pdo);
                    $user = $adminModel->login($username, $password);

                    if ($user === 'NOT_VERIFIED') {
                        $error = "Veuillez confirmer votre adresse email avant de vous connecter. Vérifiez votre boîte mail (et vos spams).";
                    } elseif ($user) {
                        // Reset rate limiting après succès
                        $rateLimiter->reset('login');

                        // Régénération de l'ID de session (anti session fixation)
                        session_regenerate_id(true);

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
        // En mode démo, rediriger vers la page de fin de démo
        if ($this->isDemoMode()) {
            header('Location: ?page=demo-logout');
            exit;
        }

        // Destruction de la session
        session_destroy();

        // Redirection vers la page de connexion avec un script pour afficher le toast
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <script>
                sessionStorage.setItem('pendingToast', JSON.stringify({
                    message: 'Vous avez été déconnecté avec succès.',
                    type: 'success'
                }));
                window.location.href = '?page=login';
            </script>
        </head>
        <body></body>
        </html>
        <?php
        exit;
    }

    /**
     * Tableau de bord de l'administrateur
     */
    public function dashboard()
    {
        $this->requireLogin();

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

        // Récupération du slug et de la date de dernière modification
        $slug = null;
        $last_updated = null;
        if ($restaurant_id) {
            try {
                $stmt = $this->pdo->prepare("SELECT slug, updated_at FROM restaurants WHERE id = ?");
                $stmt->execute([$restaurant_id]);
                $restaurant = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($restaurant) {
                    $slug = $restaurant['slug'] ?? null;
                    $last_updated = $restaurant['updated_at'] ?? null;
                }
            } catch (Exception $e) {
                // Silencieux en production
            }
        }

        $messages = $this->getFlashMessages();
        $success_message = $messages['success_message'];
        $error_message = $messages['error_message'];

        // Vérifier si l'option statistiques avancées est disponible
        $hasAdvancedStats = false;
        $hasOnlineBooking = false;
        try {
            require_once __DIR__ . '/../Models/PremiumFeature.php';
            $pf = new PremiumFeature($this->pdo);
            $hasAdvancedStats = ($role === 'SUPER_ADMIN') || $pf->isEnabled($_SESSION['admin_id'], 'advanced_analytics');
            $hasOnlineBooking = ($role === 'SUPER_ADMIN') || $pf->isEnabled($_SESSION['admin_id'], 'online_booking');
        } catch (Exception $e) { /* silencieux */ }

        // Récupérer les tokens de démo actifs pour SUPER_ADMIN
        $demoTokens = [];
        $demoExists = false;
        if ($role === 'SUPER_ADMIN' && !$this->isDemoMode()) {
            $demoTokenModel = new DemoToken($this->pdo);
            $demoTokens = $demoTokenModel->getActiveTokens();
            $demoExists = (bool) $demoTokenModel->getDemoAdminId();
        }

        $this->render('admin/dashboard', [
            'success_message' => $success_message,
            'error_message' => $error_message,
            'restaurant_name' => $restaurant_name,
            'username' => $username,
            'role' => $role,
            'last_updated' => $last_updated,
            'restaurant_id' => $restaurant_id,
            'slug' => $slug,
            'is_demo' => $this->isDemoMode(),
            'is_read_only' => $this->isReadOnly(),
            'has_advanced_stats' => $hasAdvancedStats,
            'has_online_booking' => $hasOnlineBooking,
            'demoTokens' => $demoTokens,
            'demoExists' => $demoExists,
            'csrf_token' => $this->getCsrfToken(),
        ]);
    }
    /**
     * Réinitialisation de mot de passe (2 étapes)
     * Étape 1 : saisie de l'email → envoi du lien de réinitialisation
     * Étape 2 : saisie du nouveau mot de passe via le token reçu par mail
     */
    public function resetPassword()
    {
        // Initialisation
        $token = $_GET['token'] ?? $_POST['token'] ?? null;
        $adminModel = new Admin($this->pdo);

        // Traitement du formulaire
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->verifyCsrfToken($_POST['csrf_token'] ?? null)) {
                ?>
                <!DOCTYPE html>
                <html>
                <head>
                    <meta charset="UTF-8">
                    <script>
                        sessionStorage.setItem('pendingToast', JSON.stringify({
                            message: 'Requête invalide (CSRF).',
                            type: 'error'
                        }));
                        window.location.href = '<?= $token ? "?page=reset-password&token=" . urlencode($token) : "?page=reset-password" ?>';
                    </script>
                </head>
                <body></body>
                </html>
                <?php
                exit;
            }

            if (empty($token)) {
                // Étape 1 : demande d'email
                $email = trim($_POST['email'] ?? '');
                $message = '';
                $type = '';
                
                if (empty($email)) {
                    $message = 'Veuillez renseigner une adresse email.';
                    $type = 'error';
                } else {
                    if ($adminModel->requestPasswordReset($email)) {
                        $message = 'Si cette adresse existe dans notre système, vous recevrez un email.';
                        $type = 'success';
                    } else {
                        $message = 'Erreur lors de l\'envoi de l\'email de réinitialisation.';
                        $type = 'error';
                    }
                }
                ?>
                <!DOCTYPE html>
                <html>
                <head>
                    <meta charset="UTF-8">
                    <script>
                        sessionStorage.setItem('pendingToast', JSON.stringify({
                            message: '<?= addslashes($message) ?>',
                            type: '<?= $type ?>'
                        }));
                        window.location.href = '?page=reset-password';
                    </script>
                </head>
                <body></body>
                </html>
                <?php
                exit;
            } else {
                // Étape 2 : réinitialisation du mot de passe
                $newPassword = $_POST['new_password'] ?? '';
                $confirmPassword = $_POST['confirm_password'] ?? '';

                // Validation côté serveur via Validator centralisé
                $errors = Validator::validatePassword($newPassword, $confirmPassword);

                if (!empty($errors)) {
                    $errorMessage = addslashes(implode(' ', $errors));
                    ?>
                    <!DOCTYPE html>
                    <html>
                    <head>
                        <meta charset="UTF-8">
                        <script>
                            sessionStorage.setItem('pendingToast', JSON.stringify({
                                message: '<?= $errorMessage ?>',
                                type: 'error'
                            }));
                            window.location.href = '?page=reset-password&token=<?= urlencode($token) ?>';
                        </script>
                    </head>
                    <body></body>
                    </html>
                    <?php
                    exit;
                }

                // Tentative de réinitialisation
                if ($adminModel->resetPassword($token, $newPassword)) {
                    ?>
                    <!DOCTYPE html>
                    <html>
                    <head>
                        <meta charset="UTF-8">
                        <script>
                            sessionStorage.setItem('pendingToast', JSON.stringify({
                                message: 'Mot de passe mis à jour avec succès. Vous pouvez maintenant vous connecter.',
                                type: 'success'
                            }));
                            window.location.href = '?page=login';
                        </script>
                    </head>
                    <body></body>
                    </html>
                    <?php
                    exit;
                } else {
                    ?>
                    <!DOCTYPE html>
                    <html>
                    <head>
                        <meta charset="UTF-8">
                        <script>
                            sessionStorage.setItem('pendingToast', JSON.stringify({
                                message: 'Lien de réinitialisation invalide ou expiré.',
                                type: 'error'
                            }));
                            window.location.href = '?page=reset-password&token=<?= urlencode($token) ?>';
                        </script>
                    </head>
                    <body></body>
                    </html>
                    <?php
                    exit;
                }
            }
        }

        // Affichage du formulaire (première visite ou après erreur)
        $success_message = $_SESSION['success_message'] ?? null;
        $error_message = $_SESSION['error_message'] ?? null;
        unset($_SESSION['success_message'], $_SESSION['error_message']);

        $this->render('admin/reset-password', [
            'success_message' => $success_message,
            'error_message' => $error_message,
            'token' => $token,
            'csrf_token' => $this->getCsrfToken()
        ]);
    }

    /**
     * Réinitialisation du mot de passe (contexte admin sécurisé)
     * Réservée aux utilisateurs déjà connectés
     * Étape 1 : saisie de l'email → envoi du lien de réinitialisation
     * Étape 2 : saisie du nouveau mot de passe via le token reçu par mail
     */
    public function resetPasswordAdmin()
    {
        // Vérification de sécurité : l'utilisateur doit être connecté
        $this->requireLogin();

        // Initialisation
        $token = $_GET['token'] ?? $_POST['token'] ?? null;
        $adminModel = new Admin($this->pdo);

        // Traitement du formulaire
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->verifyCsrfToken($_POST['csrf_token'] ?? null)) {
                $_SESSION['error_message'] = "Requête invalide (CSRF).";
                // Redirection vers la même page (avec ou sans token)
                $redirect = $token ? '?page=reset-password-admin&token=' . urlencode($token) : '?page=reset-password-admin';
                header('Location: ' . $redirect);
                exit;
            }

            if (empty($token)) {
                // Étape 1 : demande d'email
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
                header('Location: ?page=reset-password-admin');
                exit;
            } else {
                // Étape 2 : réinitialisation du mot de passe
                $newPassword = $_POST['new_password'] ?? '';
                $confirmPassword = $_POST['confirm_password'] ?? '';

                // Validation côté serveur via Validator centralisé
                $errors = Validator::validatePassword($newPassword, $confirmPassword);

                if (!empty($errors)) {
                    $_SESSION['error_message'] = implode('<br>', $errors);
                    header('Location: ?page=reset-password-admin&token=' . urlencode($token));
                    exit;
                }

                // Tentative de réinitialisation
                if ($adminModel->resetPassword($token, $newPassword)) {
                    $_SESSION['success_message'] = "Mot de passe mis à jour avec succès.";
                    header('Location: ?page=settings&section=password');
                    exit;
                } else {
                    $_SESSION['error_message'] = "Lien de réinitialisation invalide ou expiré.";
                    header('Location: ?page=reset-password-admin&token=' . urlencode($token));
                    exit;
                }
            }
        }

        // Affichage du formulaire (première visite ou après erreur)
        $success_message = $_SESSION['success_message'] ?? null;
        $error_message = $_SESSION['error_message'] ?? null;
        unset($_SESSION['success_message'], $_SESSION['error_message']);

        $this->render('admin/reset-password-admin', [
            'success_message' => $success_message,
            'error_message' => $error_message,
            'token' => $token,
            'csrf_token' => $this->getCsrfToken()
        ]);
    }
}
