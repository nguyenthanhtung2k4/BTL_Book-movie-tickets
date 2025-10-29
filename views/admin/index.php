<?php
$adminName = "Admin Scarlet";
$title = "Dashboard";
$pageName = "Bảng điều khiển quản trị";
require_once __DIR__ ."/side_bar.php";
require_once __DIR__ ."/../../function/reponsitory.php";
?>

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

</body>
</html>
