SET FOREIGN_KEY_CHECKS = 0;

CREATE DATABASE IF NOT EXISTS cinema CHARACTER SET = 'utf8mb4' COLLATE = 'utf8mb4_unicode_ci';
USE cinema;

-- 1) users
CREATE TABLE IF NOT EXISTS users (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(255) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  full_name VARCHAR(150),
  phone VARCHAR(20),
  role ENUM('customer','staff','admin') NOT NULL DEFAULT 'customer',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2) movies
CREATE TABLE IF NOT EXISTS movies (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  duration_min SMALLINT UNSIGNED NOT NULL,
  description TEXT,
  rating VARCHAR(16),
  release_date DATE,
  
  banner_url VARCHAR(512) DEFAULT NULL,           -- Link ảnh banner (poster chính)
  trailer_url VARCHAR(512) DEFAULT NULL, 

  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_title (title(100))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



-- 3) theaters (rạp)
CREATE TABLE IF NOT EXISTS theaters (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(200) NOT NULL,
  address VARCHAR(300),
  city VARCHAR(100),
  phone VARCHAR(20),
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_city (city)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4) screens (phòng chiếu)
CREATE TABLE IF NOT EXISTS screens (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  theater_id INT UNSIGNED NOT NULL,
  name VARCHAR(100) NOT NULL,
  capacity SMALLINT UNSIGNED,
  seat_layout JSON DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_theater_id (theater_id),
  CONSTRAINT fk_screens_theaters FOREIGN KEY (theater_id) REFERENCES theaters(id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5) shows (suất chiếu)
CREATE TABLE IF NOT EXISTS shows (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  movie_id INT UNSIGNED NOT NULL,
  screen_id INT UNSIGNED NOT NULL,
  show_time DATETIME NOT NULL,
  `format` ENUM('2D','3D','IMAX','4DX') DEFAULT '2D',
  price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  status ENUM('scheduled','cancelled','finished') NOT NULL DEFAULT 'scheduled',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_movie_id (movie_id),
  INDEX idx_screen_time (screen_id, show_time),
  CONSTRAINT fk_shows_movies FOREIGN KEY (movie_id) REFERENCES movies(id) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_shows_screens FOREIGN KEY (screen_id) REFERENCES screens(id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6) bookings (đơn đặt vé)
CREATE TABLE IF NOT EXISTS bookings (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NULL,
  show_id BIGINT UNSIGNED NOT NULL,
  status ENUM('pending','confirmed','cancelled','expired') NOT NULL DEFAULT 'pending',
  total_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  payment_method VARCHAR(50),
  payment_status ENUM('unpaid','paid','refunded') NOT NULL DEFAULT 'unpaid',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_user_id (user_id),
  INDEX idx_show_id (show_id),
  CONSTRAINT fk_bookings_users FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_bookings_shows FOREIGN KEY (show_id) REFERENCES shows(id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7) booking_items (vé/ghế trong 1 booking)
CREATE TABLE IF NOT EXISTS booking_items (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  booking_id BIGINT UNSIGNED NOT NULL,
  show_id BIGINT UNSIGNED NOT NULL,
  seat_code VARCHAR(16) NOT NULL, -- VD: A1, B12
  ticket_price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  ticket_type ENUM('adult','child','senior','student') DEFAULT 'adult',
  status ENUM('booked','cancelled','checked_in') NOT NULL DEFAULT 'booked',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_booking_id (booking_id),
  INDEX idx_show_id (show_id),
  UNIQUE KEY uq_show_seat (show_id, seat_code),
  CONSTRAINT fk_bitems_bookings FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_bitems_shows FOREIGN KEY (show_id) REFERENCES shows(id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
