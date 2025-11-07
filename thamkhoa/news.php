<?php
// Giả lập dữ liệu lấy từ Database (thay vì gọi $repo->getAll())
$demo_news = [
    [
        'id' => 1,
        'category_name' => 'Khuyến mãi',
        'title' => 'Giảm giá 50% vé xem phim cuối tuần này!',
        'summary' => 'Nhân dịp cuối tuần, Scarlet Cinema tung ra ưu đãi "Xem phim thả ga - Giảm 50%" cho tất cả các suất chiếu sau 22:00.',
        'image_url' => 'https://images.unsplash.com/photo-1540375849638-700b522306f0?q=80&w=1974&auto=format&fit=crop',
        'published_at' => '2025-11-06 14:30:00'
    ],
    [
        'id' => 2,
        'category_name' => 'Phim mới',
        'title' => 'Bom tấn "Hành Tinh Cát: Phần 2" chính thức có mặt',
        'summary' => 'Đừng bỏ lỡ siêu phẩm hành động viễn tưởng được mong chờ nhất năm. Phim đã có mặt tại tất cả các cụm rạp Scarlet.',
        'image_url' => 'https://images.unsplash.com/photo-1517604931442-7e0c8ed2963c?q=80&w=2070&auto=format&fit=crop',
        'published_at' => '2025-11-05 10:00:00'
    ],
    [
        'id' => 3,
        'category_name' => 'Sự kiện',
        'title' => 'Gặp gỡ đoàn làm phim "Mắt Biếc" tại Scarlet TPHCM',
        'summary' => 'Sự kiện giao lưu độc quyền với đạo diễn Victor Vũ và các diễn viên chính. Cơ hội nhận chữ ký và chụp ảnh lưu niệm.',
        'image_url' => 'https://images.unsplash.com/photo-1594909122845-11baa439b7bf?q=80&w=2070&auto=format&fit=crop',
        'published_at' => '2025-11-04 18:00:00'
    ]
];

// --- Bắt đầu trang HTML (Giả định bạn đã include header.php) ---
// require_once __DIR__ . "/header.php"; 
?>

<!DOCTYPE html>
<html lang="vi" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tin Tức | SCARLET CINEMA</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: '#dc2626',
                        'dark-bg': '#0a0a0a'
                    }
                }
            }
        };
    </script>
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-dark-bg text-gray-100" onload="lucide.createIcons();">

    <main class="max-w-7xl mx-auto p-6 space-y-12 pt-24">

        <section id="news-list">
            <h1 class="text-4xl font-extrabold text-white mb-8 border-b-4 border-primary pb-4">
                Tin Tức & Sự Kiện
            </h1>

            <?php if (empty($demo_news)): ?>
                <div class="text-center text-gray-400 text-xl p-12 bg-gray-800 rounded-lg">
                    <p>Hiện chưa có tin tức nào.</p>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    
                    <?php foreach ($demo_news as $article): ?>
                        <div class="bg-gray-800 rounded-xl overflow-hidden shadow-lg border border-gray-700/50
                                    transition-transform duration-300 ease-in-out hover:-translate-y-2 hover:shadow-primary/30 hover:shadow-lg">
                            
                            <a href="news-detail.php?id=<?= $article['id'] ?>" class="block h-48 overflow-hidden">
                                <img src="<?= htmlspecialchars($article['image_url']) ?>" 
                                     alt="<?= htmlspecialchars($article['title']) ?>"
                                     class="w-full h-full object-cover transition-transform duration-300 hover:scale-105">
                            </a>

                            <div class="p-5">
                                <div class="flex justify-between items-center text-xs text-gray-400 mb-3">
                                    <span class="inline-block bg-primary/20 text-primary px-3 py-1 rounded-full font-semibold">
                                        <?= htmlspecialchars($article['category_name']) ?>
                                    </span>
                                    <span class="flex items-center gap-1">
                                        <i data-lucide="calendar" class="w-3 h-3"></i>
                                        <?= date('d/m/Y', strtotime($article['published_at'])) ?>
                                    </span>
                                </div>

                                <h2 class="text-xl font-bold text-white mb-3 hover:text-primary transition">
                                    <a href="news-detail.php?id=<?= $article['id'] ?>">
                                        <?= htmlspecialchars($article['title']) ?>
                                    </a>
                                </h2>

                                <p class="text-sm text-gray-400 mb-5 line-clamp-3">
                                    <?= htmlspecialchars($article['summary']) ?>
                                </p>

                                <a href="news-detail.php?id=<?= $article['id'] ?>" 
                                   class="font-semibold text-primary hover:text-red-400 transition flex items-center gap-1">
                                    Đọc thêm
                                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>

                </div>
            <?php endif; ?>
        </section>

    </main>

    </body>
</html>