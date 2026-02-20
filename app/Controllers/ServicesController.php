<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../Models/OptionModel.php';

class ServicesController extends BaseController
{
    private $optionModel;

    public function __construct($pdo)
    {
        parent::__construct($pdo);
        $this->optionModel = new OptionModel($pdo);
    }

    /**
     * Affiche la page de gestion des services, paiements et réseaux
     */
    public function show()
    {
        $this->requireLogin();
        $admin_id = $_SESSION['admin_id'];

        // Récupérer toutes les options existantes
        $options = $this->optionModel->getAll($admin_id);

        // Organiser les options par catégorie pour la vue
        $data = [
            'services' => $this->extractServices($options),
            'payments' => $this->extractPayments($options),
            'socials' => $this->extractSocials($options),
            'csrf_token' => $this->getCsrfToken(),
            'success_message' => $_SESSION['success_message'] ?? null,
            'error_message' => $_SESSION['error_message'] ?? null,
        ];
        unset($_SESSION['success_message'], $_SESSION['error_message']);

        $this->render('admin/edit-services', $data);
    }

    /**
     * Enregistre tous les paramètres
     */
    public function save()
    {
        $this->requireLogin();
        $admin_id = $_SESSION['admin_id'];

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ?page=edit-services');
            exit;
        }

        if (!$this->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['error_message'] = "Token de sécurité invalide.";
            header('Location: ?page=edit-services');
            exit;
        }

        // Traitement des services
        // Services
        $services = [
            'service_sur_place'                => isset($_POST['service_sur_place']) ? '1' : '0',
            'service_a_emporter'               => isset($_POST['service_a_emporter']) ? '1' : '0',
            'service_livraison_ubereats'       => isset($_POST['service_livraison_ubereats']) ? '1' : '0',
            'service_livraison_etablissement'  => isset($_POST['service_livraison_etablissement']) ? '1' : '0',
            'service_wifi'                      => isset($_POST['service_wifi']) ? '1' : '0',
            'service_climatisation'             => isset($_POST['service_climatisation']) ? '1' : '0',
            'service_pmr'                       => isset($_POST['service_pmr']) ? '1' : '0',
        ];

        // Traitement des paiements (checkbox multiples)
        $payments = [
            'payment_visa' => isset($_POST['payment_visa']) ? '1' : '0',
            'payment_mastercard' => isset($_POST['payment_mastercard']) ? '1' : '0',
            'payment_cb' => isset($_POST['payment_cb']) ? '1' : '0',
            'payment_especes' => isset($_POST['payment_especes']) ? '1' : '0',
            'payment_cheques' => isset($_POST['payment_cheques']) ? '1' : '0',
        ];

        // Traitement des réseaux (champs texte)
        $socials = [
            'social_instagram' => trim($_POST['social_instagram'] ?? ''),
            'social_facebook' => trim($_POST['social_facebook'] ?? ''),
            'social_x' => trim($_POST['social_x'] ?? ''),
            'social_tiktok' => trim($_POST['social_tiktok'] ?? ''),
            'social_snapchat' => trim($_POST['social_snapchat'] ?? ''),
        ];

        // Fusionner toutes les options
        $allOptions = array_merge($services, $payments, $socials);

        // Sauvegarder chaque option
        $success = true;
        foreach ($allOptions as $key => $value) {
            if (!$this->optionModel->set($admin_id, $key, $value)) {
                $success = false;
            }
        }

        if ($success) {
            $_SESSION['success_message'] = "Paramètres enregistrés avec succès.";
        } else {
            $_SESSION['error_message'] = "Erreur lors de l'enregistrement de certains paramètres.";
        }

        header('Location: ?page=edit-services');
        exit;
    }

    /**
     * Extrait les options de services
     */
    private function extractServices($options)
{
    $defaults = [
        'service_sur_place'                => '0',
        'service_a_emporter'               => '0',
        'service_livraison_ubereats'       => '0',
        'service_livraison_etablissement'  => '0',
        'service_wifi'                      => '0',
        'service_climatisation'             => '0',
        'service_pmr'                       => '0',
    ];
    return array_merge($defaults, array_intersect_key($options, $defaults));
}

    /**
     * Extrait les options de paiement
     */
    private function extractPayments($options)
    {
        $defaults = [
            'payment_visa' => '0',
            'payment_mastercard' => '0',
            'payment_cb' => '0',
            'payment_especes' => '0',
            'payment_cheques' => '0',
        ];
        return array_merge($defaults, array_intersect_key($options, $defaults));
    }

    /**
     * Extrait les options de réseaux sociaux
     */
    private function extractSocials($options)
    {
        $defaults = [
            'social_instagram' => '',
            'social_facebook' => '',
            'social_x' => '',
            'social_tiktok' => '',
            'social_snapchat' => '',
        ];
        return array_merge($defaults, array_intersect_key($options, $defaults));
    }
}
