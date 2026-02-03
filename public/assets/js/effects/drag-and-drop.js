(function() {
    'use strict';
    
    // Variables d'état
    let isReorderMode = false;
    let draggedCard = null;
    let dragGhost = null;
    let imagesGrid = null;
    
    /**
     * Initialisation
     */
    function init() {
        console.log('Drag & Drop initialisé');
        
        // Vérifier si on est en mode images
        if (!isImagesMode()) return;
        
        imagesGrid = document.getElementById('sortable-images');
        if (!imagesGrid) {
            console.warn('Aucune grille d\'images trouvée');
            return;
        }
        
        if (imagesGrid.children.length === 0) {
            console.warn('Aucune image dans la grille');
            return;
        }
        
        console.log('Grille trouvée avec', imagesGrid.children.length, 'images');
        
        // Initialiser les événements
        setupEventListeners();
        
        // Mettre à jour les numéros de position
        updatePositionNumbers();
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
        // Bouton "Réorganiser l'ordre d'affichage"
        const startReorderBtn = document.getElementById('start-reorder-btn');
        if (startReorderBtn) {
            startReorderBtn.addEventListener('click', enableReorderMode);
        }
        
        // Bouton "Enregistrer le nouvel ordre"
        const saveOrderBtn = document.getElementById('save-order-btn');
        if (saveOrderBtn) {
            saveOrderBtn.addEventListener('click', saveNewOrder);
        }
        
        // Bouton "Annuler"
        const cancelOrderBtn = document.getElementById('cancel-order-btn');
        if (cancelOrderBtn) {
            cancelOrderBtn.addEventListener('click', cancelReorder);
        }
        
        // Boutons Monter/Descendre
        document.addEventListener('click', handleMoveButtons);
        
        // Initialiser le drag & drop
        initDragAndDrop();
    }
    
    /**
     * Initialise le système de drag & drop
     */
    function initDragAndDrop() {
        if (!imagesGrid) return;
        
        const imageCards = imagesGrid.querySelectorAll('.image-card');
        
        imageCards.forEach(card => {
            // Réinitialiser l'état
            card.draggable = false;
            card.classList.remove('dragging', 'drag-over');
            
            // Supprimer les anciens écouteurs
            card.removeEventListener('dragstart', handleDragStart);
            card.removeEventListener('dragend', handleDragEnd);
            card.removeEventListener('dragover', handleDragOver);
            card.removeEventListener('dragenter', handleDragEnter);
            card.removeEventListener('dragleave', handleDragLeave);
            card.removeEventListener('drop', handleDrop);
            
            // Ajouter les nouveaux écouteurs uniquement si en mode réorganisation
            if (isReorderMode) {
                card.draggable = true;
                
                card.addEventListener('dragstart', handleDragStart);
                card.addEventListener('dragend', handleDragEnd);
                card.addEventListener('dragover', handleDragOver);
                card.addEventListener('dragenter', handleDragEnter);
                card.addEventListener('dragleave', handleDragLeave);
                card.addEventListener('drop', handleDrop);
                
                // Afficher la poignée de drag
                const dragHandle = card.querySelector('.drag-handle');
                if (dragHandle) {
                    dragHandle.style.display = 'flex';
                }
            } else {
                // Cacher la poignée de drag
                const dragHandle = card.querySelector('.drag-handle');
                if (dragHandle) {
                    dragHandle.style.display = 'none';
                }
            }
        });
    }
    
    /**
     * Gère le début du drag
     */
    function handleDragStart(e) {
        if (!isReorderMode) {
            e.preventDefault();
            return false;
        }
        
        draggedCard = this;
        
        // Ajouter la classe dragging
        this.classList.add('dragging');
        
        // Définir l'effet de drag
        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('text/plain', this.getAttribute('data-image-id'));
        
        // Créer un élément fantôme pour le feedback visuel
        createDragGhost(this, e.clientX, e.clientY);
        
        // Feedback visuel
        showFeedback('Glissez pour déplacer l\'image', 'info');
        
        console.log('Drag started:', this.getAttribute('data-image-id'));
    }
    
    /**
     * Crée un élément fantôme pour le drag
     */
    function createDragGhost(element, x, y) {
        const rect = element.getBoundingClientRect();
        
        dragGhost = element.cloneNode(true);
        dragGhost.classList.add('drag-ghost');
        
        // Positionner le fantôme
        dragGhost.style.cssText = `
            position: fixed;
            left: ${x - rect.width / 2}px;
            top: ${y - rect.height / 2}px;
            width: ${rect.width}px;
            height: ${rect.height}px;
            z-index: 9999;
            opacity: 0.9;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3);
            transform: rotate(3deg);
            pointer-events: none;
            cursor: grabbing;
            background: white;
            border-radius: 12px;
            overflow: hidden;
        `;
        
        // Supprimer les éléments inutiles du fantôme
        dragGhost.querySelectorAll('.reorder-controls, .image-actions, .drag-handle, .drop-zone-indicator').forEach(el => el.remove());
        
        document.body.appendChild(dragGhost);
    }
    
    /**
     * Gère le survol pendant le drag
     */
    function handleDragOver(e) {
        if (!isReorderMode || !draggedCard) return;
        
        e.preventDefault();
        e.dataTransfer.dropEffect = 'move';
        
        return false;
    }
    
    /**
     * Gère l'entrée dans une zone de drop
     */
    function handleDragEnter(e) {
        if (!isReorderMode || !draggedCard || this === draggedCard) return;
        
        e.preventDefault();
        this.classList.add('drag-over');
        
        // Afficher les indicateurs de zone de drop
        const dropIndicators = this.querySelectorAll('.drop-zone-indicator');
        dropIndicators.forEach(indicator => indicator.classList.add('active'));
    }
    
    /**
     * Gère la sortie d'une zone de drop
     */
    function handleDragLeave(e) {
        if (!isReorderMode) return;
        
        e.preventDefault();
        this.classList.remove('drag-over');
        
        // Cacher les indicateurs de zone de drop
        const dropIndicators = this.querySelectorAll('.drop-zone-indicator');
        dropIndicators.forEach(indicator => indicator.classList.remove('active'));
    }
    
    /**
     * Gère le drop
     */
    function handleDrop(e) {
        if (!isReorderMode || !draggedCard || this === draggedCard) return;
        
        e.preventDefault();
        e.stopPropagation();
        
        // Calculer la position de drop
        const rect = this.getBoundingClientRect();
        const dropY = e.clientY;
        const midY = rect.top + rect.height / 2;
        
        // Déterminer où insérer
        if (dropY < midY) {
            // Insérer avant cet élément
            imagesGrid.insertBefore(draggedCard, this);
        } else {
            // Insérer après cet élément
            const nextSibling = this.nextElementSibling;
            if (nextSibling) {
                imagesGrid.insertBefore(draggedCard, nextSibling);
            } else {
                imagesGrid.appendChild(draggedCard);
            }
        }
        
        // Nettoyer
        this.classList.remove('drag-over');
        const dropIndicators = this.querySelectorAll('.drop-zone-indicator');
        dropIndicators.forEach(indicator => indicator.classList.remove('active'));
        
        // Mettre à jour l'interface
        updatePositionNumbers();
        updateOrderInput();
        updateMoveButtonsVisibility();
        
        // Feedback
        showFeedback('Image déplacée', 'success');
        
        return false;
    }
    
    /**
     * Gère la fin du drag
     */
    function handleDragEnd(e) {
        if (!isReorderMode) return;
        
        // Nettoyer les classes
        if (draggedCard) {
            draggedCard.classList.remove('dragging');
        }
        
        document.querySelectorAll('.image-card.drag-over').forEach(card => {
            card.classList.remove('drag-over');
            card.querySelectorAll('.drop-zone-indicator').forEach(indicator => {
                indicator.classList.remove('active');
            });
        });
        
        // Supprimer l'élément fantôme
        if (dragGhost && dragGhost.parentNode) {
            dragGhost.parentNode.removeChild(dragGhost);
        }
        
        // Réinitialiser
        draggedCard = null;
        dragGhost = null;
    }
    
    /**
     * Gère les boutons Monter/Descendre
     */
    function handleMoveButtons(e) {
        if (!isReorderMode) return;
        
        // Bouton Monter
        if (e.target.closest('.move-up')) {
            e.preventDefault();
            const button = e.target.closest('.move-up');
            const imageCard = button.closest('.image-card');
            if (imageCard) {
                moveImageUp(imageCard);
            }
        }
        
        // Bouton Descendre
        if (e.target.closest('.move-down')) {
            e.preventDefault();
            const button = e.target.closest('.move-down');
            const imageCard = button.closest('.image-card');
            if (imageCard) {
                moveImageDown(imageCard);
            }
        }
    }
    
    /**
     * Monte une image
     */
    function moveImageUp(imageCard) {
        const previous = imageCard.previousElementSibling;
        if (previous && previous.classList.contains('image-card')) {
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
        const next = imageCard.nextElementSibling;
        if (next && next.classList.contains('image-card')) {
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
        const imageCards = imagesGrid.querySelectorAll('.image-card');
        
        imageCards.forEach((card, index) => {
            const positionSpan = card.querySelector('.position-number');
            if (positionSpan) {
                positionSpan.textContent = index + 1;
            }
            
            // Mettre à jour l'attribut data-position pour les boutons
            const moveUpBtn = card.querySelector('.move-up');
            const moveDownBtn = card.querySelector('.move-down');
            
            if (moveUpBtn) {
                moveUpBtn.setAttribute('data-position', index + 1);
            }
            
            if (moveDownBtn) {
                moveDownBtn.setAttribute('data-position', index + 1);
            }
        });
    }
    
    /**
     * Met à jour la visibilité des boutons Monter/Descendre
     */
    function updateMoveButtonsVisibility() {
        const imageCards = imagesGrid.querySelectorAll('.image-card');
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
        
        const imageCards = imagesGrid.querySelectorAll('.image-card');
        const newOrder = Array.from(imageCards).map(card => 
            card.getAttribute('data-image-id')
        ).filter(id => id);
        
        newOrderInput.value = JSON.stringify(newOrder);
    }
    
    /**
     * Active le mode réorganisation
     */
    function enableReorderMode() {
        if (isReorderMode) return;
        
        isReorderMode = true;
        
        // Afficher l'interface de réorganisation
        showReorderInterface();
        
        // Initialiser le drag & drop
        initDragAndDrop();
        
        // Mettre à jour les positions
        updatePositionNumbers();
        updateMoveButtonsVisibility();
        
        // Feedback
        showFeedback('Mode réorganisation activé. Glissez les images pour les réorganiser.', 'info');
    }
    
    /**
     * Affiche l'interface de réorganisation
     */
    function showReorderInterface() {
        const startReorderBtn = document.getElementById('start-reorder-btn');
        const reorderButtons = document.getElementById('reorder-buttons');
        const reorderInstructions = document.getElementById('reorder-instructions');
        
        if (startReorderBtn) startReorderBtn.style.display = 'none';
        if (reorderButtons) reorderButtons.style.display = 'flex';
        if (reorderInstructions) reorderInstructions.style.display = 'block';
        if (imagesGrid) imagesGrid.classList.add('reorder-mode');
        
        // Afficher les contrôles de réorganisation
        document.querySelectorAll('.reorder-controls').forEach(controls => {
            controls.style.display = 'flex';
        });
    }
    
    /**
     * Désactive le mode réorganisation
     */
    function disableReorderMode() {
        if (!isReorderMode) return;
        
        isReorderMode = false;
        
        // Cacher l'interface de réorganisation
        hideReorderInterface();
        
        // Désactiver le drag & drop
        initDragAndDrop();
        
        // Feedback
        showFeedback('Réorganisation annulée', 'warning');
    }
    
    /**
     * Cache l'interface de réorganisation
     */
    function hideReorderInterface() {
        const startReorderBtn = document.getElementById('start-reorder-btn');
        const reorderButtons = document.getElementById('reorder-buttons');
        const reorderInstructions = document.getElementById('reorder-instructions');
        
        if (startReorderBtn) startReorderBtn.style.display = 'inline-block';
        if (reorderButtons) reorderButtons.style.display = 'none';
        if (reorderInstructions) reorderInstructions.style.display = 'none';
        if (imagesGrid) imagesGrid.classList.remove('reorder-mode');
        
        // Cacher les contrôles de réorganisation
        document.querySelectorAll('.reorder-controls').forEach(controls => {
            controls.style.display = 'none';
        });
    }
    
    /**
     * Sauvegarde le nouvel ordre
     */
    function saveNewOrder() {
        const newOrderInput = document.getElementById('new-order-input');
        if (!newOrderInput || !newOrderInput.value) {
            showFeedback('Aucun changement détecté', 'warning');
            return;
        }
        
        // Vérifier s'il y a des changements
        const currentOrder = Array.from(imagesGrid.querySelectorAll('.image-card'))
            .map(card => card.getAttribute('data-image-id'));
        
        const savedOrder = newOrderInput.value ? JSON.parse(newOrderInput.value) : [];
        
        if (JSON.stringify(currentOrder) === JSON.stringify(savedOrder)) {
            showFeedback('Aucun changement à enregistrer', 'info');
            return;
        }
        
        if (typeof Swal === 'undefined') {
            if (confirm('Enregistrer le nouvel ordre des images ?')) {
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
                title: 'Enregistrement en cours...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });
        }
        
        // Soumettre le formulaire
        form.submit();
    }
    
    /**
     * Annule la réorganisation
     */
    function cancelReorder() {
        if (typeof Swal === 'undefined') {
            if (confirm('Annuler la réorganisation ? Les modifications non enregistrées seront perdues.')) {
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
        
        // Auto-dismiss après 3 secondes
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
        cancel: cancelReorder,
        updatePositions: updatePositionNumbers
    };
    
    // Initialisation
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => setTimeout(init, 100));
    } else {
        setTimeout(init, 100);
    }
})();