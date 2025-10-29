<?php
require_once __DIR__ . '/../function/reponsitory.php';

function handleUser($action, $data = [], $id = null) {
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
                
                if (session_status() === PHP_SESSION_NONE) session_start();
                if (isset($_SESSION['user']) && intval($_SESSION['user']['id']) === intval($id)) {
                    $response['message'] = 'Bạn không thể xóa chính mình.';
                    return $response;
                }
                $ok = $repo->delete($id);
                $response['success'] = (bool)$ok;
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


if ($action='edit' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    

    $data = [
        'full_name' => trim($_POST['full_name']),
        'email'     => trim($_POST['email']),
        'role'      => $_POST['role'] ?? 'customer',
        'updated_at'=> date('Y-m-d H:i:s')
    ];

    if (!empty($_POST['password'])) {
        $data['password_hash'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
    }

    if ($data['email'] !== $user['email']) {
        $existing = $repo->findBy('email', $data['email']);
        if ($existing && (int)$existing['id'] !== $id) {
            $message = '⚠️ Email đã được sử dụng bởi tài khoản khác.';
        } else {
            $result = handleUser('edit', $data, $id);
            if (is_array($result)) {
                $isSuccess = $result['success'];
                $message = $result['message'];
            } else {
                if ($result) {
                    $isSuccess = true;
                    $message = 'Cập nhật thành công!';
                } else {
                    $isSuccess = false;
                    $message = 'Cập nhật thất bại!';
                }
            }
        }
    } else {
        $result = handleUser('edit', $data, $id);
        if (is_array($result)) {
            $isSuccess = $result['success'];
            $message = $result['message'];
        } else {
            if ($result) {
                $isSuccess = true;
                $message = '✅ Cập nhật thành công!';
            } else {
                $isSuccess = false;
                $message = '❌ Cập nhật thất bại!';
            }
        }
    }

    if ($isSuccess) {
        header("Refresh: 1.2; url=users.php");
        $user = $repo->find($id);
    }

}

