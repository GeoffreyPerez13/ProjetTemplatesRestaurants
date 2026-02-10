<?php
$title = "Création de compte";
$scripts = ["js/sections/register/register.js"];

require __DIR__ . '/../partials/header.php';

// Récupérer directement les messages de session
$success_message = $_SESSION['success_message'] ?? null;
$error_message = $_SESSION['error_message'] ?? null;

// Les effacer après les avoir récupérés
if (isset($_SESSION['success_message'])) unset($_SESSION['success_message']);
if (isset($_SESSION['error_message'])) unset($_SESSION['error_message']);
?>

<div class="register-container">
    <h2>Création de votre compte restaurant</h2>

    <!-- Affichage des messages -->
    <?php if (!empty($success_message)): ?>
        <p class="message-success"><?= htmlspecialchars($success_message) ?></p>
    <?php endif; ?>

    <?php if (!empty($error_message)): ?>
        <p class="message-error"><?= htmlspecialchars($error_message) ?></p>
    <?php endif; ?>

    <!-- Vérifie que l'invitation est valide avant d'afficher le formulaire -->
    <?php if (!empty($invitation)): ?>
        <div class="invitation-info">
            <p><strong>Restaurant :</strong> <?= htmlspecialchars($invitation->restaurant_name) ?></p>
            <p><strong>Email :</strong> <?= htmlspecialchars($invitation->email) ?></p>
        </div>

        <form method="post" id="register-form">
            <!-- CSRF token pour protéger le formulaire -->
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">

            <!-- Token d'invitation -->
            <input type="hidden" name="invitation_token" value="<?= htmlspecialchars($token ?? '') ?>">

            <div class="form-group">
                <label for="username">Nom d'utilisateur *</label>
                <input type="text" id="username" name="username" placeholder="Choisissez un nom d'utilisateur" required>
            </div>

            <div class="form-group">
                <label for="password">Mot de passe *</label>
                <div class="password-input-group">
                    <div class="password-input-wrapper">
                        <input type="password" id="password" name="password" placeholder="Créez un mot de passe sécurisé" required minlength="8" class="password-input-with-toggle">
                    </div>
                    <button type="button" class="password-toggle-btn" aria-label="Afficher le mot de passe">
                        <i class="fa-regular fa-eye"></i>
                    </button>
                </div>

                <!-- Indicateurs de force du mot de passe -->
                <div class="password-strength-meter">
                    <div class="strength-bar"></div>
                </div>

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
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirmer le mot de passe *</label>
                <div class="password-input-group">
                    <div class="password-input-wrapper">
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Retapez votre mot de passe" required minlength="8" class="password-input-with-toggle">
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

            <button type="submit" class="btn btn-primary" id="submit-btn">
                <i class="fa-solid fa-user-plus"></i> Créer le compte
            </button>

            <div class="form-footer">
                <p><small>* Champs obligatoires</small></p>
                <p><small>En créant un compte, vous acceptez nos conditions d'utilisation</small></p>
            </div>
        </form>
    <?php else: ?>
        <div class="alert alert-warning">
            <i class="fa-solid fa-exclamation-triangle"></i>
            <p>Cette invitation n'est pas valide ou a expiré.</p>
        </div>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>