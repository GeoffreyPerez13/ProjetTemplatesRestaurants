// edit-card.js - Gestion de l'édition de la carte
document.addEventListener("DOMContentLoaded", function () {
    // ==================== SCROLL VERS L'ANCRE ====================
    const anchor = getUrlParameter('anchor');
    if (anchor) {
        scrollToAnchor(anchor);
    }

    // ==================== CONFIRMATION DE SUPPRESSION ====================
    setupDeleteConfirmations();

    // ==================== VALIDATION DES PRIX ====================
    setupPriceValidation();

    // ==================== GESTION DES ACCORDÉONS PAR CATÉGORIE ====================
    setupCategoryAccordionControls();

    // ==================== LIGHTBOX POUR LES IMAGES ====================
    setupImageLightbox();
});

// ==================== FONCTIONS UTILITAIRES ====================

/**
 * Récupère un paramètre d'URL par son nom
 */
function getUrlParameter(name) {
    name = name.replace(/[\[]/, '\\[').replace(/[\]]/, '\\]');
    const regex = new RegExp('[\\?&]' + name + '=([^&#]*)');
    const results = regex.exec(location.search);
    return results === null ? '' : decodeURIComponent(results[1].replace(/\+/g, ' '));
}

/**
 * Scroll vers une ancre spécifique
 */
function scrollToAnchor(anchorId) {
    // Petit délai pour s'assurer que tout est chargé
    setTimeout(function() {
        const element = document.getElementById(anchorId);
        if (element) {
            // Scroll vers l'élément
            element.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });

            // Ouvrir l'accordéon parent si nécessaire
            const accordionSection = element.closest('.accordion-section');
            if (accordionSection) {
                const accordionContent = accordionSection.querySelector('.accordion-content');
                const accordionToggle = accordionSection.querySelector('.accordion-toggle i');
                
                // Vérifier si l'accordéon est fermé
                if (accordionContent && accordionContent.classList.contains('collapsed')) {
                    // Simuler un clic sur le toggle pour ouvrir
                    const toggleBtn = accordionSection.querySelector('.accordion-toggle');
                    if (toggleBtn) {
                        toggleBtn.click();
                    }
                }
                
                // Pour les sous-sections (plats), ouvrir aussi le parent
                const dishAccordion = element.closest('.dish-accordion-content');
                if (dishAccordion && dishAccordion.classList.contains('collapsed')) {
                    const dishToggle = dishAccordion.previousElementSibling?.querySelector('.dish-accordion-toggle');
                    if (dishToggle) {
                        dishToggle.click();
                    }
                }
            }
        }
    }, 300);
}

/**
 * Configure les confirmations de suppression
 */
function setupDeleteConfirmations() {
    document.querySelectorAll("form.inline-form").forEach(form => {
        form.addEventListener("submit", function (e) {
            const deleteCategory = form.querySelector("input[name='delete_category']");
            const deleteDish = form.querySelector("input[name='delete_dish']");

            if (deleteCategory || deleteDish) {
                e.preventDefault();
                
                let itemName = "";
                let type = "";
                let warningMessage = "";

                if (deleteCategory) {
                    const categoryBlock = form.closest('.category-block');
                    const categoryNameElement = categoryBlock.querySelector('strong');
                    itemName = categoryNameElement ? categoryNameElement.textContent.trim() : "cette catégorie";
                    type = "la catégorie";
                    
                    const dishList = categoryBlock.querySelector('.dish-list');
                    const hasPlats = dishList && dishList.querySelector('li');
                    
                    if (hasPlats) {
                        warningMessage = "\n\n⚠️ Attention, tous les plats associés seront également supprimés !";
                    }
                } else if (deleteDish) {
                    const dishEditContainer = form.closest('.dish-edit-container');
                    const dishNameInput = dishEditContainer.querySelector('input[name="dish_name"]');
                    itemName = dishNameInput ? dishNameInput.value.trim() : "cet élément";
                    type = "le plat";
                }

                Swal.fire({
                    title: "Confirmer la suppression",
                    text: `Voulez-vous vraiment supprimer ${type} "${itemName}" ?${warningMessage}`,
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#d33",
                    cancelButtonColor: "#3085d6",
                    confirmButtonText: "Oui, supprimer",
                    cancelButtonText: "Annuler",
                    backdrop: true,
                    allowOutsideClick: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Afficher un loader pendant la suppression
                        Swal.fire({
                            title: 'Suppression en cours...',
                            text: 'Veuillez patienter',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });
                        
                        // Soumettre le formulaire
                        setTimeout(() => {
                            form.submit();
                        }, 100);
                    }
                });
            }
        });
    });
}

/**
 * Configure la validation des prix
 */
