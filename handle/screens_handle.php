<?php
// screens_handle.php
if (session_status() === PHP_SESSION_NONE)
      session_start();

require_once __DIR__ . '/../function/reponsitory.php';

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$id = isset($_GET['id']) ? intval($_GET['id']) : (isset($_POST['id']) ? intval($_POST['id']) : null);

// --- HÀM XỬ LÝ LOGIC CHÍNH ---
function handleScreens($action, $data = [], $id = null)
{
      $repo = new Repository('screens');

      switch ($action) {
            case 'add':
                  // 1. Kiểm tra dữ liệu bắt buộc
                  if (empty($data['theater_id']) || empty($data['name']) || empty($data['initial_capacity']) || $data['initial_capacity'] < 10) {
                        return ['success' => false, 'message' => 'Thiếu thông tin bắt buộc hoặc Sức chứa ban đầu không hợp lệ (>10).'];
                  }

                  // 2. Kiểm tra tên phòng đã tồn tại trong rạp đó chưa
                  $existing = $repo->findByMultipleFields([
                        'theater_id' => $data['theater_id'],
                        'name' => $data['name']
                  ]);
                  if ($existing) {
                        return ['success' => false, 'message' => 'Tên phòng **' . htmlspecialchars($data['name']) . '** đã tồn tại trong rạp chiếu này!'];
                  }

                  // 3. TẠO SƠ ĐỒ GHẾ MẶC ĐỊNH (Lưu vào bảng seats)
                  $initial_capacity = (int) $data['initial_capacity'];
                  $rows_per_screen = 10; // Giả định 10 ghế/hàng
                  $rows = ceil($initial_capacity / $rows_per_screen);
                  $capacity = 0;

                  // 4. Chuẩn bị dữ liệu để lưu screen
                  $dataToInsert = [
                        'theater_id' => $data['theater_id'],
                        'name' => $data['name'],
                        'capacity' => $initial_capacity,
                        'screen_type' => $data['screen_type'] ?? '2D',
                  ];

                  if ($repo->insert($dataToInsert)) {
                        // Lấy screen_id vừa tạo
                        $screen_id = $repo->pdo->lastInsertId();
                        
                        // Tạo ghế mặc định trong bảng seats
                        $seatRepo = new Repository('seats');
                        $seat_number = 1;
                        
                        for ($i = 0; $i < $rows; $i++) {
                              $row_letter = chr(65 + $i); // A, B, C...
                              
                              // Tính số ghế hàng cuối
                              $seats_in_row = ($i == $rows - 1)
                                    ? ($initial_capacity % $rows_per_screen == 0 ? $rows_per_screen : $initial_capacity % $rows_per_screen)
                                    : $rows_per_screen;

                        // Lấy ID của loại ghế standard
                        $seatTypeRepo = new Repository('seat_types');
                        $standardType = $seatTypeRepo->findBy('code', 'standard');
                        if (!$standardType) {
                              return ['success' => false, 'message' => 'Không tìm thấy loại ghế "standard" trong hệ thống!'];
                        }
                        $standardTypeId = $standardType['id'];
                        
                        // Tạo ghế cho hàng này
                        for ($j = 1; $j <= $seats_in_row; $j++) {
                              $seat_code = $row_letter . $j;
                              $seat_data = [
                                    'screen_id' => $screen_id,
                                    'row_letter' => $row_letter,
                                    'seat_number' => $j,
                                    'seat_code' => $seat_code,
                                    'seat_type_id' => $standardTypeId,
                                    'position_order' => $j
                              ];
                              $seatRepo->insert($seat_data);
                              $capacity++;
                        }
                        }
                        
                        // Cập nhật lại capacity chính xác
                        $repo->update($screen_id, ['capacity' => $capacity]);
                        
                        return ['success' => true, 'message' => "Thêm phòng chiếu **{$data['name']}** thành công!"];
                  }
                  return ['success' => false, 'message' => 'Lỗi: Không thể thêm phòng chiếu vào database.'];

            case 'edit':
                  // Logic cho việc chỉnh sửa thông tin phòng chiếu (sẽ được dùng trong editScreen.php)
                  if (!$id)
                        return ['success' => false, 'message' => 'Thiếu ID phòng chiếu để sửa.'];
                  if (!$repo->find($id))
                        return ['success' => false, 'message' => 'Phòng chiếu không tồn tại.'];
                  if (empty($data['name']) || empty($data['theater_id']))
                        return ['success' => false, 'message' => 'Tên phòng và Rạp là bắt buộc.'];

                  // Kiểm tra trùng tên phòng với các phòng khác trong cùng rạp (trừ chính nó)
                  // Cần một logic/hàm tìm kiếm tinh vi hơn (vd: findByMultipleFieldsExceptId)

                  $dataToUpdate = [
                        'theater_id' => $data['theater_id'],
                        'name' => $data['name'],
                        'screen_type' => $data['screen_type'] ?? '2D',
                  ];

                  if ($repo->update($id, $dataToUpdate)) {
                        return ['success' => true, 'message' => "Cập nhật phòng chiếu **{$data['name']}** thành công."];
                  }
                  return ['success' => false, 'message' => 'Lỗi khi cập nhật phòng chiếu.'];

            case 'delete':
                  if (!$id)
                        return ['success' => false, 'message' => 'Thiếu ID phòng chiếu để xóa.'];
                  if (!$repo->find($id))
                        return ['success' => false, 'message' => 'Phòng chiếu không tồn tại.'];

                  // Xóa screen sẽ tự động xóa tất cả ghế (CASCADE)
                  if ($repo->delete($id)) {
                        return ['success' => true, 'message' => 'Xóa phòng chiếu thành công.'];
                  }
                  return ['success' => false, 'message' => 'Lỗi khi xóa phòng chiếu.'];

            case 'update_layout':
                  // 1. Kiểm tra dữ liệu
                  $layout_json = $data['seat_layout_json'] ?? null;
                  $new_capacity = intval($data['new_capacity'] ?? 0);

                  if (!$id)
                        return ['success' => false, 'message' => 'Thiếu ID phòng chiếu để cập nhật sơ đồ.'];
                  if (!$layout_json)
                        return ['success' => false, 'message' => 'Dữ liệu sơ đồ ghế JSON bị thiếu.'];

                  // 2. Parse JSON layout
                  $layout_data = json_decode($layout_json, true);
                  if (!$layout_data || !isset($layout_data['layout_details'])) {
                        return ['success' => false, 'message' => 'Dữ liệu JSON không hợp lệ.'];
                  }

                  // 3. Xóa tất cả ghế cũ
                  $seatRepo = new Repository('seats');
                  $oldSeats = $seatRepo->findAllBy('screen_id', $id);
                  foreach ($oldSeats as $oldSeat) {
                        $seatRepo->delete($oldSeat['id']);
                  }

                  // 4. Lấy mapping seat_type code -> id
                  $seatTypeRepo = new Repository('seat_types');
                  $allSeatTypes = $seatTypeRepo->getAll();
                  $seatTypeMap = [];
                  foreach ($allSeatTypes as $st) {
                        $seatTypeMap[$st['code']] = $st['id'];
                  }

                  // 5. Tạo ghế mới từ layout
                  $seatRepo = new Repository('seats');
                  foreach ($layout_data['layout_details'] as $row) {
                        $row_letter = $row['row'];
                        if (!isset($row['seat_data']) || !is_array($row['seat_data'])) {
                              continue;
                        }
                        
                        $position = 1;
                        foreach ($row['seat_data'] as $index => $seat_type_code) {
                              // Bỏ qua lối đi (aisle)
                              if ($seat_type_code === 'aisle') {
                                    continue;
                              }
                              
                              // Lấy seat_type_id từ code
                              $seat_type_id = $seatTypeMap[$seat_type_code] ?? $seatTypeMap['standard'];
                              
                              $seat_number = $index + 1;
                              $seat_code = $row_letter . $seat_number;
                              
                              $seat_data = [
                                    'screen_id' => $id,
                                    'row_letter' => $row_letter,
                                    'seat_number' => $seat_number,
                                    'seat_code' => $seat_code,
                                    'seat_type_id' => $seat_type_id,
                                    'position_order' => $position
                              ];
                              
                              $seatRepo->insert($seat_data);
                              $position++;
                        }
                  }

                  // 5. Cập nhật capacity
                  $repo->update($id, ['capacity' => $new_capacity]);

                  return ['success' => true, 'message' => 'Cập nhật sơ đồ ghế thành công!'];

            default:
                  return ['success' => false, 'message' => 'Hành động không hợp lệ.'];
      }
}


