-- ============================================================
-- MenuCraft — Schéma complet de la base de données
-- ============================================================
-- Exécution : mysql -u root -p < database/schema.sql
-- ============================================================

CREATE DATABASE IF NOT EXISTS `menucraft` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `menucraft`;

SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- 1. Restaurants
-- ============================================================
CREATE TABLE IF NOT EXISTS `restaurants` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(255) UNIQUE NOT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- 2. Admins (utilisateurs)
-- ============================================================
CREATE TABLE IF NOT EXISTS `admins` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(100) UNIQUE NOT NULL,
    `email` VARCHAR(255) NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM('ADMIN', 'SUPER_ADMIN') DEFAULT 'ADMIN',
    `restaurant_name` VARCHAR(255),
    `restaurant_id` INT,
    `carte_mode` ENUM('editable', 'images') DEFAULT 'editable',
    `reset_token` VARCHAR(255),
    `reset_token_expiry` DATETIME,
    `email_verified` TINYINT(1) DEFAULT 0,
    `verification_token` VARCHAR(255),
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================================
-- 3. Catégories de la carte
-- ============================================================
CREATE TABLE IF NOT EXISTS `categories` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `admin_id` INT NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `description` TEXT,
    `image` VARCHAR(500),
    `display_order` INT DEFAULT 0,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`admin_id`) REFERENCES `admins`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- 4. Plats
-- ============================================================
CREATE TABLE IF NOT EXISTS `plats` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `category_id` INT NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `description` TEXT,
    `price` DECIMAL(8,2) NOT NULL,
    `image` VARCHAR(500),
    `display_order` INT DEFAULT 0,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- 5. Allergènes (14 réglementaires)
-- ============================================================
CREATE TABLE IF NOT EXISTS `allergenes` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nom` VARCHAR(100) NOT NULL,
    `icone` VARCHAR(100)
) ENGINE=InnoDB;

-- Données de référence : 14 allergènes réglementaires
INSERT IGNORE INTO `allergenes` (`id`, `nom`, `icone`) VALUES
(1, 'Gluten', 'fa-bread-slice'),
(2, 'Crustacés', 'fa-shrimp'),
(3, 'Œufs', 'fa-egg'),
(4, 'Poissons', 'fa-fish'),
(5, 'Arachides', 'fa-seedling'),
(6, 'Soja', 'fa-leaf'),
(7, 'Lait', 'fa-glass-water'),
(8, 'Fruits à coque', 'fa-tree'),
(9, 'Céleri', 'fa-carrot'),
(10, 'Moutarde', 'fa-jar'),
(11, 'Graines de sésame', 'fa-circle'),
(12, 'Sulfites', 'fa-wine-bottle'),
(13, 'Lupin', 'fa-flower'),
(14, 'Mollusques', 'fa-clam');

-- ============================================================
-- 6. Pivot plats ↔ allergènes
-- ============================================================
CREATE TABLE IF NOT EXISTS `plat_allergenes` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `plat_id` INT NOT NULL,
    `allergene_id` INT NOT NULL,
    FOREIGN KEY (`plat_id`) REFERENCES `plats`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`allergene_id`) REFERENCES `allergenes`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- 7. Images de carte (mode images)
-- ============================================================
CREATE TABLE IF NOT EXISTS `card_images` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `admin_id` INT NOT NULL,
    `filename` VARCHAR(500) NOT NULL,
    `display_order` INT DEFAULT 0,
    `uploaded_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`admin_id`) REFERENCES `admins`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- 8. Menus du jour / Formules
-- ============================================================
CREATE TABLE IF NOT EXISTS `daily_menus` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `admin_id` INT NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT,
    `price` DECIMAL(8,2),
    `items` JSON,
    `display_order` INT DEFAULT 0,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`admin_id`) REFERENCES `admins`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- 9. Contact du restaurant
