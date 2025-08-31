-- IKIRAHA Food Delivery App Database Schema
-- Created for XAMPP/MySQL environment
-- Supports 4 user types: Client, Merchant, Accountant, Super Admin

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- Create database
CREATE DATABASE IF NOT EXISTS `ikiraha_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `ikiraha_db`;

-- --------------------------------------------------------
-- Table structure for table `users`
-- --------------------------------------------------------

CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uuid` varchar(36) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `role` enum('client','merchant','accountant','super_admin') NOT NULL DEFAULT 'client',
  `status` enum('active','inactive','suspended') NOT NULL DEFAULT 'active',
  `email_verified` tinyint(1) NOT NULL DEFAULT 0,
  `profile_image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uuid` (`uuid`),
  UNIQUE KEY `email` (`email`),
  KEY `role` (`role`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `user_addresses`
-- --------------------------------------------------------

CREATE TABLE `user_addresses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `type` enum('home','work','other') NOT NULL DEFAULT 'home',
  `address` text NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `restaurants`
-- --------------------------------------------------------

CREATE TABLE `restaurants` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uuid` varchar(36) NOT NULL,
  `merchant_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text,
  `image` varchar(255) DEFAULT NULL,
  `rating` decimal(2,1) DEFAULT 0.0,
  `delivery_time` varchar(50) DEFAULT NULL,
  `delivery_fee` int(11) DEFAULT 0,
  `status` enum('active','inactive','suspended') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uuid` (`uuid`),
  KEY `merchant_id` (`merchant_id`),
  KEY `status` (`status`),
  FOREIGN KEY (`merchant_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `categories`
-- --------------------------------------------------------

CREATE TABLE `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `icon` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `products`
-- --------------------------------------------------------

CREATE TABLE `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uuid` varchar(36) NOT NULL,
  `restaurant_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text,
  `price` int(11) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `status` enum('available','unavailable','discontinued') NOT NULL DEFAULT 'available',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uuid` (`uuid`),
  KEY `restaurant_id` (`restaurant_id`),
  KEY `category_id` (`category_id`),
  KEY `status` (`status`),
  KEY `is_featured` (`is_featured`),
  FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `orders`
-- --------------------------------------------------------

CREATE TABLE `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uuid` varchar(36) NOT NULL,
  `order_number` varchar(20) NOT NULL,
  `client_id` int(11) NOT NULL,
  `restaurant_id` int(11) NOT NULL,
  `status` enum('pending','confirmed','preparing','ready','out_for_delivery','delivered','cancelled') NOT NULL DEFAULT 'pending',
  `total_amount` int(11) NOT NULL,
  `delivery_fee` int(11) NOT NULL DEFAULT 0,
  `payment_method` enum('mtn_rwanda','airtel_rwanda','cash') NOT NULL,
  `payment_phone` varchar(20) DEFAULT NULL,
  `payment_status` enum('pending','paid','failed','refunded') NOT NULL DEFAULT 'pending',
  `delivery_address` text NOT NULL,
  `delivery_phone` varchar(20) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `estimated_delivery_time` datetime DEFAULT NULL,
  `delivered_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uuid` (`uuid`),
  UNIQUE KEY `order_number` (`order_number`),
  KEY `client_id` (`client_id`),
  KEY `restaurant_id` (`restaurant_id`),
  KEY `status` (`status`),
  KEY `payment_status` (`payment_status`),
  FOREIGN KEY (`client_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT,
  FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `order_items`
-- --------------------------------------------------------

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `product_id` (`product_id`),
  FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `transactions`
-- --------------------------------------------------------

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uuid` varchar(36) NOT NULL,
  `order_id` int(11) NOT NULL,
  `transaction_id` varchar(100) DEFAULT NULL,
  `amount` int(11) NOT NULL,
  `payment_method` enum('mtn_rwanda','airtel_rwanda','cash') NOT NULL,
  `status` enum('pending','completed','failed','cancelled','refunded') NOT NULL DEFAULT 'pending',
  `reference` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uuid` (`uuid`),
  KEY `order_id` (`order_id`),
  KEY `status` (`status`),
  FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `user_favorites`
-- --------------------------------------------------------

CREATE TABLE `user_favorites` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_product` (`user_id`, `product_id`),
  KEY `user_id` (`user_id`),
  KEY `product_id` (`product_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `auth_tokens`
-- --------------------------------------------------------

CREATE TABLE `auth_tokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `type` enum('access','refresh') NOT NULL DEFAULT 'access',
  `expires_at` timestamp NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`),
  KEY `user_id` (`user_id`),
  KEY `expires_at` (`expires_at`),
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `notifications`
-- --------------------------------------------------------

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `type` enum('order','promotion','system','payment') NOT NULL DEFAULT 'system',
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `is_read` (`is_read`),
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Insert sample data
-- --------------------------------------------------------

-- Insert categories
INSERT INTO `categories` (`name`, `icon`) VALUES
('Ice Cream', 'images/ice-cream.png'),
('Pizza', 'images/pizza.png'),
('Salad', 'images/salad.png'),
('Burger', 'images/burger.png'),
('Sushi', 'images/salad.png'),
('Pasta', 'images/pizza.png');

-- Insert default users
INSERT INTO `users` (`uuid`, `email`, `password_hash`, `name`, `phone`, `role`, `status`, `email_verified`) VALUES
('550e8400-e29b-41d4-a716-446655440000', 'admin@ikiraha.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Super Admin', '+250788123456', 'super_admin', 'active', 1),
('550e8400-e29b-41d4-a716-446655440001', 'merchant@ikiraha.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Restaurant Owner', '+250788123457', 'merchant', 'active', 1),
('550e8400-e29b-41d4-a716-446655440002', 'accountant@ikiraha.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Financial Manager', '+250788123458', 'accountant', 'active', 1),
('550e8400-e29b-41d4-a716-446655440003', 'client@ikiraha.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'John Doe', '+250788123459', 'client', 'active', 1);

-- Insert sample restaurants
INSERT INTO `restaurants` (`uuid`, `merchant_id`, `name`, `description`, `image`, `rating`, `delivery_time`, `delivery_fee`, `status`) VALUES
('660e8400-e29b-41d4-a716-446655440000', 2, 'Avuma Restaurant', 'Delicious Italian and American cuisine', 'images/avuma-restaurant.jpg', 4.5, '25-30 min', 500, 'active'),
('660e8400-e29b-41d4-a716-446655440001', 2, 'Blessing Restaurant', 'Fresh sushi and Asian fusion', 'images/blessing-restaurant.jpg', 4.3, '20-25 min', 300, 'active'),
('660e8400-e29b-41d4-a716-446655440002', 2, 'Glass Restaurant', 'Premium dining experience', 'images/glass-restaurant.jpg', 4.7, '30-35 min', 700, 'active'),
('660e8400-e29b-41d4-a716-446655440003', 2, 'Ice Restaurant', 'Cool treats and healthy options', 'images/ice-restaurant.jpg', 4.2, '15-20 min', 200, 'active'),
('660e8400-e29b-41d4-a716-446655440004', 2, 'Ikigugu Restaurant', 'Traditional and modern cuisine', 'images/ikigugu-restaurant.jpg', 4.6, '25-30 min', 400, 'active'),
('660e8400-e29b-41d4-a716-446655440005', 2, 'Peace Restaurant', 'Peaceful dining with great food', 'images/peace-restaurant.webp', 4.4, '20-25 min', 350, 'active');

-- Insert sample products
INSERT INTO `products` (`uuid`, `restaurant_id`, `category_id`, `name`, `description`, `price`, `image`, `is_featured`, `status`) VALUES
('770e8400-e29b-41d4-a716-446655440000', 1, 3, 'Fresh Garden Salad', 'Mixed greens with fresh vegetables and dressing', 2700, 'images/salad2.png', 1, 'available'),
('770e8400-e29b-41d4-a716-446655440001', 1, 3, 'Chicken Caesar Salad', 'Grilled chicken with romaine lettuce and Caesar dressing', 3850, 'images/salad3.png', 1, 'available'),
('770e8400-e29b-41d4-a716-446655440002', 1, 3, 'Fruit Salad Bowl', 'Assorted fresh fruits with yogurt dressing', 2600, 'images/salad4.png', 1, 'available'),
('770e8400-e29b-41d4-a716-446655440003', 1, 4, 'Classic Burger', 'Juicy beef patty with fresh vegetables and special sauce', 3500, 'images/salad2.png', 0, 'available'),
('770e8400-e29b-41d4-a716-446655440004', 1, 2, 'Margherita Pizza', 'Classic pizza with tomato and fresh mozzarella cheese', 4200, 'images/salad3.png', 0, 'available'),
('770e8400-e29b-41d4-a716-446655440005', 1, 1, 'Vanilla Ice Cream', 'Creamy vanilla ice cream with chocolate toppings', 1800, 'images/salad4.png', 0, 'available'),
('770e8400-e29b-41d4-a716-446655440006', 1, 3, 'Greek Salad', 'Traditional Greek salad with feta cheese and olives', 2900, 'images/salad2.png', 0, 'available'),
('770e8400-e29b-41d4-a716-446655440007', 1, 2, 'BBQ Chicken Pizza', 'Smoky BBQ sauce with grilled chicken and red onions', 4500, 'images/salad3.png', 0, 'available'),
('770e8400-e29b-41d4-a716-446655440008', 1, 1, 'Chocolate Sundae', 'Vanilla ice cream with hot fudge and nuts', 2200, 'images/salad2.png', 0, 'available'),
('770e8400-e29b-41d4-a716-446655440009', 1, 4, 'Bacon Cheeseburger', 'Beef patty with crispy bacon and melted cheese', 3800, 'images/salad4.png', 0, 'available'),
('770e8400-e29b-41d4-a716-446655440010', 2, 5, 'California Roll', 'Fresh crab with avocado and cucumber', 3200, 'images/salad2.png', 0, 'available'),
('770e8400-e29b-41d4-a716-446655440011', 2, 6, 'Caesar Pasta', 'Creamy Caesar sauce with pasta and parmesan', 3100, 'images/salad3.png', 0, 'available'),
('770e8400-e29b-41d4-a716-446655440012', 2, 5, 'Spicy Tuna Roll', 'Spicy tuna with cucumber and sesame seeds', 3400, 'images/salad2.png', 0, 'available'),
('770e8400-e29b-41d4-a716-446655440013', 3, 4, 'Veggie Burger', 'Plant-based patty with fresh vegetables', 3300, 'images/salad4.png', 0, 'available'),
('770e8400-e29b-41d4-a716-446655440014', 4, 1, 'Chocolate Chip Ice Cream', 'Creamy ice cream with chocolate chunks', 2000, 'images/salad2.png', 0, 'available');

COMMIT;