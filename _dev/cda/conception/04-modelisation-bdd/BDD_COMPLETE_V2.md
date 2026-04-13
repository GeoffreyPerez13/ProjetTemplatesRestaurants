# 🗄️ Base de Données Complète - MenuMiam V2
## Modélisation MERISE - État Actuel (Avril 2026)

**Version** : 2.0 (Production)  
**SGBD** : MySQL 8.0+  
**Moteur** : InnoDB  
**Encodage** : utf8mb4_unicode_ci

---

## 📋 **RÈGLES DE GESTION COMPLÈTES**

### **RG1 - Utilisateurs et Restaurants**
- Un **administrateur** possède **un restaurant** (relation 1:1)
- Un **restaurant** est géré par **un seul** administrateur
- Un **administrateur** a un rôle : `SUPER_ADMIN` ou `ADMIN`
- Un **SUPER_ADMIN** gère la plateforme
- Un **ADMIN** gère son restaurant

### **RG2 - Carte et Contenu**
- Un **restaurant** possède **une carte** composée de **catégories**
- Une **catégorie** contient **zéro ou plusieurs plats**
- Un **plat** appartient à **une seule catégorie**
- Un **plat** peut contenir **zéro ou plusieurs allergènes**
- Un **allergène** peut concerner **zéro ou plusieurs plats** (N:N)
- Un **restaurant** peut avoir **zéro ou plusieurs menus du jour**
- Un **restaurant** peut avoir **zéro ou plusieurs images de carte**

### **RG3 - Médias**
- Un **restaurant** possède **au plus un logo**
- Un **restaurant** possède **au plus une bannière**
- Les **images de carte** ont un ordre d'affichage

### **RG4 - Contact et Options**
- Un **restaurant** possède **une fiche contact** unique
- Les **options** sont stockées en **clé-valeur** dans `admin_options`
- Les options incluent : services, paiements, réseaux sociaux, paramètres

### **RG5 - Abonnements et Premium**
- Un **administrateur** possède **un abonnement** (basique par défaut)
- Un **abonnement** a un statut : `active`, `inactive`, `cancelled`, `expired`
- Un **administrateur** peut activer **zéro ou plusieurs fonctionnalités premium**
- Fonctionnalités premium : `google_reviews`, `advanced_analytics`, `online_booking`, `delivery_integration`

### **RG6 - Réservations** ✅ IMPLÉMENTÉ
- Un **restaurant** reçoit **zéro ou plusieurs réservations** (si premium activé)
- Une **réservation** a un statut : `pending`, `confirmed`, `cancelled`, `completed`, `no_show`
- Une **réservation** contient les infos client et la date/heure souhaitée
- Une **réservation** peut être liée à **une table** (optionnel)

### **RG7 - Plan de Salle (Floor Plan)** ✅ IMPLÉMENTÉ
- Un **restaurant** possède **zéro ou plusieurs salles** (floors)
- Une **salle** contient **zéro ou plusieurs tables**
- Une **table** appartient à **une seule salle**
- Une **table** a une capacité, une forme et une position
- Une **salle** peut contenir **zéro ou plusieurs éléments décoratifs**
- La **numérotation des tables** est globale au restaurant

### **RG8 - Intégration Livraison** ✅ IMPLÉMENTÉ
- Les **configurations de livraison** sont stockées dans `admin_options`
- Clés : `delivery_{platform}_enabled`, `delivery_{platform}_api_key`, `delivery_{platform}_store_id`
- Plateformes supportées : `ubereats`, `deliveroo`, `justeat`

### **RG9 - Analytics**
- Chaque **visite** sur une vitrine est enregistrée de manière **anonymisée**
- Les visites sont rattachées à un **restaurant** via `admin_id`
- Les données incluent : hash visiteur, device, browser, page

### **RG10 - Démo et Invitations**
- Un **SUPER_ADMIN** peut générer des **tokens de démo** temporaires
- Un **token de démo** crée un **clone isolé** du restaurant template
- Une **invitation** permet à un nouveau restaurateur de s'inscrire

---

## 🗂️ **DICTIONNAIRE DE DONNÉES**

