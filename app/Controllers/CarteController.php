<?php

require_once __DIR__ . '/BaseController.php'; // Inclusion du contrôleur de base (hérité pour utiliser les fonctions communes)
require_once __DIR__ . '/../Models/Category.php'; // Inclusion du modèle Category (gestion des catégories de plats)
require_once __DIR__ . '/../Models/Dish.php'; // Inclusion du modèle Dish (gestion des plats associés aux catégories)

// Définition du contrôleur CarteController, responsable de la gestion de la carte du restaurant
class CarteController extends BaseController
{
    // Constructeur : récupère la connexion PDO et l’envoie au contrôleur parent
    public function __construct($pdo)
    {
        parent::__construct($pdo);
    }

    // Page d’édition de la carte (catégories + plats)
    public function edit()
    {
        // Vérifie que l’administrateur est bien connecté
        $this->requireLogin();

        // Récupère l’ID de l’admin connecté depuis la session
        $admin_id = $_SESSION['admin_id'];

        // Instancie les modèles nécessaires pour manipuler les données
        $categoryModel = new Category($this->pdo);
        $dishModel = new Dish($this->pdo);

        // Variable pour afficher un message de confirmation ou d’erreur
        $message = null;

        // --- AJOUT D’UNE CATÉGORIE ---
        if (!empty($_POST['new_category'])) {
            // Nettoyage du nom de la catégorie
            $name = trim($_POST['new_category']);

            // Création de la catégorie dans la base
            $categoryModel->create($admin_id, $name);

            // Message de confirmation
            $message = "Catégorie ajoutée.";
        }

        // --- SUPPRESSION D’UNE CATÉGORIE ---
        if (!empty($_POST['delete_category'])) {
            // Suppression de la catégorie correspondant à l’ID transmis
            $categoryModel->delete((int)$_POST['delete_category'], $admin_id);

            // Message de confirmation
            $message = "Catégorie supprimée.";
        }

        // --- AJOUT D'UN PLAT ---
        if (isset($_POST['new_dish'])) {
            // Récupération et conversion des valeurs saisies
            $category_id = (int)$_POST['category_id']; // ID de la catégorie liée
            $name = trim($_POST['dish_name']);          // Nom du plat
            $price = floatval($_POST['dish_price']);    // Prix du plat
            $description = trim($_POST['dish_description'] ?? ''); // DESCRIPTION (avec valeur par défaut)

            // Création du plat dans la base
            $dishModel->create($category_id, $name, $price, $description);

            // Message de confirmation
            $message = "Plat ajouté.";
        }

        // --- MODIFICATION D'UN PLAT ---
        if (isset($_POST['edit_dish'])) {
            // Récupération des données du plat à modifier
            $dish_id = (int)$_POST['dish_id'];
            $name = trim($_POST['dish_name']);
            $price = floatval($_POST['dish_price']);
            $description = trim($_POST['dish_description'] ?? ''); // DESCRIPTION (avec valeur par défaut)

            // Mise à jour du plat dans la base
            $dishModel->update($dish_id, $name, $price, $description);

            // Message de confirmation
            $message = "Plat modifié.";
        }

        // --- SUPPRESSION D’UN PLAT ---
        if (isset($_POST['delete_dish'])) {
            // Suppression du plat correspondant à l’ID transmis
            $dishModel->delete((int)$_POST['delete_dish']);

            // Message de confirmation
            $message = "Plat supprimé.";
        }

        // --- RÉCUPÉRATION DE TOUTES LES CATÉGORIES DE L’ADMIN ---
        $categories = $categoryModel->getAllByAdmin($admin_id);

        // Pour chaque catégorie, on récupère les plats associés
        foreach ($categories as &$cat) {
            // Ajoute dans le tableau $cat un sous-tableau 'plats' contenant les plats de cette catégorie
            $cat['plats'] = $dishModel->getAllByCategory($cat['id']);
        }

        // Rend la vue "edit-carte" en lui passant les données nécessaires :
        // - les catégories et leurs plats
        // - le message de succès ou d’erreur
        $this->render('admin/edit-carte', [
            'categories' => $categories,
            'message' => $message
        ]);
    }

    // Page d'aperçu de la carte (lecture seule)
    public function view()
    {
        // Vérifie que l'administrateur est bien connecté
        $this->requireLogin();

        // Récupère l'ID de l'admin connecté depuis la session
        $admin_id = $_SESSION['admin_id'];

        // Instancie les modèles nécessaires
        $categoryModel = new Category($this->pdo);
        $dishModel = new Dish($this->pdo);

        // Récupère toutes les catégories de l'admin
        $categories = $categoryModel->getAllByAdmin($admin_id);

        // Pour chaque catégorie, on récupère les plats associés
        foreach ($categories as &$cat) {
            $cat['plats'] = $dishModel->getAllByCategory($cat['id']);
        }

        // Rend la vue "view-carte" en passant les catégories
        $this->render('admin/view-carte', [
            'categories' => $categories
        ]);
    }
}
