<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Administration') ?></title>
    
    <!-- Meta description pour le SEO -->
    <meta name="description" content="Interface d'administration MenuMiam - Gérez votre carte de restaurant en ligne">
    
    <!-- Favicon -->
    <link rel="icon" href="/assets/favicon.ico" type="image/x-icon">
    
    <!-- CSS principal -->
    <link rel="stylesheet" href="/assets/css/admin.css">
    
    <!-- Font Awesome pour les icônes -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- SweetAlert2 pour les alertes stylisées -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Sortable pour le drag and drop -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.14.0/Sortable.min.js" defer></script>

    <!-- Inclusion de scripts additionnels dynamiques si fournis -->
    <?php if (!empty($scripts)): ?>
        <?php foreach ($scripts as $script): ?>
            <script src="/assets/<?= htmlspecialchars($script) ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</head>

<body>
    <!-- Conteneur principal de toutes les pages admin -->
    <div class="container">
    
    <!-- Bannière cookies (si non acceptés) -->
    <?php 
    // Vérifier si l'utilisateur a déjà fait son choix
    $cookieConsent = $_COOKIE['cookie_consent'] ?? null;
    ?>
    <?php if (!$cookieConsent): ?>
    <div id="cookie-banner" class="cookie-banner">
        <div class="cookie-content">
            <p>
                🍪 Nous utilisons des cookies pour améliorer votre expérience. 
                Certains cookies sont essentiels au fonctionnement du site.
                <a href="?page=legal&section=cookies" class="cookie-link">En savoir plus</a>
            </p>
            <div class="cookie-buttons">
                <button class="cookie-btn accept" onclick="acceptCookies()">Accepter</button>
                <button class="cookie-btn reject" onclick="rejectCookies()">Refuser</button>
                <button class="cookie-btn customize" onclick="showCookieSettings()">Personnaliser</button>
            </div>
        </div>
    </div>

    <div id="cookie-settings" class="cookie-settings" style="display: none;">
        <div class="cookie-settings-content">
            <h3>📊 Gestion des cookies</h3>
            
            <div class="cookie-category">
                <label class="cookie-toggle">
                    <input type="checkbox" id="cookie-essential" checked disabled>
                    <span class="toggle-slider"></span>
                    <strong>Cookies essentiels</strong>
                </label>
                <p class="cookie-desc">Nécessaires au fonctionnement du site (connexion, panier, etc.)</p>
            </div>
            
            <div class="cookie-category">
                <label class="cookie-toggle">
                    <input type="checkbox" id="cookie-analytics" checked>
                    <span class="toggle-slider"></span>
                    <strong>Cookies analytiques</strong>
                </label>
                <p class="cookie-desc">Pour analyser l'usage du site et améliorer nos services</p>
            </div>
            
            <div class="cookie-category">
                <label class="cookie-toggle">
                    <input type="checkbox" id="cookie-marketing">
                    <span class="toggle-slider"></span>
                    <strong>Cookies marketing</strong>
                </label>
                <p class="cookie-desc">Pour personnaliser les publicités et suggestions</p>
            </div>
            
            <div class="cookie-settings-buttons">
                <button class="cookie-btn accept" onclick="saveCookieSettings()">Enregistrer mes préférences</button>
                <button class="cookie-btn cancel" onclick="hideCookieSettings()">Annuler</button>
            </div>
        </div>
    </div>

    <script>
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
    </script>
    <?php endif; ?>