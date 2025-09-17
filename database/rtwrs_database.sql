-- Database: rtwrs
-- Rameshwar Traditional Wear Rental System

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- Create database
CREATE DATABASE IF NOT EXISTS `rtwrs1` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `rtwrs1`;

-- Table structure for table `categories`
CREATE TABLE `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert sample categories
INSERT INTO `categories` (`id`, `name`, `description`) VALUES
(1, 'Sherwani', 'Traditional Indian formal wear for men'),
(2, 'Kurta', 'Traditional Indian shirt for men'),
(3, 'Blazer', 'Modern formal blazers'),
(4, 'Indo-Western', 'Fusion of Indian and Western styles');

-- Table structure for table `products`
CREATE TABLE `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_number` varchar(15) NOT NULL,
  `category_id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `description` text,
  `image_url` text,
  `size` varchar(100),
  `color` varchar(100),
  `price_per_day` decimal(10,2) NOT NULL,
  `quantity_available` int(11) NOT NULL DEFAULT 1,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert sample products
INSERT INTO `products` (`product_number`, `category_id`, `product_name`, `description`, `image_url`, `size`, `color`, `price_per_day`, `quantity_available`) VALUES
('PRD001', 1, 'Royal Blue Sherwani', 'Elegant royal blue sherwani with intricate embroidery', '../assets/images/sherwani1.jpg,../assets/images/sherwani1_2.jpg', 'M,L,XL', 'royal blue,navy blue', 1500.00, 3),
('PRD002', 1, 'Golden Sherwani', 'Traditional golden sherwani perfect for weddings', '../assets/images/sherwani2.jpg,../assets/images/sherwani2_2.jpg', 'S,M,L,XL', 'gold,cream', 1800.00, 2),
('PRD003', 1, 'Maroon Velvet Sherwani', 'Luxurious maroon velvet sherwani with gold embroidery', '../assets/images/sherwani3.jpg', 'M,L,XL,XXL', 'maroon,burgundy', 2000.00, 1),
('PRD004', 2, 'White Cotton Kurta', 'Comfortable white cotton kurta for festivals', '../assets/images/kurta1.jpg,../assets/images/kurta1_2.jpg', 'S,M,L,XL,XXL', 'white,off-white', 500.00, 5),
('PRD005', 2, 'Printed Silk Kurta', 'Elegant printed silk kurta with matching pajama', '../assets/images/kurta2.jpg', 'M,L,XL', 'multicolor,blue,green', 800.00, 3),
('PRD006', 2, 'Black Cotton Kurta', 'Stylish black cotton kurta with embroidery', '../assets/images/kurta3.jpg', 'S,M,L,XL', 'black,charcoal', 600.00, 4),
('PRD007', 3, 'Navy Blue Blazer', 'Classic navy blue blazer for formal occasions', '../assets/images/blazer1.jpg', 'M,L,XL', 'navy blue,dark blue', 1200.00, 2),
(3, 'Grey Formal Blazer', 'Professional grey blazer with modern fit', '../assets/images/blazer2.jpg', 'S,M,L,XL', 'grey,charcoal', 1000.00, 3),
(4, 'Indo-Western Jacket', 'Trendy indo-western jacket with kurta', '../assets/images/indo1.jpg', 'M,L,XL', 'black,brown', 1300.00, 2);

-- Table structure for table `users`
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_name` varchar(25) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20),
  `address` text,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `user_name` (`user_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert sample users
INSERT INTO `users` (`user_name`, `name`, `email`, `phone`, `address`, `password`) VALUES
('johndoe', 'John Doe', 'john@example.com', '9876543210', '123 Main St, City', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('janesmith', 'Jane Smith', 'jane@example.com', '9876543211', '456 Oak Ave, City', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('rajpatel', 'Raj Patel', 'raj@example.com', '9876543212', '789 Pine Rd, City', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Table structure for table `bookings`
CREATE TABLE `bookings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `selected_size` varchar(10),
  `selected_color` varchar(50),
  `special_requests` text,
  `payment_method` enum('COD','UPI','CARD') NOT NULL,
  `upi_id` varchar(100),
  `status` enum('pending','confirmed','cancelled','completed') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert sample bookings
INSERT INTO `bookings` (`product_id`, `user_id`, `start_date`, `end_date`, `total_price`, `selected_size`, `selected_color`, `payment_method`, `status`) VALUES
(1, 1, '2024-01-15', '2024-01-17', 4500.00, 'L', 'royal blue', 'COD', 'confirmed'),
(2, 2, '2024-01-20', '2024-01-22', 5400.00, 'M', 'gold', 'UPI', 'pending'),
(4, 3, '2024-01-25', '2024-01-26', 1000.00, 'XL', 'white', 'COD', 'completed');

-- Table structure for table `payments`
CREATE TABLE `payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `booking_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_status` enum('pending','completed','failed','refunded') NOT NULL DEFAULT 'pending',
  `payment_method` enum('COD','UPI','CARD') NOT NULL,
  `transaction_id` varchar(100),
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `booking_id` (`booking_id`),
  CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert sample payments
INSERT INTO `payments` (`booking_id`, `amount`, `payment_status`, `payment_method`) VALUES
(1, 4500.00, 'completed', 'COD'),
(2, 5400.00, 'pending', 'UPI'),
(3, 1000.00, 'completed', 'COD');

-- Table structure for table `feedback`
CREATE TABLE `feedback` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `booking_id` int(11),
  `rating` int(1) NOT NULL CHECK (`rating` >= 1 AND `rating` <= 5),
  `feedback` text,
  `image` varchar(255),
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `product_id` (`product_id`),
  KEY `booking_id` (`booking_id`),
  CONSTRAINT `feedback_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `feedback_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  CONSTRAINT `feedback_ibfk_3` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert sample feedback
INSERT INTO `feedback` (`user_id`, `product_id`, `booking_id`, `rating`, `feedback`, `image`) VALUES
(1, 1, 1, 5, 'Excellent quality sherwani! Perfect fit and beautiful embroidery.'),
(3, 4, 3, 4, 'Good quality kurta, comfortable to wear for the festival.');

-- Table structure for table `contactus`
CREATE TABLE `contactus` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20),
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table structure for table `admin_notifications`
CREATE TABLE `admin_notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` enum('booking','payment','stock','general') NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert sample notifications
INSERT INTO `admin_notifications` (`type`, `title`, `message`) VALUES
('booking', 'New Booking', 'New booking received for Royal Blue Sherwani'),
('stock', 'Low Stock Alert', 'Maroon Velvet Sherwani is running low on stock (1 remaining)'),
('payment', 'Payment Received', 'Payment of ₹4500 received for booking #1');

-- Table structure for table `cart_items`
CREATE TABLE IF NOT EXISTS `cart_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `selected_size` varchar(20),
  `selected_color` varchar(50),
  `start_date` date NOT NULL, 
  `end_date` date NOT NULL,
  `price_per_day` decimal(10,2) NOT NULL,
  `rental_days` int(11) NOT NULL DEFAULT 1,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `cart_items_ibfk_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cart_items_ibfk_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

COMMIT;
