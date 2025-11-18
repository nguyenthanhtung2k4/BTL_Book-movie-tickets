<?php
/**
 * API Endpoint: Lấy danh sách ghế của một screen
 * Method: GET
 * Params: screen_id
 * Returns: JSON array of seats
 */

header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../function/reponsitory.php";

$screen_id = $_GET['screen_id'] ?? null;

if (!$screen_id || !is_numeric($screen_id)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid or missing screen_id parameter']);
    exit;
}

try {
    $seatRepo = new Repository('seats');
    
    // Lấy tất cả ghế của screen, sắp xếp theo hàng và số ghế
    $seats = $seatRepo->getByCondition(
        "screen_id = :screen_id",
        ['screen_id' => $screen_id],
        "*",
        "row_letter ASC, seat_number ASC"
    );
    
    // Lấy thông tin loại ghế
    $seatTypeRepo = new Repository('seat_types');
    
    // Format dữ liệu để dễ sử dụng (JOIN với seat_types)
    $formattedSeats = [];
    foreach ($seats as $seat) {
        $seatType = $seatTypeRepo->find($seat['seat_type_id']);
        
        $formattedSeats[] = [
            'id' => $seat['id'],
            'row_letter' => $seat['row_letter'],
            'seat_number' => $seat['seat_number'],
            'seat_code' => $seat['seat_code'],
            'seat_type_id' => $seat['seat_type_id'],
            'seat_type_code' => $seatType['code'] ?? 'standard',
            'seat_type_name' => $seatType['name_vi'] ?? 'Ghế Thường',
            'price_modifier' => floatval($seatType['price_modifier'] ?? 1.0),
            'color_code' => $seatType['color_code'] ?? '#3b82f6',
            'is_bookable' => (bool)($seatType['is_bookable'] ?? 1),
            'position_order' => $seat['position_order']
        ];
    }
    
    echo json_encode($formattedSeats);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error', 'message' => $e->getMessage()]);
    exit;
}

