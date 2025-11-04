<?php
// Luôn bắt đầu session ở đầu tệp
session_start();

// Tải các tệp xử lý và repository
require_once __DIR__ . '/../../handle/user_handle.php';
require_once __DIR__ . '/../../function/reponsitory.php';
require_once __DIR__ . '/../../function/auth_helper.php';

// 1. KIỂM TRA XÁC THỰC
// Bắt buộc đăng nhập để xem profile
requireLogin();

// Lấy ID người dùng từ session
$user_id = getUserId();
$repo = new Repository('users');

// Lấy thông tin người dùng MỚI NHẤT từ CSDL
// (Phòng trường hợp thông tin trong session đã cũ)
$user = $repo->find($user_id);
if (!$user) {
    // Nếu người dùng không còn tồn tại (ví dụ: bị admin xóa), ép đăng xuất
    header('Location: ../../logout.php');
    exit;
}

// Lấy danh sách đơn đặt vé của người dùng
$bookingRepo = new Repository('bookings');
$bookingItemRepo = new Repository('booking_items');
$movieRepo = new Repository('movies');
$showRepo = new Repository('shows');

// Lấy tất cả booking của user, sắp xếp mới nhất lên đầu
$userBookings = $bookingRepo->getByCondition(
    "user_id = :user_id",
    ['user_id' => $user_id],
    "*",
    "created_at DESC"
);

// Biến để lưu thông báo
$profile_message = '';
$profile_message_type = '';
$password_message = '';
$password_message_type = '';

// 2. XỬ LÝ POST REQUEST (Khi người dùng nhấn nút)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // --- XỬ LÝ CẬP NHẬT HỒ SƠ (Tên, Email) ---
    if (isset($_POST['action']) && $_POST['action'] === 'update_profile') {
        $data = [
            'full_name' => $_POST['fullname'] ?? $user['full_name'],
            'email' => $_POST['email'] ?? $user['email']
        ];
        
        $response = handleUser('edit', $data, $user_id);
        $profile_message = $response['message'];

        if ($response['success']) {
            $profile_message_type = 'success';
            // Cập nhật lại session
            $_SESSION['user']['full_name'] = $data['full_name'];
            $_SESSION['user']['email'] = $data['email'];
            // Tải lại dữ liệu $user để hiển thị trên form
            $user = $repo->find($user_id);
        } else {
            $profile_message_type = 'error';
        }
    }

    // --- XỬ LÝ ĐỔI MẬT KHẨU ---
    if (isset($_POST['action']) && $_POST['action'] === 'change_password') {
        $old_pass = $_POST['old_password'] ?? '';
        $new_pass = $_POST['new_password'] ?? '';
        $confirm_pass = $_POST['confirm_password'] ?? '';

        // 1. Kiểm tra mật khẩu cũ
        if (!password_verify($old_pass, $user['password_hash'])) {
            $password_message = '❌ Mật khẩu cũ không chính xác.';
            $password_message_type = 'error';
        } 
        // 2. Kiểm tra mật khẩu mới có trống không
        elseif (empty($new_pass)) {
            $password_message = '❌ Mật khẩu mới không được để trống.';
            $password_message_type = 'error';
        }
        // 3. Kiểm tra mật khẩu mới có trùng khớp không
        elseif ($new_pass !== $confirm_pass) {
            $password_message = '❌ Mật khẩu mới không trùng khớp.';
            $password_message_type = 'error';
        }
        // 4. Mọi thứ OK, tiến hành cập nhật
        else {
            $data = [
                'password_hash' => password_hash($new_pass, PASSWORD_DEFAULT)
            ];
            // Chúng ta dùng lại hàm 'edit' của handleUser
            $response = handleUser('edit', $data, $user_id);
            $password_message = $response['message'];
            $password_message_type = $response['success'] ? 'success' : 'error';
        }
    }
}

// Lấy tên người dùng (an toàn)
$userName = htmlspecialchars($user['full_name']);
?>

