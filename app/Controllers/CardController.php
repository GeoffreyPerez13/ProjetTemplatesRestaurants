<?php

// Inclusion des fichiers nécessaires
require_once __DIR__ . '/BaseController.php';         // Contrôleur de base avec les fonctionnalités communes
require_once __DIR__ . '/../Models/Category.php';     // Modèle pour gérer les catégories
require_once __DIR__ . '/../Models/Dish.php';         // Modèle pour gérer les plats
require_once __DIR__ . '/../Models/CardImage.php';    // Modèle pour gérer les images de carte
require_once __DIR__ . '/../Models/Admin.php';        // Modèle pour gérer les administrateurs
require_once __DIR__ . '/../Helpers/Validator.php';   // Helper pour valider les données des formulaires

/**
 * Contrôleur pour la gestion de la carte du restaurant
 * Permet d'éditer, visualiser et gérer les catégories, plats et images de la carte
 */
class CardController extends BaseController
{
    /**
     * Constructeur
     * Initialise le contrôleur avec la connexion PDO
     * Définit le délai de scroll à 5 secondes pour tous les messages
     * @param PDO $pdo Instance de connexion à la base de données
     */
    public function __construct($pdo)
    {
        parent::__construct($pdo);  // Appelle le constructeur du parent (BaseController)
        $this->setScrollDelay(3500); // Définit un délai de 3,5 secondes pour le défilement
    }

    /**
     * Page d'édition de la carte (catégories + plats)
     * Méthode principale pour modifier la structure et le contenu de la carte
     * 
     * Processus:
     * 1. Vérifie que l'admin est connecté
     * 2. Récupère les données selon le mode (éditable ou images)
     * 3. Traite les formulaires POST
     * 4. Récupère les messages flash
     * 5. Affiche la vue d'édition avec les données
     */
    public function edit()
    {
        // Étape 1: Vérification de l'authentification
        $this->requireLogin();  // Redirige vers login.php si non connecté

        // Étape 2: Récupération de l'ID de l'admin depuis la session
        // $_SESSION['admin_id'] est défini lors de la connexion dans AdminController
        $admin_id = $_SESSION['admin_id'];

        // Étape 3: Instanciation des modèles nécessaires
        $adminModel = new Admin($this->pdo);           // Pour les infos admin (mode de carte)
        $categoryModel = new Category($this->pdo);     // Pour les opérations sur les catégories
        $dishModel = new Dish($this->pdo);             // Pour les opérations sur les plats
        $carteImageModel = new CardImage($this->pdo);  // Pour les images de carte (mode images)

        // Étape 4: Récupération du mode actuel de la carte
        // Deux modes possibles: 'editable' (catégories/plats) ou 'images' (PDF/images)
        $currentMode = $adminModel->getCarteMode($admin_id);

        // Étape 5: Traitement des formulaires POST (si soumis)
        // La méthode handlePostActions gère toutes les actions et redirige
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handlePostActions($categoryModel, $dishModel, $carteImageModel, $adminModel, $admin_id, $currentMode);
        }

        // Étape 6: Récupération des messages flash de session
        // Utilise la méthode héritée de BaseController pour centraliser la gestion
        $messages = $this->getFlashMessages();
        extract($messages); // Crée les variables $success_message, $error_message, $scroll_delay, $anchor

        // Étape 7: Récupération des autres données de session spécifiques
        // Ces données ne sont pas gérées par getFlashMessages() car spécifiques à cette page
        $error_fields = $_SESSION['error_fields'] ?? [];
        $old_input = $_SESSION['old_input'] ?? [];

        // Étape 8: Nettoyage des variables de session spécifiques après lecture
        // Évite que les données persistent après rafraîchissement
        unset($_SESSION['error_fields'], $_SESSION['old_input']);

