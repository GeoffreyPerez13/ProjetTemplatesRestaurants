# 📋 ORGANISATION DES TÂCHES PAR CATÉGORIES ET PAGES
*Projet MenuMiam - Branch testRefonte*

---

## 🏗️ **STRUCTURE ET ARCHITECTURE**

### **CSS/JS Modulaire**
- **6cb4236** (2026-02-25) - Restructuration CSS/JS modulaire, dark mode, templates (5), SEO/sitemap, vitrine peaufinée, système de démo temporaire pour clients (tokens avec clones isolés, bandeau démo, restrictions, UI SUPER_ADMIN)
- **e99ef5d** (2025-12-02) - 2eme refonte des fichiers JS et CSS pour plus de clarté
- **d48a133** (2025-12-01) - Refonte des fichiers et dossiers css pour plus de clarté et une meilleure visibilité
- **2e84517** (2025-12-10) - Arborescence des fichiers css et js revues

### **Base de Données & Modèles**
- **b39f868** (2025-12-05) - Réécrire des classes et autres fichiers en anglais pour une meilleure uniformité. Modèle CardImage créé pour stocker les images, table identique en base de données
- **5684851** (2025-11-14) - Ajout de l'insertion d'image pour les catégories et les plats, ainsi qu'en base de données
- **00c707d** (2025-10-22) - Ajout des getter et setter pour toutes les classes

---

## 🎨 **TEMPLATES & DESIGN**

### **Templates System**
- **964f4e9** (2026-02-26) - Séparation palette/layout, accordéons, fix header logo, nouveaux templates bistro/ocean
- **6cb4236** (2026-02-25) - Templates (5), dark mode

### **Dark Mode**
- **6cb4236** (2026-02-25) - Dark mode intégré

---

## 🖥️ **PAGES ADMIN**

### **Dashboard**
- **949b84b** (2026-02-20) - La dernière modification de la carte s'affiche désormais dans le dashboard
- **b2ef8b2** (2026-02-04) - Dans le dashboard, ajout du bouton paramètre et de la page en question
- **cf592ce** (2026-02-05) - Ajustement de la taille des boutons dans le dashboard

### **Settings/Paramètres**
- **74d9b81** (2026-03-10) - Améliorations UI/UX : Accordéons settings et Cookies redesign
- **fc6e6ea** (2026-03-10) - Corrections visuelles settings
- **3194448** (2026-02-19) - Dans la page des paramètres, toutes les sections sont désormais enregistrables
- **13bf670** (2026-02-10) - Dans la page des paramètres, toutes les sections sont désormais enregistrables

#### **Settings - Options du compte**
- **cfa3e6c** (2026-03-06) - Ajout fermetures exceptionnelles et améliorations admin
- **1045bc5** (2026-03-02) - Accordéons ajoutés sur settings premium (Gérer abonnements + Options à la carte)

#### **Settings - Premium/Abonnements**
- **0d1d4b2** (2026-03-09) - Refactor Subscription Management UI and Premium Features - Final Update
- **7ac1eb2** (2026-03-09) - Refactor Subscription Management UI and Premium Features
- **98cc380** (2026-03-02) - Multi-select premium checkout + subscription management
- **1045bc5** (2026-03-02) - Gérer abonnements + Options à la carte, Prix affiché pour toutes les options premium

### **Edit-Cart (Carte du Restaurant)**
- **a6376be** (2026-03-03) - Refonte UI/UX edit-card - Quick-add, accordéons, allergènes et settings
- **adb2929** (2026-03-09) - Améliorations UI edit-card et styles allergènes
- **1c10f50** (2026-02-25) - Dans panel administrateur, section carte, ajout d'une sous section allergène dans création de plat d'une catégorie
- **b27ebde** (2026-02-09) - Page 'Modifier la carte' totalement fonctionnelle et revue, ainsi que le visuel des accordéons
- **5a42fed** (2026-01-20) - Mode éditable complètement fonctionnel, mode images en cours, mais téléchargement d'images et drag and drop fonctionnels
- **d0f7cb2** (2026-01-19) - Correction des fonctionnalités et des redirections pour l'ajout de catégorie/plats/images/etc... dans le mode éditable
- **7740458** (2025-12-10) - Système de drag and drop mis en place pour le cas d'ajout d'images. Visuel retravaillé, ajout de nouveaux boutons de type accordéons
- **b5621a1** (2025-11-13) - Mise à jour du formulaire de modification de la carte. Ajout de la description. Ajout d'une page d'aperçu de la carte

