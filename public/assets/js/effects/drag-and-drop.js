// drag-and-drop.js - Version nettoyée
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
     * Initialisation
     */
    function init() {
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
     * Configure les événements
     */
    function setupEventListeners() {
        // Boutons de réorganisation
        const startReorderBtn = document.getElementById('start-reorder-btn');
        const saveOrderBtn = document.getElementById('save-order-btn');
        const cancelOrderBtn = document.getElementById('cancel-order-btn');
        
        if (startReorderBtn) startReorderBtn.addEventListener('click', enableReorderMode);
        if (saveOrderBtn) saveOrderBtn.addEventListener('click', saveNewOrder);
        if (cancelOrderBtn) cancelOrderBtn.addEventListener('click', cancelReorder);
        
        // Boutons Monter/Descendre
        document.addEventListener('click', handleMoveButtons);
        
        // Drag & drop
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
        
        imagesGrid.addEventListener('mousedown', startDrag);
        imagesGrid.addEventListener('touchstart', startDragTouch, { passive: false });
        document.addEventListener('dragstart', preventImageDrag);
    }
    
    /**
     * Empêche le drag natif
     */
    function preventImageDrag(e) {
        if (e.target.tagName === 'IMG') e.preventDefault();
    }
    
    /**
     * Démarre le drag (souris)
     */
    function startDrag(e) {
        if (!isReorderMode || isDragging || !e.target.closest('.image-card')) return;
        
        const imageCard = e.target.closest('.image-card');
        if (e.target.closest('button')) return;
        
        e.preventDefault();
        e.stopPropagation();
        
        const rect = imageCard.getBoundingClientRect();
        dragOffsetX = e.clientX - rect.left;
        dragOffsetY = e.clientY - rect.top;
        
        beginDrag(imageCard, e.clientX, e.clientY);
        
        document.addEventListener('mousemove', handleDragMove);
        document.addEventListener('mouseup', handleDragEnd);
    }
    
    /**
     * Démarre le drag (touch)
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
        
        document.addEventListener('touchmove', handleDragMoveTouch, { passive: false });
        document.addEventListener('touchend', handleDragEndTouch);
    }
    
    /**
     * Commence le drag
     */
    function beginDrag(element, clientX, clientY) {
        isDragging = true;
        draggedElement = element;
        originalIndex = Array.from(element.parentElement.children).indexOf(element);
        
        createPlaceholder();
        createDragClone(clientX, clientY);
        
        element.style.opacity = '0.3';
        element.style.pointerEvents = 'none';
        disableAllControls();
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
        `;
        
        const imagesGrid = draggedElement.parentElement;
        imagesGrid.insertBefore(placeholder, draggedElement);
    }
    
    /**
     * Crée le clone de drag
     */
    function createDragClone(clientX, clientY) {
        const rect = draggedElement.getBoundingClientRect();
        
        dragClone = draggedElement.cloneNode(true);
        dragClone.id = 'drag-clone';
        dragClone.classList.add('drag-clone-active');
        
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
        `;
        
        // Supprimer les boutons
        dragClone.querySelectorAll('button').forEach(btn => btn.remove());
        document.body.appendChild(dragClone);
    }
    
    /**
     * Gère le mouvement (souris)
     */
    function handleDragMove(e) {
        if (!isDragging || !dragClone) return;
        e.preventDefault();
        
        dragClone.style.left = `${e.clientX - dragOffsetX}px`;
        dragClone.style.top = `${e.clientY - dragOffsetY}px`;
        updatePlaceholderPosition(e.clientY);
    }
    
    /**
     * Gère le mouvement (touch)
     */
    function handleDragMoveTouch(e) {
        if (!isDragging || !dragClone) return;
        e.preventDefault();
        
        const touch = e.touches[0];
        dragClone.style.left = `${touch.clientX - dragOffsetX}px`;
        dragClone.style.top = `${touch.clientY - dragOffsetY}px`;
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
        
        if (insertIndex >= allCards.length) {
            insertIndex = allCards.length;
        }
        
        const currentIndex = Array.from(imagesGrid.children).indexOf(placeholder);
        if (currentIndex !== insertIndex) {
            const targetCard = allCards[insertIndex];
            if (targetCard) {
                imagesGrid.insertBefore(placeholder, targetCard);
            } else {
                imagesGrid.appendChild(placeholder);
            }
        }
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
        
        // Animation du clone
        const placeholderRect = placeholder.getBoundingClientRect();
        dragClone.style.transition = 'all 0.3s ease';
        dragClone.style.left = `${placeholderRect.left}px`;
        dragClone.style.top = `${placeholderRect.top}px`;
        dragClone.style.transform = 'rotate(0deg)';
        dragClone.style.opacity = '0.7';
        
        setTimeout(() => {
            // Réinsérer l'élément
            imagesGrid.insertBefore(draggedElement, placeholder);
            
            // Restaurer l'élément
            draggedElement.style.opacity = '';
            draggedElement.style.pointerEvents = '';
            
            // Nettoyer
            if (dragClone.parentNode) dragClone.parentNode.removeChild(dragClone);
            if (placeholder.parentNode) placeholder.parentNode.removeChild(placeholder);
            
            // Mettre à jour
            updatePositionNumbers();
            updateOrderInput();
            updateMoveButtonsVisibility();
            
            // Feedback
            if (newIndex !== originalIndex) {
                showFeedback('Position modifiée', 'success');
            }
            
            // Réinitialiser
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
     * Désactive les contrôles
     */
    function disableAllControls() {
        document.querySelectorAll('.move-up, .move-down').forEach(btn => {
            btn.style.opacity = '0.5';
            btn.style.pointerEvents = 'none';
        });
    }
    
    /**
     * Réactive les contrôles
     */
    function enableAllControls() {
        document.querySelectorAll('.move-up, .move-down').forEach(btn => {
            btn.style.opacity = '';
            btn.style.pointerEvents = '';
        });
    }
    
    /**
     * Active le mode réorganisation
     */
    function enableReorderMode() {
        if (isReorderMode || isDragging) return;
        
        isReorderMode = true;
        showReorderInterface();
        updatePositionNumbers();
        showFeedback('Mode réorganisation activé', 'info');
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
        
        document.querySelectorAll('.reorder-controls').forEach(controls => {
            controls.style.display = 'flex';
        });
        
        updateMoveButtonsVisibility();
    }
    
    /**
     * Désactive le mode réorganisation
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
            imagesGrid.insertBefore(imageCard, previous);
            
            updatePositionNumbers();
            updateOrderInput();
            updateMoveButtonsVisibility();
            showFeedback('Image déplacée vers le haut', 'success');
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
            
            if (afterNext) {
                imagesGrid.insertBefore(imageCard, afterNext);
            } else {
                imagesGrid.appendChild(imageCard);
            }
            
            updatePositionNumbers();
            updateOrderInput();
            updateMoveButtonsVisibility();
            showFeedback('Image déplacée vers le bas', 'success');
        }
    }
    
    /**
     * Met à jour les numéros de position
     */
    function updatePositionNumbers() {
        document.querySelectorAll('.image-card').forEach((card, index) => {
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
                upBtn.classList.toggle('hidden', index === 0);
                upBtn.disabled = index === 0;
            }
            
            if (downBtn) {
                downBtn.classList.toggle('hidden', index === total - 1);
                downBtn.disabled = index === total - 1;
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
        init,
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