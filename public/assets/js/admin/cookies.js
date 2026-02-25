/**
 * Cookies — Gestion du consentement cookies côté admin
 */
function setCookie(name, value, days) {
    let expires = "";
    if (days) {
        const date = new Date();
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
        expires = "; expires=" + date.toUTCString();
    }
    document.cookie = name + "=" + (value || "") + expires + "; path=/; SameSite=Lax";
}

function acceptCookies() {
    setCookie('cookie_consent', 'accepted', 365);
    setCookie('cookie_analytics', 'true', 365);
    setCookie('cookie_marketing', 'false', 365);
    document.getElementById('cookie-banner').style.display = 'none';
    loadAnalytics();
}

function rejectCookies() {
    setCookie('cookie_consent', 'rejected', 365);
    setCookie('cookie_analytics', 'false', 365);
    setCookie('cookie_marketing', 'false', 365);
    document.getElementById('cookie-banner').style.display = 'none';
}

function showCookieSettings() {
    document.getElementById('cookie-banner').style.display = 'none';
    document.getElementById('cookie-settings').style.display = 'block';
}

function hideCookieSettings() {
    document.getElementById('cookie-settings').style.display = 'none';
    document.getElementById('cookie-banner').style.display = 'block';
}

function saveCookieSettings() {
    const analytics = document.getElementById('cookie-analytics').checked ? 'true' : 'false';
    const marketing = document.getElementById('cookie-marketing').checked ? 'true' : 'false';
    
    setCookie('cookie_consent', 'custom', 365);
    setCookie('cookie_analytics', analytics, 365);
    setCookie('cookie_marketing', marketing, 365);
    
    if (analytics === 'true') {
        loadAnalytics();
    }
    
    document.getElementById('cookie-settings').style.display = 'none';
}

// Fonction pour charger les analytics
function loadAnalytics() {
    if (document.cookie.includes('cookie_analytics=true')) {
        // Insérez ici votre code Google Analytics ou autre
        console.log('Analytics chargés');
    }
}

// Au chargement de la page
document.addEventListener('DOMContentLoaded', function() {
    loadAnalytics();
});
