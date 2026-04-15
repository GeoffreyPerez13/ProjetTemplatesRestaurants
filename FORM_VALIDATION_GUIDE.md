# Guide de Validation des Formulaires - MenuMiam

## Vue d'ensemble

Le système de validation visuelle des formulaires fournit un feedback immédiat à l'utilisateur avec des couleurs :
- **Vert** : Champ valide et correctement rempli
- **Rouge** : Champ invalide avec message d'erreur explicite

## Activation automatique

Le système s'active automatiquement sur **tous les formulaires** de l'application, sauf :
- Les formulaires de recherche (classe `search-form`)
- Les formulaires avec l'ID `search-form`

## Fichiers créés

### CSS
- `public/assets/css/admin/form-validation.css`
  - Styles pour les champs valides/invalides
  - Messages d'erreur
  - Support du dark mode

### JavaScript
- `public/assets/js/admin/form-validation.js`
  - Validation en temps réel
  - Gestion des événements (input, blur, change)
  - Prévention de soumission si erreurs

## Intégration dans une page

### 1. Inclure le CSS dans le `<head>`
```php
<link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/css/admin/form-validation.css">
```

### 2. Inclure le JS avant la fermeture du `<body>`
```php
<script src="<?= BASE_URL ?>/public/assets/js/admin/form-validation.js"></script>
```

## Astérisques rouges automatiques

Le système ajoute **automatiquement** un astérisque rouge `*` après chaque label de champ obligatoire.

### Fonctionnement
- Détecte tous les champs avec l'attribut `required`
- Trouve le label associé (via `for`, parent, ou précédent)
- Ajoute un `<span class="required-asterisk"> *</span>` rouge

### Exemple
```html
<label for="nom">Nom de la catégorie</label>
<input type="text" id="nom" name="nom" required>
<!-- Résultat affiché : "Nom de la catégorie *" (avec * en rouge) -->
```

Pas besoin d'ajouter manuellement les `*` dans vos labels, c'est automatique !

## Attributs HTML supportés

Le système valide automatiquement les attributs HTML5 standards :

### Attributs de validation
- `required` : Champ obligatoire
- `minlength` : Longueur minimale
- `maxlength` : Longueur maximale
- `min` : Valeur minimale (nombres)
- `max` : Valeur maximale (nombres)
- `pattern` : Expression régulière
- `type="email"` : Validation email
- `type="number"` : Validation nombre

### Exemple de formulaire
```html
<form id="mon-formulaire">
    <!-- Champ texte obligatoire -->
    <input type="text" name="nom" required minlength="2" maxlength="100">
    
    <!-- Email obligatoire -->
    <input type="email" name="email" required>
    
    <!-- Nombre avec limites -->
    <input type="number" name="ordre" min="1" max="50" required>
    
    <!-- Textarea optionnel -->
    <textarea name="description" maxlength="500"></textarea>
    
    <button type="submit">Enregistrer</button>
</form>
```

## Comportement

### Validation en temps réel
- La validation se déclenche sur les événements `input`, `blur` et `change`
- Les bordures et le fond changent de couleur instantanément
- Les messages d'erreur apparaissent sous le champ concerné

### Soumission du formulaire
- Si des champs sont invalides, la soumission est bloquée
- Un toast d'erreur s'affiche : "Veuillez corriger les erreurs dans le formulaire"
- Le scroll se positionne automatiquement sur le premier champ invalide
- Le focus est mis sur ce champ

### Réinitialisation
Pour réinitialiser manuellement la validation d'un formulaire :
```javascript
const form = document.getElementById('mon-formulaire');
const validator = new FormValidation.FormValidator(form);
validator.reset();
```

## Classes CSS appliquées

### Sur les champs
- `.field-valid` : Champ valide (vert)
- `.field-invalid` : Champ invalide (rouge)

### Messages d'erreur
- `.field-error-message` : Conteneur du message d'erreur

## Personnalisation

### Désactiver la validation sur un formulaire spécifique
Ajoutez la classe `no-validation` :
```html
<form class="no-validation">
    <!-- Ce formulaire ne sera pas validé -->
</form>
```

Puis modifiez `form-validation.js` :
```javascript
forms.forEach(form => {
    if (form.classList.contains('search-form') || 
        form.classList.contains('no-validation') || 
        form.id === 'search-form') {
        return;
    }
    new FormValidator(form);
});
```

## Dark Mode

Le système supporte automatiquement le dark mode :
- Champs valides : fond vert foncé (#064e3b)
- Champs invalides : fond rouge foncé (#450a0a)
- Messages d'erreur : texte rouge clair (#fca5a5)

## Corrections appliquées

### Problèmes résolus
1. ✅ Erreurs console (loadDishes en boucle)
2. ✅ Ordres commençant à 1 au lieu de 0
3. ✅ Validation visuelle rouge/vert implémentée
4. ✅ Messages d'erreur explicites au lieu de "Erreur de connexion au serveur"

### Migration BDD
Exécuter le script SQL pour normaliser les ordres existants :
```sql
-- Fichier : database/migrations/fix_display_orders.sql
UPDATE categories SET display_order = display_order + 1 WHERE display_order >= 0;
UPDATE dishes SET display_order = display_order + 1 WHERE display_order >= 0;
```

## Pages intégrées

- ✅ `app/Views/admin/edit-card.php`

## TODO : Intégrer dans les autres pages

Pour appliquer la validation à toutes les pages de l'application, ajouter dans chaque vue admin :

### Dans le `<head>`
```php
<link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/css/admin/form-validation.css">
```

### Avant `</body>`
```php
<script src="<?= BASE_URL ?>/public/assets/js/admin/form-validation.js"></script>
```

### Pages à mettre à jour
- [ ] `app/Views/admin/dashboard.php`
- [ ] `app/Views/admin/settings.php`
- [ ] `app/Views/admin/contact.php`
- [ ] `app/Views/admin/logo-banner.php`
- [ ] `app/Views/admin/services.php`
- [ ] `app/Views/admin/reservations.php`
- [ ] Toutes les autres vues admin
