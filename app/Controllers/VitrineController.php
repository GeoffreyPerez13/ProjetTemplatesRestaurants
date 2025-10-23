<?php

require_once __DIR__ . '/BaseController.php'; // Inclusion du contrôleur de base (pour utiliser les fonctions communes : rendu de vue, session, etc.)

// Inclusion des modèles nécessaires pour afficher la vitrine
require_once __DIR__ . '/../Models/Restaurant.php'; // Infos sur le restaurant
require_once __DIR__ . '/../Models/Category.php';   // Catégories de plats
require_once __DIR__ . '/../Models/Dish.php';       // Plats
require_once __DIR__ . '/../Models/Contact.php';    // Informations de contact

// Définition du contrôleur VitrineController : gère l’affichage public de la vitrine du restaurant
class VitrineController extends BaseController
{
    // Constructeur : transmet la connexion PDO au parent
    public function __construct($pdo) {
        parent::__construct($pdo);
    }

    // Méthode show : affiche la page publique d’un restaurant
    // Paramètre : $slug = identifiant unique dans l’URL
    public function show($slug = null)
    {
        // Si aucun slug n’est fourni, renvoie une erreur 404
        if (empty($slug)) {
            http_response_code(404);   // Code HTTP "non trouvé"
            echo "Restaurant introuvable"; // Message pour l’utilisateur
            return;                     // Stoppe l’exécution
        }

        // Instancie le modèle Restaurant et recherche le restaurant correspondant au slug
        $restaurantModel = new Restaurant($this->pdo);
        $restaurant = $restaurantModel->findBySlug($slug);

        // Si aucun restaurant n’est trouvé, renvoie également une 404
        if (!$restaurant) {
            http_response_code(404);
            echo "Restaurant introuvable";
            return;
        }

        // Récupération des catégories de plats du restaurant
        $categoryModel = new Category($this->pdo);
        $categories = $categoryModel->getByRestaurant($restaurant->id);

        // Récupération des plats pour chaque catégorie
        $dishModel = new Dish($this->pdo);
        foreach ($categories as &$cat) {
            // Ajoute un attribut "dishes" à chaque catégorie contenant les plats correspondants
            $cat->dishes = $dishModel->getByCategory($cat->id);
        }

        // Récupération des informations de contact du restaurant
        $contactModel = new Contact($this->pdo);
        $contact = $contactModel->getByRestaurant($restaurant->id);

        // Rend la vue "vitrine/show" en passant toutes les données nécessaires
        // - $restaurant : infos générales du restaurant
        // - $categories : catégories + plats
        // - $contact : informations de contact
        $this->render('vitrine/show', [
            'restaurant' => $restaurant,
            'categories' => $categories,
            'contact' => $contact
        ]);
    }
}