<?php

/** @var string $csrf_token */
/** @var array|null $current_logo */
/** @var array|null $current_banner */
/** @var string $closeAccordion */
/** @var string $openAccordion */

$title = "Modifier les médias";
$scripts = [
    "js/sections/edit-logo-banner/edit-logo-banner.js",
    "js/effects/accordion.js",
    "js/effects/lightbox.js"
];
$styles = [
    "css/admin/shared/sweetalert-custom.css",
    "css/admin/sections/edit-logo-banner/center-forms.css"
];
require __DIR__ . '/../partials/header.php';
?>

<script>
    window.scrollParams = {
        currentLogoUrl: '<?= !empty($current_logo['public_url']) ? htmlspecialchars($current_logo['public_url']) : '' ?>',
        hasLogo: <?= !empty($current_logo) ? 'true' : 'false' ?>,
        currentBannerUrl: '<?= !empty($current_banner['public_url']) ? htmlspecialchars($current_banner['public_url']) : '' ?>',
        hasBanner: <?= !empty($current_banner) ? 'true' : 'false' ?>,
        bannerText: '<?= !empty($current_banner['text']) ? htmlspecialchars($current_banner['text']) : '' ?>',
        closeAccordion: '<?= htmlspecialchars($closeAccordion ?? '') ?>',
        openAccordion: '<?= htmlspecialchars($openAccordion ?? '') ?>'
    };
</script>

<a class="btn-back" href="?page=dashboard">Retour</a>

<div class="edit-logo-container">
    <div class="template-header">
        <h2><i class="fas fa-image"></i> Personnaliser l'image de marque</h2>
        <p class="template-subtitle">Configurez votre logo et votre bannière. Affichez votre identité visuelle en un clic !</p>
    </div>

    <!-- Boutons de contrôle généraux pour tous les accordéons -->
    <div class="global-accordion-controls">
        <button type="button" id="expand-all-accordions" class="btn"><i class="fas fa-expand-alt"></i> Tout ouvrir</button>
        <button type="button" id="collapse-all-accordions" class="btn"><i class="fas fa-compress-alt"></i> Tout fermer</button>
    </div>

    <!-- ==================== SECTION LOGO ==================== -->
    <?php if (!empty($current_logo)): ?>
        <div class="accordion-section current-logo-accordion" id="current-logo">
            <div class="accordion-header">
                <h2><i class="fas fa-image"></i> Logo actuel</h2>
                <button type="button" class="accordion-toggle" data-target="current-logo-content"><i class="fas fa-chevron-down"></i></button>
            </div>
            <div id="current-logo-content" class="accordion-content expanded prevent-auto-close">
                <div class="logo-display">
                    <div class="logo-image-container">
                        <img src="<?= htmlspecialchars($current_logo['public_url']) ?>" alt="Logo actuel" class="current-logo-image" id="current-logo-image">
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
    <?php endif; ?>

    <div class="accordion-section upload-logo-accordion" id="upload-logo">
        <div class="accordion-header">
            <h2><i class="fas fa-upload"></i> <?= !empty($current_logo) ? 'Changer le logo' : 'Ajouter un logo' ?></h2>
            <button type="button" class="accordion-toggle" data-target="upload-logo-content"><i class="fas fa-chevron-down"></i></button>
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
                <button type="button" class="accordion-toggle" data-target="current-banner-content"><i class="fas fa-chevron-down"></i></button>
            </div>
            <div id="current-banner-content" class="accordion-content expanded prevent-auto-close">
                <div class="banner-display">
                    <div class="banner-image-container">
                        <img src="<?= htmlspecialchars($current_banner['public_url']) ?>" alt="Bannière actuelle" class="current-banner-image" id="current-banner-image">
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
    <?php endif; ?>

    <div class="accordion-section upload-banner-accordion" id="upload-banner">
        <div class="accordion-header">
            <h2><i class="fas fa-upload"></i> <?= !empty($current_banner) ? 'Changer la bannière' : 'Ajouter une bannière' ?></h2>
            <button type="button" class="accordion-toggle" data-target="upload-banner-content"><i class="fas fa-chevron-down"></i></button>
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

    <!-- ==================== SECTION TEXTE DE LA BANNIÈRE (quand pas de bannière) ==================== -->
    <?php if (empty($current_banner)): ?>
        <div class="accordion-section banner-text-accordion" id="banner-text">
            <div class="accordion-header">
                <h2><i class="fas fa-comment-dots"></i> Texte de la bannière</h2>
                <button type="button" class="accordion-toggle" data-target="banner-text-content">
                    <i class="fas fa-chevron-down"></i>
                </button>
            </div>
            <div id="banner-text-content" class="accordion-content collapsed">
                <p class="info-message" id="banner-text-info"><i class="fas fa-info-circle"></i> Sélectionnez d'abord une bannière pour pouvoir ajouter du texte dessus.</p>
                <form method="post" action="?page=edit-logo-banner&action=updateBannerText" class="banner-text-form" id="banner-text-form-no-banner" style="display: none;">
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                    <input type="hidden" name="anchor" value="banner-text">

                    <div class="form-group">
                        <label for="banner_text_temp">Saisissez le texte à afficher sur la bannière :</label>
                        <textarea name="banner_text" id="banner_text_temp" rows="3" class="form-control" placeholder="Ex : Bienvenue chez nous !"></textarea>
                        <p class="form-text text-muted"><i class="fas fa-info-circle"></i> Ce texte apparaîtra en superposition sur la bannière (position par défaut : centré).</p>
                        <p class="form-text text-muted"><i class="fas fa-check-circle"></i> <strong>Astuce :</strong> Vous pouvez utiliser le bouton "Tout enregistrer" pour uploader la bannière et le texte en une seule fois !</p>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn success" disabled id="save-banner-text-temp"><i class="fas fa-save"></i> Enregistrer le texte</button>
                    </div>
                </form>
            </div>
        </div>
    <?php else: ?>
        <!-- ==================== SECTION TEXTE DE LA BANNIÈRE (quand bannière existe) ==================== -->
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
    <?php endif; ?>
    
    <!-- Bouton global pour tout enregistrer -->
    <div class="global-save-section">
        <button type="button" id="save-all-btn" class="btn success btn-large">
            <i class="fas fa-save"></i> Tout enregistrer
        </button>
        <p class="global-save-info">
            <i class="fas fa-info-circle"></i> Ce bouton enregistre tous les formulaires actifs de la page en une seule fois.
        </p>
    </div>

    <!-- Définition des étapes du tour guidé pour cette page -->
    <script>
