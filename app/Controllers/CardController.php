<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../Models/Category.php';
require_once __DIR__ . '/../Models/Dish.php';
require_once __DIR__ . '/../Models/CardImage.php';
require_once __DIR__ . '/../Models/Admin.php';
require_once __DIR__ . '/../Models/Restaurant.php';
require_once __DIR__ . '/../Helpers/Validator.php';
require_once __DIR__ . '/../Models/Allergene.php';

/**
 * Contrôleur de gestion de la carte du restaurant
 * Gère les deux modes de carte : "editable" (catégories/plats) et "images" (photos uploadées)
 * Inclut le CRUD complet pour les catégories, plats, images et leurs fichiers associés
 */
class CardController extends BaseController
{
    /** @var Restaurant Modèle pour mettre à jour le timestamp du restaurant */
    private $restaurantModel;

    /**
     * @param PDO $pdo Connexion à la base de données
     */
    public function __construct($pdo)
    {
        parent::__construct($pdo);
        $this->setScrollDelay(1500);
        $this->restaurantModel = new Restaurant($pdo);
    }

    /**
     * Met à jour le timestamp du restaurant
     */
    private function updateRestaurantTimestamp()
    {
        if (isset($_SESSION['admin_id'])) {
            error_log("updateRestaurantTimestamp: admin_id = " . $_SESSION['admin_id']);

            // Récupérer le restaurant_id
            $stmt = $this->pdo->prepare("SELECT restaurant_id FROM admins WHERE id = ?");
            $stmt->execute([$_SESSION['admin_id']]);
            $restaurantId = $stmt->fetchColumn();

            if ($restaurantId) {
                error_log("updateRestaurantTimestamp: restaurant_id = " . $restaurantId);

                // Mettre à jour le timestamp
                $stmt2 = $this->pdo->prepare("UPDATE restaurants SET updated_at = NOW() WHERE id = ?");
                $result = $stmt2->execute([$restaurantId]);

                if ($result) {
                    error_log("updateRestaurantTimestamp: SUCCESS");
                } else {
                    $errorInfo = $stmt2->errorInfo();
                    error_log("updateRestaurantTimestamp: ERROR - " . $errorInfo[2]);
                }
                return $result;
            } else {
                error_log("updateRestaurantTimestamp: Aucun restaurant_id trouvé pour admin " . $_SESSION['admin_id']);
            }
        } else {
            error_log("updateRestaurantTimestamp: admin_id non défini dans la session");
        }
        return false;
    }

    /**
     * Page d'édition de la carte
     */
    public function edit()
    {
        // 1. Vérifier la connexion
        $this->requireLogin();
        $admin_id = $_SESSION['admin_id'];

        error_log("=== EDIT CARD START ===");
        error_log("Admin ID: $admin_id");

        // 2. Initialiser les modèles
        $adminModel = new Admin($this->pdo);
        $categoryModel = new Category($this->pdo);
        $dishModel = new Dish($this->pdo);
        $carteImageModel = new CardImage($this->pdo);

        // 3. Récupérer le mode actuel
        $currentMode = $adminModel->getCarteMode($admin_id);
        error_log("Current mode: $currentMode");

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            error_log("=== POST REQUEST ===");
            error_log("POST data: " . print_r($_POST, true));

            $this->handlePostRequest($adminModel, $categoryModel, $dishModel, $carteImageModel, $admin_id, $currentMode);
        }

        $messages = $this->getFlashMessages();
        extract($messages);

        $error_fields = $_SESSION['error_fields'] ?? [];
        $old_input = $_SESSION['old_input'] ?? [];
        unset($_SESSION['error_fields'], $_SESSION['old_input']);

        if ($currentMode === 'editable') {
            $data = $this->getEditableModeData($admin_id, $categoryModel, $dishModel, $messages, $error_fields, $old_input);
        } else {
            $data = $this->getImagesModeData($admin_id, $carteImageModel, $messages, $error_fields, $old_input);
        }

