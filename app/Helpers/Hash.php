<?php

namespace App\Helpers;

/**
 * Classe Hash - Gestion du hachage de mots de passe
 */
class Hash
{
    /**
     * Hasher un mot de passe
     */
    public static function make(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => BCRYPT_COST]);
    }

    /**
     * Vérifier un mot de passe
     */
    public static function verify(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /**
     * Vérifier si le hash doit être re-hashé
     */
    public static function needsRehash(string $hash): bool
    {
        return password_needs_rehash($hash, PASSWORD_BCRYPT, ['cost' => BCRYPT_COST]);
    }

    /**
     * Générer un token aléatoire sécurisé
     */
    public static function token(int $length = 32): string
    {
        return bin2hex(random_bytes($length));
    }
}
