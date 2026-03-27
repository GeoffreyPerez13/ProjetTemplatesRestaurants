# 🗄️ Modélisation Base de Données (MERISE) - MenuMiam
## De l'Existant vers la V2

---

## 📋 **SOMMAIRE**
1. Règles de gestion
2. MCD (Modèle Conceptuel de Données)
3. MLD (Modèle Logique de Données)
4. Dictionnaire de données
5. Schéma relationnel existant (V1)
6. Schéma relationnel cible (V2)
7. Stratégie de migration V1 → V2
8. Scripts SQL de création
9. Optimisations et indexation

---

## 📜 **1. RÈGLES DE GESTION**

### **RG1 - Utilisateurs et Restaurants**
- Un **administrateur** possède **un ou plusieurs** restaurants
- Un **restaurant** est géré par **un seul** administrateur (propriétaire)
- Un **restaurant** possède **un slug unique** pour son URL publique
- Un **administrateur** a un rôle : `SUPER_ADMIN` ou `ADMIN`
- Un **SUPER_ADMIN** gère la plateforme et peut inviter des admins
- Un **ADMIN** gère uniquement son/ses restaurant(s)

### **RG2 - Carte et Contenu**
- Un **restaurant** possède **une carte** composée de **catégories**
- Une **catégorie** contient **zéro ou plusieurs plats**
- Un **plat** appartient à **une seule catégorie**
- Un **plat** peut contenir **zéro ou plusieurs allergènes**
- Un **allergène** peut concerner **zéro ou plusieurs plats**
- La relation plat-allergène est une association **N:N**

### **RG3 - Menus du Jour**
- Un **restaurant** peut proposer **zéro ou plusieurs menus du jour**
- Un **menu du jour** contient des **items** stockés en JSON (label + valeur)
- Un **menu du jour** peut être **actif ou inactif**

### **RG4 - Médias**
- Un **restaurant** possède **au plus un logo** et **au plus une bannière**
- Un **restaurant** en mode images possède **zéro ou plusieurs images de carte**
- Les images de carte ont un **ordre d'affichage**

### **RG5 - Contact et Services**
- Un **restaurant** possède **une fiche contact** (téléphone, email, adresse, horaires)
- Les **options** (services, paiements, réseaux sociaux, paramètres) sont stockées en **clé-valeur**

### **RG6 - Abonnements et Premium**
- Un **administrateur** possède **un abonnement** (basique par défaut)
- Un **abonnement** a un statut : `active`, `inactive`, `cancelled`, `expired`
- Un **administrateur** peut activer **zéro ou plusieurs fonctionnalités premium**
- Les fonctionnalités premium sont : `google_reviews`, `advanced_analytics`, `online_booking`, `delivery_integration`

### **RG7 - Réservations**
- Un **restaurant** reçoit **zéro ou plusieurs réservations** (si premium activé)
- Une **réservation** a un statut : `pending`, `confirmed`, `cancelled`, `completed`, `no_show`
- Une **réservation** contient les infos client et la date/heure souhaitée

### **RG8 - Analytics**
- Chaque **visite** sur une vitrine est enregistrée de manière **anonymisée** (hash IP+UA)
- Les visites sont rattachées à un **restaurant** via `admin_id`

### **RG9 - Démo et Invitations**
- Un **SUPER_ADMIN** peut générer des **tokens de démo** temporaires
- Un **token de démo** crée un **clone isolé** du restaurant template
- Une **invitation** permet à un nouveau restaurateur de s'inscrire

---

## 🔷 **2. MODÈLE CONCEPTUEL DE DONNÉES (MCD)**

### **2.1 MCD V1 (État Actuel)**

