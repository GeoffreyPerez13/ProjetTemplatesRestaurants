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
            header('Location: login.php'); // En-tête HTTP 302 (redirection temporaire)
            exit; // Arrêt immédiat pour éviter que le reste du code s'exécute
        }
        // Si connecté, le code continue après cette méthode
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
        // Transforme les clés du tableau en variables
        // Ex: $data['title'] devient $title, $data['user'] devient $user, etc.
        extract($data);
        
        // Inclusion du fichier de vue
        // __DIR__: répertoire où se trouve ce fichier (BaseController.php)
        // "/../Views/": remonte d'un répertoire et entre dans Views/
        // "$view.php": ajoute l'extension .php au nom de la vue
        include __DIR__ . "/../Views/$view.php";
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

        // Étape 4: Retour du token (stocké dans la session pour cette visite)
        return $_SESSION['csrf_token'];
    }

    /**
     * Vérifie que le token CSRF soumis correspond à celui stocké en session
     * Protège contre les attaques CSRF où un site malveillant tente d'exécuter des actions
     * au nom d'un utilisateur authentifié
     * 
     * @param string|null $token Token CSRF à vérifier (peut être null)
     * @return bool true si le token est valide, false sinon
     * 
     * Explication détaillée pas à pas:
     * 1. Vérifie que la session est active (comme dans getCsrfToken)
     * 2. Vérifie trois conditions avec ET logique (&&):
     *    a. !empty($token): Le token fourni n'est pas vide
     *    b. !empty($_SESSION['csrf_token']): Un token existe dans la session
     *    c. hash_equals($_SESSION['csrf_token'], $token): Comparaison sécurisée des tokens
     * 3. hash_equals() est une comparaison "temps constant":
     *    - Prend toujours le même temps quelle que soit l'entrée
     *    - Évite les attaques par analyse de temps (timing attacks)
     *    - Compare les chaînes caractère par caractère sans court-circuit
     * 
     * Retourne false si une des conditions échoue
     */
    protected function verifyCsrfToken(?string $token): bool
    {
        // Étape 1: Vérification et démarrage de la session (comme dans getCsrfToken)
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        // Étape 2: Validation en trois parties (toutes doivent être vraies)
        // Condition 1: $token n'est pas vide (vérifie que quelque chose a été soumis)
        // Condition 2: $_SESSION['csrf_token'] existe (doit avoir été créé par getCsrfToken)
        // Condition 3: Comparaison sécurisée avec hash_equals() (évite les attaques temporelles)
        return !empty($token)
            && !empty($_SESSION['csrf_token'])
            && hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Définit le délai global de défilement pour tous les messages
     * Permet de personnaliser le temps d'affichage du message avant le scroll automatique
     * 
     * @param int $delay Délai en millisecondes (ex: 3500 pour 3,5 secondes)
     * 
     * Explication détaillée:
     * 1. Modifie la propriété $this->scrollDelay qui est utilisée par addSuccessMessage() et addErrorMessage()
     * 2. La valeur est exprimée en millisecondes (1000 ms = 1 seconde)
     * 3. Cette méthode peut être appelée dans le constructeur des contrôleurs enfants
     *    pour personnaliser le délai selon les besoins spécifiques de chaque page
     * 4. Utilisation typique: $this->setScrollDelay(3500) dans le constructeur du contrôleur
     * 
     * Note: Cette méthode utilise la fonction max() pour garantir que le délai
     *       n'est jamais inférieur à 500ms (demi-seconde) pour éviter des scrolls trop rapides
     */
    public function setScrollDelay($delay)
    {
        // Utilisation de max() pour garantir un délai minimum de 500ms
        // Empêche les valeurs trop basses qui rendraient l'UX désagréable
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