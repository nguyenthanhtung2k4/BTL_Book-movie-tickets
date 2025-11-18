CREATE DATABASE  IF NOT EXISTS `cinema` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `cinema`;
-- MySQL dump 10.13  Distrib 8.0.43, for Win64 (x86_64)
--
-- Host: localhost    Database: cinema
-- ------------------------------------------------------
-- Server version	8.0.43

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

--
-- Table structure for table `booking_items`
--

DROP TABLE IF EXISTS `booking_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `booking_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `booking_id` bigint unsigned NOT NULL,
  `show_id` bigint unsigned NOT NULL,
  `seat_code` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `ticket_price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `ticket_type` enum('adult','child','senior','student') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'adult',
  `status` enum('booked','cancelled','checked_in') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'booked',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_show_seat` (`show_id`,`seat_code`),
  KEY `idx_booking_id` (`booking_id`),
  KEY `idx_show_id` (`show_id`),
  CONSTRAINT `fk_bitems_bookings` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_bitems_shows` FOREIGN KEY (`show_id`) REFERENCES `shows` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `booking_items`
--

LOCK TABLES `booking_items` WRITE;
/*!40000 ALTER TABLE `booking_items` DISABLE KEYS */;
INSERT INTO `booking_items` VALUES (1,1,5,'A2',20000.00,'adult','cancelled','2025-11-03 16:13:47'),(2,1,5,'A3',12000.00,'adult','booked','2025-11-03 16:42:45'),(3,24,5,'C5',3000000.00,'adult','booked','2025-11-04 16:21:52'),(4,24,5,'C4',3000000.00,'adult','booked','2025-11-04 16:21:52'),(5,24,5,'C6',3000000.00,'adult','booked','2025-11-04 16:21:52'),(6,25,9,'A1',300000.00,'adult','booked','2025-11-04 17:08:44'),(7,25,9,'A2',300000.00,'adult','booked','2025-11-04 17:08:44'),(8,25,9,'A3',300000.00,'adult','booked','2025-11-04 17:08:44'),(9,26,12,'B4',400000.00,'adult','booked','2025-11-07 00:43:05'),(10,27,12,'C7',600000.00,'adult','booked','2025-11-07 00:44:06');
/*!40000 ALTER TABLE `booking_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bookings`
--

DROP TABLE IF EXISTS `bookings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `bookings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned DEFAULT NULL,
  `show_id` bigint unsigned NOT NULL,
  `status` enum('pending','confirmed','cancelled','expired') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `total_amount` decimal(15,0) NOT NULL DEFAULT '0',
  `payment_method` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_status` enum('unpaid','paid','refunded') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'unpaid',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_show_id` (`show_id`),
  CONSTRAINT `fk_bookings_shows` FOREIGN KEY (`show_id`) REFERENCES `shows` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_bookings_users` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bookings`
--

LOCK TABLES `bookings` WRITE;
/*!40000 ALTER TABLE `bookings` DISABLE KEYS */;
INSERT INTO `bookings` VALUES (1,5,5,'pending',10000,NULL,'unpaid','2025-11-03 16:08:48','2025-11-03 16:08:48'),(2,6,5,'pending',20000,NULL,'unpaid','2025-11-03 16:43:44','2025-11-03 16:43:44'),(24,NULL,5,'pending',9000000,'cash','unpaid','2025-11-04 16:21:52','2025-11-04 16:21:52'),(25,1,9,'pending',900000,'cash','unpaid','2025-11-04 17:08:44','2025-11-04 17:08:44'),(26,2,12,'confirmed',400000,'vnpay','paid','2025-11-07 00:43:05','2025-11-07 00:43:05'),(27,16,12,'confirmed',600000,'vnpay','paid','2025-11-07 00:44:06','2025-11-07 00:44:06');
/*!40000 ALTER TABLE `bookings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `movies`
--

