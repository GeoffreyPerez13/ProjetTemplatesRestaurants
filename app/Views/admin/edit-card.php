<?php
require_once __DIR__ . '/../../Helpers/CategoryIconHelper.php';

$title = "Modifier la carte";
$scripts = [
    "js/effects/accordion.js",
    "js/sections/edit-card/edit-card.js",
    "js/effects/lightbox.js"
];

if ($currentMode === 'images') {
    $scripts[] = "js/sections/edit-card/images-mode.js";
    $scripts[] = "js/sections/edit-card/drag-and-drop.js";
} elseif ($currentMode === 'editable') {
    $scripts[] = "js/sections/edit-card/category-order.js";
    $scripts[] = "js/sections/edit-card/quick-add.js";
    $scripts[] = "js/sections/edit-card/allergens-accordion.js";
}

// Récupérer les paramètres de session pour les accordéons
$closeAccordion = $_SESSION['close_accordion'] ?? '';
$closeAccordionSecondary = $_SESSION['close_accordion_secondary'] ?? '';
$closeDishAccordion = $_SESSION['close_dish_accordion'] ?? '';
$openAccordion = $_SESSION['open_accordion'] ?? '';

// Nettoyer les variables de session après lecture
unset(
    $_SESSION['close_accordion'],
    $_SESSION['close_accordion_secondary'],
    $_SESSION['close_dish_accordion'],
    $_SESSION['open_accordion']
);

require __DIR__ . '/../partials/header.php';
?>

<!-- Script pour passer les paramètres au JavaScript -->
<script>
    // Variables disponibles pour edit-card.js
    window.scrollParams = {
        anchor: '<?= htmlspecialchars($anchor ?? '') ?>',
        scrollDelay: <?= (int)($scroll_delay ?? 1500) ?>,
        closeAccordion: '<?= htmlspecialchars($closeAccordion) ?>',
        closeAccordionSecondary: '<?= htmlspecialchars($closeAccordionSecondary) ?>',
        closeDishAccordion: '<?= htmlspecialchars($closeDishAccordion) ?>',
        openAccordion: '<?= htmlspecialchars($openAccordion) ?>'
    };

    // FORCER l'ouverture de l'accordéon images si on vient d'une réorganisation
    <?php if (isset($_POST['update_image_order']) && empty($error_message)): ?>
        window.scrollParams.openAccordion = 'images-list-content';
        window.scrollParams.closeAccordion = 'mode-selector-content';
    <?php endif; ?>
</script>

<a class="btn-back" href="?page=dashboard">Retour</a>

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

<!-- Sélecteur de mode (accordéon) -->
<div class="accordion-section mode-selector-accordion" id="mode-selector">
    <div class="accordion-header">
        <h2><i class="fas fa-cogs"></i> Mode d'affichage de la carte</h2>
        <button type="button" class="accordion-toggle" data-target="mode-selector-content">
            <i class="fas fa-chevron-down"></i>
        </button>
    </div>

    <div id="mode-selector-content" class="accordion-content expanded">
        <form method="post" class="mode-selector-form">
            <input type="hidden" name="anchor" value="mode-selector">

            <div class="mode-options">
                <label class="mode-option">
                    <input type="radio" name="carte_mode" value="editable"
                        <?= $currentMode === 'editable' ? 'checked' : '' ?>>
                    <div class="mode-card">
                        <i class="fas fa-edit"></i>
                        <h3>Mode Éditable</h3>
                        <p>Créez et modifiez des catégories et plats détaillés</p>
                    </div>
                </label>

                <label class="mode-option">
                    <input type="radio" name="carte_mode" value="images"
                        <?= $currentMode === 'images' ? 'checked' : '' ?>>
                    <div class="mode-card">
                        <i class="fas fa-images"></i>
                        <h3>Mode Images</h3>
                        <p>Téléchargez des images de votre carte existante (PDF, JPG, etc.)</p>
                    </div>
                </label>
            </div>

            <button type="submit" name="change_mode" class="btn primary">Changer de mode</button>
        </form>
    </div>
</div>

