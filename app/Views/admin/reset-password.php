<?php
$title = "Réinitialisation du mot de passe";
require __DIR__ . '/../partials/header.php';
?>
<div class="reset-password-container">
    <h2>Réinitialisation du mot de passe</h2>

    <?php if (!empty($error)): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <p class="success"><?= htmlspecialchars($success) ?></p>
    <?php endif; ?>

    <?php if (empty($token)): ?>
        <!-- Étape 1 : Demande d'email -->
        <form method="post" action="?page=reset-password">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
            <p>Entrez votre adresse email pour recevoir un lien de réinitialisation.</p>
            <input type="email" name="email" placeholder="Votre adresse email" required>
            <button type="submit" class="btn">Envoyer le lien</button>
        </form>
    <?php else: ?>
        <!-- Étape 2 : Nouveau mot de passe -->
        <form method="post" action="?page=reset-password">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
            <input type="hidden" name="token" value="<?= htmlspecialchars($token ?? '') ?>">
            <input type="password" name="new_password" placeholder="Nouveau mot de passe" required>
            <input type="password" name="confirm_password" placeholder="Confirmer le mot de passe" required>
            <button type="submit" class="btn">Réinitialiser le mot de passe</button>
        </form>
    <?php endif; ?>

    <div class="back-to-login">
        <a href="?page=login">Retour à la page de connexion</a>
    </div>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>