        $this->render('admin/edit-card', $data);
    }

    /**
     * Dispatche les requêtes POST vers le bon handler selon le mode et l'action
     *
     * @param Admin     $adminModel      Modèle admin (changement de mode)
     * @param Category  $categoryModel   Modèle catégories
     * @param Dish      $dishModel       Modèle plats
     * @param CardImage $carteImageModel Modèle images de carte
     * @param int       $admin_id        ID de l'admin connecté
     * @param string    $currentMode     Mode actuel ('editable' ou 'images')
     */
    private function handlePostRequest($adminModel, $categoryModel, $dishModel, $carteImageModel, $admin_id, $currentMode)
    {
        $anchor = $_POST['anchor'] ?? '';

        try {
            if (isset($_POST['change_mode'])) {
                $this->handleChangeMode($adminModel, $admin_id, $anchor);
                return;
            }

            if ($currentMode === 'editable') {
                $this->handleEditableModeActions($categoryModel, $dishModel, $admin_id, $anchor);
                return;
            }

            if ($currentMode === 'images') {
                $this->handleImagesModeActions($carteImageModel, $admin_id, $anchor);
                return;
            }
        } catch (Exception $e) {
            error_log("Exception in handlePostRequest: " . $e->getMessage());
            $this->addErrorMessage("Erreur: " . $e->getMessage(), $anchor);
        }

        $this->redirectToEditCard($anchor);
    }

    /**
     * Bascule entre les modes 'editable' et 'images'
     *
     * @param Admin  $adminModel Modèle admin
     * @param int    $admin_id   ID de l'admin
     * @param string $anchor     Ancre HTML pour le scroll
     */
    private function handleChangeMode($adminModel, $admin_id, $anchor)
    {
        $newMode = $_POST['carte_mode'] ?? '';

        if (in_array($newMode, ['editable', 'images'])) {
            $adminModel->updateCarteMode($admin_id, $newMode);
            $this->updateRestaurantTimestamp();
            $this->addSuccessMessage("Mode de carte changé avec succès", 'mode-selector');
            $_SESSION['close_accordion'] = 'mode-selector-content';
        }

        $this->redirectToEditCard($anchor);
    }

    /**
     * Dispatche les actions du mode éditable (catégories et plats)
     *
     * @param Category $categoryModel Modèle catégories
     * @param Dish     $dishModel     Modèle plats
     * @param int      $admin_id      ID de l'admin
     * @param string   $anchor        Ancre HTML pour le scroll
     */
    private function handleEditableModeActions($categoryModel, $dishModel, $admin_id, $anchor)
    {
        error_log("Handling editable mode actions");

        if (isset($_POST['new_category'])) {
            $this->handleAddCategory($categoryModel, $admin_id, $anchor);
        } elseif (isset($_POST['edit_category'])) {
            $this->handleEditCategory($categoryModel, $admin_id, $anchor);
        } elseif (isset($_POST['delete_category'])) {
            $this->handleDeleteCategory($categoryModel, $admin_id, $anchor);
        } elseif (isset($_POST['remove_category_image'])) {
            $this->handleRemoveCategoryImage($categoryModel, $admin_id, $anchor);
        } elseif (isset($_POST['new_dish'])) {
            $this->handleAddDish($dishModel, $anchor);
        } elseif (isset($_POST['edit_dish'])) {
            $this->handleEditDish($dishModel, $anchor);
        } elseif (isset($_POST['delete_dish'])) {
            $this->handleDeleteDish($dishModel, $anchor);
        } elseif (isset($_POST['remove_dish_image'])) {
            $this->handleRemoveDishImage($dishModel, $anchor);
        }

        $this->redirectToEditCard($anchor);
    }

    /**
     * Dispatche les actions du mode images (upload, suppression, réorganisation)
     *
     * @param CardImage $carteImageModel Modèle images
     * @param int       $admin_id        ID de l'admin
     * @param string    $anchor          Ancre HTML pour le scroll
     */
    private function handleImagesModeActions($carteImageModel, $admin_id, $anchor)
    {
        error_log("Handling images mode actions");

        if (isset($_POST['upload_images']) && isset($_FILES['card_images'])) {
            $this->handleUploadImages($carteImageModel, $admin_id, $anchor);
        } elseif (isset($_POST['delete_image'])) {
            $this->handleDeleteImageSimple($carteImageModel, $admin_id, $anchor);
        } elseif (isset($_POST['update_image_order'])) {
            $this->handleReorderImages($carteImageModel, $admin_id, $anchor);
        }

        $this->redirectToEditCard($anchor);
    }

    /**
     * Gestion simplifiée de la suppression d'image
     */
    private function handleDeleteImageSimple($carteImageModel, $admin_id, $anchor)
    {
        $image_id = (int)($_POST['image_id'] ?? 0);
        error_log("=== DELETE IMAGE PROCESS START ===");
        error_log("Image ID from POST: $image_id");
        error_log("Admin ID: $admin_id");

        if ($image_id <= 0) {
            error_log("Invalid image ID");
            $this->addErrorMessage("ID d'image invalide.", 'images-list');
            return;
        }

        // Récupérer les infos de l'image
        $stmt = $this->pdo->prepare("SELECT * FROM card_images WHERE id = ? AND admin_id = ?");
        $stmt->execute([$image_id, $admin_id]);
        $image = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$image) {
            error_log("Image not found or doesn't belong to admin");
            $this->addErrorMessage("Image non trouvée ou vous n'avez pas les droits.", 'images-list');
            return;
        }

        // Supprimer de la base
        try {
            $stmt = $this->pdo->prepare("DELETE FROM card_images WHERE id = ? AND admin_id = ?");
            $stmt->execute([$image_id, $admin_id]);
            $rowCount = $stmt->rowCount();

            if ($rowCount === 0) {
                error_log("Failed to delete from database");
                $this->addErrorMessage("Échec de la suppression en base.", 'images-list');
                $_SESSION['open_accordion'] = 'images-list-content';
                $this->redirectToEditCard($anchor);
                return;
            }
        } catch (Exception $e) {
            error_log("Database delete error: " . $e->getMessage());
            $this->addErrorMessage("Erreur de base de données: " . $e->getMessage(), 'images-list');
            $_SESSION['open_accordion'] = 'images-list-content';
            $this->redirectToEditCard($anchor);
            return;
        }

        // Supprimer le fichier physique
        if (!empty($image['filename'])) {
            $this->deletePhysicalFile($image['filename']);
        }

        // Mettre à jour le timestamp du restaurant
        $this->updateRestaurantTimestamp();

        $this->addSuccessMessage("Image supprimée avec succès.", 'images-list');
        $_SESSION['close_accordion'] = 'mode-selector-content';
        $_SESSION['open_accordion'] = 'images-list-content';

        $this->redirectToEditCard($anchor);
    }

    /**
     * Supprime un fichier physique du serveur à partir de son chemin relatif
     *
     * @param string $filename Chemin relatif du fichier (ex: 'uploads/carte-images/xxx.jpg')
     * @return bool true si supprimé ou inexistant, false si échec
     */
    private function deletePhysicalFile($filename)
    {
        error_log("Attempting to delete physical file: $filename");

        $filepath = trim($filename);
        if (strpos($filepath, '/') === 0) {
            $filepath = substr($filepath, 1);
            error_log("Removed leading slash, new path: $filepath");
        }

        $projectRoot = realpath(__DIR__ . '/../../');
        $absolutePath = $projectRoot . '/' . $filepath;

        error_log("Project root: $projectRoot");
        error_log("Absolute path: $absolutePath");

        if (!file_exists($absolutePath)) {
            error_log("File does not exist at: $absolutePath");
            return true; // On considère que c'est ok si le fichier n'existe pas
        }

        if (!is_writable($absolutePath)) {
            error_log("File is not writable: $absolutePath");
            return false;
        }

        if (unlink($absolutePath)) {
            error_log("Physical file deleted successfully: $absolutePath");
            return true;
        } else {
            error_log("Failed to delete physical file: $absolutePath");
            return false;
        }
    }

    /**
     * Crée une nouvelle catégorie avec image optionnelle
     *
     * @param Category $categoryModel Modèle catégories
     * @param int      $admin_id      ID de l'admin
     * @param string   $anchor        Ancre HTML pour le scroll
     */
    private function handleAddCategory($categoryModel, $admin_id, $anchor)
    {
        $name = trim($_POST['new_category'] ?? '');

        if (empty($name) || strlen($name) > 100) {
            $this->addErrorMessage("Le nom de la catégorie est requis (max 100 caractères)", 'new-category');
            $_SESSION['error_fields'] = ['new_category' => true];
            $_SESSION['old_input'] = $_POST;
            $_SESSION['open_accordion'] = 'new-category-content';
            $this->redirectToEditCard($anchor);
            return;
        }

        $imagePath = null;
        if (isset($_FILES['category_image']) && $_FILES['category_image']['error'] === UPLOAD_ERR_OK) {
            try {
                $imagePath = $categoryModel->uploadImage($_FILES['category_image']);
            } catch (Exception $e) {
                $this->addErrorMessage("Erreur image: " . $e->getMessage(), 'new-category');
                $this->redirectToEditCard($anchor);
                return;
            }
        }

        try {
            $categoryModel->create($admin_id, $name, $imagePath);
            $categoryId = $this->pdo->lastInsertId();

            $this->updateRestaurantTimestamp(); // Mise à jour du restaurant

            $this->addSuccessMessage("Catégorie ajoutée avec succès.", 'category-' . $categoryId);
            $_SESSION['close_accordion'] = 'mode-selector-content';
            $_SESSION['close_accordion_secondary'] = 'new-category-content';
        } catch (Exception $e) {
            $this->addErrorMessage("Erreur: " . $e->getMessage(), 'new-category');
        }

        $this->redirectToEditCard($anchor);
    }

    /**
     * Met à jour le nom et/ou l'image d'une catégorie existante
     *
     * @param Category $categoryModel Modèle catégories
     * @param int      $admin_id      ID de l'admin
     * @param string   $anchor        Ancre HTML pour le scroll
     */
    private function handleEditCategory($categoryModel, $admin_id, $anchor)
    {
        $category_id = (int)($_POST['category_id'] ?? 0);
        $new_name = trim($_POST['edit_category_name'] ?? '');

        if (empty($new_name) || strlen($new_name) > 100) {
            $this->addErrorMessage("Nom invalide (max 100 caractères)", 'category-' . $category_id);
            $_SESSION['error_fields'] = ['edit_category_name' => true];
            $_SESSION['old_input'] = $_POST;
            $_SESSION['open_accordion'] = 'edit-category-' . $category_id;
            $this->redirectToEditCard($anchor);
            return;
        }

        $imagePath = null;
        if (isset($_FILES['edit_category_image']) && $_FILES['edit_category_image']['error'] === UPLOAD_ERR_OK) {
            try {
                $imagePath = $categoryModel->uploadImage($_FILES['edit_category_image']);

                $category = $categoryModel->getById($category_id, $admin_id);
                if ($category && !empty($category['image'])) {
                    $categoryModel->deleteImage($category['image']);
                }
            } catch (Exception $e) {
                $this->addErrorMessage("Erreur image: " . $e->getMessage(), 'category-' . $category_id);
                $this->redirectToEditCard($anchor);
                return;
            }
        }

        try {
            $categoryModel->update($category_id, $new_name, $imagePath);

            $this->updateRestaurantTimestamp(); // Mise à jour du restaurant

            $this->addSuccessMessage("Catégorie modifiée avec succès.", 'category-' . $category_id);
            $_SESSION['close_accordion'] = 'mode-selector-content';
            $_SESSION['close_accordion_secondary'] = 'edit-category-' . $category_id;
        } catch (Exception $e) {
            $this->addErrorMessage("Erreur: " . $e->getMessage(), 'category-' . $category_id);
        }

        $this->redirectToEditCard($anchor);
    }

    /**
     * Supprime une catégorie et tous ses plats associés (cascade)
     *
     * @param Category $categoryModel Modèle catégories
     * @param int      $admin_id      ID de l'admin
     * @param string   $anchor        Ancre HTML pour le scroll
     */
    private function handleDeleteCategory($categoryModel, $admin_id, $anchor)
    {
        $category_id = (int)($_POST['delete_category'] ?? 0);

        try {
            $category = $categoryModel->getById($category_id, $admin_id);

            if ($category) {
                if (!empty($category['image'])) {
                    $categoryModel->deleteImage($category['image']);
                }

                $categoryModel->delete($category_id, $admin_id);

                $this->updateRestaurantTimestamp(); // Mise à jour du restaurant

                $this->addSuccessMessage("Catégorie et tous ses plats supprimés avec succès.", 'categories-grid');
                $_SESSION['close_accordion'] = 'mode-selector-content';
            } else {
                $this->addErrorMessage("Catégorie non trouvée.", $anchor);
            }
        } catch (Exception $e) {
            $this->addErrorMessage("Erreur: " . $e->getMessage(), $anchor);
        }

        $this->redirectToEditCard($anchor);
    }

    /**
     * Gestion de la suppression d'image de catégorie
     */
    private function handleRemoveCategoryImage($categoryModel, $admin_id, $anchor)
    {
        $category_id = (int)($_POST['remove_category_image'] ?? 0);

        try {
            $category = $categoryModel->getById($category_id, $admin_id);

            if ($category && !empty($category['image'])) {
                $categoryModel->deleteImage($category['image']);
                $categoryModel->update($category_id, $category['name'], '');

                $this->updateRestaurantTimestamp(); // Mise à jour du restaurant

                $this->addSuccessMessage("Image de catégorie supprimée avec succès.", 'category-' . $category_id);
                $_SESSION['close_accordion'] = 'mode-selector-content';
                $_SESSION['close_accordion_secondary'] = 'edit-category-' . $category_id;
            } else {
                $this->addErrorMessage("Cette catégorie n'a pas d'image.", 'category-' . $category_id);
            }
        } catch (Exception $e) {
            $this->addErrorMessage("Erreur: " . $e->getMessage(), 'category-' . $category_id);
        }

        $this->redirectToEditCard($anchor);
    }

    /**
     * Crée un nouveau plat avec image et allergènes optionnels
     *
     * @param Dish   $dishModel Modèle plats
     * @param string $anchor    Ancre HTML pour le scroll
     */
    private function handleAddDish($dishModel, $anchor)
    {
        $category_id = (int)($_POST['category_id'] ?? 0);
        $name = trim($_POST['dish_name'] ?? '');
        $price = str_replace(',', '.', $_POST['dish_price'] ?? '0');
        $description = trim($_POST['dish_description'] ?? '');

        $errors = [];
        if (empty($name) || strlen($name) > 100) $errors['dish_name'] = true;
        if (!is_numeric($price) || $price <= 0 || $price > 999.99) $errors['dish_price'] = true;
        if (strlen($description) > 500) $errors['dish_description'] = true;

        if (!empty($errors)) {
            $this->addErrorMessage("Veuillez corriger les erreurs.", 'category-' . $category_id);
            $_SESSION['error_fields'] = $errors;
            $_SESSION['old_input'] = $_POST;
            $_SESSION['open_accordion'] = 'add-dish-' . $category_id;
            $this->redirectToEditCard($anchor);
            return;
        }

        $imagePath = null;
        if (isset($_FILES['dish_image']) && $_FILES['dish_image']['error'] === UPLOAD_ERR_OK) {
            try {
                $imagePath = $dishModel->uploadImage($_FILES['dish_image']);
            } catch (Exception $e) {
                $this->addErrorMessage("Erreur image: " . $e->getMessage(), 'category-' . $category_id);
                $this->redirectToEditCard($anchor);
                return;
            }
        }

        try {
            $dish = $dishModel->create($category_id, $name, (float)$price, $description, $imagePath);
            $dishId = $dish->getId();  // ou $this->pdo->lastInsertId()

            // Sauvegarde des allergènes
            require_once __DIR__ . '/../Models/Allergene.php';
            $allergeneModel = new Allergene($this->pdo);
            $allergeneIds = $_POST['allergenes'] ?? [];
            $allergeneModel->saveForDish($dishId, $allergeneIds);

            $this->updateRestaurantTimestamp();

            $this->addSuccessMessage("Plat ajouté avec succès.", 'dish-' . $dishId);
            $_SESSION['close_accordion'] = 'mode-selector-content';
            $_SESSION['open_accordion'] = 'edit-dishes-' . $category_id;
            $_SESSION['close_dish_accordion'] = 'dish-' . $dishId;
            $_SESSION['close_accordion_secondary'] = 'add-dish-' . $category_id;
        } catch (Exception $e) {
            $this->addErrorMessage("Erreur: " . $e->getMessage(), 'category-' . $category_id);
        }

        $this->redirectToEditCard($anchor);
    }

    /**
     * Met à jour un plat existant (nom, prix, description, image, allergènes)
     *
     * @param Dish   $dishModel Modèle plats
     * @param string $anchor    Ancre HTML pour le scroll
     */
    private function handleEditDish($dishModel, $anchor)
    {
        $dish_id = (int)($_POST['dish_id'] ?? 0);
        $current_category_id = (int)($_POST['current_category_id'] ?? 0);
        $name = trim($_POST['dish_name'] ?? '');
        $price = str_replace(',', '.', $_POST['dish_price'] ?? '0');
        $description = trim($_POST['dish_description'] ?? '');

        $errors = [];
        if (empty($name) || strlen($name) > 100) $errors['dish_name_' . $dish_id] = true;
        if (!is_numeric($price) || $price <= 0 || $price > 999.99) $errors['dish_price_' . $dish_id] = true;
        if (strlen($description) > 500) $errors['dish_description_' . $dish_id] = true;

        if (!empty($errors)) {
            $this->addErrorMessage("Veuillez corriger les erreurs.", 'dish-' . $dish_id);
            $_SESSION['error_fields'] = $errors;
            $_SESSION['old_input'] = $_POST;
            $_SESSION['open_accordion'] = 'dish-' . $dish_id;
            $this->redirectToEditCard($anchor);
            return;
        }

        $stmt = $this->pdo->prepare("SELECT * FROM plats WHERE id = ?");
        $stmt->execute([$dish_id]);
        $existingDish = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$existingDish) {
            $this->addErrorMessage("Plat non trouvé.", 'dish-' . $dish_id);
            $this->redirectToEditCard($anchor);
            return;
        }

        $imagePath = $existingDish['image'];
        if (isset($_FILES['dish_image']) && $_FILES['dish_image']['error'] === UPLOAD_ERR_OK) {
            try {
                $imagePath = $dishModel->uploadImage($_FILES['dish_image']);
                if (!empty($existingDish['image'])) {
                    $dishModel->deleteImage($existingDish['image']);
                }
            } catch (Exception $e) {
                $this->addErrorMessage("Erreur image: " . $e->getMessage(), 'dish-' . $dish_id);
                $this->redirectToEditCard($anchor);
                return;
            }
        }

        try {
            $dishModel->update($dish_id, $name, (float)$price, $description, $imagePath);

            // Mise à jour des allergènes
            require_once __DIR__ . '/../Models/Allergene.php';
            $allergeneModel = new Allergene($this->pdo);
            $allergeneIds = $_POST['allergenes_' . $dish_id] ?? []; // name="allergenes_XX[]"
            $allergeneModel->saveForDish($dish_id, $allergeneIds);

            $this->updateRestaurantTimestamp();

            $this->addSuccessMessage("Plat modifié avec succès.", 'dish-' . $dish_id);
            $_SESSION['close_accordion'] = 'mode-selector-content';
            $_SESSION['close_dish_accordion'] = 'dish-' . $dish_id;
            $_SESSION['open_accordion'] = 'edit-dishes-' . $current_category_id;
        } catch (Exception $e) {
            $this->addErrorMessage("Erreur: " . $e->getMessage(), 'dish-' . $dish_id);
        }

        $this->redirectToEditCard($anchor);
    }

    /**
     * Supprime un plat et son fichier image associé
     *
     * @param Dish   $dishModel Modèle plats
     * @param string $anchor    Ancre HTML pour le scroll
     */
    private function handleDeleteDish($dishModel, $anchor)
    {
        $dish_id = (int)($_POST['delete_dish'] ?? 0);
        $current_category_id = (int)($_POST['current_category_id'] ?? 0);

        try {
            $stmt = $this->pdo->prepare("SELECT * FROM plats WHERE id = ?");
            $stmt->execute([$dish_id]);
            $dish = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($dish) {
                if (!empty($dish['image'])) {
                    $dishModel->deleteImage($dish['image']);
                }

                $dishModel->delete($dish_id);

                $this->updateRestaurantTimestamp(); // Mise à jour du restaurant

                $this->addSuccessMessage("Plat supprimé avec succès.", 'category-' . $current_category_id);
                $_SESSION['close_accordion'] = 'mode-selector-content';
                $_SESSION['open_accordion'] = 'edit-dishes-' . $current_category_id;
            } else {
                $this->addErrorMessage("Plat non trouvé.", 'category-' . $current_category_id);
            }
        } catch (Exception $e) {
            $this->addErrorMessage("Erreur: " . $e->getMessage(), 'category-' . $current_category_id);
        }

        $this->redirectToEditCard($anchor);
    }

    /**
     * Supprime uniquement l'image d'un plat sans supprimer le plat
     *
     * @param Dish   $dishModel Modèle plats
     * @param string $anchor    Ancre HTML pour le scroll
     */
    private function handleRemoveDishImage($dishModel, $anchor)
    {
        $dish_id = (int)($_POST['remove_dish_image'] ?? 0);
        $current_category_id = (int)($_POST['current_category_id'] ?? 0);

        try {
            $stmt = $this->pdo->prepare("SELECT * FROM plats WHERE id = ?");
            $stmt->execute([$dish_id]);
            $dish = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($dish && !empty($dish['image'])) {
                $dishModel->deleteImage($dish['image']);
                $dishModel->update($dish_id, $dish['name'], $dish['price'], $dish['description'], null);

                $this->updateRestaurantTimestamp(); // Mise à jour du restaurant

                $this->addSuccessMessage("Image du plat supprimée avec succès.", 'dish-' . $dish_id);
                $_SESSION['close_accordion'] = 'mode-selector-content';
                $_SESSION['close_dish_accordion'] = 'dish-' . $dish_id;
                $_SESSION['open_accordion'] = 'edit-dishes-' . $current_category_id;
            } else {
                $this->addErrorMessage("Ce plat n'a pas d'image.", 'dish-' . $dish_id);
            }
        } catch (Exception $e) {
            $this->addErrorMessage("Erreur: " . $e->getMessage(), 'dish-' . $dish_id);
        }

        $this->redirectToEditCard($anchor);
    }

    /**
     * Upload multiple d'images pour le mode carte images
     *
     * @param CardImage $carteImageModel Modèle images
     * @param int       $admin_id        ID de l'admin
     * @param string    $anchor          Ancre HTML pour le scroll
     */
    private function handleUploadImages($carteImageModel, $admin_id, $anchor)
    {
        error_log("=== HANDLE UPLOAD IMAGES ===");

        if (!isset($_FILES['card_images']) || empty($_FILES['card_images']['name'][0])) {
            $this->addErrorMessage("Veuillez sélectionner au moins un fichier.", 'upload-images');
            $this->redirectToEditCard($anchor);
            return;
        }

        $uploadCount = 0;
        $errorCount = 0;

        foreach ($_FILES['card_images']['name'] as $index => $name) {
            if ($_FILES['card_images']['error'][$index] === UPLOAD_ERR_OK) {
                try {
                    $file = [
                        'name' => $_FILES['card_images']['name'][$index],
                        'type' => $_FILES['card_images']['type'][$index],
                        'tmp_name' => $_FILES['card_images']['tmp_name'][$index],
                        'error' => $_FILES['card_images']['error'][$index],
                        'size' => $_FILES['card_images']['size'][$index]
                    ];

                    $filename = $carteImageModel->uploadImage($file);
                    $carteImageModel->add($admin_id, $filename, $name);
                    $uploadCount++;
                } catch (Exception $e) {
                    $errorCount++;
                    error_log("Error uploading file $name: " . $e->getMessage());
                }
            }
        }

        if ($uploadCount > 0) {
            $this->updateRestaurantTimestamp(); // Mise à jour du restaurant
            $this->addSuccessMessage("$uploadCount image(s) téléchargée(s) avec succès.", 'upload-images');
            if ($errorCount > 0) {
                $this->addErrorMessage("$errorCount image(s) n'ont pas pu être téléchargées.", 'upload-images');
            }
            $_SESSION['close_accordion'] = 'mode-selector-content';
        } else {
            $this->addErrorMessage("Aucune image n'a pu être téléchargée.", 'upload-images');
        }

        $this->redirectToEditCard($anchor);
    }

    /**
     * Met à jour l'ordre d'affichage des images (drag & drop)
     *
     * @param CardImage $carteImageModel Modèle images
     * @param int       $admin_id        ID de l'admin
     * @param string    $anchor          Ancre HTML pour le scroll
     */
    private function handleReorderImages($carteImageModel, $admin_id, $anchor)
    {
        $newOrder = $_POST['new_order'] ?? '';
        $orderArray = json_decode($newOrder, true);

        if (!is_array($orderArray) || empty($orderArray)) {
            $this->addErrorMessage("Aucun ordre valide reçu.", 'images-list');
            $this->redirectToEditCard($anchor);
            return;
        }

        try {
            $carteImageModel->updateImageOrder($admin_id, $orderArray);

            $this->updateRestaurantTimestamp(); // Mise à jour du restaurant

            $this->addSuccessMessage("Ordre des images mis à jour avec succès.", 'images-list');
            $_SESSION['close_accordion'] = 'mode-selector-content';
            $_SESSION['open_accordion'] = 'images-list-content';
        } catch (Exception $e) {
            $this->addErrorMessage("Erreur: " . $e->getMessage(), 'images-list');
            $_SESSION['open_accordion'] = 'images-list-content';
        }

        $this->redirectToEditCard($anchor);
    }

    /**
     * Prépare les données pour la vue en mode éditable (catégories + plats + allergènes)
     *
     * @param int      $admin_id      ID de l'admin
     * @param Category $categoryModel Modèle catégories
     * @param Dish     $dishModel     Modèle plats
     * @param array    $messages      Messages flash récupérés
     * @param array    $error_fields  Champs en erreur pour la validation
     * @param array    $old_input     Anciennes valeurs saisies
     * @return array Données prêtes pour la vue
     */
    private function getEditableModeData($admin_id, $categoryModel, $dishModel, $messages, $error_fields, $old_input)
    {
        $categories = $categoryModel->getAllByAdmin($admin_id);

        foreach ($categories as &$cat) {
            $cat['plats'] = $dishModel->getAllByCategory($cat['id']);
        }

        // Récupération des allergènes
        require_once __DIR__ . '/../Models/Allergene.php';
        $allergeneModel = new Allergene($this->pdo);
        $allergenes = $allergeneModel->getAll();

        // Récupérer les associations pour chaque plat
        $platsAllergenes = [];
        foreach ($categories as $cat) {
            foreach ($cat['plats'] as $plat) {
                $platsAllergenes[$plat['id']] = $allergeneModel->getForDish($plat['id']);
            }
        }

        $_SESSION['categories_cache'] = $categories;

        return [
            'currentMode' => 'editable',
            'categories' => $categories,
            'allergenes' => $allergenes,
            'platsAllergenes' => $platsAllergenes,
            'success_message' => $messages['success_message'] ?? null,
            'error_message' => $messages['error_message'] ?? null,
            'error_fields' => $error_fields,
            'old_input' => $old_input,
            'anchor' => $messages['anchor'] ?? null,
            'scroll_delay' => $messages['scroll_delay'] ?? $this->scrollDelay
        ];
    }

    /**
     * Prépare les données pour la vue en mode images
     *
     * @param int       $admin_id        ID de l'admin
     * @param CardImage $carteImageModel Modèle images
     * @param array     $messages        Messages flash récupérés
     * @param array     $error_fields    Champs en erreur
     * @param array     $old_input       Anciennes valeurs saisies
     * @return array Données prêtes pour la vue
     */
    private function getImagesModeData($admin_id, $carteImageModel, $messages, $error_fields, $old_input)
    {
        $carteImages = $carteImageModel->getAllByAdmin($admin_id);
        error_log("Images loaded: " . count($carteImages));

        return [
            'currentMode' => 'images',
            'carteImages' => $carteImages,
            'success_message' => $messages['success_message'] ?? null,
            'error_message' => $messages['error_message'] ?? null,
            'error_fields' => $error_fields,
            'old_input' => $old_input,
            'anchor' => $messages['anchor'] ?? null,
            'scroll_delay' => $messages['scroll_delay'] ?? $this->scrollDelay
        ];
    }

    /**
     * Redirige vers ?page=edit-card avec ancre optionnelle et stoppe le script
     *
     * @param string $anchor Ancre HTML pour le scroll post-redirection
     */
    private function redirectToEditCard($anchor = '')
    {
        $redirectUrl = '?page=edit-card';
        if (!empty($anchor)) {
            $redirectUrl .= '&anchor=' . urlencode($anchor);
        }
        error_log("Redirecting to: $redirectUrl");
        header('Location: ' . $redirectUrl);
        exit;
    }

    /**
     * Page d'aperçu de la carte
     */
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
}
