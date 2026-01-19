// drag-and-drop.js - Gestion du drag & drop pour les images de carte
(function() {
    'use strict';
    
    // Configuration
    const CONFIG = {
        animationDuration: 200,
        dragHandleClass: 'drag-handle',
        ghostClass: 'sortable-ghost',
        chosenClass: 'sortable-chosen',
        dragClass: 'sortable-drag',
        scrollSensitivity: 60,
        scrollSpeed: 20
    };
    
    // Variables d'état
    let isReorderMode = false;
    let sortableInstance = null;
    let imagesGrid = null;
    let startReorderBtn = null;
    let saveOrderBtn = null;
    let cancelOrderBtn = null;
    let reorderButtons = null;
    let reorderInstructions = null;
    let newOrderInput = null;
    let originalOrder = [];
    
    /**
     * Initialisation principale
     */
    function init() {
        console.log('drag-and-drop.js: Initialisation...');
        
        // Vérifier si nous sommes en mode images
        if (!isImagesMode()) {
            console.log('Mode images non actif, arrêt de l\'initialisation');
            return;
        }
        
        // Initialiser les éléments DOM
        initializeElements();
        
        // Vérifier que les éléments requis existent
        if (!validateRequiredElements()) {
            console.warn('Éléments de réorganisation non trouvés');
            return;
        }
        
        // Sauvegarder l'ordre original
        originalOrder = getCurrentOrder();
        
        // Configurer les événements
        setupEventListeners();
        
        console.log('Module de drag & drop prêt');
    }
    
    /**
     * Vérifie si on est en mode images
     */
    function isImagesMode() {
        return document.querySelector('.images-mode-container') !== null;
    }
    
    /**
     * Initialise les références aux éléments DOM
     */
    function initializeElements() {
        imagesGrid = document.getElementById('sortable-images');
        startReorderBtn = document.getElementById('start-reorder-btn');
        saveOrderBtn = document.getElementById('save-order-btn');
        cancelOrderBtn = document.getElementById('cancel-order-btn');
        reorderButtons = document.getElementById('reorder-buttons');
        reorderInstructions = document.getElementById('reorder-instructions');
        newOrderInput = document.getElementById('new-order-input');
    }
    
    /**
     * Vérifie que les éléments requis existent
     */
    function validateRequiredElements() {
        return imagesGrid && startReorderBtn;
    }
    
    /**
     * Configure tous les écouteurs d'événements
     */
    function setupEventListeners() {
        // Événement principal pour démarrer le réordre
        startReorderBtn.addEventListener('click', enableReorderMode);
        
        // Événements pour les boutons de contrôle
        if (saveOrderBtn) {
            saveOrderBtn.addEventListener('click', saveNewOrder);
        }
        
        if (cancelOrderBtn) {
            cancelOrderBtn.addEventListener('click', cancelReorder);
        }
        
        // Événement pour les fenêtres modales de confirmation
        document.addEventListener('click', handleModalConflicts);
    }
    
    /**
     * Gère les conflits avec les modales
     */
    function handleModalConflicts(e) {
        // Si une modale est ouverte, désactiver temporairement le drag & drop
        if (document.querySelector('.modal-open') || 
            document.querySelector('.swal2-container')) {
            if (sortableInstance) {
                sortableInstance.option('disabled', true);
            }
        }
    }
    
    /**
     * Active le mode de réorganisation
     */
    function enableReorderMode() {
        if (isReorderMode) return;
        
        isReorderMode = true;
        
        // Afficher l'interface de réorganisation
        showReorderInterface();
        
        // Activer le drag & drop
        enableDragAndDrop();
        
        // Mettre à jour les numéros de position
        updatePositionNumbers();
        
        // Afficher un message
        showFeedback(
            'Mode réorganisation activé. Glissez-déposez les images pour les réorganiser.',
            'info'
        );
    }
    
    /**
     * Affiche l'interface de réorganisation
     */
    function showReorderInterface() {
        // Cacher le bouton de départ
        startReorderBtn.style.display = 'none';
        
        // Afficher les boutons de contrôle
        if (reorderButtons) {
            reorderButtons.style.display = 'flex';
        }
        
        // Afficher les instructions
        if (reorderInstructions) {
            reorderInstructions.style.display = 'block';
        }
        
        // Ajouter la classe de style
        imagesGrid.classList.add('reorder-mode');
        
        // Ajouter les handles visuels
        addVisualDragHandles();
    }
    
    /**
     * Ajoute les handles visuels de drag
     */
    function addVisualDragHandles() {
        const imageCards = imagesGrid.querySelectorAll('.image-card');
        
        imageCards.forEach(card => {
            if (!card.querySelector('.drag-handle')) {
                const dragHandle = document.createElement('div');
                dragHandle.className = 'drag-handle';
                dragHandle.innerHTML = '<i class="fas fa-grip-vertical"></i>';
                dragHandle.title = 'Glisser pour réorganiser';
                card.appendChild(dragHandle);
            }
        });
    }
    
    /**
     * Active le drag & drop avec Sortable.js
     */
    function enableDragAndDrop() {
        // Vérifier que Sortable.js est disponible
        if (typeof Sortable === 'undefined') {
            console.error('Sortable.js n\'est pas chargé');
            showFeedback('Erreur: Bibliothèque de drag & drop non chargée', 'error');
            return;
        }
        
        // Détruire l'instance précédente si elle existe
        if (sortableInstance) {
            sortableInstance.destroy();
        }
        
        // Créer une nouvelle instance
        sortableInstance = Sortable.create(imagesGrid, {
            animation: CONFIG.animationDuration,
            ghostClass: CONFIG.ghostClass,
            chosenClass: CONFIG.chosenClass,
            dragClass: CONFIG.dragClass,
            handle: '.drag-handle',
            draggable: '.image-card',
            scroll: true,
            scrollSensitivity: CONFIG.scrollSensitivity,
            scrollSpeed: CONFIG.scrollSpeed,
            
            // Exclusion des éléments interactifs
            filter: '.image-actions, .btn, button, a, .lightbox-trigger',
            preventOnFilter: true,
            
            // Support mobile
            delay: 100,
            delayOnTouchOnly: true,
            touchStartThreshold: 5,
            
            // Événements
            onStart: function(evt) {
                imagesGrid.classList.add('dragging');
                evt.item.style.cursor = 'grabbing';
                const imageName = getImageName(evt.item);
                showFeedback(`Déplacement de "${imageName}"`, 'info');
            },
            
            onEnd: function(evt) {
                imagesGrid.classList.remove('dragging');
                evt.item.style.cursor = '';
                updatePositionNumbers();
                updateOrderInput();
                
                const imageName = getImageName(evt.item);
                const position = getItemPosition(evt.item) + 1;
                showFeedback(`"${imageName}" déplacé à la position ${position}`, 'success');
            },
            
            onSort: function(evt) {
                // Déclencher un événement personnalisé
                const event = new CustomEvent('imagesReordered', {
                    detail: {
                        order: getCurrentOrder(),
                        oldIndex: evt.oldIndex,
                        newIndex: evt.newIndex
                    }
                });
                document.dispatchEvent(event);
            }
        });
    }
    
    /**
     * Récupère le nom d'une image
     */
    function getImageName(card) {
        const nameElement = card.querySelector('.image-name');
        return nameElement ? nameElement.textContent.trim() : 'Image';
    }
    
    /**
     * Récupère la position d'un élément
     */
    function getItemPosition(item) {
        const items = imagesGrid.querySelectorAll('.image-card');
        return Array.from(items).indexOf(item);
    }
    
    /**
     * Récupère l'ordre actuel
     */
    function getCurrentOrder() {
        const imageCards = imagesGrid.querySelectorAll('.image-card');
        return Array.from(imageCards).map(card => 
            card.getAttribute('data-image-id')
        ).filter(id => id); // Filtrer les valeurs nulles
    }
    
    /**
     * Met à jour les numéros de position
     */
    function updatePositionNumbers() {
        const imageCards = imagesGrid.querySelectorAll('.image-card');
        
        imageCards.forEach((card, index) => {
            let positionSpan = card.querySelector('.position-number');
            
            if (!positionSpan) {
                positionSpan = document.createElement('span');
                positionSpan.className = 'position-number';
                const header = card.querySelector('.image-card-header');
                if (header) {
                    header.appendChild(positionSpan);
                }
            }
            
            positionSpan.textContent = index + 1;
            positionSpan.title = `Position ${index + 1} sur ${imageCards.length}`;
        });
    }
    
    /**
     * Met à jour l'input hidden avec le nouvel ordre
     */
    function updateOrderInput() {
        const newOrder = getCurrentOrder();
        
        if (newOrderInput) {
            newOrderInput.value = JSON.stringify(newOrder);
        }
        
        // Mettre à jour le formulaire de réorganisation alphabétique
        const reorderForm = document.getElementById('reorder-images-form');
        if (reorderForm && reorderForm.querySelector('input[name="new_order"]')) {
            reorderForm.querySelector('input[name="new_order"]').value = JSON.stringify(newOrder);
        }
    }
    
    /**
     * Désactive le mode de réorganisation
     */
    function disableReorderMode() {
        if (!isReorderMode) return;
        
        isReorderMode = false;
        
        // Cacher l'interface de réorganisation
        hideReorderInterface();
        
        // Désactiver le drag & drop
        disableDragAndDrop();
        
        // Restaurer l'ordre original
        restoreOriginalOrder();
        
        showFeedback('Réorganisation annulée', 'warning');
    }
    
    /**
     * Cache l'interface de réorganisation
     */
    function hideReorderInterface() {
        // Afficher le bouton de départ
        startReorderBtn.style.display = 'inline-block';
        
        // Cacher les boutons de contrôle
        if (reorderButtons) {
            reorderButtons.style.display = 'none';
        }
        
        // Cacher les instructions
        if (reorderInstructions) {
            reorderInstructions.style.display = 'none';
        }
        
        // Retirer les classes de style
        imagesGrid.classList.remove('reorder-mode');
        imagesGrid.classList.remove('dragging');
        
        // Retirer les handles
        const dragHandles = imagesGrid.querySelectorAll('.drag-handle');
        dragHandles.forEach(handle => handle.remove());
    }
    
    /**
     * Désactive le drag & drop
     */
    function disableDragAndDrop() {
        if (sortableInstance) {
            sortableInstance.destroy();
            sortableInstance = null;
        }
    }
    
    /**
     * Restaure l'ordre original
     */
    function restoreOriginalOrder() {
        const fragment = document.createDocumentFragment();
        
        originalOrder.forEach(id => {
            const card = imagesGrid.querySelector(`[data-image-id="${id}"]`);
            if (card) {
                fragment.appendChild(card);
            }
        });
        
        // Vider et reconstruire la grille
        while (imagesGrid.firstChild) {
            imagesGrid.removeChild(imagesGrid.firstChild);
        }
        imagesGrid.appendChild(fragment);
        
        updatePositionNumbers();
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
            title: 'Annuler la réorganisation ?',
            html: 'Les modifications non enregistrées seront perdues.<br><br>' +
                  '<small class="text-muted">L\'ordre original sera restauré.</small>',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Oui, annuler',
            cancelButtonText: 'Continuer',
            backdrop: true,
            allowOutsideClick: false
        }).then(result => {
            if (result.isConfirmed) {
                disableReorderMode();
            }
        });
    }
    
    /**
     * Sauvegarde le nouvel ordre
     */
    function saveNewOrder() {
        const newOrder = getCurrentOrder();
        
        // Vérifier si l'ordre a changé
        const hasChanged = JSON.stringify(newOrder) !== JSON.stringify(originalOrder);
        
        if (!hasChanged) {
            if (typeof Swal === 'undefined') {
                alert('L\'ordre des images n\'a pas été modifié.');
                return;
            }
            
            Swal.fire({
                title: 'Aucun changement',
                text: 'L\'ordre des images n\'a pas été modifié.',
                icon: 'info',
                confirmButtonText: 'OK'
            });
            return;
        }
        
        // Demander confirmation
        if (typeof Swal === 'undefined') {
            if (confirm('Voulez-vous enregistrer le nouvel ordre des images ?')) {
                submitOrderForm();
            }
            return;
        }
        
        Swal.fire({
            title: 'Confirmer la réorganisation',
            html: 'Voulez-vous enregistrer le nouvel ordre des images ?<br><br>' +
                  '<small class="text-muted">Les images seront affichées dans cet ordre sur la vitrine.</small>',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-save"></i> Enregistrer',
            cancelButtonText: 'Annuler',
            backdrop: true,
            allowOutsideClick: false
        }).then(result => {
            if (result.isConfirmed) {
                submitOrderForm();
            }
        });
    }
    
    /**
     * Soumet le formulaire avec le nouvel ordre
     */
    function submitOrderForm() {
        // Afficher un loader
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Enregistrement en cours...',
                text: 'Veuillez patienter',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
        }
        
        // Mettre à jour l'input
        updateOrderInput();
        
        // Trouver et soumettre le formulaire
        const form = document.getElementById('reorder-form');
        if (form) {
            // Ajouter un champ anchor pour le scroll
            const anchorInput = document.createElement('input');
            anchorInput.type = 'hidden';
            anchorInput.name = 'anchor';
            anchorInput.value = 'images-list';
            form.appendChild(anchorInput);
            
            // Soumettre après un court délai
            setTimeout(() => {
                form.submit();
            }, 500);
        } else {
            console.error('Formulaire de réorganisation non trouvé');
            
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Erreur',
                    text: 'Impossible de trouver le formulaire de réorganisation',
                    icon: 'error'
                });
            }
        }
    }
    
    /**
     * Affiche un feedback à l'utilisateur
     */
    function showFeedback(message, type = 'info') {
        // Créer ou récupérer l'élément de feedback
        let feedbackEl = document.getElementById('reorder-feedback');
        
        if (!feedbackEl) {
            feedbackEl = document.createElement('div');
            feedbackEl.id = 'reorder-feedback';
            feedbackEl.className = 'reorder-feedback';
            
            // Ajouter au début du conteneur d'images
            const container = imagesGrid.parentElement;
            if (container) {
                container.insertBefore(feedbackEl, container.firstChild);
            }
        }
        
        // Mettre à jour le contenu et le style
        feedbackEl.textContent = message;
        feedbackEl.className = `reorder-feedback ${type}`;
        feedbackEl.style.display = 'block';
        
        // Masquer après 3 secondes
        setTimeout(() => {
            feedbackEl.style.display = 'none';
        }, 3000);
    }
    
    /**
     * Réinitialise le module (après AJAX ou rafraîchissement)
     */
    function refresh() {
        // Réinitialiser les références
        initializeElements();
        
        // Sauvegarder le nouvel ordre original
        originalOrder = getCurrentOrder();
        
        // Réinitialiser l'état
        isReorderMode = false;
        
        // Réappliquer les styles si nécessaire
        if (imagesGrid && imagesGrid.classList.contains('reorder-mode')) {
            imagesGrid.classList.remove('reorder-mode');
        }
        
        // Réinitialiser les boutons
        if (startReorderBtn) {
            startReorderBtn.style.display = 'inline-block';
        }
        
        if (reorderButtons) {
            reorderButtons.style.display = 'none';
        }
        
        console.log('Module de drag & drop rafraîchi');
    }
    
    /**
     * API publique
     */
    window.ImageReorder = {
        init: init,
        enable: enableReorderMode,
        disable: disableReorderMode,
        save: saveNewOrder,
        cancel: cancelReorder,
        refresh: refresh,
        getCurrentOrder: getCurrentOrder,
        getOriginalOrder: () => originalOrder,
        isReorderMode: () => isReorderMode
    };
    
    // Initialisation
    document.addEventListener('DOMContentLoaded', function() {
        // Petit délai pour s'assurer que tout est chargé
        setTimeout(init, 100);
    });
    
})();