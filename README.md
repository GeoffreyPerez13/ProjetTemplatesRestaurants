# TemplatesRestaurants  
## Système d'administration de carte pour restaurants

## Description
TemplatesRestaurants est une application web complète permettant aux restaurateurs de gérer et présenter leur carte en ligne selon deux modes distincts :

- **Mode éditable** : création et organisation des catégories et plats, avec gestion des allergènes par plat.
- **Mode images** : affichage de cartes scannées ou conçues (images/PDF).

Le système inclut un panneau d'administration sécurisé, une base de données MySQL et une interface responsive adaptée à tous les appareils.  
Il est conçu pour fonctionner avec **WampServer** ou **XAMPP** sous Windows.

---

## Étapes effectuées et explications

### 1. Migration vers une architecture MVC-like
- Système de routage centralisé via `index.php`
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
- `allergenes` : liste des allergènes disponibles
- `plat_allergenes` : liaison entre plats et allergènes
- `card_images` : images/PDF du mode images
- `contact`, `logos`, `invitations`, `restaurants`
- `admin_options` : paramètres utilisateur (site en ligne, rappels, notifications)

Deux modes d'opération :

- **Mode Éditable** : interface de gestion des catégories et plats
- **Mode Images** : galerie d'images/PDF uploadés

**Pourquoi :**  
Offre plus de flexibilité, avec de meilleures performances et une gestion plus robuste. L'ajout des allergènes répond aux exigences réglementaires et améliore l'expérience client.

---

### 3. Panneau d'administration complet
Interface organisée en sections :

- Dashboard : vue d'ensemble et changement de mode
- Édition de carte : gestion des catégories et plats avec upload d'images et sélection d'allergènes
- Aperçu de carte : visualisation selon le mode sélectionné
- Gestion des contacts et horaires
- Personnalisation du logo et de la bannière
- Gestion des services, paiements et réseaux sociaux
- Paramètres utilisateur (profil, mot de passe, options)

Fonctionnalités supplémentaires :
- Système d'accordéons pour une navigation intuitive
- Lightbox intégrée pour l'affichage des images en grand
- Boutons "Tout (dé)cocher" pour faciliter la sélection multiple d'allergènes

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
```
#### Environnement domicile (port 8080)
```text
http://templatesrestaurants.local/admin/login.php
http://localhost/phpmyadmin
```

**Pourquoi cette différence :**  
- Le port 80 est souvent occupé à domicile (IIS, Skype, autres services)
- Apache est configuré pour écouter sur le port 8080
- La structure d'URL a évolué vers un système de routage centralisé

#### Fichiers de configuration modifiés
#### Fichier hosts
```text
127.0.0.1    templatesrestaurants.local
127.0.0.1    phpmyadmin.local
```
#### Virtual Hosts Apache
```apache
<VirtualHost *:80>
  ServerName templatesrestaurants.local
  DocumentRoot "C:/xampp/htdocs/templates-restaurants"
  <Directory "C:/xampp/htdocs/templates-restaurants">
      Options Indexes FollowSymLinks
      AllowOverride All
      Require all granted
  </Directory>
</VirtualHost>

<VirtualHost *:8080>
  ServerName templatesrestaurants.local
  DocumentRoot "C:/xampp/htdocs/templates-restaurants"
  <Directory "C:/xampp/htdocs/templates-restaurants">
      Options Indexes FollowSymLinks
      AllowOverride All
      Require all granted
  </Directory>
</VirtualHost>
```
#### Configuration Apache
```apache
# Environnement de travail
Listen 80
ServerName localhost:80

