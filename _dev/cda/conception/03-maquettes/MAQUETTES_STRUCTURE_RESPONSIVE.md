# 🎨 Maquettage et Structure Responsive - MenuMiam V2
## Design System et Architecture UX

---

## 🎯 **1. VISION DESIGN ET STRATÉGIE UX**

### **1.1 Philosophie de Design**
- **Simplicité avant tout** : Interface intuitive pour restaurateurs non-techniques
- **Mobile-first** : Optimisé pour usage sur smartphone (80% des consultations)
- **Performance visuelle** : Chargement rapide et animations fluides
- **Accessibilité universelle** : WCAG 2.1 AA compliance
- **Personnalisation avancée** : Chaque restaurant a une identité unique

### **1.2 Personas Utilisateurs**

#### **Persona Principal : Le Restaurateur**
```
Nom : Marie Dubois
Âge : 42 ans
Rôle : Gérante de restaurant bistronomique
Expérience : Intermédiaire (utilise Facebook, Instagram)
Objectifs :
- Mettre à jour sa carte rapidement (5min max)
- Avoir une vitrine moderne qui attire les clients
- Suivre les statistiques de fréquentation
- Gérer les réservations sans stress
Frustrations :
- Solutions techniques trop complexes
- Interfaces lentes et peu intuitives
- Manque de personnalisation
- Support technique réactif
```

#### **Persona Secondaire : Le Client Final**
```
Nom : Thomas Martin
Âge : 28 ans
Comportement : Scanne les QR codes au restaurant
Objectifs :
- Consulter la carte rapidement
- Voir les photos des plats
- Connaître les allergènes
- Faire une réservation si nécessaire
Attentes :
- Interface rapide et responsive
- Photos haute qualité
- Informations claires et complètes
```

---

## 📱 **2. ARCHITECTURE MOBILE-FIRST**

### **2.1 Breakpoints Responsives**
```css
/* Mobile First Breakpoints */
/* Mobile : 320px - 767px */
@media (min-width: 320px) { /* Base mobile styles */ }

/* Tablet : 768px - 1023px */
@media (min-width: 768px) { /* Tablet adaptations */ }

/* Desktop : 1024px - 1439px */
@media (min-width: 1024px) { /* Desktop enhancements */ }

/* Large Desktop : 1440px+ */
@media (min-width: 1440px) { /* Large screen optimizations */ }
```

### **2.2 Grid System**
```
Mobile (320px+) :
┌─────────────────────┐
│ 12 columns @ 20px   │
│ gutter: 8px         │
│ margin: 16px        │
└─────────────────────┘

Tablet (768px+) :
┌─────────────────────────────┐
│ 12 columns @ 60px            │
│ gutter: 24px                │
│ margin: 32px                │
└─────────────────────────────┘

Desktop (1024px+) :
┌─────────────────────────────────────┐
│ 12 columns @ 80px                    │
│ gutter: 32px                        │
│ max-width: 1200px                    │
│ margin: 0 auto                       │
└─────────────────────────────────────┘
```

---

## 🎨 **3. DESIGN SYSTEM COMPLET**

