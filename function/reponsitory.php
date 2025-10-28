<?php
require_once __DIR__ . '/db.php';

class Repository {
    private $pdo;
    private $table;

    // ✅ Khởi tạo class: truyền vào tên bảng
    public function __construct($table) {
      $db = new  Database();
        $this->pdo = $db->connect();  // <-- Lấy kết nối từ db.php
        $this->table = $table;
    }

    // 🔹 Lấy tất cả dữ liệu
    public function getAll() {
        $stmt = $this->pdo->query("SELECT * FROM {$this->table}");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 🔹 Tìm theo ID
    public function find($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // 🔹 Tìm theo cột (ví dụ findBy('email', 'abc@gmail.com'))
    public function findBy($column, $value) {
        $sql = "SELECT * FROM {$this->table} WHERE $column = :value LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['value' => $value]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // 🔹 Thêm mới bản ghi
    public function insert($data) {
        $keys = array_keys($data);
        $fields = implode(',', $keys);
        $placeholders = ':' . implode(', :', $keys);

        $sql = "INSERT INTO {$this->table} ($fields) VALUES ($placeholders)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($data);
    }

    // 🔹 Cập nhật bản ghi
    public function update($id, $data) {
        $fields = implode(', ', array_map(fn($k) => "$k = :$k", array_keys($data)));
        $data['id'] = $id;
        $sql = "UPDATE {$this->table} SET $fields WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($data);
    }

    // 🔹 Xóa bản ghi
    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM {$this->table} WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
