# Guide d'Utilisation de l'Infrastructure AJAX - MenuMiam V2

## 📋 Vue d'ensemble

Toute l'application MenuMiam V2 fonctionne **sans rechargement de page** grâce à une infrastructure AJAX globale.

## 🎯 Principes

- **Feedback instantané** : Notifications toast pour chaque action
- **Pas de rechargement** : Toutes les soumissions de formulaires en AJAX
- **Réponses standardisées** : Format JSON uniforme
- **Réutilisable** : Helpers JavaScript globaux

---

## 🚀 Utilisation Côté Frontend (JavaScript)

### **Méthode 1 : Classe CSS automatique (Recommandé)**

Ajoutez simplement la classe `ajax-form` à votre formulaire :

```html
<form method="POST" action="/update-profile" class="ajax-form">
    <input type="text" name="username" required>
    <button type="submit">Enregistrer</button>
</form>
```

**C'est tout !** Le formulaire sera automatiquement soumis en AJAX.

### **Options via data-attributes**

```html
<form method="POST" action="/update-profile" class="ajax-form" 
      data-reset-on-success="true">
    <!-- Le formulaire sera réinitialisé après succès -->
</form>
```

### **Méthode 2 : Utilisation manuelle**

```javascript
const form = document.getElementById('my-form');

App.ajaxForm(form, {
    onSuccess: (data) => {
        console.log('Succès !', data);
        // Actions personnalisées après succès
    },
    onError: (data) => {
        console.log('Erreur', data);
    },
    onComplete: (data) => {
        console.log('Terminé', data);
    }
});
```

### **Méthode 3 : Requête AJAX personnalisée**

```javascript
App.ajaxRequest({
    url: '/api/delete-item',
    method: 'POST',
    data: new FormData(form),
    onSuccess: (data) => {
        // Supprimer l'élément du DOM
        element.remove();
    }
});
```

---

## 🔧 Utilisation Côté Backend (PHP)

### **Format de Réponse Standardisé**

Toutes les réponses doivent suivre ce format :

```php
{
    "success": true/false,
    "message": "Message pour l'utilisateur",
    "data": {...},      // Optionnel
    "errors": {...},    // Optionnel (si success = false)
    "redirect": "/url"  // Optionnel
}
```

### **Méthodes BaseController**

#### **1. Réponse de succès**

```php
// Dans votre Controller
public function updateProfile(): void
{
    $this->requireAuth();
    
    // Validation et traitement...
    
    // Réponse AJAX ou redirection classique
    if ($this->isAjax()) {
        $this->jsonSuccess('Profil mis à jour avec succès', [
            'username' => $newUsername
        ]);
    } else {
        $this->success('Profil mis à jour avec succès');
        $this->redirect('/dashboard');
    }
}
```

#### **2. Réponse d'erreur**

```php
if ($validator->fails()) {
    if ($this->isAjax()) {
        $this->jsonError('Erreur de validation', $validator->errors());
    } else {
        $this->error($validator->first('field'));
        $this->redirect('/back');
    }
}
```

#### **3. Avec redirection**

```php
$this->jsonSuccess('Compte créé avec succès', [], '/dashboard');
// Le frontend redirigera automatiquement après 1 seconde
```

---

## 🎨 Notifications Toast

### **Afficher un toast manuellement**

```javascript
// Succès
App.showToast('Opération réussie !', 'success');

// Erreur
App.showToast('Une erreur est survenue', 'error');

// Info
App.showToast('Information importante', 'info');

// Warning
App.showToast('Attention !', 'warning');
```

### **Types de toasts**

- `success` : Vert avec ✓
- `error` : Rouge avec ✗
- `info` : Bleu avec ℹ
- `warning` : Orange avec ⚠

---

## 📦 Loader Global

Le loader s'affiche automatiquement pendant les requêtes AJAX.

Pour le désactiver sur une requête spécifique :

```javascript
App.ajaxRequest({
    url: '/api/check',
    showLoader: false,  // Pas de loader
    onSuccess: (data) => {
        // ...
    }
});
```

---

## 🔄 Pattern Complet : Exemple CRUD

### **Frontend (Vue)**

```html
<!-- Liste des éléments -->
<div id="items-list">
    <div class="item" data-id="1">
        <span>Élément 1</span>
        <button onclick="deleteItem(1)">Supprimer</button>
    </div>
</div>

<!-- Formulaire d'ajout -->
<form method="POST" action="/add-item" class="ajax-form" data-reset-on-success="true">
    <input type="text" name="name" required>
    <button type="submit">Ajouter</button>
</form>

<script>
function deleteItem(id) {
    if (!confirm('Supprimer cet élément ?')) return;
    
    App.ajaxRequest({
        url: '/delete-item',
        method: 'POST',
        data: new FormData().append('id', id),
        onSuccess: () => {
            document.querySelector(`[data-id="${id}"]`).remove();
        }
    });
}
</script>
```

### **Backend (Controller)**

```php
public function addItem(): void
{
    $this->requireAuth();
    
    $validator = new Validator($_POST);
    $validator->required('name');
    
    if ($validator->fails()) {
        $this->jsonError('Le nom est requis', $validator->errors());
    }
    
    $id = Item::create([
        'name' => $_POST['name'],
        'admin_id' => $this->getAuthId()
    ]);
    
    $this->jsonSuccess('Élément ajouté avec succès', ['id' => $id]);
}

public function deleteItem(): void
{
    $this->requireAuth();
    
    $id = $_POST['id'] ?? null;
    
    if (!$id || !Item::delete($id, $this->getAuthId())) {
        $this->jsonError('Impossible de supprimer l\'élément');
    }
    
    $this->jsonSuccess('Élément supprimé avec succès');
}
```

---

## 📱 Responsive

Les toasts sont automatiquement responsive :
- Desktop : En haut à droite
- Mobile : Pleine largeur en haut

---

## ✅ Checklist pour Nouvelle Fonctionnalité

1. **Frontend** :
   - [ ] Ajouter `class="ajax-form"` au formulaire
   - [ ] Ou utiliser `App.ajaxForm()` manuellement
   - [ ] Gérer les callbacks si nécessaire

2. **Backend** :
   - [ ] Vérifier si AJAX avec `$this->isAjax()`
   - [ ] Retourner `jsonSuccess()` ou `jsonError()`
   - [ ] Garder le fallback classique pour non-AJAX

3. **Test** :
   - [ ] Vérifier le toast de succès
   - [ ] Vérifier le toast d'erreur
   - [ ] Vérifier que le loader s'affiche
   - [ ] Tester sur mobile

---

## 🎯 Avantages

✅ **UX moderne** : Pas de rechargement  
✅ **Feedback immédiat** : Toast notifications  
✅ **Code réutilisable** : Helpers globaux  
✅ **Facile à utiliser** : Juste une classe CSS  
✅ **Performant** : Seules les données changent  
✅ **Progressive** : Fallback classique si JS désactivé  

---

## 🔗 Fichiers Importants

- `public/assets/js/app.js` : Helpers JavaScript
- `public/assets/css/shared/toast.css` : Styles toast et loader
- `app/Controllers/BaseController.php` : Méthodes JSON
