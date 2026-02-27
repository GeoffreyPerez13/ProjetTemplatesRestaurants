<?php
$title = $title ?? "Paramètres";
$scripts = [
    "js/sections/settings/settings.js"
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
                    <a href="?page=settings&section=account" class="<?= $current_section === 'account' ? 'active' : '' ?>">
                        Informations du compte
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
                        <span>Fonctionnalités Premium</span>
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
                <a href="?page=settings&section=account"
                    class="<?= $current_section === 'account' ? 'active' : '' ?>">
                    Informations du compte
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
                    Fonctionnalités Premium
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
            <div class="settings-section" id="options-form">
                <h2>Options du compte</h2>
                <p class="section-description">Configurez les paramètres de votre compte et de votre site.</p>

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
        <?php elseif ($current_section === 'premium'): ?>
            <!-- Section Fonctionnalités Premium -->
            <div class="settings-section">
                <link rel="stylesheet" href="assets/css/admin/sections/settings/premium.css">
                <script src="assets/js/admin/premium.js"></script>
                <h2>Fonctionnalités Premium</h2>
                <p class="section-description">Débloquez des fonctionnalités avancées pour votre restaurant.</p>

                <?php
                require_once __DIR__ . '/../../Models/PremiumFeature.php';
                $premiumFeature = new PremiumFeature($pdo);
                $availableFeatures = $premiumFeature->getAvailableFeatures();
                $userFeatures = $premiumFeature->getAllFeatures($_SESSION['admin_id']);
                $userFeaturesMap = array_column($userFeatures, 'is_active', 'feature_name');
                ?>

                <div class="premium-features-grid">
                    <?php foreach ($availableFeatures as $featureKey => $feature): ?>
                        <div class="premium-feature-card <?= $userFeaturesMap[$featureKey] ?? 0 ? 'active' : '' ?>">
                            <div class="feature-header">
                                <div class="feature-icon">
                                    <i class="fas <?= $feature['icon'] ?>"></i>
                                </div>
                                <div class="feature-info">
                                    <h3><?= htmlspecialchars($feature['name']) ?></h3>
                                    <p><?= htmlspecialchars($feature['description']) ?></p>
                                </div>
                            </div>
                            <div class="feature-status">
                                <?php if ($userFeaturesMap[$featureKey] ?? 0): ?>
                                    <span class="status-badge active">
                                        <i class="fas fa-check-circle"></i>
                                        Activé
                                    </span>
                                <?php else: ?>
                                    <span class="status-badge inactive">
                                        <i class="fas fa-lock"></i>
                                        Non disponible
                                    </span>
                                <?php endif; ?>
                            </div>
                            <div class="feature-actions">
                                <?php if ($featureKey === 'google_reviews'): ?>
                                    <?php if ($userFeaturesMap[$featureKey] ?? 0): ?>
                                        <button type="button" class="btn btn-sm configure-google-reviews">
                                            <i class="fas fa-cog"></i>
                                            Configurer
                                        </button>
                                        <button type="button" class="btn danger btn-sm toggle-premium" 
                                                data-feature="<?= $featureKey ?>">
                                            <i class="fas fa-times"></i>
                                            Désactiver
                                        </button>
                                    <?php else: ?>
                                        <button type="button" class="btn premium-btn toggle-premium" 
                                                data-feature="<?= $featureKey ?>">
                                            <i class="fas fa-crown"></i>
                                            Activer Premium
                                        </button>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <?php if ($userFeaturesMap[$featureKey] ?? 0): ?>
                                        <button type="button" class="btn danger btn-sm toggle-premium" 
                                                data-feature="<?= $featureKey ?>">
                                            <i class="fas fa-times"></i>
                                            Désactiver
                                        </button>
                                    <?php else: ?>
                                        <button type="button" class="btn secondary btn-sm" disabled>
                                            <i class="fas fa-lock"></i>
                                            Bientôt disponible
                                        </button>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

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
                            <li>Les fonctionnalités Premium nécessitent un abonnement MenuMiam Premium</li>
                            <li>Contactez-nous à <a href="mailto:premium@menumiam.fr">premium@menumiam.fr</a> pour souscrire</li>
                            <li>Pour les tests : les super-admins peuvent activer/désactiver les fonctionnalités</li>
                        </ul>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>