### **Table : admins**
| Champ | Type | Contraintes | Description |
|-------|------|-------------|-------------|
| id | INT | PK, AUTO_INCREMENT | Identifiant unique |
| username | VARCHAR(50) | UNIQUE, NOT NULL | Nom d'utilisateur |
| email | VARCHAR(100) | UNIQUE, NOT NULL | Email de connexion |
| password | VARCHAR(255) | NOT NULL | Hash bcrypt du mot de passe |
| role | ENUM | DEFAULT 'ADMIN' | Rôle : ADMIN ou SUPER_ADMIN |
| restaurant_name | VARCHAR(100) | NOT NULL | Nom du restaurant |
| slug | VARCHAR(100) | UNIQUE, NOT NULL | Slug URL unique |
| carte_mode | ENUM | DEFAULT 'carte' | Mode d'affichage : carte ou images |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Date de création |
| last_card_update | TIMESTAMP | NULL | Dernière mise à jour de la carte |

### **Table : admin_options**
| Champ | Type | Contraintes | Description |
|-------|------|-------------|-------------|
| id | INT | PK, AUTO_INCREMENT | Identifiant unique |
| admin_id | INT | FK → admins(id), NOT NULL | Référence admin |
| option_name | VARCHAR(100) | NOT NULL | Nom de l'option (clé) |
| option_value | TEXT | NULL | Valeur de l'option |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Date de création |
| updated_at | TIMESTAMP | ON UPDATE CURRENT_TIMESTAMP | Dernière modification |

**Index** : UNIQUE(admin_id, option_name)

**Options courantes** :
- `site_online` : 1 ou 0
- `site_template` : classic, modern, elegant
- `email_reminders` : 1 ou 0
- `hide_dark_mode` : 1 ou 0
- `hide_tour_button` : 1 ou 0
- `notifications_enabled` : 1 ou 0
- `google_place_id` : ID Google Places
- `google_api_key` : Clé API Google
- `google_reviews_enabled` : 1 ou 0
- `delivery_ubereats_enabled` : 1 ou 0
- `delivery_ubereats_api_key` : Clé API Uber Eats
- `delivery_ubereats_store_id` : ID restaurant Uber Eats
- `delivery_deliveroo_enabled` : 1 ou 0
- `delivery_deliveroo_api_key` : Clé API Deliveroo
- `delivery_deliveroo_store_id` : ID restaurant Deliveroo
- `delivery_justeat_enabled` : 1 ou 0
- `delivery_justeat_api_key` : Clé API Just Eat
- `delivery_justeat_store_id` : ID restaurant Just Eat

### **Table : categories**
| Champ | Type | Contraintes | Description |
|-------|------|-------------|-------------|
| id | INT | PK, AUTO_INCREMENT | Identifiant unique |
| admin_id | INT | FK → admins(id), NOT NULL | Référence admin |
| name | VARCHAR(100) | NOT NULL | Nom de la catégorie |
| image | VARCHAR(255) | NULL | Nom du fichier image |
| display_order | INT | DEFAULT 0 | Ordre d'affichage |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Date de création |

**Index** : INDEX(admin_id), INDEX(display_order)

### **Table : dishes**
| Champ | Type | Contraintes | Description |
|-------|------|-------------|-------------|
| id | INT | PK, AUTO_INCREMENT | Identifiant unique |
| category_id | INT | FK → categories(id), NOT NULL | Référence catégorie |
| name | VARCHAR(100) | NOT NULL | Nom du plat |
| description | TEXT | NULL | Description du plat |
| price | DECIMAL(10,2) | NOT NULL | Prix du plat |
| image | VARCHAR(255) | NULL | Nom du fichier image |
| display_order | INT | DEFAULT 0 | Ordre d'affichage |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Date de création |

**Index** : INDEX(category_id), INDEX(display_order)

### **Table : allergens**
| Champ | Type | Contraintes | Description |
|-------|------|-------------|-------------|
| id | INT | PK, AUTO_INCREMENT | Identifiant unique |
| nom | VARCHAR(50) | UNIQUE, NOT NULL | Nom de l'allergène |
| icone | VARCHAR(50) | NULL | Classe CSS de l'icône |

**Données pré-remplies** : Gluten, Crustacés, Œufs, Poissons, Arachides, Soja, Lait, Fruits à coque, Céleri, Moutarde, Sésame, Sulfites, Lupin, Mollusques

### **Table : dish_allergens**
| Champ | Type | Contraintes | Description |
|-------|------|-------------|-------------|
| dish_id | INT | FK → dishes(id), NOT NULL | Référence plat |
| allergen_id | INT | FK → allergens(id), NOT NULL | Référence allergène |

**Contrainte** : PRIMARY KEY(dish_id, allergen_id)  
**Index** : INDEX(allergen_id)

