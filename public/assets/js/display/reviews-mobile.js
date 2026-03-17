// reviews-mobile.js — Déroulement progressif des avis sur mobile
(function () {
    'use strict';

    // Configuration
    const REVIEWS_PER_STEP = 3; // Nombre d'avis à afficher par étape
    const MAX_VISIBLE_DESKTOP = 999; // Sur desktop, tout est visible

    let currentVisible = REVIEWS_PER_STEP;
    let isExpanded = false;

    /**
     * Initialise le système de déroulement progressif
     */
    function init() {
        const toggleBtn = document.getElementById('toggle-reviews');
        const reviewsGrid = document.getElementById('reviews-grid');
        
        if (!toggleBtn || !reviewsGrid) return;

        // Uniquement sur mobile
        if (window.innerWidth > 768) return;

        const allReviews = reviewsGrid.querySelectorAll('.review-card');
        const totalReviews = allReviews.length;

        // Si 3 avis ou moins, pas de bouton
        if (totalReviews <= REVIEWS_PER_STEP) {
            toggleBtn.parentElement.style.display = 'none';
            return;
        }

        // Gestionnaire d'événement
        toggleBtn.addEventListener('click', toggleReviews);

        // Mettre à jour le texte initial
        updateButtonText();
    }

    /**
     * Bascule l'affichage des avis (progressif)
     */
    function toggleReviews() {
        const allReviews = document.querySelectorAll('.review-card');
        const totalReviews = allReviews.length;

        if (isExpanded) {
            // Replier : revenir à 3 avis
            showLimitedReviews();
            isExpanded = false;
        } else {
            // Dérouler progressif
            const hiddenReviews = document.querySelectorAll('.review-hidden');
            const remainingHidden = hiddenReviews.length;
            
            if (remainingHidden <= REVIEWS_PER_STEP) {
                // Moins de 3 avis restants : tout afficher
                showAllReviews();
                isExpanded = true;
            } else {
                // Afficher 3 avis supplémentaires
                showNextReviews();
                // Ne pas changer isExpanded tant qu'il reste des avis
            }
        }

        updateButtonText();
        updateButtonState();
    }

    /**
     * Affiche les 3 prochains avis cachés
     */
    function showNextReviews() {
        const hiddenReviews = document.querySelectorAll('.review-hidden');
        const toShow = Array.from(hiddenReviews).slice(0, REVIEWS_PER_STEP);
        
        toShow.forEach(review => {
            review.classList.remove('review-hidden');
        });
    }

    /**
     * Affiche tous les avis
     */
    function showAllReviews() {
        const hiddenReviews = document.querySelectorAll('.review-hidden');
        hiddenReviews.forEach(review => {
            review.classList.remove('review-hidden');
        });
    }

    /**
     * Affiche seulement les 3 premiers avis
     */
    function showLimitedReviews() {
        const allReviews = document.querySelectorAll('.review-card');
        allReviews.forEach((review, index) => {
            if (index >= REVIEWS_PER_STEP) {
                review.classList.add('review-hidden');
            }
        });
    }

    /**
     * Met à jour le texte du bouton
     */
    function updateButtonText() {
        const toggleBtn = document.getElementById('toggle-reviews');
        const span = toggleBtn.querySelector('span');
        const hiddenReviews = document.querySelectorAll('.review-hidden');
        const remainingHidden = hiddenReviews.length;
        
        if (isExpanded) {
            span.textContent = 'Replier les avis';
        } else if (remainingHidden <= REVIEWS_PER_STEP) {
            span.textContent = 'Voir les derniers avis';
        } else {
            span.textContent = 'Voir 3 autres avis';
        }
    }

    /**
     * Met à jour l'état visuel du bouton
     */
    function updateButtonState() {
        const toggleBtn = document.getElementById('toggle-reviews');
        
        if (isExpanded) {
            toggleBtn.classList.add('expanded');
        } else {
            toggleBtn.classList.remove('expanded');
        }
    }

    // Initialisation au chargement
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // Réinitialiser au redimensionnement (desktop/mobile)
    let resizeTimer;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            if (window.innerWidth > 768) {
                // Desktop : tout afficher
                const hiddenReviews = document.querySelectorAll('.review-hidden');
                hiddenReviews.forEach(review => {
                    review.classList.remove('review-hidden');
                });
                const toggleBtn = document.getElementById('toggle-reviews');
                if (toggleBtn) {
                    toggleBtn.parentElement.style.display = 'none';
                }
            } else {
                // Mobile : réinitialiser
                isExpanded = false;
                init();
            }
        }, 250);
    });
})();
