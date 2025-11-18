<?php
/**
 * API Endpoint: Lấy danh sách loại ghế
 * Method: GET
 * Returns: JSON array of seat types
 */

header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../function/reponsitory.php";

try {
    $seatTypeRepo = new Repository('seat_types');
    
    // Lấy tất cả loại ghế, sắp xếp theo display_order
    $seatTypes = $seatTypeRepo->getByCondition(
        "1=1",
        [],
        "*",
        "display_order ASC, id ASC"
    );
    
    echo json_encode($seatTypes);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error', 'message' => $e->getMessage()]);
    exit;
}

