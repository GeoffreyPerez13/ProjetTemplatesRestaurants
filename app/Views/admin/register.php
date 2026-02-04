<?php
$title = "Création de compte";
$scripts = ["js/sections/register/auth.js"];

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

    <!-- Message de succès après création de compte -->
    <?php if (!empty($success_message)): ?>
        <p class="message-success"><?= htmlspecialchars($success_message) ?></p>
    <?php endif; ?>

    <!-- Affichage des erreurs si le formulaire a échoué -->
    <?php if (!empty($error_message)): ?>
        <p class="message-error"><?= htmlspecialchars($error_message) ?></p>
    <?php endif; ?>

    <!-- Vérifie que l’invitation est valide avant d’afficher le formulaire -->
    <?php if (!empty($invitation)): ?>
        <form method="post">
            <!-- CSRF token pour protéger le formulaire -->
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
            
            <!-- Token d'invitation -->
            <input type="hidden" name="invitation_token" value="<?= htmlspecialchars($token ?? '') ?>">

            <input type="text" name="username" placeholder="Nom d'utilisateur" required>
            
            <div class="password-input-group">
                <div class="password-input-wrapper">
                    <input type="password" name="password" placeholder="Mot de passe" required minlength="8" class="password-input-with-toggle">
                </div>
                <button type="button" class="password-toggle-btn" aria-label="Afficher le mot de passe">
                    <i class="fa-regular fa-eye"></i>
                </button>
            </div>
            
            <div class="password-input-group">
                <div class="password-input-wrapper">
                    <input type="password" name="confirm_password" placeholder="Confirmer le mot de passe" required minlength="8" class="password-input-with-toggle">
                </div>
                <button type="button" class="password-toggle-btn" aria-label="Afficher le mot de passe">
                    <i class="fa-regular fa-eye"></i>
                </button>
            </div>
            
            <button type="submit" class="btn">Créer le compte</button>
        </form>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>