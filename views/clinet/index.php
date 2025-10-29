<?php
session_start();
require_once __DIR__ ."/../../function/reponsitory.php";
$repo =  new Repository('movies');
$data= $repo->getAll(); 
$today = date('Y-m-d');
$siteTitle = "PHÒNG VÉ SCARLET | Trải Nghiệm Điện Ảnh Tối Thượng";
?>


<!DOCTYPE html>
<html lang="vi" class="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($siteTitle) ?></title>

  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../../asset/css/style.css">
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
</head>

<body class="bg-dark-bg text-gray-100 font-[Inter] pt-16" onload="lucide.createIcons();">

  <header class="fixed top-0 w-full bg-dark-bg/90 backdrop-blur-md shadow-lg z-50">
    <nav class="max-w-7xl mx-auto flex justify-between items-center p-4">
      <a href="#" class="flex items-center space-x-2">
        <i data-lucide='popcorn' class='text-primary'></i>
        <span class="font-bold text-2xl">SCARLET CINEMA</span>
      </a>
      <div class="hidden md:flex space-x-6">
        <a href="#featured" class="hover:text-primary">NỔI BẬT</a>
        <a href="#current" class="hover:text-primary">ĐANG CHIẾU</a>
        <a href="#upcoming" class="hover:text-primary">SẮP CHIẾU</a>
      </div>
      <button class="bg-primary px-4 py-2 rounded text-black font-semibold hover:bg-red-500 transition">ĐẶT VÉ</button>
    </nav>
  </header>

  <main class="max-w-7xl mx-auto p-6 space-y-20">

   <section id="featured" class="text-center relative overflow-hidden">
  <h1 class="text-5xl font-extrabold text-white mb-10">
    Trải Nghiệm <span class="text-primary">Điện Ảnh Tối Thượng</span>
  </h1>

  <div id="carousel" class="relative overflow-hidden max-w-6xl mx-auto">
    <div id="slides" class="flex transition-transform duration-[1200ms] ease-in-out">
   <?php foreach ($data as $index => $movie): ?>
<div class="w-1/3 flex-shrink-0 p-2">
  <img src="<?= htmlspecialchars($movie['banner_url'] ?? '') ?>"
       class="w-full aspect-video object-cover rounded-lg shadow-lg"
       alt="<?= htmlspecialchars($movie['title'] ?? 'Poster phim') ?>">
</div>
<?php endforeach; ?>
    </div>


    <button id="prevSlide"
      class="absolute left-0 top-1/2 transform -translate-y-1/2 bg-black/40 hover:bg-black/70 p-3 rounded-r text-white z-10">
      &#10094;
    </button>
    <button id="nextSlide"
      class="absolute right-0 top-1/2 transform -translate-y-1/2 bg-black/40 hover:bg-black/70 p-3 rounded-l text-white z-10">
      &#10095;
    </button>
  </div>
</section>

<section id="current">
  <h2 class="text-3xl font-bold mb-8 border-b-4 border-primary inline-block">PHIM ĐANG CHIẾU</h2>
  <div class="grid grid-cols-2 md:grid-cols-4 gap-6">

    <?php
    // LỌC DỮ LIỆU TRƯỚC KHI LẶP
    $currentMovies = array_filter($data, function($m) use ($today) {
        return (!empty($m['release_date']) && $m['release_date'] <= $today);
    });
    usort($currentMovies, function($a, $b) {
        return strtotime($b['release_date']) - strtotime($a['release_date']);
    });
    $currentMovies = array_slice($currentMovies, 0, 8);
    
    if (empty($currentMovies)):
    ?>
        <p class="col-span-full text-center text-gray-400">Chưa có phim đang chiếu.</p>
    <?php
    else:
        foreach ($currentMovies as $movie):
            $banner = htmlspecialchars($movie['banner_url'] ?? '../../asset/img/no-banner.png');
            $title = htmlspecialchars($movie['title'] ?? 'Không có tiêu đề');
            $rating = htmlspecialchars($movie['rating'] ?? 'N/A');
            $trailer_url = htmlspecialchars($movie['trailer_url'] ?? '#');
    ?>

    <div class='bg-gray-800 rounded-xl overflow-hidden shadow-lg'>
       <button class="open-details-modal w-full" 
        data-movie='<?= htmlspecialchars(json_encode($movie), ENT_QUOTES, 'UTF-8'); ?>'>
    <img src='<?= $banner ?>' alt="<?= $title ?>" class='w-full h-64 object-cover'>
