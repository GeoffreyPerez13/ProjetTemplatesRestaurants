// edit-card-accordions.js - Gestion des accordéons spécifiques au mode éditable
(function () {
    "use strict";

    const EditCardAccordions = {
        /**
         * Initialisation
         */
        init() {
            if (!this.isEditableMode()) return;
            
            this.setupCategoryControls();
            this.setupPriceAutoFormat();
            this.setupPriceValidation();
        },

        /**
         * Vérifie si on est en mode éditable
         */
        isEditableMode() {
            return document.querySelector('.edit-carte-container') !== null;
        },

        /**
         * Configure les contrôles par catégorie
         */
        setupCategoryControls() {
            // Tout ouvrir pour une catégorie
            document.querySelectorAll('.expand-category').forEach(btn => {
                btn.addEventListener('click', () => {
                    const categoryId = btn.getAttribute('data-category-id');
                    this.expandAllInCategory(categoryId);
                });
            });

            // Tout fermer pour une catégorie
            document.querySelectorAll('.collapse-category').forEach(btn => {
                btn.addEventListener('click', () => {
                    const categoryId = btn.getAttribute('data-category-id');
                    this.collapseAllInCategory(categoryId);
                });
            });
        },

        /**
         * Ouvre tous les accordéons d'une catégorie
         */
        expandAllInCategory(categoryId) {
            if (window.AccordionManager) {
                window.AccordionManager.expandCategory(categoryId);
            } else {
                this.expandAllInCategoryFallback(categoryId);
            }
        },

        /**
         * Fallback pour ouvrir tous les accordéons d'une catégorie
         */
        expandAllInCategoryFallback(categoryId) {
            const categoryBlock = document.getElementById(`category-${categoryId}`);
            if (!categoryBlock) return;

            // Ouvrir les sections principales
            const sections = ['edit-category', 'add-dish', 'edit-dishes'];
            sections.forEach(section => {
                const sectionId = `${section}-${categoryId}`;
                const section = document.getElementById(sectionId);
                if (section && section.classList.contains('collapsed')) {
                    const toggle = document.querySelector(`[data-target="${sectionId}"]`);
                    if (toggle) toggle.click();
                }
            });

            // Ouvrir tous les plats
            setTimeout(() => {
                categoryBlock.querySelectorAll('.dish-accordion-content.collapsed').forEach(content => {
                    const toggle = content.previousElementSibling?.querySelector('.dish-accordion-toggle');
                    if (toggle) toggle.click();
                });
            }, 100);
        },

        /**
         * Ferme tous les accordéons d'une catégorie
         */
        collapseAllInCategory(categoryId) {
            if (window.AccordionManager) {
                window.AccordionManager.collapseCategory(categoryId);
            } else {
                this.collapseAllInCategoryFallback(categoryId);
            }
        },

        /**
         * Fallback pour fermer tous les accordéons d'une catégorie
         */
        collapseAllInCategoryFallback(categoryId) {
            const categoryBlock = document.getElementById(`category-${categoryId}`);
            if (!categoryBlock) return;

            // Fermer les sections principales
            const sections = ['edit-category', 'add-dish', 'edit-dishes'];
            sections.forEach(section => {
                const sectionId = `${section}-${categoryId}`;
                const section = document.getElementById(sectionId);
                if (section && section.classList.contains('expanded')) {
                    const toggle = document.querySelector(`[data-target="${sectionId}"]`);
                    if (toggle) toggle.click();
                }
            });

            // Fermer tous les plats
            setTimeout(() => {
                categoryBlock.querySelectorAll('.dish-accordion-content.expanded').forEach(content => {
                    const toggle = content.previousElementSibling?.querySelector('.dish-accordion-toggle');
                    if (toggle) toggle.click();
                });
            }, 100);
        },

        /**
         * Configure le formatage automatique des prix
         */
        setupPriceAutoFormat() {
            document.querySelectorAll('.price-input').forEach(input => {
                input.addEventListener('input', (e) => {
                    // Autoriser uniquement chiffres, point et virgule
                    e.target.value = e.target.value.replace(/[^0-9.,]/g, '');
                    
                    // Remplacer virgule par point
                    if (e.target.value.includes(',')) {
                        e.target.value = e.target.value.replace(',', '.');
                    }
                    
                    // Un seul point décimal
                    const parts = e.target.value.split('.');
                    if (parts.length > 2) {
                        e.target.value = parts[0] + '.' + parts.slice(1).join('');
                    }
                });

                // Formatage à la perte de focus
                input.addEventListener('blur', (e) => {
                    let value = e.target.value.replace(',', '.');
                    let number = parseFloat(value);
                    
                    if (!isNaN(number) && number >= 0) {
                        e.target.value = number.toFixed(2);
                    }
                });
            });
        },

        /**
         * Configure la validation des prix
         */
        setupPriceValidation() {
            const priceForms = document.querySelectorAll('.new-dish-form, .edit-form');
            
            priceForms.forEach(form => {
                form.addEventListener('submit', (e) => {
                    const priceInput = form.querySelector('input[name="dish_price"]');
                    if (!priceInput) return;
                    
                    let priceValue = priceInput.value.replace(',', '.');
                    let price = parseFloat(priceValue);
                    
                    if (isNaN(price) || price < 0) {
                        e.preventDefault();
                        this.showPriceError(priceInput);
                    } else {
                        // Formater à 2 décimales
                        priceInput.value = price.toFixed(2);
                    }
                });
            });
        },

        /**
         * Affiche une erreur de prix
         */
        showPriceError(input) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Erreur',
                    text: 'Veuillez saisir un prix valide pour le plat.',
                    icon: 'error',
                    confirmButtonColor: '#3085d6'
                });
            } else {
                alert('Veuillez saisir un prix valide pour le plat.');
            }
            input.focus();
        },

        /**
         * API publique
         */
        expandCategory: (categoryId) => this.expandAllInCategory(categoryId),
        collapseCategory: (categoryId) => this.collapseAllInCategory(categoryId),
        closeAllDishesInCategory: (categoryId) => this.collapseAllInCategory(categoryId)
    };

    // API globale
    window.EditCardAccordions = EditCardAccordions;

    // Initialisation
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => EditCardAccordions.init());
    } else {
        setTimeout(() => EditCardAccordions.init(), 150);
    }

    // Fonctions de compatibilité
    window.expandAllInCategory = (categoryId) => EditCardAccordions.expandAllInCategory(categoryId);
    window.collapseAllInCategory = (categoryId) => EditCardAccordions.collapseAllInCategory(categoryId);
    window.closeAllDishesInCategory = (categoryId) => EditCardAccordions.collapseAllInCategory(categoryId);
})();