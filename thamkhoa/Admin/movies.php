<?php
$movies = [
  ["id" => 1, "title" => "Người Kế Thừa Vũ Trụ", "year" => 2025],
  ["id" => 2, "title" => "Thiên Thần Mất Cánh", "year" => 2024],
];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Quản lý phim | SCARLET ADMIN</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 text-gray-100 p-10">
  <h1 class="text-3xl font-bold text-red-500 mb-6">🎬 Quản lý phim</h1>

  <table class="min-w-full bg-gray-800 rounded-lg overflow-hidden">
    <thead>
      <tr class="bg-gray-700 text-gray-300">
        <th class="p-3 text-left">ID</th>
        <th class="p-3 text-left">Tên phim</th>
        <th class="p-3 text-left">Năm</th>
        <th class="p-3 text-left">Hành động</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach($movies as $m): ?>
      <tr class="border-b border-gray-700 hover:bg-gray-700">
        <td class="p-3"><?= $m["id"] ?></td>
        <td class="p-3"><?= htmlspecialchars($m["title"]) ?></td>
        <td class="p-3"><?= $m["year"] ?></td>
        <td class="p-3">
          <button class="text-blue-400 hover:underline">Sửa</button> |
          <button class="text-red-500 hover:underline">Xóa</button>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <a href="index.php" class="inline-block mt-6 text-gray-400 hover:text-red-500">← Quay lại bảng điều khiển</a>
</body>
</html>
