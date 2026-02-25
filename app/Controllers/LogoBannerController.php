<?php

require_once __DIR__ . '/BaseController.php';

class LogoBannerController extends BaseController
{

    public function __construct($pdo)
    {
        parent::__construct($pdo);
        $this->setScrollDelay(1500);
    }

    /**
     * Page d'édition du logo et de la bannière
     */
    public function show()
    {
        $this->requireLogin();
        $admin_id = $_SESSION['admin_id'];

        $current_logo = $this->getCurrentLogo($admin_id);
        $current_banner = $this->getCurrentBanner($admin_id);
        $messages = $this->getFlashMessages();

        $data = [
            'current_logo' => $current_logo,
            'current_banner' => $current_banner,
            'success_message' => $messages['success_message'] ?? null,
            'error_message' => $messages['error_message'] ?? null,
            'scroll_delay' => $messages['scroll_delay'] ?? $this->scrollDelay,
            'anchor' => $messages['anchor'] ?? null,
            'closeAccordion' => $_SESSION['close_accordion'] ?? '',
            'openAccordion' => $_SESSION['open_accordion'] ?? '',
            'csrf_token' => $this->getCsrfToken() // ← AJOUT IMPORTANT
        ];

        unset($_SESSION['close_accordion'], $_SESSION['open_accordion']);
        $this->render('admin/edit-logo-banner', $data);
    }

    // ==================== LOGO ====================

    public function uploadLogo()
    {
        $this->requireLogin();
        $admin_id = $_SESSION['admin_id'];
        $anchor = $_POST['anchor'] ?? 'upload-logo';
        $this->handleUpload('logo', $admin_id, $anchor, 'logos', 'Logo');
    }

    public function deleteLogo()
    {
        $this->requireLogin();
        $admin_id = $_SESSION['admin_id'];
        $anchor = $_POST['anchor'] ?? 'current-logo';
        $this->handleDelete('logo', $admin_id, $anchor, 'logos', 'Logo');
    }

    // ==================== BANNIÈRE ====================

    public function uploadBanner()
    {
        $this->requireLogin();
        $admin_id = $_SESSION['admin_id'];
        $anchor = $_POST['anchor'] ?? 'upload-banner';
        $this->handleUpload('banner', $admin_id, $anchor, 'banners', 'Bannière');
    }

    public function deleteBanner()
    {
        $this->requireLogin();
        $admin_id = $_SESSION['admin_id'];
        $anchor = $_POST['anchor'] ?? 'current-banner';
        $this->handleDelete('banner', $admin_id, $anchor, 'banners', 'Bannière');
    }

    // ==================== MÉTHODES PRIVÉES GÉNÉRIQUES ====================

    /**
     * Récupère le logo actuel
     */
    private function getCurrentLogo($admin_id)
    {
        return $this->getCurrentMedia($admin_id, 'logos');
    }

    /**
     * Récupère la bannière actuelle
     */
    private function getCurrentBanner($admin_id)
    {
        return $this->getCurrentMedia($admin_id, 'banners');
    }

    /**
     * Récupère un média (logo ou bannière) depuis la base
     */
    private function getCurrentMedia($admin_id, $table)
    {
        // Whitelist des tables autorisées pour éviter les injections SQL
        $allowedTables = ['logos', 'banners'];
        if (!in_array($table, $allowedTables)) {
            throw new \InvalidArgumentException("Table non autorisée: $table");
        }
        $stmt = $this->pdo->prepare("SELECT * FROM $table WHERE admin_id = ?");
        $stmt->execute([$admin_id]);
        $media = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($media && !empty($media['filename'])) {
            $uploadDir = __DIR__ . "/../../public/assets/$table/";
            if (file_exists($uploadDir . $media['filename'])) {
                $media['public_url'] = "/assets/$table/" . $media['filename'];
                $media['upload_date'] = !empty($media['uploaded_at'])
                    ? date('d/m/Y à H:i', strtotime($media['uploaded_at']))
                    : 'Date inconnue';
                return $media;
            } else {
                $this->cleanupMissingMedia($admin_id, $table);
                return null;
            }
        }
        return null;
    }

    /**
     * Met à jour le texte de la bannière
     */
    public function updateBannerText()
    {
        $this->requireLogin();
        $admin_id = $_SESSION['admin_id'];
        $anchor = 'banner-text'; // ID de l'accordéon

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: ?page=edit-logo-banner");
            exit;
        }

        if (!$this->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            $this->addErrorMessage("Token de sécurité invalide.", $anchor);
            header("Location: ?page=edit-logo-banner&anchor=$anchor");
            exit;
        }

