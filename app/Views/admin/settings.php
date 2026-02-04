<?php
$title = $title ?? "Paramètres";
$scripts = ["js/sections/settings/settings.js"];

require __DIR__ . '/../partials/header.php';

// Formatage des dates
$created_at = !empty($user['created_at']) ? (new DateTime($user['created_at']))->format('d/m/Y') : 'N/A';
$last_card_update = !empty($user['last_card_update']) ? (new DateTime($user['last_card_update']))->format('d/m/Y') : 'Jamais modifiée';
?>

<a class="btn-back" href="?page=dashboard">Retour au dashboard</a>

<div class="settings-container" data-csrf-token="<?= htmlspecialchars($csrf_token ?? '') ?>">
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
            <!-- NOUVEAU LIEN POUR OPTIONS -->
            <li>
                <a href="?page=settings&section=options"
                    class="<?= $current_section === 'options' ? 'active' : '' ?>">
                    Options
                </a>
            </li>
        </ul>
    </div>

    <div class="settings-content">
        <!-- Messages flash -->
        <?php if (!empty($success_message)): ?>
            <div class="message-success"><?= htmlspecialchars($success_message) ?></div>
        <?php endif; ?>

        <?php if (!empty($error_message)): ?>
            <div class="message-error"><?= htmlspecialchars($error_message) ?></div>
        <?php endif; ?>

        <h1><?= htmlspecialchars($title) ?></h1>

        <?php if ($current_section === 'profile'): ?>
            <!-- Section Profil -->
            <div class="settings-section" id="profile-form">
                <h2>Modifier le profil</h2>
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
            <!-- Section Mot de passe -->
            <div class="settings-section" id="password-form">
                <h2>Changer le mot de passe</h2>
                <form method="POST" action="?page=settings&action=change-password">
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">

                    <div class="form-group">
                        <label for="current_password">Mot de passe actuel</label>
                        <input type="password" id="current_password" name="current_password" required>
                    </div>

                    <div class="form-group">
                        <label for="new_password">Nouveau mot de passe</label>
                        <input type="password" id="new_password" name="new_password" required>
                        <small class="form-hint">Minimum 8 caractères</small>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirmer le nouveau mot de passe</label>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                    </div>

                    <button type="submit" class="btn">Changer le mot de passe</button>

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
            <!-- NOUVELLE SECTION OPTIONS -->
            <div class="settings-section" id="options-form">
                <h2>Options du compte</h2>
                <p class="section-description">Configurez les paramètres de votre compte et de votre site.</p>
                
                <div class="options-list">
                    <div class="option-item">
                        <div class="option-header">
                            <span class="option-label">Afficher le site en ligne</span>
                            <div class="option-tooltip">
                                <span class="tooltip-icon" title="Plus d'infos">i</span>
                                <div class="tooltip-content">
                                    <p>Activez cette option pour rendre votre site visible au public. Si désactivé, votre site sera en maintenance.</p>
                                </div>
                            </div>
                        </div>
                        <div class="option-buttons">
                            <button type="button" class="option-btn option-active" data-option="site_online" data-value="1">Actif</button>
                            <button type="button" class="option-btn" data-option="site_online" data-value="0">Non actif</button>
                        </div>
                        <div class="option-description">
                            <small>Contrôle la visibilité publique de votre site.</small>
                        </div>
                    </div>
                    
                    <div class="option-item">
                        <div class="option-header">
                            <span class="option-label">Rappel mail pour actualisation</span>
                            <div class="option-tooltip">
                                <span class="tooltip-icon" title="Plus d'infos">i</span>
                                <div class="tooltip-content">
                                    <p>Recevez un email de rappel tous les mois pour mettre à jour votre carte. Assurez-vous que vos plats et prix sont à jour.</p>
                                </div>
                            </div>
                        </div>
                        <div class="option-buttons">
                            <button type="button" class="option-btn option-active" data-option="mail_reminder" data-value="1">Actif</button>
                            <button type="button" class="option-btn" data-option="mail_reminder" data-value="0">Non actif</button>
                        </div>
                        <div class="option-description">
                            <small>Recevez des rappels mensuels pour mettre à jour votre carte.</small>
                        </div>
                    </div>
                    
                    <div class="option-item">
                        <div class="option-header">
                            <span class="option-label">Notifications par email</span>
                            <div class="option-tooltip">
                                <span class="tooltip-icon" title="Plus d'infos">i</span>
                                <div class="tooltip-content">
                                    <p>Recevez des notifications par email pour les mises à jour importantes et les activités sur votre compte.</p>
                                </div>
                            </div>
                        </div>
                        <div class="option-buttons">
                            <button type="button" class="option-btn option-active" data-option="email_notifications" data-value="1">Actif</button>
                            <button type="button" class="option-btn" data-option="email_notifications" data-value="0">Non actif</button>
                        </div>
                        <div class="option-description">
                            <small>Activez les notifications importantes par email.</small>
                        </div>
                    </div>
                </div>
                
                <div class="options-actions">
                    <button type="button" class="btn" id="save-all-options">Enregistrer toutes les options</button>
                    <button type="button" class="btn secondary" id="reset-options">Restaurer les valeurs par défaut</button>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>