### **3.1 Palette de Couleurs (Customizable)**
```css
:root {
  /* Primary Colors - Customizable per restaurant */
  --color-primary: #b45309;        /* Amber 600 */
  --color-primary-light: #f59e0b;    /* Amber 500 */
  --color-primary-dark: #92400e;     /* Amber 800 */
  
  /* Semantic Colors */
  --color-success: #16a34a;          /* Green 600 */
  --color-success-light: #22c55e;    /* Green 500 */
  --color-error: #dc2626;            /* Red 600 */
  --color-error-light: #ef4444;      /* Red 500 */
  --color-warning: #ea580c;          /* Orange 600 */
  --color-info: #0ea5e9;             /* Sky 600 */
  
  /* Neutral Colors - Dark mode support */
  --color-bg-primary: #ffffff;
  --color-bg-secondary: #f9fafb;
  --color-bg-tertiary: #f3f4f6;
  --color-text-primary: #111827;
  --color-text-secondary: #6b7280;
  --color-text-tertiary: #9ca3af;
  --color-border: #e5e7eb;
  
  /* Dark Mode Overrides */
  --color-bg-primary-dark: #111827;
  --color-bg-secondary-dark: #1f2937;
  --color-bg-tertiary-dark: #374151;
  --color-text-primary-dark: #f9fafb;
  --color-text-secondary-dark: #d1d5db;
  --color-text-tertiary-dark: #9ca3af;
  --color-border-dark: #4b5563;
}

/* Dark Mode */
body.dark-mode {
  --color-bg-primary: var(--color-bg-primary-dark);
  --color-bg-secondary: var(--color-bg-secondary-dark);
  --color-bg-tertiary: var(--color-bg-tertiary-dark);
  --color-text-primary: var(--color-text-primary-dark);
  --color-text-secondary: var(--color-text-secondary-dark);
  --color-text-tertiary: var(--color-text-tertiary-dark);
  --color-border: var(--color-border-dark);
}
```

### **3.2 Typography System**
```css
:root {
  /* Font Families */
  --font-primary: 'Inter', system-ui, sans-serif;
  --font-secondary: 'Playfair Display', serif;
  --font-mono: 'JetBrains Mono', monospace;
  
  /* Font Sizes - Fluid Typography */
  --font-size-xs: clamp(0.75rem, 0.7rem + 0.25vw, 0.875rem);    /* 12px - 14px */
  --font-size-sm: clamp(0.875rem, 0.8rem + 0.375vw, 1rem);      /* 14px - 16px */
  --font-size-base: clamp(1rem, 0.9rem + 0.5vw, 1.125rem);     /* 16px - 18px */
  --font-size-lg: clamp(1.125rem, 1rem + 0.625vw, 1.25rem);    /* 18px - 20px */
  --font-size-xl: clamp(1.25rem, 1.1rem + 0.75vw, 1.5rem);     /* 20px - 24px */
  --font-size-2xl: clamp(1.5rem, 1.3rem + 1vw, 1.875rem);     /* 24px - 30px */
  --font-size-3xl: clamp(1.875rem, 1.5rem + 1.875vw, 2.25rem); /* 30px - 36px */
  --font-size-4xl: clamp(2.25rem, 1.8rem + 2.25vw, 3rem);      /* 36px - 48px */
  
  /* Line Heights */
  --line-height-tight: 1.25;
  --line-height-normal: 1.5;
  --line-height-relaxed: 1.75;
  
  /* Font Weights */
  --font-weight-light: 300;
  --font-weight-normal: 400;
  --font-weight-medium: 500;
  --font-weight-semibold: 600;
  --font-weight-bold: 700;
  --font-weight-extrabold: 800;
  
  /* Letter Spacing */
  --letter-spacing-tight: -0.025em;
  --letter-spacing-normal: 0;
  --letter-spacing-wide: 0.025em;
  --letter-spacing-wider: 0.05em;
  --letter-spacing-widest: 0.1em;
}
```

### **3.3 Spacing System (8px Grid)**
```css
:root {
  --space-1: 0.25rem;   /* 4px */
  --space-2: 0.5rem;    /* 8px */
  --space-3: 0.75rem;   /* 12px */
  --space-4: 1rem;      /* 16px */
  --space-5: 1.25rem;   /* 20px */
  --space-6: 1.5rem;    /* 24px */
  --space-8: 2rem;      /* 32px */
  --space-10: 2.5rem;   /* 40px */
  --space-12: 3rem;     /* 48px */
  --space-16: 4rem;     /* 64px */
  --space-20: 5rem;     /* 80px */
  --space-24: 6rem;     /* 96px */
  --space-32: 8rem;     /* 128px */
}
```

