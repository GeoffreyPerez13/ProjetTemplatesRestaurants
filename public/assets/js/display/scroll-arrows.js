// scroll-arrows.js — Flèches de navigation scroll pour la page landing (visuel identique au panel admin)
(function () {
    'use strict';

    const scrollToTopBtn = document.getElementById('scroll-to-top');
    const scrollToBottomBtn = document.getElementById('scroll-to-bottom');

    if (!scrollToTopBtn || !scrollToBottomBtn) return;

    /**
     * Affiche/masque les flèches selon la position de scroll
     */
    function updateArrowsVisibility() {
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        const scrollHeight = document.documentElement.scrollHeight;
        const clientHeight = document.documentElement.clientHeight;
        const scrollBottom = scrollHeight - scrollTop - clientHeight;

        // Flèche "haut" visible si on a scrollé plus de 300px
        if (scrollTop > 300) {
            scrollToTopBtn.classList.add('visible');
        } else {
            scrollToTopBtn.classList.remove('visible');
        }

        // Flèche "bas" visible si on n'est pas tout en bas (plus de 100px restants)
        if (scrollBottom > 100) {
            scrollToBottomBtn.classList.add('visible');
        } else {
            scrollToBottomBtn.classList.remove('visible');
        }
    }

    /**
     * Scroll vers le haut de la page
     */
    function scrollToTop() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    }

    /**
     * Scroll vers le bas de la page
     */
    function scrollToBottom() {
        window.scrollTo({
            top: document.documentElement.scrollHeight,
            behavior: 'smooth'
        });
    }

    // Événements
    scrollToTopBtn.addEventListener('click', scrollToTop);
    scrollToBottomBtn.addEventListener('click', scrollToBottom);
    window.addEventListener('scroll', updateArrowsVisibility);
    window.addEventListener('resize', updateArrowsVisibility);

    // Initialisation
    updateArrowsVisibility();
})();
