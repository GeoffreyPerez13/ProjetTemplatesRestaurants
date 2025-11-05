<?php
$title = "Modifier le logo";
require __DIR__ . '/../partials/header.php';
?>

<!-- Bouton retour vers le dashboard -->
<a class="btn-back" href="?page=dashboard">← Retour au dashboard</a>

<!-- Affichage des messages de succès ou d'erreur -->
<?php if (!empty($message)): ?>
    <p class="success"><?= htmlspecialchars($message) ?></p>
<?php endif; ?>
<?php if (!empty($error)): ?>
    <p class="error"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<!-- Formulaire pour uploader un nouveau logo -->
<form method="post" enctype="multipart/form-data">
    <h2>Changer le logo</h2>
    
    <input type="file" name="logo" accept="image/*" required>
    <button type="submit" class="btn">Uploader</button>
</form>

<?php require __DIR__ . '/../partials/footer.php'; ?>
