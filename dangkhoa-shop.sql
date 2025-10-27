-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Oct 27, 2025 at 05:00 PM
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
-- Database: `dangkhoa-shop2`
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
('17d4d76b-0b9a-48af-98c0-90dd56ba403b', '43684875', '8e8fea86-fe33-4910-8d28-6ef614286d25', 'cash', NULL, 'pending', 'Đạt Mg', 'tiendatmagic8@yopmail.com', '1234567890', 'Nha Trang', NULL, '2025-10-09 20:27:39', '2025-10-09 20:27:39'),
('2073f855-0956-45c4-b1af-6ba673182a7f', '17125650', '9b3e4d1c-91c1-440c-bb00-a76e60f7446b', 'cash', NULL, 'completed', 'User48096', 'doandangkhoa1492004@gmail.com', '0879288566', 'Da Nang', NULL, '2025-10-09 07:10:54', '2025-10-09 09:17:25'),
('2411f9d1-d45c-44e8-80b1-364b2c7cfada', '55267082', 'f8734e0e-1a12-4faf-b78d-aa9c17030cc4', 'cash', NULL, 'completed', 'User57389', 'nguyenanhvu9899@gmai.com', '0889857843', 'Hà Nội', NULL, '2025-10-09 01:01:42', '2025-10-09 01:04:14'),
('25eebef4-233b-41fb-8839-6dc889e12c38', '05207906', '8e8fea86-fe33-4910-8d28-6ef614286d25', 'cash', NULL, 'pending', 'Đạt Mg', 'tiendatmagic8@yopmail.com', '1234567890', 'Nha Trang', NULL, '2025-10-09 20:24:39', '2025-10-09 20:24:39'),
('3d903a94-70f1-4d49-9eef-b697f6c92a88', '73347562', '8e8fea86-fe33-4910-8d28-6ef614286d25', 'cash', NULL, 'pending', 'Đạt Mg', 'tiendatmagic8@yopmail.com', '1234567890', 'Nha Trang', NULL, '2025-10-09 20:02:58', '2025-10-09 20:02:58'),
('44849ec8-e471-4cf0-ad85-8c8bf7ff550f', '08556176', '8e8fea86-fe33-4910-8d28-6ef614286d25', 'cash', NULL, 'pending', 'Đạt Mg', 'tiendatmagic8@yopmail.com', '1234567890', 'Nha Trang', NULL, '2025-10-09 20:24:50', '2025-10-09 20:24:50'),
('4994e417-f861-46f2-971b-b0837049f3f9', '13264963', '6b5b50dd-acbc-4373-b691-86f69d11b87a', 'cash', NULL, 'completed', 'User26725', 'anhvu149@gmail.com', '0879288864', 'Da Nang', '0x5c452de543ba531c8d38bbcfa582a982a6374b95cea30d6d909ef3584908d376', '2025-10-09 01:13:12', '2025-10-09 01:13:59'),
('5324781f-3478-4d67-b03c-40d7a08cea7a', '90260830', '8e8fea86-fe33-4910-8d28-6ef614286d25', 'cash', NULL, 'pending', 'Đạt Mg', 'tiendatmagic8@yopmail.com', '1234567890', 'Nha Trang', NULL, '2025-10-09 20:23:53', '2025-10-09 20:23:53'),
('5d17caa2-f2fc-448c-8fa7-a05d865b174d', '90900865', '8e8fea86-fe33-4910-8d28-6ef614286d25', 'cash', NULL, 'pending', 'Đạt Mg', 'tiendatmagic8@yopmail.com', '1234567890', 'Nha Trang', NULL, '2025-10-09 20:25:19', '2025-10-09 20:25:19'),
('67083da0-16aa-4756-9ffb-b48cfbce4ba6', '14469788', '8e8fea86-fe33-4910-8d28-6ef614286d25', 'cash', NULL, 'pending', 'Đạt Mg', 'tiendatmagic8@yopmail.com', '1234567890', 'Nha Trang', NULL, '2025-10-09 21:04:46', '2025-10-09 21:04:46'),
('6c24c13c-95d0-4cd5-9ad2-69f32c7e03d3', '88001929', '8e8fea86-fe33-4910-8d28-6ef614286d25', 'cash', NULL, 'pending', 'Đạt Mg', 'tiendatmagic8@yopmail.com', '1234567890', 'Nha Trang', NULL, '2025-10-09 20:07:46', '2025-10-09 20:07:46'),
('7f3af434-339f-413b-abda-9d8723859ca3', '00394236', '8e8fea86-fe33-4910-8d28-6ef614286d25', 'cash', NULL, 'pending', 'Đạt Mg', 'tiendatmagic8@yopmail.com', '1234567890', 'Nha Trang', NULL, '2025-10-09 17:50:32', '2025-10-09 17:50:32'),
('7fa76404-ca9b-498d-9602-70f45963e647', '43689166', '6b5b50dd-acbc-4373-b691-86f69d11b87a', 'cash', NULL, 'completed', 'User26725', 'anhvu149@gmail.com', '0879288478', 'Ho Chi Minh', '0x5e4FC833925D66918Af48F1deb0d07f12229bF8b', '2025-10-09 01:08:35', '2025-10-09 01:13:41'),
('915abfba-58f4-470c-8d51-325a522b9051', '41559750', '8e8fea86-fe33-4910-8d28-6ef614286d25', 'cash', NULL, 'completed', 'Đạt Mg', 'tiendatmagic8@yopmail.com', '1234567890', 'Nha Trang', NULL, '2025-10-08 22:45:24', '2025-10-08 22:51:17'),
('980c0683-dbdc-48d5-849e-95292aff8e10', '10003079', '8e8fea86-fe33-4910-8d28-6ef614286d25', 'cash', NULL, 'pending', 'Đạt Mg', 'tiendatmagic8@yopmail.com', '1234567890', 'Nha Trang', NULL, '2025-10-09 20:09:14', '2025-10-09 20:09:14'),
('9d77c636-5873-4be0-bf35-4f593d2361ff', '38801451', '8e8fea86-fe33-4910-8d28-6ef614286d25', 'cash', NULL, 'pending', 'Đạt Mg', 'tiendatmagic8@yopmail.com', '1234567890', 'Nha Trang', NULL, '2025-10-09 18:28:20', '2025-10-09 18:28:20'),
('9ddeafcc-3b43-4de7-bbd0-08d490001e0d', '39297056', '6b5b50dd-acbc-4373-b691-86f69d11b87a', 'cash', NULL, 'completed', 'User26725', 'anhvu149@gmail.com', '0968237410', 'Phường Hàng Bạc, Quận Hoàn Kiếm, Hà Nội', NULL, '2025-10-09 01:12:06', '2025-10-09 01:13:52'),
('a0a24d37-ef6e-4c16-a127-5e71e9c9a401', '33539644', '8e8fea86-fe33-4910-8d28-6ef614286d25', 'cash', NULL, 'pending', 'Đạt Mg', 'tiendatmagic8@yopmail.com', '1234567890', 'Nha Trang', '0x1AD11e0e96797a14336Bf474676EB0A332055555', '2025-10-09 00:44:24', '2025-10-09 00:44:24'),
('ab495b7f-639c-41d8-88de-e05bc4d2cc48', '42770696', '8e8fea86-fe33-4910-8d28-6ef614286d25', 'cash', NULL, 'pending', 'Đạt Mg', 'tiendatmagic8@yopmail.com', '1234567890', 'Nha Trang', '0x1AD11e0e96797a14336Bf474676EB0A332055555', '2025-10-09 21:09:16', '2025-10-09 21:09:16'),
('abc06081-ef57-459a-8db0-66446440720f', '11799214', '8e8fea86-fe33-4910-8d28-6ef614286d25', 'cash', NULL, 'pending', 'Đạt Mg', 'tiendatmagic8@yopmail.com', '1234567890', 'Nha Trang', NULL, '2025-10-09 20:32:05', '2025-10-09 20:32:05'),
('b8def3a4-b76f-4d3f-a87e-81782297ab87', '98536480', '9b3e4d1c-91c1-440c-bb00-a76e60f7446b', 'cash', NULL, 'completed', 'User48096', 'doandangkhoa1492004@gmail.com', '0879288478', '29X6+76J, Ta An Khuong Dong-Tan Thuan, Tn Thun, m Di, C Mau, Vit Nam', '0x421f2f731b3a1C30c3893BbF97F0A337FB921815', '2025-10-09 01:26:27', '2025-10-09 01:33:24'),
('d13d65c6-88d7-49dd-8892-30f34078b660', '84483705', '8e8fea86-fe33-4910-8d28-6ef614286d25', 'cash', NULL, 'pending', 'Đạt Mg', 'tiendatmagic8@yopmail.com', '1234567890', 'Nha Trang', NULL, '2025-10-09 20:11:26', '2025-10-09 20:11:26'),
('ddfa6524-81ae-429b-8f81-a5528075dad9', '31764317', '8e8fea86-fe33-4910-8d28-6ef614286d25', 'cash', NULL, 'completed', 'Đạt Mg', 'tiendatmagic8@yopmail.com', '1234567890', 'Nha Trang', NULL, '2025-05-09 00:29:49', '2025-10-09 00:30:05'),
('e3a23308-147c-48c0-9dda-cf2831bce051', '89834020', '8e8fea86-fe33-4910-8d28-6ef614286d25', 'cash', NULL, 'pending', 'Đạt Mg', 'tiendatmagic8@yopmail.com', '1234567890', 'Nha Trang', NULL, '2025-10-09 00:42:43', '2025-10-09 00:42:43'),
('f6d0822c-35cf-4e0c-bb0f-8417dfc62119', '33313714', '8e8fea86-fe33-4910-8d28-6ef614286d25', 'cash', NULL, 'pending', 'Đạt Mg', 'tiendatmagic8@yopmail.com', '1234567890', 'Nha Trang', NULL, '2025-10-09 20:29:08', '2025-10-09 20:29:08'),
('fe18a24b-444c-4a40-9f62-8ff6eee866e2', '68804961', '8e8fea86-fe33-4910-8d28-6ef614286d25', 'cash', NULL, 'pending', 'Đạt Mg', 'tiendatmagic8@yopmail.com', '1234567890', 'Nha Trang', NULL, '2025-10-09 21:07:08', '2025-10-09 21:07:08');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `order_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `product_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `size` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity` bigint NOT NULL,
  `price` decimal(12,6) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `size`, `quantity`, `price`, `created_at`, `updated_at`) VALUES
('095826f5-b7e9-4245-a091-8ad13bed0b5b', '44849ec8-e471-4cf0-ad85-8c8bf7ff550f', '155', 'BNB', 1, 1248.660000, '2025-10-09 20:24:50', '2025-10-09 20:24:50'),
('0e4627e0-a685-4f06-88fc-2ad6cac2d624', 'd13d65c6-88d7-49dd-8892-30f34078b660', '155', 'BNB', 1, 1250.410000, '2025-10-09 20:11:26', '2025-10-09 20:11:26'),
('0e65e8d7-0e79-40dd-981b-5de0d5ad0bf4', '5324781f-3478-4d67-b03c-40d7a08cea7a', '155', 'BNB', 1, 1248.660000, '2025-10-09 20:23:53', '2025-10-09 20:23:53'),
('176599fc-cce5-4072-ad85-bac1c553c8ee', 'a0a24d37-ef6e-4c16-a127-5e71e9c9a401', '155', 'BNB', 1, 1314.300000, '2025-10-09 00:44:24', '2025-10-09 00:44:24'),
('18eec5fb-faec-4cc9-a879-85e045591d2d', 'f6d0822c-35cf-4e0c-bb0f-8417dfc62119', '31', 'Vàng PNJ', 1, 3982.460000, '2025-10-09 20:29:08', '2025-10-09 20:29:08'),
('1a3c6517-f154-498b-b467-87f7e130c94c', 'ddfa6524-81ae-429b-8f81-a5528075dad9', '30', 'POL', 1, 0.238200, '2025-10-09 00:29:50', '2025-10-09 00:29:50'),
('1ce56f67-2440-4e8f-8097-01eaf0f111ca', '7f3af434-339f-413b-abda-9d8723859ca3', '30', 'POL', 1, 0.235600, '2025-10-09 17:50:32', '2025-10-09 17:50:32'),
('1e0ce07e-c50a-441b-bbc3-ceff062ce550', '9ddeafcc-3b43-4de7-bbd0-08d490001e0d', '31', 'Vàng SJC', 0, 4047.330000, '2025-10-09 01:12:06', '2025-10-09 01:12:06'),
('26793bc4-f6f0-476e-944a-ae00c630ce9b', '44849ec8-e471-4cf0-ad85-8c8bf7ff550f', '31', 'Vàng PNJ', 1, 3982.460000, '2025-10-09 20:24:50', '2025-10-09 20:24:50'),
('305eae5c-23b0-412b-9574-91469fd1022a', '67083da0-16aa-4756-9ffb-b48cfbce4ba6', '30', 'POL', 2, 0.238300, '2025-10-09 21:04:46', '2025-10-09 21:04:46'),
('32ca6c49-10f3-4a43-8ab2-ebe711e78181', '6c24c13c-95d0-4cd5-9ad2-69f32c7e03d3', '155', 'BNB', 1, 1250.410000, '2025-10-09 20:07:46', '2025-10-09 20:07:46'),
('3c5d992e-3b6f-48ab-af1b-2926271b3c58', '17d4d76b-0b9a-48af-98c0-90dd56ba403b', '155', 'BNB', 1, 1248.660000, '2025-10-09 20:27:39', '2025-10-09 20:27:39'),
('424e8307-a6ce-40c1-93e7-2ff5f12464cc', '4994e417-f861-46f2-971b-b0837049f3f9', '28', 'SOL', 2, 224.520000, '2025-10-09 01:13:12', '2025-10-09 01:13:12'),
('4f8e0d38-d52f-4300-9512-e0184acdedf6', 'f6d0822c-35cf-4e0c-bb0f-8417dfc62119', '31', 'Vàng SJC', 1, 3982.460000, '2025-10-09 20:29:08', '2025-10-09 20:29:08'),
('544f3dc7-ba1a-40b7-b571-9b63eb91035d', '2073f855-0956-45c4-b1af-6ba673182a7f', '31', 'Vàng SJC', 0, 4046.190000, '2025-10-09 07:10:54', '2025-10-09 07:10:54'),
('5528f6d4-0bfa-4987-af7f-911567b56fd6', 'abc06081-ef57-459a-8db0-66446440720f', '155', 'BNB', 1, 1248.660000, '2025-10-09 20:32:05', '2025-10-09 20:32:05'),
('5d60afff-4f3c-4a2e-a432-d9d21f405100', '5d17caa2-f2fc-448c-8fa7-a05d865b174d', '31', 'Vàng SJC', 1, 3982.460000, '2025-10-09 20:25:19', '2025-10-09 20:25:19'),
('608c47e7-4bfd-4c93-93b1-c59bf43aa47e', '5324781f-3478-4d67-b03c-40d7a08cea7a', '31', 'Vàng SJC', 1, 3982.460000, '2025-10-09 20:23:53', '2025-10-09 20:23:53'),
('680f79ae-400d-4712-99f8-733e7ed1a566', '44849ec8-e471-4cf0-ad85-8c8bf7ff550f', '31', 'Vàng SJC', 1, 3982.460000, '2025-10-09 20:24:50', '2025-10-09 20:24:50'),
('73b885d6-0f20-4b69-8310-f82abc7c772b', '25eebef4-233b-41fb-8839-6dc889e12c38', '31', 'Vàng SJC', 1, 3982.460000, '2025-10-09 20:24:39', '2025-10-09 20:24:39'),
('75b5366c-91c2-48d1-8118-cf1873da831f', '5d17caa2-f2fc-448c-8fa7-a05d865b174d', '31', 'Vàng PNJ', 1, 3982.460000, '2025-10-09 20:25:19', '2025-10-09 20:25:19'),
('771a693d-ec20-4bd7-825d-e9501d19b7b3', '25eebef4-233b-41fb-8839-6dc889e12c38', '31', 'Vàng PNJ', 1, 3982.460000, '2025-10-09 20:24:39', '2025-10-09 20:24:39'),
('7ff7ddc9-bcdb-4ebb-b858-31781433fa2d', 'ab495b7f-639c-41d8-88de-e05bc4d2cc48', '30', 'POL', 1, 0.238300, '2025-10-09 21:09:16', '2025-10-09 21:09:16'),
('96020648-7fc2-40cb-a7bb-0431bce6a91e', 'ddfa6524-81ae-429b-8f81-a5528075dad9', '28', 'SOL', 1, 224.950000, '2025-10-09 00:29:49', '2025-10-09 00:29:49'),
('98a2e2a8-040a-4c0d-8a8a-50ef73d40959', 'f6d0822c-35cf-4e0c-bb0f-8417dfc62119', '155', 'BNB', 1, 1248.660000, '2025-10-09 20:29:08', '2025-10-09 20:29:08'),
('9cbe34d0-90e3-4370-8066-12ad1244332d', '7fa76404-ca9b-498d-9602-70f45963e647', '30', 'POL', 175, 0.236900, '2025-10-09 01:08:35', '2025-10-09 01:08:35'),
('b0bd85dd-edce-40c5-8ecb-2300b93ddfdb', 'fe18a24b-444c-4a40-9f62-8ff6eee866e2', '30', 'POL', 2, 0.238300, '2025-10-09 21:07:08', '2025-10-09 21:07:08'),
('bbc316df-acc2-4b1d-8866-ef6b8a74ffc1', '17d4d76b-0b9a-48af-98c0-90dd56ba403b', '31', 'Vàng SJC', 1, 3982.460000, '2025-10-09 20:27:39', '2025-10-09 20:27:39'),
('bec7d336-bf03-4e88-8289-16cb01906502', 'b8def3a4-b76f-4d3f-a87e-81782297ab87', '156', '0.01', 1, 4052.340000, '2025-10-09 01:26:27', '2025-10-09 01:26:27'),
('c4d1d26a-03eb-431b-9800-87deb5cf3932', 'e3a23308-147c-48c0-9dda-cf2831bce051', '155', 'BNB', 1, 1314.300000, '2025-10-09 00:42:43', '2025-10-09 00:42:43'),
('c8928b5c-d073-4943-bfe6-fd8a7b129505', '3d903a94-70f1-4d49-9eef-b697f6c92a88', '155', 'BNB', 1, 1250.410000, '2025-10-09 20:02:58', '2025-10-09 20:02:58'),
('d2f8f042-5f36-4674-b4a3-c832e008ecc3', '5324781f-3478-4d67-b03c-40d7a08cea7a', '31', 'Vàng PNJ', 1, 3982.460000, '2025-10-09 20:23:53', '2025-10-09 20:23:53'),
('dbca31b5-1dbc-49cf-852d-76e16a6d9ee1', '5d17caa2-f2fc-448c-8fa7-a05d865b174d', '155', 'BNB', 1, 1248.660000, '2025-10-09 20:25:19', '2025-10-09 20:25:19'),
('dc257006-2708-46be-9c06-df3ded871927', '25eebef4-233b-41fb-8839-6dc889e12c38', '155', 'BNB', 1, 1248.660000, '2025-10-09 20:24:39', '2025-10-09 20:24:39'),
('deff944c-7557-412c-8534-982dfcb18698', '7fa76404-ca9b-498d-9602-70f45963e647', '28', 'SOL', 0, 224.520000, '2025-10-09 01:08:35', '2025-10-09 01:08:35'),
('ed49b113-0d86-4fc9-a118-0c8e2e5e7656', '2411f9d1-d45c-44e8-80b1-364b2c7cfada', '155', 'BNB', 1, 1309.720000, '2025-10-09 01:01:42', '2025-10-09 01:01:42'),
('f42d59de-2fbb-40a2-86c9-2ddb38ef0531', '980c0683-dbdc-48d5-849e-95292aff8e10', '155', 'BNB', 1, 1250.410000, '2025-10-09 20:09:14', '2025-10-09 20:09:14'),
('f63cb83f-803b-49eb-aec8-53063b9e028c', '17d4d76b-0b9a-48af-98c0-90dd56ba403b', '31', 'Vàng PNJ', 1, 3982.460000, '2025-10-09 20:27:39', '2025-10-09 20:27:39'),
('fd224e76-5c9b-4987-8eba-7a93bf31b7bc', '915abfba-58f4-470c-8d51-325a522b9051', '155', 'BNB', 1, 1300.170000, '2025-10-08 22:45:24', '2025-10-08 22:45:24'),
('fefb2db6-01ad-42ce-a985-6b4432261fc7', '9d77c636-5873-4be0-bf35-4f593d2361ff', '156', '1', 1, 3997.340000, '2025-10-09 18:28:20', '2025-10-09 18:28:20');

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
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` decimal(12,6) NOT NULL,
  `quantity` int DEFAULT NULL,
  `image` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `size` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `category` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_best_seller` tinyint(1) NOT NULL DEFAULT '0',
  `product_type` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `price`, `quantity`, `image`, `size`, `category`, `is_best_seller`, `product_type`, `created_at`, `updated_at`) VALUES
(1, 'Football Jersey', 6.000000, NULL, '[\"\\/storage\\/images\\/1e4600b5-984d-4eba-89a1-23820d5df8b2_1759851111.png\"]', '[\"S\",\"M\",\"XL\",\"XXL\"]', 'men', 0, NULL, '2025-10-07 09:04:39', '2025-10-07 08:31:52'),
(2, 'Hampton Long Sleeve Shirt', 50.000000, NULL, '[\"https:\\/\\/res.cloudinary.com\\/dfyykwzsa\\/image\\/upload\\/v1753063302\\/wowbtd0tlj6tpfxvkxcg.jpg\"]', '[\"S\",\"M\",\"L\"]', 'men', 0, NULL, '2025-10-07 09:04:39', '2025-10-07 07:42:19'),
(3, 'Cropped Fit Graphic T-Shirt', 30.000000, NULL, '[\"https://res.cloudinary.com/dfyykwzsa/image/upload/v1753063623/gnttim2dl0vxljmpejlu.jpg\", \"https://res.cloudinary.com/dfyykwzsa/image/upload/v1753063623/fwtx0axdz5iowwgarff7.jpg\"]', '[\"S\", \"M\", \"XL\", \"XXL\", \"L\"]', 'men', 0, NULL, '2025-10-07 09:04:39', '2025-10-07 09:04:47'),
(4, 'Easy Short', 10.000000, NULL, '[\"https://res.cloudinary.com/dfyykwzsa/image/upload/v1753063667/qudc6bwwlvnzfsgmx2ah.jpg\", \"https://res.cloudinary.com/dfyykwzsa/image/upload/v1753063667/ocu1bqsqtipqrk1dt61i.jpg\"]', '[\"S\", \"M\", \"L\"]', 'men', 0, NULL, '2025-10-07 09:04:39', '2025-10-07 09:04:47'),
(5, 'Alina Shirred Halter Top', 35.000000, NULL, '[\"https://res.cloudinary.com/dfyykwzsa/image/upload/v1753063762/ggjerydhpsewxvm8kbyd.jpg\", \"https://res.cloudinary.com/dfyykwzsa/image/upload/v1753063762/jqoik9izii3hv5j2z2b4.jpg\"]', '[\"S\", \"M\", \"L\", \"XL\"]', 'women', 1, NULL, '2025-10-07 09:04:39', '2025-10-07 09:04:47'),
(6, 'Mikki Drop Hem Mini Dress', 60.000000, NULL, '[\"https://res.cloudinary.com/dfyykwzsa/image/upload/v1753063815/hbrtbquoemjpaghclydx.jpg\", \"https://res.cloudinary.com/dfyykwzsa/image/upload/v1753063815/hk4ofjpoxe8ocrfvagkz.jpg\"]', '[\"S\", \"M\", \"L\", \"XL\"]', 'women', 1, NULL, '2025-10-07 09:04:39', '2025-10-07 09:04:47'),
(7, 'Haven Wide Leg Pant', 30.000000, NULL, '[\"https://res.cloudinary.com/dfyykwzsa/image/upload/v1753063885/cnffks7xikrhgjan9zil.jpg\", \"https://res.cloudinary.com/dfyykwzsa/image/upload/v1753063885/e6xpjggzveoxjospfxos.jpg\"]', '[\"S\", \"M\", \"L\", \"XL\"]', 'women', 0, NULL, '2025-10-07 09:04:39', '2025-10-07 09:04:47'),
(8, 'Kaia Faux Leather Bomber', 109.000000, NULL, '[\"https://res.cloudinary.com/dfyykwzsa/image/upload/v1753064020/zc0xgahhdyl4jttwci2e.jpg\", \"https://res.cloudinary.com/dfyykwzsa/image/upload/v1753064020/mwzlfoxgfofmncknwecv.jpg\"]', '[\"S\", \"M\", \"L\", \"XL\", \"XXL\"]', 'women', 1, NULL, '2025-10-07 09:04:39', '2025-10-07 09:04:47'),
(9, 'Sammy Oversize Hoodie', 34.000000, NULL, '[\"https://res.cloudinary.com/dfyykwzsa/image/upload/v1753064102/ri8zbzu6eq3z2ajkrwff.jpg\"]', '[\"S\", \"M\", \"L\"]', 'kids', 0, NULL, '2025-10-07 09:04:39', '2025-10-07 09:04:47'),
(10, 'Sammy Oversize Hoodie 2', 34.000000, NULL, '[\"https://res.cloudinary.com/dfyykwzsa/image/upload/v1753064102/ri8zbzu6eq3z2ajkrwff.jpg\"]', '[\"S\", \"M\", \"L\"]', 'kids', 0, NULL, '2025-10-07 09:04:39', '2025-10-07 09:04:47'),
(11, 'Sammy Oversize Hoodie 3', 34.000000, NULL, '[\"https://res.cloudinary.com/dfyykwzsa/image/upload/v1753064102/ri8zbzu6eq3z2ajkrwff.jpg\"]', '[\"S\", \"M\", \"L\"]', 'kids', 0, NULL, '2025-10-07 09:04:39', '2025-10-07 09:04:47'),
(28, 'SOL', 202.610000, 1, '[\"\\/storage\\/images\\/c843dde0-73ed-48b6-af7f-da2329530f3e_1759975080.png\"]', '[\"SOL\"]', '', 1, 'sol', '2025-10-08 08:21:51', '2025-10-27 09:40:18'),
(30, 'POL', 0.203000, 1, '[\"\\/storage\\/images\\/bb15b380-9615-488a-ac88-aca38ba4e2e7_1759943241.jpg\"]', '[\"POL\"]', 'crypto', 1, 'pol', '2025-10-08 10:07:22', '2025-10-27 09:40:18'),
(31, 'Vàng SJC', 3988.630000, 1, '[\"\\/storage\\/images\\/ae14c8d7-d2be-4344-9f47-cf73a51620a7_1759976340.webp\"]', '[\"V\\u00e0ng SJC\",\"V\\u00e0ng PNJ\"]', 'gold/silver', 1, 'paxg', '2025-10-08 19:19:01', '2025-10-27 09:11:23'),
(155, 'BNB', 1151.280000, 1, '[\"\\/storage\\/images\\/6f478042-b50d-4c10-bc14-9fd8f35b67d2_1759988654.png\"]', '[\"BNB\"]', 'crypto', 0, 'bnb', '2025-10-08 22:44:15', '2025-10-27 09:40:18'),
(156, 'XAU (Token)', 3988.630000, 1, '[\"\\/storage\\/images\\/5a155d5d-e641-4a3e-b182-d3d6b249ab55_1759998027.jpg\"]', '[\"0.01\",\"0.05\",\"0.1\",\"0.5\",\"1\"]', 'gold/silver', 0, 'paxg', '2025-10-09 01:20:28', '2025-10-27 09:11:23'),
(157, '1 SUI', 2.649000, 1, '[\"\\/storage\\/images\\/c45ce6ab-afb8-4154-9c4d-3a3221eaf48e_1761581764.jpg\"]', '[\"SUI\"]', '', 0, 'sui', '2025-10-27 09:16:06', '2025-10-27 09:40:18'),
(158, 'SHIB', 0.000011, 1, '[\"\\/storage\\/images\\/7c00320c-9b91-4cc4-bbd7-b43e06823e42_1761582742.jpg\"]', '[\"1\"]', '', 0, 'shib', '2025-10-27 09:32:24', '2025-10-27 09:59:28');

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
('6b5b50dd-acbc-4373-b691-86f69d11b87a', 'User49732', 'User26725', NULL, NULL, 'anhvu149@gmail.com', NULL, '$2y$12$Tbf7Su8B1g40b4ADpTJDQOg3GW0AG6zcOGUTxfWpBKCSB88jUAwBC', 0.00000, 0, NULL, '2025-10-09 01:07:25', '2025-10-09 01:07:25'),
('8e8fea86-fe33-4910-8d28-6ef614286d25', 'User82210', 'Đạt Mg', '1234567890', 'Nha Trang', 'tiendatmagic8@yopmail.com', NULL, '$2y$12$I/UGVtopHukSw7BHdk1AbOgK/70SfTarknv1bGLRb/vcZhzoO3iby', 0.00000, 1, NULL, '2025-08-23 13:00:57', '2025-09-28 01:44:54'),
('9b3e4d1c-91c1-440c-bb00-a76e60f7446b', 'User80107', 'User48096', NULL, NULL, 'doandangkhoa1492004@gmail.com', NULL, '$2y$12$d0aspH6l5/cgkp1utTOZFOP7h.h8F6PatdnVbnfOgO6QZQxQIpkhy', 0.00000, 1, NULL, '2025-10-07 10:06:45', '2025-10-07 10:06:45'),
('e0f8acd1-b5a9-4516-a12c-739e4728b0fe', 'User73407', 'User63361', '123456789876', '123', 'tiendatmagic9@yopmail.com', NULL, '$2y$12$kDcqueX0nC0GmKvezB4xnePsAL0UFq5ejyfjOIyhg4wKW5TKF2OVm', 0.00000, 0, NULL, '2025-08-28 10:43:15', '2025-08-29 04:56:37'),
('f1d8cd64-8f3e-4760-9d6b-9da5d1064ef7', 'User70153', 'User96908', NULL, NULL, 'viet99cm@gmail.com', NULL, '$2y$12$sgObxy73pAFJRgVE887f3OMSPrSkGSWUtwDzwckrUuRYZ4GJ.A2Z2', 0.00000, 0, NULL, '2025-08-29 06:55:43', '2025-08-29 06:55:43'),
('f8734e0e-1a12-4faf-b78d-aa9c17030cc4', 'User23288', 'User57389', NULL, NULL, 'nguyenanhvu9899@gmai.com', NULL, '$2y$12$Pq/Q/iJrbGYWttvagu0dWO2qNu4/R9MU5QcTZPGqPWn0MTd26Mp5e', 0.00000, 0, NULL, '2025-10-09 00:50:25', '2025-10-09 00:50:25');

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
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=159;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