#### **Edit-Cart - Quick-Add**
- **a6376be** (2026-03-03) - Quick-add functionality

#### **Edit-Cart - Allergènes**
- **a6376be** (2026-03-03) - Allergènes management
- **adb2929** (2026-03-09) - Styles allergènes
- **1c10f50** (2026-02-25) - Ajout d'une sous section allergène dans création de plat

#### **Edit-Cart - Images**
- **7740458** (2025-12-10) - Drag and drop pour images, X pour supprimer une image téléchargée
- **110fbe9** (2025-12-09) - L'ajout d'images fonctionne
- **e99ef5d** (2025-12-02) - Options pour ajouter/modifier/supprimer des images pour les catégories et les plats, lightbox
- **68dfd30** (2026-02-03) - Bug de drag and drop dans l'édition de la carte (non résolu)

#### **Edit-Cart - Responsive**
- **e99ef5d** (2025-12-02) - Responsive design de la page edit-carte.php, au delà de 1024px le responsive ne fonctionne plus
- **1daa8f3** (2026-02-03) - Responsive design de cette page à revoir

### **Logo/Bannière**
- **3194448** (2026-02-19) - Modification de la page modifier le logo en modifier logo/bannière. De nouveaux accordéons ont été ajoutés
- **55d0d43** (2026-02-04) - Modification visuelle pour la modification du logo
- **68dfd30** (2026-02-03) - Formulaire de modification de logos mis à jour et fonctionnels

### **Register/Inscription**
- **13bf670** (2026-02-10) - Page de register revue, avec ajout de paramètres de sécurité pour la création du mot de passe

### **Login/Connexion**
- **55d0d43** (2026-02-04) - Ajout de l'affichage d'erreur de connexion sur la page de login
- **d48a133** (2025-12-01) - Ajout du visuel pour afficher ou masquer le mot de passe lors de la connexion

### **Password Reset**
- **3194448** (2026-02-19) - Réglages visuels et sécuritaires de la page de réinitialisation de mot de passe

---

## 🌐 **PAGES VITRINE (DISPLAY)**

### **Page Principale (Landing)**
- **ed2af57** (2026-02-21) - Site vitrine à jour mais encore à paufiner. Avertissement sur les cookies mis en place
- **267b64a** (2025-10-23) - Mise en place du site vitrine avec création du contrôleur et de la vue
- **110fbe9** (2025-12-09) - Nouvelle base de données récupérée

#### **Landing - Bannière**
- **ed2af57** (2026-02-21) - Ajout de champ de texte pour la bannière

#### **Landing - Services/Paiements/Réseaux**
- **ed2af57** (2026-02-21) - Ajout d'un nouveau formulaire : Services, paiements & réseaux
- **1c10f50** (2026-02-25) - Ajout du symbole pour livraison ubereat/Deliveroo, bouton 'tout cocher/decocher'
- **ed2af57** (2026-02-21) - Modification des éléments des services et moyens de paiement

#### **Landing - Contact**
- **b27ebde** (2026-02-09) - Le formulaire de contact a aussi été revu
- **68dfd30** (2026-02-03) - Formulaire de contact mis à jour et fonctionnels

#### **Landing - Footer**
- **b2ef8b2** (2026-02-04) - Ajout du footer complet avec CGV, confidentialité, Cookies
- **cf592ce** (2026-02-05) - Responsive du footer revu, notamment pour la version mobile

