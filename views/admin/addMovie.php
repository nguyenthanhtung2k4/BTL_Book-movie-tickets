<?php
$adminName = "Admin Scarlet";
$title = "Thêm phim mới";
$pageName = "Thêm phim mới";

require_once __DIR__ . "/../../function/reponsitory.php";
require_once __DIR__ . "/side_bar.php";

// Đảm bảo session khởi động
if (session_status() == PHP_SESSION_NONE) {
  session_start();
}


// Khởi tạo các biến
$message = '';
$isSuccess = false;

// 1. Lấy thông báo flash message từ Session (được gửi từ movies_handle.php)
if (isset($_SESSION['flash_message'])) {
  $message = $_SESSION['flash_message'];
  $isSuccess = $_SESSION['flash_success'] ?? false;
  unset($_SESSION['flash_message'], $_SESSION['flash_success']);
}


$URL="../../handle/movies_handle.php"; // file xử lý chung

?>

  <script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>
  <style>
    /* Tùy chỉnh cho CKEditor để phù hợp với Dark Mode */
    .ck-editor__editable {
        min-height: 200px;
        background-color: #374151 !important; /* gray-700 */
        border: 1px solid #4b5563 !important; /* gray-600 */
        color: #f3f4f6 !important; /* gray-100 */
        border-radius: 0.5rem;
        padding: 1rem;
    }
    .ck-toolbar {
        background-color: #1f2937 !important; /* gray-800 đậm hơn */
        border-radius: 0.5rem 0.5rem 0 0;
        border: 1px solid #4b5563 !important;
    }
  </style>
</head>

<main class="flex-1 p-8 sm:p-10 min-h-screen">

  <div class="border-b-2 border-blue-600 pb-4 mb-8 max-w-4xl mx-auto">
    <h2 class="text-3xl font-extrabold text-white tracking-wide">
        <span class="text-blue-400"></span> <?= $pageName ?>
    </h2>
  </div>

  <?php if ($message): ?>
    <div class="mb-8 p-4 rounded-xl shadow-xl border max-w-4xl mx-auto <?= $success ? 'bg-green-800/50 border-green-500 text-green-200' : 'bg-red-800/50 border-red-500 text-red-200' ?>">
        <p class="font-medium"><?= htmlspecialchars($message) ?></p>
    </div>
  <?php endif; ?>

  <form method="POST" action="<?=$URL?>?action=add" class="max-w-4xl mx-auto bg-gray-800 p-8 sm:p-10 rounded-2xl shadow-2xl border border-gray-700 space-y-8">

    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">

        <div>
            <label class="block text-gray-300 font-semibold mb-2">🎞️ Tên phim</label>
            <input type="text" name="title" required
                   class="w-full p-3 rounded-xl bg-gray-700 border border-gray-600 text-white
                          focus:ring-2 focus:ring-blue-400 focus:border-blue-400 transition"
                   placeholder="Nhập tên phim..." 
                   value="<?= htmlspecialchars($_POST['title'] ?? '') ?>">
        </div>

        <div>
            <label class="block text-gray-300 font-semibold mb-2">🖼️ Ảnh banner (URL)</label>
            <input type="text" name="banner_url"
                   class="w-full p-3 rounded-xl bg-gray-700 border border-gray-600 text-white
                          focus:ring-2 focus:ring-blue-400 focus:border-blue-400 transition"
                   placeholder="https://example.com/poster.jpg"
                   value="<?= htmlspecialchars($_POST['banner_url'] ?? '') ?>">
        </div>
        
        <div>
            <label class="block text-gray-300 font-semibold mb-2">🎥 Trailer (URL YouTube)</label>
            <input type="text" name="trailer_url"
                   class="w-full p-3 rounded-xl bg-gray-700 border border-gray-600 text-white
                          focus:ring-2 focus:ring-blue-400 focus:border-blue-400 transition"
                   placeholder="https://youtube.com/watch?v=..."
                   value="<?= htmlspecialchars($_POST['trailer_url'] ?? '') ?>">
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-gray-300 font-semibold mb-2">⏱️ Thời lượng (phút)</label>
                <input type="number" name="duration_min" min="1" required
                       class="w-full p-3 rounded-xl bg-gray-700 border border-gray-600 text-white
                              focus:ring-2 focus:ring-blue-400 focus:border-blue-400 transition"
                       placeholder="120"
                       value="<?= htmlspecialchars($_POST['duration_min'] ?? '') ?>">
            </div>

            <div>
                <label class="block text-gray-300 font-semibold mb-2">📅 Ngày ra mắt</label>
                <input type="date" name="release_date"
                       class="w-full p-3 rounded-xl bg-gray-700 border border-gray-600 text-white
                              focus:ring-2 focus:ring-blue-400 focus:border-blue-400 transition"
                       value="<?= htmlspecialchars($_POST['release_date'] ?? '') ?>">
            </div>
        </div>
        
        <div class="col-span-1 md:col-span-2">
            <label class="block text-gray-300 font-semibold mb-2">⭐ Đánh giá (0-10)</label>
            <input type="number" name="rating" step="0.1" min="0" max="10"
                   class="w-full p-3 rounded-xl bg-gray-700 border border-gray-600 text-white
                          focus:ring-2 focus:ring-blue-400 focus:border-blue-400 transition"
                   placeholder="8.5"
                   value="<?= htmlspecialchars($_POST['rating'] ?? '') ?>">
        </div>

    </div>
    
     <div>
      <label class="block text-gray-300 font-medium mb-2">📝 Mô tả phim</label>
      <textarea name="description" id="editor"
                class="w-full p-3 rounded-lg bg-gray-700 border border-gray-600 text-white"
                placeholder="Viết mô tả chi tiết về phim..."></textarea>
    </div>

    <script>
      ClassicEditor.create(document.querySelector('#editor')).catch(error => console.error(error));
    </script>

    <div class="flex justify-center gap-6 pt-6 border-t border-gray-700/50">
      <a href="movies.php"
         class="w-full sm:w-auto bg-gray-600 hover:bg-gray-700 text-white font-medium px-8 py-3 rounded-full shadow-lg transition duration-300 transform hover:scale-[1.03]">
        ← Hủy bỏ & Quay lại
      </a>
      <button type="submit"
              class="w-full sm:w-auto bg-blue-600 hover:bg-blue-500 text-white font-bold px-8 py-3 rounded-full shadow-lg transition duration-300 transform hover:scale-[1.03] hover:shadow-blue-500/50">
         Thêm phim mới
      </button>
    </div>
  </form>
</main>
</body>
</html>