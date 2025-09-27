-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Aug 31, 2025 at 03:38 AM
-- Server version: 10.6.20-MariaDB-cll-lve-log
-- PHP Version: 8.2.21

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+07:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `xomdoxythosting_tdatshop`
--

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
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
  `id` char(36) NOT NULL,
  `user_id` char(36) NOT NULL,
  `payment` varchar(10) NOT NULL,
  `txhash` varchar(70) DEFAULT NULL,
  `status` varchar(10) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `note` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `payment`, `txhash`, `status`, `name`, `email`, `phone`, `address`, `note`, `created_at`, `updated_at`) VALUES
('11dc5771-deaf-4bd6-909c-570d9fa9e2de', '8e8fea86-fe33-4910-8d28-6ef614286d25', 'cash', NULL, 'pending', 'Đạt Mg', 'tiendatmagic8@yopmail.com', '1234567890', 'Nha Trang', NULL, '2025-08-29 20:13:56', '2025-08-29 20:13:56'),
('1c2a0cc3-6c20-44d9-bf2d-4212df0d5aca', '8e8fea86-fe33-4910-8d28-6ef614286d25', 'cash', NULL, 'pending', 'Đạt Mg', 'tiendatmagic8@yopmail.com', '1234567890', 'Nha Trang', NULL, '2025-08-28 15:47:13', '2025-08-28 15:47:13'),
('1e769b46-9ca5-4cc1-9b29-40779e0a8181', '8e8fea86-fe33-4910-8d28-6ef614286d25', 'usdt', '0x44ed52b93ca583894f66848d9b0f72dbf637459325a8dff6da9f796a9194bb62', 'pending', NULL, NULL, NULL, NULL, NULL, '2025-08-28 14:27:44', '2025-08-28 14:27:44'),
('1f8b4b0b-596d-4b21-82c2-362f4526b61c', '8e8fea86-fe33-4910-8d28-6ef614286d25', 'cash', NULL, 'pending', 'Đạt Mg', 'tiendatmagic8@yopmail.com', '1234567890', 'Nha Trang', NULL, '2025-08-28 15:45:01', '2025-08-28 15:45:01'),
('3314c387-5199-492d-b82d-fac64688058e', '8e8fea86-fe33-4910-8d28-6ef614286d25', 'cash', NULL, 'pending', 'Đạt Mg', 'tiendatmagic8@yopmail.com', '1234567890', 'Nha Trang', NULL, '2025-08-28 16:13:09', '2025-08-28 16:13:09'),
('367b5a7f-2248-4239-84a7-c7e3166f2ae9', '8e8fea86-fe33-4910-8d28-6ef614286d25', 'usdt', '0xcb7441269f92426d999c56c539772b2bdd68df49675ae298b7f4eb5ba3ca8eb4', 'pending', 'Đạt Mg', 'tiendatmagic8@yopmail.com', '1234567890', 'Nha Trang', NULL, '2025-08-28 14:31:52', '2025-08-28 14:31:52'),
('55af48a4-a5a8-4f16-944c-d68b3b98ee0e', '8e8fea86-fe33-4910-8d28-6ef614286d25', 'usdt', '0xf8b161a09a1857beea6f41e63cb4ae6160efa89ca9f4d0ef8f0c5277d74adb3c', 'pending', NULL, NULL, NULL, NULL, NULL, '2025-08-28 05:53:00', '2025-08-28 05:53:00'),
('6234f4b9-6882-4ae1-bd1a-d1d95985c0a8', '8e8fea86-fe33-4910-8d28-6ef614286d25', 'cash', NULL, 'pending', 'Đạt Mg', 'tiendatmagic8@yopmail.com', '1234567890', 'Nha Trang', NULL, '2025-08-29 19:23:10', '2025-08-29 19:23:10'),
('8bd0d101-5f38-4e31-8f49-80afa379c935', '8e8fea86-fe33-4910-8d28-6ef614286d25', 'usdt', '0xcf77c0ea1d4916d4bad6a3d54d8c786e1d3724d7b295b6ecfa3ae73dac9d2c70', 'pending', 'Đạt Mg', 'tiendatmagic8@yopmail.com', '1234567890', 'Nha Trang', NULL, '2025-08-29 00:18:34', '2025-08-29 00:18:34'),
('9102ebb2-8d4b-43a3-853b-bcd6dd08f786', '8e8fea86-fe33-4910-8d28-6ef614286d25', 'cash', NULL, 'pending', 'Đạt Magic', 'tiendatmagic8@yopmail.com', '1234567890', 'Nha Trang', NULL, '2025-08-30 07:02:51', '2025-08-30 07:02:51'),
('99a5ec1d-8b76-4cda-bffd-618b7ec812c0', 'e0f8acd1-b5a9-4516-a12c-739e4728b0fe', 'cash', NULL, 'pending', 'User63361', 'tiendatmagic9@yopmail.com', '123456789876', '123', NULL, '2025-08-29 08:35:51', '2025-08-29 08:35:51'),
('a956e297-cdbf-40e0-969c-e9203ca16729', 'e0f8acd1-b5a9-4516-a12c-739e4728b0fe', 'usdt', '0xa223d6de37ed85d994c633e643bb29813dfd027f0f3a7cb0a5c629cd79d731e4', 'pending', 'User63361', 'tiendatmagic9@yopmail.com', '123456789876', '123', NULL, '2025-08-29 01:01:50', '2025-08-29 01:01:50'),
('ad87d71a-d68d-4237-9613-f7b588c8e4d7', '8e8fea86-fe33-4910-8d28-6ef614286d25', 'usdt', '0xea21a2438d45aadac03c535bd33fd85e1a2baa52da7ac7f368d17441a17b6d2b', 'pending', NULL, NULL, NULL, NULL, NULL, '2025-08-27 20:21:38', '2025-08-27 20:21:38'),
('ba11f4c3-f663-45f6-9546-96e95cdd27a9', '8e8fea86-fe33-4910-8d28-6ef614286d25', 'cash', NULL, 'pending', NULL, NULL, NULL, NULL, NULL, '2025-08-28 14:26:19', '2025-08-28 14:26:19'),
('bffecfe5-0f8a-42cb-abd3-0d7315e9b516', '8e8fea86-fe33-4910-8d28-6ef614286d25', 'cash', NULL, 'pending', 'Đạt Magic', 'tiendatmagic8@yopmail.com', '1234567890', 'Nha Trang', NULL, '2025-08-30 09:41:59', '2025-08-30 09:41:59'),
('da6550ff-7e2c-4816-9024-fa42900318de', '8e8fea86-fe33-4910-8d28-6ef614286d25', 'cash', NULL, 'pending', 'Đạt Magic', 'tiendatmagic8@yopmail.com', '1234567890', 'Nha Trang', NULL, '2025-08-29 20:47:11', '2025-08-29 20:47:11'),
('ecac91f9-1693-47c5-82e3-7cc315239670', '8e8fea86-fe33-4910-8d28-6ef614286d25', 'cash', NULL, 'pending', 'Đạt Mg', 'tiendatmagic8@yopmail.com', '1234567890', 'Nha Trang', NULL, '2025-08-29 20:45:09', '2025-08-29 20:45:09'),
('f7f75064-82e4-4666-9bdd-44ce8cea0f53', '8e8fea86-fe33-4910-8d28-6ef614286d25', 'cash', NULL, 'pending', 'Đạt Mg', 'tiendatmagic8@yopmail.com', '1234567890', 'Nha Trang', NULL, '2025-08-29 19:28:12', '2025-08-29 19:28:12');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` char(36) NOT NULL,
  `order_id` char(36) NOT NULL,
  `product_id` char(36) NOT NULL,
  `size` varchar(5) NOT NULL,
  `quantity` bigint(20) NOT NULL,
  `price` bigint(20) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `size`, `quantity`, `price`, `created_at`, `updated_at`) VALUES
('0958b234-0938-40dd-a737-31070b45cb21', 'a956e297-cdbf-40e0-969c-e9203ca16729', '1', 'XL', 1, 1, '2025-08-29 01:01:50', '2025-08-29 01:01:50'),
('1217e822-5fdc-4dac-8879-70b892919d0b', '9102ebb2-8d4b-43a3-853b-bcd6dd08f786', '2', 'L', 1, 50, '2025-08-30 07:02:51', '2025-08-30 07:02:51'),
('1e19815c-25ab-4039-930e-3f8726f5a05e', '1f8b4b0b-596d-4b21-82c2-362f4526b61c', '1', 'XXL', 1, 1, '2025-08-28 15:45:01', '2025-08-28 15:45:01'),
('23342310-4a33-4c7d-96e0-5cdea6d357f9', 'bffecfe5-0f8a-42cb-abd3-0d7315e9b516', '1', 'XXL', 1, 1, '2025-08-30 09:41:59', '2025-08-30 09:41:59'),
('2621c16b-6ce0-450c-882f-2a34ade9d959', '1c2a0cc3-6c20-44d9-bf2d-4212df0d5aca', '2', 'L', 1, 50, '2025-08-28 15:47:13', '2025-08-28 15:47:13'),
('3242d060-9955-45e1-b90a-3cff29e8c459', 'bffecfe5-0f8a-42cb-abd3-0d7315e9b516', '3', 'XXL', 1, 30, '2025-08-30 09:41:59', '2025-08-30 09:41:59'),
('3e945dfe-176e-4c32-8048-c2d51c83b64c', '9102ebb2-8d4b-43a3-853b-bcd6dd08f786', '1', 'XXL', 1, 1, '2025-08-30 07:02:51', '2025-08-30 07:02:51'),
('4e92c626-3243-4dac-8e15-79d3b111d340', '1f8b4b0b-596d-4b21-82c2-362f4526b61c', '1', 'XXL', 1, 1, '2025-08-28 15:45:01', '2025-08-28 15:45:01'),
('538e1461-9954-4e7f-a5df-e98daafeb873', '1e769b46-9ca5-4cc1-9b29-40779e0a8181', '1', 'XXL', 1, 1, '2025-08-28 14:27:44', '2025-08-28 14:27:44'),
('54aa8227-826f-4800-8b5d-ff800d97ca05', '1c2a0cc3-6c20-44d9-bf2d-4212df0d5aca', '1', 'XXL', 1, 1, '2025-08-28 15:47:13', '2025-08-28 15:47:13'),
('554a759b-fea3-49bd-afb0-b196ed571d8a', '3314c387-5199-492d-b82d-fac64688058e', '1', 'XXL', 1, 1, '2025-08-28 16:13:09', '2025-08-28 16:13:09'),
('5e7f164e-4404-4d44-a851-f57e63a99cd7', '367b5a7f-2248-4239-84a7-c7e3166f2ae9', '1', 'XXL', 1, 1, '2025-08-28 14:31:52', '2025-08-28 14:31:52'),
('60056de0-3430-4bde-a667-60e7f8f76a87', 'ba11f4c3-f663-45f6-9546-96e95cdd27a9', '1', 'XXL', 1, 1, '2025-08-28 14:26:19', '2025-08-28 14:26:19'),
('6576f0ea-ff63-49e4-b719-099ac5d9922d', 'bffecfe5-0f8a-42cb-abd3-0d7315e9b516', '3', 'XXL', 1, 30, '2025-08-30 09:41:59', '2025-08-30 09:41:59'),
('6fe1e29a-611d-4eec-a99a-917d3a57421d', '11dc5771-deaf-4bd6-909c-570d9fa9e2de', '2', 'S', 1, 50, '2025-08-29 20:13:56', '2025-08-29 20:13:56'),
('7009e2d6-6777-463f-8d34-f4c6fb9c7e32', 'a956e297-cdbf-40e0-969c-e9203ca16729', '1', 'XXL', 1, 1, '2025-08-29 01:01:50', '2025-08-29 01:01:50'),
('71a37dcb-ae3f-4c7f-a8d8-d2c2520e18e4', '11dc5771-deaf-4bd6-909c-570d9fa9e2de', '3', 'M', 1, 30, '2025-08-29 20:13:56', '2025-08-29 20:13:56'),
('74de514b-5605-463b-bb30-ff2b503657ea', 'da6550ff-7e2c-4816-9024-fa42900318de', '2', 'L', 1, 50, '2025-08-29 20:47:11', '2025-08-29 20:47:11'),
('7deaa6cd-0858-404a-865d-26ae22d06aa9', 'f7f75064-82e4-4666-9bdd-44ce8cea0f53', '1', 'XXL', 1, 1, '2025-08-29 19:28:12', '2025-08-29 19:28:12'),
('8d126d04-02aa-4372-8a7d-76b630563de1', 'ad87d71a-d68d-4237-9613-f7b588c8e4d7', '1', 'XXL', 1, 1, '2025-08-27 20:21:39', '2025-08-27 20:21:39'),
('92900640-bfe5-4108-bcae-2330ccb1494f', 'ecac91f9-1693-47c5-82e3-7cc315239670', '1', 'S', 1, 1, '2025-08-29 20:45:09', '2025-08-29 20:45:09'),
('99428a96-8f40-4e75-a171-f24ca0f71813', '55af48a4-a5a8-4f16-944c-d68b3b98ee0e', '1', 'XXL', 1, 1, '2025-08-28 05:53:00', '2025-08-28 05:53:00'),
('9a307475-f5f3-4a51-8665-b0ddc037a8f5', '99a5ec1d-8b76-4cda-bffd-618b7ec812c0', '2', 'L', 1, 50, '2025-08-29 08:35:51', '2025-08-29 08:35:51'),
('9d647f66-47ef-4cce-897b-1ec168871179', 'bffecfe5-0f8a-42cb-abd3-0d7315e9b516', '1', 'XXL', 1, 1, '2025-08-30 09:41:59', '2025-08-30 09:41:59'),
('a6df2a54-4d00-4799-9127-d813c1b19db6', 'bffecfe5-0f8a-42cb-abd3-0d7315e9b516', '3', 'XXL', 1, 30, '2025-08-30 09:41:59', '2025-08-30 09:41:59'),
('ab0a7d1b-a1a6-4826-8c6b-805fa1bf9999', '6234f4b9-6882-4ae1-bd1a-d1d95985c0a8', '2', 'L', 1, 50, '2025-08-29 19:23:10', '2025-08-29 19:23:10'),
('c33ab377-79de-427f-9414-75cdd921d745', '3314c387-5199-492d-b82d-fac64688058e', '3', 'L', 3, 30, '2025-08-28 16:13:09', '2025-08-28 16:13:09'),
('c928e2bc-b573-41b8-a6d8-15b1caca00d3', '11dc5771-deaf-4bd6-909c-570d9fa9e2de', '4', 'L', 1, 25, '2025-08-29 20:13:56', '2025-08-29 20:13:56'),
('dda2b3c1-933d-4d10-bed0-fb63163da98d', '1f8b4b0b-596d-4b21-82c2-362f4526b61c', '2', 'L', 1, 50, '2025-08-28 15:45:01', '2025-08-28 15:45:01'),
('ef3b376f-6d3b-4ce7-8fb1-64dbbf59d2b8', '8bd0d101-5f38-4e31-8f49-80afa379c935', '1', 'XXL', 1, 1, '2025-08-29 00:18:34', '2025-08-29 00:18:34'),
('f8fb256c-fe69-4ff9-b864-ff40ece35426', '11dc5771-deaf-4bd6-909c-570d9fa9e2de', '4', 'M', 1, 25, '2025-08-29 20:13:56', '2025-08-29 20:13:56');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `image` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`image`)),
  `size` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`size`)),
  `category` varchar(50) NOT NULL,
  `is_best_seller` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `price`, `image`, `size`, `category`, `is_best_seller`, `created_at`, `updated_at`) VALUES
(1, 'Football Jersey', 6.00, '[\"https://res.cloudinary.com/dfyykwzsa/image/upload/v1753063577/b1devpuovnvqwdo8pfia.jpg\", \"https://res.cloudinary.com/dfyykwzsa/image/upload/v1753063577/o7kcxzpkbvobb05iu949.jpg\", \"https://res.cloudinary.com/dfyykwzsa/image/upload/v1753063577/iwopzvmpc5olrt7anheh.jpg\"]', '[\"S\", \"M\", \"XL\", \"XXL\"]', 'men', 1, NULL, NULL),
(2, 'Hampton Long Sleeve Shirt', 50.00, '[\"https://res.cloudinary.com/dfyykwzsa/image/upload/v1753063302/wowbtd0tlj6tpfxvkxcg.jpg\", \"https://res.cloudinary.com/dfyykwzsa/image/upload/v1753063302/kjfejg6lvpqwib89qwfy.jpg\"]', '[\"S\", \"M\", \"L\"]', 'men', 0, NULL, NULL),
(3, 'Cropped Fit Graphic T-Shirt', 30.00, '[\"https://res.cloudinary.com/dfyykwzsa/image/upload/v1753063623/gnttim2dl0vxljmpejlu.jpg\", \"https://res.cloudinary.com/dfyykwzsa/image/upload/v1753063623/fwtx0axdz5iowwgarff7.jpg\"]', '[\"S\", \"M\", \"XL\", \"XXL\", \"L\"]', 'men', 0, NULL, NULL),
(4, 'Easy Short', 10.00, '[\"https://res.cloudinary.com/dfyykwzsa/image/upload/v1753063667/qudc6bwwlvnzfsgmx2ah.jpg\", \"https://res.cloudinary.com/dfyykwzsa/image/upload/v1753063667/ocu1bqsqtipqrk1dt61i.jpg\"]', '[\"S\", \"M\", \"L\"]', 'men', 0, NULL, NULL),
(5, 'Alina Shirred Halter Top', 35.00, '[\"https://res.cloudinary.com/dfyykwzsa/image/upload/v1753063762/ggjerydhpsewxvm8kbyd.jpg\", \"https://res.cloudinary.com/dfyykwzsa/image/upload/v1753063762/jqoik9izii3hv5j2z2b4.jpg\"]', '[\"S\", \"M\", \"L\", \"XL\"]', 'women', 1, NULL, NULL),
(6, 'Mikki Drop Hem Mini Dress', 60.00, '[\"https://res.cloudinary.com/dfyykwzsa/image/upload/v1753063815/hbrtbquoemjpaghclydx.jpg\", \"https://res.cloudinary.com/dfyykwzsa/image/upload/v1753063815/hk4ofjpoxe8ocrfvagkz.jpg\"]', '[\"S\", \"M\", \"L\", \"XL\"]', 'women', 1, NULL, NULL),
(7, 'Haven Wide Leg Pant', 30.00, '[\"https://res.cloudinary.com/dfyykwzsa/image/upload/v1753063885/cnffks7xikrhgjan9zil.jpg\", \"https://res.cloudinary.com/dfyykwzsa/image/upload/v1753063885/e6xpjggzveoxjospfxos.jpg\"]', '[\"S\", \"M\", \"L\", \"XL\"]', 'women', 0, NULL, NULL),
(8, 'Kaia Faux Leather Bomber', 109.00, '[\"https://res.cloudinary.com/dfyykwzsa/image/upload/v1753064020/zc0xgahhdyl4jttwci2e.jpg\", \"https://res.cloudinary.com/dfyykwzsa/image/upload/v1753064020/mwzlfoxgfofmncknwecv.jpg\"]', '[\"S\", \"M\", \"L\", \"XL\", \"XXL\"]', 'women', 1, NULL, NULL),
(9, 'Sammy Oversize Hoodie', 34.00, '[\"https://res.cloudinary.com/dfyykwzsa/image/upload/v1753064102/ri8zbzu6eq3z2ajkrwff.jpg\"]', '[\"S\", \"M\", \"L\"]', 'kids', 0, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` char(36) NOT NULL,
  `name` varchar(255) NOT NULL,
  `full_name` varchar(255) DEFAULT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `amount` decimal(20,5) NOT NULL DEFAULT 0.00000,
  `is_admin` tinyint(1) NOT NULL DEFAULT 0,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `full_name`, `phone`, `address`, `email`, `email_verified_at`, `password`, `amount`, `is_admin`, `remember_token`, `created_at`, `updated_at`) VALUES
