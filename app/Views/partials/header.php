<header class="main-header">
    <div class="header-container">
        <div class="header-left">
            <a href="/dashboard" class="logo">MenuMiam</a>
        </div>
        <nav class="header-nav">
            <a href="/dashboard">Tableau de bord</a>
            <a href="/carte">Ma Carte</a>
            <a href="/contact">Contact</a>
            <a href="/settings">Paramètres</a>
        </nav>
        <div class="header-right">
            <span class="user-name"><?= htmlspecialchars(\App\Helpers\Session::get('username', 'Utilisateur')) ?></span>
            <a href="/logout" class="btn-logout">Déconnexion</a>
        </div>
    </div>
</header>
