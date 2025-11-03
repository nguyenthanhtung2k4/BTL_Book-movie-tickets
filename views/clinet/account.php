<?php
// account.php

// Luôn bắt đầu session ở đầu tệp
session_start();
// Tải tệp xử lý logic (account_handle.php)
// NOTE: Đảm bảo đường dẫn này là đúng: /../../handle/account_handle.php
require_once __DIR__ . '/../../handle/account_handle.php'; 

// === XỬ LÝ FLASH MESSAGE VÀ VIEW ===

// Lấy thông báo từ session và xóa ngay lập tức
$message = $_SESSION['flash_message'] ?? '';
$message_type = ($_SESSION['flash_success'] ?? false) ? 'success' : 'error';
$flash_view = $_SESSION['flash_view'] ?? null;

// Xóa session flash message ngay sau khi lấy
unset($_SESSION['flash_message']);
unset($_SESSION['flash_success']);
unset($_SESSION['flash_view']);

// Quyết định thanh trượt ngang bắt đầu ở đâu
// 1. Ưu tiên view từ URL (nếu người dùng tự bấm link)
// 2. Ưu tiên view từ flash session (nếu có lỗi/thành công từ handler)
// 3. Mặc định là 'login'
$view = $_GET['view'] ?? $flash_view ?? 'login'; 