<?php if ($currentMode === 'editable'): ?>
    <!-- ==================== MODE ÉDITABLE ==================== -->
    <div class="edit-carte-container">

        <!-- Bloc Ajout rapide de catégories (accordéon) -->
        <div class="accordion-section quick-add-categories-accordion" id="quick-add-categories">
            <div class="accordion-header">
                <h2><i class="fas fa-layer-group"></i> Ajout rapide de catégories</h2>
                <button type="button" class="accordion-toggle" data-target="quick-add-categories-content">
                    <i class="fas fa-chevron-down"></i>
                </button>
            </div>

            <div id="quick-add-categories-content" class="accordion-content collapsed">
                <form method="post" enctype="multipart/form-data" class="quick-add-form" id="quick-add-categories-form">
                    <input type="hidden" name="batch_add_categories" value="1">
                    <input type="hidden" name="anchor" value="quick-add-categories">

                    <div class="quick-add-header">
                        <p class="quick-add-description">
                            <i class="fas fa-info-circle"></i>
                            Ajoutez plusieurs catégories en une seule fois avec leurs images (optionnelles).
                        </p>
                    </div>

                    <div class="quick-add-table">
                        <div class="quick-add-table-header">
                            <div class="quick-add-col-name">Nom de la catégorie</div>
                            <div class="quick-add-col-order">Ordre</div>
                            <div class="quick-add-col-image">Image</div>
                            <div class="quick-add-col-actions">Actions</div>
                        </div>
                        <div id="categories-rows-container">
                            <!-- Ligne initiale -->
                            <div class="quick-add-row">
                                <div class="quick-add-col-name">
                                    <input type="text" name="categories[0][name]" placeholder="Ex: Entrées" required class="quick-add-input">
                                </div>
                                <div class="quick-add-col-order">
                                    <input type="number" name="categories[0][order]" value="1" min="1" class="quick-add-input-small">
                                </div>
                                <div class="quick-add-col-image">
                                    <input type="file" name="category_images[0]" accept="image/jpeg,image/png,image/gif,image/webp" class="quick-add-file">
                                </div>
                                <div class="quick-add-col-actions">
                                    <button type="button" class="btn-icon btn-remove-row" title="Supprimer cette ligne" disabled>
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="quick-add-actions">
                        <button type="button" class="btn btn-add-row" id="add-category-row">
                            <i class="fas fa-plus"></i> Ajouter une ligne
                        </button>
                        <button type="submit" class="btn success">
                            <i class="fas fa-check"></i> Créer les catégories
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Bloc Ajout rapide de plats (accordéon) -->
        <div class="accordion-section quick-add-dishes-accordion" id="quick-add-dishes">
            <div class="accordion-header">
                <h2><i class="fas fa-utensils"></i> Ajout rapide de plats</h2>
                <button type="button" class="accordion-toggle" data-target="quick-add-dishes-content">
                    <i class="fas fa-chevron-down"></i>
                </button>
            </div>

            <div id="quick-add-dishes-content" class="accordion-content collapsed">
                <form method="post" enctype="multipart/form-data" class="quick-add-form" id="quick-add-dishes-form">
                    <input type="hidden" name="batch_add_dishes" value="1">
                    <input type="hidden" name="anchor" value="quick-add-dishes">

                    <div class="quick-add-header">
                        <p class="quick-add-description">
                            <i class="fas fa-info-circle"></i>
                            Ajoutez plusieurs plats en une seule fois avec leurs images et allergènes (optionnels).
                        </p>
                        <div class="form-group">
                            <label>Catégorie cible :</label>
                            <select name="target_category_id" id="target-category" style="position:absolute;opacity:0;height:0;width:0;pointer-events:none;" tabindex="-1" aria-hidden="true">
                                <option value="">-- Sélectionnez une catégorie --</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="custom-category-select" id="custom-category-select">
                                <button type="button" class="custom-category-toggle" id="custom-category-toggle" aria-expanded="false">
                                    <span class="custom-category-value">
                                        <i class="fas fa-layer-group"></i>
                                        Sélectionnez une catégorie
                                    </span>
                                    <i class="fas fa-chevron-down custom-category-chevron"></i>
                                </button>
                                <div class="custom-category-dropdown" id="custom-category-dropdown">
                                    <?php foreach ($categories as $cat): ?>
                                        <div class="custom-category-option" data-value="<?= $cat['id'] ?>">
                                            <i class="fas <?= CategoryIconHelper::getIcon($cat['name']) ?>"></i>
                                            <?= htmlspecialchars($cat['name']) ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <p class="custom-category-error" id="custom-category-error" style="display:none;">
                                <i class="fas fa-exclamation-circle"></i> Veuillez sélectionner une catégorie
                            </p>
                        </div>
                    </div>

                    <div class="quick-add-table">
                        <div class="quick-add-table-header">
                            <div class="quick-add-col-dish-name">Nom du plat</div>
                            <div class="quick-add-col-price">Prix (€)</div>
                            <div class="quick-add-col-description">Description</div>
                            <div class="quick-add-col-image">Image</div>
                            <div class="quick-add-col-allergens">Allergènes</div>
                            <div class="quick-add-col-actions">Actions</div>
                        </div>
                        <div id="dishes-rows-container">
                            <!-- Ligne initiale -->
                            <div class="quick-add-row">
                                <div class="quick-add-col-dish-name">
                                    <input type="text" name="dishes[0][name]" placeholder="Ex: Salade César" required class="quick-add-input">
                                </div>
                                <div class="quick-add-col-price">
                                    <input type="number" name="dishes[0][price]" step="0.01" min="0.01" placeholder="12.50" required class="quick-add-input-small">
                                </div>
                                <div class="quick-add-col-description">
                                    <input type="text" name="dishes[0][description]" placeholder="Description courte (optionnel)" class="quick-add-input">
                                </div>
                                <div class="quick-add-col-image">
                                    <input type="file" name="dish_images[0]" accept="image/jpeg,image/png,image/gif,image/webp" class="quick-add-file">
                                </div>
                                <div class="quick-add-col-allergens">
                                    <button type="button" class="btn-allergens-toggle" data-row="0" title="Sélectionner les allergènes">
                                        <i class="fas fa-exclamation-triangle"></i> <span class="allergens-count">0</span>
                                    </button>
                                    <div class="allergens-popup" data-row="0" style="display: none;">
                                        <div class="allergens-popup-header">
                                            <span>Allergènes</span>
                                            <button type="button" class="btn-close-popup"><i class="fas fa-times"></i></button>
                                        </div>
                                        <div class="allergens-popup-grid">
                                            <?php foreach ($allergenes as $allergene): ?>
                                                <label class="allergen-checkbox-compact" data-allergen-name="<?= htmlspecialchars($allergene['nom']) ?>">
                                                    <input type="checkbox" name="dishes[0][allergens][]" value="<?= $allergene['id'] ?>">
                                                    <?php if (!empty($allergene['icone'])): ?>
                                                        <i class="fas <?= htmlspecialchars($allergene['icone']) ?>"></i>
                                                    <?php endif; ?>
                                                    <span><?= htmlspecialchars($allergene['nom']) ?></span>
                                                </label>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="quick-add-col-actions">
                                    <button type="button" class="btn-icon btn-remove-row" title="Supprimer cette ligne" disabled>
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="quick-add-actions">
                        <button type="button" class="btn btn-add-row" id="add-dish-row">
                            <i class="fas fa-plus"></i> Ajouter une ligne
                        </button>
                        <button type="submit" class="btn success">
                            <i class="fas fa-check"></i> Créer les plats
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Bloc catégories existantes -->
        <div class="categories-grid">
            <?php foreach ($categories as $cat): ?>
                <div class="category-block" id="category-<?= $cat['id'] ?>">
                    <div class="category-header">
                        <!-- Colonne gauche : ordre en haut, nom en dessous -->
                        <div class="category-left">
                            <div class="category-order-control">
                                <div class="option-tooltip">
                                    <span class="tooltip-icon" title="Plus d'infos">i</span>
                                    <div class="tooltip-content">
                                        <p>Définissez l'ordre d'affichage de vos catégories sur votre site. Les catégories avec un numéro plus petit apparaîtront en premier.</p>
                                    </div>
                                </div>
                                <span class="category-order-label">Ordre d'affichage</span>
                                <input type="number"
                                    class="category-order-input"
                                    min="1"
                                    value="<?= (int)($cat['display_order'] ?? 0) ?>"
                                    data-category-id="<?= $cat['id'] ?>"
                                    title="Ordre d'affichage de la catégorie">
                                <span class="category-order-status" data-category-id="<?= $cat['id'] ?>"></span>
                            </div>
                            <strong><i class="fas <?= CategoryIconHelper::getIcon($cat['name']) ?>"></i> <?= htmlspecialchars($cat['name']) ?></strong>
                        </div>

                        <!-- Colonne droite : X en haut, boutons accordéon en dessous -->
                        <div class="category-right">
                            <form method="post" class="category-delete-form">
                                <input type="hidden" name="delete_category" value="<?= $cat['id'] ?>">
                                <input type="hidden" name="anchor" value="categories-grid">
                                <button type="submit" class="category-delete-btn" title="Supprimer cette catégorie">
                                    <i class="fas fa-times"></i>
                                </button>
                            </form>
                        </div>
                        <div class="category-accordion-controls">
                            <button type="button" class="btn small expand-category" data-category-id="<?= $cat['id'] ?>">
                                <i class="fas fa-expand-alt"></i> Tout ouvrir
                            </button>
                            <button type="button" class="btn small collapse-category" data-category-id="<?= $cat['id'] ?>">
                                <i class="fas fa-compress-alt"></i> Tout fermer
                            </button>
                        </div>
                    </div>

                    <!-- Affichage de l'image de la catégorie -->
                    <?php if (!empty($cat['image'])): ?>
                        <div class="current-image">
                            <label>Image actuelle :</label>
                            <img src="/<?= htmlspecialchars($cat['image']) ?>"
                                alt="<?= htmlspecialchars($cat['name']) ?>"
                                class="image-preview lightbox-image"
                                data-caption="<?= htmlspecialchars($cat['name']) ?>">
                        </div>
                    <?php endif; ?>

                    <!-- Section Modifier la catégorie (accordéon) -->
                    <div class="accordion-section">
                        <div class="accordion-header">
                            <h3><i class="fas fa-edit"></i> Modifier la catégorie</h3>
                            <button type="button" class="accordion-toggle" data-target="edit-category-<?= $cat['id'] ?>">
                                <i class="fas fa-chevron-down"></i>
                            </button>
                        </div>

                        <div id="edit-category-<?= $cat['id'] ?>" class="accordion-content collapsed">
                            <form method="post" class="edit-category-form" enctype="multipart/form-data">
                                <input type="hidden" name="category_id" value="<?= $cat['id'] ?>">
                                <input type="hidden" name="anchor" value="category-<?= $cat['id'] ?>">

                                <div class="form-group <?= isset($error_fields['edit_category_name']) ? 'has-error' : '' ?>">
                                    <label for="edit_category_name_<?= $cat['id'] ?>">Nom de la catégorie</label>
                                    <input type="text" name="edit_category_name" id="edit_category_name_<?= $cat['id'] ?>"
                                        value="<?= htmlspecialchars($old_input['edit_category_name'] ?? $cat['name']) ?>"
                                        placeholder="Ex: Entrées" required
                                        class="<?= isset($error_fields['edit_category_name']) ? 'error-field' : '' ?>">
                                    <?php if (isset($error_fields['edit_category_name'])): ?>
                                        <div class="field-error-message">Le nom de la catégorie est requis (max 100 caractères)</div>
                                    <?php endif; ?>
                                </div>

                                <div class="form-group">
                                    <label for="edit_category_image_<?= $cat['id'] ?>">
                                        <i class="fas fa-image"></i> <?= empty($cat['image']) ? 'Ajouter une image' : 'Changer l\'image' ?> (optionnel)
                                    </label>
                                    <input type="file" name="edit_category_image" id="edit_category_image_<?= $cat['id'] ?>"
                                        accept="image/jpeg, image/png, image/gif, image/webp">
                                    <small>Formats acceptés: JPEG, PNG, GIF, WebP (max 2MB)</small>
                                </div>

                                <div class="form-actions">
                                    <button type="submit" name="edit_category" class="btn primary">
                                        <i class="fas fa-save"></i> Enregistrer
                                    </button>
                                    <?php if (!empty($cat['image'])): ?>
                                        <button type="submit" name="remove_category_image" value="<?= $cat['id'] ?>" class="btn secondary">
                                            <i class="fas fa-trash-alt"></i> Supprimer l'image
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Section Ajouter un plat (accordéon) -->
                    <div class="accordion-section">
                        <div class="accordion-header">
                            <h3><i class="fas fa-plus-square"></i> Ajouter un plat</h3>
                            <button type="button" class="accordion-toggle" data-target="add-dish-<?= $cat['id'] ?>">
                                <i class="fas fa-chevron-down"></i>
                            </button>
                        </div>

                        <div id="add-dish-<?= $cat['id'] ?>" class="accordion-content collapsed">
                            <form method="post" class="new-dish-form" enctype="multipart/form-data">
                                <input type="hidden" name="category_id" value="<?= $cat['id'] ?>">
                                <input type="hidden" name="anchor" value="category-<?= $cat['id'] ?>">

                                <div class="form-row">
                                    <div class="form-group <?= isset($error_fields['dish_name']) ? 'has-error' : '' ?>">
                                        <input type="text" name="dish_name" placeholder="Nom du plat" required
                                            value="<?= htmlspecialchars($old_input['dish_name'] ?? '') ?>"
                                            class="<?= isset($error_fields['dish_name']) ? 'error-field' : '' ?>">
                                        <?php if (isset($error_fields['dish_name'])): ?>
                                            <div class="field-error-message">Le nom du plat est requis (max 100 caractères)</div>
                                        <?php endif; ?>
                                    </div>

                                    <div class="form-group <?= isset($error_fields['dish_price']) ? 'has-error' : '' ?>">
                                        <div class="input-euro <?= isset($error_fields['dish_price']) ? 'error-input-group' : '' ?>">
                                            <input type="number" step="0.01" name="dish_price" placeholder="Prix" required
                                                value="<?= htmlspecialchars($old_input['dish_price'] ?? '') ?>"
                                                class="<?= isset($error_fields['dish_price']) ? 'error-field' : '' ?>">
                                            <span class="euro-symbol">€</span>
                                        </div>
                                        <?php if (isset($error_fields['dish_price'])): ?>
                                            <div class="field-error-message">Le prix doit être un nombre entre 0.01 et 999.99</div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="form-group <?= isset($error_fields['dish_description']) ? 'has-error' : '' ?>">
                                    <textarea name="dish_description" placeholder="Description du plat (optionnel)"
                                        class="<?= isset($error_fields['dish_description']) ? 'error-field' : '' ?>"><?= htmlspecialchars($old_input['dish_description'] ?? '') ?></textarea>
                                    <?php if (isset($error_fields['dish_description'])): ?>
                                        <div class="field-error-message">La description ne doit pas dépasser 500 caractères</div>
                                    <?php endif; ?>
                                </div>

                                <div class="image-upload-container">
                                    <label for="dish_image_<?= $cat['id'] ?>">Image du plat (optionnel) :</label>
                                    <input type="file" name="dish_image" id="dish_image_<?= $cat['id'] ?>" accept="image/jpeg, image/png, image/gif, image/webp">
                                    <small>Formats acceptés: JPEG, PNG, GIF, WebP (max 2MB)</small>
                                </div>

                                <!-- Allergènes pour le nouveau plat (accordéon) -->
                                <div class="allergenes-accordion">
                                    <button type="button" class="allergenes-accordion-toggle" data-target="allergenes-add-<?= $cat['id'] ?>">
                                        <span><i class="fas fa-exclamation-triangle"></i> Allergènes (optionnel)</span>
                                        <i class="fas fa-chevron-down"></i>
                                    </button>
                                    <div class="allergenes-accordion-content collapsed" id="allergenes-add-<?= $cat['id'] ?>">
                                        <div class="allergenes-controls">
                                            <button type="button" class="btn-allergenes-toggle" data-target="allergenes-add-<?= $cat['id'] ?>">
                                                <i class="fas fa-check-double"></i> Tout (dé)cocher
                                            </button>
                                        </div>
                                        <div class="allergenes-grid">
                                            <?php foreach ($allergenes as $allergene): ?>
                                                <label class="allergene-checkbox">
                                                    <input type="checkbox" name="allergenes[]" value="<?= $allergene['id'] ?>">
                                                    <?php if (!empty($allergene['icone'])): ?>
                                                        <i class="fas <?= htmlspecialchars($allergene['icone']) ?>"></i>
                                                    <?php endif; ?>
                                                    <?= htmlspecialchars($allergene['nom']) ?>
                                                </label>
                                            <?php endforeach; ?>
                                        </div>
                                        <small>Sélectionnez les allergènes présents dans ce plat.</small>
                                    </div>
                                </div>

                                <button type="submit" name="new_dish" class="btn success">Ajouter plat</button>
                            </form>
                        </div>
                    </div>

                    <?php $plats = $cat['plats'] ?? []; ?>
                    <?php if ($plats): ?>
                        <!-- Section Modifier les plats (accordéon) -->
                        <div class="accordion-section">
                            <div class="accordion-header">
                                <h3><i class="fas fa-utensils"></i> Modifier les plats (<?= count($plats) ?>)</h3>
                                <button type="button" class="accordion-toggle" data-target="edit-dishes-<?= $cat['id'] ?>">
                                    <i class="fas fa-chevron-down"></i>
                                </button>
                            </div>

                            <div id="edit-dishes-<?= $cat['id'] ?>" class="accordion-content collapsed">
                                <ul class="dish-list">
                                    <?php foreach ($plats as $plat): ?>
                                        <li class="dish-accordion-item">
                                            <!-- En-tête du plat (accordéon) -->
                                            <div class="dish-accordion-header">
                                                <h4><i class="fas fa-utensil-spoon"></i> <?= htmlspecialchars($plat['name']) ?> - <?= htmlspecialchars($plat['price']) ?>€</h4>
                                                <div class="dish-header-actions">
                                                    <form method="post" class="dish-delete-form">
                                                        <input type="hidden" name="delete_dish" value="<?= $plat['id'] ?>">
                                                        <input type="hidden" name="current_category_id" value="<?= $cat['id'] ?>">
                                                        <input type="hidden" name="anchor" value="category-<?= $cat['id'] ?>">
                                                        <button type="submit" class="dish-delete-btn" title="Supprimer ce plat">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    </form>
                                                    <button type="button" class="dish-accordion-toggle"
                                                        data-target="dish-<?= $plat['id'] ?>"
                                                        data-category="<?= $cat['id'] ?>">
                                                        <i class="fas fa-chevron-down"></i>
                                                    </button>
                                                </div>
                                            </div>

                                            <!-- Contenu du plat -->
                                            <div id="dish-<?= $plat['id'] ?>" class="dish-accordion-content" data-category="<?= $cat['id'] ?>">
                                                <div class="dish-edit-container">
                                                    <form method="post" class="inline-form edit-form" enctype="multipart/form-data">
                                                        <input type="hidden" name="dish_id" value="<?= $plat['id'] ?>">
                                                        <input type="hidden" name="current_category_id" value="<?= $cat['id'] ?>">
                                                        <input type="hidden" name="anchor" value="dish-<?= $plat['id'] ?>">

                                                        <!-- Section Image -->
                                                        <?php if (!empty($plat['image'])): ?>
                                                            <div class="dish-current-image-container">
                                                                <label>Image actuelle :</label>
                                                                <img src="/<?= htmlspecialchars($plat['image']) ?>"
                                                                    alt="<?= htmlspecialchars($plat['name']) ?>"
                                                                    class="dish-image-preview lightbox-image"
                                                                    data-caption="<?= htmlspecialchars($plat['name']) ?> - <?= htmlspecialchars($plat['price']) ?>€">
                                                            </div>
                                                        <?php endif; ?>

                                                        <div class="form-group">
                                                            <label for="edit_dish_image_<?= $plat['id'] ?>">
                                                                <i class="fas fa-image"></i> <?= empty($plat['image']) ? 'Ajouter une image' : 'Changer l\'image' ?> (optionnel)
                                                            </label>
                                                            <input type="file" name="dish_image" id="edit_dish_image_<?= $plat['id'] ?>"
                                                                accept="image/jpeg, image/png, image/gif, image/webp">
                                                            <small>Formats acceptés: JPEG, PNG, GIF, WebP (max 2MB)</small>
                                                        </div>

                                                        <!-- Nom + Prix -->
                                                        <div class="dish-name-price-row">
                                                            <div class="form-group <?= isset($error_fields['dish_name_' . $plat['id']]) ? 'has-error' : '' ?>">
                                                                <input type="text" name="dish_name"
                                                                    value="<?= htmlspecialchars($old_input['dish_name'] ?? $plat['name']) ?>"
                                                                    placeholder="Nom du plat" required
                                                                    class="<?= isset($error_fields['dish_name_' . $plat['id']]) ? 'error-field' : '' ?>">
                                                                <?php if (isset($error_fields['dish_name_' . $plat['id']])): ?>
                                                                    <div class="field-error-message">Le nom du plat est requis (max 100 caractères)</div>
                                                                <?php endif; ?>
                                                            </div>

                                                            <div class="form-group <?= isset($error_fields['dish_price_' . $plat['id']]) ? 'has-error' : '' ?>">
                                                                <div class="input-euro <?= isset($error_fields['dish_price_' . $plat['id']]) ? 'error-input-group' : '' ?>">
                                                                    <input type="text" name="dish_price"
                                                                        value="<?= htmlspecialchars($old_input['dish_price'] ?? $plat['price']) ?>"
                                                                        placeholder="Prix" required
                                                                        class="price-input <?= isset($error_fields['dish_price_' . $plat['id']]) ? 'error-field' : '' ?>">
                                                                    <span class="euro-symbol">€</span>
                                                                </div>
                                                                <?php if (isset($error_fields['dish_price_' . $plat['id']])): ?>
                                                                    <div class="field-error-message">Le prix doit être un nombre entre 0.01 et 999.99</div>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>

                                                        <!-- Description -->
                                                        <div class="form-group <?= isset($error_fields['dish_description_' . $plat['id']]) ? 'has-error' : '' ?>">
                                                            <textarea name="dish_description" placeholder="Description du plat (optionnel)"
                                                                class="description-input <?= isset($error_fields['dish_description_' . $plat['id']]) ? 'error-field' : '' ?>"><?= htmlspecialchars($old_input['dish_description'] ?? ($plat['description'] ?? '')) ?></textarea>
                                                            <?php if (isset($error_fields['dish_description_' . $plat['id']])): ?>
                                                                <div class="field-error-message">La description ne doit pas dépasser 500 caractères</div>
                                                            <?php endif; ?>
                                                        </div>

                                                        <!-- Allergènes pour ce plat (accordéon) -->
                                                        <div class="allergenes-accordion">
                                                            <button type="button" class="allergenes-accordion-toggle" data-target="allergenes-edit-<?= $plat['id'] ?>">
                                                                <span><i class="fas fa-exclamation-triangle"></i> Allergènes</span>
                                                                <i class="fas fa-chevron-down"></i>
                                                            </button>
                                                            <div class="allergenes-accordion-content collapsed" id="allergenes-edit-<?= $plat['id'] ?>">
                                                                <div class="allergenes-controls">
                                                                    <button type="button" class="btn-allergenes-toggle" data-target="allergenes-edit-<?= $plat['id'] ?>">
                                                                        <i class="fas fa-check-double"></i> Tout (dé)cocher
                                                                    </button>
                                                                </div>
                                                                <div class="allergenes-grid">
                                                                    <?php
                                                                    $allergenesDuPlat = $platsAllergenes[$plat['id']] ?? [];
                                                                    foreach ($allergenes as $allergene):
                                                                    ?>
                                                                        <label class="allergene-checkbox">
                                                                            <input type="checkbox" name="allergenes_<?= $plat['id'] ?>[]" value="<?= $allergene['id'] ?>"
                                                                                <?= in_array($allergene['id'], $allergenesDuPlat) ? 'checked' : '' ?>>
                                                                            <?php if (!empty($allergene['icone'])): ?>
                                                                                <i class="fas <?= htmlspecialchars($allergene['icone']) ?>"></i>
                                                                            <?php endif; ?>
                                                                            <?= htmlspecialchars($allergene['nom']) ?>
                                                                        </label>
                                                                    <?php endforeach; ?>
                                                                </div>
                                                                <small>Cochez les allergènes présents.</small>
                                                            </div>
                                                        </div>

                                                        <div class="form-actions">
                                                            <button type="submit" name="edit_dish" class="btn primary">
                                                                <i class="fas fa-save"></i> Enregistrer
                                                            </button>
                                                            <?php if (!empty($plat['image'])): ?>
                                                                <button type="submit" name="remove_dish_image" value="<?= $plat['id'] ?>" class="btn secondary">
                                                                    <i class="fas fa-trash-alt"></i> Supprimer l'image
                                                                </button>
                                                            <?php endif; ?>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    <?php else: ?>
                        <p class="no-dishes">Aucun plat pour cette catégorie.</p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

