# Installation MenuMiam V2

## Prérequis

- PHP 8.0+
- MySQL 8.0+
- Apache avec mod_rewrite activé
- WAMP/XAMPP/MAMP

## Installation

### 1. Cloner le projet

```bash
git clone https://github.com/GeoffreyPerez13/ProjetTemplatesRestaurants.git
cd ProjetTemplatesRestaurants
git checkout V2-refonte
```

### 2. Créer la base de données

```bash
# Se connecter à MySQL
mysql -u root -p

# Créer la base de données
CREATE DATABASE menumiam_v2 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
exit;

# Importer le schéma
mysql -u root -p menumiam_v2 < database/migrations/001_create_initial_schema.sql
```

### 3. Configurer la base de données

```bash
# Copier le fichier de configuration
cp config/database.example.php config/database.php

# Éditer config/database.php avec vos identifiants MySQL
```

### 4. Configuration Apache

Assurez-vous que le `DocumentRoot` pointe vers le dossier `public/` :

```apache
<VirtualHost *:80>
    DocumentRoot "C:/wamp64/www/ProjetTemplatesRestaurants/public"
    ServerName menumiam.local
    
    <Directory "C:/wamp64/www/ProjetTemplatesRestaurants/public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

Ou accédez via : `http://localhost/ProjetTemplatesRestaurants/public`

### 5. Tester l'application

Ouvrez votre navigateur et accédez à :
- `http://localhost/ProjetTemplatesRestaurants/public/register` pour créer un compte
- `http://localhost/ProjetTemplatesRestaurants/public/login` pour vous connecter

## Structure du projet

```
ProjetTemplatesRestaurants/
├── app/
│   ├── Controllers/      # Contrôleurs MVC
│   ├── Models/          # Modèles de données
│   ├── Views/           # Vues (templates)
│   ├── Core/            # Classes core (Database, Router, etc.)
│   └── Helpers/         # Helpers (Session, Validator, Hash)
├── config/              # Configuration
├── database/
│   ├── migrations/      # Schémas SQL
│   └── seeds/           # Données de test
├── public/              # Point d'entrée web
│   ├── index.php        # Bootstrap
│   ├── .htaccess        # Réécriture URL
│   └── assets/          # CSS, JS, uploads
└── tests/               # Tests unitaires
```

## Fonctionnalités disponibles

### Phase 2 (Actuelle)
- ✅ Inscription / Connexion
- ✅ Dashboard administrateur
- ✅ Gestion de session
- ✅ Protection CSRF
- ✅ Validation des formulaires

### Prochaines phases
- 🔄 Gestion de la carte (catégories, plats)
- 🔄 Gestion du contact
- 🔄 Upload logo/bannière
- 🔄 Templates/apparence
- 🔄 Fonctionnalités premium

## Dépannage

### Erreur 404 sur toutes les routes
- Vérifiez que `mod_rewrite` est activé dans Apache
- Vérifiez que le fichier `.htaccess` est présent dans `public/`

### Erreur de connexion à la base de données
- Vérifiez vos identifiants dans `config/database.php`
- Vérifiez que MySQL est démarré
- Vérifiez que la base de données existe

### Page blanche
- Activez l'affichage des erreurs dans `config/app.php` : `'debug' => true`
- Vérifiez les logs Apache

## Support

Pour toute question, consultez la documentation dans `_dev/cda/conception/`
