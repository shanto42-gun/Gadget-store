-- TechGadget Store Database Schema
SET FOREIGN_KEY_CHECKS=0;
-- Import this file into phpMyAdmin or run via MySQL CLI

CREATE DATABASE IF NOT EXISTS `techgadget_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `techgadget_db`;

-- -------------------------------------------------------
-- Settings
-- -------------------------------------------------------
CREATE TABLE `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `site_name` varchar(100) DEFAULT 'TechGadget Store',
  `site_logo` varchar(255) DEFAULT '',
  `footer_text` varchar(255) DEFAULT '© 2026 TechGadget Store. All rights reserved.',
  `currency` varchar(10) DEFAULT 'BDT',
  `currency_symbol` varchar(5) DEFAULT '৳',
  `shipping_cost` decimal(10,2) DEFAULT 60.00,
  `email` varchar(100) DEFAULT 'support@techgadget.com',
  `phone` varchar(30) DEFAULT '+880 1700-000000',
  `address` varchar(255) DEFAULT 'Dhaka, Bangladesh',
  `facebook` varchar(255) DEFAULT '#',
  `instagram` varchar(255) DEFAULT '#',
  `youtube` varchar(255) DEFAULT '#',
  `twitter` varchar(255) DEFAULT '#',
  `bkash_number` varchar(20) DEFAULT '',
  `nagad_number` varchar(20) DEFAULT '',
  `maintenance_mode` tinyint(1) DEFAULT 0,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `settings` (`id`) VALUES (1);