        $texte = trim($_POST['banner_text'] ?? '');

        // Vérifier qu'une bannière existe
        $banner = $this->getCurrentBanner($admin_id);
        if (!$banner) {
            $this->addErrorMessage("Vous devez d'abord uploader une bannière.", $anchor);
            header("Location: ?page=edit-logo-banner&anchor=$anchor");
            exit;
        }

        // Mise à jour en base
        $stmt = $this->pdo->prepare("UPDATE banners SET text = ? WHERE admin_id = ?");
        if ($stmt->execute([$texte !== '' ? $texte : null, $admin_id])) {
            $this->addSuccessMessage("Texte de la bannière mis à jour.", $anchor);
        } else {
            $this->addErrorMessage("Erreur lors de l'enregistrement.", $anchor);
        }

        header("Location: ?page=edit-logo-banner&anchor=$anchor");
        exit;
    }

    /**
     * Supprime le texte de la bannière
     */
    public function deleteBannerText()
    {
        $this->requireLogin();
        $admin_id = $_SESSION['admin_id'];
        $anchor = 'banner-text';

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: ?page=edit-logo-banner");
            exit;
        }

        if (!$this->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            $this->addErrorMessage("Token de sécurité invalide.", $anchor);
            header("Location: ?page=edit-logo-banner&anchor=$anchor");
            exit;
        }

        $stmt = $this->pdo->prepare("UPDATE banners SET text = NULL WHERE admin_id = ?");
        if ($stmt->execute([$admin_id])) {
            $this->addSuccessMessage("Texte de la bannière supprimé.", $anchor);
        } else {
            $this->addErrorMessage("Erreur lors de la suppression.", $anchor);
        }

        header("Location: ?page=edit-logo-banner&anchor=$anchor");
        exit;
    }

    /**
     * Nettoie une entrée orpheline
     */
    private function cleanupMissingMedia($admin_id, $table)
    {
        $allowedTables = ['logos', 'banners'];
        if (!in_array($table, $allowedTables)) {
            throw new \InvalidArgumentException("Table non autorisée: $table");
        }
        $stmt = $this->pdo->prepare("DELETE FROM $table WHERE admin_id = ?");
        $stmt->execute([$admin_id]);
    }

    /**
     * Gère l'upload d'un média
     */
    private function handleUpload($field, $admin_id, $anchor, $table, $label)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES[$field])) {
            $this->addErrorMessage("Aucun fichier reçu.", $anchor);
            header("Location: ?page=edit-logo-banner&anchor=$anchor");
            exit;
        }

        if (!$this->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            $this->addErrorMessage("Token de sécurité invalide.", $anchor);
            header("Location: ?page=edit-logo-banner&anchor=$anchor");
            exit;
        }

        $file = $_FILES[$field];
        $uploadDir = __DIR__ . "/../../public/assets/$table/";

        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0755, true)) {
                $this->addErrorMessage("Impossible de créer le dossier de destination.", $anchor);
                header("Location: ?page=edit-logo-banner&anchor=$anchor");
                exit;
            }
        }

        // Validation
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $error = $this->uploadErrorToString($file['error']);
            $this->addErrorMessage($error, $anchor);
            header("Location: ?page=edit-logo-banner&anchor=$anchor");
            exit;
        }

        if ($file['size'] > 5 * 1024 * 1024) {
            $this->addErrorMessage("Le fichier est trop volumineux (max 5Mo).", $anchor);
            header("Location: ?page=edit-logo-banner&anchor=$anchor");
            exit;
        }

        $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mime, $allowedMimes)) {
            $this->addErrorMessage("Type de fichier non autorisé. Formats acceptés: JPG, PNG, GIF, WebP, SVG.", $anchor);
            header("Location: ?page=edit-logo-banner&anchor=$anchor");
            exit;
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowedExt = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
        if (!in_array($ext, $allowedExt)) {
            $this->addErrorMessage("Extension de fichier non autorisée.", $anchor);
            header("Location: ?page=edit-logo-banner&anchor=$anchor");
            exit;
        }

        // Nom de fichier sécurisé
        $fileName = $field . '_' . $admin_id . '_' . time() . '_' . uniqid() . '.' . $ext;
        $targetFile = $uploadDir . $fileName;

        if (!move_uploaded_file($file['tmp_name'], $targetFile)) {
            $this->addErrorMessage("Erreur lors du déplacement du fichier.", $anchor);
            header("Location: ?page=edit-logo-banner&anchor=$anchor");
            exit;
        }

        // Supprimer l'ancien fichier
        $this->deleteOldMedia($admin_id, $table, $uploadDir);

        // Sauvegarde en base
        try {
            $this->saveMediaToDatabase($admin_id, $fileName, $table);
            $this->addSuccessMessage("$label mis à jour avec succès.", $anchor);
            // Gérer l'ouverture/fermeture des accordéons
            if ($field === 'logo') {
                $_SESSION['close_accordion'] = 'upload-logo-content';
                $_SESSION['open_accordion'] = 'current-logo-content';
            } else {
                $_SESSION['close_accordion'] = 'upload-banner-content';
                $_SESSION['open_accordion'] = 'current-banner-content';
            }
        } catch (Exception $e) {
            // Rollback
            if (file_exists($targetFile)) {
                unlink($targetFile);
            }
            $this->addErrorMessage("Erreur de base de données: " . $e->getMessage(), $anchor);
        }

        header("Location: ?page=edit-logo-banner&anchor=$anchor");
        exit;
    }

    /**
     * Gère la suppression d'un média
     */
    private function handleDelete($field, $admin_id, $anchor, $table, $label)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: ?page=edit-logo-banner");
            exit;
        }

        if (!$this->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            $this->addErrorMessage("Token de sécurité invalide.", $anchor);
            header("Location: ?page=edit-logo-banner&anchor=$anchor");
            exit;
        }

        $uploadDir = __DIR__ . "/../../public/assets/$table/";
        $media = $this->getCurrentMedia($admin_id, $table);

        if ($media && !empty($media['filename'])) {
            // Supprimer le fichier
            if (file_exists($uploadDir . $media['filename'])) {
                unlink($uploadDir . $media['filename']);
            }

            // Supprimer de la base
            $stmt = $this->pdo->prepare("DELETE FROM $table WHERE admin_id = ?");
            $stmt->execute([$admin_id]);

            $this->addSuccessMessage("$label supprimé avec succès.", $anchor);

            // Gérer les accordéons
            if ($field === 'logo') {
                $_SESSION['close_accordion'] = 'current-logo-content';
                $_SESSION['open_accordion'] = 'upload-logo-content';
            } else {
                $_SESSION['close_accordion'] = 'current-banner-content';
                $_SESSION['open_accordion'] = 'upload-banner-content';
            }
        } else {
            $this->addErrorMessage("Aucun $label à supprimer.", $anchor);
        }

        header("Location: ?page=edit-logo-banner&anchor=$anchor");
        exit;
    }

    /**
     * Supprime l'ancien fichier média
     */
    private function deleteOldMedia($admin_id, $table, $uploadDir)
    {
        $old = $this->getCurrentMedia($admin_id, $table);
        if ($old && !empty($old['filename']) && file_exists($uploadDir . $old['filename'])) {
            unlink($uploadDir . $old['filename']);
        }
    }

    /**
     * Sauvegarde le média en base
     */
    private function saveMediaToDatabase($admin_id, $filename, $table)
    {
        $allowedTables = ['logos', 'banners'];
        if (!in_array($table, $allowedTables)) {
            throw new \InvalidArgumentException("Table non autorisée: $table");
        }
        $stmt = $this->pdo->prepare("
            INSERT INTO $table (admin_id, filename, uploaded_at)
            VALUES (?, ?, NOW())
            ON DUPLICATE KEY UPDATE
                filename = VALUES(filename),
                uploaded_at = NOW()
        ");
        return $stmt->execute([$admin_id, $filename]);
    }

    /**
     * Convertit les erreurs d'upload en message
     */
    private function uploadErrorToString($error)
    {
        $errors = [
            UPLOAD_ERR_INI_SIZE => "Le fichier dépasse la taille maximale autorisée.",
            UPLOAD_ERR_FORM_SIZE => "Le fichier dépasse la taille maximale spécifiée dans le formulaire.",
            UPLOAD_ERR_PARTIAL => "Le fichier n'a été que partiellement uploadé.",
            UPLOAD_ERR_NO_FILE => "Aucun fichier n'a été uploadé.",
            UPLOAD_ERR_NO_TMP_DIR => "Dossier temporaire manquant.",
            UPLOAD_ERR_CANT_WRITE => "Échec de l'écriture du fichier sur le disque.",
            UPLOAD_ERR_EXTENSION => "Une extension PHP a arrêté l'upload du fichier."
        ];
        return $errors[$error] ?? "Erreur d'upload inconnue.";
    }
}
