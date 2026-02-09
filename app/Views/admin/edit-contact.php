<?php
// Titre de la page pour le header
$title = "Modifier le contact";
$scripts = [
    "js/effects/scroll-buttons.js",
    "js/sections/edit-contact/edit-contact.js"
];
require __DIR__ . '/../partials/header.php';
?>

<!-- Boutons de navigation haut/bas -->
<div class="page-navigation-buttons">
    <button type="button" class="btn-navigation scroll-to-bottom" title="Aller en bas de la page">
        <i class="fas fa-arrow-down"></i>
    </button>
    <button type="button" class="btn-navigation scroll-to-top" title="Aller en haut de la page">
        <i class="fas fa-arrow-up"></i>
    </button>
</div>

<!-- Script pour passer les paramètres au JavaScript -->
<script>
    // Variables disponibles pour le JavaScript
    window.scrollParams = {
        anchor: '<?= htmlspecialchars($anchor ?? '') ?>',
        scrollDelay: <?= (int)($scroll_delay ?? 1500) ?>
    };
</script>

<!-- Bouton retour vers le dashboard -->
<a class="btn-back" href="?page=dashboard">Retour au dashboard</a>

<!-- Affichage des messages - Même structure que edit-card.php -->
<?php if (!empty($success_message)): ?>
    <p class="message-success" id="edit-contact-form"><?= htmlspecialchars($success_message) ?></p>
<?php endif; ?>

<?php if (!empty($error_message)): ?>
    <p class="message-error" id="edit-contact-form"><?= htmlspecialchars($error_message) ?></p>
<?php endif; ?>

<!-- Formulaire pour modifier les informations de contact -->
<form method="post" class="edit-contact-form">
    <h2><i class="fas fa-address-book"></i> Modifier les informations de contact</h2>

    <input type="hidden" name="anchor" value="edit-contact-form">

    <div class="form-group">
        <label for="telephone">
            <i class="fas fa-phone"></i> Téléphone
        </label>
        <input type="text" name="telephone" id="telephone" 
               value="<?= htmlspecialchars($contact['telephone']) ?>" 
               placeholder="Ex: 01 23 45 67 89" required>
    </div>

    <div class="form-group">
        <label for="email">
            <i class="fas fa-envelope"></i> Email
        </label>
        <input type="email" name="email" id="email" 
               value="<?= htmlspecialchars($contact['email']) ?>" 
               placeholder="Ex: contact@restaurant.com" required>
    </div>

    <div class="form-group">
        <label for="adresse">
            <i class="fas fa-map-marker-alt"></i> Adresse
        </label>
        <input type="text" name="adresse" id="adresse" 
               value="<?= htmlspecialchars($contact['adresse']) ?>" 
               placeholder="Ex: 123 Rue du Restaurant, 75000 Paris" required>
    </div>

    <div class="form-group">
        <label for="horaires">
            <i class="fas fa-clock"></i> Horaires
        </label>
        <textarea name="horaires" id="horaires" 
                  placeholder="Ex: Lundi - Vendredi: 12h-14h30 / 19h-23h&#10;Samedi: 12h-15h / 19h-00h&#10;Dimanche: 12h-16h"><?= htmlspecialchars($contact['horaires']) ?></textarea>
        <small class="form-hint">Vous pouvez utiliser plusieurs lignes pour organiser les horaires.</small>
    </div>

    <button type="submit" class="btn primary">
        <i class="fas fa-save"></i> Mettre à jour les informations
    </button>
</form>

<?php require __DIR__ . '/../partials/footer.php'; ?>