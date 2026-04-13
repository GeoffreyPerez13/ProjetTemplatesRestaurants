<?php

namespace App\Controllers;

use App\Models\Admin;
use App\Helpers\Validator;

/**
 * Classe SettingsController - Gestion des paramètres
 */
class SettingsController extends BaseController
{
    /**
     * Afficher la page de paramètres
     */
    public function index(): void
    {
        $this->requireAuth();

        $admin = Admin::findById($this->getAuthId());

        $this->render('admin.settings', [
            'admin' => $admin,
            'page_title' => 'Paramètres',
            'csrf_token' => $this->generateCsrfToken()
        ]);
    }

    /**
     * Mettre à jour le profil
     */
    public function updateProfile(): void
    {
        $this->requireAuth();

        $csrf_token = $_POST['csrf_token'] ?? '';

        // Vérifier le token CSRF
        if (!$this->verifyCsrfToken($csrf_token)) {
            if ($this->isAjax()) {
                $this->jsonError('Token de sécurité invalide');
            }
            $this->error('Token de sécurité invalide.');
            $this->redirect('/settings');
        }

        // Valider les données
        $validator = new Validator($_POST);
        $validator->required('username', 'Le nom d\'utilisateur est requis.')
                  ->min('username', 3, 'Le nom d\'utilisateur doit contenir au moins 3 caractères.')
                  ->max('username', 50, 'Le nom d\'utilisateur ne doit pas dépasser 50 caractères.')
                  ->required('email', 'L\'email est requis.')
                  ->email('email', 'L\'email n\'est pas valide.')
                  ->required('restaurant_name', 'Le nom du restaurant est requis.')
                  ->min('restaurant_name', 3, 'Le nom du restaurant doit contenir au moins 3 caractères.');

        if ($validator->fails()) {
            $errors = $validator->errors();
            $firstError = reset($errors)[0];
            
            if ($this->isAjax()) {
                $this->jsonError($firstError, $validator->errors());
            }
            
            $this->error($firstError);
            $this->withInput($_POST);
            $this->redirect('/settings');
        }

        $adminId = $this->getAuthId();

        // Vérifier si l'email existe déjà (sauf pour l'utilisateur actuel)
        if (Admin::emailExists($_POST['email'], $adminId)) {
            if ($this->isAjax()) {
                $this->jsonError('Cet email est déjà utilisé.');
            }
            $this->error('Cet email est déjà utilisé.');
            $this->withInput($_POST);
            $this->redirect('/settings');
        }

        // Vérifier si le username existe déjà (sauf pour l'utilisateur actuel)
        if (Admin::usernameExists($_POST['username'], $adminId)) {
            if ($this->isAjax()) {
                $this->jsonError('Ce nom d\'utilisateur est déjà utilisé.');
            }
            $this->error('Ce nom d\'utilisateur est déjà utilisé.');
            $this->withInput($_POST);
            $this->redirect('/settings');
        }

        // Mettre à jour le profil
        try {
            Admin::update($adminId, [
                'username' => $_POST['username'],
                'email' => $_POST['email'],
                'restaurant_name' => $_POST['restaurant_name']
            ]);

            // Mettre à jour la session
            \App\Helpers\Session::set('username', $_POST['username']);
            \App\Helpers\Session::set('email', $_POST['email']);
            \App\Helpers\Session::set('restaurant_name', $_POST['restaurant_name']);

            if ($this->isAjax()) {
                $this->jsonSuccess('Profil mis à jour avec succès', [
                    'username' => $_POST['username'],
                    'email' => $_POST['email'],
                    'restaurant_name' => $_POST['restaurant_name']
                ]);
            }

            $this->success('Profil mis à jour avec succès.');
            $this->redirect('/settings');
        } catch (\Exception $e) {
            if ($this->isAjax()) {
                $this->jsonError('Une erreur est survenue lors de la mise à jour.');
            }
            $this->error('Une erreur est survenue lors de la mise à jour.');
            $this->redirect('/settings');
        }
    }
}
