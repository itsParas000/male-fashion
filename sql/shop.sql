-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 20, 2025 at 10:14 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `shop`
--

-- --------------------------------------------------------

--
-- Table structure for table `ai_chat_logs`
--

CREATE TABLE `ai_chat_logs` (
  `id` int(11) NOT NULL,
  `chat_session_id` varchar(200) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `sender_type` enum('user','ai') NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_deleted` tinyint(1) DEFAULT 0,
  `response_time_ms` int(11) DEFAULT NULL COMMENT 'Time taken for AI response in milliseconds'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ai_chat_logs`
--

INSERT INTO `ai_chat_logs` (`id`, `chat_session_id`, `user_id`, `sender_type`, `message`, `created_at`, `is_deleted`, `response_time_ms`) VALUES
(3, 'default_session', NULL, 'user', 'hi', '2025-03-09 13:32:04', 1, NULL),
(4, 'default_session', NULL, 'ai', 'Hi there! How can I help you today?\n', '2025-03-09 13:32:18', 1, NULL),
(5, 'default_session', NULL, 'user', 'new', '2025-03-09 13:36:52', 1, NULL),
(6, 'default_session', NULL, 'ai', '\"New\" is a versatile word!  To help me understand what you mean, could you please provide some context?  For example, are you looking for:\n\n* **Something new to you?**  (e.g., a new movie, a new hobby, a new fact)\n* **Something new in general?** (e.g., new technology, new legislation, new research)\n* **The word \"new\" itself?** (e.g., its etymology, synonyms, antonyms)\n\nPlease tell me more about what you\'re interested in!\n', '2025-03-09 13:36:53', 1, 1526),
(7, 'default_session', NULL, 'user', 'what is meaning of paras', '2025-03-10 07:09:46', 1, NULL),
(8, 'default_session', NULL, 'ai', '\"Paras\" is a shortened form of the word **paragraphs**.\n', '2025-03-10 07:09:47', 1, 1035),
(9, 'default_session', 21, 'user', 'hi', '2025-03-11 09:12:59', 1, NULL),
(10, 'default_session', NULL, 'ai', 'Hi there! How can I help you today?\n', '2025-03-11 09:13:00', 1, 758),
(11, 'default_session', 21, 'user', 'hi', '2025-03-11 09:13:15', 1, NULL),
(12, 'default_session', NULL, 'ai', 'Hi there! How can I help you today?\n', '2025-03-11 09:13:17', 1, 2083),
(13, 'default_session', 21, 'user', 'hi', '2025-04-14 11:48:37', 1, NULL),
(14, 'default_session', NULL, 'ai', 'Hi there! How can I help you today?\n', '2025-04-14 11:48:40', 1, 3097),
(15, 'default_session', 21, 'user', 'hi', '2025-04-14 11:53:29', 1, NULL),
(16, 'default_session', NULL, 'ai', 'Hi there! How can I help you today?\n', '2025-04-14 11:53:30', 1, 1146),
(17, 'default_session', 21, 'user', 'hi', '2025-04-14 12:05:21', 1, NULL),
(18, 'default_session', NULL, 'ai', 'Hi there! How can I help you today?\n', '2025-04-14 12:05:22', 1, 1002),
(19, 'default_session', 21, 'user', 'hi', '2025-04-14 12:13:30', 1, NULL),
(20, 'default_session', NULL, 'ai', 'Hi there! How can I help you today?\n', '2025-04-14 12:13:31', 1, 900),
(21, 'default_session', 21, 'user', 'hi', '2025-04-14 12:13:56', 1, NULL),
(22, 'default_session', NULL, 'ai', 'Hi there! How can I help you today?\n', '2025-04-14 12:13:57', 1, 789),
(23, 'default_session', 21, 'user', 'hiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiii', '2025-04-14 12:14:05', 1, NULL),
(24, 'default_session', NULL, 'ai', 'Hi there!  How can I help you today?\n', '2025-04-14 12:14:08', 1, 2079),
(25, 'default_session', 21, 'user', 'hi', '2025-04-14 12:15:41', 1, NULL),
(26, 'default_session', NULL, 'ai', 'Hi there! How can I help you today?\n', '2025-04-14 12:15:42', 1, 787),
(27, 'default_session', 21, 'user', 'hi', '2025-04-14 12:20:52', 1, NULL),
(28, 'default_session', NULL, 'ai', 'Hi there! How can I help you today?\n', '2025-04-14 12:20:53', 1, 856),
(29, 'default_session', 21, 'user', 'hi', '2025-04-14 12:22:11', 1, NULL),
(30, 'default_session', NULL, 'ai', 'Hi there! How can I help you today?\n', '2025-04-14 12:22:12', 1, 1061),
(31, 'default_session', 21, 'user', 'hi', '2025-04-14 12:25:46', 1, NULL),
(32, 'default_session', NULL, 'ai', 'Hi there! How can I help you today?\n', '2025-04-14 12:25:46', 1, 800),
(33, 'default_session', 21, 'user', 'hi', '2025-04-16 12:52:14', 1, NULL),
(34, 'default_session', NULL, 'ai', 'Hi there! How can I help you today?\n', '2025-04-16 12:52:15', 1, 943),
(35, 'default_session', 21, 'user', 'which shirt is best for brown shirt', '2025-04-20 11:28:44', 1, NULL),
(36, 'default_session', NULL, 'ai', 'There\'s no single \"best\" shirt to wear *with* a brown shirt, as the ideal choice depends heavily on the shade of brown, the style of the brown shirt (e.g., button-down, henley, t-shirt), and the overall look you\'re going for.  However, here are some good options depending on the situation:\n\n**For a casual look:**\n\n* **White T-shirt:**  A classic and versatile choice that works with almost any shade of brown.\n* **Gray T-shirt:**  A slightly more sophisticated alternative to white, especially with darker browns.\n* **Light blue T-shirt:** Offers a refreshing contrast, particularly with medium to dark browns.\n\n**', '2025-04-20 11:28:46', 1, 1918),
(37, 'default_session', 21, 'user', 'hi', '2025-04-20 12:58:07', 1, NULL),
(38, 'default_session', NULL, 'ai', 'Hi there! How can I help you today?\n', '2025-04-20 12:58:08', 1, 1037),
(39, 'default_session', 21, 'user', 'hi', '2025-04-20 12:58:48', 1, NULL),
(40, 'default_session', NULL, 'ai', 'Hi there! How can I help you today?\n', '2025-04-20 12:58:49', 1, 1019);

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('active','ordered') DEFAULT 'active',
  `color` varchar(50) DEFAULT NULL,
  `size` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `created_at`) VALUES
