<?php
$title = "Tableau de bord";

// Formater la date si elle existe
$formatted_date = null;
if (!empty($last_updated)) {
    $date = new DateTime($last_updated);
    $formatted_date = $date->format('d/m/Y à H:i');
}

require __DIR__ . '/../partials/header.php';
?>
<div class="dashboard">
    <h2>Tableau de bord</h2>

    <!-- Messages flash -->
    <?php if (!empty($success_message)): ?>
        <div class="message-success"><?= htmlspecialchars($success_message) ?></div>
    <?php endif; ?>
    
    <?php if (!empty($error_message)): ?>
        <div class="message-error"><?= htmlspecialchars($error_message) ?></div>
    <?php endif; ?>

    <!-- Affichage du message de bienvenue personnalisé -->
    <div class="welcome-message">
        <p>Bienvenue <strong><?= htmlspecialchars($username) ?></strong>.</p>
        <p>Vous gérez le restaurant : <strong><?= htmlspecialchars($restaurant_name) ?></strong>.</p>

        <?php if (!empty($last_updated)): ?>
            <p class="last-updated">Dernière modification de la carte le : <em><strong><?= htmlspecialchars($formatted_date) ?></strong></em>.</p>
        <?php else: ?>
            <p class="last-updated">La carte n'a pas encore été modifiée.</p>
        <?php endif; ?>
    </div>

    <div class="dashboard-top-buttons">
        <a href="?page=edit-card" class="btn">Modifier la carte</a>
        <a href="?page=edit-contact" class="btn">Modifier le contact</a>
        <a href="?page=edit-logo" class="btn">Modifier le logo</a>
        <a href="?page=view-card" class="btn success">Aperçu de la carte</a>
        <a href="?page=settings" class="btn" title="Paramètres">Paramètres</a>
    </div>

    <!-- Zone du bas pour les boutons d'action -->
    <div class="dashboard-bottom">
        <?php if ($role === 'SUPER_ADMIN'): ?>
            <a href="?page=send-invitation" class="btn left">Envoyer un lien de création de compte</a>
        <?php endif; ?>

        <a href="?page=logout" class="btn danger right">Se déconnecter</a>
    </div>
</div>

<?php
require __DIR__ . '/../partials/footer.php';