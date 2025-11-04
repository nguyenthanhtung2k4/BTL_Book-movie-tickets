// Tên file: asset/js/booking_client.js

// --- Global States ---
let selectedShowId = null;
let basePrice = 0;
// Lưu trữ ghế đã chọn: [{seat_name: 'A1', type: 'standard', price: 100000}, ...]
let selectedSeats = []; 
let currentSeatLayout = null; 
let currentBookedSeats = []; 


// --- Helper Functions ---

function formatCurrency(amount) {
    return Math.round(amount).toLocaleString('vi-VN') + ' VNĐ';
}

function calculateSeatPrice(seatType, basePrice) {
    const typeInfo = SEAT_TYPES[seatType];
    // Kiểm tra loại ghế có tồn tại và hệ số giá > 0
    if (typeInfo && typeInfo.price_modifier && typeInfo.price_modifier > 0) {
        return basePrice * typeInfo.price_modifier;
    }
    return 0; // Trả về 0 cho lối đi hoặc loại ghế không hợp lệ
}

function updateSummary() {
    let totalAmount = 0;
    let seatList = [];

    selectedSeats.forEach(seat => {
        totalAmount += seat.price;
        seatList.push(seat.seat_name);
    });

    document.getElementById('summary-seats-list').textContent = seatList.length > 0 ? seatList.join(', ') : 'Chưa chọn';
    document.getElementById('summary-total-amount').textContent = formatCurrency(totalAmount);
    
    const nextBtn = document.getElementById('next-to-checkout-btn');
    nextBtn.disabled = selectedSeats.length === 0;
}

/**
 * Lấy danh sách ghế đã đặt từ Server (API call thực tế)
 */
async function fetchBookedSeats(showId) {
    console.log(`[AJAX] Đang gọi API lấy ghế đã bán: ../../handle/get_sold_seats.php?show_id=${showId}`);
    try {
        // Đảm bảo đường dẫn chính xác
        const response = await fetch(`../../handle/get_sold_seats.php?show_id=${showId}`);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        const bookedSeats = await response.json();
        if (!Array.isArray(bookedSeats)) {
            console.error('[AJAX Error] Dữ liệu trả về không phải là một mảng JSON.');
            return [];
        }
        console.log(`[AJAX Success] Tải thành công ${bookedSeats.length} ghế đã bán.`);
        return bookedSeats;

    } catch (error) {
        console.error('[AJAX FAILED] Lỗi khi gọi API lấy ghế đã bán:', error);
        return []; 
    }
}


/**
 * Xử lý sự kiện click vào ghế để chọn/hủy chọn
 */
function toggleSeatSelection(event) {
    const seatElement = event.currentTarget;
    const seatName = seatElement.dataset.seatName;
    const seatType = seatElement.dataset.seatType;
    const seatPrice = parseFloat(seatElement.dataset.price);

    // Không cho phép chọn ghế đã bán hoặc lối đi
    if (currentBookedSeats.includes(seatName) || seatType === 'aisle' || seatPrice === 0) return; 
    
    const isSelected = seatElement.classList.contains('seat-selected');
    
    if (isSelected) {
        // Hủy chọn
        seatElement.classList.remove('seat-selected', 'border-primary', 'border-2');
        selectedSeats = selectedSeats.filter(seat => seat.seat_name !== seatName);
    } else {
        // Chọn
        seatElement.classList.add('seat-selected', 'border-primary', 'border-2');
        selectedSeats.push({
            seat_name: seatName, 
            type: seatType, // Lưu lại loại ghế để gửi lên server (dành cho logic sau này)
            price: seatPrice
        });
    }

    updateSummary();
}


/**
 * Vẽ sơ đồ ghế dựa trên layout JSON
 */
