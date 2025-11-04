<?php
/**
 * Logout Handler
 * Xử lý đăng xuất người dùng
 */

session_start();

// Xóa tất cả session variables
$_SESSION = array();

// Hủy session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 42000, '/');
}

// Hủy session
session_destroy();

// Set flash message
session_start(); // Start lại session để set message
$_SESSION['flash_message'] = 'Đã đăng xuất thành công!';
$_SESSION['flash_success'] = true;

// Redirect về trang chủ
header('Location: index.php');
exit;
