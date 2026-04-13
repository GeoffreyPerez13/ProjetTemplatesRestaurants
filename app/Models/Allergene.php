<?php

namespace App\Models;

use App\Core\Database;
use PDO;

/**
 * Classe Allergene - Gestion des allergènes
 */
class Allergene
{
    /**
     * Récupérer tous les allergènes
     */
    public static function getAll(): array
    {
        $db = Database::getInstance();
        
        $sql = "SELECT * FROM allergens ORDER BY nom ASC";
        
        $stmt = $db->query($sql);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupérer un allergène par ID
     */
    public static function findById(int $id): ?array
    {
        $db = Database::getInstance();
        
        $sql = "SELECT * FROM allergens WHERE id = :id";
        
        $stmt = $db->prepare($sql);
        $stmt->execute(['id' => $id]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Seed les 14 allergènes de base (si la table est vide)
     */
    public static function seed(): bool
    {
        $db = Database::getInstance();
        
        // Vérifier si des allergènes existent déjà
        $count = $db->query("SELECT COUNT(*) FROM allergens")->fetchColumn();
        if ($count > 0) {
            return true; // Déjà seedé
        }
        
        $allergenes = [
            ['nom' => 'Gluten', 'icone' => 'fa-wheat-awn'],
            ['nom' => 'Crustacés', 'icone' => 'fa-shrimp'],
            ['nom' => 'Œufs', 'icone' => 'fa-egg'],
            ['nom' => 'Poissons', 'icone' => 'fa-fish'],
            ['nom' => 'Arachides', 'icone' => 'fa-peanut'],
            ['nom' => 'Soja', 'icone' => 'fa-seedling'],
            ['nom' => 'Lait', 'icone' => 'fa-cow'],
            ['nom' => 'Fruits à coque', 'icone' => 'fa-acorn'],
            ['nom' => 'Céleri', 'icone' => 'fa-carrot'],
            ['nom' => 'Moutarde', 'icone' => 'fa-jar'],
            ['nom' => 'Sésame', 'icone' => 'fa-cookie'],
            ['nom' => 'Sulfites', 'icone' => 'fa-wine-bottle'],
            ['nom' => 'Lupin', 'icone' => 'fa-leaf'],
            ['nom' => 'Mollusques', 'icone' => 'fa-fish-fins']
        ];
        
        try {
            $db->beginTransaction();
            
            $sql = "INSERT INTO allergens (nom, icone) VALUES (:nom, :icone)";
            $stmt = $db->prepare($sql);
            
            foreach ($allergenes as $allergene) {
                $stmt->execute($allergene);
            }
            
            $db->commit();
            return true;
        } catch (\Exception $e) {
            $db->rollBack();
            return false;
        }
    }

    /**
     * Récupérer les allergènes d'un plat
     */
    public static function getByDish(int $platId): array
    {
        $db = Database::getInstance();
        
        $sql = "SELECT a.* 
                FROM allergens a
                INNER JOIN dish_allergens pa ON a.id = pa.allergen_id
                WHERE pa.dish_id = :dish_id
                ORDER BY a.nom ASC";
        
        $stmt = $db->prepare($sql);
        $stmt->execute(['dish_id' => $platId]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
