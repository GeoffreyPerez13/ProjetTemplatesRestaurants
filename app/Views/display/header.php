<!-- Bandeau mode démo (vitrine) -->
<?php if (!empty($_SESSION['demo_mode']) && $_SESSION['demo_mode'] === true): ?>
    <div class="demo-banner-vitrine">
        <i class="fas fa-flask"></i>
        <span><strong>Démo MenuCraft</strong> — Ceci est un exemple de site vitrine restaurant.</span>
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
            <?php if (!empty($logo)): ?>
                <img src="<?= htmlspecialchars($logo['url']) ?>" alt="Logo <?= htmlspecialchars($restaurant->name ?? '') ?>" class="logo-image">
            <?php endif; ?>
            <h1><?= htmlspecialchars($restaurant->name ?? 'Restaurant') ?></h1>
        </a>
        <button class="hamburger" id="hamburger" aria-label="Menu">
            <span></span>
            <span></span>
            <span></span>
        </button>
        <nav class="nav-menu" id="nav-menu">
            <ul>
                <li><a href="#accueil">Accueil</a></li>
                <?php if (!empty($dailyMenus)): ?>
                    <li><a href="#menus-du-jour">Menus</a></li>
                <?php endif; ?>
                <li><a href="#carte">Carte</a></li>
                <?php
                // Même logique que services.php : afficher seulement si au moins un service/paiement est actif
                $hasServices = (
                    ($services['service_sur_place'] ?? '0') == '1' ||
                    ($services['service_a_emporter'] ?? '0') == '1' ||
                    ($services['service_livraison_ubereats'] ?? '0') == '1' ||
                    ($services['service_livraison_etablissement'] ?? '0') == '1' ||
                    ($services['service_wifi'] ?? '0') == '1' ||
                    ($services['service_climatisation'] ?? '0') == '1' ||
                    ($services['service_pmr'] ?? '0') == '1' ||
                    !empty($services['service_animaux'] ?? '')
                );
                $hasPayments = (
                    ($payments['payment_visa'] ?? '0') == '1' ||
                    ($payments['payment_mastercard'] ?? '0') == '1' ||
                    ($payments['payment_cb'] ?? '0') == '1' ||
                    ($payments['payment_especes'] ?? '0') == '1' ||
                    ($payments['payment_cheques'] ?? '0') == '1' ||
                    ($payments['payment_tickets_restaurant'] ?? '0') == '1'
                );
                if ($hasServices || $hasPayments): ?>
                <li><a href="#services">Services</a></li>
                <?php endif; ?>
                <?php if (!empty($bookingEnabled)): ?>
                    <li><a href="#reservation">Réservation</a></li>
                <?php endif; ?>
                <li><a href="#contact">Contact</a></li>
            </ul>
        </nav>
    </div>
</header>
