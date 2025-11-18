<?php
$adminName = "Admin Scarlet";
$title = "Quản lý Đơn Đặt Vé";
$pageName = "Danh sách Đơn Đặt Vé (Bookings)";

// Khởi động session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../../function/reponsitory.php";
require_once __DIR__ . "/side_bar.php"; 

// Khởi tạo Repositories
$bookingRepo = new Repository('bookings');
$userRepo = new Repository('users');

$itemsPerPage = 10;
$currentPage = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($currentPage - 1) * $itemsPerPage;

$totalBookings = $bookingRepo->countAll();
$totalPages = ceil($totalBookings / $itemsPerPage);

$bookings = $bookingRepo->getLimitAndOffset($itemsPerPage, $offset, 'created_at', 'DESC'); 

$statusMapping = [
    'pending' => ['label' => 'Đang chờ', 'class' => 'bg-yellow-500/20 text-yellow-300'],
    'confirmed' => ['label' => 'Đã xác nhận', 'class' => 'bg-green-500/20 text-green-300'],
    'canceled' => ['label' => 'Đã hủy', 'class' => 'bg-red-500/20 text-red-300'],
];

$paymentMapping = [
    'cash' => 'Tiền mặt',
    'credit_card' => 'Thẻ tín dụng',
    'vnpay' => 'VNPay',
];

// Hàm giả định lấy tên người dùng (cần join hoặc tra cứu)
function get_user_name_by_id($id, $userRepo) {
    $user = $userRepo->find($id);
    return $user ? htmlspecialchars($user['name'] ?? $user['email'] ?? 'User ID: ' . $id) : 'Người dùng (ID: ' . $id . ')';
}
// ------------------------------------------------------------------

$flash_message = $_SESSION['flash_message'] ?? '';
$flash_success = $_SESSION['flash_success'] ?? false;
unset($_SESSION['flash_message'], $_SESSION['flash_success']);
?>

<style>
    body { font-family: 'Inter', sans-serif; background-color: #1f2937; color: #f3f4f6; }
    .table-header { background-color: #374151; color: #f3f4f6; }
    .table-row:nth-child(even) { background-color: #1f2937; }
    .table-row:nth-child(odd) { background-color: #111827; }
    .table-row:hover { background-color: #4b5563; }
    .detail-row { display: none; background-color: #111827; } /* Dùng để ẩn/hiện chi tiết */
</style>

<main class="flex-1 p-8 sm:p-10 min-h-screen">

    <h2 class="text-3xl font-bold text-red-500 mb-6"><?= $pageName ?></h2>

    <div class="mb-6 flex justify-between items-center">
        <span class="text-sm text-gray-400">Tổng số đơn: <?= $totalBookings ?></span>
    </div>

    <?php if ($flash_message): ?>
        <div id='flash-message' class="fixed top-6 right-6 z-50 px-6 py-3 rounded-lg shadow-lg text-white font-semibold transition-transform duration-300
             <?= $flash_success ? 'bg-green-500' : 'bg-red-600' ?>">
            <?= htmlspecialchars($flash_message) ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($bookings)): ?>
        <div class="bg-gray-800 rounded-xl shadow-2xl overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-700">
                <thead class="table-header">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Mã Đơn</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Người Mua</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Tổng Tiền</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">P.Thức TT</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">T.Thái TT</th> <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">T.Thái Đơn</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Thời Gian</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider">Hành động</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700 text-sm">
                    <?php foreach ($bookings as $booking): 
                        $orderStatus = $statusMapping[$booking['status']] ?? $statusMapping['pending'];
                        $paymentMethod = $paymentMapping[$booking['payment_method']] ?? 'Không rõ';
                        // Thêm logic cho Trạng thái Thanh toán
                        $paymentStatus = strtolower($booking['payment_status']) === 'paid' 
                                       ? ['label' => 'Đã trả', 'class' => 'bg-green-500/20 text-green-300'] 
                                       : ['label' => 'Chưa trả', 'class' => 'bg-red-500/20 text-red-300'];
                    ?>
                        <tr class="table-row text-gray-300">
                            <td class="px-6 py-4 whitespace-nowrap font-mono text-xs text-red-300">#<?= htmlspecialchars($booking['id']) ?></td>
                            
                            <td class="px-6 py-4 whitespace-nowrap font-medium text-white max-w-xs truncate">
                                <?= get_user_name_by_id($booking['user_id'], $userRepo) ?>
                            </td>
                            
                            <td class="px-6 py-4 whitespace-nowrap text-green-400 font-bold">
                                <?= number_format($booking['total_amount'] ?? 0, 0, ',', '.') ?>₫
                            </td>
                            
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?= $paymentMethod ?>
                            </td>
                            
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $paymentStatus['class'] ?>">
                                    <?= $paymentStatus['label'] ?>
                                </span>
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $orderStatus['class'] ?>">
                                    <?= $orderStatus['label'] ?>
                                </span>
                            </td>
                            
                            <td class="px-6 py-4 whitespace-nowrap text-gray-400">
                                <?= date('d/m/Y H:i', strtotime($booking['created_at'])) ?>
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button type="button" data-id="<?= (int)$booking['id'] ?>"
                                   class="view-detail-btn text-blue-400 hover:text-blue-500 mx-2 transition" title="Xem Chi tiết">
                                    👁️ Xem chi tiết
                                </button>
                            </td>
                        </tr>
                        
                        <tr id="detail-<?= (int)$booking['id'] ?>" class="detail-row">
                            <td colspan="8" class="p-0">
                                <div class="p-4" id="detail-content-<?= (int)$booking['id'] ?>">
                                    <p class="text-center text-gray-400">Đang tải chi tiết...</p>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
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

    <?php else: ?>
        <div class="text-center text-gray-400 text-xl mt-20 p-8 border-2 border-dashed border-gray-700 rounded-xl max-w-lg mx-auto">
            <p> Chưa có đơn đặt vé nào.</p>
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

    // --- LOGIC XEM CHI TIẾT DÙNG AJAX (JavaScript) ---
    document.addEventListener('DOMContentLoaded', () => {
        const detailButtons = document.querySelectorAll('.view-detail-btn');
        // Đảm bảo đường dẫn này đúng với vị trí file handler của bạn
        const handlerUrl = '../../handle/booking_detail_handler.php'; 

        detailButtons.forEach(button => {
            button.addEventListener('click', async (e) => {
                const bookingId = e.target.dataset.id;
                const detailRow = document.getElementById(`detail-${bookingId}`);
                const detailContent = document.getElementById(`detail-content-${bookingId}`);

                // 1. Toggle hiển thị hàng chi tiết
                const isHidden = detailRow.style.display === 'none' || detailRow.style.display === '';
                detailRow.style.display = isHidden ? 'table-row' : 'none';
                e.target.innerText = isHidden ? 'Ẩn chi tiết' : '👁️ Xem chi tiết';

                // 2. Chỉ tải dữ liệu nếu đang mở và nội dung chưa được tải
                if (isHidden) {
                    detailContent.innerHTML = '<p class="text-center text-gray-400">Đang tải chi tiết...</p>';

                    try {
                        const response = await fetch(`${handlerUrl}?id=${bookingId}`);
                        const htmlContent = await response.text();
                        
                        detailContent.innerHTML = htmlContent;
                    } catch (error) {
                        console.error('Lỗi tải chi tiết booking:', error);
                        detailContent.innerHTML = '<p class="text-center text-red-400">Lỗi khi tải dữ liệu chi tiết.</p>';
                    }
                }
            });
        });
    });
</script>
</body>
</html>