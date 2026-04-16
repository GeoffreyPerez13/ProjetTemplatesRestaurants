/* MenuMiam - Edit Card - Logique Spécifique */

// Variables globales (seront initialisées par edit-card.php)
let categories = [];
let csrfToken = '';
let baseUrl = '';

// État de l'accordéon
let allDishesExpanded = false;

// Timer pour le debounce de la sauvegarde
let saveCategoryOrderTimer = null;
let isSavingCategoryOrder = false;
let categoryOrderChanged = false;

// Compteur pour la création multiple de catégories
let bulkCategoryCounter = 0;

// ========== INITIALISATION ==========

function initEditCard(categoriesData, token, url) {
    categories = categoriesData;
    csrfToken = token;
    baseUrl = url;
    
    // Charger les plats de chaque catégorie au démarrage
    categories.forEach(category => {
        loadDishes(category.id);
    });
    
    initCategoryDragDrop();
    
    // Afficher le message de succès stocké après rechargement
    const successMessage = sessionStorage.getItem('successMessage');
    if (successMessage) {
        showToast(successMessage, 'success');
        sessionStorage.removeItem('successMessage');
    }
    
    // Stocker l'ordre original dans les inputs pour validation
    document.querySelectorAll('input[data-category-id]').forEach(input => {
        input.setAttribute('data-original-order', input.value);
    });
    
    // Initialiser les event listeners
    initEventListeners();
}

// ========== STATISTIQUES ==========

function updateStats() {
    let totalDishes = 0;
    let totalImages = 0;
    let allergensUsed = new Set();

    document.querySelectorAll('.dish-item').forEach(dish => {
        totalDishes++;
        
        if (dish.querySelector('img')) {
            totalImages++;
        }
        
        const allergenTooltip = dish.querySelector('.allergenes-tooltip');
        if (allergenTooltip) {
            const tooltipText = allergenTooltip.textContent;
            const allergensText = tooltipText.split('Allergènes:')[1];
            if (allergensText) {
                const allergensList = allergensText.split(',').map(a => a.trim());
                allergensList.forEach(allergen => {
                    if (allergen) {
                        allergensUsed.add(allergen);
                    }
                });
            }
        }
    });

    document.querySelectorAll('.category-item img').forEach(() => {
        totalImages++;
    });

    document.getElementById('stat-dishes').textContent = totalDishes;
    document.getElementById('stat-images').textContent = totalImages;
    document.getElementById('stat-allergens').textContent = allergensUsed.size;
}

// ========== DRAG & DROP CATÉGORIES ==========

function initCategoryDragDrop() {
    const categoryItems = document.querySelectorAll('.category-item[draggable="true"]');
    let draggedElement = null;

    categoryItems.forEach(item => {
        item.addEventListener('dragstart', function(e) {
            draggedElement = this;
            this.classList.add('dragging');
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/html', this.innerHTML);
        });

        item.addEventListener('dragend', function(e) {
            this.classList.remove('dragging');
            document.querySelectorAll('.category-item').forEach(cat => {
                cat.classList.remove('drag-over');
            });
            showCategoryOrderSuccess();
        });

        item.addEventListener('dragover', function(e) {
            if (e.preventDefault) {
                e.preventDefault();
            }
            e.dataTransfer.dropEffect = 'move';
            
            if (this !== draggedElement) {
                this.classList.add('drag-over');
            }
            return false;
        });

        item.addEventListener('dragleave', function(e) {
            this.classList.remove('drag-over');
        });

        item.addEventListener('drop', function(e) {
            e.preventDefault();
            e.stopPropagation();

            if (draggedElement !== this) {
                const allCategories = Array.from(document.querySelectorAll('.category-item'));
                const draggedIndex = allCategories.indexOf(draggedElement);
                const targetIndex = allCategories.indexOf(this);

                if (draggedIndex < targetIndex) {
                    this.parentNode.insertBefore(draggedElement, this.nextSibling);
                } else {
                    this.parentNode.insertBefore(draggedElement, this);
                }

                saveCategoryOrder();
            }

            this.classList.remove('drag-over');
            return false;
        });
    });
}