const tourSteps = [
    {
        element: '.global-accordion-controls',
        title: 'Contrôles des accordéons',
        content: '<p>Utilisez ces boutons pour ouvrir ou fermer tous les accordéons de la page en un clic.</p><p>Pratique pour avoir une vue d\'ensemble ou se concentrer sur une section.</p>'
    },
    {
        element: '#upload-logo',
        title: 'Logo de votre restaurant',
        content: '<p>Le logo apparaît en haut de votre site et dans le navigateur.</p><p>Formats acceptés : JPG, PNG, GIF, WebP, SVG (max 5MB)</p><p>Recommandé : format carré avec fond transparent (PNG)</p>'
    },
    {
        element: '#upload-banner',
        title: 'Bannière d\'accueil',
        content: '<p>La bannière est l\'image principale qui accueille vos visiteurs en haut de votre site.</p><p>Format recommandé : large (1200×300 px) pour un bel effet visuel</p>',
        beforeShow: function() {
            // Ouvrir l'accordéon "upload-banner" avant d'afficher cette étape
            const uploadBannerAccordion = document.querySelector('#upload-banner-content');
            const uploadBannerToggle = document.querySelector('[data-target="upload-banner-content"]');
            if (uploadBannerAccordion && uploadBannerAccordion.classList.contains('collapsed')) {
                uploadBannerToggle.click();
            }
        }
    },
    {
        element: '#banner-text',
        title: 'Texte sur la bannière',
        content: '<p>Ajoutez un message de bienvenue ou un slogan qui s\'affichera en superposition sur votre bannière.</p><p>Exemple : "Bienvenue chez nous !", "Cuisine traditionnelle depuis 1950"</p><p><?php if (empty($current_banner)): ?>Note : Vous devez d\'abord uploader une bannière pour utiliser cette fonctionnalité.<?php endif; ?></p>',
        beforeShow: function() {
            // Ouvrir l'accordéon "banner-text" avant d'afficher cette étape
            const bannerTextAccordion = document.querySelector('#banner-text-content');
            const bannerTextToggle = document.querySelector('[data-target="banner-text-content"]');
            if (bannerTextAccordion && bannerTextAccordion.classList.contains('collapsed')) {
                bannerTextToggle.click();
            }
        }
    },
    {
        element: '#uploadBannerArea',
        title: 'Zone de téléchargement',
        content: '<p>Deux façons de télécharger vos images :</p><ul><li><strong>Glisser-déposer</strong> : Faites glisser votre fichier directement dans cette zone</li><li><strong>Cliquer</strong> : Cliquez sur "Choisir un fichier" pour parcourir vos dossiers</li></ul>',
        beforeShow: function() {
            // S'assurer que l'accordéon "upload-banner" est ouvert
            const uploadBannerAccordion = document.querySelector('#upload-banner-content');
            const uploadBannerToggle = document.querySelector('[data-target="upload-banner-content"]');
            if (uploadBannerAccordion && uploadBannerAccordion.classList.contains('collapsed')) {
                uploadBannerToggle.click();
            }
        }
    }
];

