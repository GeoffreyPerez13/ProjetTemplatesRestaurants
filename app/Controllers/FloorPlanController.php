<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../Models/Floor.php';
require_once __DIR__ . '/../Models/RestaurantTable.php';
require_once __DIR__ . '/../Models/RestaurantElement.php';

/**
 * Contrôleur pour la gestion du plan de salle du restaurant
 */
class FloorPlanController extends BaseController
{
    /**
     * Afficher l'éditeur de plan de salle
     */
    public function show()
    {
        $this->requireLogin();
        $this->requireActiveSubscription();

        $admin_id = $_SESSION['admin_id'];
        $floorModel = new Floor($this->pdo);
        $tableModel = new RestaurantTable($this->pdo);
        $elementModel = new RestaurantElement($this->pdo);

        // Récupérer tous les étages
        $floors = $floorModel->getAllByAdmin($admin_id);

        // Si aucun étage n'existe, en créer un par défaut
        if (empty($floors)) {
            $floorModel->createDefault($admin_id);
            $floors = $floorModel->getAllByAdmin($admin_id);
        }

        // Récupérer le floor_id sélectionné (ou le premier par défaut)
        $selected_floor_id = $_GET['floor_id'] ?? $floors[0]['id'];

        // Récupérer les tables et éléments de l'étage sélectionné
        $tables = $tableModel->getAllByFloor($selected_floor_id);
        $elements = $elementModel->getAllByFloor($selected_floor_id);

        $this->render('admin/floor-plan', [
            'floors' => $floors,
            'selected_floor_id' => $selected_floor_id,
            'tables' => $tables,
            'elements' => $elements,
            'csrf_token' => $this->getCsrfToken()
        ]);
    }

    /**
     * Créer un nouvel étage
     */
    public function createFloor()
    {
        $this->requireLogin();
        $this->requireActiveSubscription();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
            exit;
        }

        if (!$this->verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Token CSRF invalide']);
            exit;
        }

        $admin_id = $_SESSION['admin_id'];
        $name = trim($_POST['name'] ?? '');
        $display_order = intval($_POST['display_order'] ?? 0);

        if (empty($name)) {
            echo json_encode(['success' => false, 'message' => 'Le nom est requis']);
            exit;
        }

        $floorModel = new Floor($this->pdo);
        $floor_id = $floorModel->create($admin_id, $name, $display_order);

