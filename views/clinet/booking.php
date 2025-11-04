<?php
session_start();

$siteTitle = 'Đặt Vé | SCARLET CINEMA';

require_once __DIR__ . "/../../function/reponsitory.php";
require_once __DIR__ . "/../../function/auth_helper.php";

// Kiểm tra trạng thái đăng nhập
$isLoggedIn = isLoggedIn();
$userRole = getUserRole();

// Lấy movie_id từ URL
$movie_id = $_GET['movie_id'] ?? null;

if (!$movie_id) {
    $_SESSION['flash_message'] = 'Vui lòng chọn phim để đặt vé!';
    $_SESSION['flash_success'] = false;
    header('Location: index.php');
    exit;
}

// Khởi tạo Repository
$movieRepo = new Repository('movies');
$showRepo = new Repository('shows');
$screenRepo = new Repository('screens');
$theaterRepo = new Repository('theaters');

// Lấy thông tin phim
$movie = $movieRepo->find($movie_id);
if (!$movie) {
    $_SESSION['flash_message'] = 'Không tìm thấy phim!';
    $_SESSION['flash_success'] = false;
    header('Location: index.php');
    exit;
}

$allShows = $showRepo->findAllBy('movie_id', $movie_id);

// Lọc chỉ những suất chiếu trong tương lai
$shows = array_filter($allShows, function($show) {
    $showDateTime = strtotime($show['show_time']);
    return $showDateTime > time();
});

// Nhóm suất chiếu theo ngày và rạp
$showsByDate = [];
$showDetails = [];

foreach ($shows as $show) {
    $screen = $screenRepo->find($show['screen_id']);
    $theater = $screen ? $theaterRepo->find($screen['theater_id']) : null;
    
    if (!$screen || !$theater) continue;
    
    $showDate = date('Y-m-d', strtotime($show['show_time']));
    $showTime = date('H:i', strtotime($show['show_time']));
    
    if (!isset($showsByDate[$showDate])) {
        $showsByDate[$showDate] = [];
    }
    
    $theaterName = $theater['name'];
    if (!isset($showsByDate[$showDate][$theaterName])) {
        $showsByDate[$showDate][$theaterName] = [];
    }
    
    $showsByDate[$showDate][$theaterName][] = [
        'show_id' => $show['id'],
        'show_time' => $showTime,
        'screen_name' => $screen['name'],
        'screen_type' => $screen['screen_type'],
        'format' => $show['format'],
        'price' => $show['price'],
        'seat_layout' => $screen['seat_layout']
    ];
}

// Sắp xếp theo ngày
ksort($showsByDate);

// Flash messages
$flash_message = $_SESSION['flash_message'] ?? '';
$flash_success = $_SESSION['flash_success'] ?? false;
unset($_SESSION['flash_message'], $_SESSION['flash_success']);

require_once __DIR__ . "/header.php";
?>

