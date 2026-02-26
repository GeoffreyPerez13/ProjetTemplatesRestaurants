<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../Helpers/Validator.php';

/**
 * Contrôleur des paramètres de l'administrateur
 * Gère le profil, le mot de passe, les options (site online, rappels, notifications),
 * la suppression de compte et la sélection de template
 */
class SettingsController extends BaseController
{
    /**
     * Affiche la page des paramètres avec la section demandée (?section=profile|password|options|account)
     */
    public function show()
    {
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
            'email_notifications' => '0'
        ];

        $options = array_merge($defaultOptions, $userOptions);

        // Section par défaut
        $section = $_GET['section'] ?? 'profile';

        // Récupérer les messages flash en utilisant la méthode du BaseController
        $messages = $this->getFlashMessages();
        $success_message = $messages['success_message'];
        $error_message = $messages['error_message'];

        $this->render('admin/settings', [
            'user' => $user,
            'options' => $options,
            'current_section' => $section,
            'title' => 'Paramètres',
            'csrf_token' => $this->getCsrfToken(),
            'success_message' => $success_message,
            'error_message' => $error_message
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

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validation CSRF
            if (!$this->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
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

                        // Utiliser la méthode du BaseController
                        $this->addSuccessMessage('Profil mis à jour avec succès', 'profile-form');
                    } else {
                        $this->pdo->rollBack();
                        $this->addErrorMessage('Erreur lors de la mise à jour de la base de données', 'profile-form');
                    }
                } catch (Exception $e) {
                    $this->pdo->rollBack();
                    error_log("Erreur mise à jour profil: " . $e->getMessage());
                    $this->addErrorMessage('Une erreur est survenue lors de la mise à jour du profil', 'profile-form');
                }
            } else {
                // Utiliser la méthode du BaseController pour chaque erreur
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

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validation CSRF
            if (!$this->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
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
                        $this->addSuccessMessage('Mot de passe modifié avec succès', 'password-form');
                    } else {
                        $this->pdo->rollBack();
                        $this->addErrorMessage('Erreur lors de la mise à jour du mot de passe', 'password-form');
                    }
                } catch (Exception $e) {
                    $this->pdo->rollBack();
                    error_log("Erreur changement mot de passe: " . $e->getMessage());
                    $this->addErrorMessage('Une erreur est survenue lors du changement de mot de passe', 'password-form');
                }
            } else {
                // Utiliser la méthode du BaseController pour chaque erreur
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
                    if (in_array($option, ['site_online', 'mail_reminder', 'email_notifications'])) {
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
     * Détecte si la requête courante est une requête AJAX (XMLHttpRequest)
     *
     * @return bool true si requête AJAX
     */
    private function isAjaxRequest()
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
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

        require_once __DIR__ . '/../Models/OptionModel.php';
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

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ?page=edit-template');
            exit;
        }

        if (!$this->verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            $this->addErrorMessage('Token CSRF invalide.');
            header('Location: ?page=edit-template');
            exit;
        }

        $palette = $_POST['palette'] ?? 'classic';
        $allowed = ['classic', 'modern', 'elegant', 'nature', 'rose', 'bistro', 'ocean'];

        if (!in_array($palette, $allowed)) {
            $this->addErrorMessage('Palette invalide.');
            header('Location: ?page=edit-template');
            exit;
        }

        require_once __DIR__ . '/../Models/OptionModel.php';
        $optionModel = new OptionModel($this->pdo);
        $optionModel->set($_SESSION['admin_id'], 'site_palette', $palette);

        $names = ['classic' => 'Classique', 'modern' => 'Moderne', 'elegant' => 'Élégant', 'nature' => 'Nature', 'rose' => 'Rosé', 'bistro' => 'Bistro', 'ocean' => 'Océan'];
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

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ?page=edit-template');
            exit;
        }

        if (!$this->verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            $this->addErrorMessage('Token CSRF invalide.');
            header('Location: ?page=edit-template');
            exit;
        }

        $layout = $_POST['layout'] ?? 'standard';
        $allowed = ['standard', 'bistro', 'ocean'];

        if (!in_array($layout, $allowed)) {
            $this->addErrorMessage('Layout invalide.');
            header('Location: ?page=edit-template');
            exit;
        }

        require_once __DIR__ . '/../Models/OptionModel.php';
        $optionModel = new OptionModel($this->pdo);
        $optionModel->set($_SESSION['admin_id'], 'site_layout', $layout);

        $names = ['standard' => 'Standard', 'bistro' => 'Bistro', 'ocean' => 'Océan'];
        $this->addSuccessMessage('Design "' . $names[$layout] . '" appliqué avec succès !');
        $_SESSION['open_template_accordion'] = 'layout';
        header('Location: ?page=edit-template');
        exit;
    }
}