### **Table : daily_menus**
| Champ | Type | Contraintes | Description |
|-------|------|-------------|-------------|
| id | INT | PK, AUTO_INCREMENT | Identifiant unique |
| admin_id | INT | FK → admins(id), NOT NULL | Référence admin |
| title | VARCHAR(100) | NOT NULL | Titre du menu |
| description | TEXT | NULL | Description du menu |
| price | DECIMAL(10,2) | NULL | Prix du menu |
| items | JSON | NULL | Items du menu (label + valeur) |
| is_active | TINYINT(1) | DEFAULT 1 | Menu actif ou non |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Date de création |

**Index** : INDEX(admin_id), INDEX(is_active)

**Structure JSON items** :
```json
[
  {"label": "Entrée", "value": "Salade César"},
  {"label": "Plat", "value": "Poulet rôti"},
  {"label": "Dessert", "value": "Tarte aux pommes"}
]
```

### **Table : card_images**
| Champ | Type | Contraintes | Description |
|-------|------|-------------|-------------|
| id | INT | PK, AUTO_INCREMENT | Identifiant unique |
| admin_id | INT | FK → admins(id), NOT NULL | Référence admin |
| filename | VARCHAR(255) | NOT NULL | Nom du fichier |
| original_name | VARCHAR(255) | NULL | Nom original du fichier |
| display_order | INT | DEFAULT 0 | Ordre d'affichage |
| uploaded_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Date d'upload |

**Index** : INDEX(admin_id), INDEX(display_order)

### **Table : contact**
| Champ | Type | Contraintes | Description |
|-------|------|-------------|-------------|
| id | INT | PK, AUTO_INCREMENT | Identifiant unique |
| admin_id | INT | FK → admins(id), UNIQUE, NOT NULL | Référence admin |
| telephone | VARCHAR(20) | NULL | Téléphone fixe |
| mobile | VARCHAR(20) | NULL | Téléphone mobile |
| email | VARCHAR(100) | NULL | Email de contact |
| adresse | TEXT | NULL | Adresse complète |
| horaires | JSON | NULL | Horaires d'ouverture |
| facebook | VARCHAR(255) | NULL | URL Facebook |
| instagram | VARCHAR(255) | NULL | URL Instagram |
| twitter | VARCHAR(255) | NULL | URL Twitter |

**Structure JSON horaires** :
```json
{
  "lundi": {"ouvert": true, "matin": "09:00-12:00", "soir": "18:00-22:00"},
  "mardi": {"ouvert": true, "matin": "09:00-12:00", "soir": "18:00-22:00"},
  ...
}
```

### **Table : reservations** ✅ IMPLÉMENTÉ
| Champ | Type | Contraintes | Description |
|-------|------|-------------|-------------|
| id | INT | PK, AUTO_INCREMENT | Identifiant unique |
| admin_id | INT | FK → admins(id), NOT NULL | Référence admin |
| table_id | INT | FK → tables(id), NULL | Référence table (optionnel) |
| customer_name | VARCHAR(100) | NOT NULL | Nom du client |
| customer_email | VARCHAR(100) | NOT NULL | Email du client |
| customer_phone | VARCHAR(20) | NOT NULL | Téléphone du client |
| reservation_date | DATE | NOT NULL | Date de réservation |
| reservation_time | TIME | NOT NULL | Heure de réservation |
| party_size | INT | NOT NULL | Nombre de personnes |
| special_requests | TEXT | NULL | Demandes spéciales |
| status | ENUM | DEFAULT 'pending' | Statut : pending, confirmed, cancelled, completed, no_show |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Date de création |
| updated_at | TIMESTAMP | ON UPDATE CURRENT_TIMESTAMP | Dernière modification |

**Index** : INDEX(admin_id), INDEX(table_id), INDEX(status), INDEX(reservation_date)

### **Table : floors** ✅ IMPLÉMENTÉ
| Champ | Type | Contraintes | Description |
|-------|------|-------------|-------------|
| id | INT | PK, AUTO_INCREMENT | Identifiant unique |
| admin_id | INT | FK → admins(id), NOT NULL | Référence admin |
| name | VARCHAR(100) | NOT NULL | Nom de la salle |
| display_order | INT | DEFAULT 0 | Ordre d'affichage |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Date de création |

**Index** : INDEX(admin_id), INDEX(display_order)

