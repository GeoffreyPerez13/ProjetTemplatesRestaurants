<?php

require_once __DIR__ . '/BaseController.php'; // Inclusion du contrôleur de base contenant les fonctions communes (authentification, rendu, CSRF, etc.)

// Définition du contrôleur LogoController, chargé de la gestion du logo du site
class LogoController extends BaseController {

    // Constructeur : initialise la connexion PDO via le parent
    public function __construct($pdo) {
        parent::__construct($pdo);
    }

    // Méthode principale : permet à l’admin de modifier ou d’ajouter son logo
    public function edit() {
        // Vérifie que l’administrateur est connecté, sinon redirection vers la page de login
        $this->requireLogin();

        // Variables de retour (messages d’information ou d’erreur)
        $message = $error = null;

        // Récupère l’ID de l’administrateur depuis la session
        $admin_id = $_SESSION['admin_id'];

        // Si un fichier "logo" a bien été envoyé dans le formulaire
        if (!empty($_FILES['logo']['tmp_name'])) {

            // Définit le chemin du dossier de destination pour les logos
            $uploadDir = __DIR__ . '/../../public/assets/logos/';
            
            // Si le dossier n’existe pas encore, on le crée avec des permissions 755
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            // Récupère l’extension du fichier uploadé (ex : png, jpg, etc.)
            $ext = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);

            // Génère un nom de fichier unique : logo_IDADMIN_timestamp.extension
            // Ex : logo_3_1730123456.png
            $fileName = "logo_{$admin_id}_" . time() . "." . $ext;

            // Construit le chemin complet du fichier de destination
            $targetFile = $uploadDir . $fileName;

            // Déplace le fichier temporaire vers le dossier final
            if (move_uploaded_file($_FILES['logo']['tmp_name'], $targetFile)) {

                // --- GESTION DE L’ANCIEN LOGO ---

                // Récupère l’ancien logo de cet admin dans la base
                $stmt = $this->pdo->prepare("SELECT filename FROM logos WHERE admin_id = ?");
                $stmt->execute([$admin_id]);
                $oldLogo = $stmt->fetchColumn();

                // Si un ancien logo existe et que le fichier est présent sur le serveur, on le supprime
                if ($oldLogo && file_exists($uploadDir . $oldLogo)) {
                    unlink($uploadDir . $oldLogo);
                }

                // --- SAUVEGARDE DU NOUVEAU LOGO ---

                // Si un enregistrement existe déjà pour cet admin, on met à jour le logo
                // Sinon, on insère une nouvelle ligne
                $stmt = $this->pdo->prepare("
                    INSERT INTO logos (admin_id, filename, uploaded_at)
                    VALUES (?, ?, NOW())
                    ON DUPLICATE KEY UPDATE 
                        filename = VALUES(filename),
                        uploaded_at = NOW()
                ");
                $stmt->execute([$admin_id, $fileName]);

                // Message de succès affiché à l’utilisateur
                $message = "Logo mis à jour avec succès !";

            } else {
                // Si le déplacement du fichier a échoué (problème de permission ou de taille)
                $error = "Erreur lors du téléchargement du logo.";
            }
        }

        // Appelle la vue "edit-logo" pour afficher le formulaire et les messages
        $this->render('admin/edit-logo', [
            'message' => $message, // message de succès (si upload ok)
            'error' => $error       // message d’erreur (si échec)
        ]);
    }
}
?>