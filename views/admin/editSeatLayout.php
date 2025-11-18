<?php
$adminName = "Admin Scarlet";
$title = "Chỉnh Sửa Sơ Đồ Ghế";
$pageName = "Sơ Đồ Ghế Phòng Chiếu";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../../function/reponsitory.php";
require_once __DIR__ . "/side_bar.php"; // Giả định side_bar.php chứa phần mở đầu HTML (<body>)

$screenRepo = new Repository('screens');
$theaterRepo = new Repository('theaters');
$seatRepo = new Repository('seats');
$seatTypeRepo = new Repository('seat_types');

$screen_id = $_GET['id'] ?? null;

// 1. Kiểm tra ID và lấy dữ liệu phòng chiếu
if (!$screen_id || !($screen = $screenRepo->find($screen_id))) {
    $_SESSION['flash_error'] = "Phòng chiếu không tồn tại hoặc thiếu ID.";
    header("Location: index.php");
    exit;
}

$theater_name = $theaterRepo->find($screen['theater_id'])['name'] ?? 'Rạp không xác định';

// Lấy tất cả ghế của phòng chiếu này
$seats = $seatRepo->getByCondition(
    "screen_id = :screen_id",
    ['screen_id' => $screen_id],
    "*",
    "row_letter ASC, seat_number ASC"
);
$seatTypes = $seatTypeRepo->getAll();
$seatTypeCodeMap = [];
foreach ($seatTypes as $st) {
    $seatTypeCodeMap[$st['id']] = $st['code'];
}

// Chuyển đổi dữ liệu ghế thành layout_details
$layout_details = [];
if ($seats) {
    // Gom nhóm theo row_letter
    $rows = [];
    foreach ($seats as $seat) {
        $row = $seat['row_letter'];
        if (!isset($rows[$row])) {
            $rows[$row] = [];
        }
        $rows[$row][$seat['position_order'] - 1] = $seatTypeCodeMap[$seat['seat_type_id']] ?? 'standard';
    }
    // Sắp xếp theo row (A, B, C...)
    ksort($rows);
    foreach ($rows as $row_letter => $seat_data) {
        // Sắp xếp theo vị trí và re-index để JSON là mảng (không phải object)
        ksort($seat_data);
        $normalized = array_values($seat_data);

        $layout_details[] = [
            'row' => $row_letter,
            'seats' => count($normalized),
            'seat_data' => $normalized
        ];
    }
}

$initial_layout = [
    "rows_count" => count($layout_details),
    "total_capacity" => $screen['capacity'],
    "layout_details" => $layout_details
];

$handleURL = "../../handle/screens_handle.php";
?>

