<?php
// Search Handle: cung cấp hàm tìm kiếm đa bảng dùng Repository::runRawQuery
require_once __DIR__ . '/../function/reponsitory.php';

/**
 * Chuẩn hóa keyword (cắt khoảng trắng, loại ký tự lạ)
 */
function normalize_keyword(string $q): string {
    $q = trim($q);
    // Giữ lại chữ cái, số, khoảng trắng và một số ký tự phổ biến
    $q = preg_replace('/[^\p{L}\p{N}\s\-_.]/u', '', $q);
    return $q;
}

/**
 * Tìm kiếm đa bảng cho Admin.
 * Trả về mảng kết quả theo từng bảng.
 */
function searchAll(string $keyword, int $limitPerTable = 10): array {
    $kw = normalize_keyword($keyword);
    if ($kw === '') {
        return [
            'users' => [], 'theaters' => [], 'screens' => [], 'movies' => [], 'shows' => []
        ];
    }

    $like = "%" . $kw . "%";

    // Users
    $usersRepo = new Repository('users');
    $users = $usersRepo->runRawQuery(
        "SELECT id, full_name, email, role, created_at
         FROM users
         WHERE full_name LIKE ? OR email LIKE ?
         ORDER BY id DESC
         LIMIT $limitPerTable",
        [$like, $like]
    );

    // Theaters
    $theatersRepo = new Repository('theaters');
    $theaters = $theatersRepo->runRawQuery(
        "SELECT id, name, city, address, phone, created_at
         FROM theaters
         WHERE name LIKE ? OR city LIKE ? OR address LIKE ?
         ORDER BY id DESC
         LIMIT $limitPerTable",
        [$like, $like, $like]
    );

    // Screens (join theaters để hiển thị tên rạp)
    $screensRepo = new Repository('screens');
    $screens = $screensRepo->runRawQuery(
        "SELECT s.id, s.name, s.screen_type, s.capacity, t.name AS theater_name, s.created_at
         FROM screens s
         INNER JOIN theaters t ON s.theater_id = t.id
         WHERE s.name LIKE ? OR t.name LIKE ?
         ORDER BY s.id DESC
         LIMIT $limitPerTable",
        [$like, $like]
    );

    // Movies
    $moviesRepo = new Repository('movies');
    $movies = $moviesRepo->runRawQuery(
        "SELECT id, title, rating, release_date, created_at
         FROM movies
         WHERE title LIKE ? OR description LIKE ?
         ORDER BY id DESC
         LIMIT $limitPerTable",
        [$like, $like]
    );

    // Shows (join movies + screens)
    $showsRepo = new Repository('shows');
    $shows = $showsRepo->runRawQuery(
        "SELECT sh.id, sh.show_time, sh.format, sh.price, sh.status,
                mv.title AS movie_title, sc.name AS screen_name
         FROM shows sh
         INNER JOIN movies mv ON sh.movie_id = mv.id
         INNER JOIN screens sc ON sh.screen_id = sc.id
         WHERE mv.title LIKE ? OR sc.name LIKE ? OR DATE_FORMAT(sh.show_time, '%Y-%m-%d %H:%i') LIKE ?
         ORDER BY sh.id DESC
         LIMIT $limitPerTable",
        [$like, $like, $like]
    );

    // Bookings (join users + shows + movies + screens)
    $bookingsRepo = new Repository('bookings');
    $bookings = $bookingsRepo->runRawQuery(
        "SELECT b.id, b.status, b.payment_status, b.total_amount, b.created_at,
                u.email AS user_email, u.full_name AS user_name,
                sh.show_time, mv.title AS movie_title, sc.name AS screen_name
         FROM bookings b
         LEFT JOIN users u ON b.user_id = u.id
         INNER JOIN shows sh ON b.show_id = sh.id
         INNER JOIN movies mv ON sh.movie_id = mv.id
         INNER JOIN screens sc ON sh.screen_id = sc.id
         WHERE (u.email LIKE ? OR u.full_name LIKE ? OR mv.title LIKE ? OR sc.name LIKE ?
                OR b.status LIKE ? OR b.payment_status LIKE ?
                OR DATE_FORMAT(sh.show_time, '%Y-%m-%d %H:%i') LIKE ?)
         ORDER BY b.id DESC
         LIMIT $limitPerTable",
        [$like, $like, $like, $like, $like, $like, $like]
    );

    return compact('users', 'theaters', 'screens', 'movies', 'shows', 'bookings');
}

/**
 * Tìm kiếm dành cho Client (ưu tiên movies và shows).
 */
