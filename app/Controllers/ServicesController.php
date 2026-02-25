<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../Models/OptionModel.php';

/**
 * Contrôleur de gestion des services, moyens de paiement et réseaux sociaux
 * Les données sont stockées sous forme de clé/valeur dans la table admin_options
 */
class ServicesController extends BaseController
{
    /** @var OptionModel Modèle pour lire/écrire les options admin */
    private $optionModel;

    /**
     * @param PDO $pdo Connexion à la base de données
     */
    public function __construct($pdo)
    {
        parent::__construct($pdo);
        $this->setScrollDelay(1500);
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
            'scroll_delay' => $this->scrollDelay, // Ajouté
            'anchor' => $_SESSION['anchor'] ?? null, // Ajouté
        ];
        unset($_SESSION['success_message'], $_SESSION['error_message'], $_SESSION['anchor']);

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

        // Récupérer l'ancre pour le scroll après soumission
        $anchor = $_POST['anchor'] ?? 'services';

        // Traitement des services, paiements, réseaux (comme avant)
        $services = [
            'service_sur_place'                => isset($_POST['service_sur_place']) ? '1' : '0',
            'service_a_emporter'               => isset($_POST['service_a_emporter']) ? '1' : '0',
            'service_livraison_ubereats'       => isset($_POST['service_livraison_ubereats']) ? '1' : '0',
            'service_livraison_etablissement'  => isset($_POST['service_livraison_etablissement']) ? '1' : '0',
            'service_wifi'                      => isset($_POST['service_wifi']) ? '1' : '0',
            'service_climatisation'             => isset($_POST['service_climatisation']) ? '1' : '0',
            'service_pmr'                       => isset($_POST['service_pmr']) ? '1' : '0',
        ];

        $payments = [
            'payment_visa' => isset($_POST['payment_visa']) ? '1' : '0',
            'payment_mastercard' => isset($_POST['payment_mastercard']) ? '1' : '0',
            'payment_cb' => isset($_POST['payment_cb']) ? '1' : '0',
            'payment_especes' => isset($_POST['payment_especes']) ? '1' : '0',
            'payment_cheques' => isset($_POST['payment_cheques']) ? '1' : '0',
        ];

        $socials = [
            'social_instagram' => trim($_POST['social_instagram'] ?? ''),
            'social_facebook' => trim($_POST['social_facebook'] ?? ''),
            'social_x' => trim($_POST['social_x'] ?? ''),
            'social_tiktok' => trim($_POST['social_tiktok'] ?? ''),
            'social_snapchat' => trim($_POST['social_snapchat'] ?? ''),
        ];

        $allOptions = array_merge($services, $payments, $socials);

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

        // Stocker l'ancre pour le scroll après redirection
        $_SESSION['anchor'] = $anchor;

        header('Location: ?page=edit-services');
        exit;
    }

    /**
     * Extrait les options de services depuis le tableau global des options
     *
     * @param array $options Tableau clé/valeur de toutes les options
     * @return array Services avec valeurs par défaut à '0'
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
     * Extrait les options de paiement depuis le tableau global des options
     *
     * @param array $options Tableau clé/valeur de toutes les options
     * @return array Paiements avec valeurs par défaut à '0'
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
     * Extrait les URLs de réseaux sociaux depuis le tableau global des options
     *
     * @param array $options Tableau clé/valeur de toutes les options
     * @return array Réseaux sociaux avec valeurs par défaut vides
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
