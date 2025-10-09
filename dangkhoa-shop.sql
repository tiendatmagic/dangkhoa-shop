-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Oct 09, 2025 at 07:45 AM
-- Server version: 8.4.3
-- PHP Version: 8.1.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `dangkhoa-shop`
--

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2014_10_12_000000_create_users_table', 1),
(2, '2014_10_12_100000_create_password_resets_table', 1),
(3, '2019_08_19_000000_create_failed_jobs_table', 1),
(4, '2019_12_14_000001_create_personal_access_tokens_table', 1);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `order_code` char(8) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payment` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `txhash` varchar(70) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(15) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `note` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `order_code`, `user_id`, `payment`, `txhash`, `status`, `name`, `email`, `phone`, `address`, `note`, `created_at`, `updated_at`) VALUES
('915abfba-58f4-470c-8d51-325a522b9051', '41559750', '8e8fea86-fe33-4910-8d28-6ef614286d25', 'cash', NULL, 'completed', 'Đạt Mg', 'tiendatmagic8@yopmail.com', '1234567890', 'Nha Trang', NULL, '2025-10-08 22:45:24', '2025-10-08 22:51:17'),
('ddfa6524-81ae-429b-8f81-a5528075dad9', '31764317', '8e8fea86-fe33-4910-8d28-6ef614286d25', 'cash', NULL, 'completed', 'Đạt Mg', 'tiendatmagic8@yopmail.com', '1234567890', 'Nha Trang', NULL, '2025-05-09 00:29:49', '2025-10-09 00:30:05');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `order_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `product_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `size` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity` bigint NOT NULL,
  `price` decimal(10,4) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `size`, `quantity`, `price`, `created_at`, `updated_at`) VALUES
('1a3c6517-f154-498b-b467-87f7e130c94c', 'ddfa6524-81ae-429b-8f81-a5528075dad9', '30', 'POL', 1, 0.2382, '2025-10-09 00:29:50', '2025-10-09 00:29:50'),
('96020648-7fc2-40cb-a7bb-0431bce6a91e', 'ddfa6524-81ae-429b-8f81-a5528075dad9', '28', 'SOL', 1, 224.9500, '2025-10-09 00:29:49', '2025-10-09 00:29:49'),
('fd224e76-5c9b-4987-8eba-7a93bf31b7bc', '915abfba-58f4-470c-8d51-325a522b9051', '155', 'BNB', 1, 1300.1700, '2025-10-08 22:45:24', '2025-10-08 22:45:24');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` decimal(10,4) NOT NULL,
  `quantity` int DEFAULT NULL,
  `image` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `size` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `category` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_best_seller` tinyint(1) NOT NULL DEFAULT '0',
  `product_type` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `price`, `quantity`, `image`, `size`, `category`, `is_best_seller`, `product_type`, `created_at`, `updated_at`) VALUES
