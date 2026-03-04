<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MenuMiam — Créez le site vitrine de votre restaurant en quelques clics</title>
    <meta name="description" content="MenuMiam est la solution clé en main pour créer et gérer le site vitrine de votre restaurant. Carte en ligne, avis Google, réservations et plus encore.">
    <meta name="keywords" content="site restaurant, carte en ligne, menu digital, vitrine restaurant, MenuMiam">
    
    <!-- Open Graph -->
    <meta property="og:title" content="MenuMiam — Le site vitrine de votre restaurant">
    <meta property="og:description" content="Créez votre site vitrine professionnel en quelques minutes. Gérez votre carte, vos horaires, vos avis Google et bien plus.">
    <meta property="og:type" content="website">
    <meta name="theme-color" content="#b45309">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- CSS -->
    <link rel="stylesheet" href="assets/css/landing.css">
    
    <!-- Dark mode script (chargé tôt pour éviter le flash) -->
    <script src="assets/js/landing/dark-mode.js"></script>
</head>
<body>

<!-- ========== NAVIGATION ========== -->
<nav class="landing-nav" id="landing-nav">
    <div class="nav-container">
        <a href="?page=landing" class="nav-logo">
            <i class="fas fa-utensils"></i>
            <span>MenuMiam</span>
        </a>
        <div class="nav-links" id="nav-links">
            <a href="#features">Fonctionnalités</a>
            <a href="#pricing">Tarifs</a>
            <a href="#demo">Démo</a>
            <a href="#faq">FAQ</a>
        </div>
        <div class="nav-actions">
            <a href="?page=login" class="nav-btn-outline">Se connecter</a>
            <a href="?page=auto-register" class="nav-btn-primary">Créer mon site</a>
        </div>
        <button class="nav-mobile-toggle" id="nav-mobile-toggle" aria-label="Menu">
            <i class="fas fa-bars"></i>
        </button>
    </div>
</nav>

<!-- ========== HERO ========== -->
<section class="hero">
    <div class="hero-bg-shapes">
        <div class="shape shape-1"></div>
        <div class="shape shape-2"></div>
        <div class="shape shape-3"></div>
    </div>
    <div class="hero-container">
        <div class="hero-content">
            <div class="hero-badge">
                <i class="fas fa-rocket"></i>
                <span>La solution n°1 pour les restaurateurs</span>
            </div>
            <h1>Créez le <span class="text-gradient">site vitrine</span> de votre restaurant en quelques clics</h1>
            <p class="hero-subtitle">
                Gérez votre carte en ligne, vos horaires, vos avis Google et bien plus. 
                Sans compétence technique, à partir de <strong>9€/mois</strong>.
            </p>
            <div class="hero-actions">
                <a href="?page=auto-register" class="btn-hero-primary">
                    <i class="fas fa-bolt"></i>
                    Créer mon site
                </a>
                <a href="#demo" class="btn-hero-secondary">
                    <i class="fas fa-play-circle"></i>
                    Voir la démo
                </a>
            </div>
            <div class="hero-trust">
                <div class="trust-avatars">
                    <div class="trust-avatar" style="background:#e8d5b7;">🍕</div>
                    <div class="trust-avatar" style="background:#d4e8d5;">🍣</div>
                    <div class="trust-avatar" style="background:#d5d4e8;">🍔</div>
                    <div class="trust-avatar" style="background:#e8d4d4;">🥗</div>
                </div>
                <p>Rejoignez les restaurateurs qui nous font confiance</p>
            </div>
        </div>
        <div class="hero-visual">
            <div class="hero-mockup">
                <div class="mockup-browser">
                    <div class="browser-bar">
                        <div class="browser-dots">
                            <span></span><span></span><span></span>
                        </div>
                        <div class="browser-url">menumiam.fr/mon-restaurant</div>
                    </div>
                    <div class="browser-content">
                        <div class="mockup-header">
                            <div class="mockup-logo"></div>
                            <div class="mockup-nav-lines">
                                <span></span><span></span><span></span>
                            </div>
                        </div>
                        <div class="mockup-banner"></div>
                        <div class="mockup-cards">
                            <div class="mockup-card"></div>
                            <div class="mockup-card"></div>
                            <div class="mockup-card"></div>
                        </div>
                    </div>
                </div>
                <div class="mockup-floating mockup-float-1">
                    <i class="fas fa-star"></i>
                    <span>4.8/5</span>
                </div>
                <div class="mockup-floating mockup-float-2">
                    <i class="fas fa-check-circle"></i>
                    <span>Site en ligne !</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ========== LOGOS / SOCIAL PROOF ========== -->
