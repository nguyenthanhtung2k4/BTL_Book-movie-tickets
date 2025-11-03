<?php
// handle/shows_handle.php
if (session_status() === PHP_SESSION_NONE)
      session_start();

require_once __DIR__ . '/../function/reponsitory.php';

// Khởi tạo Repositories
$showRepo = new Repository('shows');
$movieRepo = new Repository('movies');

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$id = isset($_GET['id']) ? intval($_GET['id']) : (isset($_POST['id']) ? intval($_POST['id']) : null);
$data = $_POST;

/**
 * Hàm kiểm tra sự trùng lặp lịch chiếu (Logic Nghiệp vụ).
 * @param int $screenId ID phòng chiếu
 * @param string $newShowTime Thời gian bắt đầu của suất chiếu mới (Y-m-d H:i:s)
 * @param int $durationMinutes Thời lượng phim (phút)
 * @param int|null $currentShowId ID suất chiếu đang sửa (để loại trừ)
 * @return array|false Trả về mảng suất chiếu bị trùng nếu có, ngược lại trả về false.
 */
function check_show_conflict($screenId, $newShowTime, $durationMinutes, $currentShowId = null)
{
      global $showRepo, $movieRepo;

      // Thêm thời gian nghỉ giữa các suất chiếu (ví dụ: 15 phút dọn dẹp/quảng cáo)
      $bufferTime = 15;

      // Tính toán thời gian kết thúc của suất chiếu mới (bao gồm buffer)
      $endTime = date('Y-m-d H:i:s', strtotime($newShowTime . ' + ' . $durationMinutes . ' minutes + ' . $bufferTime . ' minutes'));

      $allShows = $showRepo->getAll();
      $conflictingShows = [];

      foreach ($allShows as $existingShow) {
            if ($currentShowId && $existingShow['id'] == $currentShowId) {
                  continue;
            }

            if ($existingShow['screen_id'] != $screenId) {
                  continue;
            }

            // Tra cứu thời lượng phim cũ
            $existingMovie = $movieRepo->find($existingShow['movie_id']);
            $existingDuration = (int) ($existingMovie['duration_minutes'] ?? 90);

            $existingStartTime = $existingShow['show_time'];
            // Tính toán thời gian kết thúc của suất chiếu cũ (bao gồm buffer)
            $existingEndTime = date('Y-m-d H:i:s', strtotime($existingStartTime . ' + ' . $existingDuration . ' minutes + ' . $bufferTime . ' minutes'));

            // Kiểm tra chồng lấn: (Bắt đầu mới < Kết thúc cũ) VÀ (Kết thúc mới > Bắt đầu cũ)
            if (($newShowTime < $existingEndTime) && ($endTime > $existingStartTime)) {
                  $conflictingShows[] = $existingShow;
            }
      }

      return empty($conflictingShows) ? false : $conflictingShows;
}


/**
 * Hàm CRUD thuần túy cho shows.
 * @param string $action 'add', 'edit', 'delete'
 * @param array $data Dữ liệu để chèn/cập nhật
 * @param int|null $id ID suất chiếu
 * @return array Kết quả: ['success' => bool, 'message' => string]
 */
function handleShow($action, $data = [], $id = null)
{
      global $showRepo;
      $response = ['success' => false, 'message' => 'Lỗi không xác định.'];

      // Chuẩn hóa dữ liệu đầu vào cho CSDL
      $dataToProcess = [
            'movie_id' => (int) ($data['movie_id'] ?? 0),
            'screen_id' => (int) ($data['screen_id'] ?? 0),
            'show_time' => date('Y-m-d H:i:s', strtotime($data['show_time'])),
            'format' => $data['format'], // Đã bỏ htmlspecialchars
            'price' => (int) str_replace(['.', ','], '', $data['price']),
            'status' => $data['status'] ?? 'active', // Đã bỏ htmlspecialchars
            'updated_at' => date('Y-m-d H:i:s'),
      ];

      switch ($action) {
            case 'add':
                  $dataToProcess['created_at'] = date('Y-m-d H:i:s');
                  if ($showRepo->insert($dataToProcess)) {
                        return ['success' => true, 'message' => 'Thêm suất chiếu mới thành công.'];
                  }
                  return ['success' => false, 'message' => 'Lỗi khi thêm suất chiếu vào CSDL.'];

            case 'edit':
                  if (!$id || !$showRepo->find($id))
                        return ['success' => false, 'message' => 'Suất chiếu không tồn tại hoặc thiếu ID.'];

                  // Xóa created_at nếu có
                  unset($dataToProcess['created_at']);

                  if ($showRepo->update($id, $dataToProcess)) {
                        return ['success' => true, 'message' => 'Cập nhật suất chiếu thành công.'];
                  }
                  return ['success' => false, 'message' => 'Lỗi khi cập nhật suất chiếu.'];

            case 'delete':
                  if (!$id || !$showRepo->find($id))
                        return ['success' => false, 'message' => 'Suất chiếu không tồn tại hoặc thiếu ID.'];

                  if ($showRepo->delete($id)) {
                        return ['success' => true, 'message' => "Xóa suất chiếu ID #{$id} thành công."];
                  }
                  return ['success' => false, 'message' => 'Lỗi khi xóa suất chiếu.'];

            default:
                  return ['success' => false, 'message' => 'Hành động không hợp lệ.'];
      }
}


