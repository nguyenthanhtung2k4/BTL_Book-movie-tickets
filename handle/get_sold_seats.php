<?php
/**
 * API Endpoint: Lấy danh sách ghế đã được đặt cho một suất chiếu
 * Method: GET
 * Params: show_id
 * Returns: JSON array of seat codes
 */

header('Content-Type: application/json');

// Start session if needed (for potential auth checks)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../function/reponsitory.php";

// Lấy show_id từ query string
$show_id = $_GET['show_id'] ?? null;

if (!$show_id || !is_numeric($show_id)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid or missing show_id parameter']);
    exit;
}

try {
    // Khởi tạo Repository cho booking_items
    $bookingItemRepo = new Repository('booking_items');
    
    // Query: Lấy tất cả ghế đã đặt (status = 'booked' hoặc 'checked_in') cho show_id này
    $bookedItems = $bookingItemRepo->getByCondition(
        "show_id = :show_id AND status IN ('booked', 'checked_in')",
        ['show_id' => $show_id],
        "seat_code"
    );
    
    // Chỉ trả về mảng seat_code
    $bookedSeats = array_column($bookedItems, 'seat_code');
    
    // Trả về JSON
    echo json_encode($bookedSeats);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error', 'message' => $e->getMessage()]);
    exit;
}

