# 🍽️ MenuMiam V2 - Application SaaS de Gestion de Carte Digitale
## Refonte Complète - Architecture MVC Moderne

**Version** : 2.0 (En développement)  
**Statut** : Refonte from scratch basée sur conception CDA complète  
**Branche** : V2-refonte

---

## 🎯 **À Propos**

MenuMiam V2 est une **refonte complète** de l'application, développée selon les standards **CDA (Concepteur Développeur d'Application) Niveau 6**.

Cette version est construite **from scratch** en suivant une conception professionnelle complète incluant :
- ✅ Architecture MVC robuste
- ✅ Approche Mobile First & Responsive
- ✅ Optimisation tablette (iPad, Galaxy Tab)
- ✅ Sécurité renforcée (CSRF, XSS, SQLi)
- ✅ Performance optimisée (< 2s)
- ✅ Base de données normalisée (3NF)

---

## 📚 **Documentation Complète**

Toute la conception est disponible dans `_dev/cda/conception/` :

### **Documents Essentiels**
- 📊 [`RESUME_EXECUTIF_V2.md`](_dev/cda/conception/RESUME_EXECUTIF_V2.md) - Vue d'ensemble complète
- 📋 [`CAHIER_DES_CHARGES.md`](_dev/cda/conception/01-analyse-besoins/CAHIER_DES_CHARGES.md) - Spécifications
- 📱 [`MOBILE_FIRST_RESPONSIVE.md`](_dev/cda/conception/01-analyse-besoins/MOBILE_FIRST_RESPONSIVE.md) - Guide responsive
- 🗄️ [`BDD_COMPLETE_V2.md`](_dev/cda/conception/04-modelisation-bdd/BDD_COMPLETE_V2.md) - Modélisation BDD
- 📱 [`OPTIMISATION_TABLETTE.md`](_dev/cda/conception/01-analyse-besoins/OPTIMISATION_TABLETTE.md) - Optimisation tablette
- 📚 [`INDEX_DOCUMENTATION.md`](_dev/cda/conception/INDEX_DOCUMENTATION.md) - Guide navigation

---

## 🏗️ **Architecture V2**

### **Stack Technique**

**Frontend** :
- HTML5 sémantique
- CSS3 (Variables CSS, Grid, Flexbox)
- JavaScript ES6+ (Vanilla JS)
- Mobile First (320px → 1440px+)

**Backend** :
- PHP 8.0+ (MVC custom)
- MySQL 8.0+ (InnoDB)
- Apache 2.4+
- Composer (autoloading PSR-4)

**Sécurité** :
- Protection CSRF
- Validation stricte
- Hashage bcrypt
- Requêtes préparées

### **Structure du Projet**

```
ProjetTemplatesRestaurants/
├── _dev/                    # Conception CDA complète ✅
│   └── cda/conception/
│
├── app/                     # Backend MVC (à créer)
│   ├── Controllers/
│   ├── Models/
│   ├── Views/
│   ├── Core/
│   └── Helpers/
│
├── public/                  # Point d'entrée web (à créer)
│   ├── index.php
│   ├── assets/
│   └── uploads/
│
├── config/                  # Configuration (à créer)
├── database/                # SQL (à créer)
├── tests/                   # Tests (à créer)
├── vendor/                  # Dépendances Composer
├── .gitignore
├── composer.json
└── README.md
```

---

## 🚀 **Fonctionnalités Prévues**

### **Module Core (Gratuit)**
- Gestion de carte (catégories, plats, allergènes)
- 3 Templates (Classic, Modern, Elegant)
- Personnalisation (logo, bannière, couleurs)
- Contact & Services
- Interface admin responsive

### **Modules Premium**
- **Réservations** (+8€/mois) : Système complet + Floor Plan
- **Statistiques** (+5€/mois) : Analytics avec graphiques
- **Avis Google** (+5€/mois) : Intégration API Places
- **Livraison** (+7€/mois) : Uber Eats, Deliveroo, Just Eat

---

## 📋 **Prérequis**

- PHP 8.0+
- MySQL 8.0+
- Apache 2.4+ (avec mod_rewrite)
- Composer
- WAMP/XAMPP (développement)

---

## 🛠️ **Installation (V2 en développement)**

### **1. Cloner le Repository**

```bash
git clone https://github.com/GeoffreyPerez13/ProjetTemplatesRestaurants.git
cd ProjetTemplatesRestaurants
```

### **2. Basculer sur la Branche V2**

```bash
git checkout V2-refonte
```

### **3. Installer les Dépendances**

```bash
composer install
```

### **4. Configuration Base de Données**

```bash
# Créer la base de données
mysql -u root -p
CREATE DATABASE menumiam_v2 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;

# Importer le schéma (à venir)
mysql -u root -p menumiam_v2 < database/schema.sql
```

### **5. Configuration**

```bash
# Copier le fichier de configuration
cp config/database.example.php config/database.php

# Éditer avec vos identifiants
nano config/database.php
```

### **6. Accès**

```
http://localhost/ProjetTemplatesRestaurants
```

---

## 🌿 **Branches Git**

- `main` : Production (V1 stable)
- `testRefonte` : Développement V1
- `V1Projet` : **Sauvegarde V1** (tag: v1.0-final) 📌
- `V2-refonte` : **Développement V2** (from scratch) 🚀

---

## 📊 **Roadmap V2**

### **Phase 1 : Core & Architecture** (Semaine 1-2)
- [ ] Structure MVC de base
- [ ] Classes Core (Database, Router, Request, Response)
- [ ] Autoloading PSR-4
- [ ] Sécurité de base (CSRF, validation)

### **Phase 2 : Authentification** (Semaine 2)
- [ ] Système login/logout
- [ ] Gestion sessions
- [ ] Rôles et permissions

### **Phase 3 : Modules Core** (Semaine 3-4)
- [ ] Dashboard
- [ ] Gestion de carte
- [ ] Contact & Services
- [ ] Templates

### **Phase 4 : Modules Premium** (Semaine 5-8)
- [ ] Réservations + Floor Plan
- [ ] Statistiques
- [ ] Avis Google
- [ ] Intégration Livraison

### **Phase 5 : Optimisations** (Semaine 9-10)
- [ ] Responsive complet (Mobile + Tablette)
- [ ] Performance
- [ ] Accessibilité
- [ ] Tests

---

## 🎓 **Projet CDA**

Ce projet est réalisé dans le cadre de la formation **Concepteur Développeur d'Application (CDA)** - Niveau 6 (RNCP 37273).

**Compétences validées** :
- Concevoir et développer des composants d'interface
- Concevoir et développer la persistance des données
- Développer une application multicouche répartie
- Préparer le déploiement d'une application

---

## 👨‍💻 **Auteur**

**Geoffrey Perez**  
Formation CDA - Concepteur Développeur d'Application  
GitHub: [@GeoffreyPerez13](https://github.com/GeoffreyPerez13)

---

## 📝 **Licence**

Propriétaire - Tous droits réservés

---

**Note** : Cette branche (`V2-refonte`) contient la refonte complète de MenuMiam. Pour accéder à la V1, basculez sur la branche `V1Projet`.
