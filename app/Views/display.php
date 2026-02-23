<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($restaurant->name) ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
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
        <!-- Bannière de consentement aux cookies -->
        <div id="cookie-banner" class="cookie-banner">
            <div class="cookie-text">
                <p>Nous utilisons des cookies pour améliorer votre expérience sur notre site. En poursuivant votre navigation, vous acceptez notre politique de cookies.</p>
            </div>
            <div class="cookie-buttons">
                <button id="accept-all-cookies" class="btn-cookie accept">Accepter tous</button>
                <button id="open-cookie-preferences" class="btn-cookie preferences">Choisir mes préférences</button>
            </div>
        </div>

        <!-- Modale de préférences des cookies -->
        <div id="cookie-modal" class="cookie-modal">
            <div class="modal-content">
                <span class="close-modal">&times;</span>
                <h3>Préférences des cookies</h3>
                <p>Personnalisez votre consentement.</p>
                <div class="cookie-option">
                    <label>
                        <input type="checkbox" id="cookies-necessary" checked disabled>
                        Cookies nécessaires (obligatoires)
                    </label>
                    <p class="option-desc">Ces cookies sont indispensables au fonctionnement du site.</p>
                </div>
                <div class="cookie-option">
                    <label>
                        <input type="checkbox" id="cookies-analytics">
                        Cookies analytiques
                    </label>
                    <p class="option-desc">Nous aident à améliorer notre site en collectant des informations anonymes.</p>
                </div>
                <div class="cookie-option">
                    <label>
                        <input type="checkbox" id="cookies-marketing">
                        Cookies marketing
                    </label>
                    <p class="option-desc">Utilisés pour vous proposer des publicités adaptées.</p>
                </div>
                <div class="modal-buttons">
                    <button id="save-cookie-preferences" class="btn-cookie save">Enregistrer mes choix</button>
                    <button id="accept-all-from-modal" class="btn-cookie accept">Tout accepter</button>
                </div>
            </div>
        </div>

        <!-- Site normal -->
        <header>
            <div class="container header-content">
                <div class="logo-area">
                    <?php if ($logo): ?>
                        <img src="<?= htmlspecialchars($logo['url']) ?>" alt="Logo" class="lightbox-image">
                    <?php endif; ?>
                    <h1><?= htmlspecialchars($restaurant->name) ?></h1>
                </div>
                <button class="hamburger" id="hamburger" aria-label="Menu">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
                <nav class="nav-menu" id="nav-menu">
                    <ul>
                        <li><a href="#accueil">Accueil</a></li>
                        <li><a href="#carte">Carte</a></li>
                        <li><a href="#services">Services</a></li>
                        <li><a href="#contact">Contact</a></li>
                    </ul>
                </nav>
            </div>
        </header>

        <section id="accueil">
            <?php if ($banner): ?>
                <div class="banner" style="background-image: url('<?= htmlspecialchars($banner['url']) ?>');">
                    <div class="banner-content">
                        <h2><?= htmlspecialchars($restaurant->name) ?></h2>
                        <?php if (!empty($banner['text'])): ?>
                            <div class="banner-text"><?= nl2br(htmlspecialchars($banner['text'])) ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="banner" style="background-color: #007bff;">
                    <div class="banner-content">
                        <h2><?= htmlspecialchars($restaurant->name) ?></h2>
                    </div>
                </div>
            <?php endif; ?>
        </section>

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
                                        <img src="<?= htmlspecialchars($image['url']) ?>" alt="<?= htmlspecialchars($displayName) ?>" class="lightbox-image">
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

        <!-- ==================== SECTION SERVICES & PAIEMENTS ==================== -->
        <?php
        // Vérifier si au moins un service est actif
        $hasServices = (
            $services['service_sur_place'] == '1' ||
            $services['service_a_emporter'] == '1' ||
            $services['service_livraison_ubereats'] == '1' ||
            $services['service_livraison_etablissement'] == '1' ||
            $services['service_wifi'] == '1' ||
            $services['service_climatisation'] == '1' ||
            $services['service_pmr'] == '1'
        );
        // Vérifier si au moins un paiement est actif
        $hasPayments = (
            $payments['payment_visa'] == '1' ||
            $payments['payment_mastercard'] == '1' ||
            $payments['payment_cb'] == '1' ||
            $payments['payment_especes'] == '1' ||
            $payments['payment_cheques'] == '1'
        );
        if ($hasServices || $hasPayments):
        ?>
            <section id="services" class="services-payments-section">
                <div class="container">
                    <h2>Services & Moyens de paiement</h2>
                    <div class="services-payments-grid">
                        <?php if ($hasServices): ?>
                            <div class="services-column">
                                <h3><i class="fas fa-concierge-bell"></i> Nos services</h3>
                                <ul class="services-list">
                                    <?php if ($services['service_sur_place'] == '1'): ?>
                                        <li><i class="fas fa-store"></i> Sur place</li>
                                    <?php endif; ?>
                                    <?php if ($services['service_a_emporter'] == '1'): ?>
                                        <li><i class="fas fa-shopping-bag"></i> À emporter</li>
                                    <?php endif; ?>
                                    <?php if ($services['service_livraison_ubereats'] == '1' || $services['service_livraison_etablissement'] == '1'): ?>
                                        <li class="delivery-group">
                                            <div class="delivery-header">
                                                <i class="fas fa-truck"></i> Livraison
                                            </div>
                                            <div class="delivery-options">
                                                <?php if ($services['service_livraison_ubereats'] == '1'): ?>
                                                    <div class="delivery-option"><i class="fa-solid fa-car"></i> Uber Eats / Deliveroo</div>
                                                <?php endif; ?>
                                                <?php if ($services['service_livraison_etablissement'] == '1'): ?>
                                                    <div class="delivery-option"><i class="fas fa-motorcycle"></i> Par l'établissement</div>
                                                <?php endif; ?>
                                            </div>
                                        </li>
                                    <?php endif; ?>
                                    <?php if ($services['service_wifi'] == '1'): ?>
                                        <li><i class="fas fa-wifi"></i> Wi-Fi gratuit</li>
                                    <?php endif; ?>
                                    <?php if ($services['service_climatisation'] == '1'): ?>
                                        <li><i class="fas fa-wind"></i> Climatisation</li>
                                    <?php endif; ?>
                                    <?php if ($services['service_pmr'] == '1'): ?>
                                        <li><i class="fas fa-wheelchair"></i> Accès personnes à mobilité réduite</li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <?php if ($hasPayments): ?>
                            <div class="payments-column">
                                <h3><i class="fas fa-credit-card"></i> Moyens de paiement</h3>
                                <ul class="payments-list">
                                    <?php if ($payments['payment_visa'] == '1'): ?>
                                        <li><i class="fab fa-cc-visa"></i> Visa</li>
                                    <?php endif; ?>
                                    <?php if ($payments['payment_mastercard'] == '1'): ?>
                                        <li><i class="fab fa-cc-mastercard"></i> Mastercard</li>
                                    <?php endif; ?>
                                    <?php if ($payments['payment_cb'] == '1'): ?>
                                        <li><i class="fas fa-credit-card"></i> Carte bancaire</li>
                                    <?php endif; ?>
                                    <?php if ($payments['payment_especes'] == '1'): ?>
                                        <li><i class="fas fa-money-bill-wave"></i> Espèces</li>
                                    <?php endif; ?>
                                    <?php if ($payments['payment_cheques'] == '1'): ?>
                                        <li><i class="fas fa-money-check"></i> Chèques</li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <footer id="contact">
            <div class="container">
                <h2>Contactez-nous</h2>
                <div class="footer-content">
                    <div class="footer-contact">
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
                            <div class="contact-item"><i class="fas fa-clock"></i><span><?= nl2br(htmlspecialchars($contact['horaires'])) ?></span></div>
                        <?php endif; ?>

                        <!-- Réseaux sociaux -->
                        <?php
                        $activeSocials = array_filter($socials);
                        if (!empty($activeSocials)):
                        ?>
                            <div class="social-links">
                                <h4>Suivez-nous</h4>
                                <div class="social-icons">
                                    <?php if (!empty($socials['social_facebook'])): ?>
                                        <a href="<?= htmlspecialchars($socials['social_facebook']) ?>" target="_blank" rel="noopener" title="Facebook"><i class="fab fa-facebook-f"></i></a>
                                    <?php endif; ?>
                                    <?php if (!empty($socials['social_instagram'])): ?>
                                        <a href="<?= htmlspecialchars($socials['social_instagram']) ?>" target="_blank" rel="noopener" title="Instagram"><i class="fab fa-instagram"></i></a>
                                    <?php endif; ?>
                                    <?php if (!empty($socials['social_x'])): ?>
                                        <a href="<?= htmlspecialchars($socials['social_x']) ?>" target="_blank" rel="noopener" title="X (Twitter)"><i class="fab fa-x-twitter"></i></a>
                                    <?php endif; ?>
                                    <?php if (!empty($socials['social_tiktok'])): ?>
                                        <a href="<?= htmlspecialchars($socials['social_tiktok']) ?>" target="_blank" rel="noopener" title="TikTok"><i class="fab fa-tiktok"></i></a>
                                    <?php endif; ?>
                                    <?php if (!empty($socials['social_snapchat'])): ?>
                                        <a href="https://snapchat.com/add/<?= urlencode($socials['social_snapchat']) ?>" target="_blank" rel="noopener" title="Snapchat"><i class="fab fa-snapchat-ghost"></i></a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="footer-map">
                        <?php if ($contact && !empty($contact['adresse'])): ?>
                            <iframe
                                width="100%"
                                height="300"
                                style="border:0; border-radius: 8px;"
                                loading="lazy"
                                allowfullscreen
                                referrerpolicy="no-referrer-when-downgrade"
                                src="https://www.google.com/maps?q=<?= urlencode($contact['adresse']) ?>&output=embed">
                            </iframe>
                        <?php else: ?>
                            <p class="no-map">Carte non disponible</p>
                        <?php endif; ?>
                    </div>
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