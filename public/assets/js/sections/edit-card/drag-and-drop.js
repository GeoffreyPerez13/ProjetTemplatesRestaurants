// drag-and-drop.js - Version refaite pour un drag vraiment smooth
(function() {
    'use strict';
    
    // Variables d'état
    let isReorderMode = false;
    let isDragging = false;
    let sortableInstance = null;
    
    /**
     * Initialisation principale
     */
    function init() {
        console.log('Drag & Drop initialisé');
        
        // Vérifier si nous sommes en mode images
        if (!isImagesMode()) return;
        
        const imagesGrid = document.getElementById('sortable-images');
        if (!imagesGrid || imagesGrid.children.length === 0) return;
        
        const reorderForm = document.getElementById('reorder-form');
        if (reorderForm) {
            reorderForm.addEventListener('submit', handleFormSubmit);
        }
        
        // Boutons de mode réorganisation
        const startReorderBtn = document.getElementById('start-reorder-btn');
        const saveOrderBtn = document.getElementById('save-order-btn');
        const cancelOrderBtn = document.getElementById('cancel-order-btn');
        
        if (startReorderBtn) startReorderBtn.addEventListener('click', enableReorderMode);
        if (saveOrderBtn) saveOrderBtn.addEventListener('click', saveNewOrder);
        if (cancelOrderBtn) cancelOrderBtn.addEventListener('click', cancelReorder);
        
        // Boutons Monter/Descendre
        document.addEventListener('click', handleMoveButtons);
    }
    
    /**
     * Vérifie si on est en mode images
     */
    function isImagesMode() {
        return document.querySelector('.images-mode-container') !== null;
    }
    
    /**
     * Gère les boutons Monter/Descendre
     */
    function handleMoveButtons(e) {
        if (isDragging) return;
        
        if (e.target.closest('.move-up')) {
            e.preventDefault();
            const button = e.target.closest('.move-up');
            const imageCard = button.closest('.image-card');
            if (imageCard) moveImageUp(imageCard);
        }
        
        if (e.target.closest('.move-down')) {
            e.preventDefault();
            const button = e.target.closest('.move-down');
            const imageCard = button.closest('.image-card');
            if (imageCard) moveImageDown(imageCard);
        }
    }
    
    /**
     * Active le mode de réorganisation
     */
    function enableReorderMode() {
        if (isReorderMode || isDragging) return;
        
        isReorderMode = true;
        
        // Afficher l'interface
        showReorderInterface();
        
        // Initialiser Sortable.js pour le drag & drop
        initSortable();
        
        // Mettre à jour les numéros
        updatePositionNumbers();
        
        showFeedback('Mode réorganisation activé. Glissez une image pour la déplacer.', 'info');
    }
    
    /**
     * Désactive le mode de réorganisation
     */
    function disableReorderMode() {
        if (!isReorderMode || isDragging) return;
        
        isReorderMode = false;
        
        destroySortable();
        hideReorderInterface();
        showFeedback('Réorganisation annulée', 'warning');
    }
    
    /**
     * Initialise Sortable.js sur la grille d'images
     */
    function initSortable() {
        const imagesGrid = document.getElementById('sortable-images');
        if (!imagesGrid || sortableInstance) return;
        
        sortableInstance = new Sortable(imagesGrid, {
            animation: 250,
            easing: 'cubic-bezier(0.4, 0, 0.2, 1)',
            ghostClass: 'drag-placeholder',
            chosenClass: 'dragging',
            dragClass: 'drag-clone-active',
            filter: 'button, .btn, a, input',
            preventOnFilter: false,
            forceFallback: true,
            fallbackOnBody: true,
            fallbackTolerance: 3,
            swapThreshold: 0.5,
            onStart: function() {
                isDragging = true;
            },
            onEnd: function(evt) {
                isDragging = false;
                
                updatePositionNumbers();
                updateOrderInput();
                updateMoveButtonsVisibility();
                
                if (evt.oldIndex !== evt.newIndex) {
                    showFeedback('Image déplacée à la position ' + (evt.newIndex + 1), 'success');
                }
            }
        });
    }
    
    /**
     * Détruit l'instance Sortable
     */
    function destroySortable() {
        if (sortableInstance) {
            sortableInstance.destroy();
            sortableInstance = null;
        }
    }
    
    /**
     * Affiche l'interface de réorganisation
     */
    function showReorderInterface() {
        const startReorderBtn = document.getElementById('start-reorder-btn');
        const reorderButtons = document.getElementById('reorder-buttons');
        const reorderInstructions = document.getElementById('reorder-instructions');
        const imagesGrid = document.getElementById('sortable-images');
        
        if (startReorderBtn) startReorderBtn.style.display = 'none';
        if (reorderButtons) reorderButtons.style.display = 'flex';
        if (reorderInstructions) reorderInstructions.style.display = 'block';
        if (imagesGrid) imagesGrid.classList.add('reorder-mode');
        
        // Afficher les contrôles
        document.querySelectorAll('.reorder-controls').forEach(controls => {
            controls.style.display = 'flex';
        });
        
        updateMoveButtonsVisibility();
    }
    
    /**
     * Cache l'interface de réorganisation
     */
    function hideReorderInterface() {
        const startReorderBtn = document.getElementById('start-reorder-btn');
        const reorderButtons = document.getElementById('reorder-buttons');
        const reorderInstructions = document.getElementById('reorder-instructions');
        const imagesGrid = document.getElementById('sortable-images');
        
        if (startReorderBtn) startReorderBtn.style.display = 'inline-block';
        if (reorderButtons) reorderButtons.style.display = 'none';
        if (reorderInstructions) reorderInstructions.style.display = 'none';
        if (imagesGrid) imagesGrid.classList.remove('reorder-mode');
        
        document.querySelectorAll('.reorder-controls').forEach(controls => {
            controls.style.display = 'none';
        });
    }
    
    /**
     * Monte une image
     */
    function moveImageUp(imageCard) {
        if (isDragging) return;
        
        const previous = imageCard.previousElementSibling;
        if (previous && previous.classList.contains('image-card')) {
            const imagesGrid = imageCard.parentElement;
            
            // Animation
            imageCard.style.transform = 'translateY(-20px)';
            previous.style.transform = 'translateY(20px)';
            
            setTimeout(() => {
                imagesGrid.insertBefore(imageCard, previous);
                
                // Réinitialiser
                imageCard.style.transform = '';
                previous.style.transform = '';
                
                // Mettre à jour
                updatePositionNumbers();
                updateOrderInput();
                updateMoveButtonsVisibility();
                
                showFeedback('Image déplacée vers le haut', 'success');
            }, 300);
        }
    }
    
    /**
     * Descend une image
     */
    function moveImageDown(imageCard) {
        if (isDragging) return;
        
        const next = imageCard.nextElementSibling;
        if (next && next.classList.contains('image-card')) {
            const imagesGrid = imageCard.parentElement;
            const afterNext = next.nextElementSibling;
            
            // Animation
            imageCard.style.transform = 'translateY(20px)';
            next.style.transform = 'translateY(-20px)';
            
            setTimeout(() => {
                if (afterNext) {
                    imagesGrid.insertBefore(imageCard, afterNext);
                } else {
                    imagesGrid.appendChild(imageCard);
                }
                
                // Réinitialiser
                imageCard.style.transform = '';
                next.style.transform = '';
                
                // Mettre à jour
                updatePositionNumbers();
                updateOrderInput();
                updateMoveButtonsVisibility();
                
                showFeedback('Image déplacée vers le bas', 'success');
            }, 300);
        }
    }
    
    /**
     * Met à jour les numéros de position
     */
    function updatePositionNumbers() {
        const imageCards = document.querySelectorAll('.image-card');
        
        imageCards.forEach((card, index) => {
            const positionSpan = card.querySelector('.position-number');
            if (positionSpan) {
                positionSpan.textContent = index + 1;
            }
        });
    }
    
    /**
     * Met à jour la visibilité des boutons
     */
    function updateMoveButtonsVisibility() {
        const imageCards = document.querySelectorAll('.image-card');
        const total = imageCards.length;
        
        imageCards.forEach((card, index) => {
            const upBtn = card.querySelector('.move-up');
            const downBtn = card.querySelector('.move-down');
            
            if (upBtn) {
                if (index === 0) {
                    upBtn.classList.add('hidden');
                    upBtn.disabled = true;
                } else {
                    upBtn.classList.remove('hidden');
                    upBtn.disabled = false;
                }
            }
            
            if (downBtn) {
                if (index === total - 1) {
                    downBtn.classList.add('hidden');
                    downBtn.disabled = true;
                } else {
                    downBtn.classList.remove('hidden');
                    downBtn.disabled = false;
                }
            }
        });
    }
    
    /**
     * Met à jour l'input avec le nouvel ordre
     */
    function updateOrderInput() {
        const newOrderInput = document.getElementById('new-order-input');
        if (!newOrderInput) return;
        
        const imageCards = document.querySelectorAll('.image-card');
        const newOrder = Array.from(imageCards).map(card => 
            card.getAttribute('data-image-id')
        ).filter(id => id);
        
        newOrderInput.value = JSON.stringify(newOrder);
    }
    
    /**
     * Gère la soumission du formulaire
     */
    function handleFormSubmit(e) {
        if (isReorderMode) {
            e.preventDefault();
            saveNewOrder();
        }
    }
    
    /**
     * Sauvegarde le nouvel ordre
     */
    function saveNewOrder() {
        if (isDragging) return;
        
        const newOrderInput = document.getElementById('new-order-input');
        if (!newOrderInput || !newOrderInput.value) {
            showFeedback('Aucun changement détecté', 'warning');
            return;
        }
        
        if (typeof Swal === 'undefined') {
            if (confirm('Enregistrer le nouvel ordre ?')) {
                submitOrderForm();
            }
            return;
        }
        
        Swal.fire({
            title: 'Confirmer',
            text: 'Enregistrer le nouvel ordre des images ?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Enregistrer',
            cancelButtonText: 'Annuler'
        }).then(result => {
            if (result.isConfirmed) {
                submitOrderForm();
            }
        });
    }
    
    /**
     * Soumet le formulaire
     */
    function submitOrderForm() {
        const form = document.getElementById('reorder-form');
        if (!form) return;
        
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Enregistrement...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });
        }
        
        setTimeout(() => form.submit(), 300);
    }
    
    /**
     * Annule la réorganisation
     */
    function cancelReorder() {
        if (isDragging) return;
        
        if (typeof Swal === 'undefined') {
            if (confirm('Annuler la réorganisation ?')) {
                disableReorderMode();
            }
            return;
        }
        
        Swal.fire({
            title: 'Annuler ?',
            text: 'Les modifications non enregistrées seront perdues.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Oui, annuler',
            cancelButtonText: 'Continuer'
        }).then(result => {
            if (result.isConfirmed) {
                disableReorderMode();
            }
        });
    }
    
    /**
     * Affiche un feedback
     */
    function showFeedback(message, type = 'info') {
        let feedbackEl = document.getElementById('reorder-feedback');
        
        if (!feedbackEl) {
            feedbackEl = document.createElement('div');
            feedbackEl.id = 'reorder-feedback';
            feedbackEl.className = 'reorder-feedback';
            
            const reorderActions = document.querySelector('.reorder-actions');
            if (reorderActions) {
                reorderActions.appendChild(feedbackEl);
            }
        }
        
        feedbackEl.textContent = message;
        feedbackEl.className = `reorder-feedback ${type}`;
        feedbackEl.style.display = 'block';
        
        setTimeout(() => {
            feedbackEl.style.opacity = '0';
            setTimeout(() => {
                feedbackEl.style.display = 'none';
                feedbackEl.style.opacity = '1';
            }, 500);
        }, 3000);
    }
    
    /**
     * API publique
     */
    window.ImageReorder = {
        init: init,
        enable: enableReorderMode,
        disable: disableReorderMode,
        save: saveNewOrder,
        cancel: cancelReorder
    };
    
    // Initialisation
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => setTimeout(init, 100));
    } else {
        setTimeout(init, 100);
    }
    
})();