</button>
        <div class='p-4 space-y-3'>
            <h3 class='font-semibold text-white truncate' title="<?= $title ?>"><?= $title ?></h3>
            <p class="text-sm text-yellow-400 flex items-center gap-1">
                ⭐ <?= $rating ?>
            </p>
            <div class="flex gap-2">
                <button class='flex-1 bg-primary text-black py-2 rounded font-semibold hover:bg-red-500 transition'>Đặt vé</button>
                
                <?php if ($trailer_url !== '#'): ?>
                    <button
                        data-trailer-url="<?= $trailer_url ?>"
                        class='open-trailer-modal flex-1 bg-gray-700 text-white py-2 rounded text-center hover:bg-gray-600 transition'>
                        Trailer
                    </button>
                <?php else: ?>
                    <button class='flex-1 bg-gray-600 text-white py-2 rounded opacity-60 cursor-not-allowed' disabled>Trailer</button>
                <?php endif; ?>
                </div>
        </div>
    </div>
    
    <?php
        endforeach; 
    endif; 
    ?>

  </div>
</section>

    <section id="upcoming" class="mt-16">
  <h2 class="text-3xl font-bold mb-8 border-b-4 border-primary inline-block">PHIM SẮP CHIẾU</h2>
  <div class="grid grid-cols-2 md:grid-cols-4 gap-6">

    <?php
    // LỌC DỮ LIỆU SẮP CHIẾU
    $upcomingMovies = array_filter($data, function($m) use ($today) {
        return (!empty($m['release_date']) && $m['release_date'] > $today);
    });
    usort($upcomingMovies, function($a, $b) {
        return strtotime($a['release_date']) - strtotime($b['release_date']);
    });
    $upcomingMovies = array_slice($upcomingMovies, 0, 8);

    if (empty($upcomingMovies)):
    ?>
        <p class="col-span-full text-center text-gray-400">Hiện chưa có thông tin phim sắp chiếu.</p>
    <?php
    else:
        foreach ($upcomingMovies as $movie):
            $banner = htmlspecialchars($movie['banner_url'] ?? '../../asset/img/no-banner.png');
            $title = htmlspecialchars($movie['title'] ?? 'Không có tiêu đề');
            $release_date = date('d/m/Y', strtotime($movie['release_date']));
            $trailer_url = htmlspecialchars($movie['trailer_url'] ?? '#');
    ?>

    <div class='bg-gray-800 rounded-xl overflow-hidden shadow-lg'>
        <button class="open-details-modal w-full" 
        data-movie='<?= htmlspecialchars(json_encode($movie), ENT_QUOTES, 'UTF-8'); ?>'>
    <img src='<?= $banner ?>' alt="<?= $title ?>" class='w-full h-64 object-cover'>
</button>
        <div class='p-4 space-y-3'>
            <h3 class='font-semibold text-white truncate' title="<?= $title ?>"><?= $title ?></h3>
            
            <p class="text-sm text-primary flex items-center gap-1">
                <i data-lucide="calendar" class="w-4 h-4"></i> Khởi Chiếu: <?= $release_date ?>
            </p>

            <div class="flex gap-2">
            <button class='flex-1 bg-primary text-black py-2 rounded font-semibold hover:bg-red-500 transition'>Sắp khởi chiếu</button>
    
            <?php if ($trailer_url !== '#'): ?>
                <button
                    data-trailer-url="<?= $trailer_url ?>"
                    class='open-trailer-modal flex-1 bg-gray-700 text-white py-2 rounded text-center hover:bg-gray-600 transition'>
                    Trailer
                </button>
            <?php else: ?>
                <button class='flex-1 bg-gray-600 text-white py-2 rounded opacity-60 cursor-not-allowed' disabled>Trailer</button>
            <?php endif; ?>
            </div>
        </div>
    </div>
    
    <?php
        endforeach;
    endif;
    ?>

  </div>