#### **Landing - Maintenance**
- **949b84b** (2026-02-20) - Si le site est en non actif, une page de maintenance sera affichée

#### **Landing - SEO/Optimisation**
- **6cb4236** (2026-02-25) - SEO/sitemap, vitrine peaufinée

### **Cookies**
- **74d9b81** (2026-03-10) - Cookies redesign
- **ed2af57** (2026-02-21) - Avertissement sur les cookies mis en place

### **Fermetures Exceptionnelles**
- **cfa3e6c** (2026-03-06) - Ajout fermetures exceptionnelles
- **fc6e6ea** (2026-03-10) - Bannière fermeture fixe

---

## 💰 **FONCTIONNALITÉS PREMIUM**

### **Abonnements & Stripe**
- **0d1d4b2** (2026-03-09) - Refactor Subscription Management UI and Premium Features - Final Update
- **7ac1eb2** (2026-03-09) - Refactor Subscription Management UI and Premium Features
- **98cc380** (2026-03-02) - Multi-select premium checkout + subscription management

### **Google Reviews**
- **cc620af** (2026-02-27) - Integrate Google Reviews as Premium feature with optimized UX

### **Options Premium**
- **1045bc5** (2026-03-02) - Prix affiché pour toutes les options premium

---

## 🎯 **FONCTIONNALITÉS SPÉCIALES**

### **Guided Tour System**
- **22c2fb0** (2026-03-04) - Implement guided tour system with adaptive paths

### **Système de Démo**
- **6cb4236** (2026-02-25) - Système de démo temporaire pour clients (tokens avec clones isolés, bandeau démo, restrictions, UI SUPER_ADMIN)

### **Fermetures Exceptionnelles**
- **cfa3e6c** (2026-03-06) - Ajout fermetures exceptionnelles et améliorations admin
- **fc6e6ea** (2026-03-10) - Bannière fermeture fixe

### **Notifications Email**
- **1c10f50** (2026-02-25) - mail_reminder et email_notifications fonctionnent, tâche cron avec planificateur Windows

---

## 🎨 **UI/UX & DESIGN**

### **Accordéons**
- **964f4e9** (2026-02-26) - Accordéons
- **a6376be** (2026-03-03) - Accordéons settings
- **1045bc5** (2026-03-02) - Accordéons ajoutés sur settings premium, correction rotation chevrons
- **3194448** (2026-02-19) - De nouveaux accordéons ont été ajoutés
- **b27ebde** (2026-02-09) - Visuel des accordéons
- **7740458** (2025-12-10) - Nouveaux boutons de type accordéons
- **e99ef5d** (2025-12-02) - Menu accordéon mis en place

### **Responsive Design**
- **cf592ce** (2026-02-05) - Responsive du footer revu, menus déroulants pour mobile
- **b2ef8b2** (2026-02-04) - Responsive design pour la version mobile revu
- **1daa8f3** (2026-02-03) - Responsive design à revoir
- **e99ef5d** (2025-12-02) - Responsive design de la page edit-carte.php, problème >1024px

### **Animations & Interactions**
- **1045bc5** (2026-03-02) - Pop-ups natives remplacées par SweetAlert2 dans premium.js

### **Couleurs & Visuel**
- **55d0d43** (2026-02-04) - Modification des couleurs des titres
- **13bf670** (2026-02-10) - Correction visuelle de certains boutons (suppression des couleurs rouges, sauf pour les boutons de suppression)
- **d82682b** (2025-11-05) - Premières corrections visuelles
- **6263ff5** (2026-02-03) - Corrections visuelles supplémentaires
- **1daa8f3** (2026-02-03) - Corrections visuelles pour les deux modes d'affichages

---

## 📱 **MOBILE & RESPONSIVE**

### **Footer Mobile**
- **cf592ce** (2026-02-05) - Responsive du footer revu pour mobile
- **b2ef8b2** (2026-02-04) - Responsive design mobile revu

