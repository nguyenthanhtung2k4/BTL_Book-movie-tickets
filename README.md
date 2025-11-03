#
# Cấu trúc Folder: 

``` bash
asset/
│── css/
│── img/
│── js/
function/
│── auth.php
│── db.php
handle/
thamkhoa/
views/
│── admin/
│   ├── footer.php
│   ├── fromt.php   ← (chắc là front.php / form.php ?)
│   ├── header.php
│   ├── index.php
│   ├── side_bar.php
│   ├── users.php
│── clinet/
│   ├── home.php
index.php
README.md

```
# SQL: 

Đây là một mô hình cơ sở dữ liệu (Database Schema) tiêu chuẩn cho một hệ thống quản lý rạp chiếu phim hoặc đặt vé xem phim trực tuyến.

Dưới đây là danh sách các bảng (Tables) trong database **`cinema`** và mục đích của chúng:

| Tên Bảng (Table) | Mục đích (Purpose) |
| :--- | :--- |
| **`users`** | Chứa thông tin về tất cả người dùng hệ thống (khách hàng, admin, nhân viên). |
| **`theaters`** | Lưu trữ thông tin chi tiết về các cụm rạp hoặc chi nhánh rạp chiếu phim (ví dụ: tên rạp, địa chỉ, sức chứa tổng thể). |
| **`movies`** | Chứa thông tin chi tiết về tất cả các bộ phim có trong hệ thống (ví dụ: tiêu đề, mô tả, thời lượng, đạo diễn, rating, poster). |
| **`screens`** | Đại diện cho các phòng chiếu phim (còn gọi là màn hình chiếu) bên trong mỗi rạp (`theaters`). Nó sẽ chứa thông tin như số phòng, loại phòng (2D, 3D, IMAX), và số lượng ghế. |
| **`shows`** | Đây là bảng quan trọng nhất. Nó lưu trữ thông tin về **mỗi suất chiếu cụ thể** của một bộ phim (`movies`) tại một phòng chiếu (`screens`) vào một thời điểm nhất định (ngày và giờ). |
| **`bookings`** | Lưu trữ thông tin về mỗi giao dịch đặt vé đã được thực hiện bởi người dùng (`users`). Nó chứa thông tin tổng quát về đơn đặt vé (ví dụ: ID đơn hàng, tổng tiền, ngày đặt, trạng thái). |
| **`booking_items`** | Đây là bảng chi tiết của bảng `bookings`. Nó lưu trữ thông tin chi tiết về **từng vé** trong một đơn đặt vé (`bookings`), bao gồm ghế đã chọn và giá vé cụ thể cho từng ghế đó. |

### Tóm tắt mối quan hệ (Summary of Relationships):

1.  **`theaters`** ➡️ **`screens`**: Một rạp chiếu có nhiều phòng chiếu.
2.  **`movies`** ➡️ **`shows`**: Một bộ phim có nhiều suất chiếu.
3.  **`screens`** ➡️ **`shows`**: Một phòng chiếu tổ chức nhiều suất chiếu.
4.  **`users`** ➡️ **`bookings`**: Một người dùng tạo nhiều đơn đặt vé.
5.  **`bookings`** ➡️ **`booking_items`**: Một đơn đặt vé bao gồm nhiều mục vé chi tiết (tức là nhiều ghế).




Loại ghế,Màu sắc,Mục đích
Standard,(Màu xanh dương),"Ghế tiêu chuẩn, giá thông thường."
VIP,(Màu vàng/cam),"Ghế cao cấp, thoải mái hơn, giá cao hơn."
Disabled,(Màu xanh lá),Ghế dành riêng cho người khuyết tật.
Lối đi,(Màu tối/đen),"Vị trí không có ghế, dùng để phân tách hàng hoặc tạo không gian đi lại."