<!DOCTYPE html>
<html lang="vi" class="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Hồ Sơ Của: <?= $userName ?> | SCARLET CINEMA</title>
  
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap" rel="stylesheet">
  
  <link rel="stylesheet" href="../../asset/css/style.css">

  <script>
    // Config Tailwind (giống index.php)
    tailwind.config = {
      darkMode: 'class',
      theme: {
        extend: {
          colors: {
            primary: '#dc2626',
            'dark-bg': '#0a0a0a'
          },
          boxShadow: {
            'primary-glow': '0 0 20px rgba(255, 255, 255, 0.4), 0 0 8px rgba(255, 255, 255, 0.3)'
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
  </style>
</head>

<body class="bg-dark-bg text-gray-100 font-[Inter] pt-16" onload="lucide.createIcons();">

  <header class="fixed top-0 w-full bg-dark-bg/90 backdrop-blur-md shadow-lg z-50">
    <nav class="max-w-7xl mx-auto flex justify-between items-center p-4">
      <a href="index.php" class="flex items-center space-x-2">
        <i data-lucide='popcorn' class='text-primary'></i>
        <span class="font-bold text-2xl">SCARLET CINEMA</span>
      </a>
      <div class="hidden md:flex space-x-6">
        <a href="index.php#current" class="hover:text-primary">ĐANG CHIẾU</a>
        <a href="index.php#upcoming" class="hover:text-primary">SẮP CHIẾU</a>
      </div>
      
      <div class="flex items-center space-x-4">
        <?php if (isset($_SESSION['user'])): ?>
            <span class="text-gray-300">
                Xin chào, 
                <a href="profile.php" class="font-bold text-white hover:text-primary">
                    <?= htmlspecialchars($_SESSION['user']['full_name']) ?>
                </a>
            </span>
            <a href="logout.php" class="bg-gray-700 px-4 py-2 rounded text-white font-semibold hover:bg-gray-600 transition">
                Đăng Xuất
            </a>
        <?php else: ?>
            <a href="account.php?view=login" class="bg-primary px-4 py-2 rounded text-black font-semibold hover:bg-red-500 transition">
                Đăng Nhập
            </a>
        <?php endif; ?>
      </div>
    </nav>
  </header>

  <main class="max-w-4xl mx-auto p-6 space-y-10 pt-24">
    <h1 class="text-4xl font-bold text-white">Hồ Sơ Của Bạn</h1>

    <div class="bg-gray-800 p-8 rounded-lg shadow-lg">
        <h2 class="text-2xl font-semibold text-white mb-6">Thông Tin Cá Nhân</h2>
        
        <form method="POST" class="space-y-5">
            <input type="hidden" name="action" value="update_profile">
            
            <div>
                <label for="fullname" class="block text-sm font-medium text-gray-300 mb-1">Họ và Tên</label>
                <input id="fullname" name="fullname" type="text" required 
                       value="<?= htmlspecialchars($user['full_name']) ?>"
                       class="w-full p-3 rounded-lg bg-gray-700 border border-gray-600 text-white focus:ring-primary focus:border-primary">
            </div>
            
            <div>
                <label for="email" class="block text-sm font-medium text-gray-300 mb-1">Email</label>
                <input id="email" name="email" type="email" required 
                       value="<?= htmlspecialchars($user['email']) ?>"
                       class="w-full p-3 rounded-lg bg-gray-700 border border-gray-600 text-white focus:ring-primary focus:border-primary">
            </div>
            
            <button type="submit" class="bg-primary px-6 py-3 rounded text-black font-semibold hover:bg-red-500 transition">
                Cập Nhật Thông Tin
            </button>

            <?php if (!empty($profile_message)): ?>
                <p class="text-center text-sm mt-3 
                    <?php echo ($profile_message_type === 'success') ? 'text-green-400' : 'text-red-400'; ?>">
                    <?= htmlspecialchars($profile_message) ?>
                </p>
            <?php endif; ?>
        </form>
    </div>

    <div class="bg-gray-800 p-8 rounded-lg shadow-lg">
        <h2 class="text-2xl font-semibold text-white mb-6">Đổi Mật Khẩu</h2>
        
        <form method="POST" class="space-y-5">
            <input type="hidden" name="action" value="change_password">
            
            <div>
                <label for="old_password" class="block text-sm font-medium text-gray-300 mb-1">Mật khẩu cũ</label>
                <input id="old_password" name="old_password" type="password" required 
                       placeholder="••••••••"
                       class="w-full p-3 rounded-lg bg-gray-700 border border-gray-600 text-white focus:ring-primary focus:border-primary">
            </div>
            
            <div>
                <label for="new_password" class="block text-sm font-medium text-gray-300 mb-1">Mật khẩu mới</label>
                <input id="new_password" name="new_password" type="password" required 
                       placeholder="••••••••"
                       class="w-full p-3 rounded-lg bg-gray-700 border border-gray-600 text-white focus:ring-primary focus:border-primary">
            </div>
            
            <div>
                <label for="confirm_password" class="block text-sm font-medium text-gray-300 mb-1">Xác nhận mật khẩu mới</label>
                <input id="confirm_password" name="confirm_password" type="password" required 
                       placeholder="••••••••"
                       class="w-full p-3 rounded-lg bg-gray-700 border border-gray-600 text-white focus:ring-primary focus:border-primary">
            </div>

            <button type="submit" class="bg-gray-600 px-6 py-3 rounded text-white font-semibold hover:bg-gray-500 transition">
                Đổi Mật Khẩu
            </button>

            <?php if (!empty($password_message)): ?>
                <p class="text-center text-sm mt-3 
                    <?php echo ($password_message_type === 'success') ? 'text-green-400' : 'text-red-400'; ?>">
                    <?= htmlspecialchars($password_message) ?>
                </p>
            <?php endif; ?>
        </form>
    </div>

    <!-- Lịch Sử Đặt Vé -->
    <div class="bg-gray-800 p-8 rounded-lg shadow-lg">
        <h2 class="text-2xl font-semibold text-white mb-6 flex items-center gap-2">
            <i data-lucide="ticket" class="w-6 h-6 text-primary"></i>
            Lịch Sử Đặt Vé
        </h2>
        
        <?php if (empty($userBookings)): ?>
            <div class="text-center py-12">
                <i data-lucide="inbox" class="w-16 h-16 mx-auto text-gray-600 mb-4"></i>
                <p class="text-gray-400 text-lg mb-4">Bạn chưa có đơn đặt vé nào.</p>
                <a href="index.php" class="inline-block bg-primary text-black px-6 py-3 rounded-lg font-semibold hover:bg-red-500 transition">
                    Đặt vé ngay
                </a>
            </div>
        <?php else: ?>
            <div class="space-y-4">
                <?php foreach ($userBookings as $booking): 
                    // Lấy thông tin show và movie
                    $show = $showRepo->find($booking['show_id']);
                    $movie = $show ? $movieRepo->find($show['movie_id']) : null;
                    
                    // Lấy chi tiết các vé (booking items)
                    $items = $bookingItemRepo->findAllBy('booking_id', $booking['id']);
                    
                    // Đếm số ghế
                    $seatCodes = array_column($items, 'seat_code');
                    $seatList = implode(', ', $seatCodes);
                    
                    // Xác định màu trạng thái
                    $statusColors = [
                        'pending' => 'bg-yellow-500/20 text-yellow-300',
                        'confirmed' => 'bg-green-500/20 text-green-300',
                        'cancelled' => 'bg-red-500/20 text-red-300',
                        'expired' => 'bg-gray-500/20 text-gray-300'
                    ];
                    $statusLabels = [
                        'pending' => 'Chờ xử lý',
                        'confirmed' => 'Đã xác nhận',
                        'cancelled' => 'Đã hủy',
                        'expired' => 'Hết hạn'
                    ];
                    $statusColor = $statusColors[$booking['status']] ?? 'bg-gray-500/20 text-gray-300';
                    $statusLabel = $statusLabels[$booking['status']] ?? $booking['status'];
                    
                    $paymentStatusColors = [
                        'unpaid' => 'bg-red-500/20 text-red-300',
                        'paid' => 'bg-green-500/20 text-green-300',
                        'refunded' => 'bg-blue-500/20 text-blue-300'
                    ];
                    $paymentStatusLabels = [
                        'unpaid' => 'Chưa thanh toán',
                        'paid' => 'Đã thanh toán',
                        'refunded' => 'Đã hoàn tiền'
                    ];
                    $paymentColor = $paymentStatusColors[$booking['payment_status']] ?? 'bg-gray-500/20 text-gray-300';
                    $paymentLabel = $paymentStatusLabels[$booking['payment_status']] ?? $booking['payment_status'];
                ?>
                
                <div class="bg-gray-700/50 rounded-lg p-5 border border-gray-600 hover:border-primary transition">
                    <div class="flex flex-col md:flex-row gap-4">
                        <!-- Poster phim -->
                        <?php if ($movie): ?>
                            <div class="flex-shrink-0">
                                <img src="<?= htmlspecialchars($movie['banner_url'] ?? '') ?>" 
                                     alt="<?= htmlspecialchars($movie['title'] ?? '') ?>"
                                     class="w-24 h-32 object-cover rounded-lg">
                            </div>
                        <?php endif; ?>
                        
                        <!-- Thông tin đơn hàng -->
                        <div class="flex-1">
                            <div class="flex items-start justify-between mb-3">
                                <div>
                                    <h3 class="text-lg font-bold text-white mb-1">
                                        <?= htmlspecialchars($movie['title'] ?? 'Không xác định') ?>
                                    </h3>
                                    <p class="text-sm text-gray-400">
                                        Mã đơn: <span class="font-mono text-primary">#<?= $booking['id'] ?></span>
                                    </p>
                                </div>
                                <div class="text-right space-y-1">
                                    <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold <?= $statusColor ?>">
                                        <?= $statusLabel ?>
                                    </span>
                                    <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold <?= $paymentColor ?>">
                                        <?= $paymentLabel ?>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm text-gray-300">
                                <div>
                                    <i data-lucide="calendar" class="inline w-4 h-4 mr-1 text-primary"></i>
                                    <span>Suất chiếu: <?= $show ? date('d/m/Y H:i', strtotime($show['show_time'])) : 'N/A' ?></span>
                                </div>
                                <div>
                                    <i data-lucide="armchair" class="inline w-4 h-4 mr-1 text-primary"></i>
                                    <span>Ghế: <?= htmlspecialchars($seatList ?: 'N/A') ?></span>
                                </div>
                                <div>
                                    <i data-lucide="banknote" class="inline w-4 h-4 mr-1 text-primary"></i>
                                    <span>Tổng tiền: <strong class="text-green-400"><?= number_format($booking['total_amount'], 0, ',', '.') ?>₫</strong></span>
                                </div>
                                <div>
                                    <i data-lucide="credit-card" class="inline w-4 h-4 mr-1 text-primary"></i>
                                    <span>Thanh toán: <?= htmlspecialchars($booking['payment_method'] ?? 'N/A') ?></span>
                                </div>
                            </div>
                            
                            <div class="mt-3 pt-3 border-t border-gray-600 text-xs text-gray-500">
                                Đặt lúc: <?= date('d/m/Y H:i:s', strtotime($booking['created_at'])) ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Chi tiết vé -->
                    <?php if (!empty($items)): ?>
                        <details class="mt-4">
                            <summary class="cursor-pointer text-sm text-primary hover:text-red-400 font-semibold">
                                Xem chi tiết vé (<?= count($items) ?> vé)
                            </summary>
                            <div class="mt-3 bg-gray-800/50 rounded-lg p-3">
                                <table class="w-full text-sm">
                                    <thead class="border-b border-gray-600">
                                        <tr class="text-left text-gray-400">
                                            <th class="pb-2">Mã ghế</th>
                                            <th class="pb-2">Loại vé</th>
                                            <th class="pb-2">Giá</th>
                                            <th class="pb-2">Trạng thái</th>
                                        </tr>
                                    </thead>
                                    <tbody class="text-gray-300">
                                        <?php foreach ($items as $item): 
                                            $itemStatusColors = [
                                                'booked' => 'text-yellow-300',
                                                'cancelled' => 'text-red-300',
                                                'checked_in' => 'text-green-300'
                                            ];
                                            $itemStatusLabels = [
                                                'booked' => 'Đã đặt',
                                                'cancelled' => 'Đã hủy',
                                                'checked_in' => 'Đã check-in'
                                            ];
                                        ?>
                                            <tr class="border-b border-gray-700/50">
                                                <td class="py-2 font-bold text-primary"><?= htmlspecialchars($item['seat_code']) ?></td>
                                                <td class="py-2"><?= ucfirst($item['ticket_type']) ?></td>
                                                <td class="py-2 text-green-400"><?= number_format($item['ticket_price'], 0, ',', '.') ?>₫</td>
                                                <td class="py-2 <?= $itemStatusColors[$item['status']] ?? 'text-gray-400' ?>">
                                                    <?= $itemStatusLabels[$item['status']] ?? $item['status'] ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </details>
                    <?php endif; ?>
                </div>
                
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
  </main>
  
  <script src="../../asset/js/javascript.js"></script>
</body>
</html>