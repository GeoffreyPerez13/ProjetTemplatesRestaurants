<?php
$title = "Connexion Admin";
$scripts = ["js/sections/login/login.js"];

require __DIR__ . '/../partials/header.php';
?>

<div class="login-container">
    <h2>Connexion administrateur</h2>

    <!-- Message de succès après création de compte -->
    <?php if (!empty($success_message)): ?>
        <p class="message-success"><?= htmlspecialchars($success_message) ?></p>
    <?php endif; ?>

    <!-- Message d'erreur si identifiant ou mot de passe incorrect -->
    <?php if (!empty($error_message)): ?>
        <p class="message-error"><?= htmlspecialchars($error_message) ?></p>
    <?php endif; ?>

    <!-- Formulaire de connexion -->
    <form method="post">
        <input type="text" name="username" placeholder="Identifiant" required>
        <input type="password" name="password" placeholder="Mot de passe" required>
        <button type="submit" class="btn">Se connecter</button>
    </form>

    <!-- Lien vers la réinitialisation de mot de passe -->
    <div class="password-reset">
        <a href="?page=reset-password">Mot de passe oublié ?</a>
    </div>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>