### **Dashboard Mobile**
- **cf592ce** (2026-02-05) - Créé des menus déroulants pour le dashboard (mobile seulement)

### **Settings Mobile**
- **cf592ce** (2026-02-05) - Créé des menus déroulants pour les paramètres (mobile seulement)

### **Scroll Mobile**
- **cf592ce** (2026-02-05) - Ajout des flèches de scroll automatiques sur toutes les pages (sauf dashboard)

---

## 🔧 **DÉVELOPPEMENT & OUTILS**

### **Git & Documentation**
- **4638d5d** (2026-01-02) - Update README
- **99fcd0e** (2026-01-02) - Revise README for environment and security updates
- **ddeacab** (2026-01-02) - Update README.md
- **e245263** (2026-01-02) - Revise README for project overview and setup
- **e35c7eb** (2026-01-02) - Revise README for project overview and details
- **4c7d335** (2026-02-09) - Update README.md
- **8945162** (2025-10-22) - Complétion du gitignore

### **Mail & Notifications**
- **b27ebde** (2026-02-09) - MailHog installé pour tester l'envoi d'e-mail virtuel
- **dc211f9** (2025-10-23) - Ajout d'envoi d'un lien de création de compte

### **Sécurité**
- **13bf670** (2026-02-10) - Ajout de paramètres de sécurité pour la création du mot de passe
- **3194448** (2026-02-19) - Réglages sécuritaires de la page de réinitialisation de mot de passe

---

## 🐛 **PROBLÈMES IDENTIFIÉS**

### **Non Résolus**
_(Aucun problème en attente)_

### **Résolus**
- **1daa8f3** (2026-02-03) - Corrections visuelles pour les deux modes d'affichages - RÉSOLU
- **6263ff5** (2026-02-03) - Corrections visuelles supplémentaires - RÉSOLU
- **68dfd30** (2026-02-03) - Bug de drag and drop dans l'édition de la carte - RÉSOLU (double appel saveNewOrder corrigé, ordre initial peuplé, console.log supprimé)
- **e99ef5d** (2025-12-02) - Responsive design de la page edit-carte.php au delà de 1024px - RÉSOLU (supprimé max-width:80% sur categories-grid, ajouté breakpoint 1024px)
- **b39f868** (2025-12-05) - Le problème de responsive est toujours présent - RÉSOLU (même correctif que e99ef5d)
- **b27ebde** (2026-02-09) - Création d'un compte ne fonctionne plus après redirection email - RÉSOLU (URL invitation corrigée via getBaseUrl(), logging ajouté dans verifyEmail())

---

## 📊 **STATISTIQUES DÉVELOPPEMENT**

### **Par Mois**
- **Octobre 2025**: 5 commits (Initialisation)
- **Novembre 2025**: 4 commits (Fonctionnalités de base)
- **Décembre 2025**: 8 commits (Images & Refonte)
- **Janvier 2026**: 6 commits (Tests & Mode éditable)
- **Février 2026**: 15 commits (Refonte intégrale)
- **Mars 2026**: 14 commits (Fonctionnalités avancées)

### **Par Catégorie**
- **Pages Admin**: 20+ commits
- **Pages Vitrine**: 10+ commits
- **UI/UX**: 15+ commits
- **Fonctionnalités Premium**: 5+ commits
- **Architecture**: 5+ commits

---

## 🎯 **PROCHAINES ÉTAPES SUGGÉRÉES**

### **Priorité Haute**
1. **Corriger le drag and drop** dans edit-card (68dfd30)
2. **Fixer le responsive >1024px** (e99ef5d)
3. **Résoudre la création de compte** après e-mail (b27ebde)

### **Priorité Moyenne**
1. **Optimiser le responsive** général
2. **Finaliser les templates** bistro/ocean
3. **Améliorer les performances** de chargement

### **Priorité Basse**
1. **Nettoyer le code** et les commentaires
2. **Ajouter plus de tests** unitaires
3. **Documenter les API** internes

---

*Document généré le 12 mars 2026 - 52 commits analysés*
