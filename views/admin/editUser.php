<?php

if (session_status() === PHP_SESSION_NONE) session_start(); 

$title = "Sửa người dùng";
$pageName = "Chỉnh sửa người dùng";
require_once __DIR__ . "/side_bar.php";

require_once __DIR__ . '/../../function/reponsitory.php';

// Khởi tạo các biến
$message = '';
$isSuccess = false;
$formData = []; // Mảng này sẽ lưu trữ dữ liệu form cũ nếu có lỗi

// 1. Lấy thông báo flash message từ Session (được gửi từ user_handle.php)
if (isset($_SESSION['flash_message'])) {
    $message = $_SESSION['flash_message'];
    $isSuccess = $_SESSION['flash_success'] ?? false;
    // Xóa session để thông báo không xuất hiện lại
    unset($_SESSION['flash_message'], $_SESSION['flash_success']);
}

// 2. Lấy dữ liệu form cũ từ Session nếu xảy ra lỗi (để giữ lại input)
// $formData sẽ chứa các key: 'full_name', 'email', 'role' giống như $_POST
if (isset($_SESSION['form_data'])) {
    $formData = $_SESSION['form_data'];
    unset($_SESSION['form_data']); // Xóa session sau khi đã lấy
}

// query
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$repo = new Repository('users');

if ($id <= 0) {
    header('Location: users.php');
    exit;
}
$user = $repo->find($id);
if (!$user) {
    header('Location: users.php');
    exit;
}

$URL= '../../handle/user_handle.php';

?>

<main class="flex-1 p-10 text-gray-100 min-h-screen bg-gray-900 relative">
    <!-- Notification -->
    <?php if ($message): ?>
    <div id="flash-message" class="fixed top-6 right-6 z-50 px-6 py-3 rounded-lg shadow-lg text-white font-semibold transition-transform duration-300
                 <?= $isSuccess ? 'bg-green-500' : 'bg-red-600' ?>">
        <?= htmlspecialchars($message) ?>
    </div>
    <?php endif; ?>

    <div class="max-w-2xl mx-auto bg-gray-800 rounded-2xl shadow-xl border border-gray-700 p-8 mt-10">
        <h2 class="text-3xl font-bold text-center text-red-500 mb-8 uppercase tracking-wide">
            <?= $pageName ?>
        </h2>

        <!-- Action đã được sửa đúng cú pháp và action -->
        <form method="POST" action="<?=$URL?>?action=edit&id=<?=$id?>" class="space-y-5">
            <!-- Họ tên -->
            <div>
                <label class="block mb-2 text-gray-300 font-medium"> Họ và tên</label>
                <input type="text" name="full_name" required
                       
                       value="<?= htmlspecialchars($formData['full_name'] ?? $user['full_name'] ?? '') ?>"
                       placeholder="Nhập họ tên đầy đủ..."
                       class="w-full p-3 rounded-lg bg-gray-700 border border-gray-600 text-white
                              focus:outline-none focus:ring-2 focus:ring-red-400 focus:border-red-400 transition">
            </div>

            <!-- Email -->
            <div>
                <label class="block mb-2 text-gray-300 font-medium"> Email</label>
                <input type="email" name="email" required
                       
                       value="<?= htmlspecialchars($formData['email'] ?? $user['email'] ?? '') ?>"
                       placeholder="ví dụ: example@gmail.com"
                       class="w-full p-3 rounded-lg bg-gray-700 border border-gray-600 text-white
                              focus:outline-none focus:ring-2 focus:ring-red-400 focus:border-red-400 transition">
            </div>

            <div>
                <label class="block mb-2 text-gray-300 font-medium"> Mật khẩu (để trống nếu không đổi)</label>
                <input type="password" name="password"
                       placeholder="••••••••"
                       class="w-full p-3 rounded-lg bg-gray-700 border border-gray-600 text-white
                              focus:outline-none focus:ring-2 focus:ring-red-400 focus:border-red-400 transition">
            </div>

            <!-- Vai trò -->
            <div>
                <label class="block mb-2 text-gray-300 font-medium"> Vai trò</label>
                <select name="role"
                        class="w-full p-3 rounded-lg bg-gray-700 border border-gray-600 text-white
                               focus:outline-none focus:ring-2 focus:ring-red-400 focus:border-red-400 transition">
                    <!-- SỬA ĐÚNG LOGIC: Dùng $formData['role'] để chọn option khi có lỗi -->
                    <?php $currentRole = $formData['role'] ?? $user['role'] ?? 'customer'; ?>
                    <option value="customer" <?= ($currentRole === 'customer') ? 'selected' : '' ?>>👥 Khách hàng</option>
                    <option value="admin" <?= ($currentRole === 'admin') ? 'selected' : '' ?>>🛠️ Quản trị viên</option>
                </select>
            </div>

            <div class="flex justify-center gap-6 pt-4">
                <button type="submit"
                        class="bg-red-500 hover:bg-red-600 text-white font-semibold px-8 py-2.5 rounded-lg
                                shadow-md hover:shadow-lg transition-all duration-200">
                    Lưu thay đổi
                </button>

                <a href="users.php"
                   class="bg-gray-600 hover:bg-gray-700 text-white font-medium px-8 py-2.5 rounded-lg
                           shadow-md hover:shadow-lg transition-all duration-200">
                    ← Quay lại
                </a>
            </div>
        </form>
    </div>
</main>
<script>
    // Script ẩn thông báo flash message sau 3 giây
    setTimeout(() => {
        const flash = document.getElementById('flash-message');
        if (flash) {
            // Thêm transition CSS nếu chưa có để ẩn mượt mà hơn
            flash.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            flash.style.opacity = '0';
            flash.style.transform = 'translateY(-10px)';
            setTimeout(() => flash.remove(), 500);
        }
    }, 3000); // Tăng thời gian hiển thị lên 3 giây
</script>
