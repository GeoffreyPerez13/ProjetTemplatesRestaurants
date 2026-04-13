<?php

namespace App\Controllers;

use App\Models\Category;
use App\Models\Dish;
use App\Models\Allergene;
use App\Helpers\Validator;

/**
 * Classe CardController - Gestion de la carte (catégories et plats)
 */
class CardController extends BaseController
{
    /**
     * Afficher la page de gestion de la carte
     */
    public function index(): void
    {
        $this->requireAuth();

        $adminId = $this->getAuthId();
        
        // Seed les allergènes si nécessaire
        Allergene::seed();
        
        $categories = Category::getAllByAdmin($adminId);
        $allergenes = Allergene::getAll();

        $this->render('admin.edit-card', [
            'categories' => $categories,
            'allergenes' => $allergenes,
            'page_title' => 'Gérer ma carte',
            'csrf_token' => $this->generateCsrfToken()
        ]);
    }

    /**
     * Créer une catégorie
     */
    public function createCategory(): void
    {
        $this->requireAuth();

        if (!$this->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            $this->jsonError('Token de sécurité invalide');
        }

        $validator = new Validator($_POST);
        $validator->required('name', 'Le nom de la catégorie est requis.')
                  ->min('name', 2, 'Le nom doit contenir au moins 2 caractères.')
                  ->max('name', 100, 'Le nom ne doit pas dépasser 100 caractères.');

        if ($validator->fails()) {
            $this->jsonError($validator->first('name'), $validator->errors());
        }

        $adminId = $this->getAuthId();
        
        // Gérer l'upload d'image si présente
        $imageName = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $imageName = $this->uploadCategoryImage($_FILES['image']);
            if (!$imageName) {
                $this->jsonError('Erreur lors de l\'upload de l\'image');
            }
        }

        $categoryId = Category::create([
            'admin_id' => $adminId,
            'name' => $_POST['name'],
            'image' => $imageName,
            'display_order' => Category::countByAdmin($adminId)
        ]);

        $category = Category::findById($categoryId, $adminId);