<section class="social-proof">
    <div class="container">
        <p class="social-proof-label">Ils proposent leur carte en ligne avec MenuMiam</p>
        <div class="social-proof-logos">
            <div class="proof-item">🍕 Pizzeria Roma</div>
            <div class="proof-item">🍣 Sushi Palace</div>
            <div class="proof-item">🥐 Le Bistrot Parisien</div>
            <div class="proof-item">🍔 Burger Factory</div>
            <div class="proof-item">🥗 Green Bowl</div>
        </div>
    </div>
</section>

<!-- ========== FONCTIONNALITÉS ========== -->
<section class="features" id="features">
    <div class="container">
        <div class="section-header">
            <span class="section-badge">Fonctionnalités</span>
            <h2>Tout ce dont votre restaurant a besoin</h2>
            <p>Une solution complète pour gérer votre présence en ligne, sans aucune compétence technique.</p>
        </div>

        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-card-icon">
                    <i class="fas fa-utensils"></i>
                </div>
                <h3>Carte en ligne</h3>
                <p>Créez et mettez à jour votre carte avec catégories, plats, prix, allergènes et photos. Mode éditable ou images.</p>
                <span class="feature-tag free">Gratuit</span>
            </div>

            <div class="feature-card">
                <div class="option-tooltip">
                    <span class="tooltip-icon" title="Plus d'infos">i</span>
                    <div class="tooltip-content">
                        <p>Choisissez parmi 7 styles de couleurs et 3 présentations différentes pour personnaliser l'apparence de votre site.</p>
                    </div>
                </div>
                <div class="feature-card-icon">
                    <i class="fas fa-palette"></i>
                </div>
                <h3>Templates personnalisables</h3>
                <p>Choisissez parmi plusieurs palettes de couleurs et layouts pour un site qui vous ressemble.</p>
                <span class="feature-tag free">Gratuit</span>
            </div>

            <div class="feature-card">
                <div class="feature-card-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <h3>Horaires & Contact</h3>
                <p>Affichez vos horaires d'ouverture, adresse, téléphone, email et carte Google Maps interactive.</p>
                <span class="feature-tag free">Gratuit</span>
            </div>

            <div class="feature-card">
                <div class="feature-card-icon">
                    <i class="fas fa-mobile-alt"></i>
                </div>
                <h3>100% Responsive</h3>
                <p>Votre site s'adapte parfaitement à tous les écrans : mobile, tablette et ordinateur.</p>
                <span class="feature-tag free">Gratuit</span>
            </div>

            <div class="feature-card">
                <div class="option-tooltip">
                    <span class="tooltip-icon" title="Plus d'infos">i</span>
                    <div class="tooltip-content">
                        <p>Votre site est automatiquement optimisé pour apparaître dans les résultats de recherche Google.</p>
                    </div>
                </div>
                <div class="feature-card-icon">
                    <i class="fas fa-search"></i>
                </div>
                <h3>SEO optimisé</h3>
                <p>Référencement naturel optimisé avec Schema.org, sitemap, balises méta et données structurées.</p>
                <span class="feature-tag free">Gratuit</span>
            </div>

            <div class="feature-card">
                <div class="option-tooltip">
                    <span class="tooltip-icon" title="Plus d'infos">i</span>
                    <div class="tooltip-content">
                        <p>Toutes les pages légales obligatoires sont incluses : cookies, mentions légales et confidentialité.</p>
                    </div>
                </div>
                <div class="feature-card-icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h3>RGPD & Légal</h3>
                <p>Gestion des cookies, mentions légales, CGU et politique de confidentialité intégrées.</p>
                <span class="feature-tag free">Gratuit</span>
            </div>

            <div class="feature-card premium-card">
                <div class="feature-card-icon premium">
                    <i class="fas fa-star"></i>
                </div>
                <h3>Avis Google</h3>
                <p>Affichez automatiquement les avis Google de votre restaurant avec note moyenne et photos des clients.</p>
                <span class="feature-tag premium">Premium</span>
            </div>

            <div class="feature-card premium-card">
                <div class="feature-card-icon premium">
                    <i class="fas fa-chart-line"></i>
                </div>
                <h3>Statistiques avancées</h3>
                <p>Analysez le trafic de votre site, les pages les plus vues et le comportement de vos visiteurs.</p>
                <span class="feature-tag premium">Premium</span>
            </div>

            <div class="feature-card premium-card">
                <div class="feature-card-icon premium">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <h3>Réservations en ligne</h3>
                <p>Permettez à vos clients de réserver directement depuis votre site avec confirmation automatique.</p>
                <span class="feature-tag coming">Bientôt</span>
            </div>
        </div>
    </div>
