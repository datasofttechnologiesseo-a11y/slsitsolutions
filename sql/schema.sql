-- SLS IT Solutions — Admin / Enquiries / Testimonials schema
-- Run on database: slsdb
-- Run order: 1. schema.sql  2. blog.sql
-- (Or run install.sql which sources both in the correct order.)

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- -----------------------------
-- Admins
-- -----------------------------
CREATE TABLE IF NOT EXISTS `admins` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_admin_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------
-- Enquiries (contact form submissions)
-- -----------------------------
CREATE TABLE IF NOT EXISTS `enquiries` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(150) NOT NULL,
  `company` VARCHAR(150) DEFAULT NULL,
  `email` VARCHAR(190) NOT NULL,
  `phone` VARCHAR(40) DEFAULT NULL,
  `service` VARCHAR(60) DEFAULT NULL,
  `message` TEXT,
  `ip` VARCHAR(45) DEFAULT NULL,
  `user_agent` VARCHAR(255) DEFAULT NULL,
  `is_read` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_enq_created` (`created_at`),
  KEY `idx_enq_isread` (`is_read`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------
-- Testimonials
-- -----------------------------
CREATE TABLE IF NOT EXISTS `testimonials` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `client_name` VARCHAR(120) NOT NULL,
  `company` VARCHAR(150) DEFAULT NULL,
  `quote` TEXT NOT NULL,
  `initials` VARCHAR(4) DEFAULT NULL,
  `avatar_color` VARCHAR(20) NOT NULL DEFAULT 'blue',
  `rating` TINYINT UNSIGNED NOT NULL DEFAULT 5,
  `sort_order` INT NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_t_active` (`is_active`),
  KEY `idx_t_sort` (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------
-- Login attempts (brute-force lockout)
-- -----------------------------
CREATE TABLE IF NOT EXISTS `login_attempts` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `ip` VARCHAR(45) NOT NULL,
  `email` VARCHAR(190) DEFAULT NULL,
  `success` TINYINT(1) NOT NULL DEFAULT 0,
  `attempted_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_la_ip_time` (`ip`, `attempted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------
-- Seed: default admin
-- email:    admin@slsitsolutions.com
-- password: Admin@12345   (CHANGE AFTER FIRST LOGIN)
-- bcrypt hash generated with PHP password_hash(..., PASSWORD_BCRYPT)
-- -----------------------------
INSERT IGNORE INTO `admins` (`name`, `email`, `password_hash`)
VALUES ('Administrator', 'admin@slsitsolutions.com', '$2y$10$REPLACED_AT_RUNTIME');

-- -----------------------------
-- Seed: existing testimonials from index.php
-- -----------------------------
INSERT IGNORE INTO `testimonials` (`id`, `client_name`, `company`, `quote`, `initials`, `avatar_color`, `rating`, `sort_order`, `is_active`)
VALUES
(1, 'Rajesh Kumar', 'Rahul Technic, Faridabad',
 'SLS IT Solutions completely transformed our IT infrastructure. Their team handled our server migration seamlessly with zero downtime. We finally have the security and reliability our operations demand.',
 'RK', 'blue', 5, 1, 1),
(2, 'Amit Mehta', 'AVON Industrial Packaging, Delhi',
 'We approached SLS IT Solutions for our data backup and disaster recovery setup. Their response time is exceptional and the solution they implemented gives us complete peace of mind. Highly recommend for any manufacturing business.',
 'AM', 'green', 5, 2, 1),
(3, 'Priya Sharma', 'Indogulf Cropsciences, NCR',
 'As a fast-growing agri-business, our data security was a real concern. SLS IT Solutions provided enterprise-grade cybersecurity that actually fits our budget. Their team is always just a call away — that''s rare in India.',
 'PS', 'purple', 5, 3, 1);

SET FOREIGN_KEY_CHECKS = 1;
