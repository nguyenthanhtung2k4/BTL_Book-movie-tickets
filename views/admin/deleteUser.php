<?php
// views/admin/deleteUser.php
session_start();
require_once __DIR__ . '/../../handle/user_handle.php'; // đường dẫn tới handle/user_handle.php — sửa nếu cần
require_once __DIR__ . '/../../function/reponsitory.php';
require_once __DIR__ . '/side_bar.php';

$id = $_GET['id'] ?? $_POST['id'] ?? null;
if (!$id || !is_numeric($id)) {
      header('Location: users.php');
      exit;
}

$message = '';
$isSuccess = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['confirm'] ?? '') === 'yes') {
      $res = handle('delete', [], (int) $id);
      $isSuccess = $res['success'] ?? false;
      $message = $res['message'] ?? ($isSuccess ? 'Xóa thành công' : 'Xóa thất bại');

      // set flash và redirect
      $_SESSION['flash_message'] = $message;
      $_SESSION['flash_success'] = $isSuccess;
      header('Location: users.php');
      exit;
}
?>


<style>
      /* Tùy chỉnh nhỏ để nâng cao trải nghiệm */
      .button-delete:hover {
            transform: scale(1.05);
      }
</style>
<main class="w-full">
      <div class="max-w-md mx-auto mt-20">
            <div class="bg-gray-800 border border-red-700 rounded-2xl p-8 text-center shadow-2xl shadow-red-900/50">

                  <div
                        class="text-red-500 mb-6 mx-auto w-16 h-16 flex items-center justify-center rounded-full bg-red-900/30">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24"
                              stroke="currentColor" stroke-width="2">
                              <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                  </div>

                  <h1 class="text-3xl font-extrabold text-red-400 mb-4 tracking-tight">CẢNH BÁO NGUY HIỂM!</h1>

                  <p class="mb-6 text-gray-300 text-lg">
                        Bạn có chắc chắn muốn <span class="font-black text-red-300 uppercase">XÓA VĨNH VIỄN</span> người
                        dùng có ID:
                        <span
                              class="inline-block bg-red-900/50 text-white font-mono px-3 py-1 rounded-lg ml-2 border border-red-700">
                              <?= htmlspecialchars($id) ?>
                        </span>
                  </p>

                  <p class="mt-4 mb-8 text-sm text-gray-400">
                        Lưu ý: Thao tác này sẽ xóa toàn bộ dữ liệu liên quan và <span
                              class="font-bold text-yellow-400">không thể hoàn tác</span>.
                  </p>

                  <div class="flex flex-col sm:flex-row justify-center gap-4">
                        <form method="POST">
                              <input type="hidden" name="id" value="<?= htmlspecialchars($id) ?>">
                              <input type="hidden" name="confirm" value="yes">
                              <button type="submit"
                                    class="button-delete w-full sm:w-auto bg-red-600 hover:bg-red-700 text-white font-bold px-6 py-3 rounded-xl shadow-lg transition duration-200">
                                    XÁC NHẬN XÓA
                              </button>
                        </form>

                        <a href="users.php"
                              class="w-full sm:w-auto bg-gray-600 hover:bg-gray-700 text-white font-medium px-6 py-3 rounded-xl shadow transition duration-200 hover:scale-[1.03] flex items-center justify-center">
                              Hủy bỏ & Quay lại
                        </a>
                  </div>

            </div>
      </div>
</main>
</body>

</html>