-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Nov 11, 2025 at 10:17 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.0.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ban_banh`
--
CREATE DATABASE IF NOT EXISTS `ban_banh` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `ban_banh`;

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

DROP TABLE IF EXISTS `cart`;
CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `session_id` varchar(255) NOT NULL,
  `product_id` int(11) NOT NULL,
  `size` varchar(50) DEFAULT '20cm',
  `flavor` varchar(50) DEFAULT 'Vani',
  `quantity` int(11) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `created_at`, `updated_at`) VALUES
(1, 'Bánh sinh nhật', 'banh-sinh-nhat', NULL, '2025-11-08 09:31:24', '2025-11-08 09:31:24'),
(2, 'Bánh mì & bánh mặn', 'banh-mi-banh-man', NULL, '2025-11-08 09:31:24', '2025-11-08 09:31:24'),
(3, 'Cookies & Minicake', 'cookies-minicake', NULL, '2025-11-08 09:31:24', '2025-11-08 09:31:24'),
(4, 'Dự án đặc biệt', 'du-an-dac-biet', NULL, '2025-11-08 09:31:24', '2025-11-08 09:31:24'),
(5, 'Bánh kem mousse', 'banh-kem-mousse', NULL, '2025-11-08 09:31:24', '2025-11-08 09:31:24');

-- --------------------------------------------------------

--
-- Table structure for table `contacts`
--

DROP TABLE IF EXISTS `contacts`;
CREATE TABLE `contacts` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `session_id` varchar(255) DEFAULT NULL,
  `full_name` varchar(100) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `address` text NOT NULL,
  `note` text DEFAULT NULL,
  `total_amount` decimal(12,0) NOT NULL,
  `status` enum('pending','confirmed','shipping','delivered','cancelled') DEFAULT 'pending',
  `payment_method` enum('cod','banking','momo') DEFAULT 'cod',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

DROP TABLE IF EXISTS `order_items`;
CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `size` varchar(50) DEFAULT NULL,
  `flavor` varchar(50) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(12,0) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `posts`
--

DROP TABLE IF EXISTS `posts`;
CREATE TABLE `posts` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `content` text NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `category` enum('news','promotion') DEFAULT 'news',
  `is_published` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `posts`
--

INSERT INTO `posts` (`id`, `title`, `slug`, `content`, `image`, `category`, `is_published`, `created_at`, `updated_at`) VALUES
(1, 'Khuyến mãi 20% bánh sinh nhật', 'khuyen-mai-20-banh-sinh-nhat', 'Từ 1/11 - 15/11, giảm 20% tất cả bánh sinh nhật...', 'km-20.jpg', 'promotion', 1, '2025-11-08 09:31:24', '2025-11-08 09:31:24'),
(2, 'Mở cửa hàng mới tại Quận 7', 'mo-cua-hang-quan-7', 'Anh Hoa Bakery chính thức khai trương...', 'store-q7.jpg', 'news', 1, '2025-11-08 09:31:24', '2025-11-08 09:31:24');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `slug` varchar(200) DEFAULT NULL,
  `price` decimal(12,0) NOT NULL,
  `image` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `short_description` varchar(500) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `stock` int(11) DEFAULT 100,
  `is_featured` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `slug`, `price`, `image`, `description`, `short_description`, `category_id`, `stock`, `is_featured`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Bánh kem dâu tây 20cm', 'banh-kem-dau-tay-20cm', 450000, 'banh-kem-dau.jpg', NULL, 'Bánh kem tươi vị dâu, mềm mịn, ngọt thanh', 1, 100, 0, 1, '2025-11-08 09:31:24', '2025-11-08 09:31:24'),
(2, 'Bánh mì pate 5k', 'banh-mi-pate-5k', 5000, 'banh-mi-pate.jpg', NULL, 'Bánh mì pate truyền thống, nóng hổi', 2, 100, 0, 1, '2025-11-08 09:31:24', '2025-11-08 09:31:24'),
(3, 'Cookie socola chip', 'cookie-socola-chip', 35000, 'cookie-choco.jpg', NULL, 'Cookie giòn tan, socola đậm đà', 3, 100, 0, 1, '2025-11-08 09:31:24', '2025-11-08 09:31:24'),
(4, 'Bánh sinh nhật 3 tầng', 'banh-sinh-nhat-3-tang', 2500000, 'banh-3-tang.jpg', NULL, 'Dành cho tiệc lớn, thiết kế theo yêu cầu', 4, 100, 0, 1, '2025-11-08 09:31:24', '2025-11-08 09:31:24'),
(5, 'Bánh bông lan ', 'banh-bong-lan', 65000, 'banh-bong-lan-trung-muoi-gau.webp', NULL, 'Vị chua ngọt thanh mát, topping trái cây', 1, 100, 0, 1, '2025-11-08 09:31:24', '2025-11-09 09:46:05');

-- --------------------------------------------------------

--
-- Table structure for table `product_images`
--

DROP TABLE IF EXISTS `product_images`;
CREATE TABLE `product_images` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `image` varchar(255) NOT NULL,
  `is_main` tinyint(1) DEFAULT 0,
  `alt_text` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_images`
--

INSERT INTO `product_images` (`id`, `product_id`, `image`, `is_main`, `alt_text`, `created_at`) VALUES
(1, 1, 'banh-kem-dau-1.jpg', 1, 'Bánh kem dâu tây mặt trước', '2025-11-08 09:31:24'),
(2, 1, 'banh-kem-dau-2.jpg', 0, 'Cận cảnh lớp kem', '2025-11-08 09:31:24'),
(3, 1, 'banh-kem-dau-3.jpg', 0, 'Cắt lát bánh', '2025-11-08 09:31:24'),
(4, 2, 'banh-mi-pate.jpg', 1, 'Bánh mì pate nóng', '2025-11-08 09:31:24');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

DROP TABLE IF EXISTS `reviews`;
CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `rating` tinyint(4) DEFAULT NULL CHECK (`rating` between 1 and 5),
  `comment` text DEFAULT NULL,
  `is_approved` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`id`, `product_id`, `user_id`, `rating`, `comment`, `is_approved`, `created_at`) VALUES
(1, 1, 2, 5, 'Bánh rất ngon, giao hàng nhanh!', 1, '2025-11-08 09:31:24'),
(2, 1, NULL, 4, 'Đẹp mắt, vị ổn, hơi ngọt', 1, '2025-11-08 09:31:24');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `role` enum('customer','admin') DEFAULT 'customer',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users` (với mật khẩu đã hash)
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `full_name`, `phone`, `address`, `role`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'admin@anhhoa.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Quản trị viên', NULL, NULL, 'admin', 1, '2025-11-08 09:31:24', '2025-11-08 09:31:24'),
(2, 'khachhang1', 'khach@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Nguyễn Văn A', NULL, NULL, 'customer', 1, '2025-11-08 09:31:24', '2025-11-08 09:31:24');

-- --------------------------------------------------------

--
-- Indexes + AUTO_INCREMENT + Constraints (giữ nguyên)
--

ALTER TABLE `cart` ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `unique_cart_item` (`session_id`,`product_id`,`size`,`flavor`), ADD KEY `user_id` (`user_id`), ADD KEY `product_id` (`product_id`);
ALTER TABLE `categories` ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `slug` (`slug`);
ALTER TABLE `contacts` ADD PRIMARY KEY (`id`);
ALTER TABLE `orders` ADD PRIMARY KEY (`id`), ADD KEY `user_id` (`user_id`);
ALTER TABLE `order_items` ADD PRIMARY KEY (`id`), ADD KEY `order_id