// Logic điều chỉnh view nếu có lỗi xảy ra
if (!empty($message) && $message_type === 'error') {
    // Nếu có lỗi, đảm bảo slider trượt đến đúng vị trí của lỗi.
    // Nếu flash_view có, nó đã được ưu tiên ở dòng 21. 
    // Nếu không có flash_view, giữ nguyên $view hiện tại (mặc định là login).
    if ($view === 'register') {
         // Lỗi đăng ký, giữ ở form đăng ký
         $pageTitle = "Đăng Ký";
    } else {
         // Lỗi đăng nhập, luôn chuyển về form đăng nhập
         $view = 'login';
         $pageTitle = "Đăng Nhập";
    }
} else {
    // Không có lỗi flash
    if ($view === 'register') {
        $pageTitle = "Đăng Ký";
    } else {
        $pageTitle = "Đăng Nhập";
        $view = 'login'; // Đảm bảo luôn là login nếu không phải register rõ ràng
    }
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
                    animation: { 'neon-glow': 'neonGlow 1.5s ease-in-out infinite alternate' },
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

                <form id="loginForm" class="space-y-5" action="../../handle/account_handle.php" method="POST">
                    <input type="hidden" name="action" value="login">
                    <input type="hidden" name="view" value="login"> 
                    <div>
                        <label for="loginEmail" class="block text-sm font-medium text-gray-300 mb-1">Email</label>
                        <input id="loginEmail" name="email" type="email" required placeholder="nhapemail@domain.com"
                            class="w-full p-3 rounded-lg bg-gray-700 border border-gray-600 text-white focus:ring-primary focus:border-primary">
                    </div>
                    <div>
                        <label for="loginPassword" class="block text-sm font-medium text-gray-300 mb-1">Mật khẩu</label>
                        <input id="loginPassword" name="password" type="password" required placeholder="••••••••"
                            class="w-full p-3 rounded-lg bg-gray-700 border border-gray-600 text-white focus:ring-primary focus:border-primary">
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <button type="button" id="goToForgot" class="text-primary hover:underline font-semibold">Quên mật khẩu?</button>
                    </div>
                    <button type="submit" class="btn-neon w-full mt-4">Đăng Nhập</button>
                    
                    <?php if ($view === 'login' && !empty($message)): ?>
                        <p class="text-center text-sm mt-3 <?php echo ($message_type === 'success') ? 'text-green-400' : 'text-red-400'; ?>">
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

                <form class="space-y-5" method="POST" action='../../handle/account_handle.php'>
                    <input type="hidden" name="action" value="register">
                    <input type="hidden" name="view" value="register"> <div>
                        <label for="fullname" class="block text-sm font-medium text-gray-300 mb-1">Họ và Tên</label>
                        <input id="fullname" name="fullname" type="text" required placeholder="Nguyễn Văn A"
                            class="w-full p-3 rounded-lg bg-gray-700 border border-gray-600 text-white focus:ring-primary focus:border-primary">
                    </div>
                    <div>
                        <label for="registerEmail" class="block text-sm font-medium text-gray-300 mb-1">Email</label>
                        <input id="registerEmail" name="email" type="email" required placeholder="nhapemail@domain.com"
                            class="w-full p-3 rounded-lg bg-gray-700 border border-gray-600 text-white focus:ring-primary focus:border-primary">
                    </div>
                    <div>
                        <label for="registerPassword" class="block text-sm font-medium text-gray-300 mb-1">Mật khẩu</label>
                        <input id="registerPassword" name="password" type="password" required placeholder="••••••••"
                            class="w-full p-3 rounded-lg bg-gray-700 border border-gray-600 text-white focus:ring-primary focus:border-primary">
                    </div>
                    <div>
                        <label for="confirmPassword" class="block text-sm font-medium text-gray-300 mb-1">Xác nhận mật khẩu</label>
                        <input id="confirmPassword" name="confirmPassword" type="password" required placeholder="Nhập lại mật khẩu"
                            class="w-full p-3 rounded-lg bg-gray-700 border border-gray-600 text-white focus:ring-primary focus:border-primary">
                    </div>
                    
                    <button type="submit" class="btn-neon w-full mt-4">Tạo Tài Khoản</button>
                    
                    <?php if ($view === 'register' && !empty($message)): ?>
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

        </div> 
        
        <div id="forgot-slider" class="absolute inset-0 bg-card-bg p-8 transition-transform duration-500 ease-in-out translate-y-full form-slide">
            <div class="flex items-center justify-center space-x-2 mb-6">
                <i data-lucide="popcorn" class="text-primary h-8 w-8"></i>
                <h1 class="text-2xl font-bold text-white tracking-wide">SCARLET CINEMA</h1>
            </div>
            
            <h2 class="text-center text-3xl font-extrabold text-white mb-2">Quên Mật Khẩu</h2>
            <p class="text-center text-gray-400 mb-6">Nhập email của bạn để nhận liên kết khôi phục.</p>
            
            <form id="forgotForm" class="space-y-5">
                <div>
                    <label for="forgotEmail" class="block text-sm font-medium text-gray-300 mb-1">Email</label>
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

    </div> 
    
    <script>
    // Giữ nguyên logic JS cho Quên mật khẩu và chuyển slide
    </script>
    
    <script>
        const formForgot = document.getElementById('forgotForm');
        const msgForgot = document.getElementById('forgotMessage');
        if (formForgot) {
            formForgot.addEventListener('submit', (e) => {
              e.preventDefault();
              msgForgot.classList.remove('hidden', 'text-green-400', 'text-red-400');
              msgForgot.classList.add('text-gray-300');
              msgForgot.textContent = 'Đang gửi email...';
              setTimeout(() => {
                const email = document.getElementById('forgotEmail').value.trim();
                if (!email.includes('@')) {
                    msgForgot.classList.replace('text-gray-300', 'text-red-400');
                    msgForgot.textContent = '❌ Email không hợp lệ.';
                    return;
                }
                // Giả lập thành công
                msgForgot.classList.replace('text-gray-300', 'text-green-400');
                msgForgot.textContent = `✅ Liên kết khôi phục đã được gửi.`;
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
                    // Cập nhật URL để duy trì trạng thái khi refresh
                    window.history.pushState({view: 'register'}, "Đăng Ký", "?view=register"); 
                });
                goToLogin.addEventListener('click', () => {
                    horizontalSlider.style.transform = 'translateX(0%)';
                    // Cập nhật URL để duy trì trạng thái khi refresh
                    window.history.pushState({view: 'login'}, "Đăng Nhập", "?view=login");
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
            
            // Xử lý nút back/forward của trình duyệt
            window.onpopstate = (event) => {
                const currentView = new URLSearchParams(window.location.search).get('view');
                if (currentView === 'register') {
                    horizontalSlider.style.transform = 'translateX(-50%)';
                } else {
                    horizontalSlider.style.transform = 'translateX(0%)';
                }
            };
        });
    </script>

</body>
</html>