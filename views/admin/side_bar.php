<?php
$baseUrl = "/BTL_Book_movie_tickets/views/admin";

$adminName = "Admin Scarlet";
// $title= "ADMIN";

?>
<!DOCTYPE html>
<html lang="vi" class="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?=$title?> | SCARLET CINEMA</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap" rel="stylesheet">
  <script src="https://unpkg.com/lucide@latest"></script>
  <style>
    body { font-family: 'Inter', sans-serif; }
    .sidebar { background-color: #1f2937; }
  </style>
</head>

<body class="bg-gray-900 text-gray-100" onload="lucide.createIcons();">

  <div class="flex min-h-screen">
    <!-- SIDEBAR -->
    <aside class="sidebar w-64 p-5 flex flex-col">
      <div class="flex items-center space-x-2 mb-8">
        <i data-lucide="popcorn" class="text-red-600 h-6 w-6"></i>
        <h1 class="font-bold text-xl">SCARLET ADMIN</h1>
      </div>

      <nav class="flex-1 space-y-3">
        <a href="<?= $baseUrl?>/index.php" class="block p-2 rounded hover:bg-gray-700 font-medium text-gray-300">
          <i data-lucide="home" class="inline w-4 h-4 mr-2"></i>Dashboard
        </a>
        <a href="<?= $baseUrl?>/users.php" class="block p-2 rounded hover:bg-gray-700 font-medium text-gray-300">
          <i data-lucide="users" class="inline w-4 h-4 mr-2"></i>Quản lý người dùng
        </a>
        <a href="<?= $baseUrl?>/movies.php" class="block p-2 rounded hover:bg-gray-700 font-medium text-gray-300">
          <i data-lucide="film" class="inline w-4 h-4 mr-2"></i>Quản lý phim
        </a>
      </nav>

      <div class="border-t border-gray-700 pt-4 mt-4 text-sm">
        <p class="text-gray-400">Xin chào, <span class="text-red-500 font-semibold"><?= $adminName ?></span></p>
        <a href="../login.php" class="text-gray-400 hover:text-red-500 mt-2 block">Đăng xuất</a>
      </div>
    </aside>

    