### **3.4 Border Radius and Shadows**
```css
:root {
  /* Border Radius */
  --radius-none: 0;
  --radius-sm: 0.125rem;   /* 2px */
  --radius-base: 0.25rem;  /* 4px */
  --radius-md: 0.375rem;   /* 6px */
  --radius-lg: 0.5rem;     /* 8px */
  --radius-xl: 0.75rem;    /* 12px */
  --radius-2xl: 1rem;      /* 16px */
  --radius-full: 9999px;
  
  /* Shadows */
  --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
  --shadow-base: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
  --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
  --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
  --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
  --shadow-2xl: 0 25px 50px -12px rgb(0 0 0 / 0.25);
}
```

---

## 📲 **4. WIREFRAMES MOBILE FIRST**

### **4.1 Vitrine Restaurant - Mobile**
```
┌─────────────────────────────┐
│ ☰  Le Bistrot Parisien      │
│    📍 75001 Paris           │
├─────────────────────────────┤
│ 📸 Logo/Banner Restaurant   │
│    (height: 200px)          │
├─────────────────────────────┤
│ 🍽️ NOTRE CARTE             │
├─────────────────────────────┤
│ 🥗 ENTRÉES                  │
│ ┌─────────────────────────┐ │
│ │ 📸 Salade César        │ │
│ │ 12€ • Laitue, poulet   │ │
│ │ 🥜 lactosérum, œuf     │ │
│ └─────────────────────────┘ │
│ ┌─────────────────────────┐ │
│ │ 📸 Soupe à l'oignon    │ │
│ │ 8€ • Oignons, gruyère  │ │
│ └─────────────────────────┘ │
├─────────────────────────────┤
│ 🍕 PLATS PRINCIPAUX        │
│ ┌─────────────────────────┐ │
│ │ 📸 Pizza Margherita    │ │
│ │ 14€ • Mozzarella, basilic│
│ │ 🌾 gluten              │ │
│ └─────────────────────────┘ │
├─────────────────────────────┤
│ 🍨 DESSERTS                │
│ ┌─────────────────────────┐ │
│ │ 📸 Tiramisù            │ │
│ │ 7€ • Mascarpone, café  │ │
│ └─────────────────────────┘ │
├─────────────────────────────┤
│ 📞 01 42 56 78 90         │
│ 🕐 Lun-Dim: 12h-23h       │
│ 📍 15 Rue de la Paix      │
│ 📧 contact@bistrot.fr     │
├─────────────────────────────┤
│ 💳 CB, Espèces, Chèques   │
│ 🚲 Livraison Uber Eats    │
├─────────────────────────────┤
│ ⭐ 4.8/5 (124 avis)        │
│ 📸 Dernière mise à jour   │
│    il y a 2 jours         │
└─────────────────────────────┘
```

### **4.2 Interface Admin - Mobile**
```
┌─────────────────────────────┐
│ 👤 Marie D. ⚙️            │
│    Le Bistrot Parisien      │
├─────────────────────────────┤
│ 📊 Vue d'ensemble          │
│ ┌─────────────────────────┐ │
│ │ 👁️ 245 vistes aujourd'hui│ │
│ │ 📈 +15% vs semaine     │ │
│ └─────────────────────────┘ │
│ ┌─────────────────────────┐ │
│ │ 🍽️ 3 réservations       │ │
│ │ 🕐 19h30, 20h00, 20h30 │ │
│ └─────────────────────────┘ │
├─────────────────────────────┤
│ 🎨 Modifier ma carte        │
│ ┌─────────────────────────┐ │
│ │ 📸 Changer photo        │ │
│ │ ✏️ Modifier prix         │ │
│ │ ➕ Ajouter plat          │ │
│ └─────────────────────────┘ │
├─────────────────────────────┤
│ 📈 Statistiques            │
│ ┌─────────────────────────┐ │
│ │ 📊 Vues par plat        │ │
│ │ 🕐 Heures d'affluence   │ │
│ │ 📱 Appareils utilisés   │ │
│ └─────────────────────────┘ │
├─────────────────────────────┤
│ ⚙️ Paramètres              │
│ ┌─────────────────────────┐ │
│ │ 🎨 Personnalisation     │ │
│ │ 💰 Abonnement           │ │
│ │ 📞 Contact              │ │
│ └─────────────────────────┘ │
└─────────────────────────────┘
```

