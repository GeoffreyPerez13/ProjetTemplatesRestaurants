/**
 * Gestion des formulaires d'ajout rapide (batch) de catégories et plats
 */
(function() {
    'use strict';

    let categoryRowIndex = 1;
    let dishRowIndex = 1;

    document.addEventListener('DOMContentLoaded', function() {
        initQuickAddCategories();
        initQuickAddDishes();
        initCustomCategorySelect();
    });

    /**
     * Initialise le formulaire d'ajout rapide de catégories
     */
    function initQuickAddCategories() {
        const addButton = document.getElementById('add-category-row');
        const container = document.getElementById('categories-rows-container');

        if (!addButton || !container) return;

        addButton.addEventListener('click', function() {
            const newRow = createCategoryRow(categoryRowIndex);
            container.appendChild(newRow);
            categoryRowIndex++;
            updateRemoveButtons(container);
        });

        // Initialiser les boutons de suppression existants
        updateRemoveButtons(container);
    }

    /**
     * Initialise le formulaire d'ajout rapide de plats
     */
    function initQuickAddDishes() {
        const addButton = document.getElementById('add-dish-row');
        const container = document.getElementById('dishes-rows-container');

        if (!addButton || !container) return;

        addButton.addEventListener('click', function() {
            const newRow = createDishRow(dishRowIndex);
            container.appendChild(newRow);
            dishRowIndex++;
            updateRemoveButtons(container);
        });

        // Initialiser les boutons de suppression existants
        updateRemoveButtons(container);
    }

    /**
     * Crée une nouvelle ligne de catégorie
     */
    function createCategoryRow(index) {
        const row = document.createElement('div');
        row.className = 'quick-add-row';
        row.innerHTML = `
            <div class="quick-add-col-name">
                <input type="text" name="categories[${index}][name]" placeholder="Ex: Entrées" required class="quick-add-input">
            </div>
            <div class="quick-add-col-order">
                <input type="number" name="categories[${index}][order]" value="${index + 1}" min="1" class="quick-add-input-small">
            </div>
            <div class="quick-add-col-image">
                <input type="file" name="category_images[${index}]" accept="image/jpeg,image/png,image/gif,image/webp" class="quick-add-file">
            </div>
            <div class="quick-add-col-actions">
                <button type="button" class="btn-icon btn-remove-row" title="Supprimer cette ligne">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        `;

        // Ajouter l'événement de suppression
        const removeBtn = row.querySelector('.btn-remove-row');
        removeBtn.addEventListener('click', function() {
            row.remove();
            updateRemoveButtons(row.parentElement || document.getElementById('categories-rows-container'));
        });

        return row;
    }

    /**
     * Crée une nouvelle ligne de plat
     */
    function createDishRow(index) {
        const row = document.createElement('div');
        row.className = 'quick-add-row';
        
        // Récupérer la liste des allergènes depuis la première ligne
        const firstRow = document.querySelector('#dishes-rows-container .quick-add-row');
        let allergensHTML = '';
        if (firstRow) {
            const allergensCheckboxes = firstRow.querySelectorAll('.allergen-checkbox-compact');
            allergensCheckboxes.forEach(checkbox => {
                const input = checkbox.querySelector('input');
                const icon = checkbox.querySelector('i');
                const span = checkbox.querySelector('span');
                const value = input.value;
                const iconClass = icon ? icon.className : '';
                const text = span ? span.textContent : '';
                const allergenName = checkbox.getAttribute('data-allergen-name') || text;
                
                allergensHTML += `
                    <label class="allergen-checkbox-compact" data-allergen-name="${allergenName}">
                        <input type="checkbox" name="dishes[${index}][allergens][]" value="${value}">
                        ${icon ? `<i class="${iconClass}"></i>` : ''}
                        <span>${text}</span>
                    </label>
                `;
            });
        }
        
        row.innerHTML = `
            <div class="quick-add-col-dish-name">
                <input type="text" name="dishes[${index}][name]" placeholder="Ex: Salade César" required class="quick-add-input">
            </div>
            <div class="quick-add-col-price">
                <input type="number" name="dishes[${index}][price]" step="0.01" min="0.01" placeholder="12.50" required class="quick-add-input-small">
            </div>
            <div class="quick-add-col-description">
                <input type="text" name="dishes[${index}][description]" placeholder="Description courte (optionnel)" class="quick-add-input">
            </div>
            <div class="quick-add-col-image">
                <input type="file" name="dish_images[${index}]" accept="image/jpeg,image/png,image/gif,image/webp" class="quick-add-file">
            </div>
            <div class="quick-add-col-allergens">
                <button type="button" class="btn-allergens-toggle" data-row="${index}" title="Sélectionner les allergènes">
                    <i class="fas fa-exclamation-triangle"></i> <span class="allergens-count">0</span>
                </button>
                <div class="allergens-popup" data-row="${index}" style="display: none;">
                    <div class="allergens-popup-header">
                        <span>Allergènes</span>
                        <button type="button" class="btn-close-popup"><i class="fas fa-times"></i></button>
                    </div>
                    <div class="allergens-popup-grid">
                        ${allergensHTML}
                    </div>
                </div>
            </div>
            <div class="quick-add-col-actions">
                <button type="button" class="btn-icon btn-remove-row" title="Supprimer cette ligne">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        `;

        // Ajouter l'événement de suppression
        const removeBtn = row.querySelector('.btn-remove-row');
        removeBtn.addEventListener('click', function() {
            row.remove();
            updateRemoveButtons(row.parentElement || document.getElementById('dishes-rows-container'));
        });
        
        // Initialiser les événements pour les allergènes
        initAllergensPopup(row);

        return row;
    }

    /**
     * Met à jour l'état des boutons de suppression
     * Le premier bouton est désactivé s'il n'y a qu'une seule ligne
     */
    function updateRemoveButtons(container) {
        if (!container) return;

        const rows = container.querySelectorAll('.quick-add-row');
        const removeButtons = container.querySelectorAll('.btn-remove-row');

        removeButtons.forEach((btn, index) => {
            if (rows.length === 1) {
                btn.disabled = true;
            } else {
                btn.disabled = false;
            }
        });
    }
    
    /**
     * Initialise les événements pour le popup d'allergènes
     */
    function initAllergensPopup(row) {
        const toggleBtn = row.querySelector('.btn-allergens-toggle');
        const popup = row.querySelector('.allergens-popup');
        const closeBtn = row.querySelector('.btn-close-popup');
        const checkboxes = row.querySelectorAll('.allergen-checkbox-compact input[type="checkbox"]');
        const countSpan = row.querySelector('.allergens-count');
        
        if (!toggleBtn || !popup) return;
        
        // Toggle popup
        toggleBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            // Fermer les autres popups
            document.querySelectorAll('.allergens-popup').forEach(p => {
                if (p !== popup) p.style.display = 'none';
            });
            
            popup.style.display = popup.style.display === 'none' ? 'block' : 'none';
        });
        
        // Close popup
        if (closeBtn) {
            closeBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                popup.style.display = 'none';
            });
        }
        
        // Update count on checkbox change
        checkboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const count = row.querySelectorAll('.allergen-checkbox-compact input[type="checkbox"]:checked').length;
                countSpan.textContent = count;
            });
        });
    }
    
    /**
     * Initialise le custom dropdown pour la sélection de catégorie cible
     */
    function initCustomCategorySelect() {
        const wrapper   = document.getElementById('custom-category-select');
        const toggle    = document.getElementById('custom-category-toggle');
        const dropdown  = document.getElementById('custom-category-dropdown');
        const valueSpan = toggle && toggle.querySelector('.custom-category-value');
        const hidden    = document.getElementById('target-category');
        const error     = document.getElementById('custom-category-error');
        const form      = document.getElementById('quick-add-dishes-form');

        if (!wrapper || !toggle || !dropdown || !hidden) return;

        // Ouvrir / fermer
        toggle.addEventListener('click', function(e) {
            e.stopPropagation();
            const isOpen = dropdown.classList.contains('open');
            closeDropdown();
            if (!isOpen) openDropdown();
        });

        // Sélectionner une option
        dropdown.querySelectorAll('.custom-category-option').forEach(function(opt) {
            opt.addEventListener('click', function() {
                const val  = opt.dataset.value;
                const text = opt.textContent.trim();
                const icon = opt.querySelector('i');

                // Mettre à jour le select caché
                hidden.value = val;

                // Mettre à jour l'affichage du bouton
                valueSpan.innerHTML = (icon ? icon.outerHTML + ' ' : '') + text;
                valueSpan.classList.add('selected');

                // Marquer l'option active
                dropdown.querySelectorAll('.custom-category-option').forEach(o => o.classList.remove('selected'));
                opt.classList.add('selected');

                // Cacher l'erreur si présente
                if (error) error.style.display = 'none';

                closeDropdown();
            });
        });

        // Fermer en cliquant à l'extérieur
        document.addEventListener('click', function(e) {
            if (!wrapper.contains(e.target)) closeDropdown();
        });

        // Validation avant submit : vérifier qu'une catégorie est sélectionnée
        if (form) {
            form.addEventListener('submit', function(e) {
                if (!hidden.value) {
                    e.preventDefault();
                    if (error) error.style.display = 'flex';
                    toggle.focus();
                    wrapper.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            });
        }

        function openDropdown() {
            toggle.classList.add('open');
            dropdown.classList.add('open');
            toggle.setAttribute('aria-expanded', 'true');
        }

        function closeDropdown() {
            toggle.classList.remove('open');
            dropdown.classList.remove('open');
            toggle.setAttribute('aria-expanded', 'false');
        }
    }

    // Initialiser les popups d'allergènes existants au chargement
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('#dishes-rows-container .quick-add-row').forEach(row => {
            initAllergensPopup(row);
        });
        
        // Fermer les popups en cliquant à l'extérieur
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.quick-add-col-allergens')) {
                document.querySelectorAll('.allergens-popup').forEach(popup => {
                    popup.style.display = 'none';
                });
            }
        });
    });

})();
