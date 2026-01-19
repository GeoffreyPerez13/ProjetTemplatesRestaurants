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
                    $_SESSION['close_accordion'] = 'mode-selector-content';
                }
            }

            // Si on est en mode éditable
            else {
                // --- SUPPRESSION D'UNE CATÉGORIE ---
                if (isset($_POST['delete_category'])) {
                    $category_id = (int)$_POST['delete_category'];
                    
                    // Vérifier que la catégorie appartient bien à l'admin
                    $category = $categoryModel->getById($category_id, $admin_id);
                    
                    if ($category) {
                        try {
                            // Supprimer l'image de la catégorie si elle existe
                            if (!empty($category['image'])) {
                                $categoryModel->deleteImage($category['image']);
                            }
                            
                            // Supprimer la catégorie (la méthode delete() supprime aussi les plats)
                            $categoryModel->delete($category_id, $admin_id);
                            
                            $_SESSION['success_message'] = "Catégorie et tous ses plats supprimés avec succès.";
                            $anchor = 'categories-grid';
                            $_SESSION['close_accordion'] = 'mode-selector-content';
                            
                        } catch (Exception $e) {
                            $_SESSION['error_message'] = "Erreur lors de la suppression : " . $e->getMessage();
                        }
                    } else {
                        $_SESSION['error_message'] = "Catégorie non trouvée ou vous n'avez pas les droits.";
                    }
                }

                // --- AJOUT D'UNE CATÉGORIE ---
                elseif (!empty($_POST['new_category'])) {
                    $name = trim($_POST['new_category']);
                    $imagePath = null;

                    if (isset($_FILES['category_image']) && $_FILES['category_image']['error'] === UPLOAD_ERR_OK) {
                        $imagePath = $categoryModel->uploadImage($_FILES['category_image']);
                    }

                    $categoryModel->create($admin_id, $name, $imagePath);
                    $categoryId = $this->pdo->lastInsertId();

                    $_SESSION['success_message'] = "Catégorie ajoutée avec succès.";
                    $anchor = 'category-' . $categoryId;
                    $_SESSION['scroll_delay'] = 1000;
                    $_SESSION['close_accordion'] = 'mode-selector-content';
                }

                // --- MODIFICATION D'UNE CATÉGORIE ---
                elseif (isset($_POST['edit_category'])) {
                    $category_id = (int)$_POST['category_id'];
                    $new_name = trim($_POST['edit_category_name'] ?? '');

                    if ($category_id && $new_name) {
                        $imagePath = null;
                        $hasNewImage = false;

                        if (isset($_FILES['edit_category_image']) && $_FILES['edit_category_image']['error'] === UPLOAD_ERR_OK) {
                            $imagePath = $categoryModel->uploadImage($_FILES['edit_category_image']);
                            $hasNewImage = true;

                            $existingCategory = $this->getCategoryById($categoryModel, $category_id, $admin_id);
                            if ($existingCategory && !empty($existingCategory['image'])) {
                                $categoryModel->deleteImage($existingCategory['image']);
                            }
                        }

                        $categoryModel->update($category_id, $new_name, $imagePath);
                        $_SESSION['success_message'] = "Catégorie modifiée avec succès.";
                        $anchor = 'category-' . $category_id;
                        $_SESSION['close_accordion'] = 'mode-selector-content';

                        // Fermer l'accordéon "Modifier la catégorie" après modification
                        $_SESSION['close_accordion_secondary'] = 'edit-category-' . $category_id;
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
                        $anchor = 'category-' . $category_id;
                        $_SESSION['close_accordion'] = 'mode-selector-content';

                        // Fermer l'accordéon "Modifier la catégorie" après suppression
                        $_SESSION['close_accordion_secondary'] = 'edit-category-' . $category_id;
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

                    // La méthode create() retourne un objet Dish
                    $dishObject = $dishModel->create($category_id, $name, $price, $description, $imagePath);
                    
                    // Récupérer l'ID du plat créé via la méthode getId()
                    $dishId = $dishObject->getId();
                    
                    $_SESSION['success_message'] = "Plat ajouté avec succès.";

                    // Rediriger vers l'accordéon du plat (qui doit être fermé)
                    $anchor = 'dish-' . $dishId;
                    
                    // Fermer le mode-selector
                    $_SESSION['close_accordion'] = 'mode-selector-content';
                    
                    // Ouvrir "Modifier les plats" pour voir la liste
                    $_SESSION['open_accordion'] = 'edit-dishes-' . $category_id;
                    
                    // FORCER la fermeture de l'accordéon du plat
                    $_SESSION['close_dish_accordion'] = 'dish-' . $dishId;
                }

                // --- MODIFICATION D'UN PLAT ---
                elseif (isset($_POST['edit_dish'])) {
                    $dish_id = (int)$_POST['dish_id'];
                    $current_category_id = (int)($_POST['current_category_id'] ?? 0);
                    $name = trim($_POST['dish_name']);
                    $price = floatval($_POST['dish_price']);
                    $description = trim($_POST['dish_description'] ?? '');

                    // Récupérer le plat existant D'ABORD
                    $existingDish = $this->getDishById($dishModel, $dish_id);

                    // Par défaut, garder l'image existante
                    $imagePath = $existingDish && !empty($existingDish['image']) ? $existingDish['image'] : null;

                    // Vérifier si une nouvelle image est uploadée
                    if (isset($_FILES['dish_image']) && $_FILES['dish_image']['error'] === UPLOAD_ERR_OK) {
                        $imagePath = $dishModel->uploadImage($_FILES['dish_image']);

                        // Supprimer l'ancienne image si elle existe
                        if ($existingDish && !empty($existingDish['image'])) {
                            $dishModel->deleteImage($existingDish['image']);
                        }
                    }

                    $dishModel->update($dish_id, $name, $price, $description, $imagePath);
                    $_SESSION['success_message'] = "Plat modifié avec succès.";
                    
                    // Rediriger vers l'accordéon du plat (fermé)
                    $anchor = 'dish-' . $dish_id;
                    
                    // Fermer le mode-selector
                    $_SESSION['close_accordion'] = 'mode-selector-content';
                    
                    // Fermer l'accordéon du plat modifié
                    $_SESSION['close_dish_accordion'] = 'dish-' . $dish_id;
                    
                    // Ouvrir "Modifier les plats" pour voir la liste
                    $_SESSION['open_accordion'] = 'edit-dishes-' . $current_category_id;
                }

                // --- SUPPRESSION DE L'IMAGE D'UN PLAT ---
                elseif (isset($_POST['remove_dish_image'])) {
                    $dish_id = (int)$_POST['remove_dish_image'];
                    $current_category_id = (int)($_POST['current_category_id'] ?? 0);

                    // Récupérer le plat AVANT de le modifier
                    $stmt = $this->pdo->prepare("SELECT * FROM plats WHERE id = ?");
                    $stmt->execute([$dish_id]);
                    $existingDish = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($existingDish) {
                        if (!empty($existingDish['image'])) {
                            // 1. D'abord supprimer le fichier physique
                            $dishModel->deleteImage($existingDish['image']);
                            
                            // 2. Ensuite mettre à jour la base de données avec image = NULL
                            $dishModel->update(
                                $dish_id, 
                                $existingDish['name'], 
                                $existingDish['price'], 
                                $existingDish['description'], 
                                null
                            );
                            
                            $_SESSION['success_message'] = "Image du plat supprimée avec succès.";
                        } else {
                            $_SESSION['error_message'] = "Ce plat n'a pas d'image à supprimer.";
                        }
                    } else {
                        $_SESSION['error_message'] = "Plat non trouvé.";
                    }
                    
                    $anchor = 'dish-' . $dish_id;
                    $_SESSION['close_accordion'] = 'mode-selector-content';
                    $_SESSION['close_dish_accordion'] = 'dish-' . $dish_id;
                    $_SESSION['open_accordion'] = 'edit-dishes-' . $current_category_id;
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
                    $anchor = 'category-' . $current_category_id;
                    $_SESSION['close_accordion'] = 'mode-selector-content';

                    // Ouvrir "Modifier les plats" pour voir la liste mise à jour
                    $_SESSION['open_accordion'] = 'edit-dishes-' . $current_category_id;
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

    private function getDishByIdDirect($dishModel, $dish_id)
    {
        // Requête directe à la base de données
        $stmt = $this->pdo->prepare("SELECT * FROM plats WHERE id = ?");
        $stmt->execute([$dish_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
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