-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: localhost
-- Thời gian đã tạo: Th10 10, 2025 lúc 05:31 AM
-- Phiên bản máy phục vụ: 10.6.20-MariaDB-cll-lve-log
-- Phiên bản PHP: 8.2.21

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `xomdoxythosting_dangkhoashop`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `failed_jobs`
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
-- Cấu trúc bảng cho bảng `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2014_10_12_000000_create_users_table', 1),
(2, '2014_10_12_100000_create_password_resets_table', 1),
(3, '2019_08_19_000000_create_failed_jobs_table', 1),
(4, '2019_12_14_000001_create_personal_access_tokens_table', 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `orders`
--

CREATE TABLE `orders` (
  `id` char(36) NOT NULL,
  `order_code` char(8) DEFAULT NULL,
  `user_id` char(36) NOT NULL,
  `payment` varchar(10) NOT NULL,
  `txhash` varchar(70) DEFAULT NULL,
  `status` varchar(10) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `note` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `orders`
--

INSERT INTO `orders` (`id`, `order_code`, `user_id`, `payment`, `txhash`, `status`, `name`, `email`, `phone`, `address`, `note`, `created_at`, `updated_at`) VALUES
('043adb93-c0d6-4ada-a46d-932bba2283ea', '60137794', '9b3e4d1c-91c1-440c-bb00-a76e60f7446b', 'cash', NULL, 'pending', 'User48096', 'doandangkhoa1492004@gmail.com', '0389986712', 'yen minh yen minh', 'xxx', '2025-10-09 20:38:17', '2025-10-09 20:38:17'),
('0f2f355e-5033-424f-860c-05d936c345a8', '79974247', '8e8fea86-fe33-4910-8d28-6ef614286d25', 'cash', NULL, 'completed', 'Đạt Mg', 'tiendatmagic8@yopmail.com', '1234567890', 'Nha Trang', 'Giao đi nha', '2025-10-09 20:50:58', '2025-10-09 20:51:22'),
('12fd6b73-b017-4b7a-a585-dcbbc847cd46', '96845805', '8e8fea86-fe33-4910-8d28-6ef614286d25', 'cash', NULL, 'pending', 'Đạt Mg', 'tiendatmagic8@yopmail.com', '1234567890', 'Nha Trang', NULL, '2025-10-09 20:53:49', '2025-10-09 20:53:49'),
('1f8c49c5-d8c5-4f56-b3ae-231f7da9a102', '68848985', '8e8fea86-fe33-4910-8d28-6ef614286d25', 'cash', NULL, 'pending', 'Đạt Mg', 'tiendatmagic8@yopmail.com', '1234567890', 'Hà Nội', NULL, '2025-10-09 20:56:21', '2025-10-09 20:56:21'),
('2073f855-0956-45c4-b1af-6ba673182a7f', '17125650', '9b3e4d1c-91c1-440c-bb00-a76e60f7446b', 'cash', NULL, 'pending', 'User48096', 'doandangkhoa1492004@gmail.com', '0879288566', 'Da Nang', NULL, '2025-10-09 07:10:54', '2025-10-09 07:10:54'),
('2411f9d1-d45c-44e8-80b1-364b2c7cfada', '55267082', 'f8734e0e-1a12-4faf-b78d-aa9c17030cc4', 'cash', NULL, 'completed', 'User57389', 'nguyenanhvu9899@gmai.com', '0889857843', 'Hà Nội', NULL, '2025-10-09 01:01:42', '2025-10-09 01:04:14'),
('3e8278f8-dc12-4475-b0b4-bf7d0a25d10a', '71497599', '9b3e4d1c-91c1-440c-bb00-a76e60f7446b', 'cash', NULL, 'pending', 'User48096', 'doandangkhoa1492004@gmail.com', '123', '123', NULL, '2025-10-09 18:42:12', '2025-10-09 18:42:12'),
('3ec3520e-66f8-46e6-908f-63bfada35cab', '36053464', '9b3e4d1c-91c1-440c-bb00-a76e60f7446b', 'cash', NULL, 'pending', 'User48096', 'doandangkhoa1492004@gmail.com', '0389986712', 'yen minh yen minh', 'ccc', '2025-10-09 21:23:24', '2025-10-09 21:23:24'),
('4994e417-f861-46f2-971b-b0837049f3f9', '13264963', '6b5b50dd-acbc-4373-b691-86f69d11b87a', 'cash', NULL, 'completed', 'User26725', 'anhvu149@gmail.com', '0879288864', 'Da Nang', '0x5c452de543ba531c8d38bbcfa582a982a6374b95cea30d6d909ef3584908d376', '2025-10-09 01:13:12', '2025-10-09 01:13:59'),
('72217f2e-ddd9-4126-b33c-4ce9954a34ba', '67761652', '9b3e4d1c-91c1-440c-bb00-a76e60f7446b', 'cash', NULL, 'pending', 'User48096', 'nguyenanhvu9899@gmail.com', '0888836644', 'Ha Noi', NULL, '2025-10-09 20:57:41', '2025-10-09 20:57:41'),
('7fa76404-ca9b-498d-9602-70f45963e647', '43689166', '6b5b50dd-acbc-4373-b691-86f69d11b87a', 'cash', NULL, 'completed', 'User26725', 'anhvu149@gmail.com', '0879288478', 'Ho Chi Minh', '0x5e4FC833925D66918Af48F1deb0d07f12229bF8b', '2025-10-09 01:08:35', '2025-10-09 01:13:41'),
('915abfba-58f4-470c-8d51-325a522b9051', '41559750', '8e8fea86-fe33-4910-8d28-6ef614286d25', 'cash', NULL, 'completed', 'Đạt Mg', 'tiendatmagic8@yopmail.com', '1234567890', 'Nha Trang', NULL, '2025-10-08 22:45:24', '2025-10-08 22:51:17'),
('9ddeafcc-3b43-4de7-bbd0-08d490001e0d', '39297056', '6b5b50dd-acbc-4373-b691-86f69d11b87a', 'cash', NULL, 'completed', 'User26725', 'anhvu149@gmail.com', '0968237410', 'Phường Hàng Bạc, Quận Hoàn Kiếm, Hà Nội', NULL, '2025-10-09 01:12:06', '2025-10-09 01:13:52'),
('a0a24d37-ef6e-4c16-a127-5e71e9c9a401', '33539644', '8e8fea86-fe33-4910-8d28-6ef614286d25', 'cash', NULL, 'pending', 'Đạt Mg', 'tiendatmagic8@yopmail.com', '1234567890', 'Nha Trang', '0x1AD11e0e96797a14336Bf474676EB0A332055555', '2025-10-09 00:44:24', '2025-10-09 00:44:24'),
('b4b63ec5-7735-418d-ba80-62484bace36b', '62247550', '9b3e4d1c-91c1-440c-bb00-a76e60f7446b', 'cash', NULL, 'pending', 'User48096', 'doandangkhoa1492004@gmail.com', ',', '.', NULL, '2025-10-09 18:44:24', '2025-10-09 18:44:24'),
('b8def3a4-b76f-4d3f-a87e-81782297ab87', '98536480', '9b3e4d1c-91c1-440c-bb00-a76e60f7446b', 'cash', NULL, 'completed', 'User48096', 'doandangkhoa1492004@gmail.com', '0879288478', '29X6+76J, Ta An Khuong Dong-Tan Thuan, Tn Thun, m Di, C Mau, Vit Nam', '0x421f2f731b3a1C30c3893BbF97F0A337FB921815', '2025-10-09 01:26:27', '2025-10-09 01:33:24'),
('ddfa6524-81ae-429b-8f81-a5528075dad9', '31764317', '8e8fea86-fe33-4910-8d28-6ef614286d25', 'cash', NULL, 'completed', 'Đạt Mg', 'tiendatmagic8@yopmail.com', '1234567890', 'Nha Trang', NULL, '2025-05-09 00:29:49', '2025-10-09 00:30:05'),
('e3a23308-147c-48c0-9dda-cf2831bce051', '89834020', '8e8fea86-fe33-4910-8d28-6ef614286d25', 'cash', NULL, 'pending', 'Đạt Mg', 'tiendatmagic8@yopmail.com', '1234567890', 'Nha Trang', NULL, '2025-10-09 00:42:43', '2025-10-09 00:42:43');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `order_items`
--

CREATE TABLE `order_items` (
  `id` char(36) NOT NULL,
  `order_id` char(36) NOT NULL,
  `product_id` char(36) NOT NULL,
  `size` varchar(255) NOT NULL,
  `quantity` bigint(20) NOT NULL,
  `price` decimal(10,4) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `size`, `quantity`, `price`, `created_at`, `updated_at`) VALUES
('1587f5df-0351-4389-ae9d-a6f87d782425', '72217f2e-ddd9-4126-b33c-4ce9954a34ba', '30', 'POL', 100, 0.2393, '2025-10-09 20:57:41', '2025-10-09 20:57:41'),
('176599fc-cce5-4072-ad85-bac1c553c8ee', 'a0a24d37-ef6e-4c16-a127-5e71e9c9a401', '155', 'BNB', 1, 1314.3000, '2025-10-09 00:44:24', '2025-10-09 00:44:24'),
('1a3c6517-f154-498b-b467-87f7e130c94c', 'ddfa6524-81ae-429b-8f81-a5528075dad9', '30', 'POL', 1, 0.2382, '2025-10-09 00:29:50', '2025-10-09 00:29:50'),
('1e0ce07e-c50a-441b-bbc3-ceff062ce550', '9ddeafcc-3b43-4de7-bbd0-08d490001e0d', '31', 'Vàng SJC', 1, 4047.3300, '2025-10-09 01:12:06', '2025-10-09 01:12:06'),
('3f2bdcde-f6c8-474e-b6e2-5ecafd26fad7', '043adb93-c0d6-4ada-a46d-932bba2283ea', '155', 'BNB', 0, 1252.2500, '2025-10-09 20:38:17', '2025-10-09 20:38:17'),
('424e8307-a6ce-40c1-93e7-2ff5f12464cc', '4994e417-f861-46f2-971b-b0837049f3f9', '28', 'SOL', 2, 224.5200, '2025-10-09 01:13:12', '2025-10-09 01:13:12'),
('544f3dc7-ba1a-40b7-b571-9b63eb91035d', '2073f855-0956-45c4-b1af-6ba673182a7f', '31', 'Vàng SJC', 1, 4046.1900, '2025-10-09 07:10:54', '2025-10-09 07:10:54'),
('6f6252c3-2792-45b4-b1fe-46643c1a31a0', '12fd6b73-b017-4b7a-a585-dcbbc847cd46', '30', 'POL', 3, 0.2393, '2025-10-09 20:53:49', '2025-10-09 20:53:49'),
('7735cb43-ebf8-4d96-98a0-dd740e1ffaae', 'b4b63ec5-7735-418d-ba80-62484bace36b', '156', '0.01', 1, 3987.6000, '2025-10-09 18:44:24', '2025-10-09 18:44:24'),
('8d169f1a-79fe-42c6-afac-aa05cadb10ab', '3ec3520e-66f8-46e6-908f-63bfada35cab', '156', '0.01', 1, 3964.0000, '2025-10-09 21:23:24', '2025-10-09 21:23:24'),
('96020648-7fc2-40cb-a7bb-0431bce6a91e', 'ddfa6524-81ae-429b-8f81-a5528075dad9', '28', 'SOL', 1, 224.9500, '2025-10-09 00:29:49', '2025-10-09 00:29:49'),
('9cbe34d0-90e3-4370-8066-12ad1244332d', '7fa76404-ca9b-498d-9602-70f45963e647', '30', 'POL', 175, 0.2369, '2025-10-09 01:08:35', '2025-10-09 01:08:35'),
('a07a1248-d43a-4e09-9c7e-ab57296422e0', '3e8278f8-dc12-4475-b0b4-bf7d0a25d10a', '31', 'Vàng SJC', 1, 3987.6000, '2025-10-09 18:42:12', '2025-10-09 18:42:12'),
('acdbca65-28a9-46da-a19e-df1d6f1d56e8', '1f8c49c5-d8c5-4f56-b3ae-231f7da9a102', '31', 'Vàng PNJ', 1, 3972.8000, '2025-10-09 20:56:21', '2025-10-09 20:56:21'),
('bec7d336-bf03-4e88-8289-16cb01906502', 'b8def3a4-b76f-4d3f-a87e-81782297ab87', '156', '0.01', 1, 4052.3400, '2025-10-09 01:26:27', '2025-10-09 01:26:27'),
('c4d1d26a-03eb-431b-9800-87deb5cf3932', 'e3a23308-147c-48c0-9dda-cf2831bce051', '155', 'BNB', 1, 1314.3000, '2025-10-09 00:42:43', '2025-10-09 00:42:43'),
('c9d9ab49-3d97-4ed9-87c8-d8ac85793390', '0f2f355e-5033-424f-860c-05d936c345a8', '31', 'Vàng PNJ', 1, 3972.8000, '2025-10-09 20:50:58', '2025-10-09 20:50:58'),
('deff944c-7557-412c-8534-982dfcb18698', '7fa76404-ca9b-498d-9602-70f45963e647', '28', 'SOL', 1, 224.5200, '2025-10-09 01:08:35', '2025-10-09 01:08:35'),
('ed49b113-0d86-4fc9-a118-0c8e2e5e7656', '2411f9d1-d45c-44e8-80b1-364b2c7cfada', '155', 'BNB', 1, 1309.7200, '2025-10-09 01:01:42', '2025-10-09 01:01:42'),
('fd224e76-5c9b-4987-8eba-7a93bf31b7bc', '915abfba-58f4-470c-8d51-325a522b9051', '155', 'BNB', 1, 1300.1700, '2025-10-08 22:45:24', '2025-10-08 22:45:24');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `password_resets`
--

CREATE TABLE `password_resets` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `personal_access_tokens`
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
-- Cấu trúc bảng cho bảng `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` decimal(10,4) NOT NULL,
  `quantity` int(11) DEFAULT NULL,
  `image` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `size` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `category` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_best_seller` tinyint(1) NOT NULL DEFAULT 0,
  `product_type` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `products`
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
(28, 'SOL', 221.0500, 1, '[\"\\/storage\\/images\\/c843dde0-73ed-48b6-af7f-da2329530f3e_1759975080.png\"]', '[\"SOL\"]', '', 1, 'sol', '2025-10-08 08:21:51', '2025-10-09 21:50:49'),
(30, 'POL', 0.2382, 1, '[\"\\/storage\\/images\\/bb15b380-9615-488a-ac88-aca38ba4e2e7_1759943241.jpg\"]', '[\"POL\"]', 'crypto', 1, 'pol', '2025-10-08 10:07:22', '2025-10-09 21:50:49'),
(31, 'Vàng SJC', 3973.9300, 1, '[\"\\/storage\\/images\\/ae14c8d7-d2be-4344-9f47-cf73a51620a7_1759976340.webp\"]', '[\"V\\u00e0ng SJC\",\"V\\u00e0ng PNJ\"]', 'gold/silver', 1, 'paxg', '2025-10-08 19:19:01', '2025-10-09 21:50:49'),
(155, 'BNB', 1265.8500, 1, '[\"\\/storage\\/images\\/6f478042-b50d-4c10-bc14-9fd8f35b67d2_1759988654.png\"]', '[\"BNB\"]', 'crypto', 0, 'bnb', '2025-10-08 22:44:15', '2025-10-09 21:50:49'),
(156, 'XAU (Token)', 3973.9300, 1, '[\"\\/storage\\/images\\/5a155d5d-e641-4a3e-b182-d3d6b249ab55_1759998027.jpg\"]', '[\"0.01\",\"0.05\",\"0.1\",\"0.5\",\"1\"]', 'gold/silver', 0, 'paxg', '2025-10-09 01:20:28', '2025-10-09 21:50:49');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `users`
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
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`id`, `name`, `full_name`, `phone`, `address`, `email`, `email_verified_at`, `password`, `amount`, `is_admin`, `remember_token`, `created_at`, `updated_at`) VALUES
('6b5b50dd-acbc-4373-b691-86f69d11b87a', 'User49732', 'User26725', NULL, NULL, 'anhvu149@gmail.com', NULL, '$2y$12$Tbf7Su8B1g40b4ADpTJDQOg3GW0AG6zcOGUTxfWpBKCSB88jUAwBC', 0.00000, 0, NULL, '2025-10-09 01:07:25', '2025-10-09 01:07:25'),
('8e8fea86-fe33-4910-8d28-6ef614286d25', 'User82210', 'Đạt Mg', '1234567890', 'Hà Nội', 'tiendatmagic8@yopmail.com', NULL, '$2y$12$I/UGVtopHukSw7BHdk1AbOgK/70SfTarknv1bGLRb/vcZhzoO3iby', 0.00000, 1, NULL, '2025-08-23 13:00:57', '2025-10-09 20:55:57'),
('9b3e4d1c-91c1-440c-bb00-a76e60f7446b', 'User80107', 'User48096', NULL, NULL, 'doandangkhoa1492004@gmail.com', NULL, '$2y$12$d0aspH6l5/cgkp1utTOZFOP7h.h8F6PatdnVbnfOgO6QZQxQIpkhy', 0.00000, 1, NULL, '2025-10-07 10:06:45', '2025-10-07 10:06:45'),
('e0f8acd1-b5a9-4516-a12c-739e4728b0fe', 'User73407', 'User63361', '123456789876', '123', 'tiendatmagic9@yopmail.com', NULL, '$2y$12$kDcqueX0nC0GmKvezB4xnePsAL0UFq5ejyfjOIyhg4wKW5TKF2OVm', 0.00000, 0, NULL, '2025-08-28 10:43:15', '2025-08-29 04:56:37'),
('f1d8cd64-8f3e-4760-9d6b-9da5d1064ef7', 'User70153', 'User96908', NULL, NULL, 'viet99cm@gmail.com', NULL, '$2y$12$sgObxy73pAFJRgVE887f3OMSPrSkGSWUtwDzwckrUuRYZ4GJ.A2Z2', 0.00000, 0, NULL, '2025-08-29 06:55:43', '2025-08-29 06:55:43'),
('f8734e0e-1a12-4faf-b78d-aa9c17030cc4', 'User23288', 'User57389', NULL, NULL, 'nguyenanhvu9899@gmai.com', NULL, '$2y$12$Pq/Q/iJrbGYWttvagu0dWO2qNu4/R9MU5QcTZPGqPWn0MTd26Mp5e', 0.00000, 0, NULL, '2025-10-09 00:50:25', '2025-10-09 00:50:25');

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Chỉ mục cho bảng `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `password_resets`
--
ALTER TABLE `password_resets`
  ADD KEY `password_resets_email_index` (`email`);

--
-- Chỉ mục cho bảng `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Chỉ mục cho bảng `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT cho bảng `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=157;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
