<?php
$adminName = "Admin Scarlet";
$title = "Chỉnh Sửa Suất Chiếu";
$pageName = "Cập Nhật Suất Chiếu";

// Khởi động session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../../function/reponsitory.php";
require_once __DIR__ . "/side_bar.php"; 

$showRepo = new Repository('shows');
$movieRepo = new Repository('movies');
$screenRepo = new Repository('screens');
$theaterRepo = new Repository('theaters');

$show_id = $_GET['id'] ?? null;

if (!$show_id || !($show = $showRepo->find($show_id))) {
    $_SESSION['flash_message'] = "Suất chiếu không tồn tại hoặc thiếu ID.";
    $_SESSION['flash_success'] = false;
    header("Location: shows.php");
    exit;
}


$movies = $movieRepo->getAll();
$screens = $screenRepo->getAll();
$theaters = $theaterRepo->getAll();


$theaterNames = [];
foreach ($theaters as $theater) {
    $theaterNames[$theater['id']] = $theater['name'];
}

$movieDurations = [];
foreach ($movies as $movie) {
    $movieDurations[$movie['id']] = (int)($movie['duration_minutes'] ?? 90);
}

$flash_message = $_SESSION['flash_message'] ?? '';
$flash_success = $_SESSION['flash_success'] ?? false;
unset($_SESSION['flash_message'], $_SESSION['flash_success']);

$handleURL = "../../handle/shows_handle.php";

$formats = ['2D', '3D', 'IMAX'];
$statuses = ['active' => 'Đang chiếu', 'upcoming' => 'Sắp chiếu', 'canceled' => 'Đã hủy'];

// Input datetime-local cần định dạng YYYY-MM-DDThh:mm
$currentShowTime = date('Y-m-d\TH:i', strtotime($show['show_time']));

?>

