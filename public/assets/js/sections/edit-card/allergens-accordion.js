/**
 * Gestion des accordéons d'allergènes dans les formulaires de plats
 */

(function() {
    'use strict';

    /**
     * Initialise les accordéons d'allergènes
     */
    function initAllergensAccordions() {
        const toggleButtons = document.querySelectorAll('.allergenes-accordion-toggle');
        
        toggleButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                toggleAccordion(this);
            });
        });
    }

    /**
     * Toggle un accordéon d'allergènes
     */
    function toggleAccordion(button) {
        const targetId = button.getAttribute('data-target');
        const content = document.getElementById(targetId);
        
        if (!content) return;
        
        const isCollapsed = content.classList.contains('collapsed');
        
        if (isCollapsed) {
            // Ouvrir
            content.classList.remove('collapsed');
            button.classList.add('expanded');
        } else {
            // Fermer
            content.classList.add('collapsed');
            button.classList.remove('expanded');
        }
    }

    /**
     * Gestion du bouton "Tout (dé)cocher"
     */
    function initToggleAllButtons() {
        const toggleAllButtons = document.querySelectorAll('.btn-allergenes-toggle');
        
        toggleAllButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                toggleAllCheckboxes(this);
            });
        });
    }

    /**
     * Toggle toutes les checkboxes d'allergènes dans un groupe
     */
    function toggleAllCheckboxes(button) {
        const targetId = button.getAttribute('data-target');
        const container = document.getElementById(targetId);
        
        if (!container) return;
        
        const checkboxes = container.querySelectorAll('.allergene-checkbox input[type="checkbox"]');
        const allChecked = Array.from(checkboxes).every(cb => cb.checked);
        
        checkboxes.forEach(checkbox => {
            checkbox.checked = !allChecked;
        });
    }

    // Initialisation au chargement de la page
    document.addEventListener('DOMContentLoaded', function() {
        initAllergensAccordions();
        initToggleAllButtons();
    });

    // Réinitialiser après l'ajout dynamique de contenu (si nécessaire)
    window.reinitAllergensAccordions = function() {
        initAllergensAccordions();
        initToggleAllButtons();
    };
})();