-- ============================================================
CREATE TABLE IF NOT EXISTS `contact` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `admin_id` INT UNIQUE NOT NULL,
    `telephone` VARCHAR(50),
    `email` VARCHAR(255),
    `adresse` TEXT,
    `horaires` TEXT,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`admin_id`) REFERENCES `admins`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- 10. Logos
-- ============================================================
CREATE TABLE IF NOT EXISTS `logos` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `admin_id` INT UNIQUE NOT NULL,
    `filename` VARCHAR(500) NOT NULL,
    `uploaded_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`admin_id`) REFERENCES `admins`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- 11. Bannières
-- ============================================================
CREATE TABLE IF NOT EXISTS `banners` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `admin_id` INT UNIQUE NOT NULL,
    `filename` VARCHAR(500) NOT NULL,
    `text` TEXT,
    `uploaded_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`admin_id`) REFERENCES `admins`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- 12. Options admin (clé/valeur)
-- ============================================================
CREATE TABLE IF NOT EXISTS `admin_options` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `admin_id` INT NOT NULL,
    `option_name` VARCHAR(100) NOT NULL,
    `option_value` TEXT DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_admin_option` (`admin_id`, `option_name`),
    FOREIGN KEY (`admin_id`) REFERENCES `admins`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- 13. Invitations (envoyées par SUPER_ADMIN)
-- ============================================================
CREATE TABLE IF NOT EXISTS `invitations` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `email` VARCHAR(255) NOT NULL,
    `restaurant_name` VARCHAR(255) NOT NULL,
    `token` VARCHAR(255) UNIQUE NOT NULL,
    `expiry` DATETIME NOT NULL,
    `used` TINYINT(1) DEFAULT 0,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- 14. Tokens de démonstration
-- ============================================================
CREATE TABLE IF NOT EXISTS `demo_tokens` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `token` VARCHAR(255) UNIQUE NOT NULL,
    `admin_id` INT NOT NULL,
    `clone_admin_id` INT,
    `clone_restaurant_id` INT,
    `label` VARCHAR(255),
    `expires_at` DATETIME NOT NULL,
    `created_by` INT,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`admin_id`) REFERENCES `admins`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`created_by`) REFERENCES `admins`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================================
-- 15. Abonnements clients
-- ============================================================
CREATE TABLE IF NOT EXISTS `client_subscriptions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `admin_id` INT UNIQUE NOT NULL,
    `plan_type` VARCHAR(50) DEFAULT 'basique',
    `status` ENUM('active', 'inactive', 'cancelled', 'expired') DEFAULT 'inactive',
    `price_per_month` DECIMAL(8,2),
    `features_enabled` JSON,
    `started_at` DATETIME,
    `expires_at` DATETIME,
    `billing_cycle_day` INT DEFAULT 15,
    `next_billing_date` DATE,
    `stripe_session_id` VARCHAR(255),
    `created_by` INT,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`admin_id`) REFERENCES `admins`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- 16. Fonctionnalités premium
