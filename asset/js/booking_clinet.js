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
 * Lấy danh sách ghế từ API
 */
async function fetchSeatsByScreen(screenId) {
    try {
        const response = await fetch(`../../handle/get_seats_by_screen.php?screen_id=${screenId}`);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        const seats = await response.json();
        if (!Array.isArray(seats)) {
            console.error('[AJAX Error] Dữ liệu trả về không phải là một mảng JSON.');
            return [];
        }
        return seats;
    } catch (error) {
        console.error('[AJAX FAILED] Lỗi khi gọi API lấy ghế:', error);
        return [];
    }
}

/**
 * Vẽ sơ đồ ghế dựa trên dữ liệu từ bảng seats
 */
async function renderSeatMap(seatsData, bookedSeats) {
    const seatMapDiv = document.getElementById('seat-map');
    seatMapDiv.innerHTML = ''; 
    
    // Reset trạng thái
    selectedSeats = []; 
    currentBookedSeats = bookedSeats; 

    if (!seatsData || seatsData.length === 0) {
        seatMapDiv.innerHTML = '<p class="text-gray-400 italic py-10">Phòng chiếu chưa được cấu hình sơ đồ ghế.</p>';
        return;
    }

    // Nhóm ghế theo hàng
    const seatsByRow = {};
    seatsData.forEach(seat => {
        if (!seatsByRow[seat.row_letter]) {
            seatsByRow[seat.row_letter] = [];
        }
        seatsByRow[seat.row_letter].push(seat);
    });

    // Sắp xếp hàng theo thứ tự A, B, C...
    const sortedRows = Object.keys(seatsByRow).sort();
    
    // Tìm số cột lớn nhất
    let maxCols = 0;
    sortedRows.forEach(row => {
        if (seatsByRow[row].length > maxCols) {
            maxCols = seatsByRow[row].length;
        }
    });

    if (maxCols === 0) {
        seatMapDiv.innerHTML = '<p class="text-gray-400 italic py-10">Phòng chiếu chưa có ghế nào.</p>';
        return;
    }
    
    // Thiết lập CSS Grid
    seatMapDiv.style.gridTemplateColumns = `30px repeat(${maxCols}, 30px)`;
    seatMapDiv.style.display = 'grid';
    seatMapDiv.style.gap = '6px';
    seatMapDiv.style.width = 'fit-content';
    seatMapDiv.style.margin = '0 auto';

    // Render từng hàng
    sortedRows.forEach(rowLetter => {
        const rowSeats = seatsByRow[rowLetter].sort((a, b) => a.seat_number - b.seat_number);
        
        // Nhãn hàng (Row Label)
        const rowLabel = document.createElement('div');
        rowLabel.className = 'seat-row-label font-bold text-gray-400 text-center text-sm h-6 leading-6';
        rowLabel.textContent = rowLetter;
        seatMapDiv.appendChild(rowLabel);

        // Render ghế trong hàng
        let colIndex = 0;
        rowSeats.forEach(seat => {
            const seatWrapper = document.createElement('div');
            seatWrapper.className = 'flex items-center justify-center h-6';
            
            const isBooked = bookedSeats.includes(seat.seat_code);
            // Sử dụng dữ liệu từ API (đã có seat_type_code, price_modifier, color_code)
            const seatTypeCode = seat.seat_type_code || 'standard';
            const typeInfo = SEAT_TYPES[seatTypeCode] || SEAT_TYPES['standard'];
            const seatElement = document.createElement('div');
            
            // Tính giá từ price_modifier
            const seatPrice = basePrice * (seat.price_modifier || 1.0);
            
            // Sử dụng màu từ API hoặc fallback (dùng style inline vì Tailwind không hỗ trợ dynamic color)
            let typeClass = typeInfo?.color || 'bg-gray-400';
            if (seat.color_code) {
                // Dùng inline style cho màu động
                seatElement.style.backgroundColor = seat.color_code;
            }

            seatElement.dataset.seatName = seat.seat_code;
            seatElement.dataset.seatType = seatTypeCode;
            seatElement.dataset.price = seatPrice;
            seatElement.title = `${seat.seat_code} - ${seat.seat_type_name || typeInfo?.name || 'Không rõ'} (${formatCurrency(seatPrice)})`;
            
            // Nếu không thể đặt, đánh dấu
            if (!seat.is_bookable) {
                seatElement.style.pointerEvents = 'none';
                seatElement.classList.add('opacity-50', 'cursor-not-allowed');
            }

            let classes = 'w-6 h-6 rounded-sm flex items-center justify-center text-xs font-bold transition duration-150';

            if (isBooked) {
                // Ghế đã bán
                classes += ' bg-gray-500 text-gray-200 cursor-not-allowed';
                seatElement.style.pointerEvents = 'none';
                seatElement.style.backgroundColor = '#6b7280'; // Override màu nếu đã bán
                seatElement.title = `${seat.seat_code} - Đã bán`;
            } else {
                // Ghế có thể chọn
                if (!seat.color_code) {
                    classes += ` ${typeClass}`;
                }
                classes += ' text-black cursor-pointer hover:ring-2 hover:ring-white';
                seatElement.classList.add('available-seat');
                seatElement.addEventListener('click', toggleSeatSelection);
            }
            
            seatElement.className = classes;
            seatElement.textContent = seat.seat_number;
            
            seatWrapper.appendChild(seatElement);
            seatMapDiv.appendChild(seatWrapper);
            colIndex++;
        });

        // Thêm các ô trống nếu hàng không đủ maxCols
        while (colIndex < maxCols) {
            const emptyWrapper = document.createElement('div');
            emptyWrapper.className = 'flex items-center justify-center h-6';
            seatMapDiv.appendChild(emptyWrapper);
            colIndex++;
        }
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
        const screenId = this.dataset.screenId;
        basePrice = parseFloat(this.dataset.price);
        
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
            // Lấy danh sách ghế từ API
            const seatsData = await fetchSeatsByScreen(screenId);
            
            // Lấy danh sách ghế đã bán
            const bookedSeats = await fetchBookedSeats(selectedShowId);
            
            // Vẽ sơ đồ ghế
            await renderSeatMap(seatsData, bookedSeats);
            
        } catch (error) {
            console.error('Lỗi khi tải sơ đồ ghế:', error);
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