</section>

<!-- ========== COMMENT ÇA MARCHE ========== -->
<section class="how-it-works">
    <div class="container">
        <div class="section-header">
            <span class="section-badge">Simple comme bonjour</span>
            <h2>Votre site en ligne en 3 étapes</h2>
        </div>

        <div class="steps-grid">
            <div class="step-card">
                <div class="step-number">1</div>
                <div class="step-icon"><i class="fas fa-user-plus"></i></div>
                <h3>Créez votre compte</h3>
                <p>Inscription en 30 secondes. Choisissez votre abonnement et c'est parti.</p>
            </div>
            <div class="step-arrow"><i class="fas fa-arrow-right"></i></div>
            <div class="step-card">
                <div class="step-number">2</div>
                <div class="step-icon"><i class="fas fa-edit"></i></div>
                <h3>Personnalisez votre site</h3>
                <p>Ajoutez votre carte, logo, photos, horaires et choisissez votre template.</p>
            </div>
            <div class="step-arrow"><i class="fas fa-arrow-right"></i></div>
            <div class="step-card">
                <div class="step-number">3</div>
                <div class="step-icon"><i class="fas fa-globe"></i></div>
                <h3>Publiez en un clic</h3>
                <p>Votre site est en ligne instantanément avec son propre URL personnalisé.</p>
            </div>
        </div>
    </div>
</section>

