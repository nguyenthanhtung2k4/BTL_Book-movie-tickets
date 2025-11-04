<?php
session_start();

require_once __DIR__ . '/../../handle/user_handle.php';
require_once __DIR__ . '/../../function/reponsitory.php'; // Cần cho việc tìm kiếm user

$message = '';
$message_type = ''; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // 1. XỬ LÝ ĐĂNG KÝ
    if (isset($_POST['action']) && $_POST['action'] === 'register') {
        $data = [
            'full_name' => trim($_POST['fullname'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'password_hash' => password_hash($_POST['password'] ?? '', PASSWORD_DEFAULT),
            'role' => 'customer' // SỬA: 'client' -> 'customer' để khớp với ENUM trong database
        ];

        if ($_POST['password'] !== $_POST['confirmPassword']) {
            $message = '❌ Mật khẩu xác nhận không trùng khớp.';
            $message_type = 'error';
        } else {
            $response = handleUser('add', $data); 
            $message = $response['message'];
            $message_type = $response['success'] ? 'success' : 'error';
        }
    }

    // 2. LOGIC ĐĂNG NHẬP
    if (isset($_POST['action']) && $_POST['action'] === 'login') {
        $repo = new Repository('users');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        // Tìm người dùng bằng email
        $user = $repo->findBy('email', $email);

        // Kiểm tra xem $user có tồn tại VÀ mật khẩu có khớp không
        if ($user && password_verify($password, $user['password_hash'])) {
            // Đăng nhập thành công!
            // Lưu thông tin người dùng vào SESSION
            $_SESSION['user'] = [
                'id' => $user['id'],
                'full_name' => $user['full_name'],
                'email' => $user['email'],
                'role' => $user['role']
            ];

            // Thông báo thành công
            $_SESSION['flash_message'] = '✅ Đăng nhập thành công!';
            $_SESSION['flash_success'] = true;

            // Chuyển hướng dựa theo role
            if ($user['role'] === 'admin') {
                header('Location: ../admin/index.php');
            } else {
                header('Location: index.php');
            }
            exit;
        } else {
            // Đăng nhập thất bại
            $message = '❌ Sai thông tin email hoặc mật khẩu.';
            $message_type = 'error';
        }
    }
    
}

// Quyết định thanh trượt bắt đầu ở đâu
$view = $_GET['view'] ?? 'login';
if ($view === 'register' || ($message_type === 'error' && isset($_POST['action']) && $_POST['action'] === 'register')) {
    $view = 'register';
    $pageTitle = "Đăng Ký";
} elseif ($message_type === 'error' && isset($_POST['action']) && $_POST['action'] === 'login') {
    $view = 'login'; // Nếu đăng nhập lỗi, ở lại form login
    $pageTitle = "Đăng Nhập";
} elseif ($view === 'forgot') {
    $pageTitle = "Quên Mật Khẩu";
} else {
    $pageTitle = "Đăng Nhập";
}
$initial_transform = ($view === 'register') ? 'transform: translateX(-50%);' : '';
?>

<!DOCTYPE html>
<html lang="vi" class="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle) ?> | SCARLET CINEMA</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap" rel="stylesheet">
  <script>
    const THEMES = { SCARLET: { rgb: '220, 38, 38', hex: '#dc2626', shadow: 'rgba(220, 38, 38, 0.6)' } };
    const currentTheme = THEMES.SCARLET;
    function applyTheme(theme) {
      document.documentElement.style.setProperty('--primary-rgb', theme.rgb);
      document.documentElement.style.setProperty('--color-primary-hex', theme.hex);
      document.documentElement.style.setProperty('--color-primary-hex-shadow', theme.shadow);
    }
    tailwind.config = {
      darkMode: 'class',
      theme: {
        extend: {
          colors: {
            primary: 'var(--color-primary-hex)', 'dark-bg': '#0a0a0a', 'card-bg': '#1f2937'
          },
          fontFamily: { sans: ['Inter', 'sans-serif'] },
          animation: { 'neon-glow': 'neonGlow 2s ease-in-out infinite alternate' },
          keyframes: {
            neonGlow: {
              '0%': { boxShadow: '0 0 5px var(--color-primary-hex-shadow), 0 0 10px var(--color-primary-hex-shadow)' },
              '100%': { boxShadow: '0 0 10px var(--color-primary-hex), 0 0 20px var(--color-primary-hex-shadow)' }
            }
          }
        }
      }
    };
  </script>
  <style>
    body { background-color: #0a0a0a; color: #e5e7eb; font-family: 'Inter', sans-serif; }
    .btn-neon {
      background-color: var(--color-primary-hex); color: #0a0a0a; font-weight: 600;
      border-radius: 0.5rem; padding: 0.75rem 1.5rem; transition: all 0.3s ease;
      animation: neon-glow 1.5s ease-in-out infinite alternate;
    }
    .btn-neon:hover { opacity: 0.9; transform: scale(1.05); }
    .form-slide { min-height: 520px; }
  </style>
</head>

<body class="dark bg-dark-bg min-h-screen flex items-center justify-center" onload="applyTheme(currentTheme); lucide.createIcons();">
  
    <div class="bg-card-bg rounded-2xl shadow-2xl w-full max-w-md animate-fade-in overflow-hidden relative">
        
        <div id="form-slider" class="flex w-[200%] transition-transform duration-500 ease-in-out" style="<?= $initial_transform ?>">

            <div class="w-1/2 p-8 form-slide">
                <div class="flex items-center justify-center space-x-2 mb-6">
                    <i data-lucide="popcorn" class="text-primary h-8 w-8"></i>
                    <h1 class="text-2xl font-bold text-white tracking-wide">SCARLET CINEMA</h1>
                </div>
                <h2 class="text-center text-3xl font-extrabold text-white mb-2">Đăng Nhập</h2>
                <p class="text-center text-gray-400 mb-6">Trải nghiệm điện ảnh tối thượng!</p>

                <form class="space-y-5" method="POST">
                    <input type="hidden" name="action" value="login">
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-300 mb-1">Email</label>
                        <input id="email" name="email" type="email" required placeholder="nhapemail@domain.com"
                            class="w-full p-3 rounded-lg bg-gray-700 border border-gray-600 text-white focus:ring-primary focus:border-primary">
                    </div>
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-300 mb-1">Mật khẩu</label>
                        <input id="password" name="password" type="password" required placeholder="••••••••"
                            class="w-full p-3 rounded-lg bg-gray-700 border border-gray-600 text-white focus:ring-primary focus:border-primary">
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <button type="button" id="goToForgot" class="text-primary hover:underline font-semibold">Quên mật khẩu?</button>
                    </div>
                    <button type="submit" class="btn-neon w-full mt-4">Đăng Nhập</button>
                    
                    <?php if (!empty($message) && $view === 'login'): ?>
                        <p class="text-center text-sm mt-3 text-red-400">
                            <?= htmlspecialchars($message) ?>
                        </p>
                    <?php endif; ?>

                    <div class="text-sm text-center mt-4">
                        Chưa có tài khoản?
                        <button type="button" id="goToRegister" class="text-primary hover:underline font-semibold">Đăng ký ngay</button>
                    </div>
                </form>
            </div>

            <div class="w-1/2 p-8 form-slide">
                <div class="flex items-center justify-center space-x-2 mb-6">
                    <i data-lucide="popcorn" class="text-primary h-8 w-8"></i>
                    <h1 class="text-2xl font-bold text-white tracking-wide">SCARLET CINEMA</h1>
                </div>
                <h2 class="text-center text-3xl font-extrabold text-white mb-2">Tạo Tài Khoản</h2>
                <p class="text-center text-gray-400 mb-6">Gia nhập Scarlet để nhận ưu đãi!</p>

                <form class="space-y-5" method="POST">
                    <input type="hidden" name="action" value="register">
                    <div>
                        <label for="fullname" class="block text-sm font-medium text-gray-300 mb-1">Họ và Tên</label>
                        <input id="fullname" name="fullname" type="text" required placeholder="Nguyễn Văn A"
                            class="w-full p-3 rounded-lg bg-gray-700 border border-gray-600 text-white focus:ring-primary focus:border-primary">
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-300 mb-1">Email</label>
                        <input id="email" name="email" type="email" required placeholder="nhapemail@domain.com"
                            class="w-full p-3 rounded-lg bg-gray-700 border border-gray-600 text-white focus:ring-primary focus:border-primary">
                    </div>
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-300 mb-1">Mật khẩu</label>
                        <input id="password" name="password" type="password" required placeholder="••••••••"
                            class="w-full p-3 rounded-lg bg-gray-700 border border-gray-600 text-white focus:ring-primary focus:border-primary">
                    </div>
                    <div>
                        <label for="confirmPassword" class="block text-sm font-medium text-gray-300 mb-1">Xác nhận mật khẩu</label>
                        <input id="confirmPassword" name="confirmPassword" type="password" required placeholder="Nhập lại mật khẩu"
                            class="w-full p-3 rounded-lg bg-gray-700 border border-gray-600 text-white focus:ring-primary focus:border-primary">
                    </div>
                    
                    <button type="submit" class="btn-neon w-full mt-4">Tạo Tài Khoản</button>
                    
                    <?php if (!empty($message) && $view === 'register'): ?>
                        <p class="text-center text-sm mt-3 <?php echo ($message_type === 'success') ? 'text-green-400' : 'text-red-400'; ?>">
                            <?= htmlspecialchars($message) ?>
                        </p>
                    <?php endif; ?>

                    <div class="text-sm text-center mt-4">
                        Đã có tài khoản?
                        <button type="button" id="goToLogin" class="text-primary hover:underline font-semibold">Đăng nhập ngay</button>
                    </div>
                </form>
            </div>

        </div> <div id="forgot-slider" class="absolute inset-0 bg-card-bg p-8 transition-transform duration-500 ease-in-out translate-y-full form-slide">
            <div class="flex items-center justify-center space-x-2 mb-6">
                <i data-lucide="popcorn" class="text-primary h-8 w-8"></i>
                <h1 class="text-2xl font-bold text-white tracking-wide">SCARLET CINEMA</h1>
            </div>
            <h2 class="text-center text-3xl font-extrabold text-white mb-2">Quên Mật Khẩu</h2>
            <p class="text-center text-gray-400 mb-6">Nhập email của bạn để nhận liên kết khôi phục.</p>
            <form id="forgotForm" class="space-y-5">
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-300 mb-1">Email</label>
                    <input id="forgotEmail" name="email" type="email" required placeholder="nhapemail@domain.com"
                        class="w-full p-3 rounded-lg bg-gray-700 border border-gray-600 text-white focus:ring-primary focus:border-primary">
                </div>
                <button type="submit" class="btn-neon w-full mt-4">Gửi Liên Kết Khôi Phục</button>
                <p id="forgotMessage" class="text-center text-sm mt-3 hidden"></p>
                <div class="text-sm text-center mt-4">
                    <button type="button" id="backToLogin" class="text-primary hover:underline font-semibold">Quay lại đăng nhập</button>
                </div>
            </form>
        </div>

    </div> <script>
    const formForgot = document.getElementById('forgotForm');
    const msgForgot = document.getElementById('forgotMessage');
    if (formForgot) {
        formForgot.addEventListener('submit', (e) => {
          e.preventDefault();
          // ... (logic JS mô phỏng của bạn) ...
          msgForgot.classList.remove('hidden', 'text-green-400', 'text-red-400');
          msgForgot.classList.add('text-gray-300');
          msgForgot.textContent = 'Đang gửi email...';
          setTimeout(() => {
            const email = document.getElementById('forgotEmail').value.trim();
            if (!email.includes('@')) {
                msgForgot.classList.replace('text-gray-300', 'text-red-400');
                msgForgot.textContent = ' Email không hợp lệ.';
                return;
            }
            msgForgot.classList.replace('text-gray-300', 'text-green-400');
            msgForgot.textContent = ` Liên kết khôi phục đã được gửi.`;
          }, 1000);
        });
    }
  </script>
  
  <script>
    document.addEventListener('DOMContentLoaded', () => {
        const horizontalSlider = document.getElementById('form-slider');
        const verticalSlider = document.getElementById('forgot-slider');
        const goToRegister = document.getElementById('goToRegister');
        const goToLogin = document.getElementById('goToLogin');
        const goToForgot = document.getElementById('goToForgot');
        const backToLogin = document.getElementById('backToLogin');

        if (horizontalSlider) { 
            goToRegister.addEventListener('click', () => {
                horizontalSlider.style.transform = 'translateX(-50%)';
            });
            goToLogin.addEventListener('click', () => {
                horizontalSlider.style.transform = 'translateX(0%)';
            });
        }
        
        if (verticalSlider) {
            goToForgot.addEventListener('click', () => {
                verticalSlider.style.transform = 'translateY(0%)';
            });
            backToLogin.addEventListener('click', () => {
                verticalSlider.style.transform = 'translateY(100%)';
            });
        }
    });
  </script>

</body>
</html>