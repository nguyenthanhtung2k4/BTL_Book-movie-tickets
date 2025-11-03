<?php
$adminName = "Admin Scarlet";
$title = "Sửa phim";
$pageName = "Cập nhật thông tin phim";

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../../function/reponsitory.php";
require_once __DIR__ . "/side_bar.php";

$repo = new Repository('movies');

// Khởi tạo các biến
$message = '';
$isSuccess = false;
$formData = []; // Mảng này sẽ lưu trữ dữ liệu form cũ nếu có lỗi

// 1. Lấy thông báo flash message từ Session (được gửi từ movies_handle.php)
if (isset($_SESSION['flash_message'])) {
    $message = $_SESSION['flash_message'];
    $isSuccess = $_SESSION['flash_success'] ?? false;
    // Xóa session để thông báo không xuất hiện lại
    unset($_SESSION['flash_message'], $_SESSION['flash_success']);
}

// 2. Lấy dữ liệu form cũ từ Session nếu xảy ra lỗi (để giữ lại input)
if (isset($_SESSION['form_data'])) {
    $formData = $_SESSION['form_data'];
    unset($_SESSION['form_data']); // Xóa session sau khi đã lấy
}

// query
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header('Location: movies.php');
    exit;
}
// 💡 SỬA: Dùng biến $movie thay vì $user
$movie = $repo->find($id); 
if (!$movie) {
    header('Location: movies.php');
    exit;
}

// Xác định dữ liệu đang được sử dụng (ưu tiên formData nếu có lỗi)
$currentData = array_merge($movie, $formData);

$URL= "../../handle/movies_handle.php";

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
    <!-- Notification -->
    <?php if ($message): ?>
    <div id="flash-message" class="fixed top-6 right-6 z-50 px-6 py-3 rounded-lg shadow-lg text-white font-semibold transition-transform duration-300
                 <?= $isSuccess ? 'bg-green-500' : 'bg-red-600' ?>">
        <?= htmlspecialchars($message) ?>
    </div>
    <?php endif; ?>
    
    <div class="flex justify-between items-center mb-8">
        <h2 class="text-3xl font-bold text-red-500"><?= $pageName ?></h2>
        <a href="movies.php" class="bg-gray-700 hover:bg-gray-600 px-4 py-2 rounded text-white font-medium transition">← Quay lại danh sách</a>
    </div>

    <form method="POST" action="<?=$URL?>?action=edit" class="bg-gray-800 p-8 rounded-2xl shadow-xl border border-gray-700 space-y-6">
        <!-- Hidden ID -->
        <input type="hidden" name="id" value="<?= htmlspecialchars($currentData['id']) ?>">

        <!-- Tên phim -->
        <div>
            <label class="block text-gray-300 font-medium mb-2">🎞️ Tên phim</label>
            <input type="text" name="title" required
                   value="<?= htmlspecialchars($currentData['title'] ?? '') ?>"
                   placeholder="Nhập tên phim"
                   class="w-full bg-gray-900 border border-gray-700 rounded-lg p-3 text-gray-100 focus:ring-2 focus:ring-red-500">
        </div>

        <!-- Thời lượng -->
        <div>
            <label class="block text-gray-300 font-medium mb-2">⏱️ Thời lượng (phút)</label>
            <input type="number" name="duration_min" required
                   value="<?= htmlspecialchars($currentData['duration_min'] ?? '') ?>"
                   placeholder="Ví dụ: 120"
                   class="w-full bg-gray-900 border border-gray-700 rounded-lg p-3 text-gray-100 focus:ring-2 focus:ring-red-500">
        </div>

        <!-- Đánh giá -->
        <div>
            <label class="block text-gray-300 font-medium mb-2">⭐ Đánh giá (0 - 10)</label>
            <input type="number" step="0.1" min="0" max="10" name="rating"
                   value="<?= htmlspecialchars($currentData['rating'] ?? '') ?>"
                   placeholder="Ví dụ: 8.5"
                   class="w-full bg-gray-900 border border-gray-700 rounded-lg p-3 text-gray-100 focus:ring-2 focus:ring-yellow-400">
        </div>

        <!-- Ngày phát hành -->
        <div>
            <label class="block text-gray-300 font-medium mb-2">📅 Ngày phát hành</label>
            <input type="date" name="release_date"
                   value="<?= htmlspecialchars($currentData['release_date'] ?? '') ?>"
                   class="w-full bg-gray-900 border border-gray-700 rounded-lg p-3 text-gray-100 focus:ring-2 focus:ring-red-500">
        </div>

        <!-- Banner -->
        <div>
            <label class="block text-gray-300 font-medium mb-2">🖼️ Ảnh Banner (URL)</label>
            <input type="url" name="banner_url"
                   value="<?= htmlspecialchars($currentData['banner_url'] ?? '') ?>"
                   placeholder="URL của ảnh banner"
                   class="w-full bg-gray-900 border border-gray-700 rounded-lg p-3 text-gray-100 focus:ring-2 focus:ring-blue-500 mb-2">
            <?php if (!empty($currentData['banner_url'])): ?>
                <!-- SỬA: Dùng $currentData['banner_url'] để hiển thị preview -->
                <img src="<?= htmlspecialchars($currentData['banner_url']) ?>" alt="Banner preview" class="rounded-lg w-56 h-80 object-cover border border-gray-700">
            <?php endif; ?>
        </div>

        <!-- Trailer -->
        <div>
            <label class="block text-gray-300 font-medium mb-2">🎥 Trailer (YouTube hoặc MP4 URL)</label>
            <input type="url" name="trailer_url"
                   value="<?= htmlspecialchars($currentData['trailer_url'] ?? '') ?>"
                   placeholder="URL của trailer"
                   class="w-full bg-gray-900 border border-gray-700 rounded-lg p-3 text-gray-100 focus:ring-2 focus:ring-blue-500 mb-2">
            <?php if (!empty($currentData['trailer_url'])): ?>
                <!-- SỬA: Dùng $currentData['trailer_url'] để hiển thị preview -->
                <div class="aspect-video w-full mt-3 rounded-lg overflow-hidden border border-gray-700">
                    <iframe class="w-full h-full" src="<?= htmlspecialchars($currentData['trailer_url']) ?>" allowfullscreen></iframe>
                </div>
            <?php endif; ?>
        </div>

        <!-- Mô tả -->
        <div>
            <label class="block text-gray-300 font-medium mb-2">📝 Mô tả phim</label>
            <!-- 💡 SỬA: Dùng $currentData để repopulate trong textarea -->
            <textarea id="description" name="description" rows="8"><?= htmlspecialchars($currentData['description'] ?? '') ?></textarea>
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
    
    // Script ẩn thông báo flash message sau 3 giây
    setTimeout(() => {
        const flash = document.getElementById('flash-message');
        if (flash) {
            flash.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            flash.style.opacity = '0';
            flash.style.transform = 'translateY(-10px)';
            setTimeout(() => flash.remove(), 500);
        }
    }, 3000); 
</script>
</body>
</html>
