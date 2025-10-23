<?php
$title = "Tableau de bord";
require __DIR__ . '/../partials/header.php';
?>
<div class="dashboard">
    <h2>Tableau de bord</h2>
    <p>Bienvenue <?= htmlspecialchars($admin_name) ?> !</p>

    <a href="?page=edit-carte" class="btn">Modifier la carte</a>
    <a href="?page=edit-contact" class="btn">Modifier le contact</a>
    <a href="?page=edit-logo" class="btn">Modifier le logo</a>
    <?php if ($role === 'SUPER_ADMIN'): ?>
        <a href="?page=send-invitation" class="btn">Envoyer un lien de création de compte</a>
    <?php endif; ?>
    <a href="?page=logout" class="btn danger">Se déconnecter</a>
</div>
<?php require __DIR__ . '/../partials/footer.php'; ?>