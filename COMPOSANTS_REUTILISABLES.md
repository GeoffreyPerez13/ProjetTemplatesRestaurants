# Composants Réutilisables - MenuMiam

Ce document liste tous les composants, patterns et logiques réutilisables pour toute l'application.

## 📋 Table des matières
1. [AJAX et Messages](#ajax-et-messages)
2. [Validation de Formulaires](#validation-de-formulaires)
3. [Accordéons](#accordéons)
4. [Drag & Drop](#drag--drop)
5. [Modales](#modales)
6. [Compteurs Dynamiques](#compteurs-dynamiques)

---

## 🔄 AJAX et Messages

### Pattern : Rechargement avec Message de Succès

**Problème** : Afficher un message de succès après rechargement de page.

**Solution** : Utiliser `sessionStorage` pour persister le message.

```javascript
// Avant rechargement
fetch(url, { method: 'POST', body: formData })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            sessionStorage.setItem('successMessage', 'Opération réussie');
            location.reload();
        }
    });

// Au chargement de la page
document.addEventListener('DOMContentLoaded', () => {
    const successMessage = sessionStorage.getItem('successMessage');
    if (successMessage) {
        App.showToast(successMessage, 'success');
        sessionStorage.removeItem('successMessage');
    }
});
```

### Pattern : Fetch Natif au lieu de App.ajaxRequest

**Problème** : `App.ajaxRequest` affiche automatiquement les messages (double affichage).

**Solution** : Utiliser `fetch` natif pour un contrôle total.

```javascript
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
        App.showToast('Message personnalisé', 'success');
        // Actions...
    } else {
        App.showToast(data.message || 'Erreur', 'error');
    }
})
.catch(error => {
    console.error('Erreur:', error);
    App.showToast('Erreur lors de l\'opération', 'error');
});
```

**Fichier** : Utiliser dans toutes les vues admin.

---

## ✅ Validation de Formulaires

### Pattern : Validation Manuelle avec Styles Inline

**Problème** : Le système de validation automatique ne fonctionne pas toujours.

**Solution** : Validation manuelle avec styles inline.

```javascript
// Dans le submit du formulaire
const nameInput = document.getElementById('field-name');

// Validation
if (!nameInput.value.trim()) {
    // Champ invalide (rouge)
    nameInput.style.borderColor = '#ef4444';
    nameInput.style.backgroundColor = '#fef2f2';
    App.showToast('Veuillez remplir tous les champs obligatoires', 'error');
    nameInput.focus();
    return;
} else {
    // Champ valide (vert)
    nameInput.style.borderColor = '#10b981';
    nameInput.style.backgroundColor = '#f0fdf4';
}
```

### Pattern : Réinitialisation des Styles de Validation

```javascript
function resetFormValidation(form) {
    form.querySelectorAll('input, textarea, select').forEach(field => {
        field.style.borderColor = '';
        field.style.backgroundColor = '';
        field.classList.remove('field-valid', 'field-invalid');
    });
}

// Utilisation
const form = document.getElementById('my-form');
form.reset();
resetFormValidation(form);
```

### Pattern : Astérisques Rouges Automatiques

**Fichiers** :
- `public/assets/js/admin/form-validation.js`
- `public/assets/css/admin/form-validation.css`

**Intégration** :
```html
<!-- Dans le <head> -->
<link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/css/admin/form-validation.css">

<!-- Avant </body> -->
<script src="<?= BASE_URL ?>/public/assets/js/admin/form-validation.js"></script>
```

Les `*` rouges sont ajoutés automatiquement sur tous les champs `required`.

---

## 📂 Accordéons

### Pattern : Accordéon avec Bouton Tout Déplier/Replier

**HTML** :
```html
<button id="toggle-all-btn" onclick="toggleAll()">
    <i class="fas fa-chevron-down"></i> Tout déplier
</button>

<div class="accordion-item">
    <div class="accordion-header" onclick="toggleAccordion(1)">
        <span>Titre</span>
        <i id="toggle-1" class="fas fa-chevron-right"></i>
    </div>
    <div id="content-1" class="accordion-content" style="display: none;">
        Contenu
    </div>
</div>
```

**JavaScript** :
```javascript
let allExpanded = false;

function toggleAccordion(id) {
    const content = document.getElementById('content-' + id);
    const icon = document.getElementById('toggle-' + id);
    
    if (content.style.display === 'none') {
        content.style.display = 'block';
        icon.style.transform = 'rotate(90deg)';
    } else {
        content.style.display = 'none';
        icon.style.transform = 'rotate(0deg)';
    }
}

function toggleAll() {
    const btn = document.getElementById('toggle-all-btn');
    const icon = btn.querySelector('i');
    allExpanded = !allExpanded;
    
    document.querySelectorAll('.accordion-content').forEach(content => {
        content.style.display = allExpanded ? 'block' : 'none';
    });
    
    document.querySelectorAll('.accordion-header i').forEach(icon => {
        icon.style.transform = allExpanded ? 'rotate(90deg)' : 'rotate(0deg)';
    });
    
    if (allExpanded) {
        icon.className = 'fas fa-chevron-up';
        btn.innerHTML = '<i class="fas fa-chevron-up"></i> Tout replier';
    } else {
        icon.className = 'fas fa-chevron-down';
        btn.innerHTML = '<i class="fas fa-chevron-down"></i> Tout déplier';
    }
}
```

**CSS** :
```css
.accordion-header {
    cursor: pointer;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px;
    background: var(--color-bg-alt);
    border-radius: 8px;
    transition: all 0.3s;
}

.accordion-header:hover {
    background: var(--color-bg-warm);
}

.accordion-header i {
    transition: transform 0.3s;
}

.accordion-content {
    padding: 16px;
    transition: all 0.3s;
}
```

---

## 🎯 Drag & Drop

### Pattern : Drag & Drop avec Mise à Jour des Inputs

**HTML** :
```html
<div class="draggable-item" draggable="true" data-id="1">
    <span>Élément 1</span>
    <input type="number" value="1" min="1" 
           data-item-id="1" 
           onchange="updateOrder(1, this.value)">
</div>
```

**JavaScript** :
```javascript
function initDragDrop() {
    const items = document.querySelectorAll('.draggable-item');
    let draggedElement = null;
    
    items.forEach(item => {
        item.addEventListener('dragstart', function(e) {
            draggedElement = this;
            this.classList.add('dragging');
        });
        
        item.addEventListener('dragend', function(e) {
            this.classList.remove('dragging');
            saveOrder();
        });
        
        item.addEventListener('dragover', function(e) {
            e.preventDefault();
            if (draggedElement !== this) {
                this.classList.add('drag-over');
            }
        });
        
        item.addEventListener('dragleave', function(e) {
            this.classList.remove('drag-over');
        });
        
        item.addEventListener('drop', function(e) {
            e.preventDefault();
            if (draggedElement !== this) {
                const allItems = Array.from(document.querySelectorAll('.draggable-item'));
                const draggedIndex = allItems.indexOf(draggedElement);
                const targetIndex = allItems.indexOf(this);
                
                if (draggedIndex < targetIndex) {
                    this.parentNode.insertBefore(draggedElement, this.nextSibling);
                } else {
                    this.parentNode.insertBefore(draggedElement, this);
                }
            }
            this.classList.remove('drag-over');
        });
    });
}

function saveOrder() {
    const items = document.querySelectorAll('.draggable-item');
    
    // Mettre à jour les inputs d'ordre
    items.forEach((item, index) => {
        const orderInput = item.querySelector('input[data-item-id]');
        if (orderInput) {
            orderInput.value = index + 1;
        }
    });
    
    // Sauvegarder en BDD
    const order = Array.from(items).map(item => item.dataset.id);
    // ... fetch pour sauvegarder
}
```

**CSS** :
```css
.draggable-item {
    cursor: move;
    user-select: none;
    transition: all 0.3s;
}

.draggable-item.dragging {
    opacity: 0.5;
}

.draggable-item.drag-over {
    border-top: 3px solid var(--color-primary);
}
```

---

## 🪟 Modales

### Pattern : Modale Réutilisable

**HTML** :
```html
<div id="my-modal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modal-title">Titre</h3>
            <button class="close-modal" onclick="closeModal('my-modal')">&times;</button>
        </div>
        <form id="my-form">
            <!-- Contenu -->
            <button type="submit">Enregistrer</button>
        </form>
    </div>
</div>
```

**JavaScript** :
```javascript
function openModal(modalId, title = null) {
    const modal = document.getElementById(modalId);
    const form = modal.querySelector('form');
    
    if (form) {
        form.reset();
        resetFormValidation(form);
    }
    
    if (title) {
        const titleElement = modal.querySelector('#modal-title');
        if (titleElement) titleElement.textContent = title;
    }
    
    modal.classList.add('show');
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.remove('show');
}

// Fermer en cliquant en dehors
document.querySelectorAll('.modal').forEach(modal => {
    modal.addEventListener('click', function(e) {
        if (e.target === this) {
            this.classList.remove('show');
        }
    });
});
```

**CSS** :
```css
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
    background: var(--color-bg);
    border-radius: 12px;
    max-width: 600px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    border-bottom: 1px solid var(--color-border);
}

.close-modal {
    background: none;
    border: none;
    font-size: 28px;
    cursor: pointer;
    color: var(--color-text-light);
}
```

---

## 🔢 Compteurs Dynamiques

### Pattern : Mise à Jour de Compteur après AJAX

```javascript
function updateCounter(categoryId) {
    fetch(baseUrl + '/items/' + categoryId, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.data.items) {
            const count = data.data.items.length;
            const categoryItem = document.querySelector(`[data-id="${categoryId}"]`);
            if (categoryItem) {
                const countSpan = categoryItem.querySelector('.counter');
                if (countSpan) {
                    countSpan.textContent = `(${count} élément${count > 1 ? 's' : ''})`;
                }
            }
        }
    })
    .catch(error => console.error('Erreur compteur:', error));
}
```

---

## 🎨 Styles Globaux Réutilisables

### Variables CSS
```css
/* Fichier : public/assets/css/shared/_variables.css */
:root {
    --color-primary: #f59e0b;
    --color-success: #10b981;
    --color-error: #ef4444;
    --color-bg: #ffffff;
    --color-text: #1f2937;
    --color-border: #e5e7eb;
}
```

### Classes Utilitaires
```css
.btn-primary {
    background: var(--color-primary);
    color: white;
    padding: 10px 20px;
    border-radius: 8px;
    border: none;
    cursor: pointer;
    transition: all 0.3s;
}

.btn-primary:hover {
    opacity: 0.9;
    transform: translateY(-2px);
}

.field-valid {
    border-color: var(--color-success) !important;
    background-color: #f0fdf4 !important;
}

.field-invalid {
    border-color: var(--color-error) !important;
    background-color: #fef2f2 !important;
}
```

---

## 📝 Checklist d'Intégration

Lors de l'ajout de ces composants sur une nouvelle page :

- [ ] Inclure `form-validation.css` et `form-validation.js`
- [ ] Utiliser `fetch` natif au lieu de `App.ajaxRequest`
- [ ] Implémenter `sessionStorage` pour messages après rechargement
- [ ] Ajouter validation manuelle avec styles inline
- [ ] Réinitialiser les formulaires correctement (styles + valeurs)
- [ ] Mettre à jour les compteurs dynamiquement après AJAX
- [ ] Synchroniser drag&drop avec inputs d'ordre
- [ ] Valider les ordres (min=1, empêcher 0)
- [ ] Tester en dark mode

---

## 🔧 Fonctions Utilitaires Globales

Créer un fichier `public/assets/js/admin/utils.js` :

```javascript
// Réinitialiser validation formulaire
function resetFormValidation(form) {
    form.querySelectorAll('input, textarea, select').forEach(field => {
        field.style.borderColor = '';
        field.style.backgroundColor = '';
        field.classList.remove('field-valid', 'field-invalid');
    });
}

// Valider un champ
function validateField(field, errorMessage = 'Ce champ est obligatoire') {
    if (!field.value.trim()) {
        field.style.borderColor = '#ef4444';
        field.style.backgroundColor = '#fef2f2';
        App.showToast(errorMessage, 'error');
        field.focus();
        return false;
    } else {
        field.style.borderColor = '#10b981';
        field.style.backgroundColor = '#f0fdf4';
        return true;
    }
}

// Afficher message après rechargement
function showStoredMessage() {
    const message = sessionStorage.getItem('successMessage');
    if (message) {
        App.showToast(message, 'success');
        sessionStorage.removeItem('successMessage');
    }
}

// Stocker message pour après rechargement
function storeSuccessMessage(message) {
    sessionStorage.setItem('successMessage', message);
}
```

---

**Dernière mise à jour** : 15 avril 2026  
**Fichiers concernés** : Toutes les vues admin