// Fonction appelée au démarrage du tour pour fermer tous les accordéons
window.tourBeforeStart = function() {
    const collapseAllBtn = document.querySelector('#collapse-all-accordions');
    if (collapseAllBtn) {
        collapseAllBtn.click();
    }
};
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Texte de la bannière (seul formulaire non géré par edit-logo-banner.js)
    var bannerTextForm = document.querySelector('.banner-text-form');
    if (bannerTextForm) {
        bannerTextForm.addEventListener('submit', function(e) {
            e.preventDefault();
            ajaxSubmit(this, '?page=edit-logo-banner&action=updateBannerText');
        });
    }
    
    // Bouton "Tout enregistrer"
    const saveAllBtn = document.getElementById('save-all-btn');
    if (saveAllBtn) {
        saveAllBtn.addEventListener('click', function() {
            const formsToSubmit = [];
            
            // Vérifier si un logo est sélectionné
            const logoInput = document.getElementById('logo-input');
            const uploadLogoBtn = document.getElementById('uploadLogoBtn');
            if (logoInput && logoInput.files.length > 0 && uploadLogoBtn && !uploadLogoBtn.disabled) {
                formsToSubmit.push({ form: document.getElementById('upload-logo-form'), name: 'Logo' });
            }
            
            // Vérifier si une bannière est sélectionnée
            const bannerInput = document.getElementById('banner-input');
            const uploadBannerBtn = document.getElementById('uploadBannerBtn');
            if (bannerInput && bannerInput.files.length > 0 && uploadBannerBtn && !uploadBannerBtn.disabled) {
                formsToSubmit.push({ form: document.getElementById('upload-banner-form'), name: 'Bannière' });
            }
            
            // Vérifier si le texte de bannière est rempli (formulaire temporaire ou existant)
            const bannerTextFormTemp = document.getElementById('banner-text-form-no-banner');
            const bannerTextInput = document.getElementById('banner_text_temp');
            if (bannerTextFormTemp && bannerTextFormTemp.style.display !== 'none' && bannerTextInput && bannerTextInput.value.trim() !== '') {
                formsToSubmit.push({ form: bannerTextFormTemp, name: 'Texte de bannière' });
            }
            
            // Vérifier aussi le formulaire de texte si bannière existe déjà
            const bannerTextForm = document.querySelector('.banner-text-form:not(#banner-text-form-no-banner)');
            const bannerTextInputExisting = document.getElementById('banner_text');
            if (bannerTextForm && bannerTextForm.style.display !== 'none' && bannerTextInputExisting && bannerTextInputExisting.value.trim() !== '') {
                formsToSubmit.push({ form: bannerTextForm, name: 'Texte de bannière' });
            }
            
            if (formsToSubmit.length === 0) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Aucune modification',
                        text: 'Aucun formulaire actif à enregistrer.',
                        icon: 'info'
                    });
                } else {
                    alert('Aucun formulaire actif à enregistrer.');
                }
                return;
            }
            
            // Confirmation avant enregistrement
            const formsList = formsToSubmit.map(f => f.name).join(', ');
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Tout enregistrer',
                    html: `<div style="text-align: left; padding-left: 20px;"><p>Vous allez enregistrer :</p><ul style="margin: 10px 0; padding-left: 20px;">${formsToSubmit.map(f => '<li>' + f.name + '</li>').join('')}</ul><p style="margin-top: 15px;">Continuer ?</p></div>`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#10b981',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: '<i class="fas fa-save"></i> Oui, tout enregistrer',
                    cancelButtonText: 'Annuler'
                }).then((result) => {
                    if (result.isConfirmed) {
                        submitAllForms(formsToSubmit);
                    }
                });
            } else {
                if (confirm('Enregistrer : ' + formsList + ' ?')) {
                    submitAllForms(formsToSubmit);
                }
            }
        });
    }
    
    function submitAllForms(formsToSubmit) {
        let completed = 0;
        let errors = 0;
        let successCount = 0;
        
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Enregistrement en cours...',
                html: `<p>Progression : <span id="save-all-progress">0 / ${formsToSubmit.length}</span></p>`,
                allowOutsideClick: false,
                showConfirmButton: false,
                willOpen: () => Swal.showLoading()
            });
        }
        
        // Fonction récursive pour soumettre les formulaires un par un
        function submitNext(index) {
            if (index >= formsToSubmit.length) {
                // Tous les formulaires ont été traités
                if (typeof Swal !== 'undefined') {
                    if (errors === 0) {
                        Swal.fire({
                            title: 'Succès !',
                            text: `Tous les éléments ont été enregistrés avec succès.`,
                            icon: 'success'
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            title: 'Terminé avec erreurs',
                            text: `${successCount} élément(s) enregistré(s), ${errors} erreur(s)`,
                            icon: 'warning'
                        }).then(() => {
                            location.reload();
                        });
                    }
                } else {
                    alert(`Enregistrement terminé : ${successCount} succès, ${errors} erreurs`);
                    location.reload();
                }
                return;
            }
            
            const item = formsToSubmit[index];
            const formData = new FormData(item.form);
            const url = item.form.getAttribute('action');
            
            console.log(`[Tout enregistrer] Soumission ${index + 1}/${formsToSubmit.length}: ${item.name}`);
            console.log(`[Tout enregistrer] URL: ${url}`);
            console.log(`[Tout enregistrer] FormData entries:`, Array.from(formData.entries()));
            
            // Utiliser XMLHttpRequest pour gérer correctement les fichiers
            const xhr = new XMLHttpRequest();
            xhr.open('POST', url, true);
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            
            xhr.onload = function() {
                completed++;
                const progressSpan = document.getElementById('save-all-progress');
                if (progressSpan) {
                    progressSpan.textContent = `${completed} / ${formsToSubmit.length}`;
                }
                
                console.log(`[Tout enregistrer] Réponse pour ${item.name} (status ${xhr.status}):`, xhr.responseText);
                
                try {
                    const data = JSON.parse(xhr.responseText);
                    if (data.success) {
                        successCount++;
                        console.log(`[Tout enregistrer] ✓ Succès pour ${item.name}`);
                    } else {
                        errors++;
                        console.error(`[Tout enregistrer] ✗ Erreur pour ${item.name}:`, data.message || 'Erreur inconnue', data);
                    }
                } catch (e) {
                    errors++;
                    console.error(`[Tout enregistrer] ✗ Erreur de parsing pour ${item.name}:`, e);
                    console.error(`[Tout enregistrer] Réponse brute:`, xhr.responseText);
                }
                
                // Attendre 500ms avant de soumettre le suivant
                setTimeout(() => submitNext(index + 1), 500);
            };
            
            xhr.onerror = function() {
                completed++;
                errors++;
                const progressSpan = document.getElementById('save-all-progress');
                if (progressSpan) {
                    progressSpan.textContent = `${completed} / ${formsToSubmit.length}`;
                }
                console.error(`[Tout enregistrer] ✗ Erreur réseau pour ${item.name}`);
                
                // Attendre 500ms avant de soumettre le suivant
                setTimeout(() => submitNext(index + 1), 500);
            };
            
            xhr.send(formData);
        }
        
        // Démarrer la soumission avec le premier formulaire
        submitNext(0);
    }
});
</script>

<?php require __DIR__ . '/../partials/footer.php'; ?>