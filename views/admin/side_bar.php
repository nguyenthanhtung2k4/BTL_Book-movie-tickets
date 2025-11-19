<?php
// Khởi động session nếu chưa có
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load auth helper
require_once __DIR__ . "/../../function/auth_helper.php";

// Kiểm tra quyền admin - bắt buộc phải đăng nhập và là admin
if (!isLoggedIn()) {
    $_SESSION['flash_message'] = 'Vui lòng đăng nhập để tiếp tục!';
    $_SESSION['flash_success'] = false;
    header('Location: ../clinet/account.php?view=login');
    exit;
}

if (!isAdmin()) {
    $_SESSION['flash_message'] = 'Bạn không có quyền truy cập trang admin!';
    $_SESSION['flash_success'] = false;
    header('Location: ../clinet/index.php');
    exit;
}

$baseUrl = "/BTL_Book_movie_tickets/views/admin";

// Lấy tên admin từ session
$adminName = $_SESSION['user']['full_name'] ?? $_SESSION['user']['email'] ?? 'Admin';
$userRole = $_SESSION['user']['role'] ?? 'admin';

?>
<!DOCTYPE html>
<html lang="vi" class="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $title ?? 'Admin Panel' ?> | SCARLET CINEMA</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap" rel="stylesheet">
  <script src="https://unpkg.com/lucide@latest"></script>
  <style>
    body { font-family: 'Inter', sans-serif; }
    .sidebar { background-color: #1f2937; }
    .nav-link { transition: all 0.2s ease; }
    .nav-link:hover { background-color: #374151; transform: translateX(4px); }
  </style>
</head>

<body class="bg-gray-900 text-gray-100" onload="lucide.createIcons();">

  <div class="flex min-h-screen">
    <aside class="sidebar w-64 p-5 flex flex-col shadow-2xl">
      <div class="flex items-center space-x-2 mb-8 pb-4 border-b border-gray-700">
        <i data-lucide="popcorn" class="text-red-600 h-8 w-8"></i>
        <h1 class="font-bold text-xl">SCARLET ADMIN</h1>
      </div>

      <nav class="flex-1 space-y-2">
         <a href="<?= $baseUrl?>/search.php" class="nav-link block p-3 rounded hover:bg-gray-700 font-medium text-gray-300">
          <i data-lucide="search" class="inline w-4 h-4 mr-2"></i>Tìm kiếm
        </a>
        
        <a href="<?= $baseUrl?>/index.php" class="nav-link block p-3 rounded hover:bg-gray-700 font-medium text-gray-300">
          <i data-lucide="layout-dashboard" class="inline w-4 h-4 mr-2"></i>Dashboard
        </a>
        
        <a href="<?= $baseUrl?>/users.php" class="nav-link block p-3 rounded hover:bg-gray-700 font-medium text-gray-300">
          <i data-lucide="users" class="inline w-4 h-4 mr-2"></i>Quản lý người dùng
        </a>
        <a href="<?= $baseUrl?>/movies.php" class="nav-link block p-3 rounded hover:bg-gray-700 font-medium text-gray-300">
          <i data-lucide="film" class="inline w-4 h-4 mr-2"></i>Quản lý phim
        </a>
        <a href="<?= $baseUrl?>/theaters.php" class="nav-link block p-3 rounded hover:bg-gray-700 font-medium text-gray-300">
          <i data-lucide="building" class="inline w-4 h-4 mr-2"></i>Quản lý rạp
        </a>
        <a href="<?= $baseUrl?>/screens.php" class="nav-link block p-3 rounded hover:bg-gray-700 font-medium text-gray-300">
          <i data-lucide="monitor" class="inline w-4 h-4 mr-2"></i>Quản lý phòng chiếu
        </a>
        <a href="<?= $baseUrl?>/shows.php" class="nav-link block p-3 rounded hover:bg-gray-700 font-medium text-gray-300">
          <i data-lucide="calendar-days" class="inline w-4 h-4 mr-2"></i>Quản lý suất chiếu
        </a>
        <a href="<?= $baseUrl?>/bookings.php" class="nav-link block p-3 rounded hover:bg-gray-700 font-medium text-gray-300">
          <i data-lucide="ticket" class="inline w-4 h-4 mr-2"></i>Quản lý đơn đặt vé
        </a>
       
      </nav>

      <!-- User Info & Actions -->
      <div class="border-t border-gray-700 pt-4 mt-4 space-y-3">
        <div class="bg-gray-800 rounded-lg p-3">
          <p class="text-xs text-gray-500 uppercase mb-1">Đang đăng nhập</p>
          <p class="text-sm font-semibold text-white truncate" title="<?= htmlspecialchars($adminName) ?>">
            <?= htmlspecialchars($adminName) ?>
          </p>
          <p class="text-xs text-red-500 font-medium mt-1">
            <i data-lucide="shield-check" class="inline w-3 h-3 mr-1"></i>
            <?= ucfirst($userRole) ?>
          </p>
        </div>
        
        <!-- Nút chuyển sang Client -->
        <a href="../clinet/index.php" 
           class="flex items-center justify-center gap-2 w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition duration-200">
          <i data-lucide="globe" class="w-4 h-4"></i>
          <span>Trang khách hàng</span>
        </a>
        
        <!-- Nút Logout -->
        <a href="../clinet/logout.php" 
           class="flex items-center justify-center gap-2 w-full bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded-lg transition duration-200">
          <i data-lucide="log-out" class="w-4 h-4"></i>
          <span>Đăng xuất</span>
        </a>
      </div>
    </aside>
