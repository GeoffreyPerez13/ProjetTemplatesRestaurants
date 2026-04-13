<?php

namespace App\Core;

/**
 * Classe Autoloader - Chargement automatique des classes (PSR-4)
 */
class Autoloader
{
    private static array $namespaces = [];

    /**
     * Enregistrer l'autoloader
     */
    public static function register(): void
    {
        spl_autoload_register([self::class, 'load']);
    }

    /**
     * Ajouter un namespace
     */
    public static function addNamespace(string $prefix, string $baseDir): void
    {
        $prefix = trim($prefix, '\\') . '\\';
        $baseDir = rtrim($baseDir, DIRECTORY_SEPARATOR) . '/';
        self::$namespaces[$prefix] = $baseDir;
    }

    /**
     * Charger une classe
     */
    private static function load(string $class): void
    {
        foreach (self::$namespaces as $prefix => $baseDir) {
            $len = strlen($prefix);
            if (strncmp($prefix, $class, $len) !== 0) {
                continue;
            }

            $relativeClass = substr($class, $len);
            $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

            if (file_exists($file)) {
                require $file;
                return;
            }
        }
    }
}