```
                                    ┌────────────────────┐
                                    │    INVITATION       │
                                    │────────────────────│
                                    │ email              │
                                    │ restaurant_name    │
                                    │ token              │
                                    │ expiry             │
                                    │ used               │
                                    └────────────────────┘

┌────────────────────┐    1,1     ┌────────────────────┐     0,N    ┌────────────────────┐
│    RESTAURANT       │◄─────────│       ADMIN         │──────────►│  ADMIN_OPTION       │
│────────────────────│  possède  │────────────────────│  configure│────────────────────│
│ name               │           │ username           │           │ option_name        │
│ slug               │           │ email              │           │ option_value       │
└────────────────────┘           │ password           │           └────────────────────┘
        │                        │ role               │
        │                        │ carte_mode         │
        │                        │ restaurant_name    │
        │                        └────────────────────┘
        │                              │         │          │           │
        │ 0,N                    0,1   │    0,1  │     1,1  │      0,N  │
        ▼                              ▼         ▼          ▼           ▼
┌────────────────┐           ┌──────────┐ ┌──────────┐ ┌──────────────────┐ ┌──────────────┐
│   CATEGORIE    │           │   LOGO   │ │ BANNIERE │ │ CLIENT_SUBSCRIPT.│ │ PREMIUM_FEAT.│
│────────────────│           │──────────│ │──────────│ │──────────────────│ │──────────────│
│ name           │           │ filename │ │ filename │ │ plan_type        │ │ feature_name │
│ image          │           └──────────┘ │ text     │ │ status           │ │ is_active    │
│ display_order  │                        └──────────┘ │ price_per_month  │ └──────────────┘
└────────────────┘                                     │ expires_at       │
        │                                              └──────────────────┘
        │ 0,N
        ▼
┌────────────────┐                    ┌────────────────┐
│      PLAT      │                    │   ALLERGENE    │
│────────────────│         N:N        │────────────────│
│ name           │◄──────────────────►│ nom            │
│ description    │    contient        │ icone          │
│ price          │                    └────────────────┘
│ image          │
└────────────────┘

        ┌────────────────────┐            ┌────────────────────┐
        │    RESERVATION     │            │    DAILY_MENU      │
        │────────────────────│            │────────────────────│
        │ customer_name      │            │ title              │
        │ customer_phone     │            │ description        │
        │ reservation_date   │            │ price              │
        │ reservation_time   │            │ items (JSON)       │
        │ party_size         │            │ is_active          │
        │ status             │            └────────────────────┘
        └────────────────────┘

┌────────────────────┐            ┌────────────────────┐
│    CARD_IMAGE      │            │    SITE_VISIT      │
│────────────────────│            │────────────────────│
│ filename           │            │ visitor_hash       │
│ original_name      │            │ device_type        │
│ display_order      │            │ browser            │
└────────────────────┘            │ page_path          │
                                  └────────────────────┘

┌────────────────────┐            ┌────────────────────┐
│    DEMO_TOKEN      │            │     CONTACT        │
│────────────────────│            │────────────────────│
│ token              │            │ telephone          │
│ expires_at         │            │ email              │
│ label              │            │ adresse            │
└────────────────────┘            │ horaires           │
                                  └────────────────────┘
```

### **2.2 Cardinalités Détaillées (V1)**

| Association                     | Entité A       | Card. A | Entité B          | Card. B |
|---------------------------------|----------------|---------|-------------------|---------|
| Possède (restaurant)            | ADMIN          | 1,1     | RESTAURANT        | 0,1     |
| Configure (options)             | ADMIN          | 0,N     | ADMIN_OPTION      | 1,1     |
| Possède (logo)                  | ADMIN          | 0,1     | LOGO              | 1,1     |
| Possède (bannière)              | ADMIN          | 0,1     | BANNIERE          | 1,1     |
| Souscrit (abonnement)           | ADMIN          | 1,1     | CLIENT_SUBSCRIPTION| 1,1    |
| Active (premium)                | ADMIN          | 0,N     | PREMIUM_FEATURE   | 1,1     |
| Crée (catégorie)                | ADMIN          | 0,N     | CATEGORIE         | 1,1     |
| Contient (plat)                 | CATEGORIE      | 0,N     | PLAT              | 1,1     |
| Contient (allergène)            | PLAT           | 0,N     | ALLERGENE         | 0,N     |
| Reçoit (réservation)            | ADMIN          | 0,N     | RESERVATION       | 1,1     |
| Propose (menu du jour)          | ADMIN          | 0,N     | DAILY_MENU        | 1,1     |
| Possède (image carte)           | ADMIN          | 0,N     | CARD_IMAGE        | 1,1     |
| Enregistre (visite)             | ADMIN          | 0,N     | SITE_VISIT        | 1,1     |
| Possède (contact)               | ADMIN          | 0,1     | CONTACT           | 1,1     |
| Génère (token démo)             | ADMIN          | 0,N     | DEMO_TOKEN        | 1,1     |

### **2.3 MCD V2 (Cible Commerciale)**

Le MCD V2 introduit les évolutions suivantes par rapport au V1 :

```
ÉVOLUTIONS MAJEURES :

1. ADMIN → USER (renommage + enrichissement)
   - Ajout : first_name, last_name, phone, last_login, avatar
   - Séparation claire des rôles via table dédiée

2. RESTAURANT enrichi
   - Ajout : latitude, longitude (géolocalisation)
   - Ajout : timezone, currency, language
   - Ajout : is_published (remplace site_online dans options)

3. Nouvelle entité : RESTAURANT_USER
   - Junction table : multi-users par restaurant
   - Rôles : owner, manager, employee
   - Permissions JSON granulaires

4. ADMIN_OPTION → RESTAURANT_SETTING
   - Rattaché au restaurant (et non à l'admin)
   - Permet multi-restaurants

5. CATEGORIE / PLAT enrichis
   - Ajout : is_hidden, updated_at sur plats
   - Ajout : nutritional_info JSON sur plats
   - Ajout : is_available (disponibilité temps réel)

6. Nouvelle entité : MENU_VERSION
   - Historique des versions de carte
   - Rollback possible

7. SITE_VISIT → ANALYTICS_EVENT
   - Enrichi : event_type, metadata JSON
   - Support multi-événements (vue, clic, réservation...)

8. Nouvelle entité : AUDIT_LOG
   - Traçabilité complète des actions admin
   - Conformité RGPD
```

