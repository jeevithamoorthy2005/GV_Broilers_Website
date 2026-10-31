-- phpMyAdmin SQL Dump
-- version 4.8.5
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Oct 31, 2026 at 07:52 AM
-- Server version: 5.7.26
-- PHP Version: 7.2.18

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `gv-broilers`
--

-- --------------------------------------------------------

--
-- Table structure for table `aboutus`
--

DROP TABLE IF EXISTS `aboutus`;
CREATE TABLE IF NOT EXISTS `aboutus` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(140) NOT NULL,
  `description` text NOT NULL,
  `mission` text NOT NULL,
  `vision` text NOT NULL,
  `image` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `aboutus`
--

INSERT INTO `aboutus` (`id`, `title`, `description`, `mission`, `vision`, `image`) VALUES
(1, 'About GV Broilers', 'GV Broilers is committed to providing fresh and quality chicken and mutton to our customers.', 'To provide fresh, hygienic and quality meat products at reasonable prices.', 'To become a trusted choice for customers through quality and excellent service.', 'images/about-us.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

DROP TABLE IF EXISTS `admins`;
CREATE TABLE IF NOT EXISTS `admins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password_hash`, `created_at`) VALUES
(1, 'GV broilers', '$2y$10$QG44r7rFLs5QlwTxk.l3QemmHp4SikorLZOBMN1YCojc8a1.6PvQa', '2026-10-31 06:24:07');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
CREATE TABLE IF NOT EXISTS `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`) VALUES
(1, 'chicken'),
(2, 'mutton');

-- --------------------------------------------------------

--
-- Table structure for table `contactus`
--

DROP TABLE IF EXISTS `contactus`;
CREATE TABLE IF NOT EXISTS `contactus` (
  `id` varchar(1) NOT NULL,
  `name` text NOT NULL,
  `email` text NOT NULL,
  `phone` varchar(10) NOT NULL,
  `address` text NOT NULL,
  `opening_hours` varchar(11) NOT NULL,
  `map_url` varchar(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `contactus`
--

INSERT INTO `contactus` (`id`, `name`, `email`, `phone`, `address`, `opening_hours`, `map_url`) VALUES
('', 'GV Broilers', 'gvbroilers@gmail.com', '8973144034', 'madurai main road,jakkampatty', '6AM-1PM', '');

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

DROP TABLE IF EXISTS `contact_messages`;
CREATE TABLE IF NOT EXISTS `contact_messages` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `home_detail`
--

DROP TABLE IF EXISTS `home_detail`;
CREATE TABLE IF NOT EXISTS `home_detail` (
  `id` int(11) NOT NULL,
  `heading` varchar(150) NOT NULL,
  `subheading` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `image` varchar(255) NOT NULL,
  `button_text` varchar(50) NOT NULL,
  `button_link` varchar(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `home_detail`
--

INSERT INTO `home_detail` (`id`, `heading`, `subheading`, `description`, `image`, `button_text`, `button_link`) VALUES
(1, 'GV Broilers', 'Good meat,Good health', 'Fresh,quality chicken and mutton for your family.', 'images/home-banner.jpg', 'view products', 'products.php');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
CREATE TABLE IF NOT EXISTS `orders` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `customer_name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `total_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `status` enum('pending','confirmed','preparing','delivered','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `customer_name`, `phone`, `address`, `notes`, `total_amount`, `status`, `created_at`) VALUES
(1, 'jeevitha', '8973144034', 'madurairoad ,aundipatty', '', '810.00', 'delivered', '2026-10-31 06:34:04'),
(2, 'jeevi', '676837877', 'kjnfkmf..,foiihsuyvsnb', '', '9950.00', 'pending', '2026-10-31 07:22:51'),
(3, 'lithu', '6337642576', 'bjdnkjsghvdjmdll,;lj', '', '8190.00', 'pending', '2026-10-31 07:23:32');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

DROP TABLE IF EXISTS `order_items`;
CREATE TABLE IF NOT EXISTS `order_items` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_name` varchar(150) NOT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `line_total` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_order_items_order` (`order_id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `product_name`, `quantity`, `unit_price`, `line_total`, `created_at`) VALUES
(1, 1, 2, 'boneless chicken', '1.00', '250.00', '250.00', '2026-10-31 06:34:04'),
(2, 1, 4, 'chicken breast', '1.00', '260.00', '260.00', '2026-10-31 06:34:04'),
(3, 1, 12, 'liver', '1.00', '300.00', '300.00', '2026-10-31 06:34:04'),
(4, 2, 2, 'boneless chicken', '5.00', '250.00', '1250.00', '2026-10-31 07:22:51'),
(5, 2, 12, 'liver', '5.00', '300.00', '1500.00', '2026-10-31 07:22:51'),
(6, 2, 7, 'mutton curry cut', '9.00', '800.00', '7200.00', '2026-10-31 07:22:51'),
(7, 3, 2, 'boneless chicken', '3.00', '250.00', '750.00', '2026-10-31 07:23:32'),
(8, 3, 5, 'chicken legs', '9.00', '260.00', '2340.00', '2026-10-31 07:23:32'),
(9, 3, 13, 'minced mutton', '6.00', '850.00', '5100.00', '2026-10-31 07:23:32');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
CREATE TABLE IF NOT EXISTS `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `description` text,
  `price` decimal(10,2) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'available',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=14 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `description`, `price`, `image`, `category_id`, `status`) VALUES
(2, 'boneless chicken', 'fresh boneless chicken', '250.00', 'images/products_chicken1.jpg', 1, 'available'),
(3, 'whole chicken', 'fresh whole chicken', '220.00', 'images/products_chicken2.jpg', 1, 'available'),
(4, 'chicken breast', 'Fresh chicken breast pieces', '260.00', 'images/products_chicken3.jpg', 1, 'available'),
(5, 'chicken legs', 'freash chicken leg pieces', '260.00', 'images/products_chicken4.jpg', 1, 'available'),
(7, 'mutton curry cut', 'fresh mutton curry cut pieces', '800.00', 'images/products_mutton1.jpg', 2, 'available'),
(8, 'mutton boneless', 'fresh mutton boneless', '950.00', 'images/products_mutton2.jpg', 2, 'available'),
(12, 'liver', 'fresh mutton liver', '300.00', 'images/liver.jpg', 2, 'available'),
(13, 'minced mutton', 'fresh minced mutton ', '850.00', 'images/products_mutton3.jpg', 2, 'available');

-- --------------------------------------------------------

--
-- Table structure for table `profile1`
--

DROP TABLE IF EXISTS `profile1`;
CREATE TABLE IF NOT EXISTS `profile1` (
  `companyname` varchar(255) DEFAULT NULL,
  `mobile` bigint(11) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `logourl` varchar(255) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `profile1`
--

INSERT INTO `profile1` (`companyname`, `mobile`, `email`, `address`, `logourl`) VALUES
('Gv Broilers', 8973144034, 'gvbroilers@gmail.com', 'Madurai Road, Jakkampatti', 'images/gv-broilers.png');

--
-- Constraints for dumped tables
--

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `fk_order_items_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
