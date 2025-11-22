<?php
$users = [
  ["id" => 1, "name" => "Nguyễn Văn A", "email" => "a@gmail.com"],
  ["id" => 2, "name" => "Trần Thị B", "email" => "b@gmail.com"],
  ["id" => 3, "name" => "Lê Văn C", "email" => "c@gmail.com"],
];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Quản lý người dùng | SCARLET ADMIN</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 text-gray-100 p-10">
  <h1 class="text-3xl font-bold text-red-500 mb-6">👤 Quản lý người dùng</h1>
  <table class="min-w-full bg-gray-800 rounded-lg overflow-hidden">
    <thead>
      <tr class="bg-gray-700 text-gray-300">
        <th class="p-3 text-left">ID</th>
        <th class="p-3 text-left">Họ tên</th>
        <th class="p-3 text-left">Email</th>
        <th class="p-3 text-left">Hành động</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach($users as $u): ?>
      <tr class="border-b border-gray-700 hover:bg-gray-700">
        <td class="p-3"><?= $u["id"] ?></td>
        <td class="p-3"><?= htmlspecialchars($u["name"]) ?></td>
        <td class="p-3"><?= htmlspecialchars($u["email"]) ?></td>
        <td class="p-3">
          <button class="text-red-500 hover:underline">Xóa</button>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <a href="index.php" class="inline-block mt-6 text-gray-400 hover:text-red-500">← Quay lại bảng điều khiển</a>
</body>
</html>
