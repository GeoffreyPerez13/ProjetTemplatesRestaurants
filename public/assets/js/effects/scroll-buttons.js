/**
 * Gestionnaire intelligent des boutons de défilement haut/bas
 * Les boutons ne sont visibles que lorsque le défilement est possible
 */
(function () {
    "use strict";

    let scrollToTopBtn;
    let scrollToBottomBtn;
    let lastScrollTop = 0;
    let isScrolling = false;

    /**
     * Initialise les boutons de défilement
     */
    function initScrollButtons() {
        scrollToTopBtn = document.querySelector('.scroll-to-top');
        scrollToBottomBtn = document.querySelector('.scroll-to-bottom');
        
        if (!scrollToTopBtn || !scrollToBottomBtn) {
            console.warn('Boutons de navigation non trouvés');
            return;
        }
        
        console.log('Initialisation des boutons de navigation intelligents...');
        
        // Fonction pour aller en haut
        scrollToTopBtn.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('Scroll vers le haut');
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
        
        // Fonction pour aller en bas
        scrollToBottomBtn.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('Scroll vers le bas');
            window.scrollTo({
                top: document.body.scrollHeight,
                behavior: 'smooth'
            });
        });
        
        // Événement de scroll pour montrer/cacher les boutons
        window.addEventListener('scroll', handleScroll);
        
        // Événement de redimensionnement pour recalculer la visibilité
        window.addEventListener('resize', checkScrollButtonsVisibility);
        
        // Vérifier la visibilité initiale
        setTimeout(() => {
            checkScrollButtonsVisibility();
            // Vérifier à nouveau après un court délai (pour charger les images)
            setTimeout(checkScrollButtonsVisibility, 500);
        }, 100);
        
        console.log('Boutons de navigation intelligents initialisés avec succès');
    }

    /**
     * Gère l'événement de scroll avec debounce
     */
    function handleScroll() {
        if (isScrolling) return;
        
        isScrolling = true;
        requestAnimationFrame(() => {
            checkScrollButtonsVisibility();
            isScrolling = false;
        });
        
        // Pour les transitions douces, on peut aussi utiliser un debounce simple
        const currentScrollTop = window.pageYOffset || document.documentElement.scrollTop;
        
        // Gérer les animations basées sur la direction du scroll
        if (currentScrollTop > lastScrollTop) {
            // Scroll vers le bas - cacher le bouton haut si on est assez bas
            if (currentScrollTop > 100) {
                scrollToTopBtn.classList.add('scrolling-down');
            }
        } else {
            // Scroll vers le haut - montrer le bouton haut
            scrollToTopBtn.classList.remove('scrolling-down');
        }
        
        lastScrollTop = currentScrollTop <= 0 ? 0 : currentScrollTop;
    }

    /**
     * Vérifie et met à jour la visibilité des boutons
     */
    function checkScrollButtonsVisibility() {
        if (!scrollToTopBtn || !scrollToBottomBtn) return;
        
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        const windowHeight = window.innerHeight;
        const documentHeight = Math.max(
            document.body.scrollHeight,
            document.body.offsetHeight,
            document.documentElement.clientHeight,
            document.documentElement.scrollHeight,
            document.documentElement.offsetHeight
        );
        
        // Vérifier si le défilement est possible
        const canScrollUp = scrollTop > 0;
        const canScrollDown = scrollTop + windowHeight < documentHeight - 50; // Marge de 50px
        
        // Mettre à jour la visibilité avec animation
        updateButtonVisibility(scrollToTopBtn, canScrollUp);
        updateButtonVisibility(scrollToBottomBtn, canScrollDown);
        
        // Ajouter/retirer les classes pour le style
        if (canScrollUp) {
            scrollToTopBtn.classList.add('can-scroll');
        } else {
            scrollToTopBtn.classList.remove('can-scroll');
        }
        
        if (canScrollDown) {
            scrollToBottomBtn.classList.add('can-scroll');
        } else {
            scrollToBottomBtn.classList.remove('can-scroll');
        }
    }

    /**
     * Met à jour la visibilité d'un bouton avec animation
     */
    function updateButtonVisibility(button, shouldBeVisible) {
        if (!button) return;
        
        if (shouldBeVisible) {
            if (!button.classList.contains('visible')) {
                button.classList.add('visible');
                button.style.pointerEvents = 'auto';
                // Animation d'apparition
                button.style.opacity = '0';
                button.style.transform = 'translateY(10px)';
                
                requestAnimationFrame(() => {
                    button.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                    button.style.opacity = '1';
                    button.style.transform = 'translateY(0)';
                });
            }
        } else {
            if (button.classList.contains('visible')) {
                button.classList.remove('visible');
                // Animation de disparition
                button.style.opacity = '0';
                button.style.transform = 'translateY(10px)';
                button.style.pointerEvents = 'none';
                
                // Retirer les styles d'animation après la disparition
                setTimeout(() => {
                    button.style.transition = '';
                }, 300);
            }
        }
    }

    /**
     * Vérifie si la page est assez longue pour nécessiter du défilement
     */
    function isPageScrollable() {
        const windowHeight = window.innerHeight;
        const documentHeight = document.documentElement.scrollHeight;
        
        // Si la hauteur du document est supérieure à la hauteur de la fenêtre + un petit seuil
        return documentHeight > windowHeight + 100;
    }

    /**
     * Désactive les boutons si la page n'est pas défilable
     */
    function checkIfScrollNeeded() {
        if (!scrollToTopBtn || !scrollToBottomBtn) return;
        
        const isScrollable = isPageScrollable();
        
        if (!isScrollable) {
            // Masquer complètement les boutons
            scrollToTopBtn.style.display = 'none';
            scrollToBottomBtn.style.display = 'none';
        } else {
            // Remettre l'affichage par défaut
            scrollToTopBtn.style.display = 'flex';
            scrollToBottomBtn.style.display = 'flex';
            // Vérifier la visibilité
            checkScrollButtonsVisibility();
        }
    }

    // Initialisation automatique quand le DOM est prêt
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            initScrollButtons();
            // Observer les changements du DOM (pour les pages dynamiques)
            observeDOMChanges();
        });
    } else {
        setTimeout(() => {
            initScrollButtons();
            observeDOMChanges();
        }, 100);
    }

    /**
     * Observe les changements du DOM pour mettre à jour la visibilité des boutons
     */
    function observeDOMChanges() {
        // Observer les changements de taille du body
        const resizeObserver = new ResizeObserver(() => {
            checkIfScrollNeeded();
            checkScrollButtonsVisibility();
        });
        
        if (document.body) {
            resizeObserver.observe(document.body);
        }
        
        // Observer les changements de contenu
        const mutationObserver = new MutationObserver(() => {
            setTimeout(checkScrollButtonsVisibility, 100);
        });
        
        mutationObserver.observe(document.body, {
            childList: true,
            subtree: true,
            attributes: true,
            characterData: true
        });
    }

    // Exposer l'API pour un usage externe si nécessaire
    window.ScrollButtons = {
        init: initScrollButtons,
        checkVisibility: checkScrollButtonsVisibility,
        updateButtons: checkScrollButtonsVisibility
    };

})();