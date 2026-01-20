<?php

require_once __DIR__ . '/BaseController.php'; // Inclusion du contrôleur de base contenant les fonctions communes (authentification, rendu, CSRF, etc.)

/**
 * Contrôleur LogoController
 * Chargé de la gestion du logo du restaurant
 * Permet à l'administrateur de télécharger, mettre à jour et gérer le logo de son établissement
 */
class LogoController extends BaseController {

    /**
     * Constructeur
     * Initialise le contrôleur avec la connexion PDO
     * @param PDO $pdo Instance de connexion à la base de données
     */
    public function __construct($pdo) {
        parent::__construct($pdo);  // Appelle le constructeur du parent (BaseController)
    }

    /**
     * Page d'édition du logo
     * Permet à l'administrateur de modifier ou d'ajouter son logo
     * 
     * Processus:
     * 1. Vérifie que l'admin est connecté
     * 2. Traite le formulaire d'upload si un fichier est envoyé
     * 3. Gère le stockage physique du fichier
     * 4. Met à jour la base de données
     * 5. Supprime l'ancien logo si nécessaire
     * 6. Affiche le formulaire avec les messages de feedback
     * 
     * @return void
     */
    public function edit() {
        // Étape 1: Vérification de l'authentification
        // Si l'utilisateur n'est pas connecté, redirection vers login.php
        $this->requireLogin();

        // Étape 2: Initialisation des variables de retour
        // Ces variables seront passées à la vue pour afficher des messages à l'utilisateur
        $message = null;  // Message de succès
        $error = null;    // Message d'erreur

        // Étape 3: Récupération de l'ID de l'administrateur connecté
        // $_SESSION['admin_id'] est défini lors de la connexion dans AdminController::login()
        $admin_id = $_SESSION['admin_id'];

        // Étape 4: Vérification si un fichier a été uploadé
        // $_FILES['logo']['tmp_name'] contient le chemin temporaire du fichier s'il a été uploadé
        // !empty() vérifie que le fichier existe et n'est pas vide
        if (!empty($_FILES['logo']['tmp_name'])) {

            // --- CONFIGURATION DU DOSSIER DE DESTINATION ---
            
            // Définit le chemin absolu du dossier de destination
            // __DIR__ : répertoire où se trouve ce fichier (LogoController.php)
            // '/../../public/assets/logos/' : remonte de 2 niveaux, puis va dans public/assets/logos/
            $uploadDir = __DIR__ . '/../../public/assets/logos/';
            
            // Vérification et création du dossier si nécessaire
            // is_dir() : vérifie si le dossier existe
            // mkdir() : crée le dossier avec les permissions spécifiées
            // 0755 : permissions Unix (rwxr-xr-x) - propriétaire: tout, groupe et autres: lecture+exécution
            // true : crée les dossiers parents si nécessaire (récursif)
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            // --- TRAITEMENT DU NOM DE FICHIER ---
            
            // Récupération de l'extension du fichier original
            // pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION) extrait l'extension
            // Exemple: "logo.png" → "png", "mon-image.jpg" → "jpg"
            $ext = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);

            // Génération d'un nom de fichier unique pour éviter les collisions
            // Format: logo_IDADMIN_timestamp.extension
            // Exemple: logo_3_1730123456.png
            // - "logo_" : préfixe constant
            // - $admin_id : identifiant unique de l'admin
            // - time() : timestamp Unix actuel (secondes depuis 1970)
            // - "." . $ext : extension originale
            $fileName = "logo_{$admin_id}_" . time() . "." . $ext;

            // Construction du chemin complet de destination
            $targetFile = $uploadDir . $fileName;

            // Étape 5: Déplacement du fichier temporaire vers sa destination finale
            // move_uploaded_file() :
            // 1. Vérifie que le fichier a été uploadé via HTTP POST (sécurité)
            // 2. Déplace le fichier du dossier temporaire vers $targetFile
            if (move_uploaded_file($_FILES['logo']['tmp_name'], $targetFile)) {
                // SUCCÈS: Le fichier a été déplacé avec succès

                // --- GESTION DE L'ANCIEN LOGO ---
                
                // Récupération du nom de l'ancien logo dans la base de données
                // Préparation de la requête SQL pour éviter les injections
                $stmt = $this->pdo->prepare("SELECT filename FROM logos WHERE admin_id = ?");
                $stmt->execute([$admin_id]);  // Exécution avec l'ID de l'admin comme paramètre
                $oldLogo = $stmt->fetchColumn();  // Récupère la première colonne du résultat

                // Si un ancien logo existe ET que le fichier physique existe
                if ($oldLogo && file_exists($uploadDir . $oldLogo)) {
                    // Suppression physique du fichier ancien logo
                    // unlink() supprime le fichier du système de fichiers
                    unlink($uploadDir . $oldLogo);
                    // Note: On ne supprime que si le fichier existe pour éviter des erreurs
                }

                // --- SAUVEGARDE EN BASE DE DONNÉES ---
                
                // Requête UPSERT (INSERT ou UPDATE) pour gérer les deux cas:
                // - Premier logo: INSERT
                // - Changement de logo: UPDATE
                $stmt = $this->pdo->prepare("
                    INSERT INTO logos (admin_id, filename, uploaded_at)
                    VALUES (?, ?, NOW())  -- Valeurs à insérer
                    ON DUPLICATE KEY UPDATE  -- Si admin_id existe déjà (contrainte d'unicité)
                        filename = VALUES(filename),  -- Met à jour avec la nouvelle valeur
                        uploaded_at = NOW()          -- Met à jour la date
                ");
                
                // Exécution avec les paramètres:
                // 1. $admin_id : ID de l'administrateur
                // 2. $fileName : Nom du nouveau fichier
                $stmt->execute([$admin_id, $fileName]);

                // Étape 6: Message de succès
                $message = "Logo mis à jour avec succès !";

            } else {
                // ÉCHEC: Le déplacement du fichier a échoué
                // Causes possibles:
                // - Permissions insuffisantes sur le dossier
                // - Espace disque insuffisant
                // - Taille du fichier trop grande (dépassement de post_max_size/upload_max_filesize)
                $error = "Erreur lors du téléchargement du logo.";
            }
        }

        // Étape 7: Affichage de la vue
        // Rend la vue "edit-logo" avec les messages appropriés
        $this->render('admin/edit-logo', [
            'message' => $message,  // Message de succès (null si pas d'upload ou échec)
            'error' => $error       // Message d'erreur (null si succès ou pas d'upload)
        ]);
    }
}
?>