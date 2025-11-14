<?php
$title = "Aperçu de la carte";
require __DIR__ . '/../partials/header.php';
?>

<a class="btn-back" href="?page=dashboard">← Retour au dashboard</a>

<h1>Aperçu de la carte du restaurant</h1>

<?php if (empty($categories)): ?>
    <p class="success">Aucune catégorie pour le moment.</p>
<?php else: ?>
    <div class="carte-preview-grid">
        <?php foreach ($categories as $cat): ?>
            <div class="category-preview">
                <!-- Image de la catégorie -->
                <?php if (!empty($cat['image'])): ?>
                    <img src="/<?= htmlspecialchars($cat['image']) ?>" alt="<?= htmlspecialchars($cat['name']) ?>" class="category-preview-image">
                <?php endif; ?>

                <h2 class="category-title"><?= htmlspecialchars($cat['name']) ?></h2>

                <?php $plats = $cat['plats'] ?? []; ?>
                <?php if (!empty($plats)): ?>
                    <div class="dishes-table">
                        <?php foreach ($plats as $plat): ?>
                            <div class="dish-row">
                                <div class="dish-main-info">
                                    <span class="dish-name"><?= htmlspecialchars($plat['name']) ?></span>
                                    <span class="dish-price"><?= number_format($plat['price'], 2, ',', ' ') ?> €</span>
                                    <!-- Image du plat à droite du prix -->
                                    <?php if (!empty($plat['image'])): ?>
                                        <img src="/<?= htmlspecialchars($plat['image']) ?>" alt="<?= htmlspecialchars($plat['name']) ?>" class="dish-preview-image">
                                    <?php endif; ?>
                                </div>
                                <!-- Description en dessous -->
                                <?php if (!empty($plat['description'])): ?>
                                    <p class="dish-description"><?= htmlspecialchars($plat['description']) ?></p>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="no-dishes">Aucun plat dans cette catégorie.</p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php
require __DIR__ . '/../partials/footer.php';