<?php

/**
 * Modèle Allergene : gestion des 14 allergènes réglementaires
 * Gère la table `allergenes` et la table pivot `plat_allergenes`
 */
class Allergene
{
    /** @var string Nom de la table en BDD */
    protected $table = 'allergenes';

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
     * Récupère tous les allergènes, triés par nom
     * @return array
     */
    public function getAll()
    {
        $stmt = $this->pdo->query("SELECT id, nom, icone FROM {$this->table} ORDER BY nom");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère les IDs des allergènes associés à un plat
     * @param int $dish_id
     * @return array
     */
    public function getForDish($dish_id)
    {
        $stmt = $this->pdo->prepare("SELECT allergene_id FROM plat_allergenes WHERE plat_id = ?");
        $stmt->execute([$dish_id]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Sauvegarde les associations pour un plat (supprime les anciennes et insère les nouvelles)
     * @param int $dish_id
     * @param array $allergene_ids Liste des IDs d'allergènes sélectionnés
     * @return void
     */
    public function saveForDish($dish_id, $allergene_ids)
    {
        // Supprimer les anciennes associations
        $stmt = $this->pdo->prepare("DELETE FROM plat_allergenes WHERE plat_id = ?");
        $stmt->execute([$dish_id]);

        // Insérer les nouvelles
        if (!empty($allergene_ids)) {
            $insert = $this->pdo->prepare("INSERT INTO plat_allergenes (plat_id, allergene_id) VALUES (?, ?)");
            foreach ($allergene_ids as $allergene_id) {
                $insert->execute([$dish_id, $allergene_id]);
            }
        }
    }
}
