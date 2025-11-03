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

                  // 3. TẠO SƠ ĐỒ GHẾ JSON MẶC ĐỊNH
                  $initial_capacity = (int) $data['initial_capacity'];
                  $rows_per_screen = 10; // Giả định 10 ghế/hàng
                  $rows = ceil($initial_capacity / $rows_per_screen);
                  $capacity = 0;
                  $layout_details = [];

                  for ($i = 0; $i < $rows; $i++) {
                        $row_name = chr(65 + $i);

                        // Tính số ghế hàng cuối (nếu không đủ 10)
                        $seats_in_row = ($i == $rows - 1)
                              ? ($initial_capacity % $rows_per_screen == 0 ? $rows_per_screen : $initial_capacity % $rows_per_screen)
                              : $rows_per_screen;

                        $layout_details[] = [
                              'row' => $row_name,
                              'seats' => $seats_in_row,
                              'type' => 'standard'
                        ];
                        $capacity += $seats_in_row;
                  }

                  $seat_layout_data = [
                        "rows_count" => $rows,
                        "total_capacity" => $capacity,
                        "layout_details" => $layout_details
                  ];

                  // 4. Chuẩn bị dữ liệu cuối cùng để lưu
                  $dataToInsert = [
                        'theater_id' => $data['theater_id'],
                        'name' => $data['name'],
                        'capacity' => $capacity,
                        'screen_type' => $data['screen_type'] ?? '2D',
                        'seat_layout' => json_encode($seat_layout_data),
                  ];

                  if ($repo->insert($dataToInsert)) {
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

                  // 2. Chuẩn bị dữ liệu cập nhật
                  $dataToUpdate = [
                        'capacity' => $new_capacity, // Cập nhật lại sức chứa
                        'seat_layout' => $layout_json,
                        'updated_at' => date('Y-m-d H:i:s'),
                  ];

                  // 3. Thực hiện cập nhật
                  if ($repo->update($id, $dataToUpdate)) {
                        return ['success' => true, 'message' => 'Cập nhật sơ đồ ghế thành công!'];
                  }
                  return ['success' => false, 'message' => 'Lỗi khi lưu sơ đồ ghế vào database.'];

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
