<?php

namespace App\Helpers;

/**
 * Classe Session - Gestion des sessions
 */
class Session
{
    /**
     * Définir une valeur en session
     */
    public static function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    /**
     * Obtenir une valeur de session
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Vérifier si une clé existe en session
     */
    public static function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    /**
     * Supprimer une valeur de session
     */
    public static function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    /**
     * Détruire toute la session
     */
    public static function destroy(): void
    {
        session_destroy();
        $_SESSION = [];
    }

    /**
     * Définir un message flash
     */
    public static function flash(string $key, string $message): void
    {
        $_SESSION['flash'][$key] = $message;
    }

    /**
     * Obtenir et supprimer un message flash
     */
    public static function getFlash(string $key): ?string
    {
        $message = $_SESSION['flash'][$key] ?? null;
        unset($_SESSION['flash'][$key]);
        return $message;
    }

    /**
     * Vérifier si un message flash existe
     */
    public static function hasFlash(string $key): bool
    {
        return isset($_SESSION['flash'][$key]);
    }

    /**
     * Régénérer l'ID de session (sécurité)
     */
    public static function regenerate(): void
    {
        session_regenerate_id(true);
    }
}
