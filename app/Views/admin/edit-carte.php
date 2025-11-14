<?php
$title = "Modifier la carte";
$scripts = ["js/edit-carte.js"];
require __DIR__ . '/../partials/header.php';
?>

<a class="btn-back" href="?page=dashboard">← Retour au dashboard</a>

<?php if (!empty($message)): ?>
    <p class="success"><?= htmlspecialchars($message) ?></p>
<?php endif; ?>

<?php if (!empty($error_message)): ?>
    <p class="error"><?= htmlspecialchars($error_message) ?></p>
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
                        <img src="/<?= htmlspecialchars($cat['image']) ?>" alt="<?= htmlspecialchars($cat['name']) ?>" class="image-preview">
                    </div>
                <?php endif; ?>

                <strong><?= htmlspecialchars($cat['name']) ?></strong>

                <form method="post" class="inline-form">
                    <input type="hidden" name="delete_category" value="<?= $cat['id'] ?>">
                    <button type="submit" class="btn danger">Supprimer catégorie</button>
                </form>

                <h3>Ajouter un plat</h3>
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

                <?php $plats = $cat['plats'] ?? []; ?>
                <?php if ($plats): ?>
                    <ul class="dish-list">
                        <h3>Modifier les plats</h3>
                        <?php foreach ($plats as $plat): ?>
                            <li>
                                <div class="dish-edit-container">
                                    <form method="post" class="inline-form edit-form" enctype="multipart/form-data">
                                        <input type="hidden" name="dish_id" value="<?= $plat['id'] ?>">
                                        <input type="hidden" name="current_category_id" value="<?= $cat['id'] ?>">

                                        <input type="text" name="dish_name" value="<?= htmlspecialchars($plat['name']) ?>"
                                            placeholder="Nom du plat" required>
                                        <input type="text" name="dish_price" value="<?= htmlspecialchars($plat['price']) ?>"
                                            placeholder="Prix" required class="price-input">

                                        <textarea name="dish_description" placeholder="Description du plat (optionnel)"
                                            class="description-input"><?= htmlspecialchars($plat['description'] ?? '') ?></textarea>

                                        <!-- Affichage de l'image actuelle -->
                                        <?php if (!empty($plat['image'])): ?>
                                            <div class="current-image">
                                                <label>Image actuelle :</label>
                                                <img src="/<?= htmlspecialchars($plat['image']) ?>" alt="<?= htmlspecialchars($plat['name']) ?>" class="image-preview">
                                            </div>
                                        <?php endif; ?>

                                        <div class="image-upload-container">
                                            <label for="edit_dish_image_<?= $plat['id'] ?>">
                                                <?= empty($plat['image']) ? 'Ajouter une image' : 'Changer l\'image' ?> :
                                            </label>
                                            <input type="file" name="dish_image" id="edit_dish_image_<?= $plat['id'] ?>"
                                                accept="image/jpeg, image/png, image/gif, image/webp">
                                            <small>Formats acceptés: JPEG, PNG, GIF, WebP (max 2MB)</small>
                                        </div>

                                        <button type="submit" name="edit_dish" class="btn">Modifier</button>
                                    </form>
                                    <form method="post" class="inline-form delete-form">
                                        <input type="hidden" name="delete_dish" value="<?= $plat['id'] ?>">
                                        <button type="submit" class="btn danger">Supprimer</button>
                                    </form>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p class="no-dishes">Aucun plat pour cette catégorie.</p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>

</div>

<?php
require __DIR__ . '/../partials/footer.php';
