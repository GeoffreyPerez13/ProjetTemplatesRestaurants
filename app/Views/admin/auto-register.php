<?php
$title = "Créer mon compte";
$scripts = ["js/sections/register/register.js"];
require __DIR__ . '/../partials/header.php';
?>

<div class="register-container auto-register">
    <div class="register-header">
        <a href="?page=landing" class="back-link">
            <i class="fas fa-arrow-left"></i> Retour
        </a>
        <div class="register-logo">
            <i class="fas fa-utensils"></i>
            <span>MenuMiam</span>
        </div>
    </div>

    <h2>Créez votre compte restaurant</h2>
    <p class="register-subtitle">
        Votre site vitrine en ligne en quelques minutes. Abonnement Basique à 9€/mois.
    </p>

    <div class="plan-badge">
        <i class="fas fa-check-circle"></i>
        <span>Abonnement <strong>Basique</strong> — Site vitrine complet, carte modifiable, templates, SEO</span>
    </div>

    <?php if (!empty($success_message)): ?>
        <p class="message-success"><?= $success_message ?></p>
    <?php endif; ?>

    <?php if (!empty($error_message)): ?>
        <p class="message-error"><?= $error_message ?></p>
    <?php endif; ?>

    <form method="post" action="?page=auto-register" id="register-form">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">

        <div class="form-group">
            <label for="restaurant_name">Nom de votre restaurant *</label>
            <input type="text" id="restaurant_name" name="restaurant_name" 
                   placeholder="ex: Le Bistrot Parisien"
                   value="<?= htmlspecialchars($form_data['restaurant_name'] ?? '') ?>" 
                   required minlength="2">
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="username">Nom d'utilisateur *</label>
                <input type="text" id="username" name="username" 
                       placeholder="ex: lebistrot"
                       value="<?= htmlspecialchars($form_data['username'] ?? '') ?>" 
                       required minlength="3">
            </div>

            <div class="form-group">
                <label for="email">Adresse email *</label>
                <input type="email" id="email" name="email" 
                       placeholder="ex: contact@monrestaurant.fr"
                       value="<?= htmlspecialchars($form_data['email'] ?? '') ?>" 
                       required>
            </div>
        </div>

        <div class="form-group">
            <label for="password">Mot de passe *</label>
            <div class="password-input-group">
                <div class="password-input-wrapper">
                    <input type="password" id="password" name="password" 
                           placeholder="Créez un mot de passe sécurisé" 
                           required minlength="8"
                           class="password-input-with-toggle">
                </div>
                <button type="button" class="password-toggle-btn" aria-label="Afficher le mot de passe">
                    <i class="fa-regular fa-eye"></i>
                </button>
            </div>

            <div class="password-strength-meter">
                <div class="strength-bar"></div>
            </div>

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
        </div>

        <div class="form-group">
            <label for="confirm_password">Confirmer le mot de passe *</label>
            <div class="password-input-group">
                <div class="password-input-wrapper">
                    <input type="password" id="confirm_password" name="confirm_password" 
                           placeholder="Retapez votre mot de passe" 
                           required minlength="8"
                           class="password-input-with-toggle">
                </div>
                <button type="button" class="password-toggle-btn" aria-label="Afficher le mot de passe">
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
        </div>

        <button type="submit" class="btn register-btn" id="submit-btn">
            <i class="fas fa-rocket"></i>
            Créer mon compte
        </button>
    </form>

    <div class="register-footer-links">
        <p>Déjà un compte ? <a href="?page=login">Se connecter</a></p>
    </div>
</div>

<style>
.auto-register {
    max-width: 560px;
}

.register-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 24px;
}

.back-link {
    color: var(--color-text-light);
    text-decoration: none;
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    gap: 6px;
    transition: color 0.2s;
}

.back-link:hover {
    color: var(--color-primary);
}

.register-logo {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 1.2rem;
    font-weight: 800;
    color: var(--color-text);
}

.register-logo i {
    color: var(--color-primary);
}

.register-subtitle {
    color: var(--color-text-light);
    margin-bottom: 24px;
    font-size: 0.95rem;
}

.plan-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    background: linear-gradient(135deg, rgba(212, 168, 83, 0.1), rgba(180, 83, 9, 0.05));
    border: 1px solid #d4a853;
    border-radius: 8px;
    color: var(--color-text);
    font-size: 0.9rem;
    margin-bottom: 8px;
}

.plan-badge i {
    color: #15803d;
}

.plan-note {
    font-size: 0.82rem;
    color: var(--color-text-muted);
    margin-bottom: 24px;
}

.plan-note i {
    color: var(--color-primary);
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}

.register-btn {
    width: 100%;
    padding: 14px;
    background: linear-gradient(135deg, var(--color-primary), var(--color-primary-dark));
    color: white;
    border: none;
    border-radius: 10px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: all 0.3s;
    margin-top: 8px;
}

.register-btn:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(180, 83, 9, 0.3);
}

.register-btn:disabled {
    opacity: 0.55;
    cursor: not-allowed;
    background: var(--color-text-muted);
}

.register-footer-links {
    text-align: center;
    margin-top: 20px;
    font-size: 0.9rem;
    color: var(--color-text-light);
}

.register-footer-links a {
    color: var(--color-primary);
    text-decoration: none;
    font-weight: 500;
}

.register-footer-links a:hover {
    text-decoration: underline;
}

@media (max-width: 600px) {
    .form-row {
        grid-template-columns: 1fr;
    }
}
</style>


<?php require __DIR__ . '/../partials/footer.php'; ?>
