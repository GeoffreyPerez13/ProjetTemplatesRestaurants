<?php

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../Models/Category.php';
require_once __DIR__ . '/../Models/Dish.php';
require_once __DIR__ . '/../Models/CardImage.php';
require_once __DIR__ . '/../Models/Admin.php';

class CardController extends BaseController
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

        // Instancie les modèles nécessaires
        $adminModel = new Admin($this->pdo);
        $categoryModel = new Category($this->pdo);
        $dishModel = new Dish($this->pdo);
        $carteImageModel = new CardImage($this->pdo);

        // Récupérer le mode actuel
        $currentMode = $adminModel->getCarteMode($admin_id);

        // Gestion des actions POST avec redirection
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handlePostActions($categoryModel, $dishModel, $carteImageModel, $adminModel, $admin_id, $currentMode);
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

        // Récupérer l'ancre de l'URL pour le scroll
        $anchor = $_GET['anchor'] ?? '';

        // Préparer les données selon le mode
        if ($currentMode === 'editable') {
            // Récupération de toutes les catégories de l'admin
            $categories = $categoryModel->getAllByAdmin($admin_id);

            // Pour chaque catégorie, on récupère les plats associés
            foreach ($categories as &$cat) {
                $cat['plats'] = $dishModel->getAllByCategory($cat['id']);
            }

            $data = [
                'currentMode' => $currentMode,
                'categories' => $categories,
                'message' => $message,
                'error_message' => $error_message,
                'anchor' => $anchor
            ];
        } else {
            // Mode images
            $carteImages = $carteImageModel->getAllByAdmin($admin_id);

            $data = [
                'currentMode' => $currentMode,
                'carteImages' => $carteImages,
                'message' => $message,
                'error_message' => $error_message,
                'anchor' => $anchor
            ];
        }

        // Rend la vue "edit-card" avec les données
        $this->render('admin/edit-card', $data);
    }

    // Gestion centralisée des actions POST
    private function handlePostActions($categoryModel, $dishModel, $carteImageModel, $adminModel, $admin_id, $currentMode)
    {
        try {
            // Récupérer l'ancre à partir du POST
            $anchor = $_POST['anchor'] ?? '';

            // --- CHANGEMENT DE MODE ---
            if (isset($_POST['change_mode'])) {
                $newMode = $_POST['carte_mode'];
                if (in_array($newMode, ['editable', 'images'])) {
                    $adminModel->updateCarteMode($admin_id, $newMode);
                    $_SESSION['success_message'] = "Mode de carte changé avec succès";
                    $anchor = 'mode-selector';
                }
            }

            // Si on est en mode images
            elseif ($currentMode === 'images') {
                // --- UPLOAD D'IMAGES DE CARTE ---
                if (isset($_FILES['card_images']) && !empty($_FILES['card_images']['name'][0])) {
                    $uploadedCount = 0;

                    foreach ($_FILES['card_images']['tmp_name'] as $index => $tmpName) {
                        if ($_FILES['card_images']['error'][$index] === UPLOAD_ERR_OK) {
                            $file = [
                                'name' => $_FILES['card_images']['name'][$index],
                                'tmp_name' => $tmpName,
                                'size' => $_FILES['card_images']['size'][$index],
                                'error' => $_FILES['card_images']['error'][$index]
                            ];

                            try {
                                $filename = $carteImageModel->uploadImage($file);
                                $carteImageModel->add($admin_id, $filename, $file['name']);
                                $uploadedCount++;
                            } catch (Exception $e) {
                                $_SESSION['error_message'] = $e->getMessage();
                            }
                        }
                    }

                    if ($uploadedCount > 0) {
                        $_SESSION['success_message'] = "$uploadedCount image(s) ajoutée(s) avec succès";
                    }

                    if (empty($anchor)) {
                        $anchor = 'upload-images';
                    }
                }

                // --- SUPPRESSION D'IMAGE DE CARTE ---
                elseif (isset($_POST['delete_image'])) {
                    $imageId = (int)$_POST['image_id'];
                    $image = $carteImageModel->getById($imageId, $admin_id);

                    if ($image) {
                        $carteImageModel->deleteImageFile($image['filename']);
                        $carteImageModel->delete($imageId, $admin_id);
                        $_SESSION['success_message'] = "Image supprimée avec succès";

                        if (empty($anchor)) {
                            $anchor = 'images-list';
                        }
                    }
                }

                // --- RÉORDONNEMENT DES IMAGES (drag & drop) ---
                elseif (isset($_POST['new_order']) && !empty($_POST['new_order'])) {
                    $newOrder = json_decode($_POST['new_order'], true);

                    if (is_array($newOrder) && !empty($newOrder)) {
                        // Mettre à jour l'ordre dans la base de données
                        $success = $carteImageModel->updateImageOrder($admin_id, $newOrder);

                        if ($success) {
                            $_SESSION['success_message'] = "L'ordre des images a été enregistré avec succès !";
                        } else {
                            $_SESSION['error_message'] = "Une erreur est survenue lors de l'enregistrement de l'ordre.";
                        }
                    } else {
                        $_SESSION['error_message'] = "Erreur : données d'ordre invalides.";
                    }

                    if (empty($anchor)) {
                        $anchor = 'images-list';
                    }
                }

                // --- RÉORDONNEMENT AUTOMATIQUE DES IMAGES ---
                elseif (isset($_POST['reorder_images'])) {
                    $carteImageModel->reorderImages($admin_id);
                    $_SESSION['success_message'] = "Les images ont été réorganisées par ordre alphabétique avec succès !";

                    if (empty($anchor)) {
                        $anchor = 'images-list';
                    }
                }
            }

            // Si on est en mode éditable
            else {
                // --- AJOUT D'UNE CATÉGORIE ---
                if (!empty($_POST['new_category'])) {
                    $name = trim($_POST['new_category']);
                    $imagePath = null;

                    if (isset($_FILES['category_image']) && $_FILES['category_image']['error'] === UPLOAD_ERR_OK) {
                        $imagePath = $categoryModel->uploadImage($_FILES['category_image']);
                    }

                    $categoryModel->create($admin_id, $name, $imagePath);
                    $_SESSION['success_message'] = "Catégorie ajoutée avec succès.";

                    if (empty($anchor)) {
                        $anchor = 'new-category';
                    }
                }

                // --- MODIFICATION D'UNE CATÉGORIE ---
                elseif (isset($_POST['edit_category'])) {
                    $category_id = (int)$_POST['category_id'];
                    $new_name = trim($_POST['edit_category_name'] ?? '');

                    if ($category_id && $new_name) {
                        $imagePath = null;

                        if (isset($_FILES['edit_category_image']) && $_FILES['edit_category_image']['error'] === UPLOAD_ERR_OK) {
                            $imagePath = $categoryModel->uploadImage($_FILES['edit_category_image']);

                            $existingCategory = $this->getCategoryById($categoryModel, $category_id, $admin_id);
                            if ($existingCategory && !empty($existingCategory['image'])) {
                                $categoryModel->deleteImage($existingCategory['image']);
                            }
                        }

                        $categoryModel->update($category_id, $new_name, $imagePath);

                        if ($imagePath) {
                            $_SESSION['success_message'] = "Catégorie modifiée avec succès.";
                        } else {
                            $_SESSION['success_message'] = "Catégorie modifiée avec succès.";
                        }

                        if (empty($anchor)) {
                            $anchor = 'category-' . $category_id;
                        }
                    } else {
                        $_SESSION['error_message'] = "Le nom de la catégorie est requis.";
                    }
                }

                // --- SUPPRESSION DE L'IMAGE DE LA CATÉGORIE ---
                elseif (isset($_POST['remove_category_image'])) {
                    $category_id = (int)$_POST['remove_category_image'];

                    $category = $categoryModel->getById($category_id, $admin_id);

                    if ($category && !empty($category['image'])) {
                        $categoryModel->deleteImage($category['image']);
                        $categoryModel->update($category_id, $category['name'], '');

                        $_SESSION['success_message'] = "Image de la catégorie supprimée avec succès.";

                        if (empty($anchor)) {
                            $anchor = 'category-' . $category_id;
                        }
                    } else {
                        $_SESSION['error_message'] = "Cette catégorie n'a pas d'image à supprimer.";
                    }
                }

                // --- SUPPRESSION D'UNE CATÉGORIE ---
                elseif (!empty($_POST['delete_category'])) {
                    $categoryId = (int)$_POST['delete_category'];

                    $categories = $categoryModel->getAllByAdmin($admin_id);
                    foreach ($categories as $cat) {
                        if ($cat['id'] == $categoryId && !empty($cat['image'])) {
                            $categoryModel->deleteImage($cat['image']);
                            break;
                        }
                    }

                    $categoryModel->delete($categoryId, $admin_id);
                    $_SESSION['success_message'] = "Catégorie supprimée avec succès.";

                    if (empty($anchor)) {
                        $anchor = 'categories-grid';
                    }
                }

                // --- AJOUT D'UN PLAT ---
                elseif (isset($_POST['new_dish'])) {
                    $category_id = (int)$_POST['category_id'];
                    $name = trim($_POST['dish_name']);
                    $price = floatval($_POST['dish_price']);
                    $description = trim($_POST['dish_description'] ?? '');
                    $imagePath = null;

                    if (isset($_FILES['dish_image']) && $_FILES['dish_image']['error'] === UPLOAD_ERR_OK) {
                        $imagePath = $dishModel->uploadImage($_FILES['dish_image']);
                    }

                    $dishModel->create($category_id, $name, $price, $description, $imagePath);
                    $_SESSION['success_message'] = "Plat ajouté avec succès.";

                    if (empty($anchor)) {
                        $anchor = 'category-' . $category_id;
                    }
                }

                // --- MODIFICATION D'UN PLAT ---
                elseif (isset($_POST['edit_dish'])) {
                    $dish_id = (int)$_POST['dish_id'];
                    $current_category_id = (int)($_POST['current_category_id'] ?? 0);
                    $name = trim($_POST['dish_name']);
                    $price = floatval($_POST['dish_price']);
                    $description = trim($_POST['dish_description'] ?? '');
                    $imagePath = null;

                    if (isset($_FILES['dish_image']) && $_FILES['dish_image']['error'] === UPLOAD_ERR_OK) {
                        $imagePath = $dishModel->uploadImage($_FILES['dish_image']);

                        $existingDish = $this->getDishById($dishModel, $dish_id);
                        if ($existingDish && !empty($existingDish['image'])) {
                            $dishModel->deleteImage($existingDish['image']);
                        }
                    }

                    $dishModel->update($dish_id, $name, $price, $description, $imagePath);
                    $_SESSION['success_message'] = "Plat modifié avec succès.";

                    if (empty($anchor)) {
                        $anchor = 'category-' . $current_category_id;
                    }
                }

                // --- SUPPRESSION DE L'IMAGE D'UN PLAT ---
                elseif (isset($_POST['remove_dish_image'])) {
                    $dish_id = (int)$_POST['remove_dish_image'];
                    $current_category_id = (int)($_POST['current_category_id'] ?? 0);

                    $existingDish = $this->getDishById($dishModel, $dish_id);

                    if ($existingDish && !empty($existingDish['image'])) {
                        $dishModel->deleteImage($existingDish['image']);
                        $dishModel->update($dish_id, $existingDish['name'], $existingDish['price'], $existingDish['description'], '');

                        $_SESSION['success_message'] = "Image du plat supprimée avec succès.";

                        if (empty($anchor)) {
                            $anchor = 'category-' . $current_category_id;
                        }
                    } else {
                        $_SESSION['error_message'] = "Ce plat n'a pas d'image à supprimer.";
                    }
                }

                // --- SUPPRESSION D'UN PLAT ---
                elseif (isset($_POST['delete_dish'])) {
                    $dishId = (int)$_POST['delete_dish'];
                    $current_category_id = (int)($_POST['current_category_id'] ?? 0);

                    $existingDish = $this->getDishById($dishModel, $dishId);
                    if ($existingDish && !empty($existingDish['image'])) {
                        $dishModel->deleteImage($existingDish['image']);
                    }

                    $dishModel->delete($dishId);
                    $_SESSION['success_message'] = "Plat supprimé avec succès.";

                    if (empty($anchor)) {
                        $anchor = 'category-' . $current_category_id;
                    }
                }
            }
        } catch (Exception $e) {
            $_SESSION['error_message'] = "Erreur : " . $e->getMessage();
        }

        // Redirection avec ancre
        $redirectUrl = '?page=edit-card';
        if (!empty($anchor)) {
            $redirectUrl .= '&anchor=' . urlencode($anchor);
        }

        header('Location: ' . $redirectUrl);
        exit;
    }

    // Page d'aperçu de la carte
    public function view()
    {
        $this->requireLogin();
        $admin_id = $_SESSION['admin_id'];

        $adminModel = new Admin($this->pdo);
        $categoryModel = new Category($this->pdo);
        $dishModel = new Dish($this->pdo);
        $carteImageModel = new CardImage($this->pdo);

        $currentMode = $adminModel->getCarteMode($admin_id);

        if ($currentMode === 'editable') {
            $categories = $categoryModel->getAllByAdmin($admin_id);

            foreach ($categories as &$cat) {
                $cat['plats'] = $dishModel->getAllByCategory($cat['id']);
            }

            $_SESSION['categories_cache'] = $categories;

            $data = [
                'currentMode' => $currentMode,
                'categories' => $categories
            ];
        } else {
            $carteImages = $carteImageModel->getAllByAdmin($admin_id);

            $data = [
                'currentMode' => $currentMode,
                'carteImages' => $carteImages
            ];
        }

        $this->render('admin/view-card', $data);
    }

    // Méthodes utilitaires
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

    private function getDishById($dishModel, $dish_id)
    {
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
}
