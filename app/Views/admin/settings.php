<?php
$title = $title ?? "Paramètres";
$scripts = [
    "js/sections/settings/settings.js",
    "js/sections/settings/premium-cart.js",
    "js/effects/accordion.js",
    "js/sections/settings/closure-dates.js",
    "js/sections/settings/subscriptions.js",
    "js/admin/premium.js"
];

require __DIR__ . '/../partials/header.php';

// Formatage des dates
$created_at = !empty($user['created_at']) ? (new \DateTime($user['created_at']))->format('d/m/Y') : 'N/A';
$last_card_update = !empty($user['last_card_update']) ? (new \DateTime($user['last_card_update']))->format('d/m/Y') : 'Jamais modifiée';
?>

<a class="btn-back" href="?page=dashboard">Retour</a>

<div class="settings-container" data-csrf-token="<?= htmlspecialchars($csrf_token ?? '') ?>">

    <!-- Menu déroulant pour mobile -->
    <div class="settings-mobile-menu">
        <button class="settings-mobile-toggle" aria-expanded="false" aria-controls="settings-mobile-content">
            <span class="settings-menu-icon">☰</span>
        </button>

        <div class="settings-mobile-content" id="settings-mobile-content">
            <ul class="settings-mobile-list">
                <li>
                    <a href="?page=settings&section=account" class="<?= $current_section === 'account' ? 'active' : '' ?>">
                        Informations du compte
                    </a>
                </li>
                <li>
                    <a href="?page=settings&section=profile" class="<?= $current_section === 'profile' ? 'active' : '' ?>">
                        Profil utilisateur
                    </a>
                </li>
                <li>
                    <a href="?page=settings&section=password" class="<?= $current_section === 'password' ? 'active' : '' ?>">
                        Mot de passe
                    </a>
                </li>
                <li>
                    <a href="?page=settings&section=options" class="<?= $current_section === 'options' ? 'active' : '' ?>">
                        Options
                    </a>
                </li>
                <li>
                    <a href="?page=settings&section=premium" class="<?= $current_section === 'premium' ? 'active' : '' ?>">
                        <i class="fas fa-crown"></i>
                        <span>Fonctionnalités</span>
                    </a>
                </li>
                <?php
                $premiumSections = [
                    ['section' => 'google-reviews',  'key' => 'google_reviews',       'icon' => 'fa-star',           'label' => 'Avis Google'],
                    ['section' => 'stats',           'key' => 'advanced_analytics',   'icon' => 'fa-chart-line',     'label' => 'Statistiques avancées'],
                    ['section' => 'online-booking',  'key' => 'online_booking',       'icon' => 'fa-calendar-check', 'label' => 'Réservations en ligne'],
                    ['section' => 'delivery',        'key' => 'delivery_integration', 'icon' => 'fa-motorcycle',     'label' => 'Intégration livraison'],
                ];
                foreach ($premiumSections as $ps):
                    $enabled = !empty($premium_statuses[$ps['key']]);
                    $isActive = $current_section === $ps['section'];
                ?>
                <li>
                    <?php if ($enabled): ?>
                    <a href="?page=settings&section=<?= $ps['section'] ?>" class="settings-premium-link<?= $isActive ? ' active' : '' ?>">
                        <i class="fas <?= $ps['icon'] ?>"></i>
                        <span><?= $ps['label'] ?></span>
                    </a>
                    <?php else: ?>
                    <span class="settings-premium-link settings-premium-locked" title="Abonnement requis pour accéder à cette fonctionnalité">
                        <i class="fas <?= $ps['icon'] ?>"></i>
                        <span><?= $ps['label'] ?></span>
                        <i class="fas fa-lock settings-lock-icon"></i>
                    </span>
                    <?php endif; ?>
                </li>
                <?php endforeach; ?>
                <li>
                    <a href="?page=settings&section=subscriptions" class="<?= $current_section === 'subscriptions' ? 'active' : '' ?>">
                        <i class="fas fa-credit-card"></i>
                        <span>Abonnements</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <!-- Sidebar pour desktop -->
    <div class="settings-sidebar">
        <h3>Paramètres</h3>
        <ul class="settings-menu">
            <li>
                <a href="?page=settings&section=account"
                    class="<?= $current_section === 'account' ? 'active' : '' ?>">
                    Informations du compte
                </a>
            </li>
            <li>
                <a href="?page=settings&section=profile"
                    class="<?= $current_section === 'profile' ? 'active' : '' ?>">
                    Profil utilisateur
                </a>
            </li>
            <li>
                <a href="?page=settings&section=password"
                    class="<?= $current_section === 'password' ? 'active' : '' ?>">
                    Mot de passe
                </a>
            </li>
            <li>
                <a href="?page=settings&section=options"
                    class="<?= $current_section === 'options' ? 'active' : '' ?>">
                    Options
                </a>
            </li>
            <li>
                <a href="?page=settings&section=premium"
                    class="<?= $current_section === 'premium' ? 'active' : '' ?>">
                    <i class="fas fa-crown"></i>
                    Fonctionnalités
                </a>
            </li>
            <?php foreach ($premiumSections as $ps):
                $enabled = !empty($premium_statuses[$ps['key']]);
                $isActive = $current_section === $ps['section'];
            ?>
            <li>
                <?php if ($enabled): ?>
                <a href="?page=settings&section=<?= $ps['section'] ?>"
                    class="settings-premium-link<?= $isActive ? ' active' : '' ?>">
                    <i class="fas <?= $ps['icon'] ?>"></i>
                    <?= $ps['label'] ?>
                </a>
                <?php else: ?>
                <span class="settings-premium-link settings-premium-locked" title="Abonnement requis pour accéder à cette fonctionnalité">
                    <i class="fas <?= $ps['icon'] ?>"></i>
                    <?= $ps['label'] ?>
                    <i class="fas fa-lock settings-lock-icon"></i>
                </span>
                <?php endif; ?>
            </li>
            <?php endforeach; ?>
            <li>
                <a href="?page=settings&section=subscriptions"
                    class="<?= $current_section === 'subscriptions' ? 'active' : '' ?>">
                    <i class="fas fa-credit-card"></i>
                    Abonnements
                </a>
            </li>
        </ul>
    </div>

    <div class="settings-content">
        <h1><?= htmlspecialchars($title) ?></h1>

        <?php if ($current_section === 'profile'): ?>
            <!-- Section Profil -->
            <div class="settings-section" id="profile-form">
                <h2>Profil utilisateur</h2>
                <form method="POST" action="?page=settings&action=update-profile">
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">

                    <div class="form-group">
                        <label for="username">Nom d'utilisateur</label>
                        <input type="text" id="username" name="username"
                            value="<?= htmlspecialchars($user['username'] ?? '') ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email"
                            value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="restaurant_name">Nom du restaurant</label>
                        <input type="text" id="restaurant_name" name="restaurant_name"
                            value="<?= htmlspecialchars($user['restaurant_name'] ?? '') ?>" required>
                    </div>

                    <button type="submit" class="btn">Mettre à jour</button>
                </form>
            </div>

        <?php elseif ($current_section === 'password'): ?>
            <!-- Section Mot de passe avec tous les éléments visuels -->
            <div class="settings-section" id="password-form">
                <h2>Changer le mot de passe</h2>
                <form method="POST" action="?page=settings&action=change-password" id="password-change-form">
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">

                    <div class="form-group">
                        <label for="current_password">Mot de passe actuel *</label>
                        <div class="password-input-group">
                            <div class="password-input-wrapper">
                                <input type="password" id="current_password" name="current_password"
                                    placeholder="Entrez votre mot de passe actuel" required>
                            </div>
                            <button type="button" class="password-toggle-btn" data-target="current_password"
                                aria-label="Afficher le mot de passe">
                                <i class="fa-regular fa-eye"></i>
                            </button>
                        </div>
                        <div class="password-error" id="current_password_error"></div>
                    </div>

                    <div class="form-group">
                        <label for="new_password">Nouveau mot de passe *</label>
                        <div class="password-input-group">
                            <div class="password-input-wrapper">
                                <input type="password" id="new_password" name="new_password"
                                    placeholder="Créez un mot de passe sécurisé" required minlength="8">
                            </div>
                            <button type="button" class="password-toggle-btn" data-target="new_password"
                                aria-label="Afficher le mot de passe">
                                <i class="fa-regular fa-eye"></i>
                            </button>
                        </div>

                        <!-- Indicateur de force du mot de passe -->
                        <div class="password-strength-meter">
                            <div class="strength-bar" id="strength-bar"></div>
                        </div>
                        <div class="strength-text" id="strength-text">Force : faible</div>

                        <!-- Liste des exigences du mot de passe -->
                        <ul class="password-requirements" id="password-requirements">
                            <li class="requirement" data-requirement="length">
                                <i class="fa-solid fa-circle"></i>
                                <span>Au moins 8 caractères</span>
                            </li>
                            <li class="requirement" data-requirement="letter">
                                <i class="fa-solid fa-circle"></i>
                                <span>Au moins une lettre</span>
                            </li>
                            <li class="requirement" data-requirement="uppercase">
                                <i class="fa-solid fa-circle"></i>
                                <span>Au moins une majuscule</span>
                            </li>
                            <li class="requirement" data-requirement="number">
                                <i class="fa-solid fa-circle"></i>
                                <span>Au moins un chiffre</span>
                            </li>
                            <li class="requirement" data-requirement="special">
                                <i class="fa-solid fa-circle"></i>
                                <span>Au moins un caractère spécial</span>
                            </li>
                        </ul>

                        <div class="help-text">
                            <i class="fa-solid fa-lightbulb"></i>
                            Utilisez une combinaison de lettres, chiffres et caractères spéciaux pour plus de sécurité
                        </div>
                        <div class="password-error" id="new_password_error"></div>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirmer le nouveau mot de passe *</label>
                        <div class="password-input-group">
                            <div class="password-input-wrapper">
                                <input type="password" id="confirm_password" name="confirm_password"
                                    placeholder="Retapez votre nouveau mot de passe" required minlength="8">
                            </div>
                            <button type="button" class="password-toggle-btn" data-target="confirm_password"
                                aria-label="Afficher le mot de passe">
                                <i class="fa-regular fa-eye"></i>
                            </button>
                        </div>
                        <div class="password-match-error" id="password-match-error" style="display: none;">
                            <i class="fa-solid fa-exclamation-circle"></i>
                            <span>Les mots de passe ne correspondent pas</span>
                        </div>
                        <div class="password-match-success" id="password-match-success" style="display: none;">
                            <i class="fa-solid fa-check-circle"></i>
                            <span>Les mots de passe correspondent</span>
                        </div>
                        <div class="password-error" id="confirm_password_error"></div>
                    </div>

                    <button type="submit" class="btn" id="submit-password">
                        Changer le mot de passe
                    </button>

                    <div class="password-reset-link">
                        <p><a href="?page=reset-password-admin" target="_blank" rel="noopener noreferrer">Mot de passe oublié ? Réinitialiser le mot de passe</a></p>
                    </div>
                </form>
            </div>

        <?php elseif ($current_section === 'account'): ?>
            <!-- Section Informations du compte -->
            <div class="settings-section">
                <h2>Informations du compte</h2>

                <div class="account-info">
                    <div class="info-row">
                        <span class="info-label">Nom d'utilisateur :</span>
                        <span class="info-value"><?= htmlspecialchars($user['username'] ?? 'N/A') ?></span>
                    </div>

                    <div class="info-row">
                        <span class="info-label">Email :</span>
                        <span class="info-value"><?= htmlspecialchars($user['email'] ?? 'N/A') ?></span>
                    </div>

                    <div class="info-row">
                        <span class="info-label">Nom du restaurant :</span>
                        <span class="info-value"><?= htmlspecialchars($user['restaurant_name'] ?? 'N/A') ?></span>
                    </div>

                    <div class="info-row">
                        <span class="info-label">Rôle :</span>
                        <span class="info-value"><?= htmlspecialchars($user['role'] ?? 'Utilisateur') ?></span>
                    </div>

                    <div class="info-row">
                        <span class="info-label">Date de création :</span>
                        <span class="info-value"><?= $created_at ?></span>
                    </div>

                    <div class="info-row">
                        <span class="info-label">Dernière modification :</span>
                        <span class="info-value"><?= $last_card_update ?></span>
                    </div>
                </div>
            </div>

        <?php elseif ($current_section === 'options'): ?>
            <!-- Section Options -->
            <link rel="stylesheet" href="/assets/css/admin/sections/settings/closure-dates.css">
            <div class="settings-section" id="options-section">
                <h2>Options du compte</h2>
                <p class="section-description">Configurez les paramètres de votre compte et de votre site.</p>

                <!-- Boutons de contrôle global des accordéons -->
                <div class="global-accordion-controls">
                    <button type="button" id="expand-options-accordions" class="btn small">
                        <i class="fas fa-expand-alt"></i> Tout ouvrir
                    </button>
                    <button type="button" id="collapse-options-accordions" class="btn small">
                        <i class="fas fa-compress-alt"></i> Tout fermer
                    </button>
                </div>

                <!-- Accordéon Options du compte -->
                <div class="accordion-section">
                    <div class="accordion-header">
                        <h2><i class="fas fa-cog"></i> Options du compte</h2>
                        <button type="button" class="accordion-toggle" data-target="account-options-content">
                            <i class="fas fa-chevron-down"></i>
                        </button>
                    </div>

                    <div id="account-options-content" class="accordion-content expanded prevent-auto-close">
                        <div class="options-list">
                            <?php foreach (['site_online', 'mail_reminder', 'email_notifications'] as $option): ?>
                                <div class="option-item">
                                    <div class="option-header">
                                        <span class="option-label">
                                            <?=
                                            $option === 'site_online' ? 'Afficher le site en ligne' : ($option === 'mail_reminder' ? 'Rappel mail pour actualisation' :
                                                'Notifications par email')
                                            ?>
                                        </span>
                                        <div class="option-tooltip">
                                            <span class="tooltip-icon" title="Plus d'infos">i</span>
                                            <div class="tooltip-content">
                                                <p>
                                                    <?=
                                                    $option === 'site_online' ? 'Activez cette option pour rendre votre site visible au public. Si désactivé, votre site sera en maintenance.' : ($option === 'mail_reminder' ? 'Recevez un email de rappel tous les mois pour mettre à jour votre carte. Assurez-vous que vos plats et prix sont à jour.' :
                                                        'Recevez des notifications par email pour les mises à jour importantes et les activités sur votre compte.')
                                                    ?>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="option-buttons">
                                        <button type="button"
                                            class="option-btn <?= ($options[$option] ?? '1') === '1' ? 'option-active' : '' ?>"
                                            data-option="<?= $option ?>"
                                            data-value="1">
                                            Actif
                                        </button>
                                        <button type="button"
                                            class="option-btn <?= ($options[$option] ?? '1') === '0' ? 'option-active' : '' ?>"
                                            data-option="<?= $option ?>"
                                            data-value="0">
                                            Non actif
                                        </button>
                                    </div>
                                    <div class="option-description">
                                        <small>
                                            <?=
                                            $option === 'site_online' ? 'Contrôle la visibilité publique de votre site.' : ($option === 'mail_reminder' ? 'Recevez des rappels mensuels pour mettre à jour votre carte.' :
                                                'Activez les notifications importantes par email.')
                                            ?>
                                        </small>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="options-actions">
                            <button type="button" class="btn" id="save-all-options">Enregistrer toutes les options</button>
                            <button type="button" class="btn secondary" id="reset-options">Restaurer les valeurs par défaut</button>
                        </div>
                    </div>
                </div>

                <!-- Accordéon Fermetures Exceptionnelles -->
                <div class="accordion-section" id="closure-dates-section">
                    <div class="accordion-header">
                        <h2><i class="fas fa-calendar-times"></i> Fermetures Exceptionnelles</h2>
                        <button type="button" class="accordion-toggle" data-target="closure-dates-content">
                            <i class="fas fa-chevron-down"></i>
                        </button>
                    </div>

                    <div id="closure-dates-content" class="accordion-content collapsed">
                        <div class="closure-dates-container">
                            <div class="closure-dates-header">
                                <div class="closure-dates-info">
                                    <i class="fas fa-calendar-times"></i>
                                    <span>Cliquez sur les dates dans le calendrier pour ajouter des fermetures exceptionnelles</span>
                                </div>
                            </div>

                            <!-- Calendrier -->
                            <div class="closure-calendar-container">
                                <div class="calendar-header">
                                    <button type="button" class="btn small" id="prev-month">
                                        <i class="fas fa-chevron-left"></i>
                                    </button>
                                    <h3 id="current-month-year">Mars 2026</h3>
                                    <button type="button" class="btn small" id="next-month">
                                        <i class="fas fa-chevron-right"></i>
                                    </button>
                                </div>
                                <div class="calendar-grid" id="closure-calendar">
                                    <!-- Généré par JavaScript -->
                                </div>
                            </div>

                            <!-- Liste des dates sélectionnées -->
                            <div class="selected-dates-container">
                                <div class="selected-dates-header-row">
                                    <h4>Dates de fermeture programmées<span class="count-wrapper"> (<span id="selected-count">0</span>)</span></h4>
                                    <button type="button" class="btn small btn-clear-dates" id="clear-all-closure-dates">
                                        <i class="fas fa-trash"></i> Tout effacer
                                    </button>
                                </div>
                                <div class="selected-dates-list" id="selected-dates-list">
                                    <p class="no-dates">Aucune date de fermeture programmée</p>
                                </div>
                            </div>

                            <div class="closure-dates-actions">
                                <button type="button" class="btn primary" id="save-closure-dates">
                                    <i class="fas fa-save"></i> Enregistrer les dates
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        <?php elseif ($current_section === 'premium'): ?>
            <!-- Section Fonctionnalités -->
            <div class="settings-section">
                <link rel="stylesheet" href="/assets/css/admin/sections/settings/premium.css">
                <script src="/assets/js/effects/accordion.js"></script>
                                <h2>Fonctionnalités</h2>
                <p class="section-description">Débloquez des fonctionnalités avancées pour votre restaurant.</p>

                <!-- Boutons de contrôle global des accordéons -->
                <div class="global-accordion-controls">
                    <button type="button" id="expand-all-accordions" class="btn small">
                        <i class="fas fa-expand-alt"></i> Tout ouvrir
                    </button>
                    <button type="button" id="collapse-all-accordions" class="btn small">
                        <i class="fas fa-compress-alt"></i> Tout fermer
                    </button>
                </div>

                <?php
                require_once __DIR__ . '/../../Models/PremiumFeature.php';
                require_once __DIR__ . '/../../Models/Admin.php';
                $premiumFeature = new PremiumFeature($pdo);
                $adminModel = new Admin($pdo);
                $currentAdmin = $adminModel->findById($_SESSION['admin_id']);
                $isSuperAdmin = ($currentAdmin && $currentAdmin->role === 'SUPER_ADMIN');

                $availableFeatures = $premiumFeature->getAvailableFeatures();
                $userFeatures = $premiumFeature->getAllFeatures($_SESSION['admin_id']);
                $userFeaturesMap = array_column($userFeatures, 'is_active', 'feature_name');
                $subscription = $premiumFeature->hasActiveSubscription($_SESSION['admin_id']);
                $hasPremiumSubscription = !empty($subscription);

                // Récupérer l'abonnement basique
                $basicSub = null;
                try {
                    $stmtB = $pdo->prepare("SELECT * FROM client_subscriptions WHERE admin_id = ? LIMIT 1");
                    $stmtB->execute([$_SESSION['admin_id']]);
                    $basicSub = $stmtB->fetch(PDO::FETCH_ASSOC);
                } catch (Exception $e) { $basicSub = null; }
                $hasActiveSub = $basicSub && $basicSub['status'] === 'active';
                ?>

                <?php if ($isSuperAdmin): ?>
                    <div class="admin-notice">
                        <i class="fas fa-shield-alt"></i>
                        <span>Mode Super-Admin : vous pouvez activer toutes les fonctionnalités sans abonnement.</span>
                    </div>
                <?php elseif ($hasPremiumSubscription): ?>
                    <div class="subscription-badge">
                        <i class="fas fa-crown"></i>
                        <span>Abonnement <strong><?= htmlspecialchars(ucfirst($subscription['plan_type'])) ?></strong> actif
                        <?php if ($subscription['expires_at']): ?>
                            — expire le <?= (new DateTime($subscription['expires_at']))->format('d/m/Y') ?>
                        <?php endif; ?>
                        </span>
                    </div>
                <?php endif; ?>

                <!-- Abonnement Basique -->
                <?php if (!$isSuperAdmin): ?>
                <div class="basique-sub-card <?= $hasActiveSub ? 'active' : 'inactive' ?>" data-basique-active="<?= $hasActiveSub ? '1' : '0' ?>">
                    <div class="basique-sub-header">
                        <div class="basique-sub-icon">
                            <i class="fas fa-store"></i>
                        </div>
                        <div class="basique-sub-info">
                            <h3>Abonnement Basique</h3>
                            <p class="basique-sub-price">9€<span>/mois</span> <small>ou 7€/mois en annuel</small></p>
                        </div>
                        <span class="status-badge <?= $hasActiveSub ? 'active' : 'locked' ?>">
                            <i class="fas <?= $hasActiveSub ? 'fa-check-circle' : 'fa-lock' ?>"></i>
                            <?= $hasActiveSub ? 'Actif' : 'Inactif' ?>
                        </span>
                    </div>
                    <ul class="basique-sub-features">
                        <li><i class="fas fa-check"></i> Site vitrine avec URL personnalisée</li>
                        <li><i class="fas fa-check"></i> Carte en ligne modifiable</li>
                        <li><i class="fas fa-check"></i> Horaires, contact &amp; Google Maps</li>
                        <li><i class="fas fa-check"></i> 7 palettes de couleurs &amp; 3 layouts</li>
                        <li><i class="fas fa-check"></i> Logo, bannière &amp; photos</li>
                        <li><i class="fas fa-check"></i> SEO, RGPD &amp; mentions légales</li>
                    </ul>
                    <?php if (!$hasActiveSub): ?>
                    <div class="basique-sub-actions">
                        <label class="basique-select-label">
                            <input type="checkbox" name="include_basique" value="1" class="basique-checkbox" id="basique-checkbox">
                            <span class="basique-checkmark"></span>
                            <span class="basique-select-text">
                                <i class="fas fa-store"></i> Sélectionner l'abonnement Basique — 9€/mois
                            </span>
                        </label>
                        <p class="basique-sub-note">
                            <i class="fas fa-info-circle"></i>
                            Cochez cette case pour inclure l'abonnement Basique dans votre panier
                        </p>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="accordion-section premium-options-accordion">
                    <div class="accordion-header">
                        <h2><i class="fas fa-bolt"></i> Options premium à la carte</h2>
                        <button type="button" class="accordion-toggle" data-target="premium-options-content">
                            <i class="fas fa-chevron-down"></i>
                        </button>
                    </div>
                    <div id="premium-options-content" class="accordion-content expanded">
                        <form method="POST" action="?page=stripe-checkout" id="premium-cart-form">
                            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">

                            <div class="premium-features-grid">
                                <?php foreach ($availableFeatures as $featureKey => $feature): ?>
                                    <?php
                                    $isActive           = (int)($userFeaturesMap[$featureKey] ?? 0) === 1;
                                    $canActivateDirectly = $isSuperAdmin && !$isActive;
                                    $isSelectable       = !$isSuperAdmin && ($hasActiveSub || !$hasActiveSub); // Sélectionnable si on a un abonnement basique OU si on n'en a pas (pour pouvoir en acheter un)
                                    $cardClass          = $isActive ? 'active' : ($isSelectable ? 'selectable' : '');
                                    ?>
                                    <div class="premium-feature-card <?= $cardClass ?>"
                                         <?= $isSelectable ? 'data-price="' . (int)$feature['price_monthly'] . '" data-feature="' . htmlspecialchars($featureKey) . '"' : '' ?>>
                                        <div class="feature-header">
                                            <div class="feature-icon">
                                                <i class="fas <?= $feature['icon'] ?>"></i>
                                            </div>
                                            <div class="feature-info">
                                                <h3><?= htmlspecialchars($feature['name']) ?></h3>
                                                <p><?= htmlspecialchars($feature['description']) ?></p>
                                            </div>
                                        </div>

                                        <div class="feature-price">
                                            <span class="feature-price-monthly">+<?= (int)$feature['price_monthly'] ?>€<small>/mois</small></span>
                                            <span class="feature-price-annual">+<?= (int)$feature['price_annual'] ?>€<small>/mois en annuel</small></span>
                                            <?php if (!$isActive): ?>
                                            <?php
                                                $now = new DateTime();
                                                $daysInMonth = (int)$now->format('t');
                                                $currentDay = (int)$now->format('j');
                                                $targetMonth = $currentDay > 15 ? (int)$now->format('m') + 1 : (int)$now->format('m');
                                                $targetDate = new DateTime($now->format('Y') . '-' . $targetMonth . '-15');
                                                $daysRemaining = (int)$targetDate->diff($now)->days;
                                                $prorataPrice = round(($daysRemaining / $daysInMonth) * (int)$feature['price_monthly'], 2);
                                            ?>
                                            <span class="feature-price-prorata">Prorata : <?= number_format($prorataPrice, 2) ?>€ <small>ce mois</small></span>
                                            <?php endif; ?>
                                        </div>

                                        <div class="feature-status">
                                            <?php if ($isActive): ?>
                                                <span class="status-badge active">
                                                    <i class="fas fa-check-circle"></i> Activé
                                                </span>
                                            <?php elseif ($canActivateDirectly || $isSelectable): ?>
                                                <span class="status-badge available">
                                                    <i class="fas fa-unlock"></i> Disponible
                                                </span>
                                            <?php else: ?>
                                                <span class="status-badge locked">
                                                    <i class="fas fa-lock"></i> Basique requis
                                                </span>
                                            <?php endif; ?>
                                        </div>

                                        <div class="feature-actions">
                                            <?php if ($isActive): ?>
                                                <?php if ($featureKey === 'google_reviews'): ?>
                                                    <button type="button" class="btn btn-sm configure-google-reviews">
                                                        <i class="fas fa-cog"></i> Configurer
                                                    </button>
                                                <?php endif; ?>
                                            <?php elseif ($canActivateDirectly): ?>
                                                <button type="button" class="btn premium-btn toggle-premium"
                                                        data-feature="<?= $featureKey ?>">
                                                    <i class="fas fa-bolt"></i> Activer
                                                </button>
                                            <?php elseif ($isSelectable): ?>
                                                <label class="feature-select-label">
                                                    <input type="checkbox" name="features[]"
                                                           value="<?= htmlspecialchars($featureKey) ?>"
                                                           class="feature-checkbox">
                                                    <span class="feature-checkmark"></span>
                                                    Sélectionner
                                                </label>
                                            <?php else: ?>
                                                <button type="button" class="btn premium-btn btn-sm" disabled>
                                                    <i class="fas fa-lock"></i> Basique requis
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <?php if ($hasActiveSub && !$isSuperAdmin): ?>
                            <div class="premium-cart-bar" id="premium-cart-bar">
                                <div class="cart-info">
                                    <i class="fas fa-shopping-cart"></i>
                                    <span><strong id="cart-count">0</strong> option(s) sélectionnée(s)</span>
                                    <span class="cart-sep">·</span>
                                    <span>À payer : <strong id="cart-total">0€</strong></span>
                                </div>
                                <div class="cart-actions">
                                    <button type="submit" form="premium-cart-form" class="btn btn-primary premium-checkout-btn" id="premium-checkout-btn" disabled>
                                        <i class="fab fa-stripe-s"></i> Payer les options
                                    </button>
                                </div>
                            </div>
                            <?php elseif (!$hasActiveSub && !$isSuperAdmin): ?>
                            <div class="premium-cart-bar" id="premium-cart-bar" style="display: none;">
                                <div class="cart-info">
                                    <i class="fas fa-shopping-cart"></i>
                                    <span><strong id="cart-count">0</strong> option(s) sélectionnée(s)</span>
                                    <span class="cart-sep">·</span>
                                    <span>À payer : <strong id="cart-total">0€</strong></span>
                                </div>
                            </div>
                            <?php endif; ?>
                        </form>
                    </div>
                
                <!-- Conditions de rétractation -->
                <div class="retractation-notice">
                    <div class="retractation-notice-content">
                        <i class="fas fa-info-circle"></i>
                        <div class="retractation-text">
                            <h4>Droit de rétractation</h4>
                            <p>Conformément à la législation européenne, vous disposez d'un délai de 14 jours pour exercer votre droit de rétractation sans frais ni pénalités.</p>
                            <p>Ce droit s'applique aux services numériques qui n'ont pas été entièrement consommés pendant cette période.</p>
                            <a href="?page=legal&section=cgu#cgu-retractation" class="retractation-link" target="_blank">Voir les conditions complètes</a>
                        </div>
                    </div>
                </div>
                </div>

                <?php if ($hasActiveSub): ?>
                <!-- Total de l'abonnement en cours -->
                <?php
                $totalMonthly = 9; // Abonnement basique
                $activePremiumFeatures = array_filter($userFeaturesMap, fn($v) => (int)$v === 1);
                foreach ($activePremiumFeatures as $featureKey => $_ignore) {
                    $featureDef = $availableFeatures[$featureKey] ?? null;
                    if ($featureDef) {
                        $totalMonthly += (int)$featureDef['price_monthly'];
                    }
                }
                ?>
                <div class="accordion-section premium-total-accordion">
                    <div class="accordion-header">
                        <h2><i class="fas fa-calculator"></i> Total de votre abonnement</h2>
                        <button type="button" class="accordion-toggle" data-target="subscription-total-content">
                            <i class="fas fa-chevron-down"></i>
                        </button>
                    </div>
                    <div id="subscription-total-content" class="accordion-content expanded prevent-auto-close">
                        <div class="subscription-total-breakdown">
                            <div class="breakdown-item">
                                <span>Abonnement Basique</span>
                                <span class="breakdown-price">9€/mois</span>
                            </div>
                            <?php foreach ($activePremiumFeatures as $featureKey => $_ignore): ?>
                                <?php $featureDef = $availableFeatures[$featureKey] ?? null; if (!$featureDef) continue; ?>
                                <div class="breakdown-item premium-item">
                                    <span>
                                        <i class="fas <?= $featureDef['icon'] ?>"></i>
                                        <?= htmlspecialchars($featureDef['name']) ?>
                                    </span>
                                    <span class="breakdown-price">+<?= (int)$featureDef['price_monthly'] ?>€/mois</span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="subscription-total-amount">
                            <span>Total mensuel</span>
                            <span class="total-price"><?= $totalMonthly ?>€<small>/mois</small></span>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                <?php endif; ?>

                <!-- Panier combiné Basique + Premium (pour les utilisateurs sans abonnement) -->
                <?php if (!$hasActiveSub && !$isSuperAdmin): ?>
                <div class="combined-cart-section" id="combined-cart-section" style="display: none;">
                    <div class="combined-cart-header">
                        <h2><i class="fas fa-shopping-cart"></i> Panier d'abonnement</h2>
                        <p>Sélectionnez votre abonnement basique et les options premium en un seul paiement</p>
                    </div>
                    
                    <form method="post" action="?page=stripe-checkout" class="combined-cart-form" id="combined-cart-form">
                        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                        
                        <div class="cart-summary">
                            <div class="cart-item basique-item" id="basique-cart-item" style="display: none;">
                                <div class="item-info">
                                    <i class="fas fa-store"></i>
                                    <span>Abonnement Basique</span>
                                </div>
                                <span class="item-price">9€/mois</span>
                            </div>
                            
                            <div class="premium-items" id="premium-items">
                                <div class="premium-placeholder">
                                    <p>Aucune option premium sélectionnée</p>
                                </div>
                            </div>
                            
                            <div class="cart-total">
                                <span>Total mensuel</span>
                                <span class="total-price" id="combined-total">0€/mois</span>
                            </div>
                            <div class="cart-prorata">
                                <span>Paiement initial</span>
                                <span class="prorata-price" id="combined-prorata">0€ à payer maintenant</span>
                            </div>
                        </div>
                        
                        <div class="combined-cart-actions">
                            <button type="submit" class="btn btn-primary combined-checkout-btn" id="combined-checkout-btn" disabled>
                                <i class="fab fa-stripe-s"></i> Payer et activer
                            </button>
                        </div>
                    </form>
                </div>
                <?php endif; ?>

                <!-- Configuration Google Reviews (affichée si activé) -->
                <div id="google-reviews-config" class="google-reviews-config" style="display: none;">
                    <div class="config-card">
                        <h3><i class="fas fa-cog"></i> Configuration Avis Google</h3>
                        <p class="config-description">Configurez votre restaurant pour afficher les avis Google sur votre site.</p>
                        
                        <form method="POST" action="?page=settings&action=update-google-reviews" class="google-config-form">
                            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                            
                            <div class="config-grid">
                                <div class="form-group">
                                    <label for="google_place_id">
                                        <i class="fas fa-map-marker-alt"></i>
                                        Google Place ID
                                    </label>
                                    <input type="text" id="google_place_id" name="google_place_id"
                                           value="<?= htmlspecialchars($options['google_place_id'] ?? '') ?>"
                                           placeholder="ex: ChIJb8h2Y6Xu5kcRjLGLt_4nN1E"
                                           class="form-control">
                                    <small class="help-text">
                                        Identifiant unique de votre lieu sur Google Maps. 
                                        <a href="https://developers.google.com/maps/documentation/places/web-service/place-id" target="_blank" rel="noopener">
                                            Comment trouver mon Place ID ? <i class="fas fa-external-link-alt"></i>
                                        </a>
                                    </small>
                                </div>

                                <div class="form-group">
                                    <label for="google_api_key">
                                        <i class="fas fa-key"></i>
                                        Clé API Google (optionnel)
                                    </label>
                                    <input type="password" id="google_api_key" name="google_api_key"
                                           value="<?= htmlspecialchars($options['google_api_key'] ?? '') ?>"
                                           placeholder="AIzaSy..."
                                           class="form-control">
                                    <small class="help-text">
                                        Clé API Google Places. Si non renseignée, utilise la clé globale du système.
                                    </small>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="checkbox-label">
                                    <input type="checkbox" id="google_reviews_enabled" name="google_reviews_enabled"
                                           value="1" <?= ($options['google_reviews_enabled'] ?? '0') === '1' ? 'checked' : '' ?>>
                                    <span class="checkmark"></span>
                                    Activer l'affichage des avis Google sur votre site
                                </label>
                                <small class="help-text">
                                    Affiche la section avis Google sur votre site vitrine (sous la carte).
                                </small>
                            </div>

                            <div class="config-actions">
                                <button type="submit" class="btn primary-btn">
                                    <i class="fas fa-save"></i>
                                    Enregistrer la configuration
                                </button>
                                <button type="button" class="btn secondary-btn" id="test-google-api">
                                    <i class="fas fa-flask"></i>
                                    Tester la connexion
                                </button>
                            </div>
                        </form>

                        <!-- Zone de test -->
                        <div id="google-api-test-result" class="api-test-result" style="display: none;">
                            <h4><i class="fas fa-vial"></i> Résultat du test</h4>
                            <div id="test-content"></div>
                        </div>
                    </div>
                </div>

                <div class="premium-info">
                    <div class="info-card">
                        <h4><i class="fas fa-info-circle"></i> Comment ça marche ?</h4>
                        <ul>
                            <li>Souscrivez un abonnement Premium pour débloquer les fonctionnalités proposées par notre service.</li>
                            <li>Activez et configurez les fonctionnalités directement depuis cette page.</li>
                            <li>Contactez-nous à <a href="mailto:premium@menumiam.fr">premium@menumiam.fr</a> pour souscrire ou pour toute question.</li>
                        </ul>
                    </div>
                </div>
            </div>
        <?php elseif ($current_section === 'subscriptions'): ?>
            <!-- Section Abonnements -->
            <div class="settings-section">
                <link rel="stylesheet" href="/assets/css/admin/sections/settings/premium.css">
                <link rel="stylesheet" href="/assets/css/admin/sections/settings/subscriptions.css">
                <script src="/assets/js/effects/accordion.js"></script>

                <h2>Abonnements</h2>
                <p class="section-description">Gérez vos abonnements et options actives.</p>
                
                <!-- Boutons de contrôle global des accordéons -->
                <div class="global-accordion-controls">
                    <button type="button" id="expand-all-accordions" class="btn small">
                        <i class="fas fa-expand-alt"></i> Tout ouvrir
                    </button>
                    <button type="button" id="collapse-all-accordions" class="btn small">
                        <i class="fas fa-compress-alt"></i> Tout fermer
                    </button>
                </div>

                <?php
                require_once __DIR__ . '/../../Models/PremiumFeature.php';
                require_once __DIR__ . '/../../Models/Admin.php';
                $premiumFeature = new PremiumFeature($pdo);
                $adminModel = new Admin($pdo);
                $currentAdmin = $adminModel->findById($_SESSION['admin_id']);
                $isSuperAdmin = ($currentAdmin && $currentAdmin->role === 'SUPER_ADMIN');

                $availableFeatures = $premiumFeature->getAvailableFeatures();
                $userFeatures = $premiumFeature->getAllFeatures($_SESSION['admin_id']);
                $userFeaturesMap = array_column($userFeatures, 'is_active', 'feature_name');

                // Récupérer l'abonnement basique
                $basicSub = null;
                try {
                    $stmtB = $pdo->prepare("SELECT * FROM client_subscriptions WHERE admin_id = ? LIMIT 1");
                    $stmtB->execute([$_SESSION['admin_id']]);
                    $basicSub = $stmtB->fetch(PDO::FETCH_ASSOC);
                } catch (Exception $e) { $basicSub = null; }
                $hasActiveSub = $basicSub && $basicSub['status'] === 'active';
                $basicStartedAt = !empty($basicSub['started_at']) ? (new DateTime($basicSub['started_at']))->format('d/m/Y') : null;
                ?>

                <?php if ($isSuperAdmin): ?>
                    <div class="admin-notice">
                        <i class="fas fa-shield-alt"></i>
                        <span>Mode Super-Admin : vous avez accès à toutes les fonctionnalités.</span>
                    </div>
                <?php endif; ?>

                <?php if (!$isSuperAdmin && $hasActiveSub): ?>
                <!-- Gestion des abonnements actifs -->
                <?php $activePremiumFeatures = array_filter($userFeaturesMap, fn($v) => (int)$v === 1); ?>
                
                <!-- Total de l'abonnement en cours -->
                <?php
                $totalMonthly = 9; // Abonnement basique
                foreach ($activePremiumFeatures as $featureKey => $_ignore) {
                    $featureDef = $availableFeatures[$featureKey] ?? null;
                    if ($featureDef) {
                        $totalMonthly += (int)$featureDef['price_monthly'];
                    }
                }
                ?>
                <div class="accordion-section premium-total-accordion" id="subscription-total">
                    <div class="accordion-header">
                        <h2><i class="fas fa-calculator"></i> Total de votre abonnement</h2>
                        <button type="button" class="accordion-toggle" data-target="subscription-total-content">
                            <i class="fas fa-chevron-down"></i>
                        </button>
                    </div>
                    <div id="subscription-total-content" class="accordion-content expanded prevent-auto-close">
                        <div class="subscription-total-breakdown">
                            <div class="breakdown-item">
                                <span>Abonnement Basique</span>
                                <span class="breakdown-price">9€/mois</span>
                            </div>
                            <?php foreach ($activePremiumFeatures as $featureKey => $_ignore): ?>
                                <?php $featureDef = $availableFeatures[$featureKey] ?? null; if (!$featureDef) continue; ?>
                                <div class="breakdown-item premium-item">
                                    <span>
                                        <i class="fas <?= $featureDef['icon'] ?>"></i>
                                        <?= htmlspecialchars($featureDef['name']) ?>
                                    </span>
                                    <span class="breakdown-price">+<?= (int)$featureDef['price_monthly'] ?>€/mois</span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="subscription-total-amount">
                            <span>Total mensuel</span>
                            <span class="total-price"><?= $totalMonthly ?>€<small>/mois</small></span>
                        </div>
                    </div>
                </div>
                
                <?php
                // Préparer les données d'abonnement pour la section subscriptions
                $basicStartedAt = null;
                $basicTimeLeft = null;
                $premiumData = [];
                
                if (!empty($subscription_data['basic'])) {
                    $basic = $subscription_data['basic'];
                    $basicStartedAt = !empty($basic['started_at']) ? (new DateTime($basic['started_at']))->format('d/m/Y H:i') : null;
                    $basicTimeLeft = $basic['time_left'] ?? null;
                }
                
                if (!empty($subscription_data['premium'])) {
                    foreach ($subscription_data['premium'] as $featureName => $premium) {
                        $activatedAt = !empty($premium['activated_at']) ? (new DateTime($premium['activated_at']))->format('d/m/Y H:i') : null;
                        $premiumData[$featureName] = [
                            'activated_at' => $activatedAt,
                            'prorata_amount' => $premium['prorata_amount'] ?? 0
                        ];
                    }
                }
                ?>
                
                <div class="accordion-section subscription-management-accordion">
                    <div class="accordion-header">
                        <h2><i class="fas fa-sliders-h"></i> Gérer mes abonnements</h2>
                        <button type="button" class="accordion-toggle" data-target="subscription-management-content">
                            <i class="fas fa-chevron-down"></i>
                        </button>
                    </div>
                    <div id="subscription-management-content" class="accordion-content expanded">
                        
                        <!-- Actions groupées -->
                        <div class="subscription-bulk-actions">
                            <div class="bulk-select-all">
                                <label class="bulk-select-label">
                                    <input type="checkbox" id="select-all-subs" class="select-all-checkbox">
                                    <span>Tout sélectionner</span>
                                </label>
                            </div>
                            <div class="bulk-actions-buttons">
                                <button type="button" id="bulk-cancel-subs" class="btn btn-danger" disabled>
                                    <i class="fas fa-trash-alt"></i> Résilier la sélection
                                </button>
                            </div>
                        </div>

                        <!-- Liste des abonnements en cards -->
                        <div class="subscriptions-list">
                            <!-- Abonnement Basique -->
                            <div class="sub-card sub-card-basique subscription-row">
                                <div class="sub-card-check">
                                    <input type="checkbox" class="sub-checkbox" data-type="basique" data-name="Abonnement Basique" id="sub-basique">
                                    <label for="sub-basique" class="sub-checkbox-label"></label>
                                </div>
                                <div class="sub-card-body">
                                    <div class="sub-card-top">
                                        <div class="sub-card-info">
                                            <span class="sub-type-badge basique-badge">
                                                <i class="fas fa-store"></i> Basique
                                            </span>
                                            <h4 class="sub-card-name">Abonnement Basique MenuMiam</h4>
                                        </div>
                                        <span class="sub-card-price">9€<small>/mois</small></span>
                                    </div>
                                    <div class="sub-card-bottom">
                                        <div class="sub-card-meta">
                                            <span class="status-badge active">
                                                <i class="fas fa-check-circle"></i> Actif
                                            </span>
                                            <?php if ($basicStartedAt): ?>
                                            <span class="sub-card-date">
                                                <i class="fas fa-calendar-alt"></i> Depuis le <?= $basicStartedAt ?>
                                            </span>
                                            <?php endif; ?>
                                            <?php if ($basicTimeLeft): ?>
                                            <span class="sub-card-time-left">
                                                <i class="fas fa-hourglass-half"></i> <?= $basicTimeLeft ?>
                                            </span>
                                            <?php endif; ?>
                                        </div>
                                        <form method="POST" action="?page=cancel-subscription"
                                              class="cancel-form"
                                              data-subscription-type="basique"
                                              data-feature-name="Abonnement Basique">
                                            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                                            <input type="hidden" name="type" value="basique">
                                            <button type="submit" class="btn btn-sm btn-danger-outline">
                                                <i class="fas fa-times-circle"></i> Résilier
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Options Premium -->
                            <?php foreach ($activePremiumFeatures as $featureKey => $_ignore): ?>
                                <?php $featureDef = $availableFeatures[$featureKey] ?? null; if (!$featureDef) continue; ?>
                            <div class="sub-card sub-card-premium subscription-row">
                                <div class="sub-card-check">
                                    <input type="checkbox" class="sub-checkbox" data-type="premium" data-name="<?= htmlspecialchars($featureDef['name']) ?>" id="sub-<?= htmlspecialchars($featureKey) ?>">
                                    <label for="sub-<?= htmlspecialchars($featureKey) ?>" class="sub-checkbox-label"></label>
                                </div>
                                <div class="sub-card-body">
                                    <div class="sub-card-top">
                                        <div class="sub-card-info">
                                            <span class="sub-type-badge premium-badge">
                                                <i class="fas fa-star"></i> Premium
                                            </span>
                                            <h4 class="sub-card-name"><?= htmlspecialchars($featureDef['name']) ?></h4>
                                        </div>
                                        <span class="sub-card-price">+<?= (int)$featureDef['price_monthly'] ?>€<small>/mois</small></span>
                                    </div>
                                    <div class="sub-card-bottom">
                                        <div class="sub-card-meta">
                                            <span class="status-badge active">
                                                <i class="fas fa-check-circle"></i> Actif
                                            </span>
                                            <?php if (!empty($premiumData[$featureKey]['activated_at'])): ?>
                                            <span class="sub-card-date">
                                                <i class="fas fa-calendar-alt"></i> Activé le <?= $premiumData[$featureKey]['activated_at'] ?>
                                            </span>
                                            <?php endif; ?>
                                            <?php if (!empty($premiumData[$featureKey]['prorata_amount']) && $premiumData[$featureKey]['prorata_amount'] > 0): ?>
                                            <span class="sub-card-prorata">
                                                <i class="fas fa-calculator"></i> Prorata : <?= $premiumData[$featureKey]['prorata_amount'] ?>€ ce mois
                                            </span>
                                            <?php endif; ?>
                                        </div>
                                        <form method="POST" action="?page=cancel-subscription"
                                              class="cancel-form"
                                              data-subscription-type="premium"
                                              data-feature-name="<?= htmlspecialchars($featureDef['name']) ?>">
                                            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                                            <input type="hidden" name="type" value="premium">
                                            <input type="hidden" name="feature" value="<?= htmlspecialchars($featureKey) ?>">
                                            <button type="submit" class="btn btn-sm btn-danger-outline">
                                                <i class="fas fa-trash-alt"></i> Supprimer
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>

                            <?php if (empty($activePremiumFeatures)): ?>
                            <div class="sub-card-empty">
                                <i class="fas fa-info-circle"></i>
                                <span>Aucune option premium active pour le moment.</span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php elseif (!$isSuperAdmin): ?>
                    <div class="subscription-notice">
                        <div class="subscription-notice-content">
                            <i class="fas fa-info-circle"></i>
                            <p>Vous devez avoir un abonnement Basique actif pour gérer vos abonnements.</p>
                        </div>
                        <a href="?page=settings&section=premium" class="btn primary">
                            <i class="fas fa-crown"></i> Voir les fonctionnalités
                        </a>
                    </div>
                <?php endif; ?>
            </div>

        <?php elseif ($current_section === 'google-reviews' && !empty($premium_statuses['google_reviews'])): ?>
            <!-- Section Avis Google -->
            <link rel="stylesheet" href="/assets/css/admin/sections/settings/google-reviews.css">
            <div class="settings-section">
                <h2><i class="fas fa-star"></i> Avis Google</h2>
                <p class="section-description">Affichez les avis Google de votre restaurant directement sur votre site vitrine.</p>

                <?php
                    $grPlaceId = $options['google_place_id'] ?? '';
                    $grApiKey = $options['google_api_key'] ?? '';
                    $grEnabled = ($options['google_reviews_enabled'] ?? '0') === '1';
                    $grData = $google_reviews_data ?? null;
                    $grConfigured = !empty($grPlaceId) && !empty($grApiKey);
                ?>

                <!-- Statut de la connexion -->
                <div class="gr-status-cards">
                    <div class="gr-status-card <?= $grConfigured ? 'success' : 'warning' ?>">
                        <i class="fas <?= $grConfigured ? 'fa-plug' : 'fa-exclamation-triangle' ?>"></i>
                        <div>
                            <strong><?= $grConfigured ? 'API configurée' : 'API non configurée' ?></strong>
                            <small><?= $grConfigured ? 'Clé API et Place ID renseignés' : 'Renseignez votre clé API et Place ID ci-dessous' ?></small>
                        </div>
                    </div>
                    <div class="gr-status-card <?= $grEnabled ? 'success' : 'info' ?>">
                        <i class="fas <?= $grEnabled ? 'fa-eye' : 'fa-eye-slash' ?>"></i>
                        <div>
                            <strong><?= $grEnabled ? 'Visible sur le site' : 'Masqué sur le site' ?></strong>
                            <small><?= $grEnabled ? 'Les avis sont affichés sur votre vitrine' : 'Activez l\'affichage dans la configuration' ?></small>
                        </div>
                    </div>
                    <div class="gr-status-card <?= $grData ? 'success' : 'neutral' ?>">
                        <i class="fas <?= $grData ? 'fa-star' : 'fa-question-circle' ?>"></i>
                        <div>
                            <strong><?= $grData ? number_format($grData['rating'] ?? 0, 1) . '/5 — ' . ($grData['total_reviews'] ?? 0) . ' avis' : 'Aucun avis chargé' ?></strong>
                            <small><?= $grData ? htmlspecialchars($grData['name'] ?? '') : 'Configurez l\'API pour voir vos avis' ?></small>
                        </div>
                    </div>
                </div>

                <!-- Configuration -->
                <div class="gr-config-section">
                    <h3><i class="fas fa-cog"></i> Configuration</h3>
                    <form method="POST" action="?page=settings&action=update-google-reviews" class="gr-config-form">
                        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                        
                        <div class="gr-config-grid">
                            <div class="form-group">
                                <label for="google_place_id">
                                    <i class="fas fa-map-marker-alt"></i> Google Place ID
                                </label>
                                <input type="text" id="google_place_id" name="google_place_id"
                                       value="<?= htmlspecialchars($grPlaceId) ?>"
                                       placeholder="ex: ChIJb8h2Y6Xu5kcRjLGLt_4nN1E"
                                       class="form-control">
                                <small class="help-text">
                                    <a href="https://developers.google.com/maps/documentation/places/web-service/place-id" target="_blank" rel="noopener">
                                        Comment trouver mon Place ID ? <i class="fas fa-external-link-alt"></i>
                                    </a>
                                </small>
                            </div>

                            <div class="form-group">
                                <label for="google_api_key">
                                    <i class="fas fa-key"></i> Clé API Google
                                </label>
                                <input type="password" id="google_api_key" name="google_api_key"
                                       value="<?= htmlspecialchars($grApiKey) ?>"
                                       placeholder="AIzaSy..."
                                       class="form-control">
                                <small class="help-text">
                                    Clé API avec l'API <strong>Places API (New)</strong> activée.
                                    <a href="https://console.cloud.google.com/apis/credentials" target="_blank" rel="noopener">
                                        Console Google Cloud <i class="fas fa-external-link-alt"></i>
                                    </a>
                                </small>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="google_reviews_enabled" value="1" <?= $grEnabled ? 'checked' : '' ?>>
                                <span class="checkmark"></span>
                                Activer l'affichage des avis Google sur votre site vitrine
                            </label>
                        </div>

                        <div class="gr-config-actions">
                            <button type="submit" class="btn primary-btn">
                                <i class="fas fa-save"></i> Enregistrer
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Aperçu des avis -->
                <?php if ($grData && !empty($grData['reviews'])): ?>
                <div class="gr-preview-section">
                    <h3><i class="fas fa-eye"></i> Aperçu des avis</h3>
                    <p class="gr-preview-info">Voici comment vos avis apparaîtront sur votre site vitrine.</p>

                    <div class="gr-summary">
                        <div class="gr-rating-large">
                            <span class="gr-rating-number"><?= number_format($grData['rating'], 1) ?></span>
                            <div class="gr-stars">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="fas fa-star <?= $i <= round($grData['rating']) ? 'filled' : 'empty' ?>"></i>
                                <?php endfor; ?>
                            </div>
                            <span class="gr-total"><?= $grData['total_reviews'] ?> avis Google</span>
                        </div>
                    </div>

                    <div class="gr-reviews-grid">
                        <?php foreach ($grData['reviews'] as $review): ?>
                        <div class="gr-review-card">
                            <div class="gr-review-header">
                                <div class="gr-reviewer">
                                    <?php if (!empty($review['profile_photo_url'])): ?>
                                        <img src="<?= htmlspecialchars($review['profile_photo_url']) ?>" 
                                             alt="<?= htmlspecialchars($review['author_name']) ?>"
                                             class="gr-avatar">
                                    <?php else: ?>
                                        <div class="gr-avatar-placeholder">
                                            <i class="fas fa-user"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div>
                                        <div class="gr-reviewer-name"><?= htmlspecialchars($review['author_name']) ?></div>
                                        <div class="gr-review-date"><?= htmlspecialchars($review['relative_time_description']) ?></div>
                                    </div>
                                </div>
                                <div class="gr-review-stars">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star <?= $i <= $review['rating'] ? 'filled' : 'empty' ?>"></i>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            <?php if (!empty($review['text'])): ?>
                            <div class="gr-review-text">
                                <p><?= htmlspecialchars($review['text']) ?></p>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php elseif ($grConfigured): ?>
                <div class="gr-preview-section">
                    <h3><i class="fas fa-eye"></i> Aperçu des avis</h3>
                    <div class="gr-empty-state">
                        <i class="fas fa-sync-alt"></i>
                        <p>Aucun avis chargé. Vérifiez votre Place ID et votre clé API, puis rechargez la page.</p>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Guide de configuration -->
                <?php if (!$grConfigured): ?>
                <div class="gr-guide-section">
                    <h3><i class="fas fa-book"></i> Guide de configuration</h3>
                    <div class="gr-steps">
                        <div class="gr-step">
                            <div class="gr-step-number">1</div>
                            <div class="gr-step-content">
                                <h4>Activer l'API Places (New)</h4>
                                <p>Dans votre <a href="https://console.cloud.google.com/apis/library/places-backend.googleapis.com" target="_blank" rel="noopener">Google Cloud Console</a>, activez l'API <strong>Places API (New)</strong>.</p>
                            </div>
                        </div>
                        <div class="gr-step">
                            <div class="gr-step-number">2</div>
                            <div class="gr-step-content">
                                <h4>Créer une clé API</h4>
                                <p>Allez dans <a href="https://console.cloud.google.com/apis/credentials" target="_blank" rel="noopener">Identifiants</a> et créez une clé API. Restreignez-la à l'API Places (New).</p>
                            </div>
                        </div>
                        <div class="gr-step">
                            <div class="gr-step-number">3</div>
                            <div class="gr-step-content">
                                <h4>Trouver votre Place ID</h4>
                                <p>Utilisez le <a href="https://developers.google.com/maps/documentation/places/web-service/place-id" target="_blank" rel="noopener">Place ID Finder</a> pour trouver l'identifiant de votre restaurant.</p>
                            </div>
                        </div>
                        <div class="gr-step">
                            <div class="gr-step-number">4</div>
                            <div class="gr-step-content">
                                <h4>Renseignez les champs ci-dessus</h4>
                                <p>Collez votre Place ID et votre clé API dans le formulaire de configuration, puis activez l'affichage.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Gestion des avis de test (uniquement pour développement) -->
                <div class="gr-test-management-section">
                    <div class="gr-warning-banner">
                        <div class="warning-content">
                            <i class="fas fa-exclamation-triangle"></i>
                            <div>
                                <strong>⚠️ ATTENTION - MODE DÉVELOPPEMENT</strong>
                                <p>Cette section permet de créer des avis fictifs pour les tests. <strong>Elle doit être supprimée avant la mise en production</strong> car les utilisateurs ne pourront pas créer d'avis fictifs.</p>
                            </div>
                        </div>
                    </div>
                    
                    <h3><i class="fas fa-tools"></i> Gestion des avis de test</h3>
                    <p class="gr-management-desc">Actions pour gérer les avis de test dans le cache (utilisé uniquement pour développement/démonstration).</p>
                    
                    <div class="gr-actions-table">
                        <div class="table-header">
                            <div class="col-action">Action</div>
                            <div class="col-description">Description</div>
                            <div class="col-link">Lien</div>
                        </div>
                        
                        <div class="table-row">
                            <div class="col-action">
                                <span class="action-badge replace">🔄 Remplacer</span>
                            </div>
                            <div class="col-description">
                                Remplace tous les avis existants par 5 avis de test standards
                            </div>
                            <div class="col-link">
                                <a href="?page=seed-reviews&action=replace" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-play"></i> Exécuter
                                </a>
                            </div>
                        </div>
                        
                        <div class="table-row">
                            <div class="col-action">
                                <span class="action-badge add">➕ Ajouter</span>
                            </div>
                            <div class="col-description">
                                Ajoute 5 nouveaux avis de test aux avis existants (Lucas, Emma, Nicolas, Camille, Antoine)
                            </div>
                            <div class="col-link">
                                <a href="?page=seed-reviews&action=add-5" class="btn btn-sm btn-outline-success">
                                    <i class="fas fa-plus"></i> Exécuter
                                </a>
                            </div>
                        </div>
                        
                        <div class="table-row">
                            <div class="col-action">
                                <span class="action-badge clear">🗑️ Vider</span>
                            </div>
                            <div class="col-description">
                                Supprime tous les avis du cache (retour à l'état vide)
                            </div>
                            <div class="col-link">
                                <a href="?page=seed-reviews&action=clear" class="btn btn-sm btn-outline-danger">
                                    <i class="fas fa-trash"></i> Exécuter
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="gr-note-box">
                        <i class="fas fa-info-circle"></i>
                        <div>
                            <strong>Note :</strong> Les avis restent en cache 1 heure. Après expiration, ils seront remplacés par les vrais avis Google si l'API est configurée.
                        </div>
                    </div>
                </div>
            </div>

        <?php elseif ($current_section === 'stats' && !empty($has_advanced_stats)): ?>
            <!-- Section Statistiques avancées -->
            <link rel="stylesheet" href="/assets/css/admin/sections/stats/stats.css">
            <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
            <div class="settings-section">
                <div class="stats-page stats-page-embedded">
                    <div class="stats-header">
                        <div class="stats-header-left">
                            <div>
                                <h2><i class="fas fa-chart-line"></i> Statistiques avancées</h2>
                                <p class="stats-subtitle">
                                    <?= htmlspecialchars($restaurant_name_display ?? '') ?>
                                    <?php if (!empty($slug)): ?>
                                        — <a href="?page=display&slug=<?= htmlspecialchars($slug) ?>" target="_blank" class="stats-site-link">
                                            <i class="fas fa-external-link-alt"></i> Voir le site
                                        </a>
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>
                        <div class="stats-header-right">
                            <div class="stats-period-selector">
                                <button type="button" class="period-btn" data-days="7">7j</button>
                                <button type="button" class="period-btn active" data-days="30">30j</button>
                                <button type="button" class="period-btn" data-days="90">90j</button>
                                <button type="button" class="period-btn" data-days="365">1an</button>
                            </div>
                        </div>
                    </div>

                    <!-- KPI Cards -->
                    <div class="stats-kpi-grid">
                        <div class="kpi-card">
                            <div class="kpi-icon"><i class="fas fa-eye"></i></div>
                            <div class="kpi-content">
                                <span class="kpi-value" id="kpi-total-visits">—</span>
                                <span class="kpi-label">Visites totales</span>
                            </div>
                            <div class="kpi-trend" id="kpi-trend-visits"></div>
                        </div>
                        <div class="kpi-card">
                            <div class="kpi-icon kpi-icon-unique"><i class="fas fa-users"></i></div>
                            <div class="kpi-content">
                                <span class="kpi-value" id="kpi-unique-visitors">—</span>
                                <span class="kpi-label">Visiteurs uniques</span>
                            </div>
                        </div>
                        <div class="kpi-card">
                            <div class="kpi-icon kpi-icon-avg"><i class="fas fa-chart-bar"></i></div>
                            <div class="kpi-content">
                                <span class="kpi-value" id="kpi-avg-daily">—</span>
                                <span class="kpi-label">Moy. / jour</span>
                            </div>
                        </div>
                        <div class="kpi-card">
                            <div class="kpi-icon kpi-icon-device"><i class="fas fa-mobile-alt"></i></div>
                            <div class="kpi-content">
                                <span class="kpi-value" id="kpi-mobile-pct">—</span>
                                <span class="kpi-label">Mobile</span>
                            </div>
                        </div>
                    </div>

                    <!-- Graphique principal : visites par jour -->
                    <div class="stats-chart-card stats-chart-main">
                        <div class="chart-card-header">
                            <h3><i class="fas fa-chart-area"></i> Visites par jour</h3>
                        </div>
                        <div class="chart-container chart-container-main">
                            <canvas id="chart-visits-per-day"></canvas>
                        </div>
                    </div>

                    <!-- Ligne 2 : Appareils + Navigateurs -->
                    <div class="stats-charts-row">
                        <div class="stats-chart-card">
                            <div class="chart-card-header">
                                <h3><i class="fas fa-laptop"></i> Appareils</h3>
                            </div>
                            <div class="chart-container chart-container-sm">
                                <canvas id="chart-devices"></canvas>
                            </div>
                        </div>
                        <div class="stats-chart-card">
                            <div class="chart-card-header">
                                <h3><i class="fas fa-globe"></i> Navigateurs</h3>
                            </div>
                            <div class="chart-container chart-container-sm">
                                <canvas id="chart-browsers"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Ligne 3 : Heures de pointe + Jours de la semaine -->
                    <div class="stats-charts-row">
                        <div class="stats-chart-card">
                            <div class="chart-card-header">
                                <h3><i class="fas fa-clock"></i> Heures de pointe</h3>
                            </div>
                            <div class="chart-container chart-container-sm">
                                <canvas id="chart-hours"></canvas>
                            </div>
                        </div>
                        <div class="stats-chart-card">
                            <div class="chart-card-header">
                                <h3><i class="fas fa-calendar-week"></i> Jours de la semaine</h3>
                            </div>
                            <div class="chart-container chart-container-sm">
                                <canvas id="chart-weekdays"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Top référents -->
                    <div class="stats-chart-card">
                        <div class="chart-card-header">
                            <h3><i class="fas fa-link"></i> Sources de trafic</h3>
                        </div>
                        <div class="stats-referrers-table" id="referrers-table">
                            <div class="stats-loading"><i class="fas fa-spinner fa-spin"></i> Chargement…</div>
                        </div>
                    </div>

                    <!-- Loader global -->
                    <div class="stats-global-loader" id="stats-loader">
                        <div class="stats-loader-spinner">
                            <i class="fas fa-chart-line fa-spin"></i>
                            <p>Chargement des statistiques…</p>
                        </div>
                    </div>
                </div>
            </div>
            <script src="/assets/js/sections/stats/stats.js"></script>

        <?php elseif ($current_section === 'online-booking' && !empty($premium_statuses['online_booking'])): ?>
            <!-- Section Réservations en ligne -->
            <div class="settings-section">
                <h2><i class="fas fa-calendar-check"></i> Réservations en ligne</h2>
                <p class="section-description">Permettez à vos clients de réserver une table directement depuis votre site vitrine.</p>
                <div class="premium-section-cta" style="text-align: center; padding: 40px 20px;">
                    <i class="fas fa-calendar-check" style="font-size: 3rem; color: var(--color-primary); margin-bottom: 16px; display: block;"></i>
                    <p style="margin-bottom: 20px; color: var(--color-text-light);">Gérez vos réservations, configurez les créneaux horaires et suivez les demandes de vos clients.</p>
                    <a href="?page=reservations" class="btn primary" style="display: inline-flex; align-items: center; gap: 8px;">
                        <i class="fas fa-external-link-alt"></i> Accéder aux réservations
                    </a>
                </div>
            </div>

        <?php elseif ($current_section === 'delivery' && !empty($premium_statuses['delivery_integration'])): ?>
            <!-- Section Intégration livraison -->
            <div class="settings-section">
                <h2><i class="fas fa-motorcycle"></i> Intégration livraison</h2>
                <p class="section-description">Connectez Uber Eats, Deliveroo et autres plateformes de livraison à votre site.</p>
                <div class="premium-section-placeholder">
                    <i class="fas fa-hard-hat"></i>
                    <p>Cette fonctionnalité est en cours de développement.</p>
                </div>
            </div>

        <?php endif; ?>
    </div>
</div>

<?php 
// Afficher les messages de succès stockés dans sessionStorage (pour la résiliation AJAX)
if ($current_section === 'subscriptions'): 
?>
<script>
// Afficher le message de succès stocké dans sessionStorage
document.addEventListener('DOMContentLoaded', function() {
    const successMessage = sessionStorage.getItem('subscriptionSuccessMessage');
    if (successMessage) {
        // Créer et afficher le message de succès comme sur le reste du site
        const messageDiv = document.createElement('p');
        messageDiv.className = 'message-success';
        messageDiv.textContent = successMessage;
        
        // Insérer au début du contenu principal
        const settingsContent = document.querySelector('.settings-content');
        if (settingsContent) {
            settingsContent.insertBefore(messageDiv, settingsContent.firstChild);
            
            // Scroller vers le message avec un petit délai
            setTimeout(() => {
                messageDiv.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }, 50);
        }
        
        // Supprimer le message du sessionStorage
        sessionStorage.removeItem('subscriptionSuccessMessage');
        
        // Auto-supprimer le message après 5 secondes
        setTimeout(() => {
            if (messageDiv.parentNode) {
                messageDiv.parentNode.removeChild(messageDiv);
            }
        }, 5000);
    }
    
    // Afficher le message d'erreur stocké dans sessionStorage
    const errorMessage = sessionStorage.getItem('subscriptionErrorMessage');
    if (errorMessage) {
        // Créer et afficher le message d'erreur comme sur le reste du site
        const messageDiv = document.createElement('p');
        messageDiv.className = 'message-error';
        messageDiv.textContent = errorMessage;
        
        // Insérer au début du contenu principal
        const settingsContent = document.querySelector('.settings-content');
        if (settingsContent) {
            settingsContent.insertBefore(messageDiv, settingsContent.firstChild);
            
            // Scroller vers le message avec un petit délai
            setTimeout(() => {
                messageDiv.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }, 50);
        }
        
        // Supprimer le message du sessionStorage
        sessionStorage.removeItem('subscriptionErrorMessage');
        
        // Auto-supprimer le message après 5 secondes
        setTimeout(() => {
            if (messageDiv.parentNode) {
                messageDiv.parentNode.removeChild(messageDiv);
            }
        }, 5000);
    }
});
</script>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Profil
    var profileForm = document.querySelector('#profile-form form[action*="update-profile"]');
    if (profileForm) {
        profileForm.addEventListener('submit', function(e) {
            e.preventDefault();
            ajaxSubmit(this, '?page=settings&action=update-profile');
        });
    }

    // Mot de passe
    var passwordForm = document.getElementById('password-change-form');
    if (passwordForm) {
        passwordForm.addEventListener('submit', function(e) {
            e.preventDefault();
            ajaxSubmit(this, '?page=settings&action=change-password', {
                onSuccess: function() {
                    passwordForm.reset();
                }
            });
        });
    }

    // Google Reviews config forms
    var grForms = document.querySelectorAll('form[action*="update-google-reviews"]');
    grForms.forEach(function(form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            ajaxSubmit(this, '?page=settings&action=update-google-reviews');
        });
    });
});
</script>

<?php require __DIR__ . '/../partials/footer.php'; ?>
