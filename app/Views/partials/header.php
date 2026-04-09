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

    <!-- Tour guidé -->
    <script src="/assets/js/admin/tour.js"></script>

    <!-- Utilitaire toast global -->
    <script src="/assets/js/admin/toast.js"></script>

    <!-- Gestion des notifications -->
    <script src="/assets/js/admin/notifications.js" defer></script>

    <!-- Script inline (données pour JS) si fourni -->
    <?php if (!empty($inline_script)): ?>
        <?= $inline_script ?>
    <?php endif; ?>

    <!-- Inclusion de scripts additionnels dynamiques si fournis -->
    <?php if (!empty($scripts)): ?>
        <?php foreach ($scripts as $script): ?>
            <script src="/assets/<?= htmlspecialchars($script) ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</head>

<body data-page="<?= htmlspecialchars($_GET['page'] ?? '') ?>" data-section="<?= htmlspecialchars($_GET['section'] ?? '') ?>">
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

    <!-- Boutons flottants (notifications + dark mode + tour guidé) -->
    <div class="floating-buttons">
        <?php if (isset($pending_reservations_count) && $pending_reservations_count > 0): ?>
        <button type="button" class="notification-toggle-floating" id="notification-toggle" title="Réservations en attente">
            <i class="fas fa-bell"></i>
            <span class="notification-badge" id="notification-count"><?= $pending_reservations_count ?></span>
        </button>
        
        <!-- Dropdown des notifications -->
        <div class="notification-dropdown" id="notification-dropdown" style="display: none;">
            <div class="notification-dropdown-header">
                <h4><i class="fas fa-bell"></i> Réservations en attente</h4>
                <a href="?page=reservations" class="notification-view-all">Tout voir</a>
            </div>
            <div class="notification-dropdown-body" id="notification-list">
                <div class="notification-loading">
                    <i class="fas fa-spinner fa-spin"></i> Chargement...
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <?php 
        // Vérifier si le bouton dark mode doit être masqué
        $hideDarkMode = !empty($_SESSION['admin_id']) && !empty($hide_dark_mode);
        if (!$hideDarkMode): 
        ?>
        <button id="dark-mode-toggle" class="dark-mode-toggle-floating" title="Mode sombre / clair">
            <i class="fas fa-moon"></i>
            <i class="fas fa-sun"></i>
        </button>
        <?php endif; ?>
        
        <?php 
        // Afficher le bouton tour uniquement sur les pages d'édition et si non masqué
        $tourPages = ['dashboard', 'edit-card', 'edit-contact', 'edit-logo-banner', 'edit-services', 'edit-template', 'reservations', 'settings', 'floor-plan', 'stats'];
        $currentPage = $_GET['page'] ?? '';
        $hideTourButton = !empty($_SESSION['admin_id']) && !empty($hide_tour_button);
        if (in_array($currentPage, $tourPages) && !$hideTourButton): 
        ?>
        <button id="tour-toggle" class="tour-toggle-floating" title="Lancer le tour guidé">
            <i class="fas fa-question-circle"></i>
        </button>
        <?php endif; ?>
    </div>

    <!-- Token CSRF pour les appels AJAX -->
    <input type="hidden" id="csrf-token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">

    <!-- Conteneur principal de toutes les pages admin -->
    <div class="container">
    
    <!-- Bannière cookies (partial réutilisable) -->
    <?php include __DIR__ . '/cookie-banner.php'; ?>