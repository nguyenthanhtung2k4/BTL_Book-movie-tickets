<?php
// movies_handle.php
if (session_status() === PHP_SESSION_NONE)
      session_start();

require_once __DIR__ . '/../function/reponsitory.php';

$action = $_GET['action'] ?? $_POST['action'] ?? '';
// Lấy ID từ GET hoặc POST
$id = isset($_GET['id']) ? intval($_GET['id']) : (isset($_POST['id']) ? intval($_POST['id']) : null);

// Wrapper CRUD dùng Repository
function handleMovie($action, $data = [], $id = null)
{
      $repo = new Repository('movies');
      $response = [
            'success' => false,
            'message' => 'Lỗi không xác định.'
      ];

      try {
            switch ($action) {
                  case 'add':
                  case 'edit':
                        // --- Validation chung cho ADD và EDIT ---

                        // 1. Kiểm tra tiêu đề
                        if (empty($data['title'])) {
                              $response['message'] = '⚠️ Tên phim không được để trống.';
                              return $response;
                        }

                        // 2. Kiểm tra trùng lặp tiêu đề
                        $existing = $repo->findBy('title', $data['title']);
                        if ($existing && intval($existing['id']) !== intval($id)) { // Kiểm tra trùng, loại trừ chính nó khi edit
                              $response['message'] = '⚠️ Tên phim này đã tồn tại.';
                              return $response;
                        }

                        // 3. Kiểm tra thời lượng (phải là số dương)
                        if (empty($data['duration_min']) || !is_numeric($data['duration_min']) || $data['duration_min'] <= 0) {
                              $response['message'] = '⏱️ Thời lượng phải là số phút hợp lệ.';
                              return $response;
                        }

                        // 4. Kiểm tra rating (0-10)
                        if (isset($data['rating']) && ($data['rating'] < 0 || $data['rating'] > 10)) {
                              $response['message'] = '⭐ Đánh giá phải nằm trong khoảng từ 0 đến 10.';
                              return $response;
                        }

                        // Loại bỏ các trường không cần thiết cho repository (nếu có)
                        unset($data['action']);

                        if ($action === 'add') {
                              $data['created_at'] = date('Y-m-d H:i:s');
                              if ($repo->insert($data)) {
                                    $response['success'] = true;
                                    $response['message'] = '✅ Thêm phim mới thành công!';
                              } else {
                                    $response['message'] = '❌ Không thể thêm phim mới.';
                              }
                        } else { // 'edit'
                              if (!$id) {
                                    $response['message'] = '⚠️ Thiếu ID phim để sửa.';
                                    return $response;
                              }
                              if (!$repo->find($id)) {
                                    $response['message'] = 'Phim không tồn tại.';
                                    return $response;
                              }

                              // Kiểm tra có dữ liệu để update không (trừ updated_at)
                              $data_to_check = $data;
                              unset($data_to_check['updated_at']);
                              if (empty($data_to_check)) {
                                    $response['message'] = 'Không có dữ liệu để cập nhật.';
                                    return $response;
                              }

                              $data['updated_at'] = date('Y-m-d H:i:s');
                              if ($repo->update($id, data: $data)) {
                                    $response['success'] = true;
                                    $response['message'] = '✅ Cập nhật phim thành công!';
                              } else {
                                    $response['message'] = '❌ Cập nhật thất bại (hoặc không có gì thay đổi).';
                              }
                        }
                        return $response;

                  case 'delete':
                        if (!$id) {
                              $response['message'] = '⚠️ Thiếu ID phim để xóa.';
                              return $response;
                        }
                        $ok = $repo->delete($id);
                        $response['success'] = (bool) $ok;
                        $response['message'] = $ok ? '✅ Xóa phim thành công.' : '❌ Xóa thất bại.';
                        return $response;

                  default:
                        $response['message'] = 'Hành động không hợp lệ.';
                        return $response;
            }

      } catch (Exception $e) {
            $response['message'] = '❌ Lỗi hệ thống: ' . $e->getMessage();
            return $response;
      }
}

