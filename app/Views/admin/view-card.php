<?php
$title = "Aperçu de la carte";
$scripts = ["js/effects/lightbox.js"];

// Si en mode images et qu'il y a des PDF, ajouter un script PDF viewer optionnel
if ($currentMode === 'images' && !empty($carteImages)) {
    $hasPdf = false;
    foreach ($carteImages as $image) {
        if (pathinfo($image['filename'], PATHINFO_EXTENSION) === 'pdf') {
            $hasPdf = true;
            break;
        }
    }
    if ($hasPdf) {
        $scripts[] = "js/effects/pdf-viewer.js"; // Optionnel
    }
}

require __DIR__ . '/../partials/header.php';
?>

<a class="btn-back" href="?page=dashboard">← Retour au dashboard</a>

<h1>Aperçu de la carte du restaurant</h1>

<?php if ($currentMode === 'editable'): ?>
    <!-- Mode éditable -->
    <?php if (empty($categories)): ?>
        <div class="empty-state">
            <i class="fas fa-utensils"></i>
            <p class="success">Aucune catégorie pour le moment.</p>
            <a href="?page=edit-carte" class="btn primary">
                <i class="fas fa-plus"></i> Créer votre première catégorie
            </a>
        </div>
    <?php else: ?>
        <div class="carte-preview-grid">
            <?php foreach ($categories as $cat): ?>
                <div class="category-preview">
                    <!-- Image de la catégorie -->
                    <?php if (!empty($cat['image'])): ?>
                        <div class="category-image-container">
                            <img src="/<?= htmlspecialchars($cat['image']) ?>" 
                                 alt="<?= htmlspecialchars($cat['name']) ?>" 
                                 class="category-preview-image lightbox-image"
                                 data-caption="<?= htmlspecialchars($cat['name']) ?>">
                        </div>
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
                                            <div class="dish-image-container">
                                                <img src="/<?= htmlspecialchars($plat['image']) ?>" 
                                                     alt="<?= htmlspecialchars($plat['name']) ?> - <?= number_format($plat['price'], 2, ',', ' ') ?> €" 
                                                     class="dish-preview-image lightbox-image"
                                                     data-caption="<?= htmlspecialchars($plat['name']) ?> - <?= number_format($plat['price'], 2, ',', ' ') ?> €">
                                            </div>
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
                        <div class="empty-dishes">
                            <p class="no-dishes">Aucun plat dans cette catégorie.</p>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

<?php else: ?>
    <!-- Mode Images -->
    <div class="carte-images-container">
        <div class="mode-indicator">
            <i class="fas fa-images"></i>
            <span>Mode Images activé</span>
        </div>
        
        <?php if (empty($carteImages)): ?>
            <div class="empty-state">
                <i class="fas fa-image"></i>
                <p class="no-images">Aucune image de carte disponible.</p>
                <a href="?page=edit-card" class="btn primary">
                    <i class="fas fa-upload"></i> Ajouter des images
                </a>
            </div>
        <?php else: ?>
            <div class="images-info">
                <p><?= count($carteImages) ?> image(s) disponible(s)</p>
                <?php 
                $pdfCount = 0;
                $imageCount = 0;
                foreach ($carteImages as $image) {
                    if (pathinfo($image['filename'], PATHINFO_EXTENSION) === 'pdf') {
                        $pdfCount++;
                    } else {
                        $imageCount++;
                    }
                }
                ?>
                <?php if ($pdfCount > 0): ?>
                    <p class="file-types">
                        <i class="fas fa-file-pdf"></i> <?= $pdfCount ?> PDF
                        <?php if ($imageCount > 0): ?>
                            &nbsp;|&nbsp;
                            <i class="fas fa-image"></i> <?= $imageCount ?> Images
                        <?php endif; ?>
                    </p>
                <?php endif; ?>
            </div>
            
            <div class="carte-images-gallery">
                <?php foreach ($carteImages as $image): ?>
                    <div class="carte-image-item" data-image-type="<?= pathinfo($image['filename'], PATHINFO_EXTENSION) === 'pdf' ? 'pdf' : 'image' ?>">
                        <?php if (pathinfo($image['filename'], PATHINFO_EXTENSION) === 'pdf'): ?>
                            <div class="pdf-viewer">
                                <div class="pdf-header">
                                    <i class="fas fa-file-pdf"></i>
                                    <span>Document PDF</span>
                                </div>
                                <embed src="/<?= htmlspecialchars($image['filename']) ?>#toolbar=0&navpanes=0&scrollbar=0" 
                                       type="application/pdf" 
                                       width="100%" 
                                       height="500px">
                                <div class="pdf-actions">
                                    <a href="/<?= htmlspecialchars($image['filename']) ?>" 
                                       target="_blank" 
                                       class="btn small">
                                        <i class="fas fa-expand-alt"></i> Ouvrir en plein écran
                                    </a>
                                    <a href="/<?= htmlspecialchars($image['filename']) ?>" 
                                       download 
                                       class="btn small success">
                                        <i class="fas fa-download"></i> Télécharger
                                    </a>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="image-container">
                                <img src="/<?= htmlspecialchars($image['filename']) ?>" 
                                     alt="<?= htmlspecialchars($image['original_name']) ?>"
                                     class="carte-full-image lightbox-image"
                                     data-caption="<?= htmlspecialchars($image['original_name']) ?>"
                                     loading="lazy">
                                <div class="image-overlay">
                                    <button class="btn-view-full" 
                                            data-src="/<?= htmlspecialchars($image['filename']) ?>"
                                            data-caption="<?= htmlspecialchars($image['original_name']) ?>">
                                        <i class="fas fa-search-plus"></i> Agrandir
                                    </button>
                                </div>
                            </div>
                        <?php endif; ?>
                        <div class="image-info">
                            <p class="image-caption">
                                <strong><?= htmlspecialchars($image['original_name']) ?></strong>
                            </p>
                            <p class="image-meta">
                                <small>
                                    <i class="far fa-calendar"></i> Ajouté le <?= date('d/m/Y', strtotime($image['created_at'])) ?>
                                    <?php 
                                    $extension = pathinfo($image['filename'], PATHINFO_EXTENSION);
                                    $fileTypes = [
                                        'pdf' => '<i class="fas fa-file-pdf"></i> PDF',
                                        'jpg' => '<i class="fas fa-image"></i> JPG',
                                        'jpeg' => '<i class="fas fa-image"></i> JPEG',
                                        'png' => '<i class="fas fa-image"></i> PNG',
                                        'gif' => '<i class="fas fa-image"></i> GIF',
                                        'webp' => '<i class="fas fa-image"></i> WebP'
                                    ];
                                    if (isset($fileTypes[$extension])): 
                                    ?>
                                        &nbsp;|&nbsp; <?= $fileTypes[$extension] ?>
                                    <?php endif; ?>
                                </small>
                            </p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="viewer-controls">
                <p class="viewer-note">
                    <i class="fas fa-info-circle"></i> 
                    Cliquez sur une image pour l'agrandir. Les PDF peuvent être visualisés directement ou téléchargés.
                </p>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php
require __DIR__ . '/../partials/footer.php';