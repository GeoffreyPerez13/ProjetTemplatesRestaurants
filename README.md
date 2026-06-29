# MenuCraft

> Plateforme SaaS de création et gestion de sites vitrines pour restaurants.

---

## Description

**MenuCraft** permet aux restaurateurs de créer et gérer un site vitrine professionnel pour leur restaurant, sans compétence technique. L'application propose :

- **Site vitrine personnalisable** par restaurant (carte en ligne, horaires, contact, avis Google, réservations)
- **Back-office d'administration** pour chaque restaurateur (rôle `ADMIN`)
- **Panneau de super-administration** pour le gestionnaire de la plateforme (rôle `SUPER_ADMIN`)
- **Page landing commerciale** pour présenter le service et permettre l'inscription
- **Système de paiement Stripe** pour les abonnements (Basique + options premium)
- **Mode démo** avec clonage isolé du restaurant de démonstration
- **Mode BETA** (configurable) rendant toutes les fonctionnalités premium gratuites

---

## Stack technique

| Composant | Technologie |
|-----------|------------|
| Langage | PHP 8.x (procédural avec classes) |
| Base de données | MySQL 8.x via PDO |
| Serveur | Apache (WampServer) avec `.htaccess` |
| Paiement | Stripe API (via cURL natif) |
| Emails | Fonction `mail()` native PHP + MailHog (dev) |
| CSS | CSS custom (pas de framework) |
| JS | Vanilla JS, SweetAlert2, Chart.js, SortableJS |
| Icônes | Font Awesome 6.5 |
| Fonts | Google Fonts (Inter, Playfair Display) |
| Tests | PHPUnit 10.5, k6 (charge) |

---

## Architecture

L'application suit un pattern **MVC simplifié** avec routage centralisé via `public/index.php` :

```
ProjetTemplatesRestaurants/
├── public/                     # Point d'entrée web
│   ├── index.php               # Routeur principal
│   ├── assets/                 # CSS, JS, images statiques
│   └── uploads/                # Fichiers uploadés (logos, bannières, plats)
├── app/
│   ├── Controllers/            # Contrôleurs (héritent de BaseController)
│   ├── Models/                 # Modèles de données (PDO)
│   ├── Views/                  # Vues PHP (admin/, display/, partials/)
│   ├── Helpers/                # Utilitaires (Mailer, Validator, RateLimiter)
│   └── Services/               # Services métier (NotificationService)
├── cron/                       # Tâches planifiées
├── database/                   # Schéma SQL de la base de données
├── tests/                      # Tests PHPUnit (Unit, Functional, Security)
├── config.php                  # Configuration locale (gitignored)
├── config.example.php          # Template de configuration
├── composer.json               # Dépendances PHP
└── SYNTHESE_APPLICATION.md     # Documentation complète des fonctionnalités
```

---

## Installation

### Prérequis

- **PHP 8.0** ou supérieur
- **MySQL 8.0** ou supérieur
- **WampServer** (ou XAMPP / MAMP)
- **Composer** (pour les dépendances de test)

### Étapes

1. **Cloner le projet**
   ```bash
   git clone <url-du-repo> ProjetTemplatesRestaurants
   ```

2. **Configurer la base de données**
   ```bash
   mysql -u root -p < database/schema.sql
   ```

3. **Créer le fichier de configuration**
   ```bash
   cp config.example.php config.php
   ```
   Puis éditer `config.php` avec vos identifiants MySQL, clés Stripe, etc.

4. **Installer les dépendances de test** (optionnel)
   ```bash
   composer install
   ```

5. **Configurer Apache**
   - Le `DocumentRoot` doit pointer vers le dossier `public/`
   - Activer `mod_rewrite`
   - S'assurer que `AllowOverride All` est activé

6. **Vérifier les permissions**
   ```
   public/uploads/     → écriture (755 ou 775)
   storage/            → écriture (755 ou 775)
   cron/logs/          → écriture (755 ou 775)
   ```

7. **Accéder à l'application**
   ```
   http://localhost/ProjetTemplatesRestaurants/public/?page=landing
   ```

### Configuration MailHog (développement)

Pour tester les emails en local :
```bash
.\mailhog.bat
```
Interface web MailHog : `http://localhost:8025`

---

## Fonctionnalités

