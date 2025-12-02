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

            // --- MODIFICATION D'UNE CATÉGORIE ---
            elseif (isset($_POST['edit_category'])) {
                $category_id = (int)$_POST['category_id'];
                $new_name = trim($_POST['edit_category_name'] ?? '');

                if ($category_id && $new_name) {
                    $imagePath = null;

                    // Gestion de l'upload de nouvelle image
                    if (isset($_FILES['edit_category_image']) && $_FILES['edit_category_image']['error'] === UPLOAD_ERR_OK) {
                        $imagePath = $categoryModel->uploadImage($_FILES['edit_category_image']);

                        // Supprimer l'ancienne image si elle existe
                        $existingCategory = $this->getCategoryById($categoryModel, $category_id, $admin_id);
                        if ($existingCategory && !empty($existingCategory['image'])) {
                            $categoryModel->deleteImage($existingCategory['image']);
                        }
                    }

                    // Mise à jour en base de données
                    $categoryModel->update($category_id, $new_name, $imagePath);

                    // Message de succès approprié
                    if ($imagePath) {
                        $_SESSION['success_message'] = "Catégorie modifiée avec succès.";
                    } else {
                        $_SESSION['success_message'] = "Catégorie modifié avec succès.";
                    }
                } else {
                    $_SESSION['error_message'] = "Le nom de la catégorie est requis.";
                }
            }
            // --- SUPPRESSION DE L'IMAGE DE LA CATÉGORIE ---
            elseif (isset($_POST['remove_category_image'])) {
                $category_id = (int)$_POST['remove_category_image'];

                // Récupérer la catégorie pour supprimer son image
                $category = $categoryModel->getById($category_id, $admin_id);

                if ($category && !empty($category['image'])) {
                    // Supprimer l'image du serveur
                    $categoryModel->deleteImage($category['image']);

                    // Mettre à jour la catégorie en base (image = NULL)
                    $categoryModel->update($category_id, $category['name'], '');

                    $_SESSION['success_message'] = "Image de la catégorie supprimée avec succès.";
                } else {
                    $_SESSION['error_message'] = "Cette catégorie n'a pas d'image à supprimer.";
                }
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

    // Méthode utilitaire pour récupérer une catégorie par son ID
    private function getCategoryById($categoryModel, $category_id, $admin_id)
    {
        $categories = $categoryModel->getAllByAdmin($admin_id);
        foreach ($categories as $cat) {
            if ($cat['id'] == $category_id) {
                return $cat;
            }
        }
        return null;
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
