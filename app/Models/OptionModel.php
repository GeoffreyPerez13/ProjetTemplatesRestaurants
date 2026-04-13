<?php

/**
 * Modèle OptionModel : gestion des options clé/valeur dans `admin_options`
 * Utilisé pour les services, paiements, réseaux sociaux, template, site_online, etc.
 */
class OptionModel
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
     * Récupère toutes les options d'un admin sous forme clé/valeur
     *
     * @param int $admin_id ID de l'admin
     * @return array Tableau associatif [option_name => option_value]
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
     * Récupère la valeur d'une option spécifique
     *
     * @param int    $admin_id    ID de l'admin
     * @param string $option_name Nom de l'option
     * @return string|false Valeur de l'option ou false si inexistante
     */
    public function get($admin_id, $option_name)
    {
        $stmt = $this->pdo->prepare("SELECT option_value FROM admin_options WHERE admin_id = ? AND option_name = ?");
        $stmt->execute([$admin_id, $option_name]);
        return $stmt->fetchColumn();
    }

    /**
     * Insère ou met à jour une option (INSERT ... ON DUPLICATE KEY UPDATE)
     *
     * @param int    $admin_id     ID de l'admin
     * @param string $option_name  Nom de l'option
     * @param string $option_value Valeur à enregistrer
     * @return bool Succès
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
     *
     * @param int    $admin_id    ID de l'admin
     * @param string $option_name Nom de l'option à supprimer
     * @return bool Succès
     */
    public function delete($admin_id, $option_name)
    {
        $stmt = $this->pdo->prepare("DELETE FROM admin_options WHERE admin_id = ? AND option_name = ?");
        return $stmt->execute([$admin_id, $option_name]);
    }
}