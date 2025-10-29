<?php
$adminName = "Admin Scarlet";
$title = "Thêm phim mới";
$pageName = "🎬 Thêm phim mới";

require_once __DIR__ . "/../../function/reponsitory.php";
require_once __DIR__ . "/../../handle/movies_handle.php"; // file xử lý chung
require_once __DIR__ . "/side_bar.php";

// Đảm bảo session khởi động
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$message = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'title'         => trim($_POST['title']),
        'duration_min'  => (int) $_POST['duration_min'],
        'description'   => trim($_POST['description']),
        'rating'        => trim($_POST['rating']),
        'release_date'  => $_POST['release_date'] ?? null,
        'banner_url'    => trim($_POST['banner_url']),
        'trailer_url'   => trim($_POST['trailer_url']),
        'created_at'    => date('Y-m-d H:i:s'),
        'updated_at'    => date('Y-m-d H:i:s')
    ];

    $result = handleMovie('add', $data);

    if ($result['success']) {
        $_SESSION['flash_message'] = $result['message'];
        $_SESSION['flash_success'] = true;
        header("Location: movies.php");
        exit;
    } else {
        $message = $result['message'];
        $success = false;
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <title><?= htmlspecialchars($title) ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- CKEditor 5 -->
  <script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>
</head>
<body class="bg-gray-900 text-gray-100">
<main class="flex-1 p-10 min-h-screen">

  <h2 class="text-3xl font-bold text-red-500 mb-6"><?= $pageName ?></h2>

  <?php if ($message): ?>
    <div class="mb-6 p-4 rounded-xl <?= $success ? 'bg-green-700/70' : 'bg-red-700/70' ?> shadow-md">
        <?= htmlspecialchars($message) ?>
    </div>
  <?php endif; ?>

  <form method="POST" class="max-w-3xl mx-auto bg-gray-800 p-8 rounded-2xl shadow-2xl border border-gray-700 space-y-6">

    <!-- Tên phim -->
    <div>
      <label class="block text-gray-300 font-medium mb-2">🎞️ Tên phim</label>
      <input type="text" name="title" required
             class="w-full p-3 rounded-lg bg-gray-700 border border-gray-600 text-white
                    focus:ring-2 focus:ring-red-400 focus:border-red-400 transition"
             placeholder="Nhập tên phim...">
    </div>

    <!-- Banner -->
    <div>
      <label class="block text-gray-300 font-medium mb-2">🖼️ Ảnh banner (URL)</label>
      <input type="text" name="banner_url"
             class="w-full p-3 rounded-lg bg-gray-700 border border-gray-600 text-white
                    focus:ring-2 focus:ring-red-400 focus:border-red-400 transition"
             placeholder="https://example.com/banner.jpg">
    </div>

    <!-- Trailer -->
    <div>
      <label class="block text-gray-300 font-medium mb-2">🎥 Trailer (URL YouTube)</label>
      <input type="text" name="trailer_url"
             class="w-full p-3 rounded-lg bg-gray-700 border border-gray-600 text-white
                    focus:ring-2 focus:ring-red-400 focus:border-red-400 transition"
             placeholder="https://youtube.com/watch?v=...">
    </div>

    <!-- Thời lượng & Ngày ra mắt -->
    <div class="grid grid-cols-2 gap-6">
      <div>
        <label class="block text-gray-300 font-medium mb-2">⏱️ Thời lượng (phút)</label>
        <input type="number" name="duration_min" min="1" required
               class="w-full p-3 rounded-lg bg-gray-700 border border-gray-600 text-white
                      focus:ring-2 focus:ring-red-400 focus:border-red-400 transition"
               placeholder="120">
      </div>

      <div>
        <label class="block text-gray-300 font-medium mb-2">📅 Ngày ra mắt</label>
        <input type="date" name="release_date"
               class="w-full p-3 rounded-lg bg-gray-700 border border-gray-600 text-white
                      focus:ring-2 focus:ring-red-400 focus:border-red-400 transition">
      </div>
    </div>

    <!-- Điểm đánh giá -->
    <div>
      <label class="block text-gray-300 font-medium mb-2">⭐ Đánh giá (0-10)</label>
      <input type="number" name="rating" step="0.1" min="0" max="10"
             class="w-full p-3 rounded-lg bg-gray-700 border border-gray-600 text-white
                    focus:ring-2 focus:ring-red-400 focus:border-red-400 transition"
             placeholder="8.5">
    </div>

    <!-- Mô tả -->
    <div>
      <label class="block text-gray-300 font-medium mb-2">📝 Mô tả phim</label>
      <textarea name="description" id="editor"
                class="w-full p-3 rounded-lg bg-gray-700 border border-gray-600 text-white"
                placeholder="Viết mô tả chi tiết về phim..."></textarea>
    </div>

    <script>
      ClassicEditor.create(document.querySelector('#editor')).catch(error => console.error(error));
    </script>

    <!-- Nút hành động -->
    <div class="flex justify-center gap-6 pt-6">
      <button type="submit"
              class="bg-red-500 hover:bg-red-600 text-white font-semibold px-8 py-3 rounded-lg shadow-lg transition">
        💾 Lưu phim
      </button>
      <a href="movies.php"
         class="bg-gray-600 hover:bg-gray-700 text-white font-medium px-8 py-3 rounded-lg shadow-lg transition">
        ← Quay lại
      </a>
    </div>
  </form>
</main>
</body>
</html>
