<header class="main-header">
    <div class="header-container">
        <div class="header-left">
            <a href="<?= BASE_URL ?>/public/dashboard" class="logo">MenuMiam</a>
        </div>
        <nav class="header-nav">
            <a href="<?= BASE_URL ?>/public/dashboard">Tableau de bord</a>
            <a href="<?= BASE_URL ?>/public/carte">Ma Carte</a>
            <a href="<?= BASE_URL ?>/public/contact">Contact</a>
            <a href="<?= BASE_URL ?>/public/settings">Paramètres</a>
        </nav>
        <div class="header-right">
            <span class="user-name"><?= htmlspecialchars(\App\Helpers\Session::get('username', 'Utilisateur')) ?></span>
            <a href="<?= BASE_URL ?>/public/logout" class="btn-logout">Déconnexion</a>
        </div>
    </div>
</header>
