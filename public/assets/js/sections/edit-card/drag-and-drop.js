    // drag-and-drop.js - Gestion simplifiée du réordonnancement des images
    (function() {
        'use strict';
        
        // Variables d'état
        let isReorderMode = false;
        
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
            
            // Configurer les événements
            setupEventListeners();
            
            console.log('Module de réorganisation d\'images prêt');
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
            const startReorderBtn = document.getElementById('start-reorder-btn');
            const saveOrderBtn = document.getElementById('save-order-btn');
            const cancelOrderBtn = document.getElementById('cancel-order-btn');
            
            if (startReorderBtn) {
                startReorderBtn.addEventListener('click', enableReorderMode);
            }
            
            if (saveOrderBtn) {
                saveOrderBtn.addEventListener('click', saveNewOrder);
            }
            
            if (cancelOrderBtn) {
                cancelOrderBtn.addEventListener('click', cancelReorder);
            }
            
            // Gérer les boutons de déplacement
            document.addEventListener('click', function(e) {
                // Bouton "Monter"
                if (e.target.closest('.move-up')) {
                    e.preventDefault();
                    const button = e.target.closest('.move-up');
                    const imageCard = button.closest('.image-card');
                    if (imageCard) {
                        moveImageUp(imageCard);
                    }
                }
                
                // Bouton "Descendre"
                if (e.target.closest('.move-down')) {
                    e.preventDefault();
                    const button = e.target.closest('.move-down');
                    const imageCard = button.closest('.image-card');
                    if (imageCard) {
                        moveImageDown(imageCard);
                    }
                }
            });
        }
        
        /**
         * Active le mode de réorganisation
         */
        function enableReorderMode() {
            if (isReorderMode) return;
            
            isReorderMode = true;
            
            // Afficher l'interface de réorganisation
            showReorderInterface();
            
            // Mettre à jour les numéros de position
            updatePositionNumbers();
            
            showFeedback('Mode réorganisation activé. Utilisez les boutons "Monter" et "Descendre" pour réorganiser.', 'info');
        }
        
        /**
         * Affiche l'interface de réorganisation
         */
        function showReorderInterface() {
            const startReorderBtn = document.getElementById('start-reorder-btn');
            const reorderButtons = document.getElementById('reorder-buttons');
            const reorderInstructions = document.getElementById('reorder-instructions');
            const imagesGrid = document.getElementById('sortable-images');
            
            if (startReorderBtn) {
                startReorderBtn.style.display = 'none';
            }
            
            if (reorderButtons) {
                reorderButtons.style.display = 'flex';
            }
            
            if (reorderInstructions) {
                reorderInstructions.style.display = 'block';
            }
            
            if (imagesGrid) {
                imagesGrid.classList.add('reorder-mode');
            }
            
            // Afficher les contrôles de réorganisation
            document.querySelectorAll('.reorder-controls').forEach(controls => {
                controls.style.display = 'block';
            });
            
            // Mettre à jour l'état des boutons
            updateMoveButtonsState();
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
            
            // Mettre à jour l'état des boutons après changement de position
            updateMoveButtonsState();
        }
        
        /**
         * Met à jour l'état des boutons Monter/Descendre
         */
        function updateMoveButtonsState() {
            const imageCards = document.querySelectorAll('.image-card');
            const totalImages = imageCards.length;
            
            imageCards.forEach((card, index) => {
                const moveUpBtn = card.querySelector('.move-up');
                const moveDownBtn = card.querySelector('.move-down');
                
                if (moveUpBtn) {
                    moveUpBtn.disabled = index === 0;
                }
                
                if (moveDownBtn) {
                    moveDownBtn.disabled = index === totalImages - 1;
                }
            });
        }
        
        /**
         * Monte une image
         */
        function moveImageUp(imageCard) {
            const previousSibling = imageCard.previousElementSibling;
            if (previousSibling && previousSibling.classList.contains('image-card')) {
                const imagesGrid = imageCard.parentElement;
                imagesGrid.insertBefore(imageCard, previousSibling);
                
                // Mettre à jour les numéros
                updatePositionNumbers();
                updateOrderInput();
                
                // Feedback
                const imageName = imageCard.querySelector('.image-name')?.textContent || 'Image';
                const position = Array.from(imagesGrid.children).indexOf(imageCard) + 1;
                showFeedback(`"${imageName}" déplacé à la position ${position}`, 'success');
            }
        }
        
        /**
         * Descend une image
         */
        function moveImageDown(imageCard) {
            const nextSibling = imageCard.nextElementSibling;
            if (nextSibling && nextSibling.classList.contains('image-card')) {
                const imagesGrid = imageCard.parentElement;
                const nextNextSibling = nextSibling.nextElementSibling;
                
                if (nextNextSibling) {
                    imagesGrid.insertBefore(imageCard, nextNextSibling);
                } else {
                    imagesGrid.appendChild(imageCard);
                }
                
                // Mettre à jour les numéros
                updatePositionNumbers();
                updateOrderInput();
                
                // Feedback
                const imageName = imageCard.querySelector('.image-name')?.textContent || 'Image';
                const position = Array.from(imagesGrid.children).indexOf(imageCard) + 1;
                showFeedback(`"${imageName}" déplacé à la position ${position}`, 'success');
            }
        }
        
        /**
         * Met à jour l'input hidden avec le nouvel ordre
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
         * Désactive le mode de réorganisation
         */
        function disableReorderMode() {
            if (!isReorderMode) return;
            
            isReorderMode = false;
            
            // Cacher l'interface de réorganisation
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
            
            if (startReorderBtn) {
                startReorderBtn.style.display = 'inline-block';
            }
            
            if (reorderButtons) {
                reorderButtons.style.display = 'none';
            }
            
            if (reorderInstructions) {
                reorderInstructions.style.display = 'none';
            }
            
            if (imagesGrid) {
                imagesGrid.classList.remove('reorder-mode');
            }
            
            // Cacher les contrôles de réorganisation
            document.querySelectorAll('.reorder-controls').forEach(controls => {
                controls.style.display = 'none';
            });
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
                text: 'Les modifications non enregistrées seront perdues.',
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
            const newOrderInput = document.getElementById('new-order-input');
            if (!newOrderInput || !newOrderInput.value) {
                showFeedback('Aucun changement détecté', 'warning');
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
                text: 'Voulez-vous enregistrer le nouvel ordre des images ?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Enregistrer',
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
            const form = document.getElementById('reorder-form');
            if (!form) {
                console.error('Formulaire de réorganisation non trouvé');
                showFeedback('Erreur: Formulaire non trouvé', 'error');
                return;
            }
            
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
            
            // Soumettre après un court délai
            setTimeout(() => {
                form.submit();
            }, 500);
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
                
                // Ajouter après le bouton de démarrage
                const startReorderBtn = document.getElementById('start-reorder-btn');
                const reorderActions = startReorderBtn?.parentElement;
                if (reorderActions) {
                    reorderActions.insertBefore(feedbackEl, startReorderBtn.nextSibling);
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
         * API publique
         */
        window.ImageReorder = {
            init: init,
            enable: enableReorderMode,
            disable: disableReorderMode,
            save: saveNewOrder,
            cancel: cancelReorder,
            isReorderMode: () => isReorderMode
        };
        
        // Initialisation
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(init, 100);
        });
        
    })();