</section>
  </main>

  <footer id="contact" class="bg-gray-900 border-t border-primary/20 mt-10">
     <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 text-gray-400">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <a href="#" class="flex items-center space-x-2 mb-4">
                        <i data-lucide="popcorn" class="text-primary h-7 w-7"></i>
                        <span class="text-xl font-bold tracking-wider text-white">SCARLET CINEMA</span>
                    </a>
                    <p class="text-sm">Nền tảng đặt vé xem phim hàng đầu Việt Nam. Cung cấp trải nghiệm điện ảnh chân thực và tiện lợi nhất.</p>
                </div>
                <div>
                    <h4 class="text-lg font-semibold text-white mb-4">DANH MỤC</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="#current" class="hover:text-primary transition duration-300">Phim Đang Chiếu</a></li>
                        <li><a href="#upcoming" class="hover:text-primary transition duration-300">Phim Sắp Chiếu</a></li>
                        <li><a href="#" class="hover:text-primary transition duration-300">Khuyến Mãi</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-lg font-semibold text-white mb-4">HỖ TRỢ</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="#" class="hover:text-primary transition duration-300">Điều Khoản & Điều Kiện</a></li>
                        <li><a href="#" class="hover:text-primary transition duration-300">Chính Sách Bảo Mật</a></li>
                        <li><a href="#" class="hover:text-primary transition duration-300">Tuyển Dụng</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-lg font-semibold text-white mb-4">LIÊN HỆ</h4>
                    <p class="text-sm">Email: support@scarletcinema.vn</p>
                    <p class="text-sm">Hotline: 1900 6789</p>
                    <div class="flex space-x-3 mt-4">
                        <i data-lucide="facebook" class="h-6 w-6 hover:text-primary cursor-pointer transition duration-300"></i>
                        <i data-lucide="instagram" class="h-6 w-6 hover:text-primary cursor-pointer transition duration-300"></i>
                        <i data-lucide="youtube" class="h-6 w-6 hover:text-primary cursor-pointer transition duration-300"></i>
                    </div>
                </div>
            </div>
            
            <div class="mt-8 pt-6 border-t border-gray-700 text-center text-sm">
                &copy; 2025 Scarlet Cinema. All rights reserved.
            </div>
        </div>
  </footer>


<div id="trailerModal" class="fixed inset-0 bg-black bg-opacity-80 hidden z-50 items-center justify-center p-4" onclick="closeTrailerModal(event)">
    <div class="relative w-full max-w-4xl max-h-full">
        <div id="trailerContent" class="trailer-container rounded-lg overflow-hidden shadow-2xl">
            </div>
        
        <button onclick="closeTrailerModal()" class="absolute -top-10 right-0 text-white hover:text-primary transition">
            <i data-lucide="x" class="w-8 h-8"></i>
        </button>
    </div>
</div>    
<div id="detailsModal" class="fixed inset-0 bg-black bg-opacity-80 hidden z-50 items-center justify-center p-4" onclick="closeDetailsModal(event)">
    <div class="relative w-full max-w-6xl h-[80vh] bg-gray-800 rounded-lg shadow-2xl overflow-hidden flex flex-col md:flex-row">
        
        <div class="w-full md:w-1/3 flex-shrink-0">
            <img id="modalDetailsImage" src="" alt="Poster" class="w-full h-full object-cover">
        </div>

        <div class="w-full md:w-2/3 p-6 flex flex-col overflow-y-auto">
            <h2 id="modalDetailsTitle" class="text-3xl font-bold text-white mb-4">
                Tên Phim
            </h2>
            
            <div classid="modalDetailsStats" class="flex items-center gap-6 text-gray-300 mb-4 text-sm">
                <span id="modalDetailsRating" class="flex items-center gap-1">⭐ N/A</span>
                <span id="modalDetailsDuration" class="flex items-center gap-1">⏱️ N/A phút</span>
                <span id="modalDetailsRelease" class="flex items-center gap-1">📅 N/A</span>
            </div>

            <div class="flex gap-2 mb-6">
                <button class='flex-1 bg-primary text-black py-2 rounded font-semibold hover:bg-red-500 transition'>Đặt vé</button>
                <button class='flex-1 bg-gray-700 text-white py-2 rounded text-center hover:bg-gray-600 transition'>Trailer</button>
            </div>

            <h3 class="text-xl font-semibold text-white mb-2">Giới Thiệu</h3>
            <p id="modalDetailsDesc" class="text-gray-400 text-sm leading-relaxed">
                Mô tả phim...
            </p>
        </div>

        <button onclick="closeDetailsModal()" class="absolute top-4 right-4 text-white hover:text-primary transition z-10">
            <i data-lucide="x" class="w-6 h-6"></i>
        </button>
    </div>
</div>
<script src="../../asset/js/javascript.js"></script>

</body>
</html>