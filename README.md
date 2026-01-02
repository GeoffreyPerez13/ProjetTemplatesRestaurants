# TemplatesRestaurants  
## Système d'administration de carte pour restaurants

## Description
TemplatesRestaurants est une application web complète permettant aux restaurateurs de gérer et présenter leur carte en ligne selon deux modes distincts :

- **Mode éditable** : création et organisation des catégories et plats
- **Mode images** : affichage de cartes scannées ou conçues (images/PDF)

Le système inclut un panneau d'administration sécurisé, une base de données MySQL et une interface responsive adaptée à tous les appareils.  
Il est conçu pour fonctionner avec **WampServer** ou **XAMPP** sous Windows.

---

## Étapes effectuées et explications

### 1. Migration vers une architecture MVC-like
- Remplacement de l'ancienne structure par un système de routage centralisé via `index.php`
- Création d'un dossier `pages/` contenant toutes les pages de l'application
- Implémentation d'un contrôleur frontal gérant les accès sécurisés

**Pourquoi :**  
Permet une meilleure organisation du code, une sécurité renforcée et une maintenance facilitée.

---

### 2. Système de gestion de contenu avancé
Base de données MySQL avec plusieurs tables principales :

- `admins` : gestion des administrateurs
- `categories` : catégories de la carte
- `plats` : plats avec prix et descriptions
- `card_images` : images/PDF du mode images
- `contact`, `logos`, `invitations`, `restaurants`

Deux modes d'opération :

- **Mode Éditable** : interface de gestion des catégories et plats
- **Mode Images** : galerie d'images/PDF uploadés

**Pourquoi :**  
Offre plus de flexibilité que le système JSON précédent, avec de meilleures performances et une gestion plus robuste.

---

### 3. Panneau d'administration complet
Interface organisée en sections :

- Dashboard : vue d'ensemble et changement de mode
- Édition de carte : gestion des catégories et plats avec upload d'images
- Aperçu de carte : visualisation selon le mode sélectionné
- Gestion des comptes : connexion, inscription, réinitialisation de mot de passe

Fonctionnalités supplémentaires :
- Système d'accordéons pour une navigation intuitive
- Lightbox intégrée pour l'affichage des images en grand

**Pourquoi :**  
Fournit une expérience utilisateur professionnelle et intuitive pour les restaurateurs.

---

### 4. Architecture CSS modulaire
Organisation des styles en dossiers thématiques :

- `basis/` : styles de base, boutons, composants
- `effects/` : animations, lightbox, accordéons
- `forms/` : styles spécifiques aux formulaires
- `sections/` : CSS par page ou section

Un fichier principal `admin.css` importe tous les modules.

**Pourquoi :**  
Facilite la maintenance, permet la réutilisation de composants et améliore la performance globale.

---

### 5. Configuration des environnements de développement
Le projet supporte deux configurations selon l'environnement.

#### Environnement travail (port 80)
```text
http://templatesrestaurants.local/admin/login.php
http://localhost/phpmyadmin