<style>
    /* CSS & UI/UX Cải tiến */
    body { font-family: 'Inter', sans-serif; background-color: #1f2937; color: #f3f4f6; }
    
    .seat-grid {
        display: grid;
        /* auto (Row Label) + repeat(Số cột JS tính toán, kích thước cột) */
        grid-template-columns: 40px repeat(var(--cols), minmax(30px, 1fr)); 
        gap: 6px; 
    }
    .seat-wrapper {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        height: 30px;
    }
    .seat {
        width: 30px;
        height: 30px;
        border-radius: 4px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 10px;
        font-weight: bold;
        transition: transform 0.2s, background-color 0.2s;
        border: 1px solid transparent;
    }
    /* Màu ghế */
    .seat.standard { background-color: #3b82f6; border-color: #1e40af; } 
    .seat.vip { background-color: #f59e0b; border-color: #92400e; color: #fff; }      
    .seat.disabled { background-color: #10b981; border-color: #065f46; } 
    
    .seat:hover { transform: scale(1.1); box-shadow: 0 0 8px rgba(255, 255, 255, 0.3); }
    
    /* Thể hiện Lối đi (aisle) bằng khoảng trống */
    .seat-wrapper.aisle {
        background-color: transparent; 
        pointer-events: none; /* Không thể tương tác */
        opacity: 0.5;
    }
    .seat-wrapper.aisle::after {
        content: ' ';
        display: block;
        width: 100%;
        height: 10px;
        background-color: #27272a; /* Màu nền lối đi */
    }
    
    .row-label { 
        font-weight: bold; 
        text-align: center; 
        color: #9ca3af; 
        line-height: 30px;
        cursor: pointer;
    }
    .screen-projection {
        background-color: #374151;
        color: #f3ff00;
        text-align: center;
        padding: 10px;
        margin-bottom: 20px;
        border-radius: 6px;
        border-bottom: 4px solid #ef4444;
        font-weight: 600;
        letter-spacing: 2px;
    }
</style>


<main class="flex-1 p-8 sm:p-10 min-h-screen">

    <h2 class="text-3xl font-bold text-red-500 mb-2"><?= $pageName ?></h2>
    <h3 class="text-xl text-gray-400 mb-6">
        <?= htmlspecialchars($theater_name) ?> - Phòng: **<?= htmlspecialchars($screen['name']) ?>**
    </h3>
    
    <?php if (isset($_SESSION['flash_error'])): ?>
        <div class="bg-red-900 border border-red-500 text-red-100 px-4 py-3 rounded mb-4" role="alert">
            <p><?= htmlspecialchars($_SESSION['flash_error']) ?></p>
        </div>
        <?php unset($_SESSION['flash_error']); ?>
    <?php endif; ?>

    <div class="bg-gray-800 rounded-xl shadow-2xl p-6 sm:p-8">
        
        <form id="seatLayoutForm" action="<?= $handleURL ?>" method="POST">
            <input type="hidden" name="action" value="update_layout">
            <input type="hidden" name="id" value="<?= (int)$screen_id ?>">
            <input type="hidden" name="seat_layout_json" id="seat_layout_json">
            <input type="hidden" name="new_capacity" id="new_capacity">

            <div class="mb-6 flex flex-wrap space-x-4 text-sm items-center">
    
    <span class="p-2 rounded-lg bg-blue-600 text-white font-semibold shadow-md">Ghế thường</span>
    
    <span id="vip" class="p-2 rounded-lg bg-yellow-600 text-white font-semibold shadow-md">VIP</span>
    
    <span class="p-2 rounded-lg bg-green-600 text-white font-semibold shadow-md">Tàn tật</span>
    
    <span class="p-2 px-4 rounded-lg bg-gray-600 text-gray-300 font-semibold shadow-md">Lối đi</span>
    <br><br><hr>
    <div class="ml-auto flex space-x-2">
        <button type="button" id="addColumnButton" class="bg-indigo-600 hover:bg-indigo-700 px-3 py-1 rounded-lg text-white font-semibold transition">
            Thêm Cột Ghế/Lối đi
        </button>
        <button type="button" id="addRowButton" class="bg-indigo-600 hover:bg-indigo-700 px-3 py-1 rounded-lg text-white font-semibold transition">
            Thêm Hàng Ghế
        </button>
    </div>
</div>
            <div class="screen-projection">MÀN HÌNH CHIẾU</div>

            <div id="seat-map-container" class="overflow-x-auto p-4">
                <div id="seat-grid" class="seat-grid">
                    </div>
            </div>

            <div class="mt-8 pt-4 border-t border-gray-700 flex justify-between items-center">
                <p class="text-lg text-yellow-400">Sức chứa hiện tại: <span id="current-capacity-display" class="font-bold">0</span></p>
                <button type="submit" class="bg-red-600 hover:bg-red-700 px-6 py-3 rounded-lg text-white font-semibold transition shadow-md">
                    💾 Lưu Sơ Đồ Ghế
                </button>
            </div>
        </form>
    </div>
</main>

<script>
    // Hàm utility để lấy tên hàng (A, B, C...)
    function chr(code) {
        return String.fromCharCode(code);
    }
    
    // Dữ liệu ban đầu
    const initialLayout = <?= json_encode($initial_layout) ?>;
    const seatGrid = document.getElementById('seat-grid');
    const capacityDisplay = document.getElementById('current-capacity-display');
    const seatLayoutInput = document.getElementById('seat_layout_json');
    const newCapacityInput = document.getElementById('new_capacity');
    
    // Các loại ghế có thể tương tác (ghế thật)
    const INTERACTIVE_TYPES = ['standard', 'vip', 'disabled']; 
    // Ghế và Lối đi
    const SEAT_TYPES = ['standard', 'vip', 'disabled', 'aisle'];

    let currentLayout = initialLayout;
    
    /**
     * Chuyển đổi loại ghế khi nhấp chuột (Chỉ áp dụng cho ghế thật)
     * @param {HTMLElement} element - Phần tử ghế được nhấp
     * @param {number} rowIndex - Chỉ số hàng trong currentLayout
     * @param {number} colIndex - Chỉ số cột trong seat_data
     */
    function toggleSeatType(element, rowIndex, colIndex) {
        const rowDetail = currentLayout.layout_details[rowIndex];
        if (!rowDetail || !rowDetail.seat_data) return;

        const currentType = rowDetail.seat_data[colIndex];
        const index = INTERACTIVE_TYPES.indexOf(currentType);
        
        // Nếu hiện tại là ghế thật, tìm loại ghế thật tiếp theo
        let nextIndex = (index + 1) % INTERACTIVE_TYPES.length;
        let newType = INTERACTIVE_TYPES[nextIndex];

        // Cập nhật mảng dữ liệu JS
        rowDetail.seat_data[colIndex] = newType;

        // Cập nhật giao diện
        element.classList.remove(currentType);
        element.classList.add(newType);
        element.title = 'Click để đổi sang: ' + INTERACTIVE_TYPES[(nextIndex + 1) % INTERACTIVE_TYPES.length].toUpperCase();

        updateSummary();
    }

    /**
     * Tính toán lại tổng số ghế và cập nhật JSON ẩn
     */
    function updateSummary() {
        let newTotalCapacity = 0;
        
        currentLayout.layout_details.forEach(detail => {
            if (detail.seat_data) {
                // Đếm số ghế không phải lối đi
                const rowSeatsCount = detail.seat_data.filter(type => type !== 'aisle').length;
                newTotalCapacity += rowSeatsCount;
                detail.seats = rowSeatsCount; // Cập nhật lại số ghế thực tế trong mảng
            }
        });

        currentLayout.total_capacity = newTotalCapacity;
        
        capacityDisplay.textContent = newTotalCapacity.toLocaleString();
        seatLayoutInput.value = JSON.stringify(currentLayout);
        newCapacityInput.value = newTotalCapacity;
    }

    /**
     * Vẽ sơ đồ ghế lên giao diện
     */
    function renderSeatMap() {
        seatGrid.innerHTML = '';
        
        // 1. Tìm số cột lớn nhất
        let maxCols = 0;
        currentLayout.layout_details.forEach(detail => {
            const numCols = detail.seat_data ? detail.seat_data.length : detail.seats; 
            if (numCols > maxCols) maxCols = numCols;
        });
        
        // 2. Thiết lập biến CSS cho số cột
        seatGrid.style.setProperty('--cols', Math.max(maxCols, 10)); // Ít nhất 10 cột

        currentLayout.layout_details.forEach((rowDetail, rowIndex) => {
            const rowName = rowDetail.row;
            
            // Nếu seat_data chưa tồn tại (từ dữ liệu cũ), tạo mảng chi tiết
            if (!rowDetail.seat_data) {
                rowDetail.seat_data = Array(rowDetail.seats).fill(rowDetail.type || 'standard');
            }
            
            // 3. Thêm Tên hàng (Row Label)
            let rowLabel = document.createElement('div');
            rowLabel.classList.add('row-label');
            rowLabel.textContent = rowName;
            
            // Xóa hàng khi click vào tên hàng
            rowLabel.addEventListener('click', () => {
                if (confirm(`Bạn có chắc chắn muốn xóa toàn bộ hàng ${rowName}?`)) {
                    currentLayout.layout_details.splice(rowIndex, 1);
                    renderSeatMap();
                }
            });
            seatGrid.appendChild(rowLabel);

            // 4. Duyệt qua từng ô trong hàng (tới maxCols)
            for (let i = 0; i < maxCols; i++) {
                
                let seatWrapper = document.createElement('div');
                seatWrapper.classList.add('seat-wrapper');

                // Lấy loại ghế, mặc định là lối đi nếu vị trí i ngoài phạm vi seat_data
                let seatType = 'aisle'; 
                if (i < rowDetail.seat_data.length) {
                    seatType = rowDetail.seat_data[i];
                }

                seatWrapper.dataset.row = rowName;
                seatWrapper.dataset.col = i;
                seatWrapper.classList.add(seatType);
                
                // 5. Nếu là GHẾ THẬT, vẽ phần tử ghế và gán sự kiện
                if (seatType !== 'aisle') {
                    let seatElement = document.createElement('div');
                    seatElement.classList.add('seat', seatType);
                    seatElement.dataset.type = seatType;
                    seatElement.textContent = i + 1; 

                    seatElement.addEventListener('click', () => toggleSeatType(seatElement, rowIndex, i));
                    seatWrapper.appendChild(seatElement);
                }
                
                seatGrid.appendChild(seatWrapper);
            }
        });
        
        updateSummary(); 
    }
    
    // --- XỬ LÝ SỰ KIỆN NÚT ---
    
    document.getElementById('addRowButton').addEventListener('click', () => {
        const lastRowIndex = currentLayout.layout_details.length;
        const newRowName = chr(65 + lastRowIndex); 
        const maxCols = parseInt(seatGrid.style.getPropertyValue('--cols'));
        
        // Tạo hàng mới với số cột tối đa hiện tại, mặc định là standard
        let seat_data = Array(maxCols).fill('standard');

        currentLayout.layout_details.push({
            row: newRowName,
            seats: maxCols,
            seat_data: seat_data 
        });
        
        renderSeatMap();
    });

    document.getElementById('addColumnButton').addEventListener('click', () => {
        const choice = confirm("Chọn OK để thêm một cột ghế Standard mới. Chọn HỦY để thêm một cột Lối đi (Aisle).");
        const newColType = choice ? 'standard' : 'aisle';

        // Thêm một vị trí mới vào cuối mỗi hàng
        currentLayout.layout_details.forEach(detail => {
            if (!detail.seat_data) {
                 // Trường hợp đặc biệt, nếu chưa có seat_data, phải tạo nó trước
                 detail.seat_data = Array(detail.seats).fill('standard');
            }
            detail.seat_data.push(newColType); 
        });
        
        renderSeatMap();
    });


    // Khởi tạo sơ đồ
    renderSeatMap();
</script>
</body>
</html>
