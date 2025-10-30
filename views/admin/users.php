<?php
$adminName = "Admin Scarlet";
$title = "User";
$pageName = "Bảng điều khiển người dùng";

require_once __DIR__ . "/../../function/reponsitory.php";
require_once __DIR__ . "/side_bar.php";

$userRepo = new Repository('users');
$users = $userRepo->getAllTimeDESC();

?>

<main class="flex-1 p-10 text-white">
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
</main>
