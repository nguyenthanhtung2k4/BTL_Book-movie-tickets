<?php
$adminName = "Admin Scarlet";
$title = "Movies";
$pageName = "Quản lý phim";

// Đảm bảo session_start() được gọi trước khi sử dụng $_SESSION
if (session_status() == PHP_SESSION_NONE) {
      session_start();
}

require_once __DIR__ . "/../../function/reponsitory.php";
require_once __DIR__ . "/side_bar.php"; // Giả định file này chứa cấu trúc bao ngoài

$repo = new Repository('movies');
// Cải tiến: Thử sắp xếp phim theo ngày tạo hoặc ngày ra mắt mới nhất
// $movies = $repo->getAll(); 
$movies = $repo->getAll(); // Giả định Repository hỗ trợ tham số ORDER BY

$flash_message = $_SESSION['flash_message'] ?? '';
$flash_success = $_SESSION['flash_success'] ?? false;
unset($_SESSION['flash_message'], $_SESSION['flash_success']);
?>
<style>
      .card-hover:hover {
            transform: translateY(-5px);
            transition: transform 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
      }
</style>


<main class="flex-1 p-8 sm:p-10 min-h-screen">

      <h2 class="text-3xl font-bold text-red-500 mb-6"><?= $pageName ?></h2>

      <div class="mb-6">
            <a href="addMovie.php" class="bg-red-600 hover:bg-blue-700 px-4 py-2 rounded text-white font-semibold">
                  Thêm người dùng mới
            </a>
      </div>

      <?php if ($flash_message): ?>
            <div
                  class="mb-8 p-4 rounded-xl shadow-xl border <?= $flash_success ? 'bg-green-800/50 border-green-500 text-green-200' : 'bg-red-800/50 border-red-500 text-red-200' ?>">
                  <p class="font-medium"><?= htmlspecialchars($flash_message) ?></p>
            </div>
      <?php endif; ?>

      <?php if (!empty($movies)): ?>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-6">
                  <?php foreach ($movies as $movie): ?>
                        <div class="bg-gray-800 rounded-xl overflow-hidden shadow-xl card-hover border border-gray-700/50">

                              <div class="relative aspect-[2/3] bg-gray-700">
                                    <img src="<?= !empty($movie['banner_url']) ? htmlspecialchars($movie['banner_url']) : '/assets/no-banner.png' ?>"
                                          alt="Banner <?= htmlspecialchars($movie['title']) ?>" class="w-full h-full object-cover">

                                    <div class="absolute inset-0 bg-gradient-to-t from-gray-900/70 to-transparent"></div>
                                    <div
                                          class="absolute top-2 right-2 bg-yellow-500 text-gray-900 font-bold px-3 py-1 rounded-full text-xs shadow-md">
                                          ⭐ <?= htmlspecialchars($movie['rating'] ?? 'N/A') ?>
                                    </div>
                              </div>

                              <div class="p-4 pt-2">
                                    <h3 class="text-lg font-bold text-white mb-2 truncate"
                                          title="<?= htmlspecialchars($movie['title']) ?>">
                                          <?= htmlspecialchars($movie['title']) ?>
                                    </h3>

                                    <div class="text-xs text-gray-400 space-y-1">
                                          <p>
                                                <span class="font-medium text-gray-300">📅 Ra mắt:</span>
                                                <?= htmlspecialchars($movie['release_date'] ?? 'N/A') ?>
                                          </p>
                                          <p>
                                                <span class="font-medium text-gray-300">⏱️ Thời lượng:</span>
                                                <?= htmlspecialchars($movie['duration_min'] ?? '-') ?> phút
                                          </p>
                                    </div>

                                    <div class="flex justify-between items-center mt-4 pt-3 border-t border-gray-700/50">
                                          <a href="editMovie.php?id=<?= $movie['id'] ?>"
                                                class="text-blue-400 hover:text-blue-500 font-medium text-sm transition flex items-center gap-1">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                                      viewBox="0 0 24 24" stroke="currentColor">
                                                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-7-7l4 4m-4-4l-4 4" />
                                                </svg>
                                                Sửa
                                          </a>
                                          <a href="deleteMovie.php?id=<?= $movie['id'] ?>"
                                                class="text-red-400 hover:text-red-500 font-medium text-sm transition flex items-center gap-1"
                                                onclick="return confirm('Bạn có chắc chắn muốn xóa phim này?')">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                                      viewBox="0 0 24 24" stroke="currentColor">
                                                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                                Xóa
                                          </a>
                                    </div>
                              </div>
                        </div>
                  <?php endforeach; ?>
            </div>

      <?php else: ?>
            <div
                  class="text-center text-gray-400 text-xl mt-20 p-8 border-2 border-dashed border-gray-700 rounded-xl max-w-lg mx-auto">
                  <p class="mb-4"> Chưa có phim nào được thêm vào hệ thống.</p>
                  <p>Hãy thêm phim đầu tiên của bạn để bắt đầu!</p>
            </div>
      <?php endif; ?>
</main>
</body>

</html>