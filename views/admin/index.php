<?php
$adminName = "Admin Scarlet";
$title = "Dashboard";
$pageName = "Bảng điều khiển quản trị";
require_once __DIR__ ."/side_bar.php";
require_once __DIR__ ."/../../function/reponsitory.php";

$userRepo = new Repository('users');
$movieRepo = new Repository('movies');
$theaterRepo = new Repository('theaters');
$bookingRepo = new Repository('bookings'); 
$bookingItemRepo = new Repository('booking_items');
$showRepo = new Repository('shows');

// 2. LẤY DỮ LIỆU THỐNG KÊ TỔNG QUAN
$totalUsers = $userRepo->countAll();
$totalMovies = $movieRepo->countAll();
$totalTheaters = $theaterRepo->countAll(); 
$totalBookings = $bookingRepo->countAll();
$totalTicketsSold = $bookingItemRepo->countBookedTickets();

// Tính tổng doanh thu (chỉ tính đơn đã thanh toán)
$totalRevenue = $bookingRepo->getTotalRevenue() ?? 0;

// Thống kê chi tiết hơn
$paidBookings = $bookingRepo->getByCondition("payment_status = 'paid'", []);
$pendingBookings = $bookingRepo->getByCondition("payment_status = 'unpaid' AND status = 'pending'", []);
$totalPaidBookings = count($paidBookings);
$totalPendingBookings = count($pendingBookings); 

// LẤY DỮ LIỆU ĐỘNG CHO BIỂU ĐỒ 1 (Cột)
$monthlyBookings = $bookingRepo->getMonthlyBookings(5); 
$maxBookings = !empty($monthlyBookings) ? max($monthlyBookings) : 1;

// LẤY DỮ LIỆU ĐỘNG CHO BIỂU ĐỒ 2 (Tròn)
$ticketsByType = $bookingItemRepo->getTicketsByType();
$totalTicketsForChart = array_sum($ticketsByType);

// HÀM TẠO MÀU NGẪU NHIÊN CHO BIỂU ĐỒ TRÒN
function generateRandomColor($index) {
    $colors = ['bg-blue-600', 'bg-green-600', 'bg-yellow-600', 'bg-red-600', 'bg-indigo-600'];
    return $colors[$index % count($colors)];
}

