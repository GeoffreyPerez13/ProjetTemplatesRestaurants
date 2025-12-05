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

    <!-- Regroupe les 3 premiers boutons -->
    <div class="dashboard-top-buttons">
        <a href="?page=edit-card" class="btn">Modifier la carte</a>
        <a href="?page=edit-contact" class="btn">Modifier le contact</a>
        <a href="?page=edit-logo" class="btn">Modifier le logo</a>
        <a href="?page=view-card" class="btn success">Aperçu de la carte</a>
    </div>

    <!-- Zone du bas pour les boutons d’action -->
    <div class="dashboard-bottom">
        <?php if ($role === 'SUPER_ADMIN'): ?>
            <a href="?page=send-invitation" class="btn left">Envoyer un lien de création de compte</a>
        <?php endif; ?>

        <a href="?page=logout" class="btn danger right">Se déconnecter</a>
    </div>
</div>

<?php
// Inclusion du footer commun
require __DIR__ . '/../partials/footer.php';
?>
