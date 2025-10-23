<?php
$title = "Envoyer une invitation";
require __DIR__ . '/../partials/header.php';
?>
<div class="container">
    <h2>Envoyer une invitation</h2>

    <!-- Affichage des erreurs -->
    <?php if (!empty($error)): ?>
        <div class="error">
            <?php
            error_log("[DEBUG] Affichage erreur: " . $error);
            echo $error;
            ?>
        </div>
    <?php endif; ?>

    <!-- Affichage d’un message de succès -->
    <?php if (!empty($success)): ?>
        <div class="success">
            <?php
            error_log("[DEBUG] Affichage succès: " . $success);
            echo $success;
            ?>
        </div>
    <?php endif; ?>

    <!-- Formulaire pour envoyer une invitation à un restaurant -->
    <form method="post">
        <!-- CSRF token pour sécuriser le formulaire -->
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">

        <input type="email" name="email" placeholder="Email du restaurant" required>
        <input type="text" name="restaurant_name" placeholder="Nom du restaurant" required>
        <button type="submit">Envoyer l'invitation</button>
    </form>
</div>
<?php require __DIR__ . '/../partials/footer.php'; ?>