?>

    <main class="flex-1 p-10">
      <h2 class="text-3xl font-bold text-red-500 mb-6">Bảng điều khiển quản trị</h2>

      <!-- Thống kê tổng quan -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-10">
        
        <!-- Tổng Doanh Thu -->
        <a href="bookings.php" class="block bg-gradient-to-br from-green-600 to-green-800 p-6 rounded-xl shadow-2xl border border-green-500/30 hover:scale-105 transition duration-300">
            <div class="flex items-center justify-between mb-3">
                <i data-lucide="dollar-sign" class="text-white w-10 h-10"></i>
                <span class="text-green-200 text-sm font-semibold bg-green-700/50 px-3 py-1 rounded-full">
                    <?= $totalPaidBookings ?> đơn
                </span>
            </div>
            <p class="text-sm uppercase text-green-200 font-semibold">Tổng Doanh Thu</p>
            <p class="text-4xl font-bold text-white mt-2"><?= number_format($totalRevenue, 0, ',', '.') ?>₫</p>
            <p class="text-xs text-green-200 mt-2">Từ <?= $totalPaidBookings ?> đơn đã thanh toán</p>
        </a>

        <!-- Đơn Hàng -->
        <a href="bookings.php" class="block bg-gradient-to-br from-blue-600 to-blue-800 p-6 rounded-xl shadow-2xl border border-blue-500/30 hover:scale-105 transition duration-300">
            <div class="flex items-center justify-between mb-3">
                <i data-lucide="shopping-cart" class="text-white w-10 h-10"></i>
                <span class="text-blue-200 text-sm font-semibold bg-blue-700/50 px-3 py-1 rounded-full">
                    <?= $totalPendingBookings ?> chờ
                </span>
            </div>
            <p class="text-sm uppercase text-blue-200 font-semibold">Tổng Đơn Hàng</p>
            <p class="text-4xl font-bold text-white mt-2"><?= number_format($totalBookings) ?></p>
            <div class="mt-2 flex gap-3 text-xs text-blue-200">
                <span>✓ <?= $totalPaidBookings ?> đã thanh toán</span>
                <span>⏳ <?= $totalPendingBookings ?> chờ xử lý</span>
            </div>
        </a>

        <!-- Vé Đã Bán -->
        <a href="bookings.php" class="block bg-gradient-to-br from-yellow-600 to-orange-700 p-6 rounded-xl shadow-2xl border border-yellow-500/30 hover:scale-105 transition duration-300">
            <div class="flex items-center justify-between mb-3">
                <i data-lucide="ticket" class="text-white w-10 h-10"></i>
                <span class="text-yellow-200 text-sm font-semibold bg-yellow-700/50 px-3 py-1 rounded-full">
                    Active
                </span>
            </div>
            <p class="text-sm uppercase text-yellow-200 font-semibold">Tổng Vé Đã Bán</p>
            <p class="text-4xl font-bold text-white mt-2"><?= number_format($totalTicketsSold) ?></p>
            <p class="text-xs text-yellow-200 mt-2">Vé đã đặt và đã check-in</p>
        </a>

      </div>

      <!-- Thống kê phụ -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-10">
        
        <a href="movies.php" class="block bg-gray-800 p-4 rounded-lg shadow-lg border border-gray-700 hover:border-purple-500 transition duration-300">
            <div class="flex items-center gap-3">
                <i data-lucide="film" class="text-purple-500 w-8 h-8"></i>
                <div>
                    <p class="text-xs text-gray-400 uppercase">Phim</p>
                    <p class="text-2xl font-bold text-white"><?= number_format($totalMovies) ?></p>
                </div>
            </div>
        </a>

        <a href="theaters.php" class="block bg-gray-800 p-4 rounded-lg shadow-lg border border-gray-700 hover:border-indigo-500 transition duration-300">
            <div class="flex items-center gap-3">
                <i data-lucide="building" class="text-indigo-500 w-8 h-8"></i>
                <div>
                    <p class="text-xs text-gray-400 uppercase">Rạp chiếu</p>
                    <p class="text-2xl font-bold text-white"><?= number_format($totalTheaters) ?></p>
                </div>
            </div>
        </a>

        <a href="shows.php" class="block bg-gray-800 p-4 rounded-lg shadow-lg border border-gray-700 hover:border-cyan-500 transition duration-300">
            <div class="flex items-center gap-3">
                <i data-lucide="calendar-days" class="text-cyan-500 w-8 h-8"></i>
                <div>
                    <p class="text-xs text-gray-400 uppercase">Suất chiếu</p>
                    <p class="text-2xl font-bold text-white"><?= number_format($showRepo->countAll()) ?></p>
                </div>
            </div>
        </a>
        
        <a href="users.php" class="block bg-gray-800 p-4 rounded-lg shadow-lg border border-gray-700 hover:border-red-500 transition duration-300">
            <div class="flex items-center gap-3">
                <i data-lucide="users" class="text-red-500 w-8 h-8"></i>
                <div>
                    <p class="text-xs text-gray-400 uppercase">Người dùng</p>
                    <p class="text-2xl font-bold text-white"><?= number_format($totalUsers) ?></p>
                </div>
            </div>
        </a>

      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          
          <div class="bg-gray-800 p-6 rounded-xl shadow-lg border border-gray-700 lg:col-span-2">
              <h3 class="text-xl font-semibold text-white mb-4">📊 Thống kê đơn hàng đã thanh toán</h3>
              <p class="text-gray-400 mb-6">Biểu đồ thể hiện số lượng đơn đã thanh toán trong 5 tháng gần nhất.</p>
              
              <div class="h-64 flex items-end justify-between space-x-4">
                  <?php if (empty($monthlyBookings)): ?>
                      <p class="text-gray-400 text-center w-full">Chưa có dữ liệu đặt vé để hiển thị biểu đồ.</p>
                  <?php endif; ?>

                  <?php foreach ($monthlyBookings as $month => $count): 
                      $heightPercent = ($count / $maxBookings) * 100;
                      $color = $heightPercent > 80 ? 'bg-green-500' : ($heightPercent > 50 ? 'bg-yellow-500' : 'bg-red-500');
                  ?>
                      <div class="flex flex-col items-center justify-end h-full flex-1 min-w-0">
                          <div 
                              class="w-10 rounded-t-lg <?= $color ?> transition-all duration-500 ease-out" 
                              style="height: <?= $heightPercent ?>%; min-height: 5px;"
                              title="<?= $count ?> lượt đặt"
                          ></div>
                          <span class="text-xs text-gray-400 mt-2 whitespace-nowrap"><?= $month ?></span>
                          <span class="text-xs text-white font-medium mt-1"><?= $count ?></span>
                      </div>
                  <?php endforeach; ?>
              </div>
          </div>
          
          <div class="bg-gray-800 p-6 rounded-xl shadow-lg border border-gray-700">
              <h3 class="text-xl font-semibold text-white mb-4">🎫 Phân bổ loại vé</h3>
              <p class="text-gray-400 mb-6">Tỷ lệ vé đã bán theo loại (Adult, Child, Senior, Student).</p>
              
              <div class="space-y-3">
                  <?php if ($totalTicketsForChart === 0): ?>
                      <p class="text-gray-400 text-center">Chưa có vé đã bán để thống kê.</p>
                  <?php endif; ?>

                  <?php 
                  $i = 0;
                  foreach ($ticketsByType as $type => $count): 
                      $percentage = ($count / $totalTicketsForChart) * 100;
                      $color = generateRandomColor($i);
                  ?>
                      <div class="flex justify-between items-center text-sm">
                          <div class="flex items-center space-x-2">
                              <span class="w-3 h-3 rounded-full <?= $color ?>"></span>
                              <span class="text-gray-300 capitalize"><?= htmlspecialchars($type) ?></span>
                          </div>
                          <span class="font-bold text-white"><?= round($percentage, 1) ?>% (<?= number_format($count) ?>)</span>
                      </div>
                      
                      <div class="w-full h-2 rounded-full bg-gray-700">
                          <div class="h-2 rounded-full <?= $color ?>" style="width: <?= $percentage ?>%;"></div>
                      </div>
                  <?php 
                      $i++;
                      endforeach; 
                  ?>
              </div>
          </div>
          
      </div>
