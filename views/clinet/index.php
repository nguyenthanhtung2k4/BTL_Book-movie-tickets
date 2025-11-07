<?php
session_start();

$siteTitle = 'Home | SCARLET CINEMA';

require_once __DIR__ . "/../../function/reponsitory.php";
require_once __DIR__ . "/../../function/auth_helper.php";
require_once __DIR__ . "/header.php";

$repo = new Repository('movies');
$data = $repo->getAll();
$today = date('Y-m-d');
$siteTitle = "PHÒNG VÉ SCARLET | Trải Nghiệm Điện Ảnh Tối Thượng";

$flash_message = $_SESSION['flash_message'] ?? '';
$flash_success = $_SESSION['flash_success'] ?? false;

unset($_SESSION['flash_message'], $_SESSION['flash_success']);

?>



<?php if ($flash_message): ?>
    <div id="flash-message" class="fixed top-6 right-6 z-50 px-6 py-3 rounded-lg shadow-xl text-white font-semibold 
            <?= $flash_success ? 'bg-green-600 border border-green-400' : 'bg-red-700 border border-red-400' ?>">
        <?= ($flash_message) ?>
    </div>
<?php endif; ?>

<main class="max-w-7xl mx-auto p-6 space-y-20">

    <section id="featured" class="text-center relative overflow-hidden">
        <h1 class="text-5xl font-extrabold text-white mb-10">
            Trải Nghiệm <span class="text-primary">Điện Ảnh Tối Thượng</span>
        </h1>

        <div id="carousel" class="relative overflow-x-hidden overflow-y-visible max-w-6xl mx-auto py-4">
            <div id="slides" class="flex transition-transform duration-[1200ms] ease-in-out">
                <?php foreach ($data as $index => $movie): ?>
                    <div class="w-1/3 flex-shrink-0 p-2 relative z-0 
            transition-all duration-300 ease-in-out 
            hover:scale-110 hover:z-40 hover:-translate-y-2">
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
            $currentMovies = array_filter($data, function ($m) use ($today) {
                return (!empty($m['release_date']) && $m['release_date'] <= $today);
            });
            usort($currentMovies, function ($a, $b) {
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

                    <div class='bg-gray-800 rounded-xl overflow-hidden shadow-lg 
            transition-transform duration-300 ease-in-out 
            hover:-translate-y-2 hover:scale-105'>
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
                                <a href="booking.php?movie_id=<?= htmlspecialchars($movie['id']) ?>"
                                    class='flex-1 bg-primary text-black py-2 rounded font-semibold hover:bg-red-500 transition text-center'>Đặt
                                    vé</a>

                                <?php if ($trailer_url !== '#'): ?>
                                    <button data-trailer-url="<?= $trailer_url ?>"
                                        class='open-trailer-modal flex-1 bg-gray-700 text-white py-2 rounded text-center hover:bg-gray-600 transition'>
                                        Trailer
                                    </button>
                                <?php else: ?>
                                    <button class='flex-1 bg-gray-600 text-white py-2 rounded opacity-60 cursor-not-allowed'
                                        disabled>Trailer</button>
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
            $upcomingMovies = array_filter($data, function ($m) use ($today) {
                return (!empty($m['release_date']) && $m['release_date'] > $today);
            });
            usort($upcomingMovies, function ($a, $b) {
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

                    <div class='bg-gray-800 rounded-xl overflow-hidden shadow-lg 
            transition-transform duration-300 ease-in-out 
            hover:-translate-y-2 hover:scale-105'>
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
                                <a href="booking.php?movie_id=<?= htmlspecialchars($movie['id']) ?>"
                                    class='flex-1 bg-primary text-black py-2 rounded font-semibold hover:bg-red-500 transition text-center'>Sắp khởi chiếu</a>
                                <?php if ($trailer_url !== '#'): ?>
                                    <button data-trailer-url="<?= $trailer_url ?>"
                                        class='open-trailer-modal flex-1 bg-gray-700 text-white py-2 rounded text-center hover:bg-gray-600 transition'>
                                        Trailer
                                    </button>
                                <?php else: ?>
                                    <button class='flex-1 bg-gray-600 text-white py-2 rounded opacity-60 cursor-not-allowed'
                                        disabled>Trailer</button>
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

<div id="trailerModal" class="fixed inset-0 bg-black bg-opacity-80 hidden z-50 items-center justify-center p-4"
    onclick="closeTrailerModal(event)">
    <div class="relative w-full max-w-4xl max-h-full">
        <div id="trailerContent" class="trailer-container rounded-lg overflow-hidden shadow-2xl">
        </div>

        <button onclick="closeTrailerModal()" class="absolute -top-10 right-0 text-white hover:text-primary transition">
            <i data-lucide="x" class="w-8 h-8"></i>
        </button>
    </div>
</div>    

<div id="detailsModal" class="fixed inset-0 bg-black bg-opacity-80 hidden z-50 items-center justify-center p-4"
    onclick="closeDetailsModal(event)">
    <div
        class="relative w-full max-w-6xl h-[80vh] bg-gray-800 rounded-lg shadow-2xl overflow-hidden flex flex-col md:flex-row">

        <div class="w-full md:w-1/3 flex-shrink-0">
            <img id="modalDetailsImage" src="" alt="Poster" class="w-full h-full object-cover">
        </div>

        <div class="w-full md:w-2/3 p-6 flex flex-col overflow-y-auto">
            <h2 id="modalDetailsTitle" class="text-3xl font-bold text-white mb-4">
                Tên Phim
            </h2>

            <div id="modalDetailsStats" class="flex items-center gap-6 text-gray-300 mb-4 text-sm">
                <span id="modalDetailsRating" class="flex items-center gap-1">⭐ N/A</span>
                <span id="modalDetailsDuration" class="flex items-center gap-1">⏱️ N/A phút</span>
                <span id="modalDetailsRelease" class="flex items-center gap-1">📅 N/A</span>
            </div>

            <div class="flex gap-2 mb-6">
                <a id="modalDetailsBookingBtn" href="#"
                    class='flex-1 bg-primary text-black py-2 rounded font-semibold hover:bg-red-500 transition text-center'>Đặt
                    vé</a>
                <button id="modalDetailsTrailerBtn"
                    class='flex-1 bg-gray-700 text-white py-2 rounded text-center hover:bg-gray-600 transition'>Trailer</button>
            </div>

            <h3 class="text-xl font-semibold text-white mb-2">Giới Thiệu</h3>
            <p id="modalDetailsDesc" class="text-gray-400 text-sm leading-relaxed">
                Mô tả phim...
            </p>
        </div>

        <button onclick="closeDetailsModal()"
            class="absolute top-4 right-4 text-white hover:text-primary transition z-10">
            <i data-lucide="x" class="w-6 h-6"></i>
        </button>
    </div>
</div>
<script src="../../asset/js/javascript.js"></script>

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

</body>

</html>

<?= require_once __DIR__ . '../footer.php';