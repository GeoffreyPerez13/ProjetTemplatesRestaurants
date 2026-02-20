/**
 * Display page JavaScript
 * Gestion du menu hamburger, lightbox et cookies
 */
document.addEventListener('DOMContentLoaded', function() {
    console.log('Display JS loaded');

    // ==================== SMOOTH SCROLL ====================
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({ behavior: 'smooth' });
            }
        });
    });

    // ==================== MENU HAMBURGER ====================
    const hamburger = document.getElementById('hamburger');
    const navMenu = document.getElementById('nav-menu');

    if (hamburger && navMenu) {
        hamburger.addEventListener('click', () => {
            hamburger.classList.toggle('active');
            navMenu.classList.toggle('active');
        });

        // Fermer le menu après clic sur un lien
        navMenu.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', () => {
                hamburger.classList.remove('active');
                navMenu.classList.remove('active');
            });
        });

        // Fermer le menu en cliquant à l'extérieur
        document.addEventListener('click', (e) => {
            if (!hamburger.contains(e.target) && !navMenu.contains(e.target) && navMenu.classList.contains('active')) {
                hamburger.classList.remove('active');
                navMenu.classList.remove('active');
            }
        });
    }

    // ==================== LIGHTBOX ====================
    const lightbox = document.getElementById('lightbox');
    if (lightbox) {
        const lightboxImg = lightbox.querySelector('.lightbox-content img');
        const closeBtn = lightbox.querySelector('.lightbox-close');
        const prevBtn = lightbox.querySelector('.lightbox-prev');
        const nextBtn = lightbox.querySelector('.lightbox-next');
        let currentIndex = 0;
        let images = [];

        const imageElements = document.querySelectorAll('.lightbox-image');
        imageElements.forEach((img, index) => {
            const src = img.src;
            if (src) {
                images.push({ element: img, src: src });
            }
            img.addEventListener('click', (e) => {
                e.stopPropagation();
                currentIndex = index;
                lightboxImg.src = images[currentIndex].src;
                lightbox.classList.add('active');
            });
        });

        if (images.length <= 1) {
            if (prevBtn) prevBtn.style.display = 'none';
            if (nextBtn) nextBtn.style.display = 'none';
        } else {
            if (prevBtn) prevBtn.style.display = 'block';
            if (nextBtn) nextBtn.style.display = 'block';
        }

        closeBtn.addEventListener('click', () => {
            lightbox.classList.remove('active');
        });

        prevBtn.addEventListener('click', () => {
            if (images.length > 1) {
                currentIndex = (currentIndex - 1 + images.length) % images.length;
                lightboxImg.src = images[currentIndex].src;
            }
        });

        nextBtn.addEventListener('click', () => {
            if (images.length > 1) {
                currentIndex = (currentIndex + 1) % images.length;
                lightboxImg.src = images[currentIndex].src;
            }
        });

        lightbox.addEventListener('click', (e) => {
            if (e.target === lightbox) {
                lightbox.classList.remove('active');
            }
        });

        document.addEventListener('keydown', (e) => {
            if (!lightbox.classList.contains('active')) return;
            if (e.key === 'Escape') lightbox.classList.remove('active');
            if (e.key === 'ArrowLeft') prevBtn.click();
            if (e.key === 'ArrowRight') nextBtn.click();
        });
    }

    // ==================== GESTION DES COOKIES ====================
    const cookieBanner = document.getElementById('cookie-banner');
    const cookieModal = document.getElementById('cookie-modal');
    const openPreferencesBtn = document.getElementById('open-cookie-preferences');
    const acceptAllBtn = document.getElementById('accept-all-cookies');
    const acceptAllFromModal = document.getElementById('accept-all-from-modal');
    const savePreferencesBtn = document.getElementById('save-cookie-preferences');
    const closeModal = document.querySelector('.close-modal');

    if (cookieBanner && cookieModal) {
        const consent = localStorage.getItem('cookieConsent');
        if (consent) {
            cookieBanner.style.display = 'none';
        } else {
            cookieBanner.style.display = 'flex';
        }

        if (openPreferencesBtn) {
            openPreferencesBtn.addEventListener('click', function() {
                cookieModal.style.display = 'flex';
            });
        }

        if (closeModal) {
            closeModal.addEventListener('click', function() {
                cookieModal.style.display = 'none';
            });
        }

        window.addEventListener('click', function(event) {
            if (event.target === cookieModal) {
                cookieModal.style.display = 'none';
            }
        });

        function acceptAll() {
            localStorage.setItem('cookieConsent', JSON.stringify({
                necessary: true,
                analytics: true,
                marketing: true,
                accepted: true
            }));
            cookieBanner.style.display = 'none';
            cookieModal.style.display = 'none';
        }

        if (acceptAllBtn) {
            acceptAllBtn.addEventListener('click', acceptAll);
        }
        if (acceptAllFromModal) {
            acceptAllFromModal.addEventListener('click', acceptAll);
        }

        if (savePreferencesBtn) {
            savePreferencesBtn.addEventListener('click', function() {
                const analytics = document.getElementById('cookies-analytics')?.checked || false;
                const marketing = document.getElementById('cookies-marketing')?.checked || false;
                localStorage.setItem('cookieConsent', JSON.stringify({
                    necessary: true,
                    analytics: analytics,
                    marketing: marketing,
                    accepted: false
                }));
                cookieBanner.style.display = 'none';
                cookieModal.style.display = 'none';
            });
        }

        if (consent) {
            try {
                const prefs = JSON.parse(consent);
                const analyticsCheck = document.getElementById('cookies-analytics');
                const marketingCheck = document.getElementById('cookies-marketing');
                if (analyticsCheck) analyticsCheck.checked = prefs.analytics || false;
                if (marketingCheck) marketingCheck.checked = prefs.marketing || false;
            } catch (e) {}
        }
    }
});