        $this->jsonSuccess('Catégorie créée avec succès', [
            'category' => $category
        ]);
    }

    /**
     * Mettre à jour une catégorie
     */
    public function updateCategory(): void
    {
        $this->requireAuth();

        if (!$this->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            $this->jsonError('Token de sécurité invalide');
        }

        $categoryId = (int) ($_POST['id'] ?? 0);
        $adminId = $this->getAuthId();

        $category = Category::findById($categoryId, $adminId);
        if (!$category) {
            $this->jsonError('Catégorie non trouvée');
        }

        $validator = new Validator($_POST);
        $validator->required('name', 'Le nom de la catégorie est requis.')
                  ->min('name', 2, 'Le nom doit contenir au moins 2 caractères.')
                  ->max('name', 100, 'Le nom ne doit pas dépasser 100 caractères.');

        if ($validator->fails()) {
            $this->jsonError($validator->first('name'), $validator->errors());
        }

        // Gérer l'upload d'image si présente
        $imageName = $category['image'];
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $newImage = $this->uploadCategoryImage($_FILES['image']);
            if ($newImage) {
                // Supprimer l'ancienne image
                if ($imageName) {
                    $oldPath = __DIR__ . '/../../public/uploads/categories/' . $imageName;
                    if (file_exists($oldPath)) {
                        unlink($oldPath);
                    }
                }
                $imageName = $newImage;
            }
        }

        Category::update($categoryId, $adminId, [
            'name' => $_POST['name'],
            'image' => $imageName
        ]);

        $updatedCategory = Category::findById($categoryId, $adminId);

        $this->jsonSuccess('Catégorie mise à jour avec succès', [
            'category' => $updatedCategory
        ]);
    }

    /**
     * Supprimer une catégorie
     */
    public function deleteCategory(): void
    {
        $this->requireAuth();

        if (!$this->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            $this->jsonError('Token de sécurité invalide');
        }

        $categoryId = (int) ($_POST['id'] ?? 0);
        $adminId = $this->getAuthId();

        if (Category::delete($categoryId, $adminId)) {
            $this->jsonSuccess('Catégorie supprimée avec succès');
        } else {
            $this->jsonError('Impossible de supprimer la catégorie');
        }
    }

    /**
     * Réorganiser les catégories
     */
    public function reorderCategories(): void
    {
        $this->requireAuth();

        if (!$this->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            $this->jsonError('Token de sécurité invalide');
        }

        $order = json_decode($_POST['order'] ?? '[]', true);
        $adminId = $this->getAuthId();

        if (Category::updateOrder($order, $adminId)) {
            $this->jsonSuccess('Ordre des catégories mis à jour');
        } else {
            $this->jsonError('Erreur lors de la mise à jour de l\'ordre');
        }
    }

    /**
     * Créer un plat
     */
    public function createDish(): void
    {
        $this->requireAuth();

        if (!$this->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            $this->jsonError('Token de sécurité invalide');
        }

        $validator = new Validator($_POST);
        $validator->required('category_id', 'La catégorie est requise.')
                  ->required('name', 'Le nom du plat est requis.')
                  ->min('name', 2, 'Le nom doit contenir au moins 2 caractères.')
                  ->max('name', 200, 'Le nom ne doit pas dépasser 200 caractères.');

        if ($validator->fails()) {
            $errors = $validator->errors();
            $firstError = reset($errors)[0];
            $this->jsonError($firstError, $validator->errors());
        }

        $categoryId = (int) $_POST['category_id'];
        $adminId = $this->getAuthId();

        // Vérifier que la catégorie appartient à l'admin
        $category = Category::findById($categoryId, $adminId);
        if (!$category) {
            $this->jsonError('Catégorie non trouvée');
        }

        // Gérer l'upload d'image si présente
        $imageName = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $imageName = $this->uploadDishImage($_FILES['image']);
            if (!$imageName) {
                $this->jsonError('Erreur lors de l\'upload de l\'image');
            }
        }

        $dishId = Dish::create([
            'category_id' => $categoryId,
            'name' => $_POST['name'],
            'description' => $_POST['description'] ?? null,
            'price' => !empty($_POST['price']) ? (float) $_POST['price'] : null,
            'image' => $imageName,
            'display_order' => Dish::countByCategory($categoryId)
        ]);

        // Associer les allergènes
        if (!empty($_POST['allergenes'])) {
            $allergeneIds = is_array($_POST['allergenes']) ? $_POST['allergenes'] : json_decode($_POST['allergenes'], true);
            Dish::syncAllergenes($dishId, $allergeneIds);
        }

        $dish = Dish::findById($dishId);

        $this->jsonSuccess('Plat créé avec succès', [
            'dish' => $dish
        ]);
    }

    /**
     * Mettre à jour un plat
     */
    public function updateDish(): void
    {
        $this->requireAuth();

        if (!$this->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            $this->jsonError('Token de sécurité invalide');
        }

        $dishId = (int) ($_POST['id'] ?? 0);
        $dish = Dish::findById($dishId);
        
        if (!$dish) {
            $this->jsonError('Plat non trouvé');
        }

        $validator = new Validator($_POST);
        $validator->required('name', 'Le nom du plat est requis.')
                  ->min('name', 2, 'Le nom doit contenir au moins 2 caractères.')
                  ->max('name', 200, 'Le nom ne doit pas dépasser 200 caractères.');

        if ($validator->fails()) {
            $errors = $validator->errors();
            $firstError = reset($errors)[0];
            $this->jsonError($firstError, $validator->errors());
        }

        // Gérer l'upload d'image si présente
        $imageName = $dish['image'];
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $newImage = $this->uploadDishImage($_FILES['image']);
            if ($newImage) {
                // Supprimer l'ancienne image
                if ($imageName) {
                    $oldPath = __DIR__ . '/../../public/uploads/plats/' . $imageName;
                    if (file_exists($oldPath)) {
                        unlink($oldPath);
                    }
                }
                $imageName = $newImage;
            }
        }

        Dish::update($dishId, [
            'name' => $_POST['name'],
            'description' => $_POST['description'] ?? null,
            'price' => !empty($_POST['price']) ? (float) $_POST['price'] : null,
            'image' => $imageName
        ]);

        // Mettre à jour les allergènes
        $allergeneIds = [];
        if (!empty($_POST['allergenes'])) {
            $allergeneIds = is_array($_POST['allergenes']) ? $_POST['allergenes'] : json_decode($_POST['allergenes'], true);
        }
        Dish::syncAllergenes($dishId, $allergeneIds);

        $updatedDish = Dish::findById($dishId);

        $this->jsonSuccess('Plat mis à jour avec succès', [
            'dish' => $updatedDish
        ]);
    }

    /**
     * Supprimer un plat
     */
    public function deleteDish(): void
    {
        $this->requireAuth();

        if (!$this->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            $this->jsonError('Token de sécurité invalide');
        }

        $dishId = (int) ($_POST['id'] ?? 0);

        if (Dish::delete($dishId)) {
            $this->jsonSuccess('Plat supprimé avec succès');
        } else {
            $this->jsonError('Impossible de supprimer le plat');
        }
    }

    /**
     * Réorganiser les plats d'une catégorie
     */
    public function reorderDishes(): void
    {
        $this->requireAuth();

        if (!$this->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            $this->jsonError('Token de sécurité invalide');
        }

        $order = json_decode($_POST['order'] ?? '[]', true);
        $categoryId = (int) ($_POST['category_id'] ?? 0);

        if (Dish::updateOrder($order, $categoryId)) {
            $this->jsonSuccess('Ordre des plats mis à jour');
        } else {
            $this->jsonError('Erreur lors de la mise à jour de l\'ordre');
        }
    }

    /**
     * Upload une image de catégorie
     */
    private function uploadCategoryImage(array $file): ?string
    {
        return $this->uploadImage($file, 'categories');
    }

    /**
     * Upload une image de plat
     */
    private function uploadDishImage(array $file): ?string
    {
        return $this->uploadImage($file, 'plats');
    }

    /**
     * Upload une image (générique)
     */
    private function uploadImage(array $file, string $folder): ?string
    {
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
        $maxSize = 5 * 1024 * 1024; // 5MB

        if (!in_array($file['type'], $allowedTypes)) {
            return null;
        }

        if ($file['size'] > $maxSize) {
            return null;
        }

        $uploadDir = __DIR__ . '/../../public/uploads/' . $folder . '/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '_' . time() . '.' . $extension;
        $destination = $uploadDir . $filename;

        if (move_uploaded_file($file['tmp_name'], $destination)) {
            return $filename;
        }

        return null;
    }
}