### **4.3 Processus Onboarding - Mobile**
```
┌─────────────────────────────┐
│ 🎉 Bienvenue sur MenuMiam!  │
│    Créez votre carte       │
│    en 5 minutes           │
├─────────────────────────────┤
│ ┌─────────────────────────┐ │
│ │ 📸                     │ │
│ │ Ajoutez votre logo      │ │
│ │                        │ │
│ │ [Prendre une photo]    │ │
│ │ [Choisir dans galerie] │ │
│ └─────────────────────────┘ │
├─────────────────────────────┤
│ Nom de votre restaurant   │
│ ┌─────────────────────────┐ │
│ │ Le Bistrot Parisien    │ │
│ └─────────────────────────┘ │
├─────────────────────────────┤
│ Choisissez un style       │
│ ┌─────┐ ┌─────┐ ┌─────┐ │
│ │ 🍷  │ │ 🍕  │ │ 🥘  │ │
│ │Class│ │Modern│ │Gourmet│ │
│ │ique │ │      │ │      │ │
│ └─────┘ └─────┘ └─────┘ │
├─────────────────────────────┤
│ 📸 Ajoutez votre première  │
│    photo de plat           │
│ ┌─────────────────────────┐ │
│ │ 📸                     │ │
│ │ Pizza Margherita       │ │
│ │ 14€                    │ │
│ └─────────────────────────┘ │
├─────────────────────────────┤
│ 🚀 Publier votre carte     │
│ ┌─────────────────────────┐ │
│ │ [Publier maintenant]   │ │
│ └─────────────────────────┘ │
└─────────────────────────────┘
```

---

## 💻 **5. WIREFRAMES DESKTOP**

### **5.1 Vitrine Restaurant - Desktop**
```
┌─────────────────────────────────────────────────────────────┐
│ ☰ Le Bistrot Parisien          📞 01 42 56 78 90        │
│    📍 75001 Paris              🕐 Lun-Dim: 12h-23h      │
├─────────────────────────────────────────────────────────────┤
│ ┌─────────────────┐ ┌─────────────────┐ ┌─────────────────┐ │
│ │ 📸               │ │ 🍽️ NOTRE CARTE │ │ 📊 STATISTIQUES │ │
│ │ Logo/Banner     │ │                 │ │                 │ │
│ │ Restaurant      │ │ 🥗 ENTRÉES      │ │ 👁️ 245 vistes   │ │
│ │                 │ │ ┌─────────────┐ │ │ 📈 +15% vs sem │ │
│ │                 │ │ │📸 Salade César│ │ │ 🕐 Pic: 20h    │ │
│ │                 │ │ │12€ • Laitue   │ │ │ 📱 75% mobile  │ │
│ │                 │ │ │poulet, œuf   │ │ │                 │ │
│ │                 │ │ └─────────────┘ │ │ ⭐ 4.8/5 (124) │ │
│ │                 │ │ ┌─────────────┐ │ └─────────────────┘ │
│ │                 │ │ │📸 Soupe oignon│ │                 │ │
│ │                 │ │ │8€ • Oignons  │ │ ┌─────────────────┐ │
│ │                 │ │ └─────────────┘ │ │ 💳 PAIEMENTS    │ │
│ └─────────────────┘ │                 │ │                 │ │
│                     │ 🍕 PLATS        │ │ CB, Espèces     │ │
│ ┌─────────────────┐ │ ┌─────────────┐ │ │ Chèques        │ │
│ │ 📍 ADRESSE       │ │ │📸 Pizza Marg  │ │                 │ │
│ │                 │ │ │14€ • Mozza   │ │ 🚲 LIVRAISON    │ │
│ │ 15 Rue de la Paix│ │ │basilic      │ │ │                 │ │
│ │ 75001 Paris      │ │ └─────────────┘ │ │ Uber Eats      │ │
│ │                 │ │ ┌─────────────┐ │ │ Deliveroo      │ │
│ │ 📧 contact@...   │ │ │📸 Pasta Carbon│ │ │                 │ │
│ │                 │ │ │10€ • Crème   │ │ └─────────────────┘ │
│ └─────────────────┘ │ └─────────────┘ │                 │ │
│                     │                 │                 │ │
│                     │ 🍨 DESSERTS     │                 │ │
│                     │ ┌─────────────┐ │                 │ │
│                     │ │📸 Tiramisù   │ │                 │ │
│                     │ │7€ • Mascarpone│ │                 │ │
│                     │ └─────────────┘ │                 │ │
│                     └─────────────────┘                 │ │
└─────────────────────────────────────────────────────────────┘
```