DROP TABLE IF EXISTS `movies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `movies` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `duration_min` smallint unsigned NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `rating` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `release_date` date DEFAULT NULL,
  `banner_url` varchar(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `trailer_url` varchar(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_title` (`title`(100))
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `movies`
--

LOCK TABLES `movies` WRITE;
/*!40000 ALTER TABLE `movies` DISABLE KEYS */;
INSERT INTO `movies` VALUES (5,'Bịt mắt bắt nai',92,'<p>bịt mắt bắt nai</p>','10','2025-11-03','https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRT2_YoNuuffm0uGLRSir-JAropYzzRKkxbow&s','https://youtu.be/ABdyHbWAPIQ','2025-11-02 07:32:55','2025-11-03 21:17:18'),(6,'Cục vàng của ngoại',1120,'<p>56565erfgtrh55</p>','4.8','2025-11-21','https://cdn2.tuoitre.vn/471584752817336320/2025/8/15/base64-17552499170051860046577.jpeg','https://www.cgv.vn/media/catalog/product/cache/1/image/1800x/71252117777b696995f01934522c402d/4/7/470wx700h-cvcn_1.jpg','2025-11-02 21:10:26','2025-11-03 21:17:34'),(7,'Nhà ma xó',108,'<p>Nhà ma xó</p><p>&nbsp;</p>','8.5','2025-11-03','https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTXr42uQhpF9UQc6wQdqWx64xKfn-PL5MuChQ&s','https://youtu.be/8F7SyR3fJ0M','2025-11-03 21:18:28','2025-11-03 21:19:09'),(8,'Phá đám sinh nhật mẹ',91,'','10','2025-11-03','https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcR8v-6SgQN8TvhIFhbJQAcmj9a1O5tqBSqfUg&s','https://youtu.be/dBsJYwaBbLA','2025-11-03 21:20:54','2025-11-03 21:20:54'),(9,'Tử Chiến Trên Không',118,'','10','2025-11-30','https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRS7MuqULl99IBalybM1DM-5p8_0py9jCgFcw&s','https://youtu.be/iJ6lKh698Js','2025-11-03 21:22:26','2025-11-03 21:24:09'),(10,'Kinh Dị Nhật Vị',80,'<p>Kinh Dị Nhật Vị</p><p>&nbsp;</p>','8.5','2025-11-03','https://dcine.vn/Areas/Admin/Content/Fileuploads/images/kinhdinhatviPoster.jpg','https://youtu.be/W4_1gMme7EI','2025-11-03 21:25:16','2025-11-03 21:25:16'),(11,'Suất chiếu đặc biệt',120,'','10','2025-12-01','https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQ7ot-5DbXw8HpbBj9TcdpG9CQc_nzDqVJz6Q&s','https://youtu.be/8sriHDDQEQM','2025-11-03 21:29:10','2025-11-03 21:29:10'),(12,'Mưa đỏ',120,'','9','2025-09-02','https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRQJVVqXjyw7BKAo0JKaguSCevyS6qZ8HToyWc4zF5xVfbv-xOzy0w8Tw8KKC7-5j-c3NQ&usqp=CAU','https://youtu.be/UEqjUBGjvwI','2025-11-03 21:30:55','2025-11-03 21:31:47'),(13,'Mắt Biếc',90,'<p>Phim Hay Demo Mỗi Ngày</p>','10','2025-11-04','https://i.pinimg.com/736x/f8/44/e4/f844e41e2a5376b9907175d0a22d63e0.jpg','https://youtu.be/2DPi23pQZeQ?si=BxeMilBcKW2BCnPY','2025-11-05 13:35:28','2025-11-05 13:35:28'),(14,'Người Vợ Cuối Cùng',123,'<p>Phim Hay Demo Mỗi Ngày</p>','10','2025-11-04','https://i.pinimg.com/1200x/8d/87/71/8d87715f3afb9ca3e65198ca2dcafaff.jpg','https://youtu.be/3GijLb3sHUo?si=Q48cazcxP6rDy7-S','2025-11-05 13:36:59','2025-11-05 13:40:24'),(15,'Thám Tử Kiên',132,'<p>Phim Hay Demo Mỗi Ngày</p>','10','2025-10-28','https://i.pinimg.com/1200x/8e/06/32/8e0632150daa57dd45c7f6944be8507d.jpg','https://youtu.be/x1XxbJGtNB0?si=tPMVEhNcoOkYKlSm','2025-11-05 13:39:02','2025-11-05 13:39:02');
/*!40000 ALTER TABLE `movies` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `screens`
--

DROP TABLE IF EXISTS `screens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `screens` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `theater_id` int unsigned NOT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `capacity` smallint unsigned DEFAULT NULL,
  `screen_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '2D',
  `seat_layout` json DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_theater_id` (`theater_id`),
  CONSTRAINT `fk_screens_theaters` FOREIGN KEY (`theater_id`) REFERENCES `theaters` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `screens`
--

LOCK TABLES `screens` WRITE;
/*!40000 ALTER TABLE `screens` DISABLE KEYS */;
INSERT INTO `screens` VALUES (2,8,'jusst',44,'3D','{\"rows_count\": 3, \"layout_details\": [{\"row\": \"A\", \"type\": \"standard\", \"seats\": 11, \"seat_data\": [\"standard\", \"standard\", \"standard\", \"standard\", \"standard\", \"standard\", \"standard\", \"standard\", \"standard\", \"standard\", \"standard\"]}, {\"row\": \"B\", \"type\": \"standard\", \"seats\": 11, \"seat_data\": [\"standard\", \"standard\", \"standard\", \"standard\", \"standard\", \"standard\", \"standard\", \"standard\", \"standard\", \"standard\", \"standard\"]}, {\"row\": \"C\", \"type\": \"standard\", \"seats\": 11, \"seat_data\": [\"standard\", \"vip\", \"vip\", \"vip\", \"disabled\", \"disabled\", \"vip\", \"vip\", \"vip\", \"standard\", \"standard\"]}, {\"row\": \"D\", \"seats\": 11, \"seat_data\": [\"standard\", \"standard\", \"standard\", \"vip\", \"vip\", \"vip\", \"vip\", \"standard\", \"standard\", \"standard\", \"standard\"]}], \"total_capacity\": 44}','2025-11-03 09:30:12','2025-11-03 04:01:15'),(3,7,'5675675',50,'2D','{\"rows_count\": 0, \"layout_details\": [{\"row\": \"A\", \"seats\": 10, \"seat_data\": [\"standard\", \"standard\", \"standard\", \"standard\", \"standard\", \"standard\", \"standard\", \"standard\", \"standard\", \"standard\"]}, {\"row\": \"B\", \"seats\": 10, \"seat_data\": [\"standard\", \"standard\", \"standard\", \"standard\", \"standard\", \"standard\", \"standard\", \"standard\", \"standard\", \"standard\"]}, {\"row\": \"C\", \"seats\": 10, \"seat_data\": [\"standard\", \"standard\", \"standard\", \"standard\", \"standard\", \"standard\", \"standard\", \"standard\", \"standard\", \"standard\"]}, {\"row\": \"D\", \"seats\": 10, \"seat_data\": [\"standard\", \"standard\", \"standard\", \"standard\", \"standard\", \"standard\", \"standard\", \"standard\", \"standard\", \"standard\"]}, {\"row\": \"E\", \"seats\": 10, \"seat_data\": [\"standard\", \"standard\", \"vip\", \"disabled\", \"disabled\", \"disabled\", \"standard\", \"standard\", \"standard\", \"standard\"]}], \"total_capacity\": 50}','2025-11-03 09:30:36','2025-11-04 10:31:29'),(4,7,'LanCuoi',50,'IMAX','{\"rows_count\": 5, \"layout_details\": [{\"row\": \"A\", \"type\": \"standard\", \"seats\": 10, \"seat_data\": [\"standard\", \"standard\", \"standard\", \"standard\", \"standard\", \"standard\", \"standard\", \"standard\", \"standard\", \"standard\"]}, {\"row\": \"B\", \"type\": \"standard\", \"seats\": 10, \"seat_data\": [\"standard\", \"standard\", \"standard\", \"standard\", \"standard\", \"standard\", \"standard\", \"standard\", \"standard\", \"standard\"]}, {\"row\": \"C\", \"type\": \"standard\", \"seats\": 10, \"seat_data\": [\"standard\", \"standard\", \"standard\", \"standard\", \"standard\", \"standard\", \"standard\", \"standard\", \"standard\", \"standard\"]}, {\"row\": \"D\", \"type\": \"standard\", \"seats\": 10, \"seat_data\": [\"standard\", \"standard\", \"standard\", \"standard\", \"vip\", \"vip\", \"vip\", \"standard\", \"standard\", \"standard\"]}, {\"row\": \"E\", \"type\": \"standard\", \"seats\": 10, \"seat_data\": [\"standard\", \"standard\", \"disabled\", \"disabled\", \"disabled\", \"disabled\", \"vip\", \"standard\", \"standard\", \"standard\"]}], \"total_capacity\": 50}','2025-11-03 09:32:23','2025-11-04 10:21:24');
/*!40000 ALTER TABLE `screens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `shows`
--

DROP TABLE IF EXISTS `shows`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `shows` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `movie_id` int unsigned NOT NULL,
  `screen_id` int unsigned NOT NULL,
  `show_time` datetime NOT NULL,
  `format` enum('2D','3D','IMAX','4DX') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '2D',
  `price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'scheduled',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_movie_id` (`movie_id`),
  KEY `idx_screen_time` (`screen_id`,`show_time`),
  CONSTRAINT `fk_shows_movies` FOREIGN KEY (`movie_id`) REFERENCES `movies` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_shows_screens` FOREIGN KEY (`screen_id`) REFERENCES `screens` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `shows`
--

LOCK TABLES `shows` WRITE;
/*!40000 ALTER TABLE `shows` DISABLE KEYS */;
INSERT INTO `shows` VALUES (5,5,4,'2025-11-04 22:56:00','IMAX',3000000.00,'upcoming','2025-11-03 08:57:04','2025-11-04 09:10:26'),(6,5,4,'2025-11-04 17:56:00','IMAX',1000000.00,'upcoming','2025-11-03 08:59:35','2025-11-04 05:20:04'),(7,6,3,'2025-11-05 21:12:00','2D',30000000.00,'upcoming','2025-11-03 09:12:44','2025-11-04 05:20:15'),(8,5,2,'2025-11-04 20:23:00','3D',30000.00,'upcoming','2025-11-04 05:24:14','2025-11-04 05:24:14'),(9,5,4,'2025-11-05 11:24:00','3D',300000.00,'upcoming','2025-11-04 05:25:09','2025-11-04 05:25:09'),(10,5,2,'2025-11-05 17:46:00','IMAX',5000000.00,'active','2025-11-05 07:44:11','2025-11-05 07:44:11'),(11,13,3,'2025-11-05 17:45:00','3D',500000.00,'active','2025-11-05 07:45:33','2025-11-05 07:45:33'),(12,13,2,'2025-11-08 00:42:00','IMAX',400000.00,'active','2025-11-06 18:42:44','2025-11-06 18:42:44');
/*!40000 ALTER TABLE `shows` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `theaters`
--

DROP TABLE IF EXISTS `theaters`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `theaters` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `address` varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_city` (`city`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `theaters`
--

LOCK TABLES `theaters` WRITE;
/*!40000 ALTER TABLE `theaters` DISABLE KEYS */;
INSERT INTO `theaters` VALUES (7,'LOTE','67 Willow Drive','34543','0985332251','2025-11-02 07:27:40','2025-11-04 10:22:42'),(8,'CGV','67 Willow Drive','Hanoi','645756','2025-11-02 10:11:06','2025-11-04 10:22:31'),(9,'Beta','65464646646','Hanoi','0985332251457','2025-11-02 15:09:01','2025-11-04 10:22:25');
/*!40000 ALTER TABLE `theaters` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `full_name` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role` enum('customer','staff','admin') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'customer',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'golike292004@gmail.com','$2y$10$.y4zud8CDLYW6Mk82O9S/.Mw.cLsT7bygy47C2EsDPCwlWVJ479g6','thanhtung',NULL,'customer','2025-10-29 10:25:52','2025-11-04 17:01:05'),(2,'admin@gmail.com','$2y$10$FohbkrrJuRoBta0YOxzQquPVEDJv1OqlJOCmVNLDe7iDvdyBz9RC.','admin',NULL,'admin','2025-10-29 10:26:11','2025-10-29 10:26:11'),(3,'t@gmail.com','$2y$10$EwQ5emcqpq/jpFk7LdBRvuFsTsj85NG4/F9v0j1mzWa1e9ZGWq2zG','7678',NULL,'customer','2025-11-02 13:56:06','2025-11-02 13:56:06'),(4,'golike2920041@gmail.com','$2y$10$/nnBbdWFhwNriNlpDFMIl.KQ6wxGk4CseAHz3VN8lE.aQf0Lw7K4G','egerhrh',NULL,'customer','2025-11-02 08:15:50','2025-11-02 14:15:51'),(5,'golike29200441@gmail.com','$2y$10$0gC0qqetIaRF2.5/0BUeHeVX.PNvYVATBQjLdMxcR8dyPs7A4fkRS','egerhrh',NULL,'customer','2025-11-02 08:18:40','2025-11-02 14:18:40'),(6,'tung@gmail.com','$2y$10$C0Ccgl/iFqh/A2dSbqmTJujLxd.BSdlktLv72sFS1EHTmskJhNESK','tung',NULL,'customer','2025-11-02 08:20:58','2025-11-02 14:20:58'),(7,'hoangsontung2k4@gmail.com','$2y$10$Ak0Z5I.3I9FNcdSfafPvPO/nASiWnHYgb.ybVFGnZBNuW6E.nrA1S','7678',NULL,'customer','2025-11-02 08:28:25','2025-11-02 14:28:25'),(8,'golike2r920041@gmail.com','$2y$10$uuPmHl6ZrccHsbiP.USbOuW7pObKZ23I9x1N2eVZmbl6keuoVx0q2','7678',NULL,'customer','2025-11-02 08:31:10','2025-11-02 14:31:10'),(9,'golike2925004@gmail.com','$2y$10$PBfdbPmli6XpCBhTABd9QOFRWABWAmNAuyER4bYkg7t0nOtZvpa92','thanhtung',NULL,'customer','2025-11-02 16:11:50','2025-11-02 16:11:50'),(10,'sdt@gmail.com','$2y$10$UBCwENZwIbw3v6uBn5IvQOORxbtZXh33GlS1VAriPTsNpQkbFNZDK','thanhtung',NULL,'customer','2025-11-02 16:13:31','2025-11-02 16:13:31'),(15,'admin123@gmail.com','$2y$10$.y4zud8CDLYW6Mk82O9S/.Mw.cLsT7bygy47C2EsDPCwlWVJ479g6','admin1234',NULL,'admin','2025-11-04 16:50:24','2025-11-04 16:51:15'),(16,'dovantuyen.2005nb@gmail.com','$2y$10$drgcV7n739ohnpNZTlz0yOyDVuG8lY6Gxr/2hfS85x1vipwfS5hD6','Do Van Tuyen',NULL,'customer','2025-11-05 13:24:05','2025-11-05 13:24:05'),(17,'adkey@gmail.com','$2y$10$86CwO.6vphp4p6hBA5HT5eEIfz3UshPpO54Tubf9m3eaUPxw4r0JO','Dovantuyen',NULL,'admin','2025-11-05 13:28:50','2025-11-05 13:28:50');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-11-18 14:06:11
