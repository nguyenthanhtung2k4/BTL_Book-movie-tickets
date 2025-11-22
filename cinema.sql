-- ============================================
-- CẤU TRÚC DATABASE HOÀN CHỈNH - CHUẨN 3NF
-- ============================================
-- File: cinema_complete_3nf.sql
-- Mô tả: Database hoàn chỉnh với bảng seats và seat_types (3NF)
-- Ngày tạo: 2025-11-05
-- ============================================

CREATE DATABASE IF NOT EXISTS `cinema` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `cinema`;

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

-- ============================================
-- BẢNG: users
-- ============================================

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `full_name` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role` enum('customer','staff','admin') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'customer',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- BẢNG: theaters
-- ============================================

DROP TABLE IF EXISTS `theaters`;
CREATE TABLE `theaters` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `address` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_city` (`city`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- BẢNG: movies
-- ============================================

DROP TABLE IF EXISTS `movies`;
CREATE TABLE `movies` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `duration_min` smallint unsigned NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `rating` varchar(16) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `release_date` date DEFAULT NULL,
  `banner_url` varchar(512) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `trailer_url` varchar(512) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_title` (`title`(100))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- BẢNG: screens (ĐÃ XÓA CỘT seat_layout)
-- ============================================

DROP TABLE IF EXISTS `screens`;
CREATE TABLE `screens` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `theater_id` int unsigned NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `capacity` smallint unsigned DEFAULT NULL,
  `screen_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT '2D',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_theater_id` (`theater_id`),
  CONSTRAINT `fk_screens_theaters` FOREIGN KEY (`theater_id`) REFERENCES `theaters` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- BẢNG: seat_types (MỚI - ĐỂ ĐẠT 3NF)
-- ============================================

DROP TABLE IF EXISTS `seat_types`;
CREATE TABLE `seat_types` (
  `id` tinyint unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Mã loại ghế (standard, vip, disabled, aisle)',
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Tên loại ghế',
  `name_vi` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Tên tiếng Việt',
  `price_modifier` decimal(5,2) NOT NULL DEFAULT '1.00' COMMENT 'Hệ số giá (1.0 = giá gốc, 1.5 = x1.5, 0.8 = x0.8)',
  `color_code` varchar(7) COLLATE utf8mb4_unicode_ci DEFAULT '#3b82f6' COMMENT 'Mã màu hex cho UI',
  `description` text COLLATE utf8mb4_unicode_ci COMMENT 'Mô tả loại ghế',
  `is_bookable` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Có thể đặt được không (0 = không, 1 = có)',
  `display_order` tinyint unsigned NOT NULL DEFAULT '0' COMMENT 'Thứ tự hiển thị',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- BẢNG: seats (MỚI - THAY THẾ JSON)
-- ============================================

DROP TABLE IF EXISTS `seats`;
CREATE TABLE `seats` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `screen_id` int unsigned NOT NULL,
  `row_letter` varchar(2) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Hàng ghế (A, B, C...)',
  `seat_number` smallint unsigned NOT NULL COMMENT 'Số ghế trong hàng (1, 2, 3...)',
  `seat_code` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Mã ghế (A1, A2, B5...)',
  `seat_type_id` tinyint unsigned NOT NULL COMMENT 'ID loại ghế (FK)',
  `position_order` smallint unsigned NOT NULL COMMENT 'Thứ tự trong hàng (1, 2, 3...)',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_screen_seat_code` (`screen_id`, `seat_code`),
  KEY `idx_screen_id` (`screen_id`),
  KEY `idx_seat_code` (`seat_code`),
  KEY `idx_seat_type_id` (`seat_type_id`),
  KEY `idx_row_seat` (`screen_id`, `row_letter`, `seat_number`),
  CONSTRAINT `fk_seats_screens` FOREIGN KEY (`screen_id`) REFERENCES `screens` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_seats_seat_types` FOREIGN KEY (`seat_type_id`) REFERENCES `seat_types` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- BẢNG: shows
-- ============================================

DROP TABLE IF EXISTS `shows`;
CREATE TABLE `shows` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `movie_id` int unsigned NOT NULL,
  `screen_id` int unsigned NOT NULL,
  `show_time` datetime NOT NULL,
  `format` enum('2D','3D','IMAX','4DX') COLLATE utf8mb4_unicode_ci DEFAULT '2D',
  `price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'scheduled',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_movie_id` (`movie_id`),
  KEY `idx_screen_time` (`screen_id`,`show_time`),
  CONSTRAINT `fk_shows_movies` FOREIGN KEY (`movie_id`) REFERENCES `movies` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_shows_screens` FOREIGN KEY (`screen_id`) REFERENCES `screens` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- BẢNG: bookings
-- ============================================

DROP TABLE IF EXISTS `bookings`;
CREATE TABLE `bookings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned DEFAULT NULL,
  `show_id` bigint unsigned NOT NULL,
  `status` enum('pending','confirmed','cancelled','expired') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `total_amount` decimal(15,0) NOT NULL DEFAULT '0',
  `payment_method` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_status` enum('unpaid','paid','refunded') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'unpaid',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_show_id` (`show_id`),
  CONSTRAINT `fk_bookings_shows` FOREIGN KEY (`show_id`) REFERENCES `shows` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_bookings_users` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- BẢNG: booking_items
-- ============================================

DROP TABLE IF EXISTS `booking_items`;
CREATE TABLE `booking_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `booking_id` bigint unsigned NOT NULL,
  `show_id` bigint unsigned NOT NULL,
  `seat_code` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ticket_price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `ticket_type` enum('adult','child','senior','student') COLLATE utf8mb4_unicode_ci DEFAULT 'adult',
  `status` enum('booked','cancelled','checked_in') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'booked',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_show_seat` (`show_id`,`seat_code`),
  KEY `idx_booking_id` (`booking_id`),
  KEY `idx_show_id` (`show_id`),
  CONSTRAINT `fk_bitems_bookings` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_bitems_shows` FOREIGN KEY (`show_id`) REFERENCES `shows` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- INSERT DỮ LIỆU MẪU
-- ============================================

LOCK TABLES `users` WRITE;
INSERT INTO `users` (`email`, `password_hash`, `full_name`, `role`) VALUES
('admin@gmail.com', '$2y$10$FohbkrrJuRoBta0YOxzQquPVEDJv1OqlJOCmVNLDe7iDvdyBz9RC.', 'Admin User', 'admin');
UNLOCK TABLES;

LOCK TABLES `theaters` WRITE;
INSERT INTO `theaters` (`name`, `address`, `city`, `phone`) VALUES
('CGV Central', '123 Nguyễn Huệ, Quận 1', 'Ho Chi Minh City', '0281234567');
UNLOCK TABLES;

LOCK TABLES `movies` WRITE;
INSERT INTO `movies` (`title`, `duration_min`, `description`, `rating`, `release_date`, `banner_url`, `trailer_url`) VALUES
('Avengers: Endgame', 181, 'Phim siêu anh hùng Marvel', '9.2', '2025-11-15', 'https://example.com/banner.jpg', 'https://youtu.be/example');
UNLOCK TABLES;

LOCK TABLES `screens` WRITE;
INSERT INTO `screens` (`theater_id`, `name`, `capacity`, `screen_type`) VALUES
(1, 'Phòng 1', 50, '2D');
UNLOCK TABLES;

LOCK TABLES `seat_types` WRITE;
INSERT INTO `seat_types` (`code`, `name`, `name_vi`, `price_modifier`, `color_code`, `description`, `is_bookable`, `display_order`) VALUES
('standard', 'Standard Seat', 'Ghế Thường', 1.00, '#3b82f6', 'Ghế tiêu chuẩn với giá cơ bản', 1, 1),
('vip', 'VIP Seat', 'Ghế VIP', 1.50, '#f59e0b', 'Ghế VIP với không gian thoải mái hơn, giá cao hơn 50%', 1, 2),
('disabled', 'Disabled Seat', 'Ghế Người Khuyết Tật', 0.80, '#10b981', 'Ghế dành riêng cho người khuyết tật, giá ưu đãi 20%', 1, 3),
('aisle', 'Aisle', 'Lối Đi', 0.00, '#1f2937', 'Vị trí lối đi, không thể đặt', 0, 4);
UNLOCK TABLES;

LOCK TABLES `seats` WRITE;
-- Ví dụ: Tạo 10 ghế cho hàng A (screen_id = 1, seat_type_id = 1 là standard)
INSERT INTO `seats` (`screen_id`, `row_letter`, `seat_number`, `seat_code`, `seat_type_id`, `position_order`) VALUES
(1, 'A', 1, 'A1', 1, 1),
(1, 'A', 2, 'A2', 1, 2),
(1, 'A', 3, 'A3', 1, 3),
(1, 'A', 4, 'A4', 1, 4),
(1, 'A', 5, 'A5', 1, 5),
(1, 'A', 6, 'A6', 1, 6),
(1, 'A', 7, 'A7', 1, 7),
(1, 'A', 8, 'A8', 1, 8),
(1, 'A', 9, 'A9', 1, 9),
(1, 'A', 10, 'A10', 1, 10);
UNLOCK TABLES;

LOCK TABLES `shows` WRITE;
INSERT INTO `shows` (`movie_id`, `screen_id`, `show_time`, `format`, `price`, `status`) VALUES
(1, 1, '2025-11-20 19:00:00', '2D', 100000.00, 'scheduled');
UNLOCK TABLES;

LOCK TABLES `bookings` WRITE;
INSERT INTO `bookings` (`user_id`, `show_id`, `status`, `total_amount`, `payment_method`, `payment_status`) VALUES
(1, 1, 'pending', 200000, 'cash', 'unpaid');
UNLOCK TABLES;

LOCK TABLES `booking_items` WRITE;
INSERT INTO `booking_items` (`booking_id`, `show_id`, `seat_code`, `ticket_price`, `ticket_type`, `status`) VALUES
(1, 1, 'A1', 100000.00, 'adult', 'booked'),
(1, 1, 'A2', 100000.00, 'adult', 'booked');
UNLOCK TABLES;

-- ============================================
-- THÊM DỮ LIỆU MẪU: Đơn hàng đã thanh toán cho 4 tháng gần đây
-- Mục đích: Tăng dữ liệu cho biểu đồ "đơn hàng đã thanh toán theo tháng"
-- Ghi chú: Các đơn hàng này cùng tham chiếu show_id = 1
-- ============================================

LOCK TABLES `bookings` WRITE;
INSERT INTO `bookings` (
  `user_id`, `show_id`, `status`, `total_amount`, `payment_method`, `payment_status`, `created_at`, `updated_at`
) VALUES
-- Tháng 7 (4 tháng trước kể từ 2025-11)
(1, 1, 'confirmed', 200000, 'card', 'paid', '2025-07-18 19:30:00', '2025-07-18 19:30:00'),
-- Tháng 8
(1, 1, 'confirmed', 300000, 'cash', 'paid', '2025-08-10 18:15:00', '2025-08-10 18:15:00'),
-- Tháng 9
(1, 1, 'confirmed', 250000, 'card', 'paid', '2025-09-12 20:05:00', '2025-09-12 20:05:00'),
-- Tháng 10
(1, 1, 'confirmed', 150000, 'cash', 'paid', '2025-10-15 15:45:00', '2025-10-15 15:45:00');
UNLOCK TABLES;

-- Nếu muốn hiển thị thêm tổng vé đã bán, có thể thêm booking_items tương ứng
-- (lưu ý ràng buộc UNIQUE (show_id, seat_code) nên mỗi ghế chỉ xuất hiện một lần trên một show)
-- Ví dụ: phân bổ các ghế còn lại cho các đơn đã thanh toán
LOCK TABLES `booking_items` WRITE;
-- Gắn vé cho các đơn "paid" bằng cách tham chiếu theo thời gian tạo (mỗi lệnh INSERT 1 dòng)
INSERT INTO `booking_items` (`booking_id`, `show_id`, `seat_code`, `ticket_price`, `ticket_type`, `status`)
SELECT b.id, 1, 'A3', 100000.00, 'adult', 'booked' FROM bookings b WHERE b.created_at = '2025-07-18 19:30:00' LIMIT 1;
INSERT INTO `booking_items` (`booking_id`, `show_id`, `seat_code`, `ticket_price`, `ticket_type`, `status`)
SELECT b.id, 1, 'A4', 100000.00, 'adult', 'booked' FROM bookings b WHERE b.created_at = '2025-07-18 19:30:00' LIMIT 1;

INSERT INTO `booking_items` (`booking_id`, `show_id`, `seat_code`, `ticket_price`, `ticket_type`, `status`)
SELECT b.id, 1, 'A5', 100000.00, 'adult', 'booked' FROM bookings b WHERE b.created_at = '2025-08-10 18:15:00' LIMIT 1;
INSERT INTO `booking_items` (`booking_id`, `show_id`, `seat_code`, `ticket_price`, `ticket_type`, `status`)
SELECT b.id, 1, 'A6', 100000.00, 'adult', 'booked' FROM bookings b WHERE b.created_at = '2025-08-10 18:15:00' LIMIT 1;

INSERT INTO `booking_items` (`booking_id`, `show_id`, `seat_code`, `ticket_price`, `ticket_type`, `status`)
SELECT b.id, 1, 'A7', 100000.00, 'adult', 'booked' FROM bookings b WHERE b.created_at = '2025-09-12 20:05:00' LIMIT 1;
INSERT INTO `booking_items` (`booking_id`, `show_id`, `seat_code`, `ticket_price`, `ticket_type`, `status`)
SELECT b.id, 1, 'A8', 100000.00, 'adult', 'booked' FROM bookings b WHERE b.created_at = '2025-09-12 20:05:00' LIMIT 1;

INSERT INTO `booking_items` (`booking_id`, `show_id`, `seat_code`, `ticket_price`, `ticket_type`, `status`)
SELECT b.id, 1, 'A9', 100000.00, 'adult', 'booked' FROM bookings b WHERE b.created_at = '2025-10-15 15:45:00' LIMIT 1;
INSERT INTO `booking_items` (`booking_id`, `show_id`, `seat_code`, `ticket_price`, `ticket_type`, `status`)
SELECT b.id, 1, 'A10', 100000.00, 'adult', 'booked' FROM bookings b WHERE b.created_at = '2025-10-15 15:45:00' LIMIT 1;
UNLOCK TABLES;

-- ============================================
-- HOÀN TẤT
-- ============================================

/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- ============================================
-- KIỂM TRA DỮ LIỆU
-- ============================================

-- Kiểm tra các bảng đã tạo
SELECT 
    TABLE_NAME,
    TABLE_ROWS
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = 'cinema'
ORDER BY TABLE_NAME;

-- Kiểm tra seat_types
SELECT * FROM seat_types ORDER BY display_order;

-- Kiểm tra seats
SELECT s.*, st.code AS seat_type_code, st.name_vi 
FROM seats s 
INNER JOIN seat_types st ON s.seat_type_id = st.id 
LIMIT 10;