### **5.2 Dashboard Admin - Desktop**
```
┌─────────────────────────────────────────────────────────────────┐
│ 👤 Marie Dubois ⚙️                    🔔 3 💬 2  🎨 Dark   │
│    Le Bistrot Parisien - Plan PRO              💳 19€/mois   │
├─────────────────────────────────────────────────────────────────┤
│ ┌─────────────┐ ┌─────────────┐ ┌─────────────┐ ┌─────────────┐ │
│ │ 📊 VUES     │ │ 🍽️ PLATS    │ │ 💰 REVENUS  │ │ ⭐ AVIS     │ │
│ │             │ │             │ │             │ │             │ │
│ │ 1,245       │ │ 24          │ │ 2,450€      │ │ 4.8/5      │ │
│ │ +15% ↗️     │ │ +2 ↗️       │ │ +12% ↗️     │ │ +0.2 ↗️    │ │
│ │ vs semaine  │ │ vs semaine  │ │ vs semaine  │ │ vs semaine  │
│ └─────────────┘ └─────────────┘ └─────────────┘ └─────────────┘ │
├─────────────────────────────────────────────────────────────────┤
│ ┌─────────────────────────────┐ ┌─────────────────────────────┐ │
│ │ 🎨 PERSONNALISATION         │ │ 📈 ANALYTICS AVANCÉS       │ │
│ │                             │ │                             │ │
│ │ 📸 [Changer logo]           │ │ 📊 Vistes par heure         │ │
│ │ 🎨 [Modifier couleurs]      │ │ 🍽️ Plats les plus vus       │ │
│ │ 📱 [Aperçu mobile]          │ │ 📱 Appareils utilisés       │ │
│ │ 📝 [Textes personnalisés]    │ │ 🗺️ Localisation des visiteurs│ │
│ │                             │ │                             │ │
│ │ 🎭 Templates:               │ │ 📅 Période:                 │ │
│ │ ● Classic (actuel)          │ │ ┌─────────┐ ┌─────────┐       │ │
│ │ ○ Modern                    │ │ │ 7 jours │ │30 jours │       │ │
│ │ ○ Gourmet                   │ │ └─────────┘ └─────────┘       │ │
│ │ ○ Custom                    │ │                             │ │
│ │                             │ │ [Exporter CSV]              │ │
│ │ [Appliquer les changements] │ │ [Partager rapport]          │ │
│ └─────────────────────────────┘ └─────────────────────────────┘ │
├─────────────────────────────────────────────────────────────────┤
│ ┌─────────────────────────────┐ ┌─────────────────────────────┐ │
│ │ 🍽️ GESTION CARTE             │ │ 📅 RÉSERVATIONS              │ │
│ │                             │ │                             │ │
│ │ 📝 [Ajouter catégorie]       │ │ 📅 Aujourd'hui: 3 réservations│ │
│ │ 🥗 Entrées (3)               │ │ ┌─────────────────────────┐   │ │
│ │ 🍕 Plats (5)                 │ │ │19h30 - Table 4 pers.    │   │ │
│ │ 🍨 Desserts (2)              │ │ │20h00 - Table 2 pers.    │   │ │
│ │ 🥤 Boissons (4)              │ │ │20h30 - Table 6 pers.    │   │ │
│ │                             │ │ └─────────────────────────┘   │ │
│ │ [Gérer les catégories]      │ │                             │ │
│ │ [Importer/Exporter]         │ │ [Voir calendrier complet]   │ │
│ │ [Historique des versions]   │ │ [Paramètres réservations]   │ │
│ └─────────────────────────────┘ └─────────────────────────────┘ │
└─────────────────────────────────────────────────────────────────┘
```

