<?php
// views/admin/editTheater.php
$adminName = "Admin Scarlet";
$title = "Sửa thông tin rạp";
$pageName = "🎬 Cập nhật rạp chiếu phim";

if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . "/../../function/reponsitory.php";
require_once __DIR__ . "/side_bar.php";

$repo = new Repository('theaters');

// Lấy ID rạp từ URL
$theaterId = $_GET['id'] ?? null;
if (!$theaterId || !is_numeric($theaterId)) {
    $_SESSION['flash_message'] = '⚠️ ID rạp không hợp lệ!';
    $_SESSION['flash_success'] = false;
    header('Location: theaters.php');
    exit;
}

$theater = $repo->find($theaterId);
if (!$theater) {
    $_SESSION['flash_message'] = '❌ Không tìm thấy rạp!';
    $_SESSION['flash_success'] = false;
    header('Location: theaters.php');
    exit;
}
$URL= '../../handle/theaters_handle.php';

?>

<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <title><?= htmlspecialchars($title) ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-900 text-gray-100">
<main class="flex-1 p-8 sm:p-10 min-h-screen">
  <div class="flex justify-between items-center mb-8">
    <h2 class="text-3xl font-bold text-red-500"><?= $pageName ?></h2>
    <a href="theaters.php" class="bg-gray-700 hover:bg-gray-600 px-4 py-2 rounded text-white font-medium">← Quay lại danh sách</a>
  </div>

  <!-- LƯU Ý: gửi action=edit và id qua query string để handler dễ đọc -->
  <form action="<?= $URL ?> ?action=edit&id=<?= (int)$theaterId ?>" method="POST" class="bg-gray-800 p-8 rounded-2xl">
    <!-- Hidden id (dự phòng) -->
    <input type="hidden" name="id" value="<?= htmlspecialchars($theater['id']) ?>">

    <div class="mb-4">
      <label class="block text-gray-300 mb-2">Tên rạp</label>
      <input type="text" name="name" required value="<?= htmlspecialchars($theater['name']) ?>"
             class="w-full p-3 rounded bg-gray-900 border border-gray-700 text-gray-100">
    </div>

    <div class="mb-4">
      <label class="block text-gray-300 mb-2">Thành phố</label>
      <input type="text" name="city" value="<?= htmlspecialchars($theater['city'] ?? '') ?>"
             class="w-full p-3 rounded bg-gray-900 border border-gray-700 text-gray-100">
    </div>

    <div class="mb-4">
      <label class="block text-gray-300 mb-2">Địa chỉ</label>
      <input type="text" name="address" value="<?= htmlspecialchars($theater['address'] ?? '') ?>"
             class="w-full p-3 rounded bg-gray-900 border border-gray-700 text-gray-100">
    </div>

    <div class="mb-4">
      <label class="block text-gray-300 mb-2">Điện thoại</label>
      <input type="text" name="phone" value="<?= htmlspecialchars($theater['phone'] ?? '') ?>"
             class="w-full p-3 rounded bg-gray-900 border border-gray-700 text-gray-100">
    </div>

    <div class="flex justify-end gap-3 pt-4 border-t border-gray-700">
      <a href="theaters.php" class="bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded">Hủy</a>
      <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-5 py-2 rounded">Lưu sửa rạp</button>
    </div>
  </form>
</main>
</body>
</html>
