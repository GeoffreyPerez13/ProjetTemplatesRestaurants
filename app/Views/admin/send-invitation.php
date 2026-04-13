<?php
$title = "Envoyer une invitation";
$scripts = ["js/sections/send-invitation/send-invitation.js"];

require __DIR__ . '/../partials/header.php';
?>

<!-- Script pour passer les paramètres au JavaScript -->
<script>
    window.scrollParams = {
        anchor: '<?= htmlspecialchars($anchor ?? '') ?>',
        scrollDelay: <?= (int)($scroll_delay ?? 1500) ?>
    };
</script>

<a class="btn-back" href="?page=dashboard">Retour</a>

<!-- Affichage des messages -->
<?php if (!empty($success_message)): ?>
    <p class="message-success"><?= htmlspecialchars($success_message) ?></p>
<?php endif; ?>

<?php if (!empty($error_message)): ?>
    <p class="message-error"><?= htmlspecialchars($error_message) ?></p>
<?php endif; ?>

<div class="send-invitation-container">
    <!-- En-tête de la page -->
    <div class="send-invitation-header">
        <h1><i class="fas fa-paper-plane"></i> Inviter un restaurant</h1>
        <p class="subtitle">Envoyez une invitation à un restaurateur pour qu'il rejoigne votre plateforme et gère sa carte en ligne.</p>
    </div>

    <!-- Carte du formulaire -->
    <div class="invitation-card">
        <form method="post" class="invitation-form">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">

            <!-- Email du restaurant -->
            <div class="invitation-form-group">
                <label for="email">
                    <i class="fas fa-envelope"></i> Adresse email du restaurant
                </label>
                <div class="input-with-icon">
                    <i class="fas fa-at"></i>
                    <input type="email"
                        id="email"
                        name="email"
                        placeholder="exemple@restaurant.fr"
                        required
                        value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>
                <div class="help-text">
                    <i class="fas fa-info-circle"></i> L'invitation sera envoyée à cette adresse email.
                </div>
            </div>

            <!-- Nom du restaurant -->
            <div class="invitation-form-group">
                <label for="restaurant_name">
                    <i class="fas fa-utensils"></i> Nom du restaurant
                </label>
                <div class="input-with-icon">
                    <i class="fas fa-store"></i>
                    <input type="text"
                        id="restaurant_name"
                        name="restaurant_name"
                        placeholder="Le nom de votre restaurant"
                        required
                        value="<?= htmlspecialchars($_POST['restaurant_name'] ?? '') ?>">
                </div>
                <div class="help-text">
                    <i class="fas fa-info-circle"></i> Ce nom apparaîtra sur la carte en ligne.
                </div>
            </div>

            <!-- Informations sur l'invitation -->
            <div class="invitation-features">
                <h3><i class="fas fa-star"></i> Ce que le restaurateur recevra :</h3>
                <ul>
                    <li><i class="fas fa-check"></i> Un lien d'inscription sécurisé</li>
                    <li><i class="fas fa-check"></i> Un compte administrateur personnel</li>
                    <li><i class="fas fa-check"></i> La possibilité de gérer sa carte en ligne</li>
                    <li><i class="fas fa-check"></i> Un accès à son tableau de bord</li>
                </ul>
            </div>

            <!-- Bouton d'envoi -->
            <button type="submit" class="send-invitation-btn">
                <i class="fas fa-paper-plane"></i> Envoyer l'invitation
            </button>

            <!-- Informations supplémentaires -->
            <div class="form-footer">
                <p><i class="fas fa-clock"></i> Le lien d'invitation est valable 24 heures.</p>
                <p><i class="fas fa-shield-alt"></i> L'inscription est sécurisée et confidentielle.</p>
            </div>
        </form>
    </div>

    <!-- Prévisualisation (optionnelle) -->
    <?php if (!empty($_POST['email']) && !empty($_POST['restaurant_name']) && empty($error)): ?>
        <div class="invitation-preview">
            <h4><i class="fas fa-eye"></i> Aperçu de l'invitation :</h4>
            <div class="preview-content">
                <p><strong>À :</strong> <?= htmlspecialchars($_POST['email']) ?></p>
                <p><strong>Restaurant :</strong> <?= htmlspecialchars($_POST['restaurant_name']) ?></p>
                <p><strong>Message :</strong> "Bonjour, vous êtes invité à créer un compte pour gérer la carte en ligne de votre restaurant..."</p>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>