<?php
/**
 * Xử lý quy trình đặt vé
 * Method: POST
 * Required: show_id, selected_seats (JSON), total_amount, payment_method
 */
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../function/reponsitory.php";

// BẮT BUỘC NGƯỜI DÙNG PHẢI ĐĂNG NHẬP MỚI ĐẶT VÉ ĐƯỢC
if (!isset($_SESSION['user']) || !isset($_SESSION['user']['id'])) {
    $_SESSION['flash_message'] = 'Vui lòng đăng nhập để đặt vé!';
    $_SESSION['flash_success'] = false;
    header('Location: ../views/clinet/account.php?view=login');
    exit;
}

$user_id = $_SESSION['user']['id'];

// Lấy dữ liệu từ form
$show_id = $_POST['show_id'] ?? null;
$selected_seats_json = $_POST['selected_seats'] ?? null;
$total_amount = $_POST['total_amount'] ?? 0;
$payment_method = $_POST['payment_method'] ?? null;


// === BẮT ĐẦU: LOGIC XỬ LÝ TRẠNG THÁI THANH TOÁN ===
$booking_status = '';
$payment_status = '';
$flash_message = '';

if ($payment_method === 'vnpay' || $payment_method === 'credit_card') {
    // Nếu là VNPay (hoặc thẻ), coi như đã thanh toán thành công
    $booking_status = 'confirmed'; // Đã xác nhận
    $payment_status = 'paid';      // Đã thanh toán
    $flash_message = 'Thanh toán thành công! Vé của bạn đã được xác nhận.';
} else {
    // Mặc định là 'cash' (tiền mặt)
    $booking_status = 'pending'; 
    $payment_status = 'unpaid';
    $flash_message = 'Đặt vé thành công! Vui lòng thanh toán tại quầy.';
}
// === KẾT THÚC: LOGIC XỬ LÝ TRẠNG THÁI ===


// Validate dữ liệu
if (!$show_id || !$selected_seats_json || !$payment_method) {
    $_SESSION['flash_message'] = 'Dữ liệu đặt vé không đầy đủ!';
    $_SESSION['flash_success'] = false;
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit;
}

// Parse JSON ghế đã chọn
$selected_seats = json_decode($selected_seats_json, true);
if (!is_array($selected_seats) || empty($selected_seats)) {
    $_SESSION['flash_message'] = 'Vui lòng chọn ít nhất một ghế!';
    $_SESSION['flash_success'] = false;
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit;
}

// Khởi tạo repositories
$bookingRepo = new Repository('bookings');
$bookingItemRepo = new Repository('booking_items');
$showRepo = new Repository('shows');
$screenRepo = new Repository('screens');

try {
    // 1. Lấy thông tin suất chiếu và giá cơ bản
    $show = $showRepo->find($show_id);
    if (!$show) {
        throw new Exception('Suất chiếu không tồn tại!');
    }
    
    $base_price = floatval($show['price']);
    $screen = $screenRepo->find($show['screen_id']);
    if (!$screen) {
        throw new Exception('Phòng chiếu không tồn tại!');
    }
    
    $seat_layout = json_decode($screen['seat_layout'], true);
    if (!$seat_layout || !isset($seat_layout['layout_details'])) {
        throw new Exception('Sơ đồ ghế không hợp lệ!');
    }
    
    // 2. Kiểm tra ghế đã được đặt chưa (Race condition prevention)
    foreach ($selected_seats as $seat_code) {
        $existing = $bookingItemRepo->findByMultipleFields([
            'show_id' => $show_id,
            'seat_code' => $seat_code
        ]);
        
        if ($existing && in_array($existing['status'], ['booked', 'checked_in'])) {
            throw new Exception("Ghế {$seat_code} đã được đặt bởi người khác!");
        }
    }
    
    // 3. Tạo bản ghi Booking chính (ĐÃ CẬP NHẬT TRẠNG THÁI)
    $booking_data = [
        'user_id' => $user_id,
        'show_id' => $show_id,
        'status' => $booking_status,     // <-- Đã sửa
        'total_amount' => $total_amount,
        'payment_method' => $payment_method,
        'payment_status' => $payment_status  // <-- Đã sửa
    ];
    
    $bookingRepo->insert($booking_data);
    
    // Lấy booking_id vừa tạo
    $booking_id = $bookingRepo->pdo->lastInsertId();
    
    // 4. Tính giá cho từng ghế và tạo booking_items
    foreach ($selected_seats as $seat_code) {
        // Phân tích seat_code (ví dụ: "A5" -> row: A, seat: 5)
        preg_match('/^([A-Z]+)(\d+)$/', $seat_code, $matches);
        if (count($matches) !== 3) {
            throw new Exception("Mã ghế không hợp lệ: {$seat_code}");
        }
        
        $row_letter = $matches[1];
        $seat_number = intval($matches[2]);
        
        // Tìm loại ghế trong layout
        $seat_type = 'standard'; // Mặc định
        foreach ($seat_layout['layout_details'] as $row) {
            if ($row['row'] === $row_letter) {
                $seat_index = $seat_number - 1;
                if (isset($row['seat_data'][$seat_index])) {
                    $seat_type = $row['seat_data'][$seat_index];
                }
                break;
            }
        }
        
        // Tính giá ghế dựa trên loại
        $seat_price = $base_price;
        switch ($seat_type) {
            case 'vip':
                $seat_price *= 1.5;
                break;
            case 'disabled':
                $seat_price *= 0.8;
                break;
            case 'aisle':
                throw new Exception("Không thể đặt lối đi: {$seat_code}");
        }
        
        // Tạo booking_item
        $item_data = [
            'booking_id' => $booking_id,
            'show_id' => $show_id,
            'seat_code' => $seat_code,
            'ticket_price' => $seat_price,
            'ticket_type' => 'adult', // Mặc định là người lớn
            'status' => 'booked'
        ];
        
        $bookingItemRepo->insert($item_data);
    }
    
    // 5. Thành công - Redirect về trang xác nhận hoặc profile (ĐÃ CẬP NHẬT THÔNG BÁO)
    $_SESSION['flash_message'] = $flash_message; // <-- Đã sửa
    $_SESSION['flash_success'] = true;
    
    // Redirect về trang profile hoặc booking confirmation
    header('Location: ../views/clinet/profile.php');
    exit;
    
} catch (Exception $e) {
    // Xử lý lỗi
    $_SESSION['flash_message'] = 'Lỗi: ' . $e->getMessage();
    $_SESSION['flash_success'] = false;
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit;
}