```
┌────────────────────┐     N:N      ┌────────────────────┐
│       USER         │◄────────────►│    RESTAURANT       │
│────────────────────│              │────────────────────│
│ email              │  via         │ name               │
│ password_hash      │  RESTAURANT_ │ slug               │
│ first_name         │  USER        │ address            │
│ last_name          │              │ phone              │
│ phone              │              │ latitude           │
│ avatar             │              │ longitude          │
│ role               │              │ timezone           │
│ email_verified     │              │ currency           │
│ last_login         │              │ language           │
└────────────────────┘              │ is_published       │
                                    └────────────────────┘
                                           │
                     ┌─────────────────────┼─────────────────────┐
                     │                     │                     │
                0,N  ▼               0,N   ▼               0,N  ▼
        ┌────────────────┐    ┌────────────────┐    ┌────────────────────┐
        │   CATEGORIE    │    │  DAILY_MENU    │    │ RESTAURANT_SETTING │
        │────────────────│    │────────────────│    │────────────────────│
        │ name           │    │ title          │    │ setting_key        │
        │ description    │    │ description    │    │ setting_value      │
        │ image          │    │ price          │    └────────────────────┘
        │ display_order  │    │ items (JSON)   │
        │ is_hidden      │    │ is_active      │
        └────────────────┘    └────────────────┘
                │
           0,N  ▼
        ┌────────────────┐         N:N       ┌────────────────┐
        │      PLAT      │◄────────────────►│   ALLERGENE    │
        │────────────────│   contient        │────────────────│
        │ name           │                   │ nom            │
        │ description    │                   │ icone          │
        │ price          │                   └────────────────┘
        │ image          │
        │ is_available   │
        │ nutritional    │
        │ display_order  │
        └────────────────┘

        ┌────────────────────┐    ┌────────────────────┐    ┌────────────────────┐
        │    RESERVATION     │    │  ANALYTICS_EVENT   │    │    AUDIT_LOG       │
        │────────────────────│    │────────────────────│    │────────────────────│
        │ customer_name      │    │ event_type         │    │ user_id            │
        │ customer_email     │    │ visitor_hash       │    │ action             │
        │ customer_phone     │    │ device_type        │    │ entity_type        │
        │ reservation_date   │    │ metadata (JSON)    │    │ entity_id          │
        │ party_size         │    └────────────────────┘    │ old_values (JSON)  │
        │ status             │                              │ new_values (JSON)  │
        └────────────────────┘                              └────────────────────┘
```

---

## 🔶 **3. MODÈLE LOGIQUE DE DONNÉES (MLD)**

### **3.1 MLD V1 (Existant)**

Traduction directe du MCD V1 en schéma relationnel :

```
admins (
    #id,
    username,
    email,
    password,
    restaurant_name,
    restaurant_id => restaurants(id),
    carte_mode,
    role,
    reset_token,
    reset_token_expiry,
    email_verified,
    verification_token,
    created_at,
    updated_at
)

restaurants (
    #id,
    name,
    slug,
    created_at,
    updated_at
)

admin_options (
    #id,
    admin_id => admins(id),
    option_name,
    option_value,
    created_at,
    updated_at
    UNIQUE(admin_id, option_name)
)

categories (
    #id,
    admin_id => admins(id),
    name,
    image,
    restaurant_id,
    created_at,
    updated_at
)

plats (
    #id,
    category_id => categories(id),
    name,
    description,
    price,
    image,
    created_at
)

allergenes (
    #id,
    nom,
    icone,
    created_at
)

plat_allergenes (
    #plat_id => plats(id),
    #allergene_id => allergenes(id)
)

logos (
    #id,
    admin_id => admins(id) UNIQUE,
    filename,
    uploaded_at
)

banners (
    #id,
    admin_id => admins(id) UNIQUE,
    filename,
    text,
    uploaded_at,
    updated_at
)

contact (
    #id,
    admin_id => admins(id),
    telephone,
    email,
    adresse,
    horaires,
    updated_at
)

card_images (
    #id,
    admin_id,
    filename,
    original_name,
    display_order,
    created_at,
    updated_at
)

client_subscriptions (
    #id,
    admin_id => admins(id),
    plan_type,
    status,
    price_per_month,
    features_enabled,
    started_at,
    expires_at,
    billing_cycle_day,
    next_billing_date,
    is_grouped,
    prorata_amount,
    created_by => admins(id),
    notes,
    created_at,
    updated_at
)

premium_features (
    #id,
    admin_id => admins(id),
    feature_name,
    is_active,
    activated_at,
    prorata_amount,
    next_billing_date,
    created_at,
    updated_at
    UNIQUE(admin_id, feature_name)
)

reservations (
    #id,
    admin_id => admins(id),
    customer_name,
    customer_email,
    customer_phone,
    reservation_date,
    reservation_time,
    party_size,
    special_requests,
    status,
    admin_notes,
    cancelled_reason,
    confirmed_at,
    cancelled_at,
    created_at,
    updated_at
)

daily_menus (
    #id,
    admin_id => admins(id),
    title,
    description,
    price,
    items,
    is_active,
    display_order,
    created_at,
    updated_at
)

site_visits (
    #id,
    admin_id => admins(id),
    visitor_hash,
    user_agent,
    referrer,
    device_type,
    browser,
    page_path,
    visited_at
)

demo_tokens (
    #id,
    token,
    admin_id => admins(id),
    expires_at,
    created_by,
    label,
    created_at
)

invitations (
    #id,
    email,
    restaurant_name,
    token,
    expiry,
    used,
    created_at
)
```