function renderSeatMap(layoutData, bookedSeats) {
    const seatMapDiv = document.getElementById('seat-map');
    seatMapDiv.innerHTML = ''; 
    
    // Reset trạng thái
    selectedSeats = []; 
    currentBookedSeats = bookedSeats; 

    // 1. Tìm số cột lớn nhất
    let maxCols = 0;
    if (layoutData && layoutData.layout_details) {
        layoutData.layout_details.forEach(detail => {
            if (detail.seat_data && detail.seat_data.length > maxCols) {
                maxCols = detail.seat_data.length;
            }
        });
    }


    if (maxCols === 0) {
         seatMapDiv.innerHTML = '<p class="text-gray-400 italic py-10">Phòng chiếu chưa được cấu hình sơ đồ ghế.</p>';
         return;
    }
    
    // 2. Thiết lập CSS Grid
    // 30px cho Row Label + repeat(số cột ghế, 30px) cho các ô ghế
    seatMapDiv.style.gridTemplateColumns = `30px repeat(${maxCols}, 30px)`;
    seatMapDiv.style.display = 'grid';
    seatMapDiv.style.gap = '6px';
    seatMapDiv.style.width = 'fit-content';
    seatMapDiv.style.margin = '0 auto';

    layoutData.layout_details.forEach(rowInfo => {
        
        // Nhãn hàng (Row Label)
        const rowLabel = document.createElement('div');
        rowLabel.className = 'seat-row-label font-bold text-gray-400 text-center text-sm h-6 leading-6';
        rowLabel.textContent = rowInfo.row;
        seatMapDiv.appendChild(rowLabel); 

        // Duyệt qua từng ô trong mảng seat_data
        rowInfo.seat_data.forEach((seatType, index) => {
            const seatWrapper = document.createElement('div');
            const seatNumber = index + 1;
            const seatName = rowInfo.row + seatNumber;
            const isBooked = bookedSeats.includes(seatName);
            const isAisle = seatType === 'aisle';
            
            // Wrapper cho từng ô trong Grid
            seatWrapper.className = 'flex items-center justify-center h-6'; 

            if (isAisle) {
                // Lối đi
                seatWrapper.className += ' bg-gray-900 opacity-50'; 
                seatWrapper.title = 'Lối đi';
                // Tạo một div trống để chiếm chỗ trong grid
                const aisleSpacer = document.createElement('div');
                aisleSpacer.className = 'w-full h-full';
                seatWrapper.appendChild(aisleSpacer);
                seatMapDiv.appendChild(seatWrapper);
            } else {
                // Ghế thật
                const typeInfo = SEAT_TYPES[seatType];
                const seatElement = document.createElement('div');
                const typeClass = typeInfo?.color || 'bg-gray-400';
                const seatPrice = calculateSeatPrice(seatType, basePrice);

                seatElement.dataset.seatName = seatName;
                seatElement.dataset.seatType = seatType;
                seatElement.dataset.price = seatPrice;
                seatElement.title = `${seatName} - ${typeInfo?.name || 'Không rõ'} (${formatCurrency(seatPrice)})`;

                let classes = 'w-6 h-6 rounded-sm flex items-center justify-center text-xs font-bold transition duration-150';

                if (isBooked) {
                    // Ghế đã bán
                    classes += ' bg-gray-500 text-gray-200 cursor-not-allowed';
                    seatElement.style.pointerEvents = 'none';
                    seatElement.title = `${seatName} - Đã bán`;
                } else {
                    // Ghế có thể chọn
                    classes += ` ${typeClass} text-black cursor-pointer hover:ring-2 hover:ring-white`;
                    seatElement.classList.add('available-seat');
                    seatElement.addEventListener('click', toggleSeatSelection);
                }
                
                seatElement.className = classes;
                seatElement.textContent = seatNumber;
                
                seatWrapper.appendChild(seatElement);
                seatMapDiv.appendChild(seatWrapper);
            }
        });
    });
    updateSummary();
}

/**
 * Lọc và hiển thị các suất chiếu dựa trên ngày được chọn. (Sửa lỗi hiển thị)
 */
function filterShowtimes(selectedDate) {
    const showtimeButtons = document.querySelectorAll('.showtime-btn');
    
    // Ẩn tất cả suất chiếu
    showtimeButtons.forEach(btn => {
        btn.style.display = 'none';
    });

    // Hiện thị suất chiếu phù hợp với ngày
    showtimeButtons.forEach(btn => {
        // Kiểm tra data-date khớp với ngày được chọn
        if (btn.dataset.date === selectedDate) {
            btn.style.display = 'inline-flex'; // Sử dụng inline-flex để nút hiển thị đúng định dạng
        }
    });

    // Ẩn sơ đồ ghế và thông tin tóm tắt khi đổi ngày
    document.getElementById('step-seat').classList.add('hidden');
    document.getElementById('step-checkout').classList.add('hidden');
}

// --- Event Listeners ---

// 1. Lắng nghe sự kiện click vào nút lọc ngày
document.querySelectorAll('.date-filter-btn').forEach(button => {
    button.addEventListener('click', function() {
        // Highlight nút ngày
        document.querySelectorAll('.date-filter-btn').forEach(btn => {
            btn.classList.remove('bg-primary', 'text-black');
            btn.classList.add('bg-gray-700', 'text-white', 'hover:bg-gray-600');
        });
        this.classList.add('bg-primary', 'text-black');
        this.classList.remove('bg-gray-700', 'text-white', 'hover:bg-gray-600');
        
        const selectedDate = this.dataset.date;
        filterShowtimes(selectedDate);
    });
});