---

## 🎯 **6. USER FLOWS DÉTAILLÉS**

### **6.1 Flow Onboarding Restaurateur**
```
1. LANDING PAGE
   └─> [S'inscrire gratuitement] 
       └─> FORMULAIRE INSCRIPTION
           └─> Email validation
               └─> WELCOME ONBOARDING
                   └─> ÉTAPE 1: Logo/Nom
                       └─> ÉTAPE 2: Template choice
                           └─> ÉTAPE 3: First photo
                               └─> ÉTAPE 4: Contact info
                                   └─> ÉTAPE 5: First dish
                                       └─> PUBLICATION
                                           └─> DASHBOARD PRINCIPAL
```

### **6.2 Flow Consultation Client**
```
1. QR CODE SCAN
   └─> LOADING RESTAURANT
       └─> VITRINE RESTAURANT
           └─> MENU NAVIGATION
               ├─> Category selection
               │   └─> Item details
               │       └─> Back to menu
               └─> [RÉSERVER] (optional)
                   └─> RESERVATION FORM
                       └─> CONFIRMATION
```

### **6.3 Flow Modification Menu**
```
DASHBOARD
└─> [MODIFIER CARTE]
    └─> MENU EDITOR
        ├─> Category management
        │   ├─> [Ajouter catégorie]
        │   ├─> [Modifier catégorie]
        │   └─> [Supprimer catégorie]
        └─> Item management
            ├─> [Ajouter plat]
            │   └─> ITEM FORM
            │       └─> [Sauvegarder]
            ├─> [Modifier plat]
            │   └─> ITEM FORM (pre-filled)
            │       └─> [Mettre à jour]
            └─> [Supprimer plat]
                └─> CONFIRMATION MODAL
```

---

## 🎨 **7. COMPOSANTS UI RÉUTILISABLES**

### **7.1 Button Component System**
```css
/* Base Button */
.btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: var(--space-3) var(--space-6);
  border-radius: var(--radius-md);
  font-weight: var(--font-weight-medium);
  text-decoration: none;
  transition: all 0.2s ease;
  cursor: pointer;
  border: 2px solid transparent;
  min-height: 44px; /* Touch target */
}

/* Button Variants */
.btn--primary {
  background-color: var(--color-primary);
  color: white;
}

.btn--primary:hover {
  background-color: var(--color-primary-dark);
  transform: translateY(-1px);
  box-shadow: var(--shadow-md);
}

.btn--secondary {
  background-color: transparent;
  color: var(--color-primary);
  border-color: var(--color-primary);
}

.btn--secondary:hover {
  background-color: var(--color-primary);
  color: white;
}

.btn--ghost {
  background-color: transparent;
  color: var(--color-text-primary);
}

.btn--ghost:hover {
  background-color: var(--color-bg-secondary);
}

/* Button Sizes */
.btn--sm {
  padding: var(--space-2) var(--space-4);
  font-size: var(--font-size-sm);
}

.btn--lg {
  padding: var(--space-4) var(--space-8);
  font-size: var(--font-size-lg);
}

/* Button States */
.btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
  transform: none;
}

.btn--loading {
  position: relative;
  color: transparent;
}

.btn--loading::after {
  content: '';
  position: absolute;
  width: 16px;
  height: 16px;
  border: 2px solid currentColor;
  border-radius: 50%;
  border-top-color: transparent;
  animation: spin 0.6s linear infinite;
}
```

