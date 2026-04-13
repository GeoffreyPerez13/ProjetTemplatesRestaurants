<?php

namespace App\Models;

use App\Core\Database;
use App\Helpers\Hash;

/**
 * Classe Admin - Modèle pour les administrateurs
 */
class Admin
{
    /**
     * Créer un nouvel administrateur
     */
    public static function create(array $data): int
    {
        $sql = "INSERT INTO admins (username, email, password, restaurant_name, slug, role, carte_mode) 
                VALUES (:username, :email, :password, :restaurant_name, :slug, :role, :carte_mode)";
        
        $params = [
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'restaurant_name' => $data['restaurant_name'],
            'slug' => self::generateSlug($data['restaurant_name']),
            'role' => $data['role'] ?? ROLE_ADMIN,
            'carte_mode' => $data['carte_mode'] ?? 'carte'
        ];
        
        Database::execute($sql, $params);
        return (int) Database::lastInsertId();
    }

    /**
     * Trouver un admin par ID
     */
    public static function findById(int $id): ?array
    {
        $sql = "SELECT * FROM admins WHERE id = :id LIMIT 1";
        $result = Database::query($sql, ['id' => $id]);
        return $result[0] ?? null;
    }

    /**
     * Trouver un admin par email
     */
    public static function findByEmail(string $email): ?array
    {
        $sql = "SELECT * FROM admins WHERE email = :email LIMIT 1";
        $result = Database::query($sql, ['email' => $email]);
        return $result[0] ?? null;
    }

    /**
     * Trouver un admin par username
     */
    public static function findByUsername(string $username): ?array
    {
        $sql = "SELECT * FROM admins WHERE username = :username LIMIT 1";
        $result = Database::query($sql, ['username' => $username]);
        return $result[0] ?? null;
    }

    /**
     * Trouver un admin par slug
     */
    public static function findBySlug(string $slug): ?array
    {
        $sql = "SELECT * FROM admins WHERE slug = :slug LIMIT 1";
        $result = Database::query($sql, ['slug' => $slug]);
        return $result[0] ?? null;
    }

    /**
     * Vérifier les identifiants de connexion
     */
    public static function authenticate(string $identifier, string $password): ?array
    {
        // Chercher par email ou username
        $admin = self::findByEmail($identifier) ?? self::findByUsername($identifier);
        
        if (!$admin) {
            return null;
        }
        
        if (!Hash::verify($password, $admin['password'])) {
            return null;
        }
        
        return $admin;
    }

    /**
     * Mettre à jour un admin
     */
    public static function update(int $id, array $data): bool
    {
        $fields = [];
        $params = ['id' => $id];
        
        foreach ($data as $key => $value) {
            if ($key === 'password') {
                $value = Hash::make($value);
            }
            $fields[] = "{$key} = :{$key}";
            $params[$key] = $value;
        }
        
        $sql = "UPDATE admins SET " . implode(', ', $fields) . " WHERE id = :id";
        return Database::execute($sql, $params);
    }

    /**
     * Supprimer un admin
     */
    public static function delete(int $id): bool
    {
        $sql = "DELETE FROM admins WHERE id = :id";
        return Database::execute($sql, ['id' => $id]);
    }

    /**
     * Obtenir tous les admins
     */
    public static function all(): array
    {
        $sql = "SELECT * FROM admins ORDER BY created_at DESC";
        return Database::query($sql);
    }

    /**
     * Vérifier si un email existe déjà
     */
    public static function emailExists(string $email, ?int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) as count FROM admins WHERE email = :email";
        $params = ['email' => $email];
        
        if ($excludeId) {
            $sql .= " AND id != :id";
            $params['id'] = $excludeId;
        }
        
        $result = Database::query($sql, $params);
        return $result[0]['count'] > 0;
    }

    /**
     * Vérifier si un username existe déjà
     */
    public static function usernameExists(string $username, ?int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) as count FROM admins WHERE username = :username";
        $params = ['username' => $username];
        
        if ($excludeId) {
            $sql .= " AND id != :id";
            $params['id'] = $excludeId;
        }
        
        $result = Database::query($sql, $params);
        return $result[0]['count'] > 0;
    }

    /**
     * Générer un slug unique à partir du nom du restaurant
     */
    private static function generateSlug(string $name): string
    {
        // Convertir en minuscules et remplacer les espaces par des tirets
        $slug = strtolower(trim($name));
        $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        $slug = trim($slug, '-');
        
        // Vérifier l'unicité
        $originalSlug = $slug;
        $counter = 1;
        
        while (self::slugExists($slug)) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }
        
        return $slug;
    }

    /**
     * Vérifier si un slug existe
     */
    private static function slugExists(string $slug): bool
    {
        $sql = "SELECT COUNT(*) as count FROM admins WHERE slug = :slug";
        $result = Database::query($sql, ['slug' => $slug]);
        return $result[0]['count'] > 0;
    }

    /**
     * Mettre à jour la date de dernière modification de la carte
     */
    public static function updateCardTimestamp(int $id): bool
    {
        $sql = "UPDATE admins SET last_card_update = NOW() WHERE id = :id";
        return Database::execute($sql, ['id' => $id]);
    }
}
