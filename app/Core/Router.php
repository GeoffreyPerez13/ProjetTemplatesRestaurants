<?php

namespace App\Core;

/**
 * Classe Router - Gestion du routage de l'application
 */
class Router
{
    private array $routes = [];
    private string $notFoundHandler = '';

    /**
     * Ajouter une route GET
     */
    public function get(string $path, callable|array $handler): void
    {
        $this->addRoute('GET', $path, $handler);
    }

    /**
     * Ajouter une route POST
     */
    public function post(string $path, callable|array $handler): void
    {
        $this->addRoute('POST', $path, $handler);
    }

    /**
     * Ajouter une route (méthode générique)
     */
    private function addRoute(string $method, string $path, callable|array $handler): void
    {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler
        ];
    }

    /**
     * Définir le gestionnaire 404
     */
    public function setNotFoundHandler(callable|array $handler): void
    {
        $this->notFoundHandler = $handler;
    }

    /**
     * Dispatcher la requête
     */
    public function dispatch(Request $request): void
    {
        $method = $request->getMethod();
        $uri = $request->getUri();

        foreach ($this->routes as $route) {
            if ($route['method'] === $method && $this->matchPath($route['path'], $uri, $params)) {
                $this->callHandler($route['handler'], $params);
                return;
            }
        }

        // 404 - Route non trouvée
        if ($this->notFoundHandler) {
            $this->callHandler($this->notFoundHandler, []);
        } else {
            http_response_code(404);
            echo "404 - Page non trouvée";
        }
    }

    /**
     * Vérifier si le chemin correspond
     */
    private function matchPath(string $pattern, string $uri, &$params = []): bool
    {
        // Supprimer les query strings
        $uri = strtok($uri, '?');
        
        // Conversion du pattern en regex
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $pattern);
        $pattern = '#^' . $pattern . '$#';

        if (preg_match($pattern, $uri, $matches)) {
            // Extraire les paramètres nommés
            $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
            return true;
        }

        return false;
    }

    /**
     * Appeler le gestionnaire
     */
    private function callHandler(callable|array $handler, array $params): void
    {
        if (is_array($handler)) {
            [$controller, $method] = $handler;
            $controllerInstance = new $controller();
            call_user_func_array([$controllerInstance, $method], $params);
        } else {
            call_user_func_array($handler, $params);
        }
    }
}
