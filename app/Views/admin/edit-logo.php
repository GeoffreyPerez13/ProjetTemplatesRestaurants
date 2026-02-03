<?php
$title = "Modifier le logo";
$scripts = [
    "js/sections/edit-logo/edit-logo.js",
    "js/effects/scroll-buttons.js"
];

require __DIR__ . '/../partials/header.php';
?>

<!-- Script pour passer les paramètres au JavaScript -->
<script>
    window.scrollParams = {
        anchor: '<?= htmlspecialchars($anchor ?? '') ?>',
        scrollDelay: <?= (int)($scroll_delay ?? 3500) ?>,
        currentLogoUrl: '<?= !empty($current_logo['public_url']) ? htmlspecialchars($current_logo['public_url']) : '' ?>',
        hasLogo: <?= !empty($current_logo) ? 'true' : 'false' ?>
    };
</script>

<a class="btn-back" href="?page=dashboard">Retour au dashboard</a>

<!-- Boutons de navigation haut/bas (alignés à droite) -->
<div class="page-navigation-buttons">
    <button type="button" class="btn-navigation scroll-to-bottom" title="Aller en bas de la page">
        <i class="fas fa-arrow-down"></i>
    </button>
    <button type="button" class="btn-navigation scroll-to-top" title="Aller en haut de la page">
        <i class="fas fa-arrow-up"></i>
    </button>
</div>

<!-- Affichage des messages -->
<?php if (!empty($success_message)): ?>
    <p class="message-success"><?= htmlspecialchars($success_message) ?></p>
<?php endif; ?>

<?php if (!empty($error_message)): ?>
    <p class="message-error"><?= htmlspecialchars($error_message) ?></p>
<?php endif; ?>

<div class="edit-logo-container">
    <?php if (!empty($current_logo)): ?>
        <!-- Section : Logo actuel -->
        <div class="current-logo-section" id="current-logo-section">
            <h2><i class="fas fa-image"></i> Logo actuel</h2>

            <div class="logo-display">
                <div class="logo-image-container">
                    <img src="<?= htmlspecialchars($current_logo['public_url']) ?>"
                        alt="Logo actuel"
                        class="current-logo-image"
                        id="current-logo-image">
                    <div class="logo-overlay">
                        <button type="button" class="btn-icon enlarge-logo" title="Agrandir">
                            <i class="fas fa-search-plus"></i>
                        </button>
                    </div>
                </div>

                <div class="logo-info">
                    <p><strong>Nom du fichier :</strong> <?= htmlspecialchars($current_logo['filename']) ?></p>
                    <p><strong>Date d'upload :</strong> <?= htmlspecialchars($current_logo['upload_date']) ?></p>

                    <form method="post" class="delete-logo-form">
                        <button type="submit" name="delete_logo" class="btn danger"
                            data-filename="<?= htmlspecialchars($current_logo['filename'] ?? 'ce logo') ?>">
                            Supprimer ce logo
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="logo-separator">
            <span>OU</span>
        </div>
    <?php endif; ?>

    <!-- Formulaire pour uploader un nouveau logo -->
    <div class="upload-logo-section" id="logo-form">
        <h2><i class="fas fa-upload"></i> <?= !empty($current_logo) ? 'Changer le logo' : 'Ajouter un logo' ?></h2>

        <form method="post" enctype="multipart/form-data" class="upload-logo-form" id="upload-logo-form">
            <div class="upload-area" id="uploadArea">
                <div class="upload-icon">
                    <i class="fas fa-cloud-upload-alt"></i>
                </div>

                <div class="upload-text">
                    <p class="upload-title">Glissez-déposez votre logo ici</p>
                    <p class="upload-subtitle">ou cliquez pour sélectionner un fichier</p>
                </div>

                <input type="file" name="logo" id="logo-input" accept="image/*"
                    class="file-input-hidden" required>

                <button type="button" class="btn select-file-btn" id="selectFileBtn">
                    Choisir un fichier
                </button>
            </div>

            <!-- La prévisualisation sera ajoutée ici par JavaScript -->

            <div class="form-info">
                <p><i class="fas fa-info-circle"></i> <strong>Formats acceptés :</strong> JPG, PNG, GIF, WebP, SVG</p>
                <p><i class="fas fa-info-circle"></i> <strong>Taille maximale :</strong> 5 Mo</p>
                <p><i class="fas fa-info-circle"></i> <strong>Recommandé :</strong> Format carré (1:1) avec fond transparent</p>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn success" id="uploadBtn" disabled>
                    <i class="fas fa-upload"></i> <?= !empty($current_logo) ? 'Remplacer le logo' : 'Uploader le logo' ?>
                </button>

                <button type="button" class="btn" id="resetBtn">
                    <i class="fas fa-redo"></i> Annuler la sélection
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Lightbox pour l'agrandissement du logo -->
<div id="logo-lightbox" class="logo-lightbox">
    <div class="lightbox-content">
        <button class="lightbox-close" id="lightboxClose">&times;</button>
        <img id="lightbox-image" src="" alt="Logo agrandi">
        <div class="lightbox-caption" id="lightbox-caption"></div>
    </div>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>