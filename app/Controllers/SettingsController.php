<?php
require_once __DIR__ . '/BaseController.php';

class SettingsController extends BaseController
{
    public function show()
    {
        $this->requireLogin();

        // Récupérer les informations de l'utilisateur
        $user = $this->getCurrentUser();

        // Section par défaut
        $section = $_GET['section'] ?? 'profile';

        $this->render('admin/settings', [
            'user' => $user,
            'current_section' => $section,
            'title' => 'Paramètres',
            'csrf_token' => $this->getCsrfToken() // AJOUTEZ CECI
        ]);
    }

    public function updateProfile()
    {
        $this->requireLogin();

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
            }

            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Email invalide";
            }

            if (empty($restaurant_name)) {
                $errors[] = "Le nom du restaurant est requis";
            }

            // Vérifier si l'email existe déjà (sauf pour l'utilisateur actuel)
            if (empty($errors)) {
                $stmt = $this->pdo->prepare("SELECT id FROM admins WHERE email = ? AND id != ?");
                $stmt->execute([$email, $_SESSION['admin_id']]);
                if ($stmt->fetch()) {
                    $errors[] = "Cet email est déjà utilisé par un autre compte";
                }
            }

            if (empty($errors)) {
                // Mettre à jour la base de données
                $stmt = $this->pdo->prepare("
                    UPDATE admins 
                    SET username = ?, email = ?, restaurant_name = ?, updated_at = NOW() 
                    WHERE id = ?
                ");

                if ($stmt->execute([$username, $email, $restaurant_name, $_SESSION['admin_id']])) {
                    // Mettre à jour la session
                    $_SESSION['admin_username'] = $username;
                    $_SESSION['admin_email'] = $email;
                    $_SESSION['restaurant_name'] = $restaurant_name;

                    $this->addSuccessMessage('Profil mis à jour avec succès', 'profile-form');
                } else {
                    $this->addErrorMessage('Erreur lors de la mise à jour', 'profile-form');
                }
            } else {
                foreach ($errors as $error) {
                    $this->addErrorMessage($error, 'profile-form');
                }
            }

            header('Location: ?page=settings&section=profile');
            exit;
        }
    }

    public function changePassword()
    {
        $this->requireLogin();

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
            } elseif (strlen($new_password) < 8) {
                $errors[] = "Le nouveau mot de passe doit contenir au moins 8 caractères";
            }

            if ($new_password !== $confirm_password) {
                $errors[] = "Les mots de passe ne correspondent pas";
            }

            // Vérifier le mot de passe actuel
            if (empty($errors)) {
                $stmt = $this->pdo->prepare("SELECT password FROM admins WHERE id = ?");
                $stmt->execute([$_SESSION['admin_id']]);
                $admin = $stmt->fetch();

                if (!$admin || !password_verify($current_password, $admin['password'])) {
                    $errors[] = "Le mot de passe actuel est incorrect";
                }
            }

            if (empty($errors)) {
                // Hasher le nouveau mot de passe
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

                // Mettre à jour la base de données
                $stmt = $this->pdo->prepare("UPDATE admins SET password = ?, updated_at = NOW() WHERE id = ?");

                if ($stmt->execute([$hashed_password, $_SESSION['admin_id']])) {
                    $this->addSuccessMessage('Mot de passe modifié avec succès', 'password-form');
                } else {
                    $this->addErrorMessage('Erreur lors de la modification du mot de passe', 'password-form');
                }
            } else {
                foreach ($errors as $error) {
                    $this->addErrorMessage($error, 'password-form');
                }
            }

            header('Location: ?page=settings&section=password');
            exit;
        }
    }

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

        // Initialiser last_card_update avec la date de mise à jour de l'admin
        $admin['last_card_update'] = $admin['updated_at'] ?? null;

        // Si vous voulez quand même essayer de récupérer les dates des plats/catégories
        // D'abord, vérifions quelles colonnes existent
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

    // Dans votre contrôleur (AdminController ou SettingsController)
    public function saveOption()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $option = $_POST['option'] ?? '';
            $value = $_POST['value'] ?? '';

            // Validation
            if (in_array($option, ['site_online', 'mail_reminder'])) {
                $stmt = $this->pdo->prepare("
                INSERT INTO admin_options (admin_id, option_name, option_value) 
                VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE option_value = ?
            ");
                $stmt->execute([$_SESSION['admin_id'], $option, $value, $value]);

                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Option invalide']);
            }
        }
    }

    public function getOptions()
    {
        $stmt = $this->pdo->prepare("SELECT option_name, option_value FROM admin_options WHERE admin_id = ?");
        $stmt->execute([$_SESSION['admin_id']]);
        $options = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        // Valeurs par défaut
        $defaultOptions = [
            'site_online' => '1', // Par défaut actif
            'mail_reminder' => '1' // Par défaut actif
        ];

        $mergedOptions = array_merge($defaultOptions, $options);
        echo json_encode($mergedOptions);
    }

    public function saveOptionsBatch()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $options = json_decode($_POST['options'] ?? '{}', true);

            if (is_array($options)) {
                foreach ($options as $option => $value) {
                    if (in_array($option, ['site_online', 'mail_reminder', 'email_notifications'])) {
                        $stmt = $this->pdo->prepare("
                        INSERT INTO admin_options (admin_id, option_name, option_value) 
                        VALUES (?, ?, ?)
                        ON DUPLICATE KEY UPDATE option_value = ?
                    ");
                        $stmt->execute([$_SESSION['admin_id'], $option, $value, $value]);
                    }
                }

                echo json_encode(['success' => true, 'message' => 'Options sauvegardées']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Données invalides']);
            }
        }
    }
}