// 2. Chọn Suất Chiếu
document.querySelectorAll('.showtime-btn').forEach(button => {
    button.addEventListener('click', async function() {
        // Bỏ highlight tất cả suất chiếu
        document.querySelectorAll('.showtime-btn').forEach(btn => {
            btn.classList.remove('bg-red-500', 'scale-105');
            btn.classList.add('bg-gray-700', 'text-white', 'hover:bg-red-500', 'hover:scale-105');
        });
        // Highlight suất chiếu được chọn
        this.classList.add('bg-red-500', 'scale-105');
        this.classList.remove('bg-gray-700', 'text-white', 'hover:bg-red-500', 'hover:scale-105');

        // Lấy dữ liệu
        selectedShowId = this.dataset.showId;
        basePrice = parseFloat(this.dataset.price);
        const layoutJson = this.dataset.layout;
        
        // Lấy thông tin hiển thị
        const theaterName = this.closest('.theater-block').querySelector('h3').textContent;
        const screenNameMatch = this.title.match(/Phòng: (.*?) \|/);
        const screenName = screenNameMatch ? screenNameMatch[1].trim() : 'N/A';
        const showTime = this.textContent.trim().split(' ')[0];
        const showDate = this.dataset.date;
        const formattedDate = new Date(showDate + 'T00:00:00').toLocaleDateString('vi-VN', { 
            weekday: 'long', day: '2-digit', month: '2-digit' 
        });

        document.getElementById('selected-show-info').innerHTML = `
            <p>Rạp: <strong>${theaterName}</strong></p>
            <p>Phòng: <strong>${screenName}</strong> | Giờ: <strong>${showTime}</strong> | Ngày: <strong>${formattedDate}</strong></p>
            <p>Giá cơ bản: <strong>${formatCurrency(basePrice)}</strong></p>
        `;
        
        // Hiển thị phần chọn ghế
        document.getElementById('step-seat').classList.remove('hidden');
        document.getElementById('step-checkout').classList.add('hidden'); // Ẩn checkout
        document.getElementById('seat-map').innerHTML = '<p class="text-primary italic py-10">Đang tải sơ đồ ghế...</p>';
        window.scrollTo({ top: document.getElementById('step-seat').offsetTop, behavior: 'smooth' });

        try {
            currentSeatLayout = JSON.parse(layoutJson);
            
            // Lấy danh sách ghế đã bán
            const bookedSeats = await fetchBookedSeats(selectedShowId);
            
            // Vẽ sơ đồ ghế
            renderSeatMap(currentSeatLayout, bookedSeats);
            
        } catch (error) {
            console.error('Lỗi khi phân tích hoặc tải sơ đồ ghế:', error);
            document.getElementById('seat-map').innerHTML = '<p class="text-red-400 italic py-10">Lỗi: Không thể tải sơ đồ ghế. (Vui lòng kiểm tra Console)</p>';
        }
    });
});

// 3. Chuyển sang thanh toán
document.getElementById('next-to-checkout-btn').addEventListener('click', function() {
    if (selectedSeats.length === 0) return;

    const totalAmount = selectedSeats.reduce((sum, seat) => sum + seat.price, 0);
    // CHỈ GỬI TÊN GHẾ LÊN SERVER: ["A1", "A2", "C3"]
    const seatsToSubmitNames = selectedSeats.map(s => s.seat_name); 

    document.getElementById('checkout-show-id').value = selectedShowId;
    document.getElementById('checkout-selected-seats').value = JSON.stringify(seatsToSubmitNames); 
    document.getElementById('checkout-total-amount-input').value = totalAmount;
    document.getElementById('checkout-total-amount').textContent = formatCurrency(totalAmount);
    
    document.getElementById('step-seat').classList.add('hidden');
    document.getElementById('step-checkout').classList.remove('hidden');
    window.scrollTo({ top: document.getElementById('step-checkout').offsetTop, behavior: 'smooth' });
});

// 4. Quay lại chọn ghế
document.getElementById('back-to-seat-btn').addEventListener('click', function() {
    document.getElementById('step-checkout').classList.add('hidden');
    document.getElementById('step-seat').classList.remove('hidden');
    window.scrollTo({ top: document.getElementById('step-seat').offsetTop, behavior: 'smooth' });
});

// 5. Khởi tạo: Kích hoạt nút ngày đầu tiên khi DOM sẵn sàng
document.addEventListener('DOMContentLoaded', () => {
    // Kích hoạt logic chọn ngày đầu tiên khi trang tải xong
    const firstDateButton = document.querySelector('.date-filter-btn');
    if(firstDateButton) {
        firstDateButton.click(); // Gọi hàm click để hiển thị suất chiếu của ngày đầu tiên
    }
    updateSummary();
});