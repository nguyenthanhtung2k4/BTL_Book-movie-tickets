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
    
    // 4. Lấy thông tin ghế từ bảng seats và tạo booking_items
    $seatRepo = new Repository('seats');
    $seatTypeRepo = new Repository('seat_types');
    
    foreach ($selected_seats as $seat_code) {
        // Tìm ghế trong bảng seats
        $seat = $seatRepo->findByMultipleFields([
            'screen_id' => $screen['id'],
            'seat_code' => $seat_code
        ]);
        
        if (!$seat) {
            throw new Exception("Ghế {$seat_code} không tồn tại trong phòng chiếu!");
        }
        
        // Lấy thông tin loại ghế từ bảng seat_types
        $seatType = $seatTypeRepo->find($seat['seat_type_id']);
        if (!$seatType) {
            throw new Exception("Loại ghế không tồn tại cho ghế {$seat_code}!");
        }
        
        // Kiểm tra không cho đặt lối đi
        if (!$seatType['is_bookable'] || $seatType['code'] === 'aisle') {
            throw new Exception("Không thể đặt loại ghế này: {$seat_code} ({$seatType['name_vi']})");
        }
        
        // Tính giá ghế dựa trên price_modifier từ bảng seat_types
        $price_modifier = floatval($seatType['price_modifier']);
        $seat_price = $base_price * $price_modifier;
        
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
    
    // 5. Thành công - Redirect về trang xác nhận hoặc profile
    $_SESSION['flash_message'] = 'Đặt vé thành công! Vui lòng thanh toán tại quầy.';
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