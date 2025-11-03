<?php
$adminName = "Admin Scarlet";
$title = "Thêm Phòng Chiếu Mới";
$pageName = "Thêm Phòng Chiếu";

// Khởi động session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../../function/reponsitory.php";
require_once __DIR__ . "/side_bar.php"; // Giả định side_bar.php chứa phần mở đầu HTML (<body>)

// Khởi tạo Repository cho bảng theaters để lấy danh sách rạp
$theaterRepo = new Repository('theaters');
$theaters = $theaterRepo->getAll(); // Lấy tất cả rạp để hiển thị trong dropdown

$handleURL = "../../handle/screens_handle.php";
?>

<style>
    /* Dark Theme / Tailwind utility classes */
    body { font-family: 'Inter', sans-serif; background-color: #1f2937; color: #f3f4f6; }
    .form-input { 
        background-color: #374151; 
        border: 1px solid #4b5563; 
        color: #f3f4f6; 
        padding: 0.5rem 1rem; 
        border-radius: 0.5rem; 
        width: 100%;
    }
    .form-label { color: #d1d5db; font-weight: 600; margin-bottom: 0.5rem; display: block; }
</style>


<main class="flex-1 p-8 sm:p-10 min-h-screen">

    <h2 class="text-3xl font-bold text-red-500 mb-6"><?= $pageName ?></h2>

    <div class="bg-gray-800 rounded-xl shadow-2xl p-6 sm:p-8 max-w-2xl mx-auto">
        
        <?php if (isset($_SESSION['flash_error'])): ?>
            <div class="bg-red-900 border border-red-500 text-red-100 px-4 py-3 rounded mb-4" role="alert">
                <p><?= htmlspecialchars($_SESSION['flash_error']) ?></p>
            </div>
            <?php unset($_SESSION['flash_error']); ?>
        <?php endif; ?>

        <form action="<?= $handleURL ?>?action=add" method="POST">
            <input type="hidden" name="action" value="add">

            <div class="mb-4">
                <label for="theater_id" class="form-label">Rạp Chiếu (Theater):</label>
                <select name="theater_id" id="theater_id" class="form-input" required>
                    <?php if (empty($theaters)): ?>
                        <option value="">-- Chưa có rạp nào --</option>
                    <?php else: ?>
                        <?php foreach ($theaters as $theater): ?>
                            <option value="<?= htmlspecialchars($theater['id']) ?>">
                                <?= htmlspecialchars($theater['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
                <?php if (empty($theaters)): ?>
                    <p class="text-sm text-yellow-500 mt-2">❗ Vui lòng thêm Rạp Chiếu trước khi thêm Phòng Chiếu.</p>
                <?php endif; ?>
            </div>

            <div class="mb-4">
                <label for="name" class="form-label">Tên Phòng:</label>
                <input type="text" name="name" id="name" class="form-input" placeholder="Ví dụ: Phòng 1, VIP Room 3" required>
            </div>

            <div class="mb-4">
                <label for="screen_type" class="form-label">Loại Phòng Chiếu:</label>
                <select name="screen_type" id="screen_type" class="form-input">
                    <option value="2D">2D Thường</option>
                    <option value="3D">3D</option>
                    <option value="IMAX">IMAX</option>
                    <option value="VIP">Phòng VIP</option>
                </select>
            </div>

            <div class="mb-4">
                <label for="initial_capacity" class="form-label">Số lượng Ghế (Để Khởi tạo Sơ đồ Ban đầu):</label>
                <input type="number" name="initial_capacity" id="initial_capacity" class="form-input" min="10" max="200" value="50" required>
                <p class="text-sm text-gray-400 mt-1">Số ghế sẽ được chia thành các hàng mặc định. Bạn sẽ chỉnh sửa chi tiết sau.</p>
            </div>


            <div class="flex justify-end space-x-4 mt-6">
                <a href="screens.php" class="bg-gray-600 hover:bg-gray-700 px-4 py-2 rounded-lg text-white font-semibold transition shadow-md">
                    Hủy
                </a>
                <button type="submit" class="bg-red-600 hover:bg-red-700 px-4 py-2 rounded-lg text-white font-semibold transition shadow-md">
                    Thêm Phòng Chiếu
                </button>
            </div>
        </form>
    </div>
</main>
</body>
</html>