function searchForClient(string $keyword, int $limitPerTable = 12): array {
    $kw = normalize_keyword($keyword);
    if ($kw === '') {
        return ['movies' => [], 'shows' => []];
    }
    $like = "%" . $kw . "%";

    $moviesRepo = new Repository('movies');
    $movies = $moviesRepo->runRawQuery(
        "SELECT id, title, rating, banner_url, trailer_url
         FROM movies
         WHERE title LIKE ? OR description LIKE ?
         ORDER BY id DESC
         LIMIT $limitPerTable",
        [$like, $like]
    );

    $showsRepo = new Repository('shows');
    $shows = $showsRepo->runRawQuery(
        "SELECT sh.id, sh.show_time, sh.format, sh.price, mv.title AS movie_title, sc.name AS screen_name
         FROM shows sh
         INNER JOIN movies mv ON sh.movie_id = mv.id
         INNER JOIN screens sc ON sh.screen_id = sc.id
         WHERE mv.title LIKE ? OR sc.name LIKE ?
         ORDER BY sh.id DESC
         LIMIT $limitPerTable",
        [$like, $like]
    );

    return compact('movies', 'shows');
}

/**
 * Tìm kiếm theo bảng cụ thể cho Admin.
 * entity: users | theaters | screens | movies | shows | bookings
 */
function searchByEntity(string $entity, string $keyword, int $limit = 10): array {
    $entity = strtolower(trim($entity));
    $kw = normalize_keyword($keyword);
    if ($kw === '') { return []; }
    $like = "%" . $kw . "%";

    switch ($entity) {
        case 'users':
            $repo = new Repository('users');
            return $repo->runRawQuery(
                "SELECT id, full_name, email, role, created_at FROM users
                 WHERE full_name LIKE ? OR email LIKE ?
                 ORDER BY id DESC LIMIT $limit",
                [$like, $like]
            );
        case 'theaters':
            $repo = new Repository('theaters');
            return $repo->runRawQuery(
                "SELECT id, name, city, address, phone, created_at FROM theaters
                 WHERE name LIKE ? OR city LIKE ? OR address LIKE ?
                 ORDER BY id DESC LIMIT $limit",
                [$like, $like, $like]
            );
        case 'screens':
            $repo = new Repository('screens');
            return $repo->runRawQuery(
                "SELECT s.id, s.name, s.screen_type, s.capacity, t.name AS theater_name, s.created_at
                 FROM screens s INNER JOIN theaters t ON s.theater_id = t.id
                 WHERE s.name LIKE ? OR t.name LIKE ?
                 ORDER BY s.id DESC LIMIT $limit",
                [$like, $like]
            );
        case 'movies':
            $repo = new Repository('movies');
            return $repo->runRawQuery(
                "SELECT id, title, rating, release_date, created_at FROM movies
                 WHERE title LIKE ? OR description LIKE ?
                 ORDER BY id DESC LIMIT $limit",
                [$like, $like]
            );
        case 'shows':
            $repo = new Repository('shows');
            return $repo->runRawQuery(
                "SELECT sh.id, sh.show_time, sh.format, sh.price, sh.status,
                        mv.title AS movie_title, sc.name AS screen_name
                 FROM shows sh
                 INNER JOIN movies mv ON sh.movie_id = mv.id
                 INNER JOIN screens sc ON sh.screen_id = sc.id
                 WHERE mv.title LIKE ? OR sc.name LIKE ? OR DATE_FORMAT(sh.show_time, '%Y-%m-%d %H:%i') LIKE ?
                 ORDER BY sh.id DESC LIMIT $limit",
                [$like, $like, $like]
            );
        case 'bookings':
            $repo = new Repository('bookings');
            return $repo->runRawQuery(
                "SELECT b.id, b.status, b.payment_status, b.total_amount, b.created_at,
                        u.email AS user_email, u.full_name AS user_name,
                        sh.show_time, mv.title AS movie_title, sc.name AS screen_name
                 FROM bookings b
                 LEFT JOIN users u ON b.user_id = u.id
                 INNER JOIN shows sh ON b.show_id = sh.id
                 INNER JOIN movies mv ON sh.movie_id = mv.id
                 INNER JOIN screens sc ON sh.screen_id = sc.id
                 WHERE (u.email LIKE ? OR u.full_name LIKE ? OR mv.title LIKE ? OR sc.name LIKE ?
                        OR b.status LIKE ? OR b.payment_status LIKE ?
                        OR DATE_FORMAT(sh.show_time, '%Y-%m-%d %H:%i') LIKE ?
                        OR CAST(b.id AS CHAR) LIKE ?)
                 ORDER BY b.id DESC LIMIT $limit",
                [$like, $like, $like, $like, $like, $like, $like, $like]
            );
        default:
            return [];
    }
}

?>