<?php else: ?>
    <!-- ==================== MODE IMAGES ==================== -->
    <div class="images-mode-container">

        <!-- Upload d'images (accordéon) -->
        <div class="accordion-section upload-images-accordion" id="upload-images">
            <div class="accordion-header">
                <h2><i class="fas fa-cloud-upload-alt"></i> Ajouter des images à la carte</h2>
                <button type="button" class="accordion-toggle" data-target="upload-images-content">
                    <i class="fas fa-chevron-down"></i>
                </button>
            </div>

            <div id="upload-images-content" class="accordion-content expanded">
                <form method="post" enctype="multipart/form-data" class="upload-form">
                    <input type="hidden" name="anchor" value="upload-images">

                    <div class="upload-area" id="uploadArea">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <p>Glissez-déposez vos images ici ou cliquez pour sélectionner</p>
                        <input type="file" name="card_images[]" id="card_images"
                            multiple accept="image/*,.pdf">
                        <button type="button" class="btn" onclick="document.getElementById('card_images').click()">
                            Choisir des fichiers
                        </button>
                        <div id="fileList" class="file-list"></div>

                        <!-- Prévisualisation des images -->
                        <div id="imagePreview" class="image-preview-grid"></div>

                        <!-- Compteur d'images -->
                        <div id="imageCounter" class="image-counter">
                            <span id="selectedCount">0</span> image(s) sélectionnée(s)
                        </div>
                    </div>

                    <div class="upload-info">
                        <p><small>Formats acceptés : JPG, PNG, GIF, WebP, PDF</small></p>
                        <p><small>Taille maximale par fichier : 5MB</small></p>
                    </div>

                    <div class="upload-actions">
                        <button type="submit" name="upload_images" class="btn success" id="uploadButton" disabled>
                            <i class="fas fa-upload"></i> Télécharger les images (<span id="uploadCount">0</span>)
                        </button>

                        <button type="button" class="btn danger" id="clearSelection">
                            <i class="fas fa-times"></i> Annuler la sélection
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Liste des images existantes (accordéon) -->
        <div class="accordion-section images-list-accordion" id="images-list">
            <div class="accordion-header">
                <h2><i class="fas fa-images"></i> Images de la carte (<?= !empty($carteImages) ? count($carteImages) : '0' ?>)</h2>
                <button type="button" class="accordion-toggle" data-target="images-list-content">
                    <i class="fas fa-chevron-down"></i>
                </button>
            </div>

            <div id="images-list-content" class="accordion-content expanded">
                <?php if (empty($carteImages)): ?>
                    <p class="no-images">Aucune image téléchargée. Ajoutez vos premières images ci-dessus.</p>
                <?php else: ?>
                    <!-- Grille d'images avec système de réorganisation -->
                    <div class="images-grid" id="sortable-images">
                        <?php foreach ($carteImages as $index => $image): ?>
                            <div class="image-card"
                                data-image-id="<?= $image['id'] ?>"
                                draggable="false"> <!-- Initialement false -->

                                <!-- Badge de position en haut à gauche -->
                                <div class="position-badge">
                                    <span class="position-number"><?= $index + 1 ?></span>
                                </div>

                                <!-- Poignée de drag (optionnelle mais recommandée) -->
                                <div class="drag-handle" title="Glisser pour réorganiser" style="display: none;">
                                    <i class="fas fa-grip-vertical"></i>
                                </div>

                                <div class="image-preview-container">
                                    <?php if (strtolower(pathinfo($image['filename'], PATHINFO_EXTENSION)) === 'pdf'): ?>
                                        <div class="pdf-preview">
                                            <i class="fas fa-file-pdf"></i>
                                            <span>PDF</span>
                                        </div>
                                    <?php else: ?>
                                        <img src="/<?= htmlspecialchars($image['filename']) ?>"
                                            alt="<?= htmlspecialchars($image['original_name']) ?>"
                                            class="carte-image-preview lightbox-image"
                                            data-caption="<?= htmlspecialchars($image['original_name']) ?>"
                                            draggable="false"> <!-- Important : empêcher le drag natif de l'image -->
                                    <?php endif; ?>
                                </div>

                                <div class="image-info">
                                    <p class="image-name"><?= htmlspecialchars($image['original_name']) ?></p>
                                    <p class="image-date">Ajouté le <?= date('d/m/Y', strtotime($image['created_at'])) ?></p>
                                </div>

                                <div class="image-actions">
                                    <form method="post" class="inline-form delete-image-form">
                                        <input type="hidden" name="image_id" value="<?= $image['id'] ?>">
                                        <input type="hidden" name="anchor" value="images-list">
                                        <button type="submit" name="delete_image" class="btn danger delete-image-btn"
                                            data-image-id="<?= $image['id'] ?>"
                                            data-image-name="<?= htmlspecialchars($image['original_name']) ?>">
                                            <i class="fas fa-trash"></i> Supprimer
                                        </button>
                                    </form>
                                </div>

                                <!-- Contrôles de réorganisation -->
                                <div class="reorder-controls" style="display: none;">
                                    <!-- Bouton "Monter" -->
                                    <button type="button" class="btn small move-up <?= $index === 0 ? 'hidden' : '' ?>"
                                        data-position="<?= $index + 1 ?>">
                                        <i class="fas fa-arrow-up"></i> Monter
                                    </button>

                                    <!-- Bouton "Descendre" -->
                                    <button type="button" class="btn small move-down <?= $index === count($carteImages) - 1 ? 'hidden' : '' ?>"
                                        data-position="<?= $index + 1 ?>">
                                        <i class="fas fa-arrow-down"></i> Descendre
                                    </button>
                                </div>

                                <!-- Indicateurs de zone de drop (optionnels) -->
                                <div class="drop-zone-indicator top"></div>
                                <div class="drop-zone-indicator bottom"></div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Formulaire de réorganisation -->
                    <form method="post" id="reorder-form" class="reorder-form">
                        <input type="hidden" name="anchor" value="images-list">
                        <input type="hidden" name="new_order" id="new-order-input">
                        <input type="hidden" name="update_image_order" value="1">

                        <div class="reorder-actions">
                            <button type="button" id="start-reorder-btn" class="btn primary">
                                <i class="fas fa-sort"></i> Réorganiser l'ordre d'affichage
                            </button>

                            <div id="reorder-buttons" style="display: none;">
                                <button type="submit" id="save-order-btn" class="btn success">
                                    <i class="fas fa-save"></i> Enregistrer le nouvel ordre
                                </button>
                                <button type="button" id="cancel-order-btn" class="btn danger">
                                    <i class="fas fa-times"></i> Annuler
                                </button>
                            </div>
                        </div>

                        <div id="reorder-instructions" class="reorder-instructions" style="display: none;">
                            <p>
                                <i class="fas fa-info-circle"></i>
                                Cliquez-glissez une image pour la déplacer, OU utilisez les boutons
                                <strong>"Monter"</strong> et <strong>"Descendre"</strong> sous chaque image.
                                Cliquez sur <strong>"Enregistrer"</strong> pour valider.
                            </p>
                            <p class="drag-hint">
                                <i class="fas fa-hand-pointer"></i>
                                <small>Astuce : Maintenez le clic sur une image et glissez-la pour changer sa position.</small>
                            </p>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Définition des étapes du tour guidé pour cette page -->
