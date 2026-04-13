<?php

namespace App\Core;

use PDO;
use PDOException;

/**
 * Classe Database - Gestion de la connexion à la base de données
 * Pattern Singleton pour une seule instance PDO
 */
class Database
{
    private static ?PDO $instance = null;
    private static array $config = [];

    /**
     * Empêcher l'instanciation directe
     */
    private function __construct() {}

    /**
     * Empêcher le clonage
     */
    private function __clone() {}

    /**
     * Initialiser la configuration
     */
    public static function init(array $config): void
    {
        self::$config = $config;
    }

    /**
     * Obtenir l'instance PDO (Singleton)
     */
    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            try {
                $dsn = sprintf(
                    'mysql:host=%s;dbname=%s;charset=%s',
                    self::$config['host'],
                    self::$config['database'],
                    self::$config['charset']
                );

                self::$instance = new PDO(
                    $dsn,
                    self::$config['username'],
                    self::$config['password'],
                    self::$config['options']
                );
            } catch (PDOException $e) {
                throw new PDOException(
                    "Erreur de connexion à la base de données : " . $e->getMessage()
                );
            }
        }

        return self::$instance;
    }

    /**
     * Exécuter une requête SELECT
     */
    public static function query(string $sql, array $params = []): array
    {
        $pdo = self::getInstance();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Exécuter une requête INSERT/UPDATE/DELETE
     */
    public static function execute(string $sql, array $params = []): bool
    {
        $pdo = self::getInstance();
        $stmt = $pdo->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Obtenir le dernier ID inséré
     */
    public static function lastInsertId(): string
    {
        return self::getInstance()->lastInsertId();
    }

    /**
     * Démarrer une transaction
     */
    public static function beginTransaction(): bool
    {
        return self::getInstance()->beginTransaction();
    }

    /**
     * Valider une transaction
     */
    public static function commit(): bool
    {
        return self::getInstance()->commit();
    }

    /**
     * Annuler une transaction
     */
    public static function rollBack(): bool
    {
        return self::getInstance()->rollBack();
    }
}
