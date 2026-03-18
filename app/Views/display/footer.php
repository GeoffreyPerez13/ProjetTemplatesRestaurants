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
                        src="https://www.google.com/maps/embed/v1/place?key=<?= $_ENV['GOOGLE_MAPS_API_KEY'] ?? '' ?>&q=<?= urlencode($contact['adresse']) ?>&zoom=16&maptype=roadmap"
                        width="100%"
                        height="320"
                        style="border:0; border-radius: var(--radius-lg);"
                        allowfullscreen
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade">
                    </iframe>
                    <div class="map-button">
                        <a href="https://www.google.com/maps/search/?api=1&query=<?= urlencode($contact['adresse']) ?>" 
                           target="_blank" 
                           rel="noopener" 
                           class="map-link-full"
                           title="Voir sur Google Maps">
                            <i class="fas fa-map-marked-alt"></i>
                            <span>Voir sur Google Maps</span>
                            <i class="fas fa-external-link-alt"></i>
                        </a>
                    </div>
                    <div class="map-fallback">
                        <a href="https://www.google.com/maps/search/?api=1&query=<?= urlencode($contact['adresse']) ?>" 
                           target="_blank" 
                           rel="noopener" 
                           class="map-link"
                           title="Voir sur Google Maps">
                            <i class="fas fa-map-marked-alt"></i>
                            Voir sur Google Maps
                        </a>
                    </div>
                <?php else: ?>
                    <p class="no-map">Carte non disponible</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <!-- Barre de crédits -->
    <div class="footer-credits">
        <div class="container footer-credits-content">
            <p>&copy; <?= date('Y') ?> <?= htmlspecialchars($restaurant->name) ?> — Propulsé par <a href="<?= SITE_URL ?>" rel="noopener">MenuMiam</a></p>
            <nav class="footer-legal-links">
                <a href="?page=legal&section=cgu">CGU</a>
                <a href="?page=legal&section=privacy">RGPD</a>
                <a href="?page=legal&section=cookies">Cookies</a>
                <a href="?page=legal&section=legal">Mentions légales</a>
            </nav>
        </div>
    </div>
</footer>
