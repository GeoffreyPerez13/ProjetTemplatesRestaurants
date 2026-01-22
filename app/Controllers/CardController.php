<?php
// ====== DEBUG TEMPORAIRE ======
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// ==============================

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../Models/Category.php';
require_once __DIR__ . '/../Models/Dish.php';
require_once __DIR__ . '/../Models/CardImage.php';
require_once __DIR__ . '/../Models/Admin.php';
require_once __DIR__ . '/../Helpers/Validator.php';

class CardController extends BaseController
{
    public function __construct($pdo)
    {
        parent::__construct($pdo);
        $this->setScrollDelay(3500);
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

        // 4. Traiter les formulaires POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            error_log("=== POST REQUEST ===");
            error_log("POST data: " . print_r($_POST, true));

            $this->handlePostRequest($adminModel, $categoryModel, $dishModel, $carteImageModel, $admin_id, $currentMode);
        }

        // 5. Préparer les données pour la vue
        $messages = $this->getFlashMessages();
        extract($messages);

        // Nettoyer les erreurs de formulaire
        $error_fields = $_SESSION['error_fields'] ?? [];
        $old_input = $_SESSION['old_input'] ?? [];
        unset($_SESSION['error_fields'], $_SESSION['old_input']);

        // 6. Charger les données selon le mode
        if ($currentMode === 'editable') {
            $data = $this->getEditableModeData($admin_id, $categoryModel, $dishModel, $messages, $error_fields, $old_input);
        } else {
            $data = $this->getImagesModeData($admin_id, $carteImageModel, $messages, $error_fields, $old_input);
        }

