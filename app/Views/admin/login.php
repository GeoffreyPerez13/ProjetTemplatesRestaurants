<?php
$title = "Connexion Admin";
$scripts = ["js/sections/login/login.js"];

// Transférer les messages vers sessionStorage pour affichage en toast
if (!empty($success_message)) {
    ?>
    <script>
    sessionStorage.setItem('pendingToast', JSON.stringify({
        message: <?= json_encode($success_message) ?>,
        type: 'success'
    }));
    </script>
    <?php
}
if (!empty($error_message)) {
    ?>
    <script>
    sessionStorage.setItem('pendingToast', JSON.stringify({
        message: <?= json_encode($error_message) ?>,
        type: 'error'
    }));
    </script>
    <?php
}

require __DIR__ . '/../partials/header.php';
?>

<div class="login-container">
    <h2>Connexion administrateur</h2>

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

    <!-- Liens supplementaires -->
    <div class="login-extra-links" style="text-align: center; margin-top: 16px;">
        <p style="margin: 0 0 6px 0;"><a href="?page=landing&from=register" target="_blank" style="color: var(--color-primary); font-size: 0.9rem; text-decoration: none;">Pas encore de compte ? Créer un compte</a></p>
        <p style="margin: 0;"><a href="?page=landing" target="_blank" style="color: var(--color-text-muted); font-size: 0.85rem; text-decoration: none;"><i class="fas fa-arrow-left"></i> Retour à la page d'accueil</a></p>
    </div>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>