<?php

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../Models/Restaurant.php';
require_once __DIR__ . '/../Models/Category.php';
require_once __DIR__ . '/../Models/Dish.php';
require_once __DIR__ . '/../Models/Contact.php';
require_once __DIR__ . '/../Models/Admin.php';

class DisplayController extends BaseController
{
    public function __construct($pdo)
    {
        parent::__construct($pdo);
    }

    public function show($slug = null)
    {
        if (empty($slug)) {
            http_response_code(404);
            echo "Restaurant introuvable";
            return;
        }

        $restaurantModel = new Restaurant($this->pdo);
        $restaurant = $restaurantModel->findBySlug($slug);

        if (!$restaurant) {
            http_response_code(404);
            echo "Restaurant introuvable";
            return;
        }

        // Récupérer l'admin associé à ce restaurant
        $adminModel = new Admin($this->pdo);
        $admin = $adminModel->findByRestaurantId($restaurant->id); // À créer dans Admin.php

        if (!$admin) {
            // Pas d'admin associé, on ne peut pas continuer
            http_response_code(500);
            echo "Erreur de configuration du restaurant";
            return;
        }

        $adminId = $admin->id;

        // Récupérer les données additionnelles
        $logo = $restaurantModel->getLogo($adminId);
        $banner = $restaurantModel->getBanner($adminId);
        $carteMode = $admin->carte_mode; // ou via AdminModel
        $contact = $restaurantModel->getContact($adminId);
        $categories = [];
        $cardImages = [];

        if ($carteMode === 'editable') {
            $categoryModel = new Category($this->pdo);
            $dishModel = new Dish($this->pdo);
            $categories = $categoryModel->getAllByAdmin($adminId); // ou getByRestaurant
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

        // Vérifier si le site est en ligne
        $siteOnline = $restaurantModel->isSiteOnline($adminId);

        $this->render('display', [
            'restaurant' => $restaurant,
            'logo' => $logo,
            'banner' => $banner,
            'carteMode' => $carteMode,
            'categories' => $categories,
            'cardImages' => $cardImages,
            'contact' => $contact,
            'siteOnline' => $siteOnline
        ]);
    }
}
