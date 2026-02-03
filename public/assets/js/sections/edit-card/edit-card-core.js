// edit-card-core.js - Logique principale
(function () {
    "use strict";

    const EditCardCore = {
        config: {
            scrollDelay: 3500,
            messagesDuration: 5000
        },

        /**
         * Initialisation principale
         */
        init() {
            if (!this.isEditCardPage()) return;

            this.handleScrollParams();
            this.setupMessageHandlers();
            this.setupFormHandlers();
            this.setupModeSpecificFeatures();
        },

        /**
         * Vérifie si on est sur la page d'édition de carte
         */
        isEditCardPage() {
            return document.querySelector('.edit-carte-container') || 
                   document.querySelector('.images-mode-container');
        },

        /**
         * Gère les paramètres de scroll depuis PHP
         */
        handleScrollParams() {
            const params = window.scrollParams || {};
            
            // Scroll vers l'ancre
            if (params.anchor) {
                this.scrollToAnchor(params.anchor, params.scrollDelay || this.config.scrollDelay);
            }

            // Gestion des accordéons
            this.handleAccordionParams(params);
        },

        /**
         * Gère les paramètres d'accordéon
         */
        handleAccordionParams(params) {
            if (!window.AccordionManager) return;

            const delays = { close: 500, secondary: 600, open: 700, dish: 800 };
            
            if (params.closeAccordion) {
                setTimeout(() => AccordionManager.close(params.closeAccordion), delays.close);
            }
            
            if (params.closeAccordionSecondary) {
                setTimeout(() => AccordionManager.close(params.closeAccordionSecondary), delays.secondary);
            }
            
            if (params.openAccordion) {
                setTimeout(() => AccordionManager.open(params.openAccordion), delays.open);
            }
            
            if (params.closeDishAccordion) {
                setTimeout(() => AccordionManager.close(params.closeDishAccordion), delays.dish);
            }
        },

        /**
         * Configure la gestion des messages
         */
        setupMessageHandlers() {
            const messages = document.querySelectorAll('.message-success, .message-error');
            
            messages.forEach(message => {
                setTimeout(() => {
                    message.style.opacity = '0';
                    message.style.transition = 'opacity 0.5s ease';
                    
                    setTimeout(() => {
                        if (message.parentNode) {
                            message.parentNode.removeChild(message);
                        }
                    }, 500);
                }, this.config.messagesDuration);
            });
        },

        /**
         * Configure les gestionnaires de formulaires
         */
        setupFormHandlers() {
            // Stocker l'ancre avant soumission
            document.querySelectorAll('form').forEach(form => {
                form.addEventListener('submit', (e) => {
                    const anchorInput = form.querySelector('input[name="anchor"]');
                    if (anchorInput && anchorInput.value) {
                        sessionStorage.setItem('pending_scroll', anchorInput.value);
                    }
                });
            });

            // Restaurer l'ancre
            const pendingScroll = sessionStorage.getItem('pending_scroll');
            if (pendingScroll) {
                this.scrollToAnchor(pendingScroll, this.config.scrollDelay);
                sessionStorage.removeItem('pending_scroll');
            }
        },

        /**
         * Configure les fonctionnalités spécifiques au mode
         */
        setupModeSpecificFeatures() {
            const isImagesMode = document.querySelector('.images-mode-container') !== null;
            
            if (isImagesMode) {
                this.setupImagesMode();
            } else {
                this.setupEditableMode();
            }
        },

        /**
         * Configuration pour le mode images
         */
        setupImagesMode() {
            // Drag & drop
            setTimeout(() => {
                if (window.ImageReorder) {
                    window.ImageReorder.init();
                }
            }, 200);
        },

        /**
         * Configuration pour le mode éditable
         */
        setupEditableMode() {
            // Les fonctionnalités spécifiques sont dans d'autres fichiers
            // Cette fonction sert de placeholder pour les extensions futures
        },

        /**
         * Fait défiler vers une ancre
         */
        scrollToAnchor(anchorId, delay = 0) {
            if (!anchorId) return;

            setTimeout(() => {
                const element = document.getElementById(anchorId);
                if (!element) return;

                // Calcul de la position
                const yOffset = -20;
                const y = element.getBoundingClientRect().top + window.pageYOffset + yOffset;

                // Scroll
                window.scrollTo({
                    top: y,
                    behavior: 'smooth'
                });

                // Effet visuel
                this.highlightElement(element);
            }, delay);
        },

        /**
         * Met en surbrillance un élément
         */
        highlightElement(element) {
            element.style.boxShadow = '0 0 0 3px rgba(52, 152, 219, 0.5)';
            element.style.transition = 'box-shadow 0.3s ease';

            setTimeout(() => {
                element.style.boxShadow = '';
            }, 2000);
        },

        /**
         * Récupère un paramètre d'URL
         */
        getUrlParameter(name) {
            name = name.replace(/[\[]/, '\\[').replace(/[\]]/, '\\]');
            const regex = new RegExp('[\\?&]' + name + '=([^&#]*)');
            const results = regex.exec(location.search);
            return results === null ? '' : decodeURIComponent(results[1].replace(/\+/g, ' '));
        }
    };

    // API publique
    window.EditCardCore = EditCardCore;

    // Fonctions utilitaires globales
    window.getUrlParameter = (name) => EditCardCore.getUrlParameter(name);
    window.scrollToAnchor = (anchorId, delay) => EditCardCore.scrollToAnchor(anchorId, delay);

    // Initialisation
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => EditCardCore.init());
    } else {
        setTimeout(() => EditCardCore.init(), 100);
    }
})();