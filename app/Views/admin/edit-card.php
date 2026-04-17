<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title ?? 'Gérer ma carte') ?> - MenuMiam</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/css/shared/image-variables.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/css/shared/container-variables.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/css/admin/dashboard.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/css/shared/toast.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/css/admin/form-validation.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- CSS Partages -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/css/shared/responsive-typography.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/css/shared/responsive-buttons.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/css/shared/modal.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/css/shared/responsive-layout.css">
    
    <!-- CSS Specifique Edit Card -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/css/admin/sections/edit-card/edit-card.css">
</head>
<body>
    <?php require APP_PATH . '/Views/partials/header.php'; ?>

    <div class="card-container">
        <div class="dashboard-header">
            <h1>Gérer ma carte</h1>
            <p>Organisez vos catégories et plats</p>
        </div>

        <!-- Statistiques -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <i class="fas fa-folder"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-value" id="stat-categories"><?= count($categories) ?></span>
                    <span class="stat-label">Catégorie<?= count($categories) > 1 ? 's' : '' ?></span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                    <i class="fas fa-utensils"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-value" id="stat-dishes">0</span>
                    <span class="stat-label">Plat(s)</span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                    <i class="fas fa-image"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-value" id="stat-images">0</span>
                    <span class="stat-label">Image(s)</span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-value" id="stat-allergens">0</span>
                    <span class="stat-label">Allergène(s) utilisé(s)</span>
                </div>
            </div>
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
                    <button class="btn btn-secondary" onclick="openBulkCategoryModal()" style="margin-left: 8px;" title="Créer plusieurs catégories">
                        <i class="fas fa-layer-group"></i>
                    </button>
                </div>
            </div>

            <!-- Barre de recherche -->
            <div class="search-bar">
                <div class="search-input-wrapper">
                    <i class="fas fa-search"></i>
                    <input type="text" id="search-input" placeholder="Rechercher une catégorie ou un plat..." onkeyup="filterCards()">
                </div>
                <button class="btn btn-secondary" onclick="clearSearch()" id="clear-search-btn" style="display: none;">
                    <i class="fas fa-times"></i> Effacer
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
                        <div class="category-item" data-id="<?= $category['id'] ?>" draggable="true">
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
                                    <div style="display: flex; align-items: center; gap: 8px; margin-right: 12px;">
                                        <label style="font-size: 13px; color: #666;">Ordre:</label>
                                        <input type="number" 
                                               class="order-input category-order-input" 
                                               value="<?= $category['display_order'] ?>" 
                                               min="1" 
                                               max="<?= count($categories) ?>"
                                               data-category-id="<?= $category['id'] ?>"
                                               onchange="updateCategoryOrder(<?= $category['id'] ?>, this.value)"
                                               onclick="event.stopPropagation()"
                                               style="width: 60px; padding: 4px 8px; border: 1px solid #ddd; border-radius: 4px; text-align: center;">
                                    </div>
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
                    <label for="category-name">Nom de la catégorie <span class="required-asterisk" style="color: #ef4444; font-weight: bold;">*</span></label>
                    <input type="text" id="category-name" name="name">
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

    <!-- Modal Création Multiple de Catégories -->
    <div id="bulk-category-modal" class="modal">
        <div class="modal-content" style="max-width: 800px;">
            <div class="modal-header">
                <h3>Créer plusieurs catégories</h3>
                <button class="close-modal" onclick="closeBulkCategoryModal()">&times;</button>
            </div>
            <div style="padding: 20px;">
                <div id="bulk-categories-container">
                    <!-- Les lignes de catégories seront ajoutées ici dynamiquement -->
                </div>
                <button type="button" class="btn btn-secondary" onclick="addBulkCategoryRow()" style="margin-top: 12px;">
                    <i class="fas fa-plus"></i> Ajouter une catégorie
                </button>
                <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 20px;">
                    <button type="button" class="btn btn-secondary" onclick="closeBulkCategoryModal()">Annuler</button>
                    <button type="button" class="btn btn-primary" onclick="saveBulkCategories()">Enregistrer toutes les catégories</button>
                </div>
            </div>
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
                    <label for="dish-name">Nom du plat <span class="required-asterisk" style="color: #ef4444; font-weight: bold;">*</span></label>
                    <input type="text" id="dish-name" name="name">
                </div>

                <div class="form-group">
                    <label for="dish-description">Description</label>
                    <textarea id="dish-description" name="description"></textarea>
                </div>

                <div class="form-group">
                    <label for="dish-price">Prix (€) <span class="required-asterisk" style="color: #ef4444; font-weight: bold;">*</span></label>
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
    <script src="<?= BASE_URL ?>/public/assets/js/admin/form-validation.js"></script>
    
    <!-- JS Partages -->
    <script src="<?= BASE_URL ?>/public/assets/js/shared/ajax-helper.js"></script>
    
    <!-- JS Specifique Edit Card -->
    <script src="<?= BASE_URL ?>/public/assets/js/sections/edit-card/edit-card.js"></script>
    
    <script>
        // Diagnostic : vérifier que les fonctions sont bien chargées
        console.log('=== DIAGNOSTIC EDIT-CARD ===');
        console.log('ajax-helper.js chargé:', typeof showToast !== 'undefined');
        console.log('edit-card.js chargé:', typeof initEditCard !== 'undefined');
        console.log('openCategoryModal disponible:', typeof openCategoryModal !== 'undefined');
        console.log('openBulkCategoryModal disponible:', typeof openBulkCategoryModal !== 'undefined');
        
        // Initialisation de edit-card.js
        document.addEventListener('DOMContentLoaded', () => {
            console.log('DOMContentLoaded - Initialisation...');
            if (typeof initEditCard === 'function') {
                initEditCard(
                    <?= json_encode($categories) ?>,
                    '<?= $csrf_token ?>',
                    '<?= BASE_URL ?>/public'
                );
                console.log('initEditCard() exécuté avec succès');
            } else {
                console.error('ERREUR: initEditCard() n\'est pas définie !');
            }
        });
    </script>
</body>
</html>
