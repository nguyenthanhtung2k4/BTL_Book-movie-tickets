<?php
$adminName = "Admin Scarlet";
$title = "Dashboard";
$pageName = "Bảng điều khiển quản trị";
require_once __DIR__ ."/side_bar.php";
require_once __DIR__ ."/../../function/reponsitory.php";

$userRepo = new Repository('users');
$movieRepo = new Repository('movies');
$theaterRepo = new Repository('theaters'); // Cần Repository mới cho theaters
$bookingRepo = new Repository('bookings'); 
$bookingItemRepo = new Repository('booking_items'); // Đã sửa lại là 'booking_item'

// 2. LẤY DỮ LIỆU THỐNG KÊ TỔNG QUAN
$totalUsers = $userRepo->countAll();
$totalMovies = $movieRepo->countAll();
$totalTheaters = $theaterRepo->countTheaters(); // Thêm thống kê rạp
$totalBookings = $bookingRepo->countAll();
$totalTicketsSold = $bookingItemRepo->countBookedTickets();
$totalRevenue = $bookingRepo->getTotalRevenue() ?? 0; 

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

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-6 mb-10">
        
        <a href="bookings.php" class="block bg-gray-800 p-5 rounded-xl shadow-lg border border-gray-700 hover:border-red-600 hover:shadow-2xl transition duration-300 col-span-2">
            <i data-lucide="wallet" class="text-green-500 w-6 h-6 mb-2"></i>
            <p class="text-sm uppercase text-gray-400 font-semibold">Tổng Doanh Thu</p>
            <p class="text-3xl font-bold text-white mt-1"><?= number_format($totalRevenue, 0, ',', '.') ?>₫</p>
        </a>

        <a href="bookings.php" class="block bg-gray-800 p-5 rounded-xl shadow-lg border border-gray-700 hover:border-red-600 hover:shadow-2xl transition duration-300">
            <i data-lucide="receipt" class="text-blue-500 w-6 h-6 mb-2"></i>
            <p class="text-sm uppercase text-gray-400 font-semibold">Tổng Đơn Hàng</p>
            <p class="text-3xl font-bold text-white mt-1"><?= number_format($totalBookings) ?></p>
        </a>

        <a href="bookings.php" class="block bg-gray-800 p-5 rounded-xl shadow-lg border border-gray-700 hover:border-red-600 hover:shadow-2xl transition duration-300">
            <i data-lucide="ticket" class="text-yellow-500 w-6 h-6 mb-2"></i>
            <p class="text-sm uppercase text-gray-400 font-semibold">Tổng Vé Đã Bán</p>
            <p class="text-3xl font-bold text-white mt-1"><?= number_format($totalTicketsSold) ?></p>
        </a>
        
        <a href="movies.php" class="block bg-gray-800 p-5 rounded-xl shadow-lg border border-gray-700 hover:border-red-600 hover:shadow-2xl transition duration-300">
            <i data-lucide="film" class="text-purple-500 w-6 h-6 mb-2"></i>
            <p class="text-sm uppercase text-gray-400 font-semibold">Tổng Phim</p>
            <p class="text-3xl font-bold text-white mt-1"><?= number_format($totalMovies) ?></p>
        </a>

        <a href="theaters.php" class="block bg-gray-800 p-5 rounded-xl shadow-lg border border-gray-700 hover:border-red-600 hover:shadow-2xl transition duration-300">
            <i data-lucide="building" class="text-indigo-500 w-6 h-6 mb-2"></i>
            <p class="text-sm uppercase text-gray-400 font-semibold">Tổng Rạp</p>
            <p class="text-3xl font-bold text-white mt-1"><?= number_format($totalTheaters) ?></p>
        </a>
        
        <a href="users.php" class="block bg-gray-800 p-5 rounded-xl shadow-lg border border-gray-700 hover:border-red-600 hover:shadow-2xl transition duration-300 col-span-2 sm:col-span-1">
            <i data-lucide="users" class="text-red-500 w-6 h-6 mb-2"></i>
            <p class="text-sm uppercase text-gray-400 font-semibold">Tổng Người Dùng</p>
            <p class="text-3xl font-bold text-white mt-1"><?= number_format($totalUsers) ?></p>
        </a>

      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          
          <div class="bg-gray-800 p-6 rounded-xl shadow-lg border border-gray-700 lg:col-span-2">
              <h3 class="text-xl font-semibold text-white mb-4">Thống kê số lượng đặt vé theo tháng</h3>
              <p class="text-gray-400 mb-6">Biểu đồ thể hiện sát suất đặt vé (đơn hàng) trong 5 tháng gần nhất.</p>
              
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
              <h3 class="text-xl font-semibold text-white mb-4">Phân bổ loại vé đã bán</h3>
              <p class="text-gray-400 mb-6">Tỷ lệ vé theo loại (Người lớn, Trẻ em, Học sinh...).</p>
              
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