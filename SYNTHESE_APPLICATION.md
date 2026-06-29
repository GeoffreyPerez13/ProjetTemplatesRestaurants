# MenuCraft — Synthèse Complète de l'Application

> **Date de génération :** 29/06/2026  
> **Objectif :** Document de référence complet pour recréer l'application dans une structure propre.

---

## Table des matières

1. [Vue d'ensemble](#1-vue-densemble)
2. [Architecture technique](#2-architecture-technique)
3. [Structure des fichiers](#3-structure-des-fichiers)
4. [Base de données](#4-base-de-données)
5. [Configuration](#5-configuration)
6. [Routeur principal (index.php)](#6-routeur-principal-indexphp)
7. [Sécurité](#7-sécurité)
8. [Page Landing / Vitrine commerciale](#8-page-landing--vitrine-commerciale)
9. [Système d'inscription et d'authentification](#9-système-dinscription-et-dauthentification)
10. [Fonctionnalités ADMIN (Client restaurateur)](#10-fonctionnalités-admin-client-restaurateur)
11. [Fonctionnalités SUPER_ADMIN](#11-fonctionnalités-super_admin)
12. [Page Display (Site vitrine du restaurant)](#12-page-display-site-vitrine-du-restaurant)
13. [Système de paiement Stripe](#13-système-de-paiement-stripe)
14. [Fonctionnalités Premium](#14-fonctionnalités-premium)
15. [Système de démonstration](#15-système-de-démonstration)
16. [CRON Jobs](#16-cron-jobs)
17. [Emails et notifications](#17-emails-et-notifications)
18. [SEO et pages légales](#18-seo-et-pages-légales)
19. [Assets et templates visuels](#19-assets-et-templates-visuels)
20. [Tests](#20-tests)

---

## 1. Vue d'ensemble

**MenuCraft** est une plateforme SaaS permettant aux restaurateurs de créer et gérer un site vitrine pour leur restaurant. L'application propose :

- Un **site vitrine personnalisable** par restaurant (carte en ligne, horaires, contact, avis Google, réservations)
- Un **back-office d'administration** pour chaque restaurateur (rôle `ADMIN`)
- Un **panneau de super-administration** pour le gestionnaire de la plateforme (rôle `SUPER_ADMIN`)
- Une **page landing commerciale** pour présenter le service et permettre l'inscription
- Un **système de paiement Stripe** pour les abonnements (Basique + options premium)
- Un **mode démo** avec clonage isolé du restaurant de démonstration
- Un **mode BETA** (configurable) qui rend toutes les fonctionnalités premium gratuites

---

## 2. Architecture technique

### Stack

| Composant | Technologie |
|-----------|------------|
| **Langage** | PHP 8.x (procédural avec classes) |
| **Base de données** | MySQL via PDO |
| **Serveur** | Apache (WAMP) avec `.htaccess` |
| **Paiement** | Stripe API (via cURL, sans SDK) |
| **Emails** | Fonction `mail()` native PHP |
| **CSS** | CSS custom (pas de framework CSS) |
| **JS** | Vanilla JS + quelques libs (SweetAlert2, Chart.js, SortableJS) |
| **Icônes** | Font Awesome 6.5 |
| **Fonts** | Google Fonts (Inter, Playfair Display) |

### Pattern architectural

L'application suit un pattern **MVC simplifié** :

- **Models** (`app/Models/`) — Classes PHP avec requêtes PDO
- **Views** (`app/Views/`) — Fichiers PHP avec HTML/CSS/JS
- **Controllers** (`app/Controllers/`) — Classes PHP héritant de `BaseController`
- **Helpers** (`app/Helpers/`) — Classes utilitaires (Mailer, Validator, RateLimiter)
- **Services** (`app/Services/`) — Services métier (NotificationService)

Le routage est centralisé dans `public/index.php` via `$_GET['page']`.

---

## 3. Structure des fichiers

```
ProjetTemplatesRestaurants/
├── public/                          # Point d'entrée web
│   ├── index.php                    # Routeur principal (726 lignes)
│   ├── assets/
│   │   ├── css/                     # Feuilles de style
│   │   │   ├── landing.css          # Styles page landing
│   │   │   ├── admin.css            # Styles back-office
│   │   │   └── display/             # Styles site vitrine (par template)
│   │   ├── js/                      # Scripts JavaScript
│   │   │   ├── landing/             # JS page landing
│   │   │   ├── admin/               # JS back-office
│   │   │   └── display/             # JS site vitrine
│   │   ├── images/                  # Images statiques
│   │   └── favicon.svg              # Favicon
│   └── uploads/                     # Fichiers uploadés (logos, bannières, plats)
│
├── app/
│   ├── Controllers/                 # Contrôleurs
│   │   ├── BaseController.php       # Classe de base (CSRF, sessions, flash, render)
│   │   ├── AdminController.php      # Auth, inscription, dashboard
│   │   ├── CardController.php       # Gestion de la carte (catégories, plats, images)
│   │   ├── ContactController.php    # Infos de contact du restaurant
│   │   ├── DisplayController.php    # Affichage du site vitrine public
│   │   ├── FeedbackController.php   # Formulaire de retour beta
│   │   ├── FloorPlanController.php  # Plan de salle (tables, éléments)
│   │   ├── LegalController.php      # Pages légales (CGU, RGPD, Cookies, Mentions)
│   │   ├── LogoBannerController.php # Upload logo et bannière
│   │   ├── ReservationController.php # Réservations en ligne (premium)
│   │   ├── ServicesController.php   # Services, paiements, réseaux sociaux
│   │   ├── SettingsController.php   # Paramètres (profil, mdp, options, template, premium)
│   │   ├── SitemapController.php    # Génération sitemap.xml dynamique
│   │   ├── StatsController.php      # Statistiques avancées (premium)
│   │   ├── StripeController.php     # Paiement Stripe (checkout, webhook, succès)
│   │   ├── ClientManagementController.php  # Gestion clients (SUPER_ADMIN)
│   │   └── NotificationStreamController.php # SSE notifications temps réel
│   │
│   ├── Models/                      # Modèles de données
│   │   ├── Admin.php                # Utilisateurs admin (28 Ko — le plus gros modèle)
│   │   ├── Allergene.php            # 14 allergènes réglementaires
│   │   ├── BillingCycle.php         # Cycles de facturation prorata
│   │   ├── CardImage.php            # Images de carte (mode images)
│   │   ├── Category.php             # Catégories de plats
│   │   ├── ClientSubscription.php   # Abonnements clients
│   │   ├── Contact.php              # Infos de contact restaurant
│   │   ├── DailyMenu.php            # Menus du jour / formules
│   │   ├── DemoToken.php            # Tokens de démo avec clonage
│   │   ├── Dish.php                 # Plats (nom, prix, description, image, allergènes)
│   │   ├── Floor.php                # Étages du plan de salle
│   │   ├── GoogleReviews.php        # Avis Google Places (API + cache)
│   │   ├── OptionModel.php          # Options clé/valeur par admin
│   │   ├── PremiumFeature.php       # Fonctionnalités premium (activation/désactivation)
│   │   ├── Reservation.php          # Réservations clients
│   │   ├── Restaurant.php           # Restaurant (slug, logo, bannière, contact)
│   │   ├── RestaurantElement.php    # Éléments décoratifs du plan de salle
│   │   ├── RestaurantTable.php      # Tables du plan de salle
│   │   └── SiteVisit.php            # Tracking des visites (anonymisé)
│   │
│   ├── Helpers/
│   │   ├── Mailer.php               # Envoi d'emails (mail() natif + logs)
│   │   ├── RateLimiter.php          # Anti-brute-force basé fichiers/IP
│   │   └── Validator.php            # Validation de formulaires (rules + password)
│   │
│   ├── Services/
│   │   └── NotificationService.php  # Notifications email aux admins
│   │
│   └── Views/
│       ├── landing.php              # Page landing commerciale (879 lignes)
│       ├── display.php              # Layout principal site vitrine
│       ├── display/                 # Composants du site vitrine
│       │   ├── head.php             # Meta tags, SEO, Schema.org
│       │   ├── header.php           # Navigation du site vitrine
│       │   ├── banner.php           # Bannière héro
│       │   ├── carte.php            # Affichage de la carte
│       │   ├── services.php         # Services, paiements, réseaux
│       │   ├── reviews.php          # Avis Google
│       │   ├── reviews-premium-upgrade.php  # Upsell avis Google
│       │   ├── reservation.php      # Formulaire de réservation public
│       │   ├── footer.php           # Pied de page + contact + Google Maps
│       │   ├── cookies.php          # Bannière cookies RGPD
│       │   └── lightbox.php         # Lightbox images
│       ├── admin/                   # Pages du back-office
│       │   ├── login.php            # Page de connexion
│       │   ├── register.php         # Inscription via invitation
│       │   ├── auto-register.php    # Inscription libre
│       │   ├── dashboard.php        # Tableau de bord
│       │   ├── edit-card.php        # Éditeur de carte (82 Ko)
│       │   ├── edit-contact.php     # Éditeur de contact
│       │   ├── edit-logo-banner.php # Éditeur logo/bannière
│       │   ├── edit-services.php    # Éditeur services/paiements/réseaux
│       │   ├── edit-template.php    # Choix palette/layout
│       │   ├── settings.php         # Page paramètres (152 Ko — la plus grosse vue)
│       │   ├── view-card.php        # Prévisualisation de la carte
│       │   ├── stats.php            # Statistiques avancées
│       │   ├── reservations.php     # Gestion des réservations (91 Ko)
│       │   ├── floor-plan.php       # Éditeur plan de salle
│       │   ├── feedback.php         # Formulaire feedback beta
│       │   ├── feedback-dashboard.php # Dashboard feedbacks (SUPER_ADMIN)
│       │   ├── legals.php           # Pages légales
│       │   ├── manage-clients.php   # Gestion clients (SUPER_ADMIN)
│       │   ├── send-invitation.php  # Envoi d'invitations (SUPER_ADMIN)
│       │   ├── reset-password.php   # Réinitialisation MDP (demande)
│       │   ├── reset-password-admin.php # Réinitialisation MDP (formulaire)
│       │   ├── logout.php           # Page de déconnexion
│       │   └── google-reviews-roadmap.php # Roadmap avis Google
│       ├── partials/                # Composants réutilisables back-office
│       │   ├── header.php           # En-tête admin (navigation, notifications)
│       │   ├── footer.php           # Pied de page admin
│       │   └── cookie-banner.php    # Bannière cookies admin
│       └── errors/                  # Pages d'erreur
│
├── cron/                            # Tâches planifiées
│   ├── auto_complete_reservations.php  # Marquage auto des réservations terminées
│   ├── send_reminders.php           # Rappels mensuels de mise à jour
│   └── logs/                        # Logs des CRON
│
├── config/                          # (vide — config dans config.php à la racine)
├── config.php                       # Configuration BDD, Stripe, URLs, BETA_MODE (gitignored)
├── storage/                         # Stockage fichiers (rate_limits)
├── tests/                           # Tests PHPUnit + k6
├── _dev/                            # Fichiers de développement
├── composer.json                    # Dépendances PHP (PHPUnit)
├── .htaccess                        # Réécriture Apache
├── .gitignore                       # Fichiers ignorés
└── README.md                        # Documentation du projet
```

---

## 4. Base de données

### Tables principales

| Table | Description |
|-------|------------|
| `admins` | Comptes administrateurs (username, email, password, restaurant_name, restaurant_id, carte_mode, role, email_verified, verification_token) |
| `restaurants` | Restaurants (name, slug, created_at, updated_at) |
| `categories` | Catégories de la carte (admin_id, name, description, image, display_order) |
| `plats` | Plats (category_id, name, description, price, image, display_order, is_active) |
| `allergenes` | 14 allergènes réglementaires (nom, icone) |
| `plat_allergenes` | Table pivot plats ↔ allergènes |
| `card_images` | Images de carte mode "images" (admin_id, filename) |
| `contacts` | Infos contact (admin_id, telephone, email, adresse, horaires) |
| `daily_menus` | Menus du jour (admin_id, title, description, price, items JSON, display_order, is_active) |
| `admin_options` | Options clé/valeur par admin (admin_id, option_name, option_value) |
| `invitations` | Invitations par email (email, restaurant_name, token, expiry, used) |
| `client_subscriptions` | Abonnements clients (admin_id, plan_type, status, price_per_month, features_enabled, started_at, expires_at, billing_cycle_day, next_billing_date) |
| `premium_features` | Features premium par admin (admin_id, feature_name, is_active, activated_at, expires_at, cancelled_at) |
| `demo_tokens` | Tokens de démo (token, admin_id, clone_admin_id, clone_restaurant_id, expires_at, created_by) |
| `site_visits` | Tracking des visites (admin_id, visitor_hash, user_agent, referrer, device_type, browser, page_path, visited_at) |
| `reservations` | Réservations (admin_id, customer_name, customer_phone, customer_email, reservation_date, reservation_time, party_size, special_requests, status, created_at) |
| `floors` | Étages du plan de salle (admin_id, name, display_order) |
| `restaurant_tables` | Tables du restaurant (floor_id, table_number, seats, x, y, width, height, shape, rotation) |
| `restaurant_elements` | Éléments décoratifs du plan (floor_id, element_type, x, y, width, height, rotation) |
| `feedbacks` | Retours d'expérience beta |
| `google_reviews_cache` | Cache des avis Google (place_id, data JSON, cached_at) |
| `password_resets` | Tokens de réinitialisation de mot de passe |

### Options admin (`admin_options`) — Clés principales

| Clé | Description | Valeurs possibles |
|-----|------------|-------------------|
| `site_online` | Site en ligne ou maintenance | `0` / `1` |
| `site_palette` | Palette de couleurs | `classic`, `modern`, `elegant`, `nature`, `rose`, `bistro`, `ocean` |
| `site_layout` | Disposition du site | `standard`, `bistro`, `ocean` |
| `email_notifications` | Recevoir les notifications email | `0` / `1` |
| `mail_reminder` | Rappel mensuel de mise à jour carte | `0` / `1` |
| `google_place_id` | Place ID Google pour les avis | string |
| `google_api_key` | Clé API Google Places | string |
| `google_reviews_enabled` | Afficher les avis Google | `0` / `1` |
| `booking_enabled` | Réservations en ligne activées | `0` / `1` |
| `booking_min_party` | Taille min du groupe | int |
| `booking_max_party` | Taille max du groupe | int |
| `booking_advance_days` | Jours de réservation à l'avance | int |
| `booking_message` | Message personnalisé réservation | string |
| `booking_auto_complete` | Marquage auto des réservations | `0` / `1` |
| `closure_dates` | Dates de fermeture exceptionnelle | JSON array |
| `service_sur_place`, `service_a_emporter`, ... | Services proposés | `0` / `1` |
| `payment_visa`, `payment_mastercard`, ... | Moyens de paiement acceptés | `0` / `1` |
| `social_instagram`, `social_facebook`, ... | Liens réseaux sociaux | URL string |

---

## 5. Configuration

Le fichier `config.php` (gitignored) contient :

```php
// Connexion BDD
$host = 'localhost';
$dbname = 'menucraft';
$user = 'root';
$pass = '';

// Création PDO
$pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
]);

// URLs et constantes
define('SITE_URL', 'http://localhost/ProjetTemplatesRestaurants/public');
define('BASE_PATH', __DIR__);

// Stripe
define('STRIPE_SECRET_KEY', 'sk_test_...');
define('STRIPE_PUBLISHABLE_KEY', 'pk_test_...');
define('STRIPE_WEBHOOK_SECRET', 'whsec_...');

// Mode Beta
define('BETA_MODE', true);  // true = toutes les features premium gratuites
define('BETA_EXPIRES', '2026-09-30');
```

---

## 6. Routeur principal (index.php)

Le routeur `public/index.php` est le point d'entrée unique de l'application. Il :

1. Démarre la session avec configuration sécurisée
2. Charge `config.php` (crée `$pdo`)
3. Inclut tous les controllers, models et helpers nécessaires
4. Dispatch la requête selon `$_GET['page']`

### Table de routage complète

| Route (`?page=`) | Contrôleur / Méthode | Auth requise | Rôle | Description |
|---|---|---|---|---|
| `landing` (ou vide) | Vue `landing.php` | Non | Public | Page commerciale |
| `login` | `AdminController->login()` | Non | Public | Connexion |
| `logout` | `AdminController->logout()` | Oui | Tous | Déconnexion |
| `register` | `AdminController->register()` | Non | Public | Inscription via invitation |
| `auto-register` | `AdminController->autoRegister()` | Non | Public | Inscription libre |
| `verify-email` | `AdminController->verifyEmail()` | Non | Public | Confirmation email |
| `reset-password` | Logique dans index.php | Non | Public | Demande de reset MDP |
| `reset-password-admin` | Logique dans index.php | Non | Public | Formulaire nouveau MDP |
| `dashboard` | `AdminController->dashboard()` | Oui | Tous | Tableau de bord |
| `edit-card` | `CardController->show()` | Oui | ADMIN | Éditeur de carte |
| `save-card` | `CardController->save()` | Oui | ADMIN | Sauvegarder la carte |
| `save-category` | `CardController->saveCategory()` | Oui | ADMIN | Sauvegarder catégorie |
| `delete-category` | `CardController->deleteCategory()` | Oui | ADMIN | Supprimer catégorie |
| `save-dish` | `CardController->saveDish()` | Oui | ADMIN | Sauvegarder plat |
| `delete-dish` | `CardController->deleteDish()` | Oui | ADMIN | Supprimer plat |
| `upload-card-image` | `CardController->uploadImage()` | Oui | ADMIN | Upload image carte |
| `delete-card-image` | `CardController->deleteImage()` | Oui | ADMIN | Supprimer image carte |
| `reorder-categories` | `CardController->reorderCategories()` | Oui | ADMIN | Réordonner catégories |
| `reorder-dishes` | `CardController->reorderDishes()` | Oui | ADMIN | Réordonner plats |
| `view-card` | `CardController->viewCard()` | Oui | ADMIN | Prévisualisation carte |
| `save-daily-menu` | `CardController->saveDailyMenu()` | Oui | ADMIN | Sauvegarder menu du jour |
| `delete-daily-menu` | `CardController->deleteDailyMenu()` | Oui | ADMIN | Supprimer menu du jour |
| `toggle-daily-menu` | `CardController->toggleDailyMenu()` | Oui | ADMIN | Activer/désactiver menu |
| `reorder-daily-menus` | `CardController->reorderDailyMenus()` | Oui | ADMIN | Réordonner menus |
| `edit-contact` | `ContactController->edit()` | Oui | ADMIN | Éditeur de contact |
| `edit-logo-banner` | `LogoBannerController->show()` | Oui | ADMIN | Éditeur logo/bannière |
| `upload-logo` | `LogoBannerController->uploadLogo()` | Oui | ADMIN | Upload logo |
| `delete-logo` | `LogoBannerController->deleteLogo()` | Oui | ADMIN | Supprimer logo |
| `upload-banner` | `LogoBannerController->uploadBanner()` | Oui | ADMIN | Upload bannière |
| `delete-banner` | `LogoBannerController->deleteBanner()` | Oui | ADMIN | Supprimer bannière |
| `save-banner-text` | `LogoBannerController->saveBannerText()` | Oui | ADMIN | Sauver texte bannière |
| `edit-services` | `ServicesController->show()` | Oui | ADMIN | Éditeur services |
| `save-services` | `ServicesController->save()` | Oui | ADMIN | Sauvegarder services |
| `settings` | `SettingsController->show()` | Oui | Tous | Page paramètres |
| `update-profile` | `SettingsController->updateProfile()` | Oui | Tous | Maj profil |
| `update-password` | `SettingsController->updatePassword()` | Oui | Tous | Maj mot de passe |
| `update-options` | `SettingsController->updateOptions()` | Oui | Tous | Maj options |
| `update-template` | `SettingsController->updateTemplate()` | Oui | ADMIN | Maj palette/layout |
| `edit-template` | Vue template | Oui | ADMIN | Choix palette/layout |
| `display` | `DisplayController->show()` | Non | Public | Site vitrine restaurant |
| `demo` | Logique démo dans index.php | Non | Public | Accès démo |
| `demo-logout` | Logique dans index.php | Oui | Démo | Fin de session démo |
| `stripe-checkout` | `StripeController->createCheckout()` | Oui | ADMIN | Paiement Stripe |
| `stripe-success` | `StripeController->handleSuccess()` | Oui | ADMIN | Retour succès Stripe |
| `stripe-webhook` | `StripeController->handleWebhook()` | Non | Système | Webhook Stripe |
| `stripe-cancel` | `StripeController->cancelSubscription()` | Oui | ADMIN | Annuler abonnement |
| `stripe-reactivate` | `StripeController->reactivateSubscription()` | Oui | ADMIN | Réactiver abonnement |
| `send-invitation` | `AdminController->sendInvitation()` | Oui | SUPER_ADMIN | Envoyer invitation |
| `manage-clients` | `ClientManagementController->show()` | Oui | SUPER_ADMIN | Gestion clients |
| `activate-subscription` | `ClientManagementController->activateSubscription()` | Oui | SUPER_ADMIN | Activer abo client |
| `deactivate-subscription` | `ClientManagementController->deactivateSubscription()` | Oui | SUPER_ADMIN | Désactiver abo client |
| `stats` | `StatsController->show()` | Oui | Premium | Page statistiques |
| `stats-data` | `StatsController->getData()` | Oui | Premium | API données stats (JSON) |
| `reservations` | `ReservationController->list()` | Oui | Premium | Liste réservations |
| `reservation-update-status` | `ReservationController->updateStatus()` | Oui | Premium | Confirmer/refuser résa |
| `public-booking` | `ReservationController->publicBooking()` | Non | Public | Soumission résa publique |
| `feedback` | `FeedbackController->show()` | Oui | Tous | Formulaire feedback |
| `submit-feedback` | `FeedbackController->submit()` | Oui | Tous | Soumettre feedback |
| `feedback-dashboard` | Vue feedback-dashboard | Oui | SUPER_ADMIN | Dashboard feedbacks |
| `floor-plan` | `FloorPlanController->show()` | Oui | ADMIN | Plan de salle |
| `floor-plan-save` | `FloorPlanController->save()` | Oui | ADMIN | Sauvegarder plan |
| `legal` | `LegalController->show()` | Non | Public | Pages légales |
| `sitemap.xml` | `SitemapController->generate()` | Non | Public | Sitemap XML |
| `notification-stream` | `NotificationStreamController->stream()` | Oui | Tous | SSE temps réel |

---

## 7. Sécurité

### Authentification
- **Sessions PHP** : `$_SESSION['admin_logged']`, `$_SESSION['admin_id']`, `$_SESSION['admin_name']`, `$_SESSION['username']`
- **Mot de passe** : `password_hash()` (bcrypt) et `password_verify()`
- **Session regeneration** : `session_regenerate_id(true)` après login (anti session fixation)
- **Vérification d'email** : Token unique envoyé à l'inscription, compte inactif tant que non vérifié

### Protection CSRF
- Token généré dans `BaseController::getCsrfToken()` et stocké en session
- Vérifié par `BaseController::verifyCsrfToken()` sur chaque formulaire POST

### Rate Limiting
- `RateLimiter` basé sur fichiers (pas de dépendance Redis/Memcached)
- Login : max 5 tentatives par 15 minutes par IP
- Réservation publique : max 10 par heure par IP

### Headers de sécurité (BaseController)
```
X-Content-Type-Options: nosniff
X-Frame-Options: DENY
X-XSS-Protection: 1; mode=block
Referrer-Policy: strict-origin-when-cross-origin
Strict-Transport-Security: max-age=31536000 (si HTTPS)
```

### Protection XSS
- `htmlspecialchars()` utilisé systématiquement dans les vues
- Données utilisateur toujours échappées avant affichage

### Protection SQL Injection
- Requêtes préparées PDO exclusivement (paramètres bindés)

### Mode démo
- Méthode `blockIfDemo()` dans `BaseController` empêche les actions sensibles en mode démo (paiement, modification profil, suppression)

---

## 8. Page Landing / Vitrine commerciale

**Route :** `?page=landing` (ou page d'accueil par défaut)  
**Vue :** `app/Views/landing.php` (879 lignes)

### Sections de la page

1. **Navigation** — Logo MenuCraft, liens Fonctionnalités/Tarifs/Démo/FAQ, boutons "Se connecter" / "Créer mon site"
2. **Hero** — Titre principal, sous-titre avec prix, CTA "Créer mon site" + "Voir la démo", mockup visuel du produit
3. **Social Proof** — Noms de restaurants fictifs utilisant MenuCraft
4. **Fonctionnalités** (9 cards) :
   - Carte en ligne (Gratuit)
   - Templates personnalisables — 7 palettes, 3 layouts (Gratuit)
   - Horaires & Contact (Gratuit)
   - 100% Responsive (Gratuit)
   - SEO optimisé (Gratuit)
   - RGPD & Légal (Gratuit)
   - Avis Google (Premium)
   - Statistiques avancées (Premium)
   - Réservations en ligne (Bientôt / Premium)
5. **Comment ça marche** — 3 étapes : Créer compte → Personnaliser → Publier
6. **Tarifs** (conditionnel selon `BETA_MODE`) :
   - **Mode Beta** : Bloc unique "100% Gratuit pendant 3 mois"
   - **Mode Normal** :
     - Abonnement Basique : **11,99€/mois** (9,99€ annuel) — toggle mensuel/annuel
     - Pack Full : **29,99€/mois** (22,99€ annuel) — durées 1 mois / 3 mois / 1 an
     - Options à la carte : Avis Google (3,99€), Stats (3,99€), Réservations (10,99€), Livraison (3,99€)
7. **Démo** — CTA vers la démo live + demande de démo privée par email
8. **FAQ** — Accordion avec questions fréquentes
9. **CTA final** — Dernier appel à l'inscription
10. **Footer** — Liens légaux, réseaux, copyright

### Comportement Beta
Quand `BETA_MODE === true` :
- Bouton "Créer mon site" → `mailto:contact.menucraft@gmail.com` (inscription sur invitation)
- Section tarifs remplacée par bloc "Beta Gratuite"
- Prix masqués, toutes les features gratuites

---

## 9. Système d'inscription et d'authentification

### Inscription libre (`auto-register`)
1. Formulaire : username, email, nom du restaurant, mot de passe + confirmation
2. Validation : champs obligatoires, email valide, username ≥ 3 chars, password robuste (via `Validator::validatePassword`)
3. Création du compte via `Admin::createAccountDirect()`
4. Envoi d'un email de vérification avec token unique
5. Création d'un abonnement basique inactif (`client_subscriptions`)
6. Redirection vers login avec message de succès

### Inscription par invitation (`register`)
1. Le SUPER_ADMIN envoie une invitation (email + nom restaurant)
2. L'invité reçoit un email avec un lien contenant un token
3. Formulaire : username + mot de passe (email et restaurant pré-remplis depuis l'invitation)
4. Validation du token (non expiré, non utilisé)
5. Création du compte via `Admin::createAccount()`

### Vérification d'email (`verify-email`)
- Vérifie le token dans l'URL
- Marque `email_verified = 1` dans `admins`
- Supprime le `verification_token`
- Envoie un email de confirmation

### Connexion (`login`)
1. Rate limiting : 5 tentatives / 15 min par IP
2. Vérification username + password
3. Vérification `email_verified` (si non vérifié → message d'erreur)
4. Régénération de session (`session_regenerate_id`)
5. Stockage en session : `admin_logged`, `admin_id`, `admin_name`, `username`
6. Redirection vers dashboard

### Réinitialisation de mot de passe
1. **Demande** (`reset-password`) : email → génération token → envoi email
2. **Formulaire** (`reset-password-admin`) : token validé → nouveau mot de passe → mise à jour

### Déconnexion (`logout`)
- Mode normal : `session_destroy()` → redirection login avec toast JS
- Mode démo : redirection vers `demo-logout` (nettoyage du clone)

---

## 10. Fonctionnalités ADMIN (Client restaurateur)

### 10.1 Dashboard (`?page=dashboard`)
**Vue :** `admin/dashboard.php` (29 Ko)

- Résumé du restaurant (nom, slug, dernière mise à jour)
- Lien rapide vers le site vitrine
- Accès rapides : Éditer la carte, Contact, Logo, Services, Paramètres
- Nombre de réservations en attente (si premium activé)
- Statut de l'abonnement
- Bouton "Mettre en ligne / hors ligne"

### 10.2 Gestion de la carte (`?page=edit-card`)
**Contrôleur :** `CardController`  
**Vue :** `admin/edit-card.php` (82 Ko — la vue la plus complexe)

**Deux modes de carte :**

#### Mode "editable" (catégories + plats)
- **Catégories** : CRUD complet, réordonnancement par drag & drop (SortableJS), image optionnelle
- **Plats** : CRUD complet par catégorie, champs : nom, description, prix, image, allergènes (14 réglementaires), statut actif/inactif, réordonnancement
- **Allergènes** : Sélection parmi les 14 allergènes réglementaires avec icônes

#### Mode "images" (photos de carte uploadées)
- Upload d'images de la carte (format photo)
- Gestion (ajout, suppression, réordonnancement)
- Adapté aux restaurants qui préfèrent prendre en photo leur carte physique

#### Menus du jour / Formules
- CRUD des menus du jour
- Champs : titre, description, prix, items (lignes en JSON : label + valeur)
- Activation/désactivation individuelle
- Réordonnancement
- Affichés sur le site vitrine quand actifs

### 10.3 Gestion du contact (`?page=edit-contact`)
**Contrôleur :** `ContactController`

- Édition : téléphone, email, adresse, horaires d'ouverture
- Validation côté serveur
- Support AJAX (réponses JSON) et formulaire classique (fallback)
- Création automatique de l'entrée contact si inexistante

### 10.4 Logo et bannière (`?page=edit-logo-banner`)
**Contrôleur :** `LogoBannerController`

- **Logo** : Upload, suppression, prévisualisation
- **Bannière** : Upload image, suppression, texte de bannière personnalisé
- Uploads stockés dans `public/uploads/`

### 10.5 Services, paiements, réseaux (`?page=edit-services`)
**Contrôleur :** `ServicesController`

#### Services proposés
- Sur place, à emporter, livraison (Uber Eats / propre), WiFi, climatisation, PMR, animaux acceptés

#### Moyens de paiement
- Visa, Mastercard, CB, espèces, chèques, tickets restaurant

#### Réseaux sociaux
- Instagram, Facebook, X (Twitter), TikTok, Snapchat (URLs)

Toutes les données stockées comme options clé/valeur dans `admin_options`.

### 10.6 Choix de template (`?page=edit-template`)
**Vue :** `admin/edit-template.php` (34 Ko)

#### Palettes de couleurs (7)
| Nom | Description |
|-----|------------|
| `classic` | Tons chauds, ambre/brun |
| `modern` | Tons froids, bleu/gris |
| `elegant` | Tons sombres, or/noir |
| `nature` | Tons verts, naturel |
| `rose` | Tons roses, féminin |
| `bistro` | Tons rouges, traditionnel |
| `ocean` | Tons bleus, marin |

#### Layouts (3)
| Nom | Description |
|-----|------------|
| `standard` | Layout classique vertical |
| `bistro` | Layout avec accent sur l'ambiance |
| `ocean` | Layout avec éléments visuels marins |

- Prévisualisation en temps réel via `?preview_palette=` et `?preview_layout=`
- Sauvegardé dans `admin_options` (`site_palette`, `site_layout`)

### 10.7 Paramètres (`?page=settings`)
**Contrôleur :** `SettingsController`  
**Vue :** `admin/settings.php` (152 Ko — la plus grosse vue)

#### Sections (via `?section=`)

| Section | Description |
|---------|------------|
| `profile` | Modifier username, email, nom du restaurant |
| `password` | Changer le mot de passe |
| `general` | Options : site en ligne/maintenance, notifications email, rappel mensuel |
| `closure-dates` | Dates de fermeture exceptionnelle (JSON) |
| `premium` | Gestion des options premium et abonnement Stripe |
| `google-reviews` | Configuration Google Place ID + API Key |
| `stats` | Accès rapide aux statistiques |
| `online-booking` | Configuration des réservations |
| `delivery` | Configuration livraison |
| `subscriptions` | Détails et historique des abonnements |

### 10.8 Plan de salle (`?page=floor-plan`)
**Contrôleur :** `FloorPlanController`  
**Vue :** `admin/floor-plan.php`

- Éditeur visuel drag & drop
- Gestion des **étages** (multi-niveaux)
- Placement des **tables** : numéro, nombre de places, position (x, y), dimensions, forme (ronde/carrée), rotation
- Placement des **éléments décoratifs** : type (bar, cuisine, entrée, WC...), position, dimensions
- Création d'un étage par défaut si aucun n'existe
- Nécessite un abonnement actif

### 10.9 Feedback beta (`?page=feedback`)
**Contrôleur :** `FeedbackController`

- Formulaire de retour d'expérience
- Champs : nom, email, note (1-5), facilité d'utilisation, commentaires
- Limite : 3 soumissions par mois par admin
- Stocké en base dans `feedbacks`

### 10.10 Prévisualisation de la carte (`?page=view-card`)
- Vue en lecture seule de la carte telle qu'elle apparaît sur le site vitrine
- Utile pour vérifier avant publication

---

## 11. Fonctionnalités SUPER_ADMIN

Le `SUPER_ADMIN` a accès à toutes les fonctionnalités ADMIN plus les suivantes :

### 11.1 Tableau de bord enrichi
- Liste des **tokens de démo** actifs (avec liens et dates d'expiration)
- Statut de l'existence du restaurant de démo (`demo-menucraft`)
- Bouton de génération de nouveaux liens de démo

### 11.2 Envoi d'invitations (`?page=send-invitation`)
**Contrôleur :** `AdminController->sendInvitation()`

- Formulaire : email, nom du restaurant
- Génération d'un token d'invitation avec expiration
- Envoi de l'email d'invitation
- Accessible uniquement au SUPER_ADMIN

### 11.3 Gestion des clients (`?page=manage-clients`)
**Contrôleur :** `ClientManagementController`  
**Vue :** `admin/manage-clients.php`

- Liste de tous les clients avec :
  - Nom, email, restaurant, date d'inscription
  - Statut de l'abonnement (actif/inactif)
  - Features premium activées
- Actions :
  - **Activer l'abonnement premium** d'un client manuellement
  - **Désactiver l'abonnement** d'un client
  - Gestion des features individuelles

### 11.4 Dashboard feedbacks (`?page=feedback-dashboard`)
**Vue :** `admin/feedback-dashboard.php`

- Visualisation de tous les retours d'expérience soumis
- Notes, commentaires, statistiques agrégées

### 11.5 Génération de démos
Via le dashboard, le SUPER_ADMIN peut :
- Générer un lien de démo (clone complet du restaurant `demo-menucraft`)
- Voir les démos actives
- Les démos expirent automatiquement après 3 jours

### 11.6 Restrictions SUPER_ADMIN
Les sections suivantes sont **inaccessibles** au SUPER_ADMIN (réservées aux clients) :
- `premium`, `google-reviews`, `stats`, `online-booking`, `delivery`, `subscriptions`

Le SUPER_ADMIN est redirigé vers `profile` s'il tente d'y accéder.

### 11.7 Prorata Stripe
Le SUPER_ADMIN a accès à l'option de calcul **prorata** lors des paiements Stripe (facturation au prorata jusqu'au 15 du mois).

---

## 12. Page Display (Site vitrine du restaurant)

**Route :** `?page=display&slug=mon-restaurant`  
**Contrôleur :** `DisplayController`  
**Vue principale :** `app/Views/display.php`

### Flux de rendu

1. Récupération du restaurant par son `slug`
2. Vérification de l'existence du restaurant et de l'admin associé
3. Vérification de l'abonnement actif (sauf SUPER_ADMIN et démo)
4. Récupération de toutes les données :
   - Logo, bannière, mode carte
   - Catégories + plats (mode editable) OU images (mode images)
   - Menus du jour actifs
   - Options (services, paiements, réseaux sociaux)
   - Contact et horaires
   - Avis Google (si premium activé)
   - Paramètres de réservation (si premium activé)
   - Dates de fermeture exceptionnelle
   - Palette et layout choisis
5. Gestion du mode maintenance (`site_online`)
6. Mode preview pour l'admin propriétaire connecté
7. Tracking de la visite (hors preview)
8. Rendu de la vue avec toutes les données

### Composants de la vue display

| Fichier | Contenu |
|---------|---------|
| `display/head.php` | Meta tags, Open Graph, Schema.org (SEO), inclusion CSS/JS |
| `display/header.php` | Navigation du restaurant (nom, logo, liens sections) |
| `display/banner.php` | Image de bannière + texte personnalisé |
| `display/carte.php` | Affichage de la carte (catégories/plats ou images) |
| `display/services.php` | Services, moyens de paiement, réseaux sociaux |
| `display/reviews.php` | Avis Google (si activé) |
| `display/reviews-premium-upgrade.php` | Bloc d'upsell pour les avis Google |
| `display/reservation.php` | Formulaire de réservation publique |
| `display/footer.php` | Contact, horaires, Google Maps, liens légaux |
| `display/cookies.php` | Bannière de consentement cookies (RGPD) |
| `display/lightbox.php` | Lightbox pour agrandir les images |

### Logique de visibilité du site

```
Si l'admin n'a PAS d'abonnement actif ET n'est PAS SUPER_ADMIN → site hors ligne
Si option site_online = 0 → site en maintenance
Si SUPER_ADMIN visite → toujours visible
Si admin propriétaire connecté → mode preview (visible même en maintenance)
Si mode démo → toujours visible
```

---

## 13. Système de paiement Stripe

**Contrôleur :** `StripeController`

### Intégration technique
- API Stripe via **cURL natif** (pas de SDK PHP Stripe)
- Clés de test : `sk_test_...` / `pk_test_...`
- Carte de test : `4242 4242 4242 4242`

### Types de checkout

| Type | Route | Metadata `type` | Description |
|------|-------|-----------------|-------------|
| Basique | POST `stripe-checkout` | `basique` | Abonnement de base (11,99€/mois) |
| Basique + Premium | POST `stripe-checkout` (include_basique=1) | `basique_premium` | Basique + options sélectionnées |
| Premium seul | POST `stripe-checkout` (features) | `premium` | Options à la carte |
| Pack Full | POST `stripe-checkout` (pack_full) | `pack_full` | Tout inclus (29,99€/mois) |

### Flux de paiement
1. L'admin choisit son plan dans les paramètres (`?page=settings&section=premium`)
2. POST vers `?page=stripe-checkout` → création d'une session Stripe Checkout
3. Redirection vers la page Stripe
4. Retour sur `?page=stripe-success&session_id=...` → activation de l'abonnement
5. Le webhook (`?page=stripe-webhook`) peut aussi traiter les événements Stripe

### Tarification

| Offre | Mensuel | Annuel |
|-------|---------|--------|
| Basique | 11,99€ | 9,99€/mois |
| Avis Google | 3,99€ | 2,99€/mois |
| Statistiques | 3,99€ | 2,99€/mois |
| Réservations | 10,99€ | 8,99€/mois |
| Livraison | 3,99€ | 2,99€/mois |
| **Pack Full** | **29,99€** | **22,99€/mois** |

### Gestion des abonnements
- **Annulation** (`stripe-cancel`) : Marque l'abonnement comme annulé
- **Réactivation** (`stripe-reactivate`) : Réactive un abonnement annulé

---

## 14. Fonctionnalités Premium

**Modèle :** `PremiumFeature`

### Features disponibles

| Clé | Nom | Prix | Description |
|-----|-----|------|-------------|
| `google_reviews` | Avis Google | 3,99€/mois | Affichage des avis Google Places sur le site vitrine |
| `advanced_analytics` | Statistiques avancées | 3,99€/mois | Dashboard de stats (visites, appareils, navigateurs, heures) |
| `online_booking` | Réservations en ligne | 10,99€/mois | Formulaire de réservation sur le site vitrine + gestion back-office |
| `delivery_integration` | Intégration livraison | 3,99€/mois | Liens vers plateformes de livraison |

### Mode BETA
Quand `BETA_MODE === true` : `PremiumFeature::isEnabled()` retourne **toujours `true`** pour toutes les features.

### Logique d'accès
```
SUPER_ADMIN → toujours accès à tout
Feature activée (is_active = 1) ET non expirée (expires_at > now ou null) → accès
BETA_MODE === true → accès à tout
Sinon → redirection vers section premium des paramètres
```

### Avis Google (`google_reviews`)
**Modèle :** `GoogleReviews`
- Utilise la **Places API (New)** de Google
- Cache en base (1h) pour réduire les appels API
- Configuration : Google Place ID + API Key dans les options admin
- Affichage : note moyenne, nombre d'avis, liste des 5 derniers avis

### Statistiques avancées (`advanced_analytics`)
**Contrôleur :** `StatsController`  
**Modèle :** `SiteVisit`

Données collectées (anonymisées via hash IP+UA) :
- Visites totales et visiteurs uniques
- Tendance (hausse/baisse en %)
- Visites par jour (graphique)
- Répartition par appareil (mobile/desktop/tablet)
- Répartition par navigateur
- Top référents
- Visites par heure
- Visites par jour de la semaine

Anti-spam : max 1 visite par visiteur par minute.

### Réservations en ligne (`online_booking`)
**Contrôleur :** `ReservationController`  
**Modèle :** `Reservation`  
**Vue admin :** `admin/reservations.php` (91 Ko)

#### Côté public (formulaire sur le site vitrine)
- Formulaire : nom, téléphone, email, date, heure, nombre de personnes, demandes spéciales
- Rate limiting : 10 réservations/heure par IP
- Validation côté serveur
- Email de confirmation au client + notification au restaurant

#### Côté admin (gestion)
- Liste des réservations avec filtres (date, statut)
- Statuts : `pending` → `confirmed` / `rejected` / `completed` / `cancelled` / `no_show`
- Actions : confirmer, refuser, marquer terminée, no-show
- Email de notification au client à chaque changement de statut
- Paramètres : taille min/max du groupe, jours à l'avance, message personnalisé, auto-complétion

#### Notifications temps réel (SSE)
**Contrôleur :** `NotificationStreamController`
- Server-Sent Events pour les notifications en temps réel
- Vérifie toutes les 3 secondes les nouvelles réservations en attente
- Heartbeat toutes les 15 secondes
- Libère la session immédiatement (`session_write_close()`)

---

## 15. Système de démonstration

**Modèle :** `DemoToken`

### Fonctionnement
1. Le SUPER_ADMIN génère un lien de démo depuis le dashboard
2. Un **clone complet** du restaurant `demo-menucraft` est créé :
   - Clone du restaurant (nouveau slug : `demo-menucraft-XXXXXXXX`)
   - Clone de l'admin (username : `demo_XXXXXXXX`)
   - Clone des catégories, plats, images, options, contacts, menus du jour
3. Un token unique est généré (validité : **3 jours**)
4. Le lien peut être partagé : `?page=demo&token=XXXXX`

### Accès démo
1. Le visiteur clique sur le lien
2. Le token est validé (existence, non expiré)
3. Une session est créée avec :
   - `$_SESSION['admin_logged'] = true`
   - `$_SESSION['admin_id']` = ID du clone
   - `$_SESSION['demo_mode'] = true`
   - `$_SESSION['demo_token']` = le token
4. Redirection vers le dashboard du clone

### Restrictions en mode démo
- Modification du profil bloquée
- Paiement Stripe bloqué
- Actions via `BaseController::blockIfDemo()` interdites

### Nettoyage
À la déconnexion (`demo-logout`), toutes les données du clone sont supprimées :
- Admin clone, restaurant clone, catégories, plats, images, options, contacts, menus du jour, images uploadées

---

## 16. CRON Jobs

### `cron/auto_complete_reservations.php`
- **Fréquence :** toutes les 15 minutes
- **Action :** marque automatiquement les réservations confirmées comme "terminées" si la date/heure est passée
- **Condition :** uniquement pour les admins ayant activé `booking_auto_complete`

### `cron/send_reminders.php`
- **Fréquence :** mensuelle
- **Action :** envoie un email de rappel aux admins ayant activé `mail_reminder`
- **Contenu :** invite à mettre à jour la carte du restaurant
- **Logs :** `cron/logs/reminders.log`

---

## 17. Emails et notifications

### Classes impliquées
- **`Mailer`** (`app/Helpers/Mailer.php`) : envoi d'emails HTML via `mail()` natif
- **`NotificationService`** (`app/Services/NotificationService.php`) : envoi groupé aux admins avec notifications activées

### Emails envoyés

| Déclencheur | Destinataire | Contenu |
|------------|-------------|---------|
| Inscription libre | Nouvel admin | Email de vérification avec token |
| Email vérifié | Admin | Confirmation d'activation du compte |
| Invitation | Invité | Lien d'inscription avec token |
| Demande reset MDP | Admin | Lien de réinitialisation |
| Nouvelle réservation | Restaurant | Détails de la réservation |
| Confirmation réservation | Client | Confirmation avec détails |
| Refus réservation | Client | Notification de refus |
| Annulation réservation | Client | Notification d'annulation |
| Rappel mensuel (CRON) | Admins avec option | Invitation à mettre à jour la carte |

### Logs
Tous les emails sont journalisés dans `cron/logs/mail.log`.

---

## 18. SEO et pages légales

### SEO
- **Meta tags** : title, description, keywords par restaurant
- **Open Graph** : og:title, og:description, og:type, og:image
- **Schema.org** : Données structurées Restaurant (dans `display/head.php`)
- **Sitemap XML** dynamique (`?page=sitemap.xml`) : liste tous les restaurants en ligne + pages légales
- **URLs propres** : `?page=display&slug=mon-restaurant`

### Pages légales (`?page=legal&section=`)
**Contrôleur :** `LegalController`

| Section | Contenu |
|---------|---------|
| `cgu` | Conditions Générales d'Utilisation |
| `privacy` | Politique de Confidentialité (RGPD) |
| `cookies` | Politique des Cookies |
| `legal` | Mentions Légales |

Contenu généré en HTML directement dans le contrôleur.

### Gestion des cookies (RGPD)
- Bannière de consentement cookies sur le site vitrine (`display/cookies.php`)
- Bannière dans le back-office (`partials/cookie-banner.php`)
- Script JS dédié (`assets/js/display/cookies.js`)

---

## 19. Assets et templates visuels

### Palettes CSS
Chaque palette définit des variables CSS qui contrôlent les couleurs du site vitrine :
- `classic` : ambre/brun chaleureux
- `modern` : bleu/gris contemporain
- `elegant` : noir/or luxueux
- `nature` : vert/terre naturel
- `rose` : rose/pastel féminin
- `bistro` : rouge/brun traditionnel
- `ocean` : bleu/turquoise marin

### Layouts
Les layouts modifient la disposition des sections du site vitrine :
- `standard` : disposition verticale classique
- `bistro` : accent sur l'ambiance et les photos
- `ocean` : éléments visuels aquatiques

### Dark mode
La page landing supporte un dark mode (script chargé en priorité pour éviter le flash).

---

## 20. Tests

### Configuration
- **PHPUnit 10.5.x** via Composer
- **Config :** `phpunit.xml`
- **Base de test :** `menucraft_test`
- **Bootstrap :** `tests/bootstrap.php` (crée le schéma complet de test)

### Suites de tests

| Suite | Chemin | Description |
|-------|--------|-------------|
| Unit | `tests/Unit/` | Tests unitaires des modèles |
| Functional | `tests/Functional/` | Tests d'accès aux routes HTTP |
| Security | `tests/Security/` | Tests SQL injection et XSS |
| Load | `tests/load/` | Tests de charge k6 |

### Exécution
```bash
php vendor/bin/phpunit                    # Tous les tests
php vendor/bin/phpunit --testsuite Unit   # Tests unitaires uniquement
```

---

## Résumé des rôles

| Fonctionnalité | Public | ADMIN | SUPER_ADMIN |
|---------------|--------|-------|-------------|
| Page landing | ✅ | ✅ | ✅ |
| Site vitrine restaurant | ✅ | ✅ | ✅ |
| Inscription / Connexion | ✅ | — | — |
| Dashboard | — | ✅ | ✅ |
| Édition carte | — | ✅ | ✅ |
| Édition contact | — | ✅ | ✅ |
| Logo / Bannière | — | ✅ | ✅ |
| Services / Paiements / Réseaux | — | ✅ | ✅ |
| Choix template | — | ✅ | ✅ |
| Paramètres profil / MDP | — | ✅ | ✅ |
| Plan de salle | — | ✅ | ✅ |
| Feedback beta | — | ✅ | ✅ |
| Paiement Stripe | — | ✅ | — |
| Options premium | — | ✅ (payant) | — |
| Envoi d'invitations | — | — | ✅ |
| Gestion des clients | — | — | ✅ |
| Dashboard feedbacks | — | — | ✅ |
| Génération de démos | — | — | ✅ |
| Activation manuelle abos | — | — | ✅ |

---

> **Ce document couvre l'intégralité des fonctionnalités de MenuCraft.**  
> Il peut servir de base pour recréer l'application dans une structure plus propre (ex : framework Laravel, architecture hexagonale, API REST + SPA frontend).
