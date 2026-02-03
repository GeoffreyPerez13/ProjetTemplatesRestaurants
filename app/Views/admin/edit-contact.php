<?php
// Titre de la page pour le header
$title = "Modifier le contact";
require __DIR__ . '/../partials/header.php';
?>

<!-- Script pour passer les paramètres au JavaScript -->
<script>
    // Variables disponibles pour le JavaScript
    window.scrollParams = {
        anchor: '<?= htmlspecialchars($anchor ?? '') ?>',
        scrollDelay: <?= (int)($scroll_delay ?? 3500) ?>
    };
</script>

<!-- Bouton retour vers le dashboard -->
<a class="btn-back" href="?page=dashboard">Retour au dashboard</a>

<!-- Affichage des messages - Même structure que edit-card.php -->
<?php if (!empty($success_message)): ?>
    <p class="message-success"><?= htmlspecialchars($success_message) ?></p>
<?php endif; ?>

<?php if (!empty($error_message)): ?>
    <p class="message-error"><?= htmlspecialchars($error_message) ?></p>
<?php endif; ?>

<!-- Formulaire pour modifier les informations de contact -->
<form method="post">
    <h2>Modifier le contact</h2>

    <input type="text" name="telephone" value="<?= htmlspecialchars($contact['telephone']) ?>" placeholder="Téléphone" required>
    <input type="email" name="email" value="<?= htmlspecialchars($contact['email']) ?>" placeholder="Email" required>
    <input type="text" name="adresse" value="<?= htmlspecialchars($contact['adresse']) ?>" placeholder="Adresse" required>
    <textarea name="horaires" placeholder="Horaires"><?= htmlspecialchars($contact['horaires']) ?></textarea>
    <button type="submit" class="btn">Mettre à jour</button>
</form>

<?php require __DIR__ . '/../partials/footer.php'; ?>