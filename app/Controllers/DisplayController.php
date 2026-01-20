<?php

require_once __DIR__ . '/BaseController.php'; // Inclusion du contrôleur de base (pour utiliser les fonctions communes : rendu de vue, session, etc.)

// Inclusion des modèles nécessaires pour afficher la vitrine
require_once __DIR__ . '/../Models/Restaurant.php'; // Infos sur le restaurant (nom, description, slug, etc.)
require_once __DIR__ . '/../Models/Category.php';   // Catégories de plats (entrées, plats principaux, desserts)
require_once __DIR__ . '/../Models/Dish.php';       // Plats individuels (avec prix, description, image)
require_once __DIR__ . '/../Models/Contact.php';    // Informations de contact (téléphone, email, adresse, horaires)

/**
 * Contrôleur DisplayController
 * Gère l'affichage public de la vitrine du restaurant
 * C'est le contrôleur qui sert les pages publiques accessibles aux clients
 */
class DisplayController extends BaseController
{
    /**
     * Constructeur
     * Initialise le contrôleur avec la connexion PDO
     * @param PDO $pdo Instance de connexion à la base de données
     */
    public function __construct($pdo) {
        parent::__construct($pdo);  // Appelle le constructeur parent (BaseController)
    }

    /**
     * Affiche la page publique d'un restaurant
     * Méthode principale qui génère la vitrine complète du restaurant
     * 
     * Processus:
     * 1. Vérifie qu'un slug est fourni
     * 2. Recherche le restaurant correspondant au slug
     * 3. Récupère les catégories de plats
     * 4. Récupère les plats pour chaque catégorie
     * 5. Récupère les informations de contact
     * 6. Affiche la vue avec toutes les données
     * 
     * @param string|null $slug Identifiant unique du restaurant dans l'URL
     *                         Exemple: "mon-restaurant-paris" dans "mon-site.com/mon-restaurant-paris"
     * @return void
     */
    public function show($slug = null)
    {
        // Étape 1: Vérification de la présence d'un slug
        // Le slug est l'identifiant unique du restaurant dans l'URL
        if (empty($slug)) {
            // Si aucun slug n'est fourni, renvoie une erreur 404
            http_response_code(404);   // Envoie le code HTTP "404 Not Found" au navigateur
            echo "Restaurant introuvable"; // Message simple affiché à l'utilisateur
            return;                     // Stoppe l'exécution de la méthode
        }

        // Étape 2: Recherche du restaurant par son slug
        // Le slug est généralement généré à partir du nom du restaurant (ex: "le-petit-bistro" pour "Le Petit Bistro")
        $restaurantModel = new Restaurant($this->pdo);  // Instance du modèle Restaurant
        $restaurant = $restaurantModel->findBySlug($slug);  // Recherche dans la base

        // Étape 3: Vérification que le restaurant existe
        // $restaurant sera false ou null si aucun restaurant n'est trouvé avec ce slug
        if (!$restaurant) {
            // Restaurant non trouvé : erreur 404
            http_response_code(404);
            echo "Restaurant introuvable";
            return;
        }

        // Étape 4: Récupération des catégories de plats du restaurant
        // Les catégories organisent les plats (ex: "Entrées", "Plats principaux", "Desserts")
        $categoryModel = new Category($this->pdo);
        
        // getByRestaurant() retourne un tableau d'objets ou d'arrays représentant les catégories
        // Chaque catégorie a: id, name, restaurant_id, image (optionnel)
        $categories = $categoryModel->getByRestaurant($restaurant->id);

        // Étape 5: Récupération des plats pour chaque catégorie
        $dishModel = new Dish($this->pdo);
        
        // Parcours de chaque catégorie par référence (&$cat) pour la modifier directement
        foreach ($categories as &$cat) {
            // Pour chaque catégorie, on récupère tous ses plats
            // getByCategory() retourne un tableau d'objets/arrays de plats
            // Chaque plat a: id, name, description, price, image, category_id
            $cat->dishes = $dishModel->getByCategory($cat->id);
            
            // Note: Après cette ligne, chaque catégorie a une propriété supplémentaire "dishes"
            // Exemple: $cat->name = "Entrées", $cat->dishes = [plat1, plat2, plat3]
        }
        // Important: Le & dans &$cat signifie "référence", on modifie l'élément original du tableau

        // Étape 6: Récupération des informations de contact du restaurant
        // Les contacts sont stockés séparément pour permettre des mises à jour indépendantes
        $contactModel = new Contact($this->pdo);
        
        // getByRestaurant() retourne un objet/array avec: telephone, email, adresse, horaires
        $contact = $contactModel->getByRestaurant($restaurant->id);

        // Étape 7: Affichage de la vue avec toutes les données
        // La vue "vitrine/show.php" sera chargée avec ces trois variables disponibles
        $this->render('vitrine/show', [
            'restaurant' => $restaurant,  // Objet Restaurant complet
            'categories' => $categories,  // Tableau de catégories (chacune avec ses plats)
            'contact' => $contact         // Objet Contact avec coordonnées
        ]);
    }
}