<!-- ========== TARIFS ========== -->
<section class="pricing" id="pricing">
    <div class="container">
        <div class="section-header">
            <span class="section-badge">Tarifs</span>
            <h2>Un abonnement simple, des options à la carte</h2>
            <p>L'abonnement Basique pour votre site, et des options premium uniquement si vous en avez besoin.</p>
        </div>

        <div class="pricing-toggle">
            <span class="toggle-label active" data-period="monthly">Mensuel</span>
            <button class="toggle-switch" id="pricing-toggle" aria-label="Changer la période">
                <span class="toggle-knob"></span>
            </button>
            <span class="toggle-label" data-period="yearly">
                Annuel <span class="toggle-badge">-20%</span>
            </span>
        </div>

        <!-- Abonnement Basique -->
        <div class="pricing-main">
            <div class="pricing-card featured wide">
                <div class="pricing-popular">Indispensable</div>
                <div class="pricing-card-inner">
                    <div class="pricing-card-left">
                        <div class="pricing-header">
                            <h3>Abonnement Basique</h3>
                            <p class="pricing-subtitle">Tout pour mettre votre restaurant en ligne</p>
                        </div>
                        <div class="pricing-price">
                            <span class="price-amount" data-monthly="9" data-yearly="7">9</span>
                            <span class="price-currency">€</span>
                            <span class="price-period">/mois</span>
                        </div>
                        <a href="?page=auto-register" class="pricing-btn primary">Créer mon site</a>
                    </div>
                    <div class="pricing-card-right">
                        <ul class="pricing-features">
                            <li class="included"><i class="fas fa-check"></i> Site vitrine avec URL personnalisée</li>
                            <li class="included"><i class="fas fa-check"></i> Carte en ligne modifiable (éditable ou images)</li>
                            <li class="included"><i class="fas fa-check"></i> Horaires, contact & Google Maps</li>
                            <li class="included"><i class="fas fa-check"></i> 7 palettes de couleurs & 3 layouts</li>
                            <li class="included"><i class="fas fa-check"></i> Logo, bannière & photos</li>
                            <li class="included"><i class="fas fa-check"></i> SEO optimisé & référencement Google</li>
                            <li class="included"><i class="fas fa-check"></i> RGPD, cookies & mentions légales</li>
                            <li class="included"><i class="fas fa-check"></i> Mode maintenance & gestion complète</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Options Premium à la carte -->
        <div class="pricing-options-header">
            <h3><i class="fas fa-puzzle-piece"></i> Options premium à la carte</h3>
            <p>Ajoutez uniquement ce dont vous avez besoin, en plus de votre abonnement Basique.</p>
        </div>

        <div class="pricing-options-grid">
            <div class="option-card">
                <div class="option-icon"><i class="fas fa-star"></i></div>
                <div class="option-info">
                    <h4>Avis Google</h4>
                    <p>Affichez vos avis Google et votre note directement sur votre site.</p>
                </div>
                <div class="option-price">
                    <span class="option-amount" data-monthly="5" data-yearly="4">+5</span>
                    <span>€/mois</span>
                </div>
            </div>

            <div class="option-card">
                <div class="option-icon"><i class="fas fa-chart-line"></i></div>
                <div class="option-info">
                    <h4>Statistiques avancées</h4>
                    <p>Analysez le trafic, les pages vues et le comportement de vos visiteurs.</p>
                </div>
                <div class="option-price">
                    <span class="option-amount" data-monthly="5" data-yearly="4">+5</span>
                    <span>€/mois</span>
                </div>
            </div>

            <div class="option-card">
                <div class="option-icon"><i class="fas fa-calendar-check"></i></div>
                <div class="option-info">
                    <h4>Réservations en ligne</h4>
                    <p>Vos clients réservent directement depuis votre site vitrine.</p>
                </div>
                <div class="option-price">
                    <span class="option-amount" data-monthly="8" data-yearly="6">+8</span>
                    <span>€/mois</span>
                </div>
            </div>

            <div class="option-card">
                <div class="option-icon"><i class="fas fa-motorcycle"></i></div>
                <div class="option-info">
                    <h4>Intégration livraison</h4>
                    <p>Connectez Uber Eats, Deliveroo et autres plateformes.</p>
                </div>
                <div class="option-price">
                    <span class="option-amount" data-monthly="7" data-yearly="6">+7</span>
                    <span>€/mois</span>
                </div>
            </div>
        </div>

        <div class="pricing-example">
            <p><i class="fas fa-lightbulb"></i> <strong>Exemple :</strong> Abonnement Basique (9€) + Avis Google (5€) = <strong>14€/mois</strong>. Vous ne payez que ce que vous utilisez.</p>
        </div>

        <div class="pricing-note">
            <p><i class="fas fa-info-circle"></i> Besoin d'une solution sur-mesure pour plusieurs établissements ? 
            <a href="mailto:contact@menumiam.fr">Contactez-nous pour un devis personnalisé</a>.</p>
        </div>
    </div>
</section>

<!-- ========== DÉMO ========== -->
<section class="demo-section" id="demo">
    <div class="container">
        <div class="demo-card">
            <div class="demo-content">
                <span class="section-badge">Démo live</span>
                <h2>Testez MenuMiam en conditions réelles</h2>
                <p>Explorez notre restaurant de démonstration pour voir à quoi ressemblera votre site. 
                   Modifiez la carte, changez le template, testez toutes les fonctionnalités.</p>
                <div class="demo-actions">
                    <a href="?page=demo" class="btn-demo-primary" target="_blank">
                        <i class="fas fa-external-link-alt"></i>
                        Voir le site démo
                    </a>
                    <a href="mailto:contact@menumiam.fr?subject=Demande de démo personnalisée" class="btn-demo-secondary">
                        <i class="fas fa-envelope"></i>
                        Demander une démo privée
                    </a>
                </div>
                <p class="demo-note">
                    <i class="fas fa-clock"></i> La démo privée vous donne accès au panel admin pendant 24h pour tout tester.
                </p>
            </div>
            <div class="demo-visual">
                <div class="demo-device">
                    <div class="device-frame">
                        <div class="device-screen">
                            <div class="demo-screen-header"></div>
                            <div class="demo-screen-banner"></div>
                            <div class="demo-screen-content">
                                <div class="demo-screen-card"></div>
                                <div class="demo-screen-card"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ========== FAQ ========== -->
