<?php
$adminName = "Admin Scarlet";
$title = "Movies";
$pageName = "Quản lý phim";

// Đảm bảo session_start() được gọi trước khi sử dụng $_SESSION
if (session_status() == PHP_SESSION_NONE) {
      session_start();
}

require_once __DIR__ . "/../../function/reponsitory.php";
require_once __DIR__ . "/side_bar.php";

$repo = new Repository('movies');

$movies = $repo->getAll();

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
            <div id='flash-message'  class="fixed top-6 right-6 z-50 px-6 py-3 rounded-lg shadow-lg text-white font-semibold transition-transform duration-300
              <?= $flash_success ? 'bg-green-500' : 'bg-red-600' ?>">
                  <?= htmlspecialchars($flash_message) ?>
                  
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
                                                <span class="font-medium text-gray-300">Ra mắt:</span>
                                                <?= htmlspecialchars($movie['release_date'] ?? 'N/A') ?>
                                          </p>
                                          <p>
                                                <span class="font-medium text-gray-300">⏱Thời lượng:</span>
                                                <?= htmlspecialchars($movie['duration_min'] ?? '-') ?> phút
                                          </p>
                                    </div>

                                    <div class="flex justify-between items-center mt-4 pt-3 border-t border-gray-700/50">
                                          <a href="editMovie.php?id=<?= $movie['id'] ?>"
                                                class="text-blue-400 hover:text-blue-500 font-medium text-sm transition flex items-center gap-1">
                                                <!-- <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                                      viewBox="0 0 24 24" stroke="currentColor">
                                                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-7-7l4 4m-4-4l-4 4" />
                                                </svg> -->
                                                ✏️
                                          </a>
                                          <a href="deleteMovie.php?action=delete&id=<?= $movie['id'] ?>"
                                                class="text-red-400 hover:text-red-500 font-medium text-sm transition flex items-center gap-1">
                                                🗑️
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
 <script>
    // làm mờ dần và ẩn thông báo
    setTimeout(() => {
      const flash = document.getElementById('flash-message');
      if (flash) {
        flash.style.opacity = '0';
        flash.style.transform = 'translateY(-10px)';
        setTimeout(() => flash.remove(), 500); 
      }
    }, 1500);
  </script>