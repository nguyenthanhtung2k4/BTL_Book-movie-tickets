<?php
require_once __DIR__ . '/../function/reponsitory.php';



$method = $_SERVER['REQUEST_METHOD'];
$action = null;
$id = null;
$result = ['success' => false, 'message' => 'Lỗi xử lý không xác định.'];

function handleMovie($action, $data = [], $id = null)
{
      $repo = new Repository('movies');
      $response = ['success' => false, 'message' => 'Lỗi không xác định.'];

      switch ($action) {
            case 'add':
                  // Kiểm tra trùng tên phim
                  $exist = $repo->findBy('title', $data['title']);
                  if ($exist) {
                        $response['message'] = 'Phim này đã tồn tại trong hệ thống.';
                        return $response;
                  }

                  if ($repo->insert($data)) {
                        $response = [
                              'success' => true,
                              'message' => 'Thêm phim thành công!'
                        ];
                  } else {
                        $response['message'] = 'Không thể thêm phim.';
                  }
                  break;

            case 'edit':
                  if (!$id) {
                        $response['message'] = 'Thiếu ID phim để sửa.';
                        break;
                  }

                  // Kiểm tra phim tồn tại
                  $exist = $repo->find($id);
                  if (!$exist) {
                        $response['message'] = 'Không tìm thấy phim để cập nhật.';
                        break;
                  }

                  if ($repo->update($id, $data)) {
                        $response = [
                              'success' => true,
                              'message' => 'Cập nhật phim thành công!'
                        ];
                  } else {
                        $response['message'] = 'Lỗi khi cập nhật phim.';
                  }
                  break;

            case 'delete':
                  if (!$id) {
                        $response['message'] = 'Thiếu ID phim để xóa.';
                        break;
                  }
                  $movie = $repo->find($id);
                  if (!$movie) {
                        $response['message'] = 'Thiếu ID phim để xóa.';
                        break ;
                  }

                  if ($repo->delete($id)) {
                        $response = [
                              'success' => true,
                              'message' => ' Xóa phim thành công!'
                        ];
                  } else {
                        $response['message'] = ' Không thể xóa phim.';
                  }
                  break;

            default:
                  $response['message'] = ' Hành động không hợp lệ.';
      }

      return $response;
}

function editTheater($id){

      // --- Xử lý khi gửi form cập nhật ---
      if ($_SERVER['REQUEST_METHOD'] === 'POST') {
          $data = [
              'title'        => trim($_POST['title']),
            //   'genre'        => trim($_POST['genre'] ?? ''),
              'duration_min' => (int)($_POST['duration_min'] ?? 0),
              'description'  => trim($_POST['description'] ?? ''),
              'rating'       => (float)($_POST['rating'] ?? 0),
              'release_date' => $_POST['release_date'] ?? null,
              'banner_url'   => trim($_POST['banner_url'] ?? ''),
              'trailer_url'  => trim($_POST['trailer_url'] ?? ''),
              'updated_at'   => date('Y-m-d H:i:s')
          ];
      
          $result = handleMovie('edit', $data, $id);
      
          $_SESSION['flash_message'] = $result['message'];
          $_SESSION['flash_success'] = $result['success'];
          header('Location: movies.php');
          exit;
      
      }
}

function deleteTheater($id){
      
// Nếu người dùng đã xác nhận xoá
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
    $result = handleMovie('delete', [], $id);

    $_SESSION['flash_message'] = $result['message'];
    $_SESSION['flash_success'] = $result['success'];

    header('Location: movies.php');
    exit;
}
}