### **Table : tables** ✅ IMPLÉMENTÉ
| Champ | Type | Contraintes | Description |
|-------|------|-------------|-------------|
| id | INT | PK, AUTO_INCREMENT | Identifiant unique |
| floor_id | INT | FK → floors(id), NOT NULL | Référence salle |
| admin_id | INT | FK → admins(id), NOT NULL | Référence admin |
| table_number | VARCHAR(20) | NOT NULL | Numéro de table |
| capacity | INT | NOT NULL | Capacité (nb de personnes) |
| shape | ENUM | DEFAULT 'round' | Forme : round, square, rectangle |
| position_x | FLOAT | DEFAULT 0 | Position X (px) |
| position_y | FLOAT | DEFAULT 0 | Position Y (px) |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Date de création |

**Index** : INDEX(floor_id), INDEX(admin_id), UNIQUE(admin_id, table_number)

### **Table : floor_elements** ✅ IMPLÉMENTÉ
| Champ | Type | Contraintes | Description |
|-------|------|-------------|-------------|
| id | INT | PK, AUTO_INCREMENT | Identifiant unique |
| floor_id | INT | FK → floors(id), NOT NULL | Référence salle |
| element_type | VARCHAR(50) | NOT NULL | Type : bar, plant, decoration, etc. |
| label | VARCHAR(100) | NULL | Label de l'élément |
| position_x | FLOAT | DEFAULT 0 | Position X (px) |
| position_y | FLOAT | DEFAULT 0 | Position Y (px) |
| width | FLOAT | NULL | Largeur (px) |
| height | FLOAT | NULL | Hauteur (px) |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Date de création |

**Index** : INDEX(floor_id)

### **Table : client_subscriptions**
| Champ | Type | Contraintes | Description |
|-------|------|-------------|-------------|
| id | INT | PK, AUTO_INCREMENT | Identifiant unique |
| admin_id | INT | FK → admins(id), UNIQUE, NOT NULL | Référence admin |
| plan_type | ENUM | DEFAULT 'basique' | Type : basique, premium |
| status | ENUM | DEFAULT 'active' | Statut : active, inactive, cancelled, expired |
| price_per_month | DECIMAL(10,2) | NULL | Prix mensuel |
| started_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Date de début |
| expires_at | TIMESTAMP | NULL | Date d'expiration |

**Index** : INDEX(status)

### **Table : premium_features**
| Champ | Type | Contraintes | Description |
|-------|------|-------------|-------------|
| id | INT | PK, AUTO_INCREMENT | Identifiant unique |
| admin_id | INT | FK → admins(id), NOT NULL | Référence admin |
| feature_name | VARCHAR(50) | NOT NULL | Nom de la fonctionnalité |
| is_active | TINYINT(1) | DEFAULT 0 | Activée ou non |
| activated_at | TIMESTAMP | NULL | Date d'activation |

**Contrainte** : UNIQUE(admin_id, feature_name)  
**Index** : INDEX(admin_id), INDEX(feature_name)

**Features disponibles** :
- `google_reviews` : Avis Google
- `advanced_analytics` : Statistiques avancées
- `online_booking` : Réservations en ligne
- `delivery_integration` : Intégration livraison

### **Table : site_visits**
| Champ | Type | Contraintes | Description |
|-------|------|-------------|-------------|
| id | INT | PK, AUTO_INCREMENT | Identifiant unique |
| admin_id | INT | FK → admins(id), NOT NULL | Référence admin |
| visitor_hash | VARCHAR(64) | NOT NULL | Hash anonymisé (IP+UA) |
| device_type | VARCHAR(20) | NULL | Type : mobile, tablet, desktop |
| browser | VARCHAR(50) | NULL | Navigateur utilisé |
| page_path | VARCHAR(255) | NULL | Chemin de la page visitée |
| visited_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Date de visite |

**Index** : INDEX(admin_id), INDEX(visited_at), INDEX(visitor_hash)

### **Table : invitations**
| Champ | Type | Contraintes | Description |
|-------|------|-------------|-------------|
| id | INT | PK, AUTO_INCREMENT | Identifiant unique |
| email | VARCHAR(100) | NOT NULL | Email du destinataire |
| restaurant_name | VARCHAR(100) | NOT NULL | Nom du restaurant |
| token | VARCHAR(64) | UNIQUE, NOT NULL | Token unique |
| expiry | TIMESTAMP | NOT NULL | Date d'expiration |
| used | TINYINT(1) | DEFAULT 0 | Utilisée ou non |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Date de création |

**Index** : INDEX(token), INDEX(email)