<style>
    .seat-standard { background-color: #3b82f6; } /* Blue */
    .seat-vip { background-color: #f59e0b; } /* Orange */
    .seat-disabled { background-color: #10b981; } /* Green */
    .seat-selected { border: 2px solid #dc2626 !important; }
</style>

<script>
    // Cấu hình loại ghế
    const SEAT_TYPES = {
        'standard': {
            name: 'Ghế Thường',
            color: 'bg-blue-500',
            price_modifier: 1.0
        },
        'vip': {
            name: 'Ghế VIP',
            color: 'bg-orange-500',
            price_modifier: 1.5
        },
        'disabled': {
            name: 'Ghế Người Khuyết Tật',
            color: 'bg-green-500',
            price_modifier: 0.8
        },
        'aisle': {
            name: 'Lối đi',
            color: 'bg-gray-900',
            price_modifier: 0
        }
    };
</script>

<?php if ($flash_message): ?>
    <div id="flash-message" class="fixed top-20 right-6 z-50 px-6 py-3 rounded-lg shadow-xl text-white font-semibold 
            <?= $flash_success ? 'bg-green-600 border border-green-400' : 'bg-red-700 border border-red-400' ?>">
        <?= htmlspecialchars($flash_message) ?>
    </div>
<?php endif; ?>

<main class="max-w-7xl mx-auto p-6 space-y-8 pb-20">
    
    <!-- Thông tin phim -->
    <section class="bg-gray-800 rounded-xl p-6 shadow-lg">
        <div class="flex flex-col md:flex-row gap-6">
            <img src="<?= htmlspecialchars($movie['banner_url']) ?>" 
                 alt="<?= htmlspecialchars($movie['title']) ?>" 
                 class="w-full md:w-64 h-auto rounded-lg object-cover">
            <div class="flex-1">
                <h1 class="text-4xl font-bold text-white mb-4"><?= htmlspecialchars($movie['title']) ?></h1>
                <div class="flex items-center gap-6 text-gray-300 mb-4">
                    <span class="flex items-center gap-1">⭐ <?= htmlspecialchars($movie['rating'] ?? 'N/A') ?></span>
                    <span class="flex items-center gap-1">⏱️ <?= htmlspecialchars($movie['duration_min']) ?> phút</span>
                    <span class="flex items-center gap-1">📅 <?= date('d/m/Y', strtotime($movie['release_date'])) ?></span>
                </div>
                <div class="text-gray-400 leading-relaxed">
                    <?= $movie['description'] ?>
                </div>
            </div>
        </div>
    </section>

    <?php if (empty($showsByDate)): ?>
        <section class="bg-gray-800 rounded-xl p-12 text-center">
            <p class="text-gray-400 text-xl">Hiện chưa có suất chiếu nào cho phim này.</p>
            <a href="index.php" class="inline-block mt-6 bg-primary text-black px-6 py-3 rounded-lg font-semibold hover:bg-red-500 transition">
                Quay lại trang chủ
            </a>
        </section>
    <?php else: ?>
        
        <!-- Bước 1: Chọn Ngày -->
        <section id="step-date" class="bg-gray-800 rounded-xl p-6 shadow-lg">
            <h2 class="text-2xl font-bold text-white mb-6 border-l-4 border-primary pl-4">Bước 1: Chọn Ngày Chiếu</h2>
            <div class="flex flex-wrap gap-3">
                <?php foreach (array_keys($showsByDate) as $index => $date): 
                    $dateObj = new DateTime($date);
                    $dayName = $dateObj->format('l');
                    $dayNameVi = [
                        'Monday' => 'Thứ 2',
                        'Tuesday' => 'Thứ 3',
                        'Wednesday' => 'Thứ 4',
                        'Thursday' => 'Thứ 5',
                        'Friday' => 'Thứ 6',
                        'Saturday' => 'Thứ 7',
                        'Sunday' => 'Chủ nhật'
                    ][$dayName] ?? $dayName;
                    $formattedDate = $dateObj->format('d/m');
                ?>
                    <button class="date-filter-btn px-6 py-3 rounded-lg font-semibold transition duration-200
                            <?= $index === 0 ? 'bg-primary text-black' : 'bg-gray-700 text-white hover:bg-gray-600' ?>"
                            data-date="<?= $date ?>">
                        <div class="text-sm"><?= $dayNameVi ?></div>
                        <div class="text-lg font-bold"><?= $formattedDate ?></div>
                    </button>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Bước 2: Chọn Suất Chiếu -->
        <section id="step-showtime" class="bg-gray-800 rounded-xl p-6 shadow-lg">
            <h2 class="text-2xl font-bold text-white mb-6 border-l-4 border-primary pl-4">Bước 2: Chọn Suất Chiếu</h2>
            
            <?php foreach ($showsByDate as $date => $theaters): ?>
                <?php foreach ($theaters as $theaterName => $theaterShows): ?>
                    <div class="theater-block mb-6 last:mb-0">
                        <h3 class="text-xl font-bold text-white mb-4 flex items-center gap-2">
                            <i data-lucide="map-pin" class="w-5 h-5 text-primary"></i>
                            <?= htmlspecialchars($theaterName) ?>
                        </h3>
                        <div class="flex flex-wrap gap-3">
                            <?php foreach ($theaterShows as $show): ?>
                                <button class="showtime-btn px-6 py-3 rounded-lg font-semibold transition duration-200 bg-gray-700 text-white hover:bg-red-500 hover:scale-105"
                                        data-show-id="<?= $show['show_id'] ?>"
                                        data-date="<?= $date ?>"
                                        data-price="<?= $show['price'] ?>"
                                        data-layout='<?= htmlspecialchars($show['seat_layout'], ENT_QUOTES) ?>'
                                        title="Phòng: <?= htmlspecialchars($show['screen_name']) ?> | Định dạng: <?= htmlspecialchars($show['format']) ?>"
                                        style="display: none;">
                                    <div class="text-lg font-bold"><?= $show['show_time'] ?></div>
                                    <div class="text-xs text-gray-300"><?= htmlspecialchars($show['format']) ?></div>
                                </button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </section>

        <!-- Bước 3: Chọn Ghế -->
        <section id="step-seat" class="bg-gray-800 rounded-xl p-6 shadow-lg hidden">
            <h2 class="text-2xl font-bold text-white mb-6 border-l-4 border-primary pl-4">Bước 3: Chọn Ghế</h2>
            
            <?php if (!$isLoggedIn): ?>
                <!-- Thông báo yêu cầu đăng nhập -->
                <div class="bg-yellow-600/20 border-2 border-yellow-500 rounded-lg p-6 mb-6 text-center">
                    <i data-lucide="lock" class="w-16 h-16 mx-auto mb-4 text-yellow-500"></i>
                    <h3 class="text-xl font-bold text-yellow-300 mb-2">Vui lòng đăng nhập để đặt vé</h3>
                    <p class="text-gray-300 mb-4">Bạn cần đăng nhập để có thể chọn ghế và đặt vé xem phim.</p>
                    <a href="account.php?view=login&redirect=<?= urlencode($_SERVER['REQUEST_URI']) ?>" 
                       class="inline-block bg-primary text-black px-6 py-3 rounded-lg font-bold hover:bg-red-500 transition">
                        Đăng nhập ngay
                    </a>
                </div>
            <?php endif; ?>
            
            <!-- Thông tin suất chiếu đã chọn -->
            <div id="selected-show-info" class="bg-gray-700/50 p-4 rounded-lg mb-6 text-gray-300">
                <!-- Sẽ được cập nhật bằng JavaScript -->
            </div>

            <!-- Chú thích loại ghế -->
            <div class="flex flex-wrap gap-6 mb-6 text-sm">
                <div class="flex items-center gap-2">
                    <div class="w-6 h-6 bg-blue-500 rounded-sm"></div>
                    <span class="text-gray-300">Ghế Thường</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-6 h-6 bg-orange-500 rounded-sm"></div>
                    <span class="text-gray-300">Ghế VIP (x1.5 giá)</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-6 h-6 bg-green-500 rounded-sm"></div>
                    <span class="text-gray-300">Người Khuyết Tật (x0.8 giá)</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-6 h-6 bg-gray-500 rounded-sm"></div>
                    <span class="text-gray-300">Đã bán</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-6 h-6 bg-primary border-2 border-primary rounded-sm"></div>
                    <span class="text-gray-300">Đang chọn</span>
                </div>
            </div>

            <!-- Màn hình -->
            <div class="text-center mb-8">
                <div class="inline-block bg-gray-700 text-white px-12 py-2 rounded-t-full border-t-4 border-primary">
                    MÀN HÌNH
                </div>
            </div>

            <!-- Sơ đồ ghế -->
            <div id="seat-map" class="mb-6 <?= !$isLoggedIn ? 'opacity-50 pointer-events-none' : '' ?>">
                <!-- Sẽ được render bằng JavaScript -->
            </div>

            <!-- Tóm tắt ghế đã chọn -->
            <div class="bg-gray-700/50 p-4 rounded-lg mb-6">
                <h3 class="font-bold text-white mb-2">Ghế đã chọn:</h3>
                <p id="summary-seats-list" class="text-gray-300 mb-3">Chưa chọn</p>
                <h3 class="font-bold text-white mb-2">Tổng tiền:</h3>
                <p id="summary-total-amount" class="text-2xl font-bold text-primary">0 VNĐ</p>
            </div>

            <button id="next-to-checkout-btn" disabled 
                    class="w-full bg-primary text-black py-3 rounded-lg font-bold text-lg hover:bg-red-500 transition disabled:bg-gray-600 disabled:cursor-not-allowed disabled:text-gray-400">
                Tiếp tục thanh toán
            </button>
        </section>

        <!-- Bước 4: Thanh toán -->
        <section id="step-checkout" class="bg-gray-800 rounded-xl p-6 shadow-lg hidden">
            <h2 class="text-2xl font-bold text-white mb-6 border-l-4 border-primary pl-4">Bước 4: Xác Nhận & Thanh Toán</h2>
            
            <form action="../../handle/booking_process.php" method="POST" class="space-y-6">
                <input type="hidden" name="show_id" id="checkout-show-id">
                <input type="hidden" name="selected_seats" id="checkout-selected-seats">
                <input type="hidden" name="total_amount" id="checkout-total-amount-input">
                
                <div class="bg-gray-700/50 p-4 rounded-lg">
                    <h3 class="font-bold text-white mb-2">Tổng thanh toán:</h3>
                    <p id="checkout-total-amount" class="text-3xl font-bold text-primary">0 VNĐ</p>
                </div>

                <div>
                    <label for="payment_method" class="block text-white font-semibold mb-2">Phương thức thanh toán:</label>
                    <select name="payment_method" id="payment_method" required
                            class="w-full bg-gray-700 text-white px-4 py-3 rounded-lg border border-gray-600 focus:border-primary focus:outline-none">
                        <option value="">-- Chọn phương thức --</option>
                        <option value="cash">Tiền mặt tại quầy</option>
                        <option value="credit_card">Thẻ tín dụng</option>
                        <option value="vnpay">VNPay</option>
                    </select>
                </div>

                <div class="flex gap-4">
                    <button type="button" id="back-to-seat-btn"
                            class="flex-1 bg-gray-700 text-white py-3 rounded-lg font-bold hover:bg-gray-600 transition">
                        Quay lại chọn ghế
                    </button>
                    <button type="submit"
                            class="flex-1 bg-primary text-black py-3 rounded-lg font-bold hover:bg-red-500 transition">
                        Xác nhận đặt vé
                    </button>
                </div>
            </form>
        </section>

    <?php endif; ?>

</main>

<script>
    // Truyền trạng thái đăng nhập từ PHP sang JavaScript
    const IS_LOGGED_IN = <?= $isLoggedIn ? 'true' : 'false' ?>;
</script>

<script src="../../asset/js/booking_clinet.js"></script>

<script>
    // Flash message auto hide
    setTimeout(() => {
        const flash = document.getElementById('flash-message');
        if (flash) {
            flash.style.opacity = '0';
            flash.style.transform = 'translateY(-10px)';
            setTimeout(() => flash.remove(), 500);
        }
    }, 3000);

    // Initialize Lucide icons
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
    
    // Kiểm tra đăng nhập khi click nút "Tiếp tục thanh toán"
    document.getElementById('next-to-checkout-btn').addEventListener('click', function(e) {
        if (!IS_LOGGED_IN) {
            e.preventDefault();
            alert('Vui lòng đăng nhập để tiếp tục đặt vé!');
            window.location.href = 'account.php?view=login&redirect=' + encodeURIComponent(window.location.href);
        }
    });
</script>

</body>
</html>