### Page publique (Landing)
- Présentation du service MenuCraft
- Tarifs (Basique 11,99€/mois, options premium à la carte, Pack Full 29,99€/mois)
- Section démo interactive
- FAQ, social proof, CTA d'inscription
- Mode BETA : affichage "gratuit pendant 3 mois"

### Site vitrine restaurant (`?page=display&slug=mon-restaurant`)
- Carte en ligne (mode éditable ou images)
- Menus du jour / formules
- Horaires, contact, Google Maps intégrée
- Logo, bannière personnalisée
- Services, moyens de paiement, réseaux sociaux
- Avis Google (premium)
- Formulaire de réservation en ligne (premium)
- 7 palettes de couleurs × 3 layouts
- SEO optimisé (Schema.org, Open Graph, sitemap XML)
- Conformité RGPD (bannière cookies, CGU, mentions légales)

### Back-office ADMIN (restaurateur)
- **Dashboard** : résumé, accès rapides, statut abonnement
- **Carte** : CRUD catégories/plats avec allergènes, images, drag & drop ; ou mode images uploadées
- **Menus du jour** : CRUD formules avec items JSON, activation/désactivation
- **Contact** : téléphone, email, adresse, horaires
- **Logo / Bannière** : upload et gestion
- **Services** : services proposés, moyens de paiement, réseaux sociaux
- **Template** : choix parmi 7 palettes et 3 layouts avec prévisualisation
- **Plan de salle** : éditeur visuel drag & drop (tables, étages, éléments décoratifs)
- **Paramètres** : profil, mot de passe, site en ligne/maintenance, notifications, dates de fermeture
- **Statistiques avancées** (premium) : visites, appareils, navigateurs, tendances
- **Réservations** (premium) : gestion complète avec statuts, emails, notifications SSE temps réel
- **Avis Google** (premium) : configuration Place ID + API Key
- **Paiement Stripe** : checkout, annulation, réactivation

### Panneau SUPER_ADMIN
- Toutes les fonctionnalités ADMIN
- **Envoi d'invitations** par email avec token
- **Gestion des clients** : activation/désactivation abonnements, features premium
- **Génération de démos** : clone isolé du restaurant de démonstration (validité 3 jours)
- **Dashboard feedbacks** : consultation des retours d'expérience beta

---

## Sécurité

- **Mots de passe** : `password_hash()` / `password_verify()` (bcrypt)
- **CSRF** : tokens générés et vérifiés sur chaque formulaire POST
- **XSS** : `htmlspecialchars()` systématique sur les sorties
- **SQL Injection** : requêtes préparées PDO exclusivement
- **Rate limiting** : anti-brute-force basé fichiers/IP (login : 5/15min, réservation : 10/h)
- **Sessions** : régénération d'ID après login, configuration sécurisée
- **Headers** : `X-Content-Type-Options`, `X-Frame-Options`, `X-XSS-Protection`, `Referrer-Policy`, `HSTS`
- **Uploads** : validation MIME, taille limitée
- **Dossiers protégés** : `.htaccess` deny sur `app/`, `cron/`, `tests/`, `storage/`

---

## Tests

```bash
# Tous les tests
php vendor/bin/phpunit

# Par suite
php vendor/bin/phpunit --testsuite=Unit
php vendor/bin/phpunit --testsuite=Functional
php vendor/bin/phpunit --testsuite=Security
```

| Suite | Description |
|-------|-------------|
| Unit | Tests unitaires des modèles (Admin, DemoToken) |
| Functional | Tests d'accès HTTP et redirections |
| Security | Tests SQL injection et XSS |
| Load (k6) | Tests de charge |

---

## CRON Jobs

| Script | Fréquence | Description |
|--------|-----------|-------------|
| `cron/auto_complete_reservations.php` | Toutes les 15 min | Marquage auto des réservations terminées |
| `cron/send_reminders.php` | Mensuel | Rappel de mise à jour de la carte |

---

## Documentation

Pour une synthèse complète et détaillée de toutes les fonctionnalités, consulter le fichier **[SYNTHESE_APPLICATION.md](SYNTHESE_APPLICATION.md)**.

---

## Auteur

**Geoffrey Perez** — Projet CDA (Concepteur Développeur d'Applications)

---

## Licence

Projet privé — Tous droits réservés.
