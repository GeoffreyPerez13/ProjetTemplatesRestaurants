<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../Helpers/Validator.php';
require_once __DIR__ . '/../Models/OptionModel.php';
require_once __DIR__ . '/../Models/Admin.php';
require_once __DIR__ . '/../Models/PremiumFeature.php';

/**
 * Contrôleur des paramètres de l'administrateur
 * Gère le profil, le mot de passe, les options (site online, rappels, notifications),
 * la suppression de compte et la sélection de template
 */
class SettingsController extends BaseController
{
    private function isAjax()
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    private function jsonResponse($success, $message, $extra = [])
    {
        header('Content-Type: application/json');
        echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra));
        exit;
    }

    /**
     * Affiche la page des paramètres avec la section demandée (?section=profile|password|options|account)
     */
    public function show()
    {
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        header('Expires: 0');

        $this->requireLogin();

        // Récupérer les informations de l'utilisateur
        $user = $this->getCurrentUser();

        // Récupérer les options de l'utilisateur
        try {
            $stmt = $this->pdo->prepare("
            SELECT option_name, option_value 
            FROM admin_options 
            WHERE admin_id = ?
        ");
            $stmt->execute([$_SESSION['admin_id']]);
            $userOptions = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        } catch (Exception $e) {
            // Si erreur, utiliser un tableau vide
            error_log("Erreur récupération options: " . $e->getMessage());
            $userOptions = [];
        }

        // VALEURS PAR DÉFAUT :
        $defaultOptions = [
            'site_online' => '1',
            'mail_reminder' => '0',
            'hide_dark_mode' => '0',
            'hide_tour_button' => '0',
            'email_notifications' => '1'
        ];

        $options = array_merge($defaultOptions, $userOptions);
        
        // Passer les options pour masquer les boutons au header
        $hide_dark_mode = ($options['hide_dark_mode'] ?? '0') === '0' ? false : true;
        $hide_tour_button = ($options['hide_tour_button'] ?? '0') === '0' ? false : true;

        // Récupérer les dates de fermeture exceptionnelles
        $closureDates = [];
        try {
            $stmt = $this->pdo->prepare("
                SELECT option_value 
                FROM admin_options 
                WHERE admin_id = ? AND option_name = 'closure_dates'
            ");
            $stmt->execute([$_SESSION['admin_id']]);
            $closureDatesValue = $stmt->fetchColumn();
            if ($closureDatesValue) {
                $closureDates = json_decode($closureDatesValue, true) ?: [];
            }
        } catch (Exception $e) {
            error_log("Erreur récupération dates fermeture: " . $e->getMessage());
            $closureDates = [];
        }

        // Section par défaut
        $section = $_GET['section'] ?? 'profile';

        // Récupérer les messages flash en utilisant la méthode du BaseController
        $messages = $this->getFlashMessages();
        $success_message = $messages['success_message'];
        $error_message = $messages['error_message'];

        // Vérifier les options premium de l'utilisateur
        $premiumFeatureStatuses = [];
        $restaurantName = $user['restaurant_name'] ?? '';
        $slug = null;
        try {
            $pf = new PremiumFeature($this->pdo);
            $adminModel = new Admin($this->pdo);
            $admin = $adminModel->findById($_SESSION['admin_id']);
            $isSuperAdmin = $admin && ($admin->role === 'SUPER_ADMIN');

            $featureKeys = ['google_reviews', 'advanced_analytics', 'online_booking', 'delivery_integration'];
            foreach ($featureKeys as $key) {
                $premiumFeatureStatuses[$key] = $isSuperAdmin || $pf->isEnabled($_SESSION['admin_id'], $key);
            }

            if ($admin && $admin->restaurant_id) {
                $stmtSlug = $this->pdo->prepare("SELECT slug FROM restaurants WHERE id = ?");
                $stmtSlug->execute([$admin->restaurant_id]);
                $slug = $stmtSlug->fetchColumn() ?: null;
            }
        } catch (Exception $e) {
            foreach (['google_reviews', 'advanced_analytics', 'online_booking', 'delivery_integration'] as $key) {
                $premiumFeatureStatuses[$key] = false;
            }
        }

        // Mapping section → feature key pour protéger l'accès
        $sectionToFeature = [
            'stats'            => 'advanced_analytics',
            'google-reviews'   => 'google_reviews',
            'online-booking'   => 'online_booking',
            'delivery'         => 'delivery_integration',
        ];
        if (isset($sectionToFeature[$section]) && empty($premiumFeatureStatuses[$sectionToFeature[$section]])) {
            $this->addErrorMessage('Cette fonctionnalité nécessite un abonnement premium.');
            header('Location: ?page=settings&section=premium');
            exit;
        }

        // Récupérer les données Google Reviews pour la section google-reviews
        $googleReviewsData = null;
        if ($section === 'google-reviews' && !empty($premiumFeatureStatuses['google_reviews'])) {
            try {
                $optionModel = new OptionModel($this->pdo);
                $googlePlaceId = $optionModel->get($_SESSION['admin_id'], 'google_place_id');
                $googleApiKey = $optionModel->get($_SESSION['admin_id'], 'google_api_key');
                $googleReviewsEnabled = $optionModel->get($_SESSION['admin_id'], 'google_reviews_enabled') === '1';

                if ($googlePlaceId && $googleApiKey) {
                    require_once __DIR__ . '/../Models/GoogleReviews.php';
                    $googleReviews = new GoogleReviews($this->pdo, $googleApiKey);
                    $googleReviewsData = $googleReviews->getReviews($googlePlaceId, 5);
                }
            } catch (Exception $e) {
                error_log('Erreur récupération avis Google: ' . $e->getMessage());
            }
        }

        // Récupérer les dates d'abonnement pour la section subscriptions
        $subscriptionData = [];
        if ($section === 'subscriptions') {
            try {
                require_once __DIR__ . '/../Models/BillingCycle.php';
                $billingCycle = new BillingCycle($this->pdo);
                
                // Utiliser BillingCycle pour récupérer toutes les informations
                $subscriptionData = $billingCycle->getBillingInfo($_SESSION['admin_id']);
                
            } catch (Exception $e) {
                error_log('Erreur récupération dates abonnements: ' . $e->getMessage());
                $subscriptionData = [];
            }
        }

        $this->render('admin/settings', [
            'user' => $user,
            'options' => $options,
            'closure_dates' => $closureDates,
            'current_section' => $section,
            'title' => 'Paramètres',
            'csrf_token' => $this->getCsrfToken(),
            'success_message' => $success_message,
            'error_message' => $error_message,
            'pdo' => $this->pdo,
            'premium_statuses' => $premiumFeatureStatuses,
            'has_advanced_stats' => $premiumFeatureStatuses['advanced_analytics'] ?? false,
            'restaurant_name_display' => $restaurantName,
            'slug' => $slug,
            'subscription_data' => $subscriptionData,
            'google_reviews_data' => $googleReviewsData,
        ]);
    }

    /**
     * Met à jour le profil admin (username, email, nom du restaurant)
     * Vérifie l'unicité de l'email et du username avant mise à jour
     */
    public function updateProfile()
    {
        $this->requireLogin();
        $this->blockIfDemo("La modification du profil n'est pas disponible en mode démonstration.");
        $ajax = $this->isAjax();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validation CSRF
            if (!$this->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                if ($ajax) $this->jsonResponse(false, 'Token de sécurité invalide.');
                $this->addErrorMessage('Token de sécurité invalide', 'profile-form');
                header('Location: ?page=settings&section=profile');
                exit;
            }

            // Récupérer les données
            $username = trim($_POST['username'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $restaurant_name = trim($_POST['restaurant_name'] ?? '');

            // Validation
            $errors = [];

            if (empty($username)) {
                $errors[] = "Le nom d'utilisateur est requis";
            } elseif (strlen($username) < 3) {
                $errors[] = "Le nom d'utilisateur doit contenir au moins 3 caractères";
            }

            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Email invalide";
            }

            if (empty($restaurant_name)) {
                $errors[] = "Le nom du restaurant est requis";
            }

            // Vérifier si l'email existe déjà (sauf pour l'utilisateur actuel)
            if (empty($errors)) {
                try {
                    $stmt = $this->pdo->prepare("SELECT id FROM admins WHERE email = ? AND id != ?");
                    $stmt->execute([$email, $_SESSION['admin_id']]);
                    if ($stmt->fetch()) {
                        $errors[] = "Cet email est déjà utilisé par un autre compte";
                    }
                } catch (Exception $e) {
                    error_log("Erreur vérification email: " . $e->getMessage());
                    $errors[] = "Erreur lors de la vérification de l'email";
                }
            }

            // Vérifier si le nom d'utilisateur existe déjà
            if (empty($errors)) {
                try {
                    $stmt = $this->pdo->prepare("SELECT id FROM admins WHERE username = ? AND id != ?");
                    $stmt->execute([$username, $_SESSION['admin_id']]);
                    if ($stmt->fetch()) {
                        $errors[] = "Ce nom d'utilisateur est déjà utilisé";
                    }
                } catch (Exception $e) {
                    error_log("Erreur vérification username: " . $e->getMessage());
                    $errors[] = "Erreur lors de la vérification du nom d'utilisateur";
                }
            }

            if (empty($errors)) {
                try {
                    $this->pdo->beginTransaction();

                    // Mettre à jour la base de données
                    $stmt = $this->pdo->prepare("
                    UPDATE admins 
                    SET username = ?, email = ?, restaurant_name = ? 
                    WHERE id = ?
                ");

                    $success = $stmt->execute([$username, $email, $restaurant_name, $_SESSION['admin_id']]);

                    if ($success) {
                        // Mettre à jour la session
                        $_SESSION['admin_username'] = $username;
                        $_SESSION['admin_email'] = $email;
                        $_SESSION['restaurant_name'] = $restaurant_name;

                        $this->pdo->commit();

                        if ($ajax) $this->jsonResponse(true, 'Profil mis à jour avec succès.');
                        $this->addSuccessMessage('Profil mis à jour avec succès', 'profile-form');
                    } else {
                        $this->pdo->rollBack();
                        if ($ajax) $this->jsonResponse(false, 'Erreur lors de la mise à jour de la base de données.');
                        $this->addErrorMessage('Erreur lors de la mise à jour de la base de données', 'profile-form');
                    }
                } catch (Exception $e) {
                    $this->pdo->rollBack();
                    error_log("Erreur mise à jour profil: " . $e->getMessage());
                    if ($ajax) $this->jsonResponse(false, 'Une erreur est survenue lors de la mise à jour du profil.');
                    $this->addErrorMessage('Une erreur est survenue lors de la mise à jour du profil', 'profile-form');
                }
            } else {
                if ($ajax) $this->jsonResponse(false, implode(' ', $errors));
                foreach ($errors as $error) {
                    $this->addErrorMessage($error, 'profile-form');
                }
            }

            header('Location: ?page=settings&section=profile');
            exit;
        }
    }

    /**
     * Change le mot de passe de l'admin
     * Vérifie l'ancien mot de passe et valide le nouveau via Validator
     */
    public function changePassword()
    {
        $this->requireLogin();
        $this->blockIfDemo("Le changement de mot de passe n'est pas disponible en mode démonstration.");
        $ajax = $this->isAjax();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validation CSRF
            if (!$this->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                if ($ajax) $this->jsonResponse(false, 'Token de sécurité invalide.');
                $this->addErrorMessage('Token de sécurité invalide', 'password-form');
                header('Location: ?page=settings&section=password');
                exit;
            }

            // Récupérer les données
            $current_password = $_POST['current_password'] ?? '';
            $new_password = $_POST['new_password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';

            // Validation
            $errors = [];

            if (empty($current_password)) {
                $errors[] = "Le mot de passe actuel est requis";
            }

            if (empty($new_password)) {
                $errors[] = "Le nouveau mot de passe est requis";
            } else {
                // Validation via Validator centralisé
                $passwordErrors = Validator::validatePassword($new_password, $confirm_password);
                $errors = array_merge($errors, $passwordErrors);
            }

            // Vérifier que le nouveau mot de passe est différent de l'ancien
            if (empty($errors) && $current_password === $new_password) {
                $errors[] = "Le nouveau mot de passe doit être différent de l'actuel";
            }

            // Vérifier le mot de passe actuel
            if (empty($errors)) {
                try {
                    $stmt = $this->pdo->prepare("SELECT password FROM admins WHERE id = ?");
                    $stmt->execute([$_SESSION['admin_id']]);
                    $admin = $stmt->fetch();

                    if (!$admin || !password_verify($current_password, $admin['password'])) {
                        $errors[] = "Le mot de passe actuel est incorrect";
                    }
                } catch (Exception $e) {
                    error_log("Erreur vérification mot de passe: " . $e->getMessage());
                    $errors[] = "Erreur lors de la vérification du mot de passe actuel";
                }
            }

            if (empty($errors)) {
                try {
                    $this->pdo->beginTransaction();

                    // Hasher le nouveau mot de passe
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

                    // Mettre à jour la base de données
                    $stmt = $this->pdo->prepare("UPDATE admins SET password = ? WHERE id = ?");

                    $success = $stmt->execute([$hashed_password, $_SESSION['admin_id']]);

                    if ($success) {
                        $this->pdo->commit();
                        if ($ajax) $this->jsonResponse(true, 'Mot de passe modifié avec succès.');
                        $this->addSuccessMessage('Mot de passe modifié avec succès', 'password-form');
                    } else {
                        $this->pdo->rollBack();
                        if ($ajax) $this->jsonResponse(false, 'Erreur lors de la mise à jour du mot de passe.');
                        $this->addErrorMessage('Erreur lors de la mise à jour du mot de passe', 'password-form');
                    }
                } catch (Exception $e) {
                    $this->pdo->rollBack();
                    error_log("Erreur changement mot de passe: " . $e->getMessage());
                    if ($ajax) $this->jsonResponse(false, 'Une erreur est survenue lors du changement de mot de passe.');
                    $this->addErrorMessage('Une erreur est survenue lors du changement de mot de passe', 'password-form');
                }
            } else {
                if ($ajax) $this->jsonResponse(false, implode(' ', $errors));
                foreach ($errors as $error) {
                    $this->addErrorMessage($error, 'password-form');
                }
            }

            header('Location: ?page=settings&section=password');
            exit;
        }
    }

    /**
     * Récupère les informations complètes de l'admin connecté
     * Inclut la date de dernière modification de la carte (plats/catégories)
     *
     * @return array Données admin avec 'last_card_update'
     */
    private function getCurrentUser()
    {
        // Récupérer les informations de base de l'admin
        $stmt = $this->pdo->prepare("
        SELECT a.* 
        FROM admins a 
        WHERE a.id = ?
    ");
        $stmt->execute([$_SESSION['admin_id']]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        // Initialiser last_card_update avec la date de création
        $admin['last_card_update'] = $admin['created_at'] ?? null;

        // Si vous voulez quand même essayer de récupérer les dates des plats/catégories
        try {
            // Essayer avec created_at pour les plats
            $stmt = $this->pdo->prepare("
            SELECT MAX(created_at) as last_update 
            FROM plats 
            WHERE admin_id = ?
        ");
            $stmt->execute([$_SESSION['admin_id']]);
            $platUpdate = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($platUpdate && $platUpdate['last_update']) {
                $admin['last_card_update'] = max($admin['last_card_update'], $platUpdate['last_update']);
            }
        } catch (PDOException $e) {
            // La colonne created_at n'existe pas probablement, on ignore
        }

        try {
            // Essayer avec created_at pour les catégories
            $stmt = $this->pdo->prepare("
            SELECT MAX(created_at) as last_update 
            FROM categories 
            WHERE admin_id = ?
        ");
            $stmt->execute([$_SESSION['admin_id']]);
            $categoryUpdate = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($categoryUpdate && $categoryUpdate['last_update']) {
                $admin['last_card_update'] = max($admin['last_card_update'], $categoryUpdate['last_update']);
            }
        } catch (PDOException $e) {
            // La colonne created_at n'existe pas probablement, on ignore
        }

        return $admin;
    }

    /**
     * Récupère toutes les options d'un admin depuis admin_options
     *
     * @param int $admin_id ID de l'admin
     * @return array Tableau clé/valeur des options
     */
    private function getUserOptions($admin_id)
    {
        try {
            // Vérifier si la table existe
            $tableExists = $this->pdo->query("SHOW TABLES LIKE 'admin_options'")->fetch();

            if (!$tableExists) {
                error_log("Table admin_options n'existe pas");
                return [];
            }

            $stmt = $this->pdo->prepare("
            SELECT option_name, option_value 
            FROM admin_options 
            WHERE admin_id = ?
        ");
            $stmt->execute([$admin_id]);
            return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        } catch (Exception $e) {
            error_log("Erreur récupération options: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Endpoint JSON : retourne les options de l'admin connecté
     * Utilisé par les appels AJAX depuis la page paramètres
     */
    public function getOptions()
    {
        $this->requireLogin();

        try {
            $stmt = $this->pdo->prepare("
            SELECT option_name, option_value 
            FROM admin_options 
            WHERE admin_id = ?
        ");
            $stmt->execute([$_SESSION['admin_id']]);
            $options = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

            // VALEURS PAR DÉFAUT :
            $defaultOptions = [
                'site_online' => '1',
                'mail_reminder' => '0',
                'email_notifications' => '0'
            ];

            $mergedOptions = array_merge($defaultOptions, $options);

            header('Content-Type: application/json');
            echo json_encode($mergedOptions);
        } catch (Exception $e) {
            // En cas d'erreur, retourner les valeurs par défaut corrigées
            error_log("Erreur getOptions: " . $e->getMessage());

            $defaultOptions = [
                'site_online' => '1',
                'mail_reminder' => '0',
                'email_notifications' => '0'
            ];

            header('Content-Type: application/json');
            echo json_encode($defaultOptions);
        }
    }

    /**
     * Sauvegarde un lot d'options (site_online, mail_reminder, email_notifications)
     * Supporte les requêtes AJAX et classiques (POST)
     */
    public function saveOptionsBatch()
    {
        $this->requireLogin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validation CSRF
            if (!$this->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                // Pour AJAX
                if ($this->isAjaxRequest()) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Token de sécurité invalide']);
                    exit;
                } else {
                    $this->addErrorMessage('Token de sécurité invalide', 'options-form');
                    header('Location: ?page=settings&section=options');
                    exit;
                }
            }

            $options = json_decode($_POST['options'] ?? '{}', true);

            if (!is_array($options) || empty($options)) {
                if ($this->isAjaxRequest()) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Aucune option à sauvegarder']);
                    exit;
                } else {
                    $this->addErrorMessage('Aucune option à sauvegarder', 'options-form');
                    header('Location: ?page=settings&section=options');
                    exit;
                }
            }

            try {
                $success = true;
                $messages = [];

                foreach ($options as $option => $value) {
                    if (in_array($option, ['site_online', 'mail_reminder', 'hide_dark_mode', 'hide_tour_button', 'email_notifications'])) {
                        $stmt = $this->pdo->prepare("
                        INSERT INTO admin_options (admin_id, option_name, option_value, created_at, updated_at) 
                        VALUES (?, ?, ?, NOW(), NOW())
                        ON DUPLICATE KEY UPDATE option_value = ?, updated_at = NOW()
                    ");

                        $result = $stmt->execute([
                            $_SESSION['admin_id'],
                            $option,
                            $value,
                            $value
                        ]);

                        if (!$result) {
                            $success = false;
                            $messages[] = "Erreur pour l'option: $option";
                        }
                    }
                }

                if ($success) {
                    if ($this->isAjaxRequest()) {
                        echo json_encode([
                            'success' => true,
                            'message' => 'Options mises à jour avec succès'
                        ]);
                    } else {
                        $this->addSuccessMessage('Options mises à jour avec succès', 'options-form');
                        header('Location: ?page=settings&section=options');
                        exit;
                    }
                } else {
                    if ($this->isAjaxRequest()) {
                        echo json_encode([
                            'success' => false,
                            'message' => 'Erreurs lors de la sauvegarde: ' . implode(', ', $messages)
                        ]);
                    } else {
                        $this->addErrorMessage('Erreurs lors de la sauvegarde: ' . implode(', ', $messages), 'options-form');
                        header('Location: ?page=settings&section=options');
                        exit;
                    }
                }
            } catch (Exception $e) {
                error_log("Erreur saveOptionsBatch: " . $e->getMessage());

                if ($this->isAjaxRequest()) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Erreur serveur: ' . $e->getMessage()
                    ]);
                } else {
                    $this->addErrorMessage('Erreur serveur: ' . $e->getMessage(), 'options-form');
                    header('Location: ?page=settings&section=options');
                    exit;
                }
            }
        }
    }

    /**
     * Supprime le compte admin et toutes ses données associées
     * Requiert le mot de passe et la saisie de "SUPPRIMER" comme confirmation
     */
    public function deleteAccount()
    {
        $this->requireLogin();
        $this->blockIfDemo("La suppression de compte n'est pas disponible en mode démonstration.");

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validation CSRF
            if (!$this->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                $this->addErrorMessage('Token de sécurité invalide');
                header('Location: ?page=settings&section=account');
                exit;
            }

            // Demander confirmation supplémentaire
            $confirmation = $_POST['confirmation'] ?? '';
            if ($confirmation !== 'SUPPRIMER') {
                $this->addErrorMessage('Veuillez taper "SUPPRIMER" pour confirmer la suppression');
                header('Location: ?page=settings&section=account');
                exit;
            }

            // Vérifier le mot de passe
            $password = $_POST['password'] ?? '';
            $stmt = $this->pdo->prepare("SELECT password FROM admins WHERE id = ?");
            $stmt->execute([$_SESSION['admin_id']]);
            $admin = $stmt->fetch();

            if (!$admin || !password_verify($password, $admin['password'])) {
                $this->addErrorMessage('Mot de passe incorrect');
                header('Location: ?page=settings&section=account');
                exit;
            }

            try {
                // Commencer une transaction
                $this->pdo->beginTransaction();

                // Supprimer les données associées (dans l'ordre logique)

                // 1. Supprimer les options
                $stmt = $this->pdo->prepare("DELETE FROM admin_options WHERE admin_id = ?");
                $stmt->execute([$_SESSION['admin_id']]);

                // 2. Supprimer les images de carte
                $stmt = $this->pdo->prepare("DELETE FROM card_images WHERE admin_id = ?");
                $stmt->execute([$_SESSION['admin_id']]);

                // 3. Supprimer les logos
                $stmt = $this->pdo->prepare("DELETE FROM logos WHERE admin_id = ?");
                $stmt->execute([$_SESSION['admin_id']]);

                // 4. Supprimer les informations de contact
                $stmt = $this->pdo->prepare("DELETE FROM contact WHERE admin_id = ?");
                $stmt->execute([$_SESSION['admin_id']]);

                // 5. Supprimer les plats (via les catégories)
                // D'abord, récupérer les catégories
                $stmt = $this->pdo->prepare("SELECT id FROM categories WHERE admin_id = ?");
                $stmt->execute([$_SESSION['admin_id']]);
                $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);

                if ($categories) {
                    $placeholders = str_repeat('?,', count($categories) - 1) . '?';
                    $stmt = $this->pdo->prepare("DELETE FROM plats WHERE category_id IN ($placeholders)");
                    $stmt->execute($categories);
                }

                // 6. Supprimer les catégories
                $stmt = $this->pdo->prepare("DELETE FROM categories WHERE admin_id = ?");
                $stmt->execute([$_SESSION['admin_id']]);

                // 7. Supprimer l'admin
                $stmt = $this->pdo->prepare("DELETE FROM admins WHERE id = ?");
                $stmt->execute([$_SESSION['admin_id']]);

                // Valider la transaction
                $this->pdo->commit();

                // Déconnecter l'utilisateur
                session_destroy();

                // Démarrer une nouvelle session pour le message flash
                session_start();
                $_SESSION['success_message'] = 'Votre compte a été supprimé avec succès.';
                header('Location: ?page=login');
                exit;
            } catch (Exception $e) {
                // Annuler la transaction en cas d'erreur
                $this->pdo->rollBack();
                $this->addErrorMessage('Une erreur est survenue lors de la suppression du compte : ' . $e->getMessage());
                header('Location: ?page=settings&section=account');
                exit;
            }
        }
    }

    /**
     * Affiche la page de sélection de template (palette + layout)
     */
    public function showTemplates()
    {
        $this->requireLogin();
        $adminId = $_SESSION['admin_id'];

        $optionModel = new OptionModel($this->pdo);

        // Rétrocompatibilité : lire site_palette, sinon site_template
        $currentPalette = $optionModel->get($adminId, 'site_palette') ?: ($optionModel->get($adminId, 'site_template') ?: 'classic');
        $currentLayout  = $optionModel->get($adminId, 'site_layout') ?: 'standard';

        // Récupérer le slug pour le lien de preview
        $slug = '';
        $stmt = $this->pdo->prepare("
            SELECT r.slug FROM restaurants r 
            JOIN admins a ON a.restaurant_id = r.id 
            WHERE a.id = ?
        ");
        $stmt->execute([$adminId]);
        $slug = $stmt->fetchColumn() ?: '';

        $messages = $this->getFlashMessages();

        $this->render('admin/edit-template', [
            'title' => 'Personnaliser le site vitrine',
            'currentPalette' => $currentPalette,
            'currentLayout' => $currentLayout,
            'slug' => $slug,
            'csrf_token' => $this->getCsrfToken(),
            'success_message' => $messages['success_message'],
            'error_message' => $messages['error_message'],
        ]);
    }

    /**
     * Sauvegarde le choix de palette de couleurs
     */
    public function savePalette()
    {
        $this->requireLogin();
        $this->requireActiveSubscription();
        $ajax = $this->isAjax();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            if ($ajax) $this->jsonResponse(false, 'Méthode non autorisée.');
            header('Location: ?page=edit-template');
            exit;
        }

        if (!$this->verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            if ($ajax) $this->jsonResponse(false, 'Token CSRF invalide.');
            $this->addErrorMessage('Token CSRF invalide.');
            header('Location: ?page=edit-template');
            exit;
        }

        $palette = $_POST['palette'] ?? 'classic';
        $allowed = ['classic', 'modern', 'elegant', 'nature', 'rose', 'bistro', 'ocean'];

        if (!in_array($palette, $allowed)) {
            if ($ajax) $this->jsonResponse(false, 'Palette invalide.');
            $this->addErrorMessage('Palette invalide.');
            header('Location: ?page=edit-template');
            exit;
        }

        $optionModel = new OptionModel($this->pdo);
        $optionModel->set($_SESSION['admin_id'], 'site_palette', $palette);

        $names = ['classic' => 'Classique', 'modern' => 'Moderne', 'elegant' => 'Élégant', 'nature' => 'Nature', 'rose' => 'Rosé', 'bistro' => 'Bistro', 'ocean' => 'Océan'];
        if ($ajax) $this->jsonResponse(true, 'Palette "' . $names[$palette] . '" appliquée avec succès !', ['reload' => true]);
        $this->addSuccessMessage('Palette "' . $names[$palette] . '" appliquée avec succès !');
        $_SESSION['open_template_accordion'] = 'palette';
        header('Location: ?page=edit-template');
        exit;
    }

    /**
     * Sauvegarde le choix de layout/design
     */
    public function saveLayout()
    {
        $this->requireLogin();
        $this->requireActiveSubscription();
        $ajax = $this->isAjax();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            if ($ajax) $this->jsonResponse(false, 'Méthode non autorisée.');
            header('Location: ?page=edit-template');
            exit;
        }

        if (!$this->verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            if ($ajax) $this->jsonResponse(false, 'Token CSRF invalide.');
            $this->addErrorMessage('Token CSRF invalide.');
            header('Location: ?page=edit-template');
            exit;
        }

        $layout = $_POST['layout'] ?? 'standard';
        $allowed = ['standard', 'bistro', 'ocean'];

        if (!in_array($layout, $allowed)) {
            if ($ajax) $this->jsonResponse(false, 'Layout invalide.');
            $this->addErrorMessage('Layout invalide.');
            header('Location: ?page=edit-template');
            exit;
        }

        $optionModel = new OptionModel($this->pdo);
        $optionModel->set($_SESSION['admin_id'], 'site_layout', $layout);

        $names = ['standard' => 'Standard', 'bistro' => 'Bistro', 'ocean' => 'Océan'];
        if ($ajax) $this->jsonResponse(true, 'Design "' . $names[$layout] . '" appliqué avec succès !', ['reload' => true]);
        $this->addSuccessMessage('Design "' . $names[$layout] . '" appliqué avec succès !');
        $_SESSION['open_template_accordion'] = 'layout';
        header('Location: ?page=edit-template');
        exit;
    }

    /**
     * Met à jour les paramètres Google Reviews
     */
    public function updateGoogleReviews()
    {
        $this->requireLogin();
        $adminId = $_SESSION['admin_id'];
        $ajax = $this->isAjax();

        // Vérifier le CSRF
        if (!$this->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            if ($ajax) $this->jsonResponse(false, 'Token de sécurité invalide.');
            $this->addErrorMessage('Token de sécurité invalide.');
            header('Location: ?page=settings&section=premium');
            exit;
        }

        try {
            $optionModel = new OptionModel($this->pdo);

            // Sauvegarder les options Google Reviews
            $options = [
                'google_place_id' => $_POST['google_place_id'] ?? '',
                'google_api_key' => $_POST['google_api_key'] ?? '',
                'google_reviews_enabled' => ($_POST['google_reviews_enabled'] ?? '0') === '1' ? '1' : '0'
            ];

            foreach ($options as $key => $value) {
                $optionModel->set($adminId, $key, $value);
            }

            if ($ajax) $this->jsonResponse(true, 'Paramètres Google Reviews mis à jour avec succès.');
            $this->addSuccessMessage('Paramètres Google Reviews mis à jour avec succès.');
            header('Location: ?page=settings&section=google-reviews');
            exit;
        } catch (Exception $e) {
            if ($ajax) $this->jsonResponse(false, 'Erreur lors de la mise à jour : ' . $e->getMessage());
            $this->addErrorMessage('Erreur lors de la mise à jour : ' . $e->getMessage());
            header('Location: ?page=settings&section=google-reviews');
            exit;
        }
    }

    /**
     * Gère le basculement des fonctionnalités premium (réponse JSON pour AJAX)
     * 
     * Logique d'accès :
     * - SUPER_ADMIN : peut tout activer/désactiver (bypass abonnement)
     * - ADMIN : peut activer/désactiver uniquement si son abonnement le permet
     */
    public function togglePremium()
    {
        // Capturer tout output potentiel (erreurs PHP, warnings...)
        ob_start();

        // Vérifier le login manuellement (pas de redirect pour une requête AJAX)
        if (!isset($_SESSION['admin_logged']) || $_SESSION['admin_logged'] !== true) {
            ob_clean();
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Non connecté.']);
            exit;
        }

        $adminId = $_SESSION['admin_id'];

        // Récupérer le rôle depuis la base de données
        $adminModel = new Admin($this->pdo);
        $currentAdmin = $adminModel->findById($adminId);

        // Nettoyer tout output avant d'envoyer le JSON
        ob_clean();
        header('Content-Type: application/json');

        if (!$currentAdmin) {
            echo json_encode(['success' => false, 'message' => 'Administrateur non trouvé.']);
            exit;
        }

        // Vérifier le CSRF
        if (!$this->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            echo json_encode(['success' => false, 'message' => 'Token de sécurité invalide.']);
            exit;
        }

        $featureName = $_POST['feature'] ?? '';
        if (empty($featureName)) {
            echo json_encode(['success' => false, 'message' => 'Fonctionnalité non spécifiée.']);
            exit;
        }

        try {
            $premiumFeature = new PremiumFeature($this->pdo);

            // SUPER_ADMIN : accès libre (peut tout tester)
            $isSuperAdmin = ($currentAdmin->role === 'SUPER_ADMIN');

            if (!$isSuperAdmin) {
                // ADMIN : vérifier que la feature est incluse dans son abonnement
                if (!$premiumFeature->isFeatureInSubscription($adminId, $featureName)) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Cette fonctionnalité nécessite un abonnement Premium. Contactez-nous à premium@menumiam.fr pour souscrire.'
                    ]);
                    exit;
                }
            }

            $premiumFeature->toggle($adminId, $featureName);

            echo json_encode([
                'success' => true,
                'message' => 'Fonctionnalité mise à jour avec succès.'
            ]);
            exit;
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Erreur : ' . $e->getMessage()
            ]);
            exit;
        }
    }

    /**
     * Récupère les dates de fermeture exceptionnelles (AJAX)
     */
    public function getClosureDates()
    {
        $this->requireLogin();
        
        header('Content-Type: application/json');
        
        try {
            $stmt = $this->pdo->prepare("
                SELECT option_value 
                FROM admin_options 
                WHERE admin_id = ? AND option_name = 'closure_dates'
            ");
            $stmt->execute([$_SESSION['admin_id']]);
            $closureDatesValue = $stmt->fetchColumn();
            
            $dates = [];
            if ($closureDatesValue) {
                $dates = json_decode($closureDatesValue, true) ?: [];
            }
            
            echo json_encode([
                'success' => true,
                'dates' => $dates
            ]);
        } catch (Exception $e) {
            error_log("Erreur récupération dates fermeture: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Erreur lors de la récupération des dates'
            ]);
        }
        exit;
    }

    /**
     * Sauvegarde les dates de fermeture exceptionnelles (AJAX)
     */
    public function saveClosureDates()
    {
        $this->requireLogin();
        $ajax = $this->isAjax();
        
        // Validation CSRF
        if (!$this->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            if ($ajax) $this->jsonResponse(false, 'Token de sécurité invalide.');
            $this->addErrorMessage('Token de sécurité invalide', 'closure-dates-section');
            header('Location: ?page=settings&section=options');
            exit;
        }
        
        $dates = $_POST['dates'] ?? [];
        
        // Si c'est une chaîne JSON, la décoder
        if (is_string($dates)) {
            $dates = json_decode($dates, true) ?: [];
        }
        
        if (!is_array($dates)) {
            if ($ajax) $this->jsonResponse(false, 'Données invalides.');
            $this->addErrorMessage('Données invalides', 'closure-dates-section');
            header('Location: ?page=settings&section=options');
            exit;
        }
        
        // Valider et nettoyer les dates
        $validDates = [];
        foreach ($dates as $date) {
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                $validDates[] = $date;
            }
        }
        
        try {
            $datesJson = json_encode($validDates);
            
            $stmt = $this->pdo->prepare("
                INSERT INTO admin_options (admin_id, option_name, option_value, created_at, updated_at) 
                VALUES (?, 'closure_dates', ?, NOW(), NOW())
                ON DUPLICATE KEY UPDATE option_value = ?, updated_at = NOW()
            ");
            
            $result = $stmt->execute([$_SESSION['admin_id'], $datesJson, $datesJson]);
            
            if ($result) {
                if ($ajax) $this->jsonResponse(true, 'Dates de fermeture enregistrées avec succès.');
                $this->addSuccessMessage('Dates de fermeture enregistrées avec succès', 'closure-dates-section');
            } else {
                if ($ajax) $this->jsonResponse(false, "Erreur lors de l'enregistrement.");
                $this->addErrorMessage('Erreur lors de l\'enregistrement', 'closure-dates-section');
            }
        } catch (Exception $e) {
            error_log("Erreur sauvegarde dates fermeture: " . $e->getMessage());
            if ($ajax) $this->jsonResponse(false, 'Erreur lors de la sauvegarde des dates.');
            $this->addErrorMessage('Erreur lors de la sauvegarde des dates', 'closure-dates-section');
        }
        
        header('Location: ?page=settings&section=options');
        exit;
    }

    /**
     * Insère des avis de test pour Google Reviews
     */
    public function seedReviews()
    {
        $this->requireLogin();

        $adminId = $_SESSION['admin_id'];

        // Récupérer le Place ID de l'admin
        $stmt = $this->pdo->prepare("SELECT option_value FROM admin_options WHERE admin_id = ? AND option_name = 'google_place_id'");
        $stmt->execute([$adminId]);
        $placeId = $stmt->fetchColumn();

        if (!$placeId) {
            echo "❌ Pas de Place ID configuré pour l'admin $adminId\n";
            echo "<br>→ Configurez d'abord votre Place ID dans ?page=settings&section=google-reviews\n";
            exit;
        }

        // Données de test
        $testData = [
            'name' => 'Restaurant Test MenuMiam',
            'rating' => 4.5,
            'total_reviews' => 12,
            'reviews' => [
                [
                    'author_name' => 'Marie Dupont',
                    'rating' => 5,
                    'text' => 'Excellent restaurant ! Les plats sont délicieux et le service est impeccable. Je reviendrai sans hésiter.',
                    'relative_time_description' => 'il y a 2 jours',
                    'profile_photo_url' => null
                ],
                [
                    'author_name' => 'Jean Martin',
                    'rating' => 4,
                    'text' => 'Très bonne expérience. La cuisine est de qualité et les portions sont généreuses. Un peu cher mais justifié.',
                    'relative_time_description' => 'il y a 1 semaine',
                    'profile_photo_url' => null
                ],
                [
                    'author_name' => 'Sophie Bernard',
                    'rating' => 5,
                    'text' => 'Une découverte formidable ! Ambiance chaleureuse, plats créatifs et service attentionné. À recommander vivement.',
                    'relative_time_description' => 'il y a 2 semaines',
                    'profile_photo_url' => null
                ],
                [
                    'author_name' => 'Pierre Durand',
                    'rating' => 4,
                    'text' => 'Bon restaurant avec des produits frais. Le menu du midi est très intéressant. Rapport qualité/prix correct.',
                    'relative_time_description' => 'il y a 3 semaines',
                    'profile_photo_url' => null
                ],
                [
                    'author_name' => 'Claire Petit',
                    'rating' => 4,
                    'text' => 'J\'ai adoré l\'originalité des plats. Le chef sait revisiter les classiques avec talent. Desserts à tomber !',
                    'relative_time_description' => 'il y a 1 mois',
                    'profile_photo_url' => null
                ]
            ]
        ];

        // Insérer dans le cache
        $stmt = $this->pdo->prepare("
            INSERT INTO google_reviews_cache (place_id, data, cached_at) 
            VALUES (?, ?, NOW())
            ON DUPLICATE KEY UPDATE data = VALUES(data), cached_at = VALUES(cached_at)
        ");

        $result = $stmt->execute([$placeId, json_encode($testData)]);

        if ($result) {
            echo "✅ Données de test insérées avec succès !\n";
            echo "<br>📍 Place ID utilisé : <code>$placeId</code>";
            echo "<br>📊 " . count($testData['reviews']) . " avis de test ajoutés";
            echo "<br>⭐ Note moyenne : " . $testData['rating'] . "/5";
            echo "<br><br>";
            echo "<strong>Actions suivantes :</strong><br>";
            echo "→ <a href='?page=settings&section=google-reviews'>Voir les avis dans l'admin</a><br>";
            echo "→ <a href='?page=display&slug=demo-menumiam'>Voir l'affichage sur le site vitrine</a><br>";
            echo "<br><small>Note : Les avis resteront en cache 1 heure. Après ça, ils seront remplacés par les vrais avis Google si votre API fonctionne.</small>";
        } else {
            echo "❌ Erreur lors de l'insertion : " . print_r($stmt->errorInfo(), true);
        }
    }

    /**
     * Vide tous les avis en cache
     */
    public function clearReviews()
    {
        $this->requireLogin();

        $adminId = $_SESSION['admin_id'];

        // Récupérer le Place ID de l'admin
        $stmt = $this->pdo->prepare("SELECT option_value FROM admin_options WHERE admin_id = ? AND option_name = 'google_place_id'");
        $stmt->execute([$adminId]);
        $placeId = $stmt->fetchColumn();

        if (!$placeId) {
            echo "❌ Pas de Place ID configuré pour l'admin $adminId\n";
            exit;
        }

        // Supprimer du cache
        $stmt = $this->pdo->prepare("DELETE FROM google_reviews_cache WHERE place_id = ?");
        $result = $stmt->execute([$placeId]);

        if ($result) {
            echo "✅ Tous les avis ont été supprimés du cache\n";
            echo "<br>📍 Place ID : <code>$placeId</code>";
            echo "<br><br>";
            echo "<strong>Actions suivantes :</strong><br>";
            echo "→ <a href='?page=seed-reviews&action=replace'>Remplacer par des avis de test</a><br>";
            echo "→ <a href='?page=seed-reviews&action=add-5'>Ajouter 5 avis de test</a><br>";
            echo "→ <a href='?page=settings&section=google-reviews'>Retour à la configuration</a><br>";
        } else {
            echo "❌ Erreur lors de la suppression : " . print_r($stmt->errorInfo(), true);
        }
    }

    /**
     * Ajoute des avis de test (sans remplacer les existants)
     */
    public function addReviews($count = 5)
    {
        $this->requireLogin();

        $adminId = $_SESSION['admin_id'];

        // Récupérer le Place ID de l'admin
        $stmt = $this->pdo->prepare("SELECT option_value FROM admin_options WHERE admin_id = ? AND option_name = 'google_place_id'");
        $stmt->execute([$adminId]);
        $placeId = $stmt->fetchColumn();

        if (!$placeId) {
            echo "❌ Pas de Place ID configuré pour l'admin $adminId\n";
            exit;
        }

        // Récupérer les avis existants
        $stmt = $this->pdo->prepare("SELECT data FROM google_reviews_cache WHERE place_id = ?");
        $stmt->execute([$placeId]);
        $existingData = $stmt->fetchColumn();

        $existingReviews = [];
        if ($existingData) {
            $decoded = json_decode($existingData, true);
            $existingReviews = $decoded['reviews'] ?? [];
        }

        // Nouveaux avis à ajouter
        $newReviews = [
            [
                'author_name' => 'Lucas Moreau',
                'rating' => 5,
                'text' => 'Exceptionnel ! Un vrai chef cuisinier avec des produits d\'exception. Le rapport qualité/prix est imbattable.',
                'relative_time_description' => 'il y a 3 jours',
                'profile_photo_url' => null
            ],
            [
                'author_name' => 'Emma Lefevre',
                'rating' => 4,
                'text' => 'Très bonne table. Service professionnel et cuisine raffinée. Je recommande pour une occasion spéciale.',
                'relative_time_description' => 'il y a 4 jours',
                'profile_photo_url' => null
            ],
            [
                'author_name' => 'Nicolas Petit',
                'rating' => 5,
                'text' => 'Une adresse à connaître ! Ambiance chaleureuse, plats créatifs et service impeccable. Bravo à toute l\'équipe.',
                'relative_time_description' => 'il y a 5 jours',
                'profile_photo_url' => null
            ],
            [
                'author_name' => 'Camille Bernard',
                'rating' => 4,
                'text' => 'Excellent restaurant. Les saveurs sont bien équilibrées et la présentation des plats est soignée.',
                'relative_time_description' => 'il y a 1 semaine',
                'profile_photo_url' => null
            ],
            [
                'author_name' => 'Antoine Dubois',
                'rating' => 5,
                'text' => 'Magifique ! Une cuisine inventive qui respecte les produits. Le menu dégustation est une vraie réussite.',
                'relative_time_description' => 'il y a 2 semaines',
                'profile_photo_url' => null
            ]
        ];

        // Ajouter seulement les premiers avis demandés
        $toAdd = array_slice($newReviews, 0, $count);
        $allReviews = array_merge($existingReviews, $toAdd);

        // Mettre à jour le cache
        $updatedData = [
            'name' => 'Restaurant Test MenuMiam',
            'rating' => 4.6, // Légèrement amélioré avec les nouveaux avis
            'total_reviews' => count($allReviews),
            'reviews' => $allReviews
        ];

        $stmt = $this->pdo->prepare("
            INSERT INTO google_reviews_cache (place_id, data, cached_at) 
            VALUES (?, ?, NOW())
            ON DUPLICATE KEY UPDATE data = VALUES(data), cached_at = VALUES(cached_at)
        ");

        $result = $stmt->execute([$placeId, json_encode($updatedData)]);

        if ($result) {
            echo "✅ $count avis de test ajoutés avec succès !\n";
            echo "<br>📍 Place ID : <code>$placeId</code>";
            echo "<br>📊 Total d'avis : " . count($allReviews);
            echo "<br>⭐ Nouvelle note moyenne : " . $updatedData['rating'] . "/5";
            echo "<br><br>";
            echo "<strong>Actions suivantes :</strong><br>";
            echo "→ <a href='?page=settings&section=google-reviews'>Voir les avis dans l'admin</a><br>";
            echo "→ <a href='?page=display&slug=demo-menumiam'>Voir sur le site vitrine</a><br>";
            echo "→ <a href='?page=seed-reviews&action=clear'>Vider tous les avis</a><br>";
        } else {
            echo "❌ Erreur lors de l'ajout : " . print_r($stmt->errorInfo(), true);
        }
    }
}