function saveCategoryOrder() {
    categoryOrderChanged = true;
    
    if (saveCategoryOrderTimer) {
        clearTimeout(saveCategoryOrderTimer);
    }

    saveCategoryOrderTimer = setTimeout(() => {
        if (isSavingCategoryOrder) return;

        isSavingCategoryOrder = true;

        const categoryItems = document.querySelectorAll('.category-item');
        const order = Array.from(categoryItems).map(item => item.dataset.id);
        
        categoryItems.forEach((item, index) => {
            const orderInput = item.querySelector('input[data-category-id]');
            if (orderInput) {
                orderInput.value = index + 1;
            }
        });

        const formData = new FormData();
        formData.append('csrf_token', csrfToken);
        formData.append('order', JSON.stringify(order));

        fetch(baseUrl + '/card/category/reorder', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            isSavingCategoryOrder = false;
            if (!data.success) {
                showToast('Erreur lors de la mise à jour de l\'ordre', 'error');
                location.reload();
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            showToast('Erreur lors de la mise à jour de l\'ordre', 'error');
            isSavingCategoryOrder = false;
            categoryOrderChanged = false;
            location.reload();
        });
    }, 200);
}

function showCategoryOrderSuccess() {
    if (categoryOrderChanged) {
        const checkInterval = setInterval(() => {
            if (!isSavingCategoryOrder) {
                clearInterval(checkInterval);
                showToast('Ordre des catégories mis à jour', 'success');
                categoryOrderChanged = false;
            }
        }, 50);
    }
}

function updateCategoryOrder(categoryId, newOrder) {
    if (parseInt(newOrder) < 1) {
        showToast('L\'ordre doit être supérieur ou égal à 1', 'error');
        const input = document.querySelector(`input[data-category-id="${categoryId}"]`);
        if (input) input.value = input.getAttribute('data-original-order') || 1;
        return;
    }
    
    const formData = new FormData();
    formData.append('csrf_token', csrfToken);
    formData.append('category_id', categoryId);
    formData.append('new_order', newOrder);

    fetch(baseUrl + '/card/category/update-order', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            sessionStorage.setItem('successMessage', 'Ordre de la catégorie mis à jour');
            location.reload();
        } else {
            showToast(data.message || 'Erreur lors de la mise à jour', 'error');
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showToast('Erreur lors de la mise à jour de l\'ordre', 'error');
    });
}

function reorganizeCategoriesDOM() {
    const categoriesList = document.querySelector('.categories-list');
    if (!categoriesList) return;
    
    const categoryItems = Array.from(categoriesList.querySelectorAll('.category-item'));
    
    categoryItems.sort((a, b) => {
        const inputA = a.querySelector('input[data-category-id]');
        const inputB = b.querySelector('input[data-category-id]');
        if (!inputA || !inputB) return 0;
        
        const orderA = parseInt(inputA.value) || 0;
        const orderB = parseInt(inputB.value) || 0;
        return orderA - orderB;
    });
    
    categoryItems.forEach((item, index) => {
        categoriesList.appendChild(item);
        
        const orderInput = item.querySelector('input[data-category-id]');
        if (orderInput) {
            orderInput.value = index + 1;
        }
    });
}

// ========== PLATS ==========

function updateDishOrder(dishId, categoryId, newOrder) {
    const formData = new FormData();
    formData.append('csrf_token', csrfToken);
    formData.append('dish_id', dishId);
    formData.append('category_id', categoryId);
    formData.append('new_order', newOrder);

    ajaxRequest(baseUrl + '/card/dish/update-order', 'POST', formData, {
        onSuccess: (data) => {
            showToast('Ordre du plat mis à jour', 'success');
            loadDishes(categoryId);
        },
        onError: (error) => {
            showToast('Erreur lors de la mise à jour de l\'ordre', 'error');
        }
    });
}