### **Table : demo_tokens**
| Champ | Type | Contraintes | Description |
|-------|------|-------------|-------------|
| id | INT | PK, AUTO_INCREMENT | Identifiant unique |
| token | VARCHAR(64) | UNIQUE, NOT NULL | Token unique |
| expires_at | TIMESTAMP | NOT NULL | Date d'expiration |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Date de création |

**Index** : INDEX(token), INDEX(expires_at)

### **Table : closure_dates**
| Champ | Type | Contraintes | Description |
|-------|------|-------------|-------------|
| id | INT | PK, AUTO_INCREMENT | Identifiant unique |
| admin_id | INT | FK → admins(id), NOT NULL | Référence admin |
| date | DATE | NOT NULL | Date de fermeture |
| reason | VARCHAR(255) | NULL | Raison de la fermeture |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Date de création |

**Contrainte** : UNIQUE(admin_id, date)  
**Index** : INDEX(admin_id), INDEX(date)

---

## 🔗 **SCHÉMA RELATIONNEL**

### **Relations Principales**

```
admins (1) ──────────── (N) categories
admins (1) ──────────── (1) contact
admins (1) ──────────── (N) admin_options
admins (1) ──────────── (N) daily_menus
admins (1) ──────────── (N) card_images
admins (1) ──────────── (1) client_subscriptions
admins (1) ──────────── (N) premium_features
admins (1) ──────────── (N) reservations
admins (1) ──────────── (N) floors
admins (1) ──────────── (N) tables
admins (1) ──────────── (N) site_visits
admins (1) ──────────── (N) closure_dates

categories (1) ─────────── (N) dishes

dishes (N) ─────────── (N) allergens
       └── via dish_allergens

floors (1) ─────────── (N) tables
floors (1) ─────────── (N) floor_elements

tables (1) ─────────── (N) reservations (optionnel)
```

---

## 📊 **OPTIMISATIONS & INDEXATION**

### **Index Créés**

```sql
-- admins
CREATE INDEX idx_admins_slug ON admins(slug);
CREATE INDEX idx_admins_email ON admins(email);

-- categories
CREATE INDEX idx_categories_admin ON categories(admin_id);
CREATE INDEX idx_categories_order ON categories(display_order);

-- dishes
CREATE INDEX idx_dishes_category ON dishes(category_id);
CREATE INDEX idx_dishes_order ON dishes(display_order);

-- dish_allergens
CREATE INDEX idx_dish_allergens_allergen ON dish_allergens(allergen_id);

-- reservations
CREATE INDEX idx_reservations_admin ON reservations(admin_id);
CREATE INDEX idx_reservations_table ON reservations(table_id);
CREATE INDEX idx_reservations_status ON reservations(status);
CREATE INDEX idx_reservations_date ON reservations(reservation_date);
CREATE INDEX idx_reservations_datetime ON reservations(reservation_date, reservation_time);

-- floors
CREATE INDEX idx_floors_admin ON floors(admin_id);
CREATE INDEX idx_floors_order ON floors(display_order);

-- tables
CREATE INDEX idx_tables_floor ON tables(floor_id);
CREATE INDEX idx_tables_admin ON tables(admin_id);

-- floor_elements
CREATE INDEX idx_floor_elements_floor ON floor_elements(floor_id);

-- site_visits
CREATE INDEX idx_visits_admin ON site_visits(admin_id);
CREATE INDEX idx_visits_date ON site_visits(visited_at);
CREATE INDEX idx_visits_hash ON site_visits(visitor_hash);

-- admin_options
CREATE UNIQUE INDEX idx_options_admin_name ON admin_options(admin_id, option_name);

-- premium_features
CREATE UNIQUE INDEX idx_features_admin_name ON premium_features(admin_id, feature_name);

-- closure_dates
CREATE UNIQUE INDEX idx_closure_admin_date ON closure_dates(admin_id, date);
```

### **Requêtes Optimisées**

