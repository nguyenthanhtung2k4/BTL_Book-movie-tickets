
<?php
require_once __DIR__ . '/../function/reponsitory.php';

function handleMovie($action, $data = [], $id = null) {
    $repo = new Repository('movies');
    $response = ['success' => false, 'message' => 'Lỗi không xác định.'];

    switch ($action) {
        case 'add':
            // Kiểm tra trùng tên phim
            $exist = $repo->findBy('title', $data['title']);
            if ($exist) {
                $response['message'] = '⚠️ Phim này đã tồn tại trong hệ thống.';
                return $response;
            }

            if ($repo->insert($data)) {
                $response['success'] = true;
                $response['message'] = '🎉 Thêm phim thành công!';
            } else {
                $response['message'] = '❌ Không thể thêm phim.';
            }
            return $response;

        case 'edit':
            if (!$id) {
                $response['message'] = 'Thiếu ID phim để sửa.';
                return $response;
            }

            if ($repo->update($id, $data)) {
                $response['success'] = true;
                $response['message'] = '✅ Cập nhật phim thành công.';
            } else {
                $response['message'] = '❌ Lỗi khi cập nhật phim.';
            }
            return $response;

        default:
            return $response;
    }
}
