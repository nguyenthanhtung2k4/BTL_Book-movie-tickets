<?php
// user_handle.php
if (session_status() === PHP_SESSION_NONE)
    session_start();

require_once __DIR__ . '/../function/reponsitory.php';

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$id = isset($_GET['id']) ? intval($_GET['id']) : (isset($_POST['id']) ? intval($_POST['id']) : null);

// Wrapper CRUD dùng Repository
function handleUser($action, $data = [], $id = null)
{
    $repo = new Repository('users');
    $response = [
        'success' => false,
        'message' => 'Lỗi không xác định.'
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
                if (!$id) {
                    $response['message'] = '⚠️ Thiếu ID người dùng để sửa.';
                    return $response;
                }
                // kiểm tra người dùng có tồn tại không
                if (!$repo->find($id)) {
                    $response['message'] = 'Người dùng không tồn tại.';
                    return $response;
                }

                // Nếu có email mới thì kiểm tra trùng
                if (isset($data['email']) && !empty($data['email'])) {
                    // Kiểm tra định dạng email
                    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                        $response['message'] = '📧 Định dạng email không hợp lệ.';
                        return $response;
                    }
                    $existing = $repo->findBy('email', $data['email']);
                    if ($existing && intval($existing['id']) !== intval($id)) { // So sánh cả id
                        $response['message'] = '⚠️ Email này đã tồn tại ở người dùng khác.';
                        return $response;
                    }
                }

                // Kiểm tra có dữ liệu để update không
                if (empty($data)) {
                    $response['message'] = 'Không có dữ liệu để cập nhật.';
                    return $response;
                }

                if ($repo->update($id, $data)) {
                    $response['success'] = true;
                    $response['message'] = '✅ Cập nhật thông tin người dùng thành công!';
                } else {
                    $response['message'] = '❌ Cập nhật thất bại (hoặc không có gì thay đổi).';
                }
                return $response;


            case 'delete':
                if (!$id) {
                    $response['message'] = '⚠️ Thiếu ID người dùng để xóa.';
                    return $response;
                }

                // Kiểm tra không cho xóa chính mình
                if (isset($_SESSION['user']) && intval($_SESSION['user']['id']) === intval($id)) {
                    $response['message'] = 'Bạn không thể xóa chính mình.';
                    return $response;
                }

                // Kiểm tra tồn tại trước khi xóa (tùy chọn)
                if (!$repo->find($id)) {
                    $response['message'] = 'Người dùng không tồn tại.';
                    return $response;
                }

                $ok = $repo->delete($id);
                $response['success'] = (bool) $ok;
                $response['message'] = $ok ? '✅ Xóa người dùng thành công.' : '❌ Xóa thất bại.';
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

// --- Xử lý Thêm người dùng ---
if ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'full_name' => trim($_POST['full_name'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        // Kiểm tra password trước khi hash
        'password_hash' => !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : '',
        'role' => $_POST['role'] ?? 'customer',
        'created_at' => date('Y-m-d H:i:s')
    ];

    // Cần kiểm tra mật khẩu có được gửi đi không cho trường hợp 'add'
    if (empty($_POST['password'])) {
        $res = ['success' => false, 'message' => '⚠️ Mật khẩu là bắt buộc.'];
    } else {
        $res = handleUser('add', $data, null);
    }

    $_SESSION['flash_message'] = $res['message'];
    $_SESSION['flash_success'] = $res['success'];

    // Nếu thêm thất bại, giữ lại dữ liệu cũ để hiển thị lại trên form
    if (!$res['success']) {
        $_SESSION['form_data'] = $_POST;
        header('Location: ' . $_SERVER['HTTP_REFERER']);
    } else {
        $_SESSION['flash_message'] = $res['message'];
        $_SESSION['flash_success'] = $res['success'];
        header('Location: ../views/admin/users.php'); // Chuyển hướng đến trang danh sách users
    }
    exit;
}

// --- Xử lý Sửa người dùng ---
if ($action === 'edit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $idToUpdate = $id ?? (isset($_POST['id']) ? intval($_POST['id']) : null);

    $data = [
        'full_name' => trim($_POST['full_name'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'role' => $_POST['role'] ?? 'customer',
        'updated_at' => date('Y-m-d H:i:s')
    ];

    // Chỉ hash và thêm password_hash nếu người dùng nhập mật khẩu mới
    if (!empty($_POST['password'])) {
        $data['password_hash'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
    }

    $res = handleUser('edit', $data, $idToUpdate);

    $_SESSION['flash_message'] = $res['message'];
    $_SESSION['flash_success'] = $res['success'];

    header('Location: ../views/admin/users.php');
    exit;
}

// --- Xử lý Xóa người dùng ---
if ($action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $idToDelete = $id ?? (isset($_POST['id']) ? intval($_POST['id']) : null);
    $res = handleUser('delete', [], $idToDelete);

    $_SESSION['flash_message'] = $res['message'];
    $_SESSION['flash_success'] = $res['success'];

    header('Location: ../views/admin/users.php');
    exit;
}

// // Chuyển hướng mặc định nếu không có action hợp lệ
// header('Location: ../views/admin/users.php');
// exit;
?>