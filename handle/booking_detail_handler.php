<?php


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../function/reponsitory.php"; 

$itemRepo = new Repository('booking_items'); 
$booking_id = $_GET['id'] ?? null;

if (!$booking_id) {
    echo "<p class='text-red-400 text-center p-4'>Thiếu ID đơn đặt vé.</p>";
    exit;
}

$bookingItems = $itemRepo->findAllBy('booking_id', $booking_id);

if (empty($bookingItems) || !is_array($bookingItems)) {
    echo "<p class='text-center text-gray-400 p-4'>Đơn hàng này chưa có chi tiết vé nào được ghi nhận hoặc dữ liệu trả về bị lỗi.</p>";
    exit;
}

// --- LOGIC ÁNH XẠ TRẠNG THÁI VÉ ---
function getItemStatusMapping($status) {
    $status = strtolower($status);
    switch ($status) {
        case 'booked':
            return ['label' => 'Đã đặt', 'class' => 'bg-yellow-500/20 text-yellow-300'];
        case 'checked_in':
            return ['label' => 'Đã Check-in', 'class' => 'bg-green-500/20 text-green-300'];
        case 'cancelled':
            return ['label' => 'Đã Hủy', 'class' => 'bg-red-500/20 text-red-300'];
        default:
            return ['label' => 'Không rõ', 'class' => 'bg-gray-500/20 text-gray-300'];
    }
}
// --------------------------------------------------------

?>

<div class="bg-gray-700/50 p-4 rounded-lg mt-2 border border-gray-600">
    <h4 class="text-lg font-bold text-red-300 mb-3">Chi Tiết Vé (Booking Items)</h4>
    <table class="min-w-full divide-y divide-gray-600 text-sm">
        <thead class="bg-gray-600">
            <tr>
                <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wider">Mã Vé ID</th>
                <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wider">Mã Ghế</th>
                <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wider">Loại Vé</th>
                <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wider">Giá</th>
                <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wider">Trạng Thái</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-700">
            <?php foreach ($bookingItems as $item): 
                $itemStatus = getItemStatusMapping($item['status'] ?? 'default');
            ?>
                <tr class="text-gray-300 hover:bg-gray-700/70">
                    <td class="px-3 py-2 whitespace-nowrap font-mono text-xs text-gray-400">#<?= htmlspecialchars($item['id'] ?? '') ?></td>
                    <td class="px-3 py-2 whitespace-nowrap font-bold text-yellow-300"><?= htmlspecialchars($item['seat_code'] ?? '') ?></td>
                    <td class="px-3 py-2 whitespace-nowrap"><?= htmlspecialchars($item['ticket_type'] ?? '') ?></td>
                    <td class="px-3 py-2 whitespace-nowrap text-green-400"><?= number_format($item['ticket_price'] ?? 0, 0, ',', '.') ?>₫</td>
                    <td class="px-3 py-2 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $itemStatus['class'] ?>">
                            <?= $itemStatus['label'] ?>
                        </span>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>