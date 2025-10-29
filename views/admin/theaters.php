<?php
$adminName = "Admin Scarlet";
$title = "Rạp chiếu";
$pageName = "Quản lý rạp chiếu phim";

if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . "/../../function/reponsitory.php";
require_once __DIR__ . "/side_bar.php";

$repo = new Repository('theaters');
$theaters = $repo->getAll(); // Lấy tất cả rạp

$flash_message = $_SESSION['flash_message'] ?? '';
$flash_success = $_SESSION['flash_success'] ?? false;
unset($_SESSION['flash_message'], $_SESSION['flash_success']);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <title><?=($pageName) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Tùy chỉnh màu sắc và hiệu ứng cho Dark Mode */
        .table-header {
            background-color: #1f2937; /* gray-900 đậm hơn */
        }
        .table-row:nth-child(even) {
            background-color: #1f2937; /* gray-900 */
        }
        .table-row:hover {
            background-color: #374151; /* gray-700 nhẹ khi hover */
        }
        .sticky-header th {
            position: sticky;
            top: 0;
            z-index: 10;
        }
    </style>
</head>
<body class="bg-gray-900 text-gray-100">
<main class="flex-1 p-8 sm:p-10 min-h-screen">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center pb-6 border-b-2 border-red-600 mb-8">
        <h2 class="text-3xl font-extrabold text-white tracking-wide mb-4 sm:mb-0">
            <span class="text-red-400"></span> <?= $pageName ?>
        </h2>
        <a href="addTheater.php"
           class="bg-blue-600 hover:bg-blue-800 text-white font-bold px-6 py-2 rounded-full shadow-lg transition-all duration-300 transform hover:scale-[1.05]">
            Thêm rạp mới
        </a>
    </div>
    
    <?php if ($flash_message): ?>
        <div id="flash-message" class="fixed top-6 right-6 z-50 px-6 py-3 rounded-lg shadow-xl text-white font-semibold 
            <?= $flash_success ? 'bg-green-600 border border-green-400' : 'bg-red-700 border border-red-400' ?>">
            <?=($flash_message) ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($theaters)): ?>
        <div class="bg-gray-800 rounded-xl shadow-2xl overflow-x-auto border border-gray-700">
            <table class="min-w-full divide-y divide-gray-700">
                
                <thead class="bg-gray-700 sticky-header">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-300 uppercase tracking-wider">#ID</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-300 uppercase tracking-wider">Tên Rạp</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-300 uppercase tracking-wider">Thành phố</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-300 uppercase tracking-wider">Địa chỉ</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-300 uppercase tracking-wider">Điện thoại</th>
                        <th class="px-6 py-3 text-center text-xs font-bold text-gray-300 uppercase tracking-wider">Thao tác</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-700">
                    <?php foreach ($theaters as $theater): ?>
                        <tr class="table-row">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-400">
                                <?=($theater['id']) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-base font-semibold text-white">
                                <?=($theater['name']) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                                🏙️ <?=($theater['city']) ?>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-400 max-w-xs truncate">
                                📍 <?=($theater['address']) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                                📞 <?=($theater['phone'] ?? '—') ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                <div class="flex justify-center space-x-3">
                                    <a href="editTheater.php?action=edit&id=<?= $theater['id'] ?>" 
                                       title="Chỉnh sửa"
                                       class="text-blue-400 hover:text-blue-500 p-2 rounded-full hover:bg-gray-700 transition">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-7-7l4 4m-4-4l-4 4"/></svg>
                                    </a>
                                    <a href="deleteTheater.php?action=delete&id=<?= $theater['id'] ?>" 
                                       title="Xóa"
                                       class="text-red-400 hover:text-red-500 p-2 rounded-full hover:bg-gray-700 transition"
                                       onclick="return confirm('Bạn có chắc chắn muốn xóa rạp chiếu [<?=($theater['name']) ?>] không?')">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    <?php else: ?>
        <div class="text-center text-gray-400 text-xl mt-20 p-8 border-2 border-dashed border-gray-700 rounded-xl max-w-lg mx-auto bg-gray-800/50">
            <p class="mb-4">🚫 Chưa có rạp chiếu phim nào được thêm vào hệ thống.</p>
            <p>Hãy thêm rạp đầu tiên của bạn!</p>
        </div>
    <?php endif; ?>
</main>
</body>

<script>
    setTimeout(() => {
        const flash = document.getElementById('flash-message');
        if (flash) {
            flash.style.opacity = '0';
            flash.style.transform = 'translateY(-10px)';
            setTimeout(() => flash.remove(), 500);
        }
    }, 3000); // Tăng thời gian hiển thị lên 3 giây
</script>
</html>