// ==========================================================
// --- XỬ LÝ ACTION & KIỂM TRA ĐIỀU KIỆN (NGOÀI HÀM CRUD) ---
// ==========================================================

// --- Xử lý Thêm Suất Chiếu (Action 'add' với POST) ---
$redirectPage = '../views/admin/shows.php'; // Trang chuyển hướng mặc định

if ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {

      // 1. Kiểm tra dữ liệu bắt buộc
      if (empty($data['movie_id']) || empty($data['screen_id']) || empty($data['show_time']) || empty($data['price'])) {
            $res = ['success' => false, 'message' => 'Vui lòng điền đầy đủ các trường bắt buộc.'];
      } else {
            // 2. Tra cứu thời lượng phim
            $movie = $movieRepo->find((int) $data['movie_id']);
            if (!$movie) {
                  $res = ['success' => false, 'message' => 'Phim được chọn không tồn tại.'];
            } else {
                  $durationMinutes = (int) ($movie['duration_minutes'] ?? 90);

                  // 3. Kiểm tra trùng lịch
                  $conflicts = check_show_conflict(
                        (int) $data['screen_id'],
                        date('Y-m-d H:i:s', strtotime($data['show_time'])),
                        $durationMinutes
                  );

                  if ($conflicts) {
                        $conflict_info = '';
                        foreach ($conflicts as $c) {
                              $conflict_info .= 'Suất ID #' . $c['id'] . ' lúc ' . date('H:i', strtotime($c['show_time'])) . '; ';
                        }
                        $res = ['success' => false, 'message' => 'Lịch chiếu bị trùng! Phòng đã có suất: ' . $conflict_info];
                  } else {
                        // 4. Gọi hàm CRUD thuần túy
                        $res = handleShow('add', $data, null);
                        //     if ($res['success']) $redirectPage = 'shows.php';
                  }
            }
      }

      $_SESSION['flash_message'] = $res['message'];
      $_SESSION['flash_success'] = $res['success'];

      header("Location: $redirectPage");
      exit;
}


//(Action 'edit' với POST) ---
if ($action === 'edit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
      $redirectPage = 'editShow.php?id=' . $id;

      // 1. Kiểm tra ID và dữ liệu bắt buộc
      if (!$id) {
            $res = ['success' => false, 'message' => 'Thiếu ID suất chiếu để sửa.'];
      } elseif (empty($data['movie_id']) || empty($data['screen_id']) || empty($data['show_time']) || empty($data['price'])) {
            $res = ['success' => false, 'message' => 'Vui lòng điền đầy đủ các trường bắt buộc.'];
      } else {
            $movie = $movieRepo->find((int) $data['movie_id']);
            if (!$movie) {
                  $res = ['success' => false, 'message' => 'Phim được chọn không tồn tại.'];
            } else {
                  $durationMinutes = (int) ($movie['duration_minutes'] ?? 90);

                  $conflicts = check_show_conflict(
                        (int) $data['screen_id'],
                        date('Y-m-d H:i:s', strtotime($data['show_time'])),
                        $durationMinutes,
                        $id
                  );

                  if ($conflicts) {
                        $conflict_info = '';
                        foreach ($conflicts as $c) {
                              $conflict_info .= 'Suất ID #' . $c['id'] . ' lúc ' . date('H:i', strtotime($c['show_time'])) . '; ';
                        }
                        $res = ['success' => false, 'message' => 'Lịch chiếu bị trùng! Phòng đã có suất: ' . $conflict_info];
                  } else {
                        // 4. Gọi hàm CRUD thuần túy
                        try{
                              $res = handleShow('edit', $data, $id);

                        }catch(Exception $e){
                              $res = ['success'=> false, 'message'=> $e->getMessage()];
                              
                        }     
                        if ($res['success'])
                              $redirectPage = 'shows.php';
                  }
            }
      }

      $_SESSION['flash_message'] = $res['message'];
      $_SESSION['flash_success'] = $res['success'];
      header("Location: ../views/admin/" . $redirectPage);
      exit;
}


// --- Xử lý Xóa Suất Chiếu (Action 'delete' với GET) ---
if ($action === 'delete') {
      $redirectPage = 'shows.php';

      // 1. Gọi hàm CRUD thuần túy
      $res = handleShow('delete', [], $id);

      $_SESSION['flash_message'] = $res['message'];
      $_SESSION['flash_success'] = $res['success'];
      header("Location: ../views/admin/" . $redirectPage);
      exit;
}

// Chuyển hướng mặc định (nếu không có action hợp lệ)
// header('Location: ../screens/shows.php');
// exit;