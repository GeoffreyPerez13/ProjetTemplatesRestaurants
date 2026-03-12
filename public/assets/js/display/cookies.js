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

    // --- Analytics ---
    function loadAnalytics() {
        if (getCookie('cookie_analytics') === 'true') {
            // Insérer ici le code Google Analytics ou autre outil d'analyse
            /*
            (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
            (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
            m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
            })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

            ga('create', 'UA-XXXXXXX-X', 'auto');
            ga('send', 'pageview');
            */
        }
    }

    // --- État initial ---
    const consent = getCookie('cookie_consent');

    cookieModal.style.setProperty('display', 'none', 'important');

    if (!consent) {
        cookieBanner.classList.add('show');
    }

    // --- Handlers ---
    if (openPreferencesBtn) {
        openPreferencesBtn.addEventListener('click', function(e) {
            e.preventDefault();
            cookieModal.style.setProperty('display', 'flex', 'important');
        });
    }

    if (closeModal) {
        closeModal.addEventListener('click', function(e) {
            e.preventDefault();
            cookieModal.style.setProperty('display', 'none', 'important');
        });
    }

    window.addEventListener('click', function(event) {
        if (event.target === cookieModal) {
            cookieModal.style.setProperty('display', 'none', 'important');
        }
    });

    if (acceptAllBtn) {
        acceptAllBtn.addEventListener('click', function(e) {
            e.preventDefault();
            setCookie('cookie_consent', 'accepted', 365);
            setCookie('cookie_analytics', 'true', 365);
            setCookie('cookie_marketing', 'false', 365);
            cookieBanner.classList.remove('show');
            cookieModal.style.setProperty('display', 'none', 'important');
            loadAnalytics();
        });
    }

    if (rejectAllBtn) {
        rejectAllBtn.addEventListener('click', function(e) {
            e.preventDefault();
            setCookie('cookie_consent', 'rejected', 365);
            setCookie('cookie_analytics', 'false', 365);
            setCookie('cookie_marketing', 'false', 365);
            cookieBanner.classList.remove('show');
            cookieModal.style.setProperty('display', 'none', 'important');
        });
    }

    if (acceptAllFromModal) {
        acceptAllFromModal.addEventListener('click', function(e) {
            e.preventDefault();
            setCookie('cookie_consent', 'accepted', 365);
            setCookie('cookie_analytics', 'true', 365);
            setCookie('cookie_marketing', 'false', 365);
            cookieBanner.classList.remove('show');
            cookieModal.style.setProperty('display', 'none', 'important');
            loadAnalytics();
        });
    }

    if (savePreferencesBtn) {
        savePreferencesBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const analytics = document.getElementById('cookies-analytics')?.checked ? 'true' : 'false';
            const marketing = document.getElementById('cookies-marketing')?.checked ? 'true' : 'false';

            setCookie('cookie_consent', 'custom', 365);
            setCookie('cookie_analytics', analytics, 365);
            setCookie('cookie_marketing', marketing, 365);

            cookieBanner.classList.remove('show');
            cookieModal.style.setProperty('display', 'none', 'important');

            if (analytics === 'true') {
                loadAnalytics();
            }
        });
    }
});
