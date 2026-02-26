-- Create database
CREATE DATABASE IF NOT EXISTS `bazarche`;
USE `bazarche`;

-- Create users table
CREATE TABLE IF NOT EXISTS `users` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `username` varchar(50) NOT NULL,
    `email` varchar(100) NOT NULL,
    `password` varchar(255) NOT NULL,
    `full_name` varchar(100) NOT NULL,
    `phone` varchar(20) NOT NULL,
    `address` text NOT NULL,
    `role` enum('user', 'admin') NOT NULL DEFAULT 'user',
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`id`),
    UNIQUE KEY `username` (`username`),
    UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create categories table
CREATE TABLE IF NOT EXISTS `categories` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(100) NOT NULL,
    `description` text DEFAULT NULL,
    `parent_id` int(11) DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `parent_id` (`parent_id`),
    FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create products table
CREATE TABLE IF NOT EXISTS `products` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(200) NOT NULL,
    `description` text NOT NULL,
    `price` decimal(10,2) NOT NULL,
    `discount_price` decimal(10,2) DEFAULT NULL,
    `stock_quantity` int(11) NOT NULL DEFAULT 0,
    `category_id` int(11) NOT NULL,
    `image` varchar(255) DEFAULT NULL,
    `status` enum('active', 'inactive', 'out_of_stock') NOT NULL DEFAULT 'active',
    `created_by` int(11) NOT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `category_id` (`category_id`),
    KEY `created_by` (`created_by`),
    FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create orders table
CREATE TABLE IF NOT EXISTS `orders` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL,
    `order_number` varchar(50) NOT NULL,
    `total_amount` decimal(10,2) NOT NULL,
    `status` enum('pending', 'processing', 'shipped', 'delivered', 'cancelled') NOT NULL DEFAULT 'pending',
    `payment_method` varchar(50) DEFAULT NULL,
    `payment_status` enum('pending', 'paid', 'failed', 'refunded') NOT NULL DEFAULT 'pending',
    `shipping_address` text NOT NULL,
    `notes` text DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`id`),
    UNIQUE KEY `order_number` (`order_number`),
    KEY `user_id` (`user_id`),
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create order_items table
CREATE TABLE IF NOT EXISTS `order_items` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `order_id` int(11) NOT NULL,
    `product_id` int(11) NOT NULL,
    `quantity` int(11) NOT NULL,
    `price` decimal(10,2) NOT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `order_id` (`order_id`),
    KEY `product_id` (`product_id`),
    FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create cart table
CREATE TABLE IF NOT EXISTS `cart` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL,
    `product_id` int(11) NOT NULL,
    `quantity` int(11) NOT NULL DEFAULT 1,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`id`),
    UNIQUE KEY `user_product` (`user_id`,`product_id`),
    KEY `user_id` (`user_id`),
    KEY `product_id` (`product_id`),
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create wishlist table
CREATE TABLE IF NOT EXISTS `wishlist` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL,
    `product_id` int(11) NOT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    UNIQUE KEY `user_product` (`user_id`,`product_id`),
    KEY `user_id` (`user_id`),
    KEY `product_id` (`product_id`),
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create reviews table
CREATE TABLE IF NOT EXISTS `reviews` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `product_id` int(11) NOT NULL,
    `user_id` int(11) NOT NULL,
    `rating` int(1) NOT NULL COMMENT 'Rating from 1 to 5',
    `comment` text DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `product_id` (`product_id`),
    KEY `user_id` (`user_id`),
    FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    UNIQUE KEY `user_product_review` (`user_id`,`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create admin user (password: admin123)
INSERT INTO `users` (`username`, `email`, `password`, `full_name`, `role`) VALUES
('admin', 'admin@bazarche.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin');

-- Create sample categories
INSERT INTO `categories` (`name`, `description`) VALUES
('الکترونیک', 'لوازم الکترونیکی و دیجیتال'),
('لباس', 'پوشاک و لباس‌های مردانه، زنانه و کودک'),
('خوراکی', 'غذاهای آماده و مواد غذایی'),
('کتاب', 'کتاب‌های مختلف در زمینه‌های گوناگون'),
('خانه و آشپزخانه', 'لوازم خانگی و وسایل آشپزخانه');

-- Create sample products
INSERT INTO `products` (`name`, `description`, `price`, `discount_price`, `stock_quantity`, `category_id`, `image`, `created_by`) VALUES
('موبایل سامسونگ Galaxy S21', 'موبایل هوشمند با صفحه نمایش 6.2 اینچی و دوربین 64 مگاپیکسلی', 35000000, 32000000, 10, 1, 'samsung-s21.jpg', 1),
('لپتاپ Dell XPS 13', 'لپتاپ نازک و سبک با پردازنده Intel Core i7', 45000000, 42000000, 5, 1, 'dell-xps13.jpg', 1),
('تیشرت مردانه', 'تیشرت نخی راحت با طرح‌های مختلف', 250000, NULL, 50, 2, 'tshirt.jpg', 1),
('شلوار جین', 'شلوار جین کلاسیک با جنس مرغوب', 800000, NULL, 30, 2, 'jeans.jpg', 1),
('پیتزا پپرونی', 'پیتزا تازه با پنیر و پپرونی اصل', 450000, NULL, 20, 3, 'pizza.jpg', 1),
('برگه نان بربری', 'نان تازه و خوشمزه بربری', 50000, NULL, 100, 3, 'barbari.jpg', 1),
('کتاب برنامه‌نویسی PHP', 'کتاب کامل برای یادگیری PHP', 150000, NULL, 25, 4, 'php-book.jpg', 1),
('کتاب طراحی وب', 'کتاب آموزش طراحی وب با HTML و CSS', 180000, NULL, 20, 4, 'web-design.jpg', 1),
('ماگ استیل', 'ماگ استیل با قابلیت نگهداری دمای طولانی', 200000, NULL, 40, 5, 'steel-mug.jpg', 1),
('ظرف پلاستیکی', 'ظرف پلاستیکی مناسب برای نگهداری مواد غذایی', 50000, NULL, 60, 5, 'plastic-container.jpg', 1);