<!-- 
      <h3 class="text-xl font-bold text-white mt-10 mb-4">Truy cập nhanh</h3>
      <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
        
        <a href="users.php" class="block bg-gray-800 p-6 rounded-xl shadow-lg border border-gray-700 hover:border-red-600 hover:shadow-2xl transition duration-200">
          <i data-lucide="users" class="text-red-500 w-8 h-8 mb-3"></i>
          <h3 class="text-xl font-semibold text-white mb-2">Quản lý người dùng</h3>
          <p class="text-gray-400 mb-4 text-sm">Xem danh sách, thêm hoặc xóa tài khoản người dùng.</p>
          <div class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-medium inline-block text-sm text-center">Truy cập</div>
        </a>

        <a href="movies.php" class="block bg-gray-800 p-6 rounded-xl shadow-lg border border-gray-700 hover:border-red-600 hover:shadow-2xl transition duration-200">
          <i data-lucide="film" class="text-red-500 w-8 h-8 mb-3"></i>
          <h3 class="text-xl font-semibold text-white mb-2">Quản lý phim</h3>
          <p class="text-gray-400 mb-4 text-sm">Thêm mới, chỉnh sửa, hoặc xóa thông tin phim đang chiếu.</p>
          <div class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-medium inline-block text-sm text-center">Truy cập</div>
        </a>
        
        <a href="bookings.php" class="block bg-gray-800 p-6 rounded-xl shadow-lg border border-gray-700 hover:border-red-600 hover:shadow-2xl transition duration-200">
          <i data-lucide="receipt" class="text-red-500 w-8 h-8 mb-3"></i>
          <h3 class="text-xl font-semibold text-white mb-2">Đơn đặt vé</h3>
          <p class="text-gray-400 mb-4 text-sm">Quản lý các đơn hàng và trạng thái thanh toán.</p>
          <div class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-medium inline-block text-sm text-center">Truy cập</div>
        </a>
        
        <a href="shows.php" class="block bg-gray-800 p-6 rounded-xl shadow-lg border border-gray-700 hover:border-red-600 hover:shadow-2xl transition duration-200">
          <i data-lucide="calendar-days" class="text-red-500 w-8 h-8 mb-3"></i>
          <h3 class="text-xl font-semibold text-white mb-2">Suất chiếu</h3>
          <p class="text-gray-400 mb-4 text-sm">Thiết lập lịch chiếu phim cho các phòng.</p>
          <div class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-medium inline-block text-sm text-center">Truy cập</div>
        </a>

      </div>
    </main> -->

</body>
</html>