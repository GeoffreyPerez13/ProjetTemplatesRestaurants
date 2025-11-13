<?php
$title = "Modifier la carte";
$scripts = ["js/edit-carte.js"];
require __DIR__ . '/../partials/header.php';
?>

<a class="btn-back" href="?page=dashboard">← Retour au dashboard</a>

<?php if (!empty($message)): ?>
    <p class="success"><?= htmlspecialchars($message) ?></p>
<?php endif; ?>

<div class="edit-carte-container">

    <!-- Bloc Ajouter une catégorie -->
    <div class="new-category-block">
        <form method="post">
            <h2>Modifier la carte</h2>
            <h3>Ajouter une catégorie</h3>
            <input type="text" name="new_category" placeholder="Nom de la catégorie" required>
            <button type="submit" class="btn">Ajouter</button>
        </form>
    </div>

    <!-- Bloc catégories existantes -->
    <div class="categories-grid">
        <?php foreach ($categories as $cat): ?>
            <div class="category-block">
                <strong><?= htmlspecialchars($cat['name']) ?></strong>

                <form method="post" class="inline-form">
                    <input type="hidden" name="delete_category" value="<?= $cat['id'] ?>">
                    <button type="submit" class="btn danger">Supprimer catégorie</button>
                </form>

                <h3>Ajouter</h3>
                <form method="post" class="new-dish-form">
                    <input type="hidden" name="category_id" value="<?= $cat['id'] ?>">
                    <div class="form-row">
                        <input type="text" name="dish_name" placeholder="Nom du plat" required>
                        <div class="input-euro">
                            <input type="number" step="0.01" name="dish_price" placeholder="Prix" required>
                            <span class="euro-symbol">€</span>
                        </div>
                    </div>
                    <textarea name="dish_description" placeholder="Description du plat (optionnel)"></textarea>
                    <button type="submit" name="new_dish" class="btn">Ajouter plat</button>
                </form>

                <?php $plats = $cat['plats'] ?? []; ?>
                <?php if ($plats): ?>
                    <ul class="dish-list">
                        <h3>Modifier</h3>
                        <?php foreach ($plats as $plat): ?>
                            <li>
                                <div class="dish-edit-container">
                                    <form method="post" class="inline-form edit-form">
                                        <input type="hidden" name="dish_id" value="<?= $plat['id'] ?>">
                                        <input type="text" name="dish_name" value="<?= htmlspecialchars($plat['name']) ?>"
                                            placeholder="Nom du plat" required>
                                        <input type="text" name="dish_price" value="<?= htmlspecialchars($plat['price']) ?>"
                                            placeholder="Prix" required class="price-input">
                                        <textarea name="dish_description" placeholder="Description du plat (optionnel)"
                                            class="description-input"><?= htmlspecialchars($plat['description'] ?? '') ?></textarea>
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
                    <p>Aucun plat pour cette catégorie.</p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>

</div>

<?php
require __DIR__ . '/../partials/footer.php';
