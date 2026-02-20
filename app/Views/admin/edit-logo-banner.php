<?php
$title = "Modifier les médias";
$scripts = [
    "js/sections/edit-logo-banner/edit-logo-banner.js",
    "js/effects/scroll-buttons.js",
    "js/effects/accordion.js",
    "js/effects/lightbox.js"
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
        currentBannerUrl: '<?= !empty($current_banner['public_url']) ? htmlspecialchars($current_banner['public_url']) : '' ?>',
        hasBanner: <?= !empty($current_banner) ? 'true' : 'false' ?>,
        bannerText: '<?= !empty($current_banner['text']) ? htmlspecialchars($current_banner['text']) : '' ?>',
        closeAccordion: '<?= htmlspecialchars($closeAccordion ?? '') ?>',
        openAccordion: '<?= htmlspecialchars($openAccordion ?? '') ?>'
    };
</script>

<a class="btn-back" href="?page=dashboard">Retour au dashboard</a>

<!-- Boutons de navigation haut/bas -->
<div class="page-navigation-buttons">
    <button type="button" class="btn-navigation scroll-to-bottom" title="Aller en bas de la page"><i class="fas fa-arrow-down"></i></button>
    <button type="button" class="btn-navigation scroll-to-top" title="Aller en haut de la page"><i class="fas fa-arrow-up"></i></button>
</div>

<!-- Boutons de contrôle généraux pour tous les accordéons -->
<div class="global-accordion-controls">
    <button type="button" id="expand-all-accordions" class="btn"><i class="fas fa-expand-alt"></i> Tout ouvrir</button>
    <button type="button" id="collapse-all-accordions" class="btn"><i class="fas fa-compress-alt"></i> Tout fermer</button>
</div>

<!-- Affichage des messages -->
<?php if (!empty($success_message)): ?>
    <p class="message-success"><?= htmlspecialchars($success_message) ?></p>
<?php endif; ?>
<?php if (!empty($error_message)): ?>
    <p class="message-error"><?= htmlspecialchars($error_message) ?></p>
<?php endif; ?>