('8e8fea86-fe33-4910-8d28-6ef614286d25', 'User82210', 'Đạt Mg', '1234567890', 'Nha Trang', 'tiendatmagic8@yopmail.com', NULL, '$2y$12$I/UGVtopHukSw7BHdk1AbOgK/70SfTarknv1bGLRb/vcZhzoO3iby', 0.00000, 0, NULL, '2025-08-24 03:00:57', '2025-08-30 20:25:44'),
('e0f8acd1-b5a9-4516-a12c-739e4728b0fe', 'User73407', 'User63361', '123456789876', '123', 'tiendatmagic9@yopmail.com', NULL, '$2y$12$kDcqueX0nC0GmKvezB4xnePsAL0UFq5ejyfjOIyhg4wKW5TKF2OVm', 0.00000, 0, NULL, '2025-08-29 00:43:15', '2025-08-29 18:56:37'),
('f1d8cd64-8f3e-4760-9d6b-9da5d1064ef7', 'User70153', 'User96908', NULL, NULL, 'viet99cm@gmail.com', NULL, '$2y$12$sgObxy73pAFJRgVE887f3OMSPrSkGSWUtwDzwckrUuRYZ4GJ.A2Z2', 0.00000, 0, NULL, '2025-08-29 20:55:43', '2025-08-29 20:55:43');

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
