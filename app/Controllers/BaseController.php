<?php

/**
 * Classe de base dont héritent tous les autres contrôleurs
 * Elle contient les fonctionnalités communes à toutes les pages du back-office
 * Cette classe fournit les méthodes essentielles pour la gestion des sessions,
 * la sécurité CSRF, les messages flash et le rendu des vues.
 */
class BaseController
{
    /**
     * Propriété pour stocker la connexion PDO à la base de données
     * @var PDO Instance de connexion à la base de données
     * @access protected Accessible aux classes qui héritent de BaseController
     */
    protected $pdo;

    /**
     * Délai global de défilement (scroll) après affichage d'un message
     * Par défaut: 3500 millisecondes (.,5 secondes)
     * Cette valeur peut être modifiée dans chaque contrôleur enfant selon les besoins
     * @var int Délai en millisecondes avant le défilement automatique
     * @access protected Accessible aux classes qui héritent de BaseController
     */
    protected $scrollDelay = 3500;

    /**
     * Constructeur : initialise la connexion à la base de données
     * @param PDO $pdo Instance de connexion PDO à la base de données
     * 
     * Explication détaillée:
     * - Reçoit une instance PDO qui doit être créée dans le point d'entrée principal (index.php)
     * - Stocke cette instance dans la propriété $this->pdo pour un usage ultérieur
     * - Permet à tous les contrôleurs enfants d'accéder à la même connexion DB
     */
    public function __construct($pdo)
    {
        $this->pdo = $pdo;  // Stockage de la connexion PDO dans la propriété de classe

        // Headers de sécurité (envoyés pour chaque réponse)
        if (!headers_sent()) {
            header('X-Content-Type-Options: nosniff');
            header('X-Frame-Options: DENY');
            header('X-XSS-Protection: 1; mode=block');
            header('Referrer-Policy: strict-origin-when-cross-origin');
            // HSTS : force HTTPS pendant 1 an (actif uniquement si la requête est en HTTPS)
            if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
                header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
            }
        }
    }

    /**
     * Vérifie si un administrateur est connecté
     * Retourne true si la session contient une variable "admin_logged" à true
     * 
     * @return bool true si l'administrateur est connecté, false sinon
     * 
     * Explication détaillée:
     * - Vérifie deux conditions avec l'opérateur ET logique (&&):
     *   1. $_SESSION['admin_logged'] existe (isset() vérifie que la variable est définie)
     *   2. $_SESSION['admin_logged'] est strictement égal à true (=== pour éviter les conversions de type)
     * - Cette méthode est utilisée comme condition pour protéger l'accès aux pages admin
     */
    protected function isLogged()
    {
        return isset($_SESSION['admin_logged']) && $_SESSION['admin_logged'] === true;
    }

    /**
     * Récupère le nombre de réservations en attente pour les notifications
     * 
     * @return int Nombre de réservations en attente
     */
    protected function getPendingReservationsCount()
    {
        if (!$this->isLogged() || empty($_SESSION['admin_id'])) {
            return 0;
        }

        try {
            // Vérifier si la table reservations existe
            $stmt = $this->pdo->query("SHOW TABLES LIKE 'reservations'");
            if ($stmt->rowCount() === 0) {
                return 0;
            }

            // Compter les réservations en attente
            $stmt = $this->pdo->prepare("
                SELECT COUNT(*) FROM reservations 
                WHERE admin_id = ? AND status = 'pending'
            ");
            $stmt->execute([$_SESSION['admin_id']]);
            return (int)$stmt->fetchColumn();
        } catch (Exception $e) {
            error_log("Erreur getPendingReservationsCount: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Récupère les options de masquage des boutons (dark mode et tour guidé)
     * 
     * @return array Tableau avec hide_dark_mode et hide_tour_button (boolean)
     */
    protected function getButtonVisibilityOptions()
    {
        if (!$this->isLogged() || empty($_SESSION['admin_id'])) {
            return ['hide_dark_mode' => false, 'hide_tour_button' => false];
        }

        try {
            $stmt = $this->pdo->prepare("
                SELECT option_name, option_value 
                FROM admin_options 
                WHERE admin_id = ? AND option_name IN ('hide_dark_mode', 'hide_tour_button')
            ");
            $stmt->execute([$_SESSION['admin_id']]);
            $options = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

            return [
                'hide_dark_mode' => isset($options['hide_dark_mode']) && $options['hide_dark_mode'] === '1',
                'hide_tour_button' => isset($options['hide_tour_button']) && $options['hide_tour_button'] === '1'
            ];
        } catch (Exception $e) {
            error_log("Erreur getButtonVisibilityOptions: " . $e->getMessage());
            return ['hide_dark_mode' => false, 'hide_tour_button' => false];
        }
    }

    /**
     * Force la connexion : si l'utilisateur n'est pas connecté, il est redirigé vers la page de login
     * 
     * Explication détaillée:
     * 1. Appelle $this->isLogged() pour vérifier l'état de connexion
     * 2. Si isLogged() retourne false (utilisateur non connecté):
     *    - Envoie un en-tête HTTP "Location: login.php" qui redirige le navigateur
     *    - Utilise exit pour arrêter immédiatement l'exécution du script
     *    - Empêche l'accès aux pages protégées sans authentification
     * 3. Si isLogged() retourne true, l'exécution continue normalement
     * 
     * Note: Cette méthode est typiquement appelée au début de chaque action protégée
     */
    protected function requireLogin()
    {
        // Vérification de l'état de connexion
        if (!$this->isLogged()) {
            // Si non connecté, redirection vers la page de connexion
            header('Location: ?page=login'); // En-tête HTTP 302 (redirection temporaire)
            exit; // Arrêt immédiat pour éviter que le reste du code s'exécute
        }

        // Vérification de l'expiration de la session démo
        if ($this->isDemoMode()) {
            $this->checkDemoExpiry();
        }
        // Si connecté, le code continue après cette méthode
    }

    /**
     * Vérifie si la session courante est en mode démo
     *
     * @return bool true si l'utilisateur est en session démo
     */
    protected function isDemoMode()
    {
        return !empty($_SESSION['demo_mode']) && $_SESSION['demo_mode'] === true;
    }

    /**
     * Vérifie que la session démo n'est pas expirée
     * Si expirée, détruit la session et redirige vers la page d'expiration
     */
    protected function checkDemoExpiry()
    {
        if (empty($_SESSION['demo_expires_at'])) {
            return;
        }
        if (strtotime($_SESSION['demo_expires_at']) < time()) {
            session_destroy();
            session_start();
            header('Location: ?page=demo-access&token=expired');
            exit;
        }
    }

    /**
     * Bloque une action si l'utilisateur est en mode démo
     * Redirige vers le dashboard avec un message d'erreur
     *
     * @param string $message Message expliquant la restriction
     */
    protected function blockIfDemo($message = "Cette action n'est pas disponible en mode démonstration.")
    {
        if ($this->isDemoMode()) {
            $this->addErrorMessage($message);
            header('Location: ?page=dashboard');
            exit;
        }
    }

    /**
     * Vérifie si l'admin connecté est en mode lecture seule (pas d'abonnement basique actif)
     * Le SUPER_ADMIN est toujours exempté.
     *
     * @return bool true si l'admin est en lecture seule
     */
    protected function isReadOnly()
    {
        if (!$this->isLogged()) {
            return false;
        }

        // BETA MODE : acces complet pour tous
        if (defined('BETA_MODE') && BETA_MODE === true) {
            return false;
        }

        $adminId = $_SESSION['admin_id'] ?? null;
        if (!$adminId) {
            return true;
        }

        // SUPER_ADMIN : jamais en lecture seule
        require_once __DIR__ . '/../Models/Admin.php';
        $adminModel = new Admin($this->pdo);
        $admin = $adminModel->findById($adminId);
        if ($admin && $admin->role === 'SUPER_ADMIN') {
            return false;
        }

        // Mode démo : jamais en lecture seule
        if ($this->isDemoMode()) {
            return false;
        }

        // Vérifier l'abonnement basique actif
        try {
            $stmt = $this->pdo->prepare("
                SELECT id FROM client_subscriptions 
                WHERE admin_id = ? AND status IN ('active', 'cancelled') 
                AND (expires_at IS NULL OR expires_at > NOW())
            ");
            $stmt->execute([$adminId]);
            return !$stmt->fetch();
        } catch (Exception $e) {
            // Table n'existe pas encore → ne pas bloquer
            return false;
        }
    }

    /**
     * Bloque les modifications si l'admin n'a pas d'abonnement actif
     * Redirige vers les paramètres avec un message explicatif
     */
    protected function requireActiveSubscription()
    {
        if ($this->isReadOnly()) {
            $this->addErrorMessage("Votre abonnement est inactif. Réactivez-le depuis vos paramètres pour modifier votre site.");
            header('Location: ?page=settings&section=premium');
            exit;
        }
    }

    /**
     * Fonction utilitaire pour charger et afficher une vue
     * 
     * @param string $view Chemin du fichier PHP à afficher (ex: "admin/login")
     *                   Correspond au nom du fichier sans extension .php
     * @param array $data Variables à passer à la vue (tableau associatif)
     *                   Ex: ['error' => 'Message d'erreur', 'user' => $userObject]
     * 
     * Explication détaillée:
     * 1. extract($data): Transforme chaque clé du tableau en variable
     *    - Exemple: $data['error'] devient la variable $error dans la vue
     *    - Permet d'accéder directement aux données dans le template
     * 2. include __DIR__ . "/../Views/$view.php":
     *    - __DIR__: Répertoire actuel (celui de BaseController.php)
     *    - "/../Views/$view.php": Remonte d'un niveau, entre dans Views, puis inclut le fichier
     *    - Exemple: Si $view = "admin/login", inclut "../Views/admin/login.php"
     * 3. La vue incluse a accès à toutes les variables extraites
     */
    protected function render($view, $data = [])
    {
        // Protection contre le path traversal
        $view = str_replace(['..', "\0"], '', $view);
        $viewPath = __DIR__ . "/../Views/$view.php";
        $realViewPath = realpath($viewPath);
        $viewsDir = realpath(__DIR__ . '/../Views');

        if ($realViewPath === false || strpos($realViewPath, $viewsDir) !== 0) {
            http_response_code(404);
            include __DIR__ . "/../Views/errors/404.php";
            return;
        }

        // Content-Security-Policy (uniquement pour les pages rendues avec HTML)
        if (!headers_sent()) {
            header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com https://code.jquery.com https://cdn.jsdelivr.net https://js.stripe.com; style-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com https://cdn.jsdelivr.net https://fonts.googleapis.com; font-src 'self' https://cdnjs.cloudflare.com https://fonts.gstatic.com; img-src 'self' data: blob:; frame-src https://js.stripe.com https://maps.google.com https://www.google.com; connect-src 'self' https://cdn.jsdelivr.net");
        }

        // Ajouter le compteur de notifications pour toutes les pages admin
        if (strpos($view, 'admin/') === 0 && !isset($data['pending_reservations_count'])) {
            $data['pending_reservations_count'] = $this->getPendingReservationsCount();
        }

        // Ajouter les options de masquage des boutons pour toutes les pages admin
        if (strpos($view, 'admin/') === 0 || strpos($view, 'partials/header') === 0) {
            $buttonOptions = $this->getButtonVisibilityOptions();
            if (!isset($data['hide_dark_mode'])) {
                $data['hide_dark_mode'] = $buttonOptions['hide_dark_mode'];
            }
            if (!isset($data['hide_tour_button'])) {
                $data['hide_tour_button'] = $buttonOptions['hide_tour_button'];
            }
        }

        // Transforme les clés du tableau en variables (EXTR_SKIP évite d'écraser des variables existantes)
        extract($data, EXTR_SKIP);
        
        // Inclusion du fichier de vue
        include $realViewPath;
    }

    /**
     * Génère ou récupère un token CSRF (Cross-Site Request Forgery)
     * Sécurité contre les attaques par falsification de requête intersites
     * 
     * @return string Token CSRF unique et sécurisé
     * 
     * Explication détaillée pas à pas:
     * 1. Vérifie si la session PHP est active (session_status() !== PHP_SESSION_ACTIVE)
     *    - Si inactive: session_start() démarre une nouvelle session ou reprend une session existante
     * 2. Vérifie si un token CSRF existe déjà dans la session
     *    - if (empty($_SESSION['csrf_token'])): Vérifie si la clé est vide ou non définie
     * 3. Si aucun token n'existe, en crée un nouveau:
     *    - random_bytes(32): Génère 32 octets (256 bits) de données aléatoires cryptographiquement sécurisées
     *    - bin2hex(): Convertit les données binaires en représentation hexadécimale (0-9, a-f)
     *    - Résultat: Une chaîne de 64 caractères hexadécimaux sécurisée
     * 4. Retourne le token (nouveau ou existant) pour l'utiliser dans les formulaires
     * 
     * Utilisation typique: <input type="hidden" name="csrf_token" value="<?= $this->getCsrfToken() ?>">
     */
    protected function getCsrfToken(): string
    {
        // Étape 1: Vérification et démarrage de la session
        // session_status() peut retourner: PHP_SESSION_DISABLED, PHP_SESSION_NONE, PHP_SESSION_ACTIVE
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();  // Démarre la session si elle n'est pas déjà active
        }

        // Étape 2: Vérification si un token CSRF existe déjà
        // empty() vérifie si la variable est vide: null, "", 0, false, array(), non définie
        if (empty($_SESSION['csrf_token'])) {
            // Étape 3: Création d'un nouveau token sécurisé
            // random_bytes(32): 32 octets aléatoires (cryptographiquement sécurisés)
            // bin2hex(): Convertit en hexadécimal (64 caractères: 0-9, a-f)
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }

    /**
     * Vérifie que le token CSRF soumis correspond à celui stocké en session
     *
     * @param string|null $token Token CSRF à vérifier
     * @return bool true si le token est valide
     */
    protected function verifyCsrfToken(?string $token): bool
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $valid = !empty($token)
            && !empty($_SESSION['csrf_token'])
            && hash_equals($_SESSION['csrf_token'], $token);

        // Rotation : régénérer le token après validation réussie
        // Sauf pour les requêtes AJAX (le token dans le DOM deviendrait invalide)
        if ($valid && !$this->isAjaxRequest()) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $valid;
    }

    /**
     * Version publique de verifyCsrfToken pour usage dans index.php (routes inline)
     *
     * @param string|null $token Token CSRF à vérifier
     * @return bool true si le token est valide
     */
    public function verifyCsrfTokenPublic(?string $token): bool
    {
        return $this->verifyCsrfToken($token);
    }

    /**
     * Détecte si la requête courante est une requête AJAX (XMLHttpRequest)
     *
     * @return bool true si requête AJAX
     */
    protected function isAjaxRequest(): bool
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    /**
     * Définit le délai global de défilement pour tous les messages
     *
     * @param int $delay Délai en millisecondes
     */
    public function setScrollDelay($delay)
    {
        $this->scrollDelay = max(500, (int)$delay);
    }

    /**
     * Ajoute un message de succès flash à la session avec gestion du défilement
     * Les messages flash sont automatiquement supprimés après leur première lecture
     * 
     * @param string $message Texte du message de succès à afficher
     * @param string $anchor Identifiant HTML vers lequel défiler après affichage du message (optionnel)
     *                      Ex: 'section-id' pour défiler vers <div id="section-id">
     * 
     * Explication détaillée pas à pas:
     * 1. Stocke le message dans $_SESSION['success_message'] pour persistance entre redirections
     * 2. Stocke le délai de défilement dans $_SESSION['scroll_delay'] pour contrôle JavaScript
     * 3. Si une ancre est fournie ($anchor non vide), la stocke dans $_SESSION['anchor']
     *    pour permettre le défilement automatique vers l'élément spécifié
     * 4. Les trois variables de session sont automatiquement nettoyées après lecture
     *    dans la méthode render() ou par le contrôleur appelant
     * 
     * Utilisation typique: $this->addSuccessMessage("Opération réussie", 'section-a-corriger');
     */
    protected function addSuccessMessage($message, $anchor = '')
    {
        // Étape 1: Stockage du message de succès dans la session
        // La clé 'success_message' est standardisée pour une récupération cohérente dans les vues
        $_SESSION['success_message'] = $message;
        
        // Étape 2: Stockage du délai de défilement configuré
        // Utilise la propriété $this->scrollDelay définie globalement ou par setScrollDelay()
        $_SESSION['scroll_delay'] = $this->scrollDelay;
        
        // Étape 3: Stockage optionnel de l'ancre pour le défilement ciblé
        // Seulement si $anchor n'est pas une chaîne vide
        if (!empty($anchor)) {
            $_SESSION['anchor'] = $anchor;
        }
    }

    /**
     * Ajoute un message d'erreur flash à la session avec gestion du défilement
     * Les messages flash sont automatiquement supprimés après leur première lecture
     * 
     * @param string $message Texte du message d'erreur à afficher
     * @param string $anchor Identifiant HTML vers lequel défiler après affichage du message (optionnel)
     *                      Ex: 'form-errors' pour défiler vers le formulaire contenant des erreurs
     * 
     * Explication détaillée pas à pas:
     * 1. Fonctionne sur le même principe que addSuccessMessage() mais pour les erreurs
     * 2. Stocke le message dans $_SESSION['error_message'] (clé différente pour éviter les conflits)
     * 3. Utilise le même délai de défilement que les messages de succès ($this->scrollDelay)
     * 4. Permet de cibler le défilement vers la section pertinente (ex: formulaire avec erreurs)
     * 5. Les variables de session sont nettoyées après lecture pour éviter la persistance
     * 
     * Différence avec addSuccessMessage():
     * - Utilise 'error_message' au lieu de 'success_message' pour la clé de session
     * - Généralement associé à des ancres différentes (zones de formulaire vs zones de confirmation)
     * 
     * Utilisation typique: $this->addErrorMessage("Veuillez corriger les erreurs", 'formulaire-inscription');
     */
    protected function addErrorMessage($message, $anchor = '')
    {
        // Étape 1: Stockage du message d'erreur dans la session
        // La clé 'error_message' est distincte de 'success_message' pour un traitement différent dans les vues
        $_SESSION['error_message'] = $message;
        
        // Étape 2: Stockage du délai de défilement (identique aux messages de succès)
        // Assure une expérience utilisateur cohérente quel que soit le type de message
        $_SESSION['scroll_delay'] = $this->scrollDelay;
        
        // Étape 3: Stockage optionnel de l'ancre pour le défilement ciblé
        // Particulièrement utile pour les erreurs de formulaire (défilement vers le champ problématique)
        if (!empty($anchor)) {
            $_SESSION['anchor'] = $anchor;
        }
    }

    /**
     * Récupère et nettoie les messages flash de la session
     * Méthode utilitaire pour centraliser la gestion des messages dans les contrôleurs
     * 
     * @return array Tableau contenant les messages et données associées:
     *              [
     *                  'success_message' => string|null,  // Message de succès ou null
     *                  'error_message' => string|null,    // Message d'erreur ou null
     *                  'scroll_delay' => int,             // Délai de défilement en ms
     *                  'anchor' => string|null           // Ancre pour le défilement ou null
     *              ]
     * 
     * Explication détaillée:
     * 1. Récupère toutes les variables de session liées aux messages
     * 2. Utilise l'opérateur de coalescence null (??) pour fournir des valeurs par défaut
     * 3. Nettoie immédiatement les variables de session après lecture (principe flash)
     * 4. Retourne un tableau structuré pour faciliter l'extraction dans les vues
     * 
     * Avantages de cette approche:
     * - Centralise la logique de nettoyage des messages
     * - Garantit que les messages ne persistent pas après rafraîchissement
     * - Fournit une interface cohérente pour tous les contrôleurs
     * 
     * Utilisation typique dans un contrôleur:
     * $messages = $this->getFlashMessages();
     * extract($messages); // Crée $success_message, $error_message, etc.
     */
    protected function getFlashMessages()
    {
        // Récupération des valeurs avec valeurs par défaut
        $messages = [
            'success_message' => $_SESSION['success_message'] ?? null,
            'error_message' => $_SESSION['error_message'] ?? null,
            'scroll_delay' => $_SESSION['scroll_delay'] ?? $this->scrollDelay,
            'anchor' => $_SESSION['anchor'] ?? null
        ];

        // Nettoyage immédiat des variables de session (principe flash)
        // Un message ne doit être affiché qu'une seule fois
        unset(
            $_SESSION['success_message'],
            $_SESSION['error_message'],
            $_SESSION['scroll_delay'],
            $_SESSION['anchor']
        );

        return $messages;
    }
}