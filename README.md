# TemplatesRestaurants - Site vitrine dynamique pour restaurants

## Description

Ce projet permet de créer un site vitrine pour un restaurant avec un design simple, responsive et administrable.  
Le contenu du site (nom du restaurant, carte, contact, horaires…) est dynamique et stocké dans un fichier JSON, modifiable via un panneau administrateur.

Le projet est conçu pour être utilisé avec **WampServer** sur Windows.

---

## Étapes effectuées et explications

### 1️⃣ Création du site dynamique

- Remplacement du HTML statique par un `index.php` dynamique.  
- Les informations du restaurant sont chargées depuis un fichier JSON (`data/content.json`) grâce à la fonction `loadContent()` de `config.php`.  
- Les sections principales :  
  - Header / menu horizontal  
  - Accueil (nom, accroche, bouton “Découvrir la carte”)  
  - La carte (Entrées, Plats, Desserts avec images et listes)  
  - Contact (Localisation Google Maps, contact, horaires)  
  - Footer (mentions légales, CGV, réseaux sociaux)  

**Pourquoi :**  
Permet au site d’être modifiable par le restaurateur sans toucher au code.

---

### 2️⃣ Création du panneau administrateur

- Dossier `admin/` avec login et pages pour modifier :  
  - Carte du restaurant  
  - Informations de contact  
  - Logo et images éventuelles  

**Pourquoi :**  
Pour que le restaurateur puisse mettre à jour son site facilement.

---

### 3️⃣ Gestion du contenu dynamique

- `config.php` contient :  
  - `loadContent()` : charge le JSON et retourne un contenu par défaut si le fichier est vide ou corrompu.  
  - `saveContent()` : sauvegarde le contenu modifié par l’admin dans le JSON.  
  - `getDefaultContent()` : valeurs par défaut pour éviter qu’une erreur PHP bloque le site.  

**Pourquoi :**  
Assure la robustesse du site même si le JSON est absent ou mal formé.

---

### 4️⃣ Sécurisation des données

Fichier `.htaccess` dans `data/` :

```apache
<FilesMatch "\.(json|txt)$">
    Order allow,deny
    Deny from all
</FilesMatch>

Options -Indexes
```

---

5️⃣ Configuration du VirtualHost dans WampServer

Ajout du VirtualHost templatesrestaurants.local :

```apache
<VirtualHost *:80>
    ServerName templatesrestaurants.local
    DocumentRoot "C:/Users/galaxy/TemplatesRestaurants"
    <Directory "C:/Users/galaxy/TemplatesRestaurants/">
        Options +Indexes +Includes +FollowSymLinks +MultiViews
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

Ajout de la ligne dans le fichier hosts :

127.0.0.1   templatesrestaurants.local

Pourquoi :
Permet de lancer le site depuis n’importe quel dossier Windows, sans le déplacer dans www, et de créer une URL locale propre.

---

6️⃣ Adaptation des chemins

Tous les chemins CSS, JS, assets et JSON pointent directement à la racine du projet.

Exemple :

```html
<link rel="stylesheet" href="style.css">
<img src="assets/logo.png">
```

Pourquoi :
Permet au site de fonctionner directement sans sous-dossiers supplémentaires.

---

7️⃣ Test en localhost

Redémarrer WampServer.

Accéder à :

http://templatesrestaurants.local/
http://templatesrestaurants.local/admin/login.php


Vérifier que toutes les sections s’affichent correctement et que le contenu modifiable par admin se reflète immédiatement.

Résultat final

Site dynamique et administrable

Contenu sécurisé et sauvegardé dans JSON

VirtualHost configuré pour fonctionner depuis n’importe quel dossier

Possibilité d’ajouter plusieurs templates ultérieurement

Démarrage rapide

Vérifier que WampServer est installé et démarré.

Placer le dossier TemplatesRestaurants à l’emplacement choisi (ici C:/Users/galaxy/TemplatesRestaurants).

Vérifier que le VirtualHost templatesrestaurants.local est configuré et que le fichier hosts contient :

127.0.0.1   templatesrestaurants.local


Redémarrer WampServer.

Ouvrir dans le navigateur :

http://templatesrestaurants.local/


Accéder au panneau administrateur :

http://templatesrestaurants.local/admin/login.php


Modifier la carte, le contact ou le logo depuis l’admin pour voir les changements en direct.

Notes

Le projet est conçu pour PHP 8.x et WampServer 3.3.7 (64-bit).

Les fichiers CSS, JS et images doivent rester à la racine et dans le dossier assets/ pour que le site fonctionne correctement.

Les prochaines évolutions peuvent inclure plusieurs templates ou un module de gestion des images directement depuis l’admin.