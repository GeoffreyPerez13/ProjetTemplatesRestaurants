<?php
$title = "Modifier la carte";
$scripts = ["js/sections/edit-carte.js", "js/effects/accordion.js", "js/effects/lightbox.js"];
require __DIR__ . '/../partials/header.php';
?>

<a class="btn-back" href="?page=dashboard">← Retour au dashboard</a>

<?php if (!empty($message)): ?>
    <p class="message-success"><?= htmlspecialchars($message) ?></p>
<?php endif; ?>

<?php if (!empty($error_message)): ?>
    <p class="message-error"><?= htmlspecialchars($error_message) ?></p>
<?php endif; ?>

<div class="edit-carte-container">

    <!-- Bloc Ajouter une catégorie -->
    <div class="new-category-block">
        <form method="post" enctype="multipart/form-data">
            <h2>Modifier la carte</h2>
            <h3>Ajouter une catégorie</h3>
            <input type="text" name="new_category" placeholder="Nom de la catégorie" required>

            <div class="image-upload-container">
                <label for="category_image">Image de la catégorie (optionnel) :</label>
                <input type="file" name="category_image" id="category_image" accept="image/jpeg, image/png, image/gif, image/webp">
                <small>Formats acceptés: JPEG, PNG, GIF, WebP (max 2MB)</small>
            </div>

            <button type="submit" class="btn success">Ajouter</button>
        </form>
    </div>

    <!-- Bloc catégories existantes -->
    <div class="categories-grid">
        <?php foreach ($categories as $cat): ?>
            <div class="category-block">
                <!-- Affichage de l'image de la catégorie -->
                <?php if (!empty($cat['image'])): ?>
                    <div class="current-image">
                        <label>Image actuelle :</label>
                        <img src="/<?= htmlspecialchars($cat['image']) ?>" alt="<?= htmlspecialchars($cat['name']) ?>" class="image-preview">
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
                                                                class="dish-image-preview">
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

<?php
require __DIR__ . '/../partials/footer.php';