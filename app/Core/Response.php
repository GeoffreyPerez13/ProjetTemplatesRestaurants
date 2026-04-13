<?php

namespace App\Core;

/**
 * Classe Response - Gestion de la réponse HTTP
 */
class Response
{
    private int $statusCode = 200;
    private array $headers = [];
    private string $body = '';

    /**
     * Définir le code de statut HTTP
     */
    public function setStatusCode(int $code): self
    {
        $this->statusCode = $code;
        return $this;
    }

    /**
     * Ajouter un header
     */
    public function setHeader(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        return $this;
    }

    /**
     * Définir le contenu de la réponse
     */
    public function setBody(string $body): self
    {
        $this->body = $body;
        return $this;
    }

    /**
     * Envoyer la réponse
     */
    public function send(): void
    {
        http_response_code($this->statusCode);

        foreach ($this->headers as $name => $value) {
            header("$name: $value");
        }

        echo $this->body;
    }

    /**
     * Envoyer une réponse JSON
     */
    public function json(array $data, int $statusCode = 200): void
    {
        $this->setStatusCode($statusCode)
             ->setHeader('Content-Type', 'application/json')
             ->setBody(json_encode($data))
             ->send();
    }

    /**
     * Rediriger vers une URL
     */
    public function redirect(string $url, int $statusCode = 302): void
    {
        $this->setStatusCode($statusCode)
             ->setHeader('Location', $url)
             ->send();
        exit;
    }
}
