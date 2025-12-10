<?php
$title = "Modifier la carte";
$scripts = ["js/sections/edit-card/edit-card.js", "js/sections/edit-card/images-mode.js", "js/sections/edit-card/drag-and-drop.js", "js/effects/accordion.js", "js/effects/lightbox.js"];

// Déterminer l'ancre à utiliser pour la redirection
$anchor = $_GET['anchor'] ?? '';

require __DIR__ . '/../partials/header.php';
?>

<a class="btn-back" href="?page=dashboard">Retour au dashboard</a>

<!-- Boutons de contrôle généraux pour tous les accordéons -->
<div class="global-accordion-controls">
    <button type="button" id="expand-all-accordions" class="btn">
        <i class="fas fa-expand-alt"></i> Tout ouvrir
    </button>
    <button type="button" id="collapse-all-accordions" class="btn">
        <i class="fas fa-compress-alt"></i> Tout fermer
    </button>
</div>

<?php if (!empty($message)): ?>
    <p class="message-success"><?= htmlspecialchars($message) ?></p>
<?php endif; ?>

<?php if (!empty($error_message)): ?>
    <p class="message-error"><?= htmlspecialchars($error_message) ?></p>
<?php endif; ?>

<!-- Sélecteur de mode (accordéon) -->
<div class="accordion-section mode-selector-accordion" id="mode-selector">
    <div class="accordion-header">
        <h2>Mode d'affichage de la carte</h2>
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

        <!-- Bloc Ajouter une catégorie (accordéon) -->
        <div class="accordion-section new-category-accordion" id="new-category">
            <div class="accordion-header">
                <h2>Ajouter une nouvelle catégorie</h2>
                <button type="button" class="accordion-toggle" data-target="new-category-content">
                    <i class="fas fa-chevron-down"></i>
                </button>
            </div>

            <div id="new-category-content" class="accordion-content expanded">
                <form method="post" enctype="multipart/form-data" class="new-category-form">
                    <input type="hidden" name="anchor" value="new-category">

                    <input type="text" name="new_category" placeholder="Nom de la catégorie" required>

                    <div class="image-upload-container">
                        <label for="category_image">Image de la catégorie (optionnel) :</label>
                        <input type="file" name="category_image" id="category_image" accept="image/jpeg, image/png, image/gif, image/webp">
                        <small>Formats acceptés: JPEG, PNG, GIF, WebP (max 2MB)</small>
                    </div>

                    <button type="submit" class="btn success">Ajouter la catégorie</button>
                </form>
            </div>
        </div>

        <!-- Bloc catégories existantes -->
        <div class="categories-grid">
            <?php foreach ($categories as $cat): ?>
                <div class="category-block" id="category-<?= $cat['id'] ?>">
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

                    <div class="category-header">
                        <strong><?= htmlspecialchars($cat['name']) ?></strong>

                        <!-- Contrôles d'accordéon pour cette catégorie -->
                        <div class="category-accordion-controls">
                            <button type="button" class="btn small expand-category" data-category-id="<?= $cat['id'] ?>">
                                <i class="fas fa-expand-alt"></i> Tout ouvrir
                            </button>
                            <button type="button" class="btn small collapse-category" data-category-id="<?= $cat['id'] ?>">
                                <i class="fas fa-compress-alt"></i> Tout fermer
                            </button>
                        </div>
                    </div>

                    <!-- Section Modifier la catégorie (accordéon) -->
                    <div class="accordion-section">
                        <div class="accordion-header">
                            <h3>Modifier la catégorie</h3>
                            <button type="button" class="accordion-toggle" data-target="edit-category-<?= $cat['id'] ?>">
                                <i class="fas fa-chevron-down"></i>
                            </button>
                        </div>

                        <div id="edit-category-<?= $cat['id'] ?>" class="accordion-content expanded">
                            <form method="post" class="edit-category-form" enctype="multipart/form-data">
                                <input type="hidden" name="category_id" value="<?= $cat['id'] ?>">
                                <input type="hidden" name="anchor" value="category-<?= $cat['id'] ?>">

                                <input type="text" name="edit_category_name" value="<?= htmlspecialchars($cat['name']) ?>"
                                    placeholder="Nom de la catégorie" required>

                                <div class="image-upload-container">
                                    <label for="edit_category_image_<?= $cat['id'] ?>">
                                        <?= empty($cat['image']) ? 'Ajouter une image' : 'Changer l\'image' ?> (optionnel) :
                                    </label>
                                    <input type="file" name="edit_category_image" id="edit_category_image_<?= $cat['id'] ?>"
                                        accept="image/jpeg, image/png, image/gif, image/webp">
                                    <small>Formats acceptés: JPEG, PNG, GIF, WebP (max 2MB)</small>
                                </div>

                                <div class="category-buttons">
                                    <button type="submit" name="edit_category" class="btn">Modifier catégorie</button>

                                    <?php if (!empty($cat['image'])): ?>
                                        <button type="submit" name="remove_category_image" value="<?= $cat['id'] ?>"
                                            class="btn danger">
                                            Supprimer l'image
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </form>

                            <!-- Formulaire pour supprimer la catégorie -->
                            <form method="post" class="inline-form">
                                <input type="hidden" name="delete_category" value="<?= $cat['id'] ?>">
                                <input type="hidden" name="anchor" value="categories-grid">
                                <button type="submit" class="btn danger">Supprimer catégorie</button>
                            </form>
                        </div>
                    </div>

                    <!-- Section Ajouter un plat (accordéon) -->
                    <div class="accordion-section">
                        <div class="accordion-header">
                            <h3>Ajouter un plat</h3>
                            <button type="button" class="accordion-toggle" data-target="add-dish-<?= $cat['id'] ?>">
                                <i class="fas fa-chevron-down"></i>
                            </button>
                        </div>

                        <div id="add-dish-<?= $cat['id'] ?>" class="accordion-content expanded">
                            <form method="post" class="new-dish-form" enctype="multipart/form-data">
                                <input type="hidden" name="category_id" value="<?= $cat['id'] ?>">
                                <input type="hidden" name="anchor" value="category-<?= $cat['id'] ?>">

                                <div class="form-row">
                                    <input type="text" name="dish_name" placeholder="Nom du plat" required>
                                    <div class="input-euro">
                                        <input type="number" step="0.01" name="dish_price" placeholder="Prix" required>
                                        <span class="euro-symbol">€</span>
                                    </div>
                                </div>

                                <textarea name="dish_description" placeholder="Description du plat (optionnel)"></textarea>

                                <div class="image-upload-container">
                                    <label for="dish_image_<?= $cat['id'] ?>">Image du plat (optionnel) :</label>
                                    <input type="file" name="dish_image" id="dish_image_<?= $cat['id'] ?>" accept="image/jpeg, image/png, image/gif, image/webp">
                                    <small>Formats acceptés: JPEG, PNG, GIF, WebP (max 2MB)</small>
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
                                <h3>Modifier les plats (<?= count($plats) ?>)</h3>
                                <button type="button" class="accordion-toggle" data-target="edit-dishes-<?= $cat['id'] ?>">
                                    <i class="fas fa-chevron-down"></i>
                                </button>
                            </div>

                            <div id="edit-dishes-<?= $cat['id'] ?>" class="accordion-content expanded">
                                <ul class="dish-list">
                                    <?php foreach ($plats as $plat): ?>
                                        <li class="dish-accordion-item">
                                            <!-- En-tête du plat (accordéon) -->
                                            <div class="dish-accordion-header">
                                                <h4><?= htmlspecialchars($plat['name']) ?> - <?= htmlspecialchars($plat['price']) ?>€</h4>
                                                <button type="button" class="dish-accordion-toggle"
                                                    data-target="dish-<?= $plat['id'] ?>"
                                                    data-category="<?= $cat['id'] ?>">
                                                    <i class="fas fa-chevron-down"></i>
                                                </button>
                                            </div>

                                            <!-- Contenu du plat -->
                                            <div id="dish-<?= $plat['id'] ?>" class="dish-accordion-content" data-category="<?= $cat['id'] ?>">
                                                <div class="dish-edit-container">
                                                    <form method="post" class="inline-form edit-form" enctype="multipart/form-data">
                                                        <input type="hidden" name="dish_id" value="<?= $plat['id'] ?>">
                                                        <input type="hidden" name="current_category_id" value="<?= $cat['id'] ?>">
                                                        <input type="hidden" name="anchor" value="category-<?= $cat['id'] ?>">

                                                        <!-- Nom + Prix -->
                                                        <div class="dish-name-price-row">
                                                            <input type="text" name="dish_name" value="<?= htmlspecialchars($plat['name']) ?>"
                                                                placeholder="Nom du plat" required>

                                                            <div class="input-euro">
                                                                <input type="text" name="dish_price" value="<?= htmlspecialchars($plat['price']) ?>"
                                                                    placeholder="Prix" required class="price-input">
                                                                <span class="euro-symbol">€</span>
                                                            </div>
                                                        </div>

                                                        <!-- Description -->
                                                        <textarea name="dish_description" placeholder="Description du plat (optionnel)"
                                                            class="description-input"><?= htmlspecialchars($plat['description'] ?? '') ?></textarea>

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

                                                        <div class="image-upload-container">
                                                            <label for="edit_dish_image_<?= $plat['id'] ?>">
                                                                <?= empty($plat['image']) ? 'Ajouter une image' : 'Changer l\'image' ?> (optionnel) :
                                                            </label>
                                                            <input type="file" name="dish_image" id="edit_dish_image_<?= $plat['id'] ?>"
                                                                accept="image/jpeg, image/png, image/gif, image/webp">
                                                            <small>Formats acceptés: JPEG, PNG, GIF, WebP (max 2MB)</small>
                                                        </div>

                                                        <!-- Boutons pour l'image -->
                                                        <div class="dish-image-buttons-row">
                                                            <button type="submit" name="edit_dish" class="btn">Modifier le plat</button>

                                                            <?php if (!empty($plat['image'])): ?>
                                                                <button type="submit" name="remove_dish_image" value="<?= $plat['id'] ?>"
                                                                    class="dish-remove-image-btn">
                                                                    Supprimer l'image
                                                                </button>
                                                            <?php endif; ?>
                                                        </div>
                                                    </form>

                                                    <!-- Bouton Supprimer le plat -->
                                                    <form method="post" class="inline-form">
                                                        <input type="hidden" name="delete_dish" value="<?= $plat['id'] ?>">
                                                        <input type="hidden" name="anchor" value="category-<?= $cat['id'] ?>">
                                                        <button type="submit" class="btn danger">Supprimer le plat</button>
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
                <h2>Ajouter des images à la carte</h2>
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
                            multiple accept="image/*,.pdf"
                            style="display: none;">
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

                    <button type="submit" name="upload_images" class="btn success" id="uploadButton" disabled>
                        <i class="fas fa-upload"></i> Télécharger les images (<span id="uploadCount">0</span>)
                    </button>

                    <button type="button" class="btn danger" id="clearSelection" style="display: none;">
                        <i class="fas fa-times"></i> Annuler la sélection
                    </button>
                </form>
            </div>
        </div>

        <!-- Liste des images existantes (accordéon) -->
        <div class="accordion-section images-list-accordion" id="images-list">
            <div class="accordion-header">
                <h2>Images de la carte (<?= !empty($carteImages) ? count($carteImages) : '0' ?>)</h2>
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
                            <div class="image-card" data-image-id="<?= $image['id'] ?>">
                                <input type="hidden" name="image_order[]" value="<?= $image['id'] ?>">

                                <div class="image-preview-container">
                                    <?php if (pathinfo($image['filename'], PATHINFO_EXTENSION) === 'pdf'): ?>
                                        <div class="pdf-preview">
                                            <i class="fas fa-file-pdf"></i>
                                            <span>PDF</span>
                                        </div>
                                    <?php else: ?>
                                        <img src="/<?= htmlspecialchars($image['filename']) ?>"
                                            alt="<?= htmlspecialchars($image['original_name']) ?>"
                                            class="carte-image-preview lightbox-image"
                                            data-caption="<?= htmlspecialchars($image['original_name']) ?>">
                                    <?php endif; ?>
                                </div>

                                <div class="image-info">
                                    <p class="image-name"><?= htmlspecialchars($image['original_name']) ?></p>
                                    <p class="image-date">Ajouté le <?= date('d/m/Y', strtotime($image['created_at'])) ?></p>
                                    <p class="image-position">Position: <span class="position-number"><?= $index + 1 ?></span></p>
                                </div>

                                <div class="image-actions">
                                    <form method="post" class="inline-form">
                                        <input type="hidden" name="image_id" value="<?= $image['id'] ?>">
                                        <input type="hidden" name="anchor" value="images-list">
                                        <button type="submit" name="delete_image" class="btn danger">
                                            <i class="fas fa-trash"></i> Supprimer
                                        </button>
                                    </form>
                                </div>

                                <!-- Contrôles de réorganisation (cachés par défaut) -->
                                <div class="reorder-controls" style="display: none;">
                                    <button type="button" class="btn small move-up" <?= $index === 0 ? 'disabled' : '' ?>>
                                        <i class="fas fa-arrow-up"></i> Monter
                                    </button>
                                    <button type="button" class="btn small move-down" <?= $index === count($carteImages) - 1 ? 'disabled' : '' ?>>
                                        <i class="fas fa-arrow-down"></i> Descendre
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Formulaire de réorganisation -->
                    <form method="post" id="reorder-form" class="reorder-form">
                        <input type="hidden" name="anchor" value="images-list">
                        <input type="hidden" name="new_order" id="new-order-input">

                        <div class="reorder-actions">
                            <button type="button" id="start-reorder-btn" class="btn">
                                <i class="fas fa-sort"></i> Réorganiser l'ordre d'affichage
                            </button>

                            <div id="reorder-buttons" style="display: none;">
                                <button type="button" id="save-order-btn" class="btn success">
                                    <i class="fas fa-save"></i> Enregistrer le nouvel ordre
                                </button>
                                <button type="button" id="cancel-order-btn" class="btn danger">
                                    <i class="fas fa-times"></i> Annuler
                                </button>
                            </div>
                        </div>

                        <div id="reorder-instructions" class="reorder-instructions" style="display: none;">
                            <p><i class="fas fa-info-circle"></i> Utilisez les boutons "Monter" et "Descendre" sous chaque image pour réorganiser. Cliquez sur "Enregistrer" pour valider.</p>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php
require __DIR__ . '/../partials/footer.php';
?>