(1, 'Shirt', '2025-03-05 07:21:30'),
(2, 'T-shirt', '2025-03-05 07:21:30'),
(3, 'Cap', '2025-03-05 07:21:30'),
(4, 'Accessories', '2025-03-05 07:21:30');

-- --------------------------------------------------------

--
-- Table structure for table `chat_messages`
--

CREATE TABLE `chat_messages` (
  `id` int(11) NOT NULL,
  `chat_session_id` varchar(200) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `sender_type` enum('user','admin') NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_deleted` tinyint(1) DEFAULT 0,
  `is_deleted_by_user` tinyint(1) DEFAULT 0,
  `is_read` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `chat_messages`
--

INSERT INTO `chat_messages` (`id`, `chat_session_id`, `user_id`, `sender_type`, `message`, `created_at`, `is_deleted`, `is_deleted_by_user`, `is_read`) VALUES
(0, 'me@gmail.com', 1, 'user', 'hi', '2025-03-09 13:30:15', 0, 1, 1),
(0, 'me@gmail.com', 1, 'user', 'hii', '2025-03-09 13:39:28', 0, 1, 1),
(0, 'me@gmail.com', NULL, 'admin', 'taklu', '2025-03-09 13:39:40', 0, 1, 0),
(0, 'me@gmail.com', NULL, 'admin', 'hi', '2025-03-10 09:58:08', 0, 1, 0),
(0, 'me@gmail.com', 21, 'user', 'hello', '2025-03-10 09:58:18', 0, 1, 1),
(0, 'me@gmail.com', 21, 'user', 'hi', '2025-04-16 12:42:52', 0, 1, 1),
(0, 'me@gmail.com', NULL, 'admin', 'hi', '2025-04-16 12:43:07', 0, 1, 0),
(0, 'me@gmail.com', 21, 'user', 'hi', '2025-04-19 05:36:10', 0, 1, 1),
(0, 'me@gmail.com', NULL, 'admin', 'hi', '2025-04-19 05:36:26', 0, 1, 0),
(0, 'me@gmail.com', 21, 'user', 'hi', '2025-04-19 05:36:44', 0, 1, 1),
(0, 'me@gmail.com', 21, 'user', 'hi', '2025-04-19 05:42:50', 0, 1, 1),
(0, 'me@gmail.com', NULL, 'admin', 'hi', '2025-04-19 05:43:05', 0, 1, 0),
(0, 'me@gmail.com', 21, 'user', 'hi', '2025-04-20 09:10:45', 0, 1, 1),
(0, 'me@gmail.com', NULL, 'admin', 'hi', '2025-04-20 09:16:19', 0, 1, 0),
(0, 'me@gmail.com', 21, 'user', 'hi', '2025-04-20 11:19:21', 0, 1, 1),
(0, 'me@gmail.com', 21, 'user', 'hi', '2025-04-20 11:19:34', 0, 1, 1),
(0, 'me@gmail.com', 21, 'user', 'hi', '2025-04-20 11:19:40', 0, 1, 1),
(0, 'me@gmail.com', NULL, 'admin', 'hi', '2025-04-20 11:19:49', 0, 1, 0),
(0, 'me@gmail.com', 21, 'user', 'hi', '2025-04-20 11:24:07', 0, 1, 1),
(0, 'me@gmail.com', 21, 'user', 'hi', '2025-04-20 12:57:35', 0, 1, 1),
(0, 'me@gmail.com', NULL, 'admin', 'hi', '2025-04-20 13:31:36', 0, 1, 0),
(0, 'me@gmail.com', 21, 'user', 'hi', '2025-04-20 13:31:49', 0, 1, 1),
(0, 'me@gmail.com', 21, 'user', 'hello', '2025-04-20 18:41:43', 0, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `coupons`
--

CREATE TABLE `coupons` (
  `id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `discount_type` enum('fixed','percentage','free_shipping') NOT NULL,
  `discount_value` decimal(10,2) DEFAULT 0.00,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NULL DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `used` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `coupons`
--

INSERT INTO `coupons` (`id`, `code`, `discount_type`, `discount_value`, `is_active`, `created_at`, `expires_at`, `user_id`, `used`) VALUES
(9, 'SAVE10', 'fixed', 10.00, 1, '2025-03-01 11:24:27', '2025-03-03 18:30:00', NULL, 0),
(10, 'SAVE20', 'fixed', 20.00, 1, '2025-03-01 11:24:27', NULL, NULL, 0),
(11, 'PERCENT10', 'percentage', 0.10, 1, '2025-03-01 11:24:27', NULL, NULL, 0),
(12, 'FREESHIP', 'free_shipping', 0.00, 1, '2025-03-01 11:24:27', NULL, NULL, 0),
(15, 'USER1-70C623', 'fixed', 0.10, 1, '2025-03-09 12:25:08', '2025-03-30 12:25:00', NULL, 1),
(18, 'USER1-2AEDE3', 'fixed', 10.00, 1, '2025-03-09 13:46:33', '2025-04-08 09:16:33', NULL, 1),
(19, 'USER26-ADB540', 'fixed', 10.00, 1, '2025-03-15 08:18:18', '2025-04-14 03:48:18', 26, 0),
(20, 'USER26-089CD5', 'fixed', 10.00, 1, '2025-03-15 08:36:53', '2025-04-14 04:06:53', 26, 0),
(21, 'USER29-D318D9', 'fixed', 10.00, 1, '2025-03-15 08:42:34', '2025-04-14 04:12:34', NULL, 0),
(22, 'USER29-4B6401', 'fixed', 10.00, 1, '2025-03-15 08:44:58', '2025-04-14 04:14:58', NULL, 0),
(23, 'USER21-BBCCFD', 'fixed', 10.00, 1, '2025-03-15 09:09:51', '2025-04-14 04:39:51', 21, 0),
(24, 'USER21-B9F244', 'fixed', 10.00, 1, '2025-03-15 09:44:12', '2025-04-14 05:14:12', 21, 0),
(25, 'USER21-16A1A2', 'fixed', 10.00, 1, '2025-03-20 19:10:31', '2025-04-19 14:40:31', 21, 0),
(26, 'USER21-E1C5F9', 'fixed', 10.00, 1, '2025-03-20 19:16:21', '2025-04-19 14:46:21', 21, 0),
(27, 'USER21-37B94E', 'fixed', 10.00, 1, '2025-03-20 19:27:06', '2025-04-19 14:57:06', 21, 0),
(28, 'USER21-BFA71C', 'fixed', 10.00, 1, '2025-04-14 10:36:30', '2025-05-14 07:06:30', 21, 0),
(29, 'USER21-43C289', 'fixed', 10.00, 1, '2025-04-16 13:03:30', '2025-05-16 09:33:30', 21, 0),
(30, 'USER21-D5A727', 'fixed', 10.00, 1, '2025-04-19 12:31:50', '2025-05-19 09:01:50', 21, 0),
(31, 'USER21-57956D', 'fixed', 10.00, 1, '2025-04-20 19:06:39', '2025-05-20 15:36:39', 21, 0),
(32, 'USER21-CC4A0C', 'fixed', 10.00, 1, '2025-04-20 19:56:39', '2025-05-20 16:26:39', 21, 0),
(33, 'USER21-04C35C', 'fixed', 10.00, 1, '2025-04-20 20:12:16', '2025-05-20 16:42:16', 21, 0);

-- --------------------------------------------------------

--
-- Table structure for table `delivery_boys`
--

CREATE TABLE `delivery_boys` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `contact` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `order_number` varchar(50) NOT NULL,
  `user_id` int(11) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `virtual_money_used` decimal(10,2) DEFAULT 0.00,
  `coupon_id` int(11) DEFAULT NULL,
  `shipping_rate_id` int(11) DEFAULT NULL,
  `delivery_boy_id` int(11) DEFAULT NULL,
  `payment_method` enum('Credit Card','PayPal','Cash on Delivery','Bank Transfer') NOT NULL,
  `payment_status` enum('Pending','Completed','Failed','Refunded') DEFAULT 'Pending',
  `status` enum('Pending','Processing','Shipped','Delivered','Cancelled','Refunded','Replacement Requested','Return Requested','Accepted') DEFAULT 'Pending',
  `delivery_status` enum('pending','accepted') DEFAULT 'pending',
  `transaction_id` varchar(255) DEFAULT NULL,
  `shipping_address` text NOT NULL,
  `billing_address` text NOT NULL,
  `tracking_number` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `order_number`, `user_id`, `total_amount`, `virtual_money_used`, `coupon_id`, `shipping_rate_id`, `delivery_boy_id`, `payment_method`, `payment_status`, `status`, `delivery_status`, `transaction_id`, `shipping_address`, `billing_address`, `tracking_number`, `created_at`, `updated_at`) VALUES