<script>
// Fonction pour détecter si des catégories existent
function hasCategories() {
    return document.querySelectorAll('.category-block').length > 0;
}

// Fonction pour détecter si des plats existent
function hasDishes() {
    return document.querySelectorAll('.plat-card').length > 0;
}

// Étapes du tour pour le mode éditable avec données existantes
const editableTourSteps = [
    {
        element: '#mode-selector',
        title: 'Mode Éditable',
        content: '<p>Vous avez choisi le mode Éditable pour créer et gérer vos catégories et plats en détail.</p><p><small>Pour découvrir le mode Images, sélectionnez-le ci-dessus puis relancez le tour.</small></p>',
        beforeShow: function() {
            const accordion = document.querySelector('#mode-selector-content');
            const toggle = document.querySelector('[data-target="mode-selector-content"]');
            if (accordion && accordion.classList.contains('collapsed')) {
                toggle.click();
            }
        }
    },
    {
        element: '#quick-add-categories',
        title: 'Ajout rapide de catégories',
        content: '<p>Gagnez du temps en ajoutant plusieurs catégories d\'un coup avec leurs images.</p><p>Parfait pour créer rapidement la structure de votre carte (Entrées, Plats, Desserts, etc.)</p>'
    },
    {
        element: '#quick-add-dishes',
        title: 'Ajout rapide de plats',
        content: '<p>Ajoutez plusieurs plats en une seule fois dans une catégorie.</p><p>Vous pouvez définir le nom, prix, description, image et allergènes pour chaque plat.</p>'
    },
    {
        element: '.categories-grid',
        title: 'Vos catégories',
        content: '<p>Chaque catégorie peut être modifiée, supprimée ou réorganisée.</p><p>Utilisez l\'infobulle <i class="fas fa-info-circle"></i> pour comprendre l\'ordre d\'affichage.</p>'
    },
    {
        element: '.category-order-control',
        title: 'Ordre d\'affichage',
        content: '<p>Définissez l\'ordre dans lequel vos catégories apparaissent sur votre site.</p><p>Les catégories avec un numéro plus petit s\'affichent en premier.</p>'
    },
    {
        element: '.category-accordion-controls',
        title: 'Contrôles d\'accordéon',
        content: '<p>Utilisez ces boutons pour ouvrir ou fermer tous les accordéons d\'une catégorie en un clic.</p><p>Pratique pour naviguer rapidement dans votre carte.</p>'
    }
];