<div class="edit-logo-container">
    <!-- ==================== SECTION LOGO ==================== -->
    <?php if (!empty($current_logo)): ?>
        <div class="accordion-section current-logo-accordion" id="current-logo">
            <div class="accordion-header">
                <h2><i class="fas fa-image"></i> Logo actuel</h2>
                <button type="button" class="accordion-toggle" data-target="current-logo-content"><i class="fas fa-chevron-up"></i></button>
            </div>
            <div id="current-logo-content" class="accordion-content expanded prevent-auto-close">
                <div class="logo-display">
                    <div class="logo-image-container">
                        <img src="<?= htmlspecialchars($current_logo['public_url']) ?>" alt="Logo actuel" class="current-logo-image" id="current-logo-image">
                        <div class="logo-overlay"><button type="button" class="btn-icon enlarge-logo" title="Agrandir"><i class="fas fa-search-plus"></i></button></div>
                    </div>
                    <div class="logo-info">
                        <p><strong>Nom du fichier :</strong> <?= htmlspecialchars($current_logo['filename']) ?></p>
                        <p><strong>Date d'upload :</strong> <?= htmlspecialchars($current_logo['upload_date']) ?></p>
                        <form method="post" action="?page=edit-logo-banner&action=deleteLogo" class="delete-logo-form">
                            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                            <input type="hidden" name="anchor" value="current-logo">
                            <button type="submit" name="delete_logo" class="btn danger" data-filename="<?= htmlspecialchars($current_logo['filename'] ?? 'ce logo') ?>">Supprimer ce logo</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="logo-separator"><span>OU</span></div>
    <?php endif; ?>

    <div class="accordion-section upload-logo-accordion" id="upload-logo">
        <div class="accordion-header">
            <h2><i class="fas fa-upload"></i> <?= !empty($current_logo) ? 'Changer le logo' : 'Ajouter un logo' ?></h2>
            <button type="button" class="accordion-toggle" data-target="upload-logo-content"><i class="fas fa-chevron-<?= empty($current_logo) ? 'down' : 'up' ?>"></i></button>
        </div>
        <div id="upload-logo-content" class="accordion-content <?= empty($current_logo) ? 'expanded' : 'collapsed' ?>">
            <form method="post" enctype="multipart/form-data" action="?page=edit-logo-banner&action=uploadLogo" class="upload-logo-form" id="upload-logo-form">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                <input type="hidden" name="anchor" value="upload-logo">
                <div class="upload-area" id="uploadLogoArea">
                    <div class="upload-icon"><i class="fas fa-cloud-upload-alt"></i></div>
                    <div class="upload-text">
                        <p class="upload-title">Glissez-déposez votre logo ici</p>
                        <p class="upload-subtitle">ou cliquez pour sélectionner un fichier</p>
                    </div>
                    <input type="file" name="logo" id="logo-input" accept="image/*" class="file-input-hidden" required>
                    <button type="button" class="btn select-file-btn" id="selectLogoBtn">Choisir un fichier</button>
                </div>
                <div id="logo-preview-container" class="file-info-container" style="display: none;"></div>
                <div class="form-info">
                    <p><i class="fas fa-info-circle"></i> <strong>Formats acceptés :</strong> JPG, PNG, GIF, WebP, SVG</p>
                    <p><i class="fas fa-info-circle"></i> <strong>Taille maximale :</strong> 5 Mo</p>
                    <p><i class="fas fa-info-circle"></i> <strong>Recommandé :</strong> Format carré (1:1) avec fond transparent</p>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn success" id="uploadLogoBtn" disabled><i class="fas fa-upload"></i> <?= !empty($current_logo) ? 'Remplacer le logo' : 'Uploader le logo' ?></button>
                    <button type="button" class="btn" id="resetLogoBtn"><i class="fas fa-redo"></i> Annuler la sélection</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ==================== SECTION BANNIÈRE ==================== -->
    <?php if (!empty($current_banner)): ?>
        <div class="accordion-section current-banner-accordion" id="current-banner">
            <div class="accordion-header">
                <h2><i class="fas fa-image"></i> Bannière actuelle</h2>
                <button type="button" class="accordion-toggle" data-target="current-banner-content"><i class="fas fa-chevron-up"></i></button>
            </div>
            <div id="current-banner-content" class="accordion-content expanded prevent-auto-close">
                <div class="banner-display">
                    <div class="banner-image-container">
                        <img src="<?= htmlspecialchars($current_banner['public_url']) ?>" alt="Bannière actuelle" class="current-banner-image" id="current-banner-image">
                        <div class="banner-overlay"><button type="button" class="btn-icon enlarge-banner" title="Agrandir"><i class="fas fa-search-plus"></i></button></div>
                    </div>
                    <div class="banner-info">
                        <p><strong>Nom du fichier :</strong> <?= htmlspecialchars($current_banner['filename']) ?></p>
                        <p><strong>Date d'upload :</strong> <?= htmlspecialchars($current_banner['upload_date']) ?></p>
                        <form method="post" action="?page=edit-logo-banner&action=deleteBanner" class="delete-banner-form">
                            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                            <input type="hidden" name="anchor" value="current-banner">
                            <button type="submit" name="delete_banner" class="btn danger" data-filename="<?= htmlspecialchars($current_banner['filename'] ?? 'cette bannière') ?>">Supprimer cette bannière</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- ==================== SECTION TEXTE DE LA BANNIÈRE ==================== -->
        <div class="accordion-section banner-text-accordion" id="banner-text">
            <div class="accordion-header">
                <h2><i class="fas fa-comment-dots"></i> Texte de la bannière</h2>
                <button type="button" class="accordion-toggle" data-target="banner-text-content">
                    <i class="fas fa-chevron-<?= !empty($current_banner['text']) ? 'up' : 'down' ?>"></i>
                </button>
            </div>
            <div id="banner-text-content" class="accordion-content <?= !empty($current_banner['text']) ? 'expanded' : 'collapsed' ?>">
                <form method="post" action="?page=edit-logo-banner&action=updateBannerText" class="banner-text-form">
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                    <input type="hidden" name="anchor" value="banner-text">

                    <div class="form-group">
                        <label for="banner_text">Saisissez le texte à afficher sur la bannière :</label>
                        <textarea name="banner_text" id="banner_text" rows="3" class="form-control" placeholder="Ex : Bienvenue chez nous !"><?= htmlspecialchars($current_banner['text'] ?? '') ?></textarea>
                        <p class="form-text text-muted"><i class="fas fa-info-circle"></i> Ce texte apparaîtra en superposition sur la bannière (position par défaut : centré).</p>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn success"><i class="fas fa-save"></i> Enregistrer le texte</button>
                        <?php if (!empty($current_banner['text'])): ?>
                            <button type="button" class="btn danger" id="deleteBannerTextBtn" data-filename="texte">Supprimer le texte</button>
                        <?php endif; ?>
                    </div>
                </form>

                <?php if (!empty($current_banner['text'])): ?>
                    <form method="post" action="?page=edit-logo-banner&action=deleteBannerText" id="deleteBannerTextForm" style="display: none;">
                        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                        <input type="hidden" name="anchor" value="banner-text">
                    </form>
                <?php endif; ?>
            </div>
        </div>

        <div class="banner-separator"><span>OU</span></div>
    <?php endif; ?>

    <div class="accordion-section upload-banner-accordion" id="upload-banner">
        <div class="accordion-header">
            <h2><i class="fas fa-upload"></i> <?= !empty($current_banner) ? 'Changer la bannière' : 'Ajouter une bannière' ?></h2>
            <button type="button" class="accordion-toggle" data-target="upload-banner-content"><i class="fas fa-chevron-<?= empty($current_banner) ? 'down' : 'up' ?>"></i></button>
        </div>
        <div id="upload-banner-content" class="accordion-content <?= empty($current_banner) ? 'expanded' : 'collapsed' ?>">
            <form method="post" enctype="multipart/form-data" action="?page=edit-logo-banner&action=uploadBanner" class="upload-banner-form" id="upload-banner-form">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                <input type="hidden" name="anchor" value="upload-banner">
                <div class="upload-area" id="uploadBannerArea">
                    <div class="upload-icon"><i class="fas fa-cloud-upload-alt"></i></div>
                    <div class="upload-text">
                        <p class="upload-title">Glissez-déposez votre bannière ici</p>
                        <p class="upload-subtitle">ou cliquez pour sélectionner un fichier</p>
                    </div>
                    <input type="file" name="banner" id="banner-input" accept="image/*" class="file-input-hidden" required>
                    <button type="button" class="btn select-file-btn" id="selectBannerBtn">Choisir un fichier</button>
                </div>
                <div id="banner-preview-container" class="file-info-container" style="display: none;"></div>
                <div class="form-info">
                    <p><i class="fas fa-info-circle"></i> <strong>Formats acceptés :</strong> JPG, PNG, GIF, WebP, SVG</p>
                    <p><i class="fas fa-info-circle"></i> <strong>Taille maximale :</strong> 5 Mo</p>
                    <p><i class="fas fa-info-circle"></i> <strong>Recommandé :</strong> Format large (1200×300 px par exemple)</p>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn success" id="uploadBannerBtn" disabled><i class="fas fa-upload"></i> <?= !empty($current_banner) ? 'Remplacer la bannière' : 'Uploader la bannière' ?></button>
                    <button type="button" class="btn" id="resetBannerBtn"><i class="fas fa-redo"></i> Annuler la sélection</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Lightbox globale -->
<div id="media-lightbox" class="media-lightbox">
    <div class="lightbox-content">
        <button class="lightbox-close" id="lightboxClose">&times;</button>
        <img id="lightbox-image" src="" alt="">
        <div class="lightbox-caption" id="lightbox-caption"></div>
    </div>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>