(31, 'ORD001', 21, 87.00, 0.00, NULL, NULL, NULL, 'Credit Card', 'Pending', 'Cancelled', 'pending', NULL, '123 Test St', '123 Test St', '', '2025-03-01 04:30:00', '2025-03-15 07:05:20'),
(33, 'ORD-20250315-0026-2350', 26, 70.00, 0.00, NULL, 15, NULL, 'Bank Transfer', 'Completed', 'Delivered', 'pending', 'DUMMY-910388', 'fbnfb fgbnfgbnfg , fgnfgnfg, fgbfgbdf, gbdfgbdfgb , Email: fghsrgthar@gmail.com, Phone: 1234567891', 'fbnfb fgbnfgbnfg , fgnfgnfg, fgbfgbdf, gbdfgbdfgb , Email: fghsrgthar@gmail.com, Phone: 1234567891', 'TRK-20250315-3396', '2025-03-15 06:51:13', '2025-03-15 06:52:16'),
(36, 'ORD-20250315-0026-3525', 26, 105.00, 10.00, NULL, 13, 28, 'Cash on Delivery', 'Completed', 'Delivered', 'pending', 'DUMMY-382576', 'dgdfg dfsvgdf, dfvgdfv, nerul, maharashtra 400706, Email: fgvfg@gmail.com, Phone: 1234567891', 'dgdfg dfsvgdf, dfvgdfv, nerul, maharashtra 400706, Email: fgvfg@gmail.com, Phone: 1234567891', 'TRK-20250315-3849', '2025-03-15 08:18:18', '2025-03-15 08:30:16'),
(41, 'ORD-20250315-0021-6924', 21, 73.00, 2.00, NULL, 11, 30, 'Bank Transfer', 'Completed', 'Replacement Requested', 'pending', 'DUMMY-621282', 'vdfg gege, gerger, vashi, dfbdfg 123679, Email: drivebdfgzb@gmail.com, Phone: 1234567890', 'vdfg gege, gerger, vashi, dfbdfg 123679, Email: drivebdfgzb@gmail.com, Phone: 1234567890', 'TRK-20250315-8250', '2025-03-15 09:09:51', '2025-03-20 14:59:46'),
(42, 'ORD-20250315-0021-5150', 21, 105.00, 0.00, NULL, 10, 31, 'Credit Card', 'Completed', 'Processing', 'pending', 'DUMMY-966431', 'feawfwe fwefwe, wfwefwefwe, panvel, fwerfgwrf 135794, Email: jyjarhart@gmail.com, Phone: 1234567891', 'feawfwe fwefwe, wfwefwefwe, panvel, fwerfgwrf 135794, Email: jyjarhart@gmail.com, Phone: 1234567891', 'TRK-20250315-6228', '2025-03-15 09:44:12', '2025-03-15 10:06:20'),
(43, 'ORD-20250320-0021-6686', 21, 385.00, 0.00, NULL, 13, NULL, 'Bank Transfer', 'Completed', 'Delivered', 'pending', 'DUMMY-436203', 'john doe, 123,hvvahvhxb,bagau,400706, city, maharashtra 400706, Email: me@gmail.com, Phone: 0123456789', 'john doe, 123,hvvahvhxb,bagau,400706, city, maharashtra 400706, Email: me@gmail.com, Phone: 0123456789', 'TRK-20250320-4704', '2025-03-20 18:56:22', '2025-03-20 19:48:29'),
(56, 'ORD-20250420-0021-7419', 21, 3025.00, 0.00, NULL, 13, 28, 'Bank Transfer', 'Completed', 'Delivered', 'accepted', 'DUMMY-251247', 'john doe, 123,hvvahvhxb,bagau,400706, nerul, Maharashtra 400706, Email: new@gmail.com, Phone: 0123456789', 'john doe, 123,hvvahvhxb,bagau,400706, nerul, Maharashtra 400706, Email: new@gmail.com, Phone: 0123456789', 'TRK-20250420-6438', '2025-04-20 19:00:45', '2025-04-20 19:05:07'),
(57, 'ORD-20250420-0021-2932', 21, 1525.00, 0.00, NULL, 13, 28, 'PayPal', 'Completed', 'Delivered', 'pending', 'DUMMY-433947', 'john doe, 123,hvvahvhxb,bagau,400706, nerul, Maharashtra 400706, Email: new@gmail.com, Phone: 0123456789', 'john doe, 123,hvvahvhxb,bagau,400706, nerul, Maharashtra 400706, Email: new@gmail.com, Phone: 0123456789', 'TRK-20250420-9620', '2025-04-20 19:06:39', '2025-04-20 19:55:44'),
(58, 'ORD-20250420-0021-5373', 21, 525.00, 0.00, NULL, 13, 28, 'PayPal', 'Completed', 'Delivered', 'pending', 'DUMMY-308065', 'john doe, 123,hvvahvhxb,bagau,400706, nerul, Maharashtra 400706, Email: new@gmail.com, Phone: 0123456789', 'john doe, 123,hvvahvhxb,bagau,400706, nerul, Maharashtra 400706, Email: new@gmail.com, Phone: 0123456789', 'TRK-20250420-7602', '2025-04-20 19:56:39', '2025-04-20 19:57:26'),
(59, 'ORD-20250420-0021-3060', 21, 1225.00, 0.00, NULL, 13, 28, 'Bank Transfer', 'Completed', 'Delivered', 'pending', 'DUMMY-824892', 'john doe, 123,hvvahvhxb,bagau,400706, nerul, Maharashtra 400706, Email: new@gmail.com, Phone: 0123456789', 'john doe, 123,hvvahvhxb,bagau,400706, nerul, Maharashtra 400706, Email: new@gmail.com, Phone: 0123456789', 'TRK-20250420-5372', '2025-04-20 20:12:16', '2025-04-20 20:13:40');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) GENERATED ALWAYS AS (`quantity` * `price`) STORED,
  `color` varchar(50) DEFAULT NULL,
  `size` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price`, `color`, `size`) VALUES
(35, 33, 10, 1, 45.00, 'Not specified', 'Not specified'),
(38, 36, 10, 2, 45.00, 'Not specified', 'Not specified'),
(43, 41, 10, 1, 45.00, 'Not specified', 'Not specified'),
(44, 42, 10, 1, 45.00, 'Not specified', 'Not specified'),
(45, 43, 10, 8, 45.00, 'Not specified', 'Not specified'),
(60, 56, 6, 1, 3000.00, 'White', '10'),
(61, 57, 8, 1, 1500.00, 'Black', 'One Size'),
(62, 58, 7, 1, 500.00, 'Black', 'M'),
(63, 59, 9, 1, 1200.00, 'Red', 'One Size');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `description` text DEFAULT NULL,
  `score_and_care_tips` text DEFAULT NULL,
  `related_products` varchar(255) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `old_price` decimal(10,2) DEFAULT NULL,
  `stock_quantity` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `category_name` varchar(100) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `size` varchar(255) DEFAULT NULL COMMENT 'Comma-separated sizes (e.g., S,M,L,XL)',
  `color` varchar(255) DEFAULT NULL COMMENT 'Comma-separated colors (e.g., Black,White,Red)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `price`, `description`, `score_and_care_tips`, `related_products`, `image`, `created_at`, `old_price`, `stock_quantity`, `is_active`, `category_name`, `category_id`, `size`, `color`) VALUES
(1, 'white cotton shirt', 899.00, 'White cotton shirt for formal and casual wear.', 'Care: ✅ Read the Label – Always check washing instructions specific to your shirt fabric.\r\n✅ Wash in Cold Water – Prevents shrinking and preserves color vibrancy.. Score: 4.5/5', '1,2,3', '67dd0f545dc23-s1.png', '2025-01-20 06:24:47', 999.00, 15, 1, 'Shirt', NULL, 'S,M,L,XL', 'White,Blue'),
(2, 'Luxury watch', 2000.00, 'A stylish and Luxury Watch.', '✅ Keep It Dry – Avoid exposure to water unless it\'s water-resistant. Wipe off moisture immediately.\r\n✅ Clean Regularly – Use a soft, lint-free cloth to remove dust and fingerprints.\r\n✅ Avoid Harsh Chemicals – Perfumes, detergents, and alcohol can damage the strap and case.', '1,2,3', '67dd0fa675f6a-w5.png', '2025-01-20 06:24:47', 2500.00, 10, 1, 'watch', NULL, 'default', 'Black,Silver'),
(3, 'watch', 2500.00, 'Elegant watch.', '✅ Keep It Dry – Avoid exposure to water unless it\'s water-resistant. Wipe off moisture immediately.\r\n✅ Clean Regularly – Use a soft, lint-free cloth to remove dust and fingerprints.\r\n✅ Avoid Harsh Chemicals – Perfumes, detergents, and alcohol can damage the strap and case.', '1,2,3', '67dd0fdb29edb-w4.png', '2025-01-20 06:24:47', 2700.00, 12, 1, 'watch', NULL, 'default', 'Gold,Black'),
(4, 'black shirt', 999.00, 'black looking cool shirt.', 'Care: ✅ Read the Label – Always check washing instructions specific to your shirt fabric.\r\n✅ Wash in Cold Water – Prevents shrinking and preserves color vibrancy.. Score: 4/5', '1,2,3', '67dd0ef635876-s2.png', '2025-02-24 08:44:30', 1100.00, 10, 1, 'shirt', NULL, 'M,L,XL', 'Black,Red'),
(5, 'formal shoes', 800.00, 'formal shoes for formal attire', '✅ Clean Regularly – Wipe off dirt and dust with a soft cloth or brush.\r\n✅ Use Mild Soap – For deeper cleaning, use mild soap and water instead of harsh chemicals.\r\n\r\n✅ Avoid Machine Washing – Hand washing is best to maintain shape and quality.\r\n✅ Use Shoe Trees – Helps retain the shape and prevent creases.', '7,8', '67dd0e7cd967e-a0.png', '2025-02-26 10:41:41', 900.00, 10, 1, 'shoes', NULL, '8,9,10', 'Black,Brown'),
(6, 'nike white Af1 shoes ', 3000.00, 'Nike white casual shoe.', '✅ Clean Regularly – Wipe off dirt and dust with a soft cloth or brush.\r\n✅ Use Mild Soap – For deeper cleaning, use mild soap and water instead of harsh chemicals.\r\n\r\n✅ Avoid Machine Washing – Hand washing is best to maintain shape and quality.\r\n✅ Use Shoe Trees – Helps retain the shape and prevent creases.', '7,8', '67dd0e093b67e-a5.png', '2025-02-28 10:53:38', 3500.00, 10, 1, 'shoes', NULL, '9,10,11', 'White,Black'),
(7, 'black T-shirt', 500.00, 'blacked good quality t-shirt oversized', '✅ Hand Wash Only – Use mild soap and lukewarm water. Avoid harsh detergents.\r\n✅ Spot Clean When Needed – Use a soft brush or cloth to remove dirt and stains.\r\n✅ Air Dry Properly – Reshape the cap and let it air dry naturally. Avoid direct sunlight.\r\n✅ Avoid Machine Washing – Washing machines can damage the shape and fabric.\r\n✅ Store Properly – Keep your cap in a cool, dry place. Avoid crushing it under heavy objects.', '5,4', '67dd0d72413c8-R.png', '2025-02-28 10:54:43', 600.00, 10, 1, 'T-shirt', NULL, 'S,M,L,XL,XXL', 'Black,White'),
(8, 'balck n/y og cap', 1500.00, 'premium og blacked color cap.', '✅ Hand Wash Only – Use mild soap and lukewarm water. Avoid harsh detergents.\r\n✅ Spot Clean When Needed – Use a soft brush or cloth to remove dirt and stains.\r\n✅ Air Dry Properly – Reshape the cap and let it air dry naturally. Avoid direct sunlight.\r\n✅ Avoid Machine Washing – Washing machines can damage the shape and fabric.\r\n✅ Store Properly – Keep your cap in a cool, dry place. Avoid crushing it under heavy objects.', '8,9', '67dd0d2a8d19e-c3.png', '2025-02-28 10:55:10', 1700.00, 12, 1, 'cap', NULL, 'One Size', 'Black'),
(9, 'red n/y cap original', 1200.00, 'n/y original premium cap.', '✅ Hand Wash Only – Use mild soap and lukewarm water. Avoid harsh detergents.\r\n✅ Spot Clean When Needed – Use a soft brush or cloth to remove dirt and stains.\r\n✅ Avoid Machine Washing – Washing machines can damage the shape and fabric.\r\n', '8,9', '67dd0cf12e45b-c4.png', '2025-02-28 10:55:22', 1500.00, 10, 1, 'cap', NULL, 'One Size', 'Red'),
(10, 'red cap', 500.00, 'cotton cap', '✅ Hand Wash Only – Use mild soap and lukewarm water. Avoid harsh detergents.\r\n✅ Spot Clean When Needed – Use a soft brush or cloth to remove dirt and stains.\r\n✅ Air Dry Properly – Reshape the cap and let it air dry naturally. Avoid direct sunlight.\r\n✅ Avoid Machine Washing – Washing machines can damage the shape and fabric.\r\n✅ Store Properly – Keep your cap in a cool, dry place. Avoid crushing it under heavy objects.', '8,6', '67dd0ca00969e-c1.png', '2025-02-28 10:55:46', 600.00, 8, 1, 'cap', NULL, 'One Size', 'Red,Blue'),
(12, 'belt', 499.00, 'leather belt freesize.', '✅ Clean Regularly – Wipe with a soft cloth to remove dust and dirt.\r\n✅ Use Leather Conditioner – For leather belts, apply conditioner to prevent cracks and dryness.\r\n✅ Avoid Water Exposure – Keep away from moisture to prevent damage, especially for leather.', '1,2,3', '67dd1042b7111-product15.jpg', '2025-03-21 07:07:46', 599.00, 15, 1, 'belt', NULL, 'Free Size', 'Black,Brown');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `user_name` varchar(255) DEFAULT NULL,
  `email` varchar(200) DEFAULT NULL,
  `rating` int(11) NOT NULL CHECK (`rating` between 1 and 5),
  `comment` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `user_id` int(11) DEFAULT NULL,
  `verified_purchase` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`id`, `product_id`, `user_name`, `email`, `rating`, `comment`, `created_at`, `user_id`, `verified_purchase`, `is_active`) VALUES
(1, 3, 'us', 'us@gmail.com', 5, 'good', '2025-04-20 11:39:09', 22, 1, 1),
(11, 2, 'me', 'me@gmail.com', 5, 'hi', '2025-04-15 12:39:29', 21, 0, 1),
(12, 2, 'me', 'me@gmail.com', 3, 'nice product', '2025-04-15 12:39:45', 21, 0, 1),
(13, 8, 'me', 'me@gmail.com', 5, 'nice', '2025-04-20 11:16:36', 21, 0, 1),
(14, 3, 'me', 'me@gmail.com', 5, 'nice', '2025-04-20 11:40:06', 21, 0, 1);

-- --------------------------------------------------------

--
-- Table structure for table `shipping_rates`
--

CREATE TABLE `shipping_rates` (
  `id` int(11) NOT NULL,
  `city` varchar(100) NOT NULL,
  `rate` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `shipping_rates`
--

INSERT INTO `shipping_rates` (`id`, `city`, `rate`, `created_at`) VALUES
(10, 'panvel', 60.00, '2025-03-01 11:24:27'),
(11, 'Vashi', 30.00, '2025-03-01 11:24:27'),
(12, 'Belapur', 49.00, '2025-03-01 11:24:27'),
(13, 'Nerul', 25.00, '2025-03-01 11:24:27'),
(14, 'Airoli', 35.00, '2025-03-01 11:24:27'),
(15, 'Kharghar', 25.00, '2025-03-01 11:24:27'),
(16, 'Kopar Khairane', 35.00, '2025-03-01 11:24:27'),
(17, 'Ghansoli', 49.00, '2025-03-01 11:24:27');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `Id` int(11) NOT NULL,
  `Username` varchar(200) DEFAULT NULL,
  `Email` varchar(200) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `Password` varchar(200) DEFAULT NULL,
  `SecurityQuestionEncrypted` varchar(255) DEFAULT NULL,
  `SecurityAnswerEncrypted` varchar(255) DEFAULT NULL,
  `Role` enum('user','admin','delivery') DEFAULT 'user',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `virtual_money` decimal(10,2) DEFAULT 0.00,
  `profile_picture` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`Id`, `Username`, `Email`, `address`, `city`, `Password`, `SecurityQuestionEncrypted`, `SecurityAnswerEncrypted`, `Role`, `created_at`, `virtual_money`, `profile_picture`, `is_active`) VALUES
(21, 'me', 'me@gmail.com', '', NULL, '$2y$10$7t3n1Pmxx08HD1tR./nT3uL4htx18hyWc.gLukCV/JNmWjoEM7Kwm', 'nidU4FfD1II2Aayr/DCTSmVxM2pod2JMRVhTeHlsV3Y1cWF1dzlhRWxQOEgrSHZXMVNNRW9BPT0=', 'oV92eJCTPY7SL6QQCDpp/mdEZkY=', 'user', '2025-03-10 14:54:57', 20.56, 'profile_21_1745176743.jpg', 1),
(22, 'us', 'us@gmail.com', NULL, NULL, '$2y$10$Olb8BoWyRmKWDKZQqAHc4Oq8iuE3raXkvqYObgkGj95dZoKJllr8e', '07NpkCpbIftUheoOoLphCFlicFBpVnExWHEyRWdYRUJBSlV3NERUOXRwdXExODdEVTNUaXpBPT0=', 'vUAmoMdny1rbFiKeitma4E1ZVWw=', 'user', '2025-03-10 15:02:15', 0.00, NULL, 1),
(23, 'shreyash', 'shreyash@gmail.com', NULL, NULL, '$2y$10$VF4NFqA8usW2l.CmJRzpIOeuxH/X2vMy5hJMChaxLwN.E7M0gyopC', 'lG6WB/6WHqgNXvCms//d0k91bElUaExaa1JISDJyeEMwRU1jaEtSWTlOVEQyQXhCUy9vdzlBPT0=', 'xKQoaVar5BwpizowGNiXp0taV1NxaHc9', 'admin', '2025-03-10 15:26:46', 0.00, NULL, 1),
(24, 'paras', 'paras@gmail.com', NULL, NULL, '$2y$10$KA3F.ex2IPN6LuTETOODwu.Z.pWP.P7qzfcKoZN.na2dyomasoIfW', '4ABxiQekhB2nTVCMMkaM2VBaV2VpSGV6QzlYVVNHeWpJSUNadXVBUkZiNmV1cGh0dVBCOUV3PT0=', 'D6Q2Huf65UcPEJs5nc0rvjhkZnVxclE9', 'admin', '2025-03-11 15:15:12', 0.00, NULL, 1),
(25, 'new', 'new@gmail.com', NULL, NULL, '$2y$10$9CubdDPRA3CyNJR6uNSVeeHYKtl0IO/sMMsIbPNDFZSJ62naJUveG', '+zHnLvNHXT6ls4XnOZRXvmFjbnpjUTBCNmlrNHA3Z29yd3kwQlVjV01FRWw4UmVlazlQL2lnPT0=', 'urWqNWVaEqQyQc8C1c1om0FDT2Q=', 'user', '2025-03-11 15:24:00', 0.00, NULL, 1),
(26, 'be', 'be@gmail.com', NULL, NULL, '$2y$10$1/pCW0mqnM.oFLneHKKLLuy2UCuFqse/HZXoZd8kKiUqUVRTseClS', 'ox9DdGtXxu8q9INbTmUXC201Vmxia0xTMXVyN1VzZmdaVklKUmNEdUIwVGd6Z2NFOUdyQ2dRPT0=', 'xhXX5B0u8XRAoEWYJlVXlXlrWWhDalE9', 'user', '2025-03-15 11:00:16', 50.00, NULL, 1),
(28, 'del', 'del@gmail.com', NULL, 'nerul', '$2y$10$wVdHn.zacA6fx0eM6jtoUOVQqT4.vNhTjU4JvuNDr.64IB8tsbUgq', 'MFm6+TkbDEas59ZNzLjtVXppSmZwOERoRHlWdmdSajR0eE5PaGxVa1F5MFFkMEozK2IwMWpRPT0=', '5MZM77jKPg76e6lYqnK4KHBjTFFnQTQ9', 'delivery', '2025-03-15 12:55:13', 0.00, NULL, 1),
(30, 'del2', 'del2@gmail.com', NULL, 'vashi', '$2y$10$/V40nTFB3ZXhBGD2djd8x.YPZNOjH01GOypzchMV3EtXFNjs3jM22', 'zWZNLvq86d/hbwqTSXkrb3RjR3VoT21lNUJxa2kwb3BTZ2FMd25Zb0RXS0FSQWVrUUlEUXpBPT0=', 'dHJ+c3TFZUrPyBJKIIc7jHNJSkRYMjA9', 'delivery', '2025-03-15 14:37:26', 0.00, NULL, 1),
(31, 'del1', 'del1@gmail.com', NULL, 'panvel', '$2y$10$uhN.gfAfX6.G2Nmato5vNOjJEsXLXDIjp7tPlD/L7ktVmCcXF6LCG', 'qArrLEiytIOKRotgveqBvFNYbEMwL2Jsb3R4Y2ZCeTBiTzBjZnlMUWE0Q0wwR0VwcEtaYXhRPT0=', '8LCL05Q7IGDnx7q/eTBrizFuTUhYSTA9', 'delivery', '2025-03-15 15:11:30', 0.00, NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `wishlist`
--

CREATE TABLE `wishlist` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `ai_chat_logs`
--
ALTER TABLE `ai_chat_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `coupons`
--
ALTER TABLE `coupons`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `delivery_boys`
--
ALTER TABLE `delivery_boys`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_number` (`order_number`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `coupon_id` (`coupon_id`),
  ADD KEY `shipping_rate_id` (`shipping_rate_id`),
  ADD KEY `delivery_boy_id` (`delivery_boy_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `shipping_rates`
