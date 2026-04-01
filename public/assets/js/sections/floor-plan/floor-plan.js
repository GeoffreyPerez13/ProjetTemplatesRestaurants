/**
 * Éditeur de plan de salle - Drag & Drop
 */

(function() {
    'use strict';

    // État global
    let currentTool = 'select';
    let selectedElement = null;
    let isDragging = false;
    let dragOffset = { x: 0, y: 0 };
    let currentFloorId = window.floorPlanData?.currentFloorId || null;
    let tables = window.floorPlanData?.tables || [];
    let elements = window.floorPlanData?.elements || [];
    let csrfToken = window.floorPlanData?.csrfToken || '';

    // Compteurs pour numérotation auto
    let tableCounter = tables.length + 1;
    const SNAP_THRESHOLD = 15; // pixels de distance pour le snapping

    /**
     * Initialisation
     */
    function init() {
        if (!document.querySelector('.floor-plan-page')) return;

        setupToolbar();
        setupCanvas();
        setupFloorTabs();
        setupModals();
        setupPropertiesPanel();
        renderCanvas();

        console.log('Floor plan editor initialized');
    }

    /**
     * Configuration du panneau de propriétés
     */
    function setupPropertiesPanel() {
        // Bouton de fermeture
        document.getElementById('close-properties-btn')?.addEventListener('click', () => {
            document.getElementById('properties-panel').style.display = 'none';
            if (selectedElement) {
                selectedElement.classList.remove('selected');
                selectedElement = null;
            }
        });

        // Clic en dehors du panneau pour le fermer
        document.addEventListener('click', (e) => {
            const panel = document.getElementById('properties-panel');
            if (panel.style.display === 'block' && 
                !panel.contains(e.target) && 
                !e.target.closest('.canvas-table') && 
                !e.target.closest('.canvas-element')) {
                panel.style.display = 'none';
                if (selectedElement) {
                    selectedElement.classList.remove('selected');
                    selectedElement = null;
                }
            }
        });
    }

    /**
     * Configuration de la barre d'outils
     */
    function setupToolbar() {
        const toolButtons = document.querySelectorAll('.tool-btn');
        
        toolButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                const tool = btn.dataset.tool;
                
                if (tool) {
                    // Désactiver tous les boutons
                    toolButtons.forEach(b => b.classList.remove('active'));
                    // Activer le bouton cliqué
                    btn.classList.add('active');
                    currentTool = tool;
                    
                    // Changer le curseur du canvas
                    const canvas = document.getElementById('floor-canvas');
                    if (tool === 'select') {
                        canvas.style.cursor = 'default';
                    } else {
                        canvas.style.cursor = 'crosshair';
                    }
                }
            });
        });

        // Bouton supprimer
        document.getElementById('delete-selected-btn')?.addEventListener('click', deleteSelected);
    }

    /**
     * Configuration du canvas
     */
    function setupCanvas() {
        const canvas = document.getElementById('floor-canvas');
        
        canvas.addEventListener('click', (e) => {
            if (currentTool === 'select') return;
            
            const rect = canvas.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            
            addElement(currentTool, x, y);
        });
    }

    /**
     * Ajouter un élément sur le canvas
     */
    function addElement(tool, x, y) {
        if (tool.startsWith('table-')) {
            addTable(tool.replace('table-', ''), x, y);
        } else {
            addWallElement(tool, x, y);
        }
    }

    /**
     * Ajouter une table
     */
    function addTable(shape, x, y) {
        const tableNumber = `T${tableCounter++}`;
        const width = shape === 'rectangle' ? 80 : 60;
        const height = shape === 'rectangle' ? 50 : 60;

        const data = new FormData();
        data.append('csrf_token', csrfToken);
        data.append('floor_id', currentFloorId);
        data.append('table_number', tableNumber);
        data.append('shape', shape);
        data.append('capacity_min', '2');
        data.append('capacity_max', '4');
        data.append('position_x', Math.round(x - width / 2));
        data.append('position_y', Math.round(y - height / 2));
        data.append('width', width);
        data.append('height', height);

        fetch('?page=floor-plan-create-table', {
            method: 'POST',
            body: data
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                if (data.csrf_token) csrfToken = data.csrf_token;
                Swal.fire({
                    icon: 'success',
                    title: 'Table ajoutée',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000
                });
                reloadFloorData();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Erreur',
                    text: data.message || 'Impossible d\'ajouter la table'
                });
            }
        })
        .catch(err => {
            console.error('Error adding table:', err);
            Swal.fire({
                icon: 'error',
                title: 'Erreur',
                text: 'Erreur lors de l\'ajout de la table'
            });
        });
    }

    /**
     * Ajouter un élément mural
     */
    function addWallElement(type, x, y) {
        const width = type === 'door' || type === 'window' ? 60 : 100;
        const height = 20;

        const data = new FormData();
        data.append('csrf_token', csrfToken);
        data.append('floor_id', currentFloorId);
        data.append('element_type', type);
        data.append('position_x', Math.round(x - width / 2));
        data.append('position_y', Math.round(y - height / 2));
        data.append('width', width);
        data.append('height', height);

        fetch('?page=floor-plan-create-element', {
            method: 'POST',
            body: data
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                if (data.csrf_token) csrfToken = data.csrf_token;
                Swal.fire({
                    icon: 'success',
                    title: 'Élément ajouté',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000
                });
                reloadFloorData();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Erreur',
                    text: data.message || 'Impossible d\'ajouter l\'élément'
                });
            }
        })
        .catch(err => {
            console.error('Error adding element:', err);
            Swal.fire({
                icon: 'error',
                title: 'Erreur',
                text: 'Erreur lors de l\'ajout de l\'élément'
            });
        });
    }

    /**
     * Rendre le canvas
     */
    function renderCanvas() {
        const canvas = document.getElementById('floor-canvas');
        canvas.innerHTML = '';

        // Rendre les éléments
        elements.forEach(el => {
            const div = createElementDiv(el);
            canvas.appendChild(div);
        });

        // Rendre les tables
        tables.forEach(table => {
            const div = createTableDiv(table);
            canvas.appendChild(div);
        });
    }

    /**
     * Créer un div pour une table
     */
    function createTableDiv(table) {
        const div = document.createElement('div');
        div.className = `canvas-table ${table.shape}`;
        div.dataset.id = table.id;
        div.dataset.type = 'table';
        div.style.left = table.position_x + 'px';
        div.style.top = table.position_y + 'px';
        div.style.width = table.width + 'px';
        div.style.height = table.height + 'px';

        div.innerHTML = `
            <div class="table-number">${table.table_number}</div>
            <div class="table-capacity">${table.capacity_min}-${table.capacity_max}p</div>
        `;

        makeDraggable(div);
        makeSelectable(div, table);

        return div;
    }

    /**
     * Créer un div pour un élément
     */
    function createElementDiv(element) {
        const div = document.createElement('div');
        div.className = `canvas-element ${element.element_type}`;
        div.dataset.id = element.id;
        div.dataset.type = 'element';
        div.style.left = element.position_x + 'px';
        div.style.top = element.position_y + 'px';
        div.style.width = element.width + 'px';
        div.style.height = element.height + 'px';
        
        if (element.rotation) {
            div.style.transform = `rotate(${element.rotation}deg)`;
        }

        // Visuels pour porte et fenêtre
        if (element.element_type === 'door') {
            div.innerHTML = '<span class="element-icon"><i class="fas fa-door-open"></i></span>';
        } else if (element.element_type === 'window') {
            div.innerHTML = '<svg class="element-icon-svg" viewBox="0 0 60 20" preserveAspectRatio="none"><line x1="15" y1="3" x2="15" y2="17" stroke="rgba(255,255,255,0.5)" stroke-width="1"/><line x1="30" y1="3" x2="30" y2="17" stroke="rgba(255,255,255,0.5)" stroke-width="1"/><line x1="45" y1="3" x2="45" y2="17" stroke="rgba(255,255,255,0.5)" stroke-width="1"/><line x1="3" y1="10" x2="57" y2="10" stroke="rgba(255,255,255,0.4)" stroke-width="0.8"/></svg>';
        }

        makeDraggable(div);
        makeSelectable(div, element);

        return div;
    }

    /**
     * Rendre un élément draggable
     */
    function makeDraggable(element) {
        element.addEventListener('mousedown', (e) => {
            isDragging = true;
            const rect = element.getBoundingClientRect();
            const canvasEl = document.getElementById('floor-canvas');
            const canvas = canvasEl.getBoundingClientRect();
            const isStructural = element.dataset.type === 'element';
            
            dragOffset.x = e.clientX - rect.left;
            dragOffset.y = e.clientY - rect.top;

            element.style.zIndex = 1000;

            const onMouseMove = (e) => {
                if (!isDragging) return;
                
                let x = e.clientX - canvas.left - dragOffset.x;
                let y = e.clientY - canvas.top - dragOffset.y;
                
                // Limiter aux bords du canvas
                x = Math.max(0, Math.min(x, canvas.width - element.offsetWidth));
                y = Math.max(0, Math.min(y, canvas.height - element.offsetHeight));

                // Snapping magnétique pour murs/portes/fenêtres
                if (isStructural) {
                    const snapped = snapToNearbyElements(element, x, y);
                    x = snapped.x;
                    y = snapped.y;
                }
                
                element.style.left = x + 'px';
                element.style.top = y + 'px';
            };

            const onMouseUp = () => {
                if (isDragging) {
                    isDragging = false;
                    element.style.zIndex = '';
                    removeSnapGuides();
                    
                    // Sauvegarder la nouvelle position
                    savePosition(element);
                }
                
                document.removeEventListener('mousemove', onMouseMove);
                document.removeEventListener('mouseup', onMouseUp);
            };

            document.addEventListener('mousemove', onMouseMove);
            document.addEventListener('mouseup', onMouseUp);

            e.preventDefault();
        });
    }

    /**
     * Snapping magnétique : colle les murs/portes/fenêtres proches
     */
    function snapToNearbyElements(draggedEl, x, y) {
        const canvasEl = document.getElementById('floor-canvas');
        const others = canvasEl.querySelectorAll('.canvas-element');
        const w = draggedEl.offsetWidth;
        const h = draggedEl.offsetHeight;
        
        let snappedX = x;
        let snappedY = y;
        let didSnap = false;

        others.forEach(other => {
            if (other === draggedEl) return;
            
            const ox = parseInt(other.style.left);
            const oy = parseInt(other.style.top);
            const ow = other.offsetWidth;
            const oh = other.offsetHeight;

            // Snap bord droit de l'élément déplacé au bord gauche de l'autre
            if (Math.abs((x + w) - ox) < SNAP_THRESHOLD) {
                snappedX = ox - w;
                didSnap = true;
            }
            // Snap bord gauche au bord droit de l'autre
            if (Math.abs(x - (ox + ow)) < SNAP_THRESHOLD) {
                snappedX = ox + ow;
                didSnap = true;
            }
            // Snap bord gauche à bord gauche (alignement)
            if (Math.abs(x - ox) < SNAP_THRESHOLD) {
                snappedX = ox;
                didSnap = true;
            }
            // Snap bord droit à bord droit
            if (Math.abs((x + w) - (ox + ow)) < SNAP_THRESHOLD) {
                snappedX = ox + ow - w;
                didSnap = true;
            }

            // Snap bord bas au bord haut de l'autre
            if (Math.abs((y + h) - oy) < SNAP_THRESHOLD) {
                snappedY = oy - h;
                didSnap = true;
            }
            // Snap bord haut au bord bas de l'autre
            if (Math.abs(y - (oy + oh)) < SNAP_THRESHOLD) {
                snappedY = oy + oh;
                didSnap = true;
            }
            // Snap bord haut à bord haut (alignement horizontal)
            if (Math.abs(y - oy) < SNAP_THRESHOLD) {
                snappedY = oy;
                didSnap = true;
            }
            // Snap bord bas à bord bas
            if (Math.abs((y + h) - (oy + oh)) < SNAP_THRESHOLD) {
                snappedY = oy + oh - h;
                didSnap = true;
            }
        });

        // Afficher/masquer le guide visuel
        if (didSnap) {
            showSnapGuide(draggedEl);
        } else {
            removeSnapGuides();
        }

        return { x: snappedX, y: snappedY };
    }

    /**
     * Afficher un indicateur visuel de snapping
     */
    function showSnapGuide(el) {
        el.classList.add('snapping');
    }

    /**
     * Retirer les indicateurs de snapping
     */
    function removeSnapGuides() {
        document.querySelectorAll('.snapping').forEach(el => el.classList.remove('snapping'));
    }

    /**
     * Rendre un élément sélectionnable
     */
    function makeSelectable(element, data) {
        element.addEventListener('click', (e) => {
            if (isDragging) return;
            
            // Désélectionner l'ancien
            if (selectedElement) {
                selectedElement.classList.remove('selected');
            }
            
            // Sélectionner le nouveau
            selectedElement = element;
            element.classList.add('selected');
            
            // Afficher les propriétés
            showProperties(data, element.dataset.type);
            
            e.stopPropagation();
        });
    }

    /**
     * Sauvegarder la position d'un élément
     */
    function savePosition(element) {
        const id = element.dataset.id;
        const type = element.dataset.type;
        const x = parseInt(element.style.left);
        const y = parseInt(element.style.top);

        const data = new FormData();
        data.append('csrf_token', csrfToken);
        data.append('position_x', x);
        data.append('position_y', y);

        const endpoint = type === 'table' 
            ? `?page=floor-plan-update-table` 
            : `?page=floor-plan-update-element`;
        
        data.append(type === 'table' ? 'table_id' : 'element_id', id);

        fetch(endpoint, {
            method: 'POST',
            body: data
        })
        .then(res => res.json())
        .then(result => {
            if (result.success) {
                if (result.csrf_token) csrfToken = result.csrf_token;
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Erreur',
                    text: result.message || 'Erreur de sauvegarde'
                });
            }
        })
        .catch(err => console.error('Error saving position:', err));
    }

    /**
     * Afficher le panneau de propriétés
     */
    function showProperties(data, type) {
        const panel = document.getElementById('properties-panel');
        const content = document.getElementById('properties-content');
        
        if (type === 'table') {
            const typeLabels = { round: 'Ronde', square: 'Carrée', rectangle: 'Rectangle' };
            content.innerHTML = `
                <div class="form-group">
                    <label>Numéro de table</label>
                    <input type="text" id="prop-table-number" value="${data.table_number}">
                </div>
                <div class="form-group">
                    <label>Forme</label>
                    <select id="prop-shape">
                        <option value="round" ${data.shape === 'round' ? 'selected' : ''}>Ronde</option>
                        <option value="square" ${data.shape === 'square' ? 'selected' : ''}>Carrée</option>
                        <option value="rectangle" ${data.shape === 'rectangle' ? 'selected' : ''}>Rectangle</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Capacité min</label>
                    <input type="number" id="prop-capacity-min" value="${data.capacity_min}" min="1">
                </div>
                <div class="form-group">
                    <label>Capacité max</label>
                    <input type="number" id="prop-capacity-max" value="${data.capacity_max}" min="1">
                </div>
                <div class="form-group">
                    <label>Zone (optionnel)</label>
                    <input type="text" id="prop-zone" value="${data.zone || ''}" placeholder="Ex: Terrasse">
                </div>
                <div class="properties-actions">
                    <button class="btn-primary" onclick="window.floorPlanEditor.saveTableProperties(${data.id})">
                        <i class="fas fa-save"></i> Enregistrer
                    </button>
                    <button class="btn-danger" onclick="window.floorPlanEditor.deleteItem(${data.id}, 'table')">
                        <i class="fas fa-trash"></i> Supprimer
                    </button>
                </div>
            `;
        } else {
            const typeLabels = { wall: 'Mur', door: 'Porte', window: 'Fenêtre' };
            content.innerHTML = `
                <p style="color: var(--color-text-light); margin: 0 0 16px 0;">
                    <i class="fas fa-info-circle"></i> Type : <strong>${typeLabels[data.element_type] || data.element_type}</strong>
                </p>
                <p style="color: var(--color-text-muted); font-size: 0.85rem; margin: 0 0 16px 0;">
                    Déplacez l'élément en le glissant sur le canvas.
                </p>
                <button class="btn-danger" style="width: 100%;" onclick="window.floorPlanEditor.deleteItem(${data.id}, 'element')">
                    <i class="fas fa-trash"></i> Supprimer
                </button>
            `;
        }
        
        panel.style.display = 'block';
    }

    /**
     * Sauvegarder les propriétés d'une table
     */
    function saveTableProperties(tableId) {
        const data = new FormData();
        data.append('csrf_token', csrfToken);
        data.append('table_id', tableId);
        data.append('table_number', document.getElementById('prop-table-number').value);
        data.append('shape', document.getElementById('prop-shape').value);
        data.append('capacity_min', document.getElementById('prop-capacity-min').value);
        data.append('capacity_max', document.getElementById('prop-capacity-max').value);
        data.append('zone', document.getElementById('prop-zone').value);

        fetch('?page=floor-plan-update-table', {
            method: 'POST',
            body: data
        })
        .then(res => res.json())
        .then(result => {
            if (result.success) {
                if (result.csrf_token) csrfToken = result.csrf_token;
                Swal.fire({
                    icon: 'success',
                    title: 'Table mise à jour',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000
                });
                // Fermer le panneau après enregistrement
                document.getElementById('properties-panel').style.display = 'none';
                if (selectedElement) {
                    selectedElement.classList.remove('selected');
                    selectedElement = null;
                }
                reloadFloorData();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Erreur',
                    text: result.message || 'Erreur de mise à jour'
                });
            }
        })
        .catch(err => {
            console.error('Error updating table:', err);
            Swal.fire({
                icon: 'error',
                title: 'Erreur',
                text: 'Erreur de mise à jour'
            });
        });
    }

    /**
     * Sauvegarder les propriétés d'un élément
     */
    function saveElementProperties(elementId) {
        const data = new FormData();
        data.append('csrf_token', csrfToken);
        data.append('element_id', elementId);
        data.append('element_type', document.getElementById('prop-element-type').value);

        fetch('?page=floor-plan-update-element', {
            method: 'POST',
            body: data
        })
        .then(res => res.json())
        .then(result => {
            if (result.success) {
                if (result.csrf_token) csrfToken = result.csrf_token;
                Swal.fire({
                    icon: 'success',
                    title: 'Élément mis à jour',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000
                });
                reloadFloorData();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Erreur',
                    text: result.message || 'Erreur de mise à jour'
                });
            }
        })
        .catch(err => {
            console.error('Error updating element:', err);
            Swal.fire({
                icon: 'error',
                title: 'Erreur',
                text: 'Erreur de mise à jour'
            });
        });
    }

    /**
     * Supprimer l'élément sélectionné
     */
    function deleteSelected() {
        if (!selectedElement) {
            Swal.fire({
                icon: 'warning',
                title: 'Aucun élément sélectionné',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000
            });
            return;
        }

        Swal.fire({
            title: 'Supprimer cet élément ?',
            text: 'Cette action est irréversible',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Oui, supprimer',
            cancelButtonText: 'Annuler',
            confirmButtonColor: '#d33'
        }).then((result) => {
            if (!result.isConfirmed) return;

            const id = selectedElement.dataset.id;
            const type = selectedElement.dataset.type;

            const data = new FormData();
            data.append('csrf_token', csrfToken);
            data.append(type === 'table' ? 'table_id' : 'element_id', id);

            const endpoint = type === 'table' 
                ? '?page=floor-plan-delete-table' 
                : '?page=floor-plan-delete-element';

            fetch(endpoint, {
                method: 'POST',
                body: data
            })
            .then(res => res.json())
            .then(result => {
                if (result.success) {
                    if (result.csrf_token) csrfToken = result.csrf_token;
                    Swal.fire({
                        icon: 'success',
                        title: 'Élément supprimé',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000
                    });
                    selectedElement = null;
                    document.getElementById('properties-panel').style.display = 'none';
                    reloadFloorData();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erreur',
                        text: result.message || 'Erreur de suppression'
                    });
                }
            })
            .catch(err => {
                console.error('Error deleting:', err);
                Swal.fire({
                    icon: 'error',
                    title: 'Erreur',
                    text: 'Erreur de suppression'
                });
            });
        });
    }

    /**
     * Recharger les données de l'étage
     */
    function reloadFloorData() {
        fetch(`?page=floor-plan-get-data&floor_id=${currentFloorId}`)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    tables = data.tables;
                    elements = data.elements;
                    renderCanvas();
                }
            })
            .catch(err => console.error('Error reloading data:', err));
    }

    /**
     * Configuration des onglets d'étages
     */
    function setupFloorTabs() {
        const tabs = document.querySelectorAll('.floor-tab');
        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                const floorId = tab.dataset.floorId;
                window.location.href = `?page=floor-plan&floor_id=${floorId}`;
            });
        });
    }

    /**
     * Configuration des modals
     */
    function setupModals() {
        // Modal ajout étage
        document.getElementById('add-floor-btn')?.addEventListener('click', () => {
            document.getElementById('add-floor-modal').style.display = 'flex';
        });

        document.getElementById('cancel-floor-btn')?.addEventListener('click', () => {
            document.getElementById('add-floor-modal').style.display = 'none';
        });

        document.getElementById('add-floor-form')?.addEventListener('submit', (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            
            fetch('?page=floor-plan-create-floor', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    if (data.csrf_token) csrfToken = data.csrf_token;
                    Swal.fire({
                        icon: 'success',
                        title: 'Étage créé',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000
                    });
                    location.reload();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erreur',
                        text: data.message || 'Erreur lors de la création'
                    });
                }
            });
        });

        // Modal édition/suppression étage
        document.getElementById('edit-floor-btn')?.addEventListener('click', () => {
            const activeTab = document.querySelector('.floor-tab.active');
            if (activeTab) {
                document.getElementById('edit-floor-id').value = currentFloorId;
                document.getElementById('edit-floor-name').value = activeTab.textContent.trim();
                document.getElementById('edit-floor-modal').style.display = 'flex';
            }
        });

        document.getElementById('cancel-edit-floor-btn')?.addEventListener('click', () => {
            document.getElementById('edit-floor-modal').style.display = 'none';
        });

        document.getElementById('edit-floor-form')?.addEventListener('submit', (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            formData.append('display_order', '0');
            
            fetch('?page=floor-plan-update-floor', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    if (data.csrf_token) csrfToken = data.csrf_token;
                    Swal.fire({
                        icon: 'success',
                        title: 'Étage mis à jour',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000
                    });
                    location.reload();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erreur',
                        text: data.message || 'Erreur'
                    });
                }
            });
        });

        // Supprimer l'étage
        document.getElementById('delete-floor-btn')?.addEventListener('click', () => {
            Swal.fire({
                title: 'Supprimer cet étage ?',
                text: 'L\'étage et tous ses éléments seront supprimés définitivement.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Oui, supprimer',
                cancelButtonText: 'Annuler',
                confirmButtonColor: '#d33'
            }).then((result) => {
                if (!result.isConfirmed) return;
                
                const formData = new FormData();
                formData.append('csrf_token', csrfToken);
                formData.append('floor_id', currentFloorId);
                
                fetch('?page=floor-plan-delete-floor', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Étage supprimé',
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000
                        });
                        window.location.href = '?page=floor-plan';
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Erreur',
                            text: data.message || 'Erreur'
                        });
                    }
                });
            });
        });

        // Vider tous les éléments de l'étage
        document.getElementById('clear-floor-btn')?.addEventListener('click', () => {
            Swal.fire({
                title: 'Vider cet étage ?',
                text: 'Toutes les tables et éléments de cet étage seront supprimés.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Oui, tout vider',
                cancelButtonText: 'Annuler',
                confirmButtonColor: '#f59e0b'
            }).then((result) => {
                if (!result.isConfirmed) return;
                
                const formData = new FormData();
                formData.append('csrf_token', csrfToken);
                formData.append('floor_id', currentFloorId);
                
                fetch('?page=floor-plan-clear-floor', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        if (data.csrf_token) csrfToken = data.csrf_token;
                        Swal.fire({
                            icon: 'success',
                            title: 'Étage vidé',
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000
                        });
                        reloadFloorData();
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Erreur',
                            text: data.message || 'Erreur'
                        });
                    }
                });
            });
        });
    }

    /**
     * Afficher un toast
     */
    function showToast(message, type = 'info') {
        if (typeof window.showToast === 'function') {
            window.showToast(message, type);
        } else {
            alert(message);
        }
    }

    /**
     * Supprimer un élément depuis le panneau de propriétés
     */
    function deleteItem(id, type) {
        Swal.fire({
            title: 'Supprimer cet élément ?',
            text: 'Cette action est irréversible',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Oui, supprimer',
            cancelButtonText: 'Annuler',
            confirmButtonColor: '#d33'
        }).then((result) => {
            if (!result.isConfirmed) return;

            const data = new FormData();
            data.append('csrf_token', csrfToken);
            data.append(type === 'table' ? 'table_id' : 'element_id', id);

            const endpoint = type === 'table' 
                ? '?page=floor-plan-delete-table' 
                : '?page=floor-plan-delete-element';

            fetch(endpoint, {
                method: 'POST',
                body: data
            })
            .then(res => res.json())
            .then(result => {
                if (result.success) {
                    if (result.csrf_token) csrfToken = result.csrf_token;
                    Swal.fire({
                        icon: 'success',
                        title: 'Élément supprimé',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000
                    });
                    selectedElement = null;
                    document.getElementById('properties-panel').style.display = 'none';
                    reloadFloorData();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erreur',
                        text: result.message || 'Erreur de suppression'
                    });
                }
            });
        });
    }

    // Exposer les fonctions publiques
    window.floorPlanEditor = {
        saveTableProperties,
        saveElementProperties,
        deleteItem
    };

    // Initialiser au chargement
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
