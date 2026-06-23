<?php
/** @var array|null $banner */
/** @var object $restaurant */
?>
<section id="accueil">
    <?php
    $isDemoBanner = $banner && strpos($banner['filename'] ?? '', 'banner_demo_') === 0;
    ?>
    <?php if ($banner && !$isDemoBanner): ?>
        <div class="banner" style="background-image: url('<?= htmlspecialchars($banner['url']) ?>');">
            <div class="banner-content">
                <h2><?= htmlspecialchars($restaurant->name) ?></h2>
                <?php if (!empty($banner['text'])): ?>
                    <div class="banner-text"><?= nl2br(htmlspecialchars($banner['text'])) ?></div>
                <?php endif; ?>
            </div>
        </div>
    <?php else: ?>
        <div class="banner banner-fallback">
            <div class="banner-content">
                <h2><?= htmlspecialchars($restaurant->name) ?></h2>
            </div>
        </div>
    <?php endif; ?>
</section>
