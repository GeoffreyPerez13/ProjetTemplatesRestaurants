TemplatesRestaurants - Système d'administration de carte pour restaurants
Description
Ce projet est une application web complète permettant aux restaurateurs de gérer et présenter leur carte en ligne avec deux modes de fonctionnement distincts : un mode éditable pour créer et organiser catégories/plats, et un mode images pour afficher des cartes scannées ou conçues.

Le système inclut un panneau d'administration sécurisé, une base de données MySQL, et une interface responsive adaptée à tous les appareils. Il est conçu pour fonctionner avec WampServer ou XAMPP sur Windows.

Étapes effectuées et explications
1️⃣ Migration vers une architecture MVC-like
Remplacement de l'ancienne structure par un système de routage centralisé via index.php

Création d'un dossier pages/ contenant toutes les pages de l'application

Implémentation d'un contrôleur frontal gérant les accès sécurisés

Pourquoi :
Permet une meilleure organisation du code, une sécurité renforcée et une maintenance facilitée.

2️⃣ Système de gestion de contenu avancé
Base de données MySQL avec 5 tables principales :

users : Gestion des administrateurs

categories : Catégories de la carte

dishes : Plats avec prix et descriptions

carte_images : Images/PDF du mode images

mode : Configuration du mode d'affichage

Deux modes d'opération :

Mode Éditable : Interface drag & drop pour organiser catégories et plats

Mode Images : Galerie d'images/PDF uploadés

Pourquoi :
Offre plus de flexibilité que le système JSON précédent, avec de meilleures performances et une gestion plus robuste.

3️⃣ Panneau d'administration complet
Interface organisée en sections :

Dashboard : Vue d'ensemble et changement de mode

Édition de carte : Gestion complète catégories/plats avec upload d'images

Aperçu de carte : Visualisation selon le mode sélectionné

Gestion des comptes : Connexion, inscription, réinitialisation de mot de passe

Système d'accordéons pour une navigation intuitive

Lightbox intégrée pour visualiser les images en grand

Pourquoi :
Fournit une expérience utilisateur professionnelle et intuitive pour les restaurateurs.

4️⃣ Architecture CSS modulaire
Organisation en dossiers thématiques :

basis/ : Styles de base, boutons, composants

effects/ : Animations, lightbox, accordéons

forms/ : Styles de formulaires spécifiques

sections/ : CSS par page/section

Fichier principal admin.css qui importe tous les modules

Pourquoi :
Facilite la maintenance, permet la réutilisation de composants et améliore la performance.

5️⃣ Configuration des environnements de développement
Le projet supporte deux configurations d'URL selon l'environnement :

🏢 Au travail (port 80 standard) :
text
http://templatesrestaurants.local/admin/login.php
http://localhost/phpmyadmin
🏠 À la maison (port 8080) :
text
http://templatesrestaurants.local:8080/?page=login
http://localhost:8080/phpmyadmin
Pourquoi cette différence :

À la maison, le port 80 est souvent utilisé par d'autres services (Skype, IIS)

Apache est configuré pour écouter sur le port 8080

La structure d'URL a évolué vers un système de routage

Fichiers modifiés pour cette configuration :

hosts (C:\Windows\System32\drivers\etc\hosts) :

text
127.0.0.1    templatesrestaurants.local
127.0.0.1    phpmyadmin.local
httpd-vhosts.conf (Apache/conf/extra/) :

apache
<VirtualHost *:80>  <!-- Au travail -->
<VirtualHost *:8080> <!-- À la maison -->
  ServerName templatesrestaurants.local
  DocumentRoot "C:/xampp/htdocs/templates-restaurants"
  <Directory "C:/xampp/htdocs/templates-restaurants">
      Options Indexes FollowSymLinks
      AllowOverride All
      Require all granted
  </Directory>
</VirtualHost>
httpd.conf (Apache/conf/) :

apache
# Au travail :
Listen 80
ServerName localhost:80

# À la maison :
Listen 8080
ServerName localhost:8080
6️⃣ Sécurité renforcée
Hachage des mots de passe avec password_hash()

Protection XSS : htmlspecialchars() sur toutes les sorties

Requêtes préparées pour prévenir les injections SQL

Validation des uploads : types MIME et tailles limitées

Sessions sécurisées avec régénération d'ID

Protection des dossiers sensibles via .htaccess

Pourquoi :
Assure la sécurité des données des restaurateurs et de leurs clients.

7️⃣ Système de fichiers organisé
text
templates-restaurants/
├── assets/
│   ├── css/admin/          # CSS modulaire par fonctionnalité
│   ├── js/effects/         # Scripts généraux
│   └── uploads/            # Images uploadées (catégories, plats, carte)
├── database/               # Structure SQL et données
├── partials/               # En-tête et pied de page réutilisables
├── pages/                  # Toutes les pages de l'application
├── index.php               # Routeur principal
├── config.php              # Configuration et fonctions utilitaires
└── .htaccess               # Règles Apache
Pourquoi :
Structure claire qui sépare les responsabilités et facilite l'évolution du projet.

8️⃣ Fonctionnalités techniques avancées
Drag & Drop : Réorganisation intuitive des catégories et plats

Lightbox personnalisée : Visualisation plein écran des images

Upload sécurisé : Support JPG, PNG, GIF, WebP, PDF (max 5MB)

Responsive design : Adapté mobile, tablette et desktop

Accordéons interactifs : Pour les sections dépliables

Notifications : Messages de succès/erreur en temps réel

Pourquoi :
Crée une expérience utilisateur moderne et professionnelle.

Test en local
Au travail (port 80) :
text
http://templatesrestaurants.local/
http://templatesrestaurants.local/admin/login.php
À la maison (port 8080) :
text
http://templatesrestaurants.local:8080/
http://templatesrestaurants.local:8080/?page=login
Vérifications :

Toutes les sections s'affichent correctement

Le mode éditable permet de créer/modifier catégories et plats

Le mode images affiche correctement les fichiers uploadés

La lightbox fonctionne sur toutes les images

L'interface est responsive sur tous les appareils

Résultat final
Application web complète pour la gestion de carte de restaurant

Deux modes d'opération adaptés à différents besoins

Interface administrateur intuitive et sécurisée

Architecture modulaire facile à maintenir et étendre

Configuration multi-environnement pour développement flexible

Sécurité renforcée à tous les niveaux

Démarrage rapide
Prérequis :
PHP 7.4+

MySQL 5.7+

WampServer ou XAMPP

Installation :
Cloner le projet dans le dossier htdocs de WampServer/XAMPP

Importer la base de données : database/database.sql

Configurer config.php avec vos identifiants MySQL

Configurer les Virtual Hosts selon votre environnement

Vérifier les permissions du dossier assets/uploads/

Redémarrer Apache et accéder à l'URL configurée

Configuration des URLs :
Si le port 80 est libre, utiliser la configuration standard

Si le port 80 est occupé, modifier Apache pour utiliser le port 8080

Adapter les URLs dans le navigateur en conséquence

Notes importantes
Migration réussie d'un système JSON simple vers une base de données relationnelle

Évolution de l'architecture vers un pattern MVC-like pour une meilleure maintenabilité

Configuration flexible supportant différents environnements de développement

Code documenté en français avec une structure claire

Sécurité prioritaire à chaque étape du développement

Le projet est maintenant prêt pour une utilisation professionnelle ou pour des évolutions futures (export PDF, multi-langue, API, etc.).
