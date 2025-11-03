<?php
$adminName = "Admin Scarlet";
$title = "Movies";
$pageName = "Quản lý phim";

// Đảm bảo session_start() được gọi trước khi sử dụng $_SESSION
if (session_status() == PHP_SESSION_NONE) {
      session_start();
}

require_once __DIR__ . "/../../function/reponsitory.php";
require_once __DIR__ . "/side_bar.php"; // Giả định side_bar.php chứa phần mở đầu HTML (<body>)

$repo = new Repository('movies');

$movies = $repo->getAll();

$flash_message = $_SESSION['flash_message'] ?? '';
$flash_success = $_SESSION['flash_success'] ?? false;
unset($_SESSION['flash_message'], $_SESSION['flash_success']);

// Định nghĩa URL xử lý (handle)
$handleURL = "../../handle/movies_handle.php";
?>
<style>
      /* Global Tailwind style setup assuming this is wrapped in a dark theme structure */
      body {
            font-family: 'Inter', sans-serif;
            background-color: #1f2937;
            color: #f3f4f6;
      }

      .card-hover {
            transition: transform 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
      }

      .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px rgba(0, 0, 0, 0.5);
      }
</style>


<main class="flex-1 p-8 sm:p-10 min-h-screen">

      <h2 class="text-3xl font-bold text-red-500 mb-6"><?= $pageName ?></h2>

      <div class="mb-6">
            <!-- 💡 SỬA: Đổi nhãn nút thành "Thêm phim mới" -->
            <a href="addMovie.php"
                  class="bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded-lg text-red font-semibold transition shadow-md">
                   Thêm phim mới
            </a>
      </div>

      <?php if ($flash_message): ?>
            <div id='flash-message' class="fixed top-6 right-6 z-50 px-6 py-3 rounded-lg shadow-lg text-white font-semibold transition-transform duration-300
             <?= $flash_success ? 'bg-green-500' : 'bg-red-600' ?>">
                  <?= htmlspecialchars($flash_message) ?>
            </div>
      <?php endif; ?>

      <?php if (!empty($movies)): ?>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-6">
                  <?php foreach ($movies as $movie): ?>
                        <div class="bg-gray-800 rounded-xl overflow-hidden shadow-xl card-hover border border-gray-700/50">

                              <div class="relative aspect-[2/3] bg-gray-700">
                                    <!-- Thêm onerror để tải placeholder nếu banner lỗi -->
                                    <img src="<?= htmlspecialchars($movie['banner_url'] ?? '') ?>"
                                          alt="Banner <?= htmlspecialchars($movie['title']) ?>"
                                          onerror="this.onerror=null; this.src='https://placehold.co/400x600/1f2937/d1d5db?text=No+Poster'"
                                          class="w-full h-full object-cover">

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
                                          <!-- 💡 SỬA LỖI: Cấu trúc URL đúng là ?id=... và sử dụng (int) để ép kiểu an toàn -->
                                          <a href="editMovie.php?id=<?= (int) $movie['id'] ?>"
                                                class="text-blue-400 hover:text-blue-500 font-medium text-sm transition flex items-center gap-1">
                                                ✏️ Sửa
                                          </a>

                                          <!-- 💡 SỬA LỖI: Trỏ đến movies_handle.php với action=delete và thêm onclick để xác nhận -->
                                          <a href="deleteMovie.php?id=<?= (int) $movie['id'] ?>"
                                                class="text-red-400 hover:text-red-500 font-medium text-sm transition flex items-center gap-1">
                                                🗑️ Xóa
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
      }, 3000); // 💡 Tăng thời gian hiển thị lên 3 giây
</script>
</body>

</html>