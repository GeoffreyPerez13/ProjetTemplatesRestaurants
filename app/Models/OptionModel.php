<?php
class OptionModel
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Récupère toutes les options d'un admin
     */
    public function getAll($admin_id)
    {
        $stmt = $this->pdo->prepare("SELECT option_name, option_value FROM admin_options WHERE admin_id = ?");
        $stmt->execute([$admin_id]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $options = [];
        foreach ($rows as $row) {
            $options[$row['option_name']] = $row['option_value'];
        }
        return $options;
    }

    /**
     * Récupère une option spécifique
     */
    public function get($admin_id, $option_name)
    {
        $stmt = $this->pdo->prepare("SELECT option_value FROM admin_options WHERE admin_id = ? AND option_name = ?");
        $stmt->execute([$admin_id, $option_name]);
        return $stmt->fetchColumn();
    }

    /**
     * Enregistre ou met à jour une option
     */
    public function set($admin_id, $option_name, $option_value)
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO admin_options (admin_id, option_name, option_value)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE option_value = VALUES(option_value)
        ");
        return $stmt->execute([$admin_id, $option_name, $option_value]);
    }

    /**
     * Supprime une option
     */
    public function delete($admin_id, $option_name)
    {
        $stmt = $this->pdo->prepare("DELETE FROM admin_options WHERE admin_id = ? AND option_name = ?");
        return $stmt->execute([$admin_id, $option_name]);
    }
}