<section class="faq" id="faq">
    <div class="container">
        <div class="section-header">
            <span class="section-badge">FAQ</span>
            <h2>Questions fréquentes</h2>
        </div>

        <div class="faq-grid">
            <div class="faq-item">
                <button class="faq-question" aria-expanded="false">
                    <span>Ai-je besoin de compétences techniques ?</span>
                    <i class="fas fa-chevron-down"></i>
                </button>
                <div class="faq-answer">
                    <p>Absolument pas ! MenuMiam est conçu pour les restaurateurs, pas les développeurs. 
                    Tout se fait via une interface simple et intuitive. Si vous savez utiliser un ordinateur, vous savez utiliser MenuMiam.</p>
                </div>
            </div>

            <div class="faq-item">
                <button class="faq-question" aria-expanded="false">
                    <span>Puis-je essayer avant de payer ?</span>
                    <i class="fas fa-chevron-down"></i>
                </button>
                <div class="faq-answer">
                    <p>Oui ! Vous pouvez demander une démo privée pour tester toutes les fonctionnalités pendant 24h, 
                    y compris les options premium. Aucun engagement avant d'être convaincu.</p>
                </div>
            </div>

            <div class="faq-item">
                <button class="faq-question" aria-expanded="false">
                    <span>Comment s'affichent les avis Google ?</span>
                    <i class="fas fa-chevron-down"></i>
                </button>
                <div class="faq-answer">
                    <p>Avec l'option Avis Google (+5€/mois), vos avis s'affichent automatiquement sur votre site vitrine. 
                    Il vous suffit de renseigner votre Google Place ID dans les paramètres. Les avis sont mis à jour automatiquement.</p>
                </div>
            </div>

            <div class="faq-item">
                <button class="faq-question" aria-expanded="false">
                    <span>Puis-je changer de plan à tout moment ?</span>
                    <i class="fas fa-chevron-down"></i>
                </button>
                <div class="faq-answer">
                    <p>Oui ! Vous pouvez ajouter ou retirer des options premium à tout moment depuis votre espace. 
                    Les ajouts sont effectifs immédiatement, les retraits prennent effet à la fin de la période en cours.</p>
                </div>
            </div>

            <div class="faq-item">
                <button class="faq-question" aria-expanded="false">
                    <span>Mon site est-il bien référencé sur Google ?</span>
                    <i class="fas fa-chevron-down"></i>
                </button>
                <div class="faq-answer">
                    <p>Oui ! Tous nos sites incluent une optimisation SEO complète : balises méta, Schema.org pour restaurants, 
                    sitemap XML, données structurées, et des temps de chargement optimisés. Votre restaurant apparaîtra dans les résultats Google.</p>
                </div>
            </div>

            <div class="faq-item">
                <button class="faq-question" aria-expanded="false">
                    <span>Comment fonctionne le paiement ?</span>
                    <i class="fas fa-chevron-down"></i>
                </button>
                <div class="faq-answer">
                    <p>Le paiement est sécurisé via Stripe. Vous pouvez payer par carte bancaire (Visa, Mastercard, CB). 
                    L'abonnement est mensuel ou annuel (avec 20% de réduction). Vous pouvez annuler à tout moment.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ========== CTA FINAL ========== -->
<section class="cta-section">
    <div class="container">
        <div class="cta-card">
            <h2>Prêt à mettre votre restaurant en ligne ?</h2>
            <p>Rejoignez les restaurateurs qui utilisent MenuMiam pour développer leur activité en ligne.</p>
            <div class="cta-actions">
                <a href="?page=auto-register" class="btn-cta-primary">
                    <i class="fas fa-rocket"></i>
                    Créer mon site maintenant
                </a>
                <a href="mailto:contact@menumiam.fr" class="btn-cta-secondary">
                    <i class="fas fa-envelope"></i>
                    Nous contacter
                </a>
            </div>
        </div>
    </div>
</section>

