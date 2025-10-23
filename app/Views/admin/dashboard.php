<?php
// Définition du titre de la page pour le header
$title = "Tableau de bord";

// Inclusion du header commun
require __DIR__ . '/../partials/header.php';
?>
<div class="dashboard">
    <h2>Tableau de bord</h2>

    <!-- Affichage du nom de l'admin connecté -->
    <p>Bienvenue <?= htmlspecialchars($admin_name) ?> !</p>

    <!-- Liens rapides pour modifier la carte, contact et logo -->
    <a href="?page=edit-carte" class="btn">Modifier la carte</a>
    <a href="?page=edit-contact" class="btn">Modifier le contact</a>
    <a href="?page=edit-logo" class="btn">Modifier le logo</a>

    <!-- Option uniquement visible pour le SUPER_ADMIN -->
    <?php if ($role === 'SUPER_ADMIN'): ?>
        <a href="?page=send-invitation" class="btn">Envoyer un lien de création de compte</a>
    <?php endif; ?>

    <!-- Bouton de déconnexion -->
    <a href="?page=logout" class="btn danger">Se déconnecter</a>
</div>

<?php
// Inclusion du footer commun
require __DIR__ . '/../partials/footer.php';
?>
