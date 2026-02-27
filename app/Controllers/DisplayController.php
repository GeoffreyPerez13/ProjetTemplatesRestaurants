<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../Models/Restaurant.php';
require_once __DIR__ . '/../Models/Category.php';
require_once __DIR__ . '/../Models/Dish.php';
require_once __DIR__ . '/../Models/Contact.php';
require_once __DIR__ . '/../Models/Admin.php';
require_once __DIR__ . '/../Models/OptionModel.php';

/**
 * Contrôleur de la vitrine publique du restaurant
 * Charge toutes les données nécessaires (menu, contact, services, template)
 * et rend la page publique accessible via le slug du restaurant
 */
class DisplayController extends BaseController
{
    /**
     * @param PDO $pdo Connexion à la base de données
     */
    public function __construct($pdo)
    {
        parent::__construct($pdo);
    }

    /**
     * Affiche la page vitrine publique d'un restaurant
     * Gère aussi le mode maintenance et la prévisualisation de templates pour les admins
     *
     * @param string|null $slug Slug unique du restaurant (ex: 'mon-restaurant')
     */
    public function show($slug = null)
    {
        if (empty($slug)) {
            http_response_code(404);
            require __DIR__ . '/../Views/errors/404.php';
            return;
        }

        $restaurantModel = new Restaurant($this->pdo);
        $restaurant = $restaurantModel->findBySlug($slug);

        if (!$restaurant) {
            http_response_code(404);
            require __DIR__ . '/../Views/errors/404.php';
            return;
        }

        // Récupérer l'admin associé à ce restaurant
        $adminModel = new Admin($this->pdo);
        $admin = $adminModel->findByRestaurantId($restaurant->id);

        if (!$admin) {
            http_response_code(500);
            require __DIR__ . '/../Views/errors/404.php';
            return;
        }

        $adminId = $admin->id;

        // Récupérer les données additionnelles
        $logo = $restaurantModel->getLogo($adminId);
        $banner = $restaurantModel->getBanner($adminId);
        $carteMode = $admin->carte_mode;
        $contact = $restaurantModel->getContact($adminId);
        $categories = [];
        $cardImages = [];

        if ($carteMode === 'editable') {
            $categoryModel = new Category($this->pdo);
            $dishModel = new Dish($this->pdo);
            $categories = $categoryModel->getAllByAdmin($adminId);
            foreach ($categories as &$cat) {
                $cat['plats'] = $dishModel->getAllByCategory($cat['id']);
                if (!empty($cat['image'])) {
                    $cat['image_url'] = '/' . $cat['image'];
                }
                foreach ($cat['plats'] as &$plat) {
                    if (!empty($plat['image'])) {
                        $plat['image_url'] = '/' . $plat['image'];
                    }
                }
            }
        } else {
            $cardImages = $restaurantModel->getCardImages($adminId);
        }

        // Récupérer les options (services, paiements, réseaux)
        $optionModel = new OptionModel($this->pdo);
        $options = $optionModel->getAll($adminId);

        // Services
        $services = [
            'service_sur_place'                => $options['service_sur_place'] ?? '0',
            'service_a_emporter'               => $options['service_a_emporter'] ?? '0',
            'service_livraison_ubereats'       => $options['service_livraison_ubereats'] ?? '0',
            'service_livraison_etablissement'  => $options['service_livraison_etablissement'] ?? '0',
            'service_wifi'                      => $options['service_wifi'] ?? '0',
            'service_climatisation'             => $options['service_climatisation'] ?? '0',
            'service_pmr'                       => $options['service_pmr'] ?? '0',
        ];

        // Paiements
        $payments = [
            'payment_visa'       => $options['payment_visa'] ?? '0',
            'payment_mastercard' => $options['payment_mastercard'] ?? '0',
            'payment_cb'         => $options['payment_cb'] ?? '0',
            'payment_especes'    => $options['payment_especes'] ?? '0',
            'payment_cheques'    => $options['payment_cheques'] ?? '0',
        ];

        // Réseaux sociaux
        $socials = [
            'social_instagram' => $options['social_instagram'] ?? '',
            'social_facebook'  => $options['social_facebook'] ?? '',
            'social_x'         => $options['social_x'] ?? '',
            'social_tiktok'    => $options['social_tiktok'] ?? '',
            'social_snapchat'  => $options['social_snapchat'] ?? '',
        ];

        // Formater la date de dernière mise à jour
        $lastUpdated = null;
        if ($restaurant->updated_at) {
            $date = new DateTime($restaurant->updated_at);
            $lastUpdated = $date->format('d/m/Y à H:i');
        }

        // Vérifier si le site est en ligne
        $siteOnline = $restaurantModel->isSiteOnline($adminId);

        // Mode preview : l'admin connecté propriétaire du restaurant peut voir son site même en maintenance
        $isPreview = false;
        if (!$siteOnline && isset($_SESSION['admin_logged']) && $_SESSION['admin_logged'] === true) {
            if (isset($_SESSION['admin_id']) && $_SESSION['admin_id'] == $adminId) {
                $siteOnline = true;
                $isPreview = true;
            }
        }

        // Récupérer la palette et le layout choisis par l'admin
        $paletteName = $optionModel->get($adminId, 'site_palette') ?: ($optionModel->get($adminId, 'site_template') ?: 'classic');
        $layoutName  = $optionModel->get($adminId, 'site_layout') ?: 'standard';

        // Récupérer les options Google Reviews
        $googlePlaceId = $optionModel->get($adminId, 'google_place_id');
        $googleApiKey = $optionModel->get($adminId, 'google_api_key');
        $googleReviewsEnabled = $optionModel->get($adminId, 'google_reviews_enabled') === '1';

        $allowedPalettes = ['classic', 'modern', 'elegant', 'nature', 'rose', 'bistro', 'ocean'];
        $allowedLayouts  = ['standard', 'bistro', 'ocean'];

        // Permettre la prévisualisation via GET (admin connecté uniquement)
        if (isset($_SESSION['admin_logged']) && $_SESSION['admin_logged'] === true) {
            if (!empty($_GET['preview_palette']) && in_array($_GET['preview_palette'], $allowedPalettes)) {
                $paletteName = $_GET['preview_palette'];
            }
            if (!empty($_GET['preview_layout']) && in_array($_GET['preview_layout'], $allowedLayouts)) {
                $layoutName = $_GET['preview_layout'];
            }
            // Rétrocompatibilité ancien paramètre preview_template
            if (!empty($_GET['preview_template']) && in_array($_GET['preview_template'], $allowedPalettes)) {
                $paletteName = $_GET['preview_template'];
            }
        }

        $this->render('display', [
            'restaurant'   => $restaurant,
            'logo'         => $logo,
            'banner'       => $banner,
            'carteMode'    => $carteMode,
            'categories'   => $categories,
            'cardImages'   => $cardImages,
            'contact'      => $contact,
            'siteOnline'   => $siteOnline,
            'isPreview'    => $isPreview,
            'lastUpdated'  => $lastUpdated,
            'services'     => $services,
            'payments'     => $payments,
            'socials'      => $socials,
            'templateName' => $paletteName,
            'layoutName'   => $layoutName,
            'googlePlaceId' => $googlePlaceId,
            'googleApiKey' => $googleApiKey,
            'googleReviewsEnabled' => $googleReviewsEnabled
        ]);
    }
}
