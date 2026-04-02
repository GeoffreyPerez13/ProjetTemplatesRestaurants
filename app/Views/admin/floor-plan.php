<?php
$title = "Plan de salle";

// Script inline à insérer AVANT floor-plan.js
$inline_script = "
<script>
    window.floorPlanData = {
        currentFloorId: " . $selected_floor_id . ",
        tables: " . json_encode($tables) . ",
        elements: " . json_encode($elements) . ",
        csrfToken: '" . $csrf_token . "'
    };
</script>
";

$scripts = ["js/sections/floor-plan/floor-plan.js"];
require __DIR__ . '/../partials/header.php';
?>

<div class="floor-plan-page">
    <a class="btn-back" href="?page=reservations">Retour aux réservations</a>

    <div class="floor-plan-header">
        <h2><i class="fas fa-map-marked-alt"></i> Plan de salle</h2>
        <p class="floor-plan-subtitle">Créez et organisez votre espace restaurant</p>
    </div>

    <!-- Gestion des étages -->
    <div class="floor-tabs-container">
        <div class="floor-tabs">
            <?php foreach ($floors as $floor): ?>
                <button class="floor-tab <?= $floor['id'] == $selected_floor_id ? 'active' : '' ?>" 
                        data-floor-id="<?= $floor['id'] ?>">
                    <?= htmlspecialchars($floor['name']) ?>
                </button>
            <?php endforeach; ?>
            <button class="floor-tab-add" id="add-floor-btn">
                <i class="fas fa-plus"></i> Ajouter un étage
            </button>
        </div>
        <div class="floor-actions">
            <button class="btn-icon btn-info" id="recenter-canvas-btn" title="Recentrer la vue">
                <i class="fas fa-compress-arrows-alt"></i>
            </button>
            <button class="btn-icon btn-primary" id="edit-floor-btn" title="Modifier l'étage">
                <i class="fas fa-edit"></i>
            </button>
            <button class="btn-icon btn-warning" id="clear-floor-btn" title="Vider l'étage">
                <i class="fas fa-broom"></i>
            </button>
            <button class="btn-icon btn-danger" id="delete-floor-btn" title="Supprimer l'étage">
                <i class="fas fa-trash"></i>
            </button>
            <button class="btn-icon btn-danger-dark" id="delete-all-floors-btn" title="Supprimer tous les étages">
                <i class="fas fa-trash-alt"></i>
            </button>
        </div>
    </div>

    <!-- Barre d'outils -->
    <div class="floor-toolbar">
        <div class="toolbar-section">
            <h4><i class="fas fa-chair"></i> Tables</h4>
            <button class="tool-btn" data-tool="table-round" title="Table ronde">
                <i class="fas fa-circle"></i> Ronde
            </button>
            <button class="tool-btn" data-tool="table-square" title="Table carrée">
                <i class="fas fa-square"></i> Carrée
            </button>
            <button class="tool-btn" data-tool="table-rectangle" title="Table rectangulaire">
                <i class="fas fa-vector-square"></i> Rectangle
            </button>
        </div>
        <div class="toolbar-section">
            <h4><i class="fas fa-shapes"></i> Éléments</h4>
            <button class="tool-btn" data-tool="wall" title="Mur">
                <i class="fas fa-minus"></i> Mur
            </button>
            <button class="tool-btn" data-tool="door" title="Porte">
                <i class="fas fa-door-open"></i> Porte
            </button>
            <button class="tool-btn" data-tool="window" title="Fenêtre">
                <i class="fas fa-window-maximize"></i> Fenêtre
            </button>
        </div>
        <div class="toolbar-section">
            <h4><i class="fas fa-mouse-pointer"></i> Actions</h4>
            <button class="tool-btn active" data-tool="select" title="Sélectionner">
                <i class="fas fa-mouse-pointer"></i> Sélectionner
            </button>
            <button class="tool-btn" id="delete-selected-btn" title="Supprimer la sélection">
                <i class="fas fa-trash"></i> Supprimer
            </button>
        </div>
    </div>

    <!-- Canvas principal -->
    <div class="floor-canvas-container">
        <div class="canvas-grid" id="floor-canvas">
            <!-- Les éléments seront ajoutés dynamiquement ici -->
        </div>
    </div>

    <!-- Panneau de propriétés -->
    <div class="properties-panel" id="properties-panel" style="display: none;">
        <div class="properties-header">
            <h3><i class="fas fa-cog"></i> Propriétés</h3>
            <button class="close-properties-btn" id="close-properties-btn" title="Fermer">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div id="properties-content">
            <!-- Le contenu sera rempli dynamiquement -->
        </div>
    </div>

    <!-- Légende -->
    <div class="floor-legend">
        <h4><i class="fas fa-info-circle"></i> Aide</h4>
        <ul>
            <li><i class="fas fa-mouse-pointer"></i> Cliquez sur un outil puis sur le canvas pour ajouter</li>
            <li><i class="fas fa-hand-paper"></i> Glissez-déposez les éléments pour les déplacer</li>
            <li><i class="fas fa-mouse"></i> Cliquez sur un élément pour voir ses propriétés</li>
            <li><i class="fas fa-trash"></i> Sélectionnez puis cliquez sur Supprimer</li>
        </ul>
    </div>
</div>

<!-- Modal ajout étage -->
<div class="modal" id="add-floor-modal" style="display: none;">
    <div class="modal-content">
        <h3>Ajouter un étage</h3>
        <form id="add-floor-form">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
            <div class="form-group">
                <label for="floor-name">Nom de l'étage *</label>
                <input type="text" id="floor-name" name="name" placeholder="Ex: 1er étage, Terrasse..." required>
            </div>
            <div class="form-actions">
                <button type="button" class="btn-secondary" id="cancel-floor-btn">Annuler</button>
                <button type="submit" class="btn-primary">Créer</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal édition étage -->
<div class="modal" id="edit-floor-modal" style="display: none;">
    <div class="modal-content">
        <h3>Modifier l'étage</h3>
        <form id="edit-floor-form">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
            <input type="hidden" id="edit-floor-id" name="floor_id">
            <div class="form-group">
                <label for="edit-floor-name">Nom de l'étage *</label>
                <input type="text" id="edit-floor-name" name="name" required>
            </div>
            <div class="form-actions">
                <button type="button" class="btn-secondary" id="cancel-edit-floor-btn">Annuler</button>
                <button type="submit" class="btn-primary">Enregistrer</button>
            </div>
        </form>
    </div>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>
