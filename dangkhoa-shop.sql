-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Máy chủ: localhost:3306
-- Thời gian đã tạo: Th1 30, 2026 lúc 09:35 AM
-- Phiên bản máy phục vụ: 8.0.30
-- Phiên bản PHP: 8.3.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `dangkhoa-shop`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `admin_wallet_settings`
--

CREATE TABLE `admin_wallet_settings` (
  `id` bigint UNSIGNED NOT NULL,
  `chain_id` bigint UNSIGNED NOT NULL DEFAULT '56',
  `rpc_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contract_address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `from_address` text COLLATE utf8mb4_unicode_ci,
  `private_key` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `admin_wallet_settings`
--

INSERT INTO `admin_wallet_settings` (`id`, `chain_id`, `rpc_url`, `contract_address`, `from_address`, `private_key`, `created_at`, `updated_at`) VALUES
(1, 56, 'https://bsc-mainnet.infura.io', '0x9b4c29ffed9fcc96e15965434c8c0f156a7b7b2d', 'eyJpdiI6IlM5RS9RVWp4TjVjb28rYzBYMmExVXc9PSIsInZhbHVlIjoiUExLYi92VERMaUVXQ1Y1ckYxY0kzTm5rd3ZQSCtZYjBZaXNRZHhMeHl2NENHdW5OYllvVVpGRUhtYVdsSXF1UiIsIm1hYyI6ImJhZWJlM2EwNTA3YjQxN2Q3NzZhYzAyZjQ1NzM4NjAxOTJmYWI4NmE0MDhkNmFkYWQ4NzJhNjJlZTVmMDcwNzUiLCJ0YWciOiIifQ==', 'eyJpdiI6IlNyL0VqYmpuZEFZREl2Nnl1QmZZY3c9PSIsInZhbHVlIjoiMWRLTmpQdEZudjB0NVBtYUdLS0ZMb2NIbCtuYmk3WFdndHl2bkx0c3d4dGhmUDFZNlhjb1U2aGtuNnRUNTIxMkJjYXZCbXlGMDZoN3ZrQ1ZMZ3NWK0c4NGVDWFhIZGQ2bnFWd1A5aHo2bXM9IiwibWFjIjoiMTU2MDU2NzBkNjA2ZjVhMzRmOTBhOGEyYTVmM2E0MmE1NDE1ODc4MzcyYTUzZmMwZjNhYzNiM2I3MjU2YWUzYSIsInRhZyI6IiJ9', '2026-01-04 10:29:18', '2026-01-04 10:29:18');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `customizations`
--

CREATE TABLE `customizations` (
  `id` bigint UNSIGNED NOT NULL,
  `slides` json DEFAULT NULL,
  `collections` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `customizations`
--

INSERT INTO `customizations` (`id`, `slides`, `collections`, `created_at`, `updated_at`) VALUES
(1, '[\"/storage/images/d5acb21d-15ad-4ecd-8456-fe5e0aedc04b_1769671858.jpg\", \"/storage/images/c0a1c1a5-9ca1-4d8f-9b6d-9fabe52d2afd_1769671863.jpg\"]', '[]', '2026-01-29 00:31:16', '2026-01-29 00:31:16'),
(2, '[\"/storage/images/d5acb21d-15ad-4ecd-8456-fe5e0aedc04b_1769671858.jpg\", \"/storage/images/c0a1c1a5-9ca1-4d8f-9b6d-9fabe52d2afd_1769671863.jpg\"]', '[]', '2026-01-29 00:31:20', '2026-01-29 00:31:20'),
(3, '[\"/storage/images/d5acb21d-15ad-4ecd-8456-fe5e0aedc04b_1769671858.jpg\", \"/storage/images/c0a1c1a5-9ca1-4d8f-9b6d-9fabe52d2afd_1769671863.jpg\"]', '[]', '2026-01-29 00:34:04', '2026-01-29 00:34:04'),
(4, '[\"/storage/images/d5acb21d-15ad-4ecd-8456-fe5e0aedc04b_1769671858.jpg\", \"/storage/images/c0a1c1a5-9ca1-4d8f-9b6d-9fabe52d2afd_1769671863.jpg\", \"/storage/images/0a1e0be9-63ee-471c-86d8-75ae181780ad_1769672043.jpg\"]', '[{\"id\": \"gold/silver\", \"name\": \"gold/silver\", \"image\": \"/storage/images/978123fe-f124-4fc1-a41b-769456108493_1761573024.jpg\"}, {\"id\": \"crypto\", \"name\": \"crypto\", \"image\": \"/storage/images/4359b6c5-8cc1-48ef-8a90-ac217058b27d_1761571828.png\"}]', '2026-01-29 00:34:33', '2026-01-29 00:34:33'),
(5, '[\"/storage/images/d5acb21d-15ad-4ecd-8456-fe5e0aedc04b_1769671858.jpg\", \"/storage/images/c0a1c1a5-9ca1-4d8f-9b6d-9fabe52d2afd_1769671863.jpg\", \"/storage/images/0a1e0be9-63ee-471c-86d8-75ae181780ad_1769672043.jpg\"]', '[{\"id\": \"gold/silver\", \"name\": \"gold/silver\", \"image\": \"/storage/images/978123fe-f124-4fc1-a41b-769456108493_1761573024.jpg\"}, {\"id\": \"crypto\", \"name\": \"crypto\", \"image\": \"/storage/images/4359b6c5-8cc1-48ef-8a90-ac217058b27d_1761571828.png\"}]', '2026-01-29 00:50:27', '2026-01-29 00:50:27'),
(6, '[\"/storage/images/d5acb21d-15ad-4ecd-8456-fe5e0aedc04b_1769671858.jpg\", \"/storage/images/0a1e0be9-63ee-471c-86d8-75ae181780ad_1769672043.jpg\", \"/storage/images/c0a1c1a5-9ca1-4d8f-9b6d-9fabe52d2afd_1769671863.jpg\"]', '[{\"id\": \"gold/silver\", \"name\": \"gold/silver\", \"image\": \"/storage/images/978123fe-f124-4fc1-a41b-769456108493_1761573024.jpg\"}, {\"id\": \"crypto\", \"name\": \"crypto\", \"image\": \"/storage/images/4359b6c5-8cc1-48ef-8a90-ac217058b27d_1761571828.png\"}]', '2026-01-29 00:53:03', '2026-01-29 00:53:03'),
(7, '[\"/storage/images/0a1e0be9-63ee-471c-86d8-75ae181780ad_1769672043.jpg\", \"/storage/images/d5acb21d-15ad-4ecd-8456-fe5e0aedc04b_1769671858.jpg\", \"/storage/images/c0a1c1a5-9ca1-4d8f-9b6d-9fabe52d2afd_1769671863.jpg\"]', '[{\"id\": \"gold/silver\", \"name\": \"gold/silver\", \"image\": \"/storage/images/978123fe-f124-4fc1-a41b-769456108493_1761573024.jpg\"}, {\"id\": \"crypto\", \"name\": \"crypto\", \"image\": \"/storage/images/4359b6c5-8cc1-48ef-8a90-ac217058b27d_1761571828.png\"}]', '2026-01-29 00:54:48', '2026-01-29 00:54:48'),
(8, '[\"/storage/images/0a1e0be9-63ee-471c-86d8-75ae181780ad_1769672043.jpg\", \"/storage/images/d5acb21d-15ad-4ecd-8456-fe5e0aedc04b_1769671858.jpg\", \"/storage/images/c0a1c1a5-9ca1-4d8f-9b6d-9fabe52d2afd_1769671863.jpg\", \"/storage/images/7c422262-e988-479e-acb7-2a50a03fc40a_1769673306.jpg\"]', '[{\"id\": \"gold/silver\", \"name\": \"gold/silver\", \"image\": \"/storage/images/978123fe-f124-4fc1-a41b-769456108493_1761573024.jpg\"}, {\"id\": \"crypto\", \"name\": \"crypto\", \"image\": \"/storage/images/4359b6c5-8cc1-48ef-8a90-ac217058b27d_1761571828.png\"}]', '2026-01-29 00:55:08', '2026-01-29 00:55:08'),
(9, '[\"/storage/images/0a1e0be9-63ee-471c-86d8-75ae181780ad_1769672043.jpg\", \"/storage/images/d5acb21d-15ad-4ecd-8456-fe5e0aedc04b_1769671858.jpg\", \"/storage/images/c0a1c1a5-9ca1-4d8f-9b6d-9fabe52d2afd_1769671863.jpg\"]', '[{\"id\": \"gold/silver\", \"name\": \"gold/silver\", \"image\": \"/storage/images/978123fe-f124-4fc1-a41b-769456108493_1761573024.jpg\"}, {\"id\": \"crypto\", \"name\": \"crypto\", \"image\": \"/storage/images/4359b6c5-8cc1-48ef-8a90-ac217058b27d_1761571828.png\"}]', '2026-01-29 01:01:13', '2026-01-29 01:01:13'),
(10, '[\"/storage/images/0a1e0be9-63ee-471c-86d8-75ae181780ad_1769672043.jpg\", \"/storage/images/d5acb21d-15ad-4ecd-8456-fe5e0aedc04b_1769671858.jpg\", \"/storage/images/c0a1c1a5-9ca1-4d8f-9b6d-9fabe52d2afd_1769671863.jpg\"]', '[{\"id\": \"crypto\", \"name\": \"crypto\", \"image\": \"/storage/images/4359b6c5-8cc1-48ef-8a90-ac217058b27d_1761571828.png\"}]', '2026-01-29 01:01:28', '2026-01-29 01:01:28'),
(11, '[\"/storage/images/0a1e0be9-63ee-471c-86d8-75ae181780ad_1769672043.jpg\", \"/storage/images/d5acb21d-15ad-4ecd-8456-fe5e0aedc04b_1769671858.jpg\", \"/storage/images/c0a1c1a5-9ca1-4d8f-9b6d-9fabe52d2afd_1769671863.jpg\"]', '[{\"id\": \"crypto\", \"name\": \"crypto\", \"image\": \"/storage/images/4359b6c5-8cc1-48ef-8a90-ac217058b27d_1761571828.png\"}, {\"id\": \"men\", \"name\": \"Men\"}]', '2026-01-29 01:05:14', '2026-01-29 01:05:14'),
(12, '[\"/storage/images/0a1e0be9-63ee-471c-86d8-75ae181780ad_1769672043.jpg\", \"/storage/images/d5acb21d-15ad-4ecd-8456-fe5e0aedc04b_1769671858.jpg\", \"/storage/images/c0a1c1a5-9ca1-4d8f-9b6d-9fabe52d2afd_1769671863.jpg\"]', '[{\"id\": \"crypto\", \"name\": \"crypto\", \"image\": \"/storage/images/4359b6c5-8cc1-48ef-8a90-ac217058b27d_1761571828.png\"}, {\"id\": \"men\", \"name\": \"Men\"}]', '2026-01-29 01:05:41', '2026-01-29 01:05:41'),
(13, '[\"/storage/images/0a1e0be9-63ee-471c-86d8-75ae181780ad_1769672043.jpg\", \"/storage/images/d5acb21d-15ad-4ecd-8456-fe5e0aedc04b_1769671858.jpg\", \"/storage/images/c0a1c1a5-9ca1-4d8f-9b6d-9fabe52d2afd_1769671863.jpg\"]', '[{\"id\": \"crypto\", \"name\": \"crypto\", \"image\": \"/storage/images/4359b6c5-8cc1-48ef-8a90-ac217058b27d_1761571828.png\"}, {\"id\": \"men\", \"name\": \"Men\"}]', '2026-01-29 01:08:11', '2026-01-29 01:08:11'),
(14, '[\"/storage/images/0a1e0be9-63ee-471c-86d8-75ae181780ad_1769672043.jpg\", \"/storage/images/d5acb21d-15ad-4ecd-8456-fe5e0aedc04b_1769671858.jpg\", \"/storage/images/c0a1c1a5-9ca1-4d8f-9b6d-9fabe52d2afd_1769671863.jpg\"]', '[{\"id\": \"crypto\", \"name\": \"crypto\", \"image\": \"/storage/images/4359b6c5-8cc1-48ef-8a90-ac217058b27d_1761571828.png\"}, {\"id\": \"men\", \"name\": \"Men\"}, {\"id\": \"women\", \"name\": \"Women\"}]', '2026-01-29 01:08:20', '2026-01-29 01:08:20'),
(15, '[\"/storage/images/0a1e0be9-63ee-471c-86d8-75ae181780ad_1769672043.jpg\", \"/storage/images/d5acb21d-15ad-4ecd-8456-fe5e0aedc04b_1769671858.jpg\", \"/storage/images/c0a1c1a5-9ca1-4d8f-9b6d-9fabe52d2afd_1769671863.jpg\", \"/storage/images/abb7845a-f077-4f28-a504-8e522547cb49_1769674746.jpg\"]', '[{\"id\": \"crypto\", \"name\": \"crypto\", \"image\": \"/storage/images/4359b6c5-8cc1-48ef-8a90-ac217058b27d_1761571828.png\"}, {\"id\": \"men\", \"name\": \"Men\"}, {\"id\": \"women\", \"name\": \"Women\"}]', '2026-01-29 01:19:07', '2026-01-29 01:19:07'),
(16, '[\"/storage/images/d5acb21d-15ad-4ecd-8456-fe5e0aedc04b_1769671858.jpg\", \"/storage/images/0a1e0be9-63ee-471c-86d8-75ae181780ad_1769672043.jpg\", \"/storage/images/c0a1c1a5-9ca1-4d8f-9b6d-9fabe52d2afd_1769671863.jpg\", \"/storage/images/abb7845a-f077-4f28-a504-8e522547cb49_1769674746.jpg\"]', '[{\"id\": \"crypto\", \"name\": \"crypto\", \"image\": \"/storage/images/4359b6c5-8cc1-48ef-8a90-ac217058b27d_1761571828.png\"}, {\"id\": \"men\", \"name\": \"Men\"}, {\"id\": \"women\", \"name\": \"Women\"}]', '2026-01-29 02:06:36', '2026-01-29 02:06:36'),
(17, '[\"/storage/images/d5acb21d-15ad-4ecd-8456-fe5e0aedc04b_1769671858.jpg\", \"/storage/images/c0a1c1a5-9ca1-4d8f-9b6d-9fabe52d2afd_1769671863.jpg\", \"/storage/images/0a1e0be9-63ee-471c-86d8-75ae181780ad_1769672043.jpg\", \"/storage/images/abb7845a-f077-4f28-a504-8e522547cb49_1769674746.jpg\"]', '[{\"id\": \"crypto\", \"name\": \"crypto\", \"image\": \"/storage/images/4359b6c5-8cc1-48ef-8a90-ac217058b27d_1761571828.png\"}, {\"id\": \"men\", \"name\": \"Men\"}, {\"id\": \"women\", \"name\": \"Women\"}]', '2026-01-29 02:09:51', '2026-01-29 02:09:51');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `uuid` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2014_10_12_000000_create_users_table', 1),
(2, '2014_10_12_100000_create_password_resets_table', 1),
(3, '2019_08_19_000000_create_failed_jobs_table', 1),
(4, '2019_12_14_000001_create_personal_access_tokens_table', 1),
(5, '2014_10_12_100000_create_password_reset_tokens_table', 2),
(6, '2025_12_24_000000_add_cryptomus_fields_to_orders_table', 2),
(7, '2025_12_27_000000_add_coinbase_fields_to_orders_table', 3),
(8, '2025_12_27_000002_add_coinbase_expires_at_to_orders_table', 4),
(9, '2026_01_04_000000_create_admin_wallet_settings_table', 5),
(10, '2026_01_04_000001_add_bnb_payout_fields_to_orders_table', 5),
(11, '2026_01_05_000000_add_contract_chain_fields_to_admin_wallet_settings_table', 6),
(12, '2026_01_05_000001_add_payout_snapshot_fields_to_orders_table', 6),
(13, '2026_01_05_000002_create_token_assets_table', 6),
(14, '2026_01_05_000003_create_order_payouts_table', 6),
(15, '2026_01_29_000000_add_sepay_code_to_orders_table', 7),
(16, '2026_01_29_000001_add_sepay_code_to_orders_table', 8),
(17, '2026_01_29_000002_add_sepay_code_to_orders_table', 9),
(18, '2026_01_29_000000_create_customizations_table', 10);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `orders`
--

CREATE TABLE `orders` (
  `id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `order_code` char(8) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sepay_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payment` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `txhash` varchar(70) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `coinbase_charge_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `coinbase_hosted_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `coinbase_expires_at` timestamp NULL DEFAULT NULL,
  `status` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `paid_at` timestamp NULL DEFAULT NULL,
  `bnb_sent_at` timestamp NULL DEFAULT NULL,
  `bnb_send_txhash` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bnb_send_error` text COLLATE utf8mb4_unicode_ci,
  `payout_type` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payout_chain_id` bigint UNSIGNED DEFAULT NULL,
  `payout_asset_symbol` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payout_token_address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payout_to_address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payout_total_usd` decimal(18,6) DEFAULT NULL,
  `payout_price_usd` decimal(18,6) DEFAULT NULL,
  `payout_amount_decimal` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payout_amount_wei` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `orders`
--

INSERT INTO `orders` (`id`, `order_code`, `sepay_code`, `user_id`, `payment`, `txhash`, `coinbase_charge_id`, `coinbase_hosted_url`, `coinbase_expires_at`, `status`, `paid_at`, `bnb_sent_at`, `bnb_send_txhash`, `bnb_send_error`, `payout_type`, `payout_chain_id`, `payout_asset_symbol`, `payout_token_address`, `payout_to_address`, `payout_total_usd`, `payout_price_usd`, `payout_amount_decimal`, `payout_amount_wei`, `name`, `email`, `phone`, `address`, `note`, `created_at`, `updated_at`) VALUES
('043adb93-c0d6-4ada-a46d-932bba2283ea', '60137794', NULL, '9b3e4d1c-91c1-440c-bb00-a76e60f7446b', 'cash', NULL, NULL, NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'User48096', 'doandangkhoa1492004@gmail.com', '0389986712', 'yen minh yen minh', 'xxx', '2025-10-09 20:38:17', '2025-10-09 20:38:17'),
('0c87f20f-8397-4f0c-93cf-45106fe21dd6', '46057984', NULL, '8e8fea86-fe33-4910-8d28-6ef614286d25', 'sepay', NULL, NULL, NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Đạt Mg', 'tiendatmagic8@yopmail.com', '1234567890', 'Hà Nội', NULL, '2026-01-28 19:27:48', '2026-01-28 19:27:48'),
('0f2f355e-5033-424f-860c-05d936c345a8', '79974247', NULL, '8e8fea86-fe33-4910-8d28-6ef614286d25', 'cash', NULL, NULL, NULL, NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Đạt Mg', 'tiendatmagic8@yopmail.com', '1234567890', 'Nha Trang', 'Giao đi nha', '2025-10-09 20:50:58', '2025-10-09 20:51:22'),
('12fd6b73-b017-4b7a-a585-dcbbc847cd46', '96845805', NULL, '8e8fea86-fe33-4910-8d28-6ef614286d25', 'cash', NULL, NULL, NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Đạt Mg', 'tiendatmagic8@yopmail.com', '1234567890', 'Nha Trang', NULL, '2025-10-09 20:53:49', '2025-10-09 20:53:49'),
('1db79a06-582f-4969-9dfe-66f00097e68f', '21498648', NULL, '05d17a8c-56c4-4679-b856-d72aeb0ddda5', 'cash', NULL, NULL, NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'User41386', 'nguyendinhchung2782003@gmail.com', '038723811', '19 Nguyễn Văn Cừ', '.', '2025-11-17 07:30:02', '2025-11-17 07:30:02'),
('1f8c49c5-d8c5-4f56-b3ae-231f7da9a102', '68848985', NULL, '8e8fea86-fe33-4910-8d28-6ef614286d25', 'cash', NULL, NULL, NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Đạt Mg', 'tiendatmagic8@yopmail.com', '1234567890', 'Hà Nội', NULL, '2025-10-09 20:56:21', '2025-10-09 20:56:21'),
('2073f855-0956-45c4-b1af-6ba673182a7f', '17125650', NULL, '9b3e4d1c-91c1-440c-bb00-a76e60f7446b', 'cash', NULL, NULL, NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'User48096', 'doandangkhoa1492004@gmail.com', '0879288566', 'Da Nang', NULL, '2025-10-09 07:10:54', '2025-10-09 07:10:54'),
('2411f9d1-d45c-44e8-80b1-364b2c7cfada', '55267082', NULL, 'f8734e0e-1a12-4faf-b78d-aa9c17030cc4', 'cash', NULL, NULL, NULL, NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'User57389', 'nguyenanhvu9899@gmai.com', '0889857843', 'Hà Nội', NULL, '2025-10-09 01:01:42', '2025-10-09 01:04:14'),
('27a5c70a-99c1-4c60-a714-a7624f4ba340', '38853838', NULL, '8e8fea86-fe33-4910-8d28-6ef614286d25', 'coinbase', NULL, '3ac2951c-6be8-4f2e-8f9e-731f5f31ca4c', 'https://commerce.coinbase.com/pay/3ac2951c-6be8-4f2e-8f9e-731f5f31ca4c', NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Đạt Mg', 'tiendatmagic8@yopmail.com', '1234567890', 'Hà Nội', NULL, '2025-12-27 09:36:13', '2025-12-27 09:36:14'),
('2cbabc3b-0802-4c51-a952-4a1c492f7737', '35443377', NULL, '8e8fea86-fe33-4910-8d28-6ef614286d25', 'cryptomus', NULL, NULL, NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Đạt Mg', 'tiendatmagic8@yopmail.com', '1234567890', 'Hà Nội', NULL, '2025-12-24 09:11:13', '2025-12-24 09:11:13'),
('39d3e3e1-5104-4634-ad2b-7a3918973813', '73627015', 'DK107126', '8e8fea86-fe33-4910-8d28-6ef614286d25', 'sepay', NULL, NULL, 'https://qr.sepay.vn/img?acc=0901980543&bank=MB&amount=2342&des=DK107126', '2026-01-28 20:58:27', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Đạt Mg', 'tiendatmagic8@yopmail.com', '1234567890', 'Hà Nội', NULL, '2026-01-28 20:43:27', '2026-01-28 20:43:27'),
('3cf4ba8f-a74d-42fb-9a4a-453c8c69652c', '64644065', 'DK554294', '8e8fea86-fe33-4910-8d28-6ef614286d25', 'sepay', NULL, NULL, 'https://qr.sepay.vn/img?acc=0901980543&bank=MB&amount=2071&des=DK554294', '2026-01-28 20:49:24', 'canceled', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Đạt Mg', 'tiendatmagic8@yopmail.com', '1234567890', 'Hà Nội', NULL, '2026-01-28 20:34:24', '2026-01-28 20:34:48'),
('3e8278f8-dc12-4475-b0b4-bf7d0a25d10a', '71497599', NULL, '9b3e4d1c-91c1-440c-bb00-a76e60f7446b', 'cash', NULL, NULL, NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'User48096', 'doandangkhoa1492004@gmail.com', '123', '123', NULL, '2025-10-09 18:42:12', '2025-10-09 18:42:12'),
('3ec3520e-66f8-46e6-908f-63bfada35cab', '36053464', NULL, '9b3e4d1c-91c1-440c-bb00-a76e60f7446b', 'cash', NULL, NULL, NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'User48096', 'doandangkhoa1492004@gmail.com', '0389986712', 'yen minh yen minh', 'ccc', '2025-10-09 21:23:24', '2025-10-09 21:23:24'),
('4817cb79-00ed-46c7-996b-7964b6047fd3', '80515754', NULL, '8e8fea86-fe33-4910-8d28-6ef614286d25', 'coinbase', NULL, 'c7a8c708-88a2-4cd7-bf0b-79d682ebf33a', 'https://commerce.coinbase.com/pay/c7a8c708-88a2-4cd7-bf0b-79d682ebf33a', '2025-12-27 10:13:30', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Đạt Mg', 'tiendatmagic8@yopmail.com', '1234567890', 'Hà Nội', NULL, '2025-12-27 10:03:29', '2025-12-27 10:03:30'),
('4994e417-f861-46f2-971b-b0837049f3f9', '13264963', NULL, '6b5b50dd-acbc-4373-b691-86f69d11b87a', 'cash', NULL, NULL, NULL, NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'User26725', 'anhvu149@gmail.com', '0879288864', 'Da Nang', '0x5c452de543ba531c8d38bbcfa582a982a6374b95cea30d6d909ef3584908d376', '2025-10-09 01:13:12', '2025-10-09 01:13:59'),
('577bc28a-e4a1-4af3-8d47-b277703804bb', '36752179', NULL, '8e8fea86-fe33-4910-8d28-6ef614286d25', 'coinbase', NULL, NULL, NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Đạt Mg', 'tiendatmagic8@yopmail.com', '1234567890', 'Hà Nội', NULL, '2025-12-27 09:35:33', '2025-12-27 09:35:33'),
('578f600d-61c1-43d8-a682-1b2a78d38946', '15013294', NULL, '8e8fea86-fe33-4910-8d28-6ef614286d25', 'coinbase', NULL, '682ab21f-9ff7-47f6-94e2-efccc300f521', 'https://commerce.coinbase.com/pay/682ab21f-9ff7-47f6-94e2-efccc300f521', '2025-12-27 10:09:01', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Đạt Mg', 'tiendatmagic8@yopmail.com', '1234567890', 'Hà Nội', NULL, '2025-12-27 09:59:01', '2025-12-27 09:59:01'),
('57cf8220-87e8-4154-b74e-841aa7a7b361', '33345345', NULL, '8e8fea86-fe33-4910-8d28-6ef614286d25', 'coinbase', NULL, '84087704-73c8-4173-a79e-f51e1e924d00', 'https://commerce.coinbase.com/pay/84087704-73c8-4173-a79e-f51e1e924d00', '2026-01-04 10:49:49', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Đạt Mg', 'tiendatmagic8@yopmail.com', '1234567890', 'Hà Nội', '0x535b7A99CAF6F73697E69bEcb437B6Ba4b788888', '2026-01-04 10:39:48', '2026-01-04 10:39:49'),
('643fda84-8c5c-43ca-9ad6-526543ee7426', '40221940', 'DK616943', '8e8fea86-fe33-4910-8d28-6ef614286d25', 'sepay', NULL, NULL, 'https://qr.sepay.vn/img?acc=0901980543&bank=MB&amount=2342&des=DK616943', '2026-01-28 20:58:08', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Đạt Mg', 'tiendatmagic8@yopmail.com', '1234567890', 'Hà Nội', NULL, '2026-01-28 20:43:08', '2026-01-28 20:43:08'),
('6a36fd30-986b-4649-bdd9-6cb0e86e4ef8', '12451132', 'DK263384', '8e8fea86-fe33-4910-8d28-6ef614286d25', 'sepay', NULL, NULL, 'https://qr.sepay.vn/img?acc=0901980543&bank=MB&amount=2342&des=DK263384', '2026-01-28 21:08:45', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Đạt Mg', 'tiendatmagic8@yopmail.com', '1234567890', 'Hà Nội', NULL, '2026-01-28 20:53:45', '2026-01-28 20:53:45'),
('6ab0f7ea-8fbf-4735-ae6f-c742053f502d', '28409607', NULL, '9b3e4d1c-91c1-440c-bb00-a76e60f7446b', 'cash', NULL, NULL, NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'User48096', 'doandangkhoa1492004@gmail.com', '0824890912', 'Tân Tiến', 'zzzz', '2025-11-17 06:59:04', '2025-11-17 06:59:04'),
('72217f2e-ddd9-4126-b33c-4ce9954a34ba', '67761652', NULL, '9b3e4d1c-91c1-440c-bb00-a76e60f7446b', 'cash', NULL, NULL, NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'User48096', 'nguyenanhvu9899@gmail.com', '0888836644', 'Ha Noi', NULL, '2025-10-09 20:57:41', '2025-10-09 20:57:41'),
('78b2056b-67e0-44a3-bbb1-5296f6e2c896', '21122988', NULL, '8e8fea86-fe33-4910-8d28-6ef614286d25', 'coinbase', NULL, NULL, NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Đạt Mg', 'tiendatmagic8@yopmail.com', '1234567890', 'Hà Nội', NULL, '2025-12-27 09:15:12', '2025-12-27 09:15:12'),
('7fa76404-ca9b-498d-9602-70f45963e647', '43689166', NULL, '6b5b50dd-acbc-4373-b691-86f69d11b87a', 'cash', NULL, NULL, NULL, NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'User26725', 'anhvu149@gmail.com', '0879288478', 'Ho Chi Minh', '0x5e4FC833925D66918Af48F1deb0d07f12229bF8b', '2025-10-09 01:08:35', '2025-10-09 01:13:41'),
('8677a444-d046-430f-81b0-01bbf299b5e2', '35788376', NULL, '8e8fea86-fe33-4910-8d28-6ef614286d25', 'coinbase', NULL, NULL, NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Đạt Mg', 'tiendatmagic8@yopmail.com', '1234567890', 'Hà Nội', NULL, '2025-12-27 09:42:41', '2025-12-27 09:42:41'),
('8d3186e7-730a-4e73-aac3-3541cf6126be', '97737771', NULL, '8e8fea86-fe33-4910-8d28-6ef614286d25', 'coinbase', NULL, '164da4e0-3a59-467d-8520-8c3af7da060f', 'https://commerce.coinbase.com/pay/164da4e0-3a59-467d-8520-8c3af7da060f', '2025-12-29 09:56:39', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Đạt Mg', 'tiendatmagic8@yopmail.com', '1234567890', 'Hà Nội', NULL, '2025-12-27 09:56:38', '2025-12-27 09:56:40'),
('915abfba-58f4-470c-8d51-325a522b9051', '41559750', NULL, '8e8fea86-fe33-4910-8d28-6ef614286d25', 'cash', NULL, NULL, NULL, NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Đạt Mg', 'tiendatmagic8@yopmail.com', '1234567890', 'Nha Trang', NULL, '2025-10-08 22:45:24', '2025-10-08 22:51:17'),
('935918ef-5df4-459e-87e7-3891a5676f03', '68984546', NULL, '8e8fea86-fe33-4910-8d28-6ef614286d25', 'sepay', NULL, NULL, 'https://qr.sepay.vn/img?acc=0901980543&bank=MB&amount=2071&des=NAP8e8fea86-fe33-4910-8d28-6ef614286d2534IPKL', '2026-01-28 19:33:14', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Đạt Mg', 'tiendatmagic8@yopmail.com', '1234567890', 'Hà Nội', NULL, '2026-01-28 19:18:14', '2026-01-28 19:18:14'),
('9ddeafcc-3b43-4de7-bbd0-08d490001e0d', '39297056', NULL, '6b5b50dd-acbc-4373-b691-86f69d11b87a', 'cash', NULL, NULL, NULL, NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'User26725', 'anhvu149@gmail.com', '0968237410', 'Phường Hàng Bạc, Quận Hoàn Kiếm, Hà Nội', NULL, '2025-10-09 01:12:06', '2025-10-09 01:13:52'),
('a0a24d37-ef6e-4c16-a127-5e71e9c9a401', '33539644', NULL, '8e8fea86-fe33-4910-8d28-6ef614286d25', 'cash', NULL, NULL, NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Đạt Mg', 'tiendatmagic8@yopmail.com', '1234567890', 'Nha Trang', '0x1AD11e0e96797a14336Bf474676EB0A332055555', '2025-10-09 00:44:24', '2025-10-09 00:44:24'),
('a2a0a649-9ab2-487a-bb3d-0b955944abeb', '90705053', NULL, '8e8fea86-fe33-4910-8d28-6ef614286d25', 'coinbase', NULL, '89fa3934-c7f7-4d60-ae82-5b67ea80cb7b', 'https://commerce.coinbase.com/pay/89fa3934-c7f7-4d60-ae82-5b67ea80cb7b', NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Đạt Mg', 'tiendatmagic8@yopmail.com', '1234567890', 'Hà Nội', NULL, '2025-12-27 09:38:05', '2025-12-27 09:38:06'),
('a46b7cdf-909e-4b2b-b476-759b32400cf7', '72758425', 'DK102761', '8e8fea86-fe33-4910-8d28-6ef614286d25', 'sepay', NULL, NULL, 'https://qr.sepay.vn/img?acc=0901980543&bank=MB&amount=2071&des=DK102761', '2026-01-28 20:49:53', 'canceled', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Đạt Mg', 'tiendatmagic8@yopmail.com', '1234567890', 'Hà Nội', NULL, '2026-01-28 20:34:53', '2026-01-28 20:37:10'),
('b4b63ec5-7735-418d-ba80-62484bace36b', '62247550', NULL, '9b3e4d1c-91c1-440c-bb00-a76e60f7446b', 'cash', NULL, NULL, NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'User48096', 'doandangkhoa1492004@gmail.com', ',', '.', NULL, '2025-10-09 18:44:24', '2025-10-09 18:44:24'),
('b8def3a4-b76f-4d3f-a87e-81782297ab87', '98536480', NULL, '9b3e4d1c-91c1-440c-bb00-a76e60f7446b', 'cash', NULL, NULL, NULL, NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'User48096', 'doandangkhoa1492004@gmail.com', '0879288478', '29X6+76J, Ta An Khuong Dong-Tan Thuan, Tn Thun, m Di, C Mau, Vit Nam', '0x421f2f731b3a1C30c3893BbF97F0A337FB921815', '2025-10-09 01:26:27', '2025-10-09 01:33:24'),
('b9f8bad9-5d72-4744-b3c6-cff915783ad3', '67841526', NULL, '8e8fea86-fe33-4910-8d28-6ef614286d25', 'sepay', NULL, NULL, 'https://qr.sepay.vn/img?acc=0901980543&bank=MB&amount=0&des=67841526', '2026-01-28 19:26:46', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Đạt Mg', 'tiendatmagic8@yopmail.com', '1234567890', 'Hà Nội', NULL, '2026-01-28 19:11:46', '2026-01-28 19:11:46'),
('c57ecb2c-358f-44c6-b760-30e6a9df1f4f', '89362617', NULL, '8e8fea86-fe33-4910-8d28-6ef614286d25', 'sepay', NULL, NULL, NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Đạt Mg', 'tiendatmagic8@yopmail.com', '1234567890', 'Hà Nội', NULL, '2026-01-28 19:27:43', '2026-01-28 19:27:43'),
('c99874a6-72a6-4c86-bcc3-3307d0274f66', '63917937', NULL, '8e8fea86-fe33-4910-8d28-6ef614286d25', 'coinbase', NULL, NULL, NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Đạt Mg', 'tiendatmagic8@yopmail.com', '1234567890', 'Hà Nội', NULL, '2025-12-27 09:42:48', '2025-12-27 09:42:48'),
('d1ca1fbc-884d-4104-9169-dcbc08dcca23', '83413256', '870731', '8e8fea86-fe33-4910-8d28-6ef614286d25', 'sepay', NULL, NULL, 'https://qr.sepay.vn/img?acc=0901980543&bank=MB&amount=2071&des=870731', '2026-01-28 19:54:29', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Đạt Mg', 'tiendatmagic8@yopmail.com', '1234567890', 'Hà Nội', NULL, '2026-01-28 19:39:29', '2026-01-28 19:39:29'),
('d233a4c3-0305-488f-a951-01a8ecbfec15', '16126983', NULL, '8e8fea86-fe33-4910-8d28-6ef614286d25', 'sepay', NULL, NULL, 'https://qr.sepay.vn/img?acc=0901980543&bank=MB&amount=2071&des=16126983', '2026-01-28 19:29:38', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Đạt Mg', 'tiendatmagic8@yopmail.com', '1234567890', 'Hà Nội', NULL, '2026-01-28 19:14:38', '2026-01-28 19:14:38'),
('d6825f3b-5d9f-46cf-991a-3822eca0f329', '93185907', NULL, '9b3e4d1c-91c1-440c-bb00-a76e60f7446b', 'cash', NULL, NULL, NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'User48096', 'doandangkhoa1492004@gmail.com', '0385686004', 'Da Nang', 'xxxx', '2025-10-14 20:17:17', '2025-10-14 20:17:17'),
('d8671e55-c9f6-4723-8605-7bd7c8da62b6', '58369383', NULL, '8e8fea86-fe33-4910-8d28-6ef614286d25', 'sepay', NULL, 'NAP8e8fea86-fe33-4910-8d28-6ef614286d25TQRADU', 'https://qr.sepay.vn/img?acc=0901980543&bank=MB&amount=2071&des=NAP8e8fea86-fe33-4910-8d28-6ef614286d25TQRADU', '2026-01-28 19:37:33', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Đạt Mg', 'tiendatmagic8@yopmail.com', '1234567890', 'Hà Nội', NULL, '2026-01-28 19:22:33', '2026-01-28 19:22:33'),
('ddfa6524-81ae-429b-8f81-a5528075dad9', '31764317', NULL, '8e8fea86-fe33-4910-8d28-6ef614286d25', 'cash', NULL, NULL, NULL, NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Đạt Mg', 'tiendatmagic8@yopmail.com', '1234567890', 'Nha Trang', NULL, '2025-05-09 00:29:49', '2025-10-09 00:30:05'),
('e005e515-c2cd-4c24-a642-110de8e96d5e', '87471671', NULL, '8e8fea86-fe33-4910-8d28-6ef614286d25', 'cash', NULL, NULL, NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Đạt Mg', 'tiendatmagic8@yopmail.com', '1234567890', 'Mm', NULL, '2025-10-10 00:54:51', '2025-10-10 00:54:51'),
('e3a23308-147c-48c0-9dda-cf2831bce051', '89834020', NULL, '8e8fea86-fe33-4910-8d28-6ef614286d25', 'cash', NULL, NULL, NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Đạt Mg', 'tiendatmagic8@yopmail.com', '1234567890', 'Nha Trang', NULL, '2025-10-09 00:42:43', '2025-10-09 00:42:43'),
('f1016018-c317-4c97-a3e1-9ce79bf16bb3', '75703150', 'DK911138', '8e8fea86-fe33-4910-8d28-6ef614286d25', 'sepay', NULL, NULL, 'https://qr.sepay.vn/img?acc=0901980543&bank=MB&amount=2071&des=DK911138', '2026-01-28 19:57:04', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Đạt Mg', 'tiendatmagic8@yopmail.com', '1234567890', 'Hà Nội', NULL, '2026-01-28 19:42:04', '2026-01-28 19:42:04'),
('faecdbb4-05ce-40e6-86d8-777950bbe5ee', '35399388', 'DK867289', '8e8fea86-fe33-4910-8d28-6ef614286d25', 'sepay', NULL, NULL, 'https://qr.sepay.vn/img?acc=0901980543&bank=MB&amount=2342&des=DK867289', '2026-01-28 20:52:14', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Đạt Mg', 'tiendatmagic8@yopmail.com', '1234567890', 'Hà Nội', NULL, '2026-01-28 20:37:14', '2026-01-28 20:37:14'),
('fd2188ae-76f7-4e6f-9773-9e1b53b1f11a', '43706854', NULL, '8e8fea86-fe33-4910-8d28-6ef614286d25', 'coinbase', NULL, NULL, NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Đạt Mg', 'tiendatmagic8@yopmail.com', '1234567890', 'Hà Nội', NULL, '2025-12-27 09:15:19', '2025-12-27 09:15:19');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `order_items`
--

CREATE TABLE `order_items` (
  `id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `order_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `product_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `size` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity` bigint NOT NULL,
  `price` decimal(12,6) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `size`, `quantity`, `price`, `created_at`, `updated_at`) VALUES
('07a8b2bb-94d3-4207-8bd0-6be645c42de9', 'd233a4c3-0305-488f-a951-01a8ecbfec15', '194', 'BNB', 1, 0.090065, '2026-01-28 19:14:38', '2026-01-28 19:14:38'),
('0a7432e0-524d-42ca-ba03-77e8a78b83cb', '935918ef-5df4-459e-87e7-3891a5676f03', '194', 'BNB', 1, 0.090065, '2026-01-28 19:18:14', '2026-01-28 19:18:14'),
('0db294b2-cded-4d66-b71c-0b9c22801f6d', '6a36fd30-986b-4649-bdd9-6cb0e86e4ef8', '194', 'BNB', 1, 0.090065, '2026-01-28 20:53:45', '2026-01-28 20:53:45'),
('0f54c506-dc46-475c-8ec5-f7ffcbedef9a', '8d3186e7-730a-4e73-aac3-3541cf6126be', '30', 'POL', 1, 0.105100, '2025-12-27 09:56:38', '2025-12-27 09:56:38'),
('13ab32da-7822-484f-9f57-8d25b0f8cf58', 'b9f8bad9-5d72-4744-b3c6-cff915783ad3', '194', 'BNB', 1, 0.090065, '2026-01-28 19:11:46', '2026-01-28 19:11:46'),
('1587f5df-0351-4389-ae9d-a6f87d782425', '72217f2e-ddd9-4126-b33c-4ce9954a34ba', '30', 'POL', 100, 0.239300, '2025-10-09 20:57:41', '2025-10-09 20:57:41'),
('176599fc-cce5-4072-ad85-bac1c553c8ee', 'a0a24d37-ef6e-4c16-a127-5e71e9c9a401', '155', 'BNB', 1, 1314.300000, '2025-10-09 00:44:24', '2025-10-09 00:44:24'),
('1a3c6517-f154-498b-b467-87f7e130c94c', 'ddfa6524-81ae-429b-8f81-a5528075dad9', '30', 'POL', 1, 0.238200, '2025-10-09 00:29:50', '2025-10-09 00:29:50'),
('1dab73c8-0599-4eb8-80b5-361eb993e449', '1db79a06-582f-4969-9dfe-66f00097e68f', '185', '1 Lượng ( 10.00 Chỉ ) = 37.5 gram', 1, 0.000000, '2025-11-17 07:30:02', '2025-11-17 07:30:02'),
('1e0ce07e-c50a-441b-bbc3-ceff062ce550', '9ddeafcc-3b43-4de7-bbd0-08d490001e0d', '31', 'Vàng SJC', 1, 4047.330000, '2025-10-09 01:12:06', '2025-10-09 01:12:06'),
('22a5c333-4358-48a1-9ea7-d45cfc8ad3cf', 'c99874a6-72a6-4c86-bcc3-3307d0274f66', '30', 'POL', 1, 0.105100, '2025-12-27 09:42:48', '2025-12-27 09:42:48'),
('22c1dafa-db8d-4bf6-aa2f-3749b792d424', '643fda84-8c5c-43ca-9ad6-526543ee7426', '194', 'BNB', 1, 0.090065, '2026-01-28 20:43:08', '2026-01-28 20:43:08'),
('2d10310b-e6fe-45d0-81f8-ea138ffb2399', '27a5c70a-99c1-4c60-a714-a7624f4ba340', '30', 'POL', 1, 0.105100, '2025-12-27 09:36:13', '2025-12-27 09:36:13'),
('30f47f29-01c0-4cda-9b4b-f3fc3511a956', '78b2056b-67e0-44a3-bbb1-5296f6e2c896', '30', 'POL', 1, 0.105100, '2025-12-27 09:15:12', '2025-12-27 09:15:12'),
('3348680a-bf0a-4328-abb3-148f15fb31a3', '2cbabc3b-0802-4c51-a952-4a1c492f7737', '30', 'POL', 1, 0.105500, '2025-12-24 09:11:13', '2025-12-24 09:11:13'),
('3f2bdcde-f6c8-474e-b6e2-5ecafd26fad7', '043adb93-c0d6-4ada-a46d-932bba2283ea', '155', 'BNB', 0, 1252.250000, '2025-10-09 20:38:17', '2025-10-09 20:38:17'),
('424e8307-a6ce-40c1-93e7-2ff5f12464cc', '4994e417-f861-46f2-971b-b0837049f3f9', '28', 'SOL', 2, 224.520000, '2025-10-09 01:13:12', '2025-10-09 01:13:12'),
('4537dca1-b3c9-4ce6-b4f6-bf475946d351', 'd6825f3b-5d9f-46cf-991a-3822eca0f329', '156', '0.01', 1, 4199.700000, '2025-10-14 20:17:17', '2025-10-14 20:17:17'),
('49224e10-2d50-4f69-a228-6bfdbd1638aa', 'c57ecb2c-358f-44c6-b760-30e6a9df1f4f', '194', 'BNB', 1, 0.090065, '2026-01-28 19:27:43', '2026-01-28 19:27:43'),
('544f3dc7-ba1a-40b7-b571-9b63eb91035d', '2073f855-0956-45c4-b1af-6ba673182a7f', '31', 'Vàng SJC', 1, 4046.190000, '2025-10-09 07:10:54', '2025-10-09 07:10:54'),
('54b6c0ec-0a5b-4e07-b19f-f008e4b61fdc', 'd1ca1fbc-884d-4104-9169-dcbc08dcca23', '194', 'BNB', 1, 0.090065, '2026-01-28 19:39:29', '2026-01-28 19:39:29'),
('5549c76d-351f-4e53-a7bc-4117e781fcf4', '577bc28a-e4a1-4af3-8d47-b277703804bb', '30', 'POL', 1, 0.105100, '2025-12-27 09:35:33', '2025-12-27 09:35:33'),
('57644922-b698-4999-957f-c0c2497ee691', '39d3e3e1-5104-4634-ad2b-7a3918973813', '194', 'BNB', 1, 0.090065, '2026-01-28 20:43:27', '2026-01-28 20:43:27'),
('6149f85d-e558-4666-b264-8c6f37a6f404', '578f600d-61c1-43d8-a682-1b2a78d38946', '30', 'POL', 1, 0.105100, '2025-12-27 09:59:01', '2025-12-27 09:59:01'),
('6e0b8693-3a85-4e75-a8a3-bca2acec81d2', '0c87f20f-8397-4f0c-93cf-45106fe21dd6', '194', 'BNB', 1, 0.090065, '2026-01-28 19:27:48', '2026-01-28 19:27:48'),
('6f6252c3-2792-45b4-b1fe-46643c1a31a0', '12fd6b73-b017-4b7a-a585-dcbbc847cd46', '30', 'POL', 3, 0.239300, '2025-10-09 20:53:49', '2025-10-09 20:53:49'),
('76b6988b-201b-4dd4-b797-3315a53a5f98', 'f1016018-c317-4c97-a3e1-9ce79bf16bb3', '194', 'BNB', 1, 0.090065, '2026-01-28 19:42:04', '2026-01-28 19:42:04'),
('7735cb43-ebf8-4d96-98a0-dd740e1ffaae', 'b4b63ec5-7735-418d-ba80-62484bace36b', '156', '0.01', 1, 3987.600000, '2025-10-09 18:44:24', '2025-10-09 18:44:24'),
('77a68174-8051-42c4-a52d-c6dafc7c83e2', '6ab0f7ea-8fbf-4735-ae6f-c742053f502d', '28', 'SOL', 1, 137.620000, '2025-11-17 06:59:04', '2025-11-17 06:59:04'),
('7e4ae4b8-343c-4298-9d8e-479dfa759782', 'a2a0a649-9ab2-487a-bb3d-0b955944abeb', '30', 'POL', 1, 0.105100, '2025-12-27 09:38:05', '2025-12-27 09:38:05'),
('84038b24-4d60-4720-82a1-ae041d17450f', '57cf8220-87e8-4154-b74e-841aa7a7b361', '194', 'BNB', 1, 0.090065, '2026-01-04 10:39:48', '2026-01-04 10:39:48'),
('89ff672b-bc31-49a8-8b6c-4a58f924d6eb', 'd8671e55-c9f6-4723-8605-7bd7c8da62b6', '194', 'BNB', 1, 0.090065, '2026-01-28 19:22:33', '2026-01-28 19:22:33'),
('8d169f1a-79fe-42c6-afac-aa05cadb10ab', '3ec3520e-66f8-46e6-908f-63bfada35cab', '156', '0.01', 1, 3964.000000, '2025-10-09 21:23:24', '2025-10-09 21:23:24'),
('917726fc-a2d8-4173-bf36-68c722339253', 'fd2188ae-76f7-4e6f-9773-9e1b53b1f11a', '30', 'POL', 1, 0.105100, '2025-12-27 09:15:19', '2025-12-27 09:15:19'),
('96020648-7fc2-40cb-a7bb-0431bce6a91e', 'ddfa6524-81ae-429b-8f81-a5528075dad9', '28', 'SOL', 1, 224.950000, '2025-10-09 00:29:49', '2025-10-09 00:29:49'),
('9cbe34d0-90e3-4370-8066-12ad1244332d', '7fa76404-ca9b-498d-9602-70f45963e647', '30', 'POL', 175, 0.236900, '2025-10-09 01:08:35', '2025-10-09 01:08:35'),
('a07a1248-d43a-4e09-9c7e-ab57296422e0', '3e8278f8-dc12-4475-b0b4-bf7d0a25d10a', '31', 'Vàng SJC', 1, 3987.600000, '2025-10-09 18:42:12', '2025-10-09 18:42:12'),
('a8848e5e-543c-4ca2-9a74-3dc72a893a6b', 'e005e515-c2cd-4c24-a642-110de8e96d5e', '31', 'Vàng SJC', 1, 3978.410000, '2025-10-10 00:54:51', '2025-10-10 00:54:51'),
('acdbca65-28a9-46da-a19e-df1d6f1d56e8', '1f8c49c5-d8c5-4f56-b3ae-231f7da9a102', '31', 'Vàng PNJ', 1, 3972.800000, '2025-10-09 20:56:21', '2025-10-09 20:56:21'),
('b3e655fd-59d1-4783-bf9f-dcdbde2cff40', 'faecdbb4-05ce-40e6-86d8-777950bbe5ee', '194', 'BNB', 1, 0.090065, '2026-01-28 20:37:14', '2026-01-28 20:37:14'),
('bba67788-b004-460f-be78-e13ac9b45009', '8677a444-d046-430f-81b0-01bbf299b5e2', '30', 'POL', 1, 0.105100, '2025-12-27 09:42:41', '2025-12-27 09:42:41'),
('bec7d336-bf03-4e88-8289-16cb01906502', 'b8def3a4-b76f-4d3f-a87e-81782297ab87', '156', '0.01', 1, 4052.340000, '2025-10-09 01:26:27', '2025-10-09 01:26:27'),
('bf33cb67-a929-4c9c-b87b-aa97a011ff0b', '3cf4ba8f-a74d-42fb-9a4a-453c8c69652c', '194', 'BNB', 1, 0.090065, '2026-01-28 20:34:24', '2026-01-28 20:34:24'),
('c4d1d26a-03eb-431b-9800-87deb5cf3932', 'e3a23308-147c-48c0-9dda-cf2831bce051', '155', 'BNB', 1, 1314.300000, '2025-10-09 00:42:43', '2025-10-09 00:42:43'),
('c9d9ab49-3d97-4ed9-87c8-d8ac85793390', '0f2f355e-5033-424f-860c-05d936c345a8', '31', 'Vàng PNJ', 1, 3972.800000, '2025-10-09 20:50:58', '2025-10-09 20:50:58'),
('cd5a7ff4-80bb-4027-9680-511fce008a95', 'a46b7cdf-909e-4b2b-b476-759b32400cf7', '194', 'BNB', 1, 0.090065, '2026-01-28 20:34:53', '2026-01-28 20:34:53'),
('deff944c-7557-412c-8534-982dfcb18698', '7fa76404-ca9b-498d-9602-70f45963e647', '28', 'SOL', 1, 224.520000, '2025-10-09 01:08:35', '2025-10-09 01:08:35'),
('ed49b113-0d86-4fc9-a118-0c8e2e5e7656', '2411f9d1-d45c-44e8-80b1-364b2c7cfada', '155', 'BNB', 1, 1309.720000, '2025-10-09 01:01:42', '2025-10-09 01:01:42'),
('fc2fcc41-cbb5-4530-8aec-4f94d1bd582b', '4817cb79-00ed-46c7-996b-7964b6047fd3', '30', 'POL', 1, 0.105100, '2025-12-27 10:03:29', '2025-12-27 10:03:29'),
('fd224e76-5c9b-4987-8eba-7a93bf31b7bc', '915abfba-58f4-470c-8d51-325a522b9051', '155', 'BNB', 1, 1300.170000, '2025-10-08 22:45:24', '2025-10-08 22:45:24');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `order_payouts`
--

CREATE TABLE `order_payouts` (
  `id` bigint UNSIGNED NOT NULL,
  `order_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `asset_symbol` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `chain_id` int UNSIGNED NOT NULL DEFAULT '56',
  `is_native` tinyint(1) NOT NULL DEFAULT '0',
  `token_address` varchar(42) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `to_address` varchar(42) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_usd` decimal(24,8) DEFAULT NULL,
  `price_usd` decimal(24,12) DEFAULT NULL,
  `amount_decimal` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amount_wei` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tx_hash` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `error` text COLLATE utf8mb4_unicode_ci,
  `sent_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `password_resets`
--

CREATE TABLE `password_resets` (
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `products`
--

CREATE TABLE `products` (
  `id` int NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` decimal(12,6) NOT NULL,
  `quantity` decimal(6,5) DEFAULT NULL,
  `image` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `size` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `category` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_best_seller` tinyint(1) NOT NULL DEFAULT '0',
  `product_type` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `products`
--

INSERT INTO `products` (`id`, `name`, `price`, `quantity`, `image`, `size`, `category`, `is_best_seller`, `product_type`, `created_at`, `updated_at`) VALUES
(162, 'Arbitrum - ARB', 0.217800, 1.00000, '[\"\\/storage\\/images\\/4359b6c5-8cc1-48ef-8a90-ac217058b27d_1761571828.png\"]', '[\"1\",\"5\",\"10\"]', 'crypto', 0, 'arb', '2025-10-27 06:30:29', '2026-01-04 10:12:32'),
(159, 'Litecoin', 1.000000, 1.00000, '[\"\\/storage\\/images\\/de31f2a9-fe10-4628-b4a4-23146910827a_1761571619.png\"]', '[\"0.1\",\"0.5\",\"1\"]', 'crypto', 1, 'usdt', '2025-10-27 06:26:59', '2025-10-27 09:43:04'),
(161, 'Aptos - APT', 0.000000, 1.00000, '[\"\\/storage\\/images\\/3a6edbd6-bf2a-4a46-b95f-269ebbd551cf_1761571772.png\"]', '[\"0.5\",\"1\",\"5\"]', 'crypto', 0, NULL, '2025-10-27 06:29:32', '2025-10-27 06:29:32'),
(179, 'Avalanche - AVAX', 0.000000, 1.00000, '[\"\\/storage\\/images\\/2bbe5f7c-7b30-488b-a6de-49fbb21849f4_1761576912.png\"]', '[\"0.5\",\"1\",\"2\",\"5\",\"10\"]', 'crypto', 0, NULL, '2025-10-27 07:55:12', '2025-10-27 07:55:12'),
(180, 'TonCoin - TON', 0.000000, 1.00000, '[\"\\/storage\\/images\\/f4339b71-c927-4895-a928-716260e296cf_1761576974.png\"]', '[\"1\",\"2\",\"5\",\"10\"]', 'crypto', 0, NULL, '2025-10-27 07:56:14', '2025-10-27 07:56:14'),
(181, 'Ondo (ONDO)', 0.443700, 1.00000, '[\"\\/storage\\/images\\/58ac2a6c-635e-44bb-ac93-31fb4f143efe_1761577193.png\"]', '[\"5\",\"10\",\"20\",\"50\"]', 'crypto', 0, 'ondo', '2025-10-27 07:59:54', '2026-01-04 10:12:32'),
(28, 'SOL', 135.040000, 1.00000, '[\"\\/storage\\/images\\/c843dde0-73ed-48b6-af7f-da2329530f3e_1759975080.png\"]', '[\"SOL\"]', '', 1, 'sol', '2025-10-08 08:21:51', '2026-01-04 09:55:08'),
(30, 'POL', 0.122600, 1.00000, '[\"\\/storage\\/images\\/bb15b380-9615-488a-ac88-aca38ba4e2e7_1759943241.jpg\"]', '[\"POL\"]', 'crypto', 1, 'pol', '2025-10-08 10:07:22', '2026-01-04 10:12:32'),
(31, 'VÀNG MIẾNG SJC 1 LƯỢNG', 0.000000, 1.00000, '[\"\\/storage\\/images\\/978123fe-f124-4fc1-a41b-769456108493_1761573024.jpg\"]', '[\"1 L\\u01b0\\u1ee3ng\"]', 'gold/silver', 1, 'paxg', '2025-10-08 19:19:01', '2025-12-24 07:51:34'),
(155, 'BNB', 897.960000, 1.00000, '[\"\\/storage\\/images\\/6f478042-b50d-4c10-bc14-9fd8f35b67d2_1759988654.png\"]', '[\"BNB\"]', 'crypto', 0, 'bnb', '2025-10-08 22:44:15', '2026-01-04 10:12:32'),
(156, 'XAU (Token)', 0.000000, 1.00000, '[\"\\/storage\\/images\\/5a155d5d-e641-4a3e-b182-d3d6b249ab55_1759998027.jpg\"]', '[\"0.01\",\"0.05\",\"0.1\",\"0.5\",\"1\"]', 'gold/silver', 0, 'paxg', '2025-10-09 01:20:28', '2025-12-24 07:51:34'),
(160, 'AAVE', 0.000000, 1.00000, '[\"\\/storage\\/images\\/4bdd4ec1-8118-4be1-af21-95ad72d6a009_1761571721.png\"]', '[\"0.1\",\"0.5\",\"01\"]', 'crypto', 0, NULL, '2025-10-27 06:28:41', '2025-10-27 06:28:41'),
(163, 'Bittensor - TAO', 0.000000, 1.00000, '[\"\\/storage\\/images\\/e4ea9c98-dc59-4da1-a15c-978c080f9d19_1761571932.png\"]', '[\"0.1\",\"0.2\",\"0.5\",\"1\"]', 'crypto', 0, NULL, '2025-10-27 06:32:13', '2025-10-27 06:32:13'),
(164, 'Cardano - ADA', 0.000000, 1.00000, '[\"\\/storage\\/images\\/cfd42565-9a64-4cd2-8d0d-0793597db4a7_1761572014.png\"]', '[\"1\",\"2\",\"5\",\"10\"]', 'crypto', 0, NULL, '2025-10-27 06:33:34', '2025-10-27 06:33:34'),
(165, 'Chainlink - LINK', 0.000000, 1.00000, '[\"\\/storage\\/images\\/640c19ec-c661-4369-b8b6-ce8a49ae29f8_1761572090.png\"]', '[\"1\",\"2\",\"5\",\"10\"]', 'crypto', 0, NULL, '2025-10-27 06:34:50', '2025-10-27 06:34:50'),
(166, 'Dogecoin - DOGE', 0.000000, 1.00000, '[\"\\/storage\\/images\\/fac9112b-cea0-4f35-aa0e-b866eb6c33f4_1761572162.png\"]', '[\"5\",\"10\",\"20\",\"50\"]', 'crypto', 0, NULL, '2025-10-27 06:36:03', '2025-10-27 06:36:03'),
(167, 'Ethena - ENA', 0.000000, 1.00000, '[\"\\/storage\\/images\\/83a72a94-7061-4994-8494-50512917baa0_1761572209.png\"]', '[\"1\",\"2\",\"5\",\"10\"]', 'crypto', 0, NULL, '2025-10-27 06:36:49', '2025-10-27 06:36:49'),
(168, 'Ethereum Classic - ETC', 0.000000, 1.00000, '[\"\\/storage\\/images\\/7de806ba-9541-4b90-9891-903e73c7846d_1761572266.png\"]', '[\"1\",\"2\",\"5\",\"10\"]', 'crypto', 0, NULL, '2025-10-27 06:37:46', '2025-10-27 06:37:46'),
(169, 'Filecoin - FIL', 0.000000, 1.00000, '[\"\\/storage\\/images\\/0518d5ec-9ce6-48cc-b1fd-677cd43ed981_1761572317.png\"]', '[\"1\",\"2\",\"5\",\"10\"]', 'crypto', 0, NULL, '2025-10-27 06:38:37', '2025-10-27 06:38:37'),
(170, 'NEAR Protocol - NEAR', 0.000000, 1.00000, '[\"\\/storage\\/images\\/b72e8428-a619-436c-a920-bd803e9caddc_1761572381.png\"]', '[\"1\",\"2\",\"5\",\"10\"]', 'crypto', 0, NULL, '2025-10-27 06:39:42', '2025-10-27 06:39:42'),
(171, 'Shina Inu - SHI', 0.000009, 1.00000, '[\"\\/storage\\/images\\/df82dda4-ee6e-458b-ad57-85f706a68453_1761572461.png\"]', '[\"100\",\"200\",\"500\",\"1000\"]', 'crypto', 0, 'shib', '2025-10-27 06:41:01', '2026-01-04 10:12:32'),
(172, 'SUI', 1.715000, 1.00000, '[\"\\/storage\\/images\\/063bb688-84dd-43c3-894d-e129809dc434_1761572519.png\"]', '[\"1\",\"2\",\"5\",\"10\"]', 'crypto', 0, 'sui', '2025-10-27 06:41:59', '2026-01-04 10:12:32'),
(173, 'Tron - TRX', 0.294400, 1.00000, '[\"\\/storage\\/images\\/5437b6af-e05f-4f5b-ac95-8e17ef06e8a4_1761572574.png\"]', '[\"5\",\"10\",\"20\",\"50\"]', 'crypto', 0, 'trx', '2025-10-27 06:42:54', '2026-01-04 10:12:32'),
(174, 'XRP', 2.095600, 1.00000, '[\"\\/storage\\/images\\/a5d4e263-07fa-4a3f-889a-b54aa8131abd_1761572666.png\"]', '[\"1\",\"2\",\"5\",\"10\"]', 'crypto', 0, 'xrp', '2025-10-27 06:44:27', '2026-01-04 10:12:32'),
(175, 'VÀNG MIẾNG SJC 5 CHỈ', 0.000000, 1.00000, '[\"\\/storage\\/images\\/cb5758ae-aa21-4f7e-86e9-e4b2dcbf5a54_1761573144.jpg\"]', '[\"5 CH\\u1ec8\"]', 'gold/silver', 0, 'paxg', '2025-10-27 06:52:24', '2025-12-24 07:51:34'),
(176, 'VÀNG MIẾNG SJC 2 CHỈ', 0.000000, 0.00000, '[\"\\/storage\\/images\\/69eb1109-b0f0-41ea-b17d-5c12ba575bd1_1761573193.jpg\"]', '[\"2 CH\\u1ec8\"]', 'gold/silver', 0, 'paxg', '2025-10-27 06:53:14', '2025-12-24 07:51:34'),
(177, 'VÀNG MIẾNG SJC 1 CHỈ', 0.000000, 0.00000, '[\"\\/storage\\/images\\/8a65d363-dffc-497f-bf8d-d61f7ecc3a9b_1761573232.jpg\"]', '[\"1 CH\\u1ec8\"]', 'gold/silver', 0, 'paxg', '2025-10-27 06:53:53', '2025-12-24 07:51:34'),
(178, 'BitCoin - BTC', 91370.090000, 1.00000, '[\"\\/storage\\/images\\/48718693-a528-40a6-b73d-1694160ac860_1761574574.png\"]', '[\"0.01\",\"0.002\",\"0.005\",\"0.1\"]', 'crypto', 0, 'btc', '2025-10-27 07:16:15', '2026-01-04 10:12:32'),
(194, 'BNB', 0.090065, 0.00010, '[\"\\/storage\\/images\\/0e97768b-f3b1-4e23-ac27-5b97a0b9a93f_1767546133.png\"]', '[\"BNB\"]', 'crypto', 1, 'bnb', '2026-01-04 10:02:15', '2026-01-04 10:26:55'),
(195, 'Men', 10.000000, 1.00000, '[\"\\/storage\\/images\\/f6229a35-0afa-4b7a-8fef-1c94346a4135_1769674528.jpg\"]', '[\"M\"]', 'men', 1, 'none', '2026-01-29 01:15:29', '2026-01-29 01:15:29'),
(196, 'A01', 1000.000000, 1.00000, '[\"\\/storage\\/images\\/d40f1c30-96a1-47e1-81c5-aafec55d3b46_1769680862.jpg\"]', '[\"A\"]', '', 0, 'none', '2026-01-29 03:01:04', '2026-01-29 03:01:04');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `token_assets`
--

CREATE TABLE `token_assets` (
  `id` bigint UNSIGNED NOT NULL,
  `symbol` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `chain_id` int UNSIGNED NOT NULL DEFAULT '56',
  `is_native` tinyint(1) NOT NULL DEFAULT '0',
  `token_address` varchar(42) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `decimals` smallint UNSIGNED NOT NULL DEFAULT '18',
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `users`
--

CREATE TABLE `users` (
  `id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `full_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(20,5) NOT NULL DEFAULT '0.00000',
  `is_admin` tinyint(1) NOT NULL DEFAULT '0',
  `remember_token` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`id`, `name`, `full_name`, `phone`, `address`, `email`, `email_verified_at`, `password`, `amount`, `is_admin`, `remember_token`, `created_at`, `updated_at`) VALUES
('05d17a8c-56c4-4679-b856-d72aeb0ddda5', 'User89633', 'User41386', NULL, NULL, 'nguyendinhchung2782003@gmail.com', NULL, '$2y$12$aEV/RTFW7srmqauN/ZwGkOSqEHuBD8T7bPU1l3AfkJw4Wh2g9a8Fy', 0.00000, 0, NULL, '2025-11-17 07:26:11', '2025-11-17 07:26:11'),
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
-- Chỉ mục cho bảng `admin_wallet_settings`
--
ALTER TABLE `admin_wallet_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `admin_wallet_settings_chain_id_unique` (`chain_id`);

--
-- Chỉ mục cho bảng `customizations`
--
ALTER TABLE `customizations`
  ADD PRIMARY KEY (`id`);

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
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `orders_sepay_code_unique` (`sepay_code`);

--
-- Chỉ mục cho bảng `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `order_payouts`
--
ALTER TABLE `order_payouts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_payouts_order_id_asset_symbol_unique` (`order_id`,`asset_symbol`),
  ADD KEY `order_payouts_order_id_index` (`order_id`);

--
-- Chỉ mục cho bảng `password_resets`
--
ALTER TABLE `password_resets`
  ADD KEY `password_resets_email_index` (`email`);

--
-- Chỉ mục cho bảng `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

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
-- Chỉ mục cho bảng `token_assets`
--
ALTER TABLE `token_assets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token_assets_symbol_unique` (`symbol`);

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
-- AUTO_INCREMENT cho bảng `admin_wallet_settings`
--
ALTER TABLE `admin_wallet_settings`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT cho bảng `customizations`
--
ALTER TABLE `customizations`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT cho bảng `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT cho bảng `order_payouts`
--
ALTER TABLE `order_payouts`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `products`
--
ALTER TABLE `products`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=197;

--
-- AUTO_INCREMENT cho bảng `token_assets`
--
ALTER TABLE `token_assets`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
