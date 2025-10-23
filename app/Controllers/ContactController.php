<?php

require_once __DIR__ . '/BaseController.php'; // Inclusion du contrôleur de base (hérite des fonctions communes : sécurité, rendu de vue, etc.)
require_once __DIR__ . '/../Models/Contact.php'; // Inclusion du modèle Contact (gère les informations de contact dans la base de données)

// Définition du contrôleur ContactController, chargé de la gestion des informations de contact du restaurant
class ContactController extends BaseController {

    // Constructeur : récupère la connexion PDO et la transmet au contrôleur parent
    public function __construct($pdo) {
        parent::__construct($pdo);
    }

    // Méthode principale : page d’édition des informations de contact
    public function edit() {
        // Vérifie que l’administrateur est bien connecté avant d’autoriser l’accès
        $this->requireLogin();

        // Récupère l’identifiant de l’administrateur depuis la session
        $admin_id = $_SESSION['admin_id'];

        // Instancie le modèle Contact pour interagir avec la base de données
        $contactModel = new Contact($this->pdo);

        // Vérifie qu'une ligne de contact existe déjà pour cet admin
        // Si ce n’est pas le cas, la méthode createIfNotExist() la crée automatiquement
        $contactModel->createIfNotExist($admin_id);

        // Variable de message pour afficher une confirmation après mise à jour
        $message = null;

        // Si le formulaire est soumis (méthode POST)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            // Met à jour les informations de contact dans la base
            // On utilise l’opérateur ?? pour éviter les erreurs si une clé POST n’existe pas
            $contactModel->update(
                $admin_id,
                $_POST['telephone'] ?? '',  // Numéro de téléphone du restaurant
                $_POST['email'] ?? '',      // Adresse e-mail du restaurant
                $_POST['adresse'] ?? '',    // Adresse physique du restaurant
                $_POST['horaires'] ?? ''    // Horaires d’ouverture
            );

            // Message de confirmation à afficher dans la vue
            $message = "Contact mis à jour.";
        }

        // Récupère les données de contact actuelles de l’administrateur pour préremplir le formulaire
        $contact = $contactModel->getByAdmin($admin_id);

        // Rend la vue "edit-contact" avec :
        // - les données de contact existantes
        // - le message de confirmation (le cas échéant)
        $this->render('admin/edit-contact', [
            'contact' => $contact,
            'message' => $message
        ]);
    }
}
?>