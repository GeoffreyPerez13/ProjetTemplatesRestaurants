<?php if (!empty($dailyMenus)): ?>
<section id="menus-du-jour" class="daily-menus-display">
    <div class="container">
        <h2><i class="fas fa-star"></i> Nos Menus & Formules</h2>
        <div class="daily-menus-grid">
            <?php foreach ($dailyMenus as $dm): ?>
                <div class="daily-menu-display-card">
                    <div class="daily-menu-display-header">
                        <h3><?= htmlspecialchars($dm['title']) ?></h3>
                        <?php if ($dm['price']): ?>
                            <span class="daily-menu-display-price"><?= number_format($dm['price'], 2, ',', '') ?> €</span>
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($dm['description'])): ?>
                        <p class="daily-menu-display-desc"><?= htmlspecialchars($dm['description']) ?></p>
                    <?php endif; ?>
                    <ul class="daily-menu-display-items">
                        <?php foreach ($dm['items'] as $item): ?>
                            <li>
                                <?php if (!empty($item['label'])): ?>
                                    <span class="dm-item-label"><?= htmlspecialchars($item['label']) ?></span>
                                <?php endif; ?>
                                <span class="dm-item-value"><?= htmlspecialchars($item['value']) ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<section id="carte">
    <div class="container">
        <h2>Notre Carte</h2>

        <?php if ($lastUpdated): ?>
            <p class="last-updated">Dernière mise à jour de la carte : <strong><?= htmlspecialchars($lastUpdated) ?></strong>.</p>
        <?php endif; ?>

        <?php if ($carteMode === 'editable'): ?>
            <div class="categories">
                <?php foreach ($categories as $category): ?>
                    <div class="category">
                        <div class="category-header">
                            <?php if (!empty($category['image_url'])): ?>
                                <img src="<?= htmlspecialchars($category['image_url']) ?>" alt="<?= htmlspecialchars($category['name']) ?> — <?= htmlspecialchars($restaurant->name) ?>" loading="lazy" class="lightbox-image">
                            <?php endif; ?>
                            <h3><?= htmlspecialchars($category['name']) ?></h3>
                        </div>
                        <?php if (!empty($category['plats'])): ?>
                            <div class="plats-grid">
                                <?php foreach ($category['plats'] as $plat): ?>
                                    <div class="plat-card">
                                        <?php if (!empty($plat['image_url'])): ?>
                                            <img src="<?= htmlspecialchars($plat['image_url']) ?>" alt="<?= htmlspecialchars($plat['name']) ?> — <?= htmlspecialchars($category['name']) ?>" loading="lazy" class="lightbox-image">
                                        <?php endif; ?>
                                        <div class="plat-info">
                                            <div class="plat-header">
                                                <h4><?= htmlspecialchars($plat['name']) ?></h4>
                                                <span class="plat-price"><?= htmlspecialchars($plat['price']) ?> €</span>
                                            </div>
                                            <p><?= htmlspecialchars($plat['description']) ?></p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p>Aucun plat dans cette catégorie.</p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>

        <?php else: ?>
            <?php if (!empty($cardImages)): ?>
                <div class="images-grid">
                    <?php foreach ($cardImages as $image): ?>
                        <?php 
                        // Ignorer les entrées sans URL ou sans nom de fichier
                        if (empty($image['url']) || empty($image['filename'])) {
                            continue;
                        }
                        $isPdf = strtolower(pathinfo($image['filename'], PATHINFO_EXTENSION)) === 'pdf';
                        $displayName = $image['original_name'] ?? $image['filename'];
                        ?>
                        <div class="image-card">
                            <?php if ($isPdf): ?>
                                <div class="pdf-preview"><i class="fas fa-file-pdf"></i></div>
                            <?php else: ?>
                                <img src="<?= htmlspecialchars($image['url']) ?>" alt="Carte <?= htmlspecialchars($restaurant->name) ?> — <?= htmlspecialchars($displayName) ?>" loading="lazy" class="lightbox-image">
                            <?php endif; ?>
                            <div class="image-info">
                                <p><?= htmlspecialchars($displayName) ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p>Aucune image de carte disponible.</p>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>
