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
            // Synchroniser l'état initial du chevron
            const targetId = button.getAttribute('data-target');
            const content = document.getElementById(targetId);
            const icon = button.querySelector('i:last-child');
            if (icon && content && content.classList.contains('collapsed')) {
                icon.classList.add('rotated');
            }

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
        
        const icon = button.querySelector('i:last-child');
        if (isCollapsed) {
            // Ouvrir
            content.classList.remove('collapsed');
            button.classList.add('expanded');
            if (icon) icon.classList.remove('rotated');
        } else {
            // Fermer
            content.classList.add('collapsed');
            button.classList.remove('expanded');
            if (icon) icon.classList.add('rotated');
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
