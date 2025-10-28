<?php

$base_path = '/BTL_Book_movie_tickets/views/';
$request_uri = $_SERVER['REQUEST_URI'];

// -----------------------------------------------------
// 1. Xử lý và làm sạch Route
// -----------------------------------------------------

// Lấy Route sạch (loại bỏ base_path và query string)
if (strpos($request_uri, $base_path) === 0) {
    $route = substr($request_uri, strlen($base_path));
} else {
    $route = $request_uri; 
}
$route = strtok($route, '?'); 
$route = trim($route, '/'); 

// -----------------------------------------------------
// 2. LOGIC ĐỊNH TUYẾN MỚI (Ưu tiên Admin)
// -----------------------------------------------------

// A. Xử lý các Route ADMIN (Định tuyến Động)
if (strpos($route, 'admin') === 0) {
    
    // Lấy phần còn lại của đường dẫn (ví dụ: 'user/edit/10')
    $admin_path = trim(substr($route, strlen('admin')), '/'); 
    
    // Tách các thành phần: [Controller, Action, Params...]
    $parts = explode('/', $admin_path);

    // Controller: Phần tử đầu tiên (hoặc 'index' nếu chỉ là /admin)
    $controller = $parts[0] ?: 'index';
    
    // Xây dựng đường dẫn file Controller Admin
    // Ví dụ: controller='user' -> file='views/admin/user.php'
    $admin_file_path = "views/admin/{$controller}.php"; 

    if (file_exists($admin_file_path)) {
        // Tải Controller Admin. Trong file này, bạn sẽ sử dụng biến $parts để xử lý chi tiết.
        
        // Đảm bảo $parts được truyền hoặc là biến toàn cục để sử dụng trong file controller
        // Chúng ta tạo biến $_GET['admin_parts'] để mô phỏng việc truyền tham số
        $_GET['admin_parts'] = array_slice($parts, 1); 
        
        require $admin_file_path; 
        exit;
    } else {
        // Ví dụ: /admin/non-existent-controller
        http_response_code(404);
        echo "<h1>404 - Trang quản trị không tồn tại.</h1><p>Controller: " . htmlspecialchars($controller) . "</p>";
        exit;
    }
}


// B. Xử lý các Route CLIENT (Định tuyến Tĩnh/Cơ bản)
// Dùng ánh xạ tĩnh cho các trang cơ bản client (như bạn đã có)

$map = [
    ''          => 'views/client/index.php',     // Route: /
    'index.php' => 'views/client/index.php',     // Route: /index.php (tùy chọn)
    'gioi-thieu'=> 'views/client/about.php',     // Route: /gioi-thieu
    // Thêm các route tĩnh khác của client tại đây
];

$file_path = $map[$route] ?? null; // Sử dụng toán tử null coalescing PHP 7+

// -----------------------------------------------------
// 3. THỰC THI VÀ XỬ LÝ LỖI
// -----------------------------------------------------

if ($file_path) {
    if (file_exists($file_path)) {
        require $file_path;
        exit;
    } else {
        // Route khớp nhưng file bị thiếu (Lỗi 500)
        echo "Lỗi 500: File xử lý không tồn tại: " . htmlspecialchars($file_path);
        http_response_code(500); 
        exit;
    }
}

// 4. TRANG LỖI 404 (Nếu không có route nào khớp trong cả Admin và Client)
http_response_code(404);
echo "<h1>404 - Trang không tồn tại</h1><p>URL yêu cầu: " . htmlspecialchars($route) . "</p>";

?>