// Étapes du tour pour le mode éditable sans données (création)
const editableCreationSteps = [
    {
        element: '#mode-selector',
        title: 'Mode Éditable',
        content: '<p>Vous avez choisi le mode Éditable pour créer et gérer vos catégories et plats en détail.</p><p><small>Pour découvrir le mode Images, sélectionnez-le ci-dessus puis relancez le tour.</small></p>',
        beforeShow: function() {
            const accordion = document.querySelector('#mode-selector-content');
            const toggle = document.querySelector('[data-target="mode-selector-content"]');
            if (accordion && accordion.classList.contains('collapsed')) {
                toggle.click();
            }
        }
    },
    {
        element: '#quick-add-categories',
        title: 'Commencez par créer des catégories',
        content: '<p>Première étape : créez les catégories de votre carte.</p><p>Exemples : Entrées, Plats, Desserts, Boissons...</p><p>Cliquez sur "Ajouter des catégories" pour commencer.</p>',
        beforeShow: function() {
            const accordion = document.querySelector('#quick-add-categories-content');
            const toggle = document.querySelector('[data-target="quick-add-categories-content"]');
            if (accordion && accordion.classList.contains('collapsed')) {
                toggle.click();
            }
        }
    },
    {
        element: '#quick-add-dishes',
        title: 'Ajoutez vos plats',
        content: '<p>Une fois vos catégories créées, ajoutez-y vos plats.</p><p>Définissez le nom, prix, description, image et allergènes pour chaque plat.</p>',
        beforeShow: function() {
            const accordion = document.querySelector('#quick-add-dishes-content');
            const toggle = document.querySelector('[data-target="quick-add-dishes-content"]');
            if (accordion && accordion.classList.contains('collapsed')) {
                toggle.click();
            }
        }
    },
    {
        element: '.categories-grid',
        title: 'Organisez votre carte',
        content: '<p>Vos catégories et plats apparaîtront ici une fois créés.</p><p>Vous pourrez ensuite les modifier, supprimer ou réorganiser.</p>'
    }
];