### **3.2 MLD V2 (Cible)**

Évolutions principales pour la version commerciale :

```
users (
    #id,
    email UNIQUE,
    password_hash,
    first_name,
    last_name,
    phone,
    avatar,
    role ENUM('super_admin', 'admin'),
    email_verified BOOLEAN,
    verification_token,
    reset_token,
    reset_token_expiry,
    last_login,
    created_at,
    updated_at
)

restaurants (
    #id,
    name,
    slug UNIQUE,
    address,
    phone,
    email,
    latitude,
    longitude,
    timezone,
    currency,
    language,
    carte_mode ENUM('editable', 'images'),
    is_published BOOLEAN,
    logo_filename,
    banner_filename,
    banner_text,
    created_at,
    updated_at
)

restaurant_users (
    #id,
    restaurant_id => restaurants(id),
    user_id => users(id),
    role ENUM('owner', 'manager', 'employee'),
    permissions JSON,
    created_at
    UNIQUE(restaurant_id, user_id)
)

restaurant_settings (
    #id,
    restaurant_id => restaurants(id),
    setting_key,
    setting_value,
    created_at,
    updated_at
    UNIQUE(restaurant_id, setting_key)
)

categories (
    #id,
    restaurant_id => restaurants(id),
    name,
    description,
    image,
    display_order,
    is_hidden BOOLEAN,
    created_at,
    updated_at
)

items (
    #id,
    category_id => categories(id),
    name,
    description,
    price,
    image,
    display_order,
    is_available BOOLEAN,
    nutritional_info JSON,
    created_at,
    updated_at
)

allergens (
    #id,
    name,
    icon,
    created_at
)

item_allergens (
    #item_id => items(id),
    #allergen_id => allergens(id)
)

daily_menus (
    #id,
    restaurant_id => restaurants(id),
    title,
    description,
    price,
    items JSON,
    is_active BOOLEAN,
    display_order,
    created_at,
    updated_at
)

card_images (
    #id,
    restaurant_id => restaurants(id),
    filename,
    original_name,
    display_order,
    created_at,
    updated_at
)

subscriptions (
    #id,
    user_id => users(id),
    plan_type ENUM('basique'),
    status ENUM('active', 'inactive', 'cancelled', 'expired'),
    price_per_month,
    started_at,
    expires_at,
    next_billing_date,
    stripe_subscription_id,
    stripe_customer_id,
    created_by => users(id),
    notes,
    created_at,
    updated_at
)

premium_features (
    #id,
    user_id => users(id),
    feature_name,
    is_active BOOLEAN,
    activated_at,
    prorata_amount,
    next_billing_date,
    created_at,
    updated_at
    UNIQUE(user_id, feature_name)
)

reservations (
    #id,
    restaurant_id => restaurants(id),
    customer_name,
    customer_email,
    customer_phone,
    reservation_date,
    reservation_time,
    party_size,
    special_requests,
    status,
    admin_notes,
    cancelled_reason,
    confirmed_at,
    cancelled_at,
    created_at,
    updated_at
)

analytics_events (
    #id,
    restaurant_id => restaurants(id),
    event_type ENUM('page_view', 'menu_view', 'item_click', 'reservation', 'call_click'),
    visitor_hash,
    user_agent,
    referrer,
    device_type,
    browser,
    page_path,
    metadata JSON,
    created_at
)

audit_logs (
    #id,
    user_id => users(id),
    restaurant_id => restaurants(id),
    action,
    entity_type,
    entity_id,
    old_values JSON,
    new_values JSON,
    ip_address,
    created_at
)

invitations (
    #id,
    email,
    restaurant_name,
    token UNIQUE,
    expiry,
    used BOOLEAN,
    created_at
)

demo_tokens (
    #id,
    token UNIQUE,
    admin_id => users(id),
    expires_at,
    created_by => users(id),
    label,
    created_at
)
```

---

## 📖 **4. DICTIONNAIRE DE DONNÉES**

### **4.1 Table `users` (ex-admins)**

