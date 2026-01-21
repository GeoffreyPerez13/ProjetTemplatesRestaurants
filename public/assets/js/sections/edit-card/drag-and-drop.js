// drag-and-drop.js - Version refaite pour un drag vraiment smooth
(function() {
    'use strict';
    
    // Variables d'état
    let isReorderMode = false;
    let isDragging = false;
    let draggedElement = null;
    let dragClone = null;
    let placeholder = null;
    let dragOffsetX = 0;
    let dragOffsetY = 0;
    let originalIndex = 0;
    
    /**
     * Initialisation principale
     */
    function init() {
        console.log('Drag & Drop initialisé');
        
        // Vérifier si nous sommes en mode images
        if (!isImagesMode()) return;
        
        const imagesGrid = document.getElementById('sortable-images');
        if (!imagesGrid || imagesGrid.children.length === 0) return;
        
        setupEventListeners();
    }
    
    /**
     * Vérifie si on est en mode images
     */
    function isImagesMode() {
        return document.querySelector('.images-mode-container') !== null;
    }
    
    /**
     * Configure tous les écouteurs d'événements
     */
    function setupEventListeners() {
        // Boutons de mode réorganisation
        const startReorderBtn = document.getElementById('start-reorder-btn');
        const saveOrderBtn = document.getElementById('save-order-btn');
        const cancelOrderBtn = document.getElementById('cancel-order-btn');
        
        if (startReorderBtn) startReorderBtn.addEventListener('click', enableReorderMode);
        if (saveOrderBtn) saveOrderBtn.addEventListener('click', saveNewOrder);
        if (cancelOrderBtn) cancelOrderBtn.addEventListener('click', cancelReorder);
        
        // Boutons Monter/Descendre
        document.addEventListener('click', handleMoveButtons);
        
        // Formulaire de réorganisation
        const reorderForm = document.getElementById('reorder-form');
        if (reorderForm) {
            reorderForm.addEventListener('submit', handleFormSubmit);
        }
        
        // Drag & drop manuel
        setupDragEvents();
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
     * Configure les événements de drag
     */
    function setupDragEvents() {
        const imagesGrid = document.getElementById('sortable-images');
        if (!imagesGrid) return;
        
        // Événements pour desktop
        imagesGrid.addEventListener('mousedown', startDrag);
        
        // Événements pour mobile
        imagesGrid.addEventListener('touchstart', startDragTouch, { passive: false });
        
        // Empêcher le drag natif des images
        document.addEventListener('dragstart', preventImageDrag);
    }
    
    /**
     * Empêche le drag natif des images
     */
    function preventImageDrag(e) {
        if (e.target.tagName === 'IMG') e.preventDefault();
    }
    
    /**
     * Démarre le drag avec la souris
     */
    function startDrag(e) {
        if (!isReorderMode || isDragging || !e.target.closest('.image-card')) return;
        
        const imageCard = e.target.closest('.image-card');
        if (e.target.closest('button')) return;
        
        e.preventDefault();
        e.stopPropagation();
        
        // Calculer l'offset par rapport au coin supérieur gauche
        const rect = imageCard.getBoundingClientRect();
        dragOffsetX = e.clientX - rect.left;
        dragOffsetY = e.clientY - rect.top;
        
        beginDrag(imageCard, e.clientX, e.clientY);
        
        // Ajouter les événements globaux
        document.addEventListener('mousemove', handleDragMove);
        document.addEventListener('mouseup', handleDragEnd);
    }
    
    /**
     * Démarre le drag avec le touch
     */
    function startDragTouch(e) {
        if (!isReorderMode || isDragging || !e.target.closest('.image-card')) return;
        
        const imageCard = e.target.closest('.image-card');
        if (e.target.closest('button')) return;
        
        e.preventDefault();
        e.stopPropagation();
        
        const touch = e.touches[0];
        const rect = imageCard.getBoundingClientRect();
        dragOffsetX = touch.clientX - rect.left;
        dragOffsetY = touch.clientY - rect.top;
        
        beginDrag(imageCard, touch.clientX, touch.clientY);
        
        // Ajouter les événements pour touch
        document.addEventListener('touchmove', handleDragMoveTouch, { passive: false });
        document.addEventListener('touchend', handleDragEndTouch);
    }
    
    /**
     * Commence le processus de drag
     */
    function beginDrag(element, clientX, clientY) {
        isDragging = true;
        draggedElement = element;
        
        // Stocker l'index original
        const imagesGrid = element.parentElement;
        originalIndex = Array.from(imagesGrid.children).indexOf(element);
        
        // Créer le placeholder
        createPlaceholder();
        
        // Créer le clone pour le drag
        createDragClone(clientX, clientY);
        
        // Cacher l'élément original
        element.style.opacity = '0';
        element.style.pointerEvents = 'none';
        
        // Désactiver les boutons
        disableAllControls();
    }
    
    /**
     * Crée le clone pour le drag
     */
    function createDragClone(clientX, clientY) {
        const rect = draggedElement.getBoundingClientRect();
        
        // Cloner l'élément
        dragClone = draggedElement.cloneNode(true);
        dragClone.id = 'drag-clone';
        dragClone.classList.add('drag-clone-active');
        
        // Style du clone
        dragClone.style.cssText = `
            position: fixed;
            left: ${clientX - dragOffsetX}px;
            top: ${clientY - dragOffsetY}px;
            width: ${rect.width}px;
            height: ${rect.height}px;
            z-index: 9999;
            opacity: 0.95;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25);
            transform: rotate(2deg);
            pointer-events: none;
            cursor: grabbing;
            margin: 0;
            transition: none;
        `;
        
        // Supprimer les boutons du clone
        const buttons = dragClone.querySelectorAll('button');
        buttons.forEach(btn => btn.remove());
        
        document.body.appendChild(dragClone);
    }
    
    /**
     * Crée le placeholder
     */
    function createPlaceholder() {
        placeholder = document.createElement('div');
        placeholder.className = 'drag-placeholder';
        placeholder.style.cssText = `
            height: ${draggedElement.offsetHeight}px;
            margin: 10px 0;
            background: rgba(52, 152, 219, 0.1);
            border: 2px dashed #3498db;
            border-radius: 12px;
            transition: all 0.2s ease;
        `;
        
        const imagesGrid = draggedElement.parentElement;
        imagesGrid.insertBefore(placeholder, draggedElement);
    }
    
    /**
     * Gère le mouvement du drag (souris)
     */
    function handleDragMove(e) {
        if (!isDragging || !dragClone) return;
        e.preventDefault();
        
        // Mettre à jour la position du clone
        dragClone.style.left = `${e.clientX - dragOffsetX}px`;
        dragClone.style.top = `${e.clientY - dragOffsetY}px`;
        
        // Mettre à jour la position du placeholder
        updatePlaceholderPosition(e.clientY);
    }
    
    /**
     * Gère le mouvement du drag (touch)
     */
    function handleDragMoveTouch(e) {
        if (!isDragging || !dragClone) return;
        e.preventDefault();
        
        const touch = e.touches[0];
        
        // Mettre à jour la position du clone
        dragClone.style.left = `${touch.clientX - dragOffsetX}px`;
        dragClone.style.top = `${touch.clientY - dragOffsetY}px`;
        
        // Mettre à jour la position du placeholder
        updatePlaceholderPosition(touch.clientY);
    }
    
    /**
     * Met à jour la position du placeholder
     */
    function updatePlaceholderPosition(clientY) {
        if (!placeholder || !draggedElement) return;
        
        const imagesGrid = placeholder.parentElement;
        const allCards = Array.from(imagesGrid.children)
            .filter(child => child !== placeholder && child !== draggedElement);
        
        // Trouver la position d'insertion
        let insertIndex = 0;
        
        for (let i = 0; i < allCards.length; i++) {
            const card = allCards[i];
            const rect = card.getBoundingClientRect();
            const cardCenter = rect.top + (rect.height / 2);
            
            if (clientY < cardCenter) {
                insertIndex = i;
                break;
            }
        }
        
        // Si pas trouvé ou dépassement
        if (insertIndex >= allCards.length) {
            insertIndex = allCards.length;
        }
        
        // Déplacer le placeholder si nécessaire
        const targetCard = allCards[insertIndex];
        const currentIndex = Array.from(imagesGrid.children).indexOf(placeholder);
        
        if (currentIndex !== insertIndex) {
            if (targetCard) {
                imagesGrid.insertBefore(placeholder, targetCard);
            } else {
                imagesGrid.appendChild(placeholder);
            }
            
            // Animation des cartes environnantes
            animateSurroundingCards(insertIndex);
        }
    }
    
    /**
     * Anime les cartes autour du placeholder
     */
    function animateSurroundingCards(insertIndex) {
        const imagesGrid = placeholder.parentElement;
        const cards = Array.from(imagesGrid.children)
            .filter(child => child !== placeholder && child !== draggedElement);
        
        // Réinitialiser les animations
        cards.forEach(card => {
            card.classList.remove('card-shift-up', 'card-shift-down');
        });
        
        // Appliquer les animations
        cards.forEach((card, index) => {
            if (index >= insertIndex) {
                card.classList.add('card-shift-down');
            } else {
                card.classList.add('card-shift-up');
            }
        });
    }
    
    /**
     * Termine le drag (souris)
     */
    function handleDragEnd(e) {
        if (!isDragging) return;
        
        document.removeEventListener('mousemove', handleDragMove);
        document.removeEventListener('mouseup', handleDragEnd);
        
        finishDrag();
    }
    
    /**
     * Termine le drag (touch)
     */
    function handleDragEndTouch(e) {
        if (!isDragging) return;
        
        document.removeEventListener('touchmove', handleDragMoveTouch);
        document.removeEventListener('touchend', handleDragEndTouch);
        
        finishDrag();
    }
    
    /**
     * Termine le processus de drag
     */
    function finishDrag() {
        if (!draggedElement || !placeholder || !dragClone) return;
        
        const imagesGrid = draggedElement.parentElement;
        const newIndex = Array.from(imagesGrid.children).indexOf(placeholder);
        
        // Animer le clone vers la position finale
        const placeholderRect = placeholder.getBoundingClientRect();
        
        dragClone.style.transition = 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
        dragClone.style.left = `${placeholderRect.left}px`;
        dragClone.style.top = `${placeholderRect.top}px`;
        dragClone.style.transform = 'rotate(0deg)';
        dragClone.style.opacity = '0.7';
        
        setTimeout(() => {
            // Réinsérer l'élément à la nouvelle position
            imagesGrid.insertBefore(draggedElement, placeholder);
            
            // Restaurer l'élément original
            draggedElement.style.opacity = '1';
            draggedElement.style.pointerEvents = '';
            
            // Supprimer le clone
            if (dragClone.parentNode) {
                dragClone.parentNode.removeChild(dragClone);
            }
            
            // Supprimer le placeholder
            if (placeholder.parentNode) {
                placeholder.parentNode.removeChild(placeholder);
            }
            
            // Réinitialiser les animations des cartes
            const cards = imagesGrid.querySelectorAll('.image-card');
            cards.forEach(card => {
                card.classList.remove('card-shift-up', 'card-shift-down');
                card.style.transform = '';
            });
            
            // Mettre à jour l'interface
            updatePositionNumbers();
            updateOrderInput();
            updateMoveButtonsVisibility();
            
            // Feedback si la position a changé
            if (newIndex !== originalIndex) {
                const imageName = draggedElement.querySelector('.image-name')?.textContent || 'Image';
                const position = newIndex + 1;
                showFeedback(`"${imageName}" déplacé à la position ${position}`, 'success');
            }
            
            // Nettoyer
            cleanupDrag();
        }, 300);
    }
    
    /**
     * Nettoie après le drag
     */
    function cleanupDrag() {
        isDragging = false;
        draggedElement = null;
        dragClone = null;
        placeholder = null;
        
        enableAllControls();
    }
    
    /**
     * Désactive tous les contrôles pendant le drag
     */
    function disableAllControls() {
        document.querySelectorAll('.move-up, .move-down').forEach(btn => {
            btn.style.opacity = '0.5';
            btn.style.pointerEvents = 'none';
        });
    }
    
    /**
     * Réactive tous les contrôles
     */
    function enableAllControls() {
        document.querySelectorAll('.move-up, .move-down').forEach(btn => {
            btn.style.opacity = '';
            btn.style.pointerEvents = '';
        });
    }
    
    /**
     * Active le mode de réorganisation
     */
    function enableReorderMode() {
        if (isReorderMode || isDragging) return;
        
        isReorderMode = true;
        
        // Afficher l'interface
        showReorderInterface();
        
        // Mettre à jour les numéros
        updatePositionNumbers();
        
        showFeedback('Mode réorganisation activé. Glissez une image pour la déplacer.', 'info');
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
     * Désactive le mode de réorganisation
     */
    function disableReorderMode() {
        if (!isReorderMode || isDragging) return;
        
        isReorderMode = false;
        
        hideReorderInterface();
        showFeedback('Réorganisation annulée', 'warning');
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