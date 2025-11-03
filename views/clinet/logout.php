<?php
// Luôn bắt đầu session
session_start();

// Hủy tất cả dữ liệu session (xóa thông tin đăng nhập)
session_unset();
session_destroy();

// Chuyển hướng người dùng về trang chủ
// (Sửa lỗi: trỏ đến 'clinet' thay vì 'client')
header('Location: index.php');
exit;
?>