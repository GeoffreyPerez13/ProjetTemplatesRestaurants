<?php

namespace App\Controllers;

use App\Helpers\Session;

/**
 * Classe BaseController - Contrôleur de base
 * Tous les contrôleurs héritent de cette classe
 */
class BaseController
{
    /**
     * Rendre une vue
     */
    protected function render(string $view, array $data = []): void
    {
        extract($data);
        
        $viewPath = APP_PATH . '/Views/' . str_replace('.', '/', $view) . '.php';
        
        if (!file_exists($viewPath)) {
            throw new \Exception("Vue non trouvée : {$view}");
        }
        
        require $viewPath;
    }

    /**
     * Rediriger vers une URL
     */
    protected function redirect(string $url): void
    {
        header("Location: {$url}");
        exit;
    }

    /**
     * Retourner une réponse JSON
     */
    protected function json(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /**
     * Générer un token CSRF
     */
    protected function generateCsrfToken(): string
    {
        if (!Session::has('csrf_token')) {
            Session::set('csrf_token', bin2hex(random_bytes(32)));
        }
        return Session::get('csrf_token');
    }

    /**
     * Vérifier le token CSRF
     */
    protected function verifyCsrfToken(string $token): bool
    {
        return Session::has('csrf_token') && hash_equals(Session::get('csrf_token'), $token);
    }

    /**
     * Vérifier si l'utilisateur est connecté
     */
    protected function isAuthenticated(): bool
    {
        return Session::has('admin_id');
    }

    /**
     * Obtenir l'ID de l'utilisateur connecté
     */
    protected function getAuthId(): ?int
    {
        return Session::get('admin_id');
    }

    /**
     * Vérifier si l'utilisateur est super admin
     */
    protected function isSuperAdmin(): bool
    {
        return Session::get('role') === ROLE_SUPER_ADMIN;
    }

    /**
     * Exiger une authentification
     */
    protected function requireAuth(): void
    {
        if (!$this->isAuthenticated()) {
            Session::flash('error', 'Vous devez être connecté pour accéder à cette page.');
            $this->redirect('/login');
        }
    }

    /**
     * Exiger un rôle super admin
     */
    protected function requireSuperAdmin(): void
    {
        $this->requireAuth();
        
        if (!$this->isSuperAdmin()) {
            Session::flash('error', 'Accès refusé. Vous devez être super administrateur.');
            $this->redirect('/dashboard');
        }
    }

    /**
     * Définir un message flash de succès
     */
    protected function success(string $message): void
    {
        Session::flash('success', $message);
    }

    /**
     * Définir un message flash d'erreur
     */
    protected function error(string $message): void
    {
        Session::flash('error', $message);
    }

    /**
     * Définir un message flash d'information
     */
    protected function info(string $message): void
    {
        Session::flash('info', $message);
    }

    /**
     * Obtenir les anciennes valeurs du formulaire
     */
    protected function old(string $key, mixed $default = ''): mixed
    {
        $old = Session::get('old', []);
        Session::remove('old');
        return $old[$key] ?? $default;
    }

    /**
     * Sauvegarder les anciennes valeurs du formulaire
     */
    protected function withInput(array $data): void
    {
        Session::set('old', $data);
    }
}