function setupPriceValidation() {
    const priceForms = document.querySelectorAll(".new-dish-form, .edit-form");
    
    priceForms.forEach(form => {
        form.addEventListener("submit", function (e) {
            const priceInput = form.querySelector("input[name='dish_price']");
            if (!priceInput) return;
            
            // Remplacer la virgule par un point si nécessaire
            let priceValue = priceInput.value.replace(',', '.');
            
            // Valider que c'est un nombre
            let price = parseFloat(priceValue);
            
            if (isNaN(price) || price < 0) {
                e.preventDefault();
                Swal.fire({
                    title: "Erreur",
                    text: "Veuillez saisir un prix valide pour le plat.",
                    icon: "error",
                    confirmButtonColor: "#3085d6"
                });
                priceInput.focus();
                return;
            }

            // Formater à 2 décimales
            priceInput.value = price.toFixed(2);
        });
    });

    // Validation en temps réel pour une meilleure UX
    document.querySelectorAll("input[name='dish_price']").forEach(input => {
        input.addEventListener('blur', function() {
            let priceValue = this.value.replace(',', '.');
            let price = parseFloat(priceValue);
            
            if (!isNaN(price) && price >= 0) {
                this.value = price.toFixed(2);
            }
        });
    });
}

/**
 * Configure les contrôles d'accordéon par catégorie
 */
function setupCategoryAccordionControls() {
    // Bouton "Tout ouvrir" pour une catégorie
    document.querySelectorAll('.expand-category').forEach(btn => {
        btn.addEventListener('click', function() {
            const categoryId = this.dataset.categoryId;
            expandAllInCategory(categoryId);
        });
    });

    // Bouton "Tout fermer" pour une catégorie
    document.querySelectorAll('.collapse-category').forEach(btn => {
        btn.addEventListener('click', function() {
            const categoryId = this.dataset.categoryId;
            collapseAllInCategory(categoryId);
        });
    });
}

/**
 * Ouvre tous les accordéons d'une catégorie
 */
function expandAllInCategory(categoryId) {
    const categoryBlock = document.querySelector(`#category-${categoryId}`);
    if (!categoryBlock) return;
    
    // Ouvrir tous les accordéons de catégorie
    categoryBlock.querySelectorAll('.accordion-content.collapsed').forEach(content => {
        const toggleBtn = content.previousElementSibling?.querySelector('.accordion-toggle');
        if (toggleBtn && !content.classList.contains('expanded')) {
            toggleBtn.click();
        }
    });
    
    // Ouvrir tous les accordéons de plat
    categoryBlock.querySelectorAll('.dish-accordion-content.collapsed').forEach(content => {
        const toggleBtn = content.previousElementSibling?.querySelector('.dish-accordion-toggle');
        if (toggleBtn && !content.classList.contains('expanded')) {
            toggleBtn.click();
        }
    });
}

/**
 * Ferme tous les accordéons d'une catégorie
 */
function collapseAllInCategory(categoryId) {
    const categoryBlock = document.querySelector(`#category-${categoryId}`);
    if (!categoryBlock) return;
    
    // Fermer tous les accordéons de catégorie
    categoryBlock.querySelectorAll('.accordion-content.expanded').forEach(content => {
        const toggleBtn = content.previousElementSibling?.querySelector('.accordion-toggle');
        if (toggleBtn && !content.classList.contains('collapsed')) {
            toggleBtn.click();
        }
    });
    
    // Fermer tous les accordéons de plat
    categoryBlock.querySelectorAll('.dish-accordion-content.expanded').forEach(content => {
        const toggleBtn = content.previousElementSibling?.querySelector('.dish-accordion-toggle');
        if (toggleBtn && !content.classList.contains('collapsed')) {
            toggleBtn.click();
        }
    });
}

/**
 * Configure la lightbox pour les images
 */
function setupImageLightbox() {
    // Cette fonction initialise la lightbox pour les images
    // La logique complète est dans lightbox.js
    // Ici on s'assure juste que les images sont cliquables
    document.querySelectorAll('.lightbox-image').forEach(img => {
        img.style.cursor = 'pointer';
        img.addEventListener('click', function() {
            // Si la lightbox n'est pas encore initialisée
            if (typeof window.openLightbox === 'function') {
                const src = this.getAttribute('src');
                const alt = this.getAttribute('alt') || '';
                const caption = this.getAttribute('data-caption') || alt;
                window.openLightbox(src, caption);
            }
        });
    });
}

/**
 * Formate automatiquement les champs de prix
 */
function setupPriceAutoFormat() {
    document.querySelectorAll('.price-input').forEach(input => {
        input.addEventListener('input', function(e) {
            // Autoriser uniquement les chiffres, point et virgule
            this.value = this.value.replace(/[^0-9.,]/g, '');
            
            // Remplacer les virgules par des points
            if (this.value.includes(',')) {
                this.value = this.value.replace(',', '.');
            }
            
            // Empêcher plus d'un point décimal
            const parts = this.value.split('.');
            if (parts.length > 2) {
                this.value = parts[0] + '.' + parts.slice(1).join('');
            }
        });
    });
}

// Initialiser le formatage automatique des prix
setupPriceAutoFormat();

// Export des fonctions pour un usage externe si nécessaire
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        scrollToAnchor,
        setupDeleteConfirmations,
        setupPriceValidation,
        setupCategoryAccordionControls
    };
}