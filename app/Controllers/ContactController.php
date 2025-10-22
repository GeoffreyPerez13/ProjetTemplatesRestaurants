<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../Models/Contact.php';

class ContactController extends BaseController {

    public function __construct($pdo) {
        parent::__construct($pdo);
    }

    public function edit() {
        $this->requireLogin();
        $admin_id = $_SESSION['admin_id'];

        $contactModel = new Contact($this->pdo);
        $contactModel->createIfNotExist($admin_id); // s’assure qu’il y a une ligne

        $message = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $contactModel->update(
                $admin_id,
                $_POST['telephone'] ?? '',
                $_POST['email'] ?? '',
                $_POST['adresse'] ?? '',
                $_POST['horaires'] ?? ''
            );
            $message = "Contact mis à jour.";
        }

        $contact = $contactModel->getByAdmin($admin_id);
        $this->render('admin/edit-contact', [
            'contact' => $contact,
            'message' => $message
        ]);
    }
}
?>
