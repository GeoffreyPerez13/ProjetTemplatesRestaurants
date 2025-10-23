<?php
$title = "Création de compte";
require __DIR__ . '/../partials/header.php';
?>

<div class="register-container">
    <h2>Création de votre compte restaurant</h2>

    <?php if (!empty($error)): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <?php if (!empty($invitation)): ?>
        <form method="post">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
            <input type="text" name="username" placeholder="Nom d'utilisateur" required>
            <input type="password" name="password" placeholder="Mot de passe" required minlength="8">
            <input type="password" name="confirm_password" placeholder="Confirmer le mot de passe" required minlength="8">
            <button type="submit" class="btn">Créer le compte</button>
        </form>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>