-- -------------------------------------------------------
-- Admins
-- -------------------------------------------------------
CREATE TABLE `admins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL UNIQUE,
  `password` varchar(255) NOT NULL,
  `avatar` varchar(255) DEFAULT '',
  `role` enum('super_admin','admin') DEFAULT 'admin',
  `status` tinyint(1) DEFAULT 1,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Default admin: admin@techgadget.com / admin123
INSERT INTO `admins` (`name`, `email`, `password`, `role`) VALUES
('Super Admin', 'admin@techgadget.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'super_admin');

-- -------------------------------------------------------
-- Users
-- -------------------------------------------------------
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL UNIQUE,
  `phone` varchar(20) DEFAULT '',
  `password` varchar(255) NOT NULL,
  `avatar` varchar(255) DEFAULT '',
  `address` text DEFAULT NULL,
  `city` varchar(100) DEFAULT '',
  `status` enum('active','blocked') DEFAULT 'active',
  `email_verified` tinyint(1) DEFAULT 0,
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_token_expiry` datetime DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -------------------------------------------------------
-- Categories
-- -------------------------------------------------------
CREATE TABLE `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL UNIQUE,
  `icon` varchar(100) DEFAULT 'fas fa-microchip',
  `image` varchar(255) DEFAULT '',
  `description` text DEFAULT NULL,
  `status` tinyint(1) DEFAULT 1,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `categories` (`name`, `slug`, `icon`, `sort_order`) VALUES
('Smart Watches', 'smart-watches', 'fas fa-clock', 1),
('Earbuds & Headphones', 'earbuds', 'fas fa-headphones', 2),
('Power Banks', 'power-banks', 'fas fa-battery-full', 3),
('Phone Accessories', 'phone-accessories', 'fas fa-mobile-alt', 4),
('Bluetooth Speakers', 'speakers', 'fas fa-volume-up', 5),
('Routers & Networking', 'routers', 'fas fa-wifi', 6);

-- -------------------------------------------------------
-- Products
-- -------------------------------------------------------
CREATE TABLE `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL UNIQUE,
  `description` text DEFAULT NULL,
  `specifications` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `discount_price` decimal(10,2) DEFAULT NULL,
  `stock` int(11) DEFAULT 0,
  `sold_count` int(11) DEFAULT 0,
  `image` varchar(255) DEFAULT '',
  `gallery` text DEFAULT NULL COMMENT 'JSON array of image paths',
  `brand` varchar(100) DEFAULT '',
  `status` enum('active','inactive','draft') DEFAULT 'active',
  `featured` tinyint(1) DEFAULT 0,
  `trending` tinyint(1) DEFAULT 0,
  `new_arrival` tinyint(1) DEFAULT 1,
  `best_seller` tinyint(1) DEFAULT 0,
  `rating` decimal(3,2) DEFAULT 0.00,
  `review_count` int(11) DEFAULT 0,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE CASCADE,
  INDEX `idx_category` (`category_id`),
  INDEX `idx_status` (`status`),
  INDEX `idx_featured` (`featured`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Sample products
INSERT INTO `products` (`category_id`,`name`,`slug`,`description`,`specifications`,`price`,`discount_price`,`stock`,`sold_count`,`image`,`brand`,`status`,`featured`,`trending`,`new_arrival`,`best_seller`,`rating`,`review_count`) VALUES
(1,'ProFit Ultra Smart Watch','profit-ultra-smart-watch','Track your fitness with premium accuracy. 1.45" AMOLED display, 7-day battery life, SpO2 monitor, 100+ workout modes.','{"Display":"1.45\\" AMOLED","Battery":"7 Days","Water Resistance":"5ATM","Connectivity":"Bluetooth 5.0"}',3500.00,2799.00,50,128,'','ProFit','active',1,1,1,1,4.50,42),
(2,'SoundPro X9 TWS Earbuds','soundpro-x9-tws','30-hour playtime, ANC, IPX5 waterproof. Crystal-clear audio with deep bass.','{"Driver":"10mm Dynamic","ANC":"Yes","Battery":"30hrs total","Water":"IPX5"}',2200.00,1599.00,80,256,'','SoundPro','active',1,1,1,1,4.70,89),
(3,'PowerMax 20000mAh','powermax-20000mah','Dual USB-C + USB-A, 65W PD fast charge. Compact design, airline-safe.','{"Capacity":"20000mAh","Output":"65W PD","Ports":"2xUSB-C, 1xUSB-A","Weight":"420g"}',1800.00,1299.00,100,312,'','PowerMax','active',1,0,1,1,4.80,67),
(4,'MagLink Phone Stand','maglink-phone-stand','Magnetic wireless charging stand compatible with MagSafe. 15W fast wireless charging.','{"Compatibility":"MagSafe + Qi","Power":"15W","Material":"Aluminum + ABS","Cable":"USB-C included"}',850.00,649.00,150,445,'','MagLink','active',0,1,1,0,4.30,34),
(5,'BassBox Pro Speaker','bassbox-pro-speaker','360° surround sound, 24h battery, IPX7 waterproof, built-in mic for calls.','{"Output":"30W RMS","Battery":"24 Hours","Water":"IPX7","Connectivity":"BT 5.3"}',3200.00,2499.00,40,78,'','BassBox','active',1,1,0,0,4.60,28),
(6,'NetBoost WiFi 6 Router','netboost-wifi6-router','AX3000 dual-band WiFi 6, covers 2500 sq.ft, MU-MIMO, parental controls.','{"Standard":"Wi-Fi 6 (AX3000)","Bands":"Dual 2.4GHz+5GHz","Coverage":"2500 sq.ft","Ports":"4x Gigabit LAN"}',5500.00,4299.00,25,34,'','NetBoost','active',1,0,1,0,4.40,19),
(1,'SlimBand Fitness Tracker','slimband-fitness-tracker','Slim design with 14-day battery, heart rate, sleep tracking, and smart notifications.','{"Display":"0.96\\" OLED","Battery":"14 Days","Sensors":"HR, SpO2, Temp","Water":"IP68"}',1200.00,899.00,75,167,'','SlimBand','active',0,1,1,1,4.20,55),
(2,'NoiseFree Pro Headphones','noisefree-pro-headphones','Over-ear, 40dB ANC, 50-hour battery, foldable design, premium leather cushions.','{"Type":"Over-ear","ANC":"40dB","Battery":"50 Hours","Driver":"40mm"}',4500.00,3299.00,30,89,'','NoiseFree','active',1,0,1,1,4.60,38);

-- -------------------------------------------------------
-- Cart
-- -------------------------------------------------------
CREATE TABLE `cart` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `session_id` varchar(255) DEFAULT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) DEFAULT 1,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
  INDEX `idx_user` (`user_id`),
  INDEX `idx_session` (`session_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -------------------------------------------------------
-- Coupons
-- -------------------------------------------------------
CREATE TABLE `coupons` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(50) NOT NULL UNIQUE,
  `type` enum('percent','fixed') DEFAULT 'percent',
  `value` decimal(10,2) NOT NULL,
  `min_order` decimal(10,2) DEFAULT 0.00,
  `max_discount` decimal(10,2) DEFAULT NULL,
  `usage_limit` int(11) DEFAULT NULL,
  `used_count` int(11) DEFAULT 0,
  `expiry_date` date DEFAULT NULL,
  `status` tinyint(1) DEFAULT 1,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `coupons` (`code`,`type`,`value`,`min_order`,`usage_limit`) VALUES
('TECH10', 'percent', 10.00, 500.00, 100),
('WELCOME100', 'fixed', 100.00, 300.00, 50),
('SAVE200', 'fixed', 200.00, 1000.00, 30);

-- -------------------------------------------------------
-- Orders
-- -------------------------------------------------------
CREATE TABLE `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_number` varchar(20) NOT NULL UNIQUE,
  `user_id` int(11) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` text NOT NULL,
  `city` varchar(100) NOT NULL,
  `notes` text DEFAULT NULL,
  `payment_method` enum('cod','bkash','nagad','card') DEFAULT 'cod',
  `payment_status` enum('pending','paid','failed','refunded') DEFAULT 'pending',
  `payment_ref` varchar(100) DEFAULT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `shipping_cost` decimal(10,2) DEFAULT 60.00,
  `discount` decimal(10,2) DEFAULT 0.00,
  `coupon_code` varchar(50) DEFAULT NULL,
  `total` decimal(10,2) NOT NULL,
  `status` enum('pending','confirmed','processing','shipped','delivered','cancelled') DEFAULT 'pending',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  INDEX `idx_user` (`user_id`),
  INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -------------------------------------------------------
-- Order Items
-- -------------------------------------------------------
CREATE TABLE `order_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `product_image` varchar(255) DEFAULT '',
  `price` decimal(10,2) NOT NULL,
  `quantity` int(11) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -------------------------------------------------------
-- Reviews
-- -------------------------------------------------------
CREATE TABLE `reviews` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `rating` tinyint(1) NOT NULL DEFAULT 5,
  `title` varchar(255) DEFAULT '',
  `review` text NOT NULL,
  `image` varchar(255) DEFAULT '',
  `status` enum('pending','approved','rejected') DEFAULT 'approved',
  `helpful_count` int(11) DEFAULT 0,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  INDEX `idx_product` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Sample reviews (added after users are created)

-- -------------------------------------------------------
-- Wishlist
-- -------------------------------------------------------
CREATE TABLE `wishlist` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_wishlist` (`user_id`,`product_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -------------------------------------------------------
-- Notifications
-- -------------------------------------------------------
CREATE TABLE `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `type` varchar(50) DEFAULT 'info',
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
SET FOREIGN_KEY_CHECKS=1;
