<?php
$title = "Tableau de bord";
$scripts = ["js/sections/dashboard/dashboard.js"];

// Formater la date si elle existe
$formatted_date = null;
if (!empty($last_updated)) {
    $date = new DateTime($last_updated);
    $formatted_date = $date->format('d/m/Y à H:i');
}

require __DIR__ . '/../partials/header.php';
?>
<div class="dashboard">
    <!-- En-tête avec titre et bouton paramètres -->
    <div class="dashboard-header">
        <h2>Tableau de bord</h2>
        <a href="?page=settings" class="settings-icon-btn" title="Paramètres">
            <span class="settings-icon">⚙️</span>
        </a>
    </div>

    <!-- Messages flash -->
    <?php if (!empty($success_message)): ?>
        <div class="message-success"><?= htmlspecialchars($success_message) ?></div>
    <?php endif; ?>

    <?php if (!empty($error_message)): ?>
        <div class="message-error"><?= htmlspecialchars($error_message) ?></div>
    <?php endif; ?>

    <!-- Affichage du message de bienvenue personnalisé -->
    <div class="welcome-message">
        <p>Bienvenue <strong><?= htmlspecialchars($username) ?></strong>.</p>
        <p>Vous gérez le restaurant : <strong><?= htmlspecialchars($restaurant_name) ?></strong>.</p>

        <?php if (!empty($slug)): ?>
            <p class="visit-site">
                <a href="?page=display&slug=<?= urlencode($slug) ?>" target="_blank" class="btn btn-outline">
                    <i class="fas fa-external-link-alt"></i> Aller sur le site
                </a>
            </p>
        <?php endif; ?>

        <?php if (!empty($last_updated)): ?>
            <p class="last-updated">Dernière modification de la carte le : <em><strong><?= htmlspecialchars($formatted_date) ?></strong></em>.</p>
        <?php else: ?>
            <p class="last-updated">La carte n'a pas encore été modifiée.</p>
        <?php endif; ?>
    </div>

    <!-- Menu déroulant pour mobile -->
    <div class="dashboard-mobile-menu">
        <button class="mobile-menu-toggle" aria-expanded="false" aria-controls="mobile-menu-content">
            <span class="menu-icon">☰</span>
            <span class="menu-text">Menu</span>
        </button>

        <div class="mobile-menu-content" id="mobile-menu-content">
            <div class="mobile-menu-items">
                <a href="?page=edit-card" class="mobile-menu-item">
                    <div class="menu-item-icon">
                        <i class="fas fa-edit"></i>
                    </div>
                    <div class="menu-item-content">
                        <span class="menu-item-title">Modifier la carte</span>
                        <span class="menu-item-desc">Gérer les catégories et plats</span>
                    </div>
                    <div class="menu-item-arrow">
                        <i class="fas fa-chevron-right"></i>
                    </div>
                </a>

                <a href="?page=edit-contact" class="mobile-menu-item">
                    <div class="menu-item-icon">
                        <i class="fas fa-address-book"></i>
                    </div>
                    <div class="menu-item-content">
                        <span class="menu-item-title">Modifier le contact</span>
                        <span class="menu-item-desc">Coordonnées et horaires</span>
                    </div>
                    <div class="menu-item-arrow">
                        <i class="fas fa-chevron-right"></i>
                    </div>
                </a>

                <a href="?page=edit-logo-banner" class="mobile-menu-item">
                    <div class="menu-item-icon">
                        <i class="fas fa-image"></i>
                    </div>
                    <div class="menu-item-content">
                        <span class="menu-item-title">Modifier logo/bannière</span>
                        <span class="menu-item-desc">Logo du restaurant</span>
                    </div>
                    <div class="menu-item-arrow">
                        <i class="fas fa-chevron-right"></i>
                    </div>
                </a>

                <!-- Services & Options -->
                <a href="?page=edit-services" class="mobile-menu-item">
                    <div class="menu-item-icon">
                        <i class="fas fa-concierge-bell"></i>
                    </div>
                    <div class="menu-item-content">
                        <span class="menu-item-title">Services, paiements & réseaux</span>
                        <span class="menu-item-desc">Gérer les services et contacts</span>
                    </div>
                    <div class="menu-item-arrow">
                        <i class="fas fa-chevron-right"></i>
                    </div>
                </a>

                <a href="?page=view-card" class="mobile-menu-item success">
                    <div class="menu-item-icon">
                        <i class="fas fa-eye"></i>
                    </div>
                    <div class="menu-item-content">
                        <span class="menu-item-title">Aperçu de la carte</span>
                        <span class="menu-item-desc">Voir comme vos clients</span>
                    </div>
                    <div class="menu-item-arrow">
                        <i class="fas fa-external-link-alt"></i>
                    </div>
                </a>

                <?php if ($role === 'SUPER_ADMIN'): ?>
                    <a href="?page=send-invitation" class="mobile-menu-item admin">
                        <div class="menu-item-icon">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <div class="menu-item-content">
                            <span class="menu-item-title">Inviter un utilisateur</span>
                            <span class="menu-item-desc">Création de compte</span>
                        </div>
                        <div class="menu-item-arrow">
                            <i class="fas fa-share"></i>
                        </div>
                    </a>
                <?php endif; ?>
            </div>

            <div class="mobile-menu-footer">
                <a href="?page=settings" class="settings-menu-link">
                    <i class="fas fa-cog"></i> Paramètres du compte
                </a>
            </div>
        </div>
    </div>

    <!-- Boutons normaux pour desktop -->
    <div class="dashboard-desktop">
        <div class="dashboard-top-buttons">
            <a href="?page=edit-card" class="btn">Modifier la carte</a>
            <a href="?page=edit-contact" class="btn">Modifier le contact</a>
            <a href="?page=edit-logo-banner" class="btn">Modifier logo/bannière</a>
            <a href="?page=edit-services" class="btn">Services, paiements & réseaux</a>
        </div>

        <!-- Zone du bas pour les boutons d'action desktop -->
        <div class="dashboard-bottom desktop-bottom">
            <div class="bottom-left">
                <?php if ($role === 'SUPER_ADMIN'): ?>
                    <a href="?page=send-invitation" class="btn">Envoyer un lien de création de compte</a>
                <?php endif; ?>
                <a href="?page=view-card" class="btn success">Aperçu de la carte</a>
            </div>
            <div class="bottom-right">
                <a href="?page=logout" class="btn danger">Se déconnecter</a>
            </div>
        </div>
    </div>

    <!-- Bouton déconnexion toujours visible sur mobile -->
    <div class="mobile-logout-container">
        <a href="?page=logout" class="btn danger mobile-logout">Se déconnecter</a>
    </div>
</div>

<?php
require __DIR__ . '/../partials/footer.php';
?>