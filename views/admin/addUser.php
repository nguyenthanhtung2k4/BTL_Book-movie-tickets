<?php
require_once __DIR__ . '/../../function/reponsitory.php';
require_once __DIR__ . '/../../handle/user_handle.php';

$message = '';
$isSuccess = false;

date_default_timezone_set('Asia/Ho_Chi_Minh');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'full_name'     => trim($_POST['full_name']),
        'email'         => trim($_POST['email']),
        'password_hash' => password_hash($_POST['password'], PASSWORD_DEFAULT),
        'role'          => $_POST['role'] ?? 'customer',
        'created_at'    => date('Y-m-d H:i:s')
    ];

    $result = handle('add', $data);

    if ($result['success']) {
        $isSuccess = true;
        $message = $result['message'];
        header("Refresh: 1.5; url=users.php"); // tự chuyển hướng sau 1.5s
    } else {
        $message = $result['message'];
    }
}

$title = "Thêm người dùng";
$pageName = "Thêm người dùng mới";
require_once __DIR__ . "/side_bar.php";
?>

<main class="flex-1 p-10 text-gray-100 min-h-screen bg-gray-900 relative">

  <!--  Thông báo nổi bên trái -->
  <?php if ($message): ?>
  <div class="fixed top-6 right-6 z-50 px-6 py-3 rounded-lg shadow-lg text-white font-semibold transition-transform duration-300
              <?= $isSuccess ? 'bg-green-500' : 'bg-red-600' ?>">
      <?= htmlspecialchars($message) ?>
  </div>
  <?php endif; ?>

  <div class="max-w-2xl mx-auto bg-gray-800 rounded-2xl shadow-xl border border-gray-700 p-8 mt-10">
    <h2 class="text-3xl font-bold text-center text-red-500 mb-8 uppercase tracking-wide">
       <?= $pageName ?>
    </h2>

    <form method="POST" class="space-y-5">
      <!-- Họ tên -->
      <div>
        <label class="block mb-2 text-gray-300 font-medium">Họ và tên</label>
        <input type="text" name="full_name" required
               value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>"
               placeholder="Nhập họ tên đầy đủ..."
               class="w-full p-3 rounded-lg bg-gray-700 border border-gray-600 text-white
                      focus:outline-none focus:ring-2 focus:ring-red-400 focus:border-red-400 transition">
      </div>

      <!-- Email -->
      <div>
        <label class="block mb-2 text-gray-300 font-medium">Email</label>
        <input type="email" name="email" required
               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
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
          <option value="customer" <?= ($_POST['role'] ?? '') === 'customer' ? 'selected' : '' ?>>👥 Khách hàng</option>
          <option value="admin" <?= ($_POST['role'] ?? '') === 'admin' ? 'selected' : '' ?>>🛠️ Quản trị viên</option>
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
