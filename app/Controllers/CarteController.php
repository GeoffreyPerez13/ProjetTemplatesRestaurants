<?php

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../Models/Category.php';
require_once __DIR__ . '/../Models/Dish.php';

class CarteController extends BaseController
{
    public function __construct($pdo)
    {
        parent::__construct($pdo);
    }

    // Page d'édition de la carte (catégories + plats)
    public function edit()
    {
        // Vérifie que l'administrateur est bien connecté
        $this->requireLogin();

        // Récupère l'ID de l'admin connecté depuis la session
        $admin_id = $_SESSION['admin_id'];

        // Instancie les modèles nécessaires pour manipuler les données
        $categoryModel = new Category($this->pdo);
        $dishModel = new Dish($this->pdo);

        // Gestion des actions POST avec redirection
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handlePostActions($categoryModel, $dishModel, $admin_id);
        }

        // Récupération du message de session
        $message = $_SESSION['success_message'] ?? null;
        $error_message = $_SESSION['error_message'] ?? null;
        
        if (isset($_SESSION['success_message'])) {
            unset($_SESSION['success_message']);
        }
        if (isset($_SESSION['error_message'])) {
            unset($_SESSION['error_message']);
        }

        // Récupération de toutes les catégories de l'admin
        $categories = $categoryModel->getAllByAdmin($admin_id);

        // Pour chaque catégorie, on récupère les plats associés
        foreach ($categories as &$cat) {
            $cat['plats'] = $dishModel->getAllByCategory($cat['id']);
        }

        // Rend la vue "edit-carte" avec les données
        $this->render('admin/edit-carte', [
            'categories' => $categories,
            'message' => $message,
            'error_message' => $error_message
        ]);
    }

    // Gestion centralisée des actions POST
    private function handlePostActions($categoryModel, $dishModel, $admin_id)
    {
        try {
            // --- AJOUT D'UNE CATÉGORIE ---
            if (!empty($_POST['new_category'])) {
                $name = trim($_POST['new_category']);
                $imagePath = null;

                // Gestion de l'upload d'image
                if (isset($_FILES['category_image']) && $_FILES['category_image']['error'] === UPLOAD_ERR_OK) {
                    $imagePath = $categoryModel->uploadImage($_FILES['category_image']);
                }

                $categoryModel->create($admin_id, $name, $imagePath);
                $_SESSION['success_message'] = "Catégorie ajoutée avec succès.";
            }

            // --- SUPPRESSION D'UNE CATÉGORIE ---
            elseif (!empty($_POST['delete_category'])) {
                $categoryId = (int)$_POST['delete_category'];
                
                // Récupérer l'image avant suppression pour la supprimer du serveur
                $categories = $categoryModel->getAllByAdmin($admin_id);
                foreach ($categories as $cat) {
                    if ($cat['id'] == $categoryId && !empty($cat['image'])) {
                        $categoryModel->deleteImage($cat['image']);
                        break;
                    }
                }

                $categoryModel->delete($categoryId, $admin_id);
                $_SESSION['success_message'] = "Catégorie supprimée avec succès.";
            }

            // --- AJOUT D'UN PLAT ---
            elseif (isset($_POST['new_dish'])) {
                $category_id = (int)$_POST['category_id'];
                $name = trim($_POST['dish_name']);
                $price = floatval($_POST['dish_price']);
                $description = trim($_POST['dish_description'] ?? '');
                $imagePath = null;

                // Gestion de l'upload d'image
                if (isset($_FILES['dish_image']) && $_FILES['dish_image']['error'] === UPLOAD_ERR_OK) {
                    $imagePath = $dishModel->uploadImage($_FILES['dish_image']);
                }

                $dishModel->create($category_id, $name, $price, $description, $imagePath);
                $_SESSION['success_message'] = "Plat ajouté avec succès.";
            }

            // --- MODIFICATION D'UN PLAT ---
            elseif (isset($_POST['edit_dish'])) {
                $dish_id = (int)$_POST['dish_id'];
                $name = trim($_POST['dish_name']);
                $price = floatval($_POST['dish_price']);
                $description = trim($_POST['dish_description'] ?? '');
                $imagePath = null;

                // Gestion de l'upload d'image
                if (isset($_FILES['dish_image']) && $_FILES['dish_image']['error'] === UPLOAD_ERR_OK) {
                    $imagePath = $dishModel->uploadImage($_FILES['dish_image']);
                    
                    // Supprimer l'ancienne image si elle existe
                    $existingDish = $this->getDishById($dishModel, $dish_id);
                    if ($existingDish && !empty($existingDish['image'])) {
                        $dishModel->deleteImage($existingDish['image']);
                    }
                }

                $dishModel->update($dish_id, $name, $price, $description, $imagePath);
                $_SESSION['success_message'] = "Plat modifié avec succès.";
            }

            // --- SUPPRESSION D'UN PLAT ---
            elseif (isset($_POST['delete_dish'])) {
                $dishId = (int)$_POST['delete_dish'];
                
                // Récupérer l'image avant suppression pour la supprimer du serveur
                $existingDish = $this->getDishById($dishModel, $dishId);
                if ($existingDish && !empty($existingDish['image'])) {
                    $dishModel->deleteImage($existingDish['image']);
                }

                $dishModel->delete($dishId);
                $_SESSION['success_message'] = "Plat supprimé avec succès.";
            }

        } catch (Exception $e) {
            $_SESSION['error_message'] = "Erreur : " . $e->getMessage();
        }

        // Redirection après toute action POST
        header('Location: ?page=edit-carte');
        exit;
    }

    // Méthode utilitaire pour récupérer un plat par son ID
    private function getDishById($dishModel, $dish_id)
    {
        // Cette méthode est simplifiée - vous devrez peut-être l'adapter selon votre structure
        $allCategories = $_SESSION['categories_cache'] ?? [];
        foreach ($allCategories as $cat) {
            if (isset($cat['plats'])) {
                foreach ($cat['plats'] as $plat) {
                    if ($plat['id'] == $dish_id) {
                        return $plat;
                    }
                }
            }
        }
        return null;
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

        // Cache les catégories pour la méthode getDishById
        $_SESSION['categories_cache'] = $categories;

        // Rend la vue "view-carte" en passant les catégories
        $this->render('admin/view-carte', [
            'categories' => $categories
        ]);
    }
}