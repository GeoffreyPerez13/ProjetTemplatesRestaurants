<!-- Bandeau mode démo (vitrine) -->
<?php if (!empty($_SESSION['demo_mode']) && $_SESSION['demo_mode'] === true): ?>
    <div class="demo-banner-vitrine">
        <i class="fas fa-flask"></i>
        <span><strong>Démo MenuMiam</strong> — Ceci est un exemple de site vitrine restaurant.</span>
        <a href="?page=dashboard" class="demo-banner-link">Voir le panel admin</a>
    </div>
<?php endif; ?>

<!-- Bandeau de prévisualisation pour l'admin -->
<?php if (!empty($isPreview)): ?>
    <div class="preview-banner">
        <i class="fas fa-eye"></i> Mode prévisualisation — Ce site est actuellement <strong>hors ligne</strong> pour vos clients.
        <a href="?page=settings&section=options">Modifier</a>
    </div>
<?php endif; ?>

<!-- Site normal -->
<header>
    <div class="container header-content">
        <a href="#accueil" class="logo-area">
            <?php if ($logo): ?>
                <img src="<?= htmlspecialchars($logo['url']) ?>" alt="Logo <?= htmlspecialchars($restaurant->name) ?>" class="logo-image">
            <?php endif; ?>
            <h1><?= htmlspecialchars($restaurant->name) ?></h1>
        </a>
        <button class="hamburger" id="hamburger" aria-label="Menu">
            <span></span>
            <span></span>
            <span></span>
        </button>
        <nav class="nav-menu" id="nav-menu">
            <ul>
                <li><a href="#accueil">Accueil</a></li>
                <li><a href="#carte">Carte</a></li>
                <li><a href="#services">Services</a></li>
                <li><a href="#contact">Contact</a></li>
            </ul>
        </nav>
    </div>
</header>
