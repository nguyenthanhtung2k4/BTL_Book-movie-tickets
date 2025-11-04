<?php
/**
 * Auth Helper Functions
 * Các hàm hỗ trợ kiểm tra xác thực và phân quyền người dùng
 */

/**
 * Kiểm tra người dùng đã đăng nhập chưa
 * @return bool
 */
function isLoggedIn() {
    return isset($_SESSION['user']) && isset($_SESSION['user']['id']);
}

/**
 * Lấy thông tin người dùng hiện tại
 * @return array|null
 */
function currentUser() {
    return $_SESSION['user'] ?? null;
}

/**
 * Lấy ID người dùng hiện tại
 * @return int|null
 */
function getUserId() {
    return $_SESSION['user']['id'] ?? null;
}

/**
 * Lấy role người dùng hiện tại
 * @return string|null ('customer', 'staff', 'admin')
 */
function getUserRole() {
    return $_SESSION['user']['role'] ?? null;
}

/**
 * Kiểm tra người dùng có phải là Admin không
 * @return bool
 */
function isAdmin() {
    return getUserRole() === 'admin';
}

/**
 * Kiểm tra người dùng có phải là Staff không
 * @return bool
 */
function isStaff() {
    return getUserRole() === 'staff';
}

/**
 * Kiểm tra người dùng có phải là Customer không
 * @return bool
 */
function isCustomer() {
    return getUserRole() === 'customer';
}

/**
 * Bắt buộc người dùng phải đăng nhập
 * Nếu chưa đăng nhập, redirect về trang login
 * @param string $redirectUrl URL để quay lại sau khi đăng nhập
 */
function requireLogin($redirectUrl = null) {
    if (!isLoggedIn()) {
        $_SESSION['flash_message'] = 'Vui lòng đăng nhập để tiếp tục!';
        $_SESSION['flash_success'] = false;
        
        $loginUrl = 'account.php?view=login';
        if ($redirectUrl) {
            $loginUrl .= '&redirect=' . urlencode($redirectUrl);
        }
        
        header('Location: ' . $loginUrl);
        exit;
    }
}

/**
 * Bắt buộc người dùng phải có quyền Admin
 * Nếu không phải admin, redirect về trang chủ
 */
function requireAdmin() {
    requireLogin(); // Đảm bảo đã đăng nhập
    
    if (!isAdmin()) {
        $_SESSION['flash_message'] = 'Bạn không có quyền truy cập trang này!';
        $_SESSION['flash_success'] = false;
        header('Location: ../clinet/index.php');
        exit;
    }
}

/**
 * Bắt buộc người dùng phải có quyền Staff hoặc Admin
 */
function requireStaffOrAdmin() {
    requireLogin();
    
    if (!isStaff() && !isAdmin()) {
        $_SESSION['flash_message'] = 'Bạn không có quyền truy cập trang này!';
        $_SESSION['flash_success'] = false;
        header('Location: ../clinet/index.php');
        exit;
    }
}

/**
 * Kiểm tra người dùng có quyền truy cập booking không
 * @param int $bookingUserId ID người dùng của booking
 * @return bool
 */
function canAccessBooking($bookingUserId) {
    if (isAdmin() || isStaff()) {
        return true; // Admin và Staff có thể xem tất cả booking
    }
    
    return getUserId() === $bookingUserId; // Customer chỉ xem được booking của mình
}

/**
 * Đăng xuất người dùng
 */
function logout() {
    // Xóa tất cả session variables
    $_SESSION = array();
    
    // Hủy session cookie
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 42000, '/');
    }
    
    // Hủy session
    session_destroy();
    
    // Redirect về trang chủ
    header('Location: index.php');
    exit;
}

