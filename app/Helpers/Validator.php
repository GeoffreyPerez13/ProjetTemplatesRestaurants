<?php

namespace App\Helpers;

/**
 * Classe Validator - Validation des données
 */
class Validator
{
    private array $errors = [];
    private array $data = [];

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Valider que le champ est requis
     */
    public function required(string $field, string $message = null): self
    {
        if (!isset($this->data[$field]) || trim($this->data[$field]) === '') {
            $this->errors[$field][] = $message ?? "Le champ {$field} est requis.";
        }
        return $this;
    }

    /**
     * Valider un email
     */
    public function email(string $field, string $message = null): self
    {
        if (isset($this->data[$field]) && !filter_var($this->data[$field], FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field][] = $message ?? "L'email n'est pas valide.";
        }
        return $this;
    }

    /**
     * Valider la longueur minimale
     */
    public function min(string $field, int $length, string $message = null): self
    {
        if (isset($this->data[$field]) && strlen($this->data[$field]) < $length) {
            $this->errors[$field][] = $message ?? "Le champ {$field} doit contenir au moins {$length} caractères.";
        }
        return $this;
    }

    /**
     * Valider la longueur maximale
     */
    public function max(string $field, int $length, string $message = null): self
    {
        if (isset($this->data[$field]) && strlen($this->data[$field]) > $length) {
            $this->errors[$field][] = $message ?? "Le champ {$field} ne doit pas dépasser {$length} caractères.";
        }
        return $this;
    }

    /**
     * Valider que deux champs sont identiques
     */
    public function match(string $field1, string $field2, string $message = null): self
    {
        if (isset($this->data[$field1]) && isset($this->data[$field2]) && $this->data[$field1] !== $this->data[$field2]) {
            $this->errors[$field1][] = $message ?? "Les champs ne correspondent pas.";
        }
        return $this;
    }

    /**
     * Valider un format personnalisé (regex)
     */
    public function pattern(string $field, string $pattern, string $message = null): self
    {
        if (isset($this->data[$field]) && !preg_match($pattern, $this->data[$field])) {
            $this->errors[$field][] = $message ?? "Le format du champ {$field} n'est pas valide.";
        }
        return $this;
    }

    /**
     * Vérifier si la validation a réussi
     */
    public function passes(): bool
    {
        return empty($this->errors);
    }

    /**
     * Vérifier si la validation a échoué
     */
    public function fails(): bool
    {
        return !$this->passes();
    }

    /**
     * Obtenir toutes les erreurs
     */
    public function errors(): array
    {
        return $this->errors;
    }

    /**
     * Obtenir la première erreur d'un champ
     */
    public function first(string $field): ?string
    {
        return $this->errors[$field][0] ?? null;
    }

    /**
     * Valider la complexité du mot de passe
     * Doit contenir : minuscule, majuscule, chiffre, caractère spécial
     */
    public function strongPassword(string $field, string $message = null): self
    {
        if (!isset($this->data[$field])) {
            return $this;
        }

        $password = $this->data[$field];
        $errors = [];

        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'une minuscule';
        }
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'une majuscule';
        }
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'un chiffre';
        }
        if (!preg_match('/[^a-zA-Z0-9]/', $password)) {
            $errors[] = 'un caractère spécial';
        }

        if (!empty($errors)) {
            $defaultMessage = 'Le mot de passe doit contenir au moins ' . implode(', ', $errors) . '.';
            $this->errors[$field][] = $message ?? $defaultMessage;
        }

        return $this;
    }
}
