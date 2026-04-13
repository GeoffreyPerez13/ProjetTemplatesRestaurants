<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title ?? 'Gérer ma carte') ?> - MenuMiam</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/css/admin/dashboard.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/css/shared/toast.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .card-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 24px;
        }

        .section {
            background: white;
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 24px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 2px solid #f0f0f0;
        }

        .section-header h2 {
            font-size: 20px;
            color: #333;
            margin: 0;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: #667eea;
            color: white;
        }

        .btn-primary:hover {
            background: #5568d3;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }

        .btn-danger {
            background: #ef4444;
            color: white;
        }

        .btn-danger:hover {
            background: #dc2626;
        }

        .btn-secondary {
            background: #6b7280;
            color: white;
        }

        .btn-secondary:hover {
            background: #4b5563;
        }

        .categories-list {
            display: grid;
            gap: 16px;
        }

        .category-item {
            background: #f9fafb;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            padding: 16px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .category-item:hover {
            border-color: #667eea;
            box-shadow: 0 2px 8px rgba(102, 126, 234, 0.2);
        }

        .category-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .category-name {
            font-size: 18px;
            font-weight: 600;
            color: #333;
        }

        .category-actions {
            display: flex;
            gap: 8px;
        }

        .icon-btn {
            width: 36px;
            height: 36px;
            border: none;
            border-radius: 6px;
            background: white;
            color: #666;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
        }

        .icon-btn:hover {
            background: #667eea;
            color: white;
        }

        .icon-btn.delete:hover {
            background: #ef4444;
        }

        .dishes-list {
            margin-top: 12px;
            padding-top: 12px;
            border-top: 1px solid #e5e7eb;
            display: grid;
            gap: 8px;
        }

        .dish-item {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .dish-info {
            flex: 1;
        }

        .dish-name {
            font-weight: 500;
            color: #333;
        }

        .dish-price {
            color: #667eea;
            font-weight: 600;
            margin-left: 12px;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }

        .modal.show {
            display: flex;
        }

        .modal-content {
            background: white;
            border-radius: 12px;
            padding: 24px;
            max-width: 600px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .modal-header h3 {
            margin: 0;
            font-size: 20px;
        }

        .close-modal {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #666;
        }

        .form-group {
            margin-bottom: 16px;
        }

        .form-group label {
            display: block;
            font-size: 14px;
            font-weight: 500;
            color: #333;
            margin-bottom: 8px;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
        }

        .form-group textarea {
            min-height: 80px;
            resize: vertical;
        }

        .allergenes-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 8px;
        }

        .allergene-checkbox {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px;
            background: #f9fafb;
            border-radius: 6px;
            cursor: pointer;
        }

        .allergene-checkbox:hover {
            background: #f3f4f6;
        }

        .allergene-checkbox input {
            width: auto;
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #666;
        }

        .empty-state i {
            font-size: 48px;
            color: #ddd;
            margin-bottom: 16px;
        }

        /* Upload d'images */
        .form-group input[type="file"] {
            padding: 8px;
            border: 2px dashed #ddd;
            border-radius: 6px;
            cursor: pointer;
            background: #f9fafb;
        }

        .form-group input[type="file"]:hover {
            border-color: #667eea;
            background: #f3f4f6;
        }

        .image-preview {
            margin-top: 12px;
            position: relative;
            display: inline-block;
            border-radius: 8px;
            overflow: hidden;
            border: 2px solid #e5e7eb;
        }

        .image-preview img {
            display: block;
            max-width: 200px;
            max-height: 200px;
            object-fit: cover;
        }

        .remove-image {
            position: absolute;
            top: 8px;
            right: 8px;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: rgba(239, 68, 68, 0.9);
            color: white;
            border: none;
            cursor: pointer;
            font-size: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
        }

        .remove-image:hover {
            background: #dc2626;
            transform: scale(1.1);
        }

        .category-item img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 6px;
            margin-right: 12px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .category-item img:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }

        /* Lightbox */
        .lightbox {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.9);
            z-index: 10000;
            align-items: center;
            justify-content: center;
            animation: fadeIn 0.3s;
        }

        .lightbox.show {
            display: flex;
        }

        .lightbox-image {
            max-width: 70%;
            max-height: 70vh;
            object-fit: contain;
            border-radius: 8px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.5);
            animation: zoomIn 0.3s;
        }

        @media (max-width: 768px) {
            .lightbox-image {
                max-width: 85%;
                max-height: 80vh;
            }
        }

        .lightbox-close {
            position: absolute;
            top: 20px;
            right: 20px;
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            color: white;
            border: none;
            font-size: 32px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
        }

        .lightbox-close:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: rotate(90deg);
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes zoomIn {
            from { transform: scale(0.8); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }

        @media (max-width: 768px) {
            .lightbox-close {
                top: 10px;
                right: 10px;
                width: 40px;
                height: 40px;
                font-size: 28px;
            }
        }
    </style>
</head>
<body>
    <?php require APP_PATH . '/Views/partials/header.php'; ?>

    <div class="card-container">
        <div class="dashboard-header">
            <h1>Gérer ma carte</h1>
            <p>Organisez vos catégories et plats</p>
        </div>

        <!-- Section Catégories -->
        <div class="section">
            <div class="section-header">
                <h2><i class="fas fa-list"></i> Catégories</h2>
                <div style="display: flex; gap: 12px;">
                    <button class="btn btn-secondary" onclick="toggleAllDishes()" id="toggle-all-btn">
                        <i class="fas fa-chevron-down"></i> Tout déplier
                    </button>
                    <button class="btn btn-primary" onclick="openCategoryModal()">
                        <i class="fas fa-plus"></i> Nouvelle catégorie
                    </button>
                </div>
            </div>

            <div id="categories-container" class="categories-list">
                <?php if (empty($categories)): ?>
                    <div class="empty-state">
                        <i class="fas fa-folder-open"></i>
                        <p>Aucune catégorie pour le moment</p>
                        <p>Créez votre première catégorie pour commencer</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($categories as $category): ?>
                        <div class="category-item" data-id="<?= $category['id'] ?>">
                            <div class="category-header">
                                <div style="display: flex; align-items: center; flex: 1; cursor: pointer;" onclick="toggleCategoryDishes(<?= $category['id'] ?>)">
                                    <i class="fas fa-chevron-right category-toggle" id="toggle-<?= $category['id'] ?>" style="margin-right: 12px; color: #667eea; transition: transform 0.3s;"></i>
                                    <?php if ($category['image']): ?>
                                        <img src="<?= BASE_URL ?>/public/uploads/categories/<?= htmlspecialchars($category['image']) ?>" alt="<?= htmlspecialchars($category['name']) ?>" onclick="event.stopPropagation(); openLightbox('<?= BASE_URL ?>/public/uploads/categories/<?= htmlspecialchars($category['image']) ?>')">
                                    <?php endif; ?>
                                    <div>
                                        <span class="category-name"><?= htmlspecialchars($category['name']) ?></span>
                                        <span style="color: #666; font-size: 14px; margin-left: 12px;">
                                            (<?= $category['plats_count'] ?> plat<?= $category['plats_count'] > 1 ? 's' : '' ?>)
                                        </span>
                                    </div>
                                </div>
                                <div class="category-actions">
                                    <button class="icon-btn" onclick="openDishModal(<?= $category['id'] ?>)" title="Ajouter un plat">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                    <button class="icon-btn" onclick="editCategory(<?= $category['id'] ?>)" title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="icon-btn delete" onclick="deleteCategory(<?= $category['id'] ?>)" title="Supprimer">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="dishes-list" id="dishes-<?= $category['id'] ?>" style="display: none;">
                                <!-- Les plats seront chargés ici -->
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Modal Catégorie -->
    <div id="category-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="category-modal-title">Nouvelle catégorie</h3>
                <button class="close-modal" onclick="closeCategoryModal()">&times;</button>
            </div>
            <form id="category-form" method="POST" action="<?= BASE_URL ?>/public/card/category/create" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                <input type="hidden" name="id" id="category-id">
                
                <div class="form-group">
                    <label for="category-name">Nom de la catégorie *</label>
                    <input type="text" id="category-name" name="name" required>
                </div>

                <div class="form-group">
                    <label for="category-image">Image de la catégorie</label>
                    <input type="file" id="category-image" name="image" accept="image/jpeg,image/jpg,image/png,image/webp">
                    <div id="category-image-preview" class="image-preview" style="display:none;">
                        <img src="" alt="Aperçu">
                        <button type="button" class="remove-image" onclick="removeCategoryImage()">✕</button>
                    </div>
                </div>

                <div style="display: flex; gap: 12px; justify-content: flex-end;">
                    <button type="button" class="btn btn-secondary" onclick="closeCategoryModal()">Annuler</button>
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Plat -->
    <div id="dish-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="dish-modal-title">Nouveau plat</h3>
                <button class="close-modal" onclick="closeDishModal()">&times;</button>
            </div>
            <form id="dish-form" method="POST" action="<?= BASE_URL ?>/public/card/dish/create" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                <input type="hidden" name="id" id="dish-id">
                <input type="hidden" name="category_id" id="dish-category-id">
                
                <div class="form-group">
                    <label for="dish-name">Nom du plat *</label>
                    <input type="text" id="dish-name" name="name" required>
                </div>

                <div class="form-group">
                    <label for="dish-description">Description</label>
                    <textarea id="dish-description" name="description"></textarea>
                </div>

                <div class="form-group">
                    <label for="dish-price">Prix (€)</label>
                    <input type="number" id="dish-price" name="price" step="0.01" min="0">
                </div>

                <div class="form-group">
                    <label for="dish-image">Image du plat</label>
                    <input type="file" id="dish-image" name="image" accept="image/jpeg,image/jpg,image/png,image/webp">
                    <div id="dish-image-preview" class="image-preview" style="display:none;">
                        <img src="" alt="Aperçu">
                        <button type="button" class="remove-image" onclick="removeDishImage()">✕</button>
                    </div>
                </div>

                <div class="form-group">
                    <label>Allergènes</label>
                    <div class="allergenes-grid">
                        <?php foreach ($allergenes as $allergene): ?>
                            <label class="allergene-checkbox">
                                <input type="checkbox" name="allergenes[]" value="<?= $allergene['id'] ?>">
                                <span><?= htmlspecialchars($allergene['nom']) ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div style="display: flex; gap: 12px; justify-content: flex-end;">
                    <button type="button" class="btn btn-secondary" onclick="closeDishModal()">Annuler</button>
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Lightbox pour afficher les images en grand -->
    <div id="lightbox" class="lightbox">
        <button class="lightbox-close" onclick="closeLightbox()">&times;</button>
        <img src="" alt="" id="lightbox-image" class="lightbox-image">
    </div>

    <?php require APP_PATH . '/Views/partials/footer.php'; ?>
    
    <script src="<?= BASE_URL ?>/public/assets/js/app.js"></script>
    <script>
        // Données des catégories
        const categories = <?= json_encode($categories) ?>;
        const csrfToken = '<?= $csrf_token ?>';
        const baseUrl = '<?= BASE_URL ?>/public';

        // État de l'accordéon
        let allDishesExpanded = false;

        // Charger les plats de chaque catégorie au démarrage
        document.addEventListener('DOMContentLoaded', () => {
            categories.forEach(category => {
                loadDishes(category.id);
            });
        });

        // Toggle les plats d'une catégorie
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

        // Toggle tous les plats
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

        // Charger les plats d'une catégorie
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

        // Afficher les plats dans la liste
        function displayDishes(categoryId, dishes) {
            const container = document.getElementById('dishes-' + categoryId);
            if (!dishes || dishes.length === 0) {
                container.innerHTML = '<p style="color: #999; font-size: 13px; padding: 8px 0;">Aucun plat dans cette catégorie</p>';
                return;
            }

            container.innerHTML = dishes.map(dish => `
                <div class="dish-item">
                    <div class="dish-info" style="display: flex; align-items: center; gap: 12px;">
                        ${dish.image ? `<img src="${baseUrl}/uploads/plats/${dish.image}" alt="${dish.name}" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px; cursor: pointer; transition: all 0.3s;" onclick="openLightbox('${baseUrl}/uploads/plats/${dish.image}')" onmouseover="this.style.transform='scale(1.05)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.2)'" onmouseout="this.style.transform='scale(1)'; this.style.boxShadow='none'">` : ''}
                        <div>
                            <span class="dish-name">${dish.name}</span>
                            ${dish.price ? `<span class="dish-price">${parseFloat(dish.price).toFixed(2)} €</span>` : ''}
                        </div>
                    </div>
                    <div style="display: flex; gap: 8px;">
                        <button class="icon-btn" onclick="editDish(${dish.id})" title="Modifier">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="icon-btn delete" onclick="deleteDish(${dish.id})" title="Supprimer">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            `).join('');
        }

        // Ouvrir la lightbox
        function openLightbox(imageSrc) {
            const lightbox = document.getElementById('lightbox');
            const lightboxImage = document.getElementById('lightbox-image');
            lightboxImage.src = imageSrc;
            lightbox.classList.add('show');
        }

        // Fermer la lightbox
        function closeLightbox() {
            const lightbox = document.getElementById('lightbox');
            lightbox.classList.remove('show');
        }

        // Fermer la lightbox en cliquant sur le fond
        document.getElementById('lightbox').addEventListener('click', function(e) {
            if (e.target === this) {
                closeLightbox();
            }
        });

        // Fermer la lightbox avec la touche Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeLightbox();
            }
        });

        // Ouvrir modal catégorie
        function openCategoryModal(categoryId = null) {
            const modal = document.getElementById('category-modal');
            const form = document.getElementById('category-form');
            const title = document.getElementById('category-modal-title');
            
            form.reset();
            
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
            if (!confirm('Supprimer cette catégorie et tous ses plats ?')) return;
            
            const formData = new FormData();
            formData.append('csrf_token', csrfToken);
            formData.append('id', id);
            
            App.ajaxRequest({
                url: baseUrl + '/card/category/delete',
                method: 'POST',
                data: formData,
                onSuccess: () => {
                    location.reload();
                }
            });
        }

        // Ouvrir modal plat
        function openDishModal(categoryId, dishId = null) {
            const modal = document.getElementById('dish-modal');
            const form = document.getElementById('dish-form');
            const title = document.getElementById('dish-modal-title');
            
            form.reset();
            document.getElementById('dish-category-id').value = categoryId;
            
            // Décocher tous les allergènes
            document.querySelectorAll('input[name="allergenes[]"]').forEach(cb => cb.checked = false);
            
            if (dishId) {
                title.textContent = 'Modifier le plat';
                form.action = baseUrl + '/card/dish/update';
                document.getElementById('dish-id').value = dishId;
                // TODO: Charger les données du plat via AJAX
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
            // Charger les données du plat
            fetch(baseUrl + '/card/dish/' + dishId, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.data.dish) {
                    const dish = data.data.dish;
                    
                    // Ouvrir le modal en mode édition
                    const modal = document.getElementById('dish-modal');
                    const form = document.getElementById('dish-form');
                    const title = document.getElementById('dish-modal-title');
                    
                    title.textContent = 'Modifier le plat';
                    form.action = baseUrl + '/card/dish/update';
                    
                    // Remplir le formulaire
                    document.getElementById('dish-id').value = dish.id;
                    document.getElementById('dish-category-id').value = dish.category_id;
                    document.getElementById('dish-name').value = dish.name;
                    document.getElementById('dish-description').value = dish.description || '';
                    document.getElementById('dish-price').value = dish.price || '';
                    
                    // Cocher les allergènes
                    document.querySelectorAll('input[name="allergenes[]"]').forEach(cb => cb.checked = false);
                    if (dish.allergene_ids) {
                        const allergeneIds = dish.allergene_ids.split(',');
                        allergeneIds.forEach(id => {
                            const checkbox = document.querySelector(`input[name="allergenes[]"][value="${id}"]`);
                            if (checkbox) checkbox.checked = true;
                        });
                    }
                    
                    // Afficher l'image existante si présente
                    if (dish.image) {
                        const preview = document.getElementById('dish-image-preview');
                        preview.querySelector('img').src = baseUrl + '/uploads/plats/' + dish.image;
                        preview.style.display = 'inline-block';
                    }
                    
                    modal.classList.add('show');
                } else {
                    App.showToast(data.message || 'Erreur lors du chargement du plat', 'error');
                }
            })
            .catch(error => {
                console.error('Erreur chargement plat:', error);
                App.showToast('Erreur de connexion au serveur', 'error');
            });
        }

        function deleteDish(dishId) {
            if (!confirm('Supprimer ce plat ?')) return;
            
            const formData = new FormData();
            formData.append('csrf_token', csrfToken);
            formData.append('id', dishId);
            
            App.ajaxRequest({
                url: baseUrl + '/card/dish/delete',
                method: 'POST',
                data: formData,
                onSuccess: () => {
                    location.reload();
                }
            });
        }

        // Fermer modals en cliquant à l'extérieur
        document.querySelectorAll('.modal').forEach(modal => {
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    modal.classList.remove('show');
                }
            });
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

        function removeCategoryImage() {
            document.getElementById('category-image').value = '';
            document.getElementById('category-image-preview').style.display = 'none';
        }

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

        function removeDishImage() {
            document.getElementById('dish-image').value = '';
            document.getElementById('dish-image-preview').style.display = 'none';
        }

        // Soumettre les formulaires
        document.getElementById('category-form').addEventListener('submit', (e) => {
            e.preventDefault();
            App.ajaxForm(e.target, {
                onSuccess: () => {
                    closeCategoryModal();
                    setTimeout(() => location.reload(), 500);
                }
            });
        });

        document.getElementById('dish-form').addEventListener('submit', (e) => {
            e.preventDefault();
            App.ajaxForm(e.target, {
                onSuccess: () => {
                    closeDishModal();
                    setTimeout(() => location.reload(), 500);
                }
            });
        });
    </script>
</body>
</html>
