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
                <button class="btn btn-primary" onclick="openCategoryModal()">
                    <i class="fas fa-plus"></i> Nouvelle catégorie
                </button>
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
                                <div>
                                    <span class="category-name"><?= htmlspecialchars($category['name']) ?></span>
                                    <span style="color: #666; font-size: 14px; margin-left: 12px;">
                                        (<?= $category['plats_count'] ?> plat<?= $category['plats_count'] > 1 ? 's' : '' ?>)
                                    </span>
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
                            <div class="dishes-list" id="dishes-<?= $category['id'] ?>">
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
            <form id="category-form" class="ajax-form" method="POST" action="<?= BASE_URL ?>/public/card/category/create">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                <input type="hidden" name="id" id="category-id">
                
                <div class="form-group">
                    <label for="category-name">Nom de la catégorie *</label>
                    <input type="text" id="category-name" name="name" required>
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
            <form id="dish-form" class="ajax-form" method="POST" action="<?= BASE_URL ?>/public/card/dish/create">
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

    <?php require APP_PATH . '/Views/partials/footer.php'; ?>
    
    <script src="<?= BASE_URL ?>/public/assets/js/app.js"></script>
    <script>
        // Données des catégories
        const categories = <?= json_encode($categories) ?>;
        const csrfToken = '<?= $csrf_token ?>';
        const baseUrl = '<?= BASE_URL ?>/public';

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
            
            if (dishId) {
                title.textContent = 'Modifier le plat';
                form.action = baseUrl + '/card/dish/update';
                document.getElementById('dish-id').value = dishId;
                // Charger les données du plat...
            } else {
                title.textContent = 'Nouveau plat';
                form.action = baseUrl + '/card/dish/create';
            }
            
            modal.classList.add('show');
        }

        function closeDishModal() {
            document.getElementById('dish-modal').classList.remove('show');
        }

        // Fermer modals en cliquant à l'extérieur
        document.querySelectorAll('.modal').forEach(modal => {
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    modal.classList.remove('show');
                }
            });
        });

        // Recharger après succès
        document.getElementById('category-form').addEventListener('submit', (e) => {
            e.preventDefault();
            App.ajaxForm(e.target, {
                onSuccess: () => {
                    setTimeout(() => location.reload(), 1000);
                }
            });
        });

        document.getElementById('dish-form').addEventListener('submit', (e) => {
            e.preventDefault();
            App.ajaxForm(e.target, {
                onSuccess: () => {
                    setTimeout(() => location.reload(), 1000);
                }
            });
        });
    </script>
</body>
</html>
