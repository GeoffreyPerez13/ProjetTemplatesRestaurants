<?php
$title = "Réinitialisation du mot de passe";

$scripts = [
    "js/sections/reset-password/reset-password.js"
];

require __DIR__ . '/../partials/header.php';
?>
<div class="reset-password-container">
    <h2>Réinitialisation du mot de passe</h2>

    <!-- Messages flash -->
    <?php if (!empty($success_message)): ?>
        <div class="message-success"><?= htmlspecialchars($success_message) ?></div>
    <?php endif; ?>

    <?php if (!empty($error_message)): ?>
        <div class="message-error"><?= htmlspecialchars($error_message) ?></div>
    <?php endif; ?>

    <?php if (empty($token)): ?>
        <!-- Étape 1 : Demande d’email -->
        <form method="post" action="?page=reset-password">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
            <p>Entrez votre adresse email pour recevoir un lien de réinitialisation.</p>
            <input type="email" name="email" placeholder="Votre adresse email" required>
            <button type="submit" class="btn">Envoyer le lien</button>
        </form>
    <?php else: ?>
        <!-- Étape 2 : Saisie du nouveau mot de passe -->
        <form method="post" action="?page=reset-password" id="reset-password-form">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
            <input type="hidden" name="token" value="<?= htmlspecialchars($token ?? '') ?>">

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

                <!-- Indicateur de force -->
                <div class="password-strength-meter">
                    <div class="strength-bar" id="strength-bar"></div>
                </div>
                <div class="strength-text" id="strength-text">Force : faible</div>

                <!-- Liste des exigences -->
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
                            placeholder="Retapez votre nouveau mot de passe" required minlength="8">
                    </div>
                    <button type="button" class="password-toggle-btn" data-target="confirm_password"
                        aria-label="Afficher le mot de passe">
                        <i class="fa-regular fa-eye"></i>
                    </button>
                </div>

                <!-- Messages de correspondance -->
                <div class="password-match-error" id="password-match-error" style="display: none;">
                    <i class="fa-solid fa-exclamation-circle"></i>
                    <span>Les mots de passe ne correspondent pas</span>
                </div>
                <div class="password-match-success" id="password-match-success" style="display: none;">
                    <i class="fa-solid fa-check-circle"></i>
                    <span>Les mots de passe correspondent</span>
                </div>
            </div>

            <button type="submit" class="btn">Réinitialiser le mot de passe</button>
        </form>
    <?php endif; ?>

    <div class="back-to-login">
        <a href="?page=login">Retour à la page de connexion</a>
    </div>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>