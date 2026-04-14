# 📋 Migration V1 → V2 - Plan Complet MenuMiam

**Date de création** : 14 avril 2026  
**Version** : 2.0  
**Objectif** : Migrer TOUTES les fonctionnalités de la V1 vers la V2 avec une architecture moderne

---

## 🎯 Vue d'ensemble

Ce document liste **TOUTES** les fonctionnalités de MenuMiam V1 qui doivent être migrées vers la V2. Chaque fonctionnalité est classée par phase d'implémentation.

---

## ✅ Phase 1 : Authentification (TERMINÉE)

### Fonctionnalités
- ✅ Inscription (register)
- ✅ Connexion (login)
- ✅ Déconnexion (logout)
- ✅ Gestion des sessions
- ✅ Tokens CSRF
- ✅ Validation des formulaires
- ✅ Messages flash (success/error)

### Tables utilisées
- `admins` (username, email, password, role, restaurant_name, slug)

---

## ✅ Phase 2 : Dashboard & Settings (TERMINÉE)

### Fonctionnalités
- ✅ Dashboard admin
- ✅ Modification du profil (username, email, restaurant_name)
- ✅ Génération automatique du slug

### Tables utilisées
- `admins`

---

## 🔄 Phase 3 : Gestion de la Carte - Mode Éditable (EN COURS)

### ✅ Déjà implémenté
- ✅ CRUD Catégories (create, read, update, delete)
- ✅ CRUD Plats (create, read, update, delete)
- ✅ Upload images catégories
- ✅ Upload images plats
- ✅ Gestion des allergènes (14 allergènes pré-remplis)
- ✅ Association plats ↔ allergènes
- ✅ Lightbox pour afficher images en grand
- ✅ Accordéon pour ouvrir/fermer plats
- ✅ Prévisualisation images avant upload
- ✅ Affichage images dans la liste

### ❌ À implémenter (Phase 3 suite)
- ❌ **Drag & Drop** pour réorganiser catégories
- ❌ **Drag & Drop** pour réorganiser plats
- ❌ **Filtres/Recherche** pour trouver rapidement un plat
- ❌ **Statistiques** (nombre total catégories, plats, etc.)
- ❌ **Uniformiser tailles images** (variables CSS globales)
- ❌ **Export PDF** de la carte
- ❌ **Duplication** de catégorie/plat

### Tables utilisées
- `categories` (admin_id, name, image, display_order)
- `dishes` (category_id, name, description, price, image, display_order)
- `allergens` (nom, icone)
- `dish_allergens` (dish_id, allergen_id)

---

## ❌ Phase 3b : Gestion de la Carte - Mode Images (NON COMMENCÉE)

### Fonctionnalités à implémenter
- ❌ **Switcher** entre mode "carte" et mode "images" (carte_mode)
- ❌ **Upload multiple** d'images de carte (card_images)
- ❌ **Drag & Drop** pour réorganiser les images
- ❌ **Suppression** d'images
- ❌ **Prévisualisation** des images uploadées
- ❌ **Galerie** d'images sur la vitrine
- ❌ **Zoom** sur les images (lightbox)

### Tables utilisées
- `admins` (carte_mode : 'carte' ou 'images')
- `card_images` (admin_id, filename, original_name, display_order)

### Models à créer
- `CardImage.php` (existe déjà mais pas utilisé)

### Controllers à créer
- `CardImageController.php` (ou ajouter méthodes dans CardController)

---

## ❌ Phase 4 : Gestion du Contact (NON COMMENCÉE)

### Fonctionnalités à implémenter
- ❌ **Téléphone** (fixe)
- ❌ **Mobile**
- ❌ **Email** de contact
- ❌ **Adresse** complète
- ❌ **Horaires** d'ouverture (JSON : lundi-dimanche avec horaires matin/soir)
- ❌ **Réseaux sociaux** (Facebook, Instagram, Twitter/X)
- ❌ **Affichage** sur la vitrine
- ❌ **Google Maps** intégration (optionnel)

### Tables utilisées
- `contact` (admin_id, telephone, mobile, email, adresse, horaires, facebook, instagram, twitter)

### Models à créer
- `Contact.php`

