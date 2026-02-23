(function () {
    "use strict";

    // Configuration
    const CONFIG = {
        scrollDelay: 1500, // Valeur par défaut
    };

    function init() {
        const scrollParams = window.scrollParams || {};

        // Gestion du scroll vers l'ancre
        const anchor = scrollParams.anchor;
        if (anchor) {
            handleAnchorScroll(anchor, scrollParams.scrollDelay || CONFIG.scrollDelay);
        }

        // Gestion de la disparition des messages
        setupMessageHandlers();

        // Configuration des boutons "Tout (dé)cocher"
        setupCheckboxesToggles();
    }

    /**
     * Gère le scroll vers une ancre avec délai
     */
    function handleAnchorScroll(anchorId, delay) {
        if (!anchorId) return;
        setTimeout(function () {
            const element = document.getElementById(anchorId);
            if (element) {
                const yOffset = -20;
                const y = element.getBoundingClientRect().top + window.pageYOffset + yOffset;
                window.scrollTo({ top: y, behavior: 'smooth' });

                // Effet visuel
                element.style.boxShadow = '0 0 0 3px rgba(52, 152, 219, 0.5)';
                element.style.transition = 'box-shadow 0.3s ease';
                setTimeout(() => element.style.boxShadow = '', 1500);
            }
        }, delay);
    }

    /**
     * Disparition automatique des messages
     */
    function setupMessageHandlers() {
        const messages = document.querySelectorAll('.message-success, .message-error');
        messages.forEach(message => {
            const scrollDelay = window.scrollParams?.scrollDelay || 1500;
            setTimeout(() => {
                message.style.opacity = '0';
                message.style.transition = 'opacity 0.5s ease';
                setTimeout(() => {
                    if (message.parentNode) message.parentNode.removeChild(message);
                }, 500);
            }, scrollDelay);
        });
    }

    /**
     * Configure les boutons "Tout (dé)cocher"
     */
    function setupCheckboxesToggles() {
        document.querySelectorAll('.btn-allergenes-toggle').forEach(btn => {
            // Supprimer les anciens écouteurs pour éviter les doublons
            btn.removeEventListener('click', handleCheckboxesToggle);
            btn.addEventListener('click', handleCheckboxesToggle);
        });
    }

    /**
     * Gestionnaire du clic sur le bouton
     */
    function handleCheckboxesToggle(e) {
        e.preventDefault();
        const targetId = this.dataset.target;
        const container = document.getElementById(targetId);
        if (!container) return;

        const checkboxes = container.querySelectorAll('input[type="checkbox"]');
        const allChecked = Array.from(checkboxes).every(cb => cb.checked);
        checkboxes.forEach(cb => cb.checked = !allChecked);
    }

    // Initialisation
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        setTimeout(init, 100);
    }
})();