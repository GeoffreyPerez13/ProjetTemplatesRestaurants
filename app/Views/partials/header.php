<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Administration') ?></title>
    
    <!-- Meta description pour le SEO -->
    <meta name="description" content="Interface d'administration MenuMiam - Gérez votre carte de restaurant en ligne">
    
    <!-- Favicon -->
    <link rel="icon" href="/assets/favicon.ico" type="image/x-icon">
    
    <!-- CSS principal -->
    <link rel="stylesheet" href="/assets/css/admin.css">
    
    <!-- Font Awesome pour les icônes -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- SweetAlert2 pour les alertes stylisées -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Sortable pour le drag and drop -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.14.0/Sortable.min.js" defer></script>

    <!-- Dark mode (chargé tôt pour éviter le flash) -->
    <script src="/assets/js/admin/dark-mode.js"></script>

    <!-- Inclusion de scripts additionnels dynamiques si fournis -->
    <?php if (!empty($scripts)): ?>
        <?php foreach ($scripts as $script): ?>
            <script src="/assets/<?= htmlspecialchars($script) ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</head>

<body>
    <!-- Bandeau mode démo (visible uniquement en session démo) -->
    <?php if (!empty($_SESSION['demo_mode']) && $_SESSION['demo_mode'] === true): ?>
        <div class="demo-banner">
            <div class="demo-banner-content">
                <i class="fas fa-flask"></i>
                <span>
                    <strong>Mode démonstration</strong> — Vous explorez MenuMiam librement.
                    <?php if (!empty($_SESSION['demo_expires_at'])): ?>
                        Expire le <?= (new DateTime($_SESSION['demo_expires_at']))->format('d/m/Y à H:i') ?>.
                    <?php endif; ?>
                </span>
                <a href="?page=demo-logout" class="demo-banner-btn">Quitter la démo</a>
            </div>
        </div>
    <?php endif; ?>

    <!-- Bouton flottant dark mode (visible sur toutes les pages) -->
    <button id="dark-mode-toggle" class="dark-mode-toggle-floating" title="Mode sombre / clair">
        <i class="fas fa-moon"></i>
        <i class="fas fa-sun"></i>
    </button>

    <!-- Conteneur principal de toutes les pages admin -->
    <div class="container">
    
    <!-- Bannière cookies (partial réutilisable) -->
    <?php include __DIR__ . '/cookie-banner.php'; ?>