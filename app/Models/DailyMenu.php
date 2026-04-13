<?php
/**
 * Modèle DailyMenu : CRUD des menus du jour / formules
 * Chaque menu appartient à un admin et contient des lignes (items) en JSON
 */
class DailyMenu
{
    /** @var PDO Connexion à la base de données */
    private $pdo;

    /**
     * @param PDO $pdo Connexion à la base de données
     */
    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Crée un nouveau menu du jour
     *
     * @param int    $adminId     ID de l'admin
     * @param string $title       Titre du menu
     * @param array  $items       Lignes du menu [{label, value}]
     * @param float|null $price   Prix (optionnel)
     * @param string $description Description (optionnel)
     * @return int ID du menu créé
     */
    public function create($adminId, $title, $items, $price = null, $description = '')
    {
        // Auto-incrémenter le display_order
        $stmtOrder = $this->pdo->prepare(
            "SELECT COALESCE(MAX(display_order), 0) + 1 FROM daily_menus WHERE admin_id = ?"
        );
        $stmtOrder->execute([$adminId]);
        $nextOrder = (int)$stmtOrder->fetchColumn();

        $stmt = $this->pdo->prepare(
            "INSERT INTO daily_menus (admin_id, title, description, price, items, display_order) 
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $adminId,
            $title,
            $description ?: null,
            $price,
            json_encode($items, JSON_UNESCAPED_UNICODE),
            $nextOrder
        ]);

        return (int)$this->pdo->lastInsertId();
    }

    /**
     * Met à jour un menu existant
     *
     * @param int    $id          ID du menu
     * @param int    $adminId     ID de l'admin (sécurité)
     * @param string $title       Titre
     * @param array  $items       Lignes du menu
     * @param float|null $price   Prix
     * @param string $description Description
     * @return bool
     */
    public function update($id, $adminId, $title, $items, $price = null, $description = '')
    {
        $stmt = $this->pdo->prepare(
            "UPDATE daily_menus 
             SET title = ?, description = ?, price = ?, items = ?, updated_at = NOW()
             WHERE id = ? AND admin_id = ?"
        );
        return $stmt->execute([
            $title,
            $description ?: null,
            $price,
            json_encode($items, JSON_UNESCAPED_UNICODE),
            $id,
            $adminId
        ]);
    }

    /**
     * Supprime un menu
     *
     * @param int $id      ID du menu
     * @param int $adminId ID de l'admin (sécurité)
     * @return bool
     */
    public function delete($id, $adminId)
    {
        $stmt = $this->pdo->prepare("DELETE FROM daily_menus WHERE id = ? AND admin_id = ?");
        return $stmt->execute([$id, $adminId]);
    }

    /**
     * Active/désactive un menu
     *
     * @param int  $id       ID du menu
     * @param int  $adminId  ID de l'admin
     * @param bool $isActive Actif ou non
     * @return bool
     */
    public function toggleActive($id, $adminId, $isActive)
    {
        $stmt = $this->pdo->prepare(
            "UPDATE daily_menus SET is_active = ?, updated_at = NOW() WHERE id = ? AND admin_id = ?"
        );
        return $stmt->execute([$isActive ? 1 : 0, $id, $adminId]);
    }

    /**
     * Récupère un menu par ID
     *
     * @param int $id      ID du menu
     * @param int $adminId ID de l'admin (sécurité)
     * @return array|null
     */
    public function findById($id, $adminId = null)
    {
        if ($adminId) {
            $stmt = $this->pdo->prepare("SELECT * FROM daily_menus WHERE id = ? AND admin_id = ?");
            $stmt->execute([$id, $adminId]);
        } else {
            $stmt = $this->pdo->prepare("SELECT * FROM daily_menus WHERE id = ?");
            $stmt->execute([$id]);
        }
        $menu = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($menu) {
            $menu['items'] = json_decode($menu['items'], true) ?: [];
        }
        return $menu ?: null;
    }

    /**
     * Récupère tous les menus d'un admin (back-office)
     *
     * @param int $adminId ID de l'admin
     * @return array
     */
    public function getAllByAdmin($adminId)
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM daily_menus WHERE admin_id = ? ORDER BY display_order ASC, id ASC"
        );
        $stmt->execute([$adminId]);
        $menus = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($menus as &$menu) {
            $menu['items'] = json_decode($menu['items'], true) ?: [];
        }
        return $menus;
    }

    /**
     * Récupère les menus actifs d'un admin (front-office / vitrine)
     *
     * @param int $adminId ID de l'admin
     * @return array
     */
    public function getActiveByAdmin($adminId)
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM daily_menus WHERE admin_id = ? AND is_active = 1 ORDER BY display_order ASC, id ASC"
        );
        $stmt->execute([$adminId]);
        $menus = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($menus as &$menu) {
            $menu['items'] = json_decode($menu['items'], true) ?: [];
        }
        return $menus;
    }
}