        // Étape 9: Préparation des données selon le mode
        if ($currentMode === 'editable') {
            // Mode éditable: récupère les catégories et leurs plats

            // Récupère toutes les catégories de cet admin
            $categories = $categoryModel->getAllByAdmin($admin_id);

            // Pour chaque catégorie, récupère les plats associés
            // &$cat passe la référence pour modifier directement le tableau
            foreach ($categories as &$cat) {
                $cat['plats'] = $dishModel->getAllByCategory($cat['id']);
            }

            // Préparation des données pour la vue
            $data = [
                'currentMode' => $currentMode,    // 'editable'
                'categories' => $categories,      // Tableau des catégories avec plats
                'success_message' => $success_message,    // Message de succès
                'error_message' => $error_message,        // Message d'erreur
                'error_fields' => $error_fields,         // Erreurs de validation
                'old_input' => $old_input,               // Anciennes valeurs des formulaires
                'anchor' => $anchor,                     // Ancre pour le scroll
                'scroll_delay' => $scroll_delay          // Délai pour le défilement
            ];
        } else {
            // Mode images: récupère les images/PDF de la carte
            $carteImages = $carteImageModel->getAllByAdmin($admin_id);

            $data = [
                'currentMode' => $currentMode,    // 'images'
                'carteImages' => $carteImages,    // Tableau des images
                'success_message' => $success_message,    // Message de succès
                'error_message' => $error_message,        // Message d'erreur
                'error_fields' => $error_fields,         // Erreurs de validation
                'old_input' => $old_input,               // Anciennes valeurs des formulaires
                'anchor' => $anchor,                     // Ancre pour le scroll
                'scroll_delay' => $scroll_delay          // Délai pour le défilement
            ];
        }

