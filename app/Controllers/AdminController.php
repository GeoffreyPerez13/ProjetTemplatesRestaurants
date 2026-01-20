<?php

require_once __DIR__ . '/BaseController.php';      // Inclusion du contrôleur de base pour hériter des fonctionnalités communes
require_once __DIR__ . '/../Models/Admin.php';     // Inclusion du modèle Admin pour interagir avec la table des administrateurs

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
        parent::__construct($pdo);  // Appelle le constructeur du parent (BaseController) pour initialiser la connexion PDO
    }

    /**
     * Envoie une invitation pour créer un compte administrateur restaurant
     * Méthode réservée aux SUPER_ADMIN seulement
     */
    public function sendInvitation()
    {
        // Étape 1: Vérifier que l'utilisateur est connecté
        $this->requireLogin();  // Si non connecté, redirection vers login.php

        // Étape 2: Vérifier les permissions (SUPER_ADMIN uniquement)
        $adminModel = new Admin($this->pdo);                    // Création d'une instance du modèle Admin
        $admin = $adminModel->findById($_SESSION['admin_id']); // Récupération des infos de l'admin connecté

        if ($admin->role !== 'SUPER_ADMIN') {
            // Si l'utilisateur n'est pas SUPER_ADMIN, on le redirige vers le dashboard
            header('Location: ?page=dashboard');  // Redirection HTTP vers le tableau de bord
            exit;                                  // Arrêt de l'exécution du script
        }

        // Étape 3: Initialisation des variables de messages
        $error = null;    // Variable pour stocker les messages d'erreur
        $success = null;  // Variable pour stocker les messages de succès

        // Étape 4: Traitement du formulaire si soumis (méthode POST)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Vérification du token CSRF pour prévenir les attaques par falsification de requête
            if (!$this->verifyCsrfToken($_POST['csrf_token'] ?? null)) {
                $error = "Requête invalide (CSRF).";  // Message d'erreur si le token est invalide
            } else {
                // Récupération et nettoyage des données du formulaire
                $email = trim($_POST['email'] ?? '');                 // Supprime les espaces autour de l'email
                $restaurantName = trim($_POST['restaurant_name'] ?? '');  // Supprime les espaces autour du nom

                // Vérification que tous les champs obligatoires sont remplis
                if (empty($email) || empty($restaurantName)) {
                    $error = "Veuillez remplir tous les champs.";  // Message d'erreur si champ vide
                } else {
                    // Étape 5: Préparation de l'invitation
                    $adminModel = new Admin($this->pdo);  // Nouvelle instance du modèle (ou réutilisation)
                    
                    // Génération d'un token sécurisé pour l'invitation
                    // random_bytes(32) génère 32 octets aléatoires cryptographiquement sécurisés
                    // bin2hex() convertit ces octets en chaîne hexadécimale lisible (64 caractères)
                    $token = bin2hex(random_bytes(32));

                    // Tentative de création de l'invitation dans la base de données
                    if ($adminModel->createInvitation($email, $restaurantName, $token)) {
                        // Construction du lien d'invitation complet
                        // $_SERVER['HTTP_HOST'] contient le nom d'hôte (ex: "mon-site.com")
                        $inviteLink = "http://" . $_SERVER['HTTP_HOST'] . "?page=register&token=" . $token;

                        // Gestion du message de succès selon l'environnement
                        if (defined('DEV_SHOW_LINK') && DEV_SHOW_LINK === true) {
                            // En environnement de développement, on pourrait montrer le lien
                            $success = "L'invitation a été envoyée avec succès. Lien: " . $inviteLink;
                        } else {
                            // En production, message générique sans le lien
                            $success = "L'invitation a été envoyée avec succès.";
                        }
                    } else {
                        // Échec de la création de l'invitation dans la base
                        $error = "Erreur lors de la création de l'invitation.";
                    }
                }
            }
        }

        // Étape 6: Affichage de la vue avec les données
        $this->render('admin/send-invitation', [
            'error' => $error,                          // Message d'erreur éventuel
            'success' => $success,                      // Message de succès éventuel
            'csrf_token' => $this->getCsrfToken()      // Génération d'un nouveau token CSRF pour le formulaire
        ]);
    }

    /**
     * Inscription via un lien d'invitation
     * Permet à un restaurateur de créer son compte après avoir reçu une invitation
     * @param string $token Token d'invitation récupéré depuis l'URL (via $_GET)
     */
    public function register()
    {
        // Étape 1: Initialisation et récupération du token
        $error = null;                        // Variable pour les messages d'erreur
        $token = $_GET['token'] ?? null;      // Récupère le token depuis l'URL (?token=...)

        // Vérification de la présence du token
        if (empty($token)) {
            // Si aucun token n'est fourni, redirection vers la page de login
            header('Location: ?page=login');  // Redirection HTTP
            exit;                             // Arrêt du script
        }

        // Étape 2: Vérification de la validité de l'invitation
        $adminModel = new Admin($this->pdo);          // Instance du modèle Admin
        $invitation = $adminModel->getInvitation($token);  // Récupère l'invitation depuis la BD

        // Vérification: l'invitation existe ET n'est pas expirée
        // strtotime($invitation->expiry) convertit la date d'expiration en timestamp
        // time() retourne le timestamp actuel
        if (!$invitation || strtotime($invitation->expiry) < time()) {
            $error = "Ce lien d'invitation n'est plus valide.";  // Message d'erreur
            $this->render('admin/register', ['error' => $error]);  // Affichage de la vue avec l'erreur
            return;  // Arrêt de la méthode (pas de exit car on veut juste retourner au contrôleur)
        }

        // Étape 3: Traitement du formulaire d'inscription si soumis
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Vérification CSRF
            if (!$this->verifyCsrfToken($_POST['csrf_token'] ?? null)) {
                $error = "Requête invalide (CSRF).";
            } else {
                // Récupération et nettoyage des données du formulaire
                $username = trim($_POST['username'] ?? '');           // Nom d'utilisateur
                $password = trim($_POST['password'] ?? '');           // Mot de passe
                $confirmPassword = trim($_POST['confirm_password'] ?? '');  // Confirmation

                // Vérification que les mots de passe correspondent
                if ($password !== $confirmPassword) {
                    $error = "Les mots de passe ne correspondent pas.";
                } 
                // Vérification de la longueur minimale du mot de passe
                elseif (strlen($password) < 8) {
                    $error = "Le mot de passe doit contenir au moins 8 caractères.";
                } else {
                    // Tentative de création du compte
                    if ($adminModel->createAccount($invitation, $username, $password)) {
                        // Succès: redirection vers la page de login avec paramètre de succès
                        header('Location: ?page=login&success=1');  // success=1 sera lu par login()
                        exit;  // Arrêt du script après redirection
                    } else {
                        // Échec: récupération de l'erreur PHP
                        $lastError = error_get_last();  // Récupère la dernière erreur PHP
                        $error = "Erreur lors de la création du compte.";  // Message générique
                        if ($lastError) {
                            // Ajout des détails de l'erreur pour le débogage
                            $error .= " Détails : " . $lastError['message'];
                        }
                    }
                }
            }
        }

        // Étape 4: Affichage du formulaire d'inscription
        $this->render('admin/register', [
            'error' => $error,                          // Messages d'erreur
            'invitation' => $invitation,               // Données de l'invitation (pour pré-remplir)
            'csrf_token' => $this->getCsrfToken()      // Token CSRF pour le formulaire
        ]);
    }

    /**
     * Connexion d'un administrateur
     * Authentifie un administrateur et démarre une session
     * @param string $username Nom d'utilisateur (via $_POST)
     * @param string $password Mot de passe (via $_POST)
     */
    public function login()
    {
        // Étape 1: Initialisation des variables
        $error = null;      // Pour les erreurs d'authentification
        $success = null;    // Pour les messages de succès (compte créé)

        // Vérification si l'utilisateur vient de créer son compte
        // $_GET['success'] == 1 est défini par la redirection dans register()
        if (isset($_GET['success']) && $_GET['success'] == 1) {
            $success = "Votre compte a été créé avec succès. Vous pouvez maintenant vous connecter.";
        }

        // Étape 2: Traitement du formulaire de connexion
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Récupération et nettoyage des identifiants
            $username = trim($_POST['username'] ?? '');
            $password = trim($_POST['password'] ?? '');

            // Vérification que les champs ne sont pas vides
            if (empty($username) || empty($password)) {
                $error = "Veuillez remplir tous les champs.";
            } else {
                // Tentative d'authentification via le modèle
                $adminModel = new Admin($this->pdo);
                $user = $adminModel->login($username, $password);  // Vérifie les identifiants dans la BD

                if ($user) {
                    // Authentification réussie: création de la session
                    $_SESSION['admin_logged'] = true;              // Indicateur de connexion
                    $_SESSION['admin_id'] = $user->id;             // ID de l'admin (pour les requêtes)
                    $_SESSION['admin_name'] = $user->restaurant_name;  // Nom du restaurant (pour affichage)

                    // Redirection vers le tableau de bord
                    header('Location: ?page=dashboard');
                    exit;  // Arrêt du script
                } else {
                    // Identifiants incorrects
                    $error = "Identifiant ou mot de passe incorrect.";
                }
            }
        }

        // Étape 3: Affichage du formulaire de connexion
        $this->render('admin/login', [
            'error' => $error,      // Message d'erreur éventuel
            'success' => $success   // Message de succès éventuel (compte créé)
        ]);
    }

    /**
     * Déconnexion de l'administrateur
     * Termine la session en cours et redirige vers la page de login
     */
    public function logout()
    {
        // Destruction complète de la session
        // Supprime: $_SESSION, cookie de session, données serveur
        session_destroy();

        // Redirection vers la page de connexion
        header('Location: ?page=login');
        exit;  // Arrêt du script
    }

    /**
     * Tableau de bord de l'administrateur
     * Page principale après connexion, affiche les informations de l'admin
     */
    public function dashboard()
    {
        // Étape 1: Vérification de la connexion
        $this->requireLogin();  // Redirige vers login si non connecté

        // Étape 2: Récupération des informations de session
        $admin_name = $_SESSION['admin_name'] ?? '';  // Nom du restaurant (avec valeur par défaut)

        // Étape 3: Récupération des informations détaillées depuis la BD
        $adminModel = new Admin($this->pdo);
        $admin = $adminModel->findById($_SESSION['admin_id']);  // Récupère l'objet Admin complet
        $role = $admin->role ?? 'ADMIN';  // Rôle de l'admin (avec valeur par défaut 'ADMIN')

        // Étape 4: Affichage du tableau de bord
        $this->render('admin/dashboard', [
            'admin_name' => $admin_name,  // Nom à afficher dans l'interface
            'role' => $role              // Rôle (pour éventuelles permissions dans la vue)
        ]);
    }

    /**
     * Gestion de la réinitialisation de mot de passe
     * Deux modes: demande de réinitialisation (sans token) et validation (avec token)
     * @param string|null $token Token de réinitialisation (optionnel - depuis URL ou formulaire)
     * @param string|null $email Adresse email pour la demande (optionnel - depuis formulaire)
     * @param string|null $newPassword Nouveau mot de passe (optionnel - depuis formulaire)
     * @param string|null $confirmPassword Confirmation du nouveau mot de passe (optionnel)
     */
    public function resetPassword()
    {
        // Étape 1: Initialisation des variables
        $error = null;      // Messages d'erreur
        $success = null;    // Messages de succès
        
        // Récupération du token depuis l'URL (?token=...) ou depuis un champ caché du formulaire
        $token = $_GET['token'] ?? $_POST['token'] ?? null;
        
        $adminModel = new Admin($this->pdo);  // Instance du modèle pour les opérations BD

        // Étape 2: Traitement du formulaire
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Vérification CSRF
            if (!$this->verifyCsrfToken($_POST['csrf_token'] ?? null)) {
                $error = "Requête invalide (CSRF).";
            } else {
                // Deux cas possibles:
                // 1. Demande initiale (sans token): on envoie un email de réinitialisation
                // 2. Validation (avec token): on change le mot de passe
                
                if (empty($token)) {
                    // CAS 1: Demande de réinitialisation (étape 1)
                    $email = trim($_POST['email'] ?? '');  // Email saisi par l'utilisateur
                    
                    if (empty($email)) {
                        $error = "Veuillez renseigner une adresse email.";
                    } else {
                        // Tentative d'envoi d'email de réinitialisation
                        if ($adminModel->requestPasswordReset($email)) {
                            // Message générique (bonne pratique de sécurité)
                            $success = "Si cette adresse existe dans notre système, vous recevrez un email.";
                        }
                        // Note: même en cas d'échec, on ne montre pas d'erreur spécifique
                        // pour éviter de divulguer qu'un email existe ou non dans le système
                    }
                } else {
                    // CAS 2: Validation du nouveau mot de passe (étape 2)
                    $newPassword = $_POST['new_password'] ?? '';           // Nouveau mot de passe
                    $confirmPassword = $_POST['confirm_password'] ?? '';   // Confirmation
                    
                    // Vérification que les deux mots de passe correspondent
                    if ($newPassword !== $confirmPassword) {
                        $error = "Les mots de passe ne correspondent pas.";
                    } 
                    // Vérification de la longueur minimale
                    elseif (strlen($newPassword) < 8) {
                        $error = "Le mot de passe doit contenir au moins 8 caractères.";
                    } else {
                        // Tentative de mise à jour du mot de passe dans la BD
                        if ($adminModel->resetPassword($token, $newPassword)) {
                            $success = "Mot de passe mis à jour avec succès.";
                            // Redirection automatique après 3 secondes
                            // header("refresh:3;url=?page=login") envoie un en-tête Refresh
                            // Le navigateur attend 3 secondes puis redirige vers login
                            header("refresh:3;url=?page=login");
                        } else {
                            // Échec: token invalide ou expiré
                            $error = "Lien de réinitialisation invalide ou expiré.";
                        }
                    }
                }
            }
        }

        // Étape 3: Affichage du formulaire approprié
        $this->render('admin/reset-password', [
            'error' => $error,                          // Messages d'erreur
            'success' => $success,                      // Messages de succès
            'token' => $token,                         // Token (null pour étape 1, valeur pour étape 2)
            'csrf_token' => $this->getCsrfToken()      // Token CSRF pour le formulaire
        ]);
    }
}