// Thiết lập múi giờ
date_default_timezone_set('Asia/Ho_Chi_Minh');

// --- Xử lý Thêm phim (Tương tự ADD USER) ---
if ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
      // Logic cho ADD MOVIE... (Chưa cần thiết nếu bạn chỉ yêu cầu EDIT, nhưng để sẵn cấu trúc)
      // Thu thập dữ liệu
      $data = [
            'title' => trim($_POST['title'] ?? ''),
            'duration_min' => intval($_POST['duration_min'] ?? 0),
            'rating' => floatval($_POST['rating'] ?? 0),
            'release_date' => trim($_POST['release_date'] ?? null),
            'banner_url' => trim($_POST['banner_url'] ?? null),
            'trailer_url' => trim($_POST['trailer_url'] ?? null),
            'description' => $_POST['description'] ?? '',
      ];

      $res = handleMovie('add', $data, null);

      $_SESSION['flash_message'] = $res['message'];
      $_SESSION['flash_success'] = $res['success'];

      if (!$res['success']) {
            $_SESSION['form_data'] = $_POST;
            header('Location: ' . $_SERVER['HTTP_REFERER']);
      } else {
            header('Location: ../views/admin/movies.php'); // Chuyển hướng đến trang danh sách
      }
      exit;
}

// --- Xử lý Sửa phim ---
if ($action === 'edit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
      $idToUpdate = $id ?? (isset($_POST['id']) ? intval($_POST['id']) : null);

      // Thu thập dữ liệu
      $data = [
            'title' => trim($_POST['title'] ?? ''),
            'duration_min' => intval($_POST['duration_min'] ?? 0),
            'rating' => floatval($_POST['rating'] ?? 0),
            'release_date' => trim($_POST['release_date'] ?? null),
            'banner_url' => trim($_POST['banner_url'] ?? null),
            'trailer_url' => trim($_POST['trailer_url'] ?? null),
            'description' => $_POST['description'] ?? '',
      ];

      // Gán action vào data để dùng trong handleMovie nếu cần (không bắt buộc)
      $data['action'] = 'edit';

      $res = handleMovie('edit', $data, $idToUpdate);

      $_SESSION['flash_message'] = $res['message'];
      $_SESSION['flash_success'] = $res['success'];

      // 💡 SỬA LẠI: Logic chuyển hướng có điều kiện
      if (!$res['success']) {
            // Nếu sửa thất bại, lưu dữ liệu form vào session và quay lại trang sửa
            $_SESSION['form_data'] = $_POST;
            header('Location: ' . $_SERVER['HTTP_REFERER']);
      } else {
            // Nếu sửa thành công, chuyển hướng đến trang danh sách users
            header('Location: ../views/admin/movies.php');
      }
      exit;
}

// --- Xử lý Xóa phim ---
if ($action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
      $idToDelete = $id ?? (isset($_POST['id']) ? intval($_POST['id']) : null);
      $res = handleMovie('delete', [], $idToDelete);

      $_SESSION['flash_message'] = $res['message'];
      $_SESSION['flash_success'] = $res['success'];

      header('Location: ../views/admin/movies.php');
      exit;
}

if ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
      $data = [
            'title' => trim($_POST['title']),
            'duration_min' => (int) $_POST['duration_min'],
            'description' => trim($_POST['description']),
            'rating' => trim($_POST['rating']),
            'release_date' => $_POST['release_date'] ?? null,
            'banner_url' => trim($_POST['banner_url']),
            'trailer_url' => trim($_POST['trailer_url']),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
      ];

      $result = handleMovie('add', $data);

      if ($result['success']) {
            $_SESSION['flash_message'] = $result['message'];
            $_SESSION['flash_success'] = true;
            header("Location: movies.php");
            exit;
      } else {
            $_SESSION['flash_message'] = $result['message'];
            $_SESSION['flash_success'] = false;
            header("Location: movies.php");
      }
}


