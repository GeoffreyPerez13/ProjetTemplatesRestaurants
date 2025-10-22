<?php 
$title = "Connexion Admin";
require __DIR__ . '/../partials/header.php'; 
?>
<div class="login-container">
    <h2>Connexion administrateur</h2>
    <?php if (!empty($error)): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <form method="post">
        <input type="text" name="username" placeholder="Identifiant" required>
        <input type="password" name="password" placeholder="Mot de passe" required>
        <button type="submit" class="btn">Se connecter</button>
    </form>
</div>
<?php require __DIR__ . '/../partials/footer.php'; ?>
