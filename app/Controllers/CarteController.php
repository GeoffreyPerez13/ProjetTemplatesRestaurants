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

    public function edit()
    {
        $this->requireLogin();
        $admin_id = $_SESSION['admin_id'];

        $categoryModel = new Category($this->pdo);
        $dishModel = new Dish($this->pdo);
        $message = null;

        // --- Ajout catégorie ---
        if (!empty($_POST['new_category'])) {
            $name = trim($_POST['new_category']);
            $categoryModel->create($admin_id, $name);
            $message = "Catégorie ajoutée.";
        }

        // --- Suppression catégorie ---
        if (!empty($_POST['delete_category'])) {
            $categoryModel->delete((int)$_POST['delete_category'], $admin_id);
            $message = "Catégorie supprimée.";
        }

        // --- Ajout plat ---
        if (isset($_POST['new_dish'])) { // ✅ isset au lieu de empty
            $category_id = (int)$_POST['category_id'];
            $name = trim($_POST['dish_name']);
            $price = floatval($_POST['dish_price']);
            $dishModel->create($category_id, $name, $price);
            $message = "Plat ajouté.";
        }

        // --- Modification plat ---
        if (isset($_POST['edit_dish'])) {
            $dish_id = (int)$_POST['dish_id'];
            $name = trim($_POST['dish_name']);
            $price = floatval($_POST['dish_price']);
            $dishModel->update($dish_id, $name, $price);
            $message = "Plat modifié.";
        }

        // --- Suppression plat ---
        if (isset($_POST['delete_dish'])) {
            $dishModel->delete((int)$_POST['delete_dish']);
            $message = "Plat supprimé.";
        }

        // --- Récupération des catégories + plats ---
        $categories = $categoryModel->getAllByAdmin($admin_id);

        foreach ($categories as &$cat) {
            $cat['plats'] = $dishModel->getAllByCategory($cat['id']); // ✅ ajout des plats
        }

        $this->render('admin/edit-carte', [
            'categories' => $categories,
            'message' => $message
        ]);
    }
}
