-- ============================================================
-- MenuMiam V2 - Schéma de Base de Données Complet
-- Date : Avril 2026
-- Version : 2.0
-- Conformité : Conception CDA (BDD_COMPLETE_V2.md)
-- ============================================================

-- Création de la base de données
CREATE DATABASE IF NOT EXISTS menumiam_v2 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE menumiam_v2;

-- ============================================================
-- TABLE : admins
-- Description : Utilisateurs administrateurs (restaurateurs)
-- ============================================================
CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('ADMIN', 'SUPER_ADMIN') DEFAULT 'ADMIN',
    restaurant_name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    carte_mode ENUM('carte', 'images') DEFAULT 'carte',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_card_update TIMESTAMP NULL,
    INDEX idx_slug (slug),
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE : admin_options
-- Description : Options clé-valeur par administrateur
-- ============================================================
CREATE TABLE admin_options (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL,
    option_name VARCHAR(100) NOT NULL,
    option_value TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY idx_admin_option (admin_id, option_name),
    INDEX idx_admin (admin_id),
    FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE : categories
-- Description : Catégories de plats
-- ============================================================
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    image VARCHAR(255) NULL,
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_admin (admin_id),
    INDEX idx_order (display_order),
    FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE : dishes
-- Description : Plats de la carte
-- ============================================================
CREATE TABLE dishes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT NULL,
    price DECIMAL(10,2) NOT NULL,
    image VARCHAR(255) NULL,
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_category (category_id),
    INDEX idx_order (display_order),
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
    CONSTRAINT chk_price CHECK (price >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE : allergens
-- Description : Allergènes (14 pré-remplis)
-- ============================================================
CREATE TABLE allergens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(50) NOT NULL UNIQUE,
    icone VARCHAR(50) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertion des 14 allergènes officiels
INSERT INTO allergens (nom, icone) VALUES
('Gluten', 'fa-wheat-awn'),
('Crustacés', 'fa-shrimp'),
('Œufs', 'fa-egg'),
('Poissons', 'fa-fish'),
('Arachides', 'fa-peanut'),
('Soja', 'fa-seedling'),
('Lait', 'fa-cow'),
('Fruits à coque', 'fa-acorn'),
('Céleri', 'fa-carrot'),
('Moutarde', 'fa-jar'),
('Sésame', 'fa-seed'),
('Sulfites', 'fa-wine-bottle'),
('Lupin', 'fa-leaf'),
('Mollusques', 'fa-shell');

-- ============================================================
-- TABLE : dish_allergens
-- Description : Relation N:N entre plats et allergènes
-- ============================================================
CREATE TABLE dish_allergens (
    dish_id INT NOT NULL,
    allergen_id INT NOT NULL,
    PRIMARY KEY (dish_id, allergen_id),
    INDEX idx_allergen (allergen_id),
    FOREIGN KEY (dish_id) REFERENCES dishes(id) ON DELETE CASCADE,
    FOREIGN KEY (allergen_id) REFERENCES allergens(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE : daily_menus
-- Description : Menus du jour
-- ============================================================
CREATE TABLE daily_menus (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL,
    title VARCHAR(100) NOT NULL,
    description TEXT NULL,
    price DECIMAL(10,2) NULL,
    items JSON NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_admin (admin_id),
    INDEX idx_active (is_active),
    FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE : card_images
-- Description : Images de carte (mode images)
-- ============================================================
CREATE TABLE card_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL,
    filename VARCHAR(255) NOT NULL,
    original_name VARCHAR(255) NULL,
    display_order INT DEFAULT 0,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_admin (admin_id),
    INDEX idx_order (display_order),
    FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE : contact
-- Description : Informations de contact du restaurant
-- ============================================================
CREATE TABLE contact (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL UNIQUE,
    telephone VARCHAR(20) NULL,
    mobile VARCHAR(20) NULL,
    email VARCHAR(100) NULL,
    adresse TEXT NULL,
    horaires JSON NULL,
    facebook VARCHAR(255) NULL,
    instagram VARCHAR(255) NULL,
    twitter VARCHAR(255) NULL,
    FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE : reservations
-- Description : Réservations en ligne (premium)
-- ============================================================
CREATE TABLE reservations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL,
    table_id INT NULL,
    customer_name VARCHAR(100) NOT NULL,
    customer_email VARCHAR(100) NOT NULL,
    customer_phone VARCHAR(20) NOT NULL,
    reservation_date DATE NOT NULL,
    reservation_time TIME NOT NULL,
    party_size INT NOT NULL,
    special_requests TEXT NULL,
    status ENUM('pending', 'confirmed', 'cancelled', 'completed', 'no_show') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_admin (admin_id),
    INDEX idx_table (table_id),
    INDEX idx_status (status),
    INDEX idx_date (reservation_date),
    INDEX idx_datetime (reservation_date, reservation_time),
    FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE CASCADE,
    CONSTRAINT chk_party_size CHECK (party_size > 0 AND party_size <= 50)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE : floors
-- Description : Salles du restaurant (Floor Plan)
-- ============================================================
CREATE TABLE floors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_admin (admin_id),
    INDEX idx_order (display_order),
    FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE : tables
-- Description : Tables du restaurant
-- ============================================================
CREATE TABLE tables (
    id INT AUTO_INCREMENT PRIMARY KEY,
    floor_id INT NOT NULL,
    admin_id INT NOT NULL,
    table_number VARCHAR(20) NOT NULL,
    capacity INT NOT NULL,
    shape ENUM('round', 'square', 'rectangle') DEFAULT 'round',
    position_x FLOAT DEFAULT 0,
    position_y FLOAT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_floor (floor_id),
    INDEX idx_admin (admin_id),
    UNIQUE KEY idx_admin_table (admin_id, table_number),
    FOREIGN KEY (floor_id) REFERENCES floors(id) ON DELETE CASCADE,
    FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE CASCADE,
    CONSTRAINT chk_capacity CHECK (capacity > 0 AND capacity <= 20)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Ajouter la contrainte FK pour reservations.table_id
ALTER TABLE reservations
ADD FOREIGN KEY (table_id) REFERENCES tables(id) ON DELETE SET NULL;

-- ============================================================
-- TABLE : floor_elements
-- Description : Éléments décoratifs du plan de salle
-- ============================================================
CREATE TABLE floor_elements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    floor_id INT NOT NULL,
    element_type VARCHAR(50) NOT NULL,
    label VARCHAR(100) NULL,
    position_x FLOAT DEFAULT 0,
    position_y FLOAT DEFAULT 0,
    width FLOAT NULL,
    height FLOAT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_floor (floor_id),
    FOREIGN KEY (floor_id) REFERENCES floors(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE : client_subscriptions
-- Description : Abonnements des clients
-- ============================================================
CREATE TABLE client_subscriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL UNIQUE,
    plan_type ENUM('basique', 'premium') DEFAULT 'basique',
    status ENUM('active', 'inactive', 'cancelled', 'expired') DEFAULT 'active',
    price_per_month DECIMAL(10,2) NULL,
    started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NULL,
    INDEX idx_status (status),
    FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE : premium_features
-- Description : Fonctionnalités premium activées
-- ============================================================
CREATE TABLE premium_features (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL,
    feature_name VARCHAR(50) NOT NULL,
    is_active TINYINT(1) DEFAULT 0,
    activated_at TIMESTAMP NULL,
    UNIQUE KEY idx_admin_feature (admin_id, feature_name),
    INDEX idx_admin (admin_id),
    INDEX idx_feature (feature_name),
    FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE : site_visits
-- Description : Statistiques de visites (analytics)
-- ============================================================
CREATE TABLE site_visits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL,
    visitor_hash VARCHAR(64) NOT NULL,
    device_type VARCHAR(20) NULL,
    browser VARCHAR(50) NULL,
    page_path VARCHAR(255) NULL,
    visited_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_admin (admin_id),
    INDEX idx_visited (visited_at),
    INDEX idx_hash (visitor_hash),
    FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE : invitations
-- Description : Invitations pour nouveaux restaurateurs
-- ============================================================
CREATE TABLE invitations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL,
    restaurant_name VARCHAR(100) NOT NULL,
    token VARCHAR(64) NOT NULL UNIQUE,
    expiry TIMESTAMP NOT NULL,
    used TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_token (token),
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE : demo_tokens
-- Description : Tokens de démonstration temporaires
-- ============================================================
CREATE TABLE demo_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    token VARCHAR(64) NOT NULL UNIQUE,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_token (token),
    INDEX idx_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE : closure_dates
-- Description : Dates de fermeture exceptionnelles
-- ============================================================
CREATE TABLE closure_dates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL,
    date DATE NOT NULL,
    reason VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY idx_admin_date (admin_id, date),
    INDEX idx_admin (admin_id),
    INDEX idx_date (date),
    FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- FIN DU SCHÉMA
-- Total : 18 tables créées
-- Conformité : BDD_COMPLETE_V2.md
-- ============================================================
