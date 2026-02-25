/**
 * Cookies — Bannière de consentement et modale de préférences
 */
document.addEventListener('DOMContentLoaded', function() {
    const cookieBanner = document.getElementById('cookie-banner');
    const cookieModal = document.getElementById('cookie-modal');
    const openPreferencesBtn = document.getElementById('open-cookie-preferences');
    const acceptAllBtn = document.getElementById('accept-all-cookies');
    const acceptAllFromModal = document.getElementById('accept-all-from-modal');
    const savePreferencesBtn = document.getElementById('save-cookie-preferences');
    const closeModal = document.querySelector('.close-modal');

    if (!cookieBanner || !cookieModal) return;

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
});
