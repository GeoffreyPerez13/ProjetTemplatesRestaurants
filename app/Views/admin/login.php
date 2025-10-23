<?php
$title = "Connexion Admin";
require __DIR__ . '/../partials/header.php';
?>
<div class="login-container">
    <h2>Connexion administrateur</h2>

    <?php if (!empty($success)): ?>
        <p class="success"><?= htmlspecialchars($success) ?></p>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="post">
        <input type="text" name="username" placeholder="Identifiant" required>
        <input type="password" name="password" placeholder="Mot de passe" required>
        <button type="submit" class="btn">Se connecter</button>
    </form>
    <div class="password-reset">
        <a href="?page=reset-password">Mot de passe oublié ?</a>
    </div>
</div>
<?php require __DIR__ . '/../partials/footer.php'; ?>