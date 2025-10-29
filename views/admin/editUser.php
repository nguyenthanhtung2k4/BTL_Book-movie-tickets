<?php
require_once __DIR__ . '/../../function/reponsitory.php';
require_once __DIR__ . '/../../handle/user_handle.php';

$message = '';
$isSuccess = false;

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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'full_name' => trim($_POST['full_name']),
        'email'     => trim($_POST['email']),
        'role'      => $_POST['role'] ?? 'customer',
        'updated_at'=> date('Y-m-d H:i:s')
    ];

    if (!empty($_POST['password'])) {
        $data['password_hash'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
    }

    if ($data['email'] !== $user['email']) {
        $existing = $repo->findBy('email', $data['email']);
        if ($existing && (int)$existing['id'] !== $id) {
            $message = '⚠️ Email đã được sử dụng bởi tài khoản khác.';
        } else {
            $result = handle('edit', $data, $id);
            if (is_array($result)) {
                $isSuccess = $result['success'];
                $message = $result['message'];
            } else {
                if ($result) {
                    $isSuccess = true;
                    $message = 'Cập nhật thành công!';
                } else {
                    $isSuccess = false;
                    $message = 'Cập nhật thất bại!';
                }
            }
        }
    } else {
        $result = handle('edit', $data, $id);
        if (is_array($result)) {
            $isSuccess = $result['success'];
            $message = $result['message'];
        } else {
            if ($result) {
                $isSuccess = true;
                $message = '✅ Cập nhật thành công!';
            } else {
                $isSuccess = false;
                $message = '❌ Cập nhật thất bại!';
            }
        }
    }

    if ($isSuccess) {
        header("Refresh: 1.2; url=users.php");
        $user = $repo->find($id);
    }
}

$title = "Sửa người dùng";
$pageName = "Chỉnh sửa người dùng";
require_once __DIR__ . "/side_bar.php";
?>

<main class="flex-1 p-10 text-gray-100 min-h-screen bg-gray-900 relative">
  <!-- Notification -->
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
        <label class="block mb-2 text-gray-300 font-medium"> Họ và tên</label>
        <input type="text" name="full_name" required
               value="<?= htmlspecialchars($_POST['full_name'] ?? $user['full_name'] ?? '') ?>"
               placeholder="Nhập họ tên đầy đủ..."
               class="w-full p-3 rounded-lg bg-gray-700 border border-gray-600 text-white
                      focus:outline-none focus:ring-2 focus:ring-red-400 focus:border-red-400 transition">
      </div>

      <!-- Email -->
      <div>
        <label class="block mb-2 text-gray-300 font-medium"> Email</label>
        <input type="email" name="email" required
               value="<?= htmlspecialchars($_POST['email'] ?? $user['email'] ?? '') ?>"
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
          <option value="customer" <?= (($_POST['role'] ?? $user['role'] ?? '') === 'customer') ? 'selected' : '' ?>> Khách hàng</option>
          <option value="admin" <?= (($_POST['role'] ?? $user['role'] ?? '') === 'admin') ? 'selected' : '' ?>>Quản trị viên</option>
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
