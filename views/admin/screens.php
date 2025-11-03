<?php
$adminName = "Admin Scarlet";
$title = "Quản lý Phòng Chiếu";
$pageName = "Danh sách Phòng Chiếu (Screens)";

// Khởi động session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../../function/reponsitory.php"; // Giả định đường dẫn đến Repository
require_once __DIR__ . "/side_bar.php"; // Giả định side_bar.php chứa phần mở đầu HTML

// Khởi tạo Repositories cho các bảng liên quan
$screenRepo = new Repository('screens');
$theaterRepo = new Repository('theaters'); // Cần để tra cứu tên rạp

$screens = $screenRepo->getAll();

// --- HÀM GIẢ ĐỊNH CHO VIỆC TRA CỨU KHÓA NGOẠI ---
function get_theater_name_by_id($id, $theaterRepo) {
    $theater = $theaterRepo->find($id);
    return $theater ? htmlspecialchars($theater['name']) : 'Rạp (ID: ' . $id . ')';
}
// ----------------------------------------------------

$flash_message = $_SESSION['flash_message'] ?? '';
$flash_success = $_SESSION['flash_success'] ?? false;
unset($_SESSION['flash_message'], $_SESSION['flash_success']);

$handleURL = "../../handle/screens_handle.php";
?>

<style>
    /* Dark Theme / Tailwind utility classes for better appearance */
    body { font-family: 'Inter', sans-serif; background-color: #1f2937; color: #f3f4f6; }
    .table-header { background-color: #374151; color: #f3f4f6; }
    .table-row:nth-child(even) { background-color: #1f2937; }
    .table-row:nth-child(odd) { background-color: #111827; }
    .table-row:hover { background-color: #4b5563; }
</style>


<main class="flex-1 p-8 sm:p-10 min-h-screen">

    <h2 class="text-3xl font-bold text-red-500 mb-6"><?= $pageName ?></h2>

    <div class="mb-6 flex justify-between items-center">
        <a href="addScreen.php" class="bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded-lg text-white font-semibold transition shadow-md">
            Thêm Phòng Chiếu Mới
        </a>
        <span class="text-sm text-gray-400">Tổng cộng: <?= count($screens) ?> phòng chiếu</span>
    </div>

    <?php if ($flash_message): ?>
        <div id='flash-message' class="fixed top-6 right-6 z-50 px-6 py-3 rounded-lg shadow-lg text-white font-semibold transition-transform duration-300
             <?= $flash_success ? 'bg-green-500' : 'bg-red-600' ?>">
            <?= htmlspecialchars($flash_message) ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($screens)): ?>
        <div class="bg-gray-800 rounded-xl shadow-2xl overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-700">
                <thead class="table-header">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">ID</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Tên Rạp</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Tên Phòng</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Loại Phòng</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Sức Chứa</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider">Hành động</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700 text-sm">
                    <?php foreach ($screens as $screen): ?>
                        <tr class="table-row text-gray-300">
                            <td class="px-6 py-4 whitespace-nowrap font-mono text-xs text-gray-400"><?= htmlspecialchars($screen['id']) ?></td>
                            
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?= get_theater_name_by_id($screen['theater_id'], $theaterRepo) ?>
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap font-medium text-white">
                                <?= htmlspecialchars($screen['name']) ?>
                            </td>
                            
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    <?= strtolower($screen['screen_type'] ?? '2d') === 'imax' ? 'bg-red-500/20 text-red-300' : 'bg-blue-500/20 text-blue-300' ?>">
                                    <?= htmlspecialchars($screen['screen_type'] ?? '2D') ?>
                                </span>
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-yellow-300 font-bold">
                                <?= number_format($screen['capacity'] ?? 0) ?> Ghế
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                
                                <a href="editSeatLayout.php?id=<?= (int)$screen['id'] ?>"
                                    class="text-green-400 hover:text-green-500 mx-2 transition" title="Sửa Sơ đồ Ghế">
                                     🪑 Sơ đồ
                                </a>

                                <a href="editScreen.php?id=<?= (int)$screen['id'] ?>"
                                    class="text-blue-400 hover:text-blue-500 mx-2 transition" title="Sửa Thông tin">
                                     ✏️
                                </a>
                                <a href="<?= $handleURL ?>?action=delete&id=<?= (int)$screen['id'] ?>"
                                    onclick="return confirm('Bạn có chắc chắn muốn xóa phòng chiếu ID #<?= $screen['id'] ?> không? Việc này sẽ xóa cả suất chiếu liên quan.')"
                                    class="text-red-400 hover:text-red-500 transition" title="Xóa">
                                     🗑️
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    <?php else: ?>
        <div
            class="text-center text-gray-400 text-xl mt-20 p-8 border-2 border-dashed border-gray-700 rounded-xl max-w-lg mx-auto">
            <p class="mb-4"> Chưa có phòng chiếu nào được thiết lập.</p>
            <p>Hãy thêm phòng chiếu đầu tiên của bạn!</p>
        </div>
    <?php endif; ?>
</main>
<script>
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