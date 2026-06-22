<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../Models/Restaurant.php';
require_once __DIR__ . '/../Models/Category.php';
require_once __DIR__ . '/../Models/Dish.php';
require_once __DIR__ . '/../Models/Contact.php';
require_once __DIR__ . '/../Models/Admin.php';
require_once __DIR__ . '/../Models/OptionModel.php';
require_once __DIR__ . '/../Models/PremiumFeature.php';
require_once __DIR__ . '/../Models/GoogleReviews.php';
require_once __DIR__ . '/../Models/SiteVisit.php';
require_once __DIR__ . '/../Models/DailyMenu.php';

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

        // Détecter si le visiteur connecté est SUPER_ADMIN
        $viewerIsSuperAdmin = false;
        if (isset($_SESSION['admin_logged']) && $_SESSION['admin_logged'] === true && isset($_SESSION['admin_id'])) {
            $loggedAdmin = $adminModel->findById($_SESSION['admin_id']);
            $viewerIsSuperAdmin = $loggedAdmin && $loggedAdmin->role === 'SUPER_ADMIN';
        }

        // Vérifier l'abonnement Basique (sauf pour SUPER_ADMIN et démo)
        $hasActiveBasique = false;
        if ($admin->role !== 'SUPER_ADMIN' && !$viewerIsSuperAdmin && !isset($_SESSION['demo_mode'])) {
            try {
                $stmt = $this->pdo->prepare(
                    "SELECT status FROM client_subscriptions WHERE admin_id = ? LIMIT 1"
                );
                $stmt->execute([$adminId]);
                $sub = $stmt->fetch(PDO::FETCH_ASSOC);
                $hasActiveBasique = $sub && $sub['status'] === 'active';
            } catch (Exception $e) {
                // Table absente ou erreur : on considère pas d'abonnement
                $hasActiveBasique = false;
            }
        } else {
            // SUPER_ADMIN et démo : toujours actif
            $hasActiveBasique = true;
        }

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

        // Récupérer les menus du jour actifs
        $dailyMenuModel = new DailyMenu($this->pdo);
        $dailyMenus = $dailyMenuModel->getActiveByAdmin($adminId);

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
            'service_animaux'                   => $options['service_animaux'] ?? '',
        ];

        // Paiements
        $payments = [
            'payment_visa'       => $options['payment_visa'] ?? '0',
            'payment_mastercard' => $options['payment_mastercard'] ?? '0',
            'payment_cb'         => $options['payment_cb'] ?? '0',
            'payment_especes'    => $options['payment_especes'] ?? '0',
            'payment_cheques'    => $options['payment_cheques'] ?? '0',
            'payment_tickets_restaurant' => $options['payment_tickets_restaurant'] ?? '0',
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

        // Récupérer les options Google Reviews
        $googlePlaceId = $optionModel->get($adminId, 'google_place_id');
        $googleApiKey = $optionModel->get($adminId, 'google_api_key');
        $googleReviewsEnabled = $optionModel->get($adminId, 'google_reviews_enabled') === '1';

        // Vérifier les dates de fermeture exceptionnelles
        $closureDates = [];
        $todayClosureDate = null;
        try {
            $stmt = $this->pdo->prepare("
                SELECT option_value 
                FROM admin_options 
                WHERE admin_id = ? AND option_name = 'closure_dates'
            ");
            $stmt->execute([$adminId]);
            $closureDatesValue = $stmt->fetchColumn();
            if ($closureDatesValue) {
                $closureDates = json_decode($closureDatesValue, true) ?: [];
                
                // Vérifier si aujourd'hui est une date de fermeture
                $today = date('Y-m-d');
                if (in_array($today, $closureDates)) {
                    $todayClosureDate = $today;
                }
            }
        } catch (Exception $e) {
            error_log("Erreur vérification dates fermeture: " . $e->getMessage());
            $closureDates = [];
        }

        // Gérer les avis Google
        $googleReviewsData = null;
        if ($googleReviewsEnabled && $googlePlaceId && $googleApiKey) {
            try {
                // Vérifier si la fonctionnalité premium est activée
                $premiumFeature = new PremiumFeature($this->pdo);
                
                if ($premiumFeature->isEnabled($adminId, 'google_reviews')) {
                    // Récupérer les avis
                    $googleReviews = new GoogleReviews($this->pdo, $googleApiKey);
                    $data = $googleReviews->getReviews($googlePlaceId, 5);
                    
                    if ($data) {
                        $googleReviewsData = [
                            'restaurant_info' => [
                                'name' => $data['name'] ?? '',
                                'rating' => $data['rating'] ?? 0,
                                'total_reviews' => count($data['reviews'] ?? [])
                            ],
                            'reviews' => $data['reviews'] ?? []
                        ];
                    }
                }
            } catch (Exception $e) {
                error_log("Erreur récupération avis Google: " . $e->getMessage());
                $googleReviewsData = null;
            }
        }

        // SUPER_ADMIN : le site est toujours considéré en ligne
        if ($viewerIsSuperAdmin) {
            $siteOnline = true;
        }

        // Si pas d'abonnement Basique actif, forcer le site hors ligne
        if (!$hasActiveBasique && !$viewerIsSuperAdmin) {
            $siteOnline = false;
        }

        // Mode preview : l'admin connecté propriétaire peut voir son site même en maintenance
        $isPreview = false;
        if (!$siteOnline && isset($_SESSION['admin_logged']) && $_SESSION['admin_logged'] === true) {
            $isOwner = isset($_SESSION['admin_id']) && $_SESSION['admin_id'] == $adminId;
            $isDemo = !empty($_SESSION['demo_mode']);

            if ($isOwner || $isDemo) {
                $siteOnline = true;
                $isPreview = true;
            }
        }

        // Récupérer la palette et le layout choisis par l'admin
        $paletteName = $optionModel->get($adminId, 'site_palette') ?: ($optionModel->get($adminId, 'site_template') ?: 'classic');
        $layoutName  = $optionModel->get($adminId, 'site_layout') ?: 'standard';

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

        // Vérifier si les réservations en ligne sont activées
        $bookingEnabled = false;
        $bookingSettings = [];
        try {
            $premiumFeature = new PremiumFeature($this->pdo);
            if ($premiumFeature->isEnabled($adminId, 'online_booking')) {
                $bookingOptionEnabled = $optionModel->get($adminId, 'booking_enabled');
                if ($bookingOptionEnabled !== '0') {
                    $bookingEnabled = true;
                    $bookingSettings = [
                        'min_party'     => max(1, (int)($optionModel->get($adminId, 'booking_min_party') ?: 1)),
                        'max_party'     => max(1, (int)($optionModel->get($adminId, 'booking_max_party') ?: 10)),
                        'advance_days'  => max(1, (int)($optionModel->get($adminId, 'booking_advance_days') ?: 30)),
                        'message'       => $optionModel->get($adminId, 'booking_message') ?: '',
                    ];
                }
            }
        } catch (Exception $e) {
            error_log("Erreur vérification réservations: " . $e->getMessage());
        }

        // Tracker la visite (uniquement si le site est en ligne et pas un preview admin)
        if ($siteOnline && !$isPreview) {
            try {
                $siteVisit = new SiteVisit($this->pdo);
                $siteVisit->track(
                    $adminId,
                    $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
                    $_SERVER['HTTP_USER_AGENT'] ?? '',
                    $_SERVER['HTTP_REFERER'] ?? '',
                    '/' . $slug
                );
            } catch (Exception $e) {
                // Silencieux — le tracking ne doit jamais bloquer l'affichage
                error_log('[SiteVisit] Tracking error: ' . $e->getMessage());
            }
        }

        $this->render('display', [
            'restaurant'   => $restaurant,
            'adminId'      => $adminId,
            'logo'         => $logo,
            'banner'       => $banner,
            'carteMode'    => $carteMode,
            'categories'   => $categories,
            'cardImages'   => $cardImages,
            'dailyMenus'   => $dailyMenus,
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
            'googleReviewsEnabled' => $googleReviewsEnabled,
            'todayClosureDate' => $todayClosureDate,
            'googleReviewsData' => $googleReviewsData,
            'bookingEnabled'    => $bookingEnabled,
            'bookingMinParty'   => $bookingSettings['min_party'] ?? 1,
            'bookingMaxParty'   => $bookingSettings['max_party'] ?? 10,
            'bookingAdvanceDays' => $bookingSettings['advance_days'] ?? 30,
            'bookingMessage'    => $bookingSettings['message'] ?? '',
        ]);
    }
}
