<?php
$adminName = "Admin Scarlet";
$title = "Xóa phim";
$pageName = "Xác nhận xóa phim";

// Khởi động session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../../function/reponsitory.php";
require_once __DIR__ . "/../../handle/movies_handle.php";
require_once __DIR__ . "/side_bar.php";

$repo = new Repository('movies');

// Lấy ID phim từ URL
$movieId = $_GET['id'] ?? null;
if (!$movieId || !is_numeric($movieId)) {
    $_SESSION['flash_message'] = '⚠️ ID phim không hợp lệ!';
    $_SESSION['flash_success'] = false;
    header('Location: movies.php');
    exit;
}

$movie = $repo->find($movieId);
if (!$movie) {
    $_SESSION['flash_message'] = ' Không tìm thấy phim!';
    $_SESSION['flash_success'] = false;
    header('Location: movies.php');
    exit;
}

?>

<main class="flex-1 p-8 sm:p-10 min-h-screen flex items-center justify-center bg-gray-900">
  
    <div class="max-w-3xl w-full">
        <div class="bg-gray-800 border border-red-700 rounded-2xl shadow-2xl shadow-red-900/40 p-8 sm:p-10 transform transition-all duration-300">
            
            <div class="flex items-center justify-center mb-8 border-b border-gray-700 pb-4">
                <div class="text-red-500 mr-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <h1 class="text-3xl font-extrabold text-red-400 tracking-tight">
                    CẢNH BÁO: XÓA VĨNH VIỄN PHIM
                </h1>
            </div>

            <div class="flex flex-col sm:flex-row gap-8 items-start mb-8">
                
                <div class="sm:w-1/3 w-full flex-shrink-0">
                    <?php if (!empty($movie['banner_url'])): ?>
                        <img src="<?= htmlspecialchars($movie['banner_url']) ?>"
                             alt="Poster phim: <?= htmlspecialchars($movie['title']) ?>"
                             class="w-full h-auto aspect-[2/3] mx-auto rounded-xl object-cover shadow-2xl border-4 border-red-500/50">
                    <?php else: ?>
                         <div class="w-full h-auto aspect-[2/3] bg-gray-700 rounded-xl flex items-center justify-center text-gray-500 border border-gray-600">
                             Không có Poster
                         </div>
                    <?php endif; ?>
                </div>

                <div class="sm:w-2/3 w-full">
                    <h2 class="text-3xl font-bold text-white mb-4 leading-snug">
                        <?= htmlspecialchars($movie['title'] ?? 'Tên phim không xác định') ?>
                    </h2>
                    
                    <ul class="text-base text-gray-300 space-y-2 mb-6 p-4 bg-gray-700/50 rounded-lg">
                        <li class="flex justify-between">
                            <span class="font-semibold text-gray-300">ID Phim:</span>
                            <span class="font-mono text-red-300"><?= htmlspecialchars($movie['id'] ?? 'N/A') ?></span>
                        </li>
                        <li class="flex justify-between">
                            <span class="font-semibold text-gray-300">Ngày ra mắt:</span>
                            <span><?= htmlspecialchars($movie['release_date'] ?? 'N/A') ?></span>
                        </li>
                        <li class="flex justify-between">
                            <span class="font-semibold text-gray-300">⏱Thời lượng:</span>
                            <span><?= htmlspecialchars($movie['duration_min'] ?? '-') ?> phút</span>
                        </li>
                        <li class="flex justify-between">
                            <span class="font-semibold text-gray-300">⭐ Rating:</span>
                            <span class="text-yellow-400 font-bold"><?= htmlspecialchars($movie['rating'] ?? 'N/A') ?></span>
                        </li>
                    </ul>

                    <p class="text-lg text-gray-400 mb-6">
                        Bạn có chắc chắn muốn xóa phim này? Hành động này **<span class="text-red-300 font-bold uppercase">không thể hoàn tác</span>** và sẽ xóa toàn bộ dữ liệu.
                    </p>
                </div>
            </div>

            <form method="POST" action="" class="flex flex-col sm:flex-row justify-center gap-4 pt-4 border-t border-gray-700">
                  <button type="submit" name="confirm_delete"
                          class="w-full sm:w-auto bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-600 text-white font-bold px-8 py-3 rounded-full shadow-lg transition duration-300 transform hover:scale-[1.03] hover:shadow-red-500/50">
                      XÁC NHẬN XÓA
                  </button>
                <a href="movies.php"
                   class="w-full sm:w-auto bg-gray-600 hover:bg-gray-500 text-white font-medium px-8 py-3 rounded-full transition duration-200 shadow-md transform hover:scale-[1.03]">
                    ✖ Hủy bỏ & Quay lại
                </a>

            </form>
        </div>
    </div>
</main>