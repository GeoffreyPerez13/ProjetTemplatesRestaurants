<?php

require_once __DIR__ . '/BaseController.php';

class LogoController extends BaseController {

    public function __construct($pdo) {
        parent::__construct($pdo);
        $this->setScrollDelay(1500); // Changé à 1.5s pour correspondre aux autres
    }

    /**
     * Page d'édition du logo
     */
    public function edit() {
        // 1. Vérification de l'authentification
        $this->requireLogin();
        $admin_id = $_SESSION['admin_id'];

        // 2. Récupération du logo actuel
        $current_logo = $this->getCurrentLogo($admin_id);

        // 3. Récupération des messages flash existants
        $messages = $this->getFlashMessages();
        
        // 4. Traitement du formulaire d'upload
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Récupérer l'ancre du formulaire
            $anchor = $_POST['anchor'] ?? 'logo-form';
            
            // Vérifier si c'est un upload de fichier
            if (isset($_FILES['logo']) && !empty($_FILES['logo']['tmp_name'])) {
                try {
                    // Traitement de l'upload
                    $result = $this->handleLogoUpload($admin_id);
                    
                    if ($result['success']) {
                        // Message de succès
                        $this->addSuccessMessage("Logo mis à jour avec succès !", $anchor);
                        
                        // Gestion des accordéons : ouvrir logo actuel, fermer upload
                        $_SESSION['close_accordion'] = 'upload-logo-content';
                        $_SESSION['open_accordion'] = 'current-logo-content';
                        
                        // Mettre à jour le logo actuel
                        $current_logo = $this->getCurrentLogo($admin_id);
                    } else {
                        // Message d'erreur
                        $this->addErrorMessage($result['error'], $anchor);
                        // En cas d'erreur, garder l'accordéon upload ouvert
                        $_SESSION['open_accordion'] = 'upload-logo-content';
                    }
                    
                    // Redirection pour éviter la soumission multiple
                    $redirectUrl = '?page=edit-logo&anchor=' . urlencode($anchor);
                    header('Location: ' . $redirectUrl);
                    exit;
                } catch (Exception $e) {
                    $this->addErrorMessage("Erreur lors du traitement: " . $e->getMessage(), $anchor);
                    $_SESSION['open_accordion'] = 'upload-logo-content';
                    $redirectUrl = '?page=edit-logo&anchor=' . urlencode($anchor);
                    header('Location: ' . $redirectUrl);
                    exit;
                }
            }
            
            // Si c'est une suppression de logo
            if (isset($_POST['delete_logo'])) {
                $this->handleLogoDeletion($admin_id, $anchor);
                $redirectUrl = '?page=edit-logo&anchor=' . urlencode($anchor);
                header('Location: ' . $redirectUrl);
                exit;
            }
        }

        // 5. Préparation des données pour la vue
        $data = [
            'current_logo' => $current_logo,
            'success_message' => $messages['success_message'] ?? null,
            'error_message' => $messages['error_message'] ?? null,
            'scroll_delay' => $messages['scroll_delay'] ?? $this->scrollDelay,
            'anchor' => $messages['anchor'] ?? null,
            'closeAccordion' => $_SESSION['close_accordion'] ?? '',
            'openAccordion' => $_SESSION['open_accordion'] ?? ''
        ];

        // Nettoyer les variables de session
        unset($_SESSION['close_accordion'], $_SESSION['open_accordion']);

