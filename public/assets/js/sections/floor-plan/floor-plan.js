/**
 * Éditeur de plan de salle - Drag & Drop
 */

(function() {
    'use strict';

    // État global
    let currentTool = 'select';
    let selectedElement = null;
    let isDragging = false;
    let isRotating = false;
    let dragOffset = { x: 0, y: 0 };
    let rotationStart = { angle: 0, mouseAngle: 0 };
    let currentFloorId = window.floorPlanData?.currentFloorId || null;
    let tables = window.floorPlanData?.tables || [];
    let elements = window.floorPlanData?.elements || [];
    let csrfToken = window.floorPlanData?.csrfToken || '';

    // Compteurs pour numérotation auto
    let tableCounter = tables.length + 1;
    const SNAP_THRESHOLD = 15; // pixels de distance pour le snapping
    const ANGLE_SNAP_THRESHOLD = 10; // degrés de tolérance pour snapping d'angle
    const GRID_SIZE = 20; // taille de la grille en pixels
    let saveRotationTimeout = null;
    let savePositionTimeout = null;
    
    // Queue pour sérialiser les requêtes AJAX et éviter conflits CSRF
    let requestQueue = [];
    let isProcessingRequest = false;

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
        setupKeyboardShortcuts();
        renderCanvas();
        
        // Afficher le toast si paramètre présent dans l'URL
        const urlParams = new URLSearchParams(window.location.search);
        const toast = urlParams.get('toast');
        if (toast === 'floor-created') {
            Swal.fire({
                icon: 'success',
                title: 'Salle créée',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000
            });
            // Nettoyer l'URL
            window.history.replaceState({}, '', '?page=floor-plan');
        }

        console.log('Floor plan editor initialized');
    }

    /**
     * Configuration des raccourcis clavier
     */
    function setupKeyboardShortcuts() {
        document.addEventListener('keydown', (e) => {
            // Touche Delete ou Suppr pour supprimer l'élément sélectionné
            if ((e.key === 'Delete' || e.key === 'Suppr') && selectedElement) {
                e.preventDefault();
                const id = selectedElement.dataset.id;
                const type = selectedElement.dataset.type;
                deleteItem(id, type);
            }
        });
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
     * Basculer sur l'outil Sélectionner
     */
    function switchToSelectTool() {
        currentTool = 'select';
        
        // Mettre à jour l'UI
        const toolButtons = document.querySelectorAll('.tool-btn');
        toolButtons.forEach(btn => {
            if (btn.dataset.tool === 'select') {
                btn.classList.add('active');
            } else {
                btn.classList.remove('active');
            }
        });
        
        // Changer le curseur du canvas
        const canvas = document.getElementById('floor-canvas');
        canvas.style.cursor = 'default';
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
            let x = e.clientX - rect.left;
            let y = e.clientY - rect.top;
            
            // Snap à la grille pour le placement initial
            x = Math.round(x / GRID_SIZE) * GRID_SIZE;
            y = Math.round(y / GRID_SIZE) * GRID_SIZE;
            
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
        data.append('capacity_min', '1');
        data.append('capacity_max', '4');
        // Position déjà snapée à la grille, pas besoin de centrer
        data.append('position_x', x);
        data.append('position_y', y);
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
        // Position déjà snapée à la grille, pas besoin de centrer
        data.append('position_x', x);
        data.append('position_y', y);
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
        
        if (table.rotation) {
            div.style.transform = `rotate(${table.rotation}deg)`;
        }

        div.innerHTML = `
            <div class="table-number">${table.table_number}</div>
            <div class="table-capacity">${table.capacity_min}-${table.capacity_max}p</div>
        `;

        addRotationHandles(div);
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

        addRotationHandles(div);
        makeDraggable(div);
        makeSelectable(div, element);

        return div;
    }

    /**
     * Ajouter les poignées de rotation aux 4 coins
     */
    function addRotationHandles(element) {
        const positions = ['top-left', 'top-right', 'bottom-left', 'bottom-right'];
        
        positions.forEach(pos => {
            const handle = document.createElement('div');
            handle.className = `rotation-handle ${pos}`;
            handle.dataset.position = pos;
            
            handle.addEventListener('mousedown', (e) => {
                e.stopPropagation();
                startRotation(element, e);
            });
            
            element.appendChild(handle);
        });
    }

    /**
     * Démarrer la rotation interactive
     */
    function startRotation(element, e) {
        isRotating = true;
        const rect = element.getBoundingClientRect();
        const centerX = rect.left + rect.width / 2;
        const centerY = rect.top + rect.height / 2;
        
        // Récupérer la rotation actuelle
        const currentRotation = getCurrentRotation(element);
        rotationStart.angle = currentRotation;
        rotationStart.mouseAngle = Math.atan2(e.clientY - centerY, e.clientX - centerX) * (180 / Math.PI);
        
        element.classList.add('rotating');

        const onMouseMove = (moveEvent) => {
            if (!isRotating) return;
            
            const rect = element.getBoundingClientRect();
            const centerX = rect.left + rect.width / 2;
            const centerY = rect.top + rect.height / 2;
            const currentMouseAngle = Math.atan2(moveEvent.clientY - centerY, moveEvent.clientX - centerX) * (180 / Math.PI);
            
            let newRotation = rotationStart.angle + (currentMouseAngle - rotationStart.mouseAngle);
            // Normaliser entre 0 et 360
            newRotation = ((newRotation % 360) + 360) % 360;
            
            // Snapping automatique à 45° (0°, 45°, 90°, 135°, 180°, 225°, 270°, 315°)
            const snapAngles = [0, 45, 90, 135, 180, 225, 270, 315];
            let snapped = false;
            for (const angle of snapAngles) {
                if (Math.abs(newRotation - angle) < ANGLE_SNAP_THRESHOLD) {
                    newRotation = angle;
                    snapped = true;
                    break;
                }
            }
            // Vérifier aussi 360° = 0°
            if (!snapped && Math.abs(newRotation - 360) < ANGLE_SNAP_THRESHOLD) {
                newRotation = 0;
            }
            
            element.style.transform = `rotate(${newRotation}deg)`;
            element.dataset.pendingRotation = newRotation;
        };

        const onMouseUp = () => {
            if (isRotating) {
                isRotating = false;
                element.classList.remove('rotating');
                
                // Sauvegarder la rotation
                const newRotation = element.dataset.pendingRotation;
                if (newRotation !== undefined) {
                    saveRotation(element, Math.round(parseFloat(newRotation)));
                    delete element.dataset.pendingRotation;
                }
            }
            
            document.removeEventListener('mousemove', onMouseMove);
            document.removeEventListener('mouseup', onMouseUp);
        };

        document.addEventListener('mousemove', onMouseMove);
        document.addEventListener('mouseup', onMouseUp);
        e.preventDefault();
    }

    /**
     * Récupérer la rotation actuelle d'un élément
     */
    function getCurrentRotation(element) {
        const id = element.dataset.id;
        const type = element.dataset.type;
        
        if (type === 'table') {
            const table = tables.find(t => t.id == id);
            return parseInt(table?.rotation || 0);
        } else {
            const el = elements.find(e => e.id == id);
            return parseInt(el?.rotation || 0);
        }
    }

    /**
     * Sauvegarder la rotation d'un élément (avec debounce pour éviter erreurs CSRF)
     */
    function saveRotation(element, rotation) {
        // Annuler le timeout précédent si existant
        if (saveRotationTimeout) {
            clearTimeout(saveRotationTimeout);
        }

        // Attendre 300ms avant de sauvegarder
        saveRotationTimeout = setTimeout(() => {
            const id = element.dataset.id;
            const type = element.dataset.type;

            const data = new FormData();
            data.append('csrf_token', csrfToken);
            data.append('rotation', rotation);

            const endpoint = type === 'table' 
                ? `?page=floor-plan-update-table` 
                : `?page=floor-plan-update-element`;
            
            data.append(type === 'table' ? 'table_id' : 'element_id', id);

            queueRequest(() => {
                return fetch(endpoint, {
                    method: 'POST',
                    body: data
                })
                .then(res => res.json())
                .then(result => {
                    if (result.success) {
                        if (result.csrf_token) csrfToken = result.csrf_token;
                        reloadFloorData();
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Erreur',
                            text: result.message || 'Erreur de sauvegarde'
                        });
                    }
                })
                .catch(err => {
                    console.error('Error saving rotation:', err);
                    Swal.fire({
                        icon: 'error',
                        title: 'Erreur',
                        text: 'Erreur de sauvegarde de la rotation'
                    });
                });
            });
        }, 300);
    }

    /**
     * Ajouter une requête à la queue pour éviter les conflits CSRF
     */
    function queueRequest(requestFn) {
        requestQueue.push(requestFn);
        processQueue();
    }

    /**
     * Traiter la queue de requêtes une par une
     */
    function processQueue() {
        if (isProcessingRequest || requestQueue.length === 0) {
            return;
        }

        isProcessingRequest = true;
        const requestFn = requestQueue.shift();

        requestFn()
            .finally(() => {
                isProcessingRequest = false;
                // Traiter la prochaine requête après un petit délai
                setTimeout(processQueue, 100);
            });
    }

    /**
     * Rendre un élément draggable
     */
    function makeDraggable(element) {
        element.addEventListener('mousedown', (e) => {
            // Ignorer si on clique sur une poignée de rotation
            if (e.target.classList.contains('rotation-handle')) return;
            
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

                // Snap strict à la grille - pas de position libre
                x = Math.round(x / GRID_SIZE) * GRID_SIZE;
                y = Math.round(y / GRID_SIZE) * GRID_SIZE;
                
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
     * Snapping magnétique : colle les murs/portes/fenêtres proches (tous les sens)
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

            // Snapping horizontal (X)
            // Bord droit → bord gauche
            if (Math.abs((x + w) - ox) < SNAP_THRESHOLD) {
                snappedX = ox - w;
                didSnap = true;
            }
            // Bord gauche → bord droit
            if (Math.abs(x - (ox + ow)) < SNAP_THRESHOLD) {
                snappedX = ox + ow;
                didSnap = true;
            }
            // Alignement bord gauche
            if (Math.abs(x - ox) < SNAP_THRESHOLD) {
                snappedX = ox;
                didSnap = true;
            }
            // Alignement bord droit
            if (Math.abs((x + w) - (ox + ow)) < SNAP_THRESHOLD) {
                snappedX = ox + ow - w;
                didSnap = true;
            }
            // Centre horizontal aligné
            if (Math.abs((x + w/2) - (ox + ow/2)) < SNAP_THRESHOLD) {
                snappedX = ox + ow/2 - w/2;
                didSnap = true;
            }

            // Snapping vertical (Y)
            // Bord bas → bord haut
            if (Math.abs((y + h) - oy) < SNAP_THRESHOLD) {
                snappedY = oy - h;
                didSnap = true;
            }
            // Bord haut → bord bas
            if (Math.abs(y - (oy + oh)) < SNAP_THRESHOLD) {
                snappedY = oy + oh;
                didSnap = true;
            }
            // Alignement bord haut
            if (Math.abs(y - oy) < SNAP_THRESHOLD) {
                snappedY = oy;
                didSnap = true;
            }
            // Alignement bord bas
            if (Math.abs((y + h) - (oy + oh)) < SNAP_THRESHOLD) {
                snappedY = oy + oh - h;
                didSnap = true;
            }
            // Centre vertical aligné
            if (Math.abs((y + h/2) - (oy + oh/2)) < SNAP_THRESHOLD) {
                snappedY = oy + oh/2 - h/2;
                didSnap = true;
            }

            // Snapping perpendiculaire : mur horizontal qui touche mur vertical
            // Si l'élément déplacé est horizontal et l'autre vertical (ou inversement)
            // Vérifier si le milieu de l'un touche le bord de l'autre
            
            // Milieu gauche/droit de draggedEl → bord haut/bas de other
            if (Math.abs(x - (ox + ow/2)) < SNAP_THRESHOLD) {
                snappedX = ox + ow/2;
                didSnap = true;
            }
            if (Math.abs((x + w) - (ox + ow/2)) < SNAP_THRESHOLD) {
                snappedX = ox + ow/2 - w;
                didSnap = true;
            }
            
            // Milieu haut/bas de draggedEl → bord gauche/droit de other
            if (Math.abs(y - (oy + oh/2)) < SNAP_THRESHOLD) {
                snappedY = oy + oh/2;
                didSnap = true;
            }
            if (Math.abs((y + h) - (oy + oh/2)) < SNAP_THRESHOLD) {
                snappedY = oy + oh/2 - h;
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
            
            // Basculer automatiquement sur l'outil Sélectionner
            switchToSelectTool();
            
            // Désélectionner l'ancien
            if (selectedElement) {
                selectedElement.classList.remove('selected');
            }
            
            selectedElement = element;
            element.classList.add('selected');
            
            // Afficher les propriétés
            showProperties(data, element.dataset.type);
            
            e.stopPropagation();
        });
    }

    /**
     * Sauvegarder la position d'un élément (avec debounce)
     */
    function savePosition(element) {
        // Annuler le timeout précédent si existant
        if (savePositionTimeout) {
            clearTimeout(savePositionTimeout);
        }

        // Attendre 300ms avant de sauvegarder
        savePositionTimeout = setTimeout(() => {
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

            queueRequest(() => {
                return fetch(endpoint, {
                    method: 'POST',
                    body: data
                })
                .then(res => res.json())
                .then(result => {
                    if (result.success && result.csrf_token) {
                        csrfToken = result.csrf_token;
                    } else if (!result.success) {
                        console.error('Error saving position:', result.message);
                    }
                })
                .catch(err => console.error('Error saving position:', err));
            });
        }, 300);
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
                <div class="form-group">
                    <label>Rotation</label>
                    <div style="display: flex; gap: 8px;">
                        <button class="btn-secondary" style="flex: 1;" onclick="window.floorPlanEditor.rotateElement(${data.id}, 'table', -45)" title="Pivoter à gauche (45°)">
                            <i class="fas fa-undo"></i> -45°
                        </button>
                        <button class="btn-secondary" style="flex: 1;" onclick="window.floorPlanEditor.rotateElement(${data.id}, 'table', 0)" title="Réinitialiser">
                            <i class="fas fa-sync"></i> 0°
                        </button>
                        <button class="btn-secondary" style="flex: 1;" onclick="window.floorPlanEditor.rotateElement(${data.id}, 'table', 45)" title="Pivoter à droite (45°)">
                            <i class="fas fa-redo"></i> +45°
                        </button>
                    </div>
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
                <div class="form-group">
                    <label>Rotation</label>
                    <div style="display: flex; gap: 8px;">
                        <button class="btn-secondary" style="flex: 1;" onclick="window.floorPlanEditor.rotateElement(${data.id}, 'element', -45)" title="Pivoter à gauche (45°)">
                            <i class="fas fa-undo"></i> -45°
                        </button>
                        <button class="btn-secondary" style="flex: 1;" onclick="window.floorPlanEditor.rotateElement(${data.id}, 'element', 0)" title="Réinitialiser">
                            <i class="fas fa-sync"></i> 0°
                        </button>
                        <button class="btn-secondary" style="flex: 1;" onclick="window.floorPlanEditor.rotateElement(${data.id}, 'element', 45)" title="Pivoter à droite (45°)">
                            <i class="fas fa-redo"></i> +45°
                        </button>
                    </div>
                </div>
                <button class="btn-danger" style="width: 100%; margin-top: 12px;" onclick="window.floorPlanEditor.deleteItem(${data.id}, 'element')">
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
     * Recharger les données de la salle
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
     * Configuration des onglets de salles
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
        // Modal ajout salle
        document.getElementById('add-floor-btn')?.addEventListener('click', () => {
            // Mettre à jour le token CSRF dans le formulaire
            const csrfInput = document.querySelector('#add-floor-form input[name="csrf_token"]');
            if (csrfInput) {
                csrfInput.value = csrfToken;
            }
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
                    // Recharger immédiatement avec paramètre pour afficher le toast
                    window.location.href = '?page=floor-plan&toast=floor-created';
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erreur',
                        text: data.message || 'Erreur lors de la création'
                    });
                }
            });
        });

        // Bouton recentrer la vue
        document.getElementById('recenter-canvas-btn')?.addEventListener('click', () => {
            const container = document.querySelector('.floor-canvas-container');
            if (container) {
                // Recentrer horizontalement et verticalement
                container.scrollLeft = (container.scrollWidth - container.clientWidth) / 2;
                container.scrollTop = (container.scrollHeight - container.clientHeight) / 2;
            }
        });

        // Modal édition/suppression salle
        document.getElementById('edit-floor-btn')?.addEventListener('click', () => {
            const activeTab = document.querySelector('.floor-tab.active');
            if (activeTab) {
                document.getElementById('edit-floor-id').value = currentFloorId;
                document.getElementById('edit-floor-name').value = activeTab.textContent.trim();
                // Mettre à jour le token CSRF dans le formulaire d'édition
                const csrfInput = document.querySelector('#edit-floor-form input[name="csrf_token"]');
                if (csrfInput) {
                    csrfInput.value = csrfToken;
                }
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
                        title: 'Salle mise à jour',
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

        // Supprimer la salle
        document.getElementById('delete-floor-btn')?.addEventListener('click', () => {
            Swal.fire({
                title: 'Supprimer cette salle ?',
                text: 'La salle et tous ses éléments seront supprimés définitivement.',
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
                            title: 'Salle supprimée',
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

        // Supprimer toutes les salles
        document.getElementById('delete-all-floors-btn')?.addEventListener('click', () => {
            Swal.fire({
                title: 'Supprimer TOUTES les salles ?',
                text: 'Toutes les salles et leur contenu seront supprimés définitivement. Cette action est irréversible.',
                icon: 'error',
                showCancelButton: true,
                confirmButtonText: 'Oui, tout supprimer',
                cancelButtonText: 'Annuler',
                confirmButtonColor: '#dc2626'
            }).then((result) => {
                if (!result.isConfirmed) return;
                
                const formData = new FormData();
                formData.append('csrf_token', csrfToken);
                
                fetch('?page=floor-plan-delete-all-floors', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        if (data.csrf_token) csrfToken = data.csrf_token;
                        
                        // Mettre à jour l'UI immédiatement
                        const floorTabsContainer = document.querySelector('.floor-tabs');
                        const allTabs = floorTabsContainer.querySelectorAll('.floor-tab');
                        
                        // Supprimer tous les onglets sauf le premier
                        allTabs.forEach((tab, index) => {
                            if (index > 0) {
                                tab.remove();
                            }
                        });
                        
                        // Activer le premier onglet
                        if (allTabs.length > 0) {
                            allTabs[0].classList.add('active');
                            currentFloorId = data.first_floor_id || allTabs[0].dataset.floorId;
                        }
                        
                        // Vider le canvas
                        document.getElementById('floor-canvas').innerHTML = '';
                        document.getElementById('properties-panel').style.display = 'none';
                        tables = [];
                        elements = [];
                        
                        Swal.fire({
                            icon: 'success',
                            title: 'Toutes les salles supprimées',
                            text: 'La salle principale a été conservée et vidée',
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 2000
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Erreur',
                            text: data.message || 'Erreur de suppression'
                        });
                    }
                })
                .catch(err => {
                    console.error('Error deleting all floors:', err);
                    Swal.fire({
                        icon: 'error',
                        title: 'Erreur',
                        text: 'Erreur de suppression'
                    });
                });
            });
        });

        // Vider tous les éléments de la salle
        document.getElementById('clear-floor-btn')?.addEventListener('click', () => {
            Swal.fire({
                title: 'Vider cette salle ?',
                text: 'Toutes les tables et éléments de cette salle seront supprimés.',
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
                            title: 'Salle vidée',
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

    /**
     * Faire pivoter un élément ou une table
     */
    function rotateElement(id, type, angle) {
        // Trouver l'élément actuel
        const current = type === 'table' 
            ? tables.find(t => t.id == id)
            : elements.find(e => e.id == id);
        
        if (!current) return;

        // Calculer la nouvelle rotation
        let newRotation = angle;
        if (angle !== 0) {
            // Si c'est +90 ou -90, ajouter à la rotation actuelle
            const currentRotation = parseInt(current.rotation || 0);
            newRotation = currentRotation + angle;
            // Normaliser entre 0 et 360
            newRotation = ((newRotation % 360) + 360) % 360;
        }

        const data = new FormData();
        data.append('csrf_token', csrfToken);
        data.append('rotation', newRotation);

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
                Swal.fire({
                    icon: 'success',
                    title: 'Rotation appliquée',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 1500
                });
                reloadFloorData();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Erreur',
                    text: result.message || 'Erreur de rotation'
                });
            }
        })
        .catch(err => {
            console.error('Error rotating:', err);
            Swal.fire({
                icon: 'error',
                title: 'Erreur',
                text: 'Erreur de rotation'
            });
        });
    }

    // Exposer les fonctions publiques
    window.floorPlanEditor = {
        saveTableProperties,
        saveElementProperties,
        deleteItem,
        rotateElement
    };

    // Initialiser au chargement
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
