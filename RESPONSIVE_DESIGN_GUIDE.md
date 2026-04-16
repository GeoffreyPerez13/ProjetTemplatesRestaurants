# Guide de Design Responsive - MenuMiam

## 📱 Breakpoints Standards

### Définition des tailles d'écran

```css
/* Desktop (par défaut) : > 1024px */
/* Tablette : 768px - 1024px */
/* Mobile : 480px - 768px */
/* Petit mobile : < 480px */
```

---

## 🎯 Système de Boutons Responsive

### Tailles des boutons selon l'écran

| Écran | Padding | Font-size | Icône |
|-------|---------|-----------|-------|
| **Desktop** (> 1024px) | `10px 20px` | `14px` | `14px` |
| **Tablette** (768-1024px) | `9px 16px` | `13px` | `13px` |
| **Mobile** (480-768px) | `8px 14px` | `12px` | `12px` |
| **Petit mobile** (< 480px) | `7px 12px` | `11px` | `11px` |

### Code CSS à utiliser

```css
/* Desktop (par défaut) */
.btn {
    padding: 10px 20px;
    font-size: 14px;
}

/* Tablette */
@media (max-width: 1024px) {
    .btn {
        padding: 9px 16px;
        font-size: 13px;
    }
}

/* Mobile */
@media (max-width: 768px) {
    .btn {
        padding: 8px 14px;
        font-size: 12px;
    }
}

/* Petit mobile */
@media (max-width: 480px) {
    .btn {
        padding: 7px 12px;
        font-size: 11px;
    }
    
    .btn i {
        font-size: 11px;
    }
}
```

---

## 📐 Layout Responsive

### Section Header (Titre + Boutons)

#### Desktop
```
┌─────────────────────────────────────┐
│ Titre                    [Btn] [Btn]│
└─────────────────────────────────────┘
```

#### Mobile (< 768px)
```
┌─────────────────────────────────────┐
│ Titre                               │
│ [Btn] [Btn] [Btn]                   │
└─────────────────────────────────────┘
```

#### Code CSS

```css
/* Desktop : flex-direction: row */
.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

/* Mobile : flex-direction: column */
@media (max-width: 768px) {
    .section-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
    }
    
    .section-header > div {
        width: 100%;
        justify-content: flex-start;
        flex-wrap: wrap;
    }
}
```

---

## 📝 Formulaires Responsive

### Grilles de formulaire

#### Desktop : Grille horizontale
```
┌────────┬──────────┬──────────┬────┐
│ Champ1 │ Champ2   │ Champ3   │ Btn│
└────────┴──────────┴──────────┴────┘
```

#### Mobile : Grille verticale
```
┌─────────────────┐
│ Champ1          │
├─────────────────┤
│ Champ2          │
├─────────────────┤
│ Champ3      Btn │
└─────────────────┘
```

#### Code CSS

```css
/* Desktop */
.form-row {
    display: grid;
    grid-template-columns: 80px 1fr 200px 40px;
    gap: 12px;
}

/* Mobile */
@media (max-width: 768px) {
    .form-row {
        grid-template-columns: 1fr auto;
        gap: 8px;
    }
    
    .form-row > div:nth-child(1),
    .form-row > div:nth-child(2) {
        grid-column: 1 / -1;
    }
    
    .form-row > div:nth-child(3) {
        grid-column: 1 / 2;
    }
    
    .form-row > div:nth-child(4) {
        grid-column: 2 / 3;
    }
}
```

---

## 🎨 Textes Responsive

### Tailles de police recommandées

| Élément | Desktop | Tablette | Mobile | Petit mobile |
|---------|---------|----------|--------|--------------|
| **H1** | `32px` | `28px` | `24px` | `20px` |
| **H2** | `24px` | `22px` | `20px` | `18px` |
| **H3** | `20px` | `18px` | `16px` | `14px` |
| **Body** | `16px` | `15px` | `14px` | `13px` |
| **Small** | `14px` | `13px` | `12px` | `11px` |

---

## ✅ Bonnes Pratiques

### 1. Mobile First
Commencer par le design mobile, puis ajouter les media queries pour desktop.

### 2. Touch Targets
Les boutons doivent avoir une taille minimale de **44x44px** sur mobile pour être facilement cliquables.

### 3. Espacement
Réduire les marges et paddings sur mobile :
- Desktop : `gap: 20px`
- Tablette : `gap: 16px`
- Mobile : `gap: 12px`
- Petit mobile : `gap: 8px`

### 4. Grilles Flexibles
Utiliser `fr` et `auto` plutôt que des pixels fixes pour les grilles.

### 5. Images Responsive
Toujours utiliser `max-width: 100%` et `height: auto` sur les images.

---

## 🔧 Outils de Test

### Breakpoints à tester
- **320px** : iPhone SE
- **375px** : iPhone 12/13
- **768px** : iPad Portrait
- **1024px** : iPad Landscape
- **1440px** : Desktop standard
- **1920px** : Full HD

### DevTools
Utiliser F12 → Mode responsive pour tester toutes les tailles.

---

## 📦 Application dans le Projet

### Fichiers concernés
- `app/Views/admin/edit-card.php` ✅ (Système implémenté)
- `app/Views/admin/dashboard.php` ⏳ (À implémenter)
- `app/Views/admin/settings.php` ⏳ (À implémenter)
- `app/Views/admin/reservations.php` ⏳ (À implémenter)
- `app/Views/admin/stats.php` ⏳ (À implémenter)

### Checklist d'implémentation
- [ ] Boutons responsive (4 breakpoints)
- [ ] Section headers (titre + boutons en colonne sur mobile)
- [ ] Formulaires (grilles adaptatives)
- [ ] Textes (tailles adaptées)
- [ ] Espacements (gap réduits)
- [ ] Touch targets (min 44x44px)

---

## 🚀 Exemple Complet

```css
/* ========== SYSTÈME RESPONSIVE GLOBAL ========== */

/* Desktop (par défaut) */
.btn {
    padding: 10px 20px;
    font-size: 14px;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

/* Tablette (768px - 1024px) */
@media (max-width: 1024px) {
    .btn {
        padding: 9px 16px;
        font-size: 13px;
    }
}

/* Mobile (< 768px) */
@media (max-width: 768px) {
    .btn {
        padding: 8px 14px;
        font-size: 12px;
    }
    
    .section-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
    }
    
    .section-header > div {
        width: 100%;
        justify-content: flex-start;
        flex-wrap: wrap;
    }
}

/* Petit mobile (< 480px) */
@media (max-width: 480px) {
    .btn {
        padding: 7px 12px;
        font-size: 11px;
    }
    
    .btn i {
        font-size: 11px;
    }
}
```

---

## 📌 Notes Importantes

1. **Maintenabilité** : Ce système doit être appliqué de manière cohérente dans toute l'application.
2. **Performance** : Éviter les media queries trop nombreuses, regrouper les styles similaires.
3. **Accessibilité** : Toujours tester avec des vrais appareils mobiles, pas seulement DevTools.
4. **Évolution** : Ce guide doit être mis à jour si de nouveaux breakpoints sont nécessaires.

---

**Dernière mise à jour** : 16 avril 2026
**Auteur** : Équipe MenuMiam
**Version** : 1.0
