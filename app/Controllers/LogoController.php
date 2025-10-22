<?php
require_once __DIR__ . '/BaseController.php';

class LogoController extends BaseController {

    public function __construct($pdo) {
        parent::__construct($pdo);
    }

    public function edit() {
        $this->requireLogin();

        $message = $error = null;
        $admin_id = $_SESSION['admin_id'];

        if (!empty($_FILES['logo']['tmp_name'])) {
            $uploadDir = __DIR__ . '/../../public/assets/logos/';
            
            // Créer le dossier s'il n'existe pas
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            // Nom du fichier = adminID_timestamp.extension
            $ext = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
            $fileName = "logo_{$admin_id}_" . time() . "." . $ext;
            $targetFile = $uploadDir . $fileName;

            // Déplacer le fichier uploadé
            if (move_uploaded_file($_FILES['logo']['tmp_name'], $targetFile)) {

                // Supprimer l'ancien logo si présent
                $stmt = $this->pdo->prepare("SELECT filename FROM logos WHERE admin_id = ?");
                $stmt->execute([$admin_id]);
                $oldLogo = $stmt->fetchColumn();

                if ($oldLogo && file_exists($uploadDir . $oldLogo)) {
                    unlink($uploadDir . $oldLogo);
                }

                // Mettre à jour ou insérer le nouveau logo
                $stmt = $this->pdo->prepare("
                    INSERT INTO logos (admin_id, filename, uploaded_at)
                    VALUES (?, ?, NOW())
                    ON DUPLICATE KEY UPDATE filename = VALUES(filename), uploaded_at = NOW()
                ");
                $stmt->execute([$admin_id, $fileName]);

                $message = "Logo mis à jour avec succès !";
            } else {
                $error = "Erreur lors du téléchargement du logo.";
            }
        }

        $this->render('admin/edit-logo', [
            'message' => $message,
            'error' => $error
        ]);
    }
}
?>
