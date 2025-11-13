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
                <h2 class="category-title"><?= htmlspecialchars($cat['name']) ?></h2>
                
                <?php $plats = $cat['plats'] ?? []; ?>
                <?php if (!empty($plats)): ?>
                    <div class="dishes-table">
                        <?php foreach ($plats as $plat): ?>
                            <div class="dish-row">
                                <span class="dish-name"><?= htmlspecialchars($plat['name']) ?></span>
                                <span class="dish-price"><?= htmlspecialchars($plat['price']) ?> €</span>
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
// Inclusion du footer commun
require __DIR__ . '/../partials/footer.php';