<?php
$title = "Modifier le logo";
$scripts = [
    "js/sections/edit-logo/edit-logo.js",
    "js/effects/scroll-buttons.js",
    "js/effects/accordion.js"
];

require __DIR__ . '/../partials/header.php';
?>

<!-- Script pour passer les paramètres au JavaScript -->
<script>
    window.scrollParams = {
        anchor: '<?= htmlspecialchars($anchor ?? '') ?>',
        scrollDelay: <?= (int)($scroll_delay ?? 1500) ?>,
        currentLogoUrl: '<?= !empty($current_logo['public_url']) ? htmlspecialchars($current_logo['public_url']) : '' ?>',
        hasLogo: <?= !empty($current_logo) ? 'true' : 'false' ?>,
        closeAccordion: '<?= htmlspecialchars($closeAccordion ?? '') ?>',
        openAccordion: '<?= htmlspecialchars($openAccordion ?? '') ?>'
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

<!-- Boutons de contrôle généraux pour tous les accordéons -->
<div class="global-accordion-controls">
    <button type="button" id="expand-all-accordions" class="btn">
        <i class="fas fa-expand-alt"></i> Tout ouvrir
    </button>
    <button type="button" id="collapse-all-accordions" class="btn">
        <i class="fas fa-compress-alt"></i> Tout fermer
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
        <!-- Section : Logo actuel (ACCORDÉON) -->
        <div class="accordion-section current-logo-accordion" id="current-logo">
            <div class="accordion-header">
                <h2><i class="fas fa-image"></i> Logo actuel</h2>
                <button type="button" class="accordion-toggle" data-target="current-logo-content">
                    <!-- Ici c'est expanded donc fa-chevron-up -->
                    <i class="fas fa-chevron-up"></i>
                </button>
            </div>

            <div id="current-logo-content" class="accordion-content expanded">
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
                            <input type="hidden" name="anchor" value="current-logo">
                            <button type="submit" name="delete_logo" class="btn danger"
                                data-filename="<?= htmlspecialchars($current_logo['filename'] ?? 'ce logo') ?>">
                                Supprimer ce logo
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="logo-separator">
            <span>OU</span>
        </div>
    <?php endif; ?>

    <div class="accordion-section upload-logo-accordion" id="upload-logo">
        <div class="accordion-header">
            <h2><i class="fas fa-upload"></i> <?= !empty($current_logo) ? 'Changer le logo' : 'Ajouter un logo' ?></h2>
            <button type="button" class="accordion-toggle" data-target="upload-logo-content">
                <!-- Changez cette ligne pour mettre fa-chevron-down quand collapsed -->
                <i class="fas fa-chevron-<?= empty($current_logo) ? 'down' : 'up' ?>"></i>
            </button>
        </div>

        <div id="upload-logo-content" class="accordion-content <?= empty($current_logo) ? 'expanded' : 'collapsed' ?>">
            <form method="post" enctype="multipart/form-data" class="upload-logo-form" id="upload-logo-form">
                <input type="hidden" name="anchor" value="upload-logo">

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
</div>

<!-- Lightbox pour l'agrandissement du logo -->
<div id="logo-lightbox" class="logo-lightbox">
    <div class="lightbox-content">
        <button class="lightbox-close" id="lightboxClose">&times;</button>
        <img id="lightbox-image" src="" alt="Logo agrandi">
        <div class="lightbox-caption" id="lightbox-caption"></div>
    </div>
</div>

<!-- Script pour corriger définitivement l'état des accordéons -->
<script>
// Exécuter après que tous les scripts soient chargés
window.addEventListener('load', function() {
    <?php if (!empty($current_logo)): ?>
        // Si un logo existe, forcer l'état des accordéons
        setTimeout(function() {
            const currentLogoContent = document.getElementById('current-logo-content');
            const uploadLogoContent = document.getElementById('upload-logo-content');
            
            // Forcer l'affichage du logo actuel
            if (currentLogoContent) {
                currentLogoContent.style.display = 'block';
                currentLogoContent.style.maxHeight = 'none';
                currentLogoContent.style.opacity = '1';
                currentLogoContent.style.visibility = 'visible';
                currentLogoContent.classList.add('expanded');
                currentLogoContent.classList.remove('collapsed');
            }
            
            // Forcer la fermeture de l'upload
            if (uploadLogoContent) {
                uploadLogoContent.style.display = 'none';
                uploadLogoContent.style.maxHeight = '0';
                uploadLogoContent.style.opacity = '0';
                uploadLogoContent.style.visibility = 'hidden';
                uploadLogoContent.classList.add('collapsed');
                uploadLogoContent.classList.remove('expanded');
            }
            
            console.log("Accordéons corrigés par PHP");
        }, 500);
    <?php endif; ?>
});
</script>

<?php require __DIR__ . '/../partials/footer.php'; ?>