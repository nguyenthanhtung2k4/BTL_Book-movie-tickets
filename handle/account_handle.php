<?php


if (session_status() === PHP_SESSION_NONE)
      session_start();

require_once __DIR__ . '/../function/reponsitory.php';

function handleUser($action, $data = [], $id = null)
{
      $repo = new Repository('users');
      $response = [
            'success' => false,
            'message' => 'Có lỗi xảy ra, vui lòng thử lại.'
      ];

      try {
            switch ($action) {
                  case 'add':
                        // 🔍 Kiểm tra dữ liệu
                        if (empty($data['full_name']) || empty($data['email']) || empty($data['password_hash'])) {
                              $response['message'] = '⚠️ Vui lòng nhập đầy đủ thông tin.';
                              return $response;
                        }

                        // Kiểm tra định dạng email
                        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                              $response['message'] = '📧 Định dạng email không hợp lệ.';
                              return $response;
                        }

                        // Kiểm tra email đã tồn tại
                        $existing = $repo->findBy('email', $data['email']);
                        if ($existing) {
                              $response['message'] = '⚠️ Email này đã được sử dụng.';
                              return $response;
                        }

                        // Thêm mới
                        if ($repo->insert($data)) {
                              $response['success'] = true;
                              $response['message'] = '✅ Thêm người dùng thành công!';
                        } else {
                              $response['message'] = '❌ Không thể thêm người dùng.';
                        }
                        return $response;


                  case 'edit':
                        if (!$id || empty($data)) {
                              $response['message'] = '⚠️ Thiếu dữ liệu để cập nhật.';
                              return $response;
                        }

                        // Nếu có email mới thì kiểm tra trùng
                        if (isset($data['email']) && !empty($data['email'])) {
                              $existing = $repo->findBy('email', $data['email']);
                              if ($existing && $existing['id'] != $id) {
                                    $response['message'] = '⚠️ Email này đã tồn tại ở người dùng khác.';
                                    return $response;
                              }
                        }

                        if ($repo->update($id, $data)) {
                              $response['success'] = true;
                              $response['message'] = '✅ Cập nhật thông tin người dùng thành công!';
                        } else {
                              $response['message'] = '❌ Cập nhật thất bại.';
                        }
                        return $response;


                  case 'delete':
                        if (!$id) {
                              $response['message'] = 'Thiếu ID người dùng để xóa.';
                              return $response;
                        }

                        if (session_status() === PHP_SESSION_NONE)
                              session_start();
                        if (isset($_SESSION['user']) && intval($_SESSION['user']['id']) === intval($id)) {
                              $response['message'] = 'Bạn không thể xóa chính mình.';
                              return $response;
                        }
                        $ok = $repo->delete($id);
                        $response['success'] = (bool) $ok;
                        $response['message'] = $ok ? 'Xóa người dùng thành công.' : 'Xóa thất bại.';
                        return $response;

                  default:
                        $response['message'] = 'Hành động không hợp lệ.';
                        return $response;
            }

      } catch (Exception $e) {
            $response['message'] = 'Lỗi hệ thống: ' . $e->getMessage();
            return $response;
      }
}



// Đường dẫn chuyển hướng mặc định sau khi xử lý xong (QUAY LẠI TRANG FORM)
$redirect_url = '../views/clinet/account.php'; 

// Lấy action và view
$action = $_POST['action'] ?? $_GET['action'] ?? '';
$view_from_form = $_POST['view'] ?? $_GET['view'] ?? '';

// 1. Xử lý ĐĂNG KÝ (REGISTER) - Dùng POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'register') {
     
     // ... (Giữ nguyên logic xử lý đăng ký) ...
     $data = [
          'full_name' => trim($_POST['fullname'] ?? ''),
          'email' => trim($_POST['email'] ?? ''),
          'password_hash' => password_hash($_POST['password'] ?? '', PASSWORD_DEFAULT),
          'role' => 'customer',
          'created_at' => date('Y-m-d H:i:s'),
     ];

     if (($_POST['password'] ?? '') !== ($_POST['confirmPassword'] ?? '')) {
          $res = ['success' => false, 'message' => '❌ Mật khẩu xác nhận không trùng khớp.'];
          $_SESSION['flash_view'] = 'register'; 
     } else {
          $res = handleUser('add', $data);
          if ($res['success']) {
               // Đăng ký thành công -> Chuyển về form Login để đăng nhập
               $res['message'] = '✅ Đăng ký thành công! Vui lòng đăng nhập.';
               $_SESSION['flash_view'] = 'login';
          } else {
               // Đăng ký thất bại -> Giữ ở form Register
               $_SESSION['flash_view'] = 'register';
          }
     }

     // Lưu thông báo và chuyển hướng về trang form
     $_SESSION['flash_message'] = $res['message'];
     $_SESSION['flash_success'] = $res['success'];

     // **SỬA LỖI CHUYỂN HƯỚNG:** Quay về trang form account.php
     header('Location: ' . $redirect_url . '?view=' . $_SESSION['flash_view']); 
     exit;
}

// 2. Xử lý ĐĂNG NHẬP (LOGIN) - Dùng POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'login') {
     
     $email = trim($_POST['email'] ?? '');
     $password = $_POST['password'] ?? '';
     $repo = new Repository('users');

     $user = $repo->findBy('email', $email);

     if ($user && password_verify($password, $user['password_hash'])) {
          // Đăng nhập thành công
          $_SESSION['user'] = [
               'id' => $user['id'],
               'full_name' => $user['full_name'],
               'email' => $user['email'],
               'role' => $user['role']
          ];

          // Chuyển hướng người dùng về trang chủ (hoặc trang admin)
          if ($user['role'] === 'admin') {
               // Có thể thêm flash message thành công nếu cần thiết
               header('Location: ../views/admin/index.php'); // Giả định admin index
          } else {
               // Có thể thêm flash message thành công nếu cần thiết
                $_SESSION['flash_message'] = '✅ Đăng nhập thành công!';
                $_SESSION['flash_success'] = true;
               header('Location: ../views/clinet/index.php'); // Giả định client index
          }
          exit;

     } else {
          // Đăng nhập thất bại
          $_SESSION['flash_message'] = '❌ Sai email hoặc mật khẩu.';
          $_SESSION['flash_success'] = false;
          $_SESSION['flash_view'] = 'login';
          
          // **SỬA LỖI CHUYỂN HƯỚNG:** Quay lại trang form account.php để hiển thị lỗi
          header('Location: ' . $redirect_url . '?view=login');
          exit;
     }
}

// Nếu không có hành động hợp lệ (và không phải đang trong account.php)
if (!empty($action)) {
    header('Location: ' . $redirect_url);
    exit;
}