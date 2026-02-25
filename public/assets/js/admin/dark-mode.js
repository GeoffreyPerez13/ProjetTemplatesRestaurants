// dark-mode.js — Toggle dark mode avec persistance localStorage
(function () {
    'use strict';

    const STORAGE_KEY = 'menumiam-dark-mode';

    /**
     * Applique le mode sombre sans transition (au chargement)
     */
    function applyInitialMode() {
        const saved = localStorage.getItem(STORAGE_KEY);
        if (saved === 'true') {
            document.documentElement.classList.add('dark-mode');
        }
    }

    /**
     * Bascule le mode sombre avec transition fluide
     */
    function toggle() {
        document.documentElement.classList.toggle('dark-mode');
        const isDark = document.documentElement.classList.contains('dark-mode');
        localStorage.setItem(STORAGE_KEY, isDark);
    }

    /**
     * Initialise le bouton toggle
     */
    function init() {
        const btn = document.getElementById('dark-mode-toggle');
        if (btn) {
            btn.addEventListener('click', toggle);
        }
    }

    // Appliquer le mode dès que possible (avant le rendu)
    applyInitialMode();

    // Brancher le bouton au DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
