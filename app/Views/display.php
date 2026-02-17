<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($restaurant->name) ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/display/display.css">
</head>

<body>
    <?php if (isset($siteOnline) && !$siteOnline): ?>
        <!-- Page de maintenance -->
        <div class="maintenance-container">
            <div class="maintenance-box">
                <i class="fas fa-tools"></i>
                <h1><?= htmlspecialchars($restaurant->name) ?></h1>
                <p>Le site est actuellement en maintenance.<br>Veuillez revenir plus tard.</p>
            </div>
        </div>
    <?php else: ?>
        <!-- Site normal -->
        <header>
            <div class="container header-content">
                <div class="logo-area">
                    <?php if ($logo): ?>
                        <img src="<?= htmlspecialchars($logo['url']) ?>" alt="Logo" class="lightbox-image">
                    <?php endif; ?>
                    <h1><?= htmlspecialchars($restaurant->name) ?></h1>
                </div>
                <nav>
                    <ul>
                        <li><a href="#accueil">Accueil</a></li>
                        <li><a href="#carte">Carte</a></li>
                        <li><a href="#contact">Contact</a></li>
                    </ul>
                </nav>
            </div>
        </header>

        <section id="accueil">
            <?php if ($banner): ?>
                <div class="banner" style="background-image: url('<?= htmlspecialchars($banner['url']) ?>');">
                    <h2><?= htmlspecialchars($restaurant->name) ?></h2>
                </div>
            <?php else: ?>
                <div class="banner" style="background-color: #007bff;">
                    <h2><?= htmlspecialchars($restaurant->name) ?></h2>
                </div>
            <?php endif; ?>
        </section>

        <section id="carte">
            <div class="container">
                <h2>Notre Carte</h2>

                <?php if ($carteMode === 'editable'): ?>
                    <div class="categories">
                        <?php foreach ($categories as $category): ?>
                            <div class="category">
                                <div class="category-header">
                                    <?php if (!empty($category['image_url'])): ?>
                                        <img src="<?= htmlspecialchars($category['image_url']) ?>" alt="<?= htmlspecialchars($category['name']) ?>" class="lightbox-image">
                                    <?php endif; ?>
                                    <h3><?= htmlspecialchars($category['name']) ?></h3>
                                </div>
                                <?php if (!empty($category['plats'])): ?>
                                    <div class="plats-grid">
                                        <?php foreach ($category['plats'] as $plat): ?>
                                            <div class="plat-card">
                                                <?php if (!empty($plat['image_url'])): ?>
                                                    <img src="<?= htmlspecialchars($plat['image_url']) ?>" alt="<?= htmlspecialchars($plat['name']) ?>" class="lightbox-image">
                                                <?php endif; ?>
                                                <div class="plat-info">
                                                    <h4><?= htmlspecialchars($plat['name']) ?></h4>
                                                    <p><?= htmlspecialchars($plat['description']) ?></p>
                                                    <span class="plat-price"><?= htmlspecialchars($plat['price']) ?> €</span>
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
                                <div class="image-card">
                                    <?php if (strtolower(pathinfo($image['filename'], PATHINFO_EXTENSION)) === 'pdf'): ?>
                                        <div class="pdf-preview"><i class="fas fa-file-pdf"></i></div>
                                    <?php else: ?>
                                        <img src="<?= htmlspecialchars($image['url']) ?>" alt="<?= htmlspecialchars($image['original_name']) ?>" class="lightbox-image">
                                    <?php endif; ?>
                                    <div class="image-info">
                                        <p><?= htmlspecialchars($image['original_name']) ?></p>
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

        <footer id="contact">
            <div class="container">
                <h2>Contactez-nous</h2>
                <div class="contact-grid">
                    <?php if ($contact && !empty($contact['telephone'])): ?>
                        <div class="contact-item"><i class="fas fa-phone"></i><span><?= htmlspecialchars($contact['telephone']) ?></span></div>
                    <?php endif; ?>
                    <?php if ($contact && !empty($contact['email'])): ?>
                        <div class="contact-item"><i class="fas fa-envelope"></i><span><?= htmlspecialchars($contact['email']) ?></span></div>
                    <?php endif; ?>
                    <?php if ($contact && !empty($contact['adresse'])): ?>
                        <div class="contact-item"><i class="fas fa-map-marker-alt"></i><span><?= htmlspecialchars($contact['adresse']) ?></span></div>
                    <?php endif; ?>
                    <?php if ($contact && !empty($contact['horaires'])): ?>
                        <div class="contact-item"><i class="fas fa-clock"></i><span><?= htmlspecialchars($contact['horaires']) ?></span></div>
                    <?php endif; ?>
                </div>
            </div>
        </footer>

        <!-- Lightbox pour toutes les images -->
        <div class="lightbox" id="lightbox">
            <button class="lightbox-close">&times;</button>
            <button class="lightbox-prev"><i class="fas fa-chevron-left"></i></button>
            <button class="lightbox-next"><i class="fas fa-chevron-right"></i></button>
            <div class="lightbox-content">
                <img src="" alt="">
            </div>
        </div>

        <script src="/assets/js/display/display.js"></script>
    <?php endif; ?>
</body>

</html>