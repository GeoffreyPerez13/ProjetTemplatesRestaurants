// edit-card-modals.js - Gestion unifiée des confirmations
(function () {
    "use strict";

    const EditCardModals = {
        /**
         * Initialisation
         */
        init() {
            this.setupCategoryImageDeletions();
            this.setupDishImageDeletions();
            this.setupCategoryDeletions();
            this.setupDishDeletions();
            this.setupImageDeletions();
        },

        /**
         * Affiche un loader
         */
        showLoading(message = 'Traitement en cours...') {
            if (typeof Swal === 'undefined') return;
            
            Swal.fire({
                title: message,
                text: 'Veuillez patienter',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
        },

        /**
         * Gestionnaire de confirmation générique
         */
        confirmAction(options) {
            return new Promise((resolve) => {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: options.title,
                        text: options.text,
                        html: options.html,
                        icon: options.icon || 'warning',
                        showCancelButton: true,
                        confirmButtonColor: options.confirmColor || '#d33',
                        cancelButtonColor: options.cancelColor || '#3085d6',
                        confirmButtonText: options.confirmText || 'Oui, supprimer',
                        cancelButtonText: options.cancelText || 'Annuler',
                        backdrop: true,
                        allowOutsideClick: false
                    }).then((result) => {
                        resolve(result.isConfirmed);
                    });
                } else {
                    const confirmed = confirm(options.text || options.title);
                    resolve(confirmed);
                }
            });
        },

        /**
         * Configure les suppressions d'image de catégorie
         */
        setupCategoryImageDeletions() {
            document.querySelectorAll('button[name="remove_category_image"]').forEach(button => {
                button.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    const form = button.closest('form');
                    if (!form) return;
                    
                    const categoryBlock = form.closest('.category-block');
                    const categoryName = categoryBlock?.querySelector('strong')?.textContent?.trim() || 'cette catégorie';
                    
                    this.confirmAction({
                        title: 'Confirmer la suppression',
                        text: `Voulez-vous vraiment supprimer l'image de la catégorie "${categoryName}" ?`
                    }).then(confirmed => {
                        if (confirmed) {
                            this.showLoading('Suppression en cours...');
                            setTimeout(() => form.submit(), 100);
                        }
                    });
                });
            });
        },

        /**
         * Configure les suppressions d'image de plat
         */
        setupDishImageDeletions() {
            document.querySelectorAll('button[name="remove_dish_image"]').forEach(button => {
                button.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    const form = button.closest('form');
                    if (!form) return;
                    
                    const dishContainer = form.closest('.dish-edit-container');
                    const dishName = dishContainer?.querySelector('input[name="dish_name"]')?.value?.trim() || 'ce plat';
                    
                    this.confirmAction({
                        title: 'Confirmer la suppression',
                        text: `Voulez-vous vraiment supprimer l'image du plat "${dishName}" ?`
                    }).then(confirmed => {
                        if (confirmed) {
                            this.showLoading('Suppression en cours...');
                            setTimeout(() => form.submit(), 100);
                        }
                    });
                });
            });
        },

        /**
         * Configure les suppressions de catégories
         */
        setupCategoryDeletions() {
            document.querySelectorAll('input[name="delete_category"]').forEach(input => {
                const form = input.closest('form');
                if (!form) return;
                
                form.addEventListener('submit', (e) => {
                    e.preventDefault();
                    
                    const categoryBlock = form.closest('.category-block');
                    const categoryName = categoryBlock?.querySelector('strong')?.textContent?.trim() || 'cette catégorie';
                    
                    // Vérifier si la catégorie a des plats
                    const dishList = categoryBlock?.querySelector('.dish-list');
                    const hasPlats = dishList && dishList.querySelector('li');
                    const warningMessage = hasPlats ? '\n\n⚠️ Attention, tous les plats associés seront également supprimés !' : '';
                    
                    this.confirmAction({
                        title: 'Confirmer la suppression',
                        text: `Voulez-vous vraiment supprimer la catégorie "${categoryName}" ?${warningMessage}`
                    }).then(confirmed => {
                        if (confirmed) {
                            this.showLoading('Suppression en cours...');
                            setTimeout(() => form.submit(), 100);
                        }
                    });
                });
            });
        },

        /**
         * Configure les suppressions de plats
         */
        setupDishDeletions() {
            document.querySelectorAll('input[name="delete_dish"]').forEach(input => {
                const form = input.closest('form');
                if (!form) return;
                
                form.addEventListener('submit', (e) => {
                    e.preventDefault();
                    
                    const dishEditContainer = form.closest('.dish-edit-container');
                    const dishName = dishEditContainer?.querySelector('input[name="dish_name"]')?.value?.trim() || 'ce plat';
                    
                    this.confirmAction({
                        title: 'Confirmer la suppression',
                        text: `Voulez-vous vraiment supprimer le plat "${dishName}" ?`
                    }).then(confirmed => {
                        if (confirmed) {
                            this.showLoading('Suppression en cours...');
                            setTimeout(() => form.submit(), 100);
                        }
                    });
                });
            });
        },

        /**
         * Configure les suppressions d'images (mode images)
         */
        setupImageDeletions() {
            document.querySelectorAll('button[name="delete_image"]').forEach(button => {
                button.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    const form = button.closest('form');
                    if (!form) return;
                    
                    const imageCard = button.closest('.image-card');
                    const imageName = imageCard?.querySelector('.image-name')?.textContent?.trim() || 'cette image';
                    const isPDF = imageCard?.querySelector('.pdf-preview') !== null;
                    const fileType = isPDF ? 'PDF' : 'image';
                    
                    this.confirmAction({
                        title: 'Confirmer la suppression',
                        html: `Voulez-vous vraiment supprimer l'${fileType} <strong>"${imageName}"</strong> ?`
                    }).then(confirmed => {
                        if (confirmed) {
                            this.showLoading('Suppression en cours...');
                            setTimeout(() => form.submit(), 100);
                        }
                    });
                });
            });
        },

        /**
         * Rafraîchit toutes les confirmations (utile après AJAX)
         */
        refresh() {
            this.init();
        }
    };

    // API globale
    window.EditCardModals = EditCardModals;

    // Initialisation
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => EditCardModals.init());
    } else {
        setTimeout(() => EditCardModals.init(), 200);
    }

    // Fonction de compatibilité
    window.refreshConfirmations = () => EditCardModals.refresh();
})();