/**
 * Cookies — Bannière de consentement et modale de préférences (vitrine)
 * Utilise document.cookie pour la persistance (cohérent avec le panel admin)
 */
document.addEventListener('DOMContentLoaded', function() {
    const cookieBanner = document.getElementById('cookie-banner');
    const cookieModal = document.getElementById('cookie-modal');
    const openPreferencesBtn = document.getElementById('open-cookie-preferences');
    const acceptAllBtn = document.getElementById('accept-all-cookies');
    const rejectAllBtn = document.getElementById('reject-all-cookies');
    const acceptAllFromModal = document.getElementById('accept-all-from-modal');
    const savePreferencesBtn = document.getElementById('save-cookie-preferences');
    const closeModal = document.querySelector('.close-modal');

    if (!cookieBanner || !cookieModal) return;

    // --- Utilitaires cookies ---
    function setCookie(name, value, days) {
        let expires = '';
        if (days) {
            const date = new Date();
            date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
            expires = '; expires=' + date.toUTCString();
        }
        const secure = location.protocol === 'https:' ? '; Secure' : '';
        document.cookie = name + '=' + (value || '') + expires + '; path=/; SameSite=Lax' + secure;
    }

    function getCookie(name) {
        const match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
        return match ? match[2] : null;
    }

    // --- État initial ---
    const consent = getCookie('cookie_consent');
    if (consent) {
        cookieBanner.style.display = 'none';
    } else {
        cookieBanner.style.display = 'flex';
    }

    // --- Handlers ---
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
        setCookie('cookie_consent', 'accepted', 365);
        setCookie('cookie_analytics', 'true', 365);
        setCookie('cookie_marketing', 'false', 365);
        cookieBanner.style.display = 'none';
        cookieModal.style.display = 'none';
        loadAnalytics();
    }

    function rejectAll() {
        setCookie('cookie_consent', 'rejected', 365);
        setCookie('cookie_analytics', 'false', 365);
        setCookie('cookie_marketing', 'false', 365);
        cookieBanner.style.display = 'none';
        cookieModal.style.display = 'none';
    }

    if (acceptAllBtn) {
        acceptAllBtn.addEventListener('click', acceptAll);
    }
    if (rejectAllBtn) {
        rejectAllBtn.addEventListener('click', rejectAll);
    }
    if (acceptAllFromModal) {
        acceptAllFromModal.addEventListener('click', acceptAll);
    }

    if (savePreferencesBtn) {
        savePreferencesBtn.addEventListener('click', function() {
            const analytics = document.getElementById('cookies-analytics')?.checked ? 'true' : 'false';
            const marketing = document.getElementById('cookies-marketing')?.checked ? 'true' : 'false';
            setCookie('cookie_consent', 'custom', 365);
            setCookie('cookie_analytics', analytics, 365);
            setCookie('cookie_marketing', marketing, 365);
            if (analytics === 'true') {
                loadAnalytics();
            }
            cookieBanner.style.display = 'none';
            cookieModal.style.display = 'none';
        });
    }

    // --- Restaurer les préférences dans la modale ---
    if (consent) {
        const analyticsCheck = document.getElementById('cookies-analytics');
        const marketingCheck = document.getElementById('cookies-marketing');
        if (analyticsCheck) analyticsCheck.checked = getCookie('cookie_analytics') === 'true';
        if (marketingCheck) marketingCheck.checked = getCookie('cookie_marketing') === 'true';
    }

    // --- Analytics ---
    function loadAnalytics() {
        if (getCookie('cookie_analytics') === 'true') {
            // Insérer ici le code Google Analytics ou autre outil d'analyse
            console.log('Analytics chargés (vitrine)');
        }
    }

    loadAnalytics();
});