### **7.2 Card Component System**
```css
.card {
  background-color: var(--color-bg-primary);
  border-radius: var(--radius-lg);
  box-shadow: var(--shadow-base);
  border: 1px solid var(--color-border);
  overflow: hidden;
  transition: all 0.3s ease;
}

.card:hover {
  box-shadow: var(--shadow-lg);
  transform: translateY(-2px);
}

.card__header {
  padding: var(--space-6);
  border-bottom: 1px solid var(--color-border);
}

.card__title {
  font-size: var(--font-size-lg);
  font-weight: var(--font-weight-semibold);
  color: var(--color-text-primary);
  margin: 0;
}

.card__subtitle {
  font-size: var(--font-size-sm);
  color: var(--color-text-secondary);
  margin: var(--space-2) 0 0 0;
}

.card__body {
  padding: var(--space-6);
}

.card__footer {
  padding: var(--space-6);
  border-top: 1px solid var(--color-border);
  background-color: var(--color-bg-secondary);
}

/* Card Variants */
.card--menu-item {
  display: flex;
  flex-direction: column;
}

.card--menu-item__image {
  width: 100%;
  height: 200px;
  object-fit: cover;
}

.card--menu-item__content {
  padding: var(--space-4);
  flex: 1;
}

.card--menu-item__title {
  font-size: var(--font-size-lg);
  font-weight: var(--font-weight-semibold);
  margin: 0 0 var(--space-2) 0;
}

.card--menu-item__price {
  font-size: var(--font-size-xl);
  font-weight: var(--font-weight-bold);
  color: var(--color-primary);
  margin: var(--space-3) 0 0 0;
}
```

### **7.3 Form Component System**
```css
.form-group {
  margin-bottom: var(--space-6);
}

.form-label {
  display: block;
  font-weight: var(--font-weight-medium);
  color: var(--color-text-primary);
  margin-bottom: var(--space-2);
}

.form-input {
  width: 100%;
  padding: var(--space-3) var(--space-4);
  border: 2px solid var(--color-border);
  border-radius: var(--radius-md);
  font-size: var(--font-size-base);
  transition: all 0.2s ease;
  min-height: 44px; /* Touch target */
}

.form-input:focus {
  outline: none;
  border-color: var(--color-primary);
  box-shadow: 0 0 0 3px rgba(180, 83, 9, 0.1);
}

.form-input--error {
  border-color: var(--color-error);
}

.form-error {
  color: var(--color-error);
  font-size: var(--font-size-sm);
  margin-top: var(--space-2);
}

.form-textarea {
  resize: vertical;
  min-height: 120px;
}

.form-select {
  background-image: url("data:image/svg+xml;charset=utf-8,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3E%3Cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3E%3C/svg%3E");
  background-position: right var(--space-3) center;
  background-repeat: no-repeat;
  background-size: 1.5em 1.5em;
  padding-right: var(--space-10);
  appearance: none;
}
```

---

## 📱 **8. MICRO-INTERACTIONS ET ANIMATIONS**

### **8.1 Loading States**
```css
@keyframes shimmer {
  0% {
    background-position: -1000px 0;
  }
  100% {
    background-position: 1000px 0;
  }
}

.skeleton {
  background: linear-gradient(
    90deg,
    var(--color-bg-tertiary) 25%,
    var(--color-bg-secondary) 50%,
    var(--color-bg-tertiary) 75%
  );
  background-size: 1000px 100%;
  animation: shimmer 2s infinite;
}

.skeleton--text {
  height: 1rem;
  border-radius: var(--radius-sm);
  margin-bottom: var(--space-2);
}

.skeleton--image {
  height: 200px;
  border-radius: var(--radius-md);
}
```

