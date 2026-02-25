<?php

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../Models/Contact.php';

/**
 * Contrôleur de gestion des informations de contact du restaurant
 * Permet à l'admin de modifier téléphone, email, adresse et horaires
 */
class ContactController extends BaseController
{
    /**
     * @param PDO $pdo Connexion à la base de données
     */
    public function __construct($pdo)
    {
        parent::__construct($pdo);
        $this->setScrollDelay(1500);
    }

    /**
     * Page d'édition du formulaire de contact
     */
    public function edit()
    {
        // 1. Vérification de connexion
        $this->requireLogin();
        $admin_id = $_SESSION['admin_id'];

        // 2. Initialisation du modèle
        $contactModel = new Contact($this->pdo);
        $contactModel->createIfNotExist($admin_id);

        // 3. Traitement du formulaire POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Récupérer l'ancre du formulaire
            $anchor = $_POST['anchor'] ?? 'edit-contact-form';
            
            // Validation des données
            $telephone = trim($_POST['telephone'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $adresse = trim($_POST['adresse'] ?? '');
            $horaires = trim($_POST['horaires'] ?? '');

            // Validation basique
            if (empty($telephone) || empty($email) || empty($adresse)) {
                $this->addErrorMessage("Veuillez remplir tous les champs obligatoires.", $anchor);
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->addErrorMessage("Veuillez saisir une adresse email valide.", $anchor);
            } else {
                // Mise à jour
                if ($contactModel->update($admin_id, $telephone, $email, $adresse, $horaires)) {
                    $this->addSuccessMessage("Contact mis à jour avec succès.", $anchor);
                } else {
                    $this->addErrorMessage("Erreur lors de la mise à jour du contact.", $anchor);
                }
            }

            // Redirection avec l'ancre pour éviter la soumission multiple
            $redirectUrl = '?page=edit-contact&anchor=' . urlencode($anchor);
            header('Location: ' . $redirectUrl);
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