```sql
-- Récupérer toute la carte d'un restaurant
SELECT 
    c.id as category_id,
    c.name as category_name,
    c.image as category_image,
    d.id as dish_id,
    d.name as dish_name,
    d.description,
    d.price,
    d.image as dish_image,
    GROUP_CONCAT(a.nom) as allergens
FROM categories c
LEFT JOIN dishes d ON c.id = d.category_id
LEFT JOIN dish_allergens da ON d.id = da.dish_id
LEFT JOIN allergens a ON da.allergen_id = a.id
WHERE c.admin_id = ?
GROUP BY c.id, d.id
ORDER BY c.display_order, d.display_order;

-- Récupérer les réservations du jour avec infos table
SELECT 
    r.*,
    t.table_number,
    t.capacity,
    f.name as floor_name
FROM reservations r
LEFT JOIN tables t ON r.table_id = t.id
LEFT JOIN floors f ON t.floor_id = f.id
WHERE r.admin_id = ?
AND r.reservation_date = CURDATE()
ORDER BY r.reservation_time;

-- Statistiques de réservations par créneau
SELECT 
    reservation_time,
    COUNT(*) as total_reservations,
    SUM(party_size) as total_guests
FROM reservations
WHERE admin_id = ?
AND reservation_date BETWEEN ? AND ?
AND status IN ('confirmed', 'completed')
GROUP BY reservation_time
ORDER BY total_reservations DESC;

-- Taux de remplissage des tables
SELECT 
    DATE(r.reservation_date) as date,
    COUNT(DISTINCT r.table_id) as tables_used,
    (SELECT COUNT(*) FROM tables WHERE admin_id = ?) as total_tables,
    ROUND(COUNT(DISTINCT r.table_id) * 100.0 / (SELECT COUNT(*) FROM tables WHERE admin_id = ?), 2) as fill_rate
FROM reservations r
WHERE r.admin_id = ?
AND r.status IN ('confirmed', 'completed')
GROUP BY DATE(r.reservation_date)
ORDER BY date DESC;
```

---

## 🔒 **SÉCURITÉ**

### **Contraintes d'Intégrité**

```sql
-- Cascade sur suppression admin
ALTER TABLE categories ADD CONSTRAINT fk_categories_admin
    FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE CASCADE;

ALTER TABLE reservations ADD CONSTRAINT fk_reservations_admin
    FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE CASCADE;

ALTER TABLE floors ADD CONSTRAINT fk_floors_admin
    FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE CASCADE;

-- Cascade sur suppression catégorie
ALTER TABLE dishes ADD CONSTRAINT fk_dishes_category
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE;

-- Cascade sur suppression salle
ALTER TABLE tables ADD CONSTRAINT fk_tables_floor
    FOREIGN KEY (floor_id) REFERENCES floors(id) ON DELETE CASCADE;

ALTER TABLE floor_elements ADD CONSTRAINT fk_elements_floor
    FOREIGN KEY (floor_id) REFERENCES floors(id) ON DELETE CASCADE;

-- SET NULL sur suppression table (réservation reste)
ALTER TABLE reservations ADD CONSTRAINT fk_reservations_table
    FOREIGN KEY (table_id) REFERENCES tables(id) ON DELETE SET NULL;
```

### **Validation des Données**

```sql
-- Contraintes CHECK (MySQL 8.0+)
ALTER TABLE reservations ADD CONSTRAINT chk_party_size
    CHECK (party_size > 0 AND party_size <= 50);

ALTER TABLE tables ADD CONSTRAINT chk_capacity
    CHECK (capacity > 0 AND capacity <= 20);

ALTER TABLE dishes ADD CONSTRAINT chk_price
    CHECK (price >= 0);
```

---

## 📈 **VOLUMÉTRIE ESTIMÉE**

| Table | Nb lignes/restaurant | Croissance |
|-------|---------------------|------------|
| admins | 1 | Stable |
| categories | 5-15 | Faible |
| dishes | 30-100 | Moyenne |
| allergens | 14 | Stable (global) |
| dish_allergens | 50-200 | Moyenne |
| daily_menus | 0-5 | Faible |
| card_images | 0-20 | Faible |
| reservations | 100-500/mois | Forte |
| floors | 1-5 | Stable |
| tables | 10-50 | Stable |
| floor_elements | 5-30 | Stable |
| admin_options | 10-30 | Faible |
| site_visits | 1000-5000/mois | Forte |
| closure_dates | 5-20/an | Faible |

**Total estimé pour 1000 restaurants** : ~50M lignes  
**Espace disque estimé** : ~10-15 GB

---

## 🎯 **CONFORMITÉ CDA**

### **Compétences RNCP Validées**

- ✅ **C2.1** : Concevoir et développer la persistance des données
- ✅ **C2.2** : Modéliser les données (MERISE)
- ✅ **C2.3** : Optimiser les requêtes SQL
- ✅ **C2.4** : Assurer l'intégrité des données
- ✅ **C2.5** : Sécuriser l'accès aux données

---

**Conclusion** : La base de données MenuMiam V2 est complète, normalisée (3NF), optimisée et sécurisée, supportant toutes les fonctionnalités actuelles de l'application.
