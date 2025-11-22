<?php
// Giả lập đăng nhập admin (sau này có thể thêm session check)
$adminName = "Admin Scarlet";
?>
<!DOCTYPE html>
<html lang="vi" class="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin | SCARLET CINEMA</title>
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
        <a href="index.php" class="block p-2 rounded hover:bg-gray-700 font-medium text-gray-300">
          <i data-lucide="home" class="inline w-4 h-4 mr-2"></i>Dashboard
        </a>
        <a href="users.php" class="block p-2 rounded hover:bg-gray-700 font-medium text-gray-300">
          <i data-lucide="users" class="inline w-4 h-4 mr-2"></i>Quản lý người dùng
        </a>
        <a href="movies.php" class="block p-2 rounded hover:bg-gray-700 font-medium text-gray-300">
          <i data-lucide="film" class="inline w-4 h-4 mr-2"></i>Quản lý phim
        </a>
      </nav>

      <div class="border-t border-gray-700 pt-4 mt-4 text-sm">
        <p class="text-gray-400">Xin chào, <span class="text-red-500 font-semibold"><?= $adminName ?></span></p>
        <a href="../login.php" class="text-gray-400 hover:text-red-500 mt-2 block">Đăng xuất</a>
      </div>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="flex-1 p-10">
      <h2 class="text-3xl font-bold text-red-500 mb-6">Bảng điều khiển quản trị</h2>

      <div class="grid md:grid-cols-2 gap-8">
        <!-- USERS -->
        <div class="bg-gray-800 p-6 rounded-xl shadow-lg">
          <i data-lucide="users" class="text-red-500 w-8 h-8 mb-3"></i>
          <h3 class="text-xl font-semibold text-white mb-2">Quản lý người dùng</h3>
          <p class="text-gray-400 mb-4">Xem danh sách, thêm hoặc xóa tài khoản người dùng.</p>
          <a href="users.php" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-medium">Truy cập</a>
        </div>

        <!-- MOVIES -->
        <div class="bg-gray-800 p-6 rounded-xl shadow-lg">
          <i data-lucide="film" class="text-red-500 w-8 h-8 mb-3"></i>
          <h3 class="text-xl font-semibold text-white mb-2">Quản lý phim</h3>
          <p class="text-gray-400 mb-4">Thêm mới, chỉnh sửa, hoặc xóa thông tin phim đang chiếu.</p>
          <a href="movies.php" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-medium">Truy cập</a>
        </div>
      </div>
    </main>
  </div>

</body>
</html>
