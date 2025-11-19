<?php
$title = "Tìm kiếm";
require_once __DIR__ . "/side_bar.php";
require_once __DIR__ . "/../../handle/search_handle.php";

$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$entity = isset($_GET['entity']) ? strtolower(trim($_GET['entity'])) : 'all';
$results = [
  'users' => [], 'theaters' => [], 'screens' => [], 'movies' => [], 'shows' => [], 'bookings' => []
];
$single = [];
if ($q !== '') {
  if ($entity === 'all') {
    $results = searchAll($q, 10);
  } else {
    $single = searchByEntity($entity, $q, 10);
  }
}
?>

<main class="flex-1 p-8">
  <h2 class="text-3xl font-bold text-red-500 mb-6">Tìm kiếm nhanh</h2>

  <form method="get" class="mb-8">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
      <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Nhập từ khóa..."
             class="md:col-span-2 bg-gray-800 border border-gray-700 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-red-500" />
      <select name="entity" class="bg-gray-800 border border-gray-700 rounded-lg px-4 py-2 text-white">
        <?php $opts = ['all'=>'Tất cả','users'=>'Users','theaters'=>'Theaters','screens'=>'Screens','movies'=>'Movies','shows'=>'Shows','bookings'=>'Bookings'];
          foreach ($opts as $val => $label): ?>
          <option value="<?= $val ?>" <?= $entity === $val ? 'selected' : '' ?>><?= $label ?></option>
        <?php endforeach; ?>
      </select>
      <div class="md:col-span-3 flex justify-end">
        <button class="bg-red-600 hover:bg-red-700 text-white font-semibold px-5 py-2 rounded-lg">Tìm kiếm</button>
      </div>
    </div>
  </form>

  <?php if ($q === ''): ?>
    <p class="text-gray-400">Nhập từ khóa để tìm trong Users, Theaters, Screens, Movies, Shows, Bookings.</p>
  <?php else: ?>
    <?php if ($entity !== 'all'): ?>
      <div class="grid grid-cols-1 gap-6">
        <?php if ($entity === 'users'): ?>
          <section class="bg-gray-800 p-5 rounded-xl border border-gray-700">
            <div class="flex items-center justify-between mb-3">
              <h3 class="text-xl font-semibold text-white">Users</h3>
              <span class="text-sm text-gray-300"><?= count($single) ?> kết quả</span>
            </div>
            <div class="overflow-x-auto">
              <table class="min-w-full text-sm">
                <thead>
                  <tr class="text-gray-400">
                    <th class="text-left py-2">ID</th>
                    <th class="text-left py-2">Họ tên</th>
                    <th class="text-left py-2">Email</th>
                    <th class="text-left py-2">Role</th>
                    <th class="text-left py-2">Thao tác</th>
                  </tr>
                </thead>
                <tbody>
                <?php foreach ($single as $u): ?>
                  <tr class="border-t border-gray-700">
                    <td class="py-2 text-gray-300"><?= $u['id'] ?></td>
                    <td class="py-2 text-white"><?= htmlspecialchars($u['full_name'] ?? '') ?></td>
                    <td class="py-2 text-gray-300"><?= htmlspecialchars($u['email'] ?? '') ?></td>
                    <td class="py-2 text-gray-300 capitalize"><?= htmlspecialchars($u['role'] ?? '') ?></td>
                    <td class="py-2">
                      <a class="text-blue-400 hover:underline" href="editUser.php?id=<?= $u['id'] ?>">Sửa</a>
                    </td>
                  </tr>
                <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </section>
        <?php elseif ($entity === 'theaters'): ?>
          <section class="bg-gray-800 p-5 rounded-xl border border-gray-700">
            <div class="flex items-center justify-between mb-3">
              <h3 class="text-xl font-semibold text-white">Theaters</h3>
              <span class="text-sm text-gray-300"><?= count($single) ?> kết quả</span>
            </div>
            <div class="overflow-x-auto">
              <table class="min-w-full text-sm">
                <thead>
                  <tr class="text-gray-400">
                    <th class="text-left py-2">ID</th>
                    <th class="text-left py-2">Tên rạp</th>
                    <th class="text-left py-2">Thành phố</th>
                    <th class="text-left py-2">Địa chỉ</th>
                    <th class="text-left py-2">Thao tác</th>
                  </tr>
                </thead>
                <tbody>
                <?php foreach ($single as $t): ?>
                  <tr class="border-t border-gray-700">
                    <td class="py-2 text-gray-300"><?= $t['id'] ?></td>
                    <td class="py-2 text-white"><?= htmlspecialchars($t['name'] ?? '') ?></td>
                    <td class="py-2 text-gray-300"><?= htmlspecialchars($t['city'] ?? '') ?></td>
                    <td class="py-2 text-gray-300"><?= htmlspecialchars($t['address'] ?? '') ?></td>
                    <td class="py-2">
                      <a class="text-blue-400 hover:underline" href="editTheater.php?id=<?= $t['id'] ?>">Sửa</a>
                    </td>
                  </tr>
                <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </section>
        <?php elseif ($entity === 'screens'): ?>
          <section class="bg-gray-800 p-5 rounded-xl border border-gray-700">
            <div class="flex items-center justify-between mb-3">
              <h3 class="text-xl font-semibold text-white">Screens</h3>
              <span class="text-sm text-gray-300"><?= count($single) ?> kết quả</span>
            </div>
            <div class="overflow-x-auto">
              <table class="min-w-full text-sm">
                <thead>
                  <tr class="text-gray-400">
                    <th class="text-left py-2">ID</th>
                    <th class="text-left py-2">Tên phòng</th>
                    <th class="text-left py-2">Rạp</th>
                    <th class="text-left py-2">Loại</th>
                    <th class="text-left py-2">Thao tác</th>
                  </tr>
                </thead>
                <tbody>
                <?php foreach ($single as $s): ?>
                  <tr class="border-t border-gray-700">
                    <td class="py-2 text-gray-300"><?= $s['id'] ?></td>
                    <td class="py-2 text-white"><?= htmlspecialchars($s['name'] ?? '') ?></td>
                    <td class="py-2 text-gray-300"><?= htmlspecialchars($s['theater_name'] ?? '') ?></td>
                    <td class="py-2 text-gray-300"><?= htmlspecialchars($s['screen_type'] ?? '') ?></td>
                    <td class="py-2">
                      <a class="text-blue-400 hover:underline" href="editScreen.php?id=<?= $s['id'] ?>">Sửa</a>
                    </td>
                  </tr>
                <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </section>
        <?php elseif ($entity === 'movies'): ?>
          <section class="bg-gray-800 p-5 rounded-xl border border-gray-700">
            <div class="flex items-center justify-between mb-3">
              <h3 class="text-xl font-semibold text-white">Movies</h3>
              <span class="text-sm text-gray-300"><?= count($single) ?> kết quả</span>
            </div>
            <div class="overflow-x-auto">
              <table class="min-w-full text-sm">
                <thead>
                  <tr class="text-gray-400">
                    <th class="text-left py-2">ID</th>
                    <th class="text-left py-2">Tiêu đề</th>
                    <th class="text-left py-2">Đánh giá</th>
                    <th class="text-left py-2">Thao tác</th>
                  </tr>
                </thead>
                <tbody>
                <?php foreach ($single as $m): ?>
                  <tr class="border-t border-gray-700">
                    <td class="py-2 text-gray-300"><?= $m['id'] ?></td>
                    <td class="py-2 text-white"><?= htmlspecialchars($m['title'] ?? '') ?></td>
                    <td class="py-2 text-gray-300"><?= htmlspecialchars($m['rating'] ?? '') ?></td>
                    <td class="py-2">
                      <a class="text-blue-400 hover:underline" href="editMovie.php?id=<?= $m['id'] ?>">Sửa</a>
                    </td>
                  </tr>
                <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </section>
        <?php elseif ($entity === 'shows'): ?>
          <section class="bg-gray-800 p-5 rounded-xl border border-gray-700">
            <div class="flex items-center justify-between mb-3">
              <h3 class="text-xl font-semibold text-white">Shows</h3>
              <span class="text-sm text-gray-300"><?= count($single) ?> kết quả</span>
            </div>
            <div class="overflow-x-auto">
              <table class="min-w-full text-sm">
                <thead>
                  <tr class="text-gray-400">
                    <th class="text-left py-2">ID</th>
                    <th class="text-left py-2">Phim</th>
                    <th class="text-left py-2">Phòng</th>
                    <th class="text-left py-2">Thời gian</th>
                    <th class="text-left py-2">Thao tác</th>
                  </tr>
                </thead>
                <tbody>
                <?php foreach ($single as $sh): ?>
                  <tr class="border-t border-gray-700">
                    <td class="py-2 text-gray-300"><?= $sh['id'] ?></td>
                    <td class="py-2 text-white"><?= htmlspecialchars($sh['movie_title'] ?? '') ?></td>
                    <td class="py-2 text-gray-300"><?= htmlspecialchars($sh['screen_name'] ?? '') ?></td>
                    <td class="py-2 text-gray-300"><?= htmlspecialchars($sh['show_time'] ?? '') ?></td>
                    <td class="py-2">
                      <a class="text-blue-400 hover:underline" href="editShow.php?id=<?= $sh['id'] ?>">Sửa</a>
                    </td>
                  </tr>
                <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </section>
        <?php elseif ($entity === 'bookings'): ?>
          <section class="bg-gray-800 p-5 rounded-xl border border-gray-700">
            <div class="flex items-center justify-between mb-3">
              <h3 class="text-xl font-semibold text-white">Bookings</h3>
              <span class="text-sm text-gray-300"><?= count($single) ?> kết quả</span>
            </div>
            <div class="overflow-x-auto">
              <table class="min-w-full text-sm">
                <thead>
                  <tr class="text-gray-400">
                    <th class="text-left py-2">ID</th>
                    <th class="text-left py-2">Người dùng</th>
                    <th class="text-left py-2">Phim/Phòng</th>
                    <th class="text-left py-2">Thời gian</th>
                    <th class="text-left py-2">Trạng thái</th>
                    <th class="text-left py-2">Thanh toán</th>
                    <th class="text-left py-2">Tổng tiền</th>
                    <th class="text-left py-2">Thao tác</th>
                  </tr>
                </thead>
                <tbody>
                <?php foreach ($single as $b): ?>
                  <tr class="border-t border-gray-700">
                    <td class="py-2 text-gray-300"><?= $b['id'] ?></td>
                    <td class="py-2 text-white"><?= htmlspecialchars(($b['user_name'] ?? '') !== '' ? $b['user_name'] : ($b['user_email'] ?? 'N/A')) ?></td>
                    <td class="py-2 text-gray-300"><?= htmlspecialchars(($b['movie_title'] ?? '')) ?> / <?= htmlspecialchars($b['screen_name'] ?? '') ?></td>
                    <td class="py-2 text-gray-300"><?= htmlspecialchars($b['show_time'] ?? '') ?></td>
                    <td class="py-2 text-gray-300"><?= htmlspecialchars($b['status'] ?? '') ?></td>
                    <td class="py-2 text-gray-300"><?= htmlspecialchars($b['payment_status'] ?? '') ?></td>
                    <td class="py-2 text-gray-300"><?= number_format($b['total_amount'] ?? 0, 0, ',', '.') ?>₫</td>
                    <td class="py-2">
                      <a class="text-blue-400 hover:underline" href="bookings.php">Xem</a>
                    </td>
                  </tr>
                <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </section>
        <?php endif; ?>
      </div>
    <?php else: ?>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Users -->
      <section class="bg-gray-800 p-5 rounded-xl border border-gray-700">
        <div class="flex items-center justify-between mb-3">
          <h3 class="text-xl font-semibold text-white">Users</h3>
          <span class="text-sm text-gray-300"><?= count($results['users']) ?> kết quả</span>
        </div>
        <div class="overflow-x-auto">
          <table class="min-w-full text-sm">
            <thead>
              <tr class="text-gray-400">
                <th class="text-left py-2">ID</th>
                <th class="text-left py-2">Họ tên</th>
                <th class="text-left py-2">Email</th>
                <th class="text-left py-2">Role</th>
                <th class="text-left py-2">Thao tác</th>
              </tr>
            </thead>
            <tbody>
            <?php foreach ($results['users'] as $u): ?>
              <tr class="border-t border-gray-700">
                <td class="py-2 text-gray-300"><?= $u['id'] ?></td>
                <td class="py-2 text-white"><?= htmlspecialchars($u['full_name'] ?? '') ?></td>
                <td class="py-2 text-gray-300"><?= htmlspecialchars($u['email'] ?? '') ?></td>
                <td class="py-2 text-gray-300 capitalize"><?= htmlspecialchars($u['role'] ?? '') ?></td>
                <td class="py-2">
                  <a class="text-blue-400 hover:underline" href="editUser.php?id=<?= $u['id'] ?>">Sửa</a>
                </td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </section>

      <!-- Theaters -->
      <section class="bg-gray-800 p-5 rounded-xl border border-gray-700">
        <div class="flex items-center justify-between mb-3">
          <h3 class="text-xl font-semibold text-white">Theaters</h3>
          <span class="text-sm text-gray-300"><?= count($results['theaters']) ?> kết quả</span>
        </div>
        <div class="overflow-x-auto">
          <table class="min-w-full text-sm">
            <thead>
              <tr class="text-gray-400">
                <th class="text-left py-2">ID</th>
                <th class="text-left py-2">Tên rạp</th>
                <th class="text-left py-2">Thành phố</th>
                <th class="text-left py-2">Địa chỉ</th>
                <th class="text-left py-2">Thao tác</th>
              </tr>
            </thead>
            <tbody>
            <?php foreach ($results['theaters'] as $t): ?>
              <tr class="border-t border-gray-700">
                <td class="py-2 text-gray-300"><?= $t['id'] ?></td>
                <td class="py-2 text-white"><?= htmlspecialchars($t['name'] ?? '') ?></td>
                <td class="py-2 text-gray-300"><?= htmlspecialchars($t['city'] ?? '') ?></td>
                <td class="py-2 text-gray-300"><?= htmlspecialchars($t['address'] ?? '') ?></td>
                <td class="py-2">
                  <a class="text-blue-400 hover:underline" href="editTheater.php?id=<?= $t['id'] ?>">Sửa</a>
                </td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </section>

      <!-- Screens -->
      <section class="bg-gray-800 p-5 rounded-xl border border-gray-700">
        <div class="flex items-center justify-between mb-3">
          <h3 class="text-xl font-semibold text-white">Screens</h3>
          <span class="text-sm text-gray-300"><?= count($results['screens']) ?> kết quả</span>
        </div>
        <div class="overflow-x-auto">
          <table class="min-w-full text-sm">
            <thead>
              <tr class="text-gray-400">
                <th class="text-left py-2">ID</th>
                <th class="text-left py-2">Tên phòng</th>
                <th class="text-left py-2">Rạp</th>
                <th class="text-left py-2">Loại</th>
                <th class="text-left py-2">Thao tác</th>
              </tr>
            </thead>
            <tbody>
            <?php foreach ($results['screens'] as $s): ?>
              <tr class="border-t border-gray-700">
                <td class="py-2 text-gray-300"><?= $s['id'] ?></td>
                <td class="py-2 text-white"><?= htmlspecialchars($s['name'] ?? '') ?></td>
                <td class="py-2 text-gray-300"><?= htmlspecialchars($s['theater_name'] ?? '') ?></td>
                <td class="py-2 text-gray-300"><?= htmlspecialchars($s['screen_type'] ?? '') ?></td>
                <td class="py-2">
                  <a class="text-blue-400 hover:underline" href="editScreen.php?id=<?= $s['id'] ?>">Sửa</a>
                </td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </section>

      <!-- Movies -->
      <section class="bg-gray-800 p-5 rounded-xl border border-gray-700">
        <div class="flex items-center justify-between mb-3">
          <h3 class="text-xl font-semibold text-white">Movies</h3>
          <span class="text-sm text-gray-300"><?= count($results['movies']) ?> kết quả</span>
        </div>
        <div class="overflow-x-auto">
          <table class="min-w-full text-sm">
            <thead>
              <tr class="text-gray-400">
                <th class="text-left py-2">ID</th>
                <th class="text-left py-2">Tiêu đề</th>
                <th class="text-left py-2">Đánh giá</th>
                <th class="text-left py-2">Thao tác</th>
              </tr>
            </thead>
            <tbody>
            <?php foreach ($results['movies'] as $m): ?>
              <tr class="border-t border-gray-700">
                <td class="py-2 text-gray-300"><?= $m['id'] ?></td>
                <td class="py-2 text-white"><?= htmlspecialchars($m['title'] ?? '') ?></td>
                <td class="py-2 text-gray-300"><?= htmlspecialchars($m['rating'] ?? '') ?></td>
                <td class="py-2">
                  <a class="text-blue-400 hover:underline" href="editMovie.php?id=<?= $m['id'] ?>">Sửa</a>
                </td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </section>

      <!-- Shows -->
      <section class="bg-gray-800 p-5 rounded-xl border border-gray-700">
        <div class="flex items-center justify-between mb-3">
          <h3 class="text-xl font-semibold text-white">Shows</h3>
          <span class="text-sm text-gray-300"><?= count($results['shows']) ?> kết quả</span>
        </div>
        <div class="overflow-x-auto">
          <table class="min-w-full text-sm">
            <thead>
              <tr class="text-gray-400">
                <th class="text-left py-2">ID</th>
                <th class="text-left py-2">Phim</th>
                <th class="text-left py-2">Phòng</th>
                <th class="text-left py-2">Thời gian</th>
                <th class="text-left py-2">Thao tác</th>
              </tr>
            </thead>
            <tbody>
            <?php foreach ($results['shows'] as $sh): ?>
              <tr class="border-t border-gray-700">
                <td class="py-2 text-gray-300"><?= $sh['id'] ?></td>
                <td class="py-2 text-white"><?= htmlspecialchars($sh['movie_title'] ?? '') ?></td>
                <td class="py-2 text-gray-300"><?= htmlspecialchars($sh['screen_name'] ?? '') ?></td>
                <td class="py-2 text-gray-300"><?= htmlspecialchars($sh['show_time'] ?? '') ?></td>
                <td class="py-2">
                  <a class="text-blue-400 hover:underline" href="editShow.php?id=<?= $sh['id'] ?>">Sửa</a>
                </td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </section>
      <!-- Bookings -->
      <section class="bg-gray-800 p-5 rounded-xl border border-gray-700">
        <div class="flex items-center justify-between mb-3">
          <h3 class="text-xl font-semibold text-white">Bookings</h3>
          <span class="text-sm text-gray-300"><?= count($results['bookings']) ?> kết quả</span>
        </div>
        <div class="overflow-x-auto">
          <table class="min-w-full text-sm">
            <thead>
              <tr class="text-gray-400">
                <th class="text-left py-2">ID</th>
                <th class="text-left py-2">Người dùng</th>
                <th class="text-left py-2">Phim/Phòng</th>
                <th class="text-left py-2">Thời gian</th>
                <th class="text-left py-2">Trạng thái</th>
                <th class="text-left py-2">Thanh toán</th>
                <th class="text-left py-2">Tổng tiền</th>
                <th class="text-left py-2">Thao tác</th>
              </tr>
            </thead>
            <tbody>
            <?php foreach ($results['bookings'] as $b): ?>
              <tr class="border-t border-gray-700">
                <td class="py-2 text-gray-300"><?= $b['id'] ?></td>
                <td class="py-2 text-white"><?= htmlspecialchars(($b['user_name'] ?? '') !== '' ? $b['user_name'] : ($b['user_email'] ?? 'N/A')) ?></td>
                <td class="py-2 text-gray-300"><?= htmlspecialchars(($b['movie_title'] ?? '')) ?> / <?= htmlspecialchars($b['screen_name'] ?? '') ?></td>
                <td class="py-2 text-gray-300"><?= htmlspecialchars($b['show_time'] ?? '') ?></td>
                <td class="py-2 text-gray-300"><?= htmlspecialchars($b['status'] ?? '') ?></td>
                <td class="py-2 text-gray-300"><?= htmlspecialchars($b['payment_status'] ?? '') ?></td>
                <td class="py-2 text-gray-300"><?= number_format($b['total_amount'] ?? 0, 0, ',', '.') ?>₫</td>
                <td class="py-2">
                  <a class="text-blue-400 hover:underline" href="bookings.php">Xem</a>
                </td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </section>
    </div>
    <?php endif; ?>
  <?php endif; ?>
</main>

</body>
</html>