# Environnement domicile
Listen 8080
ServerName localhost:8080
```
#### Système de mail box virtuelle avec MailHog
- Création d'un fichier `mailhog.bat` à la racine du projet pour lancer Mailhog.
- Exécutable avec la commande ```.\mailhog.bat``` depuis la racine du projet ```PS C:\wamp64\www\ProjetTemplatesRestaurants>```. Fermer l'onglet du navigateur arrêtera le service.
- MailHog est accessible sur http://localhost:8025 (interface web) et le serveur SMTP sur localhost:1025.
- Fermer l'onglet du navigateur n'arrête pas le service MailHog ; il faut fermer la fenêtre MailHog.
#### Simulation des tâches cron en développement
Pour tester l'envoi automatique des rappels mensuels (option `mail_reminder`), exécutez la commande suivante depuis la racine du projet :

---

### 6. Sécurité renforcée
- Hachage des mots de passe avec `password_hash()`
- Protection XSS avec `htmlspecialchars()` sur toutes les sorties
- Requêtes préparées pour prévenir les injections SQL
- Validation des uploads (types MIME et tailles limitées)
- Sessions sécurisées avec régénération d'identifiant
- Protection des dossiers sensibles via `.htaccess`

**Pourquoi :**
Assure la sécurité des données des restaurateurs et de leurs contenus.

---

### 7. Organisation du système des fichiers
```text
templates-restaurants/
├── assets/
│   ├── css/admin/
│   ├── js/effects/
│   └── uploads/
├── database/
├── partials/
├── pages/
├── index.php
├── config.php
└── .htaccess
```

**Pourquoi :**
Structure claire séparant les responsabilités et facilitant l'évolution du projet.

---

### 8. Fonctionnalités techniques avancées
- Réorganisation intuitive des catégories et plats
- Lightbox personnalisée pour l'affichage des images
- Upload sécurisé (JPG, PNG, GIF, WebP, PDF – 5 MB maximum)
- Design responsive (mobile, tablette et desktop)
- Accordéons interactifs
- Notifications de succès et d'erreur en temps réel

**Pourquoi :**
Offre une expérience utilisateur moderne et professionnelle.

---

### Tests en local
#### Environnement travail
```text
[http://templatesrestaurants.local/admin/login.php
http://localhost/phpmyadmin](http://templatesrestaurants.local/
http://templatesrestaurants.local/admin/login.php
)
```
#### Environnement domicile
```text
[http://templatesrestaurants.local/admin/login.php
http://localhost/phpmyadmin](http://templatesrestaurants.local:8080/
http://templatesrestaurants.local:8080/?page=login
)
```

Vérifications effectuées :
- Toutes les sections s'affichent correctement
- Le mode éditable fonctionne
- Le mode images affiche correctement les fichiers uploadés
- La lightbox fonctionne
- L'interface est totalement responsive

---

### Résultat final
- Application web complète pour la gestion de cartes de restaurant
- Deux modes d'utilisation adaptés à différents besoins
- Interface administrateur intuitive et sécurisée
- Architecture modulaire facile à maintenir et à faire évoluer
- Configuration multi-environnements flexible
- Sécurité renforcée à tous les niveaux

---

### Démarrage rapide
#### Prérequis
- PHP 7.4 ou supérieur
- MySQL 5.7 ou supérieur
- WampServer ou XAMPP
#### Installation
- Cloner le projet dans le dossier htdocs
- Importer la base de données depuis database/database.sql
- Configurer config.php avec vos identifiants MySQL
- Configurer les Virtual Hosts selon votre environnement
- Vérifier les permissions du dossier assets/uploads/
- Redémarrer Apache
- Accéder à l'URL configurée
#### Configuration des URLS
- Utiliser le port 80 si disponible
- Sinon, configurer Apache sur le port 8080
- Adapter les URLs dans le navigateur en conséquence

---

### Notes importantes
- Migration réussie d'un système JSON vers une base de données relationnelle
- Architecture MVC-like améliorant la maintenabilité
- Configuration flexible supportant plusieurs environnements
- Code documenté en français avec une structure claire
- Sécurité prioritaire à chaque étape du développement
- Projet prêt pour une utilisation professionnelle ou des évolutions futures (export PDF, multi-langue, API, etc.)