-- ============================================================
CREATE TABLE IF NOT EXISTS `premium_features` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `admin_id` INT NOT NULL,
    `feature_name` VARCHAR(100) NOT NULL,
    `is_active` TINYINT(1) DEFAULT 0,
    `activated_at` DATETIME,
    `expires_at` DATETIME,
    `cancelled_at` DATETIME,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_admin_feature` (`admin_id`, `feature_name`),
    FOREIGN KEY (`admin_id`) REFERENCES `admins`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- 17. Réservations
-- ============================================================
CREATE TABLE IF NOT EXISTS `reservations` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `admin_id` INT NOT NULL,
    `customer_name` VARCHAR(255) NOT NULL,
    `customer_phone` VARCHAR(50) NOT NULL,
    `customer_email` VARCHAR(255),
    `reservation_date` DATE NOT NULL,
    `reservation_time` TIME NOT NULL,
    `party_size` INT NOT NULL DEFAULT 2,
    `special_requests` TEXT,
    `status` ENUM('pending', 'confirmed', 'rejected', 'completed', 'cancelled', 'no_show') DEFAULT 'pending',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`admin_id`) REFERENCES `admins`(`id`) ON DELETE CASCADE,
    INDEX `idx_admin_status` (`admin_id`, `status`),
    INDEX `idx_admin_date` (`admin_id`, `reservation_date`)
) ENGINE=InnoDB;

-- ============================================================
-- 18. Visites du site vitrine (statistiques)
-- ============================================================
CREATE TABLE IF NOT EXISTS `site_visits` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `admin_id` INT NOT NULL,
    `visitor_hash` VARCHAR(64) NOT NULL,
    `user_agent` VARCHAR(512),
    `referrer` VARCHAR(1024),
    `device_type` VARCHAR(20),
    `browser` VARCHAR(50),
    `page_path` VARCHAR(255),
    `visited_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`admin_id`) REFERENCES `admins`(`id`) ON DELETE CASCADE,
    INDEX `idx_admin_visited` (`admin_id`, `visited_at`),
    INDEX `idx_admin_hash` (`admin_id`, `visitor_hash`)
) ENGINE=InnoDB;

-- ============================================================
-- 19. Cache des avis Google
-- ============================================================
CREATE TABLE IF NOT EXISTS `google_reviews_cache` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `place_id` VARCHAR(255) NOT NULL,
    `data` LONGTEXT NOT NULL,
    `cached_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_place` (`place_id`)
) ENGINE=InnoDB;

-- ============================================================
-- 20. Étages du plan de salle
-- ============================================================
CREATE TABLE IF NOT EXISTS `floors` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `admin_id` INT NOT NULL,
    `name` VARCHAR(100) NOT NULL DEFAULT 'Salle principale',
    `display_order` INT DEFAULT 0,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`admin_id`) REFERENCES `admins`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- 21. Tables du restaurant
-- ============================================================
CREATE TABLE IF NOT EXISTS `restaurant_tables` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `floor_id` INT NOT NULL,
    `table_number` VARCHAR(20) NOT NULL,
    `seats` INT DEFAULT 4,
    `x` FLOAT DEFAULT 0,
    `y` FLOAT DEFAULT 0,
    `width` FLOAT DEFAULT 80,
    `height` FLOAT DEFAULT 80,
    `shape` ENUM('square', 'round') DEFAULT 'square',
    `rotation` FLOAT DEFAULT 0,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`floor_id`) REFERENCES `floors`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- 22. Éléments décoratifs du plan de salle
-- ============================================================
CREATE TABLE IF NOT EXISTS `restaurant_elements` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `floor_id` INT NOT NULL,
    `element_type` VARCHAR(50) NOT NULL,
    `x` FLOAT DEFAULT 0,
    `y` FLOAT DEFAULT 0,
    `width` FLOAT DEFAULT 80,
    `height` FLOAT DEFAULT 80,
    `rotation` FLOAT DEFAULT 0,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`floor_id`) REFERENCES `floors`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- 23. Feedbacks beta
-- ============================================================
CREATE TABLE IF NOT EXISTS `feedbacks` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `admin_id` INT NOT NULL,
    `name` VARCHAR(255),
    `email` VARCHAR(255),
    `rating` INT,
    `ease_of_use` VARCHAR(50),
    `favorite_feature` TEXT,
    `improvements` TEXT,
    `comments` TEXT,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`admin_id`) REFERENCES `admins`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- 24. Réinitialisation de mot de passe
-- ============================================================
CREATE TABLE IF NOT EXISTS `password_resets` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `email` VARCHAR(255) NOT NULL,
    `token` VARCHAR(255) NOT NULL,
    `expires_at` DATETIME NOT NULL,
    `used` TINYINT(1) DEFAULT 0,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_token` (`token`),
    INDEX `idx_email` (`email`)
) ENGINE=InnoDB;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- Fin du schéma
-- ============================================================
