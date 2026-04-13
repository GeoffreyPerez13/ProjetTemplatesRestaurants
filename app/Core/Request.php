<?php

namespace App\Core;

/**
 * Classe Request - Gestion de la requête HTTP
 */
class Request
{
    private string $method;
    private string $uri;
    private array $query;
    private array $post;
    private array $files;
    private array $server;
    private array $cookies;

    public function __construct()
    {
        $this->method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $this->uri = $_SERVER['REQUEST_URI'] ?? '/';
        $this->query = $_GET;
        $this->post = $_POST;
        $this->files = $_FILES;
        $this->server = $_SERVER;
        $this->cookies = $_COOKIE;
    }

    /**
     * Obtenir la méthode HTTP
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * Obtenir l'URI
     */
    public function getUri(): string
    {
        return $this->uri;
    }

    /**
     * Obtenir un paramètre GET
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->query[$key] ?? $default;
    }

    /**
     * Obtenir un paramètre POST
     */
    public function post(string $key, mixed $default = null): mixed
    {
        return $this->post[$key] ?? $default;
    }

    /**
     * Obtenir tous les paramètres GET
     */
    public function allGet(): array
    {
        return $this->query;
    }

    /**
     * Obtenir tous les paramètres POST
     */
    public function allPost(): array
    {
        return $this->post;
    }

    /**
     * Obtenir un fichier uploadé
     */
    public function file(string $key): ?array
    {
        return $this->files[$key] ?? null;
    }

    /**
     * Vérifier si la requête est POST
     */
    public function isPost(): bool
    {
        return $this->method === 'POST';
    }

    /**
     * Vérifier si la requête est GET
     */
    public function isGet(): bool
    {
        return $this->method === 'GET';
    }

    /**
     * Vérifier si la requête est AJAX
     */
    public function isAjax(): bool
    {
        return isset($this->server['HTTP_X_REQUESTED_WITH']) 
            && strtolower($this->server['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    /**
     * Obtenir l'IP du client
     */
    public function getClientIp(): string
    {
        return $this->server['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    /**
     * Obtenir le User Agent
     */
    public function getUserAgent(): string
    {
        return $this->server['HTTP_USER_AGENT'] ?? '';
    }
}