        echo json_encode([
            'success' => true, 
            'floor_id' => $floor_id, 
            'message' => 'Étage créé avec succès',
            'csrf_token' => $this->getCsrfToken()
        ]);
        exit;
    }

    /**
     * Mettre à jour un étage
     */
    public function updateFloor()
    {
        $this->requireLogin();
        $this->requireActiveSubscription();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
            exit;
        }

        if (!$this->verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Token CSRF invalide']);
            exit;
        }

        $admin_id = $_SESSION['admin_id'];
        $floor_id = intval($_POST['floor_id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $display_order = intval($_POST['display_order'] ?? 0);

        $floorModel = new Floor($this->pdo);

        if (!$floorModel->belongsToAdmin($floor_id, $admin_id)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Accès refusé']);
            exit;
        }

        $floorModel->update($floor_id, $name, $display_order);
        echo json_encode([
            'success' => true, 
            'message' => 'Étage mis à jour',
            'csrf_token' => $this->getCsrfToken()
        ]);
        exit;
    }

    /**
     * Supprimer un étage
     */
    public function deleteFloor()
    {
        $this->requireLogin();
        $this->requireActiveSubscription();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
            exit;
        }

        if (!$this->verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Token CSRF invalide']);
            exit;
        }

        $admin_id = $_SESSION['admin_id'];
        $floor_id = intval($_POST['floor_id'] ?? 0);

        $floorModel = new Floor($this->pdo);

        if (!$floorModel->belongsToAdmin($floor_id, $admin_id)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Accès refusé']);
            exit;
        }

        $floorModel->delete($floor_id);
        echo json_encode([
            'success' => true, 
            'message' => 'Étage supprimé',
            'csrf_token' => $this->getCsrfToken()
        ]);
        exit;
    }

    /**
     * Vider tous les éléments d'un étage (tables + éléments)
     */
    public function clearFloor()
    {
        $this->requireLogin();
        $this->requireActiveSubscription();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
            exit;
        }

        if (!$this->verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Token CSRF invalide']);
            exit;
        }

        $admin_id = $_SESSION['admin_id'];
        $floor_id = intval($_POST['floor_id'] ?? 0);

        $floorModel = new Floor($this->pdo);

        if (!$floorModel->belongsToAdmin($floor_id, $admin_id)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Accès refusé']);
            exit;
        }

        $tableModel = new RestaurantTable($this->pdo);
        $elementModel = new RestaurantElement($this->pdo);

        $tableModel->deleteAllByFloor($floor_id);
        $elementModel->deleteAllByFloor($floor_id);

        echo json_encode([
            'success' => true, 
            'message' => 'Étage vidé',
            'csrf_token' => $this->getCsrfToken()
        ]);
        exit;
    }

    /**
     * Créer une nouvelle table
     */
    public function createTable()
    {
        $this->requireLogin();
        $this->requireActiveSubscription();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
            exit;
        }

        if (!$this->verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Token CSRF invalide']);
            exit;
        }

        $admin_id = $_SESSION['admin_id'];
        $floor_id = intval($_POST['floor_id'] ?? 0);
        $table_number = trim($_POST['table_number'] ?? '');
        $shape = $_POST['shape'] ?? 'round';
        $capacity_min = intval($_POST['capacity_min'] ?? 2);
        $capacity_max = intval($_POST['capacity_max'] ?? 4);
        $position_x = intval($_POST['position_x'] ?? 100);
        $position_y = intval($_POST['position_y'] ?? 100);
        $width = intval($_POST['width'] ?? 60);
        $height = intval($_POST['height'] ?? 60);
        $zone = trim($_POST['zone'] ?? '') ?: null;

        $floorModel = new Floor($this->pdo);
        if (!$floorModel->belongsToAdmin($floor_id, $admin_id)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Accès refusé']);
            exit;
        }

        $tableModel = new RestaurantTable($this->pdo);
        $table_id = $tableModel->create($floor_id, $table_number, $shape, $capacity_min, $capacity_max, $position_x, $position_y, $width, $height, $zone);

        echo json_encode([
            'success' => true, 
            'table_id' => $table_id, 
            'message' => 'Table créée',
            'csrf_token' => $this->getCsrfToken()
        ]);
        exit;
    }

    /**
     * Mettre à jour une table
     */
    public function updateTable()
    {
        $this->requireLogin();
        $this->requireActiveSubscription();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
            exit;
        }

        if (!$this->verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Token CSRF invalide']);
            exit;
        }

        $admin_id = $_SESSION['admin_id'];
        $table_id = intval($_POST['table_id'] ?? 0);

        $tableModel = new RestaurantTable($this->pdo);
        if (!$tableModel->belongsToAdmin($table_id, $admin_id)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Accès refusé']);
            exit;
        }

        $data = [];
        $allowed = ['table_number', 'shape', 'capacity_min', 'capacity_max', 'position_x', 'position_y', 'width', 'height', 'zone'];
        foreach ($allowed as $field) {
            if (isset($_POST[$field])) {
                $data[$field] = $_POST[$field];
            }
        }

        $tableModel->update($table_id, $data);
        echo json_encode([
            'success' => true, 
            'message' => 'Table mise à jour',
            'csrf_token' => $this->getCsrfToken()
        ]);
        exit;
    }

    /**
     * Supprimer une table
     */
    public function deleteTable()
    {
        $this->requireLogin();
        $this->requireActiveSubscription();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
            exit;
        }

        if (!$this->verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Token CSRF invalide']);
            exit;
        }

        $admin_id = $_SESSION['admin_id'];
        $table_id = intval($_POST['table_id'] ?? 0);

        $tableModel = new RestaurantTable($this->pdo);
        if (!$tableModel->belongsToAdmin($table_id, $admin_id)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Accès refusé']);
            exit;
        }

        $tableModel->delete($table_id);
        echo json_encode([
            'success' => true, 
            'message' => 'Table supprimée',
            'csrf_token' => $this->getCsrfToken()
        ]);
        exit;
    }

    /**
     * Créer un nouvel élément
     */
    public function createElement()
    {
        $this->requireLogin();
        $this->requireActiveSubscription();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
            exit;
        }

        if (!$this->verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Token CSRF invalide']);
            exit;
        }

        $admin_id = $_SESSION['admin_id'];
        $floor_id = intval($_POST['floor_id'] ?? 0);
        $element_type = $_POST['element_type'] ?? 'wall';
        $position_x = intval($_POST['position_x'] ?? 100);
        $position_y = intval($_POST['position_y'] ?? 100);
        $width = intval($_POST['width'] ?? 100);
        $height = intval($_POST['height'] ?? 20);
        $rotation = intval($_POST['rotation'] ?? 0);
        $color = $_POST['color'] ?? '#666666';

        $floorModel = new Floor($this->pdo);
        if (!$floorModel->belongsToAdmin($floor_id, $admin_id)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Accès refusé']);
            exit;
        }

        $elementModel = new RestaurantElement($this->pdo);
        $element_id = $elementModel->create($floor_id, $element_type, $position_x, $position_y, $width, $height, $rotation, $color);

        echo json_encode([
            'success' => true, 
            'element_id' => $element_id, 
            'message' => 'Élément créé',
            'csrf_token' => $this->getCsrfToken()
        ]);
        exit;
    }

    /**
     * Mettre à jour un élément
     */
    public function updateElement()
    {
        $this->requireLogin();
        $this->requireActiveSubscription();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
            exit;
        }

        if (!$this->verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Token CSRF invalide']);
            exit;
        }

        $admin_id = $_SESSION['admin_id'];
        $element_id = intval($_POST['element_id'] ?? 0);

        $elementModel = new RestaurantElement($this->pdo);
        if (!$elementModel->belongsToAdmin($element_id, $admin_id)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Accès refusé']);
            exit;
        }

        $data = [];
        $allowed = ['element_type', 'position_x', 'position_y', 'width', 'height', 'rotation', 'color'];
        foreach ($allowed as $field) {
            if (isset($_POST[$field])) {
                $data[$field] = $_POST[$field];
            }
        }

        $elementModel->update($element_id, $data);
        echo json_encode([
            'success' => true, 
            'message' => 'Élément mis à jour',
            'csrf_token' => $this->getCsrfToken()
        ]);
        exit;
    }

    /**
     * Supprimer un élément
     */
    public function deleteElement()
    {
        $this->requireLogin();
        $this->requireActiveSubscription();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
            exit;
        }

        if (!$this->verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Token CSRF invalide']);
            exit;
        }

        $admin_id = $_SESSION['admin_id'];
        $element_id = intval($_POST['element_id'] ?? 0);

        $elementModel = new RestaurantElement($this->pdo);
        if (!$elementModel->belongsToAdmin($element_id, $admin_id)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Accès refusé']);
            exit;
        }

        $elementModel->delete($element_id);
        echo json_encode([
            'success' => true, 
            'message' => 'Élément supprimé',
            'csrf_token' => $this->getCsrfToken()
        ]);
        exit;
    }

    /**
     * Récupérer les données d'un étage (AJAX)
     */
    public function getFloorData()
    {
        $this->requireLogin();

        $admin_id = $_SESSION['admin_id'];
        $floor_id = intval($_GET['floor_id'] ?? 0);

        $floorModel = new Floor($this->pdo);
        if (!$floorModel->belongsToAdmin($floor_id, $admin_id)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Accès refusé']);
            exit;
        }

        $tableModel = new RestaurantTable($this->pdo);
        $elementModel = new RestaurantElement($this->pdo);

        $tables = $tableModel->getAllByFloor($floor_id);
        $elements = $elementModel->getAllByFloor($floor_id);

        echo json_encode([
            'success' => true,
            'tables' => $tables,
            'elements' => $elements
        ]);
        exit;
    }
}