        // 7. Afficher la vue
        $this->render('admin/edit-card', $data);
    }

    /**
     * Gère toutes les requêtes POST
     */
    private function handlePostRequest($adminModel, $categoryModel, $dishModel, $carteImageModel, $admin_id, $currentMode)
    {
        $anchor = $_POST['anchor'] ?? '';

        try {
            // Changement de mode
            if (isset($_POST['change_mode'])) {
                $this->handleChangeMode($adminModel, $admin_id, $anchor);
                return;
            }

            // Mode éditable
            if ($currentMode === 'editable') {
                $this->handleEditableModeActions($categoryModel, $dishModel, $admin_id, $anchor);
                return;
            }

            // Mode images
            if ($currentMode === 'images') {
                $this->handleImagesModeActions($carteImageModel, $admin_id, $anchor);
                return;
            }
        } catch (Exception $e) {
            error_log("Exception in handlePostRequest: " . $e->getMessage());
            $this->addErrorMessage("Erreur: " . $e->getMessage(), $anchor);
        }

        // Redirection par défaut
        $this->redirectToEditCard($anchor);
    }

    /**
     * Gestion du changement de mode
     */
    private function handleChangeMode($adminModel, $admin_id, $anchor)
    {
        $newMode = $_POST['carte_mode'] ?? '';

        if (in_array($newMode, ['editable', 'images'])) {
            $adminModel->updateCarteMode($admin_id, $newMode);
            $this->addSuccessMessage("Mode de carte changé avec succès", 'mode-selector');
            $_SESSION['close_accordion'] = 'mode-selector-content';
        }

        $this->redirectToEditCard($anchor);
    }

    /**
     * Gestion des actions en mode éditable
     */
    private function handleEditableModeActions($categoryModel, $dishModel, $admin_id, $anchor)
    {
        error_log("Handling editable mode actions");

        // Ajout de catégorie
        if (isset($_POST['new_category'])) {
            $this->handleAddCategory($categoryModel, $admin_id, $anchor);
        }

        // Modification de catégorie
        elseif (isset($_POST['edit_category'])) {
            $this->handleEditCategory($categoryModel, $admin_id, $anchor);
        }

        // Suppression de catégorie
        elseif (isset($_POST['delete_category'])) {
            $this->handleDeleteCategory($categoryModel, $admin_id, $anchor);
        }

        // Suppression d'image de catégorie
        elseif (isset($_POST['remove_category_image'])) {
            $this->handleRemoveCategoryImage($categoryModel, $admin_id, $anchor);
        }

        // Ajout de plat
        elseif (isset($_POST['new_dish'])) {
            $this->handleAddDish($dishModel, $anchor);
        }

        // Modification de plat
        elseif (isset($_POST['edit_dish'])) {
            $this->handleEditDish($dishModel, $anchor);
        }

        // Suppression de plat
        elseif (isset($_POST['delete_dish'])) {
            $this->handleDeleteDish($dishModel, $anchor);
        }

        // Suppression d'image de plat
        elseif (isset($_POST['remove_dish_image'])) {
            $this->handleRemoveDishImage($dishModel, $anchor);
        }

        // Redirection par défaut
        $this->redirectToEditCard($anchor);
    }

    /**
     * Gestion des actions en mode images
     */
    private function handleImagesModeActions($carteImageModel, $admin_id, $anchor)
    {
        error_log("Handling images mode actions");

        // Upload d'images
        if (isset($_POST['upload_images']) && isset($_FILES['card_images'])) {
            $this->handleUploadImages($carteImageModel, $admin_id, $anchor);
        }

        // SUPPRESSION D'IMAGE - APPROCHE SIMPLIFIÉE
        elseif (isset($_POST['delete_image'])) {
            error_log("DELETE IMAGE ACTION DETECTED");
            $this->handleDeleteImageSimple($carteImageModel, $admin_id, $anchor);
        }

        // Réorganisation des images
        elseif (isset($_POST['update_image_order'])) {
            $this->handleReorderImages($carteImageModel, $admin_id, $anchor);
        }

        // Redirection par défaut
        $this->redirectToEditCard($anchor);
    }

    /**
     * Gestion simplifiée de la suppression d'image
     */
    private function handleDeleteImageSimple($carteImageModel, $admin_id, $anchor)
    {
        // 1. Récupérer l'ID de l'image
        $image_id = (int)($_POST['image_id'] ?? 0);
        error_log("Image ID from POST: $image_id");

        if ($image_id <= 0) {
            error_log("Invalid image ID");
            $this->addErrorMessage("ID d'image invalide.", 'images-list');
            return;
        }

        // 2. Vérifier que l'image appartient bien à l'admin
        $stmt = $this->pdo->prepare("SELECT * FROM card_images WHERE id = ? AND admin_id = ?");
        $stmt->execute([$image_id, $admin_id]);
        $image = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$image) {
            error_log("Image not found or doesn't belong to admin");
            $this->addErrorMessage("Image non trouvée ou vous n'avez pas les droits.", 'images-list');
            return;
        }

        error_log("Image found: " . json_encode($image));

        // 3. Supprimer le fichier physique s'il existe
        if (!empty($image['filename'])) {
            if (!$this->deletePhysicalFile($image['filename'])) {
                error_log("Failed to delete physical file");
                $this->addErrorMessage("Erreur lors de la suppression du fichier.", 'images-list');
                $_SESSION['open_accordion'] = 'images-list-content';
                $this->redirectToEditCard($anchor);
                return;
            }
        }

        // 4. Supprimer de la base de données
        try {
            $stmt = $this->pdo->prepare("DELETE FROM card_images WHERE id = ? AND admin_id = ?");
            $stmt->execute([$image_id, $admin_id]);
            $rowCount = $stmt->rowCount();

            error_log("Database delete - Rows affected: $rowCount");

            if ($rowCount > 0) {
                error_log("Image deleted successfully");
                $this->addSuccessMessage("Image supprimée avec succès.", 'images-list');

                // IMPORTANT : Configurer les accordéons après suppression
                $_SESSION['close_accordion'] = 'mode-selector-content';
                $_SESSION['open_accordion'] = 'images-list-content';
            } else {
                error_log("Failed to delete from database");
                $this->addErrorMessage("Échec de la suppression.", 'images-list');
                $_SESSION['open_accordion'] = 'images-list-content';
            }
        } catch (Exception $e) {
            error_log("Database delete error: " . $e->getMessage());
            $this->addErrorMessage("Erreur de base de données: " . $e->getMessage(), 'images-list');
            $_SESSION['open_accordion'] = 'images-list-content';
        }

        $this->redirectToEditCard($anchor);
    }

    /**
     * Supprime un fichier physique
     */
    private function deletePhysicalFile($filename)
    {
        // Nettoyer le chemin du fichier
        $filepath = $filename;

        // Si le chemin commence par '/', l'enlever
        if (strpos($filepath, '/') === 0) {
            $filepath = substr($filepath, 1);
        }

        // Chemin absolu
        $absolutePath = $_SERVER['DOCUMENT_ROOT'] . '/' . $filepath;
        error_log("Trying to delete file: $absolutePath");

        if (file_exists($absolutePath)) {
            if (unlink($absolutePath)) {
                error_log("Physical file deleted: $absolutePath");
                return true;
            } else {
                error_log("Failed to delete physical file: $absolutePath");
                return false;
            }
        } else {
            error_log("File does not exist: $absolutePath");
            return false;
        }
    }

    /**
     * Supprime une image de la base de données
     */
    private function deleteFromDatabase($image_id, $admin_id)
    {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM card_images WHERE id = ? AND admin_id = ?");
            $stmt->execute([$image_id, $admin_id]);
            $rowCount = $stmt->rowCount();

            error_log("Database delete - Rows affected: $rowCount");

            return $rowCount > 0;
        } catch (Exception $e) {
            error_log("Database delete error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Gestion de l'ajout de catégorie
     */
    private function handleAddCategory($categoryModel, $admin_id, $anchor)
    {
        $name = trim($_POST['new_category'] ?? '');

        // Validation
        if (empty($name) || strlen($name) > 100) {
            $this->addErrorMessage("Le nom de la catégorie est requis (max 100 caractères)", 'new-category');
            $_SESSION['error_fields'] = ['new_category' => true];
            $_SESSION['old_input'] = $_POST;
            $_SESSION['open_accordion'] = 'new-category-content';
            $this->redirectToEditCard($anchor);
            return;
        }

        // Gestion de l'image
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

        // Créer la catégorie
        try {
            $categoryModel->create($admin_id, $name, $imagePath);
            $categoryId = $this->pdo->lastInsertId();

            $this->addSuccessMessage("Catégorie ajoutée avec succès.", 'category-' . $categoryId);
            $_SESSION['close_accordion'] = 'mode-selector-content';
            $_SESSION['close_accordion_secondary'] = 'new-category-content';
        } catch (Exception $e) {
            $this->addErrorMessage("Erreur: " . $e->getMessage(), 'new-category');
        }

        $this->redirectToEditCard($anchor);
    }

    /**
     * Gestion de la modification de catégorie
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

        // Gestion de la nouvelle image
        $imagePath = null;
        if (isset($_FILES['edit_category_image']) && $_FILES['edit_category_image']['error'] === UPLOAD_ERR_OK) {
            try {
                $imagePath = $categoryModel->uploadImage($_FILES['edit_category_image']);

                // Supprimer l'ancienne image si elle existe
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

        // Mettre à jour
        try {
            $categoryModel->update($category_id, $new_name, $imagePath);
            $this->addSuccessMessage("Catégorie modifiée avec succès.", 'category-' . $category_id);
            $_SESSION['close_accordion'] = 'mode-selector-content';
            $_SESSION['close_accordion_secondary'] = 'edit-category-' . $category_id;
        } catch (Exception $e) {
            $this->addErrorMessage("Erreur: " . $e->getMessage(), 'category-' . $category_id);
        }

        $this->redirectToEditCard($anchor);
    }

    /**
     * Gestion de la suppression de catégorie
     */
    private function handleDeleteCategory($categoryModel, $admin_id, $anchor)
    {
        $category_id = (int)($_POST['delete_category'] ?? 0);

        try {
            // Vérifier que la catégorie existe et appartient à l'admin
            $category = $categoryModel->getById($category_id, $admin_id);

            if ($category) {
                // Supprimer l'image si elle existe
                if (!empty($category['image'])) {
                    $categoryModel->deleteImage($category['image']);
                }

                // Supprimer la catégorie
                $categoryModel->delete($category_id, $admin_id);
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
                // Supprimer le fichier
                $categoryModel->deleteImage($category['image']);

                // Mettre à jour la base
                $categoryModel->update($category_id, $category['name'], '');

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
     * Gestion de l'ajout de plat
     */
    private function handleAddDish($dishModel, $anchor)
    {
        $category_id = (int)($_POST['category_id'] ?? 0);
        $name = trim($_POST['dish_name'] ?? '');
        $price = str_replace(',', '.', $_POST['dish_price'] ?? '0');
        $description = trim($_POST['dish_description'] ?? '');

        // Validation
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

        // Gestion de l'image
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

        // Créer le plat
        try {
            $dish = $dishModel->create($category_id, $name, (float)$price, $description, $imagePath);
            $dishId = $dish->getId();

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
     * Gestion de la modification de plat
     */
    private function handleEditDish($dishModel, $anchor)
    {
        $dish_id = (int)($_POST['dish_id'] ?? 0);
        $current_category_id = (int)($_POST['current_category_id'] ?? 0);
        $name = trim($_POST['dish_name'] ?? '');
        $price = str_replace(',', '.', $_POST['dish_price'] ?? '0');
        $description = trim($_POST['dish_description'] ?? '');

        // Validation
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

        // Récupérer le plat existant
        $stmt = $this->pdo->prepare("SELECT * FROM plats WHERE id = ?");
        $stmt->execute([$dish_id]);
        $existingDish = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$existingDish) {
            $this->addErrorMessage("Plat non trouvé.", 'dish-' . $dish_id);
            $this->redirectToEditCard($anchor);
            return;
        }

        // Gestion de la nouvelle image
        $imagePath = $existingDish['image'];
        if (isset($_FILES['dish_image']) && $_FILES['dish_image']['error'] === UPLOAD_ERR_OK) {
            try {
                $imagePath = $dishModel->uploadImage($_FILES['dish_image']);

                // Supprimer l'ancienne image
                if (!empty($existingDish['image'])) {
                    $dishModel->deleteImage($existingDish['image']);
                }
            } catch (Exception $e) {
                $this->addErrorMessage("Erreur image: " . $e->getMessage(), 'dish-' . $dish_id);
                $this->redirectToEditCard($anchor);
                return;
            }
        }

        // Mettre à jour
        try {
            $dishModel->update($dish_id, $name, (float)$price, $description, $imagePath);
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
     * Gestion de la suppression de plat
     */
    private function handleDeleteDish($dishModel, $anchor)
    {
        $dish_id = (int)($_POST['delete_dish'] ?? 0);
        $current_category_id = (int)($_POST['current_category_id'] ?? 0);

        try {
            // Récupérer le plat pour supprimer son image
            $stmt = $this->pdo->prepare("SELECT * FROM plats WHERE id = ?");
            $stmt->execute([$dish_id]);
            $dish = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($dish) {
                // Supprimer l'image si elle existe
                if (!empty($dish['image'])) {
                    $dishModel->deleteImage($dish['image']);
                }

                // Supprimer le plat
                $dishModel->delete($dish_id);
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
     * Gestion de la suppression d'image de plat
     */
    private function handleRemoveDishImage($dishModel, $anchor)
    {
        $dish_id = (int)($_POST['remove_dish_image'] ?? 0);
        $current_category_id = (int)($_POST['current_category_id'] ?? 0);

        try {
            // Récupérer le plat
            $stmt = $this->pdo->prepare("SELECT * FROM plats WHERE id = ?");
            $stmt->execute([$dish_id]);
            $dish = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($dish && !empty($dish['image'])) {
                // Supprimer le fichier
                $dishModel->deleteImage($dish['image']);

                // Mettre à jour la base (image = NULL)
                $dishModel->update($dish_id, $dish['name'], $dish['price'], $dish['description'], null);

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
     * Gestion de l'upload d'images
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
                    // Préparer le tableau de fichier
                    $file = [
                        'name' => $_FILES['card_images']['name'][$index],
                        'type' => $_FILES['card_images']['type'][$index],
                        'tmp_name' => $_FILES['card_images']['tmp_name'][$index],
                        'error' => $_FILES['card_images']['error'][$index],
                        'size' => $_FILES['card_images']['size'][$index]
                    ];

                    // Uploader l'image
                    $filename = $carteImageModel->uploadImage($file);

                    // Enregistrer en base
                    $carteImageModel->add($admin_id, $filename, $name);

                    $uploadCount++;
                    error_log("File uploaded successfully: $name");
                } catch (Exception $e) {
                    $errorCount++;
                    error_log("Error uploading file $name: " . $e->getMessage());
                }
            }
        }

        // Messages de résultat
        if ($uploadCount > 0) {
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
     * Gestion de la réorganisation des images
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
            $this->addSuccessMessage("Ordre des images mis à jour avec succès.", 'images-list');

            // IMPORTANT : Garder l'accordéon "Images de la carte" ouvert
            $_SESSION['close_accordion'] = 'mode-selector-content'; // Fermer le sélecteur de mode
            $_SESSION['open_accordion'] = 'images-list-content'; // Ouvrir la liste des images
            // NE PAS fermer l'accordéon secondaire pour qu'il reste ouvert

        } catch (Exception $e) {
            $this->addErrorMessage("Erreur: " . $e->getMessage(), 'images-list');
            $_SESSION['open_accordion'] = 'images-list-content'; // Ouvrir même en cas d'erreur
        }

        $this->redirectToEditCard($anchor);
    }

    /**
     * Récupère les données pour le mode éditable
     */
    private function getEditableModeData($admin_id, $categoryModel, $dishModel, $messages, $error_fields, $old_input)
    {
        $categories = $categoryModel->getAllByAdmin($admin_id);

        foreach ($categories as &$cat) {
            $cat['plats'] = $dishModel->getAllByCategory($cat['id']);
        }

        // Mettre en cache pour les opérations de plat
        $_SESSION['categories_cache'] = $categories;

        return [
            'currentMode' => 'editable',
            'categories' => $categories,
            'success_message' => $messages['success_message'] ?? null,
            'error_message' => $messages['error_message'] ?? null,
            'error_fields' => $error_fields,
            'old_input' => $old_input,
            'anchor' => $messages['anchor'] ?? null,
            'scroll_delay' => $messages['scroll_delay'] ?? $this->scrollDelay
        ];
    }

    /**
     * Récupère les données pour le mode images
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
     * Redirection vers la page d'édition
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

    /**
     * Récupère une catégorie par ID (utilitaire)
     */
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

    /**
     * Récupère un plat par ID (utilitaire)
     */
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

    /**
     * Méthode de test directe
     */
    public function testDelete()
    {
        error_log("=== TEST DELETE METHOD CALLED ===");

        // Debug complet
        error_log("REQUEST METHOD: " . $_SERVER['REQUEST_METHOD']);
        error_log("POST data: " . print_r($_POST, true));
        error_log("GET data: " . print_r($_GET, true));
        error_log("SESSION data: " . print_r($_SESSION, true));

        // Vérifier si on est dans une requête de suppression
        if (isset($_POST['delete_image'])) {
            error_log("DELETE_IMAGE POST DETECTED!");
            error_log("Image ID: " . ($_POST['image_id'] ?? 'NOT SET'));

            // Essayer une suppression directe
            $admin_id = $_SESSION['admin_id'] ?? 0;
            $image_id = (int)($_POST['image_id'] ?? 0);

            error_log("Admin ID: $admin_id");
            error_log("Image ID to delete: $image_id");

            if ($image_id > 0 && $admin_id > 0) {
                try {
                    // Suppression directe
                    $stmt = $this->pdo->prepare("DELETE FROM card_images WHERE id = ? AND admin_id = ?");
                    $stmt->execute([$image_id, $admin_id]);

                    $rowCount = $stmt->rowCount();
                    error_log("Rows deleted: $rowCount");

                    if ($rowCount > 0) {
                        echo "SUCCESS: Image deleted from database<br>";
                    } else {
                        echo "ERROR: No rows affected<br>";
                    }
                } catch (Exception $e) {
                    error_log("Exception: " . $e->getMessage());
                    echo "EXCEPTION: " . $e->getMessage() . "<br>";
                }
            }
        }

        // Afficher un formulaire de test
?>
        <!DOCTYPE html>
        <html>

        <head>
            <title>Test Delete</title>
        </head>

        <body>
            <h1>Test Suppression Image</h1>

            <form method="post">
                <input type="hidden" name="delete_image" value="1">
                <label>Image ID: <input type="number" name="image_id" required></label><br>
                <button type="submit">Tester suppression</button>
            </form>

            <hr>

            <h2>Images existantes :</h2>
            <?php
            $admin_id = $_SESSION['admin_id'] ?? 0;
            if ($admin_id > 0) {
                $stmt = $this->pdo->prepare("SELECT * FROM card_images WHERE admin_id = ? ORDER BY id DESC");
                $stmt->execute([$admin_id]);
                $images = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($images as $image) {
                    echo "<div>";
                    echo "ID: " . $image['id'] . " - " . $image['original_name'];
                    echo " <small>(" . $image['filename'] . ")</small>";
                    echo "</div>";
                }
            }
            ?>
        </body>

        </html>
<?php
    }
}