--
ALTER TABLE `shipping_rates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `city` (`city`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`Id`);

--
-- Indexes for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `ai_chat_logs`
--
ALTER TABLE `ai_chat_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=121;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `coupons`
--
ALTER TABLE `coupons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `delivery_boys`
--
ALTER TABLE `delivery_boys`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=64;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `shipping_rates`
--
ALTER TABLE `shipping_rates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `wishlist`
--
ALTER TABLE `wishlist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=108;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `ai_chat_logs`
--
ALTER TABLE `ai_chat_logs`
  ADD CONSTRAINT `ai_chat_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`Id`) ON DELETE SET NULL;

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`Id`),
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `coupons`
--
ALTER TABLE `coupons`
  ADD CONSTRAINT `coupons_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`Id`) ON DELETE SET NULL;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`Id`) ON DELETE CASCADE,
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`coupon_id`) REFERENCES `coupons` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `orders_ibfk_3` FOREIGN KEY (`shipping_rate_id`) REFERENCES `shipping_rates` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `orders_ibfk_4` FOREIGN KEY (`delivery_boy_id`) REFERENCES `users` (`Id`) ON DELETE SET NULL;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`);

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`Id`) ON DELETE CASCADE;

--
-- Constraints for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD CONSTRAINT `wishlist_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`Id`),
  ADD CONSTRAINT `wishlist_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
