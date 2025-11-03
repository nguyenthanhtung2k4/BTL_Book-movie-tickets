<?php
// Bắt đầu session để lấy thông báo và dữ liệu form
if (session_status() === PHP_SESSION_NONE) session_start(); 

require_once __DIR__ . '/../../function/reponsitory.php';

// Khởi tạo các biến
$message = '';
$isSuccess = false;

// 1. Lấy thông báo flash message từ Session (được gửi từ user_handle.php)
if (isset($_SESSION['flash_message'])) {
    $message = $_SESSION['flash_message'];
    $isSuccess = $_SESSION['flash_success'] ?? false;
    // Xóa session để thông báo không xuất hiện lại
    unset($_SESSION['flash_message'], $_SESSION['flash_success']);
}

// 2. Lấy dữ liệu form cũ từ Session nếu xảy ra lỗi (để giữ lại input)
if (isset($_SESSION['form_data'])) {
    $formData = $_SESSION['form_data'];
    unset($_SESSION['form_data']); // Xóa session sau khi đã lấy
}

$title = "Thêm người dùng";
$pageName = "Thêm người dùng mới";
require_once __DIR__ . "/side_bar.php";

$URL= '../../handle/user_handle.php';
?>

<main class="flex-1 p-10 text-gray-100 min-h-screen bg-gray-900 relative">

    <!--  Thông báo nổi bên phải -->
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

        <form method="POST" action="<?=$URL?>?action=add" class="space-y-5">
            <!-- Họ tên -->
            <div>
                <label class="block mb-2 text-gray-300 font-medium">Họ và tên</label>
                <!-- Sử dụng $formData thay vì $_POST -->
                <input type="text" name="full_name" required
                       value="<?= htmlspecialchars($formData['full_name'] ?? '') ?>"
                       placeholder="Nhập họ tên đầy đủ..."
                       class="w-full p-3 rounded-lg bg-gray-700 border border-gray-600 text-white
                              focus:outline-none focus:ring-2 focus:ring-red-400 focus:border-red-400 transition">
            </div>

            <!-- Email -->
            <div>
                <label class="block mb-2 text-gray-300 font-medium">Email</label>
                <!-- Sử dụng $formData thay vì $_POST -->
                <input type="email" name="email" required
                       value="<?= htmlspecialchars($formData['email'] ?? '') ?>"
                       placeholder="ví dụ: example@gmail.com"
                       class="w-full p-3 rounded-lg bg-gray-700 border border-gray-600 text-white
                              focus:outline-none focus:ring-2 focus:ring-red-400 focus:border-red-400 transition">
            </div>

            <!-- Mật khẩu -->
            <div>
                <label class="block mb-2 text-gray-300 font-medium"> Mật khẩu</label>
                <input type="password" name="password" required
                       placeholder="••••••••"
                       class="w-full p-3 rounded-lg bg-gray-700 border border-gray-600 text-white
                              focus:outline-none focus:ring-2 focus:ring-red-400 focus:border-red-400 transition">
            </div>

            <!-- Vai trò -->
            <div>
                <label class="block mb-2 text-gray-300 font-medium">Vai trò</label>
                <select name="role"
                        class="w-full p-3 rounded-lg bg-gray-700 border border-gray-600 text-white
                               focus:outline-none focus:ring-2 focus:ring-red-400 focus:border-red-400 transition">
                    <!-- Sử dụng $formData cho selected -->
                    <option value="customer" <?= ($formData['role'] ?? 'customer') === 'customer' ? 'selected' : '' ?>>👥 Khách hàng</option>
                    <option value="admin" <?= ($formData['role'] ?? '') === 'admin' ? 'selected' : '' ?>>🛠️ Quản trị viên</option>
                </select>
            </div>

            <!-- Buttons -->
            <div class="flex justify-center gap-6 pt-4">
                <button type="submit"
                        class="bg-red-500 hover:bg-red-600 text-white font-semibold px-8 py-2.5 rounded-lg
                                shadow-md hover:shadow-lg transition-all duration-200">
                    Thêm người dùng
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
    setTimeout(() => {
        const flash = document.getElementById('flash-message');
        if (flash) {
            flash.style.opacity = '0';
            flash.style.transform = 'translateY(-10px)';
            setTimeout(() => flash.remove(), 500);
        }
    }, 3000); // Tăng thời gian hiển thị lên 3 giây
</script>