function toggleCategoryDishes(categoryId) {
    const dishesContainer = document.getElementById('dishes-' + categoryId);
    const toggleIcon = document.getElementById('toggle-' + categoryId);
    
    if (dishesContainer.style.display === 'none') {
        dishesContainer.style.display = 'grid';
        toggleIcon.style.transform = 'rotate(90deg)';
    } else {
        dishesContainer.style.display = 'none';
        toggleIcon.style.transform = 'rotate(0deg)';
    }
}

function toggleAllDishes() {
    const btn = document.getElementById('toggle-all-btn');
    const icon = btn.querySelector('i');
    
    allDishesExpanded = !allDishesExpanded;
    
    categories.forEach(category => {
        const dishesContainer = document.getElementById('dishes-' + category.id);
        const toggleIcon = document.getElementById('toggle-' + category.id);
        
        if (allDishesExpanded) {
            dishesContainer.style.display = 'grid';
            toggleIcon.style.transform = 'rotate(90deg)';
        } else {
            dishesContainer.style.display = 'none';
            toggleIcon.style.transform = 'rotate(0deg)';
        }
    });
    
    if (allDishesExpanded) {
        icon.className = 'fas fa-chevron-up';
        btn.innerHTML = '<i class="fas fa-chevron-up"></i> Tout replier';
    } else {
        icon.className = 'fas fa-chevron-down';
        btn.innerHTML = '<i class="fas fa-chevron-down"></i> Tout déplier';
    }
}

function loadDishes(categoryId) {
    fetch(baseUrl + '/card/dishes/' + categoryId, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.data.dishes) {
            displayDishes(categoryId, data.data.dishes);
        }
    })
    .catch(error => console.error('Erreur chargement plats:', error));
}

