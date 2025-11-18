<?php
$adminName = "Admin Scarlet";
$title = "User";
$pageName = "Bảng điều khiển người dùng";

require_once __DIR__ . "/../../function/reponsitory.php";
require_once __DIR__ . "/side_bar.php";

$userRepo = new Repository('users');
// Phân trang: 10 người dùng mỗi trang, sắp xếp mới nhất theo id
$itemsPerPage = 10;
$currentPage = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($currentPage - 1) * $itemsPerPage;

$totalUsers = $userRepo->countAll();
$totalPages = ceil($totalUsers / $itemsPerPage);

$users = $userRepo->getLimitAndOffset($itemsPerPage, $offset, 'id', 'DESC');
if (session_status() === PHP_SESSION_NONE)
    session_start();
// Khởi tạo các biến
$message = '';
$isSuccess = false;

if (isset($_SESSION['flash_message'])) {
    $message = $_SESSION['flash_message'];
    $isSuccess = $_SESSION['flash_success'] ?? false;
    // Xóa session để thông báo không xuất hiện lại
    unset($_SESSION['flash_message'], $_SESSION['flash_success']);
}



?>

<main class="flex-1 p-10 text-white">
   
<!--  Thông báo nổi bên phải -->
    <?php if ($message): ?>
    <div id="flash-message" class="fixed top-6 right-6 z-50 px-6 py-3 rounded-lg shadow-lg text-white font-semibold transition-transform duration-300
                 <?= $isSuccess ? 'bg-green-500' : 'bg-red-600' ?>">
        <?= htmlspecialchars($message) ?>
    </div>
    <?php endif; ?>

  <h2 class="text-3xl font-bold text-red-500 mb-6"><?= $pageName ?></h2>

  <div class="mb-6">
    <a href="addUser.php" class="bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded text-white font-semibold">
       Thêm người dùng mới
    </a>
  </div>

  <div class="overflow-x-auto">
    <table class="min-w-full bg-gray-800 rounded-lg overflow-hidden text-sm">
      <thead class="bg-gray-700 text-gray-300 uppercase text-xs">
        <tr>
          <th class="p-3 text-left">#</th>
          <th class="p-3 text-left">Họ tên</th>
          <th class="p-3 text-left">Email</th>
          <th class="p-3 text-left">Vai trò</th>
          <th class="p-3 text-left">Ngày tạo</th>
          <th class="p-3 text-left">Hành động</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($users)): ?>
          <tr>
            <td colspan="6" class="text-center p-4 text-gray-400">Không có người dùng nào.</td>
          </tr>
        <?php else: ?>
          <?php foreach ($users as $i => $user): ?>
            <tr class="border-b border-gray-700 hover:bg-gray-700 transition">
              <td class="p-3"><?= $i + 1 ?></td>
              <td class="p-3 font-medium"><?= htmlspecialchars($user['full_name']) ?></td>
              <td class="p-3 text-gray-300"><?= htmlspecialchars($user['email']) ?></td>
              <td class="p-3 text-gray-400"><?= htmlspecialchars($user['role']) ?></td>
              <td class="p-3 text-gray-400">
                <?= isset($user['created_at']) ? date('d/m/Y', strtotime($user['created_at'])) : '-' ?>
              </td>
              <td class="p-3">
                <a href="editUser.php?action=edit&id=<?= $user['id'] ?>" class="text-green-400 hover:underline mr-2">✏️</a>
                <a href="deleteUser.php?action=delete&id=<?= $user['id'] ?>"
                   class="text-red-500 hover:underline">
                  🗑️
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
  <?php if ($totalPages > 1): ?>
  <div class="flex justify-center mt-6 space-x-2 text-white">
      <a href="?page=<?= max(1, $currentPage - 1) ?>" class="px-4 py-2 rounded-lg <?= $currentPage == 1 ? 'bg-gray-600 text-gray-400 cursor-not-allowed' : 'bg-red-600 hover:bg-red-700' ?>">
          &laquo; Trước
      </a>
      <?php for ($i = 1; $i <= $totalPages; $i++): ?>
          <a href="?page=<?= $i ?>" class="px-4 py-2 rounded-lg <?= $i == $currentPage ? 'bg-red-700 font-bold' : 'bg-gray-700 hover:bg-gray-600' ?>">
              <?= $i ?>
          </a>
      <?php endfor; ?>
      <a href="?page=<?= min($totalPages, $currentPage + 1) ?>" class="px-4 py-2 rounded-lg <?= $currentPage == $totalPages ? 'bg-gray-600 text-gray-400 cursor-not-allowed' : 'bg-red-600 hover:bg-red-700' ?>">
          Sau &raquo;
      </a>
  </div>
  <?php endif; ?>
</main>
<script>
    setTimeout(() => {
        const flash = document.getElementById('flash-message');
        if (flash) {
            flash.style.opacity = '0';
            flash.style.transform = 'translateY(-10px)';
            setTimeout(() => flash.remove(), 500);
        }
    }, 3000); // Tăng thời gian hiển thị lên 3 giây
</script>