<style>
    /* Dark Theme Setup for the form */
    body { font-family: 'Inter', sans-serif; background-color: #1f2937; color: #f3f4f6; }
    .form-input, .form-select {
        width: 100%; 
        padding: 0.75rem 1rem; 
        background-color: #374151; 
        border: 1px solid #4b5563; 
        border-radius: 0.5rem; 
        color: #f3f4f6; 
        transition: all 0.15s ease-in-out;
    }
    .form-label {
        display: block; 
        font-size: 0.875rem; 
        font-weight: 500; 
        color: #d1d5db; 
        margin-bottom: 0.5rem;
    }
</style>

<main class="flex-1 p-8 sm:p-10 min-h-screen bg-gray-900">

    <div class="max-w-4xl mx-auto">
        <h2 class="text-3xl font-bold text-red-500 mb-8 border-b border-gray-700 pb-4"><?= $pageName ?>: ID #<?= (int)$show_id ?></h2>

        <?php if ($flash_message): ?>
            <div id='flash-message' class="fixed top-6 right-6 z-50 px-6 py-3 rounded-lg shadow-lg text-white font-semibold transition-transform duration-300
                 <?= $flash_success ? 'bg-green-500' : 'bg-red-600' ?>">
                <?= htmlspecialchars($flash_message) ?>
            </div>
        <?php endif; ?>

        <?php if (empty($movies) || empty($screens)): ?>
            <div class="bg-yellow-800/50 border border-yellow-700 p-6 rounded-xl text-yellow-300 text-lg">
                <p class="font-bold mb-2">⚠️ Thiếu dữ liệu cơ sở:</p>
                <p>Không thể chỉnh sửa nếu thiếu dữ liệu Phim hoặc Phòng Chiếu.</p>
            </div>
        <?php else: ?>
        
            <form action="<?= $handleURL ?>?action=edit" method="POST" class="bg-gray-800 p-8 rounded-xl shadow-2xl space-y-6">
                <input type="hidden" name="id" value="<?= (int)$show_id ?>">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="movie_id" class="form-label">Phim (<span class="text-red-500">*</span>)</label>
                        <select id="movie_id" name="movie_id" required class="form-select">
                            <?php foreach ($movies as $movie): ?>
                                <?php
                                    $selected = ($movie['id'] == $show['movie_id']) ? 'selected' : '';
                                ?>
                                <option value="<?= htmlspecialchars($movie['id']) ?>" 
                                        data-duration="<?= (int)($movie['duration_minutes'] ?? 90) ?>"
                                        <?= $selected ?>>
                                    <?= htmlspecialchars($movie['title']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p id="movie_duration_info" class="text-sm text-gray-400 mt-2 font-semibold"></p>
                    </div>

                    <div>
                        <label for="screen_id" class="form-label">Phòng Chiếu (<span class="text-red-500">*</span>)</label>
                        <select id="screen_id" name="screen_id" required class="form-select">
                            <?php foreach ($screens as $screen): ?>
                                <?php
                                    $theaterName = $theaterNames[$screen['theater_id']] ?? 'Rạp không rõ';
                                    $screenDisplay = htmlspecialchars($theaterName) . ' - Phòng ' . htmlspecialchars($screen['name']) . ' (' . htmlspecialchars($screen['screen_type'] ?? '2D') . ')';
                                    $selected = ($screen['id'] == $show['screen_id']) ? 'selected' : '';
                                ?>
                                <option value="<?= htmlspecialchars($screen['id']) ?>" <?= $selected ?>>
                                    <?= $screenDisplay ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="text-xs text-gray-400 mt-1">Chọn phòng chiếu có **Loại phòng** phù hợp với **Định dạng** phim.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="show_time" class="form-label">Thời Gian Chiếu (<span class="text-red-500">*</span>)</label>
                        <input type="datetime-local" id="show_time" name="show_time" 
                               min="<?= date('Y-m-d\TH:i') ?>" required class="form-input"
                               value="<?= $currentShowTime ?>">
                    </div>

                    <div>
                        <label for="format" class="form-label">Định Dạng (<span class="text-red-500">*</span>)</label>
                        <select id="format" name="format" required class="form-select">
                            <?php foreach ($formats as $format): ?>
                                <?php
                                    $selected = ($format == $show['format']) ? 'selected' : '';
                                ?>
                                <option value="<?= htmlspecialchars($format) ?>" <?= $selected ?>>
                                    <?= htmlspecialchars($format) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="price" class="form-label">Giá Vé Cơ Bản (<span class="text-red-500">*</span>)</label>
                        <input type="number" id="price" name="price" min="1000" step="1000" required placeholder="Ví dụ: 80000" class="form-input"
                               value="<?= htmlspecialchars($show['price']) ?>">
                    </div>

                    <div>
                        <label for="status" class="form-label">Trạng Thái Suất Chiếu (<span class="text-red-500">*</span>)</label>
                        <select id="status" name="status" required class="form-select">
                            <?php foreach ($statuses as $value => $label): ?>
                                <?php
                                    $selected = ($value == $show['status']) ? 'selected' : '';
                                ?>
                                <option value="<?= htmlspecialchars($value) ?>" <?= $selected ?>>
                                    <?= htmlspecialchars($label) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row justify-end gap-4 pt-4 border-t border-gray-700">
                    <a href="shows.php" class="w-full sm:w-auto text-center bg-gray-600 hover:bg-gray-500 text-white font-medium px-6 py-3 rounded-lg transition duration-200">
                        Hủy & Quay lại
                    </a>
                    <button type="submit"
                            class="w-full sm:w-auto bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-600 text-white font-bold px-6 py-3 rounded-lg shadow-lg transition duration-300 transform hover:scale-[1.02]">
                        Cập Nhật Suất Chiếu
                    </button>
                </div>

            </form>

        <?php endif; ?>
    </div>
</main>
<script>
    // Script ẩn thông báo flash message sau 3 giây
    setTimeout(() => {
        const flash = document.getElementById('flash-message');
        if (flash) {
            flash.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            flash.style.opacity = '0';
            flash.style.transform = 'translateY(-10px)';
            setTimeout(() => flash.remove(), 500);
        }
    }, 3000); 

    // --- LOGIC HIỂN THỊ THỜI LƯỢNG PHIM ---
    const movieIdSelect = document.getElementById('movie_id');
    const durationInfo = document.getElementById('movie_duration_info');

    // Hàm hiển thị thời lượng
    function updateDurationInfo() {
        const selectedOption = movieIdSelect.options[movieIdSelect.selectedIndex];
        
        if (selectedOption && selectedOption.value) {
            const duration = selectedOption.dataset.duration; // Lấy từ data attribute
            if (duration) {
                const hours = Math.floor(duration / 60);
                const minutes = duration % 60;
                
                let displayTime = '';
                if (hours > 0) {
                    displayTime += hours + ' giờ ';
                }
                displayTime += minutes + ' phút';

                durationInfo.innerHTML = `Thời lượng phim: ⏳ **${displayTime}** (${duration} phút)`;
            } else {
                durationInfo.innerHTML = 'Thời lượng phim không xác định.';
            }
        } else {
            durationInfo.innerHTML = '';
        }
    }

    movieIdSelect.addEventListener('change', updateDurationInfo);

    updateDurationInfo(); 

</script>
</body>
</html>