        // 6. Affichage de la vue
        $this->render('admin/edit-logo', $data);
    }

    /**
     * Récupère le logo actuel
     */
    private function getCurrentLogo($admin_id) {
        $stmt = $this->pdo->prepare("SELECT * FROM logos WHERE admin_id = ?");
        $stmt->execute([$admin_id]);
        $logo = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($logo && !empty($logo['filename'])) {
            // Vérifier si le fichier existe physiquement
            $uploadDir = __DIR__ . '/../../public/assets/logos/';
            if (file_exists($uploadDir . $logo['filename'])) {
                // Ajouter le chemin public pour l'affichage
                $logo['public_url'] = '/assets/logos/' . $logo['filename'];
                $logo['upload_date'] = !empty($logo['uploaded_at']) 
                    ? date('d/m/Y à H:i', strtotime($logo['uploaded_at']))
                    : 'Date inconnue';
                return $logo;
            } else {
                // Le fichier n'existe pas, nettoyer la base
                $this->cleanupMissingLogo($admin_id);
                return null;
            }
        }
        
        return null;
    }

    /**
     * Nettoie les logos manquants
     */
    private function cleanupMissingLogo($admin_id) {
        $stmt = $this->pdo->prepare("DELETE FROM logos WHERE admin_id = ?");
        $stmt->execute([$admin_id]);
    }

    /**
     * Gère l'upload du logo
     */
    private function handleLogoUpload($admin_id) {
        if (empty($_FILES['logo']['tmp_name'])) {
            return ['success' => false, 'error' => "Aucun fichier sélectionné."];
        }

        $uploadDir = __DIR__ . '/../../public/assets/logos/';
        
        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0755, true)) {
                return ['success' => false, 'error' => "Impossible de créer le dossier de destination."];
            }
        }

        $file = $_FILES['logo'];
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $uploadErrors = [
                UPLOAD_ERR_INI_SIZE => "Le fichier dépasse la taille maximale autorisée.",
                UPLOAD_ERR_FORM_SIZE => "Le fichier dépasse la taille maximale spécifiée dans le formulaire.",
                UPLOAD_ERR_PARTIAL => "Le fichier n'a été que partiellement uploadé.",
                UPLOAD_ERR_NO_FILE => "Aucun fichier n'a été uploadé.",
                UPLOAD_ERR_NO_TMP_DIR => "Dossier temporaire manquant.",
                UPLOAD_ERR_CANT_WRITE => "Échec de l'écriture du fichier sur le disque.",
                UPLOAD_ERR_EXTENSION => "Une extension PHP a arrêté l'upload du fichier."
            ];
            
            $errorMsg = $uploadErrors[$file['error']] ?? "Erreur d'upload inconnue.";
            return ['success' => false, 'error' => $errorMsg];
        }

        // Vérification de la taille (max 5MB)
        if ($file['size'] > 5 * 1024 * 1024) {
            return ['success' => false, 'error' => "Le fichier est trop volumineux (max 5MB)."];
        }

        // Vérification du type
        $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mime, $allowedMimes)) {
            return ['success' => false, 'error' => "Type de fichier non autorisé. Formats acceptés: JPG, PNG, GIF, WebP, SVG."];
        }

        // Extension
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
        
        if (!in_array($ext, $allowedExtensions)) {
            return ['success' => false, 'error' => "Extension de fichier non autorisée."];
        }

        // Génération du nom de fichier
        $safeName = preg_replace('/[^a-zA-Z0-9\._-]/', '', basename($file['name']));
        $fileName = "logo_{$admin_id}_" . time() . "_" . uniqid() . "." . $ext;
        $targetFile = $uploadDir . $fileName;

        // Déplacement du fichier
        if (!move_uploaded_file($file['tmp_name'], $targetFile)) {
            return ['success' => false, 'error' => "Erreur lors du déplacement du fichier."];
        }

        // Suppression de l'ancien logo
        $this->deleteOldLogo($admin_id, $uploadDir);

        // Sauvegarde en base
        try {
            $this->saveLogoToDatabase($admin_id, $fileName);
            return ['success' => true, 'filename' => $fileName];
        } catch (Exception $e) {
            // Rollback en cas d'erreur
            if (file_exists($targetFile)) {
                unlink($targetFile);
            }
            return ['success' => false, 'error' => "Erreur de base de données: " . $e->getMessage()];
        }
    }

    /**
     * Supprime l'ancien logo
     */
    private function deleteOldLogo($admin_id, $uploadDir) {
        $oldLogo = $this->getCurrentLogo($admin_id);
        if ($oldLogo && !empty($oldLogo['filename']) && file_exists($uploadDir . $oldLogo['filename'])) {
            unlink($uploadDir . $oldLogo['filename']);
        }
    }

    /**
     * Sauvegarde le logo dans la base
     */
    private function saveLogoToDatabase($admin_id, $filename) {
        $stmt = $this->pdo->prepare("
            INSERT INTO logos (admin_id, filename, uploaded_at)
            VALUES (?, ?, NOW())
            ON DUPLICATE KEY UPDATE
                filename = VALUES(filename),
                uploaded_at = NOW()
        ");
        return $stmt->execute([$admin_id, $filename]);
    }

    /**
     * Gère la suppression du logo
     */
    private function handleLogoDeletion($admin_id, $anchor) {
        $uploadDir = __DIR__ . '/../../public/assets/logos/';
        $logo = $this->getCurrentLogo($admin_id);
        
        if ($logo && !empty($logo['filename'])) {
            // Supprimer le fichier physique
            if (file_exists($uploadDir . $logo['filename'])) {
                unlink($uploadDir . $logo['filename']);
            }
            
            // Supprimer de la base
            $stmt = $this->pdo->prepare("DELETE FROM logos WHERE admin_id = ?");
            $stmt->execute([$admin_id]);
            
            $this->addSuccessMessage("Logo supprimé avec succès.", $anchor);
            
            // Gestion des accordéons : ouvrir upload, fermer logo actuel
            $_SESSION['close_accordion'] = 'current-logo-content';
            $_SESSION['open_accordion'] = 'upload-logo-content';
        } else {
            $this->addErrorMessage("Aucun logo à supprimer.", $anchor);
            // En cas d'erreur, garder l'accordéon logo actuel ouvert
            $_SESSION['open_accordion'] = 'current-logo-content';
        }
    }
}
?>