### Controllers à créer
- `ContactController.php`

### Vues à créer
- `admin/edit-contact.php`
- `display/contact.php` (section sur la vitrine)

---

## ❌ Phase 5 : Admin Options & Personnalisation (NON COMMENCÉE)

### Fonctionnalités à implémenter

#### 5.1 Services proposés
- ❌ Sur place
- ❌ À emporter
- ❌ Livraison Uber Eats
- ❌ Livraison par l'établissement
- ❌ WiFi gratuit
- ❌ Climatisation
- ❌ Accès PMR

#### 5.2 Moyens de paiement
- ❌ Visa
- ❌ Mastercard
- ❌ Carte Bleue
- ❌ Espèces
- ❌ Chèques
- ❌ Tickets restaurant

#### 5.3 Templates/Thèmes
- ❌ Choix du template (classic, modern, elegant, etc.)
- ❌ Personnalisation des couleurs
- ❌ Upload logo
- ❌ Upload bannière

#### 5.4 Paramètres généraux
- ❌ Site en ligne / hors ligne (site_online)
- ❌ Notifications par email
- ❌ Langue du site

### Tables utilisées
- `admin_options` (admin_id, option_name, option_value)

### Models à créer
- `AdminOption.php`

### Controllers à modifier
- `SettingsController.php` (ajouter méthodes pour options)

### Vues à modifier
- `admin/settings.php` (ajouter sections pour services, paiements, templates)

---

## ❌ Phase 6 : Menus du Jour / Formules (NON COMMENCÉE)

### Fonctionnalités à implémenter
- ❌ **Créer** un menu du jour
- ❌ **Modifier** un menu
- ❌ **Supprimer** un menu
- ❌ **Activer/Désactiver** un menu
- ❌ **Items du menu** (JSON : [{label: "Entrée", value: "Salade"}, ...])
- ❌ **Prix** du menu
- ❌ **Description** du menu
- ❌ **Affichage** sur la vitrine

### Tables utilisées
- `daily_menus` (admin_id, title, description, price, items, is_active)

### Models à créer
- `DailyMenu.php`

### Controllers à créer
- `DailyMenuController.php`

### Vues à créer
- `admin/edit-daily-menus.php`
- `display/daily-menus.php` (section sur la vitrine)

---

## ❌ Phase 7 : Réservations en Ligne (PREMIUM - NON COMMENCÉE)

### Fonctionnalités à implémenter

