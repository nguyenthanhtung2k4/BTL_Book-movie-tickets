<?php
// theaters_handle.php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../function/reponsitory.php';

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$id = isset($_GET['id']) ? intval($_GET['id']) : (isset($_POST['id']) ? intval($_POST['id']) : null);

// Wrapper CRUD dùng Repository
function handleTheater($action, $data = [], $id = null) {
    $repo = new Repository('theaters');
    $response = ['success' => false, 'message' => 'Lỗi không xác định.'];

    switch ($action) {
        case 'add':
            if (empty($data['name'])) {
                return ['success' => false, 'message' => 'Tên rạp là bắt buộc.'];
            }
            if ($repo->findBy('name', $data['name'])) {
                return ['success' => false, 'message' => 'Rạp đã tồn tại!'];
            }
            if ($repo->insert($data)) {
                return ['success' => true, 'message' => 'Thêm rạp thành công!'];
            }
            return ['success' => false, 'message' => 'Không thể thêm rạp.'];

        case 'edit':
            if (!$id) return ['success' => false, 'message' => 'Thiếu ID rạp để sửa.'];
            // kiểm tra tồn tại
            if (!$repo->find($id)) return ['success' => false, 'message' => 'Rạp không tồn tại.'];
            if (empty($data['name'])) return ['success' => false, 'message' => 'Tên rạp là bắt buộc.'];
            if ($repo->update($id, $data)) {
                return ['success' => true, 'message' => 'Cập nhật rạp thành công.'];
            }
            return ['success' => false, 'message' => 'Lỗi khi cập nhật rạp.'];

        case 'delete':
            if (!$id) return ['success' => false, 'message' => 'Thiếu ID rạp để xóa.'];
            if (!$repo->find($id)) return ['success' => false, 'message' => 'Rạp không tồn tại.'];
            if ($repo->delete($id)) {
                return ['success' => true, 'message' => 'Xóa rạp thành công.'];
            }
            return ['success' => false, 'message' => 'Lỗi khi xóa rạp.'];

        default:
            return ['success' => false, 'message' => 'Hành động không hợp lệ.'];
    }
}

if ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'name' => trim($_POST['name'] ?? ''),
        'address' => trim($_POST['address'] ?? ''),
        'city' => trim($_POST['city'] ?? ''),
        'phone' => trim($_POST['phone'] ?? ''),
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s'),
    ];
    $res = handleTheater('add', $data, null);
    $_SESSION['flash_message'] = $res['message'];
    $_SESSION['flash_success'] = $res['success'];
    header('Location: ../views/admin/theaters.php');
    exit;
}

if ($action === 'edit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $idToUpdate = $id ?? (isset($_POST['id']) ? intval($_POST['id']) : null);
    $data = [
        'name' => trim($_POST['name'] ?? ''),
        'address' => trim($_POST['address'] ?? ''),
        'city' => trim($_POST['city'] ?? ''),
        'phone' => trim($_POST['phone'] ?? ''),
        'updated_at' => date('Y-m-d H:i:s'),
    ];
    $res = handleTheater('edit', $data, $idToUpdate);
    $_SESSION['flash_message'] = $res['message'];
    $_SESSION['flash_success'] = $res['success'];
    header('Location: ../views/admin/theaters.php');
    exit;
}

if ($action === 'delete') {
    $idToDelete = $id ?? (isset($_POST['id']) ? intval($_POST['id']) : null);
    $res = handleTheater('delete', [], $idToDelete);
    $_SESSION['flash_message'] = $res['message'];
    $_SESSION['flash_success'] = $res['success'];
    header('Location: ../views/admin/theaters.php');
    exit;
}

// header('Location: ../admin/theaters.php');
// exit;