| Colonne             | Type           | Null | Défaut            | Description                           |
|---------------------|----------------|------|-------------------|---------------------------------------|
| id                  | INT AUTO_INCR  | NON  | -                 | Clé primaire                          |
| email               | VARCHAR(255)   | NON  | -                 | Email unique, login                   |
| password_hash       | VARCHAR(255)   | NON  | -                 | Mot de passe hashé bcrypt             |
| first_name          | VARCHAR(100)   | OUI  | NULL              | Prénom                                |
| last_name           | VARCHAR(100)   | OUI  | NULL              | Nom de famille                        |
| phone               | VARCHAR(20)    | OUI  | NULL              | Téléphone personnel                   |
| avatar              | VARCHAR(255)   | OUI  | NULL              | Photo de profil                       |
| role                | ENUM           | NON  | 'admin'           | super_admin, admin                    |
| email_verified      | BOOLEAN        | NON  | FALSE             | Email vérifié ?                       |
| verification_token  | VARCHAR(64)    | OUI  | NULL              | Token de vérification email           |
| reset_token         | VARCHAR(255)   | OUI  | NULL              | Token de reset mot de passe           |
| reset_token_expiry  | DATETIME       | OUI  | NULL              | Expiration du token reset             |
| last_login          | DATETIME       | OUI  | NULL              | Dernière connexion                    |
| created_at          | TIMESTAMP      | NON  | CURRENT_TIMESTAMP | Date de création                      |
| updated_at          | TIMESTAMP      | NON  | ON UPDATE         | Date de mise à jour                   |

### **4.2 Table `restaurants`**

| Colonne          | Type           | Null | Défaut            | Description                              |
|------------------|----------------|------|-------------------|------------------------------------------|
| id               | INT AUTO_INCR  | NON  | -                 | Clé primaire                             |
| name             | VARCHAR(255)   | NON  | -                 | Nom du restaurant                        |
| slug             | VARCHAR(255)   | NON  | -                 | Slug URL unique                          |
| address          | TEXT           | OUI  | NULL              | Adresse complète                         |
| phone            | VARCHAR(20)    | OUI  | NULL              | Téléphone du restaurant                  |
| email            | VARCHAR(255)   | OUI  | NULL              | Email du restaurant                      |
| latitude         | DECIMAL(10,8)  | OUI  | NULL              | Latitude GPS                             |
| longitude        | DECIMAL(11,8)  | OUI  | NULL              | Longitude GPS                            |
| timezone         | VARCHAR(50)    | NON  | 'Europe/Paris'    | Fuseau horaire                           |
| currency         | VARCHAR(3)     | NON  | 'EUR'             | Devise                                   |
| language         | VARCHAR(5)     | NON  | 'fr'              | Langue par défaut                        |
| carte_mode       | ENUM           | NON  | 'editable'        | editable, images                         |
| is_published     | BOOLEAN        | NON  | FALSE             | Vitrine visible publiquement ?           |
| logo_filename    | VARCHAR(255)   | OUI  | NULL              | Fichier logo                             |
| banner_filename  | VARCHAR(255)   | OUI  | NULL              | Fichier bannière                         |
| banner_text      | TEXT           | OUI  | NULL              | Texte de la bannière                     |
| created_at       | TIMESTAMP      | NON  | CURRENT_TIMESTAMP | Date de création                         |
| updated_at       | TIMESTAMP      | NON  | ON UPDATE         | Date de mise à jour                      |

### **4.3 Table `restaurant_users` (junction)**

| Colonne        | Type          | Null | Défaut     | Description                                  |
|----------------|---------------|------|------------|----------------------------------------------|
| id             | INT AUTO_INCR | NON  | -          | Clé primaire                                 |
| restaurant_id  | INT           | NON  | -          | FK → restaurants(id) ON DELETE CASCADE        |
| user_id        | INT           | NON  | -          | FK → users(id) ON DELETE CASCADE              |
| role           | ENUM          | NON  | 'employee' | owner, manager, employee                     |
| permissions    | JSON          | OUI  | NULL       | Permissions granulaires                      |
| created_at     | TIMESTAMP     | NON  | CURRENT_TIMESTAMP | Date d'attribution                    |

### **4.4 Table `categories`**

| Colonne        | Type          | Null | Défaut     | Description                                  |
|----------------|---------------|------|------------|----------------------------------------------|
| id             | INT AUTO_INCR | NON  | -          | Clé primaire                                 |
| restaurant_id  | INT           | NON  | -          | FK → restaurants(id) ON DELETE CASCADE        |
| name           | VARCHAR(255)  | NON  | -          | Nom de la catégorie                          |
| description    | TEXT          | OUI  | NULL       | Description optionnelle                      |
| image          | VARCHAR(255)  | OUI  | NULL       | Image de catégorie                           |
| display_order  | INT           | NON  | 0          | Ordre d'affichage                            |
| is_hidden      | BOOLEAN       | NON  | FALSE      | Catégorie masquée ?                          |
| created_at     | TIMESTAMP     | NON  | CURRENT_TIMESTAMP | Date de création                      |
| updated_at     | TIMESTAMP     | NON  | ON UPDATE  | Date de mise à jour                          |

### **4.5 Table `items` (ex-plats)**

| Colonne           | Type           | Null | Défaut     | Description                               |
|-------------------|----------------|------|------------|-------------------------------------------|
| id                | INT AUTO_INCR  | NON  | -          | Clé primaire                              |
| category_id       | INT            | NON  | -          | FK → categories(id) ON DELETE CASCADE      |
| name              | VARCHAR(255)   | NON  | -          | Nom du plat                               |
| description       | TEXT           | OUI  | NULL       | Description du plat                       |
| price             | DECIMAL(10,2)  | OUI  | NULL       | Prix (NULL = sur demande)                 |
| image             | VARCHAR(255)   | OUI  | NULL       | Photo du plat                             |
| display_order     | INT            | NON  | 0          | Ordre d'affichage                         |
| is_available      | BOOLEAN        | NON  | TRUE       | Disponible actuellement ?                 |
| nutritional_info  | JSON           | OUI  | NULL       | Infos nutritionnelles                     |
| created_at        | TIMESTAMP      | NON  | CURRENT_TIMESTAMP | Date de création                   |
| updated_at        | TIMESTAMP      | NON  | ON UPDATE  | Date de mise à jour                       |