        // Étape 10: Affichage de la vue avec les données préparées
        $this->render('admin/edit-card', $data);
    }

    /**
     * Gestion centralisée des actions POST
     * Traite toutes les soumissions de formulaires et gère les redirections
     * Utilise les méthodes addSuccessMessage() et addErrorMessage() de BaseController
     * pour une gestion cohérente des messages avec délai de défilement
     * 
     * @param Category $categoryModel Instance du modèle Category
     * @param Dish $dishModel Instance du modèle Dish
     * @param CardImage $carteImageModel Instance du modèle CardImage
     * @param Admin $adminModel Instance du modèle Admin
     * @param int $admin_id ID de l'administrateur connecté
     * @param string $currentMode Mode actuel de la carte ('editable' ou 'images')
     */
    private function handlePostActions($categoryModel, $dishModel, $carteImageModel, $adminModel, $admin_id, $currentMode)
    {
        try {
            // Récupérer l'ancre à partir du POST pour le défilement ciblé
            $anchor = $_POST['anchor'] ?? '';

            // ==================== CHANGEMENT DE MODE ====================
            if (isset($_POST['change_mode'])) {
                $newMode = $_POST['carte_mode'];
                if (in_array($newMode, ['editable', 'images'])) {
                    $adminModel->updateCarteMode($admin_id, $newMode);
                    $this->addSuccessMessage("Mode de carte changé avec succès", 'mode-selector');
                    $_SESSION['close_accordion'] = 'mode-selector-content';
                }

                // Redirection immédiate
                $this->redirectWithAnchor($anchor);
                return;
            }

            // ==================== MODE ÉDITABLE ====================
            if ($currentMode === 'editable') {
                $this->handleEditableMode($categoryModel, $dishModel, $admin_id, $anchor);
            }

            // ==================== MODE IMAGES ====================
            elseif ($currentMode === 'images') {
                $this->handleImagesMode($carteImageModel, $admin_id, $anchor);
            }
        } catch (Exception $e) {
            // Gestion globale des exceptions
            $this->addErrorMessage("Erreur : " . $e->getMessage(), $anchor);
        }

        // Redirection avec ancre
        $this->redirectWithAnchor($anchor);
    }

    /**
     * Gestion des actions en mode éditable
     */
    private function handleEditableMode($categoryModel, $dishModel, $admin_id, $anchor)
    {
        // --- SUPPRESSION D'UNE CATÉGORIE ---
        if (isset($_POST['delete_category'])) {
            $this->handleDeleteCategory($categoryModel, $admin_id, $anchor);
        }

        // --- AJOUT D'UNE CATÉGORIE ---
        elseif (isset($_POST['new_category'])) {
            $this->handleAddCategory($categoryModel, $admin_id, $anchor);
        }

        // --- MODIFICATION D'UNE CATÉGORIE ---
        elseif (isset($_POST['edit_category'])) {
            $this->handleEditCategory($categoryModel, $admin_id, $anchor);
        }

        // --- SUPPRESSION DE L'IMAGE DE LA CATÉGORIE ---
        elseif (isset($_POST['remove_category_image'])) {
            $this->handleRemoveCategoryImage($categoryModel, $admin_id, $anchor);
        }

        // --- AJOUT D'UN PLAT ---
        elseif (isset($_POST['new_dish'])) {
            $this->handleAddDish($dishModel, $anchor);
        }

        // --- MODIFICATION D'UN PLAT ---
        elseif (isset($_POST['edit_dish'])) {
            $this->handleEditDish($dishModel, $anchor);
        }

        // --- SUPPRESSION DE L'IMAGE D'UN PLAT ---
        elseif (isset($_POST['remove_dish_image'])) {
            $this->handleRemoveDishImage($dishModel, $anchor);
        }

        // --- SUPPRESSION D'UN PLAT ---
        elseif (isset($_POST['delete_dish'])) {
            $this->handleDeleteDish($dishModel, $anchor);
        }
    }

    /**
     * Gestion des actions en mode images
     */
    private function handleImagesMode($carteImageModel, $admin_id, $anchor)
    {
        // --- UPLOAD D'IMAGES ---
        if (isset($_POST['upload_images']) && isset($_FILES['card_images'])) {
            $this->handleUploadImages($carteImageModel, $admin_id, $anchor);
        }

        // --- SUPPRESSION D'UNE IMAGE ---
        elseif (isset($_POST['delete_image'])) {
            $this->handleDeleteImage($carteImageModel, $admin_id, $anchor);
        }

        // --- RÉORGANISATION DES IMAGES ---
        elseif (isset($_POST['update_image_order'])) {
            $this->handleReorderImages($carteImageModel, $admin_id, $anchor);
        }
    }

    /**
     * Gestion de la suppression d'une catégorie
     */
    private function handleDeleteCategory($categoryModel, $admin_id, $anchor)
    {
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

                // Utilisation de addSuccessMessage() avec ancre spécifique
                $this->addSuccessMessage("Catégorie et tous ses plats supprimés avec succès.", 'categories-grid');
                $_SESSION['close_accordion'] = 'mode-selector-content';
            } catch (Exception $e) {
                // Utilisation de addErrorMessage() pour les erreurs
                $this->addErrorMessage("Erreur lors de la suppression : " . $e->getMessage(), $anchor);
            }
        } else {
            $this->addErrorMessage("Catégorie non trouvée ou vous n'avez pas les droits.", $anchor);
        }
    }

    /**
     * Gestion de l'ajout d'une catégorie
     */
    private function handleAddCategory($categoryModel, $admin_id, $anchor)
    {
        $name = trim($_POST['new_category']);

        // Validation de la catégorie
        $validator = new Validator($_POST);
        $validator->rules([
            'new_category' => ['required', 'max:100']
        ]);

        if (!$validator->validate()) {
            // Gestion des erreurs de validation
            $this->addErrorMessage("Veuillez vérifier les champs du formulaire.", 'new-category');
            $_SESSION['error_fields'] = $validator->errors();
            $_SESSION['old_input'] = $_POST;
            $_SESSION['open_accordion'] = 'new-category-content';
        } else {
            $imagePath = null;

            // Gestion du téléchargement d'image
            if (isset($_FILES['category_image']) && $_FILES['category_image']['error'] === UPLOAD_ERR_OK) {
                $imagePath = $categoryModel->uploadImage($_FILES['category_image']);
            }

            // Création de la catégorie
            $categoryModel->create($admin_id, $name, $imagePath);
            $categoryId = $this->pdo->lastInsertId();

            // Message de succès avec ancre vers la nouvelle catégorie
            $this->addSuccessMessage("Catégorie ajoutée avec succès.", 'category-' . $categoryId);
            $_SESSION['close_accordion'] = 'mode-selector-content';
            $_SESSION['close_accordion_secondary'] = 'new-category-content';
        }
    }

    /**
     * Gestion de la modification d'une catégorie
     */
    private function handleEditCategory($categoryModel, $admin_id, $anchor)
    {
        $category_id = (int)$_POST['category_id'];
        $new_name = trim($_POST['edit_category_name'] ?? '');

        // Validation de la catégorie
        $validator = new Validator($_POST);
        $validator->rules([
            'edit_category_name' => ['required', 'max:100']
        ]);

        if (!$validator->validate()) {
            $this->addErrorMessage("Veuillez vérifier les champs du formulaire.", 'category-' . $category_id);
            $_SESSION['error_fields'] = $validator->errors();
            $_SESSION['old_input'] = $_POST;
            $_SESSION['open_accordion'] = 'edit-category-' . $category_id;
        } else {
            $imagePath = null;
            $hasNewImage = false;

            // Gestion de la nouvelle image
            if (isset($_FILES['edit_category_image']) && $_FILES['edit_category_image']['error'] === UPLOAD_ERR_OK) {
                $imagePath = $categoryModel->uploadImage($_FILES['edit_category_image']);
                $hasNewImage = true;

                // Suppression de l'ancienne image si elle existe
                $existingCategory = $this->getCategoryById($categoryModel, $category_id, $admin_id);
                if ($existingCategory && !empty($existingCategory['image'])) {
                    $categoryModel->deleteImage($existingCategory['image']);
                }
            }

            // Mise à jour de la catégorie
            $categoryModel->update($category_id, $new_name, $imagePath);
            $this->addSuccessMessage("Catégorie modifiée avec succès.", 'category-' . $category_id);
            $_SESSION['close_accordion'] = 'mode-selector-content';
            $_SESSION['close_accordion_secondary'] = 'edit-category-' . $category_id;
        }
    }

    /**
     * Gestion de la suppression de l'image d'une catégorie
     */
    private function handleRemoveCategoryImage($categoryModel, $admin_id, $anchor)
    {
        $category_id = (int)$_POST['remove_category_image'];

        $category = $categoryModel->getById($category_id, $admin_id);

        if ($category && !empty($category['image'])) {
            $categoryModel->deleteImage($category['image']);
            $categoryModel->update($category_id, $category['name'], '');
            $this->addSuccessMessage("Image de la catégorie supprimée avec succès.", 'category-' . $category_id);
            $_SESSION['close_accordion'] = 'mode-selector-content';
            $_SESSION['close_accordion_secondary'] = 'edit-category-' . $category_id;
        } else {
            $this->addErrorMessage("Cette catégorie n'a pas d'image à supprimer.", 'category-' . $category_id);
        }
    }

    /**
     * Gestion de l'ajout d'un plat
     */
    private function handleAddDish($dishModel, $anchor)
    {
        $category_id = (int)$_POST['category_id'];

        // Validation du plat
        $validator = new Validator($_POST);
        $validator->rules([
            'dish_name' => ['required', 'max:100'],
            'dish_price' => ['required', 'numeric', 'min_value:0.01', 'max_value:999.99'],
            'dish_description' => ['max:500']
        ]);

        if (!$validator->validate()) {
            $this->addErrorMessage("Veuillez vérifier les champs du formulaire.", 'category-' . $category_id);
            $_SESSION['error_fields'] = $validator->errors();
            $_SESSION['old_input'] = $_POST;
            $_SESSION['open_accordion'] = 'add-dish-' . $category_id;
        } else {
            $name = trim($_POST['dish_name']);
            $price = floatval($_POST['dish_price']);
            $description = trim($_POST['dish_description'] ?? '');
            $imagePath = null;

            // Gestion de l'image du plat
            if (isset($_FILES['dish_image']) && $_FILES['dish_image']['error'] === UPLOAD_ERR_OK) {
                $imagePath = $dishModel->uploadImage($_FILES['dish_image']);
            }

            // Création du plat
            $dishObject = $dishModel->create($category_id, $name, $price, $description, $imagePath);
            $dishId = $dishObject->getId();

            // Message de succès avec ancre vers le nouveau plat
            $this->addSuccessMessage("Plat ajouté avec succès.", 'dish-' . $dishId);
            $_SESSION['close_accordion'] = 'mode-selector-content';
            $_SESSION['open_accordion'] = 'edit-dishes-' . $category_id;
            $_SESSION['close_dish_accordion'] = 'dish-' . $dishId;
            $_SESSION['close_accordion_secondary'] = 'add-dish-' . $category_id;
        }
    }

    /**
     * Gestion de la modification d'un plat
     */
    private function handleEditDish($dishModel, $anchor)
    {
        $dish_id = (int)$_POST['dish_id'];
        $current_category_id = (int)($_POST['current_category_id'] ?? 0);

        // Validation du plat
        $validator = new Validator($_POST);
        $validator->rules([
            'dish_name' => ['required', 'max:100'],
            'dish_price' => ['required', 'numeric', 'min_value:0.01', 'max_value:999.99'],
            'dish_description' => ['max:500']
        ]);

        if (!$validator->validate()) {
            $this->addErrorMessage("Veuillez vérifier les champs du formulaire.", 'dish-' . $dish_id);

            // Préfixer les erreurs avec l'ID du plat pour éviter les conflits
            $errors = $validator->errors();
            $prefixedErrors = [];
            foreach ($errors as $field => $hasError) {
                $prefixedErrors[$field . '_' . $dish_id] = $hasError;
            }

            $_SESSION['error_fields'] = $prefixedErrors;
            $_SESSION['old_input'] = $_POST;
            $_SESSION['open_accordion'] = 'dish-' . $dish_id;
        } else {
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

            // Mise à jour du plat
            $dishModel->update($dish_id, $name, $price, $description, $imagePath);
            $this->addSuccessMessage("Plat modifié avec succès.", 'dish-' . $dish_id);
            $_SESSION['close_accordion'] = 'mode-selector-content';
            $_SESSION['close_dish_accordion'] = 'dish-' . $dish_id;
            $_SESSION['open_accordion'] = 'edit-dishes-' . $current_category_id;
        }
    }

    /**
     * Gestion de la suppression de l'image d'un plat
     */
    private function handleRemoveDishImage($dishModel, $anchor)
    {
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

                $this->addSuccessMessage("Image du plat supprimée avec succès.", 'dish-' . $dish_id);
            } else {
                $this->addErrorMessage("Ce plat n'a pas d'image à supprimer.", 'dish-' . $dish_id);
            }
        } else {
            $this->addErrorMessage("Plat non trouvé.", 'dish-' . $dish_id);
        }

        $_SESSION['close_accordion'] = 'mode-selector-content';
        $_SESSION['close_dish_accordion'] = 'dish-' . $dish_id;
        $_SESSION['open_accordion'] = 'edit-dishes-' . $current_category_id;
    }

    /**
     * Gestion de la suppression d'un plat
     */
    private function handleDeleteDish($dishModel, $anchor)
    {
        $dishId = (int)$_POST['delete_dish'];
        $current_category_id = (int)($_POST['current_category_id'] ?? 0);

        $existingDish = $this->getDishById($dishModel, $dishId);
        if ($existingDish && !empty($existingDish['image'])) {
            $dishModel->deleteImage($existingDish['image']);
        }

        $dishModel->delete($dishId);
        $this->addSuccessMessage("Plat supprimé avec succès.", 'category-' . $current_category_id);
        $_SESSION['close_accordion'] = 'mode-selector-content';
        $_SESSION['open_accordion'] = 'edit-dishes-' . $current_category_id;
    }

    /**
     * Gestion de l'upload d'images
     */
    private function handleUploadImages($carteImageModel, $admin_id, $anchor)
    {
        $uploadedFiles = $_FILES['card_images'];
        $uploadCount = 0;
        $errorCount = 0;

        // Vérifier s'il y a des fichiers
        if (!empty($uploadedFiles['name'][0])) {
            // Traiter chaque fichier uploadé
            for ($i = 0; $i < count($uploadedFiles['name']); $i++) {
                if ($uploadedFiles['error'][$i] === UPLOAD_ERR_OK) {
                    try {
                        // Uploader l'image via le modèle
                        $filename = $carteImageModel->uploadImage([
                            'name' => $uploadedFiles['name'][$i],
                            'type' => $uploadedFiles['type'][$i],
                            'tmp_name' => $uploadedFiles['tmp_name'][$i],
                            'error' => $uploadedFiles['error'][$i],
                            'size' => $uploadedFiles['size'][$i]
                        ]);

                        // Enregistrer en base de données
                        $carteImageModel->add($admin_id, $filename, $uploadedFiles['name'][$i]);
                        $uploadCount++;
                    } catch (Exception $e) {
                        $errorCount++;
                        error_log("Erreur upload image {$uploadedFiles['name'][$i]}: " . $e->getMessage());
                    }
                } elseif ($uploadedFiles['error'][$i] !== UPLOAD_ERR_NO_FILE) {
                    $errorCount++;
                }
            }

            if ($uploadCount > 0) {
                $this->addSuccessMessage("$uploadCount image(s) téléchargée(s) avec succès.", 'upload-images');
                if ($errorCount > 0) {
                    $this->addErrorMessage("$errorCount image(s) n'ont pas pu être téléchargées.", 'upload-images');
                }
                $_SESSION['close_accordion'] = 'mode-selector-content';
            } else {
                $this->addErrorMessage("Aucune image n'a été téléchargée. Vérifiez les formats (JPG, PNG, GIF, WebP, PDF) et tailles (max 5MB).", 'upload-images');
            }
        } else {
            $this->addErrorMessage("Veuillez sélectionner au moins un fichier.", 'upload-images');
        }
    }

    /**
     * Gestion de la suppression d'une image
     */
    private function handleDeleteImage($carteImageModel, $admin_id, $anchor)
    {
        $image_id = (int)$_POST['image_id'];

        // Vérifier que l'image appartient bien à l'admin
        $image = $carteImageModel->getById($image_id, $admin_id);

        if ($image) {
            try {
                // Supprimer le fichier physique
                if ($carteImageModel->deleteImageFile($image['filename'])) {
                    // Supprimer l'entrée en base de données
                    $carteImageModel->delete($image_id, $admin_id);

                    $this->addSuccessMessage("Image supprimée avec succès.", 'images-list');
                    $_SESSION['close_accordion'] = 'mode-selector-content';
                } else {
                    $this->addErrorMessage("Erreur lors de la suppression du fichier physique.", 'images-list');
                }
            } catch (Exception $e) {
                $this->addErrorMessage("Erreur lors de la suppression : " . $e->getMessage(), 'images-list');
            }
        } else {
            $this->addErrorMessage("Image non trouvée ou vous n'avez pas les droits.", 'images-list');
        }
    }

    /**
     * Gestion de la réorganisation des images
     */
    private function handleReorderImages($carteImageModel, $admin_id, $anchor)
    {
        $newOrder = $_POST['new_order'] ?? '';

        // Décoder le JSON
        $orderArray = json_decode($newOrder, true);

        if (is_array($orderArray) && !empty($orderArray)) {
            try {
                $carteImageModel->updateImageOrder($admin_id, $orderArray);
                $this->addSuccessMessage("Ordre des images mis à jour avec succès.", 'images-list');
                $_SESSION['close_accordion'] = 'mode-selector-content';
            } catch (Exception $e) {
                $this->addErrorMessage("Erreur lors de la mise à jour de l'ordre : " . $e->getMessage(), 'images-list');
            }
        } else {
            $this->addErrorMessage("Aucun ordre valide reçu.", 'images-list');
        }
    }

    /**
     * Redirection avec ancre
     */
    private function redirectWithAnchor($anchor)
    {
        $redirectUrl = '?page=edit-card';
        if (!empty($anchor)) {
            $redirectUrl .= '&anchor=' . urlencode($anchor);
        }

        header('Location: ' . $redirectUrl);
        exit;
    }

    /**
     * Récupère un plat directement depuis la base de données
     * Alternative à getDishById qui utilise le cache de session
     * Méthode utilisée pour des opérations critiques nécessitant des données fraîches
     * 
     * @param Dish $dishModel Instance du modèle Dish
     * @param int $dish_id ID du plat à récupérer
     * @return array|null Données du plat ou null si non trouvé
     */
    private function getDishByIdDirect($dishModel, $dish_id)
    {
        // Requête SQL directe (plus fiable que le cache)
        $stmt = $this->pdo->prepare("SELECT * FROM plats WHERE id = ?");
        $stmt->execute([$dish_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);  // Retourne un tableau associatif ou false
    }

    /**
     * Page d'aperçu de la carte
     * Version lecture seule pour visualiser la carte complète
     * Utilisée pour la prévisualisation avant publication
     */
    public function view()
    {
        // Vérification authentification
        $this->requireLogin();
        $admin_id = $_SESSION['admin_id'];

        // Instanciation des modèles
        $adminModel = new Admin($this->pdo);
        $categoryModel = new Category($this->pdo);
        $dishModel = new Dish($this->pdo);
        $carteImageModel = new CardImage($this->pdo);

        // Récupération du mode
        $currentMode = $adminModel->getCarteMode($admin_id);

        // Préparation des données selon le mode
        if ($currentMode === 'editable') {
            // Mode éditable: structure catégorie/plat
            $categories = $categoryModel->getAllByAdmin($admin_id);

            // Ajout des plats à chaque catégorie
            foreach ($categories as &$cat) {
                $cat['plats'] = $dishModel->getAllByCategory($cat['id']);
            }

            // Mise en cache en session (utilisé par getDishById)
            $_SESSION['categories_cache'] = $categories;

            $data = [
                'currentMode' => $currentMode,
                'categories' => $categories
            ];
        } else {
            // Mode images: affichage des images/PDF
            $carteImages = $carteImageModel->getAllByAdmin($admin_id);

            $data = [
                'currentMode' => $currentMode,
                'carteImages' => $carteImages
            ];
        }

        // Affichage de la vue d'aperçu
        $this->render('admin/view-card', $data);
    }

    /**
     * Méthode utilitaire pour récupérer une catégorie par son ID
     * Recherche dans le tableau des catégories de l'admin
     * Utilisée pour vérifier les droits d'accès avant modification/suppression
     * 
     * @param Category $categoryModel Instance du modèle Category
     * @param int $category_id ID de la catégorie recherchée
     * @param int $admin_id ID de l'administrateur (vérification des droits)
     * @return array|null Données de la catégorie ou null si non trouvée
     */
    private function getCategoryById($categoryModel, $category_id, $admin_id)
    {
        // Récupère toutes les catégories de cet admin
        $categories = $categoryModel->getAllByAdmin($admin_id);

        // Recherche linéaire dans le tableau
        foreach ($categories as $cat) {
            if ($cat['id'] == $category_id) {
                return $cat;  // Retourne la catégorie trouvée
            }
        }
        return null;  // Non trouvée
    }

    /**
     * Méthode utilitaire pour récupérer un plat par son ID
     * Recherche dans le cache de session des catégories
     * Plus rapide que les requêtes DB mais utilise des données en cache
     * 
     * @param Dish $dishModel Instance du modèle Dish (non utilisée ici)
     * @param int $dish_id ID du plat recherché
     * @return array|null Données du plat ou null si non trouvé
     */
    private function getDishById($dishModel, $dish_id)
    {
        // Récupération du cache depuis la session
        $allCategories = $_SESSION['categories_cache'] ?? [];

        // Parcours double: catégories -> plats
        foreach ($allCategories as $cat) {
            if (isset($cat['plats'])) {
                foreach ($cat['plats'] as $plat) {
                    if ($plat['id'] == $dish_id) {
                        return $plat;  // Plat trouvé
                    }
                }
            }
        }
        return null;  // Plat non trouvé
    }
}
