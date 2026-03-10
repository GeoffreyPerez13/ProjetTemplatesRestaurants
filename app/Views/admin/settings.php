<?php
$title = $title ?? "Paramètres";
$scripts = [
    "js/sections/settings/settings.js",
    "js/sections/settings/premium-cart.js",
    "js/effects/accordion.js",
    "js/sections/settings/closure-dates.js",
    "js/sections/settings/subscriptions.js"
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
        <!-- Affichage des messages -->
        <?php if (!empty($success_message)): ?>
            <p class="message-success"><?= htmlspecialchars($success_message) ?></p>
        <?php endif; ?>

        <?php if (!empty($error_message)): ?>
            <p class="message-error"><?= htmlspecialchars($error_message) ?></p>
        <?php endif; ?>

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
                        <p><a href="?page=reset-password">Mot de passe oublié ? Réinitialiser le mot de passe</a></p>
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
                        <h3><i class="fas fa-cog"></i> Options du compte</h3>
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
                <div class="accordion-section">
                    <div class="accordion-header">
                        <h3><i class="fas fa-calendar-times"></i> Fermetures Exceptionnelles</h3>
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
                                <button type="button" class="btn small" id="clear-all-closure-dates">
                                    <i class="fas fa-trash"></i> Tout effacer
                                </button>
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
                                <h4>Dates de fermeture programmées (<span id="selected-count">0</span>)</h4>
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
                        <h3><i class="fas fa-bolt"></i> Options premium à la carte</h3>
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
                                    $isSelectable       = !$isSuperAdmin && $hasActiveSub; // Sélectionnable si on a un abonnement basique
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
                                    <span>Total : <strong id="cart-total">0€</strong>/mois</span>
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
                                    <span>Total : <strong id="cart-total">0€</strong>/mois</span>
                                </div>
                            </div>
                            <?php endif; ?>
                        </form>
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
                        <h3><i class="fas fa-calculator"></i> Total de votre abonnement</h3>
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
                        <h3><i class="fas fa-shopping-cart"></i> Panier d'abonnement</h3>
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
                        </div>
                        
                        <div class="combined-cart-actions">
                            <button type="submit" class="btn btn-primary combined-checkout-btn" id="combined-checkout-btn" disabled>
                                <i class="fab fa-stripe-s"></i> Payer et activer
                            </button>
                            <button type="button" class="btn btn-outline" id="clear-combined-cart">
                                <i class="fas fa-trash"></i> Vider le panier
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
                
                <style>
                    /* Force dark mode styles for subscriptions - ULTRA SPECIFIC */
                    .subscription-bulk-actions {
                        background: #1f2937 !important;
                        border: 1px solid #374151 !important;
                    }
                    
                    .subscription-bulk-actions * {
                        color: #e5e7eb !important;
                    }
                    
                    /* Améliorer le bouton Tout sélectionner */
                    .subscription-bulk-actions .checkbox-label {
                        background: var(--color-primary) !important;
                        border: 1px solid var(--color-primary) !important;
                        border-radius: 6px !important;
                        padding: 8px 16px !important;
                        display: inline-flex !important;
                        align-items: center !important;
                        gap: 8px !important;
                        cursor: pointer !important;
                        transition: all 0.2s ease !important;
                    }
                    
                    .subscription-bulk-actions .checkbox-label:hover {
                        background: color-mix(in srgb, var(--color-primary) 80%, black) !important;
                        border-color: color-mix(in srgb, var(--color-primary) 80%, black) !important;
                    }
                    
                    .subscription-bulk-actions .checkbox-label input[type="checkbox"] {
                        width: 18px !important;
                        height: 18px !important;
                        accent-color: #b45309 !important;
                    }
                    
                    .subscription-bulk-actions .checkbox-label .checkmark {
                        display: none !important;
                    }
                    
                    .subscription-bulk-actions .checkbox-label span {
                        color: #fbbf24 !important;
                        font-weight: 500 !important;
                        font-size: 14px !important;
                    }
                    
                    /* Mode dark spécifique pour le bouton */
                    body.dark-mode .subscription-bulk-actions .checkbox-label {
                        background: #2c1810 !important;
                        border: 1px solid #b45309 !important;
                    }
                    
                    body.dark-mode .subscription-bulk-actions .checkbox-label:hover {
                        background: #3d2418 !important;
                        border-color: #d97706 !important;
                    }
                    
                    body.dark-mode .subscription-bulk-actions .checkbox-label span {
                        color: #fbbf24 !important;
                    }
                    
                    /* Styles pour le mode light */
                    .subscription-bulk-actions {
                        background: #ffffff !important;
                        border: 1px solid #d1d5db !important;
                        border-radius: 6px !important;
                    }
                    
                    .subscription-bulk-actions * {
                        color: #1f2937 !important;
                    }
                    
                    .subscriptions-table {
                        background: #ffffff !important;
                        border: 1px solid #d1d5db !important;
                        border-radius: 6px !important;
                    }
                    
                    .subscriptions-table thead {
                        background: #f9fafb !important;
                        border-bottom: 1px solid #d1d5db !important;
                    }
                    
                    .subscriptions-table tbody tr {
                        background: #ffffff !important;
                        border-bottom: 1px solid #f1f5f9 !important;
                    }
                    
                    .subscriptions-table tbody tr:hover {
                        background: #f8fafc !important;
                    }
                    
                    .subscriptions-table td {
                        background: transparent !important;
                        color: #1f2937 !important;
                        border-bottom-color: #f1f5f9 !important;
                    }
                    
                    .subscriptions-table th {
                        background: transparent !important;
                        color: #1f2937 !important;
                        border-bottom-color: #d1d5db !important;
                        font-weight: 600 !important;
                    }
                    
                    /* Target specific elements in dark mode */
                    body.dark-mode .subscription-bulk-actions {
                        background: #1f2937 !important;
                        border-color: #374151 !important;
                    }
                    
                    body.dark-mode .subscriptions-table {
                        background: #1f2937 !important;
                        border: 1px solid #374151 !important;
                    }
                    
                    body.dark-mode .subscriptions-table td {
                        color: #e5e7eb !important;
                        border-color: #374151 !important;
                    }
                    
                    body.dark-mode .subscriptions-table th {
                        color: #f3f4f6 !important;
                        border-color: #374151 !important;
                    }
                    
                    /* Force accordion content */
                    .accordion-content .subscription-bulk-actions {
                        background: #1f2937 !important;
                        border: 1px solid #374151 !important;
                    }
                    
                    .accordion-content .subscriptions-table {
                        background: #1f2937 !important;
                        border: 1px solid #374151 !important;
                    }
                    
                    .accordion-content .subscriptions-table td {
                        color: #e5e7eb !important;
                        border-color: #374151 !important;
                    }
                    
                    .accordion-content .subscriptions-table th {
                        color: #f3f4f6 !important;
                        border-color: #374151 !important;
                    }
                </style>
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
                <div class="accordion-section premium-total-accordion">
                    <div class="accordion-header">
                        <h3><i class="fas fa-calculator"></i> Total de votre abonnement</h3>
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
                
                <div class="accordion-section subscription-management-accordion">
                    <div class="accordion-header">
                        <h3><i class="fas fa-sliders-h"></i> Gérer mes abonnements</h3>
                        <button type="button" class="accordion-toggle" data-target="subscription-management-content">
                            <i class="fas fa-chevron-down"></i>
                        </button>
                    </div>
                    <div id="subscription-management-content" class="accordion-content expanded">
                        
                        <!-- Actions groupées -->
                        <div class="subscription-bulk-actions">
                            <div class="bulk-select-all">
                                <label class="checkbox-label">
                                    <input type="checkbox" id="select-all-subs" class="select-all-checkbox">
                                    <span class="checkmark"></span>
                                    Tout sélectionner
                                </label>
                            </div>
                            <div class="bulk-actions-buttons">
                                <button type="button" id="bulk-cancel-subs" class="btn btn-danger" disabled>
                                    <i class="fas fa-trash-alt"></i> Résilier la sélection
                                </button>
                            </div>
                        </div>

                        <!-- Tableau des abonnements -->
                        <table class="subscriptions-table">
                            <thead>
                                <tr>
                                    <th class="col-checkbox">
                                        <!-- Vide - espace pour l'alignement -->
                                    </th>
                                    <th class="col-type">Type</th>
                                    <th class="col-name">Nom</th>
                                    <th class="col-price">Prix</th>
                                    <th class="col-status">Statut</th>
                                    <th class="col-actions">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Abonnement Basique -->
                                <tr class="subscription-row basique-row">
                                    <td class="col-checkbox">
                                        <input type="checkbox" class="sub-checkbox" data-type="basique" data-name="Abonnement Basique" id="sub-basique">
                                        <label for="sub-basique" class="sub-checkbox-label"></label>
                                    </td>
                                    <td class="col-type">
                                        <span class="subscription-badge basique-badge">
                                            <i class="fas fa-store"></i>
                                            Basique
                                        </span>
                                    </td>
                                    <td class="col-name">Abonnement Basique MenuMiam</td>
                                    <td class="col-price">9€/mois</td>
                                    <td class="col-status">
                                        <span class="status-badge active">
                                            <i class="fas fa-check-circle"></i> Actif
                                        </span>
                                    </td>
                                    <td class="col-actions">
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
                                    </td>
                                </tr>

                                <!-- Options Premium -->
                                <?php foreach ($activePremiumFeatures as $featureKey => $_ignore): ?>
                                    <?php $featureDef = $availableFeatures[$featureKey] ?? null; if (!$featureDef) continue; ?>
                                <tr class="subscription-row premium-row">
                                    <td class="col-checkbox">
                                        <input type="checkbox" class="sub-checkbox" data-type="premium" data-name="<?= htmlspecialchars($featureDef['name']) ?>" id="sub-<?= htmlspecialchars($featureKey) ?>">
                                        <label for="sub-<?= htmlspecialchars($featureKey) ?>" class="sub-checkbox-label"></label>
                                    </td>
                                    <td class="col-type">
                                        <span class="subscription-badge premium-badge">
                                            <i class="fas fa-star"></i>
                                            Premium
                                        </span>
                                    </td>
                                    <td class="col-name"><?= htmlspecialchars($featureDef['name']) ?></td>
                                    <td class="col-price">+<?= (int)$featureDef['price_monthly'] ?>€/mois</td>
                                    <td class="col-status">
                                        <span class="status-badge active">
                                            <i class="fas fa-check-circle"></i> Actif
                                        </span>
                                    </td>
                                    <td class="col-actions">
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
                                    </td>
                                </tr>
                                <?php endforeach; ?>

                                <?php if (empty($activePremiumFeatures)): ?>
                                <tr class="no-premium-row">
                                    <td colspan="6" class="no-premium-cell">
                                        <div class="no-premium-message">
                                            <i class="fas fa-info-circle"></i>
                                            <span>Aucune option premium active pour le moment.</span>
                                        </div>
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
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
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-success alert-dismissible fade show';
        alertDiv.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 9999; max-width: 400px;';
        alertDiv.innerHTML = `
            <i class="fas fa-check-circle"></i> ${successMessage}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(alertDiv);
        
        // Supprimer le message du sessionStorage
        sessionStorage.removeItem('subscriptionSuccessMessage');
        
        // Auto-supprimer l'alerte après 5 secondes
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.parentNode.removeChild(alertDiv);
            }
        }, 5000);
        
        // Fermeture manuelle
        alertDiv.querySelector('.btn-close').addEventListener('click', function() {
            if (alertDiv.parentNode) {
                alertDiv.parentNode.removeChild(alertDiv);
            }
        });
    }
});
</script>
<?php endif; ?>

<?php require __DIR__ . '/../partials/footer.php'; ?>