### **4.6 Table `allergens` (ex-allergenes)**

| Colonne    | Type          | Null | Défaut            | Description                          |
|------------|---------------|------|-------------------|--------------------------------------|
| id         | INT AUTO_INCR | NON  | -                 | Clé primaire                         |
| name       | VARCHAR(100)  | NON  | -                 | Nom de l'allergène                   |
| icon       | VARCHAR(100)  | OUI  | NULL              | Classe CSS Font Awesome              |
| created_at | TIMESTAMP     | NON  | CURRENT_TIMESTAMP | Date de création                     |

### **4.7 Table `reservations`**

| Colonne           | Type          | Null | Défaut     | Description                               |
|-------------------|---------------|------|------------|-------------------------------------------|
| id                | INT AUTO_INCR | NON  | -          | Clé primaire                              |
| restaurant_id     | INT           | NON  | -          | FK → restaurants(id) ON DELETE CASCADE     |
| customer_name     | VARCHAR(100)  | NON  | -          | Nom du client                             |
| customer_email    | VARCHAR(255)  | OUI  | NULL       | Email du client                           |
| customer_phone    | VARCHAR(20)   | NON  | -          | Téléphone du client                       |
| reservation_date  | DATE          | NON  | -          | Date de réservation                       |
| reservation_time  | TIME          | NON  | -          | Heure de réservation                      |
| party_size        | INT           | NON  | 2          | Nombre de personnes                       |
| special_requests  | TEXT          | OUI  | NULL       | Demandes spéciales                        |
| status            | ENUM          | NON  | 'pending'  | pending, confirmed, cancelled, completed, no_show |
| admin_notes       | TEXT          | OUI  | NULL       | Notes internes                            |
| cancelled_reason  | VARCHAR(255)  | OUI  | NULL       | Raison d'annulation                       |
| confirmed_at      | DATETIME      | OUI  | NULL       | Date de confirmation                      |
| cancelled_at      | DATETIME      | OUI  | NULL       | Date d'annulation                         |
| created_at        | TIMESTAMP     | NON  | CURRENT_TIMESTAMP | Date de création                   |
| updated_at        | TIMESTAMP     | NON  | ON UPDATE  | Date de mise à jour                       |

### **4.8 Table `analytics_events` (ex-site_visits)**

| Colonne        | Type              | Null | Défaut     | Description                              |
|----------------|-------------------|------|------------|------------------------------------------|
| id             | BIGINT AUTO_INCR  | NON  | -          | Clé primaire                             |
| restaurant_id  | INT               | NON  | -          | FK → restaurants(id)                      |
| event_type     | ENUM              | NON  | 'page_view'| Type : page_view, menu_view, item_click, reservation, call_click |
| visitor_hash   | VARCHAR(64)       | NON  | -          | Hash SHA-256 IP+UA (RGPD compliant)      |
| user_agent     | VARCHAR(512)      | OUI  | NULL       | User-Agent du navigateur                 |
| referrer       | VARCHAR(1024)     | OUI  | NULL       | Page d'origine                           |
| device_type    | ENUM              | NON  | 'desktop'  | desktop, mobile, tablet                  |
| browser        | VARCHAR(64)       | OUI  | NULL       | Navigateur détecté                       |
| page_path      | VARCHAR(255)      | OUI  | '/'        | Page visitée                             |
| metadata       | JSON              | OUI  | NULL       | Données contextuelles additionnelles     |
| created_at     | TIMESTAMP         | NON  | CURRENT_TIMESTAMP | Date de l'événement               |

### **4.9 Table `audit_logs` (nouvelle)**

| Colonne        | Type          | Null | Défaut            | Description                           |
|----------------|---------------|------|-------------------|---------------------------------------|
| id             | BIGINT AUTO_INCR | NON | -               | Clé primaire                          |
| user_id        | INT           | OUI  | NULL              | FK → users(id) ON DELETE SET NULL      |
| restaurant_id  | INT           | OUI  | NULL              | FK → restaurants(id) ON DELETE SET NULL |
| action         | VARCHAR(100)  | NON  | -                 | Action effectuée (create, update, delete, login...) |
| entity_type    | VARCHAR(100)  | OUI  | NULL              | Type d'entité concernée               |
| entity_id      | INT           | OUI  | NULL              | ID de l'entité concernée              |
| old_values     | JSON          | OUI  | NULL              | Valeurs avant modification            |
| new_values     | JSON          | OUI  | NULL              | Valeurs après modification            |
| ip_address     | VARCHAR(45)   | OUI  | NULL              | Adresse IP (IPv4 ou IPv6)             |
| created_at     | TIMESTAMP     | NON  | CURRENT_TIMESTAMP | Date de l'action                      |

