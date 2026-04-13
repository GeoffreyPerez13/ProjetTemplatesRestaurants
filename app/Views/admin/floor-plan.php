<?php
$title = "Plan de salle";

// Script inline à insérer AVANT floor-plan.js
$inline_script = "
<script>
    window.floorPlanData = {
        currentFloorId: " . $selected_floor_id . ",
        tables: " . json_encode($tables) . ",
        elements: " . json_encode($elements) . ",
        maxTableNumber: " . $max_table_number . ",
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

    <!-- Gestion des salles -->
    <div class="floor-tabs-container">
        <div class="floor-tabs">
            <?php foreach ($floors as $floor): ?>
                <button class="floor-tab <?= $floor['id'] == $selected_floor_id ? 'active' : '' ?>" 
                        data-floor-id="<?= $floor['id'] ?>">
                    <?= htmlspecialchars($floor['name']) ?>
                </button>
            <?php endforeach; ?>
            <button class="floor-tab-add" id="add-floor-btn">
                <i class="fas fa-plus"></i> Ajouter une salle
            </button>
        </div>
        <div class="floor-actions">
            <button class="btn-icon btn-info" id="recenter-canvas-btn" title="Recentrer la vue">
                <i class="fas fa-compress-arrows-alt"></i>
            </button>
            <button class="btn-icon btn-primary" id="edit-floor-btn" title="Modifier la salle">
                <i class="fas fa-edit"></i>
            </button>
            <button class="btn-icon btn-warning" id="clear-floor-btn" title="Vider la salle">
                <i class="fas fa-broom"></i>
            </button>
            <button class="btn-icon btn-danger" id="delete-floor-btn" title="Supprimer la salle">
                <i class="fas fa-trash"></i>
            </button>
            <button class="btn-icon btn-danger-dark" id="delete-all-floors-btn" title="Supprimer toutes les salles">
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

<!-- Tour guidé -->
<script>
// Définir les étapes du tour pour cette page
const tourSteps = [
    {
        element: '.floor-plan-header',
        title: 'Plan de salle',
        content: 'Bienvenue dans l\'éditeur de plan de salle ! Créez et organisez votre espace restaurant en plaçant des tables et des éléments.',
        position: 'bottom'
    },
    {
        element: '.floor-tabs-container',
        title: 'Gestion des salles',
        content: 'Gérez plusieurs salles pour votre restaurant. Ajoutez, modifiez ou supprimez des salles selon vos besoins.',
        position: 'bottom'
    },
    {
        element: '#add-floor-btn',
        title: 'Ajouter une salle',
        content: 'Cliquez ici pour créer une nouvelle salle (ex: Rez-de-chaussée, Salle principale, Terrasse...).',
        position: 'bottom'
    },
    {
        element: '.floor-actions',
        title: 'Actions sur la salle',
        content: 'Recentrez la vue, modifiez le nom de la salle, videz-la ou supprimez-la complètement.',
        position: 'bottom'
    },
    {
        element: '.toolbar-section:nth-child(1)',
        title: 'Tables',
        content: 'Sélectionnez le type de table à ajouter : ronde, carrée ou rectangulaire. Cliquez ensuite sur le canvas pour placer la table.',
        position: 'right'
    },
    {
        element: '.toolbar-section:nth-child(2)',
        title: 'Éléments décoratifs',
        content: 'Ajoutez des murs, portes et fenêtres pour structurer votre plan de salle.',
        position: 'right'
    },
    {
        element: '.toolbar-section:nth-child(3)',
        title: 'Actions',
        content: 'Mode sélection pour déplacer les éléments, ou supprimez les éléments sélectionnés.',
        position: 'right'
    },
    {
        element: '.floor-canvas-container',
        title: 'Canvas de dessin',
        content: 'Votre espace de travail ! Cliquez pour placer des éléments, glissez-déposez pour les déplacer, cliquez pour voir leurs propriétés.',
        position: 'top'
    },
    {
        element: '.floor-legend',
        title: 'Aide rapide',
        content: 'Retrouvez ici les raccourcis et astuces pour utiliser l\'éditeur de plan de salle.',
        position: 'top'
    },
    {
        element: null,
        title: 'C\'est parti !',
        content: 'Vous êtes prêt à créer votre plan de salle. Les modifications sont sauvegardées automatiquement.',
        position: 'center'
    }
];
</script>

<!-- Modal ajout salle -->
<div class="modal" id="add-floor-modal" style="display: none;">
    <div class="modal-content">
        <h3>Ajouter une salle</h3>
        <form id="add-floor-form">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
            <div class="form-group">
                <label for="floor-name">Nom de la salle *</label>
                <input type="text" id="floor-name" name="name" placeholder="Ex: Salle principale, Terrasse..." required>
            </div>
            <div class="form-actions">
                <button type="button" class="btn-secondary" id="cancel-floor-btn">Annuler</button>
                <button type="submit" class="btn-primary">Créer</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal édition salle -->
<div class="modal" id="edit-floor-modal" style="display: none;">
    <div class="modal-content">
        <h3>Modifier la salle</h3>
        <form id="edit-floor-form">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
            <input type="hidden" id="edit-floor-id" name="floor_id">
            <div class="form-group">
                <label for="edit-floor-name">Nom de la salle *</label>
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
