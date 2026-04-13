<?php

namespace App\Controllers;

use App\Models\Admin;

/**
 * Classe DashboardController - Gestion du tableau de bord
 */
class DashboardController extends BaseController
{
    /**
     * Afficher le tableau de bord
     */
    public function index(): void
    {
        $this->requireAuth();

        $admin = Admin::findById($this->getAuthId());

        $this->render('admin.dashboard', [
            'admin' => $admin,
            'page_title' => 'Tableau de bord'
        ]);
    }
}
