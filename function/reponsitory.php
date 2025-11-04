<?php
require_once __DIR__ . '/db.php';

class Repository
{
    public $pdo; // Changed to public for lastInsertId access
    private $table;

    public function __construct($table)
    {
        $db = new Database();
        $this->pdo = $db->connect();
        $this->table = $table;
    }

    // 🔹 Lấy tất cả dữ liệu
    public function getAll()
    {
        $stmt = $this->pdo->query("SELECT * FROM {$this->table}");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 🔹 Lấy tất cả dữ liệu
    public function getAllTimeDESC()
    {
        $stmt = $this->pdo->query("SELECT * FROM {$this->table} Order BY created_at DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 🔹 Tìm theo ID
    public function find($id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // 🔹 Tìm theo cột (ví dụ findBy('email', 'abc@gmail.com'))
    public function findBy($column, $value)
    {
        $sql = "SELECT * FROM {$this->table} WHERE $column = :value LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['value' => $value]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Lấy TẤT CẢ bản ghi theo một cột (Rất cần cho Booking Items)
    public function findAllBy($column, $value)
    {
        // Bảo vệ tên cột
        $safeColumn = preg_replace('/[^a-zA-Z0-9_]/', '', $column);

        $sql = "SELECT * FROM {$this->table} WHERE {$safeColumn} = :value";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['value' => $value]);

        // Lấy TẤT CẢ các bản ghi dưới dạng mảng kết hợp
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 🔹 Thêm mới bản ghi
    public function insert($data)
    {
        $keys = array_keys($data);
        $fields = implode(',', $keys);
        $placeholders = ':' . implode(', :', $keys);

        $sql = "INSERT INTO {$this->table} ($fields) VALUES ($placeholders)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($data);
    }

    // 🔹 Cập nhật bản ghi
    public function update($id, $data)
    {
        $fields = implode(', ', array_map(fn($k) => "$k = :$k", array_keys($data)));
        $data['id'] = $id;
        $sql = "UPDATE {$this->table} SET $fields WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($data);
    }

    // 🔹 Xóa bản ghi
    public function delete($id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM {$this->table} WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Tìm kiếm bản ghi dựa trên nhiều điều kiện cột.
     * @param array $conditions Mảng key-value (cột => giá trị)
     * @return array|false Bản ghi tìm thấy hoặc false nếu không tìm thấy.
     */
    public function findByMultipleFields(array $conditions)
    {
        if (empty($conditions)) {
            return false;
        }

        // Xây dựng mệnh đề WHERE và mảng tham số (parameters)
        $where_clauses = [];
        $params = [];

        foreach ($conditions as $column => $value) {
            // Sử dụng placeholder để ngăn chặn SQL Injection
            $where_clauses[] = "`" . $column . "` = :{$column}";
            $params[":{$column}"] = $value;
        }

        $where_sql = implode(' AND ', $where_clauses);

        // Chỉ lấy 1 bản ghi đầu tiên (LIMIT 1)
        $sql = "SELECT * FROM `{$this->table}` WHERE {$where_sql} LIMIT 1";

        try {
            // Chuẩn bị câu lệnh (Prepare statement)
            $stmt = $this->pdo->prepare($sql);

            // Bind các tham số
            foreach ($params as $key => &$val) {
                // PDO::PARAM_STR thường dùng cho VARCHAR, INT cũng tự chuyển
                $stmt->bindParam($key, $val);
            }

            // Thực thi
            $stmt->execute();

            // Lấy kết quả dưới dạng mảng kết hợp
            return $stmt->fetch(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            // Xử lý lỗi CSDL (ví dụ: log lỗi)
            // echo "Lỗi truy vấn: " . $e->getMessage();
            return false;
        }
    }

    /**
     * Đếm tổng số bản ghi trong bảng.
     * @return int Tổng số bản ghi.
     */
    public function countAll()
    {
        $sql = "SELECT COUNT(*) FROM {$this->table}";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    // THÊM HÀM TÍNH TỔNG DOANH THU
    public function getTotalRevenue()
    {
        // Lưu ý: Hàm này chỉ nên chạy trên Repository của bảng 'bookings'
        $sql = "SELECT SUM(total_amount) FROM bookings WHERE status = 'paid'"; // Giả định có cột total_amount và status
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return (float) $stmt->fetchColumn();
    }

    // HÊM HÀM ĐẾM SỐ LƯỢNG VÉ ĐÃ BÁN 
    public function countBookedTickets()
    {
        // Hàm này chỉ nên chạy trên Repository của bảng 'booking_item'
        $sql = "SELECT COUNT(*) FROM booking_items WHERE status IN ('booked', 'checked_in')";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    /**
     * Lấy số lượng đặt vé (đã thanh toán) theo tháng trong 5 tháng gần nhất.
     * CHỈ NÊN CHẠY VỚI REPOSITORY CỦA BẢNG 'BOOKINGS'.
     * @return array Mảng kết hợp (Tháng/Năm => Số lượng đặt)
     */
    public function getMonthlyBookings(int $months = 5)
    {
        // MySQL query để nhóm theo tháng và đếm số lượng đơn hàng
        $sql = "
        SELECT 
            DATE_FORMAT(created_at, '%Y-%m') AS order_month,
            COUNT(id) AS total_bookings
        FROM 
            {$this->table}
        WHERE 
            created_at >= DATE_SUB(NOW(), INTERVAL :months MONTH)
            AND status IN ('paid', 'completed') -- Giả định chỉ thống kê đơn đã thanh toán
        GROUP BY 
            order_month
        ORDER BY 
            order_month ASC
    ";

        $stmt = $this->pdo->prepare($sql);
        // Bind tham số $months
        $stmt->bindParam(':months', $months, PDO::PARAM_INT);
        $stmt->execute();

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Chuyển đổi định dạng cho dễ hiển thị (ví dụ: "2025-10" thành "T10/25")
        $formatted_data = [];
        foreach ($results as $row) {
            $date = new DateTime($row['order_month'] . '-01');
            $label = 'T' . $date->format('m/y'); // Ví dụ: T11/25
            $formatted_data[$label] = (int) $row['total_bookings'];
        }

        return $formatted_data;
    }

    /**
     * Đếm số lượng các rạp chiếu đang hoạt động (Giả định có bảng 'theaters').
     * @return int
     */
    public function countTheaters()
    {
        $sql = "SELECT COUNT(*) FROM theaters";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    /**
     * Lấy tỷ lệ số lượng vé đã bán theo loại vé.
     * CHỈ NÊN CHẠY VỚI REPOSITORY CỦA BẢNG 'BOOKING_ITEM'.
     * @return array Mảng kết hợp (ticket_type => count)
     */
    public function getTicketsByType()
    {
        $sql = "
        SELECT 
            ticket_type,
            COUNT(id) as total
        FROM 
            {$this->table}
        WHERE 
            status IN ('booked', 'checked_in')
        GROUP BY 
            ticket_type
        ORDER BY 
            total DESC
    ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_KEY_PAIR); // Trả về mảng ['adult' => 500, 'child' => 200]
    }

    /**
     * Lấy danh sách bản ghi có giới hạn và offset để phục vụ phân trang.
     * @param int $limit Số lượng bản ghi mỗi trang.
     * @param int $offset Vị trí bắt đầu.
     * @param string $orderBy Cột sắp xếp (mặc định là id).
     * @param string $order Hướng sắp xếp ('ASC' hoặc 'DESC').
     * @return array Danh sách bản ghi.
     */
    public function getLimitAndOffset($limit, $offset, $orderBy = 'id', $order = 'DESC')
    {
        // Bảo vệ khỏi SQL Injection (đảm bảo $orderBy và $order là các giá trị an toàn)
        // Trong môi trường thực tế, cần whitelist các cột được phép sắp xếp.
        $safeOrderBy = preg_replace('/[^a-zA-Z0-9_]/', '', $orderBy);
        $safeOrder = (strtoupper($order) === 'ASC') ? 'ASC' : 'DESC';

        $sql = "SELECT * FROM {$this->table} ORDER BY {$safeOrderBy} {$safeOrder} LIMIT :limit OFFSET :offset";

        $stmt = $this->pdo->prepare($sql);

        // Bind giá trị và đảm bảo kiểu dữ liệu là INTEGER cho LIMIT và OFFSET
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Lấy các bản ghi từ CSDL dựa trên điều kiện WHERE.
     * Sử dụng prepared statements để đảm bảo an toàn.
     *
     * @param string $whereCondition Ví dụ: "column1 = :value1 AND column2 > :value2"
     * @param array $params Mảng key-value (tên placeholder => giá trị) cho prepared statement.
     * @param string $columns Các cột muốn chọn. Mặc định là "*" (tất cả).
     * @param string $orderBy Mệnh đề ORDER BY tùy chọn. Ví dụ: "created_at DESC"
     * @return array Mảng chứa các bản ghi (mảng kết hợp), hoặc mảng rỗng nếu không tìm thấy.
     */
    public function getByCondition(string $whereCondition, array $params = [], string $columns = "*", string $orderBy = ""): array
    {
        // Xây dựng câu truy vấn SQL
        $sql = "SELECT $columns FROM {$this->table} WHERE {$whereCondition}";

        if (!empty($orderBy)) {
            $sql .= " ORDER BY {$orderBy}";
        }

        try {
            // Chuẩn bị câu lệnh
            $stmt = $this->pdo->prepare($sql);

            // Gán các giá trị cho prepared statement
            foreach ($params as $key => &$value) {
                // Kiểm tra xem key có bắt đầu bằng ':' không (nếu là named placeholder)
                // PDO::bindParam/bindValue yêu cầu không có dấu ':' ở đầu tên tham số
                $bindKey = strpos($key, ':') === 0 ? substr($key, 1) : $key;

                // Nếu bạn dùng named placeholder trong $whereCondition (ví dụ: `WHERE id = :id`), 
                // bạn nên dùng $key chính xác từ $params
                $stmt->bindValue(":$key", $value);
            }

            // Thực thi câu lệnh
            $stmt->execute();

            // Lấy tất cả kết quả dưới dạng mảng kết hợp
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            // Xử lý lỗi (ví dụ: ghi log hoặc ném ngoại lệ)
            // Trong môi trường development, bạn có thể in ra lỗi để debug:
            // error_log("Database Error in getByCondition: " . $e->getMessage());
            // echo "Database Error: " . $e->getMessage();
            return [];
        }
    }

    // Hàm này được thêm vào để hỗ trợ logic booking_process
    public function runRawQuery(string $sql, array $params = []): array
    {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params); // Sử dụng mảng vị trí cho execute nếu query dùng '?'
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // error_log("Database Error in runRawQuery: " . $e->getMessage());
            return [];
        }
    }

}
