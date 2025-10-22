<?php
$title = "Modifier le contact";
require __DIR__ . '/../partials/header.php';
?>

<a class="btn-back" href="?page=dashboard">← Retour au dashboard</a>

<h2>Modifier le contact</h2>

<?php if (!empty($message)): ?>
    <p class="success"><?= htmlspecialchars($message) ?></p>
<?php endif; ?>

<form method="post">
    <input type="text" name="telephone" value="<?= htmlspecialchars($contact['telephone']) ?>" placeholder="Téléphone" required>
    <input type="email" name="email" value="<?= htmlspecialchars($contact['email']) ?>" placeholder="Email" required>
    <input type="text" name="adresse" value="<?= htmlspecialchars($contact['adresse']) ?>" placeholder="Adresse" required>
    <textarea name="horaires" placeholder="Horaires"><?= htmlspecialchars($contact['horaires']) ?></textarea>
    <button type="submit" class="btn">Mettre à jour</button>
</form>

<?php require __DIR__ . '/../partials/footer.php'; ?>
