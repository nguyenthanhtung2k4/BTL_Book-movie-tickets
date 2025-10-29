<?php
$adminName = "Admin Scarlet";
$title = "Thêm rạp";
$pageName = " Thêm rạp chiếu mới";

if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . "/../../function/reponsitory.php";
require_once __DIR__ . "/../../handle/theaters_handle.php";
require_once __DIR__ . "/side_bar.php";
addClaas();
?>

<main class="flex-1 p-8 sm:p-10 min-h-screen">
  <h2 class="text-3xl font-bold text-red-500 mb-8"><?= $pageName ?></h2>

  <form method="POST" class="bg-gray-800 p-8 rounded-2xl shadow-xl border border-gray-700 space-y-6">
    <div>
      <label class="block text-gray-300 font-medium mb-2">🎬 Tên rạp</label>
      <input type="text" name="name" required class="w-full bg-gray-900 border border-gray-700 rounded-lg p-3 text-gray-100 focus:ring-2 focus:ring-red-500">
    </div>

    <div>
      <label class="block text-gray-300 font-medium mb-2">📍 Địa chỉ</label>
      <input type="text" name="address" class="w-full bg-gray-900 border border-gray-700 rounded-lg p-3 text-gray-100">
    </div>

    <div>
      <label class="block text-gray-300 font-medium mb-2">🏙️ Thành phố</label>
      <input type="text" name="city" class="w-full bg-gray-900 border border-gray-700 rounded-lg p-3 text-gray-100">
    </div>

    <div>
      <label class="block text-gray-300 font-medium mb-2">📞 Số điện thoại</label>
      <input type="text" name="phone" class="w-full bg-gray-900 border border-gray-700 rounded-lg p-3 text-gray-100">
    </div>

    <!-- <div>
      <label class="block text-gray-300 font-medium mb-2">📝 Mô tả</label>
      <textarea name="description" rows="4" class="w-full bg-gray-900 border border-gray-700 rounded-lg p-3 text-gray-100"></textarea>
    </div> -->

    <div class="flex justify-end gap-3">
      <a href="theaters.php" class="bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">Hủy</a>
      <button type="submit" class="bg-gradient-to-r from-red-600 to-red-500 hover:from-red-500 hover:to-pink-500 px-6 py-2 rounded-lg text-white font-semibold shadow">
        Thêm rạp
      </button>
    </div>
  </form>
</main>
