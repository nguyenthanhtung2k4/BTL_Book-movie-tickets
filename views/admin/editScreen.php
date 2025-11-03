<?php
$adminName = "Admin Scarlet";
$title = "Chỉnh Sửa Phòng Chiếu";
$pageName = "Cập Nhật Thông Tin Phòng Chiếu";

// Khởi động session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../../function/reponsitory.php";
require_once __DIR__ . "/side_bar.php"; // Giả định side_bar.php chứa phần mở đầu HTML (<body>)

$screenRepo = new Repository('screens');
$theaterRepo = new Repository('theaters');

$screen_id = $_GET['id'] ?? null;

// 1. Kiểm tra ID và lấy dữ liệu phòng chiếu hiện tại
if (!$screen_id || !($screen = $screenRepo->find($screen_id))) {
    $_SESSION['flash_error'] = "Phòng chiếu không tồn tại hoặc thiếu ID.";
    header("Location: index.php");
    exit;
}

// 2. Lấy danh sách tất cả rạp chiếu
$theaters = $theaterRepo->getAll();

// 3. Định nghĩa các loại phòng chiếu có sẵn
$screenTypes = ['2D', '3D', 'IMAX', 'VIP'];

$handleURL = "../../handle/screens_handle.php";
?>

<style>
    /* Sử dụng lại các class CSS từ addScreen.php */
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

    <h2 class="text-3xl font-bold text-red-500 mb-6"><?= $pageName ?>: <?= htmlspecialchars($screen['name']) ?></h2>

    <div class="bg-gray-800 rounded-xl shadow-2xl p-6 sm:p-8 max-w-2xl mx-auto">
        
        <?php if (isset($_SESSION['flash_error'])): ?>
            <div class="bg-red-900 border border-red-500 text-red-100 px-4 py-3 rounded mb-4" role="alert">
                <p><?= htmlspecialchars($_SESSION['flash_error']) ?></p>
            </div>
            <?php unset($_SESSION['flash_error']); ?>
        <?php endif; ?>

        <form action="<?= $handleURL ?>" method="POST">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" value="<?= (int)$screen_id ?>">

            <div class="mb-4">
                <label for="theater_id" class="form-label">Rạp Chiếu (Theater):</label>
                <select name="theater_id" id="theater_id" class="form-input" required>
                    <?php if (empty($theaters)): ?>
                        <option value="">-- Chưa có rạp nào --</option>
                    <?php else: ?>
                        <?php foreach ($theaters as $theater): ?>
                            <?php 
                                // Chọn rạp hiện tại của phòng chiếu
                                $selected = ($theater['id'] == $screen['theater_id']) ? 'selected' : '';
                            ?>
                            <option value="<?= htmlspecialchars($theater['id']) ?>" <?= $selected ?>>
                                <?= htmlspecialchars($theater['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
                <?php if (empty($theaters)): ?>
                    <p class="text-sm text-yellow-500 mt-2">❗ Vui lòng thêm Rạp Chiếu trước.</p>
                <?php endif; ?>
            </div>

            <div class="mb-4">
                <label for="name" class="form-label">Tên Phòng:</label>
                <input type="text" name="name" id="name" class="form-input" 
                       value="<?= htmlspecialchars($screen['name']) ?>" required>
            </div>

            <div class="mb-4">
                <label for="screen_type" class="form-label">Loại Phòng Chiếu:</label>
                <select name="screen_type" id="screen_type" class="form-input">
                    <?php foreach ($screenTypes as $type): ?>
                        <?php 
                            // Chọn loại phòng hiện tại
                            $selected = ($type == $screen['screen_type']) ? 'selected' : '';
                        ?>
                        <option value="<?= htmlspecialchars($type) ?>" <?= $selected ?>>
                            <?= htmlspecialchars($type) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="mb-6 p-4 bg-gray-700 rounded-lg">
                <p class="text-gray-300 font-semibold mb-2">Thông tin Sơ đồ Ghế (Cần chỉnh sửa riêng):</p>
                <p class="text-sm text-gray-400">Sức chứa hiện tại = <?= number_format($screen['capacity']) ?> ghế</p>
                <a href="editSeatLayout.php?id=<?= (int)$screen_id ?>" class="mt-3 inline-block bg-yellow-600 hover:bg-yellow-700 px-4 py-2 rounded-lg text-white font-semibold transition">
                    ⚙️ Chỉnh sửa Sơ đồ Ghế chi tiết
                </a>
            </div>


            <div class="flex justify-end space-x-4 mt-6">
                <a href="screens.php" class="bg-gray-600 hover:bg-gray-700 px-4 py-2 rounded-lg text-white font-semibold transition shadow-md">
                    Hủy
                </a>
                <button type="submit" class="bg-red-600 hover:bg-red-700 px-4 py-2 rounded-lg text-white font-semibold transition shadow-md">
                    Cập Nhật
                </button>
            </div>
        </form>
    </div>
</main>
</body>
</html>