<!-- ========== FOOTER ========== -->
<footer class="landing-footer">
    <div class="container">
        <div class="footer-grid">
            <div class="footer-brand">
                <div class="footer-logo">
                    <i class="fas fa-utensils"></i>
                    <span>MenuMiam</span>
                </div>
                <p>La solution clé en main pour créer et gérer le site vitrine de votre restaurant.</p>
                <div class="footer-socials">
                    <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                    <a href="#" aria-label="Facebook"><i class="fab fa-facebook"></i></a>
                    <a href="#" aria-label="LinkedIn"><i class="fab fa-linkedin"></i></a>
                </div>
            </div>
            <div class="footer-links-group">
                <h4>Produit</h4>
                <ul>
                    <li><a href="#features">Fonctionnalités</a></li>
                    <li><a href="#pricing">Tarifs</a></li>
                    <li><a href="#demo">Démo</a></li>
                    <li><a href="#faq">FAQ</a></li>
                </ul>
            </div>
            <div class="footer-links-group">
                <h4>Ressources</h4>
                <ul>
                    <li><a href="mailto:contact@menumiam.fr">Contact</a></li>
                    <li><a href="mailto:support@menumiam.fr">Support</a></li>
                    <li><a href="?page=demo">Démo en ligne</a></li>
                </ul>
            </div>
            <div class="footer-links-group">
                <h4>Légal</h4>
                <ul>
                    <li><a href="?page=mentions-legales">Mentions légales</a></li>
                    <li><a href="?page=cgu">CGU</a></li>
                    <li><a href="?page=rgpd">RGPD</a></li>
                    <li><a href="?page=cookies">Cookies</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?= date('Y') ?> MenuMiam. Tous droits réservés.</p>
        </div>
    </div>
</footer>

<!-- ========== SCRIPTS ========== -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Navigation mobile
    const mobileToggle = document.getElementById('nav-mobile-toggle');
    const navLinks = document.getElementById('nav-links');
    if (mobileToggle) {
        mobileToggle.addEventListener('click', () => {
            navLinks.classList.toggle('active');
            mobileToggle.querySelector('i').classList.toggle('fa-bars');
            mobileToggle.querySelector('i').classList.toggle('fa-times');
        });
    }

    // Navigation sticky
    const nav = document.getElementById('landing-nav');
    window.addEventListener('scroll', () => {
        nav.classList.toggle('scrolled', window.scrollY > 50);
    });

    // Smooth scroll
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                navLinks.classList.remove('active');
            }
        });
    });

    // Pricing toggle (mensuel/annuel)
    const toggle = document.getElementById('pricing-toggle');
    const labels = document.querySelectorAll('.toggle-label');
    let isYearly = false;

    if (toggle) {
        toggle.addEventListener('click', () => {
            isYearly = !isYearly;
            toggle.classList.toggle('active', isYearly);
            labels.forEach(l => l.classList.toggle('active'));
            
            document.querySelectorAll('.price-amount').forEach(el => {
                el.textContent = isYearly ? el.dataset.yearly : el.dataset.monthly;
            });
            document.querySelectorAll('.option-amount').forEach(el => {
                el.textContent = isYearly ? ('+' + el.dataset.yearly) : ('+' + el.dataset.monthly);
            });
        });
    }

    // FAQ accordion
    document.querySelectorAll('.faq-question').forEach(btn => {
        btn.addEventListener('click', () => {
            const item = btn.closest('.faq-item');
            const isOpen = item.classList.contains('open');
            
            // Fermer tous
            document.querySelectorAll('.faq-item').forEach(i => i.classList.remove('open'));
            
            // Ouvrir le cliqué (sauf si déjà ouvert)
            if (!isOpen) {
                item.classList.add('open');
            }
        });
    });

    // Scroll animations
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-in');
            }
        });
    }, { threshold: 0.1 });

    document.querySelectorAll('.feature-card, .step-card, .pricing-card, .faq-item').forEach(el => {
        observer.observe(el);
    });
});
</script>

<!-- Bouton dark mode (position fixed) -->
<button id="landing-dark-mode-toggle" class="dark-mode-toggle-fixed" aria-label="Basculer le mode sombre">
    <i class="fas fa-moon"></i>
    <i class="fas fa-sun"></i>
</button>

<!-- Flèches de navigation scroll -->
<div class="page-navigation-buttons">
    <button type="button" id="scroll-to-bottom" class="btn-navigation scroll-to-bottom" title="Aller en bas de la page">
        <i class="fas fa-arrow-down"></i>
    </button>
    <button type="button" id="scroll-to-top" class="btn-navigation scroll-to-top" title="Aller en haut de la page">
        <i class="fas fa-arrow-up"></i>
    </button>
</div>

<script src="assets/js/landing/scroll-arrows.js"></script>

</body>
</html>
