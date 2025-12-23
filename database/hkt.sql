-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th12 23, 2025 lúc 01:55 AM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `hkt`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `activity_logs`
--

CREATE TABLE `activity_logs` (
  `log_id` int(11) NOT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `activity_logs`
--

INSERT INTO `activity_logs` (`log_id`, `admin_id`, `action`, `description`, `ip_address`, `created_at`) VALUES
(1, 1, 'logout', 'Đăng xuất khỏi hệ thống', '::1', '2024-12-07 14:47:31'),
(2, 1, 'login', 'Đăng nhập thành công vào hệ thống quản trị', '::1', '2024-12-07 15:17:15'),
(3, 1, 'login', 'Đăng nhập thành công vào hệ thống quản trị', '::1', '2024-12-07 15:18:12'),
(4, 1, 'login', 'Đăng nhập thành công vào hệ thống quản trị', '::1', '2024-12-12 17:46:12'),
(5, 1, 'login', 'Đăng nhập thành công vào hệ thống quản trị', '::1', '2024-12-13 04:59:03'),
(6, 1, 'login', 'Đăng nhập thành công vào hệ thống quản trị', '::1', '2024-12-21 13:18:25'),
(7, 1, 'login', 'Đăng nhập thành công vào hệ thống quản trị', '::1', '2024-12-27 05:00:05'),
(8, 1, 'login', 'Đăng nhập thành công vào hệ thống quản trị', '::1', '2024-12-29 02:33:49'),
(9, 1, 'login', 'Đăng nhập thành công vào hệ thống quản trị', '::1', '2025-01-14 13:31:16'),
(10, 1, 'login', 'Đăng nhập thành công vào hệ thống quản trị', '::1', '2025-01-15 06:27:02'),
(11, 1, 'login', 'Đăng nhập thành công vào hệ thống quản trị', '::1', '2025-01-15 07:08:01'),
(12, 1, 'login', 'Đăng nhập thành công vào hệ thống quản trị', '::1', '2025-11-21 04:03:31'),
(13, 1, 'login', 'Đăng nhập thành công vào hệ thống quản trị', '::1', '2025-11-21 05:30:19'),
(14, 1, 'login', 'Đăng nhập thành công vào hệ thống quản trị', '::1', '2025-11-21 05:56:55'),
(15, 1, 'login', 'Đăng nhập thành công vào hệ thống quản trị', '::1', '2025-11-21 07:01:33'),
(16, 1, 'login', 'Đăng nhập thành công vào hệ thống quản trị', '::1', '2025-11-22 05:46:55'),
(17, 1, 'logout', 'Đăng xuất khỏi hệ thống', '::1', '2025-11-22 05:50:48'),
(18, 1, 'login', 'Đăng nhập thành công vào hệ thống quản trị', '::1', '2025-11-22 05:51:00'),
(19, 1, 'logout', 'Đăng xuất khỏi hệ thống', '::1', '2025-11-22 05:51:06'),
(20, 1, 'login', 'Đăng nhập thành công vào hệ thống quản trị', '::1', '2025-11-22 05:54:42'),
(21, 1, 'logout', 'Đăng xuất khỏi hệ thống', '::1', '2025-11-22 06:23:48'),
(22, 1, 'logout', 'Đăng xuất khỏi hệ thống', '::1', '2025-11-22 06:27:46'),
(23, 1, 'login', 'Đăng nhập thành công vào hệ thống quản trị', '::1', '2025-11-22 06:27:52'),
(24, 1, 'login', 'Đăng nhập thành công vào hệ thống quản trị', '::1', '2025-11-23 13:41:08'),
(25, 1, 'login', 'Đăng nhập thành công vào hệ thống quản trị', '::1', '2025-11-23 14:22:40'),
(26, 1, 'login', 'Đăng nhập thành công vào hệ thống quản trị', '::1', '2025-11-25 06:14:33'),
(27, 1, 'login', 'Đăng nhập thành công vào hệ thống quản trị', '::1', '2025-11-25 14:37:22'),
(28, 1, 'logout', 'Đăng xuất khỏi hệ thống', '::1', '2025-11-25 14:49:48'),
(29, 1, 'login', 'Đăng nhập thành công vào hệ thống quản trị', '::1', '2025-12-07 15:40:25'),
(30, 1, 'login', 'Đăng nhập thành công vào hệ thống quản trị', '::1', '2025-12-09 01:07:08'),
(31, 1, 'login', 'Đăng nhập thành công vào hệ thống quản trị', '::1', '2025-12-12 11:49:16'),
(32, 1, 'logout', 'Đăng xuất khỏi hệ thống', '::1', '2025-12-12 12:29:40'),
(33, 1, 'login', 'Đăng nhập thành công vào hệ thống quản trị', '::1', '2025-12-12 12:58:12'),
(34, 1, 'logout', 'Đăng xuất khỏi hệ thống', '::1', '2025-12-12 13:25:23'),
(35, 1, 'login', 'Đăng nhập thành công vào hệ thống quản trị', '::1', '2025-12-12 13:26:00'),
(36, 1, 'logout', 'Đăng xuất khỏi hệ thống', '::1', '2025-12-12 13:26:23'),
(37, 1, 'login', 'Đăng nhập thành công vào hệ thống quản trị', '::1', '2025-12-12 13:39:50'),
(38, 1, 'login', 'Đăng nhập thành công vào hệ thống quản trị', '::1', '2025-12-23 00:15:36'),
(39, 1, 'logout', 'Đăng xuất khỏi hệ thống', '::1', '2025-12-23 00:16:45'),
(40, 1, 'login', 'Đăng nhập thành công vào hệ thống quản trị', '::1', '2025-12-23 00:18:01'),
(41, 1, 'logout', 'Đăng xuất khỏi hệ thống', '::1', '2025-12-23 00:18:15'),
(42, 1, 'login', 'Đăng nhập thành công vào hệ thống quản trị', '::1', '2025-12-23 00:19:00'),
(43, 1, 'logout', 'Đăng xuất khỏi hệ thống', '::1', '2025-12-23 00:20:04'),
(44, 1, 'login', 'Đăng nhập thành công vào hệ thống quản trị', '::1', '2025-12-23 00:20:29'),
(45, 1, 'logout', 'Đăng xuất khỏi hệ thống', '::1', '2025-12-23 00:20:37'),
(46, 1, 'login', 'Đăng nhập thành công vào hệ thống quản trị', '::1', '2025-12-23 00:22:56'),
(47, 1, 'logout', 'Đăng xuất khỏi hệ thống', '::1', '2025-12-23 00:30:32'),
(48, 1, 'login', 'Đăng nhập thành công vào hệ thống quản trị', '::1', '2025-12-23 00:44:36');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `administrators`
--

CREATE TABLE `administrators` (
  `admin_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_login` timestamp NULL DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `administrators`
--

INSERT INTO `administrators` (`admin_id`, `username`, `password`, `full_name`, `email`, `phone`, `created_at`, `last_login`, `status`) VALUES
(1, 'admin', '$2y$10$RcxuN.GUn3k1DTMXPfMFKuhk63gu26yG1p51IRzptTTmsZma2aLZK', 'Administrator', 'admin@tthuong.com', '0392656499', '2024-12-05 15:38:44', '2025-12-23 00:44:36', 'active');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `admin_permissions`
--

CREATE TABLE `admin_permissions` (
  `admin_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL,
  `granted_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `cart`
--

CREATE TABLE `cart` (
  `cart_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` varchar(10) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `categories`
--

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `categories`
--

INSERT INTO `categories` (`category_id`, `category_name`, `description`, `status`) VALUES
(1, 'tuong', 'Tượng trang trí', 'active'),
(2, 'tranh', 'Tranh treo tường', 'active'),
(3, 'den', 'Đèn trang trí', 'active'),
(4, 'khac', 'Sản phẩm khác', 'active');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `messages`
--

CREATE TABLE `messages` (
  `message_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) DEFAULT NULL,
  `sender_type` enum('user','admin') NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_read` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `messages`
--

INSERT INTO `messages` (`message_id`, `sender_id`, `receiver_id`, `sender_type`, `message`, `created_at`, `is_read`) VALUES
(1, 3, NULL, 'user', 'hi', '2025-11-22 06:53:21', 1),
(2, 1, 3, 'admin', 'hi', '2025-11-22 06:53:38', 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `address` text NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `payment_method` enum('cod','bank_transfer') NOT NULL DEFAULT 'cod',
  `payment_proof` varchar(255) DEFAULT NULL,
  `order_status` enum('Chờ thanh toán','Chờ xác nhận','Đã xác nhận','Đang giao','Hoàn thành','Đã hủy','Chờ xác nhận trả hàng','Đã duyệt trả hàng','Không đồng ý duyệt trả hàng') DEFAULT 'Chờ xác nhận',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `order_date` datetime DEFAULT current_timestamp(),
  `status` text DEFAULT 'Chờ xác nhận',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `customer_confirmed` tinyint(1) DEFAULT 0,
  `return_request` tinyint(1) DEFAULT 0,
  `return_reason` text DEFAULT NULL,
  `return_request_date` datetime DEFAULT NULL,
  `return_status` varchar(50) DEFAULT NULL,
  `completed_date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `orders`
--

INSERT INTO `orders` (`order_id`, `user_id`, `full_name`, `email`, `phone`, `address`, `total_amount`, `payment_method`, `payment_proof`, `order_status`, `notes`, `created_at`, `order_date`, `status`, `updated_at`, `customer_confirmed`, `return_request`, `return_reason`, `return_request_date`, `return_status`, `completed_date`) VALUES
(1, 3, 'Trần Thanh Thưởng', 'dubu2k4@gmail.com', '0392656499', 'Cầu Ngang trà vinh', 780000.00, 'cod', NULL, 'Hoàn thành', 'giao nhanh', '2024-12-15 14:03:02', '2024-12-15 21:16:30', 'Đã giao hàng', '2025-11-25 14:48:22', 0, 0, NULL, NULL, NULL, NULL),
(2, 3, 'Trần Thanh Thưởng', 'dubu2k4@gmail.com', '0392656499', 'Cầu Ngang trà vinh', 0.00, 'cod', NULL, 'Hoàn thành', 'giao nhanh', '2024-12-15 14:03:06', '2024-12-15 21:16:30', 'Đang giao hàng', '2025-11-25 14:48:29', 0, 0, NULL, NULL, NULL, NULL),
(3, 5, 'Nguyễn Văn A', 'nguyena@gmail.com', '0987876552', 'Cầu Ngang trà vinh', 180000.00, 'cod', NULL, 'Hoàn thành', '', '2025-11-21 05:29:44', '2025-11-21 12:29:44', 'Chờ xác nhận', '2025-12-07 15:24:36', 1, 0, NULL, NULL, NULL, NULL),
(4, 3, 'Trần Thanh Thưởng', 'dubu2k4@gmail.com', '0392656499', 'Cầu Ngang trà vinh', 200000.00, 'bank_transfer', NULL, 'Đã hủy', '\nTự động hủy: Quá thời gian thanh toán (24h)', '2025-11-23 14:04:12', '2025-11-23 21:04:12', 'Chờ xác nhận', '2025-11-25 06:16:25', 0, 0, NULL, NULL, NULL, NULL),
(5, 3, 'Trần Thanh Thưởng', 'dubu2k4@gmail.com', '0392656499', 'Cầu Ngang trà vinh', 200000.00, 'bank_transfer', NULL, 'Đã hủy', '\nTự động hủy: Quá thời gian thanh toán (24h)', '2025-11-23 14:07:00', '2025-11-23 21:07:00', 'Chờ xác nhận', '2025-11-25 06:16:25', 0, 0, NULL, NULL, NULL, NULL),
(6, 3, 'Trần Thanh Thưởng', 'dubu2k4@gmail.com', '0392656499', 'Cầu Ngang trà vinh', 180000.00, 'cod', NULL, 'Hoàn thành', '', '2025-11-23 14:26:21', '2025-11-23 21:26:21', 'Chờ xác nhận', '2025-12-12 13:16:31', 0, 0, NULL, NULL, NULL, '2025-12-12 20:16:31'),
(7, 3, 'Trần Thanh Thưởng', 'dubu2k4@gmail.com', '0392656499', 'Cầu Ngang trà vinh', 380000.00, 'bank_transfer', NULL, 'Hoàn thành', '', '2025-11-25 06:15:17', '2025-11-25 13:15:17', 'Chờ xác nhận', '2025-11-25 06:17:00', 0, 0, NULL, NULL, NULL, NULL),
(8, 3, 'Trần Thanh Thưởng', 'dubu2k4@gmail.com', '0392656499', 'Cầu Ngang trà vinh', 1060000.00, 'cod', NULL, 'Hoàn thành', '', '2025-12-12 13:25:48', '2025-12-12 20:25:48', 'Chờ xác nhận', '2025-12-23 00:17:05', 1, 0, NULL, NULL, NULL, '2025-12-23 07:17:05'),
(9, 3, 'Trần Thanh Thưởng', 'dubu2k4@gmail.com', '0392656499', 'Cầu Ngang trà vinh', 450000.00, 'cod', NULL, 'Đã duyệt trả hàng', '', '2025-12-23 00:18:49', '2025-12-23 07:18:49', 'Chờ xác nhận', '2025-12-23 00:44:51', 0, 1, 'ko ok', '2025-12-23 07:22:42', 'Đã duyệt', '2025-12-23 07:20:34');

--
-- Bẫy `orders`
--
DELIMITER $$
CREATE TRIGGER `update_order_status_time` BEFORE UPDATE ON `orders` FOR EACH ROW BEGIN
    IF NEW.status != OLD.status THEN
        SET NEW.updated_at = NOW();
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `order_details`
--

CREATE TABLE `order_details` (
  `order_detail_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `product_id` varchar(10) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `order_details`
--

INSERT INTO `order_details` (`order_detail_id`, `order_id`, `product_id`, `quantity`, `price`) VALUES
(1, 1, '015', 1, 200000.00),
(2, 1, '014', 1, 180000.00),
(3, 1, '013', 2, 200000.00),
(4, 3, '014', 1, 180000.00),
(5, 4, '013', 1, 200000.00),
(6, 5, '013', 1, 200000.00),
(7, 6, '014', 1, 180000.00),
(8, 7, '015', 1, 200000.00),
(9, 7, '014', 1, 180000.00),
(10, 8, '014', 2, 180000.00),
(11, 8, '008', 1, 200000.00),
(12, 8, '007', 1, 500000.00),
(13, 9, '004', 1, 300000.00),
(14, 9, '003', 1, 150000.00);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `permissions`
--

CREATE TABLE `permissions` (
  `permission_id` int(11) NOT NULL,
  `permission_name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `products`
--

CREATE TABLE `products` (
  `product_id` varchar(10) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `product_name` varchar(200) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `description` text DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `stock_quantity` int(11) DEFAULT 0,
  `sold_quantity` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `products`
--

INSERT INTO `products` (`product_id`, `category_id`, `product_name`, `price`, `description`, `image_url`, `stock_quantity`, `sold_quantity`) VALUES
('001', 1, 'Bộ 3 tượng decor', 600000.00, 'Bộ 3 tượng trang trí cao cấp', './images/sp1.jpg', 50, 0),
('002', 2, 'Tranh treo tường Núi & Thác Nước', 250000.00, 'Tranh phong cảnh thiên nhiên', './images/sp2.jpg', 30, 0),
('003', 4, 'Đồ trang trí lá vàng', 150000.00, 'Trang trí lá vàng nghệ thuật', './images/sp3.jpg', 40, 0),
('004', 1, 'Đồ trang trí thiếu nữ múa Ballet', 300000.00, 'Tượng thiếu nữ múa Ballet', './images/sp4.jpg', 25, 0),
('005', 2, 'Bộ 3 tranh tráng gương', 700000.00, 'Bộ tranh tráng gương nghệ thuật', './images/sp5.jpg', 20, 0),
('006', 2, 'Tranh đính đá pha lê đèn LED', 850000.00, 'Tranh đính đá có đèn LED', './images/sp6.jpg', 15, 0),
('007', 2, 'Tranh tráng gương có đèn led', 500000.00, 'Tranh tráng gương tích hợp đèn LED', './images/sp7.jpg', 24, 1),
('008', 4, 'Hộp đựng nến', 200000.00, 'Hộp đựng nến trang trí', './images/sp8.jpg', 34, 1),
('009', 4, 'Đồng hồ treo tường', 450000.00, 'Đồng hồ trang trí', './images/sp9.jpg', 30, 0),
('010', 4, 'Bình hoa đôi', 300000.00, 'Bộ bình hoa trang trí', './images/sp10.jpg', 40, 0),
('011', 3, 'Đèn thả trần', 250000.00, 'Đèn thả trần trang trí', './images/sp11.jpg', 20, 0),
('012', 3, 'Đèn treo tường', 180000.00, 'Đèn treo tường trang trí', './images/sp12.jpg', 25, 0),
('013', 4, 'Bể cá cảnh', 200000.00, 'Bể cá mini trang trí', './images/sp13.jpg', 13, 2),
('014', 4, 'Đồng hồ treo tường', 180000.00, 'Đồng hồ trang trí phòng khách', './images/sp14.jpg', 26, 4),
('015', 4, 'Thảm Trải Sàn', 200000.00, 'thảm trải sàn vô cùng tuyệt vời', 'uploads/1733580811_sp15.jpg', 19, 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `promotions`
--

CREATE TABLE `promotions` (
  `promotion_id` int(11) NOT NULL,
  `promotion_code` varchar(50) NOT NULL,
  `promotion_name` varchar(255) NOT NULL,
  `promotion_type` enum('product','category','flash_sale','coupon','minimum_order') NOT NULL,
  `discount_type` enum('percentage','fixed_amount') NOT NULL,
  `discount_value` decimal(10,2) NOT NULL,
  `min_order_amount` decimal(10,2) DEFAULT 0.00,
  `max_discount` decimal(10,2) DEFAULT NULL,
  `start_date` datetime NOT NULL,
  `end_date` datetime NOT NULL,
  `usage_limit` int(11) DEFAULT NULL,
  `used_count` int(11) DEFAULT 0,
  `status` enum('active','inactive','expired') DEFAULT 'active',
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `promotions`
--

INSERT INTO `promotions` (`promotion_id`, `promotion_code`, `promotion_name`, `promotion_type`, `discount_type`, `discount_value`, `min_order_amount`, `max_discount`, `start_date`, `end_date`, `usage_limit`, `used_count`, `status`, `description`, `created_at`, `updated_at`) VALUES
(1, 'FSCHOPNHOANG', 'Flash Sale Khai Trương Cửa Hàng', 'flash_sale', 'percentage', 10.00, 0.00, 20000.00, '2025-11-22 00:00:00', '2025-11-23 02:31:00', NULL, 0, 'active', 'Flash Sale khai trương Cửa hàng ngày 22/11/2025', '2025-11-22 07:19:24', '2025-11-22 07:19:24');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `promotion_categories`
--

CREATE TABLE `promotion_categories` (
  `id` int(11) NOT NULL,
  `promotion_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `promotion_products`
--

CREATE TABLE `promotion_products` (
  `id` int(11) NOT NULL,
  `promotion_id` int(11) NOT NULL,
  `product_id` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `promotion_usage`
--

CREATE TABLE `promotion_usage` (
  `id` int(11) NOT NULL,
  `promotion_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `discount_amount` decimal(10,2) NOT NULL,
  `used_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `reviews`
--

CREATE TABLE `reviews` (
  `review_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` varchar(10) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `rating` int(11) NOT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `content` text NOT NULL,
  `images` text DEFAULT NULL,
  `review_date` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `reviews`
--

INSERT INTO `reviews` (`review_id`, `user_id`, `product_id`, `order_id`, `rating`, `content`, `images`, `review_date`) VALUES
(4, 5, '012', 3, 5, 'ok', '[\"uploads\\/reviews\\/69200e3ee3ddb_\\u1ea2nh ch\\u1ee5p m\\u00e0n h\\u00ecnh 2024-12-12 172910.png\"]', '2025-11-21 14:01:18'),
(5, 3, '013', 7, 4, 'ok á nha', NULL, '2025-11-25 13:17:37');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `google_id` varchar(255) DEFAULT NULL,
  `google_picture` varchar(500) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_admin` tinyint(1) DEFAULT 0,
  `status` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`user_id`, `username`, `password`, `google_id`, `google_picture`, `email`, `full_name`, `phone`, `address`, `created_at`, `is_admin`, `status`) VALUES
(3, 'xali', '$2y$10$/NLh6o1wo5up/tlYlvBIN.Z3uG4M3svGtHTSgj6Rt3K601quPyKUS', '112326748375285415981', 'https://lh3.googleusercontent.com/a/ACg8ocIlCuXYXca8gBf8Ys-a_nlqCUtIF1O5yGbPEsUm2qsFkGYafZ41=s96-c', 'dubu2k4@gmail.com', 'Trần Thanh Thưởng', '0392656499', 'Cầu Ngang trà vinh', '2024-12-04 17:49:41', 0, 1),
(4, 'ss', '$2y$10$8egLwGoW1DE9HKJBVeCKT.QitqaKinmIhSqNr5mZ7.4XDixfMokq6', NULL, NULL, 'dubu2k@gmail.com', 'Trần Thanh Thưởng', '0392656499', 'dsdsd', '2024-12-04 17:59:57', 0, 1),
(5, 'dihi', '$2y$10$lWgkTUKS1fedQ2vR8tjQiOws6g9nTgxfgnYe93ORh6NKXq7SuN9JG', NULL, NULL, 'nguyena@gmail.com', 'Nguyễn Văn A', '0987876552', 'Cầu Ngang trà vinh', '2025-11-21 04:31:29', 0, 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `wishlist`
--

CREATE TABLE `wishlist` (
  `wishlist_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` varchar(10) NOT NULL,
  `added_date` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `wishlist`
--

INSERT INTO `wishlist` (`wishlist_id`, `user_id`, `product_id`, `added_date`) VALUES
(2, 3, '013', '2025-11-23 21:48:03'),
(3, 3, '012', '2025-11-25 13:14:57');

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Chỉ mục cho bảng `administrators`
--
ALTER TABLE `administrators`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Chỉ mục cho bảng `admin_permissions`
--
ALTER TABLE `admin_permissions`
  ADD PRIMARY KEY (`admin_id`,`permission_id`),
  ADD KEY `permission_id` (`permission_id`);

--
-- Chỉ mục cho bảng `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`cart_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Chỉ mục cho bảng `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`);

--
-- Chỉ mục cho bảng `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`message_id`),
  ADD KEY `idx_sender` (`sender_id`,`sender_type`),
  ADD KEY `idx_receiver` (`receiver_id`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Chỉ mục cho bảng `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Chỉ mục cho bảng `order_details`
--
ALTER TABLE `order_details`
  ADD PRIMARY KEY (`order_detail_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Chỉ mục cho bảng `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`permission_id`),
  ADD UNIQUE KEY `permission_name` (`permission_name`);

--
-- Chỉ mục cho bảng `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Chỉ mục cho bảng `promotions`
--
ALTER TABLE `promotions`
  ADD PRIMARY KEY (`promotion_id`),
  ADD UNIQUE KEY `promotion_code` (`promotion_code`),
  ADD KEY `idx_code` (`promotion_code`),
  ADD KEY `idx_type` (`promotion_type`),
  ADD KEY `idx_dates` (`start_date`,`end_date`),
  ADD KEY `idx_status` (`status`);

--
-- Chỉ mục cho bảng `promotion_categories`
--
ALTER TABLE `promotion_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_promo_category` (`promotion_id`,`category_id`);

--
-- Chỉ mục cho bảng `promotion_products`
--
ALTER TABLE `promotion_products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_promo_product` (`promotion_id`,`product_id`);

--
-- Chỉ mục cho bảng `promotion_usage`
--
ALTER TABLE `promotion_usage`
  ADD PRIMARY KEY (`id`),
  ADD KEY `promotion_id` (`promotion_id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_order` (`order_id`);

--
-- Chỉ mục cho bảng `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`review_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Chỉ mục cho bảng `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `google_id_UNIQUE` (`google_id`);

--
-- Chỉ mục cho bảng `wishlist`
--
ALTER TABLE `wishlist`
  ADD PRIMARY KEY (`wishlist_id`),
  ADD UNIQUE KEY `unique_wishlist` (`user_id`,`product_id`),
  ADD KEY `product_id` (`product_id`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT cho bảng `administrators`
--
ALTER TABLE `administrators`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `cart`
--
ALTER TABLE `cart`
  MODIFY `cart_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT cho bảng `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT cho bảng `messages`
--
ALTER TABLE `messages`
  MODIFY `message_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT cho bảng `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT cho bảng `order_details`
--
ALTER TABLE `order_details`
  MODIFY `order_detail_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT cho bảng `permissions`
--
ALTER TABLE `permissions`
  MODIFY `permission_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `promotions`
--
ALTER TABLE `promotions`
  MODIFY `promotion_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT cho bảng `promotion_categories`
--
ALTER TABLE `promotion_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `promotion_products`
--
ALTER TABLE `promotion_products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `promotion_usage`
--
ALTER TABLE `promotion_usage`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `reviews`
--
ALTER TABLE `reviews`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT cho bảng `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT cho bảng `wishlist`
--
ALTER TABLE `wishlist`
  MODIFY `wishlist_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `administrators` (`admin_id`);

--
-- Các ràng buộc cho bảng `admin_permissions`
--
ALTER TABLE `admin_permissions`
  ADD CONSTRAINT `admin_permissions_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `administrators` (`admin_id`),
  ADD CONSTRAINT `admin_permissions_ibfk_2` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`permission_id`);

--
-- Các ràng buộc cho bảng `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`);

--
-- Các ràng buộc cho bảng `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Các ràng buộc cho bảng `order_details`
--
ALTER TABLE `order_details`
  ADD CONSTRAINT `order_details_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`),
  ADD CONSTRAINT `order_details_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`);

--
-- Các ràng buộc cho bảng `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`);

--
-- Các ràng buộc cho bảng `promotion_categories`
--
ALTER TABLE `promotion_categories`
  ADD CONSTRAINT `promotion_categories_ibfk_1` FOREIGN KEY (`promotion_id`) REFERENCES `promotions` (`promotion_id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `promotion_products`
--
ALTER TABLE `promotion_products`
  ADD CONSTRAINT `promotion_products_ibfk_1` FOREIGN KEY (`promotion_id`) REFERENCES `promotions` (`promotion_id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `promotion_usage`
--
ALTER TABLE `promotion_usage`
  ADD CONSTRAINT `promotion_usage_ibfk_1` FOREIGN KEY (`promotion_id`) REFERENCES `promotions` (`promotion_id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`);

--
-- Các ràng buộc cho bảng `wishlist`
--
ALTER TABLE `wishlist`
  ADD CONSTRAINT `wishlist_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `wishlist_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