// Étapes du tour pour le mode images
const imagesTourSteps = [
    {
        element: '#mode-selector',
        title: 'Mode Images',
        content: '<p>Vous avez choisi le mode Images pour télécharger simplement des photos de votre carte existante.</p><p><small>Pour découvrir le mode Éditable, sélectionnez-le ci-dessus puis relancez le tour.</small></p>',
        beforeShow: function() {
            const accordion = document.querySelector('#mode-selector-content');
            const toggle = document.querySelector('[data-target="mode-selector-content"]');
            if (accordion && accordion.classList.contains('collapsed')) {
                toggle.click();
            }
        }
    },
    {
        element: '#upload-images-content',
        title: 'Télécharger vos images',
        content: '<p>Glissez-déposez vos images de carte ou cliquez pour les sélectionner.</p><p>Formats acceptés : JPG, PNG, PDF, WebP (max 5MB par fichier)</p>',
        beforeShow: function() {
            const accordion = document.querySelector('#upload-images-content');
            const toggle = document.querySelector('[data-target="upload-images-content"]');
            if (accordion && accordion.classList.contains('collapsed')) {
                toggle.click();
            }
        }
    },
    {
        element: '#images-list-content',
        title: 'Réorganiser vos images',
        content: '<p>Changez l\'ordre d\'affichage de vos images en les glissant-déposant.</p><p>Vous pouvez aussi utiliser les boutons "Monter" et "Descendre".</p>',
        beforeShow: function() {
            const accordion = document.querySelector('#images-list-content');
            const toggle = document.querySelector('[data-target="images-list-content"]');
            if (accordion && accordion.classList.contains('collapsed')) {
                toggle.click();
            }
        }
    }
];

// Fonction pour déterminer les étapes du tour
function getTourSteps() {
    const currentMode = '<?= $currentMode ?>';
    
    if (currentMode === 'images') {
        return imagesTourSteps;
    } else if (currentMode === 'editable') {
        // Mode éditable : vérifier s'il y a des données
        if (hasCategories()) {
            return editableTourSteps;
        } else {
            return editableCreationSteps;
        }
    }
    
    return [];
}

// Variable globale pour les étapes du tour
let tourSteps = getTourSteps();

// Fonction appelée au démarrage du tour pour fermer tous les accordéons
window.tourBeforeStart = function() {
    const collapseAllBtn = document.querySelector('#collapse-all-accordions');
    if (collapseAllBtn) {
        collapseAllBtn.click();
    }
};
</script>

<?php
require __DIR__ . '/../partials/footer.php';
?>