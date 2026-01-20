<?php

require_once __DIR__ . '/BaseController.php';     // Inclusion du contrôleur de base (hérite des fonctions communes : sécurité, rendu de vue, etc.)
require_once __DIR__ . '/../Models/Contact.php';  // Inclusion du modèle Contact (gère les informations de contact dans la base de données)

/**
 * Contrôleur ContactController
 * Chargé de la gestion des informations de contact du restaurant
 * Permet de modifier et afficher les coordonnées du restaurant
 */
class ContactController extends BaseController {

    /**
     * Constructeur
     * Initialise le contrôleur avec la connexion PDO
     * @param PDO $pdo Instance de connexion à la base de données
     */
    public function __construct($pdo) {
        parent::__construct($pdo);  // Appelle le constructeur du parent (BaseController)
    }

    /**
     * Page d'édition des informations de contact
     * Affiche et traite le formulaire de modification des coordonnées du restaurant
     * 
     * Processus:
     * 1. Vérifie que l'admin est connecté
     * 2. Vérifie/crée l'entrée contact pour cet admin
     * 3. Traite le formulaire de mise à jour (POST)
     * 4. Affiche le formulaire avec les données actuelles
     * 
     * @return void
     */
    public function edit() {
        // Étape 1: Vérification de l'authentification
        // Si l'utilisateur n'est pas connecté, redirection vers login.php
        $this->requireLogin();

        // Étape 2: Récupération de l'ID de l'administrateur connecté
        // $_SESSION['admin_id'] est défini lors de la connexion dans AdminController::login()
        $admin_id = $_SESSION['admin_id'];

        // Étape 3: Instanciation du modèle Contact
        // Ce modèle gère les opérations CRUD sur la table 'contacts'
        $contactModel = new Contact($this->pdo);

        // Étape 4: Vérification et création si nécessaire
        // Vérifie qu'une ligne de contact existe déjà pour cet admin
        // Si ce n'est pas le cas, la méthode createIfNotExist() la crée automatiquement
        // avec des valeurs par défaut (chaînes vides)
        $contactModel->createIfNotExist($admin_id);

        // Étape 5: Initialisation de la variable de message
        // Utilisée pour afficher une confirmation après une mise à jour réussie
        $message = null;

        // Étape 6: Traitement du formulaire (méthode POST)
        // Vérifie si la requête est de type POST (formulaire soumis)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Étape 6a: Mise à jour des informations de contact dans la base
            // Appelle la méthode update() du modèle avec les données du formulaire
            // L'opérateur ?? (null coalescing) fournit une valeur par défaut si la clé n'existe pas
            $contactModel->update(
                $admin_id,                      // ID de l'admin (clé étrangère)
                $_POST['telephone'] ?? '',      // Numéro de téléphone du restaurant
                $_POST['email'] ?? '',          // Adresse e-mail du restaurant
                $_POST['adresse'] ?? '',        // Adresse physique du restaurant (rue, ville, code postal)
                $_POST['horaires'] ?? ''        // Horaires d'ouverture (ex: "Lun-Ven: 9h-18h")
            );

            // Étape 6b: Définition du message de confirmation
            // Ce message sera affiché dans la vue pour informer l'utilisateur du succès
            $message = "Contact mis à jour.";
        }

        // Étape 7: Récupération des données de contact actuelles
        // Récupère les données de contact de l'administrateur pour préremplir le formulaire
        // Si aucune donnée n'existe, retourne un tableau avec des valeurs par défaut
        $contact = $contactModel->getByAdmin($admin_id);

        // Étape 8: Affichage de la vue
        // Rend la vue "edit-contact" avec les données nécessaires
        $this->render('admin/edit-contact', [
            'contact' => $contact,   // Données de contact pour préremplissage
            'message' => $message    // Message de confirmation (null si pas de mise à jour)
        ]);
    }
}
?>