function displayDishes(categoryId, dishes) {
    const container = document.getElementById('dishes-' + categoryId);
    if (!dishes || dishes.length === 0) {
        container.innerHTML = '<p style="color: #999; font-size: 13px; padding: 8px 0;">Aucun plat dans cette catégorie</p>';
        return;
    }

    const totalDishes = dishes.length;
    
    if (!document.getElementById('allergenes-tooltip-style')) {
        const style = document.createElement('style');
        style.id = 'allergenes-tooltip-style';
        style.textContent = `
            .allergenes-icon:hover .allergenes-tooltip {
                visibility: visible !important;
                opacity: 1 !important;
            }
        `;
        document.head.appendChild(style);
    }
    
    container.innerHTML = dishes.map(dish => `
        <div class="dish-item" data-id="${dish.id}" data-category-id="${categoryId}">
            <div class="dish-main-content" style="display: flex; align-items: center; gap: 12px; flex: 1;">
                ${dish.image ? `<img src="${baseUrl}/uploads/plats/${dish.image}" alt="${dish.name}" draggable="false" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px; cursor: pointer; transition: all 0.3s;" onclick="event.stopPropagation(); openLightbox('${baseUrl}/uploads/plats/${dish.image}')" onmouseover="this.style.transform='scale(1.05)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.2)'" onmouseout="this.style.transform='scale(1)'; this.style.boxShadow='none'">` : ''}
                <div class="dish-text-content" style="flex: 1;">
                    <div class="dish-name-wrapper">
                        <span class="dish-name">${dish.name}</span>
                    </div>
                    <div class="dish-price-allergens">
                        ${dish.price ? `<span class="dish-price">${parseFloat(dish.price).toFixed(2)} €</span>` : ''}
                        ${dish.allergenes && dish.allergenes.length > 0 ? `
                            <span class="allergenes-icon" style="position: relative; display: inline-block; margin-left: 8px;">
                                <i class="fas fa-exclamation-triangle" style="color: #f59e0b; font-size: 14px; cursor: help;"></i>
                                <span class="allergenes-tooltip" style="
                                    visibility: hidden;
                                    opacity: 0;
                                    position: absolute;
                                    bottom: 125%;
                                    left: 50%;
                                    transform: translateX(-50%);
                                    background: #1f2937;
                                    color: white;
                                    padding: 8px 12px;
                                    border-radius: 6px;
                                    font-size: 12px;
                                    white-space: nowrap;
                                    z-index: 100;
                                    transition: opacity 0.3s, visibility 0.3s;
                                    box-shadow: 0 4px 12px rgba(0,0,0,0.3);
                                ">
                                    <strong>Allergènes:</strong><br>
                                    ${dish.allergenes.map(a => a.name).join(', ')}
                                    <span style="
                                        position: absolute;
                                        top: 100%;
                                        left: 50%;
                                        transform: translateX(-50%);
                                        border: 6px solid transparent;
                                        border-top-color: #1f2937;
                                    "></span>
                                </span>
                            </span>
                        ` : ''}
                    </div>
                </div>
            </div>
            <div class="dish-actions" style="display: flex; align-items: center; gap: 8px;">
                <div class="dish-order" style="display: flex; align-items: center; gap: 8px;">
                    <label style="font-size: 13px; color: #666;">Ordre:</label>
                    <input type="number" 
                           class="order-input" 
                           value="${dish.display_order || 1}" 
                           min="1" 
                           max="${totalDishes}"
                           data-dish-id="${dish.id}"
                           onchange="updateDishOrder(${dish.id}, ${categoryId}, this.value)"
                           style="width: 60px; padding: 4px 8px; border: 1px solid #ddd; border-radius: 4px; text-align: center;">
                </div>
                <div class="dish-buttons" style="display: flex; gap: 8px;">
                    <button class="icon-btn" draggable="false" onclick="event.stopPropagation(); editDish(${dish.id})" title="Modifier">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="icon-btn delete" draggable="false" onclick="event.stopPropagation(); deleteDish(${dish.id})" title="Supprimer">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    `).join('');
    
    setTimeout(updateStats, 100);
}

function updateDishCount(categoryId) {
    fetch(baseUrl + '/card/dishes/' + categoryId, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.data.dishes) {
            const count = data.data.dishes.length;
            const categoryItem = document.querySelector(`.category-item[data-id="${categoryId}"]`);
            if (categoryItem) {
                const countSpan = categoryItem.querySelector('.category-name').nextElementSibling;
                if (countSpan) {
                    countSpan.textContent = `(${count} plat${count > 1 ? 's' : ''})`;
                }
            }
        }
    })
    .catch(error => console.error('Erreur mise à jour compteur:', error));
}

// ========== RECHERCHE ==========

function filterCards() {
    const searchInput = document.getElementById('search-input');
    const clearBtn = document.getElementById('clear-search-btn');
    const query = searchInput.value.toLowerCase().trim();

    clearBtn.style.display = query ? 'block' : 'none';

    if (!query) {
        document.querySelectorAll('.category-item').forEach(cat => {
            cat.classList.remove('hidden');
        });
        document.querySelectorAll('.dish-item').forEach(dish => {
            dish.classList.remove('hidden');
        });
        return;
    }

    document.querySelectorAll('.category-item').forEach(category => {
        const categoryName = category.querySelector('.category-name').textContent.toLowerCase();
        const dishes = category.querySelectorAll('.dish-item');
        let hasVisibleDish = false;

        dishes.forEach(dish => {
            const dishName = dish.querySelector('.dish-name').textContent.toLowerCase();
            if (dishName.includes(query)) {
                dish.classList.remove('hidden');
                hasVisibleDish = true;
            } else {
                dish.classList.add('hidden');
            }
        });

        if (categoryName.includes(query) || hasVisibleDish) {
            category.classList.remove('hidden');
            if (hasVisibleDish && !categoryName.includes(query)) {
                const dishesContainer = document.getElementById('dishes-' + category.dataset.id);
                const toggleIcon = document.getElementById('toggle-' + category.dataset.id);
                if (dishesContainer && dishesContainer.style.display === 'none') {
                    dishesContainer.style.display = 'grid';
                    toggleIcon.style.transform = 'rotate(90deg)';
                }
            }
        } else {
            category.classList.add('hidden');
        }
    });
}

function clearSearch() {
    document.getElementById('search-input').value = '';
    filterCards();
}

// ========== LIGHTBOX ==========

function openLightbox(imageSrc) {
    const lightbox = document.getElementById('lightbox');
    const lightboxImage = document.getElementById('lightbox-image');
    lightboxImage.src = imageSrc;
    lightbox.classList.add('show');
}

function closeLightbox() {
    const lightbox = document.getElementById('lightbox');
    lightbox.classList.remove('show');
}

// ========== MODALS CATÉGORIES ==========

function openCategoryModal(categoryId = null) {
    const modal = document.getElementById('category-modal');
    const form = document.getElementById('category-form');
    const title = document.getElementById('category-modal-title');
    
    form.reset();
    
    form.querySelectorAll('input, textarea').forEach(field => {
        field.removeAttribute('style');
        field.classList.remove('field-valid', 'field-invalid');
    });
    
    if (categoryId) {
        const category = categories.find(c => c.id == categoryId);
        title.textContent = 'Modifier la catégorie';
        form.action = baseUrl + '/card/category/update';
        document.getElementById('category-id').value = categoryId;
        document.getElementById('category-name').value = category.name;
    } else {
        title.textContent = 'Nouvelle catégorie';
        form.action = baseUrl + '/card/category/create';
    }
    
    modal.classList.add('show');
}

function closeCategoryModal() {
    document.getElementById('category-modal').classList.remove('show');
}

function editCategory(id) {
    openCategoryModal(id);
}

function deleteCategory(id) {
    confirmAction(
        'Supprimer cette catégorie ?',
        'Tous les plats de cette catégorie seront également supprimés.',
        () => {
            const formData = new FormData();
            formData.append('csrf_token', csrfToken);
            formData.append('id', id);
            
            fetch(baseUrl + '/card/category/delete', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    sessionStorage.setItem('successMessage', 'Catégorie supprimée avec succès');
                    location.reload();
                } else {
                    showToast(data.message || 'Erreur lors de la suppression', 'error');
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                showToast('Erreur lors de la suppression', 'error');
            });
        }
    );
}

// ========== MODALS PLATS ==========

function openDishModal(categoryId, dishId = null) {
    const modal = document.getElementById('dish-modal');
    const form = document.getElementById('dish-form');
    const title = document.getElementById('dish-modal-title');
    
    form.reset();
    document.getElementById('dish-category-id').value = categoryId;
    
    form.querySelectorAll('input, textarea').forEach(field => {
        field.removeAttribute('style');
        field.classList.remove('field-valid', 'field-invalid');
    });
    
    document.querySelectorAll('input[name="allergenes[]"]').forEach(cb => cb.checked = false);
    
    if (dishId) {
        title.textContent = 'Modifier le plat';
        form.action = baseUrl + '/card/dish/update';
        document.getElementById('dish-id').value = dishId;
    } else {
        title.textContent = 'Nouveau plat';
        form.action = baseUrl + '/card/dish/create';
    }
    
    modal.classList.add('show');
}

function closeDishModal() {
    document.getElementById('dish-modal').classList.remove('show');
}

function editDish(dishId) {
    fetch(baseUrl + '/card/dish/' + dishId, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.data.dish) {
            const dish = data.data.dish;
            
            const modal = document.getElementById('dish-modal');
            const form = document.getElementById('dish-form');
            const title = document.getElementById('dish-modal-title');
            
            title.textContent = 'Modifier le plat';
            form.action = baseUrl + '/card/dish/update';
            
            document.getElementById('dish-id').value = dish.id;
            document.getElementById('dish-category-id').value = dish.category_id;
            document.getElementById('dish-name').value = dish.name;
            document.getElementById('dish-description').value = dish.description || '';
            document.getElementById('dish-price').value = dish.price || '';
            
            document.querySelectorAll('input[name="allergenes[]"]').forEach(cb => cb.checked = false);
            if (dish.allergene_ids) {
                const allergeneIds = dish.allergene_ids.split(',');
                allergeneIds.forEach(id => {
                    const checkbox = document.querySelector(`input[name="allergenes[]"][value="${id}"]`);
                    if (checkbox) checkbox.checked = true;
                });
            }
            
            if (dish.image) {
                const preview = document.getElementById('dish-image-preview');
                preview.querySelector('img').src = baseUrl + '/uploads/plats/' + dish.image;
                preview.style.display = 'inline-block';
            }
            
            modal.classList.add('show');
        } else {
            showToast(data.message || 'Erreur lors du chargement du plat', 'error');
        }
    })
    .catch(error => {
        console.error('Erreur chargement plat:', error);
        showToast('Erreur de connexion au serveur', 'error');
    });
}

function deleteDish(dishId) {
    confirmAction(
        'Supprimer ce plat ?',
        'Cette action est irréversible.',
        () => {
            const dishElement = document.querySelector(`.dish-item[data-id="${dishId}"]`);
            const categoryId = dishElement ? dishElement.dataset.categoryId : null;
            
            const formData = new FormData();
            formData.append('csrf_token', csrfToken);
            formData.append('id', dishId);
            
            fetch(baseUrl + '/card/dish/delete', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Plat supprimé avec succès', 'success');
                    
                    if (categoryId) {
                        loadDishes(categoryId);
                        updateDishCount(categoryId);
                    }
                } else {
                    showToast(data.message || 'Erreur lors de la suppression', 'error');
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                showToast('Erreur lors de la suppression', 'error');
            });
        }
    );
}

// ========== BULK CATÉGORIES ==========

function openBulkCategoryModal() {
    const modal = document.getElementById('bulk-category-modal');
    const container = document.getElementById('bulk-categories-container');
    
    container.innerHTML = '';
    bulkCategoryCounter = 0;
    
    addBulkCategoryRow();
    
    modal.classList.add('show');
}

function closeBulkCategoryModal() {
    document.getElementById('bulk-category-modal').classList.remove('show');
}

function addBulkCategoryRow() {
    const container = document.getElementById('bulk-categories-container');
    const currentCategories = document.querySelectorAll('.category-item').length;
    const nextOrder = currentCategories + bulkCategoryCounter + 1;
    
    const row = document.createElement('div');
    row.className = 'bulk-category-row';
    row.dataset.index = bulkCategoryCounter;
    row.style.cssText = 'display: grid; grid-template-columns: 80px 1fr 200px 40px; gap: 12px; align-items: center; padding: 12px; background: #f9fafb; border-radius: 8px; margin-bottom: 12px;';
    
    row.innerHTML = `
        <div>
            <label style="font-size: 13px; color: #666; display: block; margin-bottom: 4px;">Ordre</label>
            <input type="number" 
                   class="bulk-order" 
                   value="${nextOrder}" 
                   min="1" 
                   style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
        </div>
        <div>
            <label style="font-size: 13px; color: #666; display: block; margin-bottom: 4px;">Nom <span style="color: #ef4444;">*</span></label>
            <input type="text" 
                   class="bulk-name" 
                   placeholder="Ex: Entrées" 
                   style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
        </div>
        <div>
            <label style="font-size: 13px; color: #666; display: block; margin-bottom: 4px;">Image</label>
            <input type="file" 
                   class="bulk-image" 
                   accept="image/jpeg,image/jpg,image/png,image/webp">
        </div>
        <div style="padding-top: 20px;">
            <button type="button" 
                    class="icon-btn delete" 
                    onclick="removeBulkCategoryRow(${bulkCategoryCounter})"
                    title="Supprimer">
                <i class="fas fa-trash"></i>
            </button>
        </div>
    `;
    
    container.appendChild(row);
    bulkCategoryCounter++;
}

function removeBulkCategoryRow(index) {
    const row = document.querySelector(`.bulk-category-row[data-index="${index}"]`);
    if (row) {
        row.remove();
    }
}

async function saveBulkCategories() {
    const rows = document.querySelectorAll('.bulk-category-row');
    
    if (rows.length === 0) {
        showToast('Veuillez ajouter au moins une catégorie', 'error');
        return;
    }
    
    const categories = [];
    let hasError = false;
    
    rows.forEach(row => {
        const name = row.querySelector('.bulk-name').value.trim();
        const order = parseInt(row.querySelector('.bulk-order').value);
        const imageFile = row.querySelector('.bulk-image').files[0];
        
        if (!name) {
            hasError = true;
            row.querySelector('.bulk-name').style.borderColor = '#ef4444';
            return;
        } else {
            row.querySelector('.bulk-name').style.borderColor = '#10b981';
        }
        
        categories.push({
            name: name,
            order: order,
            imageFile: imageFile
        });
    });
    
    if (hasError) {
        showToast('Veuillez remplir tous les noms de catégories', 'error');
        return;
    }
    
    let successCount = 0;
    
    for (const category of categories) {
        const formData = new FormData();
        formData.append('csrf_token', csrfToken);
        formData.append('name', category.name);
        formData.append('display_order', category.order);
        
        if (category.imageFile) {
            formData.append('image', category.imageFile);
        }
        
        try {
            const response = await fetch(baseUrl + '/card/category/create', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                successCount++;
            } else {
                console.error('Erreur création catégorie:', category.name, data.message);
            }
        } catch (error) {
            console.error('Erreur:', error);
        }
    }
    
    closeBulkCategoryModal();
    
    if (successCount > 0) {
        sessionStorage.setItem('successMessage', `${successCount} catégorie${successCount > 1 ? 's créées' : ' créée'} avec succès`);
        location.reload();
    } else {
        showToast('Erreur lors de la création des catégories', 'error');
    }
}

// ========== PREVIEW IMAGES ==========

function removeCategoryImage() {
    document.getElementById('category-image').value = '';
    document.getElementById('category-image-preview').style.display = 'none';
}

function removeDishImage() {
    document.getElementById('dish-image').value = '';
    document.getElementById('dish-image-preview').style.display = 'none';
}

// ========== EVENT LISTENERS ==========

function initEventListeners() {
    // Fermer modals en cliquant à l'extérieur
    document.querySelectorAll('.modal').forEach(modal => {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.classList.remove('show');
            }
        });
    });

    // Lightbox
    document.getElementById('lightbox').addEventListener('click', function(e) {
        if (e.target === this) {
            closeLightbox();
        }
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeLightbox();
        }
    });

    // Prévisualisation image catégorie
    document.getElementById('category-image').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById('category-image-preview');
                preview.querySelector('img').src = e.target.result;
                preview.style.display = 'inline-block';
            };
            reader.readAsDataURL(file);
        }
    });

    // Prévisualisation image plat
    document.getElementById('dish-image').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById('dish-image-preview');
                preview.querySelector('img').src = e.target.result;
                preview.style.display = 'inline-block';
            };
            reader.readAsDataURL(file);
        }
    });

    // Formulaire catégorie
    document.getElementById('category-form').addEventListener('submit', handleCategoryFormSubmit);

    // Formulaire plat
    document.getElementById('dish-form').addEventListener('submit', handleDishFormSubmit);
}

function handleCategoryFormSubmit(e) {
    e.preventDefault();
    
    const nameInput = document.getElementById('category-name');
    
    if (!nameInput.value.trim()) {
        nameInput.setAttribute('style', 'border: 2px solid #ef4444 !important; background-color: #fef2f2 !important;');
        nameInput.classList.add('field-invalid');
        nameInput.classList.remove('field-valid');
        showToast('Veuillez remplir tous les champs obligatoires', 'error');
        nameInput.focus();
        return;
    } else {
        nameInput.setAttribute('style', 'border: 2px solid #10b981 !important; background-color: #f0fdf4 !important;');
        nameInput.classList.add('field-valid');
        nameInput.classList.remove('field-invalid');
    }
    
    const formData = new FormData(e.target);
    const categoryId = document.getElementById('category-id').value;
    const url = categoryId ? baseUrl + '/card/category/update' : baseUrl + '/card/category/create';
    
    fetch(url, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const message = categoryId ? 'Catégorie modifiée avec succès' : 'Catégorie créée avec succès';
            sessionStorage.setItem('successMessage', message);
            closeCategoryModal();
            location.reload();
        } else {
            showToast(data.message || 'Erreur lors de l\'enregistrement', 'error');
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showToast('Erreur lors de l\'enregistrement', 'error');
    });
}

function handleDishFormSubmit(e) {
    e.preventDefault();
    
    const nameInput = document.getElementById('dish-name');
    const priceInput = document.getElementById('dish-price');
    let isValid = true;
    
    if (!nameInput.value.trim()) {
        nameInput.setAttribute('style', 'border: 2px solid #ef4444 !important; background-color: #fef2f2 !important;');
        nameInput.classList.add('field-invalid');
        nameInput.classList.remove('field-valid');
        isValid = false;
    } else {
        nameInput.setAttribute('style', 'border: 2px solid #10b981 !important; background-color: #f0fdf4 !important;');
        nameInput.classList.add('field-valid');
        nameInput.classList.remove('field-invalid');
    }
    
    if (!priceInput.value || parseFloat(priceInput.value) < 0) {
        priceInput.setAttribute('style', 'border: 2px solid #ef4444 !important; background-color: #fef2f2 !important;');
        priceInput.classList.add('field-invalid');
        priceInput.classList.remove('field-valid');
        isValid = false;
    } else {
        priceInput.setAttribute('style', 'border: 2px solid #10b981 !important; background-color: #f0fdf4 !important;');
        priceInput.classList.add('field-valid');
        priceInput.classList.remove('field-invalid');
    }
    
    if (!isValid) {
        showToast('Veuillez remplir tous les champs obligatoires', 'error');
        return;
    }
    
    const formData = new FormData(e.target);
    const dishId = document.getElementById('dish-id').value;
    const categoryId = document.getElementById('dish-category-id').value;
    const url = dishId ? baseUrl + '/card/dish/update' : baseUrl + '/card/dish/create';
    
    fetch(url, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const message = dishId ? 'Plat modifié avec succès' : 'Plat créé avec succès';
            showToast(message, 'success');
            closeDishModal();
            
            loadDishes(categoryId);
            updateDishCount(categoryId);
            
            const dishesContainer = document.getElementById('dishes-' + categoryId);
            const toggleIcon = document.getElementById('toggle-' + categoryId);
            if (dishesContainer && toggleIcon) {
                dishesContainer.style.display = 'grid';
                toggleIcon.style.transform = 'rotate(90deg)';
            }
        } else {
            showToast(data.message || 'Erreur lors de l\'enregistrement', 'error');
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showToast('Erreur lors de l\'enregistrement', 'error');
    });
}

// ========== EXPOSITION GLOBALE ==========

// Exposer les fonctions nécessaires globalement
window.initEditCard = initEditCard;
window.updateCategoryOrder = updateCategoryOrder;
window.updateDishOrder = updateDishOrder;
window.toggleCategoryDishes = toggleCategoryDishes;
window.toggleAllDishes = toggleAllDishes;
window.filterCards = filterCards;
window.clearSearch = clearSearch;
window.openLightbox = openLightbox;
window.closeLightbox = closeLightbox;
window.openCategoryModal = openCategoryModal;
window.closeCategoryModal = closeCategoryModal;
window.editCategory = editCategory;
window.deleteCategory = deleteCategory;
window.openDishModal = openDishModal;
window.closeDishModal = closeDishModal;
window.editDish = editDish;
window.deleteDish = deleteDish;
window.openBulkCategoryModal = openBulkCategoryModal;
window.closeBulkCategoryModal = closeBulkCategoryModal;
window.addBulkCategoryRow = addBulkCategoryRow;
window.removeBulkCategoryRow = removeBulkCategoryRow;
window.saveBulkCategories = saveBulkCategories;
window.removeCategoryImage = removeCategoryImage;
window.removeDishImage = removeDishImage;