$redirectPage = '../views/admin/screens.php'; // Trang chuyển hướng mặc định
// add them moi san pham
if ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
      $data = [
            'theater_id' => intval($_POST['theater_id'] ?? 0),
            'name' => trim($_POST['name'] ?? ''),
            'screen_type' => trim($_POST['screen_type'] ?? '2D'),
            'initial_capacity' => intval($_POST['initial_capacity'] ?? 0),
      ];

      $res = handleScreens('add', $data, null);

      $_SESSION['flash_message'] = $res['message'];
      $_SESSION['flash_success'] = $res['success'];

      // Nếu thêm thất bại, chuyển hướng về lại trang thêm để người dùng sửa lỗi
      if (!$res['success']) {
            $_SESSION['flash_error'] = $res['message']; // Sử dụng flash_error cho form
            $redirectPage = '../views/admin/addScreen.php';
      }

      header("Location: $redirectPage");
      exit;
}

// edit 
if ($action === 'edit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
      // Logic tương tự cho việc Sửa
      $idToUpdate = $id ?? (isset($_POST['id']) ? intval($_POST['id']) : null);

      $res = handleScreens('edit', $_POST, $idToUpdate);
      $_SESSION['flash_message'] = $res['message'];
      $_SESSION['flash_success'] = $res['success'];

      if (!$res['success']) {
            $_SESSION['flash_error'] = $res['message'];
            $redirectPage = '../views/admin/editScreen.php?id=' . $idToUpdate;
      }

      header("Location: $redirectPage");
      exit;
}
//  delete
if ($action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'GET') {
      $idToDelete = $id ?? (isset($_GET['id']) ? intval($_GET['id']) : null);
      $res = handleScreens('delete', [], $idToDelete);
      $_SESSION['flash_message'] = $res['message'];
      $_SESSION['flash_success'] = $res['success'];

      header("Location: $redirectPage");
      exit;
}

////  Cap nhat ghe ngoi
if ($action === 'update_layout' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $idToUpdate = $id ?? (isset($_POST['id']) ? intval($_POST['id']) : null);
    
    $res = handleScreens('update_layout', $_POST, $idToUpdate);
    $_SESSION['flash_message'] = $res['message'];
    $_SESSION['flash_success'] = $res['success'];
if (!$res['success']) {
            $_SESSION['flash_error'] = $res['message'];
            $redirectPage = '../views/admin/editSeatLayout.php?id=' . $idToUpdate;
      }

      header("Location: $redirectPage");
      exit;
}


// // Chuyển hướng mặc định nếu hành động không được xử lý
// header("Location: $redirectPage");
// exit;
