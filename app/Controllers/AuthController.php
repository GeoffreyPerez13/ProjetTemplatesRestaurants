<?php

namespace App\Controllers;

use App\Models\Admin;
use App\Helpers\Session;
use App\Helpers\Validator;

/**
 * Classe AuthController - Gestion de l'authentification
 */
class AuthController extends BaseController
{
    /**
     * Afficher le formulaire de connexion
     */
    public function showLogin(): void
    {
        if ($this->isAuthenticated()) {
            $this->redirect('/dashboard');
        }

        $this->render('auth.login', [
            'csrf_token' => $this->generateCsrfToken()
        ]);
    }

    /**
     * Traiter la connexion
     */
    public function login(): void
    {
        $identifier = $_POST['identifier'] ?? '';
        $password = $_POST['password'] ?? '';
        $csrf_token = $_POST['csrf_token'] ?? '';

        // Vérifier le token CSRF
        if (!$this->verifyCsrfToken($csrf_token)) {
            $this->error('Token de sécurité invalide.');
            $this->redirect('/login');
        }

        // Valider les données
        $validator = new Validator($_POST);
        $validator->required('identifier', 'L\'identifiant est requis.')
                  ->required('password', 'Le mot de passe est requis.');

        if ($validator->fails()) {
            $this->error($validator->first('identifier') ?? $validator->first('password'));
            $this->withInput($_POST);
            $this->redirect('/login');
        }

        // Authentifier l'utilisateur
        $admin = Admin::authenticate($identifier, $password);

        if (!$admin) {
            $this->error('Identifiants incorrects.');
            $this->withInput(['identifier' => $identifier]);
            $this->redirect('/login');
        }

        // Créer la session
        Session::regenerate();
        Session::set('admin_id', $admin['id']);
        Session::set('username', $admin['username']);
        Session::set('email', $admin['email']);
        Session::set('role', $admin['role']);
        Session::set('restaurant_name', $admin['restaurant_name']);
        Session::set('slug', $admin['slug']);

        $this->success('Connexion réussie ! Bienvenue ' . $admin['username'] . '.');
        $this->redirect('/dashboard');
    }

    /**
     * Afficher le formulaire d'inscription
     */
    public function showRegister(): void
    {
        if ($this->isAuthenticated()) {
            $this->redirect('/dashboard');
        }

        $this->render('auth.register', [
            'csrf_token' => $this->generateCsrfToken()
        ]);
    }

    /**
     * Traiter l'inscription
     */
    public function register(): void
    {
        $csrf_token = $_POST['csrf_token'] ?? '';

        // Vérifier le token CSRF
        if (!$this->verifyCsrfToken($csrf_token)) {
            $this->error('Token de sécurité invalide.');
            $this->redirect('/register');
        }

        // Valider les données
        $validator = new Validator($_POST);
        $validator->required('username', 'Le nom d\'utilisateur est requis.')
                  ->min('username', 3, 'Le nom d\'utilisateur doit contenir au moins 3 caractères.')
                  ->max('username', 50, 'Le nom d\'utilisateur ne doit pas dépasser 50 caractères.')
                  ->required('email', 'L\'email est requis.')
                  ->email('email', 'L\'email n\'est pas valide.')
                  ->required('password', 'Le mot de passe est requis.')
                  ->min('password', 8, 'Le mot de passe doit contenir au moins 8 caractères.')
                  ->strongPassword('password')
                  ->required('password_confirm', 'La confirmation du mot de passe est requise.')
                  ->match('password', 'password_confirm', 'Les mots de passe ne correspondent pas.')
                  ->required('restaurant_name', 'Le nom du restaurant est requis.')
                  ->min('restaurant_name', 3, 'Le nom du restaurant doit contenir au moins 3 caractères.');

        if ($validator->fails()) {
            $errors = $validator->errors();
            $firstError = reset($errors)[0];
            $this->error($firstError);
            $this->withInput($_POST);
            $this->redirect('/register');
        }

        // Vérifier si l'email existe déjà
        if (Admin::emailExists($_POST['email'])) {
            $this->error('Cet email est déjà utilisé.');
            $this->withInput($_POST);
            $this->redirect('/register');
        }

        // Vérifier si le username existe déjà
        if (Admin::usernameExists($_POST['username'])) {
            $this->error('Ce nom d\'utilisateur est déjà utilisé.');
            $this->withInput($_POST);
            $this->redirect('/register');
        }

        // Créer l'administrateur
        try {
            $adminId = Admin::create([
                'username' => $_POST['username'],
                'email' => $_POST['email'],
                'password' => $_POST['password'],
                'restaurant_name' => $_POST['restaurant_name'],
                'role' => ROLE_ADMIN,
                'carte_mode' => 'carte'
            ]);

            // Créer la session
            $admin = Admin::findById($adminId);
            Session::regenerate();
            Session::set('admin_id', $admin['id']);
            Session::set('username', $admin['username']);
            Session::set('email', $admin['email']);
            Session::set('role', $admin['role']);
            Session::set('restaurant_name', $admin['restaurant_name']);
            Session::set('slug', $admin['slug']);

            $this->success('Inscription réussie ! Bienvenue sur MenuMiam.');
            $this->redirect('/dashboard');
        } catch (\Exception $e) {
            $this->error('Une erreur est survenue lors de l\'inscription.');
            $this->withInput($_POST);
            $this->redirect('/register');
        }
    }

    /**
     * Déconnexion
     */
    public function logout(): void
    {
        Session::destroy();
        $this->success('Vous avez été déconnecté avec succès.');
        $this->redirect('/login');
    }
}
