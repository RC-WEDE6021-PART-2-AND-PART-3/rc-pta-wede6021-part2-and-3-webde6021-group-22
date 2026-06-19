-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 19, 2026 at 11:50 AM
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
-- Database: `clothingstore`
--

-- --------------------------------------------------------

--
-- Table structure for table `tbladmin`
--

CREATE TABLE `tbladmin` (
  `admin_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `access_level` enum('super','standard') DEFAULT 'standard',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbladmin`
--

INSERT INTO `tbladmin` (`admin_id`, `user_id`, `access_level`, `created_at`) VALUES
(1, 7, 'super', '2026-06-18 23:57:44');

-- --------------------------------------------------------

--
-- Table structure for table `tblaorder`
--

CREATE TABLE `tblaorder` (
  `order_id` int(11) NOT NULL,
  `buyer_id` int(11) NOT NULL,
  `seller_id` int(11) NOT NULL,
  `listing_id` int(11) NOT NULL,
  `price_paid` decimal(10,2) NOT NULL,
  `delivery_method` varchar(100) DEFAULT NULL,
  `delivery_fee` decimal(10,2) DEFAULT 0.00,
  `delivery_address` text DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `payment_status` enum('pending','paid','failed','refunded') DEFAULT 'pending',
  `order_status` enum('placed','confirmed','shipped','delivered','cancelled','disputed') DEFAULT 'placed',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tblaorder`
--

INSERT INTO `tblaorder` (`order_id`, `buyer_id`, `seller_id`, `listing_id`, `price_paid`, `delivery_method`, `delivery_fee`, `delivery_address`, `payment_method`, `payment_status`, `order_status`, `created_at`) VALUES
(1, 8, 2, 1, 450.00, '0', 65.00, '123 lion street, yeoville, Pretoria, 012', NULL, 'paid', 'shipped', '2026-06-18 21:53:29'),
(2, 8, 2, 2, 280.00, '0', 65.00, '123 lion street, yeoville, Pretoria, 012', NULL, 'paid', 'delivered', '2026-06-18 21:53:29'),
(3, 8, 6, 4, 18500.00, '0', 65.00, '123 lion street, yeoville, Pretoria, 012', NULL, 'paid', 'delivered', '2026-06-18 21:53:29'),
(4, 8, 6, 4, 18500.00, '0', 65.00, '123 lion street, yeoville, Pretoria, 012', NULL, 'paid', 'placed', '2026-06-19 00:03:51'),
(5, 8, 2, 5, 150.00, '0', 65.00, '123 lion street, yeoville, Pretoria, 012', NULL, 'paid', 'shipped', '2026-06-19 00:03:51'),
(6, 11, 8, 6, 450.00, '0', 65.00, '123 lion street, yeoville, Pretoria, 012', NULL, 'paid', 'confirmed', '2026-06-19 10:54:23'),
(7, 11, 8, 8, 1500.00, '0', 65.00, '123 lion street, yeoville, Pretoria, 012', NULL, 'paid', 'confirmed', '2026-06-19 10:54:23');

-- --------------------------------------------------------

--
-- Table structure for table `tbllisting`
--

CREATE TABLE `tbllisting` (
  `listing_id` int(11) NOT NULL,
  `seller_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `sub_category` varchar(100) DEFAULT NULL,
  `brand` varchar(100) DEFAULT NULL,
  `condition_grade` enum('new','like_new','good','fair','poor') DEFAULT 'good',
  `size` varchar(20) DEFAULT NULL,
  `colour` varchar(50) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `quantity` int(11) DEFAULT 1,
  `listing_type` enum('p2p','curated') DEFAULT 'p2p',
  `listing_status` enum('active','sold','draft','removed') DEFAULT 'active',
  `is_verified` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `image_url` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbllisting`
--

INSERT INTO `tbllisting` (`listing_id`, `seller_id`, `title`, `description`, `category`, `sub_category`, `brand`, `condition_grade`, `size`, `colour`, `price`, `quantity`, `listing_type`, `listing_status`, `is_verified`, `created_at`, `image_url`) VALUES
(1, 2, 'Vintage Levi 501 Jeans', 'Classic 90s Levi 501 straight-leg jeans in dark wash.', 'Women', 'Bottoms', 'Levi\'s', 'like_new', '32', 'Indigo', 450.00, 1, 'p2p', 'active', 0, '2026-06-18 23:57:44', NULL),
(2, 2, 'Zara Floral Midi Dress', 'Beautiful floral print midi dress, worn twice.', 'Women', 'Dresses', 'Zara', 'good', 'S', 'Multicolour', 280.00, 1, 'p2p', 'active', 0, '2026-06-18 23:57:44', NULL),
(4, 6, 'Louis Vuitton Neverfull Tote', 'Authenticated LV Neverfull MM in Damier Ebene.', 'Accessories', 'Bags', 'Louis Vuitton', 'good', 'OS', 'Brown', 18500.00, 1, 'curated', 'active', 1, '2026-06-18 23:57:44', NULL),
(5, 2, 'H&M Oversized Blazer', 'Camel oversized blazer, perfect for layering.', 'Women', 'Outerwear', 'H&M', 'new', 'M', 'Camel', 150.00, 2, 'p2p', 'active', 0, '2026-06-18 23:57:44', NULL),
(6, 8, 'vintage levi jean', 'blue thick denim', 'Men', NULL, 'levis', 'new', 'XS,M,L', 'blue', 450.00, 1, 'p2p', 'active', 1, '2026-06-19 09:00:32', NULL),
(7, 8, 'vintage jean', 'blue tick jean', 'Men', NULL, 'levis', 'like_new', 'XS,M,L', 'blue', 450.00, 1, 'p2p', 'active', 1, '2026-06-19 09:01:52', NULL),
(8, 8, 'New Nalance 550', 'still new worn 2 times', 'Shoes', 'Sneakers', 'New Balance', 'good', '6', '0', 1000.00, 1, 'p2p', 'active', 0, '2026-06-19 09:17:17', '1781853437_Nb.png'),
(9, 11, 'light pink top', 'female top', 'Women', NULL, 'zara', 'good', 'XS,M,L', 'pink and white', 250.00, 1, 'p2p', 'active', 0, '2026-06-19 10:59:45', NULL),
(10, 2, 'boogie top', 'slim fit top', 'Women', 'tops', 'zara', 'good', 'XS,M,L', '0', 150.00, 1, 'p2p', 'active', 0, '2026-06-19 11:03:41', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tbllistingphoto`
--

CREATE TABLE `tbllistingphoto` (
  `photo_id` int(11) NOT NULL,
  `listing_id` int(11) NOT NULL,
  `photo_url` varchar(255) NOT NULL,
  `is_cover` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tblmessage`
--

CREATE TABLE `tblmessage` (
  `message_id` int(11) NOT NULL,
  `sender_id` int(11) DEFAULT NULL,
  `receiver_id` int(11) DEFAULT NULL,
  `listing_id` int(11) DEFAULT NULL,
  `message_text` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `sent_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tblmessage`
--

INSERT INTO `tblmessage` (`message_id`, `sender_id`, `receiver_id`, `listing_id`, `message_text`, `is_read`, `sent_at`) VALUES
(6, NULL, 7, NULL, 'From: rosy (rosym@gmail.com)\nSubject: Other\n\nwaiting veryfication', 0, '2026-06-19 10:42:36'),
(7, NULL, 7, NULL, 'From: Junior (juniorm@gmail.com)\nSubject: Problem with an order\n\nhavent recived my order', 0, '2026-06-19 10:56:13');

-- --------------------------------------------------------

--
-- Table structure for table `tblreview`
--

CREATE TABLE `tblreview` (
  `review_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `reviewer_id` int(11) NOT NULL,
  `reviewed_user_id` int(11) NOT NULL,
  `rating` int(11) DEFAULT NULL CHECK (`rating` between 1 and 5),
  `review_text` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tblsellerrequest`
--

CREATE TABLE `tblsellerrequest` (
  `request_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `shop_name` varchar(100) NOT NULL,
  `shop_description` text DEFAULT NULL,
  `business_registration` varchar(50) DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `requested_at` datetime DEFAULT current_timestamp(),
  `reviewed_at` datetime DEFAULT NULL,
  `admin_notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tblsellerrequest`
--

INSERT INTO `tblsellerrequest` (`request_id`, `user_id`, `shop_name`, `shop_description`, `business_registration`, `status`, `requested_at`, `reviewed_at`, `admin_notes`) VALUES
(1, 8, 'lapa', 'sells vintage cloths', '00123', 'approved', '2026-06-19 00:05:48', '2026-06-19 08:31:04', ''),
(2, 9, 'rosie', 'sells female products', '00123', 'approved', '2026-06-19 09:53:27', '2026-06-19 09:56:32', ''),
(3, 11, 'papta', 'fancy hoodies', '00123', 'approved', '2026-06-19 10:55:00', '2026-06-19 10:57:13', '');

-- --------------------------------------------------------

--
-- Table structure for table `tbluser`
--

CREATE TABLE `tbluser` (
  `user_id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `shop_name` varchar(100) DEFAULT NULL,
  `shop_description` text DEFAULT NULL,
  `province` varchar(50) DEFAULT NULL,
  `city` varchar(50) DEFAULT NULL,
  `reputation_score` decimal(3,2) DEFAULT 0.00,
  `total_sales` int(11) DEFAULT 0,
  `is_top_seller` tinyint(1) DEFAULT 0,
  `holiday_mode` tinyint(1) DEFAULT 0,
  `role` enum('buyer','seller','admin') DEFAULT 'buyer',
  `account_status` enum('active','pending','suspended','deleted') DEFAULT 'pending',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbluser`
--

INSERT INTO `tbluser` (`user_id`, `first_name`, `last_name`, `email`, `phone_number`, `password_hash`, `profile_picture`, `shop_name`, `shop_description`, `province`, `city`, `reputation_score`, `total_sales`, `is_top_seller`, `holiday_mode`, `role`, `account_status`, `created_at`, `updated_at`) VALUES
(1, 'John', 'Doe', 'j.doe@abc.co.za', '27831234567', '482c811da5d5b4bc6d497ffa98491e38', NULL, NULL, NULL, 'Gauteng', 'Johannesburg', 0.00, 0, 0, 0, 'buyer', 'suspended', '2026-06-18 23:57:44', '2026-06-19 10:58:25'),
(2, 'Sarah', 'Nkosi', 's.nkosi@gmail.com', '27729876543', '7ba4b083c33d4ab6025e3249af3cd28e', NULL, NULL, NULL, 'Western Cape', 'Cape Town', 0.00, 0, 0, 0, 'seller', 'active', '2026-06-18 23:57:44', '2026-06-19 00:01:02'),
(3, 'Lebo', 'Mokoena', 'l.mokoena@outlook.com', '27641112233', 'a4f07118a548b4c7311d51558833c87a', NULL, NULL, NULL, 'Gauteng', 'Pretoria', 0.00, 0, 0, 0, 'buyer', 'active', '2026-06-18 23:57:44', '2026-06-18 23:57:44'),
(4, 'Thabo', 'Dlamini', 't.dlamini@yahoo.com', '27823334455', '588c6cd155045abf76071d198bbeb176', NULL, NULL, NULL, 'KwaZulu-Natal', 'Durban', 0.00, 0, 0, 0, 'seller', 'active', '2026-06-18 23:57:44', '2026-06-19 00:00:28'),
(5, 'Aisha', 'Patel', 'a.patel@hotmail.com', '27714445566', '626ad5818da3026da2e5cd44863145d5', NULL, NULL, NULL, 'Gauteng', 'Sandton', 0.00, 0, 0, 0, 'buyer', 'active', '2026-06-18 23:57:44', '2026-06-18 23:57:44'),
(6, 'Mpho', 'Sithole', 'm.sithole@gmail.com', '27835556677', 'd8e4d48af58947a154ddc84702e3eb27', NULL, NULL, NULL, 'Gauteng', 'Soweto', 0.00, 0, 0, 0, 'seller', 'deleted', '2026-06-18 23:57:44', '2026-06-19 00:00:45'),
(7, 'Admin', 'User', 'admin@pastimes.co.za', '27700000001', '25e4ee4e9229397b6b17776bfceaf8e7', NULL, NULL, NULL, 'Gauteng', 'Johannesburg', 0.00, 0, 0, 0, 'admin', 'active', '2026-06-18 23:57:44', '2026-06-18 23:57:44'),
(8, 'maselelo', 'rapholo', 'maselelor@gmail.com', '0636761590', '7abde28165b9a1b3490e752d4c636389', NULL, NULL, NULL, 'Gauteng', 'Pretoria', 0.00, 0, 0, 0, 'seller', 'active', '2026-06-18 23:59:27', '2026-06-19 08:31:04'),
(9, 'Rosy', 'mabelebele', 'rosym@gmail.com', '0674568932', '45af1461ae8a2b697df1b9686cec0554', NULL, NULL, NULL, 'Gauteng', 'Pretoria', 0.00, 0, 0, 0, 'seller', 'active', '2026-06-19 09:51:03', '2026-06-19 09:56:32'),
(10, 'Alicia', 'Mthethwa', 'aliciam@gmail.com', '0636761590', 'b3c301e9a1944dd2d610207da0056377', NULL, NULL, NULL, 'Gauteng', 'Pretoria', 0.00, 0, 0, 0, 'buyer', 'deleted', '2026-06-19 10:48:18', '2026-06-19 10:58:16'),
(11, 'junior', 'Mthethwa', 'juniorm@gmail.com', '0730982367', 'b3c301e9a1944dd2d610207da0056377', NULL, NULL, NULL, 'Gauteng', 'Pretoria', 0.00, 0, 0, 0, 'seller', 'active', '2026-06-19 10:50:48', '2026-06-19 10:57:13');

-- --------------------------------------------------------

--
-- Table structure for table `tblwallet`
--

CREATE TABLE `tblwallet` (
  `wallet_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `buyer_balance` decimal(10,2) DEFAULT 0.00,
  `seller_balance` decimal(10,2) DEFAULT 0.00,
  `pending_balance` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tblwallet`
--

INSERT INTO `tblwallet` (`wallet_id`, `user_id`, `buyer_balance`, `seller_balance`, `pending_balance`) VALUES
(1, 1, 65.24, 389.44, 0.00),
(2, 2, 217.10, 642.16, 0.00),
(3, 3, 156.78, 82.72, 0.00),
(4, 4, 37.00, 55.79, 0.00),
(5, 5, 141.51, 61.25, 0.00),
(6, 6, 11.40, 245.20, 0.00),
(7, 7, 396.80, 361.74, 0.00),
(8, 8, 0.00, 0.00, 0.00),
(9, 9, 0.00, 0.00, 0.00),
(10, 10, 0.00, 0.00, 0.00),
(11, 11, 0.00, 0.00, 0.00);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tbladmin`
--
ALTER TABLE `tbladmin`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `tblaorder`
--
ALTER TABLE `tblaorder`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `buyer_id` (`buyer_id`),
  ADD KEY `seller_id` (`seller_id`),
  ADD KEY `listing_id` (`listing_id`);

--
-- Indexes for table `tbllisting`
--
ALTER TABLE `tbllisting`
  ADD PRIMARY KEY (`listing_id`),
  ADD KEY `seller_id` (`seller_id`);

--
-- Indexes for table `tbllistingphoto`
--
ALTER TABLE `tbllistingphoto`
  ADD PRIMARY KEY (`photo_id`),
  ADD KEY `listing_id` (`listing_id`);

--
-- Indexes for table `tblmessage`
--
ALTER TABLE `tblmessage`
  ADD PRIMARY KEY (`message_id`),
  ADD UNIQUE KEY `sender_id` (`sender_id`),
  ADD UNIQUE KEY `sender_id_2` (`sender_id`),
  ADD KEY `tblmessage_ibfk_2` (`receiver_id`);

--
-- Indexes for table `tblreview`
--
ALTER TABLE `tblreview`
  ADD PRIMARY KEY (`review_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `reviewer_id` (`reviewer_id`),
  ADD KEY `reviewed_user_id` (`reviewed_user_id`);

--
-- Indexes for table `tblsellerrequest`
--
ALTER TABLE `tblsellerrequest`
  ADD PRIMARY KEY (`request_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `tbluser`
--
ALTER TABLE `tbluser`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `shop_name` (`shop_name`);

--
-- Indexes for table `tblwallet`
--
ALTER TABLE `tblwallet`
  ADD PRIMARY KEY (`wallet_id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tbladmin`
--
ALTER TABLE `tbladmin`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tblaorder`
--
ALTER TABLE `tblaorder`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `tbllisting`
--
ALTER TABLE `tbllisting`
  MODIFY `listing_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `tbllistingphoto`
--
ALTER TABLE `tbllistingphoto`
  MODIFY `photo_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tblmessage`
--
ALTER TABLE `tblmessage`
  MODIFY `message_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `tblreview`
--
ALTER TABLE `tblreview`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tblsellerrequest`
--
ALTER TABLE `tblsellerrequest`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tbluser`
--
ALTER TABLE `tbluser`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `tblwallet`
--
ALTER TABLE `tblwallet`
  MODIFY `wallet_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `tbladmin`
--
ALTER TABLE `tbladmin`
  ADD CONSTRAINT `tbladmin_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `tbluser` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `tblaorder`
--
ALTER TABLE `tblaorder`
  ADD CONSTRAINT `tblaorder_ibfk_1` FOREIGN KEY (`buyer_id`) REFERENCES `tbluser` (`user_id`),
  ADD CONSTRAINT `tblaorder_ibfk_2` FOREIGN KEY (`seller_id`) REFERENCES `tbluser` (`user_id`),
  ADD CONSTRAINT `tblaorder_ibfk_3` FOREIGN KEY (`listing_id`) REFERENCES `tbllisting` (`listing_id`);

--
-- Constraints for table `tbllisting`
--
ALTER TABLE `tbllisting`
  ADD CONSTRAINT `tbllisting_ibfk_1` FOREIGN KEY (`seller_id`) REFERENCES `tbluser` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `tbllistingphoto`
--
ALTER TABLE `tbllistingphoto`
  ADD CONSTRAINT `tbllistingphoto_ibfk_1` FOREIGN KEY (`listing_id`) REFERENCES `tbllisting` (`listing_id`) ON DELETE CASCADE;

--
-- Constraints for table `tblmessage`
--
ALTER TABLE `tblmessage`
  ADD CONSTRAINT `tblmessage_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `tbluser` (`user_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `tblmessage_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `tbluser` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `tblreview`
--
ALTER TABLE `tblreview`
  ADD CONSTRAINT `tblreview_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `tblaorder` (`order_id`),
  ADD CONSTRAINT `tblreview_ibfk_2` FOREIGN KEY (`reviewer_id`) REFERENCES `tbluser` (`user_id`),
  ADD CONSTRAINT `tblreview_ibfk_3` FOREIGN KEY (`reviewed_user_id`) REFERENCES `tbluser` (`user_id`);

--
-- Constraints for table `tblsellerrequest`
--
ALTER TABLE `tblsellerrequest`
  ADD CONSTRAINT `tblsellerrequest_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `tbluser` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `tblwallet`
--
ALTER TABLE `tblwallet`
  ADD CONSTRAINT `tblwallet_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `tbluser` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
