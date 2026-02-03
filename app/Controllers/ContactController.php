<?php

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../Models/Contact.php';

class ContactController extends BaseController {

    public function __construct($pdo) {
        parent::__construct($pdo);
    }

    /**
     * Page d'édition du formulaire de ocontact
     */
    public function edit() {
        // 1. Vérification de connexion
        $this->requireLogin();
        $admin_id = $_SESSION['admin_id'];
        
        // 2. Initialisation du modèle
        $contactModel = new Contact($this->pdo);
        $contactModel->createIfNotExist($admin_id);
        
        // 3. Traitement du formulaire POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validation des données
            $telephone = trim($_POST['telephone'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $adresse = trim($_POST['adresse'] ?? '');
            $horaires = trim($_POST['horaires'] ?? '');
            
            // Validation basique
            if (empty($telephone) || empty($email) || empty($adresse)) {
                // Utilisation de addErrorMessage() pour la cohérence avec CardController
                $this->addErrorMessage("Veuillez remplir tous les champs obligatoires.", 'edit-contact-form');
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->addErrorMessage("Veuillez saisir une adresse email valide.", 'edit-contact-form');
            } else {
                // Mise à jour
                if ($contactModel->update($admin_id, $telephone, $email, $adresse, $horaires)) {
                    // Utilisation de addSuccessMessage() pour la cohérence avec CardController
                    $this->addSuccessMessage("Contact mis à jour avec succès.", 'edit-contact-form');
                } else {
                    $this->addErrorMessage("Erreur lors de la mise à jour du contact.", 'edit-contact-form');
                }
            }
            
            // Redirection pour éviter la soumission multiple
            header('Location: ?page=edit-contact');
            exit;
        }
        
        // 4. Récupération des messages flash
        $messages = $this->getFlashMessages();
        
        // 5. Récupération des données de contact
        $contact = $contactModel->getByAdmin($admin_id);
        
        // 6. Préparation des données pour la vue
        $data = [
            'contact' => $contact,
            'success_message' => $messages['success_message'] ?? null,
            'error_message' => $messages['error_message'] ?? null,
            'scroll_delay' => $messages['scroll_delay'] ?? $this->scrollDelay,
            'anchor' => $messages['anchor'] ?? null
        ];
        
        // 7. Affichage de la vue
        $this->render('admin/edit-contact', $data);
    }
}