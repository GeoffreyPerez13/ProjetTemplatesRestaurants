<?php
/**
 * cookie-banner.php — Partial cookie consent pour l'interface admin
 * Inclus par partials/header.php quand le consentement n'a pas été donné.
 * Le JS est externalisé dans /assets/js/admin/cookies.js
 */
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

<script src="/assets/js/admin/cookies.js"></script>
<?php endif; ?>