#### 7.1 Gestion des réservations
- ❌ **Formulaire** de réservation public
- ❌ **Validation** des créneaux disponibles
- ❌ **Confirmation** par email
- ❌ **Statuts** : pending, confirmed, cancelled, completed, no_show
- ❌ **Dashboard** des réservations (aujourd'hui, à venir, passées)
- ❌ **Notifications** admin

#### 7.2 Gestion des tables
- ❌ **Salles** (floors)
- ❌ **Tables** (numéro, capacité, forme)
- ❌ **Plan de salle** interactif (drag & drop)
- ❌ **Éléments décoratifs** (floor_elements)
- ❌ **Attribution** automatique des tables

#### 7.3 Paramètres réservations
- ❌ **Créneaux horaires** disponibles
- ❌ **Nombre de personnes** min/max
- ❌ **Délai de réservation** (ex: 2h à l'avance)
- ❌ **Durée** d'une réservation
- ❌ **Jours de fermeture**

### Tables utilisées
- `reservations` (admin_id, table_id, customer_name, customer_email, customer_phone, reservation_date, reservation_time, party_size, special_requests, status)
- `floors` (admin_id, name, display_order)
- `tables` (floor_id, admin_id, table_number, capacity, shape, position_x, position_y)
- `floor_elements` (floor_id, element_type, label, position_x, position_y, width, height)

### Models à créer
- `Reservation.php`
- `Floor.php`
- `Table.php`
- `FloorElement.php`

### Controllers à créer
- `ReservationController.php`
- `FloorPlanController.php`

### Vues à créer
- `admin/reservations.php`
- `admin/floor-plan.php`
- `display/reservation-form.php`

---

## ❌ Phase 8 : Abonnements & Fonctionnalités Premium (NON COMMENCÉE)

### Fonctionnalités à implémenter

#### 8.1 Gestion des abonnements
- ❌ **Plans** : Basique (gratuit) vs Premium (payant)
- ❌ **Statuts** : active, inactive, cancelled, expired
- ❌ **Prix** par mois
- ❌ **Date d'expiration**
- ❌ **Paiement** (Stripe/PayPal)

#### 8.2 Fonctionnalités premium
- ❌ **Réservations en ligne**
- ❌ **Statistiques avancées**
- ❌ **Menus du jour illimités**
- ❌ **Templates premium**
- ❌ **Support prioritaire**
- ❌ **Suppression publicité MenuMiam**

### Tables utilisées
- `client_subscriptions` (admin_id, plan_type, status, price_per_month, started_at, expires_at)
- `premium_features` (admin_id, feature_name, is_active, activated_at)

### Models à créer
- `ClientSubscription.php`
- `PremiumFeature.php`

### Controllers à créer
- `SubscriptionController.php`

### Vues à créer
- `admin/subscription.php`
- `admin/upgrade-premium.php`

---

## ❌ Phase 9 : Analytics & Statistiques (NON COMMENCÉE)

### Fonctionnalités à implémenter
- ❌ **Tracking des visites** (visitor_hash, device_type, browser, page_path)
- ❌ **Dashboard analytics** (visites/jour, visiteurs uniques, pages vues)
- ❌ **Graphiques** (Chart.js)
- ❌ **Filtres** par période (7j, 30j, 90j, 1an)
- ❌ **Export** des données (CSV)

### Tables utilisées
- `site_visits` (admin_id, visitor_hash, device_type, browser, page_path, visited_at)

### Models à créer
- `SiteVisit.php`

### Controllers à créer
- `AnalyticsController.php`

### Vues à créer
- `admin/analytics.php`

---

## ❌ Phase 10 : Fonctionnalités Diverses (NON COMMENCÉE)

### 10.1 Invitations
- ❌ **Système d'invitation** pour nouveaux restaurateurs
- ❌ **Génération de tokens**
- ❌ **Expiration** des invitations
- ❌ **Tracking** des invitations utilisées

### 10.2 Dates de fermeture
- ❌ **Gestion** des dates de fermeture exceptionnelles
- ❌ **Affichage** sur la vitrine
- ❌ **Blocage** des réservations ces jours-là

### 10.3 Tokens de démo
- ❌ **Génération** de tokens temporaires
- ❌ **Expiration** automatique
- ❌ **Nettoyage** des données de démo

### Tables utilisées
- `invitations` (email, restaurant_name, token, expiry, used)
- `closure_dates` (admin_id, date, reason)
- `demo_tokens` (token, expires_at)

---

## 📊 Récapitulatif

### Phases terminées
- ✅ Phase 1 : Authentification
- ✅ Phase 2 : Dashboard & Settings

### Phases en cours
- 🔄 Phase 3 : Carte mode éditable (70% terminé)

### Phases à venir
- ❌ Phase 3b : Carte mode images
- ❌ Phase 4 : Contact
- ❌ Phase 5 : Admin Options
- ❌ Phase 6 : Menus du jour
- ❌ Phase 7 : Réservations (Premium)
- ❌ Phase 8 : Abonnements
- ❌ Phase 9 : Analytics
- ❌ Phase 10 : Fonctionnalités diverses

### Progression globale
**~15% terminé** (2/12 phases complètes)

---

## 🎯 Prochaines étapes

1. **Terminer Phase 3** : Drag & drop, filtres, statistiques
2. **Phase 3b** : Mode images
3. **Phase 4** : Contact
4. **Phase 5** : Admin Options
5. **Phase 6** : Menus du jour
6. **Phase 7+** : Fonctionnalités premium

---

## 📝 Notes importantes

- Toutes les tables existent déjà dans le schéma SQL (`001_create_initial_schema.sql`)
- Les Models manquants doivent être créés au fur et à mesure
- Respecter l'architecture MVC et PSR-4
- Utiliser AJAX pour toutes les interactions
- Responsive design obligatoire
- Dark mode à prévoir (optionnel)

---

**Dernière mise à jour** : 14 avril 2026
