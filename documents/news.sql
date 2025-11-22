/* Cấu trúc SQL cho 2 bảng: news_articles (để chứa các bài báo) và news_categories (để phân loại chúng).*/
/**E thấy cái này chả khác gì cái tin nổi bật ezzzzzzzzzzzzzzzzzzzzzzzzzzzz */
CREATE TABLE `news_categories` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Tên danh mục (ví dụ: Khuyến mãi, Phim mới)',
  `slug` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Dùng cho URL (ví dụ: khuyen-mai)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `news_articles` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `category_id` int(11) UNSIGNED DEFAULT NULL COMMENT 'Khóa ngoại tới bảng news_categories',
  `author_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'Khóa ngoại tới bảng users (admin)',
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Tiêu đề bài viết',
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Dùng cho URL (ví dụ: phim-moi-thang-12)',
  `summary` text COLLATE utf8mb4_unicode_ci COMMENT 'Tóm tắt ngắn (hiển thị ở danh sách)',
  `content` longtext COLLATE utf8mb4_unicode_ci COMMENT 'Nội dung đầy đủ (hỗ trợ HTML)',
  `image_url` varchar(512) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Đường dẫn ảnh đại diện (banner)',
  `status` enum('draft','published') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft' COMMENT 'Trạng thái: Nháp, Đã đăng',
  `published_at` datetime DEFAULT NULL COMMENT 'Thời gian đăng bài',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `fk_news_category` (`category_id`),
  KEY `fk_news_author` (`author_id`),
  CONSTRAINT `fk_news_author` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_news_category` FOREIGN KEY (`category_id`) REFERENCES `news_categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;