---

## 🔄 **5. STRATÉGIE DE MIGRATION V1 → V2**

### **5.1 Principes de Migration**
- **Rétrocompatibilité** : La V2 doit pouvoir lire les données V1
- **Zéro downtime** : Migration progressive, pas de coupure de service
- **Réversibilité** : Possibilité de rollback à chaque étape
- **Intégrité** : Aucune perte de données

### **5.2 Plan de Migration par Étapes**

#### **Étape 1 : Renommages et restructurations**
```sql
-- 1a. Renommer admins → users
RENAME TABLE admins TO users;

-- 1b. Ajouter les nouvelles colonnes à users
ALTER TABLE users
    ADD COLUMN first_name VARCHAR(100) NULL AFTER email,
    ADD COLUMN last_name VARCHAR(100) NULL AFTER first_name,
    ADD COLUMN phone VARCHAR(20) NULL AFTER last_name,
    ADD COLUMN avatar VARCHAR(255) NULL AFTER phone,
    ADD COLUMN last_login DATETIME NULL AFTER reset_token_expiry;

-- 1c. Migrer restaurant_name vers first_name temporairement
UPDATE users SET first_name = restaurant_name WHERE first_name IS NULL;
```

#### **Étape 2 : Enrichir restaurants**
```sql
ALTER TABLE restaurants
    ADD COLUMN address TEXT NULL,
    ADD COLUMN phone VARCHAR(20) NULL,
    ADD COLUMN email VARCHAR(255) NULL,
    ADD COLUMN latitude DECIMAL(10,8) NULL,
    ADD COLUMN longitude DECIMAL(11,8) NULL,
    ADD COLUMN timezone VARCHAR(50) NOT NULL DEFAULT 'Europe/Paris',
    ADD COLUMN currency VARCHAR(3) NOT NULL DEFAULT 'EUR',
    ADD COLUMN language VARCHAR(5) NOT NULL DEFAULT 'fr',
    ADD COLUMN carte_mode ENUM('editable','images') NOT NULL DEFAULT 'editable',
    ADD COLUMN is_published BOOLEAN NOT NULL DEFAULT FALSE,
    ADD COLUMN logo_filename VARCHAR(255) NULL,
    ADD COLUMN banner_filename VARCHAR(255) NULL,
    ADD COLUMN banner_text TEXT NULL;

-- Migrer les données depuis contact, logos, banners, options
UPDATE restaurants r
    JOIN contact c ON c.admin_id = (SELECT id FROM users WHERE restaurant_id = r.id LIMIT 1)
    SET r.address = c.adresse, r.phone = c.telephone, r.email = c.email;

UPDATE restaurants r
    JOIN logos l ON l.admin_id = (SELECT id FROM users WHERE restaurant_id = r.id LIMIT 1)
    SET r.logo_filename = l.filename;

UPDATE restaurants r
    JOIN banners b ON b.admin_id = (SELECT id FROM users WHERE restaurant_id = r.id LIMIT 1)
    SET r.banner_filename = b.filename, r.banner_text = b.text;
```

#### **Étape 3 : Créer restaurant_users**
```sql
CREATE TABLE restaurant_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    restaurant_id INT NOT NULL,
    user_id INT NOT NULL,
    role ENUM('owner','manager','employee') NOT NULL DEFAULT 'owner',
    permissions JSON NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_restaurant_user (restaurant_id, user_id)
);

-- Migrer la relation existante
INSERT INTO restaurant_users (restaurant_id, user_id, role)
SELECT restaurant_id, id, 'owner' FROM users WHERE restaurant_id IS NOT NULL;
```

#### **Étape 4 : Migrer admin_options → restaurant_settings**
```sql
CREATE TABLE restaurant_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    restaurant_id INT NOT NULL,
    setting_key VARCHAR(100) NOT NULL,
    setting_value TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    UNIQUE KEY unique_restaurant_setting (restaurant_id, setting_key)
);

-- Migrer les options existantes
INSERT INTO restaurant_settings (restaurant_id, setting_key, setting_value)
SELECT u.restaurant_id, ao.option_name, ao.option_value
FROM admin_options ao
JOIN users u ON ao.admin_id = u.id
WHERE u.restaurant_id IS NOT NULL;
```

#### **Étape 5 : Mettre à jour les FK des tables enfants**
```sql
-- Ajouter restaurant_id aux categories (remplacer admin_id)
ALTER TABLE categories ADD COLUMN restaurant_id_new INT NULL;
UPDATE categories c
    JOIN users u ON c.admin_id = u.id
    SET c.restaurant_id_new = u.restaurant_id;

-- Renommer plats → items
RENAME TABLE plats TO items;
RENAME TABLE allergenes TO allergens;
RENAME TABLE plat_allergenes TO item_allergens;

-- Créer analytics_events
CREATE TABLE analytics_events LIKE site_visits;
ALTER TABLE analytics_events
    ADD COLUMN event_type ENUM('page_view','menu_view','item_click','reservation','call_click')
        NOT NULL DEFAULT 'page_view',
    ADD COLUMN metadata JSON NULL;

INSERT INTO analytics_events SELECT *, 'page_view', NULL FROM site_visits;

-- Créer audit_logs
CREATE TABLE audit_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    restaurant_id INT NULL,
    action VARCHAR(100) NOT NULL,
    entity_type VARCHAR(100) NULL,
    entity_id INT NULL,
    old_values JSON NULL,
    new_values JSON NULL,
    ip_address VARCHAR(45) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user (user_id),
    INDEX idx_restaurant (restaurant_id),
    INDEX idx_action (action),
    INDEX idx_created (created_at)
);
```