(1, 'Football Jersey', 6.0000, NULL, '[\"\\/storage\\/images\\/1e4600b5-984d-4eba-89a1-23820d5df8b2_1759851111.png\"]', '[\"S\",\"M\",\"XL\",\"XXL\"]', 'men', 0, NULL, '2025-10-07 09:04:39', '2025-10-07 08:31:52'),
(2, 'Hampton Long Sleeve Shirt', 50.0000, NULL, '[\"https:\\/\\/res.cloudinary.com\\/dfyykwzsa\\/image\\/upload\\/v1753063302\\/wowbtd0tlj6tpfxvkxcg.jpg\"]', '[\"S\",\"M\",\"L\"]', 'men', 0, NULL, '2025-10-07 09:04:39', '2025-10-07 07:42:19'),
(3, 'Cropped Fit Graphic T-Shirt', 30.0000, NULL, '[\"https://res.cloudinary.com/dfyykwzsa/image/upload/v1753063623/gnttim2dl0vxljmpejlu.jpg\", \"https://res.cloudinary.com/dfyykwzsa/image/upload/v1753063623/fwtx0axdz5iowwgarff7.jpg\"]', '[\"S\", \"M\", \"XL\", \"XXL\", \"L\"]', 'men', 0, NULL, '2025-10-07 09:04:39', '2025-10-07 09:04:47'),
(4, 'Easy Short', 10.0000, NULL, '[\"https://res.cloudinary.com/dfyykwzsa/image/upload/v1753063667/qudc6bwwlvnzfsgmx2ah.jpg\", \"https://res.cloudinary.com/dfyykwzsa/image/upload/v1753063667/ocu1bqsqtipqrk1dt61i.jpg\"]', '[\"S\", \"M\", \"L\"]', 'men', 0, NULL, '2025-10-07 09:04:39', '2025-10-07 09:04:47'),
(5, 'Alina Shirred Halter Top', 35.0000, NULL, '[\"https://res.cloudinary.com/dfyykwzsa/image/upload/v1753063762/ggjerydhpsewxvm8kbyd.jpg\", \"https://res.cloudinary.com/dfyykwzsa/image/upload/v1753063762/jqoik9izii3hv5j2z2b4.jpg\"]', '[\"S\", \"M\", \"L\", \"XL\"]', 'women', 1, NULL, '2025-10-07 09:04:39', '2025-10-07 09:04:47'),
(6, 'Mikki Drop Hem Mini Dress', 60.0000, NULL, '[\"https://res.cloudinary.com/dfyykwzsa/image/upload/v1753063815/hbrtbquoemjpaghclydx.jpg\", \"https://res.cloudinary.com/dfyykwzsa/image/upload/v1753063815/hk4ofjpoxe8ocrfvagkz.jpg\"]', '[\"S\", \"M\", \"L\", \"XL\"]', 'women', 1, NULL, '2025-10-07 09:04:39', '2025-10-07 09:04:47'),
(7, 'Haven Wide Leg Pant', 30.0000, NULL, '[\"https://res.cloudinary.com/dfyykwzsa/image/upload/v1753063885/cnffks7xikrhgjan9zil.jpg\", \"https://res.cloudinary.com/dfyykwzsa/image/upload/v1753063885/e6xpjggzveoxjospfxos.jpg\"]', '[\"S\", \"M\", \"L\", \"XL\"]', 'women', 0, NULL, '2025-10-07 09:04:39', '2025-10-07 09:04:47'),
(8, 'Kaia Faux Leather Bomber', 109.0000, NULL, '[\"https://res.cloudinary.com/dfyykwzsa/image/upload/v1753064020/zc0xgahhdyl4jttwci2e.jpg\", \"https://res.cloudinary.com/dfyykwzsa/image/upload/v1753064020/mwzlfoxgfofmncknwecv.jpg\"]', '[\"S\", \"M\", \"L\", \"XL\", \"XXL\"]', 'women', 1, NULL, '2025-10-07 09:04:39', '2025-10-07 09:04:47'),
(9, 'Sammy Oversize Hoodie', 34.0000, NULL, '[\"https://res.cloudinary.com/dfyykwzsa/image/upload/v1753064102/ri8zbzu6eq3z2ajkrwff.jpg\"]', '[\"S\", \"M\", \"L\"]', 'kids', 0, NULL, '2025-10-07 09:04:39', '2025-10-07 09:04:47'),
(10, 'Sammy Oversize Hoodie 2', 34.0000, NULL, '[\"https://res.cloudinary.com/dfyykwzsa/image/upload/v1753064102/ri8zbzu6eq3z2ajkrwff.jpg\"]', '[\"S\", \"M\", \"L\"]', 'kids', 0, NULL, '2025-10-07 09:04:39', '2025-10-07 09:04:47'),
(11, 'Sammy Oversize Hoodie 3', 34.0000, NULL, '[\"https://res.cloudinary.com/dfyykwzsa/image/upload/v1753064102/ri8zbzu6eq3z2ajkrwff.jpg\"]', '[\"S\", \"M\", \"L\"]', 'kids', 0, NULL, '2025-10-07 09:04:39', '2025-10-07 09:04:47'),
(28, 'SOL', 224.9500, 1, '[\"\\/storage\\/images\\/c843dde0-73ed-48b6-af7f-da2329530f3e_1759975080.png\"]', '[\"SOL\"]', '', 1, 'sol', '2025-10-08 08:21:51', '2025-10-09 00:29:12'),
(30, 'POL', 0.2382, 1, '[\"\\/storage\\/images\\/bb15b380-9615-488a-ac88-aca38ba4e2e7_1759943241.jpg\"]', '[\"POL\"]', 'crypto', 1, 'pol', '2025-10-08 10:07:22', '2025-10-09 00:29:12'),
(31, 'Vàng SJC', 4044.8800, 1, '[\"\\/storage\\/images\\/ae14c8d7-d2be-4344-9f47-cf73a51620a7_1759976340.webp\"]', '[\"V\\u00e0ng ph\\u00fa qu\\u00fd\"]', 'gold/silver', 1, 'paxg', '2025-10-08 19:19:01', '2025-10-09 00:29:12'),
(155, 'BNB', 1314.3000, 1, '[\"\\/storage\\/images\\/6f478042-b50d-4c10-bc14-9fd8f35b67d2_1759988654.png\"]', '[\"BNB\"]', 'crypto', 0, 'bnb', '2025-10-08 22:44:15', '2025-10-09 00:29:12');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `full_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(15) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(20,5) NOT NULL DEFAULT '0.00000',
  `is_admin` tinyint(1) NOT NULL DEFAULT '0',
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `full_name`, `phone`, `address`, `email`, `email_verified_at`, `password`, `amount`, `is_admin`, `remember_token`, `created_at`, `updated_at`) VALUES
('8e8fea86-fe33-4910-8d28-6ef614286d25', 'User82210', 'Đạt Mg', '1234567890', 'Nha Trang', 'tiendatmagic8@yopmail.com', NULL, '$2y$12$I/UGVtopHukSw7BHdk1AbOgK/70SfTarknv1bGLRb/vcZhzoO3iby', 0.00000, 1, NULL, '2025-08-23 13:00:57', '2025-09-28 01:44:54'),
('9b3e4d1c-91c1-440c-bb00-a76e60f7446b', 'User80107', 'User48096', NULL, NULL, 'doandangkhoa1492004@gmail.com', NULL, '$2y$12$d0aspH6l5/cgkp1utTOZFOP7h.h8F6PatdnVbnfOgO6QZQxQIpkhy', 0.00000, 1, NULL, '2025-10-07 10:06:45', '2025-10-07 10:06:45'),
('e0f8acd1-b5a9-4516-a12c-739e4728b0fe', 'User73407', 'User63361', '123456789876', '123', 'tiendatmagic9@yopmail.com', NULL, '$2y$12$kDcqueX0nC0GmKvezB4xnePsAL0UFq5ejyfjOIyhg4wKW5TKF2OVm', 0.00000, 0, NULL, '2025-08-28 10:43:15', '2025-08-29 04:56:37'),
('f1d8cd64-8f3e-4760-9d6b-9da5d1064ef7', 'User70153', 'User96908', NULL, NULL, 'viet99cm@gmail.com', NULL, '$2y$12$sgObxy73pAFJRgVE887f3OMSPrSkGSWUtwDzwckrUuRYZ4GJ.A2Z2', 0.00000, 0, NULL, '2025-08-29 06:55:43', '2025-08-29 06:55:43');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD KEY `password_resets_email_index` (`email`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
