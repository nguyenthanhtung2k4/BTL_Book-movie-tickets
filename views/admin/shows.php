<?php
$adminName = "Admin Scarlet";
$title = "Quản lý Suất Chiếu";
$pageName = "Danh sách Suất Chiếu";

// Khởi động session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../../function/reponsitory.php";
require_once __DIR__ . "/side_bar.php"; 

$showRepo = new Repository('shows');
$movieRepo = new Repository('movies');
$screenRepo = new Repository('screens');

// Phân trang: 15 suất chiếu mỗi trang, sắp xếp mới nhất theo id
$itemsPerPage = 15; 
$currentPage = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($currentPage - 1) * $itemsPerPage;

$totalShows = $showRepo->countAll();
$totalPages = ceil($totalShows / $itemsPerPage);

$shows = $showRepo->getLimitAndOffset($itemsPerPage, $offset, 'id', 'DESC');

// --- ĐỊNH NGHĨA ÁNH XẠ TRẠNG THÁI (MỚI) ---
$statusMapping = [
    'active' => ['label' => 'Đang chiếu', 'class' => 'bg-green-500/20 text-green-300'],
    'upcoming' => ['label' => 'Sắp chiếu', 'class' => 'bg-blue-500/20 text-blue-300'],
    'canceled' => ['label' => 'Đã hủy', 'class' => 'bg-red-500/20 text-red-300'],
];

// --- CÁC HÀM GIẢ ĐỊNH CHO VIỆC TRA CỨU KHÓA NGOẠI ---
function get_movie_title_by_id($id, $movieRepo) {
    $movie = $movieRepo->find($id);
    return $movie ? htmlspecialchars($movie['title']) : 'Phim (ID: ' . $id . ')';
}

function get_screen_name_by_id($id, $screenRepo) {
    // Giả định bảng 'screens' có cột 'name' hoặc 'number'
    $screen = $screenRepo->find($id);
    // Thay vì 'screen_number', giả định cột là 'name' để hiển thị thân thiện hơn
    return $screen ? 'Phòng ' . htmlspecialchars($screen['name'] ?? $id) : 'Phòng (ID: ' . $id . ')';
}
// ----------------------------------------------------

$flash_message = $_SESSION['flash_message'] ?? '';
$flash_success = $_SESSION['flash_success'] ?? false;
unset($_SESSION['flash_message'], $_SESSION['flash_success']);

$handleURL = "../../handle/shows_handle.php";
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
        <a href="addShow.php" class="bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded-lg text-white font-semibold transition shadow-md">
            Thêm Suất Chiếu Mới
        </a>
        <span class="text-sm text-gray-400">Tổng cộng: <?= $totalShows ?> suất chiếu</span>
    </div>

    <?php if ($flash_message): ?>
        <div id='flash-message' class="fixed top-6 right-6 z-50 px-6 py-3 rounded-lg shadow-lg text-white font-semibold transition-transform duration-300
             <?= $flash_success ? 'bg-green-500' : 'bg-red-600' ?>">
            <?= htmlspecialchars($flash_message) ?>
        </div>

        <?php if ($totalPages > 1): ?>
        <div class="flex justify-center mt-6 space-x-2 text-white">
            <a href="?page=<?= max(1, $currentPage - 1) ?>" class="px-4 py-2 rounded-lg <?= $currentPage == 1 ? 'bg-gray-600 text-gray-400 cursor-not-allowed' : 'bg-red-600 hover:bg-red-700' ?>">
                &laquo; Trước
            </a>
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?page=<?= $i ?>" class="px-4 py-2 rounded-lg <?= $i == $currentPage ? 'bg-red-700 font-bold' : 'bg-gray-700 hover:bg-gray-600' ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>
            <a href="?page=<?= min($totalPages, $currentPage + 1) ?>" class="px-4 py-2 rounded-lg <?= $currentPage == $totalPages ? 'bg-gray-600 text-gray-400 cursor-not-allowed' : 'bg-red-600 hover:bg-red-700' ?>">
                Sau &raquo;
            </a>
        </div>
        <?php endif; ?>
    <?php endif; ?>

    <?php if (!empty($shows)): ?>
        <div class="bg-gray-800 rounded-xl shadow-2xl overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-700">
                <thead class="table-header">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">ID</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Tên Phim</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Phòng Chiếu</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Thời Gian</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Định Dạng</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Giá Vé</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Trạng Thái</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider">Hành động</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700 text-sm">
                    <?php foreach ($shows as $show): ?>
                        <tr class="table-row text-gray-300">
                            <td class="px-6 py-4 whitespace-nowrap font-mono text-xs text-gray-400"><?= htmlspecialchars($show['id']) ?></td>
                            
                            <td class="px-6 py-4 whitespace-nowrap font-medium text-white max-w-xs truncate" title="<?= get_movie_title_by_id($show['movie_id'], $movieRepo) ?>">
                                <?= get_movie_title_by_id($show['movie_id'], $movieRepo) ?>
                            </td>
                            
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?= get_screen_name_by_id($show['screen_id'], $screenRepo) ?>
                            </td>
                            
                            <td class="px-6 py-4 whitespace-nowrap text-yellow-300">
                                <?= date('d/m/Y H:i', strtotime($show['show_time'])) ?>
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    <?= strtolower($show['format']) === 'imax' ? 'bg-red-500/20 text-red-300' : 'bg-blue-500/20 text-blue-300' ?>">
                                    <?= htmlspecialchars($show['format'] ?? '2D') ?>
                                </span>
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-green-400 font-bold">
                                <?= number_format($show['price'] ?? 0, 0, ',', '.') ?>₫
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php
                                $statusKey = htmlspecialchars($show['status'] ?? 'active');
                                $displayInfo = $statusMapping[$statusKey] ?? [
                                    'label' => 'Không rõ', 
                                    'class' => 'bg-gray-500/20 text-gray-300'
                                ];
                                
                                $status_label = $displayInfo['label'];
                                $status_class = $displayInfo['class'];
                                ?>
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $status_class ?>">
                                    <?= $status_label ?>
                                </span>
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="editShow.php?id=<?= (int)$show['id'] ?>"
                                   class="text-blue-400 hover:text-blue-500 mx-2 transition" title="Sửa">
                                    ✏️
                                </a>
                                <a href="<?= $handleURL ?>?action=delete&id=<?= (int)$show['id'] ?>"
                                   onclick="return confirm('Bạn có chắc chắn muốn xóa suất chiếu ID #<?= $show['id'] ?> không?')"
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
            <p class="mb-4"> Chưa có suất chiếu nào được lên lịch.</p>
            <p>Hãy thêm suất chiếu đầu tiên của bạn!</p>
        </div>
    <?php endif; ?>
</main>
<script>
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