### **8.2 Success/Error Feedback**
```css
@keyframes slideInUp {
  from {
    transform: translateY(100%);
    opacity: 0;
  }
  to {
    transform: translateY(0);
    opacity: 1;
  }
}

.toast {
  position: fixed;
  bottom: var(--space-6);
  left: var(--space-4);
  right: var(--space-4);
  padding: var(--space-4);
  border-radius: var(--radius-md);
  color: white;
  font-weight: var(--font-weight-medium);
  animation: slideInUp 0.3s ease;
  z-index: 1000;
}

.toast--success {
  background-color: var(--color-success);
}

.toast--error {
  background-color: var(--color-error);
}

.toast--info {
  background-color: var(--color-info);
}
```

### **8.3 Hover Effects**
```css
.menu-item {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.menu-item:hover {
  transform: translateY(-4px);
  box-shadow: var(--shadow-xl);
}

.menu-item:hover .menu-item__image {
  transform: scale(1.05);
}

.menu-item__image {
  transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
```

---

## 🎯 **9. ACCESSIBILITÉ WCAG 2.1 AA**

### **9.1 Focus Management**
```css
/* Focus visible for keyboard navigation */
.focus-visible {
  outline: 2px solid var(--color-primary);
  outline-offset: 2px;
}

/* Skip links */
.skip-link {
  position: absolute;
  top: -40px;
  left: 6px;
  background: var(--color-primary);
  color: white;
  padding: 8px;
  text-decoration: none;
  border-radius: var(--radius-md);
  z-index: 1000;
}

.skip-link:focus {
  top: 6px;
}
```

### **9.2 Screen Reader Support**
```html
<!-- Semantic HTML5 structure -->
<main role="main">
  <section aria-labelledby="menu-heading">
    <h2 id="menu-heading">Notre Carte</h2>
    
    <article class="menu-item" aria-label="Pizza Margherita - 14€">
      <img src="pizza.jpg" alt="Pizza Margherita avec basilic frais" 
           aria-describedby="pizza-description">
      <div id="pizza-description">
        <h3>Pizza Margherita</h3>
        <p>Mozzarella di bufala, basilic frais, sauce tomate maison</p>
        <p aria-label="Prix: 14 euros">14€</p>
        <div aria-label="Allergènes: Contient du gluten et du lactose">
          <span class="allergen" aria-label="Contient du gluten">🌾</span>
          <span class="allergen" aria-label="Contient du lactose">🥛</span>
        </div>
      </div>
    </article>
  </section>
</main>
```

---

## 📊 **10. PERFORMANCE VISUELLE**

### **10.1 Image Optimization Strategy**
```css
/* Responsive images with srcset */
.responsive-image {
  width: 100%;
  height: auto;
  object-fit: cover;
  loading: lazy;
}

/* Art direction for different breakpoints */
@media (max-width: 767px) {
  .hero-image {
    height: 200px;
    object-position: center;
  }
}

@media (min-width: 768px) {
  .hero-image {
    height: 400px;
    object-position: top;
  }
}
```

### **10.2 Critical CSS Inlining**
```html
<style>
/* Critical above-the-fold CSS inlined */
.header, .hero, .navigation { /* Critical styles */ }
</style>

<link rel="preload" href="non-critical.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
```

---

## 🎯 **CONCLUSION**

Ce système de design et structure responsive garantit :

✅ **Experience utilisateur exceptionnelle** sur tous appareils  
✅ **Performance optimale** avec chargement rapide  
✅ **Accessibilité universelle** WCAG 2.1 AA compliant  
✅ **Personnalisation avancée** pour chaque restaurant  
✅ **Scalabilité visuelle** avec composants réutilisables  
✅ **Maintenance facilitée** avec design system cohérent  

**MenuMiam V2 offrira une expérience moderne et intuitive** tant pour les restaurateurs que pour leurs clients.

---
*Prochaine étape : Modélisation base de données (MERISE)* 🗄️