#### **Étape 6 : Nettoyage**
```sql
-- Supprimer les tables obsolètes (après validation)
-- DROP TABLE admin_options;
-- DROP TABLE contact;
-- DROP TABLE logos;
-- DROP TABLE banners;
-- DROP TABLE site_visits;
-- ALTER TABLE users DROP COLUMN restaurant_name;
-- ALTER TABLE users DROP COLUMN restaurant_id;
-- ALTER TABLE users DROP COLUMN carte_mode;
```

### **5.3 Rollback Plan**

À chaque étape, un script de rollback est préparé :
```sql
-- Rollback Étape 1 : Renommer users → admins
RENAME TABLE users TO admins;
ALTER TABLE admins DROP COLUMN first_name, DROP COLUMN last_name, 
    DROP COLUMN phone, DROP COLUMN avatar, DROP COLUMN last_login;
```

---

## 📊 **6. OPTIMISATIONS ET INDEXATION**

### **6.1 Index Stratégiques**
```sql
-- Recherche par slug (vitrine publique)
CREATE UNIQUE INDEX idx_restaurants_slug ON restaurants(slug);

-- Recherche géographique
CREATE INDEX idx_restaurants_location ON restaurants(latitude, longitude);

-- Catégories par restaurant triées
CREATE INDEX idx_categories_restaurant_order ON categories(restaurant_id, display_order);

-- Items par catégorie triés et disponibles
CREATE INDEX idx_items_category_order ON items(category_id, display_order, is_available);

-- Réservations par restaurant et date
CREATE INDEX idx_reservations_restaurant_date ON reservations(restaurant_id, reservation_date, status);

-- Analytics par restaurant et date
CREATE INDEX idx_analytics_restaurant_date ON analytics_events(restaurant_id, created_at, event_type);

-- Audit logs par user et date
CREATE INDEX idx_audit_user_date ON audit_logs(user_id, created_at);

-- Recherche fulltext des plats
CREATE FULLTEXT INDEX idx_items_search ON items(name, description);
```

### **6.2 Partitionnement**
```sql
-- Partitionnement des analytics par mois (table volumineuse)
ALTER TABLE analytics_events PARTITION BY RANGE (YEAR(created_at) * 100 + MONTH(created_at)) (
    PARTITION p202601 VALUES LESS THAN (202602),
    PARTITION p202602 VALUES LESS THAN (202603),
    PARTITION p202603 VALUES LESS THAN (202604),
    PARTITION p_future VALUES LESS THAN MAXVALUE
);

-- Partitionnement des audit_logs par trimestre
ALTER TABLE audit_logs PARTITION BY RANGE (YEAR(created_at) * 10 + QUARTER(created_at)) (
    PARTITION p2026q1 VALUES LESS THAN (20262),
    PARTITION p2026q2 VALUES LESS THAN (20263),
    PARTITION p_future VALUES LESS THAN MAXVALUE
);
```

### **6.3 Cache Strategy (Redis)**
```
Cache Keys :
├── restaurant:{id}              → 1h TTL (données restaurant)
├── restaurant:{slug}:id         → 24h TTL (résolution slug → id)
├── menu:{restaurant_id}         → 30min TTL (carte complète)
├── categories:{restaurant_id}   → 30min TTL (catégories)
├── items:{category_id}          → 30min TTL (plats par catégorie)
├── settings:{restaurant_id}     → 1h TTL (paramètres)
├── analytics:{restaurant_id}:today → 5min TTL (stats du jour)
└── user:session:{session_id}    → session TTL (session utilisateur)

Invalidation :
├── On menu update → invalidate menu:*, categories:*, items:*
├── On settings update → invalidate settings:*
├── On restaurant update → invalidate restaurant:*
└── On analytics event → invalidate analytics:*:today
```

---

## 🎯 **CONCLUSION**

Cette modélisation MERISE complète fournit :

✅ **Règles de gestion** formalisées et exhaustives  
✅ **MCD V1 et V2** avec cardinalités détaillées  
✅ **MLD complet** avec schéma relationnel normalisé  
✅ **Dictionnaire de données** pour chaque table et colonne  
✅ **Stratégie de migration** progressive et réversible  
✅ **Optimisations** d'indexation et partitionnement  
✅ **Cache strategy** pour performance à grande échelle  

Le passage de V1 à V2 est pensé pour être **progressif, sûr et sans perte de données**.

---
*Prochaine étape : Architecture MVC et Design Patterns* 🏛️
