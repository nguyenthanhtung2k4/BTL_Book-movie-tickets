<?php
$adminName = "Admin Scarlet";
$title = "Sửa phim";
$pageName = "Cập nhật thông tin phim";

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../../function/reponsitory.php";
require_once __DIR__ . "/../../handle/movies_handle.php";
require_once __DIR__ . "/side_bar.php";

$repo = new Repository('movies');

$movieId = $_GET['id'] ?? null;
if (!$movieId || !is_numeric($movieId)) {
    $_SESSION['flash_message'] = '⚠️ ID phim không hợp lệ!';
    $_SESSION['flash_success'] = false;
    header('Location: movies.php');
    exit;
}

$movie = $repo->find($movieId);
if (!$movie) {
    $_SESSION['flash_message'] = '❌ Không tìm thấy phim!';
    $_SESSION['flash_success'] = false;
    header('Location: movies.php');
    exit;
}


?>

  <script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>
  <style>
    body { font-family: 'Inter', sans-serif; }
    .ck-editor__editable_inline {
      background-color: #111827 !important;
      color: #f3f4f6 !important;
      border-radius: 0.5rem;
      min-height: 300px;
      border: 1px solid #374151 !important;
      padding: 1rem !important;
    }
    .ck.ck-toolbar {
      background-color: #1f2937 !important;
      border-color: #374151 !important;
      border-radius: 0.5rem 0.5rem 0 0;
    }
    .ck.ck-toolbar button.ck-button { color: #e5e7eb !important; }
  </style>
<main class="flex-1 p-8 sm:p-10 min-h-screen">
  <div class="flex justify-between items-center mb-8">
    <h2 class="text-3xl font-bold text-red-500"><?= $pageName ?></h2>
    <a href="movies.php" class="bg-gray-700 hover:bg-gray-600 px-4 py-2 rounded text-white font-medium transition">← Quay lại danh sách</a>
  </div>

  <form method="POST" action="<?=editTheater($movieId)?>" class="bg-gray-800 p-8 rounded-2xl shadow-xl border border-gray-700 space-y-6">
    <!-- Hidden ID -->
    <input type="hidden" name="id" value="<?= htmlspecialchars($movie['id']) ?>">

    <!-- Tên phim -->
    <div>
      <label class="block text-gray-300 font-medium mb-2">🎞️ Tên phim</label>
      <input type="text" name="title" required
             value="<?= htmlspecialchars($movie['title']) ?>"
             class="w-full bg-gray-900 border border-gray-700 rounded-lg p-3 text-gray-100 focus:ring-2 focus:ring-red-500">
    </div>

    <!-- Thể loại -->
    <!-- <div>
      <label class="block text-gray-300 font-medium mb-2">🎭 Thể loại</label>
      <input type="text" name="genre"
             value="<?= htmlspecialchars($movie['genre'] ?? '') ?>"
             class="w-full bg-gray-900 border border-gray-700 rounded-lg p-3 text-gray-100 focus:ring-2 focus:ring-red-500">
    </div> -->

    <!-- Thời lượng -->
    <div>
      <label class="block text-gray-300 font-medium mb-2">⏱️ Thời lượng (phút)</label>
      <input type="number" name="duration_min" required
             value="<?= htmlspecialchars($movie['duration_min'] ?? '') ?>"
             class="w-full bg-gray-900 border border-gray-700 rounded-lg p-3 text-gray-100 focus:ring-2 focus:ring-red-500">
    </div>

    <!-- Đánh giá -->
    <div>
      <label class="block text-gray-300 font-medium mb-2">⭐ Đánh giá (0 - 10)</label>
      <input type="number" step="0.1" min="0" max="10" name="rating"
             value="<?= htmlspecialchars($movie['rating'] ?? '') ?>"
             class="w-full bg-gray-900 border border-gray-700 rounded-lg p-3 text-gray-100 focus:ring-2 focus:ring-yellow-400">
    </div>

    <!-- Ngày phát hành -->
    <div>
      <label class="block text-gray-300 font-medium mb-2">📅 Ngày phát hành</label>
      <input type="date" name="release_date"
             value="<?= htmlspecialchars($movie['release_date'] ?? '') ?>"
             class="w-full bg-gray-900 border border-gray-700 rounded-lg p-3 text-gray-100 focus:ring-2 focus:ring-red-500">
    </div>

    <!-- Banner -->
    <div>
      <label class="block text-gray-300 font-medium mb-2">🖼️ Ảnh Banner (URL)</label>
      <input type="url" name="banner_url"
             value="<?= htmlspecialchars($movie['banner_url'] ?? '') ?>"
             class="w-full bg-gray-900 border border-gray-700 rounded-lg p-3 text-gray-100 focus:ring-2 focus:ring-blue-500 mb-2">
      <?php if (!empty($movie['banner_url'])): ?>
        <img src="<?= htmlspecialchars($movie['banner_url']) ?>" alt="Banner preview" class="rounded-lg w-56 h-80 object-cover border border-gray-700">
      <?php endif; ?>
    </div>

    <!-- Trailer -->
    <div>
      <label class="block text-gray-300 font-medium mb-2">🎥 Trailer (YouTube hoặc MP4 URL)</label>
      <input type="url" name="trailer_url"
             value="<?= htmlspecialchars($movie['trailer_url'] ?? '') ?>"
             class="w-full bg-gray-900 border border-gray-700 rounded-lg p-3 text-gray-100 focus:ring-2 focus:ring-blue-500 mb-2">
      <?php if (!empty($movie['trailer_url'])): ?>
        <div class="aspect-video w-full mt-3 rounded-lg overflow-hidden border border-gray-700">
          <iframe class="w-full h-full" src="<?= htmlspecialchars($movie['trailer_url']) ?>" allowfullscreen></iframe>
        </div>
      <?php endif; ?>
    </div>

    <!-- Mô tả -->
    <div>
      <label class="block text-gray-300 font-medium mb-2">📝 Mô tả phim</label>
      <textarea id="description" name="description" rows="8"><?= htmlspecialchars($movie['description'] ?? '') ?></textarea>
    </div>

    <div class="flex justify-end gap-3 pt-6 border-t border-gray-700">
      <a href="movies.php" class="bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">Hủy</a>
      <button type="submit" class="bg-gradient-to-r from-red-600 to-red-500 hover:from-red-500 hover:to-pink-500 px-6 py-2 rounded-lg text-white font-semibold shadow">
        💾 Cập nhật phim
      </button>
    </div>
  </form>
</main>

<script>
ClassicEditor
  .create(document.querySelector('#description'), {
    toolbar: [
      'undo', 'redo', '|',
      'heading', '|',
      'bold', 'italic', 'underline', 'link', '|',
      'bulletedList', 'numberedList', 'blockQuote', '|',
      'insertTable', 'imageUpload', 'mediaEmbed'
    ],
    mediaEmbed: { previewsInData: true },
    simpleUpload: { uploadUrl: '/upload_image.php' }
  })
  .catch(error => console.error('CKEditor lỗi:', error));
</script>

</body>
</html>
