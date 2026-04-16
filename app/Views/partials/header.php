<header class="main-header">
    <div class="header-container">
        <div class="header-left">
            <a href="<?= BASE_URL ?>/public/dashboard" class="logo">MenuMiam</a>
        </div>
        <button class="hamburger-menu" onclick="toggleMobileMenu()" aria-label="Menu">
            <i class="fas fa-bars"></i>
        </button>
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

<script>
function toggleMobileMenu() {
    const nav = document.querySelector('.header-nav');
    const hamburger = document.querySelector('.hamburger-menu');
    nav.classList.toggle('active');
    hamburger.classList.toggle('active');
}

// Fermer le menu si on clique sur un lien
document.querySelectorAll('.header-nav a').forEach(link => {
    link.addEventListener('click', () => {
        document.querySelector('.header-nav').classList.remove('active');
        